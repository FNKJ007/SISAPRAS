<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Servis Digital — {{ $unit->nomor_lambung }} ({{ $unit->plat_nomor }})</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: #F1F5F9;
            color: #1E293B;
            padding: 20px;
            font-size: 12px;
            line-height: 1.5;
        }

        /* Top Action Bar (Non-Printable) */
        .no-print-bar {
            max-width: 900px;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #1B2A6B;
            color: #FFFFFF;
            padding: 14px 22px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(27,42,107,0.25);
        }
        .no-print-bar .btn-action {
            background: #22C55E;
            color: #FFFFFF;
            border: none;
            padding: 9px 18px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .no-print-bar .btn-action:hover {
            background: #16A34A;
        }
        .no-print-bar .btn-back {
            background: rgba(255,255,255,0.15);
            color: #FFFFFF;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 9px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12.5px;
            text-decoration: none;
            cursor: pointer;
        }

        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }

        /* Formal Paper Container (Standard 1:1 A4 Kertas Print: 794px) */
        .paper {
            max-width: 794px;
            width: 100%;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 30px 36px;
            border-radius: 4px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            position: relative;
            box-sizing: border-box;
        }

        /* Kop Surat Resmi Kedinasan */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px double #0F172A;
            padding-bottom: 10px;
            margin-bottom: 18px;
            text-align: center;
        }
        .kop-logo {
            width: 62px;
            height: auto;
            flex-shrink: 0;
        }
        .kop-text {
            flex: 1;
            padding: 0 12px;
        }
        .kop-text h4 {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #0F172A;
            text-transform: uppercase;
        }
        .kop-text h2 {
            font-size: 16px;
            font-weight: 800;
            color: #C0201F;
            text-transform: uppercase;
            margin: 2px 0;
        }
        .kop-text h5 {
            font-size: 11px;
            font-weight: 700;
            color: #1E3A8A;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .kop-text p {
            font-size: 10px;
            color: #475569;
        }

        /* Header Judul Dokumen */
        .doc-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title {
            font-size: 14px;
            font-weight: 800;
            color: #0F172A;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-decoration: underline;
        }
        .doc-sub {
            font-size: 11px;
            color: #475569;
            margin-top: 3px;
            font-weight: 600;
        }

        /* Section Box Styles */
        .section-header {
            font-size: 11.5px;
            font-weight: 800;
            color: #1E3A8A;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-left: 3.5px solid #1B2A6B;
            padding-left: 8px;
            margin: 18px 0 8px 0;
        }

        /* Grid Table Info */
        .info-grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 11px;
        }
        .info-grid-table td {
            padding: 6px 8px;
            border: 1px solid #CBD5E1;
        }
        .info-grid-table .label {
            background: #F8FAFC;
            font-weight: 700;
            color: #334155;
            width: 18%;
        }
        .info-grid-table .value {
            font-weight: 600;
            color: #0F172A;
            width: 32%;
        }

        /* Summary KPI Cards */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        .kpi-card {
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }
        .kpi-card .kpi-lbl {
            font-size: 9.5px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
        }
        .kpi-card .kpi-val {
            font-size: 14px;
            font-weight: 800;
            color: #0F172A;
            margin-top: 2px;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 11px;
        }
        .data-table th {
            background: #1B2A6B;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 10.5px;
            text-transform: uppercase;
            padding: 7px 8px;
            border: 1px solid #1B2A6B;
            text-align: left;
        }
        .data-table td {
            padding: 7px 8px;
            border: 1px solid #CBD5E1;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background: #F9FAFB;
        }

        /* Signature Grid */
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
            text-align: center;
            page-break-inside: avoid;
        }
        .sig-box {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100px;
        }
        .sig-title {
            font-size: 11px;
            font-weight: 600;
            color: #334155;
        }
        .sig-name {
            font-size: 12px;
            font-weight: 800;
            color: #0F172A;
            text-decoration: underline;
        }
        .sig-nip {
            font-size: 10.5px;
            color: #64748B;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
                margin: 0;
            }
            .paper {
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                border: none !important;
            }
        }
    </style>
</head>
<body>

    {{-- Formal Document Paper --}}
    <div class="paper" id="document-paper">

        {{-- Kop Surat Resmi Kedinasan --}}
        <div class="kop-surat">
            <img src="{{ asset('images/logo-kabupaten.png') }}" class="kop-logo" alt="Logo Pemkab">
            <div class="kop-text">
                <h4>Pemerintah Kabupaten Bandung</h4>
                <h2>Dinas Pemadam Kebakaran dan Penyelamatan</h2>
                <h5>Bidang SPI</h5>
                <p>Jl. Raya Soreang Km.17 Bandung Telp. (022) 5891113 Soreang 40911</p>
            </div>
            <img src="{{ asset('images/logo-damkar.png') }}" class="kop-logo" alt="Logo Damkar">
        </div>

        {{-- Header Judul Dokumen --}}
        <div class="doc-header">
            <div class="doc-title">BUKU REKAM MEDIS &amp; RIWAYAT SERVIS ARMADA KENDARAAN DINAS</div>
            <div class="doc-sub">Nomor Dokumen: BRM/DISDAMKAR/{{ $unit->nomor_lambung }}/{{ date('Y') }} &nbsp;|&nbsp; Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
        </div>

        {{-- Bagian I: Identitas Kendaraan Dinas --}}
        <div class="section-header">I. Identitas Resmi Kendaraan Dinas</div>
        <table class="info-grid-table">
            <tr>
                <td class="label">Kode / No. Lambung</td>
                <td class="value"><strong style="color:#1E3A8A;">{{ $unit->nomor_lambung }}</strong></td>
                <td class="label">TNKB / Plat Nomor</td>
                <td class="value"><strong>{{ $unit->plat_nomor }}</strong></td>
            </tr>
            <tr>
                <td class="label">Nama Kendaraan</td>
                <td class="value">{{ $unit->nama }}</td>
                <td class="label">Jenis Kendaraan</td>
                <td class="value">{{ $unit->jenis_kendaraan ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Merk / Tipe Armada</td>
                <td class="value">{{ $unit->merk_tipe ?? '—' }}</td>
                <td class="label">Tahun Pembuatan</td>
                <td class="value">{{ $unit->tahun_pembuatan ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Pos Penempatan</td>
                <td class="value">{{ $unit->pos ?? '—' }}</td>
                <td class="label">Status Operasional</td>
                <td class="value">
                    <span style="font-weight:700; color: {{ $unit->status == 'aktif' ? '#047857' : '#B45309' }};">
                        {{ strtoupper($unit->status ?? 'AKTIF') }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">Pengemudi 1</td>
                <td class="value">{{ $unit->pengemudi_1 ?? '—' }}</td>
                <td class="label">Pengemudi 2</td>
                <td class="value">{{ $unit->pengemudi_2 ?? '—' }}</td>
            </tr>
        </table>

        {{-- Bagian II: Ringkasan Rekam Medis Servis --}}
        <div class="section-header">II. Ringkasan Realisasi Pemeliharaan &amp; Anggaran</div>
        <div class="kpi-row">
            <div class="kpi-card">
                <div class="kpi-lbl">Total Pengajuan Perbaikan</div>
                <div class="kpi-val" style="color:#1B2A6B;">{{ $pengajuanList->count() }} Kali</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-lbl">Total Kendali Aktual</div>
                <div class="kpi-val" style="color:#1B2A6B; font-size:12.5px;">Rp {{ number_format($totalBiayaPembayaran, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-lbl">Total Kendali SPJ</div>
                <div class="kpi-val" style="color:#059669; font-size:12.5px;">Rp {{ number_format($totalBiayaAktual, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-lbl">Terakhir Servis</div>
                <div class="kpi-val" style="font-size:12.5px;">{{ $terakhirServis }}</div>
            </div>
        </div>

        {{-- Bagian III: Riwayat Pengajuan Perbaikan --}}
        <div class="section-header">III. Riwayat Pengajuan Perbaikan Kendaraan Dinas</div>
        @if($pengajuanList->isEmpty())
            <div style="background:#F8FAFC; border:1px dashed #CBD5E1; padding:14px; text-align:center; color:#64748B; font-size:11.5px; border-radius:6px; margin-bottom:16px;">
                Belum ada rekam medis pengajuan perbaikan yang tercatat untuk unit kendaraan dinas ini.
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:6%; text-align:center;">No</th>
                        <th style="width:18%;">Tanggal</th>
                        <th style="width:42%;">Item / Komponen Kerusakan</th>
                        <th style="width:34%;">Pemohon &amp; Pos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuanList as $index => $p)
                        @php
                            $tglText = ($p->tanggal_mulai_pengerjaan ?? $p->tanggal_keberangkatan)?->format('d/m/Y')
                                ?? ($p->created_at ? $p->created_at->format('d/m/Y') : '—');
                        @endphp
                        <tr>
                            <td style="text-align:center; font-weight:700;">{{ $index + 1 }}</td>
                            <td style="font-weight:700;">{{ $tglText }}</td>
                            <td style="font-weight:700; color:#1B2A6B;">{{ $p->item_perbaikan }}</td>
                            <td>
                                <div><strong>{{ $p->nama_pemegang }}</strong></div>
                                <div style="font-size:10.5px; color:#64748B;">Pos {{ $p->pos }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Bagian IV.A: Hasil Kartu Kendali Aktual --}}
        <div class="section-header">IV.A. Hasil Rekam Medis Kartu Kendali Aktual (Aktual Pembayaran)</div>
        @if($kendaliPembayaranList->isEmpty())
            <div style="background:#F8FAFC; border:1px dashed #CBD5E1; padding:14px; text-align:center; color:#64748B; font-size:11.5px; border-radius:6px; margin-bottom:16px;">
                Belum ada catatan realisasi pada Kartu Kendali Aktual untuk unit ini.
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:6%; text-align:center;">No</th>
                        <th style="width:18%;">Tgl Invoice</th>
                        <th style="width:28%;">Nomor Invoice</th>
                        <th style="width:26%;">Nama Bengkel</th>
                        <th style="width:22%; text-align:right;">Jumlah Biaya (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kendaliPembayaranList as $index => $inv)
                        <tr>
                            <td style="text-align:center; font-weight:700;">{{ $index + 1 }}</td>
                            <td>{{ $inv->tanggal_invoice ? $inv->tanggal_invoice->format('d/m/Y') : '—' }}</td>
                            <td style="font-weight:700; color:#1B2A6B;">{{ $inv->nomor_invoice }}</td>
                            <td>{{ $inv->nama_bengkel ?? '—' }}</td>
                            <td style="text-align:right; font-weight:700; color:#1B2A6B;">
                                Rp {{ number_format((float)$inv->total_biaya, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#F1F5F9; font-weight:800;">
                        <td colspan="4" style="text-align:right; font-size:10.5px; text-transform:uppercase;">Subtotal Kendali Aktual:</td>
                        <td style="text-align:right; color:#1B2A6B; font-size:11.5px;">
                            Rp {{ number_format($totalBiayaPembayaran, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        @endif

        {{-- Bagian IV.B: Hasil Kartu Kendali SPJ --}}
        <div class="section-header">IV.B. Hasil Rekam Medis Kartu Kendali SPJ (SPJ Pembayaran)</div>
        @if($kendaliAktualList->isEmpty())
            <div style="background:#F8FAFC; border:1px dashed #CBD5E1; padding:14px; text-align:center; color:#64748B; font-size:11.5px; border-radius:6px; margin-bottom:20px;">
                Belum ada catatan pemeliharaan pada Kartu Kendali SPJ untuk unit ini.
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:6%; text-align:center;">No</th>
                        <th style="width:18%;">Tanggal</th>
                        <th style="width:28%;">Nomor Rujukan / Invoice</th>
                        <th style="width:26%;">Nama Bengkel / Pelaksana</th>
                        <th style="width:22%; text-align:right;">Jumlah Biaya (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kendaliAktualList as $index => $inv)
                        <tr>
                            <td style="text-align:center; font-weight:700;">{{ $index + 1 }}</td>
                            <td>{{ $inv->tanggal_invoice ? $inv->tanggal_invoice->format('d/m/Y') : '—' }}</td>
                            <td style="font-weight:700; color:#065F46;">{{ $inv->nomor_invoice }}</td>
                            <td>{{ $inv->nama_bengkel ?? '—' }}</td>
                            <td style="text-align:right; font-weight:700; color:#059669;">
                                Rp {{ number_format((float)$inv->total_biaya, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#F1F5F9; font-weight:800;">
                        <td colspan="4" style="text-align:right; font-size:10.5px; text-transform:uppercase;">Subtotal Kendali SPJ:</td>
                        <td style="text-align:right; color:#059669; font-size:11.5px;">
                            Rp {{ number_format($totalBiayaAktual, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        @endif

        {{-- Bagian V: Lembar Pengesahan Resmi --}}
        <div class="signature-grid">
            <div class="sig-box">
                <div class="sig-title">
                    Mengetahui,<br>
                    <strong>Pengurus Barang / Penanggung Jawab Armada</strong>
                </div>
                <div style="height:55px;"></div>
                <div>
                    <div class="sig-name">UDEN SUHENDI</div>
                    <div class="sig-nip">NIP. 19820512 200801 1 004</div>
                </div>
            </div>
            <div class="sig-box">
                <div class="sig-title">
                    Soreang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    <strong>Kepala Bidang SPI</strong>
                </div>
                <div style="height:55px;"></div>
                <div>
                    <div class="sig-name">M. RACHMAT, S.STP., M.Si.</div>
                    <div class="sig-nip">NIP. 19790414 199810 1 001</div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const element = document.getElementById('document-paper');
            const cleanLambung = '{{ preg_replace("/[^A-Za-z0-9]/", "-", $unit->nomor_lambung) }}';
            const filename = 'Buku-Servis-Digital-' + cleanLambung + '.pdf';

            const opt = {
                margin:       [8, 8, 8, 8],
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            if (window.html2pdf) {
                setTimeout(() => {
                    html2pdf().set(opt).from(element).save().catch(function(err) {
                        console.error("Auto PDF download failed:", err);
                    });
                }, 300);
            }
        });
    </script>
</body>
</html>
