<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regu extends Model
{
    use HasFactory;

    protected $table = 'regus';

    protected $fillable = [
        'nama',
        'pos',
        'bidang',
        'danru',
        'nip_danru',
        'status',
        'catatan',
    ];

    /**
     * Anggota personil yang bertugas pada regu & pos ini.
     */
    public function anggotas()
    {
        return $this->hasMany(User::class, 'regu', 'nama')
            ->where(function ($q) {
                if ($this->pos) {
                    $q->where('pos', 'LIKE', "%{$this->pos}%");
                }
            });
    }

    /**
     * Hitung jumlah anggota aktif dalam regu ini.
     */
    public function getTotalAnggotaAttribute(): int
    {
        $query = User::where('status', 'aktif')
            ->where('regu', $this->nama);

        if (!empty($this->pos)) {
            $query->where('pos', 'LIKE', "%{$this->pos}%");
        }

        if (!empty($this->bidang)) {
            $query->where('bidang', 'LIKE', "%{$this->bidang}%");
        }

        return $query->count();
    }
}