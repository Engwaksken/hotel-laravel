@extends('client.layout')
@section('title', 'My Payments')
@section('content')
<div class="client-page-head"><span>Billing</span><h1>Payments & receipts</h1><p>View your hotel payment history.</p></div>
<div class="client-list">
@forelse($payments as $payment)
    <article class="payment-card">
        <div>
            <strong>UGX {{ number_format((float) ($payment->amount ?? 0)) }}</strong>
            <small>{{ $payment->reference ?? $payment->payment_reference ?? 'Payment' }}</small>
        </div>
        <span class="client-status">{{ ucfirst((string) ($payment->status ?? 'completed')) }}</span>
    </article>
@empty
    <div class="client-empty">No payment records were found for your account.</div>
@endforelse
</div>
@endsection
