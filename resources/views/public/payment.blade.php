<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pay deposit · {{ $web->property->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/public-hotel.css') }}">
</head>
<body>
<main class="booking">
    <div class="card payment-card">
        <h1>Pay your booking deposit</h1>
        <p>Reservation <strong>{{ $reservation->reference }}</strong></p>
        <p>{{ $reservation->rooms->first()?->roomType?->name }} · {{ $reservation->check_in_at->format('d M Y') }} to {{ $reservation->check_out_at->format('d M Y') }}</p>

        @if($errors->any())
            <div class="error" role="alert">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if($depositDue < 1)
            <p>Your required deposit has already been paid.</p>
        @else
            <p class="payment-amount">Amount due: <strong>UGX {{ number_format($depositDue) }}</strong></p>

            <form method="post" action="{{ $initiateUrl }}">
                @csrf
                <label for="payment-method">Payment method</label>
                <select id="payment-method" name="method" required>
                    <option value="">Choose a payment method</option>
                    @foreach($methods as $method => $label)
                        <option value="{{ $method }}" @selected(old('method') === $method)>{{ $label }}</option>
                    @endforeach
                </select>

                <label for="payer-phone">Mobile money phone <small>(required for MTN and Airtel)</small></label>
                <input id="payer-phone" type="tel" name="phone" value="{{ old('phone', $reservation->guest?->phone) }}" autocomplete="tel" placeholder="e.g. +256 700 000000">

                <p class="payment-note">You will continue to the payment provider, or receive a mobile-money approval prompt. Your reservation is marked paid only after the provider confirms payment.</p>
                <button class="btn" type="submit">Continue to secure payment</button>
            </form>
        @endif

        <p><a href="{{ route('public.booking.confirmation', ['slug' => $web->slug, 'reservation' => $reservation->reference]) }}">Return to booking confirmation</a></p>
    </div>
</main>
</body>
</html>
