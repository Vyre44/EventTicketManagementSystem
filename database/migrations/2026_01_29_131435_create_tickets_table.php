<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tickets Migration
 *
 * Satın alınan biletleri tutan tabloyu oluşturur.
 * Her bilet bir siparişe ve bilet tipine bağlıdır.
 * Biletin kodu, durumu ve check-in zamanı saklanır.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('status')->default('active'); // active, checked_in, cancelled, refunded
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
