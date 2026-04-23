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
        Schema::create('custom_video_edit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_video_segment_id')->constrained()->onDelete('cascade');
            $table->text('edit_prompt');
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index('custom_video_segment_id', 'cver_segment_id_idx');
            $table->index('status', 'cver_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_video_edit_requests');
    }
};
