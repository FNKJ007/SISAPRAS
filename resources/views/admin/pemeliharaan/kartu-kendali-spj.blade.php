@extends('layouts.admin')
@section('title', 'Kartu Kendali SPJ — Admin')

@push('styles')
<style>
    /* Responsive Table Wrapper di Layar */
    .matrix-table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    @media (min-width: 1024px) {
        .matrix-table-wrapper {
            overflow-x: visible;
        }
    }

    /* html2pdf auto pagebreak */
    .html2pdf__page-break {
        height: 0;
        page-break-before: always;
        break-before: page;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 6mm;
        }
        /* Sembunyikan semua elemen navigasi, sidebar, topbar, filter, header */
        .sidebar,
        .sidebar-backdrop,
        .topbar,
        .no-print,
        .admin-filter-bar,
        aside,
        nav,
        .app-header,
        .mobile-menu-btn,
        header {
            display: none !important;
            width: 0 !important;
            min-width: 0 !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        /* Override height & overflow pada seluruh parent containers */
        html, body {
            height: auto !important;
            overflow: visible !important;
            background: #FFFFFF !important;
        }
        .app-wrapper {
            display: block !important;
            height: auto !important;
            overflow: visible !important;
            background: #FFFFFF !important;
        }
        .main-area {
            display: block !important;
            height: auto !important;
            overflow: visible !important;
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background: #FFFFFF !important;
        }
        .content-area {
            height: auto !important;
            overflow: visible !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            background: #FFFFFF !important;
        }
        #kartu-kendali-print-area {
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-shadow: none !important;
            border: none !important;
            background: #FFFFFF !important;
        }
        .matrix-table-wrapper {
            overflow: visible !important;
            width: 100% !important;
        }
        .kartu-kendali-matrix-table {
            width: 100% !important;
            font-size: 8px !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
        }
        thead {
            display: table-header-group !important;
        }
        tr.matrix-row,
        tr.separator-row,
        tr.total-row {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .kop-surat,
        .judul-dokumen,
        .ttd-box {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
@endpush

@section('content')
<div style="width: 100%; max-width: 100%; box-sizing: border-box;">

    {{-- Header Halaman Web (Tidak ikut tercetak) --}}
    <div class="no-print" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:20px; font-weight:800; color:#0F172A; margin:0;">Kartu Kendali SPJ</h1>
            <p style="font-size:12.5px; color:#64748B; margin-top:3px; margin-bottom:0;">
                Matriks rekapitulasi realisasi pembayaran SPJ pemeliharaan kendaraan operasional per unit dan per bulan, per tahun anggaran.
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <button type="button" onclick="window.print()" 
                    style="display:inline-flex; align-items:center; gap:6px; padding:8px 15px; background:#F1F5F9; color:#1E293B; border:1px solid #CBD5E1; border-radius:10px; font-size:12px; font-weight:700; cursor:pointer; transition:all 0.2s;">
                <i data-lucide="printer" style="width:15px; height:15px; color:#475569;"></i>
                <span>Cetak Lembar</span>
            </button>
            <button type="button" id="btn-download-pdf" onclick="downloadKartuKendaliPDF()" 
                    style="display:inline-flex; align-items:center; gap:7px; padding:8px 16px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:12px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(27,42,107,0.25); transition:all 0.2s;">
                <i data-lucide="download" style="width:15px; height:15px;"></i>
                <span>Unduh PDF Kartu Kendali</span>
            </button>
        </div>
    </div>

    {{-- Filter Bar (Tidak ikut tercetak) --}}
    <div class="admin-filter-bar no-print" style="margin-bottom:18px;">
        <form method="GET" action="{{ route('admin.pemeliharaan.kartu-kendali-spj') }}" style="display:flex; align-items:center; gap:10px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:12.5px; font-weight:700; color:#475569;">Tahun Anggaran:</span>
                <select name="tahun" onchange="this.form.submit()" style="padding:7px 14px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:700; cursor:pointer;">
                    @forelse($tahunList as $tahun)
                        <option value="{{ $tahun }}" @selected($tahunFilter == $tahun)>{{ $tahun }}</option>
                    @empty
                        <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                    @endforelse
                </select>
            </div>
        </form>
    </div>

    {{-- Container Kartu Kendali (Full Responsive 100% Lebar Layar) --}}
    <div style="background:#FFFFFF; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0px 8px 30px rgba(112,144,176,0.06); width:100%; box-sizing:border-box;">
        <div id="kartu-kendali-print-area" style="padding:22px 24px; background:#FFFFFF; width:100%; box-sizing:border-box;">

            {{-- Kop Surat Resmi Pemkab / Damkar --}}
            <div class="kop-surat" style="border-bottom:3px double #000000; padding-bottom:10px; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between; page-break-inside:avoid; break-inside:avoid;">
                <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo Kab Bandung" 
                     style="height:52px; width:auto; max-width:65px; object-fit:contain;"
                     onerror="this.style.display='none'">
                <div style="text-align:center; flex:1; padding:0 15px;">
                    <div style="font-size:12px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:#0F172A; line-height:1.2;">PEMERINTAH KABUPATEN BANDUNG</div>
                    <div style="font-size:14px; font-weight:900; letter-spacing:0.8px; text-transform:uppercase; color:#0F172A; line-height:1.2;">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                    <div style="font-size:9.5px; color:#475569; margin-top:2px; line-height:1.2;">Jl. Raya Soreang Km.17 Bandung Telp. (022) 5891113 Soreang 40911</div>
                </div>
                <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Damkar" 
                     style="height:52px; width:auto; max-width:65px; object-fit:contain;"
                     onerror="this.style.display='none'">
            </div>

            {{-- Judul Dokumen --}}
            <div class="judul-dokumen" style="margin-bottom:12px; page-break-inside:avoid; break-inside:avoid;">
                <div style="font-size:13px; font-weight:800; text-transform:uppercase; color:#0F172A; letter-spacing:0.5px; line-height:1.3;">KARTU KENDALI SPJ</div>
                <div style="font-size:11.5px; font-weight:800; text-transform:uppercase; color:#0F172A; letter-spacing:0.5px; line-height:1.3;">PEMELIHARAAN KENDARAAN OPERASIONAL</div>
                <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:#0F172A; letter-spacing:0.5px; line-height:1.3;">TAHUN ANGGARAN {{ $tahunFilter }}</div>
            </div>

            @if(empty($matrixRows))
                <div style="padding:48px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    <div style="width:52px; height:52px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 12px auto; border:1px solid #E2E8F0;">
                        <i data-lucide="truck" style="width:24px; height:24px; color:#64748B;"></i>
                    </div>
                    <div style="font-size:14px; font-weight:800; color:#0F172A; margin-bottom:4px;">Tidak Ada Data Armada</div>
                    <div style="font-size:12px; color:#64748B; max-width:360px; margin:0 auto;">Tidak ditemukan data armada atau filter yang sesuai.</div>
                </div>
            @else
                {{-- Tabel Matriks Pivot 12 Bulan (Pas 100% Tanpa Overflow) --}}
                <div class="matrix-table-wrapper" style="margin-bottom:14px; width:100%; box-sizing:border-box;">
                    <table class="kartu-kendali-matrix-table" style="width:100%; border-collapse:collapse; font-size:10px; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; table-layout:fixed; page-break-inside:auto;">
                        <colgroup>
                            <col style="width:11%;">
                            <col style="width:9%;">
                            <col style="width:6%;"><col style="width:6%;"><col style="width:6%;"><col style="width:6%;">
                            <col style="width:6%;"><col style="width:6%;"><col style="width:6%;"><col style="width:6%;">
                            <col style="width:6%;"><col style="width:6%;"><col style="width:6%;"><col style="width:6%;">
                            <col style="width:8%;">
                        </colgroup>
                        <thead style="display:table-header-group;">
                            {{-- Header Baris 1: NO.LAMBUNG, TNKB, BULAN, TOTAL --}}
                            <tr style="background:#F8FAFC; color:#0F172A; font-weight:800; text-align:center;">
                                <th style="border:1px solid #000000; border-bottom:none; padding:6px 4px; white-space:nowrap; text-transform:uppercase; vertical-align:middle; font-size:10px;">NO. LAMBUNG</th>
                                <th style="border:1px solid #000000; border-bottom:none; padding:6px 4px; white-space:nowrap; text-transform:uppercase; vertical-align:middle; font-size:10px;">TNKB</th>
                                <th colspan="12" style="border:1px solid #000000; padding:4px 2px; text-transform:uppercase; font-size:10px; letter-spacing:0.5px;">BULAN</th>
                                <th style="border:1px solid #000000; border-bottom:none; padding:6px 4px; white-space:nowrap; text-transform:uppercase; vertical-align:middle; font-size:10px;">TOTAL</th>
                            </tr>
                            {{-- Header Baris 2: Kosong | Kosong | JAN-DES | Kosong --}}
                            <tr style="background:#F8FAFC; color:#0F172A; font-weight:800; text-align:center; font-size:9px;">
                                <th style="border:1px solid #000000; border-top:none; padding:0;"></th>
                                <th style="border:1px solid #000000; border-top:none; padding:0;"></th>
                                <th style="border:1px solid #000000; padding:4px 1px;">JAN</th>
                                <th style="border:1px solid #000000; padding:4px 1px;">FEB</th>
                                <th style="border:1px solid #000000; padding:4px 1px;">MAR</th>
                                <th style="border:1px solid #000000; padding:4px 1px;">APR</th>
                                <th style="border:1px solid #000000; padding:4px 1px;">MEI</th>
                                <th style="border:1px solid #000000; padding:4px 1px;">JUN</th>
                                <th style="border:1px solid #000000; padding:4px 1px;">JUL</th>
                                <th style="border:1px solid #000000; padding:4px 1px;">AGS</th>
                                <th style="border:1px solid #000000; padding:4px 1px;">SEP</th>
                                <th style="border:1px solid #000000; padding:4px 1px;">OKT</th>
                                <th style="border:1px solid #000000; padding:4px 1px;">NOV</th>
                                <th style="border:1px solid #000000; padding:4px 1px;">DES</th>
                                <th style="border:1px solid #000000; border-top:none; padding:0;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matrixRows as $row)
                                {{-- Baris Pemisah Antar Kelompok Armada (P, R, S, PC, MP, K) seperti di spreadsheet dinas --}}
                                @if($row['is_new_group'])
                                    <tr class="separator-row" style="background:#E2E8F0; height:4px; page-break-inside:avoid; break-inside:avoid;">
                                        <td colspan="15" style="border:1px solid #000000; padding:0; height:4px;"></td>
                                    </tr>
                                @endif

                                <tr class="matrix-row" style="background:#FFFFFF; page-break-inside:avoid; break-inside:avoid;">
                                    <td style="border:1px solid #000000; padding:4px 5px; font-weight:700; color:#0F172A; text-align:left; white-space:nowrap;">
                                        {{ $row['no_lambung'] }}
                                    </td>
                                    <td style="border:1px solid #000000; padding:4px 5px; color:#0F172A; text-align:left; white-space:nowrap; font-weight:500;">
                                        {{ $row['tnkb'] }}
                                    </td>

                                    {{-- 12 Kolom Nilai Bulan (Rata Kiri) --}}
                                    @for($m = 1; $m <= 12; $m++)
                                        @php $val = $row['months'][$m] ?? 0; @endphp
                                        <td style="border:1px solid #000000; padding:4px 4px; text-align:{{ $val > 0 ? 'left' : 'center' }}; color:#0F172A; font-variant-numeric:tabular-nums; white-space:nowrap; {{ $val > 0 ? 'font-weight:600;' : 'color:#94A3B8;' }}">
                                            {{ $val > 0 ? number_format($val, 0, ',', '.') : '-' }}
                                        </td>
                                    @endfor

                                    {{-- Kolom Total Baris per Unit (Rata Kiri) --}}
                                    <td style="border:1px solid #000000; padding:4px 5px; text-align:{{ $row['total'] > 0 ? 'left' : 'center' }}; font-weight:800; color:#0F172A; font-variant-numeric:tabular-nums; white-space:nowrap; background:#FAFAFA;">
                                        {{ $row['total'] > 0 ? number_format($row['total'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Baris Total Keseluruhan (Hanya muncul sekali di paling akhir tabel / halaman terakhir) --}}
                            <tr class="total-row" style="background:#F8FAFC; font-weight:800; color:#0F172A; page-break-inside:avoid; break-inside:avoid;">
                                <td colspan="2" style="border:1px solid #000000; padding:5px 6px; text-align:center; font-size:10px; text-transform:uppercase;">
                                    TOTAL
                                </td>
                                @for($m = 1; $m <= 12; $m++)
                                    @php $mTotal = $monthlyTotals[$m] ?? 0; @endphp
                                    <td style="border:1px solid #000000; padding:5px 4px; text-align:{{ $mTotal > 0 ? 'left' : 'center' }}; font-size:9.5px; font-variant-numeric:tabular-nums; white-space:nowrap;">
                                        {{ $mTotal > 0 ? number_format($mTotal, 0, ',', '.') : '-' }}
                                    </td>
                                @endfor
                                <td style="border:1px solid #000000; padding:5px 5px; text-align:left; font-size:10px; font-weight:900; color:#1B2A6B; font-variant-numeric:tabular-nums; white-space:nowrap; background:#E2E8F0;">
                                    {{ number_format($grandTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Block Tanda Tangan Resmi (Dilindungi dari page-break terpotong) --}}
            <div class="ttd-box" style="margin-top:16px; display:flex; justify-content:flex-end; page-break-inside:avoid; break-inside:avoid;">
                <div style="text-align:center; min-width:240px; font-size:10px; color:#334155;">
                    <div>Soreang, {{ now()->translatedFormat('d F Y') }}</div>
                    <div style="font-weight:700; color:#0F172A; margin-top:2px; margin-bottom:40px;">Kepala Seksi Pemeliharaan Sarana Dan Prasarana</div>
                    <div style="font-weight:700; color:#0F172A; text-decoration:underline;">{{ $pejabatKasi->name ?? 'Ahmad Kuswara, S.M., M.M.' }}</div>
                    <div style="font-size:9.5px; color:#64748B; margin-top:1px;">NIP. {{ $pejabatKasi->nip ?? '197209212008011001' }}</div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadKartuKendaliPDF() {
        const btn = document.getElementById('btn-download-pdf');
        const originalContent = btn ? btn.innerHTML : '';
        if (btn) {
            btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin" style="width:15px; height:15px;"></i><span>Menyiapkan PDF...</span>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
            btn.disabled = true;
        }

        const source = document.getElementById('kartu-kendali-print-area');
        const filename = 'Kartu_Kendali_SPJ_{{ $tahunFilter }}.pdf';

        // Temporarily fix ALL parent overflow to prevent clipping
        const savedOverflows = [];
        let parent = source.parentElement;
        while (parent && parent !== document.body) {
            const cs = getComputedStyle(parent);
            if (cs.overflow !== 'visible' || cs.overflowX !== 'visible' || cs.overflowY !== 'visible') {
                savedOverflows.push({
                    el: parent,
                    overflow: parent.style.overflow,
                    overflowX: parent.style.overflowX,
                    overflowY: parent.style.overflowY
                });
                parent.style.overflow = 'visible';
                parent.style.overflowX = 'visible';
                parent.style.overflowY = 'visible';
            }
            parent = parent.parentElement;
        }

        // Also fix the matrix wrapper
        source.querySelectorAll('.matrix-table-wrapper').forEach(el => {
            el.style.overflow = 'visible';
        });

        // Scroll to top for clean capture
        window.scrollTo(0, 0);

        setTimeout(() => {
            const opt = {
                margin:       [6, 6, 6, 6],
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.95 },
                html2canvas:  { 
                    scale: 2, 
                    useCORS: true, 
                    logging: false,
                    scrollY: 0,
                    scrollX: 0,
                    windowWidth: document.documentElement.scrollWidth
                },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
                pagebreak:    { 
                    mode: ['css', 'legacy'],
                    avoid: ['.ttd-box', '.kop-surat', '.judul-dokumen']
                }
            };

            html2pdf().set(opt).from(source).save().then(() => {
                savedOverflows.forEach(s => {
                    s.el.style.overflow = s.overflow;
                    s.el.style.overflowX = s.overflowX;
                    s.el.style.overflowY = s.overflowY;
                });
                if (btn) {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            }).catch(err => {
                savedOverflows.forEach(s => {
                    s.el.style.overflow = s.overflow;
                    s.el.style.overflowX = s.overflowX;
                    s.el.style.overflowY = s.overflowY;
                });
                console.error('Error creating PDF:', err);
                if (btn) {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            });
        }, 200);
    }
</script>
@endpush
