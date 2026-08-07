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
        Schema::create('cek_harian_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Step 1 - Identitas
            $table->string('nama_pemeriksa');
            $table->string('jabatan');
            $table->unsignedInteger('unit_id');
            $table->string('unit_nama')->nullable();
           
            // Step 2 - Pemanasan & BBM
            $table->string('bukti_pemanasan')->nullable();
            $table->string('jenis_bbm'); // solar, bensin
            $table->string('bukti_bbm')->nullable();

            // Step 3 - Tangki & Pompa
            $table->string('level_air');
            $table->string('kondisi_tangki_air');
            $table->string('kebocoran_tangki_air');
            $table->string('tekanan_pompa');
            $table->string('selang_induk');
            $table->text('catatan_tangki_pompa')->nullable();
            $table->json('dokumentasi_tangki_pompa')->nullable();

            // Step 4 - Perlengkapan (disimpan sebagai JSON: { key: {label, status, catatan} })
            $table->json('perlengkapan')->nullable();
            $table->unsignedInteger('jumlah_rusak')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cek_harian_units');
    }
};
