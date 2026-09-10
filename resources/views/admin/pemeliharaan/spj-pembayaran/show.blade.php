@php
    $isAktual = request()->routeIs('admin.pemeliharaan.spj-pembayaran.*') || request()->routeIs('admin.pemeliharaan.monitoring-aktual.*');
    $pageTitle = $isAktual ? 'SPJ Pembayaran' : 'Aktual Pembayaran';
    $routePrefix = $isAktual ? 'admin.pemeliharaan.spj-pembayaran' : 'admin.pemeliharaan.aktual-pembayaran';

    $logoKabPath = public_path('images/logo-kabupaten.png');
    $logoDamkarPath = public_path('images/logo-damkar.png');
    $logoKabData = file_exists($logoKabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoKabPath)) : asset('images/logo-kabupaten.png');
    $logoDamkarData = file_exists($logoDamkarPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoDamkarPath)) : asset('images/logo-damkar.png');
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
            <a href="{{ route($routePrefix . '.pdf', $invoice) }}" id="btn-download-pdf" 
               style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; background: #1B2A6B; color: #FFFFFF; border: none; border-radius: 10px; font-size: 12.5px; font-weight: 700; text-decoration: none; cursor: pointer; box-shadow: 0 4px 14px rgba(27, 42, 107, 0.25); transition: all 0.2s;">
                <i data-lucide="download" style="width: 15px; height: 15px;"></i>
                <span>Unduh PDF</span>
            </a>
            <button type="button" onclick="window.print()" 
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; background: #FFFFFF; color: #334155; border: 1px solid #CBD5E1; border-radius: 10px; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                <i data-lucide="printer" style="width: 15px; height: 15px;"></i>
                <span>Cetak</span>
            </button>
        </div>
    </div>
</div>

{{-- Container Invoice (Formatted Standard A4 Printable Area) --}}
<div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow-x:auto;">
    <div id="invoice-print-area" style="padding:28px 32px; background:#FFFFFF; width:794px; min-width:794px; margin:0 auto; box-sizing:border-box;">

        {{-- Kop Surat Resmi Pemkab / Damkar --}}
        <table style="width:100%; border-collapse:collapse; border-bottom:3px double #0F172A; padding-bottom:12px; margin-bottom:16px;">
            <tr>
                <td style="width:65px; vertical-align:middle; text-align:left;">
                    <img src="{{ $logoKabData }}" alt="Logo Pemkab" style="height:55px; width:auto; display:block;">
                </td>
                <td style="text-align:center; vertical-align:middle; padding:0 10px;">
                    <div style="font-size:12.5px; font-weight:700; letter-spacing:0.5px; color:#0F172A; text-transform:uppercase;">PEMERINTAH KABUPATEN BANDUNG</div>
                    <div style="font-size:15px; font-weight:800; color:#C0201F; text-transform:uppercase; margin:2px 0;">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                    <div style="font-size:10.5px; color:#475569; font-weight:500;">Bidang Sarana, Prasarana dan Informasi — Seksi Pemeliharaan Sarana dan Prasarana</div>
                </td>
                <td style="width:65px; vertical-align:middle; text-align:right;">
                    <img src="{{ $logoDamkarData }}" alt="Logo Damkar" style="height:55px; width:auto; display:block; margin-left:auto;">
                </td>
            </tr>
        </table>

        {{-- Judul Dokumen --}}
        <div style="text-align:center; margin-bottom:16px;">
            <h2 style="font-size:14px; font-weight:800; text-transform:uppercase; color:#1B2A6B; text-decoration:underline; letter-spacing:0.5px; margin:0 0 3px 0;">INVOICE PEMELIHARAAN KENDARAAN</h2>
            <div style="font-size:11.5px; font-weight:600; color:#64748B;">Nomor: {{ $invoice->nomor_invoice }}</div>
        </div>

        {{-- DATA INVOICE & UNIT (Tabel 2 Kolom Kokoh) --}}
        <table style="width:100%; border-collapse:collapse; background:#F8FAFC; border:1px solid #CBD5E1; border-radius:8px; margin-bottom:16px;">
            <tr>
                {{-- Kolom Kiri --}}
                <td style="width:50%; vertical-align:top; padding:10px 14px; border-right:1px solid #CBD5E1;">
                    <table style="width:100%; border-collapse:collapse; font-size:11.5px;">
                        <tr>
                            <td style="width:115px; padding:3px 0; color:#64748B; font-weight:500;">Bengkel / Rekanan</td>
                            <td style="width:8px; padding:3px 0;">:</td>
                            <td style="padding:3px 0; font-weight:700; color:#1B2A6B;">{{ $invoice->nama_bengkel ?: 'CV. PRATAMA MOTOR' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:3px 0; color:#64748B; font-weight:500;">No. Invoice</td>
                            <td style="padding:3px 0;">:</td>
                            <td style="padding:3px 0; font-weight:700; color:#0F172A;">{{ $invoice->nomor_invoice }}</td>
                        </tr>
                        <tr>
                            <td style="padding:3px 0; color:#64748B; font-weight:500;">Tanggal Invoice</td>
                            <td style="padding:3px 0;">:</td>
                            <td style="padding:3px 0; font-weight:600; color:#0F172A;">{{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : '—' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:3px 0; color:#64748B; font-weight:500;">Tahun Anggaran</td>
                            <td style="padding:3px 0;">:</td>
                            <td style="padding:3px 0; font-weight:600; color:#0F172A;">{{ $invoice->tahun_anggaran }}</td>
                        </tr>
                    </table>
                </td>
                {{-- Kolom Kanan --}}
                <td style="width:50%; vertical-align:top; padding:10px 14px;">
                    <table style="width:100%; border-collapse:collapse; font-size:11.5px;">
                        <tr>
                            <td style="width:115px; padding:3px 0; color:#64748B; font-weight:500;">No. Lambung</td>
                            <td style="width:8px; padding:3px 0;">:</td>
                            <td style="padding:3px 0; font-weight:700; color:#1B2A6B;">{{ $invoice->no_lambung }}</td>
                        </tr>
                        <tr>
                            <td style="padding:3px 0; color:#64748B; font-weight:500;">No. Polisi (TNKB)</td>
                            <td style="padding:3px 0;">:</td>
                            <td style="padding:3px 0; font-weight:700; color:#0F172A;">{{ $invoice->no_pol ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:3px 0; color:#64748B; font-weight:500;">Jenis Kendaraan</td>
                            <td style="padding:3px 0;">:</td>
                            <td style="padding:3px 0; font-weight:600; color:#0F172A;">{{ $invoice->jenis_mobil ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:3px 0; color:#64748B; font-weight:500;">Lokasi / Pos</td>
                            <td style="padding:3px 0;">:</td>
                            <td style="padding:3px 0; font-weight:600; color:#0F172A;">{{ $invoice->lokasi ?: '—' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- RINCIAN ITEM --}}
        <div style="margin-bottom:18px; border-radius:8px; border:1px solid #CBD5E1; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:11px; text-align:left;">
                <thead>
                    <tr style="background:#1B2A6B; color:#FFFFFF; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                        <th style="padding:8px 8px; width:35px; text-align:center; border:1px solid #101B4B;">No</th>
                        <th style="padding:8px 8px; width:75px; text-align:center; border:1px solid #101B4B;">Kd. Item</th>
                        <th style="padding:8px 8px; border:1px solid #101B4B;">Nama Item / Jenis Perbaikan</th>
                        <th style="padding:8px 8px; width:50px; text-align:center; border:1px solid #101B4B;">Jml</th>
                        <th style="padding:8px 8px; width:60px; text-align:center; border:1px solid #101B4B;">Satuan</th>
                        <th style="padding:8px 8px; width:110px; text-align:right; border:1px solid #101B4B;">Harga (Rp)</th>
                        <th style="padding:8px 8px; width:65px; text-align:center; border:1px solid #101B4B;">Pot. (%)</th>
                        <th style="padding:8px 8px; width:120px; text-align:right; border:1px solid #101B4B;">Total (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $i => $item)
                        <tr style="border-bottom:1px solid #E2E8F0; {{ $i % 2 == 1 ? 'background:#FAFAFA;' : 'background:#FFFFFF;' }}">
                            <td style="padding:7px 8px; text-align:center; color:#64748B; font-weight:600; border:1px solid #E2E8F0;">{{ $i + 1 }}</td>
                            <td style="padding:7px 8px; text-align:center; color:#475569; font-weight:700; font-family:monospace; border:1px solid #E2E8F0;">{{ $item->kode_item ?: '—' }}</td>
                            <td style="padding:7px 8px; font-weight:600; color:#0F172A; border:1px solid #E2E8F0;">{{ ucwords(strtolower(trim($item->jenis_perbaikan))) }}</td>
                            <td style="padding:7px 8px; text-align:center; color:#334155; font-weight:600; border:1px solid #E2E8F0;">{{ rtrim(rtrim(number_format($item->vol, 2, ',', '.'), '0'), ',') }}</td>
                            <td style="padding:7px 8px; text-align:center; color:#64748B; border:1px solid #E2E8F0;">{{ ucwords(strtolower(trim($item->satuan))) }}</td>
                            <td style="padding:7px 8px; text-align:right; color:#334155; font-variant-numeric:tabular-nums; border:1px solid #E2E8F0;">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td style="padding:7px 8px; text-align:center; color:#64748B; border:1px solid #E2E8F0;">{{ (float)$item->potongan_persen > 0 ? rtrim(rtrim(number_format($item->potongan_persen, 2, ',', '.'), '0'), ',') . '%' : '0%' }}</td>
                            <td style="padding:7px 8px; text-align:right; font-weight:700; color:#0F172A; font-variant-numeric:tabular-nums; border:1px solid #E2E8F0;">{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @php
                        $subtotalVal = (float) ($invoice->subtotal ?: $invoice->items->sum('total_biaya'));
                        $potonganPct = (float) ($invoice->potongan ?? 0);
                        $potonganNominal = $subtotalVal * ($potonganPct / 100);
                        $dppVal = max(0, $subtotalVal - $potonganNominal);
                        $pajakPct = (float) ($invoice->pajak ?? 0);
                        $pajakNominal = $dppVal * ($pajakPct / 100);
                    @endphp
                    <tr style="background:#F8FAFC; border-top:2px solid #CBD5E1;">
                        <td colspan="7" style="padding:8px 10px; text-align:right; font-weight:700; font-size:11px; color:#475569; border:1px solid #CBD5E1;">SUBTOTAL ITEMS:</td>
                        <td style="padding:8px 10px; text-align:right; font-weight:800; font-size:11.5px; color:#0F172A; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">Rp {{ number_format($subtotalVal, 0, ',', '.') }}</td>
                    </tr>
                    @if($potonganPct > 0)
                        <tr style="background:#FFFFFF;">
                            <td colspan="7" style="padding:5px 10px; text-align:right; font-weight:600; font-size:10.5px; color:#64748B; border:1px solid #CBD5E1;">Potongan / Diskon Tambahan ({{ rtrim(rtrim(number_format($potonganPct, 2, ',', '.'), '0'), ',') }}%):</td>
                            <td style="padding:5px 10px; text-align:right; font-weight:700; font-size:11px; color:#DC2626; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">- Rp {{ number_format($potonganNominal, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($pajakPct > 0)
                        <tr style="background:#FFFFFF;">
                            <td colspan="7" style="padding:5px 10px; text-align:right; font-weight:600; font-size:10.5px; color:#64748B; border:1px solid #CBD5E1;">Pajak / PPN ({{ rtrim(rtrim(number_format($pajakPct, 2, ',', '.'), '0'), ',') }}%):</td>
                            <td style="padding:5px 10px; text-align:right; font-weight:700; font-size:11px; color:#0F172A; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">+ Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if((float)$invoice->biaya_lain > 0)
                        <tr style="background:#FFFFFF;">
                            <td colspan="7" style="padding:5px 10px; text-align:right; font-weight:600; font-size:10.5px; color:#64748B; border:1px solid #CBD5E1;">Biaya Lainnya:</td>
                            <td style="padding:5px 10px; text-align:right; font-weight:700; font-size:11px; color:#0F172A; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">+ Rp {{ number_format($invoice->biaya_lain, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr style="background:#F1F5F9; border-top:1.5px solid #CBD5E1;">
                        <td colspan="7" style="padding:9px 10px; text-align:right; font-weight:800; font-size:11.5px; color:#0F172A; text-transform:uppercase; border:1px solid #CBD5E1;">TOTAL AKHIR INVOICE:</td>
                        <td style="padding:9px 10px; text-align:right; font-weight:800; font-size:12.5px; color:#1B2A6B; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">Rp {{ number_format($invoice->total_biaya, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Block Tanda Tangan Resmi --}}
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <tr>
                <td style="width:60%;"></td>
                <td style="width:40%; text-align:center; font-size:11px; color:#334155; vertical-align:top;">
                    <div>Soreang, {{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</div>
                    <div style="font-weight:700; color:#0F172A; margin-top:3px; margin-bottom:50px;">Kepala Seksi Pemeliharaan Sarana Dan Prasarana</div>
                    <div style="font-weight:700; color:#0F172A; text-decoration:underline;">{{ $pejabatKasi->name ?? 'Ahmad Kuswara, S.M., M.M.' }}</div>
                    <div style="font-size:10px; color:#64748B; margin-top:2px;">NIP. {{ $pejabatKasi->nip ?? '197209212008011001' }}</div>
                </td>
            </tr>
        </table>

    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 10mm 10mm 12mm 10mm;
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
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    @if(request('download') == 1 || request('unduh') == 1 || request('pdf') == 1)
    window.addEventListener('load', function() {
        // Direct download file PDF dari backend
        setTimeout(function() {
            window.location.href = "{{ route($routePrefix . '.pdf', $invoice) }}";
        }, 300);
    });
    @endif
</script>
@endpush

