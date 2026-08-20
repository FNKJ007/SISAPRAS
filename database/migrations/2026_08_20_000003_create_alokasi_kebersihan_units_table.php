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
        if (!Schema::hasTable('alokasi_kebersihan_units')) {
            Schema::create('alokasi_kebersihan_units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
                $table->integer('tahun')->default(2026);
                $table->string('sabun_cuci')->default('12 Botol / Tahun');
                $table->string('lap_handuk')->default('4 Pcs / Tahun');
                $table->string('kanebo')->default('6 Pcs / Tahun');
                $table->string('semir_ban')->default('6 Kaleng / Tahun');
                $table->string('sikat_ban')->default('2 Pcs / Tahun');
                $table->string('pengharum')->default('12 Pcs / Tahun');
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->unique(['unit_id', 'tahun']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alokasi_kebersihan_units');
    }
};