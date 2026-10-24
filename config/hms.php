<?php

return [
    'currency' => env('HMS_CURRENCY', 'UGX'),
    'timezone' => env('HMS_TIMEZONE', 'Africa/Kampala'),
    'business_date_cutoff' => env('HMS_BUSINESS_DATE_CUTOFF', '03:00'),
    'reservation_prefix' => env('HMS_RESERVATION_PREFIX', 'RES'),
    'folio_prefix' => env('HMS_FOLIO_PREFIX', 'FOL'),
    'loyalty_spend_per_point' => (float) env('HMS_LOYALTY_SPEND_PER_POINT', 10000),
];
