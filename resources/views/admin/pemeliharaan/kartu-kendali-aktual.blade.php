@extends('layouts.admin')
@section('title', 'Kartu Kendali Aktual — Admin')

@section('content')
<div>

    {{-- Header --}}
    <div class="no-print" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Kartu Kendali Aktual Pemeliharaan</h1>
            <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                Ledger realisasi fisik pengerjaan pemeliharaan berdasarkan data Monitoring Aktual, per tahun.
            </p>
        </div>
        <button type="button" onclick="downloadKartuKendaliAktualPDF()" 
                style="display:inline-flex; align-items:center; gap:8px; padding:9px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:12.5px; font-weight:700; cursor:pointer; box-shadow:0 4px 14px rgba(27,42,107,0.25); transition:all 0.2s;">
            <i data-lucide="download" style="width:16px; height:16px;"></i>
            <span>Unduh PDF Kartu Kendali</span>
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid-container no-print" style="margin-bottom:24px;">
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#64748B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Unit (Filter Aktif)</div>
            <div style="font-size:22px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#94A3B8; font-size:11.5px; font-weight:700; text-transform:uppercase;">Belum Mulai</div>
            <div style="font-size:22px; font-weight:800; color:#475569; margin-top:6px;">{{ $kpi['belum_mulai'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#D97706; font-size:11.5px; font-weight:700; text-transform:uppercase;">Dalam Pengerjaan</div>
            <div style="font-size:22px; font-weight:800; color:#D97706; margin-top:6px;">{{ $kpi['proses'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#059669; font-size:11.5px; font-weight:700; text-transform:uppercase;">Selesai</div>
            <div style="font-size:22px; font-weight:800; color:#059669; margin-top:6px;">{{ $kpi['selesai'] }}</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="admin-filter-bar no-print" style="margin-bottom:16px;">
        <form method="GET" action="{{ route('admin.pemeliharaan.kartu-kendali-aktual') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Tahun:</span>
                    <select name="tahun" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        @forelse($tahunList as $tahun)
                            <option value="{{ $tahun }}" @selected($tahunFilter == $tahun)>{{ $tahun }}</option>
                        @empty
                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                        @endforelse
                    </select>
                </div>

                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Status Pengerjaan:</span>
                    <select name="status_pengerjaan" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        <option value="semua" @selected($statusFilter === 'semua')>Semua Status</option>
                        <option value="belum_mulai" @selected($statusFilter === 'belum_mulai')>Belum Mulai</option>
                        <option value="proses" @selected($statusFilter === 'proses')>Dalam Pengerjaan</option>
                        <option value="selesai" @selected($statusFilter === 'selesai')>Selesai</option>
                    </select>
                </div>

                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Posko:</span>
                    <select name="pos" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        <option value="semua" @selected($posFilter === 'semua')>Semua Posko</option>
                        @foreach($posList as $pos)
                            <option value="{{ $pos }}" @selected($posFilter === $pos)>{{ $pos }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:8px;">
                <div style="position:relative;">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari lambung / pos / item..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:230px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
                @if($statusFilter !== 'semua' || $posFilter !== 'semua' || !empty($searchQuery))
                    <a href="{{ route('admin.pemeliharaan.kartu-kendali-aktual', ['tahun' => $tahunFilter]) }}" style="padding:7px 12px; background:#E2E8F0; color:#475569; border-radius:8px; font-size:12px; text-decoration:none; font-weight:600;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Container Kartu Kendali (Formatted Standard A4 Printable Area) --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
        <div id="kartu-kendali-print-area" style="padding:28px 32px; background:#FFFFFF; max-width:794px; margin:0 auto; box-sizing:border-box;">

            {{-- Kop Surat Resmi Pemkab / Damkar --}}
            <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:3px double #0F172A; padding-bottom:14px; margin-bottom:20px; text-align:center;">
                <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo Pemkab" style="height:60px; width:auto;" onerror="this.style.display='none'">
                <div style="flex:1; padding:0 15px;">
                    <div style="font-size:12.5px; font-weight:700; letter-spacing:0.5px; color:#0F172A; text-transform:uppercase;">PEMERINTAH KABUPATEN BANDUNG</div>
                    <div style="font-size:16px; font-weight:800; color:#C0201F; text-transform:uppercase; margin:2px 0;">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                    <div style="font-size:11px; color:#475569; font-weight:500;">Bidang Sarana, Prasarana dan Informasi — Seksi Pemeliharaan Sarana dan Prasarana</div>
                </div>
                <img src="{{ asset('images/logo-damkar.png') }}" alt="Logo Damkar" style="height:60px; width:auto;" onerror="this.style.display='none'">
            </div>

            {{-- Judul Dokumen --}}
            <div style="text-align:center; margin-bottom:20px;">
                <h2 style="font-size:15px; font-weight:800; text-transform:uppercase; color:#1B2A6B; text-decoration:underline; letter-spacing:0.5px; margin:0 0 4px 0;">KARTU KENDALI AKTUAL PEMELIHARAAN KENDARAAN</h2>
                <div style="font-size:12px; font-weight:600; color:#64748B;">
                    Tahun {{ $tahunFilter }}
                    @if($posFilter !== 'semua') &mdash; Posko: {{ $posFilter }} @endif
                </div>
            </div>

            @if($kartuKendaliRows->isEmpty())
                <div style="padding:56px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
                        <i data-lucide="clipboard-list" style="width:30px; height:30px; color:#64748B;"></i>
                    </div>
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Pengerjaan</div>
                    <div style="font-size:13px; color:#64748B; max-width:400px; margin:0 auto;">Tidak ditemukan unit untuk tahun / filter yang dipilih.</div>
                </div>
            @else
                {{-- Tabel Presisi A4 --}}
                <div style="margin-bottom:20px; border-radius:8px; border:1px solid #E2E8F0; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse; font-size:11.5px; text-align:left;">
                        <thead>
                            <tr style="background:#1B2A6B; color:#FFFFFF; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                                <th style="padding:9px 10px; width:35px; text-align:center;">No</th>
                                <th style="padding:9px 10px; width:100px;">Mulai — Selesai</th>
                                <th style="padding:9px 10px;">Unit / Lambung</th>
                                <th style="padding:9px 10px;">Posko</th>
                                <th style="padding:9px 10px;">Item Perbaikan</th>
                                <th style="padding:9px 10px; width:130px;">Progres</th>
                                <th style="padding:9px 10px; width:80px; text-align:center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statusStyles = [
                                    'belum_mulai' => ['#F8FAFC', '#475569', '#E2E8F0'],
                                    'proses'      => ['#FEF3C7', '#92400E', '#FDE68A'],
                                    'selesai'     => ['#ECFDF5', '#065F46', '#A7F3D0'],
                                ];
                                $statusLabels = [
                                    'belum_mulai' => 'Belum Mulai',
                                    'proses'      => 'Dalam Pengerjaan',
                                    'selesai'     => 'Selesai',
                                ];
                            @endphp
                            @foreach($kartuKendaliRows as $i => $row)
                                @php $s = $statusStyles[$row->status_pengerjaan] ?? $statusStyles['belum_mulai']; @endphp
                                <tr style="border-bottom:1px solid #E2E8F0; {{ $i % 2 == 1 ? 'background:#FAFAFA;' : 'background:#FFFFFF;' }}">
                                    <td style="padding:9px 10px; text-align:center; color:#64748B; font-weight:600;">{{ $i + 1 }}</td>
                                    <td style="padding:9px 10px; color:#334155; white-space:nowrap; font-size:10.5px;">
                                        <div>Mulai: <strong>{{ $row->tanggal_mulai_pengerjaan?->format('d/m/Y') ?? '—' }}</strong></div>
                                        <div>Selesai: <strong>{{ $row->tanggal_selesai_pengerjaan?->format('d/m/Y') ?? '—' }}</strong></div>
                                    </td>
                                    <td style="padding:9px 10px;">
                                        <div style="font-weight:700; color:#1E3A8A;">{{ $row->nomor_lambung }}</div>
                                        <div style="font-size:10px; color:#64748B;">{{ $row->nama_pemegang }}</div>
                                    </td>
                                    <td style="padding:9px 10px; font-weight:600; color:#334155;">{{ $row->pos }}</td>
                                    <td style="padding:9px 10px; color:#475569; max-width:200px;">
                                        {{ \Illuminate\Support\Str::limit($row->item_perbaikan, 55) }}
                                        @if($row->progress_catatan)
                                            <div style="font-size:10px; color:#94A3B8; margin-top:2px;">{{ \Illuminate\Support\Str::limit($row->progress_catatan, 45) }}</div>
                                        @endif
                                    </td>
                                    <td style="padding:9px 10px;">
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <div style="flex:1; height:7px; background:#F1F5F9; border-radius:6px; overflow:hidden;">
                                                <div style="height:100%; width:{{ $row->progress_persen }}%; background:{{ $s[1] }}; border-radius:6px;"></div>
                                            </div>
                                            <span style="font-size:10px; font-weight:700; color:#334155;">{{ $row->progress_persen }}%</span>
                                        </div>
                                    </td>
                                    <td style="padding:9px 10px; text-align:center;">
                                        <span style="background:{{ $s[0] }}; color:{{ $s[1] }}; border:1px solid {{ $s[2] }}; padding:2px 8px; border-radius:6px; font-size:10px; font-weight:700; white-space:nowrap;">
                                            {{ $statusLabels[$row->status_pengerjaan] ?? ucfirst($row->status_pengerjaan) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#F8FAFC; border-top:2px solid #E2E8F0;">
                                <td colspan="5" style="padding:10px 12px; text-align:right; font-weight:800; color:#334155; font-size:11.5px; text-transform:uppercase;">TOTAL UNIT SELESAI</td>
                                <td colspan="2" style="padding:10px 12px; text-align:left; font-weight:800; color:#059669; font-size:11.5px;">{{ $kpi['selesai'] }} dari {{ $kpi['total'] }} unit</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif

            {{-- Block Tanda Tangan Resmi --}}
            <div style="margin-top:28px; display:flex; justify-content:flex-end;">
                <div style="text-align:center; min-width:260px; font-size:11.5px; color:#334155;">
                    <div>Soreang, {{ now()->translatedFormat('d F Y') }}</div>
                    <div style="font-weight:700; color:#0F172A; margin-top:4px; margin-bottom:55px;">Kepala Seksi Pemeliharaan Sarana dan Prasarana</div>
                    <div style="font-weight:700; color:#0F172A; text-decoration:underline;">( .................................................... )</div>
                    <div style="font-size:10.5px; color:#64748B; margin-top:3px;">NIP. ....................................................</div>
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
    function downloadKartuKendaliAktualPDF() {
        const element = document.getElementById('kartu-kendali-print-area');
        const filename = 'Kartu_Kendali_Aktual_{{ $tahunFilter }}.pdf';
        const opt = {
            margin:       [8, 8, 8, 8],
            filename:     filename,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, logging: false },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
@endpush
