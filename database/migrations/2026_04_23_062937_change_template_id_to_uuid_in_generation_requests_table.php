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
        Schema::table('generation_requests', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['template_id']);

            // Drop the index
            $table->dropIndex(['template_id']);

            // Change column to uuid string
            $table->uuid('template_id')->nullable()->change();

            // Add index back
            $table->index('template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generation_requests', function (Blueprint $table) {
            // Drop index
            $table->dropIndex(['template_id']);

            // Change back to unsignedBigInteger
            $table->unsignedBigInteger('template_id')->nullable()->change();

            // Add foreign key back
            $table->foreign('template_id')->references('id')->on('templates')->nullOnDelete();

            // Add index back
            $table->index('template_id');
        });
    }
};
