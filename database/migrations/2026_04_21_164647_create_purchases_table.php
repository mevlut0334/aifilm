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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->enum('platform', ['web', 'ios', 'android']);
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('gateway_transaction_id')->unique();
            $table->integer('token_amount');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->timestamps();

            $table->index('user_id');
            $table->index('platform');
            $table->index('status');
            $table->index('gateway_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
