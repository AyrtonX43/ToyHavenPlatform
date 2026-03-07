<?php

return [
    'mode' => env('PAYPAL_MODE', 'sandbox'),
    'sandbox' => [
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
        'app_id' => 'APP-80W284485P519543T',
    ],
    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
        'app_id' => env('PAYPAL_LIVE_APP_ID', ''),
    ],
    'payment_action' => env('PAYPAL_PAYMENT_ACTION', 'Capture'),
    'currency' => env('PAYPAL_CURRENCY', 'PHP'),
    'notify_url' => env('PAYPAL_NOTIFY_URL', env('APP_URL') . '/webhooks/paypal'),
    'locale' => env('PAYPAL_LOCALE', 'en_US'),
    'validate_ssl' => env('PAYPAL_VALIDATE_SSL', env('APP_ENV') === 'production'),
];
