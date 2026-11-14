<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Talebi oluşturan müşteri
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Müşteri talep bilgileri
            $table->longText('scenario')->nullable();
            $table->string('format', 20);
            $table->json('characters')->nullable();
            $table->json('environments')->nullable();

            // İlk formdaki revize talebi
            $table->text('initial_revision_video_url')->nullable();
            $table->text('initial_revision_description')->nullable();

            // Admin tarafından eklenen güncel video
            $table->text('video_url')->nullable();
            $table->timestamp('video_updated_at')->nullable();

            // Yalnızca processing ve completed
            $table->string('status', 20)->default('processing');

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_requests');
    }
};
