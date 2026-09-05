@extends('layouts.admin')
@section('title', 'Kartu Kendali Aktual — Admin')

@section('content')
<div>

    {{-- Header --}}
    <div class="no-print" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Kartu Kendali Aktual Pemeliharaan</h1>
            <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                Ledger rekapitulasi realisasi pemeliharaan kendaraan per bulan, per tahun anggaran.
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <button type="button" onclick="window.print()" 
                    style="display:inline-flex; align-items:center; gap:6px; padding:9px 16px; background:#F1F5F9; color:#1E293B; border:1px solid #CBD5E1; border-radius:10px; font-size:12.5px; font-weight:700; cursor:pointer; transition:all 0.2s;">
                <i data-lucide="printer" style="width:16px; height:16px; color:#475569;"></i>
                <span>Cetak Lembar</span>
            </button>
            <button type="button" id="btn-download-pdf" onclick="downloadKartuKendaliPDF()" 
                    style="display:inline-flex; align-items:center; gap:8px; padding:9px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:12.5px; font-weight:700; cursor:pointer; box-shadow:0 4px 14px rgba(27,42,107,0.25); transition:all 0.2s;">
                <i data-lucide="download" style="width:16px; height:16px;"></i>
                <span>Unduh PDF Kartu Kendali</span>
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid-container no-print" style="margin-bottom:24px;">
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#64748B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Record Pemeliharaan</div>
            <div style="font-size:22px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total_invoice'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#1B2A6B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Nilai Kumulatif</div>
            <div style="font-size:18px; font-weight:800; color:#1B2A6B; margin-top:6px;">Rp {{ number_format($kpi['total_nilai'], 0, ',', '.') }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#059669; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Unit Terlayani</div>
            <div style="font-size:18px; font-weight:800; color:#059669; margin-top:6px;">{{ $kpi['total_unit'] }} Unit</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#2563EB; font-size:11.5px; font-weight:700; text-transform:uppercase;">Rata-rata Biaya / Pemeliharaan</div>
            <div style="font-size:18px; font-weight:800; color:#2563EB; margin-top:6px;">Rp {{ number_format($kpi['rata_rata'], 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="admin-filter-bar no-print" style="margin-bottom:16px;">
        <form method="GET" action="{{ route('admin.pemeliharaan.kartu-kendali-aktual') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Tahun Anggaran:</span>
                    <select name="tahun" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        @forelse($tahunList as $tahun)
                            <option value="{{ $tahun }}" @selected($tahunFilter == $tahun)>{{ $tahun }}</option>
                        @empty
                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                        @endforelse
                    </select>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:8px;">
                <div style="position:relative;">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari no. rujukan / lambung..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:230px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
                @if(!empty($searchQuery))
                    <a href="{{ route('admin.pemeliharaan.kartu-kendali-aktual', ['tahun' => $tahunFilter]) }}" style="padding:7px 12px; background:#E2E8F0; color:#475569; border-radius:8px; font-size:12px; text-decoration:none; font-weight:600;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Container Kartu Kendali (Formatted Standard A4 Printable Area) --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
        <div id="kartu-kendali-print-area" style="padding:24px 28px; background:#FFFFFF; max-width:794px; margin:0 auto; box-sizing:border-box;">

            {{-- Kop Surat Resmi Pemkab / Damkar --}}
            <div class="kop-surat" style="display:flex; align-items:center; justify-content:space-between; border-bottom:3px double #0F172A; padding-bottom:12px; margin-bottom:18px; text-align:center; page-break-inside:avoid; break-inside:avoid;">
                <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo Pemkab" style="height:55px; width:auto;" onerror="this.style.display='none'">
                <div style="flex:1; padding:0 12px;">
                    <div style="font-size:12px; font-weight:700; letter-spacing:0.5px; color:#0F172A; text-transform:uppercase;">PEMERINTAH KABUPATEN BANDUNG</div>
                    <div style="font-size:15px; font-weight:800; color:#C0201F; text-transform:uppercase; margin:2px 0;">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                    <div style="font-size:10.5px; color:#475569; font-weight:500;">Bidang SPI — Seksi Pemeliharaan</div>
                </div>
                <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Damkar" style="height:55px; width:auto;" onerror="this.style.display='none'">
            </div>

            {{-- Judul Dokumen --}}
            <div class="judul-dokumen" style="text-align:center; margin-bottom:18px; page-break-inside:avoid; break-inside:avoid;">
                <h2 style="font-size:14px; font-weight:800; text-transform:uppercase; color:#1B2A6B; text-decoration:underline; letter-spacing:0.5px; margin:0 0 3px 0;">KARTU KENDALI AKTUAL PEMELIHARAAN KENDARAAN</h2>
                <div style="font-size:11.5px; font-weight:600; color:#64748B;">
                    Tahun Anggaran {{ $tahunFilter }}
                </div>
            </div>

            @if(empty($matrix))
                <div style="padding:56px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
                        <i data-lucide="clipboard-list" style="width:30px; height:30px; color:#64748B;"></i>
                    </div>
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Pemeliharaan</div>
                    <div style="font-size:13px; color:#64748B; max-width:400px; margin:0 auto;">Tidak ditemukan data unit untuk pemeliharaan saat ini.</div>
                </div>
            @else
                <div style="text-align:right; margin-bottom:4px;">
                    <strong style="font-size:12px; font-weight:800; color:#0F172A;">{{ number_format($grandTotal, 0, ',', '.') }}</strong>
                </div>
                {{-- Tabel Matriks Kartu Kendali Aktual --}}
                <div style="margin-bottom:18px; overflow-x:auto;">
                    <table class="kartu-kendali-table" style="width:100%; border-collapse:collapse; font-size:10px; text-align:center; table-layout:auto; page-break-inside:auto; border:1px solid #000;">
                        <thead style="display:table-header-group;">
                            <tr style="page-break-inside:avoid; break-inside:avoid;">
                                <th rowspan="2" style="padding:6px; border:1px solid #000; vertical-align:middle; width:70px;">NO. LAMBUNG</th>
                                <th rowspan="2" style="padding:6px; border:1px solid #000; vertical-align:middle; width:75px;">TNKB</th>
                                <th colspan="12" style="padding:6px; border:1px solid #000; font-weight:bold; letter-spacing:1px;">BULAN</th>
                                <th rowspan="2" style="padding:6px; border:1px solid #000; vertical-align:middle; width:80px; font-weight:bold;">TOTAL</th>
                            </tr>
                            <tr style="page-break-inside:avoid; break-inside:avoid;">
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">01 (JAN)</th>
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">02 (FEB)</th>
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">03 (MAR)</th>
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">04 (APR)</th>
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">05 (MEI)</th>
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">06 (JUN)</th>
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">07 (JUL)</th>
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">08 (AGS)</th>
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">09 (SEP)</th>
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">10 (OKT)</th>
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">11 (NOV)</th>
                                <th style="padding:4px; border:1px solid #000; font-weight:bold; width:65px;">12 (DES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matrix as $unitId => $data)
                                <tr style="page-break-inside:avoid; break-inside:avoid;">
                                    <td style="padding:4px; border:1px solid #000; text-align:left; font-weight:500;">{{ $data['unit']->nomor_lambung ?? '-' }}</td>
                                    <td style="padding:4px; border:1px solid #000; text-align:left; font-weight:500;">{{ $data['unit']->plat_nomor ?? '-' }}</td>
                                    @for($m = 1; $m <= 12; $m++)
                                        <td style="padding:4px; border:1px solid #000; text-align:right;">
                                            {{ $data['months'][$m] > 0 ? number_format($data['months'][$m], 0, ',', '.') : '-' }}
                                        </td>
                                    @endfor
                                    <td style="padding:4px; border:1px solid #000; text-align:right; font-weight:bold;">
                                        {{ $data['total'] > 0 ? number_format($data['total'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="display:table-footer-group; page-break-inside:avoid; break-inside:avoid;">
                            <tr>
                                <td colspan="2" style="padding:6px; border:1px solid #000; font-weight:bold; text-align:center;">TOTAL</td>
                                @for($m = 1; $m <= 12; $m++)
                                    <td style="padding:6px; border:1px solid #000; text-align:right; font-weight:bold;">
                                        {{ $totalPerBulan[$m] > 0 ? number_format($totalPerBulan[$m], 0, ',', '.') : '-' }}
                                    </td>
                                @endfor
                                <td style="padding:6px; border:1px solid #000; text-align:right; font-weight:bold;">
                                    {{ $grandTotal > 0 ? number_format($grandTotal, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif

            {{-- Block Tanda Tangan Resmi (Dilindungi dari page-break terpotong) --}}
            <div class="ttd-box" style="margin-top:24px; display:flex; justify-content:flex-end; page-break-inside:avoid; break-inside:avoid;">
                <div style="text-align:center; min-width:260px; font-size:11px; color:#334155;">
                    <div>Soreang, {{ now()->translatedFormat('d F Y') }}</div>
                    <div style="font-weight:700; color:#0F172A; margin-top:3px; margin-bottom:50px;">Kepala Seksi Pemeliharaan Sarana Dan Prasarana</div>
                    <div style="font-weight:700; color:#0F172A; text-decoration:underline;">{{ $pejabatKasi->name ?? 'Ahmad Kuswara, S.M., M.M.' }}</div>
                    <div style="font-size:10px; color:#64748B; margin-top:2px;">NIP. {{ $pejabatKasi->nip ?? '197209212008011001' }}</div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
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
            max-width: 100% !important;
            box-shadow: none !important;
            border: none !important;
        }
        .kop-surat, .judul-dokumen, .ttd-box {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        table.kartu-kendali-table {
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
            btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin" style="width:16px; height:16px;"></i><span>Menyiapkan PDF...</span>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
            btn.disabled = true;
        }

        const source = document.getElementById('kartu-kendali-print-area');
        const filename = 'Kartu_Kendali_SPJ_{{ $tahunFilter }}.pdf';

        // Clone ke container terisolasi di body agar layout sidebar/topbar
        // tidak menggeser posisi elemen saat html2canvas memaksa windowWidth.
        const clone = source.cloneNode(true);
        const wrapper = document.createElement('div');
        wrapper.style.position = 'fixed';
        wrapper.style.top = '0';
        wrapper.style.left = '0';
        wrapper.style.zIndex = '-1';
        wrapper.style.width = '794px';
        wrapper.style.background = '#FFFFFF';
        clone.style.maxWidth = '794px';
        clone.style.margin = '0';
        wrapper.appendChild(clone);
        document.body.appendChild(wrapper);

        const opt = {
            margin:       [8, 8, 10, 8],
            filename:     filename,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, logging: false, windowWidth: 794, width: 794, x: 0, y: 0, scrollX: 0, scrollY: 0 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
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