<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kategori',
        'nomor_lambung',
        'plat_nomor',
        'no_rangka_mesin',
        'merk_tipe',
        'tahun_pembuatan',
        'cc',
        'jenis_kendaraan',
        'peruntukan',
        'jenis_peruntukan',
        'pos',
        'pengemudi_1',
        'pengemudi_2',
        'status',
        'catatan',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($unit) {
            if ($unit->kategori) {
                $unit->kategori = ucwords(strtolower(str_replace('_', ' ', trim($unit->kategori))));
            }
            if ($unit->jenis_kendaraan) {
                $unit->jenis_kendaraan = strtoupper(trim($unit->jenis_kendaraan));
            }
            if ($unit->peruntukan) {
                $unit->peruntukan = strtoupper(trim($unit->peruntukan));
            }
            if ($unit->jenis_kendaraan || $unit->peruntukan) {
                $parts = array_filter([$unit->jenis_kendaraan, $unit->peruntukan]);
                $unit->jenis_peruntukan = implode('/', $parts);
            }
        });
    }

    public static array $kategoriMap = [
        'pemadam' => 'Pemadam',
        'rescue'  => 'Rescue',
    ];

    public static array $statusMap = [
        'aktif'     => 'Aktif',
        'perbaikan' => 'Perbaikan / Bengkel',
        'nonaktif'  => 'Non-Aktif',
    ];

    public function cekHarianUnits()
    {
        return $this->hasMany(CekHarianUnit::class, 'unit_id');
    }

    public function cekHarianAlats()
    {
        return $this->hasMany(CekHarianAlat::class, 'unit_id');
    }

    public function getNoPolAttribute()
    {
        return $this->plat_nomor;
    }

    public function getNoLambungAttribute()
    {
        return $this->nomor_lambung;
    }

    public function getJenisMobilAttribute()
    {
        return $this->jenis_kendaraan ?: ($this->merk_tipe ?: $this->jenis_peruntukan);
    }

    public function getLokasiAttribute()
    {
        return $this->pos;
    }
}
