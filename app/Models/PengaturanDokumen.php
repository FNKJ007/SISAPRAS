<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanDokumen extends Model
{
    protected $table = 'pengaturan_dokumen';

    protected $fillable = [
        'tahun',
        'nomor_pks',
        'nomor_spk',
        'tanggal_pks_spk',
        'nama_bengkel',
        'alamat_bengkel',
        'nama_pimpinan_bengkel',
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
        'is_active',
    ];

    protected $casts = [
        'tanggal_pks_spk' => 'date:Y-m-d',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'tanggal_pks_spk_label',
        'tanggal_pks_spk_ymd',
    ];

    /**
     * Ambil pengaturan dokumen aktif untuk tahun tertentu (default: tahun ini).
     */
    public static function getAktif(?int $tahun = null): ?self
    {
        $tahun = $tahun ?? (int) date('Y');

        return static::where('tahun', $tahun)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Format tanggal PKS/SPK dalam bahasa Indonesia.
     */
    public function getTanggalPksSpkLabelAttribute(): string
    {
        return $this->tanggal_pks_spk
            ? $this->tanggal_pks_spk->locale('id')->isoFormat('D MMMM Y')
            : '-';
    }

    /**
     * Format tanggal Y-m-d standar untuk input date.
     */
    public function getTanggalPksSpkYmdAttribute(): string
    {
        return $this->tanggal_pks_spk ? $this->tanggal_pks_spk->format('Y-m-d') : '';
    }
}
