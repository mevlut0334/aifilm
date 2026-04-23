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
        Schema::table('custom_video_edit_requests', function (Blueprint $table) {
            $table->integer('edit_cost')->nullable()->after('edit_prompt');
            $table->boolean('token_deducted')->default(false)->after('edit_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_video_edit_requests', function (Blueprint $table) {
            $table->dropColumn(['edit_cost', 'token_deducted']);
        });
    }
};
