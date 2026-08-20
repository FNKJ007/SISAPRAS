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
        if (!Schema::hasColumn('cek_harian_units', 'bukti_pencucian')) {
            Schema::table('cek_harian_units', function (Blueprint $table) {
                $table->string('bukti_pencucian')->nullable()->after('bukti_bbm');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cek_harian_units', 'bukti_pencucian')) {
            Schema::table('cek_harian_units', function (Blueprint $table) {
                $table->dropColumn('bukti_pencucian');
            });
        }
    }
};
