<?php 
return [
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    'currency' => 'USD',
    'locale' => 'en-US',
    'base_url' => env('PAYPAL_BASE_URL', 'https://api.sandbox.paypal.com'),
    'return_url' => env('PAYPAL_RETURN_URL', 'http://localhost:8000/paypal-return'),
];
