<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — {{ $pengajuan->kode_verifikasi ?? 'HAR-0000' }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
            font-size: 12px;
            line-height: 1.4;
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

        .document-container {
            padding-top: 65px;
            padding-bottom: 40px;
            display: flex;
            justify-content: center;
            background-color: #525659;
            min-height: 100vh;
        }

        #pdf-content {
            width: 210mm;
            margin: 0 auto;
        }

        /* Standard A4 Paper (210mm x 297mm) */
        .paper-page {
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            max-height: 297mm;
            background: #FFFFFF;
            padding: 13mm 18mm 13mm 18mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            page-break-after: always;
            break-after: page;
        }
        .paper-page:last-child {
            margin-bottom: 0;
            page-break-after: auto;
            break-after: auto;
        }

        /* Kop Surat Resmi Dinas */
        .kop-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .kop-logo {
            width: 70px;
            height: auto;
        }
        .kop-center {
            flex: 1;
            text-align: center;
            padding: 0 10px;
        }
        .kop-center .h1-line {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #000000;
            margin-bottom: 1px;
        }
        .kop-center .h2-line {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #000000;
            margin-bottom: 2px;
        }
        .kop-center .address-line {
            font-size: 10.5px;
            font-weight: 400;
            color: #000000;
            margin-bottom: 1px;
        }
        .kop-center .contact-line {
            font-size: 10.5px;
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
            margin-top: 5px;
            margin-bottom: 14px;
        }

        /* Top Meta & Destination Grid */
        .meta-dest-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
            font-size: 12px;
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
            margin-bottom: 6px;
            font-weight: 400;
        }
        .body-p {
            text-align: justify;
            text-indent: 45px;
            line-height: 1.4;
            margin-bottom: 8px;
            font-weight: 400;
        }

        /* Details Grid */
        .details-table {
            margin: 6px 0 10px 45px;
            border-collapse: collapse;
            font-size: 12px;
            font-weight: 400;
        }
        .details-table td {
            padding: 2px 0;
            vertical-align: top;
            font-weight: 400;
        }
        .details-table td.label-col {
            width: 200px;
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
            margin-bottom: 12px;
        }
        .ttd-box {
            text-align: center;
            width: 320px;
            font-size: 12px;
            line-height: 1.3;
        }
        .ttd-space {
            height: 55px;
        }

        /* QR Code Bottom Left Page 1 */
        .qr-footer-p1 {
            position: absolute;
            bottom: 13mm;
            left: 18mm;
        }
        .qr-img {
            width: 65px;
            height: 65px;
        }

        /* PAGE 2 STYLING (LAMPIRAN) */
        .lampiran-outer-box {
            border: 1.5px solid #000000;
            margin-bottom: 18px;
        }

        .lampiran-banner {
            border-bottom: 1.5px solid #000000;
            padding: 6px 10px;
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .lampiran-meta-box {
            border-bottom: 1.5px solid #000000;
            padding: 7px 12px;
            position: relative;
        }
        .lampiran-meta-left table {
            border-collapse: collapse;
            font-size: 11.5px;
        }
        .lampiran-meta-left td {
            padding: 1.5px 0;
            vertical-align: top;
            font-weight: 400;
        }

        .lampiran-qr-box {
            position: absolute;
            top: 6px;
            right: 12px;
        }
        .lampiran-qr-box img {
            width: 50px;
            height: 50px;
        }

        /* Table Pemeriksaan */
        .table-pemeriksaan {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }
        .table-pemeriksaan th {
            border: 1px solid #000000;
            border-bottom: 1.5px solid #000000;
            padding: 5px 6px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
            background: #FFFFFF;
        }
        .table-pemeriksaan td {
            border: 1px solid #000000;
            padding: 3.5px 6px;
            height: 20px;
            font-weight: 400;
        }

        /* TTD Footer Page 2 */
        .lampiran-footer-ttd {
            margin-top: auto;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin-bottom: 15px;
        }

        /* PRINT CSS RESET */
        @media print {
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .document-container {
                padding: 0 !important;
                margin: 0 !important;
                background: #FFFFFF !important;
                display: block !important;
            }
            #pdf-content {
                width: 210mm !important;
                margin: 0 !important;
            }
            .paper-page {
                box-shadow: none !important;
                margin: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                page-break-after: always !important;
                break-after: page !important;
                page-break-inside: avoid !important;
            }
            .paper-page:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
            .table-pemeriksaan th,
            .table-pemeriksaan td {
                border: 1px solid #000000 !important;
            }
            .table-pemeriksaan th {
                border-bottom: 1.5px solid #000000 !important;
            }
            .lampiran-outer-box {
                border: 1.5px solid #000000 !important;
            }
            .lampiran-banner,
            .lampiran-meta-box {
                border-bottom: 1.5px solid #000000 !important;
            }
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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

        // Data PKS, SPK, dan Bengkel Dinamis dari tabel pengaturan_dokumen
        $tahunSurat = is_object($pengajuan) && isset($pengajuan->created_at) && $pengajuan->created_at instanceof \Carbon\Carbon
            ? (int) $pengajuan->created_at->format('Y')
            : (int) date('Y');

        $docConfig = $pengaturanDokumen ?? \App\Models\PengaturanDokumen::getAktif($tahunSurat);

        // Ambil data Pejabat Aktif secara live dari Master Data Pegawai
        $pejabatTtd = \App\Models\User::getPejabatTtd();

        // Pejabat SPI (Kepala Bidang SPI selaku KUASA PENGGUNA ANGGARAN)
        $kabidNama    = $pejabatTtd['kpa'] ? $pejabatTtd['kpa']->name : ($docConfig?->ttd_kpa_nama ?: 'Erpi Suwandi, S.T., M.M.');
        $kabidNip     = $pejabatTtd['kpa'] ? $pejabatTtd['kpa']->nip : ($docConfig?->ttd_kpa_nip ?: '197908202006041010');
        $kabidJabatan = $pejabatTtd['kpa'] ? strtoupper($pejabatTtd['kpa']->jabatan) : ($docConfig?->ttd_kpa_jabatan ?: 'KEPALA BIDANG SARANA, PRASARANA DAN INFORMASI');
        $kabidPangkat = $docConfig?->ttd_kpa_pangkat ?: 'Pembina';

        // Pejabat Pemeliharaan (Kasi Pemeliharaan selaku PPTK)
        $kasiNama    = $pejabatTtd['pptk'] ? $pejabatTtd['pptk']->name : ($docConfig?->ttd_pptk_nama ?: 'Muhammad Lutfiansyah, S.Sos., M.M.');
        $kasiNip     = $pejabatTtd['pptk'] ? $pejabatTtd['pptk']->nip : ($docConfig?->ttd_pptk_nip ?: '199610172020121001');
        $kasiJabatan = $pejabatTtd['pptk'] ? strtoupper($pejabatTtd['pptk']->jabatan) : ($docConfig?->ttd_pptk_jabatan ?: 'KEPALA SEKSI PEMELIHARAAN SARANA DAN PRASARANA');
        $kasiPangkat = $docConfig?->ttd_pptk_pangkat ?: 'Penata';

        $pksNomor        = $docConfig->nomor_pks ?? ('000.4.7.2/001/PKS-Pem/Bid.SPI/' . $tahunSurat);
        $spkNomor        = $docConfig->nomor_spk ?? ('SPK-004/I/' . $tahunSurat . '/PRA');
        $pksTanggal      = $docConfig ? $docConfig->tanggal_pks_spk_label : ('9 Januari ' . $tahunSurat);
        $namaBengkel     = $docConfig->nama_bengkel ?? 'CV. Pratama Motor';
        $alamatBengkel   = $docConfig->alamat_bengkel ?? 'Jl. Soekarno Hatta No. 463, Kota Bandung';
        $pimpinanBengkel = $docConfig->nama_pimpinan_bengkel ?? 'CV. Pratama';
        $qrCodeUrl       = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($nomorSuratFormat);
    @endphp

    <div class="document-container">
        <div id="pdf-content">

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
                    <table class="details-table">
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

                    <div class="body-p" style="margin-top: 6px;">
                        Demikian permohonan ini kami buat. Atas kerja samanya, kami sampaikan terima kasih.
                    </div>

                    {{-- TTD Block Page 1 --}}
                    <div class="ttd-container-p1">
                        <div class="ttd-box">
                            <div style="font-weight:700;">{{ $kabidJabatan }}</div>
                            <div style="font-weight:700;">selaku</div>
                            <div style="font-weight:700;">KUASA PENGGUNA ANGGARAN</div>
                            <div class="ttd-space"></div>
                            <div style="font-weight:700; text-decoration:underline;">{{ $kabidNama }}</div>
                            <div style="font-weight:400;">{{ $kabidPangkat }}</div>
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
                    <div class="lampiran-outer-box">
                        
                        {{-- Banner Title Box --}}
                        <div class="lampiran-banner">
                            LAMPIRAN PEMERIKSAAN KENDARAAN OPERASIONAL OLEH BENGKEL
                        </div>

                        {{-- Metadata Box --}}
                        <div class="lampiran-meta-box">
                            <div class="lampiran-meta-left">
                                <table>
                                    <tr>
                                        <td style="width:190px;">Nomor Surat</td>
                                        <td style="width:15px;">:</td>
                                        <td>{{ $nomorSuratFormat }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal</td>
                                        <td>:</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Nomor Lambung / Nomor Polisi</td>
                                        <td>:</td>
                                        <td>{{ $nomorLambungPolisi }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="lampiran-qr-box">
                                <img src="{{ $qrCodeUrl }}" alt="QR Code">
                            </div>
                        </div>

                        {{-- Main Inspection Table --}}
                        <table class="table-pemeriksaan">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">JENIS PERBAIKAN</th>
                                    <th style="width: 15%;">JUMLAH</th>
                                    <th style="width: 15%;">SATUAN</th>
                                    <th style="width: 25%;">KETERANGAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($r = 0; $r < 20; $r++)
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    {{-- TTD Footer Page 2 --}}
                    <div class="lampiran-footer-ttd">
                        <div style="text-align: center; width: 320px; font-size: 12px;">
                            <div style="margin-bottom: 4px;">Bandung, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $tahunSurat }}</div>
                            <div style="margin-bottom: 40px;">Pemeriksa,</div>
                            <div style="display: flex; align-items: center; justify-content: center; width: 220px; margin: 0 auto 3px auto; font-weight: 700;">
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
                    $bidangLower = strtolower($bidangName);

                    // Dapatkan Kabid aktif dari Data Pegawai sesuai bidang pengajuan
                    $activeKabidUser = str_contains($bidangLower, 'rescue') || str_contains($bidangLower, 'penyelamatan')
                        ? $pejabatTtd['kabid_rescue']
                        : (str_contains($bidangLower, 'pencegah')
                            ? $pejabatTtd['kabid_pencegahan']
                            : $pejabatTtd['kabid_pemadam']);

                    // Kepala Bidang Pengaju: prioritaskan akun kabid aktif dari Master Pegawai
                    if ($activeKabidUser) {
                        $kabidBidangNama = $activeKabidUser->name;
                        $kabidBidangNip  = $activeKabidUser->nip;
                    } elseif (is_object($pengajuan) && $pengajuan->kabidUser) {
                        $kabidBidangNama = $pengajuan->kabidUser->name;
                        $kabidBidangNip  = $pengajuan->kabidUser->nip;
                    } else {
                        $kabidBidangNama = is_object($pengajuan) && !empty($pengajuan->nama_kepala_bidang) ? $pengajuan->nama_kepala_bidang : 'Rd. Asep Bintang Johar Slamet, S.IP., M.Si.';
                        $kabidBidangNip  = is_object($pengajuan) && !empty($pengajuan->nip_kepala_bidang) ? $pengajuan->nip_kepala_bidang : '197006062007011014';
                    }

                    // Menyetujui (Danru / Kasi): jika terhubung ke danruUser FK, gunakan nama live
                    if (is_object($pengajuan) && $pengajuan->danruUser) {
                        $menyetujuiNama = $pengajuan->danruUser->name;
                        $menyetujuiNip  = $pengajuan->danruUser->nip;
                    } else {
                        $menyetujuiNama = is_object($pengajuan) && !empty($pengajuan->nama_komandan_regu) ? $pengajuan->nama_komandan_regu : (is_object($pengajuan) && !empty($pengajuan->nama_kabid) ? $pengajuan->nama_kabid : 'Lukman');
                        $menyetujuiNip  = is_object($pengajuan) && !empty($pengajuan->nip_komandan_regu) ? $pengajuan->nip_komandan_regu : '197606272007011002';
                    }
                    $menyetujuiNama = preg_replace_callback('/\((.*?)\)/', fn($m) => '(' . strtoupper($m[1]) . ')', $menyetujuiNama);

                    // Pemohon (Pengemudi / Pemegang Kendaraan)
                    if (is_object($pengajuan) && $pengajuan->user) {
                        $pemohonNama = $pengajuan->user->name;
                        $pemohonNip  = $pengajuan->user->nip;
                    } else {
                        $pemohonNama = is_object($pengajuan) && !empty($pengajuan->nama_pemegang) ? $pengajuan->nama_pemegang : 'Riki Rohimat';
                        $pemohonNip  = is_object($pengajuan) && !empty($pengajuan->nip_pemegang) ? $pengajuan->nip_pemegang : '198603032014121002';
                    }
                @endphp

                {{-- ========================================================================= --}}
                {{-- PAGE 1: SURAT PERMOHONAN BIDANG (PERMOHONAN PEMELIHARAAN/PERBAIKAN) --}}
                {{-- ========================================================================= --}}
                <div class="paper-page">
                    
                    {{-- Header Title --}}
                    <div style="text-align: center; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px; margin-top: 6px;">
                        PERMOHONAN PEMELIHARAAN/PERBAIKAN KENDARAAN OPERASIONAL
                    </div>
                    <div style="border-top: 2.5px solid #000000; margin-top: 6px; margin-bottom: 18px;"></div>

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
                    <table class="details-table">
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

                    <div class="body-p" style="margin-top: 6px;">
                        Demikian permohonan ini kami buat. Atas perhatian dan kerja samanya, kami sampaikan terima kasih.
                    </div>

                    {{-- Signatures Block Page 1 --}}
                    <div class="ttd-container-p1" style="margin-top: auto; margin-bottom: 12px; width: 100%; display: block;">
                        {{-- Row 1: Menyetujui & Pemohon --}}
                        <div style="display: flex; justify-content: space-between; padding: 0 20px; margin-bottom: 14px;">
                            <div style="text-align: center; width: 220px; font-size: 12px;">
                                <div>Menyetujui,</div>
                                <div style="height: 48px;"></div>
                                <div style="font-weight: 700; text-decoration: underline;">{{ $menyetujuiNama }}</div>
                                <div style="font-size: 11px;">NIP. {{ $menyetujuiNip }}</div>
                            </div>

                            <div style="text-align: center; width: 220px; font-size: 12px;">
                                <div>Pemohon,</div>
                                <div style="height: 48px;"></div>
                                <div style="font-weight: 700; text-decoration: underline;">{{ $pemohonNama }}</div>
                                <div style="font-size: 11px;">NIP. {{ $pemohonNip }}</div>
                            </div>
                        </div>

                        {{-- Row 2: Mengetahui KEPALA BIDANG PEMADAMAN --}}
                        <div style="text-align: center; margin: 0 auto; width: 360px; font-size: 12px;">
                            <div>Mengetahui,</div>
                            <div style="font-weight: 700; text-transform: uppercase;">KEPALA BIDANG {{ $bidangName }}</div>
                            <div style="height: 45px;"></div>
                            <div style="font-weight: 700; text-decoration: underline; text-transform: uppercase;">{{ $kabidBidangNama }}</div>
                            <div style="font-size: 11px;">NIP. {{ $kabidBidangNip }}</div>
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
                    <div class="lampiran-outer-box">
                        
                        {{-- Header Title Box --}}
                        <div class="lampiran-banner">
                            LAMPIRAN PERMOHONAN PEMELIHARAAN/PERBAIKAN KENDARAAN OPERASIONAL
                        </div>

                        {{-- Metadata Box --}}
                        <div class="lampiran-meta-box">
                            <div class="lampiran-meta-left">
                                <table>
                                    <tr>
                                        <td style="width:190px;">Nomor Surat</td>
                                        <td style="width:15px;">:</td>
                                        <td>{{ $nomorSuratBidang }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal</td>
                                        <td>:</td>
                                        <td>{{ $tglSuratFormat }}</td>
                                    </tr>
                                    <tr>
                                        <td>Nomor Lambung / Nomor Polisi</td>
                                        <td>:</td>
                                        <td>{{ $nomorLambungPolisi }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="lampiran-qr-box">
                                <img src="{{ $qrCodeUrl }}" alt="QR Code">
                            </div>
                        </div>

                        {{-- Main Table --}}
                        <table class="table-pemeriksaan">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">ITEM PERBAIKAN</th>
                                    <th style="width: 15%;">JUMLAH</th>
                                    <th style="width: 15%;">SATUAN</th>
                                    <th style="width: 25%;">KETERANGAN</th>
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
                                    $totalRows = max(20, count($cleanItems) + 10);
                                @endphp
                                @for ($r = 0; $r < $totalRows; $r++)
                                    @php
                                        $itemText = $cleanItems[$r] ?? '';
                                    @endphp
                                    <tr>
                                        <td style="color: #000000;">{{ $itemText }}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
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
                    <table class="details-table">
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

                    <div class="body-p" style="margin-top: 6px;">
                        Maka kami mohon Saudara untuk menyediakan suku cadang dan melaksanakan pemeliharaan/perbaikan atas kendaraan tersebut. Adapun detail suku cadang dan pemeliharaan/perbaikan yang diperlukan sebagaimana yang tertera pada lampiran.
                    </div>
                    <div class="body-p" style="margin-top: 6px;">
                        Demikian surat pesanan ini kami buat sebagai dasar tindak lanjut pemeliharaan/perbaikan sesuai Perjanjian Kerja Sama (PKS) Nomor: {{ $pksNomor }} dan {{ $spkNomor }} tanggal {{ $pksTanggal }}. Atas perhatian dan kerja samanya, kami sampaikan terima kasih.
                    </div>

                    {{-- Signatures Block Page 1 (2 Signatures Side by Side) --}}
                    <div class="ttd-container-p1" style="margin-top: auto; margin-bottom: 12px; width: 100%; display: block;">
                        {{-- Date Row Aligned Above Right TTD --}}
                        <div style="display: flex; justify-content: flex-end; padding-right: 8px; margin-bottom: 3px; font-size: 12px;">
                            <div style="width: 300px; text-align: center;">Soreang, &nbsp; {{ $tglSuratFormat }}</div>
                        </div>

                        {{-- 2 Signatures Columns --}}
                        <div style="display: flex; justify-content: space-between; padding: 0 8px; align-items: flex-start;">
                            {{-- Left TTD: Kabid SPI --}}
                            <div style="text-align: center; width: 300px; font-size: 11.5px; line-height: 1.25;">
                                <div style="font-weight:700;">{{ $kabidJabatan }}</div>
                                <div style="font-weight:700;">selaku</div>
                                <div style="font-weight:700;">KUASA PENGGUNA ANGGARAN</div>
                                <div style="height: 48px;"></div>
                                <div style="font-weight:700; text-decoration:underline;">{{ $kabidNama }}</div>
                                <div style="font-weight:400;">{{ $kabidPangkat }}</div>
                                <div style="font-weight:400;">NIP. {{ $kabidNip }}</div>
                            </div>

                            {{-- Right TTD: Kasi Pemeliharaan --}}
                            <div style="text-align: center; width: 300px; font-size: 11.5px; line-height: 1.25;">
                                <div style="font-weight:700;">{{ $kasiJabatan }}</div>
                                <div style="font-weight:700;">selaku</div>
                                <div style="font-weight:700;">PEJABAT PELAKSANA TEKNIS KEGIATAN</div>
                                <div style="height: 48px;"></div>
                                <div style="font-weight:700; text-decoration:underline;">{{ $kasiNama }}</div>
                                <div style="font-weight:400;">{{ $kasiPangkat }}</div>
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
                    <div class="lampiran-outer-box">
                        
                        {{-- Header Title Box --}}
                        <div class="lampiran-banner">
                            LAMPIRAN PESANAN SUKU CADANG PEMELIHARAAN/PERBAIKAN KENDARAAN OPERASIONAL
                        </div>

                        {{-- Metadata Box --}}
                        <div class="lampiran-meta-box">
                            <div class="lampiran-meta-left">
                                <table>
                                    <tr>
                                        <td style="width:190px;">Nomor Surat</td>
                                        <td style="width:15px;">:</td>
                                        <td>{{ $nomorSuratSP }}</td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal</td>
                                        <td>:</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Nomor Lambung / Nomor Polisi</td>
                                        <td>:</td>
                                        <td>{{ $nomorLambungPolisi }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="lampiran-qr-box">
                                <img src="{{ $qrCodeUrl }}" alt="QR Code">
                            </div>
                        </div>

                        {{-- Main Table (No TTD below table) --}}
                        <table class="table-pemeriksaan">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">JENIS PERBAIKAN</th>
                                    <th style="width: 15%;">JUMLAH</th>
                                    <th style="width: 15%;">SATUAN</th>
                                    <th style="width: 25%;">KETERANGAN</th>
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
                                    $totalRows = max(20, count($cleanItems) + 10);
                                @endphp
                                @for ($r = 0; $r < $totalRows; $r++)
                                    @php
                                        $itemText = $cleanItems[$r] ?? '';
                                    @endphp
                                    <tr>
                                        <td style="color: #000000;">{{ $itemText }}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                </div>
            @endif

        </div>
    </div>

    <script>
        async function downloadPDFDirect() {
            const btn = document.querySelector('.btn-download');
            const originalContent = btn ? btn.innerHTML : '';
            if (btn) {
                btn.innerHTML = '<svg style="width:16px; height:16px; fill:currentColor; animation: spin 1s linear infinite;" viewBox="0 0 24 24"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/></svg> <span>Menyiapkan PDF...</span>';
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.8';
            }

            try {
                const pages = document.querySelectorAll('#pdf-content .paper-page');
                if (!pages || pages.length === 0) {
                    throw new Error('Tidak ada halaman dokumen.');
                }

                const jsPDFClass = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : (window.jsPDF ? window.jsPDF : null);
                if (!jsPDFClass) {
                    throw new Error('Library jsPDF gagal dimuat.');
                }

                const pdf = new jsPDFClass({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4',
                    compress: true
                });

                const pdfWidth = 210;
                const pdfHeight = 297;

                for (let i = 0; i < pages.length; i++) {
                    const page = pages[i];

                    // Render each paper-page individually to canvas at 2x resolution
                    const canvas = await html2canvas(page, {
                        scale: 2,
                        useCORS: true,
                        logging: false,
                        backgroundColor: '#FFFFFF',
                        width: page.offsetWidth,
                        height: page.offsetHeight,
                        windowWidth: document.documentElement.offsetWidth
                    });

                    const imgData = canvas.toDataURL('image/jpeg', 0.98);

                    if (i > 0) {
                        pdf.addPage('a4', 'portrait');
                    }

                    pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight, undefined, 'FAST');
                }

                const cleanTitle = '{{ $title }}'.replace(/[^a-zA-Z0-9_\- ]/g, '');
                const cleanKode = '{{ $kodeVerif }}'.replace(/[^a-zA-Z0-9_\- ]/g, '');
                pdf.save(`${cleanTitle}_${cleanKode}.pdf`);
            } catch (err) {
                console.error('PDF error:', err);
                alert('Gagal mengunduh PDF: ' + err.message);
            } finally {
                if (btn) {
                    btn.innerHTML = originalContent;
                    btn.style.pointerEvents = 'auto';
                    btn.style.opacity = '1';
                }
            }
        }
    </script>
</body>
</html>
