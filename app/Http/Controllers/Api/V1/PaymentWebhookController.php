<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGatewayConfig;
use App\Models\PaymentTransaction;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, string $gateway, PaymentManager $manager): JsonResponse
    {
        $canonicalGateway = $this->canonicalGateway($gateway);

        $reference = (string) ($request->input('OrderMerchantReference')
            ?? $request->input('tx_ref')
            ?? $request->input('reference')
            ?? '');
        $tracking = (string) ($request->input('OrderTrackingId')
            ?? $request->input('transaction_id')
            ?? $request->input('id')
            ?? '');

        if ($reference === '' && $tracking === '') {
            return response()->json(['received' => true]);
        }

        // Prefer the merchant reference when both are provided, and only fall back to
        // the provider reference if it did not locate a payment.
        $payment = $reference !== ''
            ? Payment::query()->where('reference', $reference)->first()
            : null;

        if (! $payment && $tracking !== '') {
            $payment = Payment::query()->where('gateway_reference', $tracking)->first();
        }

        if (! $payment || $this->canonicalGateway((string) $payment->gateway) !== $canonicalGateway) {
            return response()->json(['received' => true]);
        }

        if ($canonicalGateway === 'flutterwave' && ! $this->hasValidFlutterwaveSignature($request, $payment)) {
            return response()->json(['received' => false, 'message' => 'Invalid webhook signature.'], 401);
        }

        try {
            $verifyReference = match ($canonicalGateway) {
                // Flutterwave verifies against the merchant tx_ref, not its event id.
                'flutterwave' => (string) $payment->reference,
                'pesapal' => $tracking !== '' ? $tracking : (string) ($payment->gateway_reference ?: $payment->reference),
                default => (string) ($payment->gateway_reference ?: $payment->reference),
            };
            $verification = $manager->gateway($gateway, (int) $payment->property_id)->verify($verifyReference);
            $remoteStatus = strtolower(trim((string) ($verification['status'] ?? 'unknown')));

            DB::transaction(function () use ($payment, $gateway, $tracking, $request, $verification, $remoteStatus): void {
                $lockedPayment = Payment::query()->lockForUpdate()->find($payment->id);
                if (! $lockedPayment) {
                    return;
                }

                $currentStatus = $this->paymentStatus($lockedPayment);
                // A stale retry must not regress a successful or terminal payment.
                if (in_array($currentStatus, ['successful', 'failed', 'cancelled', 'refunded', 'reversed'], true)) {
                    return;
                }

                $transactionReference = $verification['gateway_reference']
                    ?? $lockedPayment->gateway_reference
                    ?? ($tracking !== '' ? $tracking : null);
                $transactionQuery = PaymentTransaction::query()
                    ->where('payment_id', $lockedPayment->id)
                    ->where('gateway', $gateway)
                    ->where('status', $remoteStatus);

                if ($transactionReference === null) {
                    $transactionQuery->whereNull('gateway_reference');
                } else {
                    $transactionQuery->where('gateway_reference', $transactionReference);
                }

                // The payment row lock serializes retries, while this check keeps
                // repeated identical provider notifications from creating duplicates.
                if (! $transactionQuery->exists()) {
                    PaymentTransaction::create([
                        'payment_id' => $lockedPayment->id,
                        'gateway' => $gateway,
                        'gateway_reference' => $transactionReference,
                        'status' => $remoteStatus ?: 'unknown',
                        'webhook_payload' => $request->all(),
                        'response_payload' => $verification['raw'] ?? $verification,
                        'verified_at' => now(),
                    ]);
                }

                $remoteCurrency = strtoupper(trim((string) ($verification['currency'] ?? $lockedPayment->currency)));
                $localCurrency = strtoupper(trim((string) $lockedPayment->currency));
                $currencyMatches = $remoteCurrency === $localCurrency;
                $amountMatches = (float) ($verification['amount'] ?? 0) + 0.01 >= (float) $lockedPayment->amount;

                if (($verification['successful'] ?? false) && $currencyMatches && $amountMatches) {
                    $lockedPayment->update([
                        'status' => PaymentStatus::Successful,
                        'gateway_reference' => $transactionReference ?? $lockedPayment->gateway_reference,
                        'paid_at' => $lockedPayment->paid_at ?: now(),
                    ]);
                    return;
                }

                if ($this->isTerminalFailure($remoteStatus)) {
                    $lockedPayment->update([
                        'status' => in_array($remoteStatus, ['cancelled', 'canceled', 'expired', 'aborted'], true)
                            ? PaymentStatus::Cancelled
                            : PaymentStatus::Failed,
                    ]);
                    return;
                }

                if ($currentStatus === 'pending') {
                    $lockedPayment->update(['status' => PaymentStatus::Processing]);
                }
            });
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json(['received' => true]);
    }

    private function hasValidFlutterwaveSignature(Request $request, Payment $payment): bool
    {
        $propertySecret = PaymentGatewayConfig::query()
            ->where('property_id', $payment->property_id)
            ->whereIn('gateway', ['flutterwave', 'card'])
            ->get()
            ->first(fn (PaymentGatewayConfig $config): bool => filled(data_get($config->credentials, 'webhook_secret')));
        $secret = (string) (
            data_get($propertySecret?->credentials, 'webhook_secret')
            ?: config('payment_gateways.flutterwave.webhook_secret', '')
        );
        $signature = (string) $request->header('verif-hash', '');

        return $secret !== '' && $signature !== '' && hash_equals($secret, $signature);
    }

    private function paymentStatus(Payment $payment): string
    {
        return $payment->status instanceof PaymentStatus
            ? $payment->status->value
            : (string) $payment->status;
    }

    private function isTerminalFailure(string $status): bool
    {
        return in_array($status, [
            'failed', 'failure', 'cancelled', 'canceled', 'rejected', 'declined',
            'expired', 'aborted', 'tf',
        ], true);
    }

    private function canonicalGateway(string $gateway): string
    {
        return match ($gateway) {
            'card' => 'flutterwave',
            'airtel_money' => 'airtel_momo',
            default => $gateway,
        };
    }
}
