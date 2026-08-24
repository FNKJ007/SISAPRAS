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
        if (!Schema::hasColumn('cek_harian_units', 'kebersihan_unit')) {
            Schema::table('cek_harian_units', function (Blueprint $table) {
                $table->string('kebersihan_unit')->default('bersih')->after('unit_nama');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cek_harian_units', 'kebersihan_unit')) {
            Schema::table('cek_harian_units', function (Blueprint $table) {
                $table->dropColumn('kebersihan_unit');
            });
        }
    }
};