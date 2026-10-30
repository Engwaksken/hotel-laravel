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

    <title>
        @yield('title', 'Hotel') · {{ $settings['system_name'] }}
    </title>

    @if($faviconUrl)
        <link
            rel="icon"
            href="{{ $faviconUrl }}"
            type="{{ \App\Support\BrandingAsset::mimeType($faviconPath) }}"
            sizes="any"
        >
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

    <style>
        .public-footer {
            align-items: start;
        }

        .public-footer > div {
            min-width: 0;
        }

        .public-footer > div > strong {
            display: block;
            margin-bottom: 12px;
            color: #fff;
        }

        .public-footer p,
        .public-footer a {
            overflow-wrap: anywhere;
            line-height: 1.6;
        }

        .public-footer a:hover {
            color: #fff;
        }

        @media (max-width: 650px) {
            .public-footer {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    @stack('styles')
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

        <strong>
            {{ $settings['system_name'] }}
        </strong>
    </a>

    <nav aria-label="Public navigation">
        @if($routeExists('client.landing'))
            <a
                href="{{ route('client.landing') }}"
                class="{{ request()->routeIs('client.landing') ? 'active' : '' }}"
            >
                Home
            </a>
        @endif

        @if($routeExists('public.services'))
            <a
                href="{{ route('public.services') }}"
                class="{{ request()->routeIs('public.services*') ? 'active' : '' }}"
            >
                Services
            </a>
        @endif

        @if($routeExists('public.hotel'))
            <a
                href="{{ route('public.hotel') }}"
                class="{{ request()->routeIs('public.hotel') ? 'active' : '' }}"
            >
                Accommodation
            </a>
        @endif

        @if($routeExists('client.landing'))
            <a href="{{ route('client.landing') }}#offers">
                Offers
            </a>

            <a href="{{ route('client.landing') }}#contact">
                Contact
            </a>
        @endif
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

@if(session('error'))
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
        <i class="fas fa-circle-xmark"></i>
        {{ session('error') }}
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

<main>
    @yield('content')
</main>

<footer
    class="public-footer"
    id="contact"
>
    <div>
        <strong>
            {{ $settings['system_name'] }}
        </strong>

        @if($settings['company_address'] ?? null)
            <p>
                {{ $settings['company_address'] }}
            </p>
        @endif
    </div>

    <div>
        <strong>
            Contact
        </strong>

        @if($settings['support_phone'] ?? null)
            <p>
                {{ $settings['support_phone'] }}
            </p>
        @endif

        @if($settings['support_email'] ?? null)
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

<script src="{{ asset('js/form-placeholders.js') }}?v={{ @filemtime(public_path('js/form-placeholders.js')) ?: time() }}" defer></script>
@stack('scripts')

</body>
</html>
