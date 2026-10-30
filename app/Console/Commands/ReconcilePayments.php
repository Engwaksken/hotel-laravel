<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Services\Payments\PaymentManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {--limit=100 : Maximum pending payments to verify in one run}';
    protected $description = 'Verify pending gateway payments with their payment providers and reconcile their HMS status.';

    public function handle(PaymentManager $manager): int
    {
        $limit = max(1, min(500, (int) $this->option('limit')));
        $checked = 0;
        $successful = 0;
        $failed = 0;
        $stillProcessing = 0;
        $errors = 0;

        $payments = Payment::query()
            ->whereIn('status', ['pending', 'processing'])
            ->whereNotNull('gateway')
            ->where(function ($query) {
                $query->whereNotNull('gateway_reference')
                    ->orWhereNotNull('reference');
            })
            ->oldest()
            ->limit($limit)
            ->get();

        foreach ($payments as $payment) {
            $checked++;
            $gateway = $this->normaliseGateway((string) $payment->gateway);

            try {
                $verifyReference = $this->verificationReference($payment, $gateway);
                $result = $manager->gateway($gateway, (int) $payment->property_id)->verify($verifyReference);

                $remoteStatus = strtolower(trim((string) ($result['status'] ?? 'unknown')));
                $remoteAmount = array_key_exists('amount', $result) ? (float) $result['amount'] : null;
                $remoteCurrency = strtoupper((string) ($result['currency'] ?? $payment->currency));
                $localCurrency = strtoupper((string) $payment->currency);
                $amountOkay = $remoteAmount === null || ($remoteAmount + 0.01 >= (float) $payment->amount);
                $currencyOkay = $remoteCurrency === '' || $remoteCurrency === $localCurrency;
                $verifiedSuccess = (bool) ($result['successful'] ?? false) && $amountOkay && $currencyOkay;

                DB::transaction(function () use ($payment, $gateway, $result, $remoteStatus, $verifiedSuccess, &$successful, &$failed, &$stillProcessing) {
                    $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);

                    PaymentTransaction::create([
                        'payment_id' => $locked->id,
                        'gateway' => $gateway,
                        'gateway_reference' => $result['gateway_reference'] ?? $locked->gateway_reference,
                        'status' => $remoteStatus ?: 'unknown',
                        'response_payload' => $result['raw'] ?? $result,
                        'verified_at' => now(),
                        'reconciled_at' => now(),
                    ]);

                    if ($verifiedSuccess) {
                        if ((string) ($locked->status->value ?? $locked->status) !== 'successful') {
                            $locked->update([
                                'status' => 'successful',
                                'gateway_reference' => $result['gateway_reference'] ?? $locked->gateway_reference,
                                'paid_at' => $locked->paid_at ?: now(),
                            ]);
                        }
                        $successful++;
                        return;
                    }

                    if ($this->isTerminalFailure($remoteStatus)) {
                        $locked->update(['status' => $this->failureStatus($remoteStatus)]);
                        $failed++;
                        return;
                    }

                    if ((string) ($locked->status->value ?? $locked->status) === 'pending') {
                        $locked->update(['status' => 'processing']);
                    }
                    $stillProcessing++;
                });
            } catch (\InvalidArgumentException $e) {
                $errors++;
                $this->warn("{$payment->reference}: unsupported/configuration issue for gateway [{$gateway}] - {$e->getMessage()}");
            } catch (\Throwable $e) {
                $errors++;
                report($e);
                $this->warn("{$payment->reference}: verification failed - {$e->getMessage()}");
            }
        }

        $this->info("Checked {$checked} payment(s): {$successful} successful, {$failed} failed/cancelled, {$stillProcessing} processing, {$errors} verification error(s).");

        // Provider outages/configuration errors should not crash the scheduler. They are logged and retried next run.
        return self::SUCCESS;
    }

    private function normaliseGateway(string $gateway): string
    {
        return match ($gateway) {
            'airtel_money' => 'airtel_momo',
            default => $gateway,
        };
    }

    private function verificationReference(Payment $payment, string $gateway): string
    {
        // Flutterwave's adapter verifies by tx_ref, which is the HMS payment reference.
        if (in_array($gateway, ['flutterwave', 'card'], true)) {
            return (string) $payment->reference;
        }

        return (string) ($payment->gateway_reference ?: $payment->reference);
    }

    private function isTerminalFailure(string $status): bool
    {
        return in_array($status, [
            'failed', 'failure', 'cancelled', 'canceled', 'rejected', 'declined',
            'expired', 'aborted', 'tf',
        ], true);
    }

    private function failureStatus(string $status): string
    {
        return in_array($status, ['cancelled', 'canceled', 'expired', 'aborted'], true)
            ? 'cancelled'
            : 'failed';
    }
}
