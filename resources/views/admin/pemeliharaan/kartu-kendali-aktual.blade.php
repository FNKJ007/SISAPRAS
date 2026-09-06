@extends('layouts.admin')
@section('title', 'Kartu Kendali SPJ — Admin')

@section('content')
<div style="width: 100%; max-width: 100%; box-sizing: border-box;">

    {{-- Header Halaman Web (Tidak ikut tercetak) --}}
    <div class="no-print" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:20px; font-weight:800; color:#0F172A; margin:0;">Kartu Kendali SPJ Pemeliharaan</h1>
            <p style="font-size:12.5px; color:#64748B; margin-top:3px; margin-bottom:0;">
                Matriks rekapitulasi realisasi pemeliharaan kendaraan operasional berdasarkan SPJ per unit dan per bulan, per tahun anggaran.
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
        <form method="GET" action="{{ route('admin.pemeliharaan.kartu-kendali-aktual') }}" style="display:flex; align-items:center; gap:10px;">
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
            <div class="kop-surat" style="display:flex; align-items:center; justify-content:space-between; border-bottom:3px double #0F172A; padding-bottom:10px; margin-bottom:14px; text-align:center; page-break-inside:avoid; break-inside:avoid;">
                <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo Pemkab" style="height:48px; width:auto;" onerror="this.style.display='none'">
                <div style="flex:1; padding:0 12px;">
                    <div style="font-size:11.5px; font-weight:700; letter-spacing:0.5px; color:#0F172A; text-transform:uppercase;">PEMERINTAH KABUPATEN BANDUNG</div>
                    <div style="font-size:14px; font-weight:800; color:#C0201F; text-transform:uppercase; margin:2px 0;">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                    <div style="font-size:10px; color:#475569; font-weight:500;">Bidang SPI — Seksi Pemeliharaan</div>
                </div>
                <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Damkar" style="height:48px; width:auto;" onerror="this.style.display='none'">
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
                <div class="matrix-table-wrapper" style="margin-bottom:14px; border:1px solid #000000; width:100%; box-sizing:border-box;">
                    <table class="kartu-kendali-matrix-table" style="width:100%; border-collapse:collapse; font-size:10px; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; table-layout:auto; page-break-inside:auto;">
                        <thead style="display:table-header-group;">
                            {{-- Header Baris 1 --}}
                            <tr style="background:#F8FAFC; color:#0F172A; font-weight:800; text-align:center; page-break-inside:avoid; break-inside:avoid;">
                                <th rowspan="2" style="border:1px solid #000000; padding:4px 3px; white-space:nowrap; text-transform:uppercase; vertical-align:middle; width:7%;">NO. LAMBUNG</th>
                                <th rowspan="2" style="border:1px solid #000000; padding:4px 3px; white-space:nowrap; text-transform:uppercase; vertical-align:middle; width:9%;">TNKB</th>
                                <th colspan="12" style="border:1px solid #000000; padding:3px 2px; text-transform:uppercase; font-size:10px; letter-spacing:0.5px;">BULAN</th>
                                <th rowspan="2" style="border:1px solid #000000; padding:4px 3px; white-space:nowrap; text-transform:uppercase; vertical-align:middle; width:9%;">TOTAL</th>
                            </tr>
                            {{-- Header Baris 2 (12 Kolom Bulan) --}}
                            <tr style="background:#F8FAFC; color:#0F172A; font-weight:800; text-align:center; font-size:9px; page-break-inside:avoid; break-inside:avoid;">
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">01 (JAN)</th>
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">02 (FEB)</th>
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">03 (MAR)</th>
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">04 (APR)</th>
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">05 (MEI)</th>
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">06 (JUN)</th>
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">07 (JUL)</th>
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">08 (AGS)</th>
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">09 (SEP)</th>
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">10 (OKT)</th>
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">11 (NOV)</th>
                                <th style="border:1px solid #000000; padding:4px 1px; width:6.25%;">12 (DES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matrixRows as $row)
                                {{-- Baris Pemisah Antar Kelompok Armada (P, R, S, PC, MP, K) --}}
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
                        </tbody>
                        <tfoot style="display:table-footer-group; page-break-inside:avoid; break-inside:avoid;">
                            <tr style="background:#F8FAFC; font-weight:800; color:#0F172A; page-break-inside:avoid; break-inside:avoid;">
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
                        </tfoot>
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

@push('styles')
<style>
    /* Responsive Table Styles */
    .matrix-table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (min-width: 1024px) {
        .matrix-table-wrapper {
            overflow-x: visible;
        }
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm 8mm 10mm 8mm;
        }
        .no-print, aside, nav, .sidebar, .topbar, .app-header, .mobile-menu-btn, header {
            display: none !important;
        }
        body, .app-wrapper, .main-area, .content-area {
            background: #FFFFFF !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        #kartu-kendali-print-area {
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-shadow: none !important;
            border: none !important;
        }
        .kop-surat, .judul-dokumen, .ttd-box {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        table.kartu-kendali-matrix-table {
            page-break-inside: auto !important;
            width: 100% !important;
            border-collapse: collapse !important;
        }
        thead {
            display: table-header-group !important;
        }
        tfoot {
            display: table-footer-group !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }

    /* html2pdf auto pagebreak CSS */
    .html2pdf__page-break {
        height: 0;
        page-break-before: always;
        break-before: page;
    }
</style>
@endpush

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

        // Clone ke container terisolasi di body dengan lebar pas landscape A4
        const clone = source.cloneNode(true);
        const wrapper = document.createElement('div');
        wrapper.style.position = 'fixed';
        wrapper.style.top = '0';
        wrapper.style.left = '0';
        wrapper.style.zIndex = '-1';
        wrapper.style.width = '1060px';
        wrapper.style.background = '#FFFFFF';
        clone.style.width = '1060px';
        clone.style.maxWidth = '1060px';
        clone.style.margin = '0';
        clone.style.padding = '12px 16px';
        wrapper.appendChild(clone);
        document.body.appendChild(wrapper);

        const opt = {
            margin:       [8, 8, 8, 8],
            filename:     filename,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, logging: false, windowWidth: 1060, width: 1060, x: 0, y: 0, scrollX: 0, scrollY: 0 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
            pagebreak:    { 
                mode: ['avoid-all', 'css', 'legacy'],
                avoid: ['tr', '.ttd-box', '.kop-surat', '.judul-dokumen', 'thead', 'tfoot']
            }
        };

        html2pdf().set(opt).from(clone).save().then(() => {
            wrapper.remove();
            if (btn) {
                btn.innerHTML = originalContent;
                btn.disabled = false;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }).catch(err => {
            wrapper.remove();
            console.error('Error creating PDF:', err);
            if (btn) {
                btn.innerHTML = originalContent;
                btn.disabled = false;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
    }
</script>
@endpush