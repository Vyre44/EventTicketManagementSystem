<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ticket Types Migration
 *
 * Etkinliklere ait bilet türlerini (ör: VIP, Standart) tutan tabloyu oluşturur.
 * Her bilet tipi bir etkinliğe bağlıdır ve stok yönetimi içerir.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('total_quantity'); // Toplam stok
            $table->integer('remaining_quantity'); // Kalan stok
            $table->timestamp('sale_start')->nullable();
            $table->timestamp('sale_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
