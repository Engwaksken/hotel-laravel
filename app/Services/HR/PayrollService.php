<?php

namespace App\Services\HR;

use App\Models\Employee;
use App\Models\PayrollComponent;
use App\Models\PayrollRun;
use App\Services\Accounting\AccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PayrollService
{
    public function __construct(
        private AccountingService $accounting,
        private UgandaPayrollRules $statutoryRules,
    ) {
    }

    public function generate(int $propertyId, string $start, string $end, int $userId): PayrollRun
    {
        return DB::transaction(function () use ($propertyId, $start, $end, $userId): PayrollRun {
            $run = PayrollRun::create([
                'property_id' => $propertyId,
                'reference' => 'PAY-'.date('Ym', strtotime($end)).'-'.Str::upper(Str::random(5)),
                'period_start' => $start,
                'period_end' => $end,
                'status' => 'draft',
                'created_by' => $userId,
            ]);

            $components = PayrollComponent::query()
                ->where('property_id', $propertyId)
                ->where('is_active', true)
                ->get();

            $totals = ['gross' => 0.0, 'deductions' => 0.0, 'employer_costs' => 0.0, 'net' => 0.0];

            Employee::query()
                ->where('property_id', $propertyId)
                ->where('status', 'active')
                ->get()
                ->each(function (Employee $employee) use ($run, $components, $start, $end, &$totals): void {
                    $basic = (float) $employee->basic_salary;
                    $earnings = 0.0;
                    $taxableEarnings = 0.0;
                    $deductions = 0.0;
                    $employerCosts = 0.0;
                    $breakdown = [];
                    $resolvedComponents = [];

                    foreach ($components as $component) {
                        $pivot = DB::table('employee_payroll_components')
                            ->where('employee_id', $employee->id)
                            ->where('payroll_component_id', $component->id)
                            ->whereDate('effective_from', '<=', $end)
                            ->where(fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $start))
                            ->orderByDesc('effective_from')
                            ->first();

                        $value = (float) ($pivot->value ?? $component->default_value);
                        $code = strtoupper((string) $component->code);

                        if ($this->isStatutoryCode($code)) {
                            $resolvedComponents[] = [$component, $value, $code];
                            continue;
                        }

                        $amount = $component->calculation === 'percentage'
                            ? round($basic * $value / 100, 2)
                            : round($value, 2);

                        if ($component->type === 'earning') {
                            $earnings += $amount;
                            if ($component->taxable) {
                                $taxableEarnings += $amount;
                            }
                        } elseif ($component->type === 'deduction') {
                            $deductions += $amount;
                        } else {
                            $employerCosts += $amount;
                        }

                        $breakdown[] = [
                            'code' => $component->code,
                            'name' => $component->name,
                            'type' => $component->type,
                            'amount' => $amount,
                        ];
                    }

                    $gross = $basic + $earnings;
                    $taxableIncome = $basic + $taxableEarnings;

                    foreach ($resolvedComponents as [$component, $value, $code]) {
                        $amount = match ($code) {
                            'PAYE' => $component->type === 'deduction'
                                ? $this->statutoryRules->paye(
                                $taxableIncome,
                                $employee->tax_residency ?? 'resident',
                                )
                                : 0.0,
                            'PENSION_EE', 'NSSF_EE' => $component->type === 'deduction' && $employee->nssf_eligible
                                ? $this->statutoryRules->nssfEmployee($gross)
                                : 0.0,
                            'PENSION_ER', 'NSSF_ER' => $component->type === 'employer_cost' && $employee->nssf_eligible
                                ? $this->statutoryRules->nssfEmployer($gross)
                                : 0.0,
                            default => $component->calculation === 'percentage'
                                ? round($basic * $value / 100, 2)
                                : round($value, 2),
                        };

                        if ($component->type === 'deduction') {
                            $deductions += $amount;
                        } else {
                            $employerCosts += $amount;
                        }

                        $breakdown[] = [
                            'code' => $component->code,
                            'name' => $component->name,
                            'type' => $component->type,
                            'amount' => $amount,
                        ];
                    }

                    $net = max(0, $gross - $deductions);
                    $run->items()->create([
                        'employee_id' => $employee->id,
                        'basic_salary' => $basic,
                        'earnings' => $earnings,
                        'deductions' => $deductions,
                        'employer_costs' => $employerCosts,
                        'gross_pay' => $gross,
                        'net_pay' => $net,
                        'breakdown' => $breakdown,
                    ]);

                    $totals['gross'] += $gross;
                    $totals['deductions'] += $deductions;
                    $totals['employer_costs'] += $employerCosts;
                    $totals['net'] += $net;
                });

            $run->update([
                'gross_total' => $totals['gross'],
                'deduction_total' => $totals['deductions'],
                'employer_cost_total' => $totals['employer_costs'],
                'net_total' => $totals['net'],
            ]);

            return $run->load('items.employee');
        });
    }

    private function isStatutoryCode(string $code): bool
    {
        return in_array($code, ['PAYE', 'PENSION_EE', 'PENSION_ER', 'NSSF_EE', 'NSSF_ER'], true);
    }

    public function approve(PayrollRun $run, int $userId): PayrollRun
    {
        if ($run->status !== 'draft') {
            throw new RuntimeException('Only draft payroll can be approved.');
        }

        return DB::transaction(function () use ($run, $userId): PayrollRun {
            $journal = $this->accounting->post(
                $run->property_id,
                'payroll_run',
                $run->id,
                'Payroll '.$run->period_start->format('d M').' - '.$run->period_end->format('d M Y'),
                [
                    ['code' => '5200', 'debit' => (float) $run->gross_total + (float) $run->employer_cost_total],
                    ['code' => '2250', 'credit' => (float) $run->net_total],
                    ...((float) $run->deduction_total > 0 ? [['code' => '2260', 'credit' => (float) $run->deduction_total]] : []),
                    ...((float) $run->employer_cost_total > 0 ? [['code' => '2270', 'credit' => (float) $run->employer_cost_total]] : []),
                ],
                $userId,
            );

            $run->update(['status' => 'approved', 'approved_by' => $userId, 'journal_entry_id' => $journal->id]);

            return $run->refresh();
        });
    }

    public function markPaid(PayrollRun $run, string $method, int $userId): PayrollRun
    {
        if (! in_array($run->status, ['approved', 'partially_paid'], true)) {
            throw new RuntimeException('Payroll must be approved before payment.');
        }

        return DB::transaction(function () use ($run, $method, $userId): PayrollRun {
            $unpaid = $run->items()->where('payment_status', 'unpaid')->sum('net_pay');
            if ($unpaid <= 0) {
                return $run;
            }

            $cashCode = match ($method) {
                'cash' => '1000',
                'mtn_momo' => '1020',
                'airtel_money' => '1030',
                default => '1010',
            };

            $this->accounting->post(
                $run->property_id,
                'payroll_payment',
                $run->id,
                'Payroll payment',
                [['code' => '2250', 'debit' => (float) $unpaid], ['code' => $cashCode, 'credit' => (float) $unpaid]],
                $userId,
                'PAYMENT-'.$run->reference,
            );

            $run->items()->where('payment_status', 'unpaid')->update([
                'payment_status' => 'paid',
                'payment_method' => $method,
                'paid_at' => now(),
            ]);
            $run->update(['status' => 'paid', 'pay_date' => now()->toDateString()]);

            return $run->refresh();
        });
    }
}
