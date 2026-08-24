<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kategori',
        'nomor_lambung',
        'plat_nomor',
        'no_rangka_mesin',
        'merk_tipe',
        'tahun_pembuatan',
        'cc',
        'jenis_kendaraan',
        'peruntukan',
        'jenis_peruntukan',
        'pos',
        'bidang_id',
        'pos_id',
        'pengemudi_utama_id',
        'pengemudi_cadangan_id',
        'pengemudi_1',
        'pengemudi_2',
        'status',
        'catatan',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($unit) {
            if ($unit->kategori) {
                $unit->kategori = ucwords(strtolower(str_replace('_', ' ', trim($unit->kategori))));
            }
            if ($unit->jenis_kendaraan) {
                $jk = trim($unit->jenis_kendaraan);
                $unit->jenis_kendaraan = in_array(strtoupper($jk), ['R2', 'R3', 'R4'])
                    ? strtoupper($jk)
                    : ucwords(strtolower($jk));
            }
            if ($unit->peruntukan) {
                $unit->peruntukan = ucwords(strtolower(trim($unit->peruntukan)));
            }
            if ($unit->jenis_kendaraan || $unit->peruntukan) {
                $parts = array_filter([$unit->jenis_kendaraan, $unit->peruntukan]);
                $unit->jenis_peruntukan = implode('/', $parts);
            }
        });
    }

    public static array $kategoriMap = [
        'pemadam'    => 'Pemadam',
        'rescue'     => 'Rescue',
        'pencegahan' => 'Pencegahan',
        'komando'    => 'Komando',
    ];

    public static array $statusMap = [
        'aktif'     => 'Aktif',
        'perbaikan' => 'Dalam Perbaikan',
        'nonaktif'  => 'Non-Aktif',
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

    public function pengemudiUtama()
    {
        return $this->belongsTo(User::class, 'pengemudi_utama_id');
    }

    public function pengemudiCadangan()
    {
        return $this->belongsTo(User::class, 'pengemudi_cadangan_id');
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class, 'unit_id');
    }

    public function cekHarianUnits()
    {
        return $this->hasMany(CekHarianUnit::class, 'unit_id');
    }
}