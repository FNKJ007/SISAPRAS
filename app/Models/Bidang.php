<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    use HasFactory;

    protected $table = 'bidangs';

    protected $fillable = [
        'kode',
        'nama',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'bidang_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'bidang_id');
    }

    public function peralatans()
    {
        return $this->hasMany(Peralatan::class, 'bidang_id');
    }

    public function regus()
    {
        return $this->hasMany(Regu::class, 'bidang_id');
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class, 'bidang_id');
    }
}