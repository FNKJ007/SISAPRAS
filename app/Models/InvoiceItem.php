<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'tanggal',
        'kode_item',
        'jenis_perbaikan',
        'vol',
        'satuan',
        'harga_satuan',
        'potongan_persen',
        'total_biaya',
    ];

    protected $casts = [
        'tanggal'         => 'date',
        'vol'             => 'decimal:2',
        'harga_satuan'    => 'decimal:2',
        'potongan_persen' => 'decimal:2',
        'total_biaya'     => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
