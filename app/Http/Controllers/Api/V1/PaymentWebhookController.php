<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, string $gateway, PaymentManager $manager)
    {
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

        $payment = Payment::query()
            ->where(function ($query) use ($reference, $tracking): void {
                if ($reference !== '') {
                    $query->where('reference', $reference);
                }

                if ($tracking !== '') {
                    $method = $reference !== '' ? 'orWhere' : 'where';
                    $query->{$method}('gateway_reference', $tracking);
                }
            })
            ->first();

        if (! $payment || $this->canonicalGateway((string) $payment->gateway) !== $this->canonicalGateway($gateway)) {
            return response()->json(['received' => true]);
        }

        try {
            $verifyReference = $gateway === 'pesapal' && $tracking !== ''
                ? $tracking
                : ($payment->gateway_reference ?: $payment->reference);
            $verification = $manager->gateway($gateway)->verify($verifyReference);

            DB::transaction(function () use ($payment, $gateway, $request, $verification): void {
                $lockedPayment = Payment::query()->lockForUpdate()->find($payment->id);

                if (! $lockedPayment) {
                    return;
                }

                PaymentTransaction::create([
                    'payment_id' => $lockedPayment->id,
                    'gateway' => $gateway,
                    'gateway_reference' => $verification['gateway_reference'] ?? $lockedPayment->gateway_reference,
                    'status' => $verification['status'] ?? 'unknown',
                    'webhook_payload' => $request->all(),
                    'response_payload' => $verification['raw'] ?? $verification,
                    'verified_at' => now(),
                ]);

                $currencyMatches = ($verification['currency'] ?? $lockedPayment->currency) === $lockedPayment->currency;
                $amountMatches = (float) ($verification['amount'] ?? $lockedPayment->amount) + 0.01 >= (float) $lockedPayment->amount;

                if (($verification['successful'] ?? false) && $currencyMatches && $amountMatches) {
                    $lockedPayment->update([
                        'status' => 'successful',
                        'gateway_reference' => $verification['gateway_reference'] ?? $lockedPayment->gateway_reference,
                        'paid_at' => now(),
                    ]);
                } elseif (isset($verification['status'])) {
                    $lockedPayment->update([
                        'status' => in_array($verification['status'], ['failed', 'cancelled'], true)
                            ? $verification['status']
                            : 'processing',
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json(['received' => true]);
    }

    private function canonicalGateway(string $gateway): string
    {
        return $gateway === 'card' ? 'flutterwave' : $gateway;
    }
}
