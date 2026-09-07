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
        Schema::table('cek_harian_units', function (Blueprint $table) {
            if (!Schema::hasColumn('cek_harian_units', 'kilometer')) {
                $table->string('kilometer')->nullable()->after('tanggal_pemeriksaan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cek_harian_units', function (Blueprint $table) {
            if (Schema::hasColumn('cek_harian_units', 'kilometer')) {
                $table->dropColumn('kilometer');
            }
        });
    }
};
