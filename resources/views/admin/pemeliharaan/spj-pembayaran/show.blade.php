@php
    $isAktual = request()->routeIs('admin.pemeliharaan.spj-pembayaran.*') || request()->routeIs('admin.pemeliharaan.monitoring-aktual.*');
    $pageTitle = $isAktual ? 'SPJ Pembayaran' : 'Aktual Pembayaran';
    $routePrefix = $isAktual ? 'admin.pemeliharaan.spj-pembayaran' : 'admin.pemeliharaan.aktual-pembayaran';
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
    .invoice-meta-grid {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
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
            <button type="button" id="btn-download-pdf" onclick="downloadInvoicePDF()" 
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

{{-- Container Invoice (Formatted Standard A4 Printable Area) --}}
<div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
    <div id="invoice-print-area" style="padding:24px 28px; background:#FFFFFF; max-width:794px; margin:0 auto; box-sizing:border-box;">

        {{-- Kop Surat Resmi Pemkab / Damkar --}}
        <div class="kop-surat" style="display:flex; align-items:center; justify-content:space-between; border-bottom:3px double #0F172A; padding-bottom:12px; margin-bottom:18px; text-align:center; page-break-inside:avoid; break-inside:avoid;">
            <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo Pemkab" style="height:55px; width:auto;" onerror="this.style.display='none'">
            <div style="flex:1; padding:0 12px;">
                <div style="font-size:12px; font-weight:700; letter-spacing:0.5px; color:#0F172A; text-transform:uppercase;">PEMERINTAH KABUPATEN BANDUNG</div>
                <div style="font-size:15px; font-weight:800; color:#C0201F; text-transform:uppercase; margin:2px 0;">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                <div style="font-size:10.5px; color:#475569; font-weight:500;">Bidang Sarana, Prasarana dan Informasi — Seksi Pemeliharaan Sarana dan Prasarana</div>
            </div>
            <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Damkar" style="height:55px; width:auto;" onerror="this.style.display='none'">
        </div>

        {{-- Judul Dokumen --}}
        <div class="judul-dokumen" style="text-align:center; margin-bottom:18px; page-break-inside:avoid; break-inside:avoid;">
            <h2 style="font-size:14px; font-weight:800; text-transform:uppercase; color:#1B2A6B; text-decoration:underline; letter-spacing:0.5px; margin:0 0 3px 0;">INVOICE PEMELIHARAAN KENDARAAN</h2>
            <div style="font-size:11.5px; font-weight:600; color:#64748B;">Nomor: {{ $invoice->nomor_invoice }}</div>
        </div>

        {{-- DATA INVOICE & UNIT --}}
        <div class="invoice-meta-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px 16px; margin-bottom:18px; page-break-inside:avoid; break-inside:avoid;">
            <div>
                <table style="width:100%; border-collapse:collapse; font-size:11.5px;">
                    <tr>
                        <td style="width:115px; padding:3px 0; color:#64748B; font-weight:500;">Bengkel / Rekanan</td>
                        <td style="padding:3px 0; font-weight:700; color:#1B2A6B;">: {{ $invoice->nama_bengkel ?: 'CV. PRATAMA MOTOR' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:3px 0; color:#64748B; font-weight:500;">No. Invoice</td>
                        <td style="padding:3px 0; font-weight:700; color:#0F172A;">: {{ $invoice->nomor_invoice }}</td>
                    </tr>
                    <tr>
                        <td style="padding:3px 0; color:#64748B; font-weight:500;">Tanggal Invoice</td>
                        <td style="padding:3px 0; font-weight:600; color:#0F172A;">: {{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:3px 0; color:#64748B; font-weight:500;">Tahun Anggaran</td>
                        <td style="padding:3px 0; font-weight:600; color:#0F172A;">: {{ $invoice->tahun_anggaran }}</td>
                    </tr>
                </table>
            </div>
            <div>
                <table style="width:100%; border-collapse:collapse; font-size:11.5px;">
                    <tr>
                        <td style="width:115px; padding:3px 0; color:#64748B; font-weight:500;">No. Lambung</td>
                        <td style="padding:3px 0; font-weight:700; color:#1B2A6B;">: {{ $invoice->no_lambung }}</td>
                    </tr>
                    <tr>
                        <td style="padding:3px 0; color:#64748B; font-weight:500;">No. Polisi (TNKB)</td>
                        <td style="padding:3px 0; font-weight:700; color:#0F172A;">: {{ $invoice->no_pol ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:3px 0; color:#64748B; font-weight:500;">Jenis Kendaraan</td>
                        <td style="padding:3px 0; font-weight:600; color:#0F172A;">: {{ $invoice->jenis_mobil ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:3px 0; color:#64748B; font-weight:500;">Lokasi / Pos</td>
                        <td style="padding:3px 0; font-weight:600; color:#0F172A;">: {{ $invoice->lokasi ?: '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- RINCIAN ITEM --}}
        <div style="margin-bottom:18px; border-radius:8px; border:1px solid #E2E8F0; overflow:hidden;">
            <table class="invoice-table" style="width:100%; border-collapse:collapse; font-size:11px; text-align:left; page-break-inside:auto;">
                <thead style="display:table-header-group;">
                    <tr style="background:#1B2A6B; color:#FFFFFF; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; page-break-inside:avoid; break-inside:avoid;">
                        <th style="padding:8px 8px; width:35px; text-align:center;">No</th>
                        <th style="padding:8px 8px; width:75px; text-align:center;">Kd. Item</th>
                        <th style="padding:8px 8px;">Nama Item / Jenis Perbaikan</th>
                        <th style="padding:8px 8px; width:50px; text-align:center;">Jml</th>
                        <th style="padding:8px 8px; width:60px; text-align:center;">Satuan</th>
                        <th style="padding:8px 8px; width:110px; text-align:right;">Harga (Rp)</th>
                        <th style="padding:8px 8px; width:65px; text-align:center;">Pot. (%)</th>
                        <th style="padding:8px 8px; width:120px; text-align:right;">Total (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $i => $item)
                        <tr style="border-bottom:1px solid #E2E8F0; page-break-inside:avoid; break-inside:avoid; {{ $i % 2 == 1 ? 'background:#FAFAFA;' : 'background:#FFFFFF;' }}">
                            <td style="padding:7px 8px; text-align:center; color:#64748B; font-weight:600;">{{ $i + 1 }}</td>
                            <td style="padding:7px 8px; text-align:center; color:#475569; font-weight:700; font-family:monospace;">{{ $item->kode_item ?: '—' }}</td>
                            <td style="padding:7px 8px; font-weight:600; color:#0F172A;">{{ ucwords(strtolower(trim($item->jenis_perbaikan))) }}</td>
                            <td style="padding:7px 8px; text-align:center; color:#334155; font-weight:600;">{{ rtrim(rtrim(number_format($item->vol, 2, ',', '.'), '0'), ',') }}</td>
                            <td style="padding:7px 8px; text-align:center; color:#64748B;">{{ ucwords(strtolower(trim($item->satuan))) }}</td>
                            <td style="padding:7px 8px; text-align:right; color:#334155; font-variant-numeric:tabular-nums;">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td style="padding:7px 8px; text-align:center; color:#64748B;">{{ (float)$item->potongan_persen > 0 ? rtrim(rtrim(number_format($item->potongan_persen, 2, ',', '.'), '0'), ',') . '%' : '0%' }}</td>
                            <td style="padding:7px 8px; text-align:right; font-weight:700; color:#0F172A; font-variant-numeric:tabular-nums;">{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="display:table-footer-group; page-break-inside:avoid; break-inside:avoid;">
                    @php
                        $subtotalVal = (float) ($invoice->subtotal ?: $invoice->items->sum('total_biaya'));
                        $potonganPct = (float) ($invoice->potongan ?? 0);
                        $potonganNominal = $subtotalVal * ($potonganPct / 100);
                        $dppVal = max(0, $subtotalVal - $potonganNominal);
                        $pajakPct = (float) ($invoice->pajak ?? 0);
                        $pajakNominal = $dppVal * ($pajakPct / 100);
                    @endphp
                    <tr style="background:#F8FAFC; border-top:2px solid #E2E8F0;">
                        <td colspan="7" style="padding:8px 10px; text-align:right; font-weight:700; font-size:11px; color:#475569;">SUBTOTAL ITEMS:</td>
                        <td style="padding:8px 10px; text-align:right; font-weight:800; font-size:11.5px; color:#0F172A; font-variant-numeric:tabular-nums;">Rp {{ number_format($subtotalVal, 0, ',', '.') }}</td>
                    </tr>
                    @if($potonganPct > 0)
                        <tr style="background:#FFFFFF;">
                            <td colspan="7" style="padding:5px 10px; text-align:right; font-weight:600; font-size:10.5px; color:#64748B;">Potongan / Diskon Tambahan ({{ rtrim(rtrim(number_format($potonganPct, 2, ',', '.'), '0'), ',') }}%):</td>
                            <td style="padding:5px 10px; text-align:right; font-weight:700; font-size:11px; color:#DC2626; font-variant-numeric:tabular-nums;">- Rp {{ number_format($potonganNominal, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($pajakPct > 0)
                        <tr style="background:#FFFFFF;">
                            <td colspan="7" style="padding:5px 10px; text-align:right; font-weight:600; font-size:10.5px; color:#64748B;">Pajak / PPN ({{ rtrim(rtrim(number_format($pajakPct, 2, ',', '.'), '0'), ',') }}%):</td>
                            <td style="padding:5px 10px; text-align:right; font-weight:700; font-size:11px; color:#0F172A; font-variant-numeric:tabular-nums;">+ Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if((float)$invoice->biaya_lain > 0)
                        <tr style="background:#FFFFFF;">
                            <td colspan="7" style="padding:5px 10px; text-align:right; font-weight:600; font-size:10.5px; color:#64748B;">Biaya Lainnya:</td>
                            <td style="padding:5px 10px; text-align:right; font-weight:700; font-size:11px; color:#0F172A; font-variant-numeric:tabular-nums;">+ Rp {{ number_format($invoice->biaya_lain, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr style="background:#F1F5F9; border-top:1.5px solid #CBD5E1;">
                        <td colspan="7" style="padding:9px 10px; text-align:right; font-weight:800; font-size:11.5px; color:#0F172A; text-transform:uppercase;">TOTAL AKHIR INVOICE:</td>
                        <td style="padding:9px 10px; text-align:right; font-weight:800; font-size:12.5px; color:#1B2A6B; font-variant-numeric:tabular-nums;">Rp {{ number_format($invoice->total_biaya, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Block Tanda Tangan Resmi (Dilindungi dari page-break terpotong) --}}
        <div class="ttd-box" style="margin-top:24px; display:flex; justify-content:flex-end; page-break-inside:avoid; break-inside:avoid;">
            <div style="text-align:center; min-width:260px; font-size:11px; color:#334155;">
                <div>Soreang, {{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</div>
                <div style="font-weight:700; color:#0F172A; margin-top:3px; margin-bottom:50px;">Kepala Seksi Pemeliharaan Sarana Dan Prasarana</div>
                <div style="font-weight:700; color:#0F172A; text-decoration:underline;">{{ $pejabatKasi->name ?? 'Ahmad Kuswara, S.M., M.M.' }}</div>
                <div style="font-size:10px; color:#64748B; margin-top:2px;">NIP. {{ $pejabatKasi->nip ?? '197209212008011001' }}</div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 10mm 10mm 15mm 10mm;
        }
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
            max-width: 100% !important;
            box-shadow: none !important;
            border: none !important;
        }
        .kop-surat, .judul-dokumen, .ttd-box, .invoice-meta-grid {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        table.invoice-table {
            page-break-inside: auto !important;
            width: 100% !important;
            border-collapse: collapse !important;
        }
        thead {
            display: table-header-group !important;
        }
        tfoot {
            display: table-footer-group !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }

    /* html2pdf auto pagebreak CSS */
    .html2pdf__page-break {
        height: 0;
        page-break-before: always;
        break-before: page;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadInvoicePDF() {
        const btn = document.getElementById('btn-download-pdf');
        const originalContent = btn ? btn.innerHTML : '';
        if (btn) {
            btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin" style="width:16px; height:16px;"></i><span>Menyiapkan PDF...</span>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
            btn.disabled = true;
        }

        const element = document.getElementById('invoice-print-area');
        const filename = 'Invoice_{{ str_replace(['/', '\\', ' '], '_', $invoice->nomor_invoice) }}.pdf';
        const opt = {
            margin:       [8, 8, 10, 8],
            filename:     filename,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, logging: false, windowWidth: 794, scrollY: 0 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak:    { 
                mode: ['avoid-all', 'css', 'legacy'],
                avoid: ['tr', '.ttd-box', '.kop-surat', '.judul-dokumen', '.invoice-meta-grid', 'thead', 'tfoot']
            }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            if (btn) {
                btn.innerHTML = originalContent;
                btn.disabled = false;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }).catch(err => {
            console.error('Error creating PDF:', err);
            if (btn) {
                btn.innerHTML = originalContent;
                btn.disabled = false;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
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
