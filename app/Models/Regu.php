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
        'bidang_id',
        'pos_id',
        'danru_user_id',
        'status',
        'catatan',
    ];

    // 3NF Relationships
    public function posRelasi()
    {
        return $this->belongsTo(Pos::class, 'pos_id');
    }

    public function bidangRelasi()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function danruUser()
    {
        return $this->belongsTo(User::class, 'danru_user_id');
    }

    public function anggotas()
    {
        return $this->hasMany(User::class, 'regu_id');
    }

    public function getTotalAnggotaAttribute(): int
    {
        if ($this->id) {
            $count = User::where('regu_id', $this->id)->where('status', 'aktif')->count();
            if ($count > 0) return $count;
        }

        $query = User::where('status', 'aktif')->where('regu', $this->nama);
        if (!empty($this->pos)) {
            $query->where('pos', 'LIKE', "%{$this->pos}%");
        }
        return $query->count();
    }
}