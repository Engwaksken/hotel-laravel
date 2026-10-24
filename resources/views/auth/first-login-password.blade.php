<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change temporary password | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/hms.css') }}?v={{ @filemtime(public_path('css/hms.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="login-body">
    <main class="login-card">
        <div class="login-brand">
            <h1>Set your password</h1>
            <p>Your account is using a temporary password. Create a private password before continuing.</p>
        </div>

        @if ($errors->any())
            <div class="alert error auto-dismiss-alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.first-login.update') }}" class="stack">
            @csrf
            @method('PUT')

            <label>
                New password
                <div class="password-field">
                    <input id="password" type="password" name="password" required autocomplete="new-password">
                    <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </label>

            <label>
                Confirm new password
                <div class="password-field">
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                    <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-label="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </label>

            <button class="btn primary full" type="submit">
                <i class="fas fa-key"></i>
                Save password
            </button>
        </form>
    </main>

    <script src="{{ asset('js/hms.js') }}?v={{ @filemtime(public_path('js/hms.js')) }}"></script>
</body>
</html>
