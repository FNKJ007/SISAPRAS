<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CekHarianUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_pemeriksa',
        'jabatan',
        'unit_id',
        'unit_nama',
        'shift',

        'bukti_pemanasan',
        'jenis_bbm',
        'level_bbm',
        'jumlah_bbm_liter',
        'bukti_bbm',

        'level_air',
        'kondisi_tangki',
        'kebocoran_tangki',
        'tekanan_pompa',
        'pengisian_pompa',
        'selang_induk',
        'catatan_tangki_pompa',
        'dokumentasi_tangki_pompa',

        'perlengkapan',
        'jumlah_rusak',
    ];

    protected $casts = [
        'dokumentasi_tangki_pompa' => 'array',
        'perlengkapan'             => 'array',
        'jumlah_bbm_liter'         => 'float',
    ];

    public static array $shiftMap = [
        'pagi'  => 'Pagi',
        'siang' => 'Siang',
        'malam' => 'Malam',
    ];

    public static array $levelMap = [
        'penuh'   => 'Penuh',
        '3_4'     => '3/4',
        '1_2'     => '1/2',
        'kosong'  => 'Kosong',
    ];

    /**
     * Relasi ke User (pemeriksa)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Daftar item perlengkapan yang berstatus "rusak".
     */
    public function getPerlengkapanRusakAttribute(): array
    {
        return collect($this->perlengkapan ?? [])
            ->filter(fn ($item) => ($item['status'] ?? null) === 'rusak')
            ->all();
    }
}
