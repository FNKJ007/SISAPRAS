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
        $table->string('level_air')->nullable()->change();
        $table->string('kondisi_tangki_air')->nullable()->change();
        $table->string('kebocoran_tangki_air')->nullable()->change();
        $table->string('tekanan_pompa')->nullable()->change();
        $table->string('selang_induk')->nullable()->change();
        $table->text('catatan_tangki_pompa')->nullable()->change();
        $table->json('dokumentasi_tangki_pompa')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('cek_harian_units', function (Blueprint $table) {
        $table->string('level_air')->nullable(false)->change();
        // dst — sesuaikan balik ke NOT NULL kalau perlu
    });
}
    
};
