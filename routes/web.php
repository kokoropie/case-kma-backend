<?php

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get("/test", function () {
    $payment = Order::all();
    foreach ($payment as $p) {
        dump($p->info);
    }
    dd($payment);
});