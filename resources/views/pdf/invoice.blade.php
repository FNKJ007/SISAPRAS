<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->nomor_invoice }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 12mm 12mm;
        }
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            font-size: 8.5pt;
            color: #0F172A;
            line-height: 1.3;
            background: #FFFFFF;
        }

        /* Kop Surat */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #0F172A;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .kop-logo {
            width: 50px;
            height: auto;
        }
        .kop-center {
            text-align: center;
            padding: 0 8px;
        }
        .kop-h1 {
            font-size: 9pt;
            font-weight: bold;
            color: #0F172A;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-h2 {
            font-size: 11pt;
            font-weight: bold;
            color: #C0201F;
            text-transform: uppercase;
            margin: 2px 0;
        }
        .kop-h3 {
            font-size: 7.5pt;
            color: #475569;
            font-weight: normal;
        }

        /* Judul */
        .judul-box {
            text-align: center;
            margin-bottom: 12px;
        }
        .judul-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1B2A6B;
            text-decoration: underline;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .judul-nomor {
            font-size: 8.5pt;
            font-weight: bold;
            color: #64748B;
        }

        /* Metadata Box */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            margin-bottom: 12px;
        }
        .meta-table td {
            padding: 4px 6px;
            font-size: 8pt;
            vertical-align: top;
        }
        .meta-label {
            color: #64748B;
            width: 100px;
        }
        .meta-val-bold {
            font-weight: bold;
            color: #1B2A6B;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            border: 1px solid #CBD5E1;
            margin-bottom: 12px;
        }
        .items-table th {
            background-color: #1B2A6B;
            color: #FFFFFF;
            font-weight: bold;
            padding: 5px 6px;
            font-size: 7.5pt;
            text-transform: uppercase;
            border: 1px solid #CBD5E1;
        }
        .items-table td {
            border: 1px solid #CBD5E1;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .items-table tr.even {
            background-color: #FAFAFA;
        }
        .items-table tfoot td {
            border: 1px solid #CBD5E1;
        }

        /* Signatures */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }
        .ttd-table td {
            vertical-align: top;
            font-size: 8pt;
            line-height: 1.3;
        }
        .ttd-space {
            height: 48px;
        }
        .ttd-name {
            font-weight: bold;
            color: #0F172A;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    {{-- Kop Surat Resmi --}}
    <table class="kop-table">
        <tr>
            <td style="width: 55px; text-align: left;">
                @if(!empty($logoKabData))
                    <img src="{{ $logoKabData }}" class="kop-logo" alt="Logo Pemkab">
                @endif
            </td>
            <td class="kop-center">
                <div class="kop-h1">PEMERINTAH KABUPATEN BANDUNG</div>
                <div class="kop-h2">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                <div class="kop-h3">Bidang Sarana, Prasarana dan Informasi — Seksi Pemeliharaan Sarana dan Prasarana</div>
            </td>
            <td style="width: 55px; text-align: right;">
                @if(!empty($logoDamkarData))
                    <img src="{{ $logoDamkarData }}" class="kop-logo" alt="Logo Damkar">
                @endif
            </td>
        </tr>
    </table>

    {{-- Judul Dokumen --}}
    <div class="judul-box">
        <div class="judul-title">INVOICE PEMELIHARAAN KENDARAAN</div>
        <div class="judul-nomor">Nomor: {{ $invoice->nomor_invoice }}</div>
    </div>

    {{-- Data Invoice & Unit (2 Kolom) --}}
    <table class="meta-table">
        <tr>
            {{-- Kolom Kiri --}}
            <td style="width: 50%; padding: 6px 8px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="meta-label">Bengkel / Rekanan</td>
                        <td style="width: 8px;">:</td>
                        <td class="meta-val-bold">{{ $invoice->nama_bengkel ?: 'CV. PRATAMA MOTOR' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">No. Invoice</td>
                        <td>:</td>
                        <td style="font-weight: bold; color: #0F172A;">{{ $invoice->nomor_invoice }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Tanggal Invoice</td>
                        <td>:</td>
                        <td>{{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Tahun Anggaran</td>
                        <td>:</td>
                        <td>{{ $invoice->tahun_anggaran }}</td>
                    </tr>
                </table>
            </td>
            {{-- Kolom Kanan --}}
            <td style="width: 50%; padding: 6px 8px; border-left: 1px solid #CBD5E1;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="meta-label">No. Lambung</td>
                        <td style="width: 8px;">:</td>
                        <td class="meta-val-bold">{{ $invoice->no_lambung }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">No. Polisi (TNKB)</td>
                        <td>:</td>
                        <td style="font-weight: bold; color: #0F172A;">{{ $invoice->no_pol ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Jenis Kendaraan</td>
                        <td>:</td>
                        <td>{{ $invoice->jenis_mobil ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Lokasi / Pos</td>
                        <td>:</td>
                        <td>{{ $invoice->lokasi ?: '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Rincian Item --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 60px; text-align: center;">Kd. Item</th>
                <th style="text-align: left;">Nama Item / Jenis Perbaikan</th>
                <th style="width: 35px; text-align: center;">Jml</th>
                <th style="width: 45px; text-align: center;">Satuan</th>
                <th style="width: 75px; text-align: right;">Harga (Rp)</th>
                <th style="width: 45px; text-align: center;">Pot (%)</th>
                <th style="width: 85px; text-align: right;">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $i => $item)
                <tr class="{{ $i % 2 == 1 ? 'even' : '' }}">
                    <td style="text-align: center; color: #64748B;">{{ $i + 1 }}</td>
                    <td style="text-align: center; font-family: monospace;">{{ $item->kode_item ?: '—' }}</td>
                    <td style="font-weight: bold;">{{ ucwords(strtolower(trim($item->jenis_perbaikan))) }}</td>
                    <td style="text-align: center;">{{ rtrim(rtrim(number_format($item->vol, 2, ',', '.'), '0'), ',') }}</td>
                    <td style="text-align: center; color: #64748B;">{{ ucwords(strtolower(trim($item->satuan))) }}</td>
                    <td style="text-align: right;">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td style="text-align: center; color: #64748B;">{{ (float)$item->potongan_persen > 0 ? rtrim(rtrim(number_format($item->potongan_persen, 2, ',', '.'), '0'), ',') . '%' : '0%' }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
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
            <tr style="background: #F8FAFC;">
                <td colspan="7" style="text-align: right; font-weight: bold; color: #475569;">SUBTOTAL ITEMS:</td>
                <td style="text-align: right; font-weight: bold;">Rp {{ number_format($subtotalVal, 0, ',', '.') }}</td>
            </tr>
            @if($potonganPct > 0)
                <tr>
                    <td colspan="7" style="text-align: right; color: #64748B;">Potongan / Diskon Tambahan ({{ rtrim(rtrim(number_format($potonganPct, 2, ',', '.'), '0'), ',') }}%):</td>
                    <td style="text-align: right; font-weight: bold; color: #DC2626;">- Rp {{ number_format($potonganNominal, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($pajakPct > 0)
                <tr>
                    <td colspan="7" style="text-align: right; color: #64748B;">Pajak / PPN ({{ rtrim(rtrim(number_format($pajakPct, 2, ',', '.'), '0'), ',') }}%):</td>
                    <td style="text-align: right; font-weight: bold;">+ Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if((float)$invoice->biaya_lain > 0)
                <tr>
                    <td colspan="7" style="text-align: right; color: #64748B;">Biaya Lainnya:</td>
                    <td style="text-align: right; font-weight: bold;">+ Rp {{ number_format($invoice->biaya_lain, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr style="background: #F1F5F9;">
                <td colspan="7" style="text-align: right; font-weight: bold; color: #0F172A; text-transform: uppercase;">TOTAL AKHIR INVOICE:</td>
                <td style="text-align: right; font-weight: bold; color: #1B2A6B; font-size: 9pt;">Rp {{ number_format($invoice->total_biaya, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Tanda Tangan --}}
    <table class="ttd-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%; text-align: center;">
                <div>Soreang, {{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</div>
                <div style="font-weight: bold; color: #0F172A; margin-top: 3px;">Kepala Seksi Pemeliharaan Sarana Dan Prasarana</div>
                <div class="ttd-space"></div>
                <div class="ttd-name">{{ $pejabatKasi->name ?? 'Ahmad Kuswara, S.M., M.M.' }}</div>
                <div style="font-size: 7.5pt; color: #64748B; margin-top: 2px;">NIP. {{ $pejabatKasi->nip ?? '197209212008011001' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
