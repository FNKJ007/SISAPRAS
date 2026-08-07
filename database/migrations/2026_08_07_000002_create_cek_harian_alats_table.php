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
        Schema::create('cek_harian_alats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('nama_pemeriksa');
            $table->string('jabatan');
            $table->unsignedInteger('unit_id');
            $table->string('unit_nama')->nullable();
            $table->date('tanggal_pemeriksaan');

            // Daftar alat disimpan sebagai JSON:
            // [{ id, nama, jumlah_baik, jumlah_rusak, nomor_rusak }, ...]
            $table->json('alat');
            $table->unsignedInteger('total_baik')->default(0);
            $table->unsignedInteger('total_rusak')->default(0);

            $table->text('catatan_umum')->nullable();
            $table->string('foto_umum')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cek_harian_alats');
    }
};
