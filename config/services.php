<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL', 'http://localhost:8000') . '/auth/google/callback'),
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
        // Disable SSL verification for local/XAMPP to avoid cURL error 60
        'guzzle' => env('APP_ENV') === 'local' ? ['verify' => false] : [],
    ],

    'paymongo' => [
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'public_key' => env('PAYMONGO_PUBLIC_KEY'),
        'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1'),
    ],

    'amazon' => [
        'access_key' => env('AMAZON_ACCESS_KEY'),
        'secret_key' => env('AMAZON_SECRET_KEY'),
        'partner_tag' => env('AMAZON_PARTNER_TAG'),
        'host' => env('AMAZON_HOST', 'webservices.amazon.com'),
        'region' => env('AMAZON_REGION', 'us-east-1'),
        'marketplace' => env('AMAZON_MARKETPLACE', 'www.amazon.com'),
    ],

    'product_api' => [
        'provider' => env('PRODUCT_API_PROVIDER', 'scraperapi'), // 'amazon', 'canopy', 'scraperapi'

        // ScraperAPI (Amazon structured data - no Associates account needed)
        'scraperapi' => [
            'api_key' => env('SCRAPER_API_KEY'),
            'base_url' => env('SCRAPER_API_BASE_URL', 'https://api.scraperapi.com'),
        ],

        // Canopy API (Alternative - No Associates account needed)
        'canopy' => [
            'api_key' => env('CANOPY_API_KEY'),
            'rest_url' => env('CANOPY_REST_URL', 'https://rest.canopyapi.co'),
            'graphql_url' => env('CANOPY_GRAPHQL_URL', 'https://graphql.canopyapi.co'),
        ],
    ],

    'sms' => [
        'provider' => env('SMS_PROVIDER', 'log'), // 'log', 'twilio', 'nexmo'
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'phone_number' => env('TWILIO_PHONE_NUMBER'),
    ],

    'nexmo' => [
        'api_key' => env('NEXMO_API_KEY'),
        'api_secret' => env('NEXMO_API_SECRET'),
        'from_number' => env('NEXMO_FROM_NUMBER'),
    ],

];
