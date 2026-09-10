@php
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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->nomor_invoice }}</title>
    <style>
        @page {
            margin: 36pt 45pt 36pt 45pt;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8pt;
            color: #111827;
            line-height: 1.3;
            background: #FFFFFF;
        }
        table {
            border-collapse: collapse;
        }
    </style>
</head>
<body>

    {{-- Kop Surat Resmi --}}
    <table style="width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 4px;">
        <tr>
            <td style="width: 12%; text-align: left; vertical-align: middle;">
                @if(!empty($logoKabData))
                    <img src="{{ $logoKabData }}" style="width: 48px; height: auto; display: block;" alt="Logo Pemkab">
                @endif
            </td>
            <td style="width: 76%; text-align: center; vertical-align: middle; padding: 0 4px;">
                <div style="font-size: 11pt; font-weight: bold; color: #000000; text-transform: uppercase; line-height: 1.25;">PEMERINTAH KABUPATEN BANDUNG</div>
                <div style="font-size: 13pt; font-weight: bold; color: #C0201F; text-transform: uppercase; margin-top: 2px; margin-bottom: 2px; line-height: 1.25;">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                <div style="font-size: 8pt; color: #374151; line-height: 1.25;">Bidang Sarana, Prasarana dan Informasi — Seksi Pemeliharaan Sarana dan Prasarana</div>
            </td>
            <td style="width: 12%; text-align: right; vertical-align: middle;">
                @if(!empty($logoDamkarData))
                    <img src="{{ $logoDamkarData }}" style="width: 48px; height: auto; display: block; margin-left: auto;" alt="Logo Damkar">
                @endif
            </td>
        </tr>
    </table>
    <div style="border-top: 2px solid #000000; border-bottom: 0.5px solid #000000; height: 1.5px; margin-top: 4px; margin-bottom: 10px;"></div>

    {{-- Judul Dokumen --}}
    <div style="text-align: center; margin-bottom: 10px;">
        <div style="font-size: 11.5pt; font-weight: bold; color: #1B2A6B; text-decoration: underline; letter-spacing: 0.5px; text-transform: uppercase;">INVOICE PEMELIHARAAN KENDARAAN</div>
        <div style="font-size: 8.5pt; font-weight: bold; color: #4B5563; margin-top: 2px;">Nomor: {{ $invoice->nomor_invoice }}</div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid #CBD5E1; background: #F8FAFC; table-layout: fixed; font-size: 8pt;">
        <tr>
            <td style="width: 17%; padding: 3.5px 6px; color: #4B5563;">Bengkel / Rekanan</td>
            <td style="width: 2%; padding: 3.5px 0; text-align: center; color: #4B5563;">:</td>
            <td style="width: 31%; padding: 3.5px 6px; font-weight: bold; color: #1B2A6B;">{{ $invoice->nama_bengkel ?: 'CV. PRATAMA MOTOR' }}</td>
            <td style="width: 17%; padding: 3.5px 6px; color: #4B5563; border-left: 1px solid #CBD5E1;">No. Lambung</td>
            <td style="width: 2%; padding: 3.5px 0; text-align: center; color: #4B5563;">:</td>
            <td style="width: 31%; padding: 3.5px 6px; font-weight: bold; color: #1B2A6B;">{{ $invoice->no_lambung }}</td>
        </tr>
        <tr>
            <td style="padding: 3.5px 6px; color: #4B5563;">No. Invoice</td>
            <td style="padding: 3.5px 0; text-align: center; color: #4B5563;">:</td>
            <td style="padding: 3.5px 6px; font-weight: bold; color: #111827;">{{ $invoice->nomor_invoice }}</td>
            <td style="padding: 3.5px 6px; color: #4B5563; border-left: 1px solid #CBD5E1;">No. Polisi (TNKB)</td>
            <td style="padding: 3.5px 0; text-align: center; color: #4B5563;">:</td>
            <td style="padding: 3.5px 6px; font-weight: bold; color: #111827;">{{ $invoice->no_pol ?: '—' }}</td>
        </tr>
        <tr>
            <td style="padding: 3.5px 6px; color: #4B5563;">Tanggal Invoice</td>
            <td style="padding: 3.5px 0; text-align: center; color: #4B5563;">:</td>
            <td style="padding: 3.5px 6px; color: #111827;">{{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : '—' }}</td>
            <td style="padding: 3.5px 6px; color: #4B5563; border-left: 1px solid #CBD5E1;">Jenis Kendaraan</td>
            <td style="padding: 3.5px 0; text-align: center; color: #4B5563;">:</td>
            <td style="padding: 3.5px 6px; color: #111827;">{{ $invoice->jenis_mobil ?: '—' }}</td>
        </tr>
        <tr>
            <td style="padding: 3.5px 6px; color: #4B5563;">Tahun Anggaran</td>
            <td style="padding: 3.5px 0; text-align: center; color: #4B5563;">:</td>
            <td style="padding: 3.5px 6px; color: #111827;">{{ $invoice->tahun_anggaran }}</td>
            <td style="padding: 3.5px 6px; color: #4B5563; border-left: 1px solid #CBD5E1;">Lokasi / Pos</td>
            <td style="padding: 3.5px 0; text-align: center; color: #4B5563;">:</td>
            <td style="padding: 3.5px 6px; color: #111827;">{{ $invoice->lokasi ?: '—' }}</td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; font-size: 7.8pt; border: 1px solid #CBD5E1;">
        <thead>
            <tr style="background-color: #1B2A6B; color: #FFFFFF;">
                <th style="width: 4%; text-align: center; padding: 5px 2px; border: 1px solid #1B2A6B; color: #FFFFFF; font-weight: bold; font-size: 7.5pt;">NO</th>
                <th style="width: 11%; text-align: center; padding: 5px 2px; border: 1px solid #1B2A6B; color: #FFFFFF; font-weight: bold; font-size: 7.5pt;">KD. ITEM</th>
                <th style="width: 35%; text-align: left; padding: 5px 6px; border: 1px solid #1B2A6B; color: #FFFFFF; font-weight: bold; font-size: 7.5pt;">NAMA ITEM / JENIS PERBAIKAN</th>
                <th style="width: 6%; text-align: center; padding: 5px 2px; border: 1px solid #1B2A6B; color: #FFFFFF; font-weight: bold; font-size: 7.5pt;">VOL</th>
                <th style="width: 8%; text-align: center; padding: 5px 2px; border: 1px solid #1B2A6B; color: #FFFFFF; font-weight: bold; font-size: 7.5pt;">SATUAN</th>
                <th style="width: 15%; text-align: right; padding: 5px 6px; border: 1px solid #1B2A6B; color: #FFFFFF; font-weight: bold; font-size: 7.5pt;">HARGA (RP)</th>
                <th style="width: 7%; text-align: center; padding: 5px 2px; border: 1px solid #1B2A6B; color: #FFFFFF; font-weight: bold; font-size: 7.5pt;">POT (%)</th>
                <th style="width: 14%; text-align: right; padding: 5px 6px; border: 1px solid #1B2A6B; color: #FFFFFF; font-weight: bold; font-size: 7.5pt;">TOTAL (RP)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $i => $item)
                <tr style="{{ $i % 2 == 1 ? 'background-color: #F8FAFC;' : 'background-color: #FFFFFF;' }}">
                    <td style="text-align: center; padding: 3.5px 2px; border: 1px solid #E2E8F0; color: #64748B;">{{ $i + 1 }}</td>
                    <td style="text-align: center; padding: 3.5px 2px; border: 1px solid #E2E8F0; font-family: monospace;">{{ $item->kode_item ?: '—' }}</td>
                    <td style="padding: 3.5px 6px; border: 1px solid #E2E8F0; font-weight: bold; color: #0F172A;">{{ ucwords(strtolower(trim($item->jenis_perbaikan))) }}</td>
                    <td style="text-align: center; padding: 3.5px 2px; border: 1px solid #E2E8F0;">{{ rtrim(rtrim(number_format($item->vol, 2, ',', '.'), '0'), ',') }}</td>
                    <td style="text-align: center; padding: 3.5px 2px; border: 1px solid #E2E8F0; color: #4B5563;">{{ ucwords(strtolower(trim($item->satuan))) }}</td>
                    <td style="text-align: right; padding: 3.5px 6px; border: 1px solid #E2E8F0;">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td style="text-align: center; padding: 3.5px 2px; border: 1px solid #E2E8F0; color: #64748B;">{{ (float)$item->potongan_persen > 0 ? rtrim(rtrim(number_format($item->potongan_persen, 2, ',', '.'), '0'), ',') . '%' : '0%' }}</td>
                    <td style="text-align: right; padding: 3.5px 6px; border: 1px solid #E2E8F0; font-weight: bold; color: #0F172A;">{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
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
                $totalAkhir = (float) $invoice->total_biaya;
                
                $extraRows = 1 + ($potonganPct > 0 ? 1 : 0) + ($pajakPct > 0 ? 1 : 0) + ((float)$invoice->biaya_lain > 0 ? 1 : 0) + 1;
            @endphp
            <tr>
                <td colspan="5" rowspan="{{ $extraRows }}" style="vertical-align: top; background-color: #F8FAFC; padding: 5px 8px; border: 1px solid #E2E8F0;">
                    <div style="font-size: 7pt; font-weight: bold; color: #64748B; text-transform: uppercase; margin-bottom: 2px;">Terbilang :</div>
                    <div style="font-size: 7.5pt; font-style: italic; font-weight: bold; color: #1B2A6B; line-height: 1.3; padding: 3px 6px; background-color: #FFFFFF; border: 1px solid #CBD5E1;">
                        # {{ invoiceTerbilang($totalAkhir) }} #
                    </div>
                    @if($invoice->catatan)
                        <div style="margin-top: 3px; font-size: 7pt; color: #4B5563;">
                            <strong>Catatan:</strong> {{ $invoice->catatan }}
                        </div>
                    @endif
                </td>
                <td colspan="2" style="text-align: right; font-weight: bold; color: #4B5563; background-color: #F8FAFC; padding: 3.5px 6px; border: 1px solid #E2E8F0;">SUBTOTAL:</td>
                <td style="text-align: right; font-weight: bold; background-color: #F8FAFC; padding: 3.5px 6px; border: 1px solid #E2E8F0;">Rp {{ number_format($subtotalVal, 0, ',', '.') }}</td>
            </tr>
            @if($potonganPct > 0)
                <tr>
                    <td colspan="2" style="text-align: right; color: #64748B; padding: 2.5px 6px; border: 1px solid #E2E8F0;">Diskon ({{ rtrim(rtrim(number_format($potonganPct, 2, ',', '.'), '0'), ',') }}%):</td>
                    <td style="text-align: right; font-weight: bold; color: #DC2626; padding: 2.5px 6px; border: 1px solid #E2E8F0;">- Rp {{ number_format($potonganNominal, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($pajakPct > 0)
                <tr>
                    <td colspan="2" style="text-align: right; color: #64748B; padding: 2.5px 6px; border: 1px solid #E2E8F0;">PPN ({{ rtrim(rtrim(number_format($pajakPct, 2, ',', '.'), '0'), ',') }}%):</td>
                    <td style="text-align: right; font-weight: bold; color: #0F172A; padding: 2.5px 6px; border: 1px solid #E2E8F0;">+ Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if((float)$invoice->biaya_lain > 0)
                <tr>
                    <td colspan="2" style="text-align: right; color: #64748B; padding: 2.5px 6px; border: 1px solid #E2E8F0;">Biaya Lainnya:</td>
                    <td style="text-align: right; font-weight: bold; padding: 2.5px 6px; border: 1px solid #E2E8F0;">+ Rp {{ number_format($invoice->biaya_lain, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr style="background-color: #1B2A6B; color: #FFFFFF;">
                <td colspan="2" style="text-align: right; font-weight: bold; color: #FFFFFF; font-size: 7.5pt; padding: 4px 6px; border: 1px solid #1B2A6B; text-transform: uppercase;">TOTAL AKHIR:</td>
                <td style="text-align: right; font-weight: bold; color: #FFFFFF; font-size: 8pt; padding: 4px 6px; border: 1px solid #1B2A6B;">Rp {{ number_format($totalAkhir, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Tanda Tangan Dua Pihak --}}
    <table style="width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 12px; page-break-inside: avoid;">
        <tr>
            {{-- Pihak Bengkel / Rekanan --}}
            <td style="width: 44%; text-align: center; vertical-align: top; font-size: 8pt;">
                <div style="color: #4B5563;">Penyedia Jasa / Bengkel Rekanan</div>
                <div style="font-weight: bold; color: #0F172A; margin-top: 2px;">{{ $invoice->nama_bengkel ?: 'CV. PRATAMA MOTOR' }}</div>
                <div style="height: 45px;"></div>
                <div style="font-weight: bold; color: #0F172A;">( ..................................................... )</div>
                <div style="font-size: 7pt; color: #64748B; margin-top: 2px;">Cap &amp; Tanda Tangan</div>
            </td>
            <td style="width: 12%;"></td>
            {{-- Pihak Dinas --}}
            <td style="width: 44%; text-align: center; vertical-align: top; font-size: 8pt;">
                <div style="color: #4B5563;">Soreang, {{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</div>
                <div style="font-weight: bold; color: #0F172A; margin-top: 2px;">Kepala Seksi Pemeliharaan Sarana Dan Prasarana</div>
                <div style="height: 45px;"></div>
                <div style="font-weight: bold; color: #0F172A; text-decoration: underline;">{{ $pejabatKasi->name ?? 'Ahmad Kuswara, S.M., M.M.' }}</div>
                <div style="font-size: 7pt; color: #64748B; margin-top: 2px;">NIP. {{ $pejabatKasi->nip ?? '197209212008011001' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
