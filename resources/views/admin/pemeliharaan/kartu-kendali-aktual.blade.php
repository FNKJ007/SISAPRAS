@extends('layouts.admin')
@section('title', 'Kartu Kendali Aktual — Admin')

@section('content')
<div>

    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Kartu Kendali Aktual Pemeliharaan</h1>
            <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                Ledger realisasi fisik pengerjaan pemeliharaan berdasarkan data Monitoring Aktual, per tahun.
            </p>
        </div>
        <button type="button" onclick="window.print()" style="display:inline-flex; align-items:center; gap:6px; padding:9px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:12.5px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(27,42,107,0.25);">
            <i data-lucide="printer" style="width:16px; height:16px;"></i>
            Cetak Kartu Kendali
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid-container" style="margin-bottom:24px;">
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
    <div class="admin-filter-bar" style="margin-bottom:16px;">
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

    {{-- Kartu Kendali Table --}}
    <div id="kartu-kendali-print-area" style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">

        {{-- Kop Kartu Kendali --}}
        <div style="padding:20px 24px 14px; border-bottom:2px solid #1B2A6B;">
            <div style="font-size:15px; font-weight:800; color:#0F172A; text-align:center;">KARTU KENDALI AKTUAL PEMELIHARAAN KENDARAAN</div>
            <div style="font-size:12px; color:#64748B; text-align:center; margin-top:2px;">
                Tahun {{ $tahunFilter }}
                @if($posFilter !== 'semua') &mdash; Posko: {{ $posFilter }} @endif
            </div>
        </div>

        @if($kartuKendaliRows->isEmpty())
            <div style="padding:56px 20px; text-align:center;">
                <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px; border:1px solid #E2E8F0; margin-left:auto; margin-right:auto;">
                    <i data-lucide="clipboard-list" style="width:30px; height:30px; color:#64748B;"></i>
                </div>
                <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Pengerjaan</div>
                <div style="font-size:12.5px; color:#94A3B8;">Tidak ditemukan unit untuk tahun / filter yang dipilih.</div>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; min-width:1150px; border-collapse:collapse; font-size:12.5px; text-align:left;">
                    <thead>
                        <tr style="background:#1B2A6B; color:#FFFFFF;">
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; width:40px; text-align:center;">No</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">Mulai — Selesai</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">Unit / Lambung</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">Posko</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">Item Perbaikan</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; width:170px;">Progres</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; text-align:center;">Status</th>
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
                            <tr style="border-bottom:1px solid #F1F5F9;">
                                <td style="padding:11px 14px; text-align:center; color:#64748B; font-weight:600;">{{ $i + 1 }}</td>
                                <td style="padding:11px 14px; color:#334155; white-space:nowrap; font-size:11.5px;">
                                    <div>Mulai: <strong>{{ $row->tanggal_mulai_pengerjaan?->format('d/m/Y') ?? '—' }}</strong></div>
                                    <div>Selesai: <strong>{{ $row->tanggal_selesai_pengerjaan?->format('d/m/Y') ?? '—' }}</strong></div>
                                </td>
                                <td style="padding:11px 14px;">
                                    <div style="font-weight:700; color:#1E3A8A;">{{ $row->nomor_lambung }}</div>
                                    <div style="font-size:10.5px; color:#94A3B8;">{{ $row->nama_pemegang }}</div>
                                </td>
                                <td style="padding:11px 14px; font-weight:600; color:#334155;">{{ $row->pos }}</td>
                                <td style="padding:11px 14px; color:#475569; max-width:220px;">
                                    {{ \Illuminate\Support\Str::limit($row->item_perbaikan, 55) }}
                                    @if($row->progress_catatan)
                                        <div style="font-size:10.5px; color:#94A3B8; margin-top:2px;">{{ \Illuminate\Support\Str::limit($row->progress_catatan, 45) }}</div>
                                    @endif
                                </td>
                                <td style="padding:11px 14px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="flex:1; height:8px; background:#F1F5F9; border-radius:6px; overflow:hidden;">
                                            <div style="height:100%; width:{{ $row->progress_persen }}%; background:{{ $s[1] }}; border-radius:6px;"></div>
                                        </div>
                                        <span style="font-size:11px; font-weight:700; color:#334155;">{{ $row->progress_persen }}%</span>
                                    </div>
                                </td>
                                <td style="padding:11px 14px; text-align:center;">
                                    <span style="background:{{ $s[0] }}; color:{{ $s[1] }}; border:1px solid {{ $s[2] }}; padding:3px 10px; border-radius:6px; font-size:11px; font-weight:700; white-space:nowrap;">
                                        {{ $statusLabels[$row->status_pengerjaan] ?? ucfirst($row->status_pengerjaan) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#F8FAFC; border-top:2px solid #E2E8F0;">
                            <td colspan="5" style="padding:12px 14px; text-align:right; font-weight:800; color:#334155; font-size:12.5px;">TOTAL UNIT SELESAI</td>
                            <td colspan="2" style="padding:12px 14px; text-align:left; font-weight:800; color:#059669;">{{ $kpi['selesai'] }} dari {{ $kpi['total'] }} unit</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        {{-- Tanda tangan (khusus tampil saat cetak) --}}
        <div style="padding:32px 24px 24px; display:flex; justify-content:flex-end;">
            <div style="text-align:center; font-size:12px; color:#334155;">
                <div>Soreang, {{ now()->translatedFormat('d F Y') }}</div>
                <div style="font-weight:700; margin-top:2px;">Mengetahui,</div>
                <div style="height:60px;"></div>
                <div style="font-weight:800; border-top:1px solid #334155; padding-top:4px; min-width:180px;">( ................................. )</div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        #kartu-kendali-print-area, #kartu-kendali-print-area * { visibility: visible; }
        #kartu-kendali-print-area { position: absolute; top: 0; left: 0; width: 100%; box-shadow: none; border: none; }
    }
</style>
@endsection
