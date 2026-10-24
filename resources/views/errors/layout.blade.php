<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('code') · @yield('title') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/hms.css') }}?v={{ @filemtime(public_path('css/hms.css')) ?: time() }}">
</head>
<body>
<div class="error-page">
    <div class="error-card">
        <div class="error-icon"><i class="fas @yield('icon','fa-triangle-exclamation')"></i></div>
        <div class="error-code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="error-actions">
            <button class="btn" type="button" onclick="history.back()"><i class="fas fa-arrow-left"></i> Go Back</button>
            @auth
                <a class="btn primary" href="{{ route('dashboard') }}"><i class="fas fa-gauge-high"></i> Dashboard</a>
            @else
                <a class="btn primary" href="{{ route('login') }}"><i class="fas fa-right-to-bracket"></i> Sign In</a>
            @endauth
        </div>
    </div>
</div>
</body>
</html>
