@php
    $isAktual = request()->routeIs('admin.pemeliharaan.monitoring-aktual.*');
    $pageTitle = $isAktual ? 'Monitoring Aktual' : 'Monitoring Invoice';
    $routePrefix = $isAktual ? 'admin.pemeliharaan.monitoring-aktual' : 'admin.pemeliharaan.invoice';
@endphp

@extends('layouts.admin')

@section('title', 'Detail Invoice — ' . $pageTitle)

@section('content')
<style>
.invoice-show-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.invoice-show-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.invoice-print-card {
    padding: 36px 40px;
}
.invoice-meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    padding: 18px 22px;
    margin-bottom: 24px;
}
.invoice-sign-flex {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
    margin-top: 36px;
    padding-top: 10px;
}

@media (max-width: 768px) {
    .invoice-show-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
    }
    .invoice-show-actions {
        width: 100% !important;
        flex-direction: column !important;
        gap: 8px !important;
    }
    .invoice-show-actions a,
    .invoice-show-actions button {
        width: 100% !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }
    .invoice-print-card {
        padding: 18px 14px !important;
    }
    .invoice-meta-grid {
        grid-template-columns: 1fr !important;
        padding: 14px 14px !important;
        gap: 12px !important;
    }
    .invoice-sign-flex {
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
    }
}
</style>

<div class="no-print" style="margin-bottom: 24px;">
    <div class="invoice-show-header">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #64748B; margin-bottom: 6px;">
                <a href="{{ route($routePrefix . '.index') }}" style="color: #64748B; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-weight: 500;">
                    <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
                    <span>{{ $pageTitle }}</span>
                </a>
                <span style="color: #CBD5E1;">›</span>
                <span style="color: #0F172A; font-weight: 600;">Detail</span>
            </div>
            <h1 style="font-size: 22px; font-weight: 800; color: #121E4E; margin: 0;">Invoice {{ $invoice->nomor_invoice }}</h1>
        </div>
        <div class="invoice-show-actions">
            <a href="{{ route($routePrefix . '.edit', $invoice) }}" 
               style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; background: #FFFFFF; color: #1B2A6B; border: 1.5px solid #1B2A6B; border-radius: 10px; font-size: 12.5px; font-weight: 700; text-decoration: none; cursor: pointer; transition: all 0.2s;">
                <i data-lucide="pencil" style="width: 15px; height: 15px;"></i>
                <span>Edit Invoice</span>
            </a>
            <button type="button" onclick="downloadInvoicePDF()" 
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; background: #1B2A6B; color: #FFFFFF; border: none; border-radius: 10px; font-size: 12.5px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(27, 42, 107, 0.25); transition: all 0.2s;">
                <i data-lucide="download" style="width: 15px; height: 15px;"></i>
                <span>Unduh PDF</span>
            </button>
            <button type="button" onclick="window.print()" 
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; background: #FFFFFF; color: #334155; border: 1px solid #CBD5E1; border-radius: 10px; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                <i data-lucide="printer" style="width: 15px; height: 15px;"></i>
                <span>Cetak</span>
            </button>
        </div>
    </div>
</div>

<div style="background: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: 0px 18px 40px rgba(112, 144, 176, 0.08); overflow: hidden;">
    <div id="invoice-print-area" class="invoice-print-card">

        {{-- KOP SURAT RESMI --}}
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 3px double #0F172A; padding-bottom: 16px; margin-bottom: 24px; text-align: center;">
            <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo Pemkab" style="height: 65px; width: auto;" onerror="this.style.display='none'">
            <div style="flex: 1; padding: 0 16px;">
                <div style="font-size: 13px; font-weight: 700; letter-spacing: 0.5px; color: #0F172A; text-transform: uppercase;">PEMERINTAH KABUPATEN BANDUNG</div>
                <div style="font-size: 17px; font-weight: 800; color: #C0201F; text-transform: uppercase; margin: 2px 0;">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                <div style="font-size: 11.5px; color: #64748B; font-weight: 500;">Bidang Sarana, Prasarana dan Informasi — Seksi Pemeliharaan Sarana dan Prasarana</div>
            </div>
            <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Damkar" style="height: 65px; width: auto;" onerror="this.style.display='none'">
        </div>

        {{-- JUDUL DOKUMEN --}}
        <div style="text-align: center; margin-bottom: 24px;">
            <h2 style="font-size: 16px; font-weight: 800; text-transform: uppercase; color: #1B2A6B; text-decoration: underline; letter-spacing: 0.5px; margin: 0 0 4px 0;">INVOICE PEMELIHARAAN KENDARAAN</h2>
            <div style="font-size: 12.5px; font-weight: 600; color: #64748B;">Nomor: {{ $invoice->nomor_invoice }}</div>
        </div>

        {{-- DATA INVOICE & UNIT --}}
        <div class="invoice-meta-grid">
            <div>
                <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                    <tr>
                        <td style="width: 130px; padding: 5px 0; color: #64748B; font-weight: 500;">Bengkel / Rekanan</td>
                        <td style="padding: 5px 0; font-weight: 700; color: #1B2A6B;">: {{ $invoice->nama_bengkel ?: 'CV. PRATAMA MOTOR' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #64748B; font-weight: 500;">No. Invoice</td>
                        <td style="padding: 5px 0; font-weight: 700; color: #0F172A;">: {{ $invoice->nomor_invoice }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #64748B; font-weight: 500;">Tanggal Invoice</td>
                        <td style="padding: 5px 0; font-weight: 600; color: #0F172A;">: {{ $invoice->tanggal_invoice->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #64748B; font-weight: 500;">Tahun Anggaran</td>
                        <td style="padding: 5px 0; font-weight: 600; color: #0F172A;">: {{ $invoice->tahun_anggaran }}</td>
                    </tr>

                </table>
            </div>
            <div>
                <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                    <tr>
                        <td style="width: 130px; padding: 5px 0; color: #64748B; font-weight: 500;">No. Lambung</td>
                        <td style="padding: 5px 0; font-weight: 700; color: #1B2A6B;">: {{ $invoice->no_lambung }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #64748B; font-weight: 500;">No. Polisi (TNKB)</td>
                        <td style="padding: 5px 0; font-weight: 700; color: #0F172A;">: {{ $invoice->no_pol }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #64748B; font-weight: 500;">Jenis Kendaraan</td>
                        <td style="padding: 5px 0; font-weight: 600; color: #0F172A;">: {{ $invoice->jenis_mobil }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #64748B; font-weight: 500;">Lokasi / Pos</td>
                        <td style="padding: 5px 0; font-weight: 600; color: #0F172A;">: {{ $invoice->lokasi ?: '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- RINCIAN ITEM --}}
        <div style="margin-bottom: 24px; border-radius: 10px; border: 1px solid #E2E8F0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12.5px; text-align: left;">
                    <thead>
                        <tr style="background: #1B2A6B; color: #FFFFFF; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th style="padding: 11px 12px; width: 45px; text-align: center;">No</th>
                            <th style="padding: 11px 12px; width: 90px; text-align: center;">Kd. Item</th>
                            <th style="padding: 11px 12px;">Nama Item / Jenis Perbaikan</th>
                            <th style="padding: 11px 12px; width: 65px; text-align: center;">Jml</th>
                            <th style="padding: 11px 12px; width: 75px; text-align: center;">Satuan</th>
                            <th style="padding: 11px 12px; width: 130px; text-align: right;">Harga (Rp)</th>
                            <th style="padding: 11px 12px; width: 70px; text-align: center;">Pot. (%)</th>
                            <th style="padding: 11px 12px; width: 140px; text-align: right;">Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $i => $item)
                            <tr style="border-bottom: 1px solid #F1F5F9; {{ $i % 2 == 1 ? 'background: #FAFAFA;' : 'background: #FFFFFF;' }}">
                                <td style="padding: 10px 12px; text-align: center; color: #64748B; font-weight: 600;">{{ $i + 1 }}</td>
                                <td style="padding: 10px 12px; text-align: center; color: #475569; font-weight: 700; font-family: monospace;">{{ $item->kode_item ?: '—' }}</td>
                                <td style="padding: 10px 12px; font-weight: 600; color: #0F172A;">{{ ucwords(strtolower(trim($item->jenis_perbaikan))) }}</td>
                                <td style="padding: 10px 12px; text-align: center; color: #334155; font-weight: 600;">{{ rtrim(rtrim(number_format($item->vol, 2, ',', '.'), '0'), ',') }}</td>
                                <td style="padding: 10px 12px; text-align: center; color: #64748B;">{{ ucwords(strtolower(trim($item->satuan))) }}</td>
                                <td style="padding: 10px 12px; text-align: right; color: #334155; font-variant-numeric: tabular-nums;">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                <td style="padding: 10px 12px; text-align: center; color: #64748B;">{{ (float)$item->potongan_persen > 0 ? rtrim(rtrim(number_format($item->potongan_persen, 2, ',', '.'), '0'), ',') . '%' : '0%' }}</td>
                                <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #0F172A; font-variant-numeric: tabular-nums;">{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #F8FAFC; border-top: 2px solid #E2E8F0;">
                            <td colspan="7" style="padding: 10px 14px; text-align: right; font-weight: 700; font-size: 12.5px; color: #475569;">SUBTOTAL ITEMS:</td>
                            <td style="padding: 10px 14px; text-align: right; font-weight: 800; font-size: 13px; color: #0F172A; font-variant-numeric: tabular-nums;">Rp {{ number_format($invoice->subtotal ?: $invoice->items->sum('total_biaya'), 0, ',', '.') }}</td>
                        </tr>
                        @if((float)$invoice->potongan > 0)
                            <tr style="background: #FFFFFF;">
                                <td colspan="7" style="padding: 6px 14px; text-align: right; font-weight: 600; font-size: 12px; color: #64748B;">Potongan / Diskon Tambahan:</td>
                                <td style="padding: 6px 14px; text-align: right; font-weight: 700; font-size: 12.5px; color: #DC2626; font-variant-numeric: tabular-nums;">- Rp {{ number_format($invoice->potongan, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        @if((float)$invoice->pajak > 0)
                            <tr style="background: #FFFFFF;">
                                <td colspan="7" style="padding: 6px 14px; text-align: right; font-weight: 600; font-size: 12px; color: #64748B;">Pajak / PPN:</td>
                                <td style="padding: 6px 14px; text-align: right; font-weight: 700; font-size: 12.5px; color: #0F172A; font-variant-numeric: tabular-nums;">+ Rp {{ number_format($invoice->pajak, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        @if((float)$invoice->biaya_lain > 0)
                            <tr style="background: #FFFFFF;">
                                <td colspan="7" style="padding: 6px 14px; text-align: right; font-weight: 600; font-size: 12px; color: #64748B;">Biaya Lainnya:</td>
                                <td style="padding: 6px 14px; text-align: right; font-weight: 700; font-size: 12.5px; color: #0F172A; font-variant-numeric: tabular-nums;">+ Rp {{ number_format($invoice->biaya_lain, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr style="background: #F1F5F9; border-top: 1.5px solid #CBD5E1;">
                            <td colspan="7" style="padding: 12px 14px; text-align: right; font-weight: 800; font-size: 13px; color: #0F172A; text-transform: uppercase;">TOTAL AKHIR INVOICE:</td>
                            <td style="padding: 12px 14px; text-align: right; font-weight: 800; font-size: 15px; color: #1B2A6B; font-variant-numeric: tabular-nums;">Rp {{ number_format($invoice->total_biaya, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- CATATAN --}}
        @if ($invoice->catatan)
            <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 10px; padding: 14px 18px; margin-bottom: 28px;">
                <div style="font-size: 11.5px; font-weight: 700; color: #92400E; text-transform: uppercase; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                    <span>Catatan Khusus:</span>
                </div>
                <div style="font-size: 12.5px; color: #78350F; line-height: 1.5;">{{ $invoice->catatan }}</div>
            </div>
        @endif

        {{-- STATUS & TANDA TANGAN --}}
        @php
            $statusStyles = [
                'draft' => 'background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;',
                'diajukan' => 'background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D;',
                'disetujui' => 'background: #DBEAFE; color: #1E40AF; border: 1px solid #93C5FD;',
                'lunas' => 'background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0;',
            ];
            $currentStatusStyle = $statusStyles[strtolower($invoice->status)] ?? 'background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;';
        @endphp
        <div class="invoice-sign-flex">
            <div>
                <div style="font-size: 11.5px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 6px;">Status Invoice:</div>
                <span style="display: inline-block; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; {{ $currentStatusStyle }}">
                    ● {{ $invoice->status }}
                </span>
            </div>
            <div style="text-align: center; min-width: 280px;">
                <div style="font-size: 12.5px; color: #334155;">Soreang, {{ $invoice->tanggal_invoice->translatedFormat('d F Y') }}</div>
                <div style="font-size: 12.5px; font-weight: 700; color: #0F172A; margin-top: 4px; margin-bottom: 65px;">Kepala Seksi Pemeliharaan Sarana dan Prasarana</div>
                <div style="font-size: 13px; font-weight: 700; color: #0F172A; text-decoration: underline;">( .................................................... )</div>
                <div style="font-size: 11.5px; color: #64748B; margin-top: 4px;">NIP. ....................................................</div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .no-print, aside, nav, .sidebar, .topbar, .app-header, .mobile-menu-btn, header {
            display: none !important;
        }
        body, .app-wrapper, .main-area, .content-area {
            background: #FFFFFF !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        #invoice-print-area {
            padding: 0 !important;
        }
        div[style*="border-radius: 16px"] {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadInvoicePDF() {
        const element = document.getElementById('invoice-print-area');
        const filename = 'Invoice_{{ str_replace(['/', '\\', ' '], '_', $invoice->nomor_invoice) }}.pdf';
        const opt = {
            margin:       [8, 8, 8, 8],
            filename:     filename,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, logging: false },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }

    @if(request('download') == 1 || request('unduh') == 1 || request('pdf') == 1)
    window.addEventListener('load', function() {
        setTimeout(function() {
            downloadInvoicePDF();
        }, 400);
    });
    @endif
</script>
@endpush
