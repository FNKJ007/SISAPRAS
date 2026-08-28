@extends('layouts.admin')
@section('title', 'Dashboard Admin')

@section('content')

<div style="max-width:100%;">

    {{-- Page Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:14px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#121E4E; margin-bottom:4px; display:flex; align-items:center; gap:10px;">
                <span>Dashboard Analitik &amp; Monitoring Sektor</span>
                <span style="font-size:11px; font-weight:700; background:#EEF2FF; color:#1B2A6B; border:1px solid #C7D2FE; border-radius:20px; padding:3px 10px;">Live Data</span>
            </h1>
            <p style="font-size:13px; color:#64748B; margin:0;">Ringkasan real-time status armada, peralatan sarpras, inspeksi harian &amp; realisasi biaya pemeliharaan.</p>
        </div>

        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            {{-- Filter Tahun --}}
            <form method="GET" action="{{ route('admin.dashboard') }}" style="margin:0;">
                <div style="display:flex; align-items:center; gap:6px; background:#FFFFFF; padding:4px 10px; border-radius:10px; border:1px solid #E2E8F0; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                    <i data-lucide="filter" style="width:14px; height:14px; color:#64748B;"></i>
                    <select name="tahun" onchange="this.form.submit()"
                            style="padding:4px 6px; font-size:12.5px; font-weight:700; border:none; background:transparent; color:#1E293B; outline:none; cursor:pointer;">
                        @for($y = max((int)date('Y'), 2026); $y >= 2026; $y--)
                            <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </form>

            {{-- Tanggal Hari Ini --}}
            <div style="display:flex; align-items:center; gap:8px; background:#FFFFFF; padding:8px 14px; border-radius:10px; border:1px solid #E2E8F0; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                <i data-lucide="calendar" style="width:16px; height:16px; color:#C0201F;"></i>
                <span style="font-size:12.5px; font-weight:700; color:#1E293B;">{{ \Illuminate\Support\Carbon::now()->translatedFormat('d F Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Stat Cards Grid (4 Cards Summary) --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:20px; margin-bottom:24px;">

        {{-- 1. Total Unit Armada --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); padding:20px 22px; border:1px solid #E2E8F0; position:relative; overflow:hidden;">
            <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, #1B2A6B, #3B82F6);"></div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:11px; font-weight:800; letter-spacing:.6px; text-transform:uppercase; color:#64748B;">Total Unit Armada</div>
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(27, 42, 107, 0.08); color:#1B2A6B; display:flex; align-items:center; justify-content:center;">
                    <i data-lucide="truck" style="width:20px; height:20px;"></i>
                </div>
            </div>
            <div style="display:flex; align-items:baseline; gap:8px; margin:10px 0 4px 0;">
                <div style="font-size:32px; font-weight:800; line-height:1.1; color:#0F172A;">{{ $totalUnit }}</div>
                <span style="font-size:13px; font-weight:700; color:#64748B;">Armada</span>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; font-size:11.5px; font-weight:600; margin-top:8px; padding-top:10px; border-top:1px dashed #F1F5F9;">
                <span style="color:#059669; background:#ECFDF5; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:700;">
                    ✓ {{ $unitAktif }} Siap Operasi
                </span>
                <a href="{{ route('admin.pemeliharaan.data-unit') }}" style="color:#1B2A6B; text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
                    Detail <i data-lucide="arrow-right" style="width:13px; height:13px;"></i>
                </a>
            </div>
        </div>

        {{-- 2. Total Peralatan Sarpras --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); padding:20px 22px; border:1px solid #E2E8F0; position:relative; overflow:hidden;">
            <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, #059669, #10B981);"></div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:11px; font-weight:800; letter-spacing:.6px; text-transform:uppercase; color:#64748B;">Peralatan Sarpras</div>
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(5, 150, 105, 0.08); color:#059669; display:flex; align-items:center; justify-content:center;">
                    <i data-lucide="shield-check" style="width:20px; height:20px;"></i>
                </div>
            </div>
            <div style="display:flex; align-items:baseline; gap:8px; margin:10px 0 4px 0;">
                <div style="font-size:32px; font-weight:800; line-height:1.1; color:#0F172A;">{{ number_format($totalPeralatan, 0, ',', '.') }}</div>
                <span style="font-size:13px; font-weight:700; color:#64748B;">Item Terdaftar</span>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; font-size:11.5px; font-weight:600; margin-top:8px; padding-top:10px; border-top:1px dashed #F1F5F9;">
                <span style="color:#059669; background:#ECFDF5; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:700;">
                    ✓ Master Data Aktif
                </span>
                <a href="{{ route('admin.pemeliharaan.data-peralatan') }}" style="color:#059669; text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
                    Detail <i data-lucide="arrow-right" style="width:13px; height:13px;"></i>
                </a>
            </div>
        </div>

        {{-- 3. Inspeksi & Pemeliharaan --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); padding:20px 22px; border:1px solid #E2E8F0; position:relative; overflow:hidden;">
            <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, #D97706, #F59E0B);"></div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:11px; font-weight:800; letter-spacing:.6px; text-transform:uppercase; color:#64748B;">Pemeriksaan &amp; Servis</div>
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(217, 119, 6, 0.08); color:#D97706; display:flex; align-items:center; justify-content:center;">
                    <i data-lucide="clipboard-check" style="width:20px; height:20px;"></i>
                </div>
            </div>
            <div style="display:flex; align-items:baseline; gap:8px; margin:10px 0 4px 0;">
                <div style="font-size:32px; font-weight:800; line-height:1.1; color:#0F172A;">{{ $totalPemeriksaan }}</div>
                <span style="font-size:13px; font-weight:700; color:#64748B;">Inspeksi ({{ $totalPengajuan }} Servis)</span>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; font-size:11.5px; font-weight:600; margin-top:8px; padding-top:10px; border-top:1px dashed #F1F5F9;">
                <span style="color:#D97706; background:#FEF3C7; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:700;">
                    🗓️ Terjadwal &amp; Rutin
                </span>
                <a href="{{ route('admin.pemeliharaan.pengajuan') }}" style="color:#D97706; text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
                    Detail <i data-lucide="arrow-right" style="width:13px; height:13px;"></i>
                </a>
            </div>
        </div>

        {{-- 4. Realisasi Biaya Pemeliharaan --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); padding:20px 22px; border:1px solid #E2E8F0; position:relative; overflow:hidden;">
            <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, #C0201F, #EF4444);"></div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:11px; font-weight:800; letter-spacing:.6px; text-transform:uppercase; color:#64748B;">Realisasi Biaya Servis</div>
                <div style="width:38px; height:38px; border-radius:10px; background:rgba(192, 32, 31, 0.08); color:#C0201F; display:flex; align-items:center; justify-content:center;">
                    <i data-lucide="receipt" style="width:20px; height:20px;"></i>
                </div>
            </div>
            <div style="display:flex; align-items:baseline; gap:8px; margin:10px 0 4px 0;">
                <div style="font-size:24px; font-weight:800; line-height:1.1; color:#0F172A;">Rp {{ number_format($totalInvoiceBiaya, 0, ',', '.') }}</div>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; font-size:11.5px; font-weight:600; margin-top:8px; padding-top:10px; border-top:1px dashed #F1F5F9;">
                <span style="color:#C0201F; background:#FEE2E2; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:700;">
                    💳 {{ $totalInvoiceCount }} Invoice Verified
                </span>
                <a href="{{ route('admin.pemeliharaan.monitoring-aktual.index') }}" style="color:#C0201F; text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
                    Detail <i data-lucide="arrow-right" style="width:13px; height:13px;"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- ===================== ABSEN PENGECEKAN HARIAN UNIT (REPORT SUMMARY HARIAN) ===================== --}}
    <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); border:1px solid #E2E8F0; overflow:hidden; margin-bottom:20px;" x-data="{ filterAbsen: 'semua' }">
        <div style="padding:18px 22px; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; background:linear-gradient(to right, #F8FAFC, #FFFFFF);">
            <div>
                <span style="font-size:15.5px; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="clipboard-check" style="width:19px; height:19px; color:#1B2A6B;"></i>
                    Absen Pengecekan Harian Unit Armada
                </span>
                <span style="font-size:12px; color:#64748B; margin-top:2px; display:block;">
                    Monitoring kepatuhan pemeriksaan harian 25 unit kendaraan pemadam, rescue, dan pencegahan per hari ini (<strong>{{ now()->translatedFormat('l, d F Y') }}</strong>).
                </span>
            </div>

            {{-- Filter Badges & KPI Counter --}}
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div style="display:inline-flex; background:#F1F5F9; padding:3px; border-radius:10px; font-size:12px; font-weight:700;">
                    <button type="button" @click="filterAbsen = 'semua'" :class="filterAbsen === 'semua' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800'" style="padding:5px 12px; border-radius:8px; border:none; cursor:pointer; transition:all 0.2s;">
                        Semua ({{ $absenSummary['total_unit'] }})
                    </button>
                    <button type="button" @click="filterAbsen = 'sudah'" :class="filterAbsen === 'sudah' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 hover:text-emerald-900'" style="padding:5px 12px; border-radius:8px; border:none; cursor:pointer; transition:all 0.2s;">
                        ✓ Sudah Dicek ({{ $absenSummary['sudah_dicek'] }})
                    </button>
                    <button type="button" @click="filterAbsen = 'belum'" :class="filterAbsen === 'belum' ? 'bg-red-600 text-white shadow-xs' : 'text-red-700 hover:text-red-900'" style="padding:5px 12px; border-radius:8px; border:none; cursor:pointer; transition:all 0.2s;">
                        ✕ Belum Dicek ({{ $absenSummary['belum_dicek'] }})
                    </button>
                </div>
            </div>
        </div>

        {{-- Table Absen Unit --}}
        <div style="overflow-x:auto; max-height:360px;" class="custom-scroll">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:12px;">
                <thead style="position:sticky; top:0; background:#F8FAFC; z-index:2; border-bottom:1px solid #E2E8F0; color:#475569; font-weight:800; text-transform:uppercase; font-size:11px; letter-spacing:0.5px;">
                    <tr>
                        <th style="padding:10px 16px;">Unit Armada</th>
                        <th style="padding:10px 16px;">Posko Penempatan</th>
                        <th style="padding:10px 16px;">Kategori</th>
                        <th style="padding:10px 16px;">Status Cek Hari Ini</th>
                        <th style="padding:10px 16px;">Petugas Pemeriksa</th>
                        <th style="padding:10px 16px;">Kebersihan</th>
                        <th style="padding:10px 16px; text-align:right;">Waktu Cek</th>
                    </tr>
                </thead>
                <tbody style="divide-y:1px solid #F1F5F9;">
                    @forelse($absenUnitList as $unitAbsen)
                        @php 
                            $uObj = is_array($unitAbsen) ? (object)$unitAbsen : (is_object($unitAbsen) ? $unitAbsen : (object)[]);
                            $isSudahDicek = !empty($uObj->sudah_dicek);
                        @endphp
                        <tr style="border-bottom:1px solid #F1F5F9;"
                            x-show="filterAbsen === 'semua' || (filterAbsen === 'sudah' && {{ $isSudahDicek ? 'true' : 'false' }}) || (filterAbsen === 'belum' && {{ !$isSudahDicek ? 'true' : 'false' }})"
                            class="hover:bg-gray-50/80 transition-colors">
                            <td style="padding:10px 16px;">
                                <strong style="color:#0F172A; font-weight:800; font-size:12.5px;">{{ strtoupper($uObj->nomor_lambung ?? '—') }}</strong>
                                <div style="font-size:11px; color:#64748B;">{{ !empty($uObj->plat_nomor) ? $uObj->plat_nomor . ' • ' : '' }}{{ $uObj->merk_tipe ?? '' }}</div>
                            </td>
                            <td style="padding:10px 16px; font-weight:600; color:#334155;">
                                <span style="display:inline-flex; align-items:center; gap:4px;">
                                    <i data-lucide="map-pin" style="width:13px; height:13px; color:#94A3B8;"></i>
                                    {{ $uObj->pos ?? '—' }}
                                </span>
                            </td>
                            <td style="padding:10px 16px;">
                                @php
                                    $catBadge = match(strtolower($uObj->kategori ?? '')) {
                                        'rescue' => 'background:#FEF3C7; color:#92400E;',
                                        'pencegahan' => 'background:#E0E7FF; color:#3730A3;',
                                        default => 'background:#FEE2E2; color:#991B1B;',
                                    };
                                @endphp
                                <span style="padding:3px 8px; border-radius:6px; font-size:10.5px; font-weight:700; text-transform:uppercase; {{ $catBadge }}">
                                    {{ $uObj->kategori ?? 'PEMADAM' }}
                                </span>
                            </td>
                            <td style="padding:10px 16px;">
                                @if($uObj->sudah_dicek ?? false)
                                    <span style="display:inline-flex; align-items:center; gap:5px; background:#D1FAE5; color:#065F46; padding:3px 9px; border-radius:12px; font-size:11px; font-weight:800;">
                                        <span style="width:6px; height:6px; border-radius:50%; background:#059669;"></span>
                                        Sudah Dicek
                                    </span>
                                @else
                                    <span style="display:inline-flex; align-items:center; gap:5px; background:#FEE2E2; color:#991B1B; padding:3px 9px; border-radius:12px; font-size:11px; font-weight:800;">
                                        <span style="width:6px; height:6px; border-radius:50%; background:#DC2626;"></span>
                                        Belum Dicek
                                    </span>
                                @endif
                            </td>
                            <td style="padding:10px 16px;">
                                @if($uObj->sudah_dicek ?? false)
                                    <span style="font-weight:700; color:#1E293B;">{{ $uObj->nama_pemeriksa ?? '—' }}</span>
                                    <div style="font-size:10.5px; color:#64748B;">{{ $uObj->jabatan ?? '—' }}</div>
                                @else
                                    <span style="color:#94A3B8; font-style:italic;">—</span>
                                @endif
                            </td>
                            <td style="padding:10px 16px;">
                                @if($uObj->sudah_dicek ?? false)
                                    @if(($uObj->kebersihan ?? '') === 'tidak_bersih')
                                        <span style="color:#DC2626; font-weight:700; font-size:11px;">⚠️ Tidak Bersih</span>
                                    @else
                                        <span style="color:#059669; font-weight:700; font-size:11px;">✨ Bersih</span>
                                    @endif
                                @else
                                    <span style="color:#94A3B8;">—</span>
                                @endif
                            </td>
                            <td style="padding:10px 16px; text-align:right;">
                                @if(($uObj->sudah_dicek ?? false) && !empty($uObj->waktu_cek))
                                    <span style="font-weight:700; color:#475569;">{{ $uObj->waktu_cek }} WIB</span>
                                @else
                                    <span style="color:#94A3B8;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:24px; text-align:center; color:#94A3B8;">Belum ada data unit armada yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Main Grid: Charts (Left 2/3) + Activity & Distribution (Right 1/3) --}}
    <div class="dash-bottom-grid" style="display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start;">

        {{-- Left Column: Charts Container --}}
        <div style="display:flex; flex-direction:column; gap:20px;">

            {{-- Chart 1: Grafik Tren Inspeksi & Perbaikan --}}
            <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); border:1px solid #E2E8F0; overflow:hidden;">
                <div style="padding:18px 22px; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <div>
                        <span style="font-size:15px; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="bar-chart-3" style="width:18px; height:18px; color:#1B2A6B;"></i>
                            Grafik Analitik Inspeksi &amp; Pemeliharaan {{ $currentYear }}
                        </span>
                        <span style="font-size:12px; color:#64748B; margin-top:2px; display:block;">Jumlah riwayat pemeriksaan harian unit, alat, dan pengajuan perbulan.</span>
                    </div>
                </div>

                {{-- Canvas Chart.js --}}
                <div style="padding:20px 22px; position:relative; height:270px;">
                    <canvas id="chartPemeliharaan" style="width:100%; height:100%;"></canvas>
                </div>

                {{-- Legend Footer --}}
                <div style="display:flex; gap:20px; padding:12px 22px; border-top:1px solid #F1F5F9; flex-wrap:wrap; background:#FAFCFE; justify-content:center;">
                    <div style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:#1E293B;">
                        <span style="width:12px; height:12px; border-radius:3px; background:#1B2A6B; display:inline-block;"></span> Inspeksi Unit
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:#1E293B;">
                        <span style="width:12px; height:12px; border-radius:3px; background:#059669; display:inline-block;"></span> Inspeksi Alat
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:#1E293B;">
                        <span style="width:12px; height:12px; border-radius:3px; background:#C0201F; display:inline-block;"></span> Pengajuan Servis
                    </div>
                </div>
            </div>

            {{-- Chart 2: Grafik Realisasi Biaya Pemeliharaan (Rp per Bulan) --}}
            <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); border:1px solid #E2E8F0; overflow:hidden;">
                <div style="padding:18px 22px; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <div>
                        <span style="font-size:15px; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:8px;">
                            <i data-lucide="trending-up" style="width:18px; height:18px; color:#C0201F;"></i>
                            Grafik Realisasi Biaya Perbaikan (Rp)
                        </span>
                        <span style="font-size:12px; color:#64748B; margin-top:2px; display:block;">Total pengeluaran biaya servis terverifikasi invoice per bulan.</span>
                    </div>
                    <span style="font-size:12px; font-weight:800; color:#C0201F; background:#FEE2E2; padding:4px 10px; border-radius:8px;">
                        Total: Rp {{ number_format($totalInvoiceBiaya, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Canvas Biaya --}}
                <div style="padding:20px 22px; position:relative; height:220px;">
                    <canvas id="chartBiaya" style="width:100%; height:100%;"></canvas>
                </div>
            </div>

        </div>

        {{-- Right Column: Pos Distribution + Recent Activity --}}
        <div style="display:flex; flex-direction:column; gap:20px;">

            {{-- 1. Sebaran Armada per Pos / Sektor --}}
            <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); border:1px solid #E2E8F0; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:14.5px; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="map-pin" style="width:17px; height:17px; color:#1B2A6B;"></i>
                        Sebaran Armada per Pos
                    </span>
                    <span style="font-size:11.5px; font-weight:700; color:#64748B;">Total {{ $totalUnit }} Unit</span>
                </div>
                <div style="padding:16px 20px; display:flex; flex-direction:column; gap:12px;">
                    @forelse($posDistribution as $posItem)
                        @php
                            $posName  = is_object($posItem) ? ($posItem->pos ?? '') : (is_array($posItem) ? ($posItem['pos'] ?? '') : (string) $posItem);
                            $posTotal = is_object($posItem) ? (int) ($posItem->total ?? 0) : (is_array($posItem) ? (int) ($posItem['total'] ?? 0) : 0);
                            $pct      = round(($posTotal / max($totalUnit, 1)) * 100);
                        @endphp
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">
                                <span>{{ $posName }}</span>
                                <span>{{ $posTotal }} Unit ({{ $pct }}%)</span>
                            </div>
                            <div style="width:100%; height:7px; background:#F1F5F9; border-radius:10px; overflow:hidden;">
                                <div style="width:{{ $pct }}%; height:100%; background:linear-gradient(90deg, #1B2A6B, #3B82F6); border-radius:10px;"></div>
                            </div>
                        </div>
                    @empty
                        <div style="padding:20px; text-align:center; color:#94A3B8; font-size:12px;">Belum ada data penempatan pos.</div>
                    @endforelse
                </div>
            </div>

            {{-- 2. Aktivitas Terbaru Stream --}}
            <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); border:1px solid #E2E8F0; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid #EDF2F7; display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-size:14.5px; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="activity" style="width:17px; height:17px; color:#C0201F;"></i>
                        Aktivitas Terbaru
                    </span>
                </div>
                <div style="padding:4px 20px 16px 20px;">
                    @forelse($activities as $act)
                        @php
                            $actIcon  = is_object($act) ? ($act->icon ?? 'activity') : ($act['icon'] ?? 'activity');
                            $actBg    = is_object($act) ? ($act->bg ?? 'rgba(27,42,107,.10)') : ($act['bg'] ?? 'rgba(27,42,107,.10)');
                            $actColor = is_object($act) ? ($act->color ?? '#1B2A6B') : ($act['color'] ?? '#1B2A6B');
                            $actText  = is_object($act) ? ($act->text ?? '') : ($act['text'] ?? '');
                            $actTime  = is_object($act) ? ($act->created_at ?? '') : ($act['created_at'] ?? '');
                            if ($actTime instanceof \Carbon\Carbon) {
                                $actTimeStr = $actTime->diffForHumans();
                            } else {
                                $actTimeStr = (string) $actTime;
                            }
                        @endphp
                        <div style="display:flex; align-items:flex-start; gap:12px; padding:12px 0; border-bottom:1px solid #F1F5F9;">
                            <div style="width:32px; height:32px; border-radius:10px; background:{{ $actBg }}; color:{{ $actColor }}; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;">
                                <i data-lucide="{{ $actIcon }}" style="width:15px; height:15px;"></i>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:12px; font-weight:700; color:#1E293B; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $actText }}</div>
                                <div style="font-size:10.5px; font-weight:600; color:#64748B; margin-top:2px;">{{ $actTimeStr }}</div>
                            </div>
                        </div>
                    @empty
                        <div style="padding:32px 10px; text-align:center; color:#94A3B8; font-size:12.5px;">
                            Belum ada aktivitas terbaru hari ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

@push('scripts')
{{-- Chart.js Library --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // ================= 1. CHART INSPEKSI & PEMELIHARAAN =================
    const ctx1 = document.getElementById('chartPemeliharaan').getContext('2d');

    const gradientBlue = ctx1.createLinearGradient(0, 0, 0, 250);
    gradientBlue.addColorStop(0, 'rgba(27, 42, 107, 0.85)');
    gradientBlue.addColorStop(1, 'rgba(27, 42, 107, 0.15)');

    const gradientEmerald = ctx1.createLinearGradient(0, 0, 0, 250);
    gradientEmerald.addColorStop(0, 'rgba(5, 150, 105, 0.85)');
    gradientEmerald.addColorStop(1, 'rgba(5, 150, 105, 0.15)');

    const gradientRed = ctx1.createLinearGradient(0, 0, 0, 250);
    gradientRed.addColorStop(0, 'rgba(192, 32, 31, 0.85)');
    gradientRed.addColorStop(1, 'rgba(192, 32, 31, 0.15)');

    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [
                {
                    label: 'Inspeksi Unit',
                    data: {{ json_encode($chartInspeksiUnit) }},
                    backgroundColor: gradientBlue,
                    borderColor: '#1B2A6B',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Inspeksi Alat',
                    data: {{ json_encode($chartInspeksiAlat) }},
                    backgroundColor: gradientEmerald,
                    borderColor: '#059669',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Pengajuan Servis',
                    data: {{ json_encode($chartPemeliharaan) }},
                    backgroundColor: gradientRed,
                    borderColor: '#C0201F',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0F172A',
                    titleFont: { family: 'Poppins', size: 12, weight: 'bold' },
                    bodyFont: { family: 'Poppins', size: 11 },
                    padding: 10,
                    cornerRadius: 8,
                    usePointStyle: true,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Poppins', size: 11, weight: '600' },
                        color: '#64748B'
                    }
                },
                y: {
                    grid: { color: '#F1F5F9', drawBorder: false },
                    ticks: {
                        font: { family: 'Poppins', size: 11, weight: '600' },
                        color: '#64748B',
                        stepSize: 1
                    }
                }
            }
        }
    });

    // ================= 2. CHART REALISASI BIAYA (RP) =================
    const ctx2 = document.getElementById('chartBiaya').getContext('2d');

    const gradientRedLine = ctx2.createLinearGradient(0, 0, 0, 200);
    gradientRedLine.addColorStop(0, 'rgba(192, 32, 31, 0.35)');
    gradientRedLine.addColorStop(1, 'rgba(192, 32, 31, 0.0)');

    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Biaya Realisasi (Rp)',
                data: {{ json_encode($chartBiaya) }},
                borderColor: '#C0201F',
                backgroundColor: gradientRedLine,
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#C0201F',
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0F172A',
                    titleFont: { family: 'Poppins', size: 12, weight: 'bold' },
                    bodyFont: { family: 'Poppins', size: 11 },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            let value = context.raw || 0;
                            return ' Biaya: Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Poppins', size: 11, weight: '600' },
                        color: '#64748B'
                    }
                },
                y: {
                    grid: { color: '#F1F5F9', drawBorder: false },
                    ticks: {
                        font: { family: 'Poppins', size: 10.5, weight: '600' },
                        color: '#64748B',
                        callback: function(value) {
                            if (value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt';
                            if (value >= 1000) return 'Rp ' + (value / 1000) + ' Rb';
                            return 'Rp ' + value;
                        }
                    }
                }
            }
        }
    });

});
</script>
@endpush

@push('styles')
<style>
@media (max-width: 992px) {
    .dash-bottom-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush

@endsection
