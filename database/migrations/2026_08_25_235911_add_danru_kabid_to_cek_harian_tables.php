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
            if (!Schema::hasColumn('cek_harian_units', 'nama_danru')) {
                $table->string('nama_danru')->nullable()->after('pos');
            }
            if (!Schema::hasColumn('cek_harian_units', 'nama_kabid')) {
                $table->string('nama_kabid')->nullable()->after('nama_danru');
            }
        });

        Schema::table('cek_harian_alats', function (Blueprint $table) {
            if (!Schema::hasColumn('cek_harian_alats', 'nama_danru')) {
                $table->string('nama_danru')->nullable()->after('pos');
            }
            if (!Schema::hasColumn('cek_harian_alats', 'nama_kabid')) {
                $table->string('nama_kabid')->nullable()->after('nama_danru');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cek_harian_units', function (Blueprint $table) {
            $table->dropColumn(['nama_danru', 'nama_kabid']);
        });

        Schema::table('cek_harian_alats', function (Blueprint $table) {
            $table->dropColumn(['nama_danru', 'nama_kabid']);
        });
    }
};
