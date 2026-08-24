<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pos extends Model
{
    use HasFactory;

    protected $table = 'pos';

    protected $fillable = [
        'nama',
        'kode_pos',
        'personil_pemadam',
        'personil_rescue',
        'personil_cc',
        'unit_truck_pancar',
        'unit_motor_roda3',
        'unit_motor_roda2',
        'unit_pompa',
        'unit_rescue',
        'unit_water_supply',
        'unit_lainnya',
        'alamat',
        'wilayah',
        'telepon',
        'status',
        'catatan',
    ];

    public static array $statusMap = [
        'aktif'    => 'Aktif (Siaga)',
        'nonaktif' => 'Non-Aktif',
    ];

    /**
     * Total Personil di Pos ini.
     */
    public function getTotalPersonilAttribute(): int
    {
        return (int) $this->personil_pemadam + (int) $this->personil_rescue + (int) $this->personil_cc;
    }

    /**
     * Hitungan Dinamis Unit Operasional dari Data Unit.
     */
    public function getUnitTruckPancarAttribute(): int
    {
        return Unit::where('pos', 'LIKE', $this->nama)->where('jenis_kendaraan', 'LIKE', 'Pancar')->count();
    }

    public function getUnitMotorRoda3Attribute(): int
    {
        return Unit::where('pos', 'LIKE', $this->nama)->where('jenis_kendaraan', 'LIKE', 'R3')->count();
    }

    public function getUnitMotorRoda2Attribute(): int
    {
        return Unit::where('pos', 'LIKE', $this->nama)->where('jenis_kendaraan', 'LIKE', 'R2')->count();
    }

    public function getUnitPompaAttribute(): int
    {
        return Unit::where('pos', 'LIKE', $this->nama)->where('jenis_kendaraan', 'LIKE', 'Pompa')->count();
    }

    public function getUnitRescueAttribute(): int
    {
        return Unit::where('pos', 'LIKE', $this->nama)->where('jenis_kendaraan', 'LIKE', 'Rescue')->count();
    }

    public function getUnitWaterSupplyAttribute(): int
    {
        return Unit::where('pos', 'LIKE', $this->nama)->where('jenis_kendaraan', 'LIKE', 'Supply')->count();
    }

    public function getUnitLainnyaAttribute(): int
    {
        return Unit::where('pos', 'LIKE', $this->nama)
            ->where(function($q) {
                $q->whereNotIn('jenis_kendaraan', ['Pancar', 'R3', 'R2', 'Pompa', 'Rescue', 'Supply'])
                  ->orWhereNull('jenis_kendaraan');
            })
            ->count();
    }

    public function getUnitLainnyaListAttribute()
    {
        return Unit::where('pos', 'LIKE', $this->nama)
            ->where(function($q) {
                $q->whereNotIn('jenis_kendaraan', ['Pancar', 'R3', 'R2', 'Pompa', 'Rescue', 'Supply'])
                  ->orWhereNull('jenis_kendaraan');
            })
            ->get(['id', 'nomor_lambung', 'plat_nomor', 'nama', 'jenis_kendaraan', 'merk_tipe']);
    }

    /**
     * Total Unit Operasional di Pos ini.
     */
    public function getTotalUnitAttribute(): int
    {
        return Unit::where('pos', 'LIKE', $this->nama)->count();
    }

    // 3NF Relationships
    public function units()
    {
        return $this->hasMany(Unit::class, 'pos_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'pos_id');
    }

    public function regus()
    {
        return $this->hasMany(Regu::class, 'pos_id');
    }

    public function peralatans()
    {
        return $this->hasMany(Peralatan::class, 'pos_id');
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class, 'pos_id');
    }
}
