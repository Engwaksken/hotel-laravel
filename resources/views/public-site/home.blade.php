<!doctype html>
<html lang="en">
<head>
    @php
        $brandingUrl = static function (?string $path): ?string {
            if (! $path) {
                return null;
            }

            $path = trim($path);

            if (
                str_starts_with($path, 'http://')
                || str_starts_with($path, 'https://')
            ) {
                return $path;
            }

            $path = ltrim($path, '/');

            if (str_starts_with($path, 'public/')) {
                $path = substr($path, 7);
            }

            if (str_starts_with($path, 'storage/')) {
                $path = substr($path, 8);
            }

            return asset('storage/'.$path);
        };

        $logoUrl = \App\Support\BrandingAsset::url($settings['logo_path'] ?? null);
        $faviconPath = $settings['favicon_path'] ?? null;
        $faviconUrl = \App\Support\BrandingAsset::url($faviconPath);

        $routeExists = static fn (string $name): bool =>
            \Illuminate\Support\Facades\Route::has($name);
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $settings['system_name'] }}</title>

    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}" type="{{ \App\Support\BrandingAsset::mimeType($faviconPath) }}" sizes="any">
    @endif

    <style>
        :root {
            --site-primary: {{ $settings['primary_color'] }};
            --site-secondary: {{ $settings['secondary_color'] }};
            --site-accent: {{ $settings['accent_color'] }};
        }
    </style>

    <link
        rel="stylesheet"
        href="{{ asset('css/hotel-public.css') }}?v={{ @filemtime(public_path('css/hotel-public.css')) ?: time() }}"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >
</head>

<body>

<header class="site-header">
    <a
        href="{{ route('client.landing') }}"
        class="site-brand"
        aria-label="{{ $settings['system_name'] }}"
    >
        @if($logoUrl)
            <img
                src="{{ $logoUrl }}"
                alt="{{ $settings['system_name'] }}"
            >
        @else
            <span class="site-logo-icon">
                <i class="fas fa-hotel"></i>
            </span>
        @endif

        <strong>{{ $settings['system_name'] }}</strong>
    </a>

    <nav aria-label="Public navigation">
        <a href="{{ route('client.landing') }}">
            Home
        </a>

        @if($routeExists('public.hotel'))
            <a href="{{ route('public.hotel') }}">
                Accommodation
            </a>
        @endif

        @if($routeExists('public.services'))
            <a href="{{ route('public.services') }}">
                Services
            </a>
        @endif

        <a href="#offers">
            Offers
        </a>

        <a href="#contact">
            Contact
        </a>
    </nav>

    <div class="site-header-actions">
        @if($routeExists('public.hotel'))
            <a
                href="{{ route('public.hotel') }}"
                class="site-book-btn"
            >
                Book Now
            </a>
        @endif

        @if($routeExists('login'))
            <a
                href="{{ route('login') }}"
                class="site-staff-login"
            >
                Staff / Admin Login
            </a>
        @endif
    </div>
</header>

@if(session('success'))
    <div
        style="
            width:min(1200px,calc(100% - 30px));
            margin:14px auto 0;
            padding:12px 14px;
            border:1px solid #cce6df;
            border-radius:10px;
            background:#eff9f6;
            color:#176b5f;
        "
    >
        <i class="fas fa-circle-check"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div
        style="
            width:min(1200px,calc(100% - 30px));
            margin:14px auto 0;
            padding:12px 14px;
            border:1px solid #f0c7c3;
            border-radius:10px;
            background:#fff2f1;
            color:#b42318;
        "
    >
        <i class="fas fa-circle-exclamation"></i>
        {{ $errors->first() }}
    </div>
@endif

<section
    class="hero-slider"
    id="heroSlider"
>
    @forelse($banners as $index => $banner)
        @php
            $bannerImageUrl = $brandingUrl(
                $banner->image_path ?? null
            );
        @endphp

        <article
            class="hero-slide {{ $index === 0 ? 'active' : '' }}"
            @if($bannerImageUrl)
                style="
                    background-image:
                        linear-gradient(
                            90deg,
                            rgba(9,28,25,.76),
                            rgba(9,28,25,.22)
                        ),
                        url('{{ $bannerImageUrl }}');
                "
            @endif
        >
            <div class="hero-content">
                <span class="hero-kicker">
                    Welcome to
                </span>

                <h1>
                    {{ $banner->title }}
                </h1>

                @if($banner->subtitle)
                    <p>
                        {{ $banner->subtitle }}
                    </p>
                @endif

                <div class="hero-actions">
                    @if($banner->button_url)
                        <a
                            href="{{ $banner->button_url }}"
                            class="hero-primary"
                        >
                            {{ $banner->button_text ?: 'Book Your Stay' }}
                        </a>
                    @elseif($routeExists('public.hotel'))
                        <a
                            href="{{ route('public.hotel') }}"
                            class="hero-primary"
                        >
                            {{ $banner->button_text ?: 'Book Your Stay' }}
                        </a>
                    @endif

                    @if($routeExists('public.services'))
                        <a
                            href="{{ route('public.services') }}"
                            class="hero-secondary"
                        >
                            Explore Services
                        </a>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <article class="hero-slide active fallback-hero">
            <div class="hero-content">
                <span class="hero-kicker">
                    Welcome to
                </span>

                <h1>
                    {{ $property->name ?? $settings['system_name'] }}
                </h1>

                <p>
                    Exceptional hospitality, comfortable accommodation
                    and memorable guest experiences.
                </p>

                <div class="hero-actions">
                    @if($routeExists('public.hotel'))
                        <a
                            href="{{ route('public.hotel') }}"
                            class="hero-primary"
                        >
                            Book Your Stay
                        </a>
                    @endif

                    @if($routeExists('public.services'))
                        <a
                            href="{{ route('public.services') }}"
                            class="hero-secondary"
                        >
                            Explore Services
                        </a>
                    @endif
                </div>
            </div>
        </article>
    @endforelse

    @if($banners->count() > 1)
        <div class="hero-dots">
            @foreach($banners as $index => $banner)
                <button
                    type="button"
                    class="{{ $index === 0 ? 'active' : '' }}"
                    data-slide="{{ $index }}"
                    aria-label="Show banner {{ $index + 1 }}"
                ></button>
            @endforeach
        </div>
    @endif
</section>

<section class="booking-strip">
    <div>
        <small>
            Plan your stay
        </small>

        <strong>
            Ready for a comfortable hotel experience?
        </strong>
    </div>

    @if($routeExists('public.hotel'))
        <a href="{{ route('public.hotel') }}">
            Check Rooms &amp; Book
        </a>
    @endif
</section>

<section class="public-section intro-section">
    <div class="section-heading centred">
        <span>
            Hospitality
        </span>

        <h2>
            Welcome to
            {{ $property->name ?? $settings['system_name'] }}
        </h2>

        <p>
            Discover accommodation, dining, wellness, events and
            personalised guest services from one place.
        </p>
    </div>
</section>

<section
    class="public-section"
    id="services"
>
    <div class="section-heading">
        <div>
            <span>
                Discover
            </span>

            <h2>
                Our services
            </h2>

            <p>
                Thoughtfully selected experiences and amenities for
                hotel guests.
            </p>
        </div>

        @if($routeExists('public.services'))
            <a href="{{ route('public.services') }}">
                View all services
            </a>
        @endif
    </div>

    <div class="service-image-grid">
        @forelse($services as $service)
            @php
                $serviceImageUrl = $brandingUrl(
                    $service->image_path ?? null
                );
            @endphp

            @if(
                $routeExists('public.services.show')
                && ! empty($service->slug)
            )
                <a
                    href="{{ route('public.services.show', $service->slug) }}"
                    class="service-image-card"
                >
            @else
                <article class="service-image-card">
            @endif

                @if($serviceImageUrl)
                    <img
                        src="{{ $serviceImageUrl }}"
                        alt="{{ $service->name }}"
                        loading="lazy"
                    >
                @else
                    <div class="service-placeholder">
                        <i class="fas fa-bell-concierge"></i>
                    </div>
                @endif

                <div class="service-image-overlay">
                    <span>
                        Experience
                    </span>

                    <h3>
                        {{ $service->name }}
                    </h3>

                    @if($service->short_description)
                        <p>
                            {{ $service->short_description }}
                        </p>
                    @endif

                    @if($service->price_label)
                        <strong>
                            {{ $service->price_label }}
                        </strong>
                    @elseif($service->price)
                        <strong>
                            From UGX
                            {{ number_format((float) $service->price) }}
                        </strong>
                    @endif
                </div>

            @if(
                $routeExists('public.services.show')
                && ! empty($service->slug)
            )
                </a>
            @else
                </article>
            @endif
        @empty
            <div class="public-empty">
                Hotel services will appear here after they are
                published by the administrator.
            </div>
        @endforelse
    </div>
</section>

<section
    class="public-feature-banner"
    id="offers"
>
    <div>
        <span>
            Stay • Dine • Relax
        </span>

        <h2>
            Make every stay more memorable.
        </h2>

        <p>
            Explore rooms, guest services and special hotel experiences.
        </p>
    </div>

    @if($routeExists('public.hotel'))
        <a href="{{ route('public.hotel') }}">
            Make a Reservation
        </a>
    @endif
</section>

{{-- ============================================================
     GUEST COMMENTS / REVIEWS
     ============================================================ --}}
@if(
    \Illuminate\Support\Facades\View::exists(
        'components.guest-comments'
    )
)
    <x-guest-comments />
@endif

<footer
    class="public-footer"
    id="contact"
>
    <div>
        <strong>
            {{ $settings['system_name'] }}
        </strong>

        @if($settings['company_address'])
            <p>
                {{ $settings['company_address'] }}
            </p>
        @endif
    </div>

    <div>
        <strong>
            Contact
        </strong>

        @if($settings['support_phone'])
            <p>
                {{ $settings['support_phone'] }}
            </p>
        @endif

        @if($settings['support_email'])
            <p>
                {{ $settings['support_email'] }}
            </p>
        @endif
    </div>

    <div>
        <strong>
            Quick Links
        </strong>

        @if($routeExists('public.services'))
            <a href="{{ route('public.services') }}">
                Services
            </a>
        @endif

        @if($routeExists('public.hotel'))
            <a href="{{ route('public.hotel') }}">
                Book a Stay
            </a>
        @endif

        @if($routeExists('login'))
            <a href="{{ route('login') }}">
                Staff / Admin Login
            </a>
        @endif
    </div>
</footer>

{{-- ============================================================
     ACCESSIBILITY + CHATBOT FLOATING WIDGETS
     ============================================================ --}}
@if(
    \Illuminate\Support\Facades\View::exists(
        'components.hotel-guest-tools'
    )
)
    <x-hotel-guest-tools />
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const slides = Array.from(
        document.querySelectorAll('.hero-slide')
    );

    const dots = Array.from(
        document.querySelectorAll('[data-slide]')
    );

    if (slides.length <= 1) {
        return;
    }

    let active = 0;
    let timer = null;

    function show(index) {
        active = index;

        slides.forEach(function (slide, i) {
            slide.classList.toggle(
                'active',
                i === active
            );
        });

        dots.forEach(function (dot, i) {
            dot.classList.toggle(
                'active',
                i === active
            );
        });
    }

    function startSlider() {
        if (timer) {
            window.clearInterval(timer);
        }

        timer = window.setInterval(function () {
            show(
                (active + 1) % slides.length
            );
        }, 6500);
    }

    dots.forEach(function (dot) {
        dot.addEventListener(
            'click',
            function () {
                show(
                    Number(dot.dataset.slide)
                );

                startSlider();
            }
        );
    });

    startSlider();
});
</script>

<script src="{{ asset('js/form-placeholders.js') }}?v={{ @filemtime(public_path('js/form-placeholders.js')) ?: time() }}" defer></script>
</body>
</html>
