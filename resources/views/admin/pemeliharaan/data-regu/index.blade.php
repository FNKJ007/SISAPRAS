@extends('layouts.admin')

@section('title', 'Data Master Regu — Admin')

@section('content')
<div x-data="{
    createModalOpen: {{ isset($errors) && $errors->any() ? 'true' : 'false' }},
    editModalOpen: false,
    deleteModalOpen: false,
    activeRegu: {},
    createDanruName: '',
    createDanruNip: '',
    editUrl: '',
    deleteUrl: ''
}">

    {{-- Flash Message --}}
    @if(session('success'))
        <div style="background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; padding:12px 16px; border-radius:12px; margin-bottom:20px; font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px;">
                <i data-lucide="check-circle-2" style="width:18px; height:18px; color:#059669;"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Header Section --}}
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Data Master Regu &amp; Komandan Regu (Danru)</h1>
            <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                Kelola pembagian regu operasional pasukan berdasarkan Posko, Bidang tugas, dan Komandan Regu.
            </p>
        </div>
        <button type="button" @click="createModalOpen = true"
                style="padding:10px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(27,42,107,0.2); transition:all 0.2s;">
            <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
            <span>Tambah Regu Baru</span>
        </button>
    </div>

    {{-- Ringkasan KPI Cards --}}
    <div class="kpi-grid-container" style="margin-bottom:24px;">
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#64748B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Formasi Regu</div>
            <div style="font-size:24px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total_regu'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#DC2626; font-size:11.5px; font-weight:700; text-transform:uppercase;">Regu Pemadam</div>
            <div style="font-size:24px; font-weight:800; color:#DC2626; margin-top:6px;">{{ $kpi['regu_pemadam'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#2563EB; font-size:11.5px; font-weight:700; text-transform:uppercase;">Regu Rescue</div>
            <div style="font-size:24px; font-weight:800; color:#2563EB; margin-top:6px;">{{ $kpi['regu_rescue'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#4F46E5; font-size:11.5px; font-weight:700; text-transform:uppercase;">Regu Pencegahan &amp; CC</div>
            <div style="font-size:24px; font-weight:800; color:#4F46E5; margin-top:6px;">{{ $kpi['regu_pencegahan'] + $kpi['regu_cc'] }}</div>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="admin-filter-bar">
        <form method="GET" action="{{ route('admin.pemeliharaan.data-regu') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                {{-- Filter Pos --}}
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Pos:</span>
                    <select name="pos" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        <option value="semua" @selected($posFilter === 'semua')>Semua Posko</option>
                        @foreach($posList as $p)
                            <option value="{{ $p->nama }}" @selected($posFilter === $p->nama)>{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Bidang --}}
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Bidang:</span>
                    <select name="bidang" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        <option value="semua" @selected($bidangFilter === 'semua')>Semua Bidang</option>
                        @foreach($bidangOptions as $b)
                            <option value="{{ $b }}" @selected($bidangFilter === $b)>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Input Pencarian --}}
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="position:relative;">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari nama regu / Danru..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:220px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
            </div>
        </form>
    </div>

    {{-- Tabel Data Regu --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
        @if($reguList->isEmpty())
            <div style="padding:56px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
                    <i data-lucide="search-x" style="width:30px; height:30px; color:#64748B;"></i>
                </div>
                @if(!empty($searchQuery) || $posFilter !== 'semua' || $bidangFilter !== 'semua')
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Data Tidak Ditemukan</div>
                    <div style="font-size:13px; color:#64748B; margin-bottom:18px; max-width:440px; margin-left:auto; margin-right:auto;">
                        Tidak ditemukan data regu yang sesuai dengan kriteria pencarian / filter Anda.
                    </div>
                    <a href="{{ route('admin.pemeliharaan.data-regu') }}" style="display:inline-flex; align-items:center; gap:8px; padding:9px 20px; background:#1B2A6B; color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                        <i data-lucide="rotate-ccw" style="width:14px; height:14px;"></i>
                        <span>Reset Filter &amp; Pencarian</span>
                    </a>
                @else
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Regu</div>
                    <div style="font-size:12.5px; color:#94A3B8;">Silakan tambahkan data regu operasional baru.</div>
                @endif
            </div>
        @else
            <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                <table style="width:100%; min-width:800px; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#0F172A; color:#FFFFFF; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px; font-weight:800;">
                            <th style="padding:14px 16px; width:50px; text-align:center;">No</th>
                            <th style="padding:14px 16px;">Nama Regu</th>
                            <th style="padding:14px 16px;">Pos</th>
                            <th style="padding:14px 16px;">Bidang</th>
                            <th style="padding:14px 16px;">Komandan Regu (Danru)</th>
                            <th style="padding:14px 16px; width:120px; text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="divide-y:1px solid #F1F5F9;">
                        @foreach($reguList as $index => $item)
                            <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:14px 16px; text-align:center; color:#64748B; font-weight:600;">
                                    {{ $reguList->firstItem() + $index }}
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="font-weight:800; color:#0F172A; display:inline-flex; align-items:center; gap:6px;">
                                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#1B2A6B;"></span>
                                        <span>{{ $item->nama }}</span>
                                    </span>
                                    @if($item->catatan)
                                        <div style="font-size:11px; color:#64748B; margin-top:2px;">{{ Str::limit($item->catatan, 35) }}</div>
                                    @endif
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="display:inline-flex; align-items:center; gap:5px; padding:4px 10px; background:#F1F5F9; border-radius:8px; font-weight:700; color:#334155; font-size:12px;">
                                        <i data-lucide="map-pin" style="width:13px; height:13px; color:#64748B;"></i>
                                        <span>{{ $item->pos ?: '—' }}</span>
                                    </span>
                                </td>
                                <td style="padding:14px 16px;">
                                    @if($item->bidang === 'Pemadam')
                                        <span style="padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; background:#FEE2E2; color:#991B1B; display:inline-flex; align-items:center; gap:4px;">
                                            <i data-lucide="flame" style="width:12px; height:12px;"></i>
                                            <span>Pemadam</span>
                                        </span>
                                    @elseif($item->bidang === 'Rescue')
                                        <span style="padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; background:#DBEAFE; color:#1E40AF; display:inline-flex; align-items:center; gap:4px;">
                                            <i data-lucide="life-buoy" style="width:12px; height:12px;"></i>
                                            <span>Rescue</span>
                                        </span>
                                    @elseif($item->bidang === 'Pencegahan')
                                        <span style="padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; background:#E0E7FF; color:#3730A3; display:inline-flex; align-items:center; gap:4px;">
                                            <i data-lucide="shield" style="width:12px; height:12px;"></i>
                                            <span>Pencegahan</span>
                                        </span>
                                    @else
                                        <span style="padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; background:#F1F5F9; color:#475569; display:inline-flex; align-items:center; gap:4px;">
                                            <i data-lucide="radio-tower" style="width:12px; height:12px;"></i>
                                            <span>{{ $item->bidang ?: 'Umum' }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td style="padding:14px 16px;">
                                    <div style="font-weight:700; color:#0F172A;">{{ $item->danru ?: 'Belum Ditentukan' }}</div>
                                    @if($item->nip_danru)
                                        <div style="font-size:11.5px; color:#64748B;">NIP: {{ $item->nip_danru }}</div>
                                    @endif
                                </td>
                                <td style="padding:14px 16px; text-align:center;">
                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                        {{-- Tombol Edit --}}
                                        <button type="button"
                                                @click="
                                                    activeRegu = {{ json_encode($item) }};
                                                    editUrl = '{{ route('admin.pemeliharaan.data-regu.update', $item->id) }}';
                                                    editModalOpen = true;
                                                "
                                                style="padding:6px 9px; background:#F8FAFC; border:1px solid #CBD5E1; border-radius:8px; color:#0F172A; cursor:pointer; font-size:12px; transition:all 0.15s;"
                                                title="Edit Regu">
                                            <i data-lucide="edit-3" style="width:14px; height:14px; color:#2563EB;"></i>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <button type="button"
                                                @click="
                                                    activeRegu = {{ json_encode($item) }};
                                                    deleteUrl = '{{ route('admin.pemeliharaan.data-regu.destroy', $item->id) }}';
                                                    deleteModalOpen = true;
                                                "
                                                style="padding:6px 9px; background:#F8FAFC; border:1px solid #CBD5E1; border-radius:8px; color:#DC2626; cursor:pointer; font-size:12px; transition:all 0.15s;"
                                                title="Hapus Regu">
                                            <i data-lucide="trash-2" style="width:14px; height:14px; color:#DC2626;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if($reguList->hasPages())
                <div style="padding:16px 20px; border-top:1px solid #E2E8F0; background:#F8FAFC;">
                    {{ $reguList->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- ======================== MODAL TAMBAH REGU ======================== --}}
    <div x-show="createModalOpen" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px);"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px;">
            <div @click.away="createModalOpen = false" style="background:#FFFFFF; border-radius:20px; max-width:520px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden;">
                <div style="padding:20px 24px; background:#0F172A; color:#FFFFFF; display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="shield" style="width:18px; height:18px; color:#93C5FD;"></i>
                        </div>
                        <div>
                            <h3 style="font-size:16px; font-weight:800; margin:0;">Tambah Master Regu Baru</h3>
                            <p style="font-size:11.5px; color:#94A3B8; margin:2px 0 0 0;">Formasi regu operasional &amp; Komandan Regu (Danru)</p>
                        </div>
                    </div>
                    <button type="button" @click="createModalOpen = false" style="background:transparent; border:none; color:#94A3B8; cursor:pointer; font-size:18px;">✕</button>
                </div>

                <form method="POST" action="{{ route('admin.pemeliharaan.data-regu.store') }}" style="padding:24px;">
                    @csrf
                    <input type="hidden" name="status" value="aktif">

                    {{-- Nama Regu --}}
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Nama Regu <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama') }}" placeholder="Contoh: Regu 1" required
                               style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                        {{-- Pos Penempatan --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Pos <span style="color:#DC2626;">*</span></label>
                            <select name="pos" required style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; background:#FFFFFF;">
                                <option value="" selected disabled>Pilih Pos</option>
                                @foreach($posList as $p)
                                    <option value="{{ $p->nama }}" @selected(old('pos') === $p->nama)>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Bidang Tugas --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Bidang <span style="color:#DC2626;">*</span></label>
                            <select name="bidang" required style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; background:#FFFFFF;">
                                <option value="" selected disabled>Pilih Bidang</option>
                                @foreach($bidangOptions as $b)
                                    <option value="{{ $b }}" @selected(old('bidang', 'Pemadam') === $b)>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Komandan Regu (Danru) Autocomplete from DB (Only Danru) --}}
                    <div style="margin-bottom:14px; position:relative;"
                         x-data="{
                             danruOpen: false,
                             search: '',
                             get filteredDanrus() {
                                 if (!this.search || this.search.trim() === '') return {{ json_encode($danruList) }};
                                 let q = this.search.toLowerCase();
                                 return {{ json_encode($danruList) }}.filter(d => 
                                     d.name.toLowerCase().includes(q) || 
                                     (d.nip && d.nip.toLowerCase().includes(q))
                                 );
                             },
                             select(d) {
                                 this.search = d.name;
                                 createDanruNip = d.nip || '';
                                 this.danruOpen = false;
                             }
                         }"
                         @click.away="danruOpen = false">

                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">
                            Komandan Regu (Danru) <span style="font-size:11px; font-weight:500; color:#2563EB;">(Ketik nama Danru)</span>
                        </label>

                        <div style="position:relative;">
                            <input type="text"
                                   name="danru"
                                   x-model="search"
                                   @focus="danruOpen = true"
                                   @input="danruOpen = true"
                                   placeholder="Ketik nama Danru (contoh: Agus, Solihin, Dudi)..."
                                   autocomplete="off"
                                   style="width:100%; padding:9px 12px 9px 34px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box; background:#FFFFFF;">
                            <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:#94A3B8;"></i>
                            <button type="button" @click="danruOpen = !danruOpen" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:transparent; border:none; color:#64748B; cursor:pointer;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>

                        {{-- Dropdown Suggestions (Khusus Danru) --}}
                        <div x-show="danruOpen && filteredDanrus.length > 0"
                             style="display:none; position:absolute; left:0; right:0; top:100%; margin-top:4px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; box-shadow:0 10px 25px rgba(15,23,42,0.15); max-height:210px; overflow-y:auto; z-index:99999;">
                            <template x-for="item in filteredDanrus" :key="item.id">
                                <div @click="select(item)"
                                     style="padding:9px 12px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                     onmouseover="this.style.background='#EFF6FF'"
                                     onmouseout="this.style.background='transparent'">
                                    <div>
                                        <strong style="display:block; color:#0F172A; font-size:12.5px;" x-text="item.name"></strong>
                                        <span style="font-size:11px; color:#64748B;" x-text="'NIP: ' + (item.nip || '—')"></span>
                                    </div>
                                    <span style="padding:2px 8px; border-radius:12px; background:#DBEAFE; color:#1E40AF; font-size:10.5px; font-weight:700;">
                                        Danru
                                    </span>
                                </div>
                            </template>
                        </div>
                        <div x-show="danruOpen && filteredDanrus.length === 0"
                             style="display:none; position:absolute; left:0; right:0; top:100%; margin-top:4px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; padding:12px; font-size:12px; color:#94A3B8; text-align:center; z-index:99999; box-shadow:0 10px 25px rgba(15,23,42,0.15);">
                            Tidak ditemukan Danru dengan nama "<span x-text="search"></span>"
                        </div>
                    </div>

                    {{-- NIP Danru (Terisi Otomatis) --}}
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">NIP Danru <span style="font-size:11px; font-weight:500; color:#64748B;">(Otomatis terisi)</span></label>
                        <input type="text" name="nip_danru" x-model="createDanruNip" placeholder="NIP otomatis terisi saat memilih Danru"
                               style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box; background:#F8FAFC;">
                    </div>

                    {{-- Catatan --}}
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Catatan / Keterangan</label>
                        <textarea name="catatan" rows="2" placeholder="Catatan tambahan..."
                                  style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">{{ old('catatan') }}</textarea>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                        <button type="button" @click="createModalOpen = false" style="padding:9px 18px; border-radius:8px; border:1px solid #CBD5E1; background:#F8FAFC; color:#475569; font-size:13px; font-weight:600; cursor:pointer;">
                            Batal
                        </button>
                        <button type="submit" style="padding:9px 20px; border-radius:8px; border:none; background:#1B2A6B; color:#FFFFFF; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                            Simpan Data Regu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ======================== MODAL EDIT REGU ======================== --}}
    <div x-show="editModalOpen" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px);"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px;">
            <div @click.away="editModalOpen = false" style="background:#FFFFFF; border-radius:20px; max-width:520px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden;">
                <div style="padding:20px 24px; background:#1E3A8A; color:#FFFFFF; display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="edit-3" style="width:18px; height:18px; color:#93C5FD;"></i>
                        </div>
                        <div>
                            <h3 style="font-size:16px; font-weight:800; margin:0;">Edit Data Regu</h3>
                            <p style="font-size:11.5px; color:#94A3B8; margin:2px 0 0 0;">Perbarui informasi regu, pos, bidang, atau Danru</p>
                        </div>
                    </div>
                    <button type="button" @click="editModalOpen = false" style="background:transparent; border:none; color:#94A3B8; cursor:pointer; font-size:18px;">✕</button>
                </div>

                <form method="POST" :action="editUrl" style="padding:24px;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="aktif">

                    {{-- Nama Regu --}}
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Nama Regu <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nama" x-model="activeRegu.nama" placeholder="Contoh: Regu 1" required
                               style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                        {{-- Pos Penempatan --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Pos <span style="color:#DC2626;">*</span></label>
                            <select name="pos" required x-model="activeRegu.pos" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; background:#FFFFFF;">
                                <option value="" disabled>Pilih Pos</option>
                                @foreach($posList as $p)
                                    <option value="{{ $p->nama }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Bidang Tugas --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Bidang <span style="color:#DC2626;">*</span></label>
                            <select name="bidang" required x-model="activeRegu.bidang" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; background:#FFFFFF;">
                                <option value="" disabled>Pilih Bidang</option>
                                @foreach($bidangOptions as $b)
                                    <option value="{{ $b }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Komandan Regu (Danru) Autocomplete from DB (Only Danru) --}}
                    <div style="margin-bottom:14px; position:relative;"
                         x-data="{
                             danruOpen: false,
                             get filteredDanrus() {
                                 if (!activeRegu.danru || activeRegu.danru.trim() === '') return {{ json_encode($danruList) }};
                                 let q = activeRegu.danru.toLowerCase();
                                 return {{ json_encode($danruList) }}.filter(d => 
                                     d.name.toLowerCase().includes(q) || 
                                     (d.nip && d.nip.toLowerCase().includes(q))
                                 );
                             },
                             select(d) {
                                 activeRegu.danru = d.name;
                                 activeRegu.nip_danru = d.nip || '';
                                 this.danruOpen = false;
                             }
                         }"
                         @click.away="danruOpen = false">

                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">
                            Komandan Regu (Danru) <span style="font-size:11px; font-weight:500; color:#2563EB;">(Ketik nama Danru)</span>
                        </label>

                        <div style="position:relative;">
                            <input type="text"
                                   name="danru"
                                   x-model="activeRegu.danru"
                                   @focus="danruOpen = true"
                                   @input="danruOpen = true"
                                   placeholder="Ketik nama Danru (contoh: Agus, Solihin, Dudi)..."
                                   autocomplete="off"
                                   style="width:100%; padding:9px 12px 9px 34px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box; background:#FFFFFF;">
                            <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:#94A3B8;"></i>
                            <button type="button" @click="danruOpen = !danruOpen" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:transparent; border:none; color:#64748B; cursor:pointer;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>

                        {{-- Dropdown Suggestions (Khusus Danru) --}}
                        <div x-show="danruOpen && filteredDanrus.length > 0"
                             style="display:none; position:absolute; left:0; right:0; top:100%; margin-top:4px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; box-shadow:0 10px 25px rgba(15,23,42,0.15); max-height:210px; overflow-y:auto; z-index:99999;">
                            <template x-for="item in filteredDanrus" :key="item.id">
                                <div @click="select(item)"
                                     style="padding:9px 12px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                     onmouseover="this.style.background='#EFF6FF'"
                                     onmouseout="this.style.background='transparent'">
                                    <div>
                                        <strong style="display:block; color:#0F172A; font-size:12.5px;" x-text="item.name"></strong>
                                        <span style="font-size:11px; color:#64748B;" x-text="'NIP: ' + (item.nip || '—')"></span>
                                    </div>
                                    <span style="padding:2px 8px; border-radius:12px; background:#DBEAFE; color:#1E40AF; font-size:10.5px; font-weight:700;">
                                        Danru
                                    </span>
                                </div>
                            </template>
                        </div>
                        <div x-show="danruOpen && filteredDanrus.length === 0"
                             style="display:none; position:absolute; left:0; right:0; top:100%; margin-top:4px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; padding:12px; font-size:12px; color:#94A3B8; text-align:center; z-index:99999; box-shadow:0 10px 25px rgba(15,23,42,0.15);">
                            Tidak ditemukan Danru dengan nama "<span x-text="activeRegu.danru"></span>"
                        </div>
                    </div>

                    {{-- NIP Danru (Terisi Otomatis) --}}
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">NIP Danru <span style="font-size:11px; font-weight:500; color:#64748B;">(Otomatis terisi)</span></label>
                        <input type="text" name="nip_danru" x-model="activeRegu.nip_danru" placeholder="NIP otomatis terisi saat memilih Danru"
                               style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box; background:#F8FAFC;">
                    </div>

                    {{-- Catatan --}}
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Catatan / Keterangan</label>
                        <textarea name="catatan" rows="2" x-model="activeRegu.catatan" placeholder="Catatan tambahan..."
                                  style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;"></textarea>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                        <button type="button" @click="editModalOpen = false" style="padding:9px 18px; border-radius:8px; border:1px solid #CBD5E1; background:#F8FAFC; color:#475569; font-size:13px; font-weight:600; cursor:pointer;">
                            Batal
                        </button>
                        <button type="submit" style="padding:9px 20px; border-radius:8px; border:none; background:#1E3A8A; color:#FFFFFF; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(30,58,138,0.25);">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ======================== MODAL HAPUS REGU ======================== --}}
    <div x-show="deleteModalOpen" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px);"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px;">
            <div @click.away="deleteModalOpen = false" style="background:#FFFFFF; border-radius:20px; max-width:440px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden;">
                <div style="padding:24px; text-align:center;">
                    <div style="width:52px; height:52px; background:#FEE2E2; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto;">
                        <i data-lucide="alert-triangle" style="width:26px; height:26px; color:#DC2626;"></i>
                    </div>
                    <h3 style="font-size:17px; font-weight:800; color:#0F172A; margin:0 0 6px 0;">Hapus Data Regu?</h3>
                    <p style="font-size:13px; color:#64748B; margin:0 0 20px 0;">
                        Apakah Anda yakin ingin menghapus data <strong style="color:#0F172A;" x-text="activeRegu.nama + ' - ' + activeRegu.pos + ' (' + activeRegu.bidang + ')'"></strong>?
                    </p>

                    <form method="POST" :action="deleteUrl" style="display:flex; justify-content:center; gap:10px;">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="deleteModalOpen = false" style="padding:9px 18px; border-radius:8px; border:1px solid #CBD5E1; background:#F8FAFC; color:#475569; font-size:13px; font-weight:600; cursor:pointer;">
                            Batal
                        </button>
                        <button type="submit" style="padding:9px 20px; border-radius:8px; border:none; background:#DC2626; color:#FFFFFF; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(220,38,38,0.25);">
                            Ya, Hapus Regu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection