<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — {{ $pengajuan->kode_verifikasi ?? 'HAR-0000' }}</title>
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
            font-size: 12.5px;
            line-height: 1.5;
        }

        /* Toolbar Top (Non-printable) */
        .no-print-bar {
            max-width: 820px;
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
        .no-print-bar button {
            background: #22C55E;
            color: #FFFFFF;
            border: none;
            padding: 9px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .no-print-bar button:hover {
            background: #16A34A;
        }

        /* Paper Document Layout */
        .paper {
            max-width: 820px;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 45px 50px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            position: relative;
        }

        /* Kop Surat Resmi Dinas */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px double #0F172A;
            padding-bottom: 14px;
            margin-bottom: 24px;
            text-align: center;
        }
        .kop-logo {
            width: 72px;
            height: auto;
        }
        .kop-text {
            flex: 1;
            padding: 0 15px;
        }
        .kop-text h4 {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #0F172A;
            text-transform: uppercase;
        }
        .kop-text h2 {
            font-size: 17.5px;
            font-weight: 800;
            color: #C0201F;
            text-transform: uppercase;
            margin: 3px 0;
        }
        .kop-text p {
            font-size: 11px;
            color: #475569;
        }

        /* Header Judul Dokumen */
        .doc-header {
            text-align: center;
            margin-bottom: 22px;
        }
        .doc-title {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1B2A6B;
            text-decoration: underline;
            letter-spacing: 0.5px;
        }
        .doc-nomor {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-top: 4px;
        }

        /* Target Address / Kepada Yth */
        .kepada-box {
            margin-bottom: 20px;
            font-size: 12.5px;
            line-height: 1.6;
        }

        /* Verification Badge Box */
        .verif-badge-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 22px;
        }
        .verif-code {
            font-family: monospace;
            font-size: 14px;
            font-weight: 800;
            color: #C0201F;
            background: #FEF2F2;
            padding: 5px 12px;
            border-radius: 6px;
            border: 1px solid #FCA5A5;
        }

        /* Table Information */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th, .info-table td {
            padding: 7px 10px;
            vertical-align: top;
            font-size: 12.5px;
        }
        .info-table th {
            width: 190px;
            color: #475569;
            font-weight: 600;
            text-align: left;
            background: #F8FAFC;
            border-bottom: 1px solid #E2E8F0;
        }
        .info-table td {
            color: #0F172A;
            border-bottom: 1px solid #F1F5F9;
        }

        /* Item Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 24px 0;
        }
        .items-table th {
            background: #1B2A6B;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 11.5px;
            text-transform: uppercase;
            padding: 9px 12px;
            text-align: left;
        }
        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 12px;
        }

        /* Note Box */
        .note-box {
            background: #FFFBEB;
            border: 1px solid #FCD34D;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 11.5px;
            color: #92400E;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        /* Signature Grid */
        .signature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
            text-align: center;
        }
        .sig-box {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 145px;
        }
        .sig-title {
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
        }
        .sig-name {
            font-size: 12px;
            font-weight: 700;
            color: #0F172A;
            text-decoration: underline;
        }
        .sig-nip {
            font-size: 11px;
            color: #64748B;
        }

        /* Print CSS Reset */
        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .paper {
                box-shadow: none;
                padding: 15px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>


    <!-- Paper Content -->
    <div class="paper">

        <!-- Kop Surat -->
        <div class="kop-surat">
            <img src="{{ asset('images/logo-kabupaten.png') }}" class="kop-logo" alt="Logo Pemkab">
            <div class="kop-text">
                <h4>Pemerintah Kabupaten Bandung</h4>
                <h2>Dinas Pemadam Kebakaran dan Penyelamatan</h2>
                <p>Jl. Raya Soreang - Banjaran KM. 2, Soreang, Kabupaten Bandung | Telp/Fax: (022) 5891113</p>
            </div>
            <img src="{{ asset('images/logo-damkar.png') }}" class="kop-logo" alt="Logo Damkar">
        </div>

        @if($type == 'permohonanbidang')
            <!-- ================= PERMOHONAN BIDANG ================= -->
            <div class="doc-header">
                <div class="doc-title">SURAT PERMOHONAN PEMELIHARAAN UNIT OPERASIONAL</div>
                <div class="doc-nomor">Nomor: 028 / {{ $pengajuan->kode_verifikasi }} / DISDAMKAR</div>
            </div>

            <div class="kepada-box">
                <table>
                    <tr><td style="padding:0; width:90px;"><strong>Kepada Yth.</strong></td><td style="padding:0;">: Kepala Bidang Pemeliharaan Sarana & Prasarana</td></tr>
                    <tr><td style="padding:0;"><strong>Dari</strong></td><td style="padding:0;">: {{ is_object($pengajuan) && method_exists($pengajuan, 'getPosAttribute') ? $pengajuan->pos : ($pengajuan->pos ?? '-') }} ({{ is_object($pengajuan) && method_exists($pengajuan, 'getReguAttribute') ? $pengajuan->regu : ($pengajuan->regu ?? '-') }})</td></tr>
                    <tr><td style="padding:0;"><strong>Perihal</strong></td><td style="padding:0;">: Permohonan Perbaikan / Pemeliharaan Unit Kendaraan Operasional</td></tr>
                </table>
            </div>

            <p style="margin-bottom:14px;">Dengan hormat,<br>Sehubungan dengan pemeriksaan kondisi teknis unit kendaraan operasional dinas, bersama ini kami mengajukan permohonan pemeliharaan/perbaikan unit dengan data sebagai berikut:</p>

        @elseif($type == 'permohonanbengkel')
            <!-- ================= PERMOHONAN BENGKEL ================= -->
            <div class="doc-header">
                <div class="doc-title">SURAT PENGANTAR REPARASI / PERBAIKAN KENDARAAN BENGKEL</div>
                <div class="doc-nomor">Nomor: 028 / SPB - {{ $pengajuan->kode_verifikasi }} / DISDAMKAR</div>
            </div>

            <div class="kepada-box">
                <table>
                    <tr><td style="padding:0; width:90px;"><strong>Kepada Yth.</strong></td><td style="padding:0;">: Pimpinan / Pengelola Bengkel Rekanan Operasional</td></tr>
                    <tr><td style="padding:0;"><strong>Dari</strong></td><td style="padding:0;">: Dinas Pemadam Kebakaran dan Penyelamatan Kabupaten Bandung</td></tr>
                    <tr><td style="padding:0;"><strong>Perihal</strong></td><td style="padding:0;">: Pengantar Perbaikan / Reparasi Unit Kendaraan Operasional</td></tr>
                </table>
            </div>

            <p style="margin-bottom:14px;">Dengan hormat,<br>Bersama surat pengantar ini, kami menyerahkan unit kendaraan operasional milik Dinas Pemadam Kebakaran dan Penyelamatan Kabupaten Bandung untuk dilakukan pekerjaan perbaikan/perawatan di bengkel Saudara:</p>

        @else
            <!-- ================= SURAT PESANAN ================= -->
            <div class="doc-header">
                <div class="doc-title">SURAT PESANAN (SP) PEKERJAAN PEMELIHARAAN / SPAREPART</div>
                <div class="doc-nomor">Nomor SP: 028 / SP - {{ $pengajuan->kode_verifikasi }} / DISDAMKAR / {{ date('Y') }}</div>
            </div>

            <div class="kepada-box">
                <table>
                    <tr><td style="padding:0; width:130px;"><strong>Pemberi Tugas</strong></td><td style="padding:0;">: Dinas Pemadam Kebakaran dan Penyelamatan Kabupaten Bandung</td></tr>
                    <tr><td style="padding:0;"><strong>Penyedia / Bengkel</strong></td><td style="padding:0;">: Bengkel Rekanan Pemeliharaan Sarpras Operasional</td></tr>
                    <tr><td style="padding:0;"><strong>Perihal Pesanan</strong></td><td style="padding:0;">: Pelaksanaan Pekerjaan Perbaikan Kendaraan & Pengadaan Suku Cadang</td></tr>
                </table>
            </div>

            <p style="margin-bottom:14px;">Dengan ini memerintahkan kepada Penyedia/Bengkel Rekanan untuk melaksanakan pekerjaan perbaikan dan penyediaan suku cadang unit kendaraan operasional berikut:</p>
        @endif

        <!-- Metadata Verifikasi Badge -->
        <div class="verif-badge-box">
            <div>
                <span style="font-size:11px; color:#64748B; text-transform:uppercase; font-weight:700; display:block;">Waktu Pengajuan</span>
                <strong style="font-size:13px;">{{ is_string($pengajuan->created_at) ? $pengajuan->created_at : $pengajuan->created_at->format('d F Y, H:i') }} WIB</strong>
            </div>
            <div style="text-align:right;">
                <span style="font-size:11px; color:#64748B; text-transform:uppercase; font-weight:700; display:block;">Kode Verifikasi System</span>
                <span class="verif-code">{{ $pengajuan->kode_verifikasi }}</span>
            </div>
        </div>

        <!-- Detail Kendaraan -->
        <table class="info-table">
            <tr>
                <th>Bidang Operasional</th>
                <td>: {{ is_object($pengajuan) && method_exists($pengajuan, 'getBidangAttribute') ? $pengajuan->bidang : ($pengajuan->bidang ?? '-') }}</td>
            </tr>
            <tr>
                <th>Pos / Mako Jaga</th>
                <td>: {{ is_object($pengajuan) && method_exists($pengajuan, 'getPosAttribute') ? $pengajuan->pos : ($pengajuan->pos ?? '-') }}</td>
            </tr>
            <tr>
                <th>Regu Petugas</th>
                <td>: {{ is_object($pengajuan) && method_exists($pengajuan, 'getReguAttribute') ? $pengajuan->regu : ($pengajuan->regu ?? '-') }}</td>
            </tr>
            <tr>
                <th>Jenis Kendaraan</th>
                <td>: {{ is_object($pengajuan) && method_exists($pengajuan, 'getJenisKendaraanAttribute') ? $pengajuan->jenis_kendaraan : ($pengajuan->jenis_kendaraan ?? '-') }}</td>
            </tr>
            <tr>
                <th>Nomor Lambung / Plat</th>
                <td>: <strong>{{ is_object($pengajuan) && method_exists($pengajuan, 'getNomorLambungAttribute') ? $pengajuan->nomor_lambung : ($pengajuan->nomor_lambung ?? '-') }}</strong></td>
            </tr>
        </table>

        <!-- Table Items -->
        <div style="font-weight:700; font-size:13px; color:#1B2A6B; margin-bottom:6px;">Rincian Item Perbaikan / Pemeliharaan yang Diverifikasi:</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:40px; text-align:center;">No</th>
                    <th>Uraian Kerusakan / Item Pemeliharaan</th>
                    <th style="width:130px; text-align:center;">Status Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $items = is_array($pengajuan->item_list ?? null) ? $pengajuan->item_list : (explode("\n", $pengajuan->item_perbaikan ?? ''));
                @endphp
                @foreach($items as $idx => $item)
                    @if(trim($item) != '')
                    <tr>
                        <td style="text-align:center; font-weight:600;">{{ $idx + 1 }}</td>
                        <td>{{ trim($item) }}</td>
                        <td style="text-align:center; color:#16A34A; font-weight:700;">Disetujui</td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        @if($type == 'permohonanbengkel' || $type == 'Suratpesanan')
        <div class="note-box">
            <strong>Petunjuk Pelaksanaan Pekerjaan Bengkel:</strong><br>
            1. Pekerjaan perbaikan dan penggantian suku cadang agar dilaksanakan sesuai rincian item di atas.<br>
            2. Setelah perbaikan selesai, bengkel wajib menerbitkan Invoice resmi dan suku cadang bekas harus dikembalikan.<br>
            3. Berita Acara Serah Terima (BAST) fisik kendaraan wajib ditandatangani setelah uji fungsi unit.
        </div>
        @endif

        <!-- Signatures Grid -->
        <div class="signature-grid">
            <div class="sig-box">
                <div class="sig-title">
                    @if($type == 'Suratpesanan')
                        Penerima Pesanan (Bengkel)
                    @else
                        Pemegang Unit / Pengemudi
                    @endif
                </div>
                <div>
                    <div class="sig-name">{{ $pengajuan->nama_pemegang ?? '-' }}</div>
                    <div class="sig-nip">NIP. {{ $pengajuan->nip_pemegang ?? '-' }}</div>
                </div>
            </div>

            <div class="sig-box">
                <div class="sig-title">
                    Mengetahui,<br>Komandan Regu Jaga
                </div>
                <div>
                    <div class="sig-name">{{ $pengajuan->nama_komandan_regu ?? '-' }}</div>
                    <div class="sig-nip">NIP. {{ $pengajuan->nip_komandan_regu ?? '-' }}</div>
                </div>
            </div>

            <div class="sig-box">
                <div class="sig-title">
                    Menyetujui,<br>Kepala Bidang Pemeliharaan
                </div>
                <div>
                    <div class="sig-name">{{ $pengajuan->nama_kepala_bidang ?? 'Drs. H. Mulyadi, M.Si' }}</div>
                    <div class="sig-nip">NIP. {{ $pengajuan->nip_kepala_bidang ?? '19681120 199303 1 005' }}</div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function downloadPDFDirect() {
            const element = document.querySelector('.paper');
            const opt = {
                margin:       [8, 8, 8, 8],
                filename:     '{{ $title }}_{{ $pengajuan->kode_verifikasi }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }

        // Otomatis mengunduh PDF secara langsung saat halaman dibuka
        window.addEventListener('load', function() {
            setTimeout(function() {
                downloadPDFDirect();
            }, 300);
        });
    </script>
</body>
</html>
