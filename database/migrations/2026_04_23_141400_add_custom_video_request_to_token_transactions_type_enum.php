<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add custom_video_request to enum
        DB::statement("ALTER TABLE `token_transactions` MODIFY `type` ENUM('registration','admin_grant','admin_deduct','purchase','usage','generation_request','refund','custom_image_request','custom_video_request') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove custom_video_request from enum
        DB::statement("UPDATE `token_transactions` SET `type` = 'usage' WHERE `type` = 'custom_video_request'");
        DB::statement("ALTER TABLE `token_transactions` MODIFY `type` ENUM('registration','admin_grant','admin_deduct','purchase','usage','generation_request','refund','custom_image_request') NOT NULL");
    }
};
