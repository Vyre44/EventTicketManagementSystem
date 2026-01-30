<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tickets tablosundan checked_in alanını kaldıran migration.
 *
 * checked_in_at alanı ile check-in takibi yapılmaktadır.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tickets', 'checked_in')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('checked_in');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('checked_in')->default(false);
        });
    }
};
