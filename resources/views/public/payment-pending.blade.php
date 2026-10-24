<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Payment request sent · {{ $web->property->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/public-hotel.css') }}">
</head>
<body>
<main class="booking">
    <div class="card success-card">
        <h1>Payment request sent</h1>
        <p>Approve the {{ $method }} request on your phone if prompted.</p>
        <p>Payment reference: <strong>{{ $payment->reference }}</strong></p>
        <p>We will update your booking after the provider confirms the payment. Please do not submit another payment while this request is pending.</p>
        <a class="btn" href="{{ $confirmationUrl }}">Return to booking</a>
    </div>
</main>
</body>
</html>
