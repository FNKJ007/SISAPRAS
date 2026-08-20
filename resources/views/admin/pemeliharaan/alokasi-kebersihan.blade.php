@extends('layouts.admin')

@section('title', 'Alokasi Peralatan Kebersihan Kendaraan')

@section('content')
<div class="space-y-6" x-data="{ editModal: false, selectedItem: {} }">

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0">✓</div>
                <div>
                    <h4 class="font-bold text-sm">Berhasil!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Header Banner --}}
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wide">
                    Internal SPI &amp; Kasi Pemeliharaan
                </span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs font-semibold text-gray-500">Tahun Anggaran {{ $tahun }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-6 h-6 text-blue-600"></i>
                <span>Alokasi Peralatan Kebersihan Kendaraan</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Pencatatan dan monitoring alokasi peralatan pencucian/kebersihan rutin per unit kendaraan armada Disdamkar.
            </p>
        </div>

        {{-- Filters (Tahun & Search) --}}
        <form method="GET" action="{{ route('admin.pemeliharaan.alokasi-kebersihan.index') }}" class="flex items-center gap-2 w-full md:w-auto flex-wrap">
            <select name="tahun" onchange="this.form.submit()" class="rounded-xl border border-gray-300 px-3 py-2 text-xs font-semibold bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                @foreach($tahunTersedia as $t)
                    <option value="{{ $t }}" @selected($tahun == $t)>Tahun {{ $t }}</option>
                @endforeach
            </select>
            <div class="relative flex-1 sm:w-64">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari unit / posko..."
                       class="w-full rounded-xl border border-gray-300 pl-8 pr-3 py-2 text-xs bg-white focus:ring-2 focus:ring-blue-600 focus:outline-none">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5"></i>
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition">
                Filter
            </button>
        </form>
    </div>

    {{-- Info Alert 6 Item Kebersihan --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @php
            $itemsSummary = [
                ['Sabun Cuci Unit', '🧴', '12 Botol / Unit'],
                ['Lap Handuk Unit', '🧻', '4 Pcs / Unit'],
                ['Lap Kanebo', '🧽', '6 Pcs / Unit'],
                ['Semir Ban', '✨', '6 Kaleng / Unit'],
                ['Sikat / Kuas Ban', '🧹', '2 Pcs / Unit'],
                ['Pengharum Mobil', '🌸', '12 Pcs / Unit'],
            ];
        @endphp
        @foreach($itemsSummary as $itemSum)
            <div class="bg-white border border-gray-200/80 rounded-xl p-3 shadow-xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center text-base shrink-0">
                    {{ $itemSum[1] }}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-gray-900 truncate">{{ $itemSum[0] }}</div>
                    <div class="text-[11px] text-gray-500 font-medium">{{ $itemSum[2] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Main Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
            <span class="text-sm font-bold text-gray-900">Daftar Alokasi per Unit Kendaraan (Total {{ $alokasiList->count() }} Unit)</span>
            <span class="text-xs text-gray-500">Klik tombol <strong>Edit</strong> untuk menyesuaikan alokasi unit.</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase text-[11px]">
                    <tr>
                        <th class="p-3.5">Unit Armada</th>
                        <th class="p-3.5">Posko</th>
                        <th class="p-3.5">Sabun Cuci</th>
                        <th class="p-3.5">Lap Handuk</th>
                        <th class="p-3.5">Kanebo</th>
                        <th class="p-3.5">Semir Ban</th>
                        <th class="p-3.5">Sikat Ban</th>
                        <th class="p-3.5">Pengharum</th>
                        <th class="p-3.5">Catatan</th>
                        <th class="p-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-800">
                    @forelse($alokasiList as $alok)
                        <tr class="hover:bg-blue-50/40 transition">
                            <td class="p-3.5 font-bold text-gray-900">
                                <span class="block text-xs font-extrabold text-blue-900">{{ strtoupper($alok->unit->nomor_lambung ?? '—') }}</span>
                                <span class="text-[11px] text-gray-500 font-normal">{{ $alok->unit->plat_nomor ? $alok->unit->plat_nomor . ' • ' : '' }}{{ $alok->unit->merk_tipe ?? '' }}</span>
                            </td>
                            <td class="p-3.5 font-medium text-gray-600">
                                {{ $alok->unit->pos ?? '—' }}
                            </td>
                            <td class="p-3.5 font-semibold text-emerald-800">{{ $alok->sabun_cuci ?? '—' }}</td>
                            <td class="p-3.5 font-semibold text-blue-800">{{ $alok->lap_handuk ?? '—' }}</td>
                            <td class="p-3.5 font-semibold text-amber-800">{{ $alok->kanebo ?? '—' }}</td>
                            <td class="p-3.5 font-semibold text-indigo-800">{{ $alok->semir_ban ?? '—' }}</td>
                            <td class="p-3.5 font-semibold text-teal-800">{{ $alok->sikat_ban ?? '—' }}</td>
                            <td class="p-3.5 font-semibold text-purple-800">{{ $alok->pengharum ?? '—' }}</td>
                            <td class="p-3.5 text-[11px] text-gray-500 max-w-[140px] truncate" title="{{ $alok->catatan }}">{{ $alok->catatan ?? '—' }}</td>
                            <td class="p-3.5 text-center">
                                <button type="button"
                                        @click="selectedItem = {{ json_encode($alok) }}; selectedItem.unit_nomor = '{{ $alok->unit->nomor_lambung }}'; editModal = true;"
                                        class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 font-bold hover:bg-blue-100 transition border border-blue-200">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-6 text-center text-gray-400">Tidak ada data unit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="editModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-gray-100" @click.away="editModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h3 class="font-bold text-base text-gray-900">
                    Edit Alokasi Peralatan: <span class="text-blue-700" x-text="selectedItem.unit_nomor"></span>
                </h3>
                <button type="button" @click="editModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
            </div>

            <form :action="'{{ url('admin/pemeliharaan/alokasi-kebersihan') }}/' + selectedItem.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Sabun Cuci Unit</label>
                        <input type="text" name="sabun_cuci" x-model="selectedItem.sabun_cuci" class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Lap Handuk Unit</label>
                        <input type="text" name="lap_handuk" x-model="selectedItem.lap_handuk" class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Lap Kanebo</label>
                        <input type="text" name="kanebo" x-model="selectedItem.kanebo" class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Semir Ban</label>
                        <input type="text" name="semir_ban" x-model="selectedItem.semir_ban" class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Sikat / Kuas Ban</label>
                        <input type="text" name="sikat_ban" x-model="selectedItem.sikat_ban" class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Pengharum Mobil</label>
                        <input type="text" name="pengharum" x-model="selectedItem.pengharum" class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>

                <div class="text-xs">
                    <label class="block font-bold text-gray-700 mb-1">Catatan Tambahan SPI</label>
                    <textarea name="catatan" x-model="selectedItem.catatan" rows="2" class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-blue-600"></textarea>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 font-bold text-xs hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 shadow-xs">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection