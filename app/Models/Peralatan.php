<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peralatan extends Model
{
    use HasFactory;

    protected $table = 'peralatans';

    protected $fillable = [
        'nama',
        'kategori',
        'kode_alat',
        'jumlah_total',
        'kondisi_baik',
        'kondisi_rusak',
        'satuan',
        'lokasi',
        'status',
        'catatan',
    ];

    public static array $kategoriMap = [
        'pemadam'        => 'Pemadam',
        'rescue'         => 'Rescue',
        'command_center' => 'Command Center',
    ];

    public static array $statusMap = [
        'baik'            => 'Baik',
        'perlu_perhatian' => 'Perlu Perhatian',
        'rusak'           => 'Rusak',
    ];
}
