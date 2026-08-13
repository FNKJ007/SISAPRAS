<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bidang',
        'pos',
        'regu',
        'jenis_kendaraan',
        'nomor_lambung',
        'item_perbaikan',
        'item_verifikasis',
        'nama_pemegang',
        'nip_pemegang',
        'nama_komandan_regu',
        'nip_komandan_regu',
        'nama_kepala_bidang',
        'nip_kepala_bidang',
        'status',
        'tanggal_keberangkatan',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_keberangkatan' => 'date',
        'item_verifikasis'      => 'array',
    ];

    public static array $bidangMap = [
        'pemadam'    => 'Pemadam',
        'rescue'     => 'Rescue',
        'pencegahan' => 'Pencegahan',
        'spi'        => 'SPI',
    ];

    public static array $posMap = [
        'baleendah'   => 'Baleendah',
        'cicalengka'  => 'Cicalengka',
        'cileunyi'    => 'Cileunyi',
        'ciparay'     => 'Ciparay',
        'majalaya'    => 'Majalaya',
        'margaasih'   => 'Margaasih (TKI)',
        'ciwidey'     => 'Ciwidey (PACIRA)',
        'pangalengan' => 'Pangalengan',
        'soreang'     => 'Soreang (MAKO)',
        'pencegahan'  => 'Pencegahan',
        'spi'         => 'SPI',
    ];

    public static array $reguMap = [
        'pemadam1'    => 'Regu Pemadam 1',
        'pemadam2'    => 'Regu Pemadam 2',
        'rescue1'     => 'Regu Rescue 1',
        'rescue2'     => 'Regu Rescue 2',
        'pencegahan1' => 'Regu Pencegahan',
        'spi1'        => 'SPI',
    ];

    public static array $jenisKendaraanMap = [
        'pancar'   => 'Pancar',
        'pompa'    => 'Pompa',
        'rescueK'  => 'Rescue',
        'tangki'   => 'Water supply/tangki',
        'komando'  => 'Komando',
        'motor1'   => 'Motor roda dua',
        'motor2'   => 'Motor roda tiga',
    ];

    public static array $nomorLambungMap = [
        'p01'      => 'P-01 / D 8518 V',
        'p02'      => 'P-02 / D 9923 Z',
        'p03'      => 'P-03 / NKR81-7000441',
        'p04'      => 'P-04 / D 9429 V',
        'p05'      => 'P-05 / D 9921 V',
        'p06'      => 'P-06 / D 9932 V',
        'p07'      => 'P-07 / D 9914 V',
        'p08'      => 'P-08 / D 9060 V',
        'p09'      => 'P-09 / D 9920 Z',
        'p10'      => 'P-10 / D 8559 V',
        'p11'      => 'P-11 / D 9958 Y',
        'p12'      => 'P-12 / D 9957 Y',
        'r01'      => 'R-01 / D 9933 V',
        'r02'      => 'R-02 / NKR816-7000009',
        'r03'      => 'R-03 / NKR71G-7403639',
        'r04'      => 'R-04 / D 9964 Y',
        's01'      => 'S-01 / D 8517 V',
        's02'      => 'S-02 / D 8516 V',
        'mp01'     => 'MP-01 / D 3296 Z',
        'mp02'     => 'MP-02 / D 3297 Z',
        'mp03'     => 'MP-03 / D 5650 V (SOREANG)',
        'mp04'     => 'MP-04 / D 5061 V (BALEENDAH)',
        'd8507v'   => 'D 8507 V',
        'd8508v'   => 'D 8508 V',
        'lainlain' => 'LAIN-LAIN',
    ];

    /**
     * Accessor untuk memecah string item_perbaikan menjadi array item
     */
    public function getItemListAttribute(): array
    {
        if (empty($this->item_perbaikan)) {
            return [];
        }
        $items = preg_split('/[,;\n\r]+/', $this->item_perbaikan);
        return array_values(array_filter(array_map('trim', $items)));
    }

    /**
     * Accessor untuk Bidang
     */
    public function getBidangAttribute($value)
    {
        return self::$bidangMap[$value] ?? $value;
    }

    /**
     * Accessor untuk Pos
     */
    public function getPosAttribute($value)
    {
        return self::$posMap[$value] ?? $value;
    }

    /**
     * Accessor untuk Regu
     */
    public function getReguAttribute($value)
    {
        return self::$reguMap[$value] ?? $value;
    }

    /**
     * Accessor untuk Jenis Kendaraan
     */
    public function getJenisKendaraanAttribute($value)
    {
        return self::$jenisKendaraanMap[$value] ?? $value;
    }

    /**
     * Accessor untuk Nomor Lambung
     */
    public function getNomorLambungAttribute($value)
    {
        return self::$nomorLambungMap[$value] ?? $value;
    }

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
