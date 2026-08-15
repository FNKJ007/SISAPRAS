<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kejadian extends Model
{
    use HasFactory;

    protected $table = 'kejadian';

    protected $fillable = [
        'kode_kejadian',
        'waktu_kejadian',
        'jenis_kejadian',
        'kategori_detail',
        'lokasi',
        'kecamatan',
        'kelurahan',
        'nama_pelapor',
        'no_hp_pelapor',
        'objek_terdampak',
        'penyebab',
        'pos_regu',
        'komandan_regu',
        'unit_armada',
        'estimasi_kerugian',
        'korban_luka',
        'korban_jiwa',
        'waktu_terima_laporan',
        'waktu_berangkat',
        'waktu_tiba',
        'waktu_selesai',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'waktu_kejadian' => 'datetime',
    ];
}