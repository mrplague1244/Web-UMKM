<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    /*
    |--------------------------------------------------------------------------
    | Sumopod Payment Gateway
    |--------------------------------------------------------------------------
    | SUMOPOD_BASE_URL   : https://api-pay-sandbox.sumopod.com/api/v1 (sandbox)
    |                      https://api-pay.sumopod.com/api/v1 (production)
    | SUMOPOD_API_KEY    : API key dari dashboard Sumopod
    | SUMOPOD_WEBHOOK_SECRET : Secret untuk verifikasi HMAC-SHA256 webhook
    */
    'sumopod' => [
        'base_url'       => env('SUMOPOD_BASE_URL', 'https://api-pay-sandbox.sumopod.com/api/v1'),
        'api_key'        => env('SUMOPOD_API_KEY', ''),
        'webhook_secret' => env('SUMOPOD_WEBHOOK_SECRET', ''),
    ],

];
