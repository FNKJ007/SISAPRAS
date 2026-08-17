@extends('layouts.admin')
@section('title', 'Kartu Kendali Pembayaran — Admin')

@section('content')
<div>

    {{-- Header --}}
    <div class="no-print" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Kartu Kendali Pembayaran Pemeliharaan</h1>
            <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                Ledger saldo kumulatif pembayaran pemeliharaan berdasarkan data Monitoring Invoice, per tahun anggaran.
            </p>
        </div>
        <button type="button" onclick="downloadKartuKendaliPDF()" 
                style="display:inline-flex; align-items:center; gap:8px; padding:9px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:12.5px; font-weight:700; cursor:pointer; box-shadow:0 4px 14px rgba(27,42,107,0.25); transition:all 0.2s;">
            <i data-lucide="download" style="width:16px; height:16px;"></i>
            <span>Unduh PDF Kartu Kendali</span>
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid-container no-print" style="margin-bottom:24px;">
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#64748B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Invoice (Filter Aktif)</div>
            <div style="font-size:22px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total_invoice'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#1B2A6B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Nilai Kumulatif</div>
            <div style="font-size:18px; font-weight:800; color:#1B2A6B; margin-top:6px;">Rp {{ number_format($kpi['total_nilai'], 0, ',', '.') }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#059669; font-size:11.5px; font-weight:700; text-transform:uppercase;">Sudah Lunas ({{ $kpi['jumlah_lunas'] }})</div>
            <div style="font-size:18px; font-weight:800; color:#059669; margin-top:6px;">Rp {{ number_format($kpi['total_lunas'], 0, ',', '.') }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#D97706; font-size:11.5px; font-weight:700; text-transform:uppercase;">Belum Lunas ({{ $kpi['jumlah_belum'] }})</div>
            <div style="font-size:18px; font-weight:800; color:#D97706; margin-top:6px;">Rp {{ number_format($kpi['total_belum'], 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="admin-filter-bar no-print" style="margin-bottom:16px;">
        <form method="GET" action="{{ route('admin.pemeliharaan.kartu-kendali-pembayaran') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
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

                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Kode Rekening:</span>
                    <select name="kode_rekening" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        <option value="semua" @selected($rekeningFilter === 'semua')>Semua Rekening</option>
                        @foreach($rekeningList as $rek)
                            <option value="{{ $rek }}" @selected($rekeningFilter === $rek)>{{ $rek }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Status:</span>
                    <select name="status" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        <option value="semua" @selected($statusFilter === 'semua')>Semua Status</option>
                        <option value="draft" @selected($statusFilter === 'draft')>Draft</option>
                        <option value="diajukan" @selected($statusFilter === 'diajukan')>Diajukan</option>
                        <option value="disetujui" @selected($statusFilter === 'disetujui')>Disetujui</option>
                        <option value="lunas" @selected($statusFilter === 'lunas')>Lunas</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:8px;">
                <div style="position:relative;">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari no. invoice / lambung..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:230px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
                @if($rekeningFilter !== 'semua' || $statusFilter !== 'semua' || !empty($searchQuery))
                    <a href="{{ route('admin.pemeliharaan.kartu-kendali-pembayaran', ['tahun' => $tahunFilter]) }}" style="padding:7px 12px; background:#E2E8F0; color:#475569; border-radius:8px; font-size:12px; text-decoration:none; font-weight:600;">
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
            <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:3px double #0F172A; padding-bottom:12px; margin-bottom:18px; text-align:center;">
                <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo Pemkab" style="height:55px; width:auto;" onerror="this.style.display='none'">
                <div style="flex:1; padding:0 12px;">
                    <div style="font-size:12px; font-weight:700; letter-spacing:0.5px; color:#0F172A; text-transform:uppercase;">PEMERINTAH KABUPATEN BANDUNG</div>
                    <div style="font-size:15px; font-weight:800; color:#C0201F; text-transform:uppercase; margin:2px 0;">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                    <div style="font-size:10.5px; color:#475569; font-weight:500;">Bidang Sarana, Prasarana dan Informasi — Seksi Pemeliharaan Sarana dan Prasarana</div>
                </div>
                <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Damkar" style="height:55px; width:auto;" onerror="this.style.display='none'">
            </div>

            {{-- Judul Dokumen --}}
            <div style="text-align:center; margin-bottom:18px;">
                <h2 style="font-size:14px; font-weight:800; text-transform:uppercase; color:#1B2A6B; text-decoration:underline; letter-spacing:0.5px; margin:0 0 3px 0;">KARTU KENDALI PEMBAYARAN PEMELIHARAAN KENDARAAN</h2>
                <div style="font-size:11.5px; font-weight:600; color:#64748B;">
                    Tahun Anggaran {{ $tahunFilter }}
                </div>
            </div>

            @if($kartuKendaliRows->isEmpty())
                <div style="padding:56px 20px; text-align:center;">
                    <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px; border:1px solid #E2E8F0; margin-left:auto; margin-right:auto;">
                        <i data-lucide="clipboard-list" style="width:30px; height:30px; color:#64748B;"></i>
                    </div>
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Invoice</div>
                    <div style="font-size:12.5px; color:#94A3B8;">Tidak ditemukan invoice untuk tahun anggaran / filter yang dipilih.</div>
                </div>
            @else
                {{-- Tabel Presisi A4 (Tanpa Kolom Kode Rekening & Tanpa Overflow Cut-off) --}}
                <div style="margin-bottom:18px; border-radius:8px; border:1px solid #E2E8F0; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse; font-size:11px; text-align:left; table-layout:auto;">
                        <thead>
                            <tr style="background:#1B2A6B; color:#FFFFFF; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                                <th style="padding:8px 8px; width:30px; text-align:center;">No</th>
                                <th style="padding:8px 8px; width:80px;">Tanggal</th>
                                <th style="padding:8px 8px; width:150px;">Nomor Invoice</th>
                                <th style="padding:8px 8px; width:120px;">Unit / Lambung</th>
                                <th style="padding:8px 8px; width:125px; text-align:right;">Jumlah (Rp)</th>
                                <th style="padding:8px 8px; width:135px; text-align:right;">Saldo Kumulatif (Rp)</th>
                                <th style="padding:8px 8px; width:85px; text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statusStyles = [
                                    'draft'     => 'background:#F1F5F9; color:#475569; border:1px solid #CBD5E1;',
                                    'diajukan'  => 'background:#FEF3C7; color:#92400E; border:1px solid #FDE68A;',
                                    'disetujui' => 'background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE;',
                                    'lunas'     => 'background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0;',
                                ];
                                $statusLabels = [
                                    'draft'     => 'Draft',
                                    'diajukan'  => 'Diajukan',
                                    'disetujui' => 'Disetujui',
                                    'lunas'     => 'Lunas',
                                ];
                            @endphp
                            @foreach($kartuKendaliRows as $i => $row)
                                <tr style="border-bottom:1px solid #E2E8F0; {{ $i % 2 == 1 ? 'background:#FAFAFA;' : 'background:#FFFFFF;' }}">
                                    <td style="padding:8px 8px; text-align:center; color:#64748B; font-weight:600;">{{ $i + 1 }}</td>
                                    <td style="padding:8px 8px; color:#334155; white-space:nowrap;">{{ $row->tanggal_invoice ? $row->tanggal_invoice->format('d/m/Y') : '—' }}</td>
                                    <td style="padding:8px 8px; font-weight:700; color:#1E3A8A; word-break:break-word;">{{ $row->nomor_invoice }}</td>
                                    <td style="padding:8px 8px;">
                                        <div style="font-weight:700; color:#0F172A;">{{ $row->no_lambung ?? optional($row->unit)->nomor_lambung ?? '—' }}</div>
                                        <div style="font-size:9.5px; color:#64748B;">{{ $row->no_pol ?? optional($row->unit)->plat_nomor ?? '' }}</div>
                                    </td>
                                    <td style="padding:8px 8px; text-align:right; font-weight:700; color:#0F172A; font-variant-numeric:tabular-nums;">{{ number_format($row->total_biaya, 0, ',', '.') }}</td>
                                    <td style="padding:8px 8px; text-align:right; font-weight:800; color:#1B2A6B; font-variant-numeric:tabular-nums;">{{ number_format($row->saldo_kumulatif, 0, ',', '.') }}</td>
                                    <td style="padding:8px 8px; text-align:center;">
                                        <span style="{{ $statusStyles[$row->status] ?? $statusStyles['draft'] }} padding:2px 8px; border-radius:6px; font-size:9.5px; font-weight:700; white-space:nowrap; display:inline-block;">
                                            {{ $statusLabels[$row->status] ?? ucfirst($row->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#F8FAFC; border-top:2px solid #E2E8F0;">
                                <td colspan="4" style="padding:9px 10px; text-align:right; font-weight:800; color:#334155; font-size:11px; text-transform:uppercase;">TOTAL KUMULATIF PEMBAYARAN</td>
                                <td style="padding:9px 10px; text-align:right; font-weight:800; color:#1B2A6B; font-size:11.5px; font-variant-numeric:tabular-nums;">Rp {{ number_format($kpi['total_nilai'], 0, ',', '.') }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif

            {{-- Block Tanda Tangan Resmi --}}
            <div style="margin-top:24px; display:flex; justify-content:flex-end;">
                <div style="text-align:center; min-width:240px; font-size:11px; color:#334155;">
                    <div>Soreang, {{ now()->translatedFormat('d F Y') }}</div>
                    <div style="font-weight:700; color:#0F172A; margin-top:3px; margin-bottom:50px;">Kepala Seksi Pemeliharaan Sarana dan Prasarana</div>
                    <div style="font-weight:700; color:#0F172A; text-decoration:underline;">( .................................................... )</div>
                    <div style="font-size:10px; color:#64748B; margin-top:2px;">NIP. ....................................................</div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
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
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadKartuKendaliPDF() {
        const element = document.getElementById('kartu-kendali-print-area');
        const filename = 'Kartu_Kendali_Pembayaran_{{ $tahunFilter }}.pdf';
        const opt = {
            margin:       [6, 6, 6, 6],
            filename:     filename,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, logging: false, windowWidth: 794 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
@endpush
