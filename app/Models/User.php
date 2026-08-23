<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nip',
        'email',
        'role',
        'jabatan',
        'bidang',
        'pos',
        'regu',
        'bidang_id',
        'pos_id',
        'regu_id',
        'no_hp',
        'status',
        'has_account',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'has_account'       => 'boolean',
            'password'          => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            if ($user->bidang) {
                $user->bidang = ucwords(strtolower(trim($user->bidang)));
            }
            if ($user->regu) {
                $user->regu = ucwords(strtolower(trim($user->regu)));
            }
            if ($user->jabatan) {
                $user->jabatan = ucwords(strtolower(trim($user->jabatan)));
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    // 3NF Relationships
    public function bidangRelasi()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function posRelasi()
    {
        return $this->belongsTo(Pos::class, 'pos_id');
    }

    public function reguRelasi()
    {
        return $this->belongsTo(Regu::class, 'regu_id');
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class, 'user_id');
    }
}