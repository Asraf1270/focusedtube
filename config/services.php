<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
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

    'youtube' => [
        'key'      => env('YOUTUBE_API_KEY'),
        'base_url' => env('YOUTUBE_API_BASE_URL', 'https://www.googleapis.com/youtube/v3'),
        'timeout'  => env('YOUTUBE_API_TIMEOUT', 8),
        'retries'  => env('YOUTUBE_API_RETRIES', 1),
    ],

];