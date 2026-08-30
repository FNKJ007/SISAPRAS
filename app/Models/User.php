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
            if ($user->nip) {
                $user->nip = preg_replace('/\s+/', '', trim($user->nip));
            }
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

        /**
         * Auto-sync: Saat data pegawai diupdate (nama/NIP), semua data denormalisasi
         * di tabel lain (regus, pengajuans, cek_harian_units, cek_harian_alats)
         * ikut terupdate secara otomatis.
         */
        static::updated(function ($user) {
            $nameChanged = $user->wasChanged('name');
            $nipChanged  = $user->wasChanged('nip');

            if (!$nameChanged && !$nipChanged) {
                return;
            }

            $newName = $user->name;
            $newNip  = $user->nip;
            $oldName = $user->getOriginal('name');
            $oldNip  = $user->getOriginal('nip');

            // ── 1. Sync tabel `regus` (danru & nip_danru) ──
            // Match by danru_user_id FK first
            Regu::where('danru_user_id', $user->id)->update([
                'danru'     => $newName,
                'nip_danru' => $newNip,
            ]);
            // Fallback: match by old NIP (for legacy records without FK)
            if ($oldNip) {
                $cleanOldNip = preg_replace('/\s+/', '', trim($oldNip));
                Regu::whereNull('danru_user_id')
                    ->whereRaw("REPLACE(nip_danru, ' ', '') = ?", [$cleanOldNip])
                    ->update([
                        'danru'         => $newName,
                        'nip_danru'     => $newNip,
                        'danru_user_id' => $user->id,
                    ]);
            }

            // ── 2. Sync tabel `pengajuans` ──
            // 2a. Sebagai Komandan Regu (Danru)
            Pengajuan::where('danru_user_id', $user->id)->update([
                'nama_komandan_regu' => $newName,
                'nip_komandan_regu'  => $newNip,
            ]);
            if ($oldNip) {
                $cleanOldNip = preg_replace('/\s+/', '', trim($oldNip));
                Pengajuan::whereNull('danru_user_id')
                    ->whereRaw("REPLACE(nip_komandan_regu, ' ', '') = ?", [$cleanOldNip])
                    ->update([
                        'nama_komandan_regu' => $newName,
                        'nip_komandan_regu'  => $newNip,
                        'danru_user_id'      => $user->id,
                    ]);
            }

            // 2b. Sebagai Pemegang unit (user_id)
            Pengajuan::where('user_id', $user->id)->update([
                'nama_pemegang' => $newName,
                'nip_pemegang'  => $newNip,
            ]);

            // 2c. Sebagai Kepala Bidang (kabid_user_id)
            Pengajuan::where('kabid_user_id', $user->id)->update([
                'nama_kepala_bidang' => $newName,
                'nip_kepala_bidang'  => $newNip,
            ]);

            // ── 3. Sync tabel `cek_harian_units` & `cek_harian_alats` ──
            // 3a. Sebagai pemeriksa (user_id)
            if (class_exists(CekHarianUnit::class)) {
                CekHarianUnit::where('user_id', $user->id)->update([
                    'nama_pemeriksa' => $newName,
                ]);
            }
            if (class_exists(CekHarianAlat::class)) {
                CekHarianAlat::where('user_id', $user->id)->update([
                    'nama_pemeriksa' => $newName,
                ]);
            }

            // 3b. Sebagai danru di cek harian (match by old name)
            if ($nameChanged && $oldName) {
                $tables = ['cek_harian_units', 'cek_harian_alats'];
                foreach ($tables as $table) {
                    if (\Schema::hasTable($table)) {
                        \DB::table($table)
                            ->where('nama_danru', $oldName)
                            ->update(['nama_danru' => $newName]);
                    }
                }
            }

            // 3c. Sebagai kabid di cek harian (match by old name)
            if ($nameChanged && $oldName) {
                $tables = ['cek_harian_units', 'cek_harian_alats'];
                foreach ($tables as $table) {
                    if (\Schema::hasTable($table)) {
                        \DB::table($table)
                            ->where('nama_kabid', $oldName)
                            ->update(['nama_kabid' => $newName]);
                    }
                }
            }

            // Invalidate cache
            if (class_exists(\App\Services\CacheService::class)) {
                \App\Services\CacheService::invalidate(['user', 'regu', 'pengajuan', 'cek_unit', 'cek_alat']);
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