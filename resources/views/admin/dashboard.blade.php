@php
    use Illuminate\Support\Js;
@endphp
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
                <a href="{{ route('admin.pemeliharaan.spj-pembayaran.index') }}" style="color:#C0201F; text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
                    Detail <i data-lucide="arrow-right" style="width:13px; height:13px;"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- ===================== ABSEN PENGECEKAN HARIAN (UNIT & PERALATAN) ===================== --}}
    <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); border:1px solid #E2E8F0; overflow:hidden; margin-bottom:20px;"
         x-data="{ tabAbsen: 'unit', filterUnit: 'semua', filterAlat: 'semua' }">
        
        {{-- Header & Sub-Tab Navigation --}}
        <div style="padding:18px 22px; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; background:linear-gradient(to right, #F8FAFC, #FFFFFF);">
            <div>
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <span style="font-size:15.5px; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="clipboard-check" style="width:19px; height:19px; color:#1B2A6B;"></i>
                        Absen Pengecekan Harian
                    </span>

                    {{-- Tab Switcher --}}
                    <div style="display:inline-flex; background:#E2E8F0; padding:2.5px; border-radius:10px; font-size:12px; font-weight:700;">
                        <button type="button" @click="tabAbsen = 'unit'"
                                :class="tabAbsen === 'unit' ? 'bg-[#1B2A6B] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                style="padding:4px 12px; border-radius:8px; border:none; cursor:pointer; transition:all 0.15s; display:inline-flex; align-items:center; gap:6px;">
                            <i data-lucide="truck" style="width:13px; height:13px;"></i>
                            <span>Unit Armada</span>
                            <span :class="tabAbsen === 'unit' ? 'bg-white/20 text-white' : 'bg-slate-300 text-slate-700'"
                                  style="padding:1px 6px; border-radius:999px; font-size:10px; font-weight:800;">
                                {{ $absenSummary['total_unit'] ?? 0 }}
                            </span>
                        </button>
                        <button type="button" @click="tabAbsen = 'alat'"
                                :class="tabAbsen === 'alat' ? 'bg-[#1B2A6B] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                style="padding:4px 12px; border-radius:8px; border:none; cursor:pointer; transition:all 0.15s; display:inline-flex; align-items:center; gap:6px;">
                            <i data-lucide="wrench" style="width:13px; height:13px;"></i>
                            <span>Peralatan Pos</span>
                            <span :class="tabAbsen === 'alat' ? 'bg-white/20 text-white' : 'bg-slate-300 text-slate-700'"
                                  style="padding:1px 6px; border-radius:999px; font-size:10px; font-weight:800;">
                                {{ $absenAlatSummary['total_pos_kategori'] ?? 0 }}
                            </span>
                        </button>
                    </div>
                </div>

                <span style="font-size:12px; color:#64748B; margin-top:4px; display:block;" x-show="tabAbsen === 'unit'">
                    Monitoring kepatuhan pemeriksaan harian {{ $absenSummary['total_unit'] ?? 25 }} unit kendaraan pemadam, rescue, dan pencegahan per hari ini (<strong>{{ now()->translatedFormat('l, d F Y') }}</strong>).
                </span>
                <span style="font-size:12px; color:#64748B; margin-top:4px; display:block;" x-show="tabAbsen === 'alat'" x-cloak>
                    Monitoring kepatuhan pemeriksaan harian peralatan operasional di seluruh 9 Pos Damkar sesuai kapabilitas pos per hari ini (<strong>{{ now()->translatedFormat('l, d F Y') }}</strong>).
                </span>
            </div>

            {{-- Filter Badges: Unit --}}
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;" x-show="tabAbsen === 'unit'">
                <div style="display:inline-flex; background:#F1F5F9; padding:3px; border-radius:10px; font-size:12px; font-weight:700;">
                    <button type="button" @click="filterUnit = 'semua'" :class="filterUnit === 'semua' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800'" style="padding:5px 12px; border-radius:8px; border:none; cursor:pointer; transition:all 0.2s;">
                        Semua ({{ $absenSummary['total_unit'] ?? 0 }})
                    </button>
                    <button type="button" @click="filterUnit = 'sudah'" :class="filterUnit === 'sudah' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 hover:text-emerald-900'" style="padding:5px 12px; border-radius:8px; border:none; cursor:pointer; transition:all 0.2s;">
                        ✓ Sudah Dicek ({{ $absenSummary['sudah_dicek'] ?? 0 }})
                    </button>
                    <button type="button" @click="filterUnit = 'belum'" :class="filterUnit === 'belum' ? 'bg-red-600 text-white shadow-xs' : 'text-red-700 hover:text-red-900'" style="padding:5px 12px; border-radius:8px; border:none; cursor:pointer; transition:all 0.2s;">
                        ✕ Belum Dicek ({{ $absenSummary['belum_dicek'] ?? 0 }})
                    </button>
                </div>
            </div>

            {{-- Filter Badges: Peralatan --}}
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;" x-show="tabAbsen === 'alat'" x-cloak>
                <div style="display:inline-flex; background:#F1F5F9; padding:3px; border-radius:10px; font-size:12px; font-weight:700;">
                    <button type="button" @click="filterAlat = 'semua'" :class="filterAlat === 'semua' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800'" style="padding:5px 12px; border-radius:8px; border:none; cursor:pointer; transition:all 0.2s;">
                        Semua ({{ $absenAlatSummary['total_pos_kategori'] ?? 0 }})
                    </button>
                    <button type="button" @click="filterAlat = 'sudah'" :class="filterAlat === 'sudah' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 hover:text-emerald-900'" style="padding:5px 12px; border-radius:8px; border:none; cursor:pointer; transition:all 0.2s;">
                        ✓ Sudah Dicek ({{ $absenAlatSummary['sudah_dicek'] ?? 0 }})
                    </button>
                    <button type="button" @click="filterAlat = 'belum'" :class="filterAlat === 'belum' ? 'bg-red-600 text-white shadow-xs' : 'text-red-700 hover:text-red-900'" style="padding:5px 12px; border-radius:8px; border:none; cursor:pointer; transition:all 0.2s;">
                        ✕ Belum Dicek ({{ $absenAlatSummary['belum_dicek'] ?? 0 }})
                    </button>
                </div>
            </div>
        </div>

        {{-- ===================== TAB 1: TABLE ABSEN UNIT ===================== --}}
        <div style="overflow-x:auto; max-height:360px;" class="custom-scroll" x-show="tabAbsen === 'unit'">
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:12px;">
                <thead style="position:sticky; top:0; background:#F8FAFC; z-index:2; border-bottom:1px solid #E2E8F0; color:#475569; font-weight:800; text-transform:uppercase; font-size:11px; letter-spacing:0.5px;">
                    <tr>
                        <th style="padding:10px 16px;">Unit Armada</th>
                        <th style="padding:10px 16px;">Posko Penempatan</th>
                        <th style="padding:10px 16px; text-align:center;">Kategori</th>
                        <th style="padding:10px 16px; text-align:center;">Status Cek Hari Ini</th>
                        <th style="padding:10px 16px; text-align:center;">Petugas Pemeriksa</th>
                        <th style="padding:10px 16px; text-align:center;">Kebersihan</th>
                        <th style="padding:10px 16px; text-align:center;">Waktu Cek</th>
                    </tr>
                </thead>
                <tbody style="divide-y:1px solid #F1F5F9;">
                    @forelse($absenUnitList as $unitAbsen)
                        @php 
                            $uObj = is_array($unitAbsen) ? (object)$unitAbsen : (is_object($unitAbsen) ? $unitAbsen : (object)[]);
                            $isSudahDicek = !empty($uObj->sudah_dicek);
                        @endphp
                        <tr style="border-bottom:1px solid #F1F5F9;"
                            x-show="filterUnit === 'semua' || (filterUnit === 'sudah' && {{ $isSudahDicek ? 'true' : 'false' }}) || (filterUnit === 'belum' && {{ !$isSudahDicek ? 'true' : 'false' }})"
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
                            <td style="padding:10px 16px; text-align:center;">
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
                            <td style="padding:10px 16px; text-align:center;">
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
                            <td style="padding:10px 16px; text-align:center;">
                                @if($uObj->sudah_dicek ?? false)
                                    <span style="font-weight:700; color:#1E293B;">{{ $uObj->nama_pemeriksa ?? '—' }}</span>
                                    <div style="font-size:10.5px; color:#64748B;">{{ $uObj->jabatan ?? '—' }}</div>
                                @else
                                    <span style="color:#94A3B8; font-style:italic;">—</span>
                                @endif
                            </td>
                            <td style="padding:10px 16px; text-align:center;">
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
                            <td style="padding:10px 16px; text-align:center;">
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

        {{-- ===================== TAB 2: TABLE ABSEN PERALATAN ===================== --}}
        <div style="overflow-x:auto; max-height:360px;" class="custom-scroll" x-show="tabAbsen === 'alat'" x-cloak>
            <table style="width:100%; border-collapse:collapse; text-align:left; font-size:12px;">
                <thead style="position:sticky; top:0; background:#F8FAFC; z-index:2; border-bottom:1px solid #E2E8F0; color:#475569; font-weight:800; text-transform:uppercase; font-size:11px; letter-spacing:0.5px;">
                    <tr>
                        <th style="padding:10px 16px;">Pos Damkar</th>
                        <th style="padding:10px 16px; text-align:center;">Kategori Peralatan</th>
                        <th style="padding:10px 16px; text-align:center;">Status Cek Hari Ini</th>
                        <th style="padding:10px 16px; text-align:center;">Kondisi Alat</th>
                        <th style="padding:10px 16px; text-align:center;">Petugas Pemeriksa</th>
                        <th style="padding:10px 16px; text-align:center;">Waktu Cek</th>
                    </tr>
                </thead>
                <tbody style="divide-y:1px solid #F1F5F9;">
                    @forelse($absenAlatList ?? [] as $alatAbsen)
                        @php 
                            $aObj = is_array($alatAbsen) ? (object)$alatAbsen : (is_object($alatAbsen) ? $alatAbsen : (object)[]);
                            $isSudahDicek = !empty($aObj->sudah_dicek);
                        @endphp
                        <tr style="border-bottom:1px solid #F1F5F9;"
                            x-show="filterAlat === 'semua' || (filterAlat === 'sudah' && {{ $isSudahDicek ? 'true' : 'false' }}) || (filterAlat === 'belum' && {{ !$isSudahDicek ? 'true' : 'false' }})"
                            class="hover:bg-gray-50/80 transition-colors">
                            <td style="padding:10px 16px; font-weight:700; color:#0F172A;">
                                <span style="display:inline-flex; align-items:center; gap:5px;">
                                    <i data-lucide="map-pin" style="width:13px; height:13px; color:#1B2A6B;"></i>
                                    {{ $aObj->pos ?? '—' }}
                                </span>
                            </td>
                            <td style="padding:10px 16px; text-align:center;">
                                @php
                                    $alatBadge = match(strtolower($aObj->kategori ?? '')) {
                                        'rescue'         => 'background:#FEF3C7; color:#92400E; border:1px solid #FDE68A;',
                                        'pencegahan'     => 'background:#E0E7FF; color:#3730A3; border:1px solid #C7D2FE;',
                                        'command_center' => 'background:#F3E8FF; color:#6B21A8; border:1px solid #E9D5FF;',
                                        default          => 'background:#FEE2E2; color:#991B1B; border:1px solid #FECACA;',
                                    };
                                @endphp
                                <span style="padding:3px 9px; border-radius:6px; font-size:10.5px; font-weight:700; text-transform:uppercase; {{ $alatBadge }}">
                                    {{ $aObj->kategori_label ?? 'ALAT PEMADAM' }}
                                </span>
                            </td>
                            <td style="padding:10px 16px; text-align:center;">
                                @if($aObj->sudah_dicek ?? false)
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
                            <td style="padding:10px 16px; text-align:center;">
                                @if($aObj->sudah_dicek ?? false)
                                    <span style="display:inline-flex; align-items:center; justify-content:center; gap:4px; font-size:11.5px; font-weight:700;">
                                        <span style="color:#059669;">{{ $aObj->total_baik ?? 0 }} Baik</span>
                                        @if(($aObj->total_rusak ?? 0) > 0)
                                            <span style="color:#94A3B8;">•</span>
                                            <span style="color:#DC2626; background:#FEE2E2; padding:1px 6px; border-radius:4px;">{{ $aObj->total_rusak }} Rusak</span>
                                        @endif
                                    </span>
                                @else
                                    <span style="color:#94A3B8; font-style:italic;">—</span>
                                @endif
                            </td>
                            <td style="padding:10px 16px; text-align:center;">
                                @if($aObj->sudah_dicek ?? false)
                                    <span style="font-weight:700; color:#1E293B;">{{ $aObj->nama_pemeriksa ?? '—' }}</span>
                                    <div style="font-size:10.5px; color:#64748B;">{{ $aObj->jabatan ?? '—' }}</div>
                                @else
                                    <span style="color:#94A3B8; font-style:italic;">—</span>
                                @endif
                            </td>
                            <td style="padding:10px 16px; text-align:center;">
                                @if(($aObj->sudah_dicek ?? false) && !empty($aObj->waktu_cek))
                                    <span style="font-weight:700; color:#475569;">{{ $aObj->waktu_cek }} WIB</span>
                                @else
                                    <span style="color:#94A3B8;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:24px; text-align:center; color:#94A3B8;">Belum ada data target pengecekan peralatan yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== KALENDER PEMELIHARAAN (SEMUA POS & UNIT) ===================== --}}
    <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); border:1px solid #E2E8F0; padding:22px; margin-bottom:20px;"
         x-data="calendarModal()" @keydown.escape.window="closeModal()">

        {{-- MODAL DETAIL SIMPEL & TANPA SCROLL KANAN-KIRI --}}
        <template x-teleport="body">
            <div x-show="modalOpen"
                 x-cloak
                 class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-4"
                 style="background-color: rgba(15, 23, 42, 0.72);"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">

                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[85vh] overflow-y-auto overflow-x-hidden border border-gray-100 custom-scrollbar m-auto"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.stop>

                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl z-10">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-red-50 text-red-700 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="calendar" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm sm:text-base leading-tight">Detail Pengajuan (Semua Pos)</h3>
                                <p class="text-[11px] font-semibold text-red-700 mt-0.5" x-text="selectedDate"></p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()"
                                class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                            <i data-lucide="x" class="w-4.5 h-4.5"></i>
                        </button>
                    </div>

                    {{-- Body List Events --}}
                    <div class="p-4 sm:p-5 space-y-3">
                        <template x-for="(event, idx) in selectedEvents" :key="idx">
                            <div class="border border-slate-200 rounded-xl p-3.5 sm:p-4 bg-white shadow-xs space-y-3">
                                
                                {{-- Header Unit & Status Badge --}}
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <i data-lucide="truck" class="w-4 h-4 text-slate-700 flex-shrink-0"></i>
                                        <h4 class="font-bold text-slate-900 text-xs sm:text-sm truncate" x-text="event.unit_nama"></h4>
                                    </div>

                                    <span class="inline-flex items-center gap-1.5 text-[10.5px] font-extrabold px-2.5 py-1 rounded-full w-fit shadow-2xs"
                                          :class="{
                                              'bg-amber-50 text-amber-900 border border-amber-300': event.status_kalender === 'menunggu',
                                              'bg-blue-50 text-blue-900 border border-blue-300': event.status_kalender === 'disetujui_ke_bengkel',
                                              'bg-orange-50 text-orange-950 border border-orange-300': event.status_kalender === 'dalam_perbaikan',
                                              'bg-emerald-50 text-emerald-900 border border-emerald-300': event.status_kalender === 'selesai',
                                              'bg-red-50 text-red-900 border border-red-300': event.status_kalender === 'ditolak'
                                          }">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                              :class="{
                                                  'bg-amber-500': event.status_kalender === 'menunggu',
                                                  'bg-blue-600': event.status_kalender === 'disetujui_ke_bengkel',
                                                  'bg-orange-500': event.status_kalender === 'dalam_perbaikan',
                                                  'bg-emerald-500': event.status_kalender === 'selesai',
                                                  'bg-red-600': event.status_kalender === 'ditolak'
                                              }"></span>
                                        <span x-text="event.status_label"></span>
                                    </span>
                                </div>

                                {{-- Jadwal Keberangkatan Bengkel (Belum Berangkat / Masa Depan) --}}
                                <template x-if="event.tanggal_keberangkatan && event.status_kalender === 'disetujui_ke_bengkel'">
                                    <div class="bg-blue-50/70 border border-blue-200 rounded-lg p-2.5 flex items-center gap-2 text-xs font-semibold text-blue-900">
                                        <span class="text-base">🚛</span>
                                        <div>
                                            <span class="text-[10px] font-bold text-blue-700 block uppercase">Jadwal Ke Bengkel</span>
                                            <span class="font-bold text-blue-900" x-text="event.tanggal_keberangkatan"></span>
                                        </div>
                                    </div>
                                </template>

                                {{-- Sedang Dalam Perbaikan di Bengkel --}}
                                <template x-if="event.status_kalender === 'dalam_perbaikan'">
                                    <div class="bg-orange-50/80 border border-orange-200 rounded-lg p-2.5 flex items-center gap-2 text-xs font-semibold text-orange-950">
                                        <span class="text-base">⚙️</span>
                                        <div>
                                            <span class="text-[10px] font-bold text-orange-700 block uppercase">Proses Perbaikan</span>
                                            <span class="font-bold text-orange-950">Unit Sedang Dikerjakan di Bengkel</span>
                                            <span class="text-[10.5px] font-normal text-orange-800 block" x-show="event.tanggal_keberangkatan" x-text="'Masuk Bengkel: ' + event.tanggal_keberangkatan"></span>
                                        </div>
                                    </div>
                                </template>

                                {{-- Sudah Selesai Perbaikan & Kembali ke Pos --}}
                                <template x-if="event.status_kalender === 'selesai'">
                                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-2.5 flex items-center gap-2 text-xs font-semibold text-emerald-900">
                                        <span class="text-base">✅</span>
                                        <div>
                                            <span class="text-[10px] font-bold text-emerald-700 block uppercase">Status Pemeliharaan</span>
                                            <span class="font-bold text-emerald-900">Selesai &amp; Unit Kembali Siap Operasi</span>
                                            <span class="text-[10.5px] font-normal text-emerald-800 block" x-show="event.tanggal_selesai" x-text="'Tanggal Selesai: ' + event.tanggal_selesai"></span>
                                        </div>
                                    </div>
                                </template>

                                {{-- Item Perbaikan & Badges --}}
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Item Perbaikan</span>
                                    
                                    {{-- Jika Ada Rincian Verifikasi Per Item --}}
                                    <template x-if="event.item_verifikasis && event.item_verifikasis.length > 0">
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="(it, i) in event.item_verifikasis" :key="i">
                                                <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-md border"
                                                      :class="it.status === 'disetujui' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200'">
                                                    <span x-text="it.status === 'disetujui' ? '✓' : '✕'"></span>
                                                    <span x-text="it.nama"></span>
                                                    <span class="text-[10px] font-normal" x-text="'(' + (it.status === 'disetujui' ? 'Disetujui' : 'Ditolak') + ')'"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Jika Tidak Ada Rincian Per Item --}}
                                    <template x-if="!event.item_verifikasis || event.item_verifikasis.length === 0">
                                        <p class="text-xs font-bold text-slate-800" x-text="event.item_perbaikan || '-'"></p>
                                    </template>
                                </div>

                                {{-- Catatan Admin --}}
                                <template x-if="event.catatan_admin">
                                    <div class="bg-slate-50 border border-slate-200/80 rounded-lg p-2.5 text-xs text-slate-700">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase block mb-0.5">Catatan Admin</span>
                                        <p class="italic text-slate-800" x-text="event.catatan_admin"></p>
                                    </div>
                                </template>

                                {{-- Tombol Aksi Admin: Kelola Verifikasi & Cetak Dokumen --}}
                                <div class="pt-2.5 border-t border-slate-100 flex flex-wrap items-center justify-end gap-2">
                                    <a :href="'{{ route('admin.pemeliharaan.pengajuan') }}?search=' + encodeURIComponent(event.nomor_lambung || '')"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg text-xs transition-colors border border-slate-300 shadow-2xs">
                                        <i data-lucide="check-square" class="w-3.5 h-3.5 text-slate-700"></i>
                                        <span>Kelola Pengajuan</span>
                                    </a>
                                    <a :href="'/admin/pemeliharaan/cetak-dokumen/' + event.id + '/permohonanbidang'" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-lg text-xs transition-colors border border-blue-200 shadow-2xs">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        <span>Cetak Surat Permohonan</span>
                                    </a>
                                </div>

                            </div>
                        </template>
                    </div>

                    {{-- Footer --}}
                    <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/80 rounded-b-2xl text-center">
                        <p class="text-[10.5px] text-gray-400 font-medium">Tekan <kbd class="px-1 py-0.5 bg-white border border-gray-200 rounded text-gray-600 font-bold">Esc</kbd> atau klik luar untuk menutup</p>
                    </div>
                </div>
            </div>
        </template>

        {{-- Header Banner Kalender --}}
        <div class="flex items-start justify-between flex-wrap gap-4 mb-6 pb-5 border-b border-gray-100">    
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-600 animate-pulse"></span>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">Kalender Pemeliharaan Seluruh Pos &amp; Unit</h2>
                </div>
                <p class="text-gray-500 text-xs sm:text-sm">
                    Jadwal &amp; status verifikasi pengajuan unit operasional dari seluruh Pos Damkar secara real-time.
                    <span class="hidden sm:inline text-gray-400">— Klik tanggal bertanda untuk melihat detail.</span>
                </p>
            </div>

            {{-- Ringkasan Status Badges (Menunggu, Disetujui/Bengkel, Selesai, Ditolak) --}}
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 w-full sm:w-auto">
                <div class="flex items-center justify-between sm:justify-start gap-2 text-xs font-semibold bg-amber-50 border border-amber-200 text-amber-900 rounded-xl px-3 py-1.5 shadow-2xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Menunggu:</span>
                    </div>
                    <span class="bg-amber-200/80 text-amber-950 px-2 py-0.5 rounded-md text-[11px] font-bold">{{ $calendarRingkasan['menunggu'] }}</span>
                </div>
                <div class="flex items-center justify-between sm:justify-start gap-2 text-xs font-semibold bg-blue-50 border border-blue-200 text-blue-900 rounded-xl px-3 py-1.5 shadow-2xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span>Disetujui / Bengkel:</span>
                    </div>
                    <span class="bg-blue-200/80 text-blue-950 px-2 py-0.5 rounded-md text-[11px] font-bold">{{ $calendarRingkasan['disetujui'] }}</span>
                </div>
                <div class="flex items-center justify-between sm:justify-start gap-2 text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl px-3 py-1.5 shadow-2xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span>Selesai:</span>
                    </div>
                    <span class="bg-emerald-200/80 text-emerald-950 px-2 py-0.5 rounded-md text-[11px] font-bold">{{ $calendarRingkasan['selesai'] }}</span>
                </div>
                <div class="flex items-center justify-between sm:justify-start gap-2 text-xs font-semibold bg-red-50 border border-red-200 text-red-900 rounded-xl px-3 py-1.5 shadow-2xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-600"></span>
                        <span>Ditolak:</span>
                    </div>
                    <span class="bg-red-200/80 text-red-950 px-2 py-0.5 rounded-md text-[11px] font-bold">{{ $calendarRingkasan['ditolak'] }}</span>
                </div>
            </div>
        </div>

        {{-- Navigasi Bulan & Dropdown Pilih Bulan/Tahun --}}
        <div class="flex items-center justify-between gap-1.5 sm:gap-3 mb-6 bg-slate-50 border border-slate-200/80 rounded-2xl p-2 sm:p-3 shadow-2xs">

            {{-- Tombol Bulan Sebelumnya --}}
            <a href="{{ $calendarPrevMonthUrl }}"
               class="inline-flex items-center justify-center gap-1 px-2.5 sm:px-3.5 py-2 text-xs sm:text-sm font-bold rounded-xl text-slate-700 bg-white border border-slate-200 shadow-2xs hover:bg-slate-100 hover:text-slate-900 transition-all flex-shrink-0"
               title="Bulan Sebelumnya">
                <i data-lucide="chevron-left" class="w-4 h-4 text-slate-600"></i>
                <span class="hidden sm:inline">Sebelumnya</span>
            </a>

            {{-- Dropdown Form Pilih Bulan & Tahun --}}
            <form action="{{ route('admin.dashboard') }}" method="GET" class="flex items-center gap-1.5 justify-center flex-1 min-w-0">
                <input type="hidden" name="tahun" value="{{ $currentYear }}">
                <div class="flex items-center gap-1 sm:gap-1.5 bg-white border border-slate-200 rounded-xl px-2 sm:px-3 py-1.5 shadow-2xs max-w-full overflow-hidden">
                    <i data-lucide="calendar" class="w-4 h-4 text-red-600 flex-shrink-0"></i>

                    {{-- Select Bulan --}}
                    <select name="month" onchange="this.form.submit()"
                            class="bg-transparent text-xs sm:text-sm font-bold text-slate-800 focus:outline-none cursor-pointer py-0.5 font-sans truncate max-w-[90px] xs:max-w-[110px] sm:max-w-none">
                        @foreach([
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ] as $mNum => $mName)
                            <option value="{{ $mNum }}" @selected($calendarBulanAktif->month == $mNum)>{{ $mName }}</option>
                        @endforeach
                    </select>

                    {{-- Select Tahun Dinamis --}}
                    <select name="year" onchange="this.form.submit()"
                            class="bg-transparent text-xs sm:text-sm font-bold text-slate-800 focus:outline-none cursor-pointer py-0.5 border-l border-slate-200 pl-1 sm:pl-2 font-sans">
                        @foreach(($calendarAvailableYears ?? range(2020, (int)date('Y') + 10)) as $y)
                            <option value="{{ $y }}" @selected($calendarBulanAktif->year == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                @unless($calendarBulanAktif->isCurrentMonth())
                    <a href="{{ route('admin.dashboard') }}"
                       class="text-[10px] sm:text-[11px] px-2 sm:px-2.5 py-1.5 rounded-xl bg-red-100/90 text-red-800 hover:bg-red-200 transition-colors font-bold shadow-2xs inline-flex items-center gap-1 flex-shrink-0"
                       title="Kembali ke Bulan Saat Ini">
                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                        <span class="hidden md:inline">Hari Ini</span>
                    </a>
                @endunless
            </form>

            {{-- Tombol Bulan Berikutnya --}}
            <a href="{{ $calendarNextMonthUrl }}"
               class="inline-flex items-center justify-center gap-1 px-2.5 sm:px-3.5 py-2 text-xs sm:text-sm font-bold rounded-xl text-slate-700 bg-white border border-slate-200 shadow-2xs hover:bg-slate-100 hover:text-slate-900 transition-all flex-shrink-0"
               title="Bulan Berikutnya">
                <span class="hidden sm:inline">Berikutnya</span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-600"></i>
            </a>
        </div>

        {{-- Tabel Grid Kalender --}}
        <div class="rounded-xl border border-gray-200 w-full overflow-hidden shadow-xs">
            <table class="w-full border-collapse table-fixed">
                <thead>
                    <tr class="bg-slate-100/80 border-b border-gray-200">
                        @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $hari)
                            <th class="text-[11px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider py-2.5 text-center px-1">
                                {{ $hari }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($calendarWeeks as $week)
                        <tr>
                            @foreach($week as $hari)
                                @php
                                    $tanggalKey = $hari->toDateString();
                                    $eventsHariIni = $calendarEventsByDate->get($tanggalKey, collect());
                                    $isBulanIni = $hari->month === $calendarBulanAktif->month;
                                    $isHariIni = $hari->isToday();
                                    $adaData = $eventsHariIni->count() > 0;
                                    $adaDisetujui = $eventsHariIni->contains(fn($e) => in_array($e->status_kalender ?? $e->status, ['disetujui_ke_bengkel', 'dalam_perbaikan']));
                                @endphp
                                <td class="align-top border-r border-gray-100 last:border-r-0 p-1 sm:p-2 h-16 sm:h-20 w-[14.28%] relative transition-all duration-150
                                           {{ $isBulanIni ? 'bg-white' : 'bg-slate-50/70 opacity-60' }}
                                           {{ $adaData ? 'cursor-pointer hover:bg-red-50/70 hover:ring-2 hover:ring-inset hover:ring-red-400/50' : '' }}"
                                    @if($adaData)
                                        @click="openModal({{ Js::from($hari->translatedFormat('l, d F Y')) }}, {{ Js::from($eventsHariIni->values()) }})"
                                        title="Klik untuk melihat detail status verifikasi pengajuan ({{ $eventsHariIni->count() }} unit)"
                                    @endif
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs sm:text-sm font-semibold
                                            {{ $isBulanIni ? 'text-slate-800' : 'text-slate-400' }}
                                            {{ $isHariIni ? 'inline-flex items-center justify-center w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-gradient-to-r from-red-600 to-red-800 text-white shadow-sm text-[10px] sm:text-xs font-bold' : '' }}">
                                            {{ $hari->day }}
                                        </span>

                                        @if($adaDisetujui)
                                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping" title="Unit dijadwalkan / dalam perbaikan di bengkel"></span>
                                        @endif
                                    </div>

                                    {{-- Event Badges --}}
                                    <div class="space-y-1">
                                        @foreach($eventsHariIni->take(2) as $event)
                                            @php
                                                $statusK = $event->status_kalender ?? $event->status;
                                                $badgeStyle = match($statusK) {
                                                    'menunggu'              => 'bg-amber-100/90 text-amber-900 border-amber-300',
                                                    'disetujui_ke_bengkel'  => 'bg-blue-100/90 text-blue-900 border-blue-300',
                                                    'dalam_perbaikan'       => 'bg-orange-100/90 text-orange-950 border-orange-300',
                                                    'selesai'               => 'bg-emerald-100/90 text-emerald-900 border-emerald-300',
                                                    'ditolak'               => 'bg-red-100/90 text-red-900 border-red-300',
                                                    default                 => 'bg-slate-100 text-slate-800 border-slate-300',
                                                };
                                                $prefixLabel = match($statusK) {
                                                    'menunggu'              => '⏳ ',
                                                    'disetujui_ke_bengkel'  => '🗓️ ',
                                                    'dalam_perbaikan'       => '⚙️ ',
                                                    'selesai'               => '✓ ',
                                                    'ditolak'               => '❌ ',
                                                    default                 => '',
                                                };
                                            @endphp
                                            <div class="text-[9px] sm:text-[11px] leading-tight px-1.5 py-0.5 rounded-md border {{ $badgeStyle }} truncate font-bold shadow-2xs"
                                                 title="{{ $prefixLabel }}{{ $event->unit_nama }} ({{ $event->status_label ?? ucfirst($event->status) }})">
                                                 {{ $prefixLabel }}{{ $event->unit_nama }}
                                            </div>
                                        @endforeach

                                        @if($eventsHariIni->count() > 2)
                                            <div class="text-[9px] sm:text-[10px] text-red-700 font-extrabold px-0.5">
                                                +{{ $eventsHariIni->count() - 2 }} detail →
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    {{-- Main Grid: Charts (Left 2/3) + Activity & Distribution (Right 1/3) --}}
    <div class="dash-bottom-grid" style="display:grid; grid-template-columns:minmax(0, 1fr) 340px; gap:20px; align-items:start; width:100%; min-width:0;">

        {{-- Left Column: Charts Container --}}
        <div style="display:flex; flex-direction:column; gap:20px; min-width:0; width:100%;">

            {{-- Chart 1: Grafik Tren Inspeksi & Perbaikan --}}
            <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); border:1px solid #E2E8F0; overflow:hidden; width:100%; min-width:0;">
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
                <div style="padding:20px 22px; position:relative; height:270px; width:100%; min-width:0; overflow:hidden;">
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
            <div style="background:#fff; border-radius:16px; box-shadow:0px 14px 30px rgba(15, 23, 42, 0.04); border:1px solid #E2E8F0; overflow:hidden; width:100%; min-width:0;">
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
                <div style="padding:20px 22px; position:relative; height:220px; width:100%; min-width:0; overflow:hidden;">
                    <canvas id="chartBiaya" style="width:100%; height:100%;"></canvas>
                </div>
            </div>

        </div>

        {{-- Right Column: Pos Distribution + Recent Activity --}}
        <div style="display:flex; flex-direction:column; gap:20px; min-width:0; width:100%;">

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
                            if (is_array($posItem)) {
                                $posName  = $posItem['pos'] ?? '—';
                                $posTotal = (int) ($posItem['total'] ?? 0);
                            } elseif (is_object($posItem)) {
                                $posName  = $posItem->pos ?? '—';
                                $posTotal = (int) ($posItem->total ?? 0);
                            } else {
                                $posName  = (string) $posItem;
                                $posTotal = 0;
                            }
                            $pct = round(($posTotal / max($totalUnit, 1)) * 100);
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
                            $actIcon  = is_array($act) ? ($act['icon'] ?? 'activity') : ($act->icon ?? 'activity');
                            $actBg    = is_array($act) ? ($act['bg'] ?? 'rgba(27,42,107,.10)') : ($act->bg ?? 'rgba(27,42,107,.10)');
                            $actColor = is_array($act) ? ($act['color'] ?? '#1B2A6B') : ($act->color ?? '#1B2A6B');
                            $actText  = is_array($act) ? ($act['text'] ?? 'Aktivitas') : ($act->text ?? 'Aktivitas');
                            $actTime  = is_array($act) ? ($act['created_at'] ?? 'Baru saja') : ($act->created_at ?? 'Baru saja');
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

function calendarModal() {
    return {
        modalOpen: false,
        selectedDate: '',
        selectedEvents: [],

        openModal(tanggal, events) {
            this.selectedDate = tanggal;
            this.selectedEvents = events;
            this.modalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });
        },

        closeModal() {
            this.modalOpen = false;
        }
    };
}
</script>
@endpush

@push('styles')
<style>
@media (max-width: 1140px) {
    .dash-bottom-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush

@endsection
