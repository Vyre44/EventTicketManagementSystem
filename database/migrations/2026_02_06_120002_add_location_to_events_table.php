<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Events tablosuna konum bilgisi ekle
 * 
 * location: Etkinliğin yapılacağı fiziksel adres/mekan
 * Opsiyonel alan (nullable) - UI'da @if($event->location) ile kontrollü gösterilir
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('location')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
