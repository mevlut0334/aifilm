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
        Schema::table('custom_video_requests', function (Blueprint $table) {
            // Change prompt to longText for unlimited length
            $table->longText('prompt')->change();

            // Make token_cost nullable (set by admin)
            $table->integer('token_cost')->nullable()->change();

            // Add token_deducted field
            $table->boolean('token_deducted')->default(false)->after('token_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_video_requests', function (Blueprint $table) {
            // Revert prompt back to text
            $table->text('prompt')->change();

            // Make token_cost not nullable
            $table->integer('token_cost')->default(0)->change();

            // Drop token_deducted field
            $table->dropColumn('token_deducted');
        });
    }
};
