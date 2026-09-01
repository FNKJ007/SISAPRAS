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
        Schema::table('pengaturan_dokumen', function (Blueprint $table) {
            // TTD Kuasa Pengguna Anggaran (Kabid SPI) - untuk Surat Pesanan & Surat Permohonan Bengkel
            $table->string('ttd_kpa_nama', 255)->nullable()->default('Erpi Suwandi, S.T., M.M.');
            $table->string('ttd_kpa_nip', 50)->nullable()->default('197908202006041010');
            $table->string('ttd_kpa_jabatan', 255)->nullable()->default('KEPALA BIDANG SPI');
            $table->string('ttd_kpa_pangkat', 100)->nullable()->default('Pembina');

            // TTD PPTK (Kasi Pemeliharaan Sarana) - untuk Surat Pesanan
            $table->string('ttd_pptk_nama', 255)->nullable()->default('Ahmad Kuswara, S.M., M.M.');
            $table->string('ttd_pptk_nip', 50)->nullable()->default('197209212008011001');
            $table->string('ttd_pptk_jabatan', 255)->nullable()->default('KEPALA SEKSI PEMELIHARAAN SARANA');
            $table->string('ttd_pptk_pangkat', 100)->nullable()->default('Penata');

            // TTD Mengetahui Kepala Bidang Pemadam - untuk Surat Permohonan Bidang
            $table->string('ttd_kabid_pemadam_nama', 255)->nullable()->default('RD. ASEP BINTANG JOHAR SLAMET S.IP.MSI');
            $table->string('ttd_kabid_pemadam_nip', 50)->nullable()->default('197006062007011014');
            $table->string('ttd_kabid_pemadam_jabatan', 255)->nullable()->default('KEPALA BIDANG PEMADAMAN');

            // TTD Mengetahui Kepala Bidang Penyelamatan / Rescue
            $table->string('ttd_kabid_rescue_nama', 255)->nullable()->default('H. EDI KURNIADI, S.AP.');
            $table->string('ttd_kabid_rescue_nip', 50)->nullable()->default('197008121993031005');
            $table->string('ttd_kabid_rescue_jabatan', 255)->nullable()->default('KEPALA BIDANG PENYELAMATAN');

            // TTD Mengetahui Kepala Bidang Pencegahan
            $table->string('ttd_kabid_pencegahan_nama', 255)->nullable()->default('Drs. H. MULYADI, M.Si.');
            $table->string('ttd_kabid_pencegahan_nip', 50)->nullable()->default('196811201993031005');
            $table->string('ttd_kabid_pencegahan_jabatan', 255)->nullable()->default('KEPALA BIDANG PENCEGAHAN KEBAKARAN');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_dokumen', function (Blueprint $table) {
            $table->dropColumn([
                'ttd_kpa_nama',
                'ttd_kpa_nip',
                'ttd_kpa_jabatan',
                'ttd_kpa_pangkat',
                'ttd_pptk_nama',
                'ttd_pptk_nip',
                'ttd_pptk_jabatan',
                'ttd_pptk_pangkat',
                'ttd_kabid_pemadam_nama',
                'ttd_kabid_pemadam_nip',
                'ttd_kabid_pemadam_jabatan',
                'ttd_kabid_rescue_nama',
                'ttd_kabid_rescue_nip',
                'ttd_kabid_rescue_jabatan',
                'ttd_kabid_pencegahan_nama',
                'ttd_kabid_pencegahan_nip',
                'ttd_kabid_pencegahan_jabatan',
            ]);
        });
    }
};
