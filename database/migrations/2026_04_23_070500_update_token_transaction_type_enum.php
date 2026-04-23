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
        DB::statement("ALTER TABLE `token_transactions` MODIFY `type` ENUM('registration','admin_grant','admin_deduct','purchase','usage','generation_request','refund') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE `token_transactions` SET `type` = 'usage' WHERE `type` = 'generation_request'");
        DB::statement("UPDATE `token_transactions` SET `type` = 'admin_grant' WHERE `type` = 'refund'");
        DB::statement("ALTER TABLE `token_transactions` MODIFY `type` ENUM('registration','admin_grant','admin_deduct','purchase','usage') NOT NULL");
    }
};
