<!doctype html>
<html lang="en">
<head>
    @php
        $systemName = \App\Support\SystemSetting::get(
            'system_name',
            config('app.name', 'Hotel Management System')
        );

        $primaryColor = \App\Support\SystemSetting::get(
            'primary_color',
            '#0f766e'
        );

        $secondaryColor = \App\Support\SystemSetting::get(
            'secondary_color',
            '#134e4a'
        );

        $accentColor = \App\Support\SystemSetting::get(
            'accent_color',
            '#14b8a6'
        );

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

    <title>Reset Password · {{ $systemName }}</title>

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
            --auth-primary: {{ $primaryColor }};
            --auth-secondary: {{ $secondaryColor }};
            --auth-accent: {{ $accentColor }};
            --auth-text: #17212b;
            --auth-muted: #6b7782;
            --auth-border: #d9e1e5;
            --auth-field: #f8fafb;
            --auth-error: #b42318;
        }

        * { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(
                    circle at top left,
                    color-mix(in srgb, var(--auth-accent) 16%, transparent),
                    transparent 34%
                ),
                linear-gradient(
                    135deg,
                    var(--auth-secondary),
                    var(--auth-primary)
                );
            color: var(--auth-text);
            font-family: 'Poppins', sans-serif;
        }

        .auth-shell {
            width: min(100%, 440px);
        }

        .auth-card {
            padding: 32px 30px 28px;
            border-radius: 20px;
            background: rgba(255,255,255,.98);
            box-shadow: 0 24px 60px rgba(15,23,42,.18);
        }

        .auth-brand {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            text-align: center;
        }

        .auth-brand img {
            max-width: 190px;
            max-height: 90px;
            object-fit: contain;
        }

        .auth-brand-fallback {
            width: 54px;
            height: 54px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: var(--auth-accent);
            color: var(--auth-secondary);
            font-family: 'Outfit', sans-serif;
            font-size: 23px;
            font-weight: 800;
        }

        .auth-heading {
            margin-bottom: 22px;
            text-align: center;
        }

        .auth-heading h1 {
            margin: 0 0 6px;
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
        }

        .auth-heading p {
            margin: 0;
            color: var(--auth-muted);
            font-size: 12px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid var(--auth-border);
            border-radius: 9px;
            outline: none;
            background: var(--auth-field);
            font-size: 13px;
        }

        .form-control:focus {
            border-color: var(--auth-primary);
            background: #fff;
            box-shadow: 0 0 0 3px color-mix(
                in srgb,
                var(--auth-primary) 13%,
                transparent
            );
        }

        .field-error {
            margin-top: 5px;
            color: var(--auth-error);
            font-size: 10px;
        }

        .auth-alert {
            margin-bottom: 14px;
            padding: 11px 12px;
            border-radius: 10px;
            font-size: 12px;
        }

        .auth-alert.success {
            border: 1px solid #cce6df;
            background: #eff9f6;
            color: #176b5f;
        }

        .auth-alert.error {
            border: 1px solid #f0c7c3;
            background: #fff2f1;
            color: var(--auth-error);
        }

        .auth-submit {
            width: 100%;
            min-height: 44px;
            border: 0;
            border-radius: 9px;
            background: var(--auth-primary);
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            cursor: pointer;
        }

        .auth-submit:hover {
            background: var(--auth-secondary);
        }

        .auth-footer {
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px solid #edf0f2;
            text-align: center;
        }

        .auth-footer a {
            color: var(--auth-primary);
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap .form-control {
            padding-right: 45px;
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

        @media (max-width: 520px) {
            body { padding: 14px; }

            .auth-card {
                padding: 26px 20px 22px;
            }
        }

    </style>
</head>

<body>
<main class="auth-shell">
    <section class="auth-card">
        <div class="auth-brand">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $systemName }}">
            @else
                <span class="auth-brand-fallback">
                    {{ strtoupper(substr($systemName, 0, 1)) }}
                </span>
            @endif
        </div>

        <div class="auth-heading">
            <h1>Reset password</h1>

            <p>
                Create a new secure password for your hotel system account.
            </p>
        </div>

        @if($errors->any())
            <div class="auth-alert error">
                <i class="fas fa-circle-exclamation"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('password.update') }}"
        >
            @csrf

            <input
                type="hidden"
                name="token"
                value="{{ $token }}"
            >

            <div class="form-group">
                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    class="form-control"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">New password</label>

                <div class="password-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        data-password-target="password"
                        aria-label="Show password"
                    >
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">
                    Confirm new password
                </label>

                <div class="password-wrap">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        data-password-target="password_confirmation"
                        aria-label="Show password"
                    >
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button
                type="submit"
                class="auth-submit"
            >
                Reset Password
            </button>
        </form>

        <div class="auth-footer">
            <a href="{{ route('login') }}">
                <i class="fas fa-arrow-left"></i>
                Back to sign in
            </a>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-password-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(
                button.dataset.passwordTarget
            );

            if (!input) return;

            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';

            const icon = button.querySelector('i');

            if (icon) {
                icon.className = showing
                    ? 'fas fa-eye'
                    : 'fas fa-eye-slash';
            }
        });
    });
});
</script>
<script src="{{ asset('js/form-placeholders.js') }}?v={{ @filemtime(public_path('js/form-placeholders.js')) ?: time() }}" defer></script>
</body>
</html>
