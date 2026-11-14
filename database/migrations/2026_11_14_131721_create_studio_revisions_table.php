<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_revisions', function (Blueprint $table) {
            $table->id();

            // İlgili Studio talebi
            $table->foreignId('studio_request_id')
                ->constrained('studio_requests')
                ->cascadeOnDelete();

            // Müşterinin revize açıklaması
            $table->longText('description');

            // Müşterinin eklediği referans video bağlantısı
            $table->text('reference_video_url')->nullable();

            // Yalnızca processing ve completed
            $table->string('status', 20)->default('processing');

            $table->timestamps();

            $table->index(['studio_request_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_revisions');
    }
};
