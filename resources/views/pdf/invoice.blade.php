<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->nomor_invoice }}</title>
    <style>
        /* === PAGE SETUP ===
           CATATAN PENTING:
           DomPDF sering tidak konsisten menerapkan margin lewat @page saat
           satuannya px, sehingga hasil PDF terlihat mepet ke kiri-kanan
           walau kode CSS sudah benar. Solusi paling stabil: set margin
           @page ke 0, lalu beri padding langsung di <body> memakai satuan
           mm. Ini yang membuat margin kiri-kanan benar-benar terlihat di
           file PDF hasil download.
        */
        @page {
            margin: 0;
            size: A4 portrait;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #111827;
            line-height: 1.3;
            background: #FFFFFF;
            /* Margin halaman sesungguhnya ada di sini (top right bottom left) */
            padding: 12mm 20mm 14mm 20mm;
        }
        table {
            border-collapse: collapse;
        }

        /* === HEADER / KOP SURAT === */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 2px;
        }
        .kop-logo {
            width: 13%;
            vertical-align: middle;
        }
        .kop-logo-left {
            text-align: left;
        }
        .kop-logo-right {
            text-align: right;
        }
        .logo-img {
            width: 48px;
            height: auto;
            max-height: 50px;
            display: block;
        }
        .kop-logo-right .logo-img {
            margin-left: auto;
        }
        .kop-center {
            width: 74%;
            text-align: center;
            vertical-align: middle;
            padding: 0 4px;
        }
        .kop-title1 {
            font-size: 10.5pt;
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
            line-height: 1.25;
        }
        .kop-title2 {
            font-size: 12.5pt;
            font-weight: bold;
            color: #C0201F;
            text-transform: uppercase;
            margin-top: 2px;
            margin-bottom: 2px;
            line-height: 1.25;
        }
        .kop-subtitle {
            font-size: 7.5pt;
            color: #6B7280;
            font-weight: normal;
            line-height: 1.2;
        }
        .kop-divider {
            border-top: 2.5pt solid #000000;
            margin-top: 6px;
            margin-bottom: 14px;
        }

        /* === JUDUL DOKUMEN === */
        .doc-title {
            text-align: center;
            margin-bottom: 14px;
        }
        .doc-title-text {
            font-size: 12pt;
            font-weight: bold;
            color: #000000;
            text-decoration: underline;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .doc-nomor {
            font-size: 8.5pt;
            font-weight: normal;
            color: #374151;
            margin-top: 3px;
        }

        /* === KOTAK INFORMASI (TABLE 100% SEJAJAR TABEL ITEM, PADDING 24px, GAP 50px) === */
        .info-box-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #D1D5DB;
            background-color: #FAFAFA;
            margin-bottom: 14px;
            table-layout: fixed;
        }
        .info-col-left {
            width: 50%;
            vertical-align: top;
            padding: 10px 25px 10px 24px;
        }
        .info-col-right {
            width: 50%;
            vertical-align: top;
            padding: 10px 24px 10px 25px;
        }
        .info-subtable {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            table-layout: fixed;
        }
        .info-subtable td {
            padding: 3.5px 0;
            vertical-align: top;
        }
        .info-label {
            width: 44%;
            font-weight: normal;
            color: #4B5563;
            white-space: nowrap;
        }
        .info-sep {
            width: 6%;
            text-align: center;
            font-weight: normal;
            color: #4B5563;
        }
        .info-val {
            width: 50%;
            font-weight: bold;
            color: #0F172A;
            word-wrap: break-word;
        }

        /* === TABEL ITEM (PADDING SEL 10-12px KIRI-KANAN) === */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            box-sizing: border-box;
            font-size: 8pt;
            border: 1px solid #CBD5E1;
        }
        .items-table thead tr {
            background-color: #16244f;
            color: #FFFFFF;
        }
        .items-table thead th {
            padding: 6px 8px;
            border: 1px solid #16244f;
            font-weight: bold;
            font-size: 7.5pt;
            text-transform: uppercase;
            color: #FFFFFF;
            white-space: nowrap;
        }
        .items-table tbody td {
            padding: 5.5px 10px;
            border: 1px solid #CBD5E1;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #F8FAFC;
        }
        .items-table tfoot td {
            padding: 5.5px 10px;
            border: 1px solid #CBD5E1;
        }

        /* Footer Tabel Subtotal & Total */
        .row-subtotal td {
            background-color: #EBF3FC;
            font-weight: bold;
        }
        .subtotal-label {
            text-align: right;
            padding-right: 12px !important;
            color: #0F172A;
            font-size: 8pt;
        }
        .subtotal-val {
            text-align: right;
            padding-right: 10px !important;
            color: #0F172A;
            font-size: 8pt;
        }
        .row-extra td {
            background-color: #FFFFFF;
        }
        .extra-label {
            text-align: right;
            padding-right: 12px !important;
            color: #64748B;
            font-size: 7.5pt;
        }
        .extra-val {
            text-align: right;
            padding-right: 10px !important;
            font-weight: bold;
            color: #0F172A;
            font-size: 8pt;
        }
        .extra-val-discount {
            text-align: right;
            padding-right: 10px !important;
            font-weight: bold;
            color: #DC2626;
            font-size: 8pt;
        }
        .row-total td {
            background-color: #DFEBFA;
            font-weight: bold;
        }
        .total-label {
            text-align: right;
            padding-right: 12px !important;
            color: #16244f;
            font-size: 8.5pt;
            text-transform: uppercase;
        }
        .total-val {
            text-align: right;
            padding-right: 10px !important;
            color: #16244f;
            font-size: 8.5pt;
        }

        /* === TANDA TANGAN === */
        .ttd-table {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
        }
        .ttd-cell {
            width: 42%;
            text-align: center;
            font-size: 8pt;
            vertical-align: top;
        }
        .ttd-kota {
            font-weight: normal;
            color: #374151;
            margin-bottom: 3px;
            font-size: 8pt;
        }
        .ttd-jabatan {
            font-weight: bold;
            color: #0F172A;
            font-size: 8pt;
        }
        .ttd-space {
            height: 52px;
        }
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            font-style: italic;
            color: #0F172A;
            font-size: 8pt;
        }
        .ttd-nip {
            font-size: 7pt;
            color: #4B5563;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    {{-- HEADER / KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo kop-logo-left">
                @if(!empty($logoKabData))
                    <img src="{{ $logoKabData }}" class="logo-img" alt="Logo Pemkab">
                @endif
            </td>
            <td class="kop-center">
                <div class="kop-title1">PEMERINTAH KABUPATEN BANDUNG</div>
                <div class="kop-title2">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                <div class="kop-subtitle">Bidang Sarana, Prasarana dan Informasi — Seksi Pemeliharaan Sarana dan Prasarana</div>
            </td>
            <td class="kop-logo kop-logo-right">
                @if(!empty($logoDamkarData))
                    <img src="{{ $logoDamkarData }}" class="logo-img" alt="Logo Damkar">
                @endif
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>

    {{-- JUDUL DOKUMEN --}}
    <div class="doc-title">
        <div class="doc-title-text">INVOICE PEMELIHARAAN KENDARAAN</div>
        <div class="doc-nomor">Nomor: {{ $invoice->nomor_invoice }}</div>
    </div>

    {{-- KOTAK INFORMASI (2 KOLOM, PADDING DALAM 24px, GAP 50px, LEBAR 100% SEJAJAR TABEL ITEM) --}}
    <table class="info-box-table">
        <tr>
            {{-- Kolom Kiri (Lebar 50%, Padding Kiri 24px, Padding Kanan 25px) --}}
            <td class="info-col-left">
                <table class="info-subtable">
                    <tr>
                        <td class="info-label">Bengkel / Rekanan</td>
                        <td class="info-sep">:</td>
                        <td class="info-val">{{ $invoice->nama_bengkel ?: 'CV. Pratama Motor' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">No. Invoice</td>
                        <td class="info-sep">:</td>
                        <td class="info-val">{{ $invoice->nomor_invoice }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Invoice</td>
                        <td class="info-sep">:</td>
                        <td class="info-val">{{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tahun Anggaran</td>
                        <td class="info-sep">:</td>
                        <td class="info-val">{{ $invoice->tahun_anggaran }}</td>
                    </tr>
                </table>
            </td>

            {{-- Kolom Kanan (Lebar 50%, Padding Kiri 25px, Padding Kanan 24px -> Gap Total 50px) --}}
            <td class="info-col-right">
                <table class="info-subtable">
                    <tr>
                        <td class="info-label">No. Lambung</td>
                        <td class="info-sep">:</td>
                        <td class="info-val">{{ $invoice->no_lambung ?: ($invoice->unit->nomor_lambung ?? '—') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">No. Polisi (TNKB)</td>
                        <td class="info-sep">:</td>
                        <td class="info-val">{{ $invoice->no_pol ?: ($invoice->unit->plat_nomor ?? '—') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Jenis Kendaraan</td>
                        <td class="info-sep">:</td>
                        <td class="info-val">{{ strtoupper($invoice->jenis_mobil ?: ($invoice->unit->merk_tipe ?? '—')) }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Lokasi / Pos</td>
                        <td class="info-sep">:</td>
                        <td class="info-val">{{ $invoice->lokasi ?: ($invoice->unit->lokasi_pos ?? '—') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TABEL ITEM (PADDING 10-12px) --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;  text-align: center;">NO</th>
                <th style="width: 11%; text-align: center;">KD. ITEM</th>
                <th style="width: 30%; text-align: left;   padding-left: 10px;">NAMA ITEM / JENIS PERBAIKAN</th>
                <th style="width: 6%;  text-align: center;">JML</th>
                <th style="width: 8%;  text-align: center;">SATUAN</th>
                <th style="width: 13%; text-align: right;  padding-right: 10px;">HARGA (RP)</th>
                <th style="width: 9%;  text-align: center;">POT. (%)</th>
                <th style="width: 18%; text-align: right;  padding-right: 10px;">TOTAL (RP)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $i => $item)
                <tr>
                    <td style="text-align: center; color: #4B5563;">{{ $i + 1 }}</td>
                    <td style="text-align: center; font-style: italic; color: #16244f;">{{ $item->kode_item ?: '—' }}</td>
                    <td style="padding-left: 10px; font-weight: bold; color: #0F172A;">{{ ucwords(strtolower(trim($item->jenis_perbaikan))) }}</td>
                    <td style="text-align: center;">{{ rtrim(rtrim(number_format($item->vol, 2, ',', '.'), '0'), ',') }}</td>
                    <td style="text-align: center; color: #4B5563;">{{ ucwords(strtolower(trim($item->satuan))) }}</td>
                    <td style="text-align: right; padding-right: 10px;">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td style="text-align: center; color: #4B5563;">{{ (float)$item->potongan_persen > 0 ? rtrim(rtrim(number_format($item->potongan_persen, 2, ',', '.'), '0'), ',') . '%' : '0%' }}</td>
                    <td style="text-align: right; padding-right: 10px; font-weight: bold; color: #0F172A;">{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
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
            <tr class="row-subtotal">
                <td colspan="7" class="subtotal-label">SUBTOTAL ITEMS:</td>
                <td class="subtotal-val">Rp {{ number_format($subtotalVal, 0, ',', '.') }}</td>
            </tr>
            @if($potonganPct > 0)
            <tr class="row-extra">
                <td colspan="7" class="extra-label">Potongan / Diskon ({{ rtrim(rtrim(number_format($potonganPct, 2, ',', '.'), '0'), ',') }}%):</td>
                <td class="extra-val-discount">- Rp {{ number_format($potonganNominal, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($pajakPct > 0)
            <tr class="row-extra">
                <td colspan="7" class="extra-label">Pajak / PPN ({{ rtrim(rtrim(number_format($pajakPct, 2, ',', '.'), '0'), ',') }}%):</td>
                <td class="extra-val">+ Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if((float)$invoice->biaya_lain > 0)
            <tr class="row-extra">
                <td colspan="7" class="extra-label">Biaya Lainnya:</td>
                <td class="extra-val">+ Rp {{ number_format($invoice->biaya_lain, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="row-total">
                <td colspan="7" class="total-label">TOTAL AKHIR INVOICE:</td>
                <td class="total-val">Rp {{ number_format($invoice->total_biaya, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="ttd-table">
        <tr>
            <td style="width: 58%;"></td>
            <td class="ttd-cell">
                <div class="ttd-kota">Soreang, {{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</div>
                <div class="ttd-jabatan">{{ $pejabatKasi->jabatan ?? 'Kepala Seksi Pemeliharaan Sarana Dan Prasarana' }}</div>
                <div class="ttd-space"></div>
                <div class="ttd-nama">{{ $pejabatKasi->name ?? 'Ahmad Kuswara, S.M., M.M.' }}</div>
                <div class="ttd-nip">NIP. {{ $pejabatKasi->nip ?? '197209212008011001' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
