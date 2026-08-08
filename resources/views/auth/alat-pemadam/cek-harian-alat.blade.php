@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 max-w-5xl mx-auto">

    {{-- Flash Message Success --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">✓</div>
                <div>
                    <h4 class="font-bold text-sm">Pemeriksaan Alat Berhasil Disimpan!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Cek Harian Alat Pemadam</h1>
    <p class="text-gray-500 text-sm mt-1 mb-6">
        Pemeriksaan kondisi dan kelengkapan alat pemadam kebakaran.
    </p>

    <form action="{{ route('alat-pemadam.cek-harian-alat.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Baris 1: Nama Pemeriksa & Jabatan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="nama_pemeriksa" class="block text-sm font-medium mb-1">Nama Pemeriksa <span class="text-red-500">*</span></label>
                <input type="text" id="nama_pemeriksa" name="nama_pemeriksa"
                       value="{{ old('nama_pemeriksa', auth()->user()->name ?? '') }}" required
                       placeholder="Masukkan nama pemeriksa"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('nama_pemeriksa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="jabatan" class="block text-sm font-medium mb-1">Jabatan <span class="text-red-500">*</span></label>
                <input type="text" id="jabatan" name="jabatan"
                       value="{{ old('jabatan', 'Petugas Regu') }}" required
                       placeholder="Masukkan jabatan"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('jabatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Baris 2: Unit/Kendaraan & Tanggal Pemeriksaan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="unit_id" class="block text-sm font-medium mb-1">Unit / Kendaraan</label>
                <select id="unit_id" name="unit_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="" selected disabled>Pilih Unit / Kendaraan</option>
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

            <div class="space-y-3">
                @foreach(($daftarAlat ?? []) as $index => $alat)
                    @php
                        $baikLama = old('alat.' . $index . '.jumlah_baik', $alat->jumlah_baik ?? 0);
                        $rusakLama = old('alat.' . $index . '.jumlah_rusak', $alat->jumlah_rusak ?? 0);
                        $nomorRusakLama = old('alat.' . $index . '.nomor_rusak');
                    @endphp
                    <div class="border border-gray-200 rounded-xl p-4 sm:p-5"
                         x-data="{ jumlahBaik: {{ $baikLama }}, jumlahRusak: {{ $rusakLama }} }">

                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
                            <div class="flex items-center gap-3 flex-1">
                                <span class="flex-shrink-0 w-8 h-8 rounded-md bg-red-700 text-white text-xs font-bold flex items-center justify-center">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base">{{ $alat->nama }}</h3>
                                <input type="hidden" name="alat[{{ $index }}][id]" value="{{ $alat->id }}">
                            </div>

                            {{-- Input Jumlah Baik & Rusak --}}
                            <div class="flex items-center gap-3 sm:gap-4 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <label for="baik_{{ $index }}" class="text-xs font-bold text-green-700 uppercase tracking-wide">Baik:</label>
                                    <input type="number" id="baik_{{ $index }}" name="alat[{{ $index }}][jumlah_baik]"
                                           x-model.number="jumlahBaik" min="0" placeholder="0"
                                           class="w-20 rounded-lg border border-green-300 px-3 py-1.5 text-sm font-semibold text-green-800 bg-green-50/60 focus:outline-none focus:ring-2 focus:ring-green-600">
                                </div>

                                <div class="flex items-center gap-2">
                                    <label for="rusak_{{ $index }}" class="text-xs font-bold text-red-700 uppercase tracking-wide">Rusak:</label>
                                    <input type="number" id="rusak_{{ $index }}" name="alat[{{ $index }}][jumlah_rusak]"
                                           x-model.number="jumlahRusak" min="0" placeholder="0"
                                           class="w-20 rounded-lg border border-red-300 px-3 py-1.5 text-sm font-semibold text-red-800 bg-red-50/60 focus:outline-none focus:ring-2 focus:ring-red-600">
                                </div>
                            </div>
                        </div>

                        {{-- Muncul otomatis jika ada alat yang rusak (jumlahRusak > 0) --}}
                        <div class="mt-3 pt-3 border-t border-gray-100" x-show="jumlahRusak > 0" x-cloak>
                            <label class="block text-xs font-semibold text-red-800 mb-1">Nomor / Keterangan Alat yang Rusak</label>
                            <input type="text" name="alat[{{ $index }}][nomor_rusak]"
                                   value="{{ $nomorRusakLama }}"
                                   placeholder="Contoh: No. 2, No. 5"
                                   class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 bg-red-50/30">
                            <p class="text-[11px] text-gray-400 mt-1">Sebutkan nomor urut atau keterangan spesifik barang yang rusak.</p>
                            @error('alat.' . $index . '.nomor_rusak')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @error('alat.' . $index . '.jumlah_baik')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                            @error('alat.' . $index . '.jumlah_rusak')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Catatan & Foto Umum (untuk keseluruhan pemeriksaan, bukan per-alat) --}}
        <div class="pt-2">
            <h2 class="text-base font-semibold border-b border-gray-200 pb-2 mb-4">Catatan &amp; Dokumentasi</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="catatan_umum" class="block text-sm font-medium mb-1">Catatan Umum</label>
                    <textarea id="catatan_umum" name="catatan_umum" rows="4"
                              placeholder="Tuliskan catatan keseluruhan pemeriksaan (jika ada)..."
                              class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-600">{{ old('catatan_umum') }}</textarea>
                    @error('catatan_umum') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Foto Dokumentasi</label>
                    <label for="foto_umum" id="fotoUmumLabel"
                           class="flex flex-col items-center justify-center h-[110px] border-2 border-dashed border-gray-300 rounded-lg cursor-pointer text-center hover:border-blue-500 transition-colors">
                        <span class="text-blue-600 text-lg leading-none">📷</span>
                        <span class="text-xs text-blue-700 font-medium mt-1" id="fotoUmumText">+ Tambahkan Foto</span>
                        <span class="text-[11px] text-gray-400">JPG, PNG maks. 2MB</span>
                    </label>
                    <input id="foto_umum" type="file" name="foto_umum"
                           accept="image/jpeg,image/png" class="hidden">
                    <div id="fotoUmumPreview" class="mt-2.5 flex flex-wrap gap-2.5 hidden"></div>
                    @error('foto_umum') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Tombol Kirim --}}
        <div class="flex justify-end pt-4 border-t border-gray-200">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="send" class="w-4 h-4"></i> Simpan Pemeriksaan Alat
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
(function () {
    var input = document.getElementById('foto_umum');
    var labelText = document.getElementById('fotoUmumText');
    var previewEl = document.getElementById('fotoUmumPreview');
    var labelBox = document.getElementById('fotoUmumLabel');

    if (!input || !labelText) return;

    input.addEventListener('change', function () {
        if (previewEl) previewEl.innerHTML = '';

        if (input.files.length === 0) {
            labelText.textContent = '+ Tambahkan Foto';
            labelBox.classList.remove('border-emerald-500', 'bg-emerald-50/50');
            if (previewEl) previewEl.classList.add('hidden');
            return;
        }

        labelBox.classList.add('border-emerald-500', 'bg-emerald-50/50');
        labelText.textContent = '✓ ' + input.files[0].name;

        if (previewEl && input.files[0].type.startsWith('image/')) {
            previewEl.classList.remove('hidden');
            var reader = new FileReader();
            reader.onload = function (e) {
                var item = document.createElement('div');
                item.className = 'relative border border-emerald-300 rounded-lg p-1.5 bg-emerald-50/30 flex items-center gap-2.5 shadow-2xs';
                item.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" class="w-12 h-12 object-cover rounded-md border border-emerald-200">
                    <div>
                        <span class="block text-xs font-bold text-emerald-900 truncate max-w-[160px]">${input.files[0].name}</span>
                        <span class="block text-[10px] text-emerald-700 font-semibold">${(input.files[0].size / 1024).toFixed(1)} KB · Foto Terpilih ✓</span>
                    </div>
                `;
                previewEl.appendChild(item);
            };
            reader.readAsDataURL(input.files[0]);
        }
    });
})();
</script>
@endpush
@endsection