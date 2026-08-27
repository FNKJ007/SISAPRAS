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
        });
    }

    /**
     * Dynamic accessor for backward compatibility (jenis_kendaraan / peruntukan).
     */
    public function getJenisPeruntukanAttribute(): string
    {
        $parts = array_filter([$this->jenis_kendaraan, $this->peruntukan]);
        return implode('/', $parts);
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

    /**
     * Sinkronisasi status armada unit secara otomatis berdasarkan data Pengajuan Pemeliharaan aktual.
     * Aturan:
     * 1. Jika pengajuan 'disetujui' dengan tanggal_keberangkatan di masa depan (> hari ini),
     *    unit belum masuk bengkel sehingga status tetap 'aktif' (Ready).
     * 2. Jika tanggal_keberangkatan <= hari ini (sudah masuk jadwal) dan belum selesai,
     *    unit berubah status menjadi 'perbaikan' (Dalam Perbaikan).
     * 3. Jika status pengerjaan 'selesai' atau pengajuan ditolak/menunggu, status kembali 'aktif'.
     */
    protected static bool $hasSyncedInRequest = false;

    /**
     * Sinkronisasi status armada unit secara otomatis berdasarkan data Pengajuan Pemeliharaan aktual.
     */
    public static function syncStatusAll(bool $force = false): void
    {
        if (!$force && static::$hasSyncedInRequest) {
            return;
        }
        static::$hasSyncedInRequest = true;

        $today = now()->format('Y-m-d');

        // 1. Sinkronisasi status_pengerjaan pada Pengajuan yang disetujui berdasarkan tanggal keberangkatan
        $approvedPengajuans = Pengajuan::where('status', 'disetujui')
            ->where('status_pengerjaan', '!=', 'selesai')
            ->get(['id', 'unit_id', 'nomor_lambung', 'status_pengerjaan', 'tanggal_keberangkatan', 'progress_persen']);

        foreach ($approvedPengajuans as $p) {
            $tglBerangkat = $p->tanggal_keberangkatan ? $p->tanggal_keberangkatan->format('Y-m-d') : null;
            if ($tglBerangkat) {
                if ($tglBerangkat <= $today && $p->status_pengerjaan === 'belum_mulai') {
                    $p->status_pengerjaan = 'proses';
                    if ((int)$p->progress_persen === 0) {
                        $p->progress_persen = 10;
                    }
                    $p->saveQuietly();
                } elseif ($tglBerangkat > $today && $p->status_pengerjaan === 'proses') {
                    // Jadwal masih besok / masa depan, belum masuk bengkel fisik
                    $p->status_pengerjaan = 'belum_mulai';
                    $p->progress_persen = 0;
                    $p->saveQuietly();
                }
            }
        }

        // 2. Tentukan unit mana saja yang saat ini BENAR-BENAR sudah masuk bengkel (tanggal_keberangkatan <= hari ini)
        $activeRepairPengajuans = $approvedPengajuans->filter(function ($p) use ($today) {
            $tglBerangkat = $p->tanggal_keberangkatan ? $p->tanggal_keberangkatan->format('Y-m-d') : null;
            if ($tglBerangkat && $tglBerangkat <= $today) {
                return true;
            }
            if (!$tglBerangkat && $p->status_pengerjaan === 'proses') {
                return true;
            }
            return false;
        });

        $repairUnitIds = $activeRepairPengajuans->pluck('unit_id')->filter()->unique()->toArray();
        $repairNomorLambungs = $activeRepairPengajuans->pluck('nomor_lambung')
            ->filter()
            ->map(fn($n) => strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $n))))
            ->unique()
            ->toArray();

        $units = static::where('status', '!=', 'nonaktif')->get(['id', 'nomor_lambung', 'status']);
        foreach ($units as $unit) {
            $cleanLambung = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $unit->nomor_lambung ?? '')));
            $isInRepair = in_array($unit->id, $repairUnitIds, true) 
                || in_array($cleanLambung, $repairNomorLambungs, true);

            $targetStatus = $isInRepair ? 'perbaikan' : 'aktif';

            if ($unit->status !== $targetStatus) {
                $unit->status = $targetStatus;
                $unit->saveQuietly();
            }
        }
    }
}