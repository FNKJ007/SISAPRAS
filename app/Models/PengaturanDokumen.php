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
