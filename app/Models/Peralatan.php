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

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($peralatan) {
            if ($peralatan->kategori) {
                $peralatan->kategori = ucwords(strtolower(str_replace('_', ' ', trim($peralatan->kategori))));
            }
        });
    }

    public static array $kategoriMap = [
        'Pemadam'        => 'Pemadam',
        'Rescue'         => 'Rescue',
        'Command Center' => 'Command Center',
    ];

    public static array $statusMap = [
        'baik'            => 'Baik',
        'perlu_perhatian' => 'Perlu Perhatian',
        'rusak'           => 'Rusak',
    ];
}
