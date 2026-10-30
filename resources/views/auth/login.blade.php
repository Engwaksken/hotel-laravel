<!doctype html>
<html lang="en">
<head>
    @php
        $systemName = \App\Support\SystemSetting::get(
            'system_name',
            config('app.name', 'Hotel Management System')
        );

        $primaryColor = \App\Support\SystemSetting::get('primary_color', '#0f766e');
        $secondaryColor = \App\Support\SystemSetting::get('secondary_color', '#134e4a');
        $accentColor = \App\Support\SystemSetting::get('accent_color', '#14b8a6');

        $logoPath = \App\Support\SystemSetting::get('logo_path');
        $faviconPath = \App\Support\SystemSetting::get('favicon_path');

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

        $logoUrl = \App\Support\BrandingAsset::url($logoPath);
        $faviconUrl = \App\Support\BrandingAsset::url($faviconPath);
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign In � {{ $systemName }}</title>

    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}" type="{{ \App\Support\BrandingAsset::mimeType($faviconPath) }}" sizes="any">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>
        :root {
            --login-primary: {{ $primaryColor }};
            --login-secondary: {{ $secondaryColor }};
            --login-accent: {{ $accentColor }};
            --login-text: #17212b;
            --login-muted: #6b7782;
            --login-border: #d9e1e5;
            --login-field: #f8fafb;
            --login-error: #b42318;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(
                    circle at top left,
                    color-mix(in srgb, var(--login-accent) 16%, transparent),
                    transparent 34%
                ),
                linear-gradient(
                    135deg,
                    var(--login-secondary),
                    var(--login-primary)
                );
            color: var(--login-text);
            font-family: 'Poppins', sans-serif;
        }

        button,
        input {
            font: inherit;
        }

        .login-shell {
            width: min(100%, 440px);
        }

        .login-card {
            width: 100%;
            padding: 32px 30px 28px;
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 20px;
            background: rgba(255,255,255,.98);
            box-shadow:
                0 24px 60px rgba(15,23,42,.18),
                0 4px 14px rgba(15,23,42,.08);
        }

        .login-brand {
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .login-brand-logo {
            display: block;
            width: auto;
            max-width: 200px;
            height: auto;
            max-height: 95px;
            object-fit: contain;
        }

        .login-brand-fallback {
            width: 54px;
            height: 54px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: var(--login-accent);
            color: var(--login-secondary);
            font-family: 'Outfit', sans-serif;
            font-size: 23px;
            font-weight: 800;
        }

        .login-heading {
            margin-bottom: 24px;
            text-align: center;
        }

        .login-heading h1 {
            margin: 0 0 6px;
            color: var(--login-text);
            font-family: 'Outfit', sans-serif;
            font-size: 29px;
            line-height: 1.15;
            font-weight: 800;
        }

        .login-heading p {
            margin: 0;
            color: var(--login-muted);
            font-size: 12px;
            line-height: 1.6;
        }

        .alert {
            margin-bottom: 16px;
            padding: 11px 12px;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            border-radius: 10px;
            font-size: 12px;
            line-height: 1.5;
        }

        .alert.error {
            border: 1px solid #f0c7c3;
            background: #fff2f1;
            color: var(--login-error);
        }

        .alert.success {
            border: 1px solid #cce6df;
            background: #eff9f6;
            color: #176b5f;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #35414b;
            font-size: 11px;
            font-weight: 600;
        }

        .input-wrap {
            position: relative;
        }

        .form-control {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid var(--login-border);
            border-radius: 9px;
            outline: none;
            background: var(--login-field);
            color: var(--login-text);
            font-size: 13px;
            transition:
                border-color .2s ease,
                box-shadow .2s ease,
                background .2s ease;
        }

        .form-control:focus {
            border-color: var(--login-primary);
            background: #fff;
            box-shadow:
                0 0 0 3px
                color-mix(
                    in srgb,
                    var(--login-primary) 13%,
                    transparent
                );
        }

        .form-control.is-invalid {
            border-color: #d92d20;
        }

        .password-input {
            padding-right: 46px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            width: 34px;
            height: 34px;
            transform: translateY(-50%);
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #75818a;
            cursor: pointer;
        }

        .password-toggle:hover {
            background: #eef3f4;
            color: var(--login-primary);
        }

        .field-error {
            margin-top: 5px;
            color: var(--login-error);
            font-size: 10px;
        }

        .login-options {
            margin: 2px 0 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .remember-wrap {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #53616a;
            font-size: 11px;
            cursor: pointer;
        }

        .remember-wrap input {
            width: 15px;
            height: 15px;
            margin: 0;
            accent-color: var(--login-primary);
        }

        .forgot-link {
            color: var(--login-primary);
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .sign-in-btn {
            width: 100%;
            min-height: 44px;
            border: 0;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--login-primary);
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition:
                transform .15s ease,
                background .2s ease;
        }

        .sign-in-btn:hover {
            background: var(--login-secondary);
        }

        .sign-in-btn:active {
            transform: translateY(1px);
        }

        .login-footer {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #edf0f2;
            text-align: center;
        }

        .login-footer small {
            display: block;
            color: #87929a;
            font-size: 10px;
            line-height: 1.5;
        }

        .login-footer a {
            display: inline-block;
            margin-top: 7px;
            color: var(--login-primary);
            font-size: 10px;
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width: 520px) {
            body {
                padding: 14px;
            }

            .login-card {
                padding: 26px 20px 22px;
                border-radius: 17px;
            }

            .login-brand-logo {
                max-width: 160px;
                max-height: 78px;
            }

            .login-heading h1 {
                font-size: 26px;
            }

            .login-options {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
<main class="login-shell">
    <section class="login-card">

        <div class="login-brand">
            @if($logoUrl)
                <img
                    src="{{ $logoUrl }}"
                    alt="{{ $systemName }}"
                    class="login-brand-logo"
                >
            @else
                <span class="login-brand-fallback">
                    {{ strtoupper(substr($systemName, 0, 1)) }}
                </span>
            @endif
        </div>

        <div class="login-heading">
            <h1>Welcome back</h1>

            <p>
                Sign in to manage front office operations.
            </p>
        </div>

        @if(session('status'))
            <div class="alert success">
                <i class="fas fa-circle-check"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert error">
                <i class="fas fa-circle-exclamation"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert error">
                <i class="fas fa-circle-exclamation"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('login.store') }}"
            autocomplete="on"
        >
            @csrf

            <div class="form-group">
                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    autocomplete="email"
                    inputmode="email"
                    required
                    autofocus
                >

                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control password-input @error('password') is-invalid @enderror"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        id="passwordToggle"
                        aria-label="Show password"
                        title="Show password"
                    >
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="login-options">
                <label class="remember-wrap">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        {{ old('remember') ? 'checked' : '' }}
                    >

                    <span>Keep me signed in</span>
                </label>

                @if(\Illuminate\Support\Facades\Route::has('password.request'))
                    <a
                        href="{{ route('password.request') }}"
                        class="forgot-link"
                    >
                        Forgot password?
                    </a>
                @endif
            </div>

            <button
                type="submit"
                class="sign-in-btn"
            >
                <i class="fas fa-right-to-bracket"></i>
                <span>Sign in</span>
            </button>
        </form>

        <div class="login-footer">
            <small>
                Authorised hotel staff and administrators only.
            </small>

            @if(\Illuminate\Support\Facades\Route::has('client.landing'))
                <a href="{{ route('client.landing') }}">
                    <i class="fas fa-arrow-left"></i>
                    Back to hotel website
                </a>
            @endif
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const toggle = document.getElementById('passwordToggle');

    if (!passwordInput || !toggle) {
        return;
    }

    toggle.addEventListener('click', function () {
        const showing = passwordInput.type === 'text';

        passwordInput.type = showing ? 'password' : 'text';

        toggle.setAttribute(
            'aria-label',
            showing ? 'Show password' : 'Hide password'
        );

        toggle.setAttribute(
            'title',
            showing ? 'Show password' : 'Hide password'
        );

        const icon = toggle.querySelector('i');

        if (icon) {
            icon.className = showing
                ? 'fas fa-eye'
                : 'fas fa-eye-slash';
        }
    });
});
</script>
</body>
</html>
