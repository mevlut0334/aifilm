<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_packages', function (Blueprint $table) {
            $table->id();

            // Çok dilli başlık ve açıklama (web paketiyle aynı yapı)
            $table->json('title');
            $table->json('description')->nullable();

            // Token miktarı
            $table->integer('token_amount');

            // App Store product ID (iOS)
            // Örn: com.aifilm.subscription.monthly
            $table->string('ios_product_id')->nullable()->unique();

            // Play Store product ID (Android)
            // Örn: aifilm_subscription_monthly
            $table->string('android_product_id')->nullable()->unique();

            // Sıralama ve durum
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_packages');
    }
};
