# New User Login Email Update

## Behaviour

Whenever an administrator creates a new user, the user receives an email containing:

- Hotel Management System name
- Login email
- Temporary password
- Assigned role
- Assigned branch/property
- Login button/link

The user is forced to replace the temporary password after first login.

The temporary password is never stored in plaintext in the database.

## Required routes

Add to `routes/web.php`:

```php
use App\Http\Controllers\Auth\FirstLoginPasswordController;

Route::middleware('auth')->group(function () {
    Route::get('/first-login/change-password', [FirstLoginPasswordController::class, 'edit'])
        ->name('password.first-login.edit');

    Route::put('/first-login/change-password', [FirstLoginPasswordController::class, 'update'])
        ->name('password.first-login.update');
});
```

## Protect authenticated application routes

Add `\App\Http\Middleware\RequirePasswordChange::class` after the `auth` middleware on the main authenticated route group.

Example:

```php
Route::middleware([
    'auth',
    \App\Http\Middleware\RequirePasswordChange::class,
])->group(function () {
    // Hotel application routes...
});
```

Do not put this middleware on login/logout routes.

## Update AdministrationController user creation

Instead of directly calling `User::create()` with a hashed password, inject:

```php
use App\Services\Auth\UserAccountProvisioningService;
```

Example:

```php
public function saveUser(
    Request $request,
    UserAccountProvisioningService $provisioning
) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'role' => ['required', 'string'],
        'property_id' => ['nullable', 'integer', 'exists:properties,id'],
        'is_active' => ['nullable', 'boolean'],
    ]);

    // Branch managers must remain on the logged-in manager's property.
    if ($request->user()->role === 'branch_manager') {
        $validated['property_id'] = $request->user()->property_id;
    }

    $validated['is_active'] = $request->boolean('is_active', true);

    $user = $provisioning->create($validated);

    return back()->with(
        'success',
        'User created successfully. Login details have been emailed to '.$user->email.'.'
    );
}
```

Do not display the generated password in the success message.

## Optional: resend login details

A Super Admin/Admin/Hotel Owner/General Manager/Branch Manager may resend a new temporary password to a user they are authorised to manage:

```php
$provisioning->resend($user);
```

This resets the password and `must_change_password` back to true.

## Mail configuration

The mailer must be SMTP (or another real transport), not `log`.

Example:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.kemmytech.com
MAIL_PORT=587
MAIL_USERNAME=hotel@kemmytech.com
MAIL_PASSWORD="..."
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hotel@kemmytech.com"
MAIL_FROM_NAME="${APP_NAME}"
```

After editing `.env`:

```bash
php artisan optimize:clear
php artisan config:cache
```

## Queue

The notification implements `ShouldQueue` and uses the `notifications` queue. Your existing cPanel queue cron already includes this queue.
