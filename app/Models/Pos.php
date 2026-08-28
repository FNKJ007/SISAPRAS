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
        if (array_key_exists('unit_truck_pancar', $this->attributes)) {
            return (int) $this->attributes['unit_truck_pancar'];
        }
        return Unit::where('pos', 'ILIKE', $this->nama)->where('jenis_kendaraan', 'ILIKE', 'Pancar')->count();
    }

    public function getUnitMotorRoda3Attribute(): int
    {
        if (array_key_exists('unit_motor_roda3', $this->attributes)) {
            return (int) $this->attributes['unit_motor_roda3'];
        }
        return Unit::where('pos', 'ILIKE', $this->nama)->where('jenis_kendaraan', 'ILIKE', 'R3')->count();
    }

    public function getUnitMotorRoda2Attribute(): int
    {
        if (array_key_exists('unit_motor_roda2', $this->attributes)) {
            return (int) $this->attributes['unit_motor_roda2'];
        }
        return Unit::where('pos', 'ILIKE', $this->nama)->where('jenis_kendaraan', 'ILIKE', 'R2')->count();
    }

    public function getUnitPompaAttribute(): int
    {
        if (array_key_exists('unit_pompa', $this->attributes)) {
            return (int) $this->attributes['unit_pompa'];
        }
        return Unit::where('pos', 'ILIKE', $this->nama)->where('jenis_kendaraan', 'ILIKE', 'Pompa')->count();
    }

    public function getUnitRescueAttribute(): int
    {
        if (array_key_exists('unit_rescue', $this->attributes)) {
            return (int) $this->attributes['unit_rescue'];
        }
        return Unit::where('pos', 'ILIKE', $this->nama)->where('jenis_kendaraan', 'ILIKE', 'Rescue')->count();
    }

    public function getUnitWaterSupplyAttribute(): int
    {
        if (array_key_exists('unit_water_supply', $this->attributes)) {
            return (int) $this->attributes['unit_water_supply'];
        }
        return Unit::where('pos', 'ILIKE', $this->nama)->where('jenis_kendaraan', 'ILIKE', 'Supply')->count();
    }

    public function getUnitLainnyaAttribute(): int
    {
        if (array_key_exists('unit_lainnya', $this->attributes)) {
            return (int) $this->attributes['unit_lainnya'];
        }
        return Unit::where('pos', 'ILIKE', $this->nama)
            ->where(function($q) {
                $q->whereNotIn('jenis_kendaraan', ['Pancar', 'R3', 'R2', 'Pompa', 'Rescue', 'Supply'])
                  ->orWhereNull('jenis_kendaraan');
            })
            ->count();
    }

    public function getUnitLainnyaListAttribute()
    {
        if (array_key_exists('unit_lainnya_list', $this->attributes)) {
            return $this->attributes['unit_lainnya_list'];
        }
        return Unit::where('pos', 'ILIKE', $this->nama)
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
        if (array_key_exists('total_unit', $this->attributes)) {
            return (int) $this->attributes['total_unit'];
        }
        return Unit::where('pos', 'ILIKE', $this->nama)->count();
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
