@extends('layouts.admin')

@section('title', 'Monitoring Kejadian — Admin')

@section('content')
<div x-data="monitoringKejadianAdmin()">

    {{-- Flash Message --}}
    @if(session('success'))
        <div style="background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; padding:12px 16px; border-radius:12px; margin-bottom:20px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px;">
            <i data-lucide="check-circle-2" style="width:18px; height:18px; color:#059669;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Header Halaman --}}
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; margin-bottom:22px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Monitoring Kejadian</h1>
            <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                APAR &amp; Kejadian / Rekap dan pencatatan kejadian penanganan kebakaran &amp; rescue.
            </p>
        </div>
        <button type="button" @click="createModalOpen = true"
                style="padding:10px 18px; background:var(--damkar-red); color:#FFFFFF; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(192,32,31,0.25);">
            <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
            <span>Tambah Kejadian</span>
        </button>
    </div>

    {{-- Ringkasan Kartu Statistik --}}
    <div class="kpi-grid-container">
        <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #E2E8F0; box-shadow:0 4px 14px rgba(0,0,0,0.03); display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <div>
                <div class="kpi-title" style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Total Kejadian</div>
                <div class="kpi-number" style="font-size:26px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $totalKejadian ?? 0 }}</div>
            </div>
            <div style="width:42px; height:42px; border-radius:12px; background:#FEF2F2; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i data-lucide="megaphone" style="width:20px; height:20px; color:var(--damkar-red);"></i>
            </div>
        </div>

        <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #FEE2E2; background:linear-gradient(180deg, #FFFFFF 0%, #FEF2F2 100%); box-shadow:0 4px 14px rgba(239,68,68,0.06); display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <div>
                <div class="kpi-title" style="font-size:11px; font-weight:700; color:#991B1B; text-transform:uppercase; letter-spacing:0.5px;">Kejadian Kebakaran</div>
                <div class="kpi-number" style="font-size:26px; font-weight:800; color:#B91C1C; margin-top:6px;">{{ $totalKebakaran ?? 0 }}</div>
            </div>
            <div style="width:42px; height:42px; border-radius:12px; background:#FEE2E2; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i data-lucide="flame" style="width:20px; height:20px; color:#B91C1C;"></i>
            </div>
        </div>

        <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #E2E8F0; box-shadow:0 4px 14px rgba(0,0,0,0.03); display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <div>
                <div class="kpi-title" style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Giat Rescue</div>
                <div class="kpi-number" style="font-size:26px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $totalRescue ?? 0 }}</div>
            </div>
            <div style="width:42px; height:42px; border-radius:12px; background:#EFF6FF; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i data-lucide="life-buoy" style="width:20px; height:20px; color:#1D4ED8;"></i>
            </div>
        </div>

        <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #E2E8F0; box-shadow:0 4px 14px rgba(0,0,0,0.03); display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <div style="min-width:0;">
                <div class="kpi-title" style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Est. Total Kerugian</div>
                <div class="kpi-number" style="font-size:18px; font-weight:800; color:#0F172A; margin-top:8px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Rp {{ number_format($totalKerugian ?? 0, 0, ',', '.') }}</div>
            </div>
            <div style="width:42px; height:42px; border-radius:12px; background:#ECFDF5; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i data-lucide="wallet" style="width:20px; height:20px; color:#059669;"></i>
            </div>
        </div>
    </div>

    @php
        $jenisWarna = [
            'Kebakaran'      => '#C0201F',
            'Rescue'         => '#1D4ED8',
            'Penyelamatan'   => '#0891B2',
            'Non-Kebakaran'  => '#64748B',
        ];
    @endphp

    {{-- Grafik: Tren Bulanan & Distribusi --}}
    <div class="kejadian-chart-grid" style="display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start; margin-bottom:20px;">

        {{-- Grafik Tren Kejadian per Bulan --}}
        <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
            <div style="padding:18px 22px; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <span style="font-size:15px; font-weight:700; color:#0F172A; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="bar-chart-3" style="width:18px; height:18px; color:var(--damkar-red);"></i>
                        Tren Kejadian per Bulan
                    </span>
                    <span style="font-size:12px; color:#64748B; margin-top:2px; display:block;">Jumlah kejadian yang tercatat setiap bulan, berdasarkan jenisnya.</span>
                </div>
                <form method="GET" action="{{ route('admin.apar.monitoring-kejadian') }}">
                    @if(request('jenis'))<input type="hidden" name="jenis" value="{{ request('jenis') }}">@endif
                    @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                    <select name="tahun_grafik" onchange="this.form.submit()"
                            style="padding:6px 12px; font-size:12px; font-weight:600; border-radius:8px; border:1px solid #CBD5E1; background:#F8FAFC; color:#334155; outline:none; cursor:pointer;">
                        @foreach($tahunTersedia as $th)
                            <option value="{{ $th }}" {{ (int) $tahunGrafik === (int) $th ? 'selected' : '' }}>Tahun {{ $th }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div style="padding:20px 22px; position:relative; min-height:260px;">
                <canvas id="chartTrenKejadian" style="max-height:260px; width:100%;"></canvas>
            </div>

            <div style="display:flex; gap:18px; padding:14px 22px; border-top:1px solid #F1F5F9; flex-wrap:wrap; background:#FAFCFE; justify-content:center;">
                <div style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; color:#334155;">
                    <span style="width:12px; height:12px; border-radius:3px; background:#C0201F; display:inline-block;"></span> Kebakaran
                </div>
                <div style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; color:#334155;">
                    <span style="width:12px; height:12px; border-radius:3px; background:#1D4ED8; display:inline-block;"></span> Rescue
                </div>
                <div style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; color:#334155;">
                    <span style="width:12px; height:12px; border-radius:3px; background:#0891B2; display:inline-block;"></span> Penyelamatan
                </div>
                <div style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:600; color:#334155;">
                    <span style="width:12px; height:12px; border-radius:3px; background:#64748B; display:inline-block;"></span> Non-Kebakaran
                </div>
            </div>
        </div>

        {{-- Grafik Distribusi Jenis Kejadian --}}
        <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
            <div style="padding:18px 22px; border-bottom:1px solid #F1F5F9;">
                <span style="font-size:15px; font-weight:700; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="pie-chart" style="width:18px; height:18px; color:var(--damkar-blue);"></i>
                    Distribusi Jenis
                </span>
                <span style="font-size:12px; color:#64748B; margin-top:2px; display:block;">Dari seluruh data kejadian yang sudah diinput.</span>
            </div>

            @if(($totalKejadian ?? 0) === 0)
                <div style="padding:40px 20px; text-align:center;">
                    <div style="width:56px; height:56px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px; border:1px solid #E2E8F0;">
                        <i data-lucide="pie-chart" style="width:26px; height:26px; color:#94A3B8;"></i>
                    </div>
                    <div style="font-size:13px; font-weight:700; color:#334155; margin-bottom:4px;">Belum Ada Data</div>
                    <div style="font-size:12px; color:#94A3B8;">Grafik akan muncul setelah ada kejadian yang dicatat.</div>
                </div>
            @else
                <div style="padding:20px 22px; position:relative; min-height:200px;">
                    <canvas id="chartDistribusiJenis" style="max-height:200px; width:100%;"></canvas>
                </div>
                <div style="padding:0 22px 18px;">
                    @foreach($jenisWarna as $jenis => $warna)
                        @php $jumlah = $chartDistribusiJenis[$jenis] ?? 0; @endphp
                        @if($jumlah > 0)
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:6px 0; border-bottom:1px solid #F8FAFC;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="width:10px; height:10px; border-radius:50%; background:{{ $warna }}; display:inline-block;"></span>
                                    <span style="font-size:12.5px; color:#334155; font-weight:600;">{{ $jenis }}</span>
                                </div>
                                <span style="font-size:12.5px; font-weight:800; color:#0F172A;">{{ $jumlah }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- Filter & Search --}}
    <div class="admin-filter-bar">
        <form method="GET" action="{{ route('admin.apar.monitoring-kejadian') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="font-size:12px; font-weight:600; color:#64748B;">Jenis:</span>
                <select name="jenis" onchange="this.form.submit()" style="padding:7px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                    <option value="">Semua Jenis</option>
                    <option value="Kebakaran" {{ request('jenis') == 'Kebakaran' ? 'selected' : '' }}>Kebakaran</option>
                    <option value="Rescue" {{ request('jenis') == 'Rescue' ? 'selected' : '' }}>Rescue</option>
                    <option value="Penyelamatan" {{ request('jenis') == 'Penyelamatan' ? 'selected' : '' }}>Penyelamatan</option>
                    <option value="Non-Kebakaran" {{ request('jenis') == 'Non-Kebakaran' ? 'selected' : '' }}>Non-Kebakaran</option>
                </select>
            </div>

            <div style="display:flex; align-items:center; gap:8px;">
                <div style="position:relative;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode, lokasi, kategori, pelapor..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:260px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 16px; background:var(--damkar-blue); color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer;">
                    Cari
                </button>
                @if(request('jenis') || request('search'))
                    <a href="{{ route('admin.apar.monitoring-kejadian') }}" style="padding:7px 12px; background:#F1F5F9; color:#64748B; border:1px solid #CBD5E1; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Data Kejadian --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">

        <div style="padding:16px 20px; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; gap:8px;">
            <i data-lucide="list-checks" style="width:16px; height:16px; color:var(--damkar-blue);"></i>
            <h3 style="font-size:14.5px; font-weight:800; color:#0F172A; margin:0;">Daftar Kejadian Damkar</h3>
        </div>

        @if($dataKejadian->isEmpty())
            <div style="padding:56px 20px; text-align:center;">
                <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px; border:1px solid #E2E8F0;">
                    <i data-lucide="folder-open" style="width:30px; height:30px; color:#64748B;"></i>
                </div>
                @if(request('jenis') || request('search'))
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Data Tidak Ditemukan</div>
                    <div style="font-size:13px; color:#64748B; margin-bottom:18px;">Tidak ada kejadian yang cocok dengan filter/pencarian Anda.</div>
                    <a href="{{ route('admin.apar.monitoring-kejadian') }}" style="display:inline-flex; align-items:center; gap:8px; padding:9px 20px; background:var(--damkar-blue); color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none;">
                        <i data-lucide="rotate-ccw" style="width:14px; height:14px;"></i>
                        <span>Reset Pencarian</span>
                    </a>
                @else
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Kejadian</div>
                    <div style="font-size:12.5px; color:#94A3B8;">Klik tombol "Tambah Kejadian" untuk mencatat kejadian baru.</div>
                @endif
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">
                            <th style="padding:14px 18px;">No</th>
                            <th style="padding:14px 18px;">Kode / Waktu</th>
                            <th style="padding:14px 18px;">Jenis</th>
                            <th style="padding:14px 18px;">Kategori Kejadian</th>
                            <th style="padding:14px 18px;">Lokasi</th>
                            <th style="padding:14px 18px;">Pos / Regu</th>
                            <th style="padding:14px 18px;">Status</th>
                            <th style="padding:14px 18px; text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dataKejadian as $index => $item)
                            <tr style="border-bottom:1px solid #F1F5F9;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:14px 18px; font-weight:600; color:#94A3B8;">{{ $dataKejadian->firstItem() + $index }}</td>
                                <td style="padding:14px 18px; white-space:nowrap;">
                                    <div style="font-weight:700; color:#1E293B;">{{ $item->kode_kejadian }}</div>
                                    <div style="font-size:11px; color:#94A3B8;">{{ optional($item->waktu_kejadian)->format('d/m/Y H:i') ?? '-' }} WIB</div>
                                </td>
                                <td style="padding:14px 18px;">
                                    @php
                                        $jenisStyle = match($item->jenis_kejadian) {
                                            'Kebakaran' => ['bg' => '#FEE2E2', 'text' => '#991B1B', 'border' => '#FCA5A5'],
                                            'Rescue', 'Penyelamatan' => ['bg' => '#EFF6FF', 'text' => '#1D4ED8', 'border' => '#BFDBFE'],
                                            default => ['bg' => '#F1F5F9', 'text' => '#475569', 'border' => '#CBD5E1'],
                                        };
                                    @endphp
                                    <span style="background:{{ $jenisStyle['bg'] }}; color:{{ $jenisStyle['text'] }}; border:1px solid {{ $jenisStyle['border'] }}; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap;">
                                        {{ $item->jenis_kejadian }}
                                    </span>
                                </td>
                                <td style="padding:14px 18px; max-width:220px;">
                                    <div style="font-weight:600; color:#1E293B;">{{ $item->kategori_detail ?? '-' }}</div>
                                    @if($item->penyebab)
                                        <div style="font-size:11px; color:#94A3B8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;">{{ Str::limit($item->penyebab, 40) }}</div>
                                    @endif
                                </td>
                                <td style="padding:14px 18px; max-width:200px;">
                                    <div style="color:#334155; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;" title="{{ $item->lokasi }}">{{ Str::limit($item->lokasi, 35) }}</div>
                                    @if($item->kecamatan)
                                        <div style="font-size:11px; color:#94A3B8;">Kec. {{ $item->kecamatan }}</div>
                                    @endif
                                </td>
                                <td style="padding:14px 18px; color:#334155;">
                                    {{ $item->pos_regu ?? '-' }}
                                    @if($item->komandan_regu)
                                        <div style="font-size:11px; color:#94A3B8;">Danru: {{ $item->komandan_regu }}</div>
                                    @endif
                                </td>
                                <td style="padding:14px 18px;">
                                    @php
                                        $statusStyle = match($item->status) {
                                            'Selesai' => ['bg' => '#D1FAE5', 'text' => '#065F46', 'border' => '#A7F3D0'],
                                            'Proses' => ['bg' => '#FEF3C7', 'text' => '#92400E', 'border' => '#FDE68A'],
                                            default => ['bg' => '#F1F5F9', 'text' => '#475569', 'border' => '#CBD5E1'],
                                        };
                                    @endphp
                                    <span style="background:{{ $statusStyle['bg'] }}; color:{{ $statusStyle['text'] }}; border:1px solid {{ $statusStyle['border'] }}; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; white-space:nowrap;">
                                        {{ $item->status ?? 'Selesai' }}
                                    </span>
                                </td>
                                <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                    <div style="display:inline-flex; align-items:center; gap:4px;">
                                        <button type="button" @click="openDetail({{ json_encode($item) }})"
                                                title="Detail"
                                                style="padding:6px 9px; background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center;">
                                            <i data-lucide="eye" style="width:13px; height:13px;"></i>
                                        </button>
                                        <button type="button" @click="openEdit({{ json_encode($item) }}, '{{ route('admin.apar.monitoring-kejadian.update', $item->id) }}')"
                                                title="Edit"
                                                style="padding:6px 9px; background:#F1F5F9; color:#334155; border:1px solid #CBD5E1; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center;">
                                            <i data-lucide="pencil" style="width:13px; height:13px;"></i>
                                        </button>
                                        <button type="button" @click="openDelete('{{ $item->kode_kejadian }}', '{{ route('admin.apar.monitoring-kejadian.destroy', $item->id) }}')"
                                                title="Hapus"
                                                style="padding:6px 9px; background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center;">
                                            <i data-lucide="trash-2" style="width:13px; height:13px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($dataKejadian->hasPages())
                <div style="padding:16px 20px; border-top:1px solid #F1F5F9; background:#FAFCFE;">
                    {{ $dataKejadian->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- ===================== MODAL: DETAIL KEJADIAN ===================== --}}
    <div x-show="detailModalOpen" x-cloak class="admin-modal-overlay" @click.self="detailModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; max-width:640px; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Detail Kejadian</h3>
                    <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;" x-text="activeItem.kode_kejadian"></p>
                </div>
                <button type="button" @click="detailModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            <div style="padding:20px;">

                <div style="background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0; margin-bottom:14px;">
                    <div style="font-size:11px; font-weight:800; color:#1E3A8A; margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px;">Informasi Kejadian</div>
                    <div class="modal-form-grid" style="margin-bottom:0;">
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Jenis</span><span style="font-weight:700; color:#1E293B;" x-text="activeItem.jenis_kejadian"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Status</span><span style="font-weight:700; color:#1E293B;" x-text="activeItem.status"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Waktu Kejadian</span><span style="font-weight:700; color:#1E293B;" x-text="formatDate(activeItem.waktu_kejadian)"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Kategori Detail</span><span style="font-weight:700; color:#1E293B;" x-text="activeItem.kategori_detail || '-'"></span></div>
                        <div class="full-mobile" style="grid-column: span 2;"><span style="display:block; font-size:11px; color:#94A3B8;">Lokasi</span><span style="font-weight:700; color:#1E293B;" x-text="activeItem.lokasi"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Kecamatan</span><span style="font-weight:600; color:#334155;" x-text="activeItem.kecamatan || '-'"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Kelurahan</span><span style="font-weight:600; color:#334155;" x-text="activeItem.kelurahan || '-'"></span></div>
                    </div>
                </div>

                <div style="background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0; margin-bottom:14px;">
                    <div style="font-size:11px; font-weight:800; color:#1E3A8A; margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px;">Penanganan</div>
                    <div class="modal-form-grid" style="margin-bottom:0;">
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Pos / Regu</span><span style="font-weight:700; color:#1E293B;" x-text="activeItem.pos_regu || '-'"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Danru / Komandan Regu</span><span style="font-weight:700; color:#1E293B;" x-text="activeItem.komandan_regu || '-'"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Unit / Armada</span><span style="font-weight:700; color:#1E293B;" x-text="activeItem.unit_armada || '-'"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Waktu Terima Laporan</span><span style="font-weight:600; color:#334155;" x-text="activeItem.waktu_terima_laporan || '-'"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Waktu Berangkat</span><span style="font-weight:600; color:#334155;" x-text="activeItem.waktu_berangkat || '-'"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Waktu Tiba</span><span style="font-weight:600; color:#334155;" x-text="activeItem.waktu_tiba || '-'"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Waktu Selesai</span><span style="font-weight:600; color:#334155;" x-text="activeItem.waktu_selesai || '-'"></span></div>
                    </div>
                </div>

                <div style="background:#FEF2F2; padding:14px; border-radius:10px; border:1px solid #FCA5A5; margin-bottom:14px;">
                    <div style="font-size:11px; font-weight:800; color:#991B1B; margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px;">Dampak Kejadian</div>
                    <div class="modal-form-grid-3">
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Est. Kerugian</span><span style="font-weight:700; color:#0F172A;" x-text="formatRupiah(activeItem.estimasi_kerugian)"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Korban Luka</span><span style="font-weight:700; color:#0F172A;" x-text="(activeItem.korban_luka || 0) + ' orang'"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Korban Jiwa</span><span style="font-weight:700; color:#0F172A;" x-text="(activeItem.korban_jiwa || 0) + ' orang'"></span></div>
                    </div>
                    <template x-if="activeItem.penyebab">
                        <div style="margin-top:10px;"><span style="display:block; font-size:11px; color:#94A3B8;">Penyebab</span><span style="font-weight:600; color:#334155;" x-text="activeItem.penyebab"></span></div>
                    </template>
                    <template x-if="activeItem.objek_terdampak">
                        <div style="margin-top:10px;"><span style="display:block; font-size:11px; color:#94A3B8;">Objek Terdampak</span><span style="font-weight:600; color:#334155;" x-text="activeItem.objek_terdampak"></span></div>
                    </template>
                </div>

                <div style="background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0;">
                    <div style="font-size:11px; font-weight:800; color:#1E3A8A; margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px;">Pelapor &amp; Catatan</div>
                    <div class="modal-form-grid" style="margin-bottom:10px;">
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">Nama Pelapor</span><span style="font-weight:600; color:#334155;" x-text="activeItem.nama_pelapor || '-'"></span></div>
                        <div><span style="display:block; font-size:11px; color:#94A3B8;">No. HP Pelapor</span><span style="font-weight:600; color:#334155;" x-text="activeItem.no_hp_pelapor || '-'"></span></div>
                    </div>
                    <span style="display:block; font-size:11px; color:#94A3B8;">Keterangan</span>
                    <span style="font-weight:500; color:#334155;" x-text="activeItem.keterangan || 'Tidak ada catatan tambahan.'"></span>
                </div>

            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; padding:14px 20px; border-top:1px solid #E2E8F0;">
                <button type="button" @click="detailModalOpen = false"
                        style="padding:8px 18px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: TAMBAH KEJADIAN ===================== --}}
    <div x-show="createModalOpen" x-cloak class="admin-modal-overlay" @click.self="createModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; max-width:660px; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; background:#FFFFFF;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="plus-circle" style="width:16px; height:16px; color:var(--damkar-red);"></i>
                    Form Input Kejadian Baru
                </h3>
                <button type="button" @click="createModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            <form action="{{ route('admin.apar.monitoring-kejadian.store') }}" method="POST" style="padding:20px;">
                @csrf
                @include('admin.apar-kejadian.partials.form-fields', ['mode' => 'create'])

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid #E2E8F0; padding-top:14px; margin-top:4px;">
                    <button type="button" @click="createModalOpen = false"
                            style="padding:8px 18px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:8px 20px; background:var(--damkar-red); color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">
                        <i data-lucide="save" style="width:13px; height:13px; display:inline; vertical-align:-2px; margin-right:4px;"></i>
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL: EDIT KEJADIAN ===================== --}}
    <div x-show="editModalOpen" x-cloak class="admin-modal-overlay" @click.self="editModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; max-width:660px; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; background:#FFFFFF;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="pencil" style="width:15px; height:15px; color:var(--damkar-blue);"></i>
                    Edit Data Kejadian
                </h3>
                <button type="button" @click="editModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            <form :action="editUrl" method="POST" style="padding:20px;">
                @csrf
                @method('PUT')
                @include('admin.apar-kejadian.partials.form-fields', ['mode' => 'edit'])

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid #E2E8F0; padding-top:14px; margin-top:4px;">
                    <button type="button" @click="editModalOpen = false"
                            style="padding:8px 18px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:8px 20px; background:var(--damkar-blue); color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">
                        <i data-lucide="save" style="width:13px; height:13px; display:inline; vertical-align:-2px; margin-right:4px;"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL: HAPUS KEJADIAN ===================== --}}
    <div x-show="deleteModalOpen" x-cloak class="admin-modal-overlay" @click.self="deleteModalOpen = false">
        <div class="admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; max-width:420px; padding:24px; text-align:center; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0;" @click.stop>
            <div style="width:48px; height:48px; border-radius:50%; background:#FEE2E2; color:#DC2626; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                <i data-lucide="trash-2" style="width:24px; height:24px;"></i>
            </div>
            <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0 0 6px;">Hapus Data Kejadian?</h3>
            <p style="font-size:13px; color:#64748B; margin-bottom:20px;">
                Apakah Anda yakin ingin menghapus kejadian <strong style="color:#0F172A;" x-text="deleteLabel"></strong>? Tindakan ini tidak dapat dibatalkan.
            </p>
            <form :action="deleteUrl" method="POST" style="display:flex; justify-content:center; gap:10px;">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false"
                        style="padding:8px 18px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                    Batal
                </button>
                <button type="submit"
                        style="padding:8px 18px; background:#DC2626; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">
                    Ya, Hapus Data
                </button>
            </form>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
@media (max-width: 900px) {
    .kejadian-chart-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush

@push('scripts')
{{-- Chart.js Library --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // ===================== AUTO KODE KEJADIAN (TAMBAH KEJADIAN) =====================
    // TK65      -> Kebakaran
    // RESC      -> Rescue / Penyelamatan
    // PRESC     -> Rescue / Penyelamatan yang berstatus Proses (pending)
    // KEJ       -> Jenis belum dipilih (default)
    const kodeInput   = document.getElementById('createKodeKejadian');
    const jenisSelect = document.getElementById('createJenisKejadian');
    const statusSelect = document.getElementById('createStatusKejadian');

    function generateKodeKejadian() {
        if (!kodeInput || !jenisSelect) return;

        const jenis  = jenisSelect.value;
        const status = statusSelect ? statusSelect.value : '';

        let prefix = 'KEJ';
        if (jenis === 'Kebakaran') {
            prefix = 'TK65';
        } else if (jenis === 'Rescue' || jenis === 'Penyelamatan') {
            prefix = (status === 'Proses') ? 'PRESC' : 'RESC';
        }

        const today = new Date();
        const tanggal = today.getFullYear().toString()
            + String(today.getMonth() + 1).padStart(2, '0')
            + String(today.getDate()).padStart(2, '0');
        const acak = Math.floor(100 + Math.random() * 900);

        kodeInput.value = `${prefix}-${tanggal}-${acak}`;
    }

    if (jenisSelect) jenisSelect.addEventListener('change', generateKodeKejadian);
    if (statusSelect) statusSelect.addEventListener('change', generateKodeKejadian);

    // ===================== GRAFIK: TREN KEJADIAN PER BULAN =====================
    const trenCanvas = document.getElementById('chartTrenKejadian');
    if (trenCanvas) {
        const ctx = trenCanvas.getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [
                    {
                        label: 'Kebakaran',
                        data: {{ json_encode($chartBulanan['Kebakaran']) }},
                        backgroundColor: '#C0201F',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Rescue',
                        data: {{ json_encode($chartBulanan['Rescue']) }},
                        backgroundColor: '#1D4ED8',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Penyelamatan',
                        data: {{ json_encode($chartBulanan['Penyelamatan']) }},
                        backgroundColor: '#0891B2',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Non-Kebakaran',
                        data: {{ json_encode($chartBulanan['Non-Kebakaran']) }},
                        backgroundColor: '#64748B',
                        borderRadius: 6,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1000, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { family: 'Poppins', size: 13, weight: 'bold' },
                        bodyFont: { family: 'Poppins', size: 12 },
                        padding: 12,
                        cornerRadius: 10,
                        boxPadding: 6,
                        usePointStyle: true,
                    }
                },
                scales: {
                    x: {
                        stacked: false,
                        grid: { display: false },
                        ticks: { font: { family: 'Poppins', size: 11, weight: '500' }, color: '#64748B' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F1F5F9', drawBorder: false },
                        ticks: {
                            font: { family: 'Poppins', size: 11, weight: '500' },
                            color: '#64748B',
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // ===================== GRAFIK: DISTRIBUSI JENIS KEJADIAN =====================
    const distCanvas = document.getElementById('chartDistribusiJenis');
    if (distCanvas) {
        const ctx2 = distCanvas.getContext('2d');

        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($jenisWarna)) !!},
                datasets: [{
                    data: {!! json_encode(array_map(fn($j) => $chartDistribusiJenis[$j] ?? 0, array_keys($jenisWarna))) !!},
                    backgroundColor: {!! json_encode(array_values($jenisWarna)) !!},
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                animation: { duration: 1000, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { family: 'Poppins', size: 13, weight: 'bold' },
                        bodyFont: { family: 'Poppins', size: 12 },
                        padding: 12,
                        cornerRadius: 10,
                        boxPadding: 6,
                        usePointStyle: true,
                    }
                }
            }
        });
    }
});
</script>

<script>
function monitoringKejadianAdmin() {
    return {
        createModalOpen: false,
        detailModalOpen: false,
        editModalOpen: false,
        deleteModalOpen: false,

        activeItem: {},
        editForm: {},
        editUrl: '',
        deleteUrl: '',
        deleteLabel: '',

        openDetail(item) {
            this.activeItem = item;
            this.detailModalOpen = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openEdit(item, url) {
            this.editUrl = url;
            this.editForm = {
                ...item,
                waktu_kejadian: item.waktu_kejadian ? item.waktu_kejadian.slice(0, 16) : '',
                waktu_terima_laporan: item.waktu_terima_laporan ? item.waktu_terima_laporan.slice(0, 5) : '',
                waktu_berangkat: item.waktu_berangkat ? item.waktu_berangkat.slice(0, 5) : '',
                waktu_tiba: item.waktu_tiba ? item.waktu_tiba.slice(0, 5) : '',
                waktu_selesai: item.waktu_selesai ? item.waktu_selesai.slice(0, 5) : '',
            };
            this.editModalOpen = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openDelete(label, url) {
            this.deleteLabel = label;
            this.deleteUrl = url;
            this.deleteModalOpen = true;
        },

        formatDate(val) {
            if (!val) return '-';
            const d = new Date(val);
            if (isNaN(d)) return val;
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
                   ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
        },

        formatRupiah(val) {
            const num = Number(val || 0);
            return 'Rp ' + num.toLocaleString('id-ID');
        }
    };
}
</script>
@endpush
