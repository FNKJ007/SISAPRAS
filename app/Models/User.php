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
        'no_hp',
        'status',
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

    /**
     * Cek apakah user adalah admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class);
    }

    public function cekHarianUnits()
    {
        return $this->hasMany(CekHarianUnit::class);
    }

    public function cekHarianAlats()
    {
        return $this->hasMany(CekHarianAlat::class);
    }
}
