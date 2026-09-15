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
        'pos',
        'nama_danru',
        'nama_kabid',
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
        'foto_umum'            => 'array',
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
     * Relasi ke Unit Kendaraan
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    protected $appends = [
        'total_alat_baik',
        'total_alat_rusak',
    ];

    /**
     * Accessor total_alat_baik untuk kompatibilitas frontend & JSON response.
     */
    public function getTotalAlatBaikAttribute(): int
    {
        if (isset($this->attributes['total_baik']) && $this->attributes['total_baik'] !== null) {
            return (int) $this->attributes['total_baik'];
        }
        return (int) collect($this->alat ?? [])->sum(fn ($item) => (int) ($item['jumlah_baik'] ?? 0));
    }

    /**
     * Accessor total_alat_rusak untuk kompatibilitas frontend & JSON response.
     */
    public function getTotalAlatRusakAttribute(): int
    {
        if (isset($this->attributes['total_rusak']) && $this->attributes['total_rusak'] !== null) {
            return (int) $this->attributes['total_rusak'];
        }
        return (int) collect($this->alat ?? [])->sum(fn ($item) => (int) ($item['jumlah_rusak'] ?? 0));
    }

    /**
     * Accessor total_baik (fallback hitung dari JSON alat jika null).
     */
    public function getTotalBaikAttribute($value): int
    {
        if ($value !== null) {
            return (int) $value;
        }
        return (int) collect($this->alat ?? [])->sum(fn ($item) => (int) ($item['jumlah_baik'] ?? 0));
    }

    /**
     * Accessor total_rusak (fallback hitung dari JSON alat jika null).
     */
    public function getTotalRusakAttribute($value): int
    {
        if ($value !== null) {
            return (int) $value;
        }
        return (int) collect($this->alat ?? [])->sum(fn ($item) => (int) ($item['jumlah_rusak'] ?? 0));
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
