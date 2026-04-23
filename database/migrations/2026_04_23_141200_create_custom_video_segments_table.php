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
        Schema::create('custom_video_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_video_request_id')->constrained()->onDelete('cascade');
            $table->integer('segment_number');
            $table->text('video_url')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('progress')->default(0);
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index('custom_video_request_id', 'cvs_request_id_idx');
            $table->index('segment_number', 'cvs_segment_number_idx');
            $table->index('status', 'cvs_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_video_segments');
    }
};
