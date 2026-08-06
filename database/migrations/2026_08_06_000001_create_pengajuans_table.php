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
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('bidang');
            $table->string('pos');
            $table->string('regu');
            $table->string('jenis_kendaraan');
            $table->string('nomor_lambung');
            $table->string('item_perbaikan');
            $table->string('nama_pemegang');
            $table->string('nip_pemegang');
            $table->string('nama_komandan_regu');
            $table->string('nip_komandan_regu');
            $table->string('nama_kepala_bidang');
            $table->string('nip_kepala_bidang');
            $table->string('status')->default('menunggu'); // 'menunggu', 'disetujui', 'ditolak'
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
