@extends('client.layout')

@section('title', 'Guest Dashboard')

@section('content')
<section class="client-hero">
    <div>
        <span>Welcome back</span>
        <h1>{{ $user->name }}</h1>
        <p>Book your stay, explore hotel services and track your payments.</p>
    </div>
    <a href="{{ route('public.hotel') }}" class="client-primary-btn">
        <i class="fas fa-calendar-plus"></i> Book a Stay
    </a>
</section>

<div class="client-stat-grid">
    <div class="client-stat"><i class="fas fa-calendar-check"></i><strong>{{ $bookings->count() }}</strong><span>Bookings</span></div>
    <div class="client-stat"><i class="fas fa-credit-card"></i><strong>{{ $payments->count() }}</strong><span>Payments</span></div>
    <div class="client-stat"><i class="fas fa-bell-concierge"></i><strong>{{ $services->count() }}</strong><span>Services</span></div>
    <div class="client-stat"><i class="fas fa-user"></i><strong>1</strong><span>Profile</span></div>
</div>

<section class="client-section">
    <div class="client-section-head">
        <div><h2>Quick actions</h2><p>Everything a guest needs in one place.</p></div>
    </div>

    <div class="client-action-grid">
        <a href="{{ route('public.hotel') }}"><i class="fas fa-hotel"></i><span>Book Stay</span></a>
        <a href="{{ route('client.services') }}"><i class="fas fa-bell-concierge"></i><span>Services</span></a>
        <a href="{{ route('client.bookings') }}"><i class="fas fa-calendar-check"></i><span>Bookings</span></a>
        <a href="{{ route('client.payments') }}"><i class="fas fa-credit-card"></i><span>Payments</span></a>
        @if(\Illuminate\Support\Facades\Route::has('profile.edit'))
            <a href="{{ route('profile.edit') }}"><i class="fas fa-user-pen"></i><span>Profile</span></a>
        @endif
    </div>
</section>

<section class="client-section">
    <div class="client-section-head">
        <div><h2>Hotel services</h2><p>Services available to guests.</p></div>
        <a href="{{ route('client.services') }}">View all</a>
    </div>

    <div class="client-card-grid">
        @forelse($services->take(6) as $service)
            <article class="service-card">
                <div class="service-icon"><i class="fas fa-bell-concierge"></i></div>
                <h3>{{ $service->name ?? $service->title ?? 'Hotel Service' }}</h3>
                <p>{{ $service->description ?? 'Available to hotel guests.' }}</p>
                @if(isset($service->price))
                    <strong>UGX {{ number_format((float) $service->price) }}</strong>
                @endif
            </article>
        @empty
            <div class="client-empty">No guest services have been published yet.</div>
        @endforelse
    </div>
</section>
@endsection
