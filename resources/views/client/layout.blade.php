<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Guest Portal') · {{ config('app.name', 'Hotel Management System') }}</title>
    <link rel="stylesheet" href="{{ asset('css/client-portal.css') }}?v={{ @filemtime(public_path('css/client-portal.css')) ?: time() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<header class="client-topbar">
    <a href="{{ route('client.dashboard') }}" class="client-brand">
        <i class="fas fa-hotel"></i><span>Guest Portal</span>
    </a>

    <nav>
        <a href="{{ route('client.dashboard') }}">Home</a>
        <a href="{{ route('client.services') }}">Services</a>
        <a href="{{ route('client.bookings') }}">My Bookings</a>
        <a href="{{ route('client.payments') }}">Payments</a>
        @if(\Illuminate\Support\Facades\Route::has('profile.edit'))
            <a href="{{ route('profile.edit') }}">Profile</a>
        @endif
    </nav>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="icon-button" type="submit" title="Sign out">
            <i class="fas fa-right-from-bracket"></i>
        </button>
    </form>
</header>

<main class="client-main">
    @yield('content')
</main>

<nav class="client-bottom-nav">
    <a href="{{ route('client.dashboard') }}"><i class="fas fa-house"></i><span>Home</span></a>
    <a href="{{ route('client.services') }}"><i class="fas fa-bell-concierge"></i><span>Services</span></a>
    <a href="{{ route('client.bookings') }}"><i class="fas fa-calendar-check"></i><span>Bookings</span></a>
    <a href="{{ route('client.payments') }}"><i class="fas fa-credit-card"></i><span>Payments</span></a>
</nav>
</body>
</html>
