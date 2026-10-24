@extends('client.layout')
@section('title', 'Hotel Services')
@section('content')
<div class="client-page-head"><span>Guest Services</span><h1>Hotel services</h1><p>Explore services available before and during your stay.</p></div>
<div class="client-card-grid">
@forelse($services as $service)
    <article class="service-card">
        <div class="service-icon"><i class="fas fa-bell-concierge"></i></div>
        <h3>{{ $service->name ?? $service->title ?? 'Hotel Service' }}</h3>
        <p>{{ $service->description ?? 'Available to registered guests.' }}</p>
        @if(isset($service->price))<strong>UGX {{ number_format((float) $service->price) }}</strong>@endif
    </article>
@empty
    <div class="client-empty">No services are available yet.</div>
@endforelse
</div>
@endsection
