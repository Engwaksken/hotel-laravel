<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\PropertyWebSetting;
use App\Models\Reservation;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class PublicBookingPaymentController extends Controller
{
    private const GATEWAYS = [
        'mtn_momo' => 'MTN Mobile Money',
        'airtel_momo' => 'Airtel Money',
        'pesapal' => 'Pesapal',
        'flutterwave' => 'Flutterwave',
        'card' => 'Card',
    ];

    public function show(string $slug, Reservation $reservation)
    {
        [$web, $reservation] = $this->booking($slug, $reservation);
        $depositDue = $this->depositDue($reservation);

        return view('public.payment', [
            'web' => $web,
            'reservation' => $reservation->load('guest', 'rooms.roomType'),
            'depositDue' => $depositDue,
            'methods' => self::GATEWAYS,
            'initiateUrl' => URL::temporarySignedRoute(
                'public.booking.payment.initiate',
                now()->addHours(24),
                ['slug' => $slug, 'reservation' => $reservation->reference],
            ),
        ]);
    }

    public function initiate(
        Request $request,
        string $slug,
        Reservation $reservation,
        PaymentManager $manager,
    ) {
        [$web, $reservation] = $this->booking($slug, $reservation);

        $data = $request->validate([
            'method' => ['required', 'string', 'in:'.implode(',', array_keys(self::GATEWAYS))],
            'phone' => ['nullable', 'string', 'max:50', 'required_if:method,mtn_momo,airtel_momo'],
        ]);

        $amount = $this->depositDue($reservation);
        if ($amount < 1) {
            throw ValidationException::withMessages([
                'payment' => 'There is no deposit balance left to pay for this reservation.',
            ]);
        }

        $guest = $reservation->guest;
        $method = $data['method'];
        $payment = Payment::query()->create([
            'property_id' => $reservation->property_id,
            'guest_id' => $reservation->guest_id,
            'reservation_id' => $reservation->id,
            'reference' => 'PAY-'.Str::upper(Str::random(12)),
            'method' => $method,
            'gateway' => $method,
            'status' => 'pending',
            'currency' => 'UGX',
            'amount' => $amount,
            'payer_phone' => $data['phone'] ?? $guest?->phone,
            'description' => 'Booking deposit for '.$reservation->reference,
        ]);

        $callbackUrl = url('/api/v1/payments/webhook/'.$method);
        $payload = [
            'reference' => $payment->reference,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'phone' => $payment->payer_phone,
            'email' => $guest?->email,
            'name' => trim(($guest?->first_name ?? '').' '.($guest?->last_name ?? '')) ?: 'Hotel Guest',
            'first_name' => $guest?->first_name,
            'last_name' => $guest?->last_name,
            'description' => $payment->description,
            'callback_url' => $callbackUrl,
        ];

        try {
            $checkout = $manager->gateway($method)->initiate($payload);
            $gatewayReference = $checkout['gateway_reference'] ?? null;

            $payment->update([
                'status' => 'processing',
                'gateway_reference' => $gatewayReference,
            ]);

            PaymentTransaction::query()->create([
                'payment_id' => $payment->id,
                'gateway' => $method,
                'gateway_reference' => $gatewayReference,
                'status' => 'processing',
                'request_payload' => $payload,
                'response_payload' => $checkout,
            ]);

            $redirectUrl = $checkout['redirect_url']
                ?? data_get($checkout, 'response.data.link')
                ?? data_get($checkout, 'response.data.meta.authorization.redirect');

            if ($this->isHttpsUrl($redirectUrl)) {
                return redirect()->away($redirectUrl);
            }

            return response()->view('public.payment-pending', [
                'web' => $web,
                'reservation' => $reservation,
                'payment' => $payment->fresh(),
                'method' => self::GATEWAYS[$method],
                'confirmationUrl' => route('public.booking.confirmation', [
                    'slug' => $slug,
                    'reservation' => $reservation->reference,
                ]),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            $payment->update(['status' => 'failed']);

            PaymentTransaction::query()->create([
                'payment_id' => $payment->id,
                'gateway' => $method,
                'status' => 'failed',
                'failure_reason' => 'Payment provider could not start the checkout.',
            ]);

            return back()
                ->withErrors(['payment' => 'We could not start payment with that provider. Please try another method or contact the hotel.'])
                ->withInput();
        }
    }

    private function booking(string $slug, Reservation $reservation): array
    {
        $web = PropertyWebSetting::query()
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless(
            $web->online_booking_enabled && $reservation->property_id === $web->property_id,
            404,
        );

        return [$web, $reservation];
    }

    private function depositDue(Reservation $reservation): float
    {
        $paid = (float) $reservation->payments()
            ->where('status', 'successful')
            ->sum('amount');

        return max(0, round((float) $reservation->deposit_required - $paid, 2));
    }

    private function isHttpsUrl(mixed $url): bool
    {
        if (! is_string($url) || $url === '') {
            return false;
        }

        $parts = parse_url($url);

        return ($parts['scheme'] ?? null) === 'https'
            && ! empty($parts['host']);
    }
}
