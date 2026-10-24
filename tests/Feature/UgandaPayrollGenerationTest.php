<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\PayrollComponent;
use App\Models\Property;
use App\Models\User;
use App\Services\HR\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UgandaPayrollGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_payroll_generates_progressive_paye_and_gross_wage_nssf(): void
    {
        $property = Property::factory()->create();
        $user = User::factory()->create(['property_id' => $property->id]);
        $employee = Employee::create([
            'property_id' => $property->id,
            'employee_no' => 'EMP-00001',
            'first_name' => 'Payroll',
            'last_name' => 'Test',
            'hire_date' => '2025-01-01',
            'employment_type' => 'permanent',
            'status' => 'active',
            'basic_salary' => 500_000,
            'tax_residency' => 'resident',
            'nssf_eligible' => true,
        ]);

        PayrollComponent::create([
            'property_id' => $property->id,
            'name' => 'Housing allowance',
            'code' => 'HOUSING',
            'type' => 'earning',
            'calculation' => 'fixed',
            'default_value' => 100_000,
            'taxable' => true,
            'is_active' => true,
        ]);

        foreach ([
            ['PAYE', 'PAYE', 'deduction', 'fixed', 0],
            ['NSSF employee', 'PENSION_EE', 'deduction', 'percentage', 5],
            ['NSSF employer', 'PENSION_ER', 'employer_cost', 'percentage', 10],
        ] as [$name, $code, $type, $calculation, $value]) {
            PayrollComponent::create([
                'property_id' => $property->id,
                'name' => $name,
                'code' => $code,
                'type' => $type,
                'calculation' => $calculation,
                'default_value' => $value,
                'statutory' => true,
                'is_active' => true,
            ]);
        }

        $run = app(PayrollService::class)->generate(
            $property->id,
            '2026-10-01',
            '2026-10-31',
            $user->id,
        );

        $item = $run->items()->where('employee_id', $employee->id)->firstOrFail();
        self::assertSame(600_000.0, (float) $item->gross_pay);
        self::assertSame(98_250.0, (float) $item->deductions);
        self::assertSame(60_000.0, (float) $item->employer_costs);
        self::assertSame(501_750.0, (float) $item->net_pay);
    }
}
