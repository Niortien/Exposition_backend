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

    'cinetpay' => [
        'api_key' => env('CINETPAY_API_KEY'),
        'site_id' => env('CINETPAY_SITE_ID'),
        'secret_key' => env('CINETPAY_SECRET_KEY'),
        'base_url' => env('CINETPAY_BASE_URL', 'https://api-checkout.cinetpay.com/v2'),
        'notify_url' => env('CINETPAY_NOTIFY_URL'),
        'return_url' => env('CINETPAY_RETURN_URL'),
    ],

    'otp_sms' => [
        'driver' => env('OTP_SMS_DRIVER', 'log'),
        'api_key' => env('OTP_SMS_API_KEY'),
        'sender_id' => env('OTP_SMS_SENDER_ID', 'TICKETRAMA'),
        'ttl_minutes' => env('OTP_TTL_MINUTES', 5),
    ],

    'signed_urls' => [
        'stream_ttl_seconds' => env('STREAM_URL_TTL_SECONDS', 60),
        'download_ttl_seconds' => env('DOWNLOAD_URL_TTL_SECONDS', 120),
    ],

    'tickets' => [
        'qr_secret' => env('TICKET_QR_SECRET'),
        'qr_ttl_seconds' => env('TICKET_QR_TTL_SECONDS', 0),
    ],

];
