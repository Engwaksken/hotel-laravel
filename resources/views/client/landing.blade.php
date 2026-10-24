<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>{{ config('app.name', 'Hotel Management System') }}</title>

    <link
        rel="stylesheet"
        href="{{ asset('css/client-portal.css') }}?v={{ @filemtime(public_path('css/client-portal.css')) ?: time() }}"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >
</head>

<body class="client-landing-body">

<header class="landing-nav">
    <a href="{{ route('client.landing') }}" class="client-brand">
        <i class="fas fa-hotel"></i>

        <span>
            {{ \App\Support\SystemSetting::get(
                'system_name',
                config('app.name', 'Hotel Management System')
            ) }}
        </span>
    </a>

    <nav>
        <a href="#services">Services</a>
        <a href="{{ route('public.hotel') }}">Rooms &amp; Booking</a>

        @auth
            <a href="{{ route('client.dashboard') }}">
                My Account
            </a>
        @endauth
    </nav>

    <div class="landing-actions">
        @auth
            <a
                href="{{ route('client.dashboard') }}"
                class="client-primary-btn"
            >
                <i class="fas fa-user"></i>
                My Portal
            </a>
        @else
            <a
                href="{{ route('login') }}"
                class="staff-login-link"
            >
                <i class="fas fa-lock"></i>
                Staff / Admin Login
            </a>
        @endauth
    </div>
</header>

<main>
    <section class="landing-hero">
        <div class="landing-hero-copy">
            <span class="landing-eyebrow">
                Welcome
            </span>

            <h1>
                Stay, book and enjoy hotel services with ease.
            </h1>

            <p>
                Browse rooms and services, make a booking and keep your
                stay information in one convenient place.
            </p>

            <div class="landing-hero-actions">
                <a
                    href="{{ route('public.hotel') }}"
                    class="client-primary-btn"
                >
                    <i class="fas fa-calendar-check"></i>
                    Book a Stay
                </a>

                <a
                    href="#services"
                    class="client-secondary-btn"
                >
                    <i class="fas fa-bell-concierge"></i>
                    View Services
                </a>
            </div>
        </div>

        <div class="landing-feature-panel">
            <div>
                <i class="fas fa-bed"></i>
                <strong>Room Booking</strong>
                <small>Check rooms and make reservations.</small>
            </div>

            <div>
                <i class="fas fa-bell-concierge"></i>
                <strong>Hotel Services</strong>
                <small>Discover services available during your stay.</small>
            </div>

            <div>
                <i class="fas fa-credit-card"></i>
                <strong>Payments</strong>
                <small>Keep track of your payments from your client portal.</small>
            </div>

            <div>
                <i class="fas fa-receipt"></i>
                <strong>Receipts</strong>
                <small>Review your payment records where available.</small>
            </div>
        </div>
    </section>

    <section
        class="landing-section"
        id="services"
    >
        <div class="landing-section-head">
            <span>Guest Experience</span>

            <h2>Everything you need for your stay</h2>

            <p>
                Use the public website to explore rooms and services.
                Sign in to your client portal when you need personal
                booking and payment information.
            </p>
        </div>

        <div class="landing-service-grid">
            <a
                href="{{ route('public.hotel') }}"
                class="landing-service-card"
            >
                <i class="fas fa-hotel"></i>

                <h3>Rooms &amp; Booking</h3>

                <p>
                    Browse hotel information and start a room booking.
                </p>
            </a>

            @auth
                <a
                    href="{{ route('client.services') }}"
                    class="landing-service-card"
                >
                    <i class="fas fa-bell-concierge"></i>

                    <h3>Hotel Services</h3>

                    <p>
                        Explore guest services available in your account.
                    </p>
                </a>

                <a
                    href="{{ route('client.bookings') }}"
                    class="landing-service-card"
                >
                    <i class="fas fa-calendar-check"></i>

                    <h3>My Bookings</h3>

                    <p>
                        Review upcoming and previous reservations.
                    </p>
                </a>

                <a
                    href="{{ route('client.payments') }}"
                    class="landing-service-card"
                >
                    <i class="fas fa-credit-card"></i>

                    <h3>Payments</h3>

                    <p>
                        Review your recorded hotel payments.
                    </p>
                </a>
            @else
                <div class="landing-service-card">
                    <i class="fas fa-bell-concierge"></i>

                    <h3>Hotel Services</h3>

                    <p>
                        Discover services offered by the hotel.
                    </p>
                </div>

                <div class="landing-service-card">
                    <i class="fas fa-calendar-check"></i>

                    <h3>Booking Management</h3>

                    <p>
                        Sign in to view bookings linked to your client account.
                    </p>
                </div>

                <div class="landing-service-card">
                    <i class="fas fa-credit-card"></i>

                    <h3>Payment History</h3>

                    <p>
                        Securely review payments from your client account.
                    </p>
                </div>
            @endauth
        </div>
    </section>
</main>

<footer class="landing-footer">
    <span>
        &copy; {{ now()->year }}
        {{ \App\Support\SystemSetting::get(
            'system_name',
            config('app.name', 'Hotel Management System')
        ) }}
    </span>

    <a href="{{ route('login') }}">
        Staff / Admin Login
    </a>
</footer>

</body>
</html>
