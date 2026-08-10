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
            $table->enum('kategori', ['pemadam', 'rescue'])->default('pemadam');
            $table->string('nomor_lambung')->nullable();
            $table->string('plat_nomor')->nullable();
            $table->string('pos')->nullable();
            $table->string('merk_tipe')->nullable();
            $table->integer('tahun_pembuatan')->nullable();
            $table->enum('status', ['aktif', 'perbaikan', 'nonaktif'])->default('aktif');
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
