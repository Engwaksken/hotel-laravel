@extends('public-site.simple-layout')

@section('title', 'Services')

@section('content')
<section class="inner-hero">
    <div>
        <span>Guest Experience</span>
        <h1>Hotel services</h1>
        <p>Explore dining, wellness, transfers, events and other guest services.</p>
    </div>
</section>

<section class="public-section">
    <div class="service-image-grid">
        @forelse($services as $service)
            <a href="{{ route('public.services.show', $service->slug) }}" class="service-image-card">
                @if($service->image_path)
                    <img src="{{ asset('storage/'.$service->image_path) }}" alt="{{ $service->name }}">
                @else
                    <div class="service-placeholder"><i class="fas fa-bell-concierge"></i></div>
                @endif
                <div class="service-image-overlay">
                    <h3>{{ $service->name }}</h3>
                    <p>{{ $service->short_description }}</p>
                </div>
            </a>
        @empty
            <div class="public-empty">No services have been published yet.</div>
        @endforelse
    </div>
</section>
@endsection
