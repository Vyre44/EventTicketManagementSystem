<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Orders Migration
 *
 * Bilet siparişlerini tutan tabloyu oluşturur.
 * Her sipariş bir kullanıcıya ve etkinliğe bağlıdır.
 * Siparişin toplam tutarı, durumu ve ödeme zamanı saklanır.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // satın alan
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();  // hangi etkinlik

            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending'); // pending, paid, cancelled, failed, expired, refunded

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
