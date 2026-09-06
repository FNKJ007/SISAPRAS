@php
    $isAktual = request()->routeIs('admin.pemeliharaan.spj-pembayaran.*') || request()->routeIs('admin.pemeliharaan.monitoring-aktual.*');
    $pageTitle = $isAktual ? 'SPJ Pembayaran' : 'Aktual Pembayaran';
    $subTitle  = $isAktual ? 'Monitoring data SPJ pembayaran pemeliharaan unit.' : 'Monitoring data aktual pembayaran pemeliharaan unit.';
    $routePrefix = $isAktual ? 'admin.pemeliharaan.spj-pembayaran' : 'admin.pemeliharaan.aktual-pembayaran';
@endphp

@extends('layouts.admin')

@section('title', $pageTitle . ' — Admin')

@section('content')
<style>
.invoice-index-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}
.invoice-filter-form {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    width: 100%;
    justify-content: space-between;
}
.invoice-filter-inputs {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.invoice-search-wrap {
    position: relative;
    width: 270px;
}
.invoice-search-input {
    padding: 8px 14px 8px 34px;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    font-size: 13px;
    outline: none;
    width: 100%;
    background: #FFFFFF;
    color: #0F172A;
    box-sizing: border-box;
    transition: border-color 0.2s;
}

@media (max-width: 768px) {
    .invoice-index-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
    }
    .invoice-index-header a {
        width: 100% !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }
    .invoice-filter-inputs {
        flex-direction: column !important;
        align-items: stretch !important;
        width: 100% !important;
        gap: 8px !important;
    }
    .invoice-search-wrap {
        width: 100% !important;
    }
    .invoice-filter-inputs select,
    .invoice-filter-inputs button,
    .invoice-filter-inputs a {
        width: 100% !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }
}

[x-cloak] { display: none !important; }

@media (max-width: 900px) {
    .dashboard-chart-grid {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 640px) {
    .dashboard-kpi-grid {
        grid-template-columns: 1fr !important;
    }
    .dashboard-filter-row {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .dashboard-filter-row > div {
        width: 100% !important;
    }
    .dashboard-filter-row button[type="button"] {
        min-width: 100% !important;
        width: 100% !important;
    }
}
</style>

<div class="form-card" style="max-width:100%; box-shadow:none; padding:0; background:transparent;">

    {{-- Page Header --}}
    <div class="invoice-index-header">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#121E4E; margin:0 0 2px 0;">{{ $pageTitle }}</h1>
            <p style="font-size:13px; color:#64748B; margin:0;">{{ $subTitle }}</p>
        </div>
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <a href="{{ route($routePrefix . '.create') }}"
               style="display:inline-flex; align-items:center; gap:8px; padding:9px 18px; background:#1B2A6B; color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(27,42,107,0.25); transition:all 0.2s ease;">
                <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
                <span>Buat Invoice</span>
            </a>
        </div>
    </div>

    {{-- Alert Flash Success --}}
    @if(session('success'))
        <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:12px 18px; border-radius:12px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:10px; margin-bottom:20px; box-shadow:0 2px 8px rgba(16,185,129,0.1);">
            <i data-lucide="check-circle-2" style="width:18px; height:18px; color:#10B981; flex-shrink:0;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ===================== DASHBOARD MONITORING ===================== --}}
    <div class="dashboard-monitoring-card" style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden; margin-bottom:20px;" x-data="{ unitOpen:false, bulanOpen:false }">

        <div style="padding:18px 22px; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div>
    {{-- Dashboard Ringkasan & Filter --}}
    <div style="background:#FFFFFF; border-radius:14px; border:1px solid #E2E8F0; padding:18px 20px; margin-bottom:22px; box-shadow:0 1px 4px rgba(0,0,0,0.03);">
        {{-- Filter Bar --}}
        <form method="GET" action="{{ route($routePrefix . '.index') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px; padding-bottom:16px; border-bottom:1px solid #F1F5F9;">
            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:10px;">
                {{-- Search --}}
                <div style="position:relative; min-width:240px;">
                    <i data-lucide="search" style="position:absolute; left:11px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:#94A3B8;"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari invoice, unit, bengkel..."
                           style="padding:8px 12px 8px 34px; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; width:100%; outline:none; box-sizing:border-box;">
                </div>

                {{-- Status Filter --}}
                <select name="status" onchange="this.form.submit()"
                        style="padding:8px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; background:#FFFFFF; color:#334155; font-weight:600; outline:none; cursor:pointer;">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="diajukan" {{ request('status') === 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="lunas" {{ request('status') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                </select>

                {{-- Filter Tahun Anggaran --}}
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12.5px; color:#64748B; font-weight:600;">Tahun:</span>
                    <select name="tahun" onchange="this.form.submit()"
                            style="padding:8px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; background:#FFFFFF; color:#1B2A6B; font-weight:700; outline:none; cursor:pointer;">
                        @foreach($availableTahun as $t)
                            <option value="{{ $t }}" {{ $selectedTahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Multi Unit (Dropdown) --}}
                <div style="position:relative;" x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                            style="display:inline-flex; align-items:center; gap:6px; padding:8px 12px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; color:#334155; cursor:pointer;">
                        <i data-lucide="truck" style="width:14px; height:14px; color:#64748B;"></i>
                        <span>
                            @if(empty($selectedUnitIds))
                                Semua Unit
                            @else
                                {{ count($selectedUnitIds) }} Unit Terpilih
                            @endif
                        </span>
                        <i data-lucide="chevron-down" style="width:13px; height:13px; color:#64748B;"></i>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak
                         style="position:absolute; top:calc(100% + 4px); left:0; background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.12); padding:8px; width:260px; max-height:280px; overflow-y:auto; z-index:100;">
                        <div style="font-size:11px; font-weight:700; color:#94A3B8; text-transform:uppercase; padding:4px 8px; margin-bottom:4px;">Pilih Unit</div>
                        @foreach($units as $u)
                            <label style="display:flex; align-items:center; gap:8px; padding:6px 8px; font-size:12.5px; color:#334155; cursor:pointer; border-radius:6px;"
                                   onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='transparent';">
                                <input type="checkbox" name="unit_id[]" value="{{ $u->id }}" onchange="this.form.submit()"
                                       {{ in_array($u->id, $selectedUnitIds) ? 'checked' : '' }}>
                                <span style="font-weight:700; color:#1B2A6B;">{{ $u->nomor_lambung }}</span>
                                <span style="color:#64748B; font-size:11.5px;">({{ $u->plat_nomor }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Multi Bulan (Dropdown) --}}
                <div style="position:relative;" x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                            style="display:inline-flex; align-items:center; gap:6px; padding:8px 12px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; color:#334155; cursor:pointer;">
                        <i data-lucide="calendar" style="width:14px; height:14px; color:#64748B;"></i>
                        <span>
                            @if(empty($selectedBulan))
                                Semua Bulan
                            @else
                                {{ count($selectedBulan) }} Bulan
                            @endif
                        </span>
                        <i data-lucide="chevron-down" style="width:13px; height:13px; color:#64748B;"></i>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak
                         style="position:absolute; top:calc(100% + 4px); left:0; background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.12); padding:8px; width:220px; max-height:280px; overflow-y:auto; z-index:100;">
                        <div style="font-size:11px; font-weight:700; color:#94A3B8; text-transform:uppercase; padding:4px 8px; margin-bottom:4px;">Pilih Bulan</div>
                        @foreach($bulanList as $key => $label)
                            <label style="display:flex; align-items:center; gap:8px; padding:6px 8px; font-size:12.5px; color:#334155; cursor:pointer; border-radius:6px;"
                                   onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='transparent';">
                                <input type="checkbox" name="bulan[]" value="{{ $key }}" onchange="this.form.submit()"
                                       {{ in_array($key, $selectedBulan) ? 'checked' : '' }}>
                                {{ $key }} ({{ $label }})
                            </label>
                        @endforeach
                    </div>
                </div>

                @if(!empty($selectedUnitIds) || !empty($selectedBulan) || request('tahun'))
                    <a href="{{ route($routePrefix . '.index') }}"
                       style="display:inline-flex; align-items:center; gap:4px; padding:8px 14px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; text-decoration:none; font-weight:600;">
                        <i data-lucide="rotate-ccw" style="width:13px; height:13px;"></i>
                        <span>Reset Filter</span>
                    </a>
                @endif
            </div>
        </form>

        {{-- KPI Cards --}}
        <div style="padding:20px 22px 6px 22px; display:grid; grid-template-columns:repeat(2, 1fr); gap:16px;" class="dashboard-kpi-grid">
            <div style="background:linear-gradient(135deg, #1B2A6B 0%, #26398C 100%); border-radius:14px; padding:18px 20px; display:flex; align-items:center; justify-content:space-between; gap:10px; box-shadow:0 8px 20px rgba(27,42,107,0.2);">
                <div>
                    <div style="font-size:11px; font-weight:700; color:#C7D2FE; text-transform:uppercase; letter-spacing:0.5px;">Total Anggaran Terpakai</div>
                    <div style="font-size:22px; font-weight:800; color:#FFFFFF; margin-top:6px;">Rp {{ number_format($dashboardTotalAnggaran, 0, ',', '.') }}</div>
                </div>
                <div style="width:42px; height:42px; border-radius:12px; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i data-lucide="wallet" style="width:20px; height:20px; color:#FFFFFF;"></i>
                </div>
            </div>
            <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:14px; padding:18px 20px; display:flex; align-items:center; justify-content:space-between; gap:10px; box-shadow:0 4px 14px rgba(0,0,0,0.03);">
                <div>
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Total Unit</div>
                    <div style="font-size:22px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $dashboardTotalUnit }}</div>
                </div>
                <div style="width:42px; height:42px; border-radius:12px; background:#EFF6FF; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i data-lucide="truck" style="width:20px; height:20px; color:#1D4ED8;"></i>
                </div>
            </div>
        </div>

        {{-- Grafik + Tabel Rincian --}}
        <div style="padding:20px 22px 22px 22px; display:grid; grid-template-columns:1fr 1fr; gap:18px;" class="dashboard-chart-grid">

            {{-- Biaya per Unit --}}
            <div style="border:1px solid #E2E8F0; border-radius:14px; overflow:hidden;">
                <div style="padding:14px 16px; border-bottom:1px solid #F1F5F9; font-size:13px; font-weight:700; color:#0F172A;">Biaya Pemeliharaan per Unit</div>
                <div style="padding:14px 16px; height:220px;">
                    <canvas id="chartBiayaPerUnit"></canvas>
                </div>
                <div style="max-height:180px; overflow-y:auto; border-top:1px solid #F1F5F9;">
                    <table style="width:100%; border-collapse:collapse; font-size:12.5px;">
                        <tbody>
                            @forelse($biayaPerUnit as $row)
                                <tr style="border-bottom:1px solid #F8FAFC;">
                                    <td style="padding:8px 16px; font-weight:700; color:#1B2A6B;">{{ $row['label'] }} Total</td>
                                    <td style="padding:8px 16px; text-align:right; font-weight:700; color:#0F172A;">{{ number_format($row['total'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" style="padding:14px 16px; text-align:center; color:#94A3B8;">Tidak ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Biaya per Bulan --}}
            <div style="border:1px solid #E2E8F0; border-radius:14px; overflow:hidden;">
                <div style="padding:14px 16px; border-bottom:1px solid #F1F5F9; font-size:13px; font-weight:700; color:#0F172A;">Biaya Pemeliharaan per Bulan</div>
                <div style="padding:14px 16px; height:220px;">
                    <canvas id="chartBiayaPerBulan"></canvas>
                </div>
                <div style="max-height:180px; overflow-y:auto; border-top:1px solid #F1F5F9;">
                    <table style="width:100%; border-collapse:collapse; font-size:12.5px;">
                        <tbody>
                            @forelse($biayaPerBulan as $row)
                                <tr style="border-bottom:1px solid #F8FAFC;">
                                    <td style="padding:8px 16px; font-weight:700; color:#1B2A6B;">{{ $row['label'] }} Total</td>
                                    <td style="padding:8px 16px; text-align:right; font-weight:700; color:#0F172A;">{{ number_format($row['total'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" style="padding:14px 16px; text-align:center; color:#94A3B8;">Tidak ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Data Card --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">

        {{-- Filter & Search Toolbar --}}
        <div style="padding:16px 20px; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; background:#FAFAFA;">
            <form method="GET" action="{{ route($routePrefix . '.index') }}" class="invoice-filter-form">
                @if(!empty($selectedUnitIds))
                    @foreach($selectedUnitIds as $uid)
                        <input type="hidden" name="unit[]" value="{{ $uid }}">
                    @endforeach
                @endif
                @if(!empty($selectedBulan))
                    @foreach($selectedBulan as $bl)
                        <input type="hidden" name="bulan[]" value="{{ $bl }}">
                    @endforeach
                @endif
                @if(request('tahun'))<input type="hidden" name="tahun" value="{{ request('tahun') }}">@endif
                <div class="invoice-filter-inputs">
                    {{-- Search Input --}}
                    <div class="invoice-search-wrap">
                        <i data-lucide="search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:#94A3B8;"></i>
                        <input type="text"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="Cari no. invoice / no. pol / lambung..."
                               class="invoice-search-input"
                               onfocus="this.style.borderColor='#1B2A6B';"
                               onblur="this.style.borderColor='#CBD5E1';">
                    </div>

                    <button type="submit"
                            style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer; box-shadow:0 2px 6px rgba(27,42,107,0.18); transition:all 0.2s;">
                        <i data-lucide="search" style="width:14px; height:14px;"></i>
                        <span>Cari</span>
                    </button>

                    @if(request('q'))
                        <a href="{{ route($routePrefix . '.index') }}"
                           style="display:inline-flex; align-items:center; gap:4px; padding:8px 14px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; text-decoration:none; font-weight:600; transition:all 0.2s;">
                            <i data-lucide="rotate-ccw" style="width:13px; height:13px;"></i>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>

                <div>
                    <span style="font-size:12px; color:#64748B; font-weight:600;">
                        Total: <strong style="color:#0F172A;">{{ $invoices->total() }}</strong> Invoice
                    </span>
                </div>
            </form>
        </div>

        {{-- Table Data --}}
        <div style="overflow-x:auto;">
            <table style="width:100%; min-width:850px; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="background:#1B2A6B; color:#FFFFFF;">
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B;">
                            No. Invoice
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; width:120px;">
                            Tanggal
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B;">
                            Unit Kendaraan
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; width:130px;">
                            No. Polisi
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; text-align:right; width:170px;">
                            Total Biaya
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; text-align:center; width:140px;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s ease;"
                            onmouseover="this.style.background='#F8FAFC';"
                            onmouseout="this.style.background='transparent';">
                            <td style="padding:14px 18px; font-size:13px; font-weight:700; color:#1B2A6B;">
                                <a href="{{ route($routePrefix . '.show', $invoice) }}"
                                   style="color:#1B2A6B; text-decoration:none; font-weight:700;"
                                   onmouseover="this.style.textDecoration='underline';"
                                   onmouseout="this.style.textDecoration='none';">
                                    {{ $invoice->nomor_invoice }}
                                </a>
                            </td>
                            <td style="padding:14px 18px; font-size:12.5px; color:#475569; white-space:nowrap;">
                                {{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->format('d/m/Y') : '—' }}
                            </td>
                            <td style="padding:14px 18px;">
                                <div style="font-size:13px; font-weight:700; color:#0F172A;">{{ $invoice->no_lambung ?? '—' }}</div>
                                @php
                                    $namaUnitSub = optional($invoice->unit)->merk_tipe ?: $invoice->jenis_mobil;
                                @endphp
                                @if($namaUnitSub)
                                    <div style="font-size:11.5px; color:#64748B; margin-top:2px;">{{ $namaUnitSub }}</div>
                                @endif
                            </td>
                            <td style="padding:14px 18px; font-size:12.5px; font-weight:600; color:#1E293B;">
                                <span style="background:#F1F5F9; color:#334155; padding:3px 8px; border-radius:6px; font-size:12px; font-weight:700; border:1px solid #E2E8F0;">
                                    {{ $invoice->no_pol ?? '—' }}
                                </span>
                            </td>
                            <td style="padding:14px 18px; font-size:13px; font-weight:800; color:#0F172A; text-align:right; white-space:nowrap;">
                                Rp {{ number_format($invoice->total_biaya, 0, ',', '.') }}
                            </td>
                            <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                <div style="display:inline-flex; align-items:center; gap:6px; justify-content:center;">
                                    <a href="{{ route($routePrefix . '.show', ['invoice' => $invoice, 'download' => 1]) }}"
                                       target="_blank"
                                       title="Unduh Invoice (PDF)"
                                       style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; background:#FFFFFF; color:#1B2A6B; border:1px solid #1B2A6B; border-radius:8px; text-decoration:none; transition:all 0.2s;"
                                       onmouseover="this.style.background='#1B2A6B'; this.style.color='#FFFFFF';"
                                       onmouseout="this.style.background='#FFFFFF'; this.style.color='#1B2A6B';">
                                        <i data-lucide="download" style="width:15px; height:15px;"></i>
                                    </a>

                                    <a href="{{ route($routePrefix . '.edit', $invoice) }}"
                                       title="Edit Invoice"
                                       style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; background:#FFFFFF; color:#1B2A6B; border:1px solid #1B2A6B; border-radius:8px; text-decoration:none; transition:all 0.2s;"
                                       onmouseover="this.style.background='#1B2A6B'; this.style.color='#FFFFFF';"
                                       onmouseout="this.style.background='#FFFFFF'; this.style.color='#1B2A6B';">
                                        <i data-lucide="pencil" style="width:15px; height:15px;"></i>
                                    </a>

                                    <form action="{{ route($routePrefix . '.destroy', $invoice) }}"
                                          method="POST"
                                          style="display:inline-block; margin:0;"
                                          onsubmit="return confirm('Hapus invoice {{ $invoice->nomor_invoice }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Hapus Invoice"
                                                style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; background:#FFFFFF; color:#C0201F; border:1px solid #FCA5A5; border-radius:8px; cursor:pointer; transition:all 0.2s;"
                                                onmouseover="this.style.background='#C0201F'; this.style.color='#FFFFFF'; this.style.borderColor='#C0201F';"
                                                onmouseout="this.style.background='#FFFFFF'; this.style.color='#C0201F'; this.style.borderColor='#FCA5A5';">
                                            <i data-lucide="trash-2" style="width:15px; height:15px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:56px 20px; text-align:center; background:#FFFFFF;">
                                <div style="display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                    <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
                                        <i data-lucide="file-x" style="width:30px; height:30px; color:#64748B;"></i>
                                    </div>
                                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Invoice</div>
                                    <div style="font-size:13px; color:#64748B; max-width:400px; margin:0 auto;">Belum ada catatan invoice yang tersimpan atau sesuai dengan kriteria filter.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($invoices->hasPages())
            <div style="padding:16px 20px; border-top:1px solid #F1F5F9; background:#FAFCFE;">
                {{ $invoices->links() }}
            </div>
        @endif

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const perUnitCanvas = document.getElementById('chartBiayaPerUnit');
    if (perUnitCanvas) {
        new Chart(perUnitCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($biayaPerUnit->pluck('label')) !!},
                datasets: [{
                    label: 'Total Biaya',
                    data: {!! json_encode($biayaPerUnit->pluck('total')) !!},
                    backgroundColor: '#1B2A6B',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 800, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: (ctx) => 'Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#64748B' } },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9', drawBorder: false },
                        ticks: {
                            font: { size: 10 },
                            color: '#64748B',
                            callback: (val) => 'Rp' + (val / 1000000).toFixed(1) + 'jt'
                        }
                    }
                }
            }
        });
    }

    const perBulanCanvas = document.getElementById('chartBiayaPerBulan');
    if (perBulanCanvas) {
        new Chart(perBulanCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($biayaPerBulan->pluck('label')) !!},
                datasets: [{
                    label: 'Total Biaya',
                    data: {!! json_encode($biayaPerBulan->pluck('total')) !!},
                    backgroundColor: '#0891B2',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 800, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: (ctx) => 'Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#64748B' } },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9', drawBorder: false },
                        ticks: {
                            font: { size: 10 },
                            color: '#64748B',
                            callback: (val) => 'Rp' + (val / 1000000).toFixed(1) + 'jt'
                        }
                    }
                }
            }
        });
    }

    if (window.lucide) { window.lucide.createIcons(); }
});
</script>
@endsection
