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
        'pos',
        'merk_tipe',
        'tahun_pembuatan',
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
}
