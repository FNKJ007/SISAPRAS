@extends('layouts.app')
@section('title', 'Riwayat Pengecekan dan Pengajuan — Pemadam')

@section('content')
<div class="max-w-7xl mx-auto p-3.5 sm:p-6" x-data="{
    activeTab: '{{ $tab ?? 'unit' }}',
    selectedUnit: null,
    selectedAlat: null,
    selectedPengajuan: null,
    openUnitModal(item) {
        this.selectedUnit = item;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },
    openAlatModal(item) {
        this.selectedAlat = item;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },
    openPengajuanModal(item) {
        this.selectedPengajuan = item;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },
    closeModals() {
        this.selectedUnit = null;
        this.selectedAlat = null;
        this.selectedPengajuan = null;
    }
}" x-effect="document.body.classList.toggle('modal-open', selectedUnit !== null || selectedAlat !== null || selectedPengajuan !== null)" x-init="$watch('activeTab', () => $nextTick(() => { if (window.lucide) lucide.createIcons(); }))">

    {{-- Header Page --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3.5 mb-5">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-black text-blue-950 flex items-center gap-2.5 tracking-tight">
                <span class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0 shadow-2xs">
                    <i data-lucide="history" class="w-5 h-5"></i>
                </span>
                <span>Riwayat Pengecekan dan Pengajuan Pemadam</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 max-w-2xl leading-relaxed">
                Daftar rekapan hasil pemeriksaan harian unit armada, peralatan, serta pengajuan pemeliharaan pemadam kebakaran.
            </p>
        </div>
        
        {{-- Action Buttons (Neat and Responsive) --}}
        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 sm:gap-2.5 flex-shrink-0 pt-1 lg:pt-0">
            <a href="{{ route('unit-pemadam.cek-harian-unit') }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white rounded-xl text-xs font-bold transition-all shadow-xs hover:shadow-sm whitespace-nowrap cursor-pointer">
                <i data-lucide="truck" class="w-4 h-4"></i>
                <span>Cek Unit</span>
            </a>
            <a href="{{ route('alat-pemadam.cek-harian-alat') }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-blue-900 hover:bg-blue-950 active:bg-blue-950 text-white rounded-xl text-xs font-bold transition-all shadow-xs hover:shadow-sm whitespace-nowrap cursor-pointer">
                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                <span>Cek Alat</span>
            </a>
            <a href="{{ route('pemeliharaan.pengajuan') }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold transition-all shadow-xs hover:shadow-sm whitespace-nowrap cursor-pointer">
                <i data-lucide="file-plus-2" class="w-4 h-4"></i>
                <span>Pengajuan Baru</span>
            </a>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-200 shadow-2xs mb-4">
        <form method="GET" action="{{ route('unit-pemadam.riwayat') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 sm:gap-3 items-center">
            <input type="hidden" name="tab" :value="activeTab">
            
            <div class="sm:col-span-5 relative">
                <input type="text" name="search" value="{{ $searchQuery ?? '' }}"
                       placeholder="Cari Unit, Pemeriksa, Pos, Danru, Kabid..."
                       class="w-full pl-8 sm:pl-9 pr-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-red-600 bg-white">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2"></i>
            </div>

            <div class="sm:col-span-4 relative">
                <input type="date" name="tanggal" value="{{ $tanggal ?? '' }}"
                       class="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-red-600 bg-white text-gray-700">
            </div>

            <div class="sm:col-span-3 flex items-center gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-950 hover:bg-blue-900 text-white text-xs font-bold rounded-lg transition shadow-2xs cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                @if(!empty($searchQuery) || !empty($tanggal))
                    <a href="{{ route('unit-pemadam.riwayat') }}?tab={{ $tab }}"
                       class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg transition border border-gray-200 text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tab Switcher --}}
    <div class="mb-4">
        <div class="bg-gray-100/90 p-1 rounded-xl border border-gray-200 inline-flex flex-nowrap overflow-x-auto no-scrollbar max-w-full gap-1 shadow-2xs">
            <button type="button" @click="activeTab = 'unit'"
                    :class="activeTab === 'unit' ? 'bg-white text-red-600 shadow-xs font-extrabold border border-gray-200/80' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60 font-bold'"
                    class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 text-xs rounded-lg transition-all whitespace-nowrap flex-shrink-0 cursor-pointer">
                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                <span>Unit Kendaraan</span>
                <span :class="activeTab === 'unit' ? 'bg-red-100 text-red-700' : 'bg-gray-200 text-gray-700'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-extrabold transition">
                    {{ $cekUnitList->total() }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'alat'"
                    :class="activeTab === 'alat' ? 'bg-white text-red-600 shadow-xs font-extrabold border border-gray-200/80' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60 font-bold'"
                    class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 text-xs rounded-lg transition-all whitespace-nowrap flex-shrink-0 cursor-pointer">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                <span>Peralatan<span class="hidden sm:inline"> Pemadam</span></span>
                <span :class="activeTab === 'alat' ? 'bg-red-100 text-red-700' : 'bg-gray-200 text-gray-700'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-extrabold transition">
                    {{ $cekAlatList->total() }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'pengajuan'"
                    :class="activeTab === 'pengajuan' ? 'bg-white text-red-600 shadow-xs font-extrabold border border-gray-200/80' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60 font-bold'"
                    class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 text-xs rounded-lg transition-all whitespace-nowrap flex-shrink-0 cursor-pointer">
                <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                <span>Pengajuan</span>
                <span :class="activeTab === 'pengajuan' ? 'bg-red-100 text-red-700' : 'bg-gray-200 text-gray-700'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-extrabold transition">
                    {{ $pengajuanList->total() }}
                </span>
            </button>
        </div>
    </div>

    {{-- ===================== TAB 1: UNIT KENDARAAN ===================== --}}
    <div x-show="activeTab === 'unit'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
            @if($cekUnitList->isEmpty())
                <div class="p-10 sm:p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-50 border border-gray-200 flex items-center justify-center mx-auto mb-3 text-gray-400">
                        <i data-lucide="inbox" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Belum Ada Riwayat Pengecekan Unit</h3>
                    <p class="text-xs text-gray-500 mt-1">Data pengecekan unit pemadam yang disimpan akan muncul di sini.</p>
                </div>
            @else
                {{-- DESKTOP & TABLET TABLE VIEW (Hidden on Mobile) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-3.5 text-center w-12">No</th>
                                <th class="py-3 px-3.5">Tanggal</th>
                                <th class="py-3 px-3.5">Unit Kendaraan</th>
                                <th class="py-3 px-3.5">Pos</th>
                                <th class="py-3 px-3.5">Pemeriksa / Danru</th>
                                <th class="py-3 px-3.5">Kebersihan</th>
                                <th class="py-3 px-3.5">Kondisi</th>
                                <th class="py-3 px-3.5 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($cekUnitList as $index => $item)
                            <tr class="hover:bg-blue-50/20 transition">
                                <td class="py-3 px-3.5 text-center text-gray-500 font-medium">
                                    {{ $cekUnitList->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap">
                                    <span class="font-bold text-gray-900 block">
                                        {{ \Carbon\Carbon::parse($item->tanggal_pemeriksaan)->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        {{ $item->created_at->format('H:i') }} WIB
                                    </span>
                                </td>
                                <td class="py-3 px-3.5">
                                    <span class="font-bold text-blue-950 block">{{ $item->unit_nama }}</span>
                                    <span class="text-[10px] text-gray-500 font-medium">BBM: {{ ucfirst($item->jenis_bbm ?? '-') }}</span>
                                </td>
                                <td class="py-3 px-3.5 font-medium text-gray-700 whitespace-nowrap">
                                    {{ $item->pos ?? '-' }}
                                </td>
                                <td class="py-3 px-3.5">
                                    <span class="font-bold text-gray-900 block">{{ $item->nama_pemeriksa }}</span>
                                    <span class="text-[10px] text-gray-500 block">Danru: {{ $item->nama_danru ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap">
                                    @if(($item->kebersihan_unit ?? 'bersih') === 'tidak_bersih')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">⚠️ Tidak Bersih</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">✨ Bersih</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap">
                                    @if(($item->jumlah_rusak ?? 0) > 0)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">
                                            {{ $item->jumlah_rusak }} Rusak
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                            Semua Baik ✓
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('unit-pemadam.cek-harian-unit.export-pdf', $item->id) }}"
                                           title="Unduh PDF Resmi"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-[11px] font-bold transition shadow-2xs">
                                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                            <span>PDF</span>
                                        </a>
                                        <button type="button" @click="openUnitModal({{ json_encode($item) }})"
                                                title="Lihat Rincian Data"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-[11px] font-bold transition border border-gray-200 cursor-pointer">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            <span>Detail</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARD VIEW (Optimized for Phones) --}}
                <div class="block md:hidden divide-y divide-gray-100">
                    @foreach($cekUnitList as $item)
                    <div class="p-3.5 space-y-2.5">
                        {{-- Card Header: Unit Name & Badges --}}
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-extrabold text-xs text-blue-950 leading-snug">{{ $item->unit_nama }}</h3>
                                <span class="text-[10px] text-gray-500 font-medium">BBM: {{ ucfirst($item->jenis_bbm ?? '-') }}</span>
                            </div>
                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                @if(($item->jumlah_rusak ?? 0) > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-red-100 text-red-700">
                                        {{ $item->jumlah_rusak }} Rusak
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700">
                                        Semua Baik ✓
                                    </span>
                                @endif
                                @if(($item->kebersihan_unit ?? 'bersih') === 'tidak_bersih')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">⚠️ Tidak Bersih</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">✨ Bersih</span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Info Grid --}}
                        <div class="grid grid-cols-2 gap-2 text-xs bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Tanggal</span>
                                <span class="font-bold text-gray-800 text-[11px] block">{{ \Carbon\Carbon::parse($item->tanggal_pemeriksaan)->translatedFormat('d M Y') }}</span>
                                <span class="text-[10px] text-gray-400">{{ $item->created_at->format('H:i') }} WIB</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Pos</span>
                                <span class="font-bold text-gray-800 text-[11px] block truncate">{{ $item->pos ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Pemeriksa</span>
                                <span class="font-medium text-gray-800 text-[11px] block truncate">{{ $item->nama_pemeriksa }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Danru</span>
                                <span class="font-medium text-gray-800 text-[11px] block truncate">{{ $item->nama_danru ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Card Actions --}}
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ route('unit-pemadam.cek-harian-unit.export-pdf', $item->id) }}"
                               class="inline-flex items-center justify-center gap-1 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-lg text-xs font-bold transition shadow-xs text-center">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Unduh PDF</span>
                            </a>
                            <button type="button" @click="openUnitModal({{ json_encode($item) }})"
                                    class="inline-flex items-center justify-center gap-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 rounded-lg text-xs font-bold transition border border-gray-200 cursor-pointer">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Detail</span>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="p-3 sm:p-3.5 border-t border-gray-100 bg-gray-50/50">
                    {{ $cekUnitList->links('vendor.pagination.custom') }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===================== TAB 2: PERALATAN ===================== --}}
    <div x-show="activeTab === 'alat'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
            @if($cekAlatList->isEmpty())
                <div class="p-10 sm:p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-50 border border-gray-200 flex items-center justify-center mx-auto mb-3 text-gray-400">
                        <i data-lucide="inbox" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Belum Ada Riwayat Pengecekan Alat</h3>
                    <p class="text-xs text-gray-500 mt-1">Data pengecekan alat pemadam yang disimpan akan muncul di sini.</p>
                </div>
            @else
                {{-- DESKTOP & TABLET TABLE VIEW (Hidden on Mobile) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-3.5 text-center w-12">No</th>
                                <th class="py-3 px-3.5">Tanggal</th>
                                <th class="py-3 px-3.5">Unit / Pos</th>
                                <th class="py-3 px-3.5">Pemeriksa</th>
                                <th class="py-3 px-3.5">Danru / Kabid</th>
                                <th class="py-3 px-3.5">Alat Baik</th>
                                <th class="py-3 px-3.5">Alat Rusak</th>
                                <th class="py-3 px-3.5 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($cekAlatList as $index => $item)
                            <tr class="hover:bg-blue-50/20 transition">
                                <td class="py-3 px-3.5 text-center text-gray-500 font-medium">
                                    {{ $cekAlatList->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap">
                                    <span class="font-bold text-gray-900 block">
                                        {{ \Carbon\Carbon::parse($item->tanggal_pemeriksaan)->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        {{ $item->created_at->format('H:i') }} WIB
                                    </span>
                                </td>
                                <td class="py-3 px-3.5">
                                    <span class="font-bold text-blue-950 block">{{ $item->unit_nama ?? 'Pemeriksaan Pos' }}</span>
                                    <span class="text-[10px] text-gray-500 font-medium">{{ $item->pos ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3.5">
                                    <span class="font-bold text-gray-900 block">{{ $item->nama_pemeriksa }}</span>
                                    <span class="text-[10px] text-gray-500">{{ $item->jabatan ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3.5">
                                    <span class="font-medium text-gray-800 block">Danru: {{ $item->nama_danru ?? '-' }}</span>
                                    <span class="text-[10px] text-gray-500 block">Kabid: {{ $item->nama_kabid ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                        {{ $item->total_alat_baik ?? 0 }} Baik
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap">
                                    @if(($item->total_alat_rusak ?? 0) > 0)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">
                                            {{ $item->total_alat_rusak }} Rusak
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">
                                            0 Rusak
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('alat-pemadam.cek-harian-alat.export-pdf', $item->id) }}"
                                           title="Unduh PDF Resmi"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-[11px] font-bold transition shadow-2xs">
                                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                            <span>PDF</span>
                                        </a>
                                        <button type="button" @click="openAlatModal({{ json_encode($item) }})"
                                                title="Lihat Rincian Data"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-[11px] font-bold transition border border-gray-200 cursor-pointer">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            <span>Detail</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARD VIEW (Optimized for Phones) --}}
                <div class="block md:hidden divide-y divide-gray-100">
                    @foreach($cekAlatList as $item)
                    <div class="p-3.5 space-y-2.5">
                        {{-- Card Header: Alat / Pos & Badges --}}
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-extrabold text-xs text-blue-950 leading-snug">{{ $item->unit_nama ?? 'Pemeriksaan Pos' }}</h3>
                                <span class="text-[10px] text-gray-500 font-medium">{{ $item->pos ?? '-' }}</span>
                            </div>
                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700">
                                    {{ $item->total_alat_baik ?? 0 }} Baik
                                </span>
                                @if(($item->total_alat_rusak ?? 0) > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-red-100 text-red-700">
                                        {{ $item->total_alat_rusak }} Rusak
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">
                                        0 Rusak
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Info Grid --}}
                        <div class="grid grid-cols-2 gap-2 text-xs bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Tanggal</span>
                                <span class="font-bold text-gray-800 text-[11px] block">{{ \Carbon\Carbon::parse($item->tanggal_pemeriksaan)->translatedFormat('d M Y') }}</span>
                                <span class="text-[10px] text-gray-400">{{ $item->created_at->format('H:i') }} WIB</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Pemeriksa</span>
                                <span class="font-bold text-gray-800 text-[11px] block truncate">{{ $item->nama_pemeriksa }}</span>
                                <span class="text-[10px] text-gray-400 block truncate">{{ $item->jabatan ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Danru</span>
                                <span class="font-medium text-gray-800 text-[11px] block truncate">{{ $item->nama_danru ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Kabid</span>
                                <span class="font-medium text-gray-800 text-[11px] block truncate">{{ $item->nama_kabid ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Card Actions --}}
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ route('alat-pemadam.cek-harian-alat.export-pdf', $item->id) }}"
                               class="inline-flex items-center justify-center gap-1 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-lg text-xs font-bold transition shadow-xs text-center">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Unduh PDF</span>
                            </a>
                            <button type="button" @click="openAlatModal({{ json_encode($item) }})"
                                    class="inline-flex items-center justify-center gap-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 rounded-lg text-xs font-bold transition border border-gray-200 cursor-pointer">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Detail</span>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="p-3 sm:p-3.5 border-t border-gray-100 bg-gray-50/50">
                    {{ $cekAlatList->links('vendor.pagination.custom') }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===================== TAB 3: PENGAJUAN PEMELIHARAAN ===================== --}}
    <div x-show="activeTab === 'pengajuan'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
            @if($pengajuanList->isEmpty())
                <div class="p-10 sm:p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-50 border border-gray-200 flex items-center justify-center mx-auto mb-3 text-gray-400">
                        <i data-lucide="inbox" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">Belum Ada Riwayat Pengajuan Pemeliharaan</h3>
                    <p class="text-xs text-gray-500 mt-1">Data surat permohonan pemeliharaan atau perbaikan yang diajukan akan tercatat di sini.</p>
                    <a href="{{ route('pemeliharaan.pengajuan') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 mt-4 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition shadow-xs">
                        <i data-lucide="file-plus" class="w-4 h-4"></i>
                        <span>Buat Pengajuan Baru</span>
                    </a>
                </div>
            @else
                {{-- DESKTOP & TABLET TABLE VIEW (Hidden on Mobile) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-3.5 text-center w-12">No</th>
                                <th class="py-3 px-3.5">Tanggal / Kode</th>
                                <th class="py-3 px-3.5">Unit Kendaraan</th>
                                <th class="py-3 px-3.5">Pos &amp; Regu</th>
                                <th class="py-3 px-3.5">Pemegang / Danru</th>
                                <th class="py-3 px-3.5">Item Perbaikan</th>
                                <th class="py-3 px-3.5 text-center">Status</th>
                                <th class="py-3 px-3.5 text-center w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($pengajuanList as $index => $item)
                            <tr class="hover:bg-blue-50/20 transition">
                                <td class="py-3 px-3.5 text-center text-gray-500 font-medium">
                                    {{ $pengajuanList->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap">
                                    <span class="font-bold text-gray-900 block">
                                        {{ $item->created_at ? $item->created_at->translatedFormat('d M Y') : '-' }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-mono">
                                        {{ $item->kode_verifikasi }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5">
                                    <span class="font-bold text-blue-950 block">{{ $item->nomor_lambung ?: '-' }}</span>
                                    <span class="text-[10px] text-gray-500 font-medium">{{ $item->jenis_kendaraan ?: 'Unit Kendaraan' }}</span>
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap">
                                    <span class="font-bold text-gray-800 block">{{ $item->pos ?: '-' }}</span>
                                    <span class="text-[10px] text-gray-500">{{ $item->regu ?: 'Regu 1' }}</span>
                                </td>
                                <td class="py-3 px-3.5">
                                    <span class="font-bold text-gray-900 block">{{ $item->nama_pemegang }}</span>
                                    <span class="text-[10px] text-gray-500 block">Danru: {{ $item->nama_komandan_regu ?: '-' }}</span>
                                </td>
                                <td class="py-3 px-3.5 max-w-[200px]">
                                    @php
                                        $itemList = $item->verified_item_list;
                                    @endphp
                                    @if(count($itemList) > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach(array_slice($itemList, 0, 2) as $it)
                                                <span class="inline-block px-2 py-0.5 bg-gray-100 border border-gray-200 text-gray-700 rounded text-[10px] font-medium truncate max-w-[120px]">
                                                    {{ $it }}
                                                </span>
                                            @endforeach
                                            @if(count($itemList) > 2)
                                                <span class="inline-block px-1.5 py-0.5 bg-gray-200 text-gray-600 rounded text-[10px] font-bold">
                                                    +{{ count($itemList) - 2 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                    @if($item->status === 'disetujui')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                                            <span>Disetujui</span>
                                        </span>
                                        @if($item->status_pengerjaan === 'selesai')
                                            <span class="block text-[9.5px] text-emerald-700 font-semibold mt-0.5">Selesai Servis</span>
                                        @elseif(!empty($item->tanggal_keberangkatan))
                                            <span class="block text-[9.5px] text-orange-600 font-semibold mt-0.5">Di Bengkel</span>
                                        @endif
                                    @elseif($item->status === 'ditolak')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                            <i data-lucide="x-circle" class="w-3 h-3"></i>
                                            <span>Ditolak</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <span>Menunggu</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('pemeliharaan.pengajuan.cetak-dokumen', ['id' => $item->id, 'type' => 'permohonanbidang']) }}"
                                           target="_blank"
                                           title="Cetak Surat Permohonan Bidang"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-[11px] font-bold transition shadow-2xs">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                            <span>Surat</span>
                                        </a>
                                        <button type="button" @click="openPengajuanModal({{ json_encode($item) }})"
                                                title="Lihat Rincian Pengajuan"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-[11px] font-bold transition border border-gray-200 cursor-pointer">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            <span>Detail</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARD VIEW (Optimized for Phones) --}}
                <div class="block md:hidden divide-y divide-gray-100">
                    @foreach($pengajuanList as $item)
                    <div class="p-3.5 space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-mono text-gray-400 block">{{ $item->kode_verifikasi }}</span>
                                <h3 class="font-extrabold text-xs text-blue-950 leading-snug">{{ $item->nomor_lambung ?: '-' }}</h3>
                                <span class="text-[10px] text-gray-500 font-medium">{{ $item->jenis_kendaraan ?: 'Unit Kendaraan' }}</span>
                            </div>
                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                @if($item->status === 'disetujui')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Disetujui ✓
                                    </span>
                                @elseif($item->status === 'ditolak')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                        Ditolak ✕
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Menunggu ⏳
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Tanggal</span>
                                <span class="font-bold text-gray-800 text-[11px] block">{{ $item->created_at ? $item->created_at->translatedFormat('d M Y') : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Pos &amp; Regu</span>
                                <span class="font-bold text-gray-800 text-[11px] block truncate">{{ $item->pos ?: '-' }} ({{ $item->regu ?: 'Regu 1' }})</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Pemegang</span>
                                <span class="font-medium text-gray-800 text-[11px] block truncate">{{ $item->nama_pemegang }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-semibold text-gray-400 block uppercase">Danru</span>
                                <span class="font-medium text-gray-800 text-[11px] block truncate">{{ $item->nama_komandan_regu ?: '-' }}</span>
                            </div>
                        </div>

                        <div class="text-xs">
                            <span class="text-[10px] font-semibold text-gray-400 block uppercase mb-1">Item Perbaikan</span>
                            <p class="text-[11px] text-gray-700 font-medium line-clamp-2">{{ $item->item_perbaikan ?: '-' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ route('pemeliharaan.pengajuan.cetak-dokumen', ['id' => $item->id, 'type' => 'permohonanbidang']) }}"
                               target="_blank"
                               class="inline-flex items-center justify-center gap-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-xs text-center">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                <span>Surat Bidang</span>
                            </a>
                            <button type="button" @click="openPengajuanModal({{ json_encode($item) }})"
                                    class="inline-flex items-center justify-center gap-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition border border-gray-200 text-center cursor-pointer">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Detail</span>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="p-3 sm:p-3.5 border-t border-gray-100 bg-gray-50/50">
                    {{ $pengajuanList->links('vendor.pagination.custom') }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===================== MODAL DETAIL UNIT ===================== --}}
    <div x-show="selectedUnit !== null" x-cloak
         class="fixed inset-0 z-50 overflow-hidden bg-black/50 flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl relative max-h-[90vh] flex flex-col overflow-hidden">
            <button type="button" @click="closeModals()"
                    class="absolute top-3.5 right-3.5 text-gray-400 hover:text-gray-600 p-1 cursor-pointer z-10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <template x-if="selectedUnit">
                <div class="flex flex-col h-full min-h-0">
                    {{-- HEADER (tidak scroll) --}}
                    <div class="flex-shrink-0 p-4 sm:p-6 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-extrabold text-blue-950 mb-1 pr-6" x-text="'Detail Pengecekan: ' + selectedUnit.unit_nama"></h3>
                        <p class="text-xs text-gray-500 mb-3.5" x-text="'Tanggal: ' + selectedUnit.tanggal_pemeriksaan + ' | Pos: ' + (selectedUnit.pos || '-')"></p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-xs bg-gray-50 p-3 sm:p-3.5 rounded-xl border border-gray-200">
                            <div><strong class="text-gray-500">Pemeriksa:</strong> <span class="font-bold text-gray-900" x-text="selectedUnit.nama_pemeriksa"></span></div>
                            <div><strong class="text-gray-500">Jabatan:</strong> <span class="text-gray-800" x-text="selectedUnit.jabatan"></span></div>
                            <div><strong class="text-gray-500">Danru:</strong> <span class="text-gray-800" x-text="selectedUnit.nama_danru || '-'"></span></div>
                            <div><strong class="text-gray-500">Kepala Bidang:</strong> <span class="text-gray-800" x-text="selectedUnit.nama_kabid || '-'"></span></div>
                            <div><strong class="text-gray-500">Kilometer:</strong> <span class="font-bold text-gray-900" x-text="selectedUnit.kilometer ? selectedUnit.kilometer + ' KM' : '-'"></span></div>
                            <div><strong class="text-gray-500">Kebersihan:</strong> <span class="font-bold capitalize" x-text="selectedUnit.kebersihan_unit"></span></div>
                            <div><strong class="text-gray-500">Kondisi Rusak:</strong> <span class="font-bold text-red-600" x-text="(selectedUnit.jumlah_rusak || 0) + ' item'"></span></div>
                        </div>
                    </div>

                    {{-- MIDDLE (scrollable) --}}
                    <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-6 space-y-4">
                        <div x-show="selectedUnit.bukti_pemanasan || selectedUnit.bukti_bbm || selectedUnit.bukti_pencucian || (selectedUnit.dokumentasi_tangki_pompa && selectedUnit.dokumentasi_tangki_pompa.length)">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-gray-700 mb-2">Bukti Pengecekan</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <template x-for="photo in [
                                    { label: 'Pemanasan', path: selectedUnit.bukti_pemanasan },
                                    { label: 'Level BBM', path: selectedUnit.bukti_bbm },
                                    { label: 'Pembersihan', path: selectedUnit.bukti_pencucian }
                                ]" :key="photo.label">
                                    <div x-show="photo.path" class="text-center">
                                        <div class="text-[10px] font-semibold text-gray-500 mb-1" x-text="photo.label"></div>
                                        <img :src="'/storage/' + photo.path" :alt="'Bukti ' + photo.label" class="w-full h-28 object-contain rounded-lg border border-gray-200 bg-gray-50 p-1">
                                    </div>
                                </template>
                                <template x-for="(path, index) in (selectedUnit.dokumentasi_tangki_pompa || [])" :key="'tangki-' + index">
                                    <div class="text-center">
                                        <div class="text-[10px] font-semibold text-gray-500 mb-1" x-text="'Tangki & Pompa ' + (index + 1)"></div>
                                        <img :src="'/storage/' + path" :alt="'Bukti tangki dan pompa ' + (index + 1)" class="w-full h-28 object-contain rounded-lg border border-gray-200 bg-gray-50 p-1">
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold text-xs uppercase tracking-wider text-gray-700 mb-2">Item Perlengkapan yang Rusak / Catatan</h4>
                            <div class="space-y-1.5 border border-gray-200 rounded-xl p-3 bg-white max-h-60 overflow-y-auto">
                                <template x-for="(val, key) in (selectedUnit.perlengkapan || {})" :key="key">
                                    <div class="flex items-center justify-between text-xs py-1.5 border-b border-gray-100 last:border-b-0 gap-2">
                                        <span class="font-medium text-gray-700" x-text="val.label || key"></span>
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            <span :class="val.status === 'rusak' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'"
                                                  class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" x-text="val.status || 'baik'"></span>
                                            <span class="text-gray-400 text-[10px]" x-show="val.catatan" x-text="'(' + val.catatan + ')'"></span>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </div>
                    </div>

                    {{-- FOOTER (tidak scroll) --}}
                    <div class="flex-shrink-0 flex flex-col-reverse sm:flex-row justify-end gap-2 p-4 sm:p-6 border-t border-gray-200">
                        <button type="button" @click="closeModals()"
                                class="w-full sm:w-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition cursor-pointer text-center">
                            Tutup
                        </button>
                        <a :href="'/unit-pemadam/cek-harian-unit/' + selectedUnit.id + '/export-pdf'"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs text-center">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Unduh PDF</span>
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ===================== MODAL DETAIL ALAT ===================== --}}
    <div x-show="selectedAlat !== null" x-cloak
         class="fixed inset-0 z-50 overflow-hidden bg-black/50 flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl relative max-h-[90vh] flex flex-col overflow-hidden">
            <button type="button" @click="closeModals()"
                    class="absolute top-3.5 right-3.5 text-gray-400 hover:text-gray-600 p-1 cursor-pointer z-10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <template x-if="selectedAlat">
                <div class="flex flex-col h-full min-h-0">
                    {{-- HEADER (tidak scroll) --}}
                    <div class="flex-shrink-0 p-4 sm:p-6 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-extrabold text-blue-950 mb-1 pr-6" x-text="'Detail Pengecekan Alat: ' + (selectedAlat.unit_nama || 'Peralatan')"></h3>
                        <p class="text-xs text-gray-500 mb-3.5" x-text="'Tanggal: ' + selectedAlat.tanggal_pemeriksaan + ' | Pos: ' + (selectedAlat.pos || '-')"></p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-xs bg-gray-50 p-3 sm:p-3.5 rounded-xl border border-gray-200">
                            <div><strong class="text-gray-500">Pemeriksa:</strong> <span class="font-bold text-gray-900" x-text="selectedAlat.nama_pemeriksa"></span></div>
                            <div><strong class="text-gray-500">Jabatan:</strong> <span class="text-gray-800" x-text="selectedAlat.jabatan"></span></div>
                            <div><strong class="text-gray-500">Danru:</strong> <span class="text-gray-800" x-text="selectedAlat.nama_danru || '-'"></span></div>
                            <div><strong class="text-gray-500">Kepala Bidang:</strong> <span class="text-gray-800" x-text="selectedAlat.nama_kabid || '-'"></span></div>
                            <div><strong class="text-gray-500">Total Baik:</strong> <span class="font-bold text-emerald-600" x-text="(selectedAlat.total_alat_baik || 0) + ' Unit'"></span></div>
                            <div><strong class="text-gray-500">Total Rusak:</strong> <span class="font-bold text-red-600" x-text="(selectedAlat.total_alat_rusak || 0) + ' Unit'"></span></div>
                        </div>
                    </div>

                    {{-- MIDDLE (scrollable) --}}
                    <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-6">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-gray-700 mb-2">Daftar Peralatan yang Diperiksa</h4>
                        <div class="space-y-1.5 border border-gray-200 rounded-xl p-3 bg-white">
                            <template x-for="(item, idx) in (selectedAlat.alat || [])" :key="idx">
                                <div class="flex items-center justify-between text-xs py-1.5 border-b border-gray-100 last:border-b-0 gap-2">
                                    <span class="font-medium text-gray-800" x-text="item.nama || ('Alat #' + item.id)"></span>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-emerald-700 font-bold" x-text="item.jumlah_baik + ' Baik'"></span>
                                        <span class="text-red-700 font-bold" x-show="item.jumlah_rusak > 0" x-text="item.jumlah_rusak + ' Rusak'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- New block: display uploaded photos for the alat check -->
                        <div x-show="selectedAlat.foto_umum && selectedAlat.foto_umum.length" class="mt-4">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-gray-700 mb-2">Foto Dokumentasi</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <template x-for="(path, idx) in selectedAlat.foto_umum" :key="idx">
                                    <div class="text-center">
                                        <img :src="'/storage/' + path" alt="Foto Dokumentasi" class="w-full h-28 object-contain rounded-lg border border-gray-200 bg-gray-50 p-1">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER (tidak scroll) --}}
                    <div class="flex-shrink-0 flex flex-col-reverse sm:flex-row justify-end gap-2 p-4 sm:p-6 border-t border-gray-200">
                        <button type="button" @click="closeModals()"
                                class="w-full sm:w-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition cursor-pointer text-center">
                            Tutup
                        </button>
                        <a :href="'/alat-pemadam/cek-harian-alat/' + selectedAlat.id + '/export-pdf'"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs text-center">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Unduh PDF</span>
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ===================== MODAL DETAIL PENGAJUAN ===================== --}}
    <div x-show="selectedPengajuan !== null" x-cloak
         class="fixed inset-0 z-50 overflow-hidden bg-black/50 flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl relative max-h-[90vh] flex flex-col overflow-hidden">
            <button type="button" @click="closeModals()"
                    class="absolute top-3.5 right-3.5 text-gray-400 hover:text-gray-600 p-1 cursor-pointer z-10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <template x-if="selectedPengajuan">
                <div class="flex flex-col h-full min-h-0">
                    {{-- HEADER (tidak scroll) --}}
                    <div class="flex-shrink-0 p-4 sm:p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between pr-6 mb-1">
                            <span class="text-[11px] font-mono font-bold text-gray-400" x-text="selectedPengajuan.kode_verifikasi || '-'"></span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                  :class="{
                                      'bg-emerald-100 text-emerald-800 border border-emerald-200': selectedPengajuan.status === 'disetujui',
                                      'bg-red-100 text-red-800 border border-red-200': selectedPengajuan.status === 'ditolak',
                                      'bg-amber-100 text-amber-800 border border-amber-200': selectedPengajuan.status !== 'disetujui' && selectedPengajuan.status !== 'ditolak'
                                  }"
                                  x-text="selectedPengajuan.status === 'disetujui' ? 'Disetujui' : (selectedPengajuan.status === 'ditolak' ? 'Ditolak' : 'Menunggu Verifikasi')">
                            </span>
                        </div>
                        <h3 class="text-base sm:text-lg font-extrabold text-blue-950 mb-1" x-text="'Pengajuan Pemeliharaan: ' + (selectedPengajuan.nomor_lambung || 'Unit')"></h3>
                        <p class="text-xs text-gray-500 mb-3.5" x-text="'Pos: ' + (selectedPengajuan.pos || '-') + ' (' + (selectedPengajuan.regu || 'Regu 1') + ') | Bidang: ' + (selectedPengajuan.bidang || '-')"></p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-xs bg-gray-50 p-3 sm:p-3.5 rounded-xl border border-gray-200">
                            <div><strong class="text-gray-500">Pemegang Unit:</strong> <span class="font-bold text-gray-900" x-text="selectedPengajuan.nama_pemegang"></span></div>
                            <div><strong class="text-gray-500">NIP Pemegang:</strong> <span class="text-gray-800" x-text="selectedPengajuan.nip_pemegang || '-'"></span></div>
                            <div><strong class="text-gray-500">Komandan Regu:</strong> <span class="text-gray-800" x-text="selectedPengajuan.nama_komandan_regu || '-'"></span></div>
                            <div><strong class="text-gray-500">Kepala Bidang:</strong> <span class="text-gray-800" x-text="selectedPengajuan.nama_kepala_bidang || '-'"></span></div>
                            <div><strong class="text-gray-500">Jenis Kendaraan:</strong> <span class="font-bold text-gray-900" x-text="selectedPengajuan.jenis_kendaraan || '-'"></span></div>
                            <div><strong class="text-gray-500">Tanggal Pengajuan:</strong> <span class="font-bold text-gray-900" x-text="selectedPengajuan.created_at ? new Date(selectedPengajuan.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'}) : '-'"></span></div>
                        </div>
                    </div>

                    {{-- MIDDLE (scrollable) --}}
                    <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-6 space-y-4">
                        {{-- Status Pengerjaan Bengkel --}}
                        <div x-show="selectedPengajuan.status === 'disetujui'"
                             class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-blue-950 flex items-center gap-1.5">
                                    <i data-lucide="wrench" class="w-3.5 h-3.5 text-blue-600"></i>
                                    Status Pengerjaan Bengkel
                                </span>
                                <span class="font-extrabold text-[10px] px-2 py-0.5 rounded-full"
                                      :class="{
                                          'bg-emerald-100 text-emerald-800': selectedPengajuan.status_pengerjaan === 'selesai',
                                          'bg-orange-100 text-orange-800': selectedPengajuan.status_pengerjaan === 'proses' || (selectedPengajuan.tanggal_keberangkatan && selectedPengajuan.status_pengerjaan !== 'selesai'),
                                          'bg-gray-100 text-gray-700': !selectedPengajuan.status_pengerjaan || selectedPengajuan.status_pengerjaan === 'belum_mulai'
                                      }"
                                      x-text="selectedPengajuan.status_pengerjaan === 'selesai' ? 'Selesai Pengerjaan' : (selectedPengajuan.status_pengerjaan === 'proses' || selectedPengajuan.tanggal_keberangkatan ? 'Sedang Dikerjakan' : 'Menunggu Pengerjaan')">
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-[11px] text-gray-600 pt-1">
                                <div x-show="selectedPengajuan.tanggal_keberangkatan">
                                    <span>Tgl Masuk Bengkel:</span>
                                    <strong class="text-gray-900 block" x-text="selectedPengajuan.tanggal_keberangkatan"></strong>
                                </div>
                                <div x-show="selectedPengajuan.tanggal_selesai_pengerjaan">
                                    <span>Tgl Selesai:</span>
                                    <strong class="text-gray-900 block" x-text="selectedPengajuan.tanggal_selesai_pengerjaan"></strong>
                                </div>
                            </div>
                        </div>

                        {{-- Item Perbaikan --}}
                        <div>
                            <h4 class="font-bold text-xs uppercase tracking-wider text-gray-700 mb-2">Rincian Item Perbaikan</h4>
                            
                            {{-- Jika ada verifikasi per item dari admin --}}
                            <template x-if="selectedPengajuan.item_verifikasis && Object.keys(selectedPengajuan.item_verifikasis).length > 0">
                                <div class="space-y-1.5 border border-gray-200 rounded-xl p-3 bg-white">
                                    <template x-for="(st, nm) in selectedPengajuan.item_verifikasis" :key="nm">
                                        <div class="flex items-center justify-between text-xs py-1.5 border-b border-gray-100 last:border-b-0 gap-2">
                                            <span class="font-medium text-gray-800" x-text="nm"></span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                                                  :class="st === 'disetujui' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                                                  x-text="st === 'disetujui' ? 'Disetujui ✓' : 'Ditolak ✕'">
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Jika belum ada verifikasi per item, tampilkan dari verified_item_list atau string item_perbaikan --}}
                            <template x-if="!selectedPengajuan.item_verifikasis || Object.keys(selectedPengajuan.item_verifikasis).length === 0">
                                <div class="space-y-1.5 border border-gray-200 rounded-xl p-3 bg-white">
                                    <template x-if="selectedPengajuan.verified_item_list && selectedPengajuan.verified_item_list.length > 0">
                                        <div>
                                            <template x-for="(it, idx) in selectedPengajuan.verified_item_list" :key="idx">
                                                <div class="flex items-center justify-between text-xs py-1.5 border-b border-gray-100 last:border-b-0 gap-2">
                                                    <span class="font-medium text-gray-800" x-text="it"></span>
                                                    <span class="text-[10px] text-gray-400 italic">Menunggu Verifikasi</span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!selectedPengajuan.verified_item_list || selectedPengajuan.verified_item_list.length === 0">
                                        <p class="text-xs text-gray-700 font-medium" x-text="selectedPengajuan.item_perbaikan || '-'"></p>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Catatan Admin --}}
                        <div x-show="selectedPengajuan.catatan_admin">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-gray-700 mb-1.5">Catatan Verifikasi Admin</h4>
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 italic"
                                 x-text="selectedPengajuan.catatan_admin">
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER (tidak scroll) --}}
                    <div class="flex-shrink-0 flex flex-col-reverse sm:flex-row justify-end gap-2 p-4 sm:p-6 border-t border-gray-200">
                        <button type="button" @click="closeModals()"
                                class="w-full sm:w-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition cursor-pointer text-center">
                            Tutup
                        </button>
                        <a :href="'/pemeliharaan/pengajuan/' + selectedPengajuan.id + '/cetak-dokumen/permohonanbidang'"
                           target="_blank"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition shadow-xs text-center">
                            <i data-lucide="printer" class="w-4 h-4"></i>
                            <span>Cetak Surat Permohonan Bidang</span>
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
