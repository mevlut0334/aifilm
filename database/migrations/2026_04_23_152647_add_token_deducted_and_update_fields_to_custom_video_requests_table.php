<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_video_requests', function (Blueprint $table) {
            // Change prompt to longText for unlimited length
            $table->longText('prompt')->change();

            // Make token_cost nullable (set by admin)
            $table->integer('token_cost')->nullable()->change();

            // Add token_deducted field (only if not exists)
            if (!Schema::hasColumn('custom_video_requests', 'token_deducted')) {
                $table->boolean('token_deducted')->default(false)->after('token_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('custom_video_requests', function (Blueprint $table) {
            $table->text('prompt')->change();
            $table->integer('token_cost')->default(0)->change();

            if (Schema::hasColumn('custom_video_requests', 'token_deducted')) {
                $table->dropColumn('token_deducted');
            }
        });
    }
};
