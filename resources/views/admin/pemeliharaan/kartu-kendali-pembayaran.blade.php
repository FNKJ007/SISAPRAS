@extends('layouts.admin')
@section('title', 'Kartu Kendali Pembayaran — Admin')

@section('content')
<div>

    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Kartu Kendali Pembayaran Pemeliharaan</h1>
            <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                Ledger saldo kumulatif pembayaran pemeliharaan berdasarkan data Monitoring Invoice, per kode rekening &amp; tahun anggaran.
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
    <div class="admin-filter-bar" style="margin-bottom:16px;">
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

    {{-- Kartu Kendali Table --}}
    <div id="kartu-kendali-print-area" style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">

        {{-- Kop Kartu Kendali (khusus tampil rapi saat cetak) --}}
        <div style="padding:20px 24px 14px; border-bottom:2px solid #1B2A6B;">
            <div style="font-size:15px; font-weight:800; color:#0F172A; text-align:center;">KARTU KENDALI PEMBAYARAN PEMELIHARAAN KENDARAAN</div>
            <div style="font-size:12px; color:#64748B; text-align:center; margin-top:2px;">
                Tahun Anggaran {{ $tahunFilter }}
                @if($rekeningFilter !== 'semua') &mdash; Kode Rekening: {{ $rekeningFilter }} @endif
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
            <div style="overflow-x:auto;">
                <table style="width:100%; min-width:1100px; border-collapse:collapse; font-size:12.5px; text-align:left;">
                    <thead>
                        <tr style="background:#1B2A6B; color:#FFFFFF;">
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; width:40px; text-align:center;">No</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; width:100px;">Tanggal</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">Nomor Invoice</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">Unit / Lambung</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">Kode Rekening</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; text-align:right;">Jumlah (Rp)</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; text-align:right;">Saldo Kumulatif (Rp)</th>
                            <th style="padding:12px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; text-align:center;">Status</th>
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
                            <tr style="border-bottom:1px solid #F1F5F9;">
                                <td style="padding:11px 14px; text-align:center; color:#64748B; font-weight:600;">{{ $i + 1 }}</td>
                                <td style="padding:11px 14px; color:#334155; white-space:nowrap;">{{ $row->tanggal_invoice ? $row->tanggal_invoice->format('d/m/Y') : '—' }}</td>
                                <td style="padding:11px 14px; font-weight:700; color:#1E3A8A;">{{ $row->nomor_invoice }}</td>
                                <td style="padding:11px 14px;">
                                    <div style="font-weight:700; color:#0F172A;">{{ $row->no_lambung ?? optional($row->unit)->nomor_lambung ?? '—' }}</div>
                                    <div style="font-size:10.5px; color:#94A3B8;">{{ $row->no_pol ?? optional($row->unit)->plat_nomor ?? '' }}</div>
                                </td>
                                <td style="padding:11px 14px; color:#475569; font-family:monospace; font-size:11.5px;">{{ $row->kode_rekening ?? '—' }}</td>
                                <td style="padding:11px 14px; text-align:right; font-weight:700; color:#0F172A;">{{ number_format($row->total_biaya, 0, ',', '.') }}</td>
                                <td style="padding:11px 14px; text-align:right; font-weight:800; color:#1B2A6B;">{{ number_format($row->saldo_kumulatif, 0, ',', '.') }}</td>
                                <td style="padding:11px 14px; text-align:center;">
                                    <span style="{{ $statusStyles[$row->status] ?? $statusStyles['draft'] }} padding:3px 10px; border-radius:6px; font-size:11px; font-weight:700; white-space:nowrap;">
                                        {{ $statusLabels[$row->status] ?? ucfirst($row->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#F8FAFC; border-top:2px solid #E2E8F0;">
                            <td colspan="5" style="padding:12px 14px; text-align:right; font-weight:800; color:#334155; font-size:12.5px;">TOTAL KUMULATIF</td>
                            <td style="padding:12px 14px; text-align:right; font-weight:800; color:#0F172A;">Rp {{ number_format($kpi['total_nilai'], 0, ',', '.') }}</td>
                            <td colspan="2"></td>
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
