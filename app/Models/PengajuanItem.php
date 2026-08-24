<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanItem extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_items';

    protected $fillable = [
        'pengajuan_id',
        'deskripsi_kerusakan',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }
}