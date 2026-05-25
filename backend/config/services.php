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

    'n8n' => [
        'webhook_secret' => env('N8N_WEBHOOK_SECRET', 'barangay_n8n_secret_2026'),
        'webhooks' => [
            'document_status' => env('N8N_WEBHOOK_DOCUMENT_STATUS', 'http://localhost:5678/webhook/document-status'),
            'blotter_status' => env('N8N_WEBHOOK_BLOTTER_STATUS', 'http://localhost:5678/webhook/blotter-status'),
            'announcement' => env('N8N_WEBHOOK_ANNOUNCEMENT', 'http://localhost:5678/webhook/announcement'),
            'resident_created' => env('N8N_WEBHOOK_RESIDENT_CREATED', 'http://localhost:5678/webhook/resident-created'),
        ],
    ],

];
