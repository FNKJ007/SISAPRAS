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
        Schema::create('peralatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('kategori', ['pemadam', 'rescue', 'command_center'])->default('pemadam');
            $table->string('kode_alat')->nullable();
            $table->integer('jumlah_total')->default(1);
            $table->integer('kondisi_baik')->default(1);
            $table->integer('kondisi_rusak')->default(0);
            $table->string('satuan')->default('unit');
            $table->string('lokasi')->nullable();
            $table->enum('status', ['baik', 'perlu_perhatian', 'rusak'])->default('baik');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peralatans');
    }
};
