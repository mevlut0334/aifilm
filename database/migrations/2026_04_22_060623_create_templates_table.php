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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->json('title'); // translatable: {en: "", tr: ""}
            $table->json('description')->nullable(); // translatable
            $table->integer('token_cost')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('landscape_video_path')->nullable();
            $table->string('portrait_video_path')->nullable();
            $table->string('square_video_path')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
