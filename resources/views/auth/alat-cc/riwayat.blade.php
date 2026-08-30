@extends('layouts.app')
@section('title', 'Riwayat Pengecekan — Command Center')

@section('content')
<div class="max-w-7xl mx-auto p-3.5 sm:p-6" x-data="{
    selectedAlat: null,
    openAlatModal(item) {
        this.selectedAlat = item;
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },
    closeModals() {
        this.selectedAlat = null;
    }
}" x-effect="document.body.classList.toggle('modal-open', selectedAlat !== null)">

    {{-- Header Page --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-5">
        <div>
            <h1 class="text-lg sm:text-2xl font-extrabold text-blue-950 flex items-center gap-2">
                <i data-lucide="history" class="w-5 h-5 sm:w-6 sm:h-6 text-blue-800 flex-shrink-0"></i>
                <span>Riwayat Pengecekan Command Center</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Daftar rekapan hasil pemeriksaan harian peralatan dan fasilitas Command Center.</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('alat-cc.cek-alat-cc') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-900 hover:bg-blue-950 text-white text-xs font-bold transition shadow-xs text-center">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                <span>Cek Alat Baru</span>
            </a>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-200 shadow-2xs mb-4">
        <form method="GET" action="{{ route('alat-cc.riwayat') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 sm:gap-3 items-center">
            
            <div class="sm:col-span-5 relative">
                <input type="text" name="search" value="{{ $searchQuery ?? '' }}"
                       placeholder="Cari Pemeriksa, Pos, Danru, Kabid..."
                       class="w-full pl-8 sm:pl-9 pr-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-800 bg-white">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2"></i>
            </div>

            <div class="sm:col-span-4 relative">
                <input type="date" name="tanggal" value="{{ $tanggal ?? '' }}"
                       class="w-full px-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-800 bg-white text-gray-700">
            </div>

            <div class="sm:col-span-3 flex items-center gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-950 hover:bg-blue-900 text-white text-xs font-bold rounded-lg transition shadow-2xs cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                @if(!empty($searchQuery) || !empty($tanggal))
                    <a href="{{ route('alat-cc.riwayat') }}"
                       class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg transition border border-gray-200 text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ===================== TABEL & MOBILE CARDS PERALATAN ===================== --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
        @if($cekAlatList->isEmpty())
            <div class="p-10 sm:p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-50 border border-gray-200 flex items-center justify-center mx-auto mb-3 text-gray-400">
                    <i data-lucide="inbox" class="w-6 h-6"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-800">Belum Ada Riwayat Pengecekan Alat Command Center</h3>
                <p class="text-xs text-gray-500 mt-1">Data pengecekan alat Command Center yang disimpan akan muncul di sini.</p>
            </div>
        @else
            {{-- DESKTOP & TABLET TABLE VIEW (Hidden on Mobile) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-3.5 text-center w-12">No</th>
                            <th class="py-3 px-3.5">Tanggal</th>
                            <th class="py-3 px-3.5">Pos / Lokasi</th>
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
                                <span class="font-bold text-blue-950 block">{{ $item->pos ?? 'Command Center' }}</span>
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
                                    <a href="{{ route('alat-cc.cek-alat-cc.export-pdf', $item->id) }}"
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
                            <span class="text-[10px] text-gray-500 font-medium">{{ $item->pos ?? 'Command Center' }}</span>
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
                        <a href="{{ route('alat-cc.cek-alat-cc.export-pdf', $item->id) }}"
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

    {{-- ===================== MODAL DETAIL ALAT ===================== --}}
    <div x-show="selectedAlat !== null" x-cloak
         class="fixed inset-0 z-50 overflow-hidden bg-black/50 flex items-center justify-center p-3 sm:p-4">
        <div @click.away="closeModals()"
             class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl relative max-h-[90vh] flex flex-col overflow-hidden">
            <button type="button" @click="closeModals()"
                    class="absolute top-3.5 right-3.5 text-gray-400 hover:text-gray-600 p-1 cursor-pointer z-10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <template x-if="selectedAlat">
                <div class="flex flex-col min-h-0 max-h-[90vh]">
                    {{-- HEADER (tidak scroll) --}}
                    <div class="flex-shrink-0 p-4 sm:p-6 border-b border-gray-200">
                        <h3 class="text-base sm:text-lg font-extrabold text-blue-950 mb-1 pr-6" x-text="'Detail Pengecekan Alat Command Center'"></h3>
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
                        <div class="space-y-1.5 border border-gray-200 rounded-xl p-3 bg-white max-h-[40vh] overflow-y-auto">
                            <template x-for="(item, idx) in (selectedAlat.alat || [])" :key="idx">
                                <div class="flex items-start justify-between text-xs py-1.5 border-b border-gray-100 last:border-b-0 gap-2">
                                    <span class="font-medium text-gray-800 break-words min-w-0 flex-1" x-text="item.nama || ('Alat #' + item.id)"></span>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <span class="text-emerald-700 font-bold whitespace-nowrap" x-text="item.jumlah_baik + ' Baik'"></span>
                                        <span class="text-red-700 font-bold whitespace-nowrap" x-show="item.jumlah_rusak > 0" x-text="item.jumlah_rusak + ' Rusak'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- FOOTER (tidak scroll) --}}
                    <div class="flex-shrink-0 flex flex-col-reverse sm:flex-row justify-end gap-2 p-4 sm:p-6 border-t border-gray-200">
                        <button type="button" @click="closeModals()"
                                class="w-full sm:w-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition cursor-pointer text-center">
                            Tutup
                        </button>
                        <a :href="'/alat-cc/cek-alat-cc/' + selectedAlat.id + '/export-pdf'"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs text-center">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Unduh PDF</span>
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
