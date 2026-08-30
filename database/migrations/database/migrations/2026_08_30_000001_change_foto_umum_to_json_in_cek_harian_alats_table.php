<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Kolom foto_umum dulunya menyimpan 1 path foto (string, maks 255 char).
     * Sekarang fitur upload mendukung sampai 3 foto sekaligus yang disimpan
     * sebagai array JSON (lihat cast 'array' di App\Models\CekHarianAlat).
     * Kolom string(255) berisiko terpotong kalau path gabungan 3 foto lebih
     * panjang dari 255 karakter, jadi diubah ke json.
     */
    public function up(): void
    {
        Schema::table('cek_harian_alats', function (Blueprint $table) {
            $table->json('foto_umum')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cek_harian_alats', function (Blueprint $table) {
            $table->string('foto_umum')->nullable()->change();
        });
    }
};
