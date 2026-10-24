@extends('public-site.simple-layout')

@section('title', $service->name)

@section('content')
@php
    $enquiryUrl = filled($service->button_url)
        ? $service->button_url
        : (filled($settings['support_email'] ?? null)
            ? 'mailto:'.$settings['support_email'].'?subject='.rawurlencode('Enquiry about '.$service->name)
            : (filled($settings['support_phone'] ?? null)
                ? 'tel:'.$settings['support_phone']
                : '#contact'));
@endphp

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

        <a href="{{ $enquiryUrl }}" class="site-book-btn">
            {{ $service->button_text ?: 'Book / Enquire' }}
        </a>
    </div>
</section>
@endsection
