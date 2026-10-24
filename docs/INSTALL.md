# HMS Laravel Admin + Finance + QR Enhancement

This package adds:

1. Super Admin system branding:
   - system name / short name
   - primary / secondary / accent colours
   - logo
   - favicon
   - support email / phone
   - invoice / receipt prefixes and footers
   - tabbed configuration page

2. Permissions:
   - multiple permission selection
   - Select All / Clear All
   - bulk sync by role

3. Sidebar:
   - grouped dropdown navigation using `<details>`
   - prevents very long scrolling menus
   - Font Awesome icons supported

4. Bulk CSV tools:
   - Rooms template / export / upload
   - Guests template / export / upload
   - Inventory template / export / upload

5. Documents / payments:
   - browser invoice preview
   - DOMPDF invoice preview
   - browser print
   - multiple receipts per invoice
   - instalment/partial payment tracking
   - receipt PDF

6. Guest QR:
   - unique guest QR token
   - printable QR
   - signed verification route
   - QR scan opens guest verification page

## Required packages

DOMPDF:
```bash
composer require barryvdh/laravel-dompdf
```

Guest QR:
```bash
composer require endroid/qr-code
```

## Install

Extract into:
`/home/kemmytec/hotel.kemmytech.com`

Require routes at the bottom of `routes/web.php`:

```php
require __DIR__.'/hms_enhancements.php';
```

Run:
```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan route:cache
php artisan config:cache
php artisan view:cache
```

Include CSS after your existing HMS stylesheet:
```blade
<link rel="stylesheet" href="{{ asset('css/hms-enhancements.css') }}?v={{ filemtime(public_path('css/hms-enhancements.css')) }}">
```

To replace the current long sidebar, include:
```blade
<x-sidebar-grouped />
```

## Applying configured branding globally

In your main layout:
```blade
@php
    $brandPrimary = \App\Support\SystemSetting::get('primary_color', '#0f766e');
    $brandLogo = \App\Support\SystemSetting::get('logo_path');
    $systemName = \App\Support\SystemSetting::get('system_name', config('app.name'));
@endphp

<style>
:root { --hms-primary: {{ $brandPrimary }}; }
</style>
```

Logo:
```blade
@if($brandLogo)
    <img src="{{ asset('storage/'.$brandLogo) }}" alt="{{ $systemName }}">
@endif
```

Favicon:
```blade
@php($favicon = \App\Support\SystemSetting::get('favicon_path'))
@if($favicon)
<link rel="icon" href="{{ asset('storage/'.$favicon) }}">
@endif
```

## Important

This patch intentionally does not hard-delete completed/paid POS and linked accounting history.
Invoices can have multiple receipts for instalment payments.

CSV imports should be reviewed for your exact live column rules before importing production data.
