# Hotel HMS Full Mobile API

This patch fixes all current Flutter route errors by exposing the endpoints the
mobile app already calls.

## Endpoints

Public:

- POST `/api/login`

Authenticated with Laravel Sanctum:

- GET `/api/me`
- POST `/api/logout`
- GET `/api/dashboard`
- GET `/api/rooms`
- GET `/api/rooms/{id}`
- GET `/api/bookings`
- GET `/api/bookings/{id}`
- GET `/api/guests`
- GET `/api/guests/{id}`
- GET `/api/tasks`
- GET `/api/pos/orders`
- GET `/api/inventory/items`
- GET `/api/notifications`
- POST `/api/notifications/{id}/read`
- POST `/api/notifications/read-all`

## Important

The API controller intentionally supports common HMS table-name variations:
`reservations`/`bookings`, `guests`/`customers`,
`pos_orders`/`restaurant_orders`/`orders`, and
`inventory_items`/`stock_items`/`items`.

It also automatically scopes rows to the authenticated user's
`property_id`/`branch_id`/`hotel_id` where the table contains such a column.

## Installation

Extract over:

`/home/kemmytec/hotel.kemmytech.com`

Then run:

```bash
cd /home/kemmytec/hotel.kemmytech.com

php artisan optimize:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

php artisan route:list | grep "^.*api/"
```

After confirming routes, rebuild caches:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Verify specifically

```bash
php artisan route:list | grep -E "api/(login|me|dashboard|rooms|bookings|guests|tasks|pos/orders|inventory/items|notifications)"
```

## Check routing bootstrap

Laravel 13 `bootstrap/app.php` must contain:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

## Test Rooms with a token

First log in:

```bash
curl -s -X POST https://hotel.kemmytech.com/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@kemmytech.com","password":"YOUR_PASSWORD","device_name":"Hotel HMS Test"}'
```

Copy the returned token, then:

```bash
curl -s https://hotel.kemmytech.com/api/rooms \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

The response should now be JSON data rather than:
`The route api/rooms could not be found.`

## Flutter

No URL change is needed. The Flutter app already uses:

`https://hotel.kemmytech.com/api`
