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
        'jenis_peruntukan',
        'pos',
        'pengemudi_1',
        'pengemudi_2',
        'status',
        'catatan',
    ];

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
}
