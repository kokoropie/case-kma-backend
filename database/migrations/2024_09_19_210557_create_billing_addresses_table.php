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
        Schema::create('billing_addresses', function (Blueprint $table) {
            $table->uuid('billing_address_id')->primary();
            $table->string('name');
            $table->string('phone_number');
            $table->string('email');
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
        Schema::dropIfExists('billing_addresses');
    }
};
