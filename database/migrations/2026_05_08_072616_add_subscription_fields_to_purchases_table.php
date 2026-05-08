<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->boolean('is_subscription')->default(false)->after('status');
            $table->timestamp('expires_at')->nullable()->after('is_subscription');
            $table->string('original_transaction_id')->nullable()->after('expires_at');
            $table->boolean('auto_renewing')->default(false)->after('original_transaction_id');
            $table->string('subscription_status')->nullable()->after('auto_renewing');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'is_subscription',
                'expires_at',
                'original_transaction_id',
                'auto_renewing',
                'subscription_status',
            ]);
        });
    }
};
