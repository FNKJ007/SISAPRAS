@extends('layouts.app')

@section('content')
<div class="daily-tool-page bg-white rounded-xl shadow-sm p-4 sm:p-6 max-w-5xl mx-auto" style="--tool-accent:#1d4ed8;--tool-soft:#eff6ff">

    {{-- Flash Message Success --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">✓</div>
                <div>
                    <h4 class="font-bold text-sm">Pemeriksaan Alat Pencegahan Berhasil Disimpan!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            @if(session('cek_id'))
                <a href="{{ route('alat-pencegahan.cek-harian-alat.export-pdf', session('cek_id')) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v8.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V4a1 1 0 011-1zM4 15a1 1 0 011 1v1h10v-1a1 1 0 112 0v1a2 2 0 01-2 2H5a2 2 0 01-2-2v-1a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Unduh PDF
                </a>
            @endif
        </div>
    @endif

    {{-- Flash Message Error --}}
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3 shadow-xs">
            <div class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center font-bold text-sm shrink-0">!</div>
            <div>
                <h4 class="font-bold text-sm">Gagal Membuat PDF</h4>
                <p class="text-xs text-red-700 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Cek Harian Alat Pencegahan</h1>
    <p class="text-gray-500 text-sm mt-1 mb-6">
        Pemeriksaan kondisi dan kelengkapan alat pencegahan.
    </p>

    <form action="{{ route('alat-pencegahan.cek-harian-alat.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Identitas Pemeriksaan: Pos Damkar, Nama Pemeriksa, Jabatan, Tanggal, Danru, Kabid --}}
        <div class="daily-tool-identity rounded-xl p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label for="pos" class="block text-sm font-medium mb-1">Pos Damkar <span class="text-red-500">*</span></label>
                <select id="pos" name="pos" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="" selected disabled>Pilih Pos Damkar</option>
                    @foreach($posList ?? [] as $p)
                        <option value="{{ $p->nama }}" @selected(old('pos', auth()->user()->pos ?? '') == $p->nama)>{{ $p->nama }}</option>
                    @endforeach
                </select>
                @error('pos') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="nama_pemeriksa" class="block text-sm font-medium mb-1">Nama Pemeriksa <span class="text-red-500">*</span></label>
                <input type="text" id="nama_pemeriksa" name="nama_pemeriksa"
                       value="{{ old('nama_pemeriksa', auth()->user()->name ?? '') }}"
                       placeholder="Masukkan nama pemeriksa" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                @error('nama_pemeriksa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="jabatan" class="block text-sm font-medium mb-1">Jabatan <span class="text-red-500">*</span></label>
                <input type="text" id="jabatan" name="jabatan"
                       value="{{ old('jabatan', auth()->user()->jabatan ?? 'Petugas Regu') }}" required
                       placeholder="Masukkan jabatan"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                @error('jabatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="tanggal_pemeriksaan" class="block text-sm font-medium mb-1">Tanggal Pemeriksaan <span class="text-red-500">*</span></label>
                <input type="date" id="tanggal_pemeriksaan" name="tanggal_pemeriksaan" required
                       value="{{ old('tanggal_pemeriksaan', date('Y-m-d')) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                @error('tanggal_pemeriksaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Nama Komandan Regu (Danru) --}}
            <div x-data="comboboxDanru()" style="position:relative;">
                <label for="nama_danru" class="block text-sm font-medium mb-1">Nama Komandan Regu (Danru) <span class="text-red-500">*</span></label>
                <div style="position:relative;">
                    <input type="text" id="nama_danru" name="nama_danru"
                           x-model="searchQuery"
                           @focus="open = true"
                           @input="open = true"
                           placeholder="Ketik atau pilih nama Danru..."
                           autocomplete="off"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                    <button type="button" @click.stop="open = !open" tabindex="-1"
                            style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748B; cursor:pointer; padding:4px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>

                {{-- Floating Dropdown Suggestion List --}}
                <div x-show="open && filteredList().length > 0"
                     x-cloak
                     @click.outside="open = false"
                     style="position:absolute; left:0; right:0; top:100%; margin-top:4px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; box-shadow:0 10px 25px rgba(15,23,42,0.15); max-height:200px; overflow-y:auto; z-index:99999;">
                    <template x-for="item in filteredList()" :key="item.name">
                        <div @click="selectItem(item)"
                             style="padding:8px 12px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                             onmouseover="this.style.background='#EFF6FF'"
                             onmouseout="this.style.background='transparent'">
                            <div>
                                <strong style="display:block; color:#0F172A; font-size:12.5px;" x-text="item.name"></strong>
                                <span style="font-size:11px; color:#64748B;" x-text="item.pos ? 'Pos ' + item.pos : (item.bidang || '')"></span>
                            </div>
                            <span x-show="item.jabatan"
                                  style="font-size:10px; font-weight:700; color:#1E40AF; background:#DBEAFE; padding:2px 6px; border-radius:8px;"
                                  x-text="item.jabatan"></span>
                        </div>
                    </template>
                </div>
                @error('nama_danru') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Nama Kepala Bidang --}}
            <div x-data="comboboxKabid()" style="position:relative;">
                <label for="nama_kabid" class="block text-sm font-medium mb-1">Nama Kepala Bidang <span class="text-red-500">*</span></label>
                <div style="position:relative;">
                    <input type="text" id="nama_kabid" name="nama_kabid"
                           x-model="searchQuery"
                           @focus="open = true"
                           @input="open = true"
                           placeholder="Ketik atau pilih nama Kepala Bidang..."
                           autocomplete="off"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 bg-white">
                    <button type="button" @click.stop="open = !open" tabindex="-1"
                            style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748B; cursor:pointer; padding:4px;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>

                {{-- Floating Dropdown Suggestion List --}}
                <div x-show="open && filteredList().length > 0"
                     x-cloak
                     @click.outside="open = false"
                     style="position:absolute; left:0; right:0; top:100%; margin-top:4px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; box-shadow:0 10px 25px rgba(15,23,42,0.15); max-height:200px; overflow-y:auto; z-index:99999;">
                    <template x-for="item in filteredList()" :key="item.name">
                        <div @click="selectItem(item)"
                             style="padding:8px 12px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                             onmouseover="this.style.background='#EFF6FF'"
                             onmouseout="this.style.background='transparent'">
                            <div>
                                <strong style="display:block; color:#0F172A; font-size:12.5px;" x-text="item.name"></strong>
                                <span style="font-size:11px; color:#64748B;" x-text="item.bidang || ''"></span>
                            </div>
                            <span x-show="item.jabatan"
                                  style="font-size:10px; font-weight:700; color:#065F46; background:#D1FAE5; padding:2px 6px; border-radius:8px;"
                                  x-text="item.jabatan"></span>
                        </div>
                    </template>
                </div>
                @error('nama_kabid') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Daftar Pemeriksaan Alat --}}
        <div class="daily-tool-list pt-5">
            <h2 class="text-base font-semibold border-b border-gray-200 pb-2 mb-4">Daftar Pemeriksaan Alat</h2>

            <div class="space-y-3">
                @foreach(($daftarAlat ?? []) as $index => $alat)
                    @php
                        $baikLama = old('alat.' . $index . '.jumlah_baik', $alat->jumlah_baik ?? 0);
                        $rusakLama = old('alat.' . $index . '.jumlah_rusak', $alat->jumlah_rusak ?? 0);
                        $nomorRusakLama = old('alat.' . $index . '.nomor_rusak');
                    @endphp
                    <div class="daily-tool-item border border-gray-200 rounded-xl p-4 sm:p-5"
                         x-data="{ jumlahBaik: {{ $baikLama }}, jumlahRusak: {{ $rusakLama }} }">

                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
                            <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-100 text-gray-500 text-[11px] font-semibold flex items-center justify-center">
                                    {{ $index + 1 }}
                                </span>
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $alat->nama }}</h3>
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
                                   placeholder="Contoh: Unit 1 mengalami kerusakan / kebocoran"
                                   class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 bg-red-50/30">
                            <p class="text-[11px] text-gray-400 mt-1">Sebutkan keterangan spesifik barang yang rusak.</p>
                            @error('alat.' . $index . '.nomor_rusak')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
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
                    <label class="block text-sm font-medium mb-1">Foto Dokumentasi <span class="text-red-500">*</span></label>
                    <label for="foto_umum" id="fotoUmumLabel"
                           class="flex flex-col items-center justify-center h-[110px] border-2 border-dashed border-gray-300 rounded-lg cursor-pointer text-center hover:border-blue-500 transition-colors">
                        <span class="text-blue-600 text-lg leading-none">📷</span>
                        <span class="text-xs text-blue-700 font-medium mt-1" id="fotoUmumText">+ Tambahkan Foto <span class="text-red-500">*</span></span>
                        <span class="text-[11px] text-gray-400">JPG, PNG, WEBP maks. 10MB</span>
                    </label>
                    <input id="foto_umum" type="file" name="foto_umum"
                           accept="image/*" class="hidden">
                    <p id="err_foto_umum" class="text-xs text-red-600 font-medium mt-1.5 hidden"></p>
                    <div id="fotoUmumPreview" class="mt-2.5 flex flex-wrap gap-2.5 hidden"></div>
                    @error('foto_umum') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Tombol Kirim --}}
        <div class="flex justify-end pt-4 border-t border-gray-200">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="send" class="w-4 h-4"></i> Simpan Pemeriksaan Alat Pencegahan
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
(function () {
    var form = document.querySelector('form');
    var input = document.getElementById('foto_umum');
    var labelText = document.getElementById('fotoUmumText');
    var previewEl = document.getElementById('fotoUmumPreview');
    var labelBox = document.getElementById('fotoUmumLabel');
    var errEl = document.getElementById('err_foto_umum');

    if (form && input) {
        form.addEventListener('submit', function (e) {
            if (!input.files || input.files.length === 0) {
                e.preventDefault();
                if (labelBox) {
                    labelBox.classList.add('border-red-500', 'bg-red-50/50');
                }
                if (errEl) {
                    errEl.textContent = 'Foto dokumentasi pemeriksaan alat wajib dilampirkan.';
                    errEl.classList.remove('hidden');
                }
                if (labelBox) {
                    labelBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        });
    }

    if (input && labelText) {
        input.addEventListener('change', function () {
            if (previewEl) previewEl.innerHTML = '';

            if (input.files.length === 0) {
                labelText.textContent = '+ Tambahkan Foto';
                labelBox.classList.remove('border-emerald-500', 'bg-emerald-50/50', 'border-red-500', 'bg-red-50/50');
                if (previewEl) previewEl.classList.add('hidden');
                return;
            }

            if (errEl) errEl.classList.add('hidden');
            labelBox.classList.remove('border-red-500', 'bg-red-50/50');
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
    }

    var posSelect = document.getElementById('pos');
    var allReguData = @json($allReguList ?? []);
    var danruUsersData = @json($danruUsers ?? []);

    function normalizeKeyPos(str) {
        return (str || '').trim().toLowerCase().replace(/[^a-z0-9]/g, '');
    }

    if (posSelect) {
        posSelect.addEventListener('change', function () {
            var selectedPosKey = normalizeKeyPos(this.value);

            // Auto-update Danru saat Pos diganti
            if (allReguData.length > 0 && window.danruComp) {
                var matchedRegu = allReguData.find(function (r) {
                    var rPos = normalizeKeyPos(r.pos || '');
                    return rPos && selectedPosKey && (rPos.includes(selectedPosKey) || selectedPosKey.includes(rPos));
                });
                if (matchedRegu && matchedRegu.danru) {
                    window.danruComp.searchQuery = matchedRegu.danru;
                } else if (danruUsersData.length > 0) {
                    var fallback = danruUsersData.find(function (u) {
                        var uPos = normalizeKeyPos(u.pos || '');
                        return uPos && selectedPosKey && (uPos.includes(selectedPosKey) || selectedPosKey.includes(uPos));
                    }) || danruUsersData[0];
                    if (fallback) {
                        window.danruComp.searchQuery = fallback.name;
                    }
                }
            }
        });
    }
})();

window.danruComp = null;
window.kabidComp = null;

function comboboxDanru() {
    return {
        open: false,
        searchQuery: @json(old('nama_danru', $defaultDanruName ?? '')),
        items: @json($danruOptions ?? []),
        init() {
            window.danruComp = this;
        },
        filteredList() {
            if (!this.searchQuery || this.searchQuery.trim() === '') {
                return [];
            }
            const q = this.searchQuery.toLowerCase();
            return this.items.filter(item => 
                (item.name && item.name.toLowerCase().includes(q)) ||
                (item.jabatan && item.jabatan.toLowerCase().includes(q)) ||
                (item.pos && item.pos.toLowerCase().includes(q))
            );
        },
        selectItem(item) {
            this.searchQuery = item.name;
            this.open = false;
        }
    };
}

function comboboxKabid() {
    return {
        open: false,
        searchQuery: @json(old('nama_kabid', $defaultKabidName ?? '')),
        items: @json($kabidOptions ?? []),
        init() {
            window.kabidComp = this;
        },
        filteredList() {
            if (!this.searchQuery || this.searchQuery.trim() === '') {
                return [];
            }
            const q = this.searchQuery.toLowerCase();
            return this.items.filter(item => 
                (item.name && item.name.toLowerCase().includes(q)) ||
                (item.jabatan && item.jabatan.toLowerCase().includes(q)) ||
                (item.bidang && item.bidang.toLowerCase().includes(q))
            );
        },
        selectItem(item) {
            this.searchQuery = item.name;
            this.open = false;
        }
    };
}
</script>
@endpush
@endsection