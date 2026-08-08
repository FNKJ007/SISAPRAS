<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CekHarianAlat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kategori',
        'nama_pemeriksa',
        'jabatan',
        'unit_id',
        'unit_nama',
        'tanggal_pemeriksaan',
        'alat',
        'total_baik',
        'total_rusak',
        'catatan_umum',
        'foto_umum',
    ];

    protected $casts = [
        'alat'                 => 'array',
        'tanggal_pemeriksaan'  => 'date',
    ];

    /**
     * Relasi ke User (pemeriksa)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Daftar alat yang memiliki jumlah rusak > 0.
     */
    public function getAlatRusakAttribute(): array
    {
        return collect($this->alat ?? [])
            ->filter(fn ($item) => (int) ($item['jumlah_rusak'] ?? 0) > 0)
            ->all();
    }
}
