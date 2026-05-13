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

    'stripe' => [
        'model' => App\Models\Tenant::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'sslcommerz' => [
        'url' => env('SSLCOMMERZ_URL'),
        'store_id' => env('SSLCOMMERZ_STORE_ID'),
        'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
    ],

    'bkash' => [
        'url' => env('BKASH_URL'),
        'merchant_id' => env('BKASH_MERCHANT_ID'),
        'username' => env('BKASH_USERNAME'),
        'password' => env('BKASH_PASSWORD'),
    ],

    'nagad' => [
        'url' => env('NAGAD_URL'),
        'merchant_id' => env('NAGAD_MERCHANT_ID'),
        'username' => env('NAGAD_USERNAME'),
        'password' => env('NAGAD_PASSWORD'),
    ],

    'rocket' => [
        'url' => env('ROCKET_URL'),
        'merchant_id' => env('ROCKET_MERCHANT_ID'),
        'username' => env('ROCKET_USERNAME'),
        'password' => env('ROCKET_PASSWORD'),
    ],

    'bank' => [
        'account_name' => env('BANK_ACCOUNT_NAME'),
        'account_number' => env('BANK_ACCOUNT_NUMBER'),
        'bank_name' => env('BANK_NAME'),
        'branch' => env('BANK_BRANCH'),
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

];
