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
            $table->string('thumbnail_url');
            $table->float('price');
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->string('material')->nullable();
            $table->enum('finish', ['smooth', 'textured']);
            $table->softDeletes();

            $table->foreign('color')->references('slug')->on('case_colors')->onDelete('set null');
            $table->foreign('model')->references('slug')->on('phone_models')->onDelete('set null');
            $table->foreign('material')->references('slug')->on('case_materials')->onDelete('set null');
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
