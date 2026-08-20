<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlokasiKebersihanUnit extends Model
{
    use HasFactory;

    protected $table = 'alokasi_kebersihan_units';

    protected $fillable = [
        'unit_id',
        'tahun',
        'sabun_cuci',
        'lap_handuk',
        'kanebo',
        'semir_ban',
        'sikat_ban',
        'pengharum',
        'catatan',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}