<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('order_id')->primary();
            $table->foreignId('configuration_id')->references('configuration_id')->on('configurations')->onDelete('cascade');
            $table->foreignId('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->enum('payment_method', ['paypal', 'card', 'vnpay', 'bank']);
            $table->unsignedInteger('quantity');
            $table->float('amount', 2);
            $table->float('shipping_fee', 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->string('shipping_address_id')->references('shipping_address_id')->on('shipping_addresses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
