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
        Schema::create('configurations', function (Blueprint $table) {
            $table->id('configuration_id');
            $table->integer('width');
            $table->integer('height');
            $table->string('image_url');
            $table->string('cropped_image_url');
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->float('amount', 2);
            $table->float('amount_material', 2)->default(0);
            $table->float('amount_finish', 2)->default(0);
            $table->enum('material', ['silicone', 'polycarbonate'])->default('silicone');
            $table->enum('finish', ['smooth', 'textured'])->default('smooth');
            $table->foreignId('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->softDeletes();

            $table->foreign('color')->references('slug')->on('case_colors')->onDelete('set null');
            $table->foreign('model')->references('slug')->on('phone_models')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
