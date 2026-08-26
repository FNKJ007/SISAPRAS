<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_dokumen', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun')->unique()->comment('Tahun berlaku PKS/SPK');
            $table->string('nomor_pks', 255)->comment('Nomor Perjanjian Kerja Sama');
            $table->string('nomor_spk', 255)->comment('Nomor Surat Perintah Kerja');
            $table->date('tanggal_pks_spk')->comment('Tanggal PKS/SPK ditandatangani');
            $table->string('nama_bengkel', 255)->comment('Nama bengkel rekanan');
            $table->string('alamat_bengkel', 500)->nullable()->comment('Alamat bengkel rekanan');
            $table->string('nama_pimpinan_bengkel', 255)->nullable()->comment('Nama pimpinan bengkel untuk TTD');
            $table->boolean('is_active')->default(true)->comment('Apakah data ini aktif digunakan');
            $table->timestamps();
        });

        // Insert default data tahun 2026
        \DB::table('pengaturan_dokumen')->insert([
            'tahun' => 2026,
            'nomor_pks' => '000.4.7.2/001/PKS-Pem/Bid.SPI/2026',
            'nomor_spk' => 'SPK-004/I/2026/PRA',
            'tanggal_pks_spk' => '2026-01-09',
            'nama_bengkel' => 'CV. Pratama Motor',
            'alamat_bengkel' => 'Jl. Soekarno Hatta No. 463, Kota Bandung',
            'nama_pimpinan_bengkel' => 'CV. Pratama',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_dokumen');
    }
};
