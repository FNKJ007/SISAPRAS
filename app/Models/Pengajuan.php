<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'unit_id',
        'bidang_id',
        'pos_id',
        'regu_id',
        'danru_user_id',
        'kabid_user_id',
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
        'status_pengerjaan',
        'tanggal_mulai_pengerjaan',
        'tanggal_selesai_pengerjaan',
        'progress_persen',
        'progress_catatan',
    ];

    protected $casts = [
        'tanggal_keberangkatan'      => 'date:Y-m-d',
        'item_verifikasis'           => 'array',
        'tanggal_mulai_pengerjaan'   => 'date:Y-m-d',
        'tanggal_selesai_pengerjaan' => 'date:Y-m-d',
    ];

    public static array $statusPengerjaanMap = [
        'belum_mulai' => 'Belum Mulai',
        'proses'      => 'Dalam Pengerjaan',
        'selesai'     => 'Selesai',
    ];

    public static array $bidangMap = [
        'pemadam'    => 'Pemadam',
        'rescue'     => 'Rescue',
        'pencegahan' => 'Pencegahan',
        'spi'        => 'SPI',
    ];

    public static array $jenisKendaraanMap = [
        'pancaran'       => 'Pancaran',
        'water_supply'   => 'Water Supply',
        'quick_response' => 'Quick Response',
        'komando'        => 'Komando',
        'rescue'         => 'Rescue',
        'r2'             => 'R2',
        'r3'             => 'R3',
    ];

    public static array $statusMap = [
        'menunggu'  => 'Menunggu',
        'disetujui' => 'Disetujui',
        'ditolak'   => 'Ditolak',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($pengajuan) {
            // Auto-clean nomor lambung e.g. p01 -> P-01
            if (!empty($pengajuan->nomor_lambung)) {
                $raw = trim($pengajuan->nomor_lambung);
                if (preg_match('/^([a-zA-Z]+)[-_ ]*(\d+)$/', $raw, $m)) {
                    $pengajuan->nomor_lambung = strtoupper($m[1]) . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT);
                }
            }

            // Auto-clean pos
            if (!empty($pengajuan->pos)) {
                $cleanPos = preg_replace('/[^a-z0-9]/', '', strtolower($pengajuan->pos));
                if (str_contains($cleanPos, 'soreang') || str_contains($cleanPos, 'mako')) {
                    $pengajuan->pos = 'Soreang (MAKO)';
                } elseif (str_contains($cleanPos, 'ciwidey') || str_contains($cleanPos, 'pacira')) {
                    $pengajuan->pos = 'Ciwidey (PACIRA)';
                } elseif (str_contains($cleanPos, 'margaasih') || str_contains($cleanPos, 'tki')) {
                    $pengajuan->pos = 'Margaasih (TKI)';
                } else {
                    $pengajuan->pos = ucwords(strtolower(trim($pengajuan->pos)));
                }
            }

            // Auto-clean bidang
            if (!empty($pengajuan->bidang)) {
                $cleanB = strtolower(trim($pengajuan->bidang));
                if ($cleanB === 'spi' || str_contains($cleanB, 'sarana') || str_contains($cleanB, 'informasi')) {
                    $pengajuan->bidang = 'Sarana Dan Informasi';
                } elseif ($cleanB === 'cc' || str_contains($cleanB, 'command')) {
                    $pengajuan->bidang = 'Command Center';
                } else {
                    $pengajuan->bidang = ucwords($cleanB);
                }
            }

            // Auto-clean regu
            if (!empty($pengajuan->regu)) {
                $cleanR = strtolower(trim($pengajuan->regu));
                if (preg_match('/regu[_\s]*([0-9]+)/i', $cleanR, $m)) {
                    $num = (int)$m[1];
                    $pengajuan->regu = $num > 0 ? "Regu {$num}" : "Regu 1";
                } else {
                    $pengajuan->regu = ucwords($cleanR);
                }
            }

            // Auto-clean names
            if (!empty($pengajuan->nama_pemegang)) {
                $pengajuan->nama_pemegang = ucwords(strtolower(trim($pengajuan->nama_pemegang)));
            }
            if (!empty($pengajuan->nama_komandan_regu)) {
                $pengajuan->nama_komandan_regu = ucwords(strtolower(trim($pengajuan->nama_komandan_regu)));
            }
            if (!empty($pengajuan->nama_kepala_bidang)) {
                $parts = explode(',', $pengajuan->nama_kepala_bidang);
                $name = ucwords(strtolower(trim($parts[0])));
                if (count($parts) > 1) {
                    $gelar = implode(',', array_slice($parts, 1));
                    $pengajuan->nama_kepala_bidang = $name . ',' . $gelar;
                } else {
                    $pengajuan->nama_kepala_bidang = $name;
                }
            }
        });

        static::created(function ($pengajuan) {
            if (!empty($pengajuan->item_perbaikan)) {
                $rawItems = preg_split('/[,;\n\r]+/', $pengajuan->item_perbaikan);
                foreach ($rawItems as $itemText) {
                    $clean = trim($itemText);
                    if ($clean) {
                        PengajuanItem::create([
                            'pengajuan_id'        => $pengajuan->id,
                            'deskripsi_kerusakan' => ucwords(strtolower($clean)),
                        ]);
                    }
                }
            }
        });

        static::saved(function () {
            Unit::syncStatusAll();
        });

        static::deleted(function () {
            Unit::syncStatusAll();
        });
    }

    // Accessors for Title Case and Official Master Data Formats
    public function getNomorLambungAttribute($value)
    {
        if (!$value) return $value;
        $raw = trim($value);
        if (preg_match('/^([a-zA-Z]+)[-_ ]*(\d+)$/', $raw, $m)) {
            return strtoupper($m[1]) . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT);
        }
        return strtoupper($raw);
    }

    public function getPosAttribute($value)
    {
        if (!$value) return $value;
        $clean = preg_replace('/[^a-z0-9]/', '', strtolower($value));
        if (str_contains($clean, 'soreang') || str_contains($clean, 'mako')) {
            return 'Soreang (MAKO)';
        }
        if (str_contains($clean, 'ciwidey') || str_contains($clean, 'pacira')) {
            return 'Ciwidey (PACIRA)';
        }
        if (str_contains($clean, 'margaasih') || str_contains($clean, 'tki')) {
            return 'Margaasih (TKI)';
        }
        return ucwords(strtolower($value));
    }

    public function getBidangAttribute($value)
    {
        if (!$value) return $value;
        $clean = strtolower(trim($value));
        if ($clean === 'spi' || str_contains($clean, 'sarana') || str_contains($clean, 'informasi')) {
            return 'Sarana Dan Informasi';
        }
        if ($clean === 'cc' || str_contains($clean, 'command')) {
            return 'Command Center';
        }
        return ucwords($clean);
    }

    public function getReguAttribute($value)
    {
        if (!$value) return 'Regu 1';
        $clean = strtolower(trim($value));
        if (preg_match('/regu[_\s]*([0-9]+)/i', $clean, $m)) {
            $num = (int)$m[1];
            return $num > 0 ? "Regu {$num}" : "Regu 1";
        }
        return ucwords($clean);
    }

    public function getNamaPemegangAttribute($value)
    {
        return $value ? ucwords(strtolower(trim($value))) : $value;
    }

    public function getNamaKomandanReguAttribute($value)
    {
        return $value ? ucwords(strtolower(trim($value))) : $value;
    }

    public function getNamaKepalaBidangAttribute($value)
    {
        if (!$value) return $value;
        $parts = explode(',', $value);
        $name = ucwords(strtolower(trim($parts[0])));
        if (count($parts) > 1) {
            $gelar = implode(',', array_slice($parts, 1));
            return $name . ',' . $gelar;
        }
        return $name;
    }

    public function getVerifiedItemListAttribute(): array
    {
        $cleanItems = [];

        // 1. Prioritas: Ambil item yang diverifikasi 'disetujui' jika ada
        if (!empty($this->item_verifikasis) && is_array($this->item_verifikasis)) {
            foreach ($this->item_verifikasis as $itemName => $vStatus) {
                if ($vStatus === 'disetujui') {
                    $cleanItems[] = trim($itemName);
                }
            }
        }

        // 2. Fallback: Relasi tabel items (PengajuanItem)
        if (empty($cleanItems)) {
            if ($this->relationLoaded('items') ? $this->items->isNotEmpty() : $this->items()->exists()) {
                $cleanItems = $this->items->pluck('deskripsi_kerusakan')->filter()->toArray();
            }
        }

        // 3. Fallback: item_list array
        if (empty($cleanItems) && !empty($this->item_list) && is_array($this->item_list)) {
            $cleanItems = $this->item_list;
        }

        // 4. Fallback: item_perbaikan string (dipisah koma, titik koma, baris baru)
        if (empty($cleanItems) && !empty($this->item_perbaikan)) {
            $cleanItems = preg_split('/[,;\r\n]+/', $this->item_perbaikan);
        }

        return array_values(array_filter(array_map('trim', $cleanItems)));
    }

    public function getPosLabelAttribute(): string
    {
        return $this->pos ?? ($this->unitRelasi?->pos ?? 'Pos Dinas');
    }

    public function getJenisKendaraanLabelAttribute(): string
    {
        return $this->jenis_kendaraan ?? ($this->unitRelasi?->merk_tipe ?? 'Unit Operasional');
    }

    public function getKodeVerifikasiAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        $datePart = $this->created_at ? $this->created_at->format('Ymd') : date('Ymd');
        return 'HAR-' . $datePart . '-' . sprintf('%04d', $this->id ?? 1);
    }

    // 3NF Relationships
    public function items()
    {
        return $this->hasMany(PengajuanItem::class, 'pengajuan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unitRelasi()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

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

    public function danruUser()
    {
        return $this->belongsTo(User::class, 'danru_user_id');
    }

    public function kabidUser()
    {
        return $this->belongsTo(User::class, 'kabid_user_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'pengajuan_id');
    }
}