<?php
return [
    'paystack' => [
        'public' => env('PAYSTACK_PUBLIC_KEY'),
        'secret' => env('PAYSTACK_SECRET_KEY'),
        'callback' => env('PAYSTACK_CALLBACK_URL'),
    ],
    'peyflex' => [
        'api_key' => env('PEYFLEX_API_KEY'),
        'base_url' => env('PEYFLEX_BASE_URL', 'https://api.peyflex.com'),
    ],
];
