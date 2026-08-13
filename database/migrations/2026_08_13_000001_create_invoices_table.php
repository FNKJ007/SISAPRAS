<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_invoice', 50)->unique();
            $table->date('tanggal_invoice');

            // Relasi ke unit kendaraan (menu Data Unit)
            $table->foreignId('unit_id')->constrained('units')->cascadeOnUpdate()->restrictOnDelete();

            // Snapshot data unit pada saat invoice dibuat (biar histori tidak berubah
            // walau data unit di-update di kemudian hari)
            $table->string('no_pol', 20)->nullable();
            $table->string('no_lambung', 20)->nullable();
            $table->string('jenis_mobil', 100)->nullable();
            $table->string('lokasi', 100)->nullable();

            $table->string('kode_rekening', 100)->nullable();
            $table->string('tahun_anggaran', 4)->nullable();

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);

            $table->enum('status', ['draft', 'diajukan', 'disetujui', 'lunas'])->default('draft');
            $table->text('catatan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
