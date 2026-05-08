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
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paddle' => [
        'vendor_id'      => env('PADDLE_VENDOR_ID'),
        'api_key'        => env('PADDLE_API_KEY'),
        'public_key'     => env('PADDLE_PUBLIC_KEY'),
        'environment'    => env('PADDLE_ENVIRONMENT', 'sandbox'),
        'webhook_secret' => env('PADDLE_WEBHOOK_SECRET'),
    ],

    // Apple In-App Purchase
    'apple' => [
        // App Store Connect > Genel > Paylaşılan Sır
        'shared_secret' => env('APPLE_SHARED_SECRET'),

        // App Store Server Notifications için webhook'u doğrulamak amacıyla (opsiyonel)
        'bundle_id'     => env('APPLE_BUNDLE_ID'),
    ],

    // Google Play
    'google' => [
        // Google Cloud Console'dan indirilen service account JSON dosyasının
        // container içindeki tam yolu (örn: /var/www/html/storage/google-service-account.json)
        'service_account_json' => env('GOOGLE_SERVICE_ACCOUNT_JSON'),

        // Google Play Console'daki uygulama paket adı (örn: com.example.aifilm)
        'play_package_name'    => env('GOOGLE_PLAY_PACKAGE_NAME'),
    ],

];
