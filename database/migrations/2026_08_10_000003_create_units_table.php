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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kategori', 100)->default('pemadam');
            $table->string('nomor_lambung')->nullable();
            $table->string('plat_nomor')->nullable();
            $table->string('no_rangka_mesin')->nullable();
            $table->string('merk_tipe')->nullable();
            $table->integer('tahun_pembuatan')->nullable();
            $table->string('cc')->nullable();
            $table->string('jenis_peruntukan')->nullable();
            $table->string('pos')->nullable();
            $table->string('pengemudi_1')->nullable();
            $table->string('pengemudi_2')->nullable();
            $table->string('status', 50)->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
