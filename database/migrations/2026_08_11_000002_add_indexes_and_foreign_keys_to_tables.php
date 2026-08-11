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
        Schema::table('pengajuans', function (Blueprint $table) {
            $table->index('status', 'idx_pengajuans_status');
            $table->index('tanggal_keberangkatan', 'idx_pengajuans_tgl_keberangkatan');
            $table->index('user_id', 'idx_pengajuans_user_id');
        });

        Schema::table('cek_harian_units', function (Blueprint $table) {
            $table->index('kategori', 'idx_cek_unit_kategori');
            $table->index('unit_id', 'idx_cek_unit_unit_id');
            $table->index('pos', 'idx_cek_unit_pos');
        });

        Schema::table('cek_harian_alats', function (Blueprint $table) {
            $table->index('kategori', 'idx_cek_alat_kategori');
            $table->index('unit_id', 'idx_cek_alat_unit_id');
            $table->index('pos', 'idx_cek_alat_pos');
            $table->index('tanggal_pemeriksaan', 'idx_cek_alat_tgl_pemeriksaan');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->index('kategori', 'idx_units_kategori');
            $table->index('status', 'idx_units_status');
        });

        Schema::table('peralatans', function (Blueprint $table) {
            $table->index('kategori', 'idx_peralatans_kategori');
            $table->index('status', 'idx_peralatans_status');
        });

        Schema::table('pos', function (Blueprint $table) {
            $table->index('status', 'idx_pos_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            $table->dropIndex('idx_pengajuans_status');
            $table->dropIndex('idx_pengajuans_tgl_keberangkatan');
            $table->dropIndex('idx_pengajuans_user_id');
        });

        Schema::table('cek_harian_units', function (Blueprint $table) {
            $table->dropIndex('idx_cek_unit_kategori');
            $table->dropIndex('idx_cek_unit_unit_id');
            $table->dropIndex('idx_cek_unit_pos');
        });

        Schema::table('cek_harian_alats', function (Blueprint $table) {
            $table->dropIndex('idx_cek_alat_kategori');
            $table->dropIndex('idx_cek_alat_unit_id');
            $table->dropIndex('idx_cek_alat_pos');
            $table->dropIndex('idx_cek_alat_tgl_pemeriksaan');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex('idx_units_kategori');
            $table->dropIndex('idx_units_status');
        });

        Schema::table('peralatans', function (Blueprint $table) {
            $table->dropIndex('idx_peralatans_kategori');
            $table->dropIndex('idx_peralatans_status');
        });

        Schema::table('pos', function (Blueprint $table) {
            $table->dropIndex('idx_pos_status');
        });
    }
};
