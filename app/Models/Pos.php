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
        return $this->personil_pemadam + $this->personil_rescue + $this->personil_cc;
    }

    /**
     * Total Unit Operasional di Pos ini.
     */
    public function getTotalUnitAttribute(): int
    {
        return $this->unit_truck_pancar
            + $this->unit_motor_roda3
            + $this->unit_motor_roda2
            + $this->unit_pompa
            + $this->unit_rescue
            + $this->unit_water_supply
            + $this->unit_lainnya;
    }
}
