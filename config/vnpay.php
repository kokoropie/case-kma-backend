<?php 
return [
    "version" => "2.1.0",
    "tmn_code" => env('VNPAY_TMN_CODE', 'YOUR_TMNCODE'),
    "hash_secret" => env('VNPAY_HASH_SECRET', 'YOUR_HASH_SECRET'),
    "url" => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    "return_url" => env('VNPAY_RETURN_URL', 'http://localhost:8000/vnpay-return'),
    "api" => env('VNPAY_API_URL', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'),
    "expire" => (float) env('VNPAY_EXPIRED', 15), // Expire time in minutes
];
