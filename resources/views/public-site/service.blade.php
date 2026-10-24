@extends('public-site.simple-layout')

@section('title', $service->name)

@section('content')
<section class="service-detail">
    <div class="service-detail-image">
        @if($service->image_path)
            <img src="{{ asset('storage/'.$service->image_path) }}" alt="{{ $service->name }}">
        @else
            <div class="service-placeholder"><i class="fas fa-bell-concierge"></i></div>
        @endif
    </div>

    <div class="service-detail-copy">
        <span>Hotel Service</span>
        <h1>{{ $service->name }}</h1>
        <p>{{ $service->description ?: $service->short_description }}</p>

        @if($service->price_label)
            <strong class="service-price">{{ $service->price_label }}</strong>
        @elseif($service->price)
            <strong class="service-price">UGX {{ number_format((float) $service->price) }}</strong>
        @endif

        <a href="{{ $service->button_url ?: route('public.hotel') }}" class="site-book-btn">
            {{ $service->button_text ?: 'Book / Enquire' }}
        </a>
    </div>
</section>
@endsection
