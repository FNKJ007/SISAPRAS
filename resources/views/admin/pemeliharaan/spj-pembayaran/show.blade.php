@php
    $isAktual = request()->routeIs('admin.pemeliharaan.spj-pembayaran.*') || request()->routeIs('admin.pemeliharaan.monitoring-aktual.*');
    $pageTitle = $isAktual ? 'SPJ Pembayaran' : 'Aktual Pembayaran';
    $routePrefix = $isAktual ? 'admin.pemeliharaan.spj-pembayaran' : 'admin.pemeliharaan.aktual-pembayaran';

    $logoKabPath = public_path('images/logo-kabupaten.png');
    $logoDamkarPath = public_path('images/logo-damkar.png');
    $logoKabData = file_exists($logoKabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoKabPath)) : asset('images/logo-kabupaten.png');
    $logoDamkarData = file_exists($logoDamkarPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoDamkarPath)) : asset('images/logo-damkar.png');

    if (!function_exists('invoicePenyebut')) {
        function invoicePenyebut($nilai) {
            $nilai = abs($nilai);
            $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
            $temp = "";
            if ($nilai < 12) {
                $temp = " ". $huruf[$nilai];
            } else if ($nilai < 20) {
                $temp = invoicePenyebut($nilai - 10). " Belas";
            } else if ($nilai < 100) {
                $temp = invoicePenyebut(floor($nilai/10))." Puluh". invoicePenyebut($nilai % 10);
            } else if ($nilai < 200) {
                $temp = " Seratus" . invoicePenyebut($nilai - 100);
            } else if ($nilai < 1000) {
                $temp = invoicePenyebut(floor($nilai/100)) . " Ratus" . invoicePenyebut($nilai % 100);
            } else if ($nilai < 2000) {
                $temp = " Seribu" . invoicePenyebut($nilai - 1000);
            } else if ($nilai < 1000000) {
                $temp = invoicePenyebut(floor($nilai/1000)) . " Ribu" . invoicePenyebut($nilai % 1000);
            } else if ($nilai < 1000000000) {
                $temp = invoicePenyebut(floor($nilai/1000000)) . " Juta" . invoicePenyebut($nilai % 1000000);
            } else if ($nilai < 1000000000000) {
                $temp = invoicePenyebut(floor($nilai/1000000000)) . " Milyar" . invoicePenyebut(fmod($nilai,1000000000));
            } else if ($nilai < 1000000000000000) {
                $temp = invoicePenyebut(floor($nilai/1000000000000)) . " Trilyun" . invoicePenyebut(fmod($nilai,1000000000000));
            }     
            return $temp;
        }
    }
    if (!function_exists('invoiceTerbilang')) {
        function invoiceTerbilang($nilai) {
            if ($nilai < 0) {
                $hasil = "Minus ". trim(invoicePenyebut($nilai));
            } else {
                $hasil = trim(invoicePenyebut($nilai));
            }     
            return ($hasil ?: 'Nol') . " Rupiah";
        }
    }
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
    <div id="invoice-print-area" style="padding:40px 56px; background:#FFFFFF; width:800px; min-width:800px; margin:0 auto; box-sizing:border-box;">

        {{-- Kop Surat Resmi Pemkab / Damkar --}}
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:65px; vertical-align:middle; text-align:left;">
                    <img src="{{ $logoKabData }}" alt="Logo Pemkab" style="height:50px; width:auto; display:block;">
                </td>
                <td style="text-align:center; vertical-align:middle; padding:0 10px;">
                    <div style="font-size:12.5px; font-weight:700; letter-spacing:0.5px; color:#000000; text-transform:uppercase;">PEMERINTAH KABUPATEN BANDUNG</div>
                    <div style="font-size:15px; font-weight:800; color:#C0201F; text-transform:uppercase; margin:2px 0;">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                    <div style="font-size:10px; color:#6B7280; font-weight:400;">Bidang Sarana, Prasarana dan Informasi — Seksi Pemeliharaan Sarana dan Prasarana</div>
                </td>
                <td style="width:65px; vertical-align:middle; text-align:right;">
                    <img src="{{ $logoDamkarData }}" alt="Logo Damkar" style="height:50px; width:auto; display:block; margin-left:auto;">
                </td>
            </tr>
        </table>
        {{-- Garis Horizontal Tebal di Bawah Header --}}
        <div style="border-top:3px solid #000000; margin-top:8px; margin-bottom:16px;"></div>

        {{-- Judul Dokumen --}}
        <div style="text-align:center; margin-bottom:16px;">
            <h2 style="font-size:14px; font-weight:800; text-transform:uppercase; color:#000000; text-decoration:underline; letter-spacing:0.5px; margin:0 0 3px 0;">INVOICE PEMELIHARAAN KENDARAAN</h2>
            <div style="font-size:11px; font-weight:400; color:#374151;">Nomor: {{ $invoice->nomor_invoice }}</div>
        </div>

        {{-- KOTAK INFORMASI (2 KOLOM, PADDING DALAM 24px, GAP 50px) --}}
        <div style="background:#FAFAFA; border:1px solid #D1D5DB; padding:12px 24px; margin-bottom:18px; box-sizing:border-box;">
            <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
                <tr>
                    {{-- Kolom Kiri (Lebar 50%, Padding Kanan 25px) --}}
                    <td style="width:50%; vertical-align:top; padding-right:25px; box-sizing:border-box;">
                        <table style="width:100%; border-collapse:collapse; font-size:11.5px; table-layout:fixed;">
                            <tr>
                                <td style="width:120px; padding:3.5px 0; color:#4B5563; font-weight:400; white-space:nowrap;">Bengkel / Rekanan</td>
                                <td style="width:12px; padding:3.5px 0; text-align:center; color:#4B5563;">:</td>
                                <td style="padding:3.5px 0; font-weight:700; color:#0F172A;">{{ $invoice->nama_bengkel ?: 'CV. Pratama Motor' }}</td>
                            </tr>
                            <tr>
                                <td style="width:120px; padding:3.5px 0; color:#4B5563; font-weight:400; white-space:nowrap;">No. Invoice</td>
                                <td style="width:12px; padding:3.5px 0; text-align:center; color:#4B5563;">:</td>
                                <td style="padding:3.5px 0; font-weight:700; color:#0F172A;">{{ $invoice->nomor_invoice }}</td>
                            </tr>
                            <tr>
                                <td style="width:120px; padding:3.5px 0; color:#4B5563; font-weight:400; white-space:nowrap;">Tanggal Invoice</td>
                                <td style="width:12px; padding:3.5px 0; text-align:center; color:#4B5563;">:</td>
                                <td style="padding:3.5px 0; font-weight:700; color:#0F172A;">{{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : '—' }}</td>
                            </tr>
                            <tr>
                                <td style="width:120px; padding:3.5px 0; color:#4B5563; font-weight:400; white-space:nowrap;">Tahun Anggaran</td>
                                <td style="width:12px; padding:3.5px 0; text-align:center; color:#4B5563;">:</td>
                                <td style="padding:3.5px 0; font-weight:700; color:#0F172A;">{{ $invoice->tahun_anggaran }}</td>
                            </tr>
                        </table>
                    </td>

                    {{-- Kolom Kanan (Lebar 50%, Padding Kiri 25px -> Gap Total 50px) --}}
                    <td style="width:50%; vertical-align:top; padding-left:25px; box-sizing:border-box;">
                        <table style="width:100%; border-collapse:collapse; font-size:11.5px; table-layout:fixed;">
                            <tr>
                                <td style="width:120px; padding:3.5px 0; color:#4B5563; font-weight:400; white-space:nowrap;">No. Lambung</td>
                                <td style="width:12px; padding:3.5px 0; text-align:center; color:#4B5563;">:</td>
                                <td style="padding:3.5px 0; font-weight:700; color:#0F172A;">{{ $invoice->no_lambung ?: ($invoice->unit->nomor_lambung ?? '—') }}</td>
                            </tr>
                            <tr>
                                <td style="width:120px; padding:3.5px 0; color:#4B5563; font-weight:400; white-space:nowrap;">No. Polisi (TNKB)</td>
                                <td style="width:12px; padding:3.5px 0; text-align:center; color:#4B5563;">:</td>
                                <td style="padding:3.5px 0; font-weight:700; color:#0F172A;">{{ $invoice->no_pol ?: ($invoice->unit->plat_nomor ?? '—') }}</td>
                            </tr>
                            <tr>
                                <td style="width:120px; padding:3.5px 0; color:#4B5563; font-weight:400; white-space:nowrap;">Jenis Kendaraan</td>
                                <td style="width:12px; padding:3.5px 0; text-align:center; color:#4B5563;">:</td>
                                <td style="padding:3.5px 0; font-weight:700; color:#0F172A;">{{ strtoupper($invoice->jenis_mobil ?: ($invoice->unit->merk_tipe ?? '—')) }}</td>
                            </tr>
                            <tr>
                                <td style="width:120px; padding:3.5px 0; color:#4B5563; font-weight:400; white-space:nowrap;">Lokasi / Pos</td>
                                <td style="width:12px; padding:3.5px 0; text-align:center; color:#4B5563;">:</td>
                                <td style="padding:3.5px 0; font-weight:700; color:#0F172A;">{{ $invoice->lokasi ?: ($invoice->unit->lokasi_pos ?? '—') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        {{-- RINCIAN ITEM (PADDING 10-12px) ===================================
             FIX: tambah table-layout:fixed + box-sizing:border-box supaya lebar
             tabel item benar-benar terkunci 100% dan garis kanannya sejajar
             dengan kotak informasi di atasnya (sebelumnya tabel ini pakai
             auto-layout, sehingga bisa melebar sedikit melewati kotak info
             ketika ada teks item yang panjang).
        ========================================================================= --}}
        <div style="margin-bottom:18px;">
            <table class="invoice-items-table" style="width:100%; border-collapse:collapse; table-layout:fixed; box-sizing:border-box; font-size:11px; text-align:left; border:1px solid #CBD5E1;">
                <colgroup>
                    <col style="width:35px;">
                    <col style="width:80px;">
                    <col>
                    <col style="width:45px;">
                    <col style="width:60px;">
                    <col style="width:110px;">
                    <col style="width:65px;">
                    <col style="width:120px;">
                </colgroup>
                <thead>
                    <tr style="background:#16244f; color:#FFFFFF;">
                        <th style="padding:8px 11px; text-align:center; border:1px solid #16244f; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#FFFFFF; white-space:nowrap;">NO</th>
                        <th style="padding:8px 11px; text-align:center; border:1px solid #16244f; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#FFFFFF; white-space:nowrap;">KD. ITEM</th>
                        <th style="padding:8px 11px; border:1px solid #16244f; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#FFFFFF; white-space:nowrap;">NAMA ITEM / JENIS PERBAIKAN</th>
                        <th style="padding:8px 11px; text-align:center; border:1px solid #16244f; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#FFFFFF; white-space:nowrap;">JML</th>
                        <th style="padding:8px 11px; text-align:center; border:1px solid #16244f; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#FFFFFF; white-space:nowrap;">SATUAN</th>
                        <th style="padding:8px 11px; text-align:right; border:1px solid #16244f; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#FFFFFF; white-space:nowrap;">HARGA (RP)</th>
                        <th style="padding:8px 11px; text-align:center; border:1px solid #16244f; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#FFFFFF; white-space:nowrap;">POT. (%)</th>
                        <th style="padding:8px 11px; text-align:right; border:1px solid #16244f; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#FFFFFF; white-space:nowrap;">TOTAL (RP)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $i => $item)
                        <tr style="{{ $i % 2 == 1 ? 'background:#F8FAFC;' : 'background:#FFFFFF;' }}">
                            <td style="padding:7px 11px; text-align:center; color:#4B5563; font-weight:400; border:1px solid #CBD5E1;">{{ $i + 1 }}</td>
                            <td style="padding:7px 11px; text-align:center; color:#16244f; font-weight:400; font-style:italic; border:1px solid #CBD5E1; overflow-wrap:break-word;">{{ $item->kode_item ?: '—' }}</td>
                            <td style="padding:7px 11px; font-weight:700; color:#0F172A; border:1px solid #CBD5E1; overflow-wrap:break-word;">{{ ucwords(strtolower(trim($item->jenis_perbaikan))) }}</td>
                            <td style="padding:7px 11px; text-align:center; color:#334155; font-weight:400; border:1px solid #CBD5E1;">{{ rtrim(rtrim(number_format($item->vol, 2, ',', '.'), '0'), ',') }}</td>
                            <td style="padding:7px 11px; text-align:center; color:#4B5563; border:1px solid #CBD5E1; overflow-wrap:break-word;">{{ ucwords(strtolower(trim($item->satuan))) }}</td>
                            <td style="padding:7px 11px; text-align:right; color:#334155; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td style="padding:7px 11px; text-align:center; color:#4B5563; border:1px solid #CBD5E1;">{{ (float)$item->potongan_persen > 0 ? rtrim(rtrim(number_format($item->potongan_persen, 2, ',', '.'), '0'), ',') . '%' : '0%' }}</td>
                            <td style="padding:7px 11px; text-align:right; font-weight:700; color:#0F172A; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
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
                    <tr style="background:#EBF3FC;">
                        <td colspan="7" style="padding:8px 12px; text-align:right; font-weight:700; font-size:11px; color:#0F172A; border:1px solid #CBD5E1;">SUBTOTAL ITEMS:</td>
                        <td style="padding:8px 11px; text-align:right; font-weight:700; font-size:11.5px; color:#0F172A; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">Rp {{ number_format($subtotalVal, 0, ',', '.') }}</td>
                    </tr>
                    @if($potonganPct > 0)
                        <tr style="background:#FFFFFF;">
                            <td colspan="7" style="padding:5px 12px; text-align:right; font-weight:500; font-size:10.5px; color:#64748B; border:1px solid #CBD5E1;">Potongan / Diskon ({{ rtrim(rtrim(number_format($potonganPct, 2, ',', '.'), '0'), ',') }}%):</td>
                            <td style="padding:5px 11px; text-align:right; font-weight:700; font-size:11px; color:#DC2626; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">- Rp {{ number_format($potonganNominal, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($pajakPct > 0)
                        <tr style="background:#FFFFFF;">
                            <td colspan="7" style="padding:5px 12px; text-align:right; font-weight:500; font-size:10.5px; color:#64748B; border:1px solid #CBD5E1;">Pajak / PPN ({{ rtrim(rtrim(number_format($pajakPct, 2, ',', '.'), '0'), ',') }}%):</td>
                            <td style="padding:5px 11px; text-align:right; font-weight:700; font-size:11px; color:#0F172A; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">+ Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if((float)$invoice->biaya_lain > 0)
                        <tr style="background:#FFFFFF;">
                            <td colspan="7" style="padding:5px 12px; text-align:right; font-weight:500; font-size:10.5px; color:#64748B; border:1px solid #CBD5E1;">Biaya Lainnya:</td>
                            <td style="padding:5px 11px; text-align:right; font-weight:700; font-size:11px; color:#0F172A; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">+ Rp {{ number_format($invoice->biaya_lain, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr style="background:#DFEBFA;">
                        <td colspan="7" style="padding:9px 12px; text-align:right; font-weight:700; font-size:11.5px; color:#16244f; text-transform:uppercase; border:1px solid #CBD5E1;">TOTAL AKHIR INVOICE:</td>
                        <td style="padding:9px 11px; text-align:right; font-weight:700; font-size:12px; color:#16244f; font-variant-numeric:tabular-nums; border:1px solid #CBD5E1;">Rp {{ number_format($invoice->total_biaya, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Block Tanda Tangan Resmi --}}
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <tr>
                <td style="width:58%;"></td>
                <td style="width:42%; text-align:center; font-size:11px; color:#334155; vertical-align:top;">
                    <div style="font-weight:400; color:#374151;">Soreang, {{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</div>
                    <div style="font-weight:700; color:#0F172A; margin-top:3px; margin-bottom:50px;">{{ $pejabatKasi->jabatan ?? 'Kepala Seksi Pemeliharaan Sarana Dan Prasarana' }}</div>
                    <div style="font-weight:700; color:#0F172A; text-decoration:underline; font-style:italic;">{{ $pejabatKasi->name ?? 'Ahmad Kuswara, S.M., M.M.' }}</div>
                    <div style="font-size:10px; color:#64748B; margin-top:2px;">NIP. {{ $pejabatKasi->nip ?? '197209212008011001' }}</div>
                </td>
            </tr>
        </table>

    </div>
</div>
@endsection

@push('styles')
<style>
    .invoice-items-table thead th {
        background-color: #16244f !important;
        color: #FFFFFF !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    @media print {
        @page {
            size: A4 portrait;
            margin: 28px 56px;
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
        .invoice-items-table thead th {
            background-color: #16244f !important;
            color: #FFFFFF !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    @if(request('download') == 1 || request('unduh') == 1 || request('pdf') == 1)
    window.addEventListener('load', function() {
        setTimeout(function() {
            window.location.href = "{{ route($routePrefix . '.pdf', $invoice) }}";
        }, 300);
    });
    @endif
</script>
@endpush
