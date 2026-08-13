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
        'unit_id',
        'no_pol',
        'no_lambung',
        'jenis_mobil',
        'lokasi',
        'kode_rekening',
        'tahun_anggaran',
        'subtotal',
        'total_biaya',
        'status',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_invoice' => 'date',
        'subtotal' => 'decimal:2',
        'total_biaya' => 'decimal:2',
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
        $subtotal = $this->items()->sum('total_biaya');

        $this->subtotal = $subtotal;
        $this->total_biaya = $subtotal; // tambahkan pajak/potongan di sini jika diperlukan
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
