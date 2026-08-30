<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — {{ $pengajuan->kode_verifikasi ?? 'HAR-0000' }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            background-color: #525659;
            color: #000000;
            padding: 20px 0;
            font-size: 12.5px;
            line-height: 1.45;
            -webkit-font-smoothing: antialiased;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        /* Top Action Bar */
        .no-print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 54px;
            background: #1E293B;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .no-print-bar h3 {
            font-size: 15px;
            font-weight: 700;
            color: #F8FAFC;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-print {
            background: #2563EB;
            color: #FFFFFF;
        }
        .btn-print:hover { background: #1D4ED8; }
        .btn-download {
            background: #16A34A;
            color: #FFFFFF;
        }
        .btn-download:hover { background: #15803D; }

        .document-wrapper {
            margin-top: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        /* Standard A4 Paper (210mm x 297mm) */
        .paper-page {
            width: 210mm;
            min-height: 297mm;
            height: 297mm;
            background: #FFFFFF;
            padding: 15mm 20mm 15mm 20mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Kop Surat Resmi Dinas */
        .kop-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .kop-logo {
            width: 72px;
            height: auto;
        }
        .kop-center {
            flex: 1;
            text-align: center;
            padding: 0 10px;
        }
        .kop-center .h1-line {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #000000;
            margin-bottom: 2px;
        }
        .kop-center .h2-line {
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #000000;
            margin-bottom: 3px;
        }
        .kop-center .address-line {
            font-size: 11px;
            font-weight: 400;
            color: #000000;
            margin-bottom: 1px;
        }
        .kop-center .contact-line {
            font-size: 11px;
            font-weight: 400;
            color: #000000;
        }
        .kop-center a {
            color: #000000;
            text-decoration: underline;
        }

        /* Divider Line Under Kop Surat */
        .kop-divider {
            border: none;
            border-top: 2.5px solid #000000;
            margin-top: 6px;
            margin-bottom: 18px;
        }

        /* Top Meta & Destination Grid */
        .meta-dest-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            font-size: 12.5px;
            font-weight: 400;
        }
        .meta-left {
            width: 63%;
        }
        .meta-left table, .dest-right table {
            border-collapse: collapse;
        }
        .meta-left td, .dest-right td {
            padding: 1.5px 0;
            vertical-align: top;
            font-weight: 400;
        }
        .dest-right {
            text-align: left;
            width: 35%;
            font-weight: 400;
        }

        /* Body Paragraphs */
        .salutation {
            margin-left: 45px;
            margin-bottom: 8px;
            font-weight: 400;
        }
        .body-p {
            text-align: justify;
            text-indent: 45px;
            line-height: 1.45;
            margin-bottom: 12px;
            font-weight: 400;
        }

        /* Details Grid */
        .details-table {
            margin: 8px 0 14px 30px;
            border-collapse: collapse;
            font-size: 12.5px;
            font-weight: 400;
        }
        .details-table td {
            padding: 2.5px 0;
            vertical-align: top;
            font-weight: 400;
        }
        .details-table td.label-col {
            width: 210px;
            font-weight: 400;
        }
        .details-table td.colon-col {
            width: 15px;
            font-weight: 400;
        }

        /* TTD Block Page 1 */
        .ttd-container-p1 {
            margin-top: auto;
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }
        .ttd-box {
            text-align: center;
            width: 320px;
            font-size: 12.5px;
            line-height: 1.3;
        }
        .ttd-space {
            height: 75px;
        }

        /* QR Code Bottom Left Page 1 */
        .qr-footer-p1 {
            position: absolute;
            bottom: 15mm;
            left: 20mm;
        }
        .qr-img {
            width: 75px;
            height: 75px;
        }

        /* PAGE 2 STYLING (LAMPIRAN) */
        .lampiran-banner {
            border: 2px solid #000000;
            padding: 7px 12px;
            text-align: center;
            font-weight: 700;
            font-size: 12.5px;
            text-transform: uppercase;
            margin-bottom: 14px;
            letter-spacing: 0.3px;
        }

        .lampiran-meta-box {
            border: 1px solid #000000;
            padding: 10px 14px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
        }
        .lampiran-meta-left table {
            border-collapse: collapse;
            font-size: 12px;
        }
        .lampiran-meta-left td {
            padding: 2px 0;
            vertical-align: top;
            font-weight: 400;
        }

        .lampiran-qr-box {
            position: absolute;
            top: 8px;
            right: 12px;
        }
        .lampiran-qr-box img {
            width: 60px;
            height: 60px;
        }

        /* Table Pemeriksaan */
        .table-pemeriksaan {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .table-pemeriksaan th {
            border: 1px solid #000000;
            padding: 6px 6px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            background: #FFFFFF;
        }
        .table-pemeriksaan td {
            border: 1px solid #000000;
            padding: 5px 8px;
            height: 22px;
            font-weight: 400;
        }

        /* TTD Footer Page 2 */
        .lampiran-footer-ttd {
            margin-top: auto;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin-bottom: 25px;
        }

        /* PRINT CSS RESET */
        @media print {
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .document-wrapper {
                margin-top: 0 !important;
                gap: 0 !important;
            }
            .paper-page {
                box-shadow: none !important;
                margin: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
            }
            .paper-page:last-child {
                page-break-after: auto !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar -->
    <div class="no-print-bar">
        <h3>Preview Cetak: {{ $title }}</h3>
        <div style="display:flex; gap:10px;">
            <button onclick="window.print()" class="btn-action btn-print">
                <svg style="width:16px; height:16px; fill:currentColor;" viewBox="0 0 24 24"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
                <span>Cetak / Print PDF</span>
            </button>
            <button onclick="downloadPDFDirect()" class="btn-action btn-download">
                <svg style="width:16px; height:16px; fill:currentColor;" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
                <span>Unduh File PDF</span>
            </button>
        </div>
    </div>

    @php
        $kodeVerif = is_object($pengajuan) && isset($pengajuan->kode_verifikasi) ? $pengajuan->kode_verifikasi : ('HAR-' . date('Ymd') . '-0059');
        $nomorSuratFormat = '000.1.7.2/' . $kodeVerif . '/PEM';
        
        if (is_object($pengajuan) && isset($pengajuan->created_at) && $pengajuan->created_at instanceof \Carbon\Carbon) {
            $tglSuratFormat = $pengajuan->created_at->locale('id')->isoFormat('D MMMM Y');
        } else {
            $tglSuratFormat = '31 Juli 2026';
        }

        $nomorLambungPolisi = is_object($pengajuan) && !empty($pengajuan->nomor_lambung) ? $pengajuan->nomor_lambung : 'P-04 / D 9429 V';
        $jenisKendaraanLabel = is_object($pengajuan) && !empty($pengajuan->jenis_kendaraan) ? ucwords(strtolower(trim($pengajuan->jenis_kendaraan))) : 'Pancar';
        $pengemudiNama = is_object($pengajuan) && !empty($pengajuan->nama_pemegang) ? $pengajuan->nama_pemegang : 'Riki Rohimat';
        $penempatanPos = is_object($pengajuan) && !empty($pengajuan->pos) ? $pengajuan->pos : 'MARGAASIH (TKI)';

        // Pejabat SPI (Kepala Bidang SPI selaku KUASA PENGGUNA ANGGARAN)
        // Diambil langsung dari data resmi master pegawai
        $userKabidSpi = \App\Models\User::where(function($q) {
                $q->where('jabatan', 'LIKE', '%Kepala Bidang SPI%')
                  ->orWhere(function($q2) {
                      $q2->where('bidang', 'LIKE', '%SPI%')
                         ->where('jabatan', 'LIKE', '%Kepala Bidang%');
                  });
            })->first();
        $kabidNama = $userKabidSpi ? $userKabidSpi->name : 'Erpi Suwandi, S.T., M.M.';
        $kabidNip = $userKabidSpi ? $userKabidSpi->nip : '197908202006041010';

        // Pejabat Pemeliharaan (Kasi Pemeliharaan selaku PPTK)
        $userKasiPml = \App\Models\User::where('jabatan', 'LIKE', '%Pemeliharaan%')->first();
        $kasiNama = $userKasiPml ? $userKasiPml->name : 'Ahmad Kuswara, S.M., M.M.';
        $kasiNip = $userKasiPml ? $userKasiPml->nip : '197209212008011001';

        // Data PKS, SPK, dan Bengkel Dinamis dari tabel pengaturan_dokumen (atau fallback jika belum diatur)
        $tahunSurat = is_object($pengajuan) && isset($pengajuan->created_at) && $pengajuan->created_at instanceof \Carbon\Carbon
            ? (int) $pengajuan->created_at->format('Y')
            : (int) date('Y');

        $docConfig = $pengaturanDokumen ?? \App\Models\PengaturanDokumen::getAktif($tahunSurat);

        $pksNomor        = $docConfig->nomor_pks ?? ('000.4.7.2/001/PKS-Pem/Bid.SPI/' . $tahunSurat);
        $spkNomor        = $docConfig->nomor_spk ?? ('SPK-004/I/' . $tahunSurat . '/PRA');
        $pksTanggal      = $docConfig ? $docConfig->tanggal_pks_spk_label : ('9 Januari ' . $tahunSurat);
        $namaBengkel     = $docConfig->nama_bengkel ?? 'CV. Pratama Motor';
        $alamatBengkel   = $docConfig->alamat_bengkel ?? 'Jl. Soekarno Hatta No. 463, Kota Bandung';
        $pimpinanBengkel = $docConfig->nama_pimpinan_bengkel ?? 'CV. Pratama';
        $qrCodeUrl       = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($nomorSuratFormat);
    @endphp

    <div class="document-wrapper" id="pdf-content">

        @if($type == 'permohonanbengkel')
            {{-- ========================================================================= --}}
            {{-- PAGE 1: SURAT PERMOHONAN PEMERIKSAAN KENDARAAN (BENGKEL) --}}
            {{-- ========================================================================= --}}
            <div class="paper-page">
                
                {{-- Kop Surat Resmi --}}
                <div class="kop-container">
                    <img src="{{ asset('images/logo-kabupaten.png') }}" class="kop-logo" alt="Logo Pemkab">
                    <div class="kop-center">
                        <div class="h1-line">Pemerintah Kabupaten Bandung</div>
                        <div class="h2-line">Dinas Pemadam Kebakaran dan Penyelamatan</div>
                        <div class="address-line">Jl. Raya Soreang Km.17 Bandung Telp. (022) 5891113 Soreang 40911</div>
                        <div class="contact-line">Email: <a href="mailto:disdamkar@bandungkab.go.id">disdamkar@bandungkab.go.id</a> Website: <a href="https://disdamkar.bandungkab.go.id" target="_blank">disdamkar.bandungkab.go.id</a></div>
                    </div>
                    <img src="{{ asset('images/logo-damkar.png') }}" class="kop-logo" alt="Logo Damkar">
                </div>
                <div class="kop-divider"></div>

                {{-- Metadata Left & Right Destination --}}
                <div class="meta-dest-grid">
                    <div class="meta-left">
                        <table>
                            <tr>
                                <td style="width:75px;">Nomor</td>
                                <td style="width:12px;">:</td>
                                <td>{{ $nomorSuratFormat }}</td>
                            </tr>
                            <tr>
                                <td>Sifat</td>
                                <td>:</td>
                                <td>Penting</td>
                            </tr>
                            <tr>
                                <td>Lampiran</td>
                                <td>:</td>
                                <td>1 (satu) lembar</td>
                            </tr>
                            <tr>
                                <td>Perihal</td>
                                <td>:</td>
                                <td>Permohonan Pemeriksaan Kendaraan</td>
                            </tr>
                        </table>
                    </div>

                    <div class="dest-right">
                        <div style="margin-bottom: 2px;">Soreang, &nbsp; {{ $tglSuratFormat }}</div>
                        <div>Kepada Yth.</div>
                        <div>Pimpinan {{ $namaBengkel }}</div>
                        <div>di</div>
                        <div>{{ $alamatBengkel }}</div>
                    </div>
                </div>

                {{-- Body Paragraph --}}
                <div class="salutation">Dengan Hormat,</div>
                <div class="body-p">
                    Berdasarkan Perjanjian Kerja Sama (PKS) Nomor: {{ $pksNomor }} dan {{ $spkNomor }} tanggal {{ $pksTanggal }}, dan Surat permohonan pemeliharaan/perbaikan kendaraan Nomor: {{ $nomorSuratFormat }}, {{ $tglSuratFormat }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;, melalui surat ini kami meminta Saudara untuk melaksanakan pengecekan, analisis, dan perincian jumlah biaya atas kendaraan dengan detail sebagai berikut:
                </div>

                {{-- Vehicle Details Grid --}}
                <table class="details-table" style="margin: 8px 0 14px 45px;">
                    <tr>
                        <td class="label-col">Nomor Lambung / Nomor Polisi</td>
                        <td class="colon-col">:</td>
                        <td>{{ $nomorLambungPolisi }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Jenis Kendaraan</td>
                        <td class="colon-col">:</td>
                        <td>{{ $jenisKendaraanLabel }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Pengemudi</td>
                        <td class="colon-col">:</td>
                        <td>{{ $pengemudiNama }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Penempatan</td>
                        <td class="colon-col">:</td>
                        <td>{{ $penempatanPos }}</td>
                    </tr>
                </table>

                <div class="body-p" style="margin-top: 10px;">
                    Demikian permohonan ini kami buat. Atas kerja samanya, kami sampaikan terima kasih.
                </div>

                {{-- TTD Block Page 1 --}}
                <div class="ttd-container-p1">
                    <div class="ttd-box">
                        <div style="font-weight:700;">KEPALA BIDANG SPI</div>
                        <div style="font-weight:700;">selaku</div>
                        <div style="font-weight:700;">KUASA PENGGUNA ANGGARAN</div>
                        <div class="ttd-space"></div>
                        <div style="font-weight:700; text-decoration:underline;">{{ $kabidNama }}</div>
                        <div style="font-weight:400;">Pembina</div>
                        <div style="font-weight:400;">NIP. {{ $kabidNip }}</div>
                    </div>
                </div>

                {{-- QR Code Bottom Left Page 1 --}}
                <div class="qr-footer-p1">
                    <img src="{{ $qrCodeUrl }}" class="qr-img" alt="QR Code Validation">
                </div>

            </div>

            {{-- ========================================================================= --}}
            {{-- PAGE 2: LAMPIRAN PEMERIKSAAN KENDARAAN OPERASIONAL OLEH BENGKEL --}}
            {{-- ========================================================================= --}}
            <div class="paper-page">
                
                {{-- Integrated Outer Border Box --}}
                <div style="border: 2px solid #000000; margin-bottom: 25px;">
                    
                    {{-- Banner Title Box --}}
                    <div style="border-bottom: 2px solid #000000; padding: 7px 12px; text-align: center; font-weight: 700; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                        LAMPIRAN PEMERIKSAAN KENDARAAN OPERASIONAL OLEH BENGKEL
                    </div>

                    {{-- Metadata Box --}}
                    <div style="border-bottom: 2px solid #000000; padding: 8px 14px; position: relative;">
                        <div class="lampiran-meta-left">
                            <table style="border-collapse: collapse; font-size: 12px;">
                                <tr>
                                    <td style="width:190px; padding: 1.5px 0;">Nomor Surat</td>
                                    <td style="width:15px; padding: 1.5px 0;">:</td>
                                    <td style="padding: 1.5px 0;">{{ $nomorSuratFormat }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 1.5px 0;">Tanggal</td>
                                    <td style="padding: 1.5px 0;">:</td>
                                    <td style="padding: 1.5px 0;"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 1.5px 0;">Nomor Lambung / Nomor Polisi</td>
                                    <td style="padding: 1.5px 0;">:</td>
                                    <td style="padding: 1.5px 0;">{{ $nomorLambungPolisi }}</td>
                                </tr>
                            </table>
                        </div>
                        <div style="position: absolute; top: 6px; right: 12px;">
                            <img src="{{ $qrCodeUrl }}" style="width: 55px; height: 55px;" alt="QR Code">
                        </div>
                    </div>

                    {{-- Main Inspection Table --}}
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                        <thead>
                            <tr style="border-bottom: 2px solid #000000;">
                                <th style="width: 45%; border-right: 1px solid #000000; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">JENIS PERBAIKAN</th>
                                <th style="width: 15%; border-right: 1px solid #000000; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">JUMLAH</th>
                                <th style="width: 15%; border-right: 1px solid #000000; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">SATUAN</th>
                                <th style="width: 25%; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($r = 0; $r < 25; $r++)
                                <tr style="{{ $r < 24 ? 'border-bottom: 1px solid #000000;' : '' }}">
                                    <td style="border-right: 1px solid #000000; height: 22px; padding: 4px 8px;"></td>
                                    <td style="border-right: 1px solid #000000; height: 22px; padding: 4px 8px;"></td>
                                    <td style="border-right: 1px solid #000000; height: 22px; padding: 4px 8px;"></td>
                                    <td style="height: 22px; padding: 4px 8px;"></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                {{-- TTD Footer Page 2 --}}
                <div class="lampiran-footer-ttd">
                    <div style="text-align: center; width: 320px; font-size: 12.5px;">
                        <div style="margin-bottom: 6px;">Bandung, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $tahunSurat }}</div>
                        <div style="margin-bottom: 45px;">Pemeriksa,</div>
                        <div style="display: flex; align-items: center; justify-content: center; width: 220px; margin: 0 auto 4px auto; font-weight: 700;">
                            (<span style="display: inline-block; width: 190px; border-bottom: 1.5px solid #000000; margin: 0 4px;"></span>)
                        </div>
                        <div style="font-weight: 700;">{{ $pimpinanBengkel }}</div>
                    </div>
                </div>

            </div>

        @elseif($type == 'permohonanbidang')
            @php
                $nomorSuratBidang = '000.1.7.2/' . $kodeVerif;
                $bidangName = is_object($pengajuan) && !empty($pengajuan->bidang) ? strtoupper($pengajuan->bidang) : 'PEMADAMAN';
                $menyetujuiNama = is_object($pengajuan) && !empty($pengajuan->nama_komandan_regu) ? $pengajuan->nama_komandan_regu : (is_object($pengajuan) && !empty($pengajuan->nama_kabid) ? $pengajuan->nama_kabid : 'Lukman');
                $menyetujuiNip = is_object($pengajuan) && !empty($pengajuan->nip_komandan_regu) ? $pengajuan->nip_komandan_regu : '197606272007011002';
                $pemohonNama = is_object($pengajuan) && !empty($pengajuan->nama_pemegang) ? $pengajuan->nama_pemegang : 'Riki Rohimat';
                $pemohonNip = is_object($pengajuan) && !empty($pengajuan->nip_pemegang) ? $pengajuan->nip_pemegang : '198603032014121002';
                $kabidBidangNama = is_object($pengajuan) && !empty($pengajuan->nama_kepala_bidang) ? $pengajuan->nama_kepala_bidang : 'RD. ASEP BINTANG JOHAR SLAMET S.IP.MSI';
                $kabidBidangNip = is_object($pengajuan) && !empty($pengajuan->nip_kepala_bidang) ? $pengajuan->nip_kepala_bidang : '197006062007011014';
            @endphp

            {{-- ========================================================================= --}}
            {{-- PAGE 1: SURAT PERMOHONAN BIDANG (PERMOHONAN PEMELIHARAAN/PERBAIKAN) --}}
            {{-- ========================================================================= --}}
            <div class="paper-page">
                
                {{-- Header Title --}}
                <div style="text-align: center; font-weight: 700; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.3px; margin-top: 10px;">
                    PERMOHONAN PEMELIHARAAN/PERBAIKAN KENDARAAN OPERASIONAL
                </div>
                <div style="border-top: 2.5px solid #000000; margin-top: 8px; margin-bottom: 22px;"></div>

                {{-- Metadata & Destination --}}
                <div class="meta-dest-grid">
                    <div class="meta-left">
                        <table>
                            <tr>
                                <td style="width:75px;">Nomor</td>
                                <td style="width:12px;">:</td>
                                <td>{{ $nomorSuratBidang }}</td>
                            </tr>
                            <tr>
                                <td>Sifat</td>
                                <td>:</td>
                                <td>Penting</td>
                            </tr>
                            <tr>
                                <td>Lampiran</td>
                                <td>:</td>
                                <td>1 (satu) lembar</td>
                            </tr>
                            <tr>
                                <td>Perihal</td>
                                <td>:</td>
                                <td>Pemeliharaan/Perbaikan Kendaraan</td>
                            </tr>
                        </table>
                    </div>

                    <div class="dest-right">
                        <div style="margin-bottom: 2px;">Soreang, &nbsp; {{ $tglSuratFormat }}</div>
                        <div>Kepada Yth.</div>
                        <div>Kepala Bidang SPI</div>
                        <div>di</div>
                        <div style="text-indent: 15px;">Tempat</div>
                    </div>
                </div>

                {{-- Body Paragraph --}}
                <div class="salutation">Dengan Hormat,</div>
                <div class="body-p">
                    Melalui surat ini, kami sampaikan permohonan pemeliharaan/perbaikan kendaraan operasional agar menunjang sarana pasukan pada Bidang <span style="text-decoration: underline;">{{ $bidangName }}</span> dengan identitas sebagai berikut:
                </div>

                {{-- Vehicle Details Grid --}}
                <table class="details-table" style="margin: 8px 0 14px 45px;">
                    <tr>
                        <td class="label-col">Nomor Lambung / Nomor Polisi</td>
                        <td class="colon-col">:</td>
                        <td>{{ $nomorLambungPolisi }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Jenis Kendaraan</td>
                        <td class="colon-col">:</td>
                        <td>{{ $jenisKendaraanLabel }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Pengemudi</td>
                        <td class="colon-col">:</td>
                        <td>{{ $pengemudiNama }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Penempatan</td>
                        <td class="colon-col">:</td>
                        <td>{{ $penempatanPos }}</td>
                    </tr>
                </table>

                <div class="body-p" style="margin-top: 10px;">
                    Demikian permohonan ini kami buat. Atas perhatian dan kerja samanya, kami sampaikan terima kasih.
                </div>

                {{-- Signatures Block Page 1 --}}
                <div style="margin-top: 35px;">
                    {{-- Row 1: Menyetujui & Pemohon --}}
                    <div style="display: flex; justify-content: space-between; padding: 0 30px; margin-bottom: 20px;">
                        <div style="text-align: center; width: 220px; font-size: 12.5px;">
                            <div>Menyetujui,</div>
                            <div style="height: 60px;"></div>
                            <div style="font-weight: 700; text-decoration: underline;">{{ $menyetujuiNama }}</div>
                            <div style="font-size: 11.5px;">NIP. {{ $menyetujuiNip }}</div>
                        </div>

                        <div style="text-align: center; width: 220px; font-size: 12.5px;">
                            <div>Pemohon,</div>
                            <div style="height: 60px;"></div>
                            <div style="font-weight: 700; text-decoration: underline;">{{ $pemohonNama }}</div>
                            <div style="font-size: 11.5px;">NIP. {{ $pemohonNip }}</div>
                        </div>
                    </div>

                    {{-- Row 2: Mengetahui KEPALA BIDANG PEMADAMAN --}}
                    <div style="text-align: center; margin: 0 auto; width: 360px; font-size: 12.5px;">
                        <div>Mengetahui,</div>
                        <div style="font-weight: 700; text-transform: uppercase;">KEPALA BIDANG {{ $bidangName }}</div>
                        <div style="height: 55px;"></div>
                        <div style="font-weight: 700; text-decoration: underline; text-transform: uppercase;">{{ $kabidBidangNama }}</div>
                        <div style="font-size: 11.5px;">NIP. {{ $kabidBidangNip }}</div>
                    </div>
                </div>

                {{-- QR Code Bottom Left Page 1 --}}
                <div class="qr-footer-p1">
                    <img src="{{ $qrCodeUrl }}" class="qr-img" alt="QR Code Validation">
                </div>

            </div>

            {{-- ========================================================================= --}}
            {{-- PAGE 2: LAMPIRAN PERMOHONAN PEMELIHARAAN/PERBAIKAN KENDARAAN OPERASIONAL --}}
            {{-- ========================================================================= --}}
            <div class="paper-page">
                
                {{-- Integrated Outer Border Box --}}
                <div style="border: 1.5px solid #000000; margin-bottom: 25px;">
                    
                    {{-- Header Title Box --}}
                    <div style="border-bottom: 1.5px solid #000000; padding: 7px 12px; text-align: center; font-weight: 700; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                        LAMPIRAN PERMOHONAN PEMELIHARAAN/PERBAIKAN KENDARAAN OPERASIONAL
                    </div>

                    {{-- Metadata Box --}}
                    <div style="border-bottom: 1.5px solid #000000; padding: 8px 14px; position: relative;">
                        <div class="lampiran-meta-left">
                            <table style="border-collapse: collapse; font-size: 12px;">
                                <tr>
                                    <td style="width:190px; padding: 1.5px 0;">Nomor Surat</td>
                                    <td style="width:15px; padding: 1.5px 0;">:</td>
                                    <td style="padding: 1.5px 0;">{{ $nomorSuratBidang }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 1.5px 0;">Tanggal</td>
                                    <td style="padding: 1.5px 0;">:</td>
                                    <td style="padding: 1.5px 0;">{{ $tglSuratFormat }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 1.5px 0;">Nomor Lambung / Nomor Polisi</td>
                                    <td style="padding: 1.5px 0;">:</td>
                                    <td style="padding: 1.5px 0;">{{ $nomorLambungPolisi }}</td>
                                </tr>
                            </table>
                        </div>
                        <div style="position: absolute; top: 6px; right: 12px;">
                            <img src="{{ $qrCodeUrl }}" style="width: 55px; height: 55px;" alt="QR Code">
                        </div>
                    </div>

                    {{-- Main Table --}}
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                        <thead>
                            <tr style="border-bottom: 1.5px solid #000000;">
                                <th style="width: 45%; border-right: 1px solid #000000; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">ITEM PERBAIKAN</th>
                                <th style="width: 15%; border-right: 1px solid #000000; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">JUMLAH</th>
                                <th style="width: 15%; border-right: 1px solid #000000; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">SATUAN</th>
                                <th style="width: 25%; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $cleanItems = [];
                                if (!empty($pengajuan->item_verifikasis) && is_array($pengajuan->item_verifikasis)) {
                                    foreach ($pengajuan->item_verifikasis as $itemName => $vStatus) {
                                        if ($vStatus === 'disetujui') {
                                            $cleanItems[] = trim($itemName);
                                        }
                                    }
                                }

                                if (empty($cleanItems)) {
                                    if (!empty($pengajuan->items) && count($pengajuan->items) > 0) {
                                        foreach ($pengajuan->items as $it) {
                                            $cleanItems[] = trim($it->deskripsi_kerusakan);
                                        }
                                    } elseif (!empty($pengajuan->item_list) && is_array($pengajuan->item_list)) {
                                        $cleanItems = $pengajuan->item_list;
                                    } elseif (!empty($pengajuan->item_perbaikan)) {
                                        $cleanItems = preg_split('/[,;\n\r]+/', $pengajuan->item_perbaikan);
                                    }
                                }

                                $cleanItems = array_values(array_filter(array_map('trim', $cleanItems)));
                                if (empty($cleanItems)) {
                                    $cleanItems = ['Selang hisap portable'];
                                }
                                $totalRows = max(25, count($cleanItems) + 15);
                            @endphp
                            @for ($r = 0; $r < $totalRows; $r++)
                                @php
                                    $itemText = $cleanItems[$r] ?? '';
                                @endphp
                                <tr style="{{ $r < ($totalRows - 1) ? 'border-bottom: 1px solid #000000;' : '' }}">
                                    <td style="border-right: 1px solid #000000; height: 22px; padding: 4px 8px; font-weight: 400; color: #000000;">{{ $itemText }}</td>
                                    <td style="border-right: 1px solid #000000; height: 22px; padding: 4px 8px;"></td>
                                    <td style="border-right: 1px solid #000000; height: 22px; padding: 4px 8px;"></td>
                                    <td style="height: 22px; padding: 4px 8px;"></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

            </div>

        @else
            @php
                $nomorSuratSP = '000.1.7.2/' . $kodeVerif . '/SP';
                $nomorSuratPEM = '000.1.7.2/' . $kodeVerif . '/PEM';
                $kasiNama = is_object($pengajuan) && !empty($pengajuan->nama_kasi) ? $pengajuan->nama_kasi : 'AHMAD KUSWARA, S.M., M.M';
                $kasiNip  = is_object($pengajuan) && !empty($pengajuan->nip_kasi) ? $pengajuan->nip_kasi : '19720921 200801 1001';
            @endphp

            {{-- ========================================================================= --}}
            {{-- PAGE 1: SURAT PESANAN BARANG DAN PEMELIHARAAN/PERBAIKAN --}}
            {{-- ========================================================================= --}}
            <div class="paper-page">
                
                {{-- Kop Surat Resmi --}}
                <div class="kop-container">
                    <img src="{{ asset('images/logo-kabupaten.png') }}" class="kop-logo" alt="Logo Pemkab">
                    <div class="kop-center">
                        <div class="h1-line">Pemerintah Kabupaten Bandung</div>
                        <div class="h2-line">Dinas Pemadam Kebakaran dan Penyelamatan</div>
                        <div class="address-line">Jl. Raya Soreang Km.17 Bandung Telp. (022) 5891113 Soreang 40911</div>
                        <div class="contact-line">Email: <a href="mailto:disdamkar@bandungkab.go.id">disdamkar@bandungkab.go.id</a> Website: <a href="https://disdamkar.bandungkab.go.id" target="_blank">disdamkar.bandungkab.go.id</a></div>
                    </div>
                    <img src="{{ asset('images/logo-damkar.png') }}" class="kop-logo" alt="Logo Damkar">
                </div>
                <div class="kop-divider"></div>

                {{-- Metadata Left & Right Destination --}}
                <div class="meta-dest-grid">
                    <div class="meta-left">
                        <table>
                            <tr>
                                <td style="width:75px;">Nomor</td>
                                <td style="width:12px;">:</td>
                                <td>{{ $nomorSuratSP }}</td>
                            </tr>
                            <tr>
                                <td>Sifat</td>
                                <td>:</td>
                                <td>Penting</td>
                            </tr>
                            <tr>
                                <td>Lampiran</td>
                                <td>:</td>
                                <td>1 (satu) lembar</td>
                            </tr>
                            <tr>
                                <td>Perihal</td>
                                <td>:</td>
                                <td>Pesanan Barang dan Pemeliharaan/Perbaikan</td>
                            </tr>
                        </table>
                    </div>

                    <div class="dest-right">
                        <div>Kepada Yth.</div>
                        <div>Pimpinan {{ $namaBengkel }}</div>
                        <div>di</div>
                        <div>{{ $alamatBengkel }}</div>
                    </div>
                </div>

                {{-- Body Paragraph --}}
                <div class="salutation">Dengan Hormat,</div>
                <div class="body-p">
                    Berdasarkan hasil pemeriksaan pihak {{ $pimpinanBengkel }} pada lampiran Surat Permohonan Pemeriksaan Kendaraan Nomor : {{ $nomorSuratPEM }} tanggal {{ $tglSuratFormat }} untuk kendaraan dengan detail sebagai berikut:
                </div>

                {{-- Vehicle Details Grid --}}
                <table class="details-table" style="margin: 8px 0 14px 45px;">
                    <tr>
                        <td class="label-col">Nomor Lambung / Nomor Polisi</td>
                        <td class="colon-col">:</td>
                        <td>{{ $nomorLambungPolisi }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Jenis Kendaraan</td>
                        <td class="colon-col">:</td>
                        <td>{{ $jenisKendaraanLabel }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Pengemudi</td>
                        <td class="colon-col">:</td>
                        <td>{{ $pengemudiNama }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Penempatan</td>
                        <td class="colon-col">:</td>
                        <td>{{ $penempatanPos }}</td>
                    </tr>
                </table>

                <div class="body-p" style="margin-top: 10px;">
                    Maka kami mohon Saudara untuk menyediakan suku cadang dan melaksanakan pemeliharaan/perbaikan atas kendaraan tersebut. Adapun detail suku cadang dan pemeliharaan/perbaikan yang diperlukan sebagaimana yang tertera pada lampiran.
                </div>
                <div class="body-p" style="margin-top: 10px;">
                    Demikian surat pesanan ini kami buat sebagai dasar tindak lanjut pemeliharaan/perbaikan sesuai Perjanjian Kerja Sama (PKS) Nomor: {{ $pksNomor }} dan {{ $spkNomor }} tanggal {{ $pksTanggal }}. Atas perhatian dan kerja samanya, kami sampaikan terima kasih.
                </div>

                {{-- Signatures Block Page 1 (2 Signatures Side by Side) --}}
                <div style="margin-top: 25px; width: 100%;">
                    {{-- Date Row Aligned Above Right TTD --}}
                    <div style="display: flex; justify-content: flex-end; padding-right: 10px; margin-bottom: 4px; font-size: 12.5px;">
                        <div style="width: 310px; text-align: center;">Soreang, &nbsp; {{ $tglSuratFormat }}</div>
                    </div>

                    {{-- 2 Signatures Columns --}}
                    <div style="display: flex; justify-content: space-between; padding: 0 10px; align-items: flex-start;">
                        {{-- Left TTD: Kabid SPI --}}
                        <div style="text-align: center; width: 310px; font-size: 12px; line-height: 1.3;">
                            <div style="font-weight:700;">KEPALA BIDANG SPI</div>
                            <div style="font-weight:700;">DAN INFORMASI selaku</div>
                            <div style="font-weight:700;">KUASA PENGGUNA ANGGARAN</div>
                            <div style="height: 60px;"></div>
                            <div style="font-weight:700; text-decoration:underline;">{{ $kabidNama }}</div>
                            <div style="font-weight:400;">Pembina</div>
                            <div style="font-weight:400;">NIP. {{ $kabidNip }}</div>
                        </div>

                        {{-- Right TTD: Kasi Pemeliharaan --}}
                        <div style="text-align: center; width: 310px; font-size: 12px; line-height: 1.3;">
                            <div style="font-weight:700;">KEPALA SEKSI PEMELIHARAAN SARANA</div>
                            <div style="font-weight:700;">SPI selaku</div>
                            <div style="font-weight:700;">PEJABAT PELAKSANA TEKNIS KEGIATAN</div>
                            <div style="height: 60px;"></div>
                            <div style="font-weight:700; text-decoration:underline;">{{ $kasiNama }}</div>
                            <div style="font-weight:400;">Penata</div>
                            <div style="font-weight:400;">NIP. {{ $kasiNip }}</div>
                        </div>
                    </div>
                </div>

                {{-- QR Code Bottom Left Page 1 --}}
                <div class="qr-footer-p1">
                    <img src="{{ $qrCodeUrl }}" class="qr-img" alt="QR Code Validation">
                </div>

            </div>

            {{-- ========================================================================= --}}
            {{-- PAGE 2: LAMPIRAN PESANAN SUKU CADANG PEMELIHARAAN/PERBAIKAN --}}
            {{-- ========================================================================= --}}
            <div class="paper-page">
                
                {{-- Integrated Outer Border Box --}}
                <div style="border: 1.5px solid #000000; margin-bottom: 25px;">
                    
                    {{-- Header Title Box --}}
                    <div style="border-bottom: 1.5px solid #000000; padding: 7px 12px; text-align: center; font-weight: 700; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                        LAMPIRAN PESANAN SUKU CADANG PEMELIHARAAN/PERBAIKAN KENDARAAN OPERASIONAL
                    </div>

                    {{-- Metadata Box --}}
                    <div style="border-bottom: 1.5px solid #000000; padding: 8px 14px; position: relative;">
                        <div class="lampiran-meta-left">
                            <table style="border-collapse: collapse; font-size: 12px;">
                                <tr>
                                    <td style="width:190px; padding: 1.5px 0;">Nomor Surat</td>
                                    <td style="width:15px; padding: 1.5px 0;">:</td>
                                    <td style="padding: 1.5px 0;">{{ $nomorSuratSP }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 1.5px 0;">Tanggal</td>
                                    <td style="padding: 1.5px 0;">:</td>
                                    <td style="padding: 1.5px 0;"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 1.5px 0;">Nomor Lambung / Nomor Polisi</td>
                                    <td style="padding: 1.5px 0;">:</td>
                                    <td style="padding: 1.5px 0;">{{ $nomorLambungPolisi }}</td>
                                </tr>
                            </table>
                        </div>
                        <div style="position: absolute; top: 6px; right: 12px;">
                            <img src="{{ $qrCodeUrl }}" style="width: 55px; height: 55px;" alt="QR Code">
                        </div>
                    </div>

                    {{-- Main Table (No TTD below table) --}}
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                        <thead>
                            <tr style="border-bottom: 1.5px solid #000000;">
                                <th style="width: 45%; border-right: 1px solid #000000; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">JENIS PERBAIKAN</th>
                                <th style="width: 15%; border-right: 1px solid #000000; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">JUMLAH</th>
                                <th style="width: 15%; border-right: 1px solid #000000; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">SATUAN</th>
                                <th style="width: 25%; padding: 6px; font-weight: 700; text-transform: uppercase; text-align: center;">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $cleanItems = [];
                                if (!empty($pengajuan->item_verifikasis) && is_array($pengajuan->item_verifikasis)) {
                                    foreach ($pengajuan->item_verifikasis as $itemName => $vStatus) {
                                        if ($vStatus === 'disetujui') {
                                            $cleanItems[] = trim($itemName);
                                        }
                                    }
                                }

                                if (empty($cleanItems)) {
                                    if (!empty($pengajuan->items) && count($pengajuan->items) > 0) {
                                        foreach ($pengajuan->items as $it) {
                                            $cleanItems[] = trim($it->deskripsi_kerusakan);
                                        }
                                    } elseif (!empty($pengajuan->item_list) && is_array($pengajuan->item_list)) {
                                        $cleanItems = $pengajuan->item_list;
                                    } elseif (!empty($pengajuan->item_perbaikan)) {
                                        $cleanItems = preg_split('/[,;\n\r]+/', $pengajuan->item_perbaikan);
                                    }
                                }

                                $cleanItems = array_values(array_filter(array_map('trim', $cleanItems)));
                                $totalRows = max(26, count($cleanItems) + 15);
                            @endphp
                            @for ($r = 0; $r < $totalRows; $r++)
                                @php
                                    $itemText = $cleanItems[$r] ?? '';
                                @endphp
                                <tr style="{{ $r < ($totalRows - 1) ? 'border-bottom: 1px solid #000000;' : '' }}">
                                    <td style="border-right: 1px solid #000000; height: 22px; padding: 4px 8px; font-weight: 400; color: #000000;">{{ $itemText }}</td>
                                    <td style="border-right: 1px solid #000000; height: 22px; padding: 4px 8px;"></td>
                                    <td style="border-right: 1px solid #000000; height: 22px; padding: 4px 8px;"></td>
                                    <td style="height: 22px; padding: 4px 8px;"></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

            </div>
        @endif

    </div>

    <script>
        function downloadPDFDirect() {
            const element = document.getElementById('pdf-content');
            const opt = {
                margin:       0,
                filename:     '{{ $title }}_{{ $kodeVerif }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>l>
