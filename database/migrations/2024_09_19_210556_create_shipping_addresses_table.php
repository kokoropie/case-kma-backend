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
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->uuid('shipping_address_id')->primary();
            $table->string('name');
            $table->string('phone_number');
            $table->string('address');
            $table->string('street');
            $table->string('province');
            $table->string('city');
            $table->string('postal_code');
            $table->string('country');
            $table->foreignId('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};
