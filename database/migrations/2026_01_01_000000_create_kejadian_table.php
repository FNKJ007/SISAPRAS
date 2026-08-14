<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kejadian', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kejadian')->unique();
            $table->dateTime('waktu_kejadian');
            $table->enum('jenis_kejadian', ['Kebakaran', 'Rescue', 'Penyelamatan', 'Non-Kebakaran']);
            $table->string('kategori_detail'); // Contoh: Evakuasi Ular, Kebakaran Rumah, Sarang Tawon, Pohon Tumbang
            $table->text('lokasi');
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('nama_pelapor')->nullable();
            $table->string('no_hp_pelapor')->nullable();
            $table->string('objek_terdampak')->nullable();
            $table->text('penyebab')->nullable();
            $table->string('pos_regu')->nullable(); // Pos Damkar / Regu Pelaksana
            $table->string('unit_armada')->nullable(); // Armada Mobil
            $table->bigInteger('estimasi_kerugian')->default(0);
            $table->integer('korban_luka')->default(0);
            $table->integer('korban_jiwa')->default(0);
            $table->time('waktu_terima_laporan')->nullable();
            $table->time('waktu_berangkat')->nullable();
            $table->time('waktu_tiba')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->enum('status', ['Proses', 'Selesai', 'Dibatalkan'])->default('Selesai');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kejadian');
    }
};