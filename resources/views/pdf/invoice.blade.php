<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->nomor_invoice }}</title>
    <style>
        @page {
            margin: 28pt 40pt 28pt 40pt;
            size: A4;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8.5pt;
            color: #111827;
            line-height: 1.35;
            background: #FFFFFF;
        }
        table {
            border-collapse: collapse;
        }

        /* === KOP SURAT === */
        .kop-table { width: 100%; table-layout: fixed; margin-bottom: 4px; }
        .kop-logo { width: 12%; vertical-align: middle; }
        .kop-center { width: 76%; text-align: center; vertical-align: middle; padding: 0 6px; }
        .kop-title1 { font-size: 11pt; font-weight: bold; color: #000000; text-transform: uppercase; line-height: 1.2; }
        .kop-title2 { font-size: 13.5pt; font-weight: bold; color: #C0201F; text-transform: uppercase; margin-top: 2px; margin-bottom: 2px; line-height: 1.2; }
        .kop-subtitle { font-size: 7.5pt; color: #374151; }
        .kop-divider { border-top: 3px solid #000000; margin-bottom: 2px; }
        .kop-divider2 { border-top: 1px solid #000000; margin-bottom: 14px; }

        /* === JUDUL DOKUMEN === */
        .doc-title { text-align: center; margin-bottom: 14px; }
        .doc-title-text {
            font-size: 12pt;
            font-weight: bold;
            color: #000000;
            text-decoration: underline;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .doc-nomor { font-size: 8.5pt; font-weight: normal; color: #333333; margin-top: 4px; }

        /* === TABEL INFO === */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border: 1px solid #94A3B8;
            font-size: 8pt;
        }
        .info-table td { padding: 4px 8px; vertical-align: top; }
        .info-label { color: #374151; width: 22%; }
        .info-sep { color: #374151; width: 2%; text-align: center; }
        .info-val { font-weight: bold; color: #0F172A; width: 26%; }
        .info-divider { border-left: 1px solid #94A3B8; }
        .info-table tr { border-bottom: 1px solid #CBD5E1; }
        .info-table tr:last-child { border-bottom: none; }

        /* === TABEL RINCIAN ITEM === */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            font-size: 8pt;
            border: 1px solid #94A3B8;
        }
        .items-table thead tr {
            background-color: #1B2A6B;
            color: #FFFFFF;
        }
        .items-table thead th {
            padding: 6px 4px;
            border: 1px solid #263380;
            font-weight: bold;
            font-size: 7.5pt;
            text-transform: uppercase;
        }
        .items-table tbody td {
            padding: 5px 6px;
            border: 1px solid #CBD5E1;
        }
        .items-table tbody tr:nth-child(even) { background-color: #F8FAFC; }
        .items-table tfoot td {
            padding: 5px 8px;
            border: 1px solid #94A3B8;
        }

        /* === TANDA TANGAN === */
        .ttd-table { width: 100%; margin-top: 16px; }
        .ttd-cell {
            width: 40%;
            text-align: center;
            font-size: 8pt;
            color: #1a1a1a;
            vertical-align: top;
        }
        .ttd-kota { color: #374151; margin-bottom: 3px; }
        .ttd-jabatan { font-weight: bold; color: #111827; margin-bottom: 48px; }
        .ttd-nama { font-weight: bold; color: #111827; text-decoration: underline; font-style: italic; }
        .ttd-nip { font-size: 7pt; color: #374151; margin-top: 2px; }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo" style="text-align: left;">
                @if(!empty($logoKabData))
                    <img src="{{ $logoKabData }}" style="width: 52px; height: auto; display: block;" alt="Logo Pemkab">
                @endif
            </td>
            <td class="kop-center">
                <div class="kop-title1">PEMERINTAH KABUPATEN BANDUNG</div>
                <div class="kop-title2">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                <div class="kop-subtitle">Bidang Sarana, Prasarana dan Informasi &mdash; Seksi Pemeliharaan Sarana dan Prasarana</div>
            </td>
            <td class="kop-logo" style="text-align: right;">
                @if(!empty($logoDamkarData))
                    <img src="{{ $logoDamkarData }}" style="width: 52px; height: auto; display: block; margin-left: auto;" alt="Logo Damkar">
                @endif
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>
    <div class="kop-divider2"></div>

    {{-- JUDUL DOKUMEN --}}
    <div class="doc-title">
        <div class="doc-title-text">INVOICE PEMELIHARAAN KENDARAAN</div>
        <div class="doc-nomor">Nomor: {{ $invoice->nomor_invoice }}</div>
    </div>

    {{-- DATA INVOICE & UNIT --}}
    <table class="info-table">
        <tr>
            <td class="info-label">Bengkel / Rekanan</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $invoice->nama_bengkel ?: 'CV. Pratama Motor' }}</td>
            <td class="info-label info-divider">No. Lambung</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $invoice->no_lambung ?: '—' }}</td>
        </tr>
        <tr>
            <td class="info-label">No. Invoice</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $invoice->nomor_invoice }}</td>
            <td class="info-label info-divider">No. Polisi (TNKB)</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $invoice->no_pol ?: '—' }}</td>
        </tr>
        <tr>
            <td class="info-label">Tanggal Invoice</td>
            <td class="info-sep">:</td>
            <td class="info-val" style="font-weight: normal;">{{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : '—' }}</td>
            <td class="info-label info-divider">Jenis Kendaraan</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ strtoupper($invoice->jenis_mobil ?: '—') }}</td>
        </tr>
        <tr>
            <td class="info-label">Tahun Anggaran</td>
            <td class="info-sep">:</td>
            <td class="info-val" style="font-weight: normal;">{{ $invoice->tahun_anggaran }}</td>
            <td class="info-label info-divider">Lokasi / Pos</td>
            <td class="info-sep">:</td>
            <td class="info-val" style="font-weight: normal;">{{ $invoice->lokasi ?: '—' }}</td>
        </tr>
    </table>

    {{-- RINCIAN ITEM --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 4%;  text-align: center;">NO</th>
                <th style="width: 11%; text-align: center;">KD. ITEM</th>
                <th style="width: 35%; text-align: left;   padding-left: 8px;">NAMA ITEM / JENIS PERBAIKAN</th>
                <th style="width: 6%;  text-align: center;">JML</th>
                <th style="width: 8%;  text-align: center;">SATUAN</th>
                <th style="width: 15%; text-align: right;  padding-right: 8px;">HARGA (Rp)</th>
                <th style="width: 7%;  text-align: center;">POT. (%)</th>
                <th style="width: 14%; text-align: right;  padding-right: 8px;">TOTAL (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $i => $item)
                <tr>
                    <td style="text-align: center; color: #374151;">{{ $i + 1 }}</td>
                    <td style="text-align: center; font-style: italic; color: #1B2A6B;">{{ $item->kode_item ?: '—' }}</td>
                    <td style="padding-left: 8px; font-weight: bold; color: #0F172A;">{{ ucwords(strtolower(trim($item->jenis_perbaikan))) }}</td>
                    <td style="text-align: center;">{{ rtrim(rtrim(number_format($item->vol, 2, ',', '.'), '0'), ',') }}</td>
                    <td style="text-align: center; color: #4B5563;">{{ ucwords(strtolower(trim($item->satuan))) }}</td>
                    <td style="text-align: right; padding-right: 8px;">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td style="text-align: center; color: #374151;">{{ (float)$item->potongan_persen > 0 ? rtrim(rtrim(number_format($item->potongan_persen, 2, ',', '.'), '0'), ',') . '%' : '0%' }}</td>
                    <td style="text-align: right; padding-right: 8px; font-weight: bold; color: #0F172A;">{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $subtotalVal    = (float) ($invoice->subtotal ?: $invoice->items->sum('total_biaya'));
                $potonganPct    = (float) ($invoice->potongan ?? 0);
                $potonganNominal = $subtotalVal * ($potonganPct / 100);
                $dppVal         = max(0, $subtotalVal - $potonganNominal);
                $pajakPct       = (float) ($invoice->pajak ?? 0);
                $pajakNominal   = $dppVal * ($pajakPct / 100);
            @endphp
            <tr style="background-color: #F8FAFC;">
                <td colspan="7" style="text-align: right; font-weight: bold; font-size: 8pt; color: #475569; padding-right: 10px;">SUBTOTAL ITEMS:</td>
                <td style="text-align: right; font-weight: bold; font-size: 8.5pt; color: #0F172A; padding-right: 8px;">Rp {{ number_format($subtotalVal, 0, ',', '.') }}</td>
            </tr>
            @if($potonganPct > 0)
            <tr style="background-color: #FFFFFF;">
                <td colspan="7" style="text-align: right; color: #64748B; padding-right: 10px;">Potongan / Diskon ({{ rtrim(rtrim(number_format($potonganPct, 2, ',', '.'), '0'), ',') }}%):</td>
                <td style="text-align: right; font-weight: bold; color: #DC2626; padding-right: 8px;">- Rp {{ number_format($potonganNominal, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($pajakPct > 0)
            <tr style="background-color: #FFFFFF;">
                <td colspan="7" style="text-align: right; color: #64748B; padding-right: 10px;">Pajak / PPN ({{ rtrim(rtrim(number_format($pajakPct, 2, ',', '.'), '0'), ',') }}%):</td>
                <td style="text-align: right; font-weight: bold; color: #0F172A; padding-right: 8px;">+ Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if((float)$invoice->biaya_lain > 0)
            <tr style="background-color: #FFFFFF;">
                <td colspan="7" style="text-align: right; color: #64748B; padding-right: 10px;">Biaya Lainnya:</td>
                <td style="text-align: right; font-weight: bold; color: #0F172A; padding-right: 8px;">+ Rp {{ number_format($invoice->biaya_lain, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr style="background-color: #EFF6FF;">
                <td colspan="7" style="text-align: right; font-weight: bold; font-size: 8.5pt; color: #0F172A; text-transform: uppercase; padding-right: 10px;">TOTAL AKHIR INVOICE:</td>
                <td style="text-align: right; font-weight: bold; font-size: 9pt; color: #1B2A6B; padding-right: 8px;">Rp {{ number_format($invoice->total_biaya, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="ttd-table">
        <tr>
            <td style="width: 60%;"></td>
            <td class="ttd-cell">
                <div class="ttd-kota">Soreang, {{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</div>
                <div class="ttd-jabatan">Kepala Seksi Pemeliharaan Sarana Dan Prasarana</div>
                <div class="ttd-nama">{{ $pejabatKasi->name ?? 'Ahmad Kuswara, S.M., M.M.' }}</div>
                <div class="ttd-nip">NIP. {{ $pejabatKasi->nip ?? '197209212008011001' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
