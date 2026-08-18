<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_invoice',
        'tanggal_invoice',
        'nama_bengkel',
        'unit_id',
        'no_pol',
        'no_lambung',
        'jenis_mobil',
        'lokasi',
        'kode_rekening',
        'tahun_anggaran',
        'subtotal',
        'potongan',
        'pajak',
        'biaya_lain',
        'total_biaya',
        'status',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_invoice' => 'date',
        'subtotal'        => 'decimal:2',
        'potongan'        => 'decimal:2',
        'pajak'           => 'decimal:2',
        'biaya_lain'      => 'decimal:2',
        'total_biaya'     => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Hitung ulang subtotal & total_biaya dari item, lalu simpan.
     */
    public function recalculateTotals(): void
    {
        $subtotal  = (float) $this->items()->sum('total_biaya');
        $potongan  = (float) ($this->potongan ?? 0);
        $pajak     = (float) ($this->pajak ?? 0);
        $biayaLain = (float) ($this->biaya_lain ?? 0);

        $this->subtotal = $subtotal;
        $this->total_biaya = max(0, $subtotal - $potongan + $pajak + $biayaLain);
        $this->save();
    }

    /**
     * Generate nomor invoice otomatis: INV/{tahun}/{bulan romawi}/{urutan}
     */
    public static function generateNomorInvoice(?\DateTimeInterface $tanggal = null): string
    {
        $tanggal = $tanggal ?? now();
        $romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        $countThisMonth = static::whereYear('tanggal_invoice', $tanggal->format('Y'))
            ->whereMonth('tanggal_invoice', $tanggal->format('n'))
            ->count();

        $urutan = str_pad((string) ($countThisMonth + 1), 4, '0', STR_PAD_LEFT);

        return sprintf(
            'INV/%s/%s/%s',
            $tanggal->format('Y'),
            $romawi[(int) $tanggal->format('n')],
            $urutan
        );
    }
}
