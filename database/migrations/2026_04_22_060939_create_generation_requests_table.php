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
        Schema::create('generation_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['custom_image', 'custom_video', 'template_image', 'template_video']);
            $table->enum('orientation', ['landscape', 'portrait', 'square'])->nullable();
            $table->text('description')->nullable();
            $table->integer('token_cost')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('output_url')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('template_id');
            $table->index('status');
            $table->index('type');
            $table->index('orientation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generation_requests');
    }
};
