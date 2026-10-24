@extends('client.layout')
@section('title', 'My Bookings')
@section('content')
<div class="client-page-head split">
    <div><span>Reservations</span><h1>My bookings</h1><p>Track your hotel stays and reservation status.</p></div>
    <a href="{{ route('public.hotel') }}" class="client-primary-btn"><i class="fas fa-calendar-plus"></i> New Booking</a>
</div>
<div class="client-list">
@forelse($bookings as $booking)
    <article class="booking-card">
        <div>
            <strong>{{ $booking->booking_reference ?? $booking->reference ?? ('Booking #'.$booking->id) }}</strong>
            <small>{{ $booking->check_in_date ?? $booking->arrival_date ?? '—' }} → {{ $booking->check_out_date ?? $booking->departure_date ?? '—' }}</small>
        </div>
        <span class="client-status">{{ ucfirst((string) ($booking->status ?? 'pending')) }}</span>
    </article>
@empty
    <div class="client-empty">You have not made any bookings yet.</div>
@endforelse
</div>
@endsection
