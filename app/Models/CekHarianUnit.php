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
        'kondisi_tangki_air',
        'kebocoran_tangki_air',
        'tekanan_pompa',
        'selang_induk',
        'catatan_tangki_pompa',
        'dokumentasi_tangki_pompa',

        'perlengkapan',
        'jumlah_rusak',
    ];

    protected $casts = [
        'dokumentasi_tangki_pompa' => 'array',
        'perlengkapan'             => 'array',
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
