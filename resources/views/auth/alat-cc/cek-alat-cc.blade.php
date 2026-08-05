
<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Harian Alat Command Center </title>

    {{-- Tailwind CSS (ganti dengan build asset Laravel Mix/Vite di project asli) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Alpine.js untuk toggle sidebar responsive --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">


@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 max-w-5xl mx-auto">

    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Cek Harian Alat Command Center</h1>
    <p class="text-gray-500 text-sm mt-1 mb-6">
        Pemeriksaan kondisi dan kelengkapan alat Command Center.
    </p>

    <form action="{{ route('alat-cc.cek-alat-cc') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Baris 1: Nama Pemeriksa & Jabatan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="nama_pemeriksa" class="block text-sm font-medium mb-1">Nama Pemeriksa</label>
                <input type="text" id="nama_pemeriksa" name="nama_pemeriksa"
                       value="{{ old('nama_pemeriksa') }}"
                       placeholder="Masukkan nama pemeriksa"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('nama_pemeriksa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="jabatan" class="block text-sm font-medium mb-1">Jabatan</label>
                <input type="text" id="jabatan" name="jabatan"
                       value="{{ old('jabatan') }}"
                       placeholder="Masukkan jabatan"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('jabatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Baris 2: Unit/Kendaraan & Tanggal Pemeriksaan (fitur Shift dihilangkan) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="unit_id" class="block text-sm font-medium mb-1">Regu</label>
                <select id="unit_id" name="unit_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="" selected disabled>Pilih Regu</option>
                    @foreach($unitList ?? [] as $unit)
                        <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>
                            {{ $unit->nama }}
                        </option>
                    @endforeach
                </select>
                @error('unit_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="tanggal_pemeriksaan" class="block text-sm font-medium mb-1">Tanggal Pemeriksaan</label>
                <input type="date" id="tanggal_pemeriksaan" name="tanggal_pemeriksaan"
                       value="{{ old('tanggal_pemeriksaan', date('Y-m-d')) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                @error('tanggal_pemeriksaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Daftar Pemeriksaan Alat --}}
        <div class="pt-2">
            <h2 class="text-base font-semibold border-b border-gray-200 pb-2 mb-4">Daftar Pemeriksaan Alat</h2>

            <div class="space-y-4">
                @foreach(($daftarAlat ?? []) as $index => $alat)
                    <div class="border border-gray-200 rounded-xl p-4 sm:p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="flex-shrink-0 w-8 h-8 rounded-md bg-red-700 text-white text-xs font-bold flex items-center justify-center">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <h3 class="font-semibold text-gray-900">{{ $alat->nama }}</h3>
                            <input type="hidden" name="alat[{{ $index }}][id]" value="{{ $alat->id }}">
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            {{-- Status Kondisi --}}
                            <div>
                                <label class="block text-sm font-medium mb-1">Status Kondisi</label>
                                <select name="alat[{{ $index }}][status]"
                                        class="status-select w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                                    <option value="baik" @selected(($alat->status ?? 'baik') === 'baik')>Baik</option>
                                    <option value="rusak" @selected(($alat->status ?? '') === 'rusak')>Rusak</option>
                                </select>
                            </div>

                            {{-- Keterangan --}}
                            <div>
                                <label class="block text-sm font-medium mb-1">Keterangan</label>
                                <textarea name="alat[{{ $index }}][keterangan]" rows="3"
                                          placeholder="Tuliskan kondisi alat (jika ada catatan)..."
                                          class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-600">{{ old("alat.$index.keterangan") }}</textarea>
                            </div>

                            {{-- Foto Kondisi Alat --}}
                            <div>
                                <label class="block text-sm font-medium mb-1">Foto Kondisi Alat</label>
                                <label for="foto_{{ $index }}"
                                       class="flex flex-col items-center justify-center h-[86px] border-2 border-dashed border-gray-300 rounded-lg cursor-pointer text-center hover:border-blue-500 transition-colors">
                                    <span class="text-blue-600 text-lg leading-none">📷</span>
                                    <span class="text-xs text-blue-700 font-medium mt-1">+ Tambahkan Foto</span>
                                    <span class="text-[11px] text-gray-400">JPG, PNG maks. 2MB</span>
                                </label>
                                <input id="foto_{{ $index }}" type="file" name="alat[{{ $index }}][foto]"
                                       accept="image/jpeg,image/png" class="hidden">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="send" class="w-4 h-4"></i> Kirim Pemeriksaan
            </button>
        </div>

    </form>
</div>
@endsection
