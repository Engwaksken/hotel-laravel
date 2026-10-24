<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Booking confirmed · {{ $web->property->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/public-hotel.css') }}">
</head>
<body>
<main class="booking">
    <div class="card success-card">
        <h1>Booking confirmed</h1>
        <p>Your reference is</p>
        <h2>{{ $reservation->reference }}</h2>
        <p>{{ $reservation->check_in_at->format('d M Y') }} → {{ $reservation->check_out_at->format('d M Y') }}</p>
        <p>{{ $reservation->rooms->first()?->roomType?->name }}</p>
        <p>Total: <b>UGX {{ number_format((float) $reservation->grand_total) }}</b></p>
        <p>Deposit required: <b>UGX {{ number_format((float) $reservation->deposit_required) }}</b></p>

        @if($paymentUrl)
            <p>Pay the remaining deposit securely to complete your booking.</p>
            <a class="btn" href="{{ $paymentUrl }}">Proceed to payment · UGX {{ number_format($depositDue) }}</a>
        @else
            <p class="payment-success">Your required deposit has been paid.</p>
        @endif

        <p><a href="{{ route('public.hotel.property', $web->slug) }}">Back to hotel</a></p>
    </div>
</main>
</body>
</html>
