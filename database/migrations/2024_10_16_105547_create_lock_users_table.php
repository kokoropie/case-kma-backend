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
        Schema::create('lock_users', function (Blueprint $table) {
            $table->id('lock_user_id');
            $table->text('reason');
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
            $table->foreignId("user_id")->constrained('users', 'user_id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lock_users');
    }
};
