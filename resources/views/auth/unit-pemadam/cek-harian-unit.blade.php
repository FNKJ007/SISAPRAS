@extends('layouts.app')

@section('content')
<div class="daily-check-unit bg-white rounded-xl shadow-sm p-4 sm:p-6 max-w-4xl mx-auto" id="wizardCekHarianUnit">

    {{-- Flash Message Success --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">✓</div>
                <div>
                    <h4 class="font-bold text-sm">Pemeriksaan Berhasil Disimpan!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            @if(session('cek_id'))
                <a href="{{ route('unit-pemadam.cek-harian-unit.export-pdf', session('cek_id')) }}"
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

    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Cek Harian Unit Kendaraan Pemadam</h1>

    {{-- ===================== STEPPER ===================== --}}
    <div class="mt-6 mb-8 select-none">
        <div class="grid grid-cols-5 gap-0 relative">
            @php
                $steps = [
                    1 => 'Identitas',
                    2 => 'Pemanasan, BBM & Kebersihan',
                    3 => 'Tangki & Pompa',
                    4 => 'Kendaraan',
                    5 => 'Konfirmasi',
                ];
            @endphp
            @foreach($steps as $num => $label)
                <div class="flex flex-col items-center text-center relative" data-step-indicator="{{ $num }}">
                    @if($num < count($steps))
                        <div data-line class="absolute top-[18px] left-[50%] w-full h-0.5 bg-gray-200 transition-colors"></div>
                    @endif
                    <div data-circle
                         class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold border-2 border-gray-300 text-gray-400 bg-white relative z-10 transition-colors shadow-xs">
                        {{ $num }}
                    </div>
                    <span data-label class="text-[11px] sm:text-xs font-medium mt-2 text-gray-500 text-center px-1 leading-tight">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <form action="{{ route('unit-pemadam.cek-harian-unit.store') }}" method="POST" enctype="multipart/form-data" id="formCekHarianUnit" class="space-y-6">
        @csrf

        {{-- ===================== STEP 1 - IDENTITAS ===================== --}}
        <div data-step-panel="1">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="pos" class="block text-sm font-medium mb-1">Pos Damkar <span class="text-red-500">*</span></label>
                    <select id="pos" name="pos" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" selected disabled>Pilih Pos Damkar</option>
                        @foreach($posList ?? [] as $p)
                            @php $pName = is_string($p) ? $p : ($p->nama ?? ($p['nama'] ?? '')); @endphp
                            <option value="{{ $pName }}" @selected(old('pos', auth()->user()->pos ?? '') == $pName)>{{ $pName }}</option>
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
                    <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', auth()->user()->jabatan ?? 'Petugas Regu') }}"
                           placeholder="Masukkan jabatan" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('jabatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="unit_id" class="block text-sm font-medium mb-1">Unit Kendaraan <span class="text-red-500">*</span></label>
                    <select id="unit_id" name="unit_id" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" selected disabled>Pilih Unit / Kendaraan Pemadam</option>
                        @foreach($unitList ?? [] as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>
                                {{ $unit->nomor_lambung ? $unit->nomor_lambung . ' — ' . $unit->plat_nomor . ($unit->pos ? ' [' . $unit->pos . ']' : '') : $unit->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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
        </div>

        {{-- ===================== STEP 2 - PEMANASAN, BBM & KEBERSIHAN ===================== --}}
        <div data-step-panel="2" class="hidden space-y-6">
            {{-- 1. Pemanasan Kendaraan --}}
            <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/30">
                <h3 class="font-bold text-sm text-blue-950 flex items-center gap-2 mb-1">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">1</span>
                    <span>Pemanasan Kendaraan <span class="text-red-500">*</span></span>
                </h3>
                <p class="text-xs text-gray-600 mb-3">(Unit harus dioperasikan dan dikendarai minimal sejauh 1 KM. Silakan lampirkan dokumentasi sebagai bukti)</p>
                <label for="bukti_pemanasan"
                       class="flex items-center justify-between border border-gray-300 bg-white rounded-lg px-3 py-2.5 text-sm text-gray-500 cursor-pointer hover:border-blue-500 transition-colors">
                    <span id="buktiPemanasanLabel">Lampirkan Bukti Pemanasan <span class="text-red-500">*</span></span>
                    <span>📎</span>
                </label>
                <input id="bukti_pemanasan" type="file" name="bukti_pemanasan" accept="image/*" class="hidden">
                <p id="err_bukti_pemanasan" class="text-xs text-red-600 font-medium mt-1.5 hidden"></p>
                <div id="buktiPemanasanPreview" class="mt-2.5 flex flex-wrap gap-2.5 hidden"></div>
                @error('bukti_pemanasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 2. Bahan Bakar Minyak (BBM) --}}
            <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/30">
                <h3 class="font-bold text-sm text-amber-950 flex items-center gap-2 mb-3">
                    <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs">2</span>
                    <span>Bahan Bakar Minyak (BBM) <span class="text-red-500">*</span></span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                    <div>
                        <label for="jenis_bbm" class="block text-sm font-medium mb-1">Jenis BBM <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-2">(Pilih jenis bahan bakar kendaraan)</p>
                        <select id="jenis_bbm" name="jenis_bbm" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="" selected disabled>Pilih Jenis BBM</option>
                            <option value="solar" @selected(old('jenis_bbm', 'solar') === 'solar')>Solar</option>
                            <option value="bensin" @selected(old('jenis_bbm') === 'bensin')>Bensin</option>
                        </select>
                        @error('jenis_bbm') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <p class="font-medium text-sm mb-1">Bukti Foto Level BBM <span class="text-red-500">*</span></p>
                        <p class="text-xs text-gray-500 mb-2">(Fotokan Speedometer untuk bukti level BBM)</p>
                        <label for="bukti_bbm"
                               class="flex items-center justify-between border border-gray-300 bg-white rounded-lg px-3 py-2.5 text-sm text-gray-500 cursor-pointer hover:border-blue-500 transition-colors">
                            <span id="buktiBbmLabel">Lampirkan Bukti Level BBM <span class="text-red-500">*</span></span>
                            <span>📎</span>
                        </label>
                        <input id="bukti_bbm" type="file" name="bukti_bbm" accept="image/*" class="hidden">
                        <p id="err_bukti_bbm" class="text-xs text-red-600 font-medium mt-1.5 hidden"></p>
                        <div id="buktiBbmPreview" class="mt-2.5 flex flex-wrap gap-2.5 hidden"></div>
                        @error('bukti_bbm') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- 3. Kebersihan Unit --}}
            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                    <h3 class="font-bold text-sm text-emerald-950 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs">3</span>
                        <span>Pemeriksaan Kebersihan Unit <span class="text-red-500">*</span></span>
                    </h3>
                    <span class="text-xs font-semibold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full">Wajib Pasukan</span>
                </div>
                <p class="text-xs text-gray-600 mb-3">Dokumentasi kegiatan pembersihan/pencucian unit oleh pasukan. (Kondisi kebersihan unit diisi pada bagian Kendaraan)</p>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Foto Kegiatan Pasukan Membersihkan Unit <span class="text-red-500">*</span></label>
                    <label for="bukti_pencucian"
                           class="flex items-center justify-between border border-gray-300 bg-white rounded-lg px-3 py-2 text-xs text-gray-600 cursor-pointer hover:border-emerald-500 transition">
                        <span id="buktiPencucianLabel">Lampirkan Foto Pembersihan <span class="text-red-500">*</span></span>
                        <span>📎</span>
                    </label>
                    <input id="bukti_pencucian" type="file" name="bukti_pencucian" accept="image/*" class="hidden">
                    <p id="err_bukti_pencucian" class="text-xs text-red-600 font-medium mt-1.5 hidden"></p>
                    <div id="buktiPencucianPreview" class="mt-2 flex flex-wrap gap-2 hidden"></div>
                    @error('bukti_pencucian') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ===================== STEP 3 - TANGKI & POMPA ===================== --}}
        <div data-step-panel="3" class="hidden">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="level_air" class="block text-sm font-medium mb-1">Level Air <span class="text-red-500">*</span></label>
                    <select id="level_air" name="level_air" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" selected disabled>Pilih Level Air</option>
                        <option value="penuh" @selected(old('level_air', 'penuh') === 'penuh')>Penuh</option>
                        <option value="3_4" @selected(old('level_air') === '3_4')>3/4</option>
                        <option value="1_2" @selected(old('level_air') === '1_2')>1/2</option>
                        <option value="kosong" @selected(old('level_air') === 'kosong')>Kosong</option>
                    </select>
                    @error('level_air') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="kondisi_tangki_air" class="block text-sm font-medium mb-1">Kondisi Tangki Air <span class="text-red-500">*</span></label>
                    <select id="kondisi_tangki_air" name="kondisi_tangki_air" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" selected disabled>Pilih Kondisi</option>
                        <option value="baik" @selected(old('kondisi_tangki_air', 'baik') === 'baik')>Baik</option>
                        <option value="perlu_perhatian" @selected(old('kondisi_tangki_air') === 'perlu_perhatian')>Perlu Perhatian</option>
                        <option value="rusak" @selected(old('kondisi_tangki_air') === 'rusak')>Rusak</option>
                    </select>
                    @error('kondisi_tangki_air') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="kebocoran_tangki_air" class="block text-sm font-medium mb-1">Kebocoran Tangki Air <span class="text-red-500">*</span></label>
                    <select id="kebocoran_tangki_air" name="kebocoran_tangki_air" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" selected disabled>Pilih Kondisi</option>
                        <option value="tidak_ada" @selected(old('kebocoran_tangki_air', 'tidak_ada') === 'tidak_ada')>Tidak Ada</option>
                        <option value="ada" @selected(old('kebocoran_tangki_air') === 'ada')>Ada</option>
                    </select>
                    @error('kebocoran_tangki_air') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tekanan_pompa" class="block text-sm font-medium mb-1">Tekanan Pompa <span class="text-red-500">*</span></label>
                    <select id="tekanan_pompa" name="tekanan_pompa" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" selected disabled>Pilih Tekanan</option>
                        <option value="baik" @selected(old('tekanan_pompa', 'baik') === 'baik')>Baik</option>
                        <option value="kurang" @selected(old('tekanan_pompa') === 'kurang')>Kurang</option>
                        <option value="tidak_ada" @selected(old('tekanan_pompa') === 'tidak_ada')>Tidak Ada</option>
                    </select>
                    @error('tekanan_pompa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="selang_induk" class="block text-sm font-medium mb-1">Selang Induk <span class="text-red-500">*</span></label>
                    <select id="selang_induk" name="selang_induk" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" selected disabled>Pilih Kondisi</option>
                        <option value="baik" @selected(old('selang_induk', 'baik') === 'baik')>Baik</option>
                        <option value="rusak" @selected(old('selang_induk') === 'rusak')>Rusak</option>
                    </select>
                    @error('selang_induk') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4">
                <label for="catatan_tangki_pompa" class="block text-sm font-medium mb-1">Catatan Khusus Terkait Pemeriksaan Tangki dan Pompa</label>
                <textarea id="catatan_tangki_pompa" name="catatan_tangki_pompa" rows="3"
                          placeholder="Tuliskan catatan khusus (jika ada)"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-600">{{ old('catatan_tangki_pompa') }}</textarea>
            </div>

            <div class="mt-4">
                <label for="dokumentasi_tangki_pompa" class="block text-sm font-medium mb-1">Dokumentasi Pengecekan Tangki dan Pompa (maksimal 3 foto) <span class="text-red-500">*</span></label>
                <label for="dokumentasi_tangki_pompa"
                       class="flex items-center justify-between border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-500 cursor-pointer hover:border-blue-500 transition-colors">
                    <span id="dokumentasiTangkiLabel">Lampirkan Foto (Maks. 3 file) <span class="text-red-500">*</span></span>
                    <span>📎</span>
                </label>
                <input id="dokumentasi_tangki_pompa" type="file" name="dokumentasi_tangki_pompa[]" accept="image/*" multiple class="hidden">
                <p id="err_dokumentasi_tangki_pompa" class="text-xs text-red-600 font-medium mt-1.5 hidden"></p>
                <div id="dokumentasiTangkiPreview" class="mt-2.5 flex flex-wrap gap-2.5 hidden"></div>
                @error('dokumentasi_tangki_pompa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                @error('dokumentasi_tangki_pompa.*') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ===================== STEP 4 - PERLENGKAPAN ===================== --}}
        <div data-step-panel="4" class="hidden">
            <div class="mb-4 p-4 rounded-xl border border-blue-100 bg-blue-50/60"><h2 class="font-bold text-base text-gray-900">Pemeriksaan Perlengkapan Kendaraan</h2><p class="text-xs text-gray-600 mt-1">Periksa setiap item satu per satu. Pilih kondisi dan tambahkan catatan bila diperlukan.</p></div>

            <div class="space-y-2.5">
                {{-- Kondisi Kebersihan Unit (Paling Atas) --}}
                <div class="unit-vehicle-item rounded-xl border border-gray-200 bg-white p-3 sm:p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_140px] gap-3 items-center">
                    <label for="kebersihan_unit" class="flex items-center gap-2 text-sm font-semibold text-gray-800"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-100 text-gray-500 text-[11px] font-semibold flex items-center justify-center">1</span>Kondisi Kebersihan Unit <span class="text-red-500">*</span></label>
                    <select id="kebersihan_unit" name="kebersihan_unit" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="bersih" @selected(old('kebersihan_unit', 'bersih') === 'bersih')>Bersih</option>
                        <option value="tidak_bersih" @selected(old('kebersihan_unit') === 'tidak_bersih')>Tidak Bersih</option>
                    </select>
                    </div>
                    <input type="text" name="catatan_kebersihan_unit" value="{{ old('catatan_kebersihan_unit') }}"
                           placeholder="Catatan (jika ada)"
                           class="w-full mt-2.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">
                </div>
                @error('kebersihan_unit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                @php
                    $perlengkapan = [
                        'engine_starter'             => 'Engine Starter',
                        'rem_tangan'                 => 'Rem Tangan',
                        'rem_kaki'                   => 'Rem Kaki',
                        'kelistrikan'                => 'Kelistrikan',
                        'klakson'                    => 'Klakson',
                        'sirine_tunggal'             => 'Sirine Tunggal',
                        'sirine'                     => 'Sirine',
                        'speedometer'                => 'Speedometer',
                        'dashboard_camera'           => 'Dashboard Camera',
                        'gps_tracker'                => 'GPS Tracker',
                        'flasher_sein_kanan_kiri'    => 'Flasher Sein Kanan-Kiri',
                        'spion_dalam'                => 'Spion Dalam',
                        'rig'                        => 'RIG',
                        'speaker'                    => 'Speaker',
                        'megaphone_toa'              => 'Megaphone (TOA)',
                        'oli_power_steering'         => 'Oli Power Steering',
                        'air_radiator'               => 'Air Radiator',
                        'minyak_rem'                 => 'Minyak Rem',
                        'oli_mesin'                  => 'Oli Mesin',
                        'air_wiper'                  => 'Air Wiper',
                        'ac'                         => 'AC',
                        'lampu_depan_dim_kanan'      => 'Lampu Depan (Dim) Kanan',
                        'lampu_depan_dim_kiri'       => 'Lampu Depan (Dim) Kiri',
                        'lampu_belakang_kanan'       => 'Lampu Belakang Kanan',
                        'lampu_belakang_kiri'        => 'Lampu Belakang Kiri',
                        'lampu_belakang_hazard'      => 'Lampu Belakang Hazard',
                        'lampu_sein_depan_kanan'     => 'Lampu Sein Depan Kanan',
                        'lampu_sein_depan_kiri'      => 'Lampu Sein Depan Kiri',
                        'lampu_sein_belakang_kanan'  => 'Lampu Sein Belakang Kanan',
                        'lampu_sein_belakang_kiri'   => 'Lampu Sein Belakang Kiri',
                        'spion_kanan'                => 'Spion Kanan',
                        'spion_kiri'                 => 'Spion Kiri',
                        'wiper'                      => 'Wiper',
                        'winch'                      => 'Winch',
                        'ban_depan_kanan'            => 'Ban Depan Kanan',
                        'ban_depan_kiri'             => 'Ban Depan Kiri',
                        'ban_belakang_kanan'         => 'Ban Belakang Kanan',
                        'ban_belakang_kiri'          => 'Ban Belakang Kiri',
                        'ban_cadangan'               => 'Ban Cadangan',
                        'lampu_rotary'               => 'Lampu Rotary',
                        'lampu_rem_kanan'            => 'Lampu Rem Kanan',
                        'lampu_rem_kiri'             => 'Lampu Rem Kiri',
                        'pintu_kompartemen_kanan'    => 'Pintu Kompartemen Kanan',
                        'pintu_kompartemen_kiri'     => 'Pintu Kompartemen Kiri',
                        'pintu_kompartemen_belakang' => 'Pintu Kompartemen Belakang',
                        'ganjal_ban'                 => 'Ganjal Ban',
                        'dongkrak'                   => 'Dongkrak',
                        'kabin'                      => 'Kabin',
                        'body_unit'                  => 'Body Unit',
                        'kunci_kunci'                => 'Kunci-Kunci',
                    ];
                @endphp
                @foreach($perlengkapan as $key => $label)
                    <div class="unit-vehicle-item rounded-xl border border-gray-200 bg-white p-3 sm:p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_140px] gap-3 items-center">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-800"><span class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-100 text-gray-500 text-[11px] font-semibold flex items-center justify-center">{{ $loop->iteration + 1 }}</span>{{ $label }}</label>
                        <select name="perlengkapan[{{ $key }}][status]"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="baik" selected>Baik</option>
                            <option value="rusak">Rusak</option>
                        </select>
                        </div>
                        <input type="text" name="perlengkapan[{{ $key }}][catatan]"
                               placeholder="Catatan (jika ada)"
                               class="w-full mt-2.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===================== STEP 5 - KONFIRMASI ===================== --}}
        <div data-step-panel="5" class="hidden unit-confirmation-panel">
            <div class="mb-4 flex items-start gap-3 p-4 rounded-xl border border-emerald-100 bg-emerald-50/60"><div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0"><i data-lucide="clipboard-check" class="w-5 h-5"></i></div><div><h2 class="font-bold text-base text-gray-900">Ringkasan Pemeriksaan</h2><p class="text-xs text-gray-600 mt-1">Periksa kembali seluruh bagian sebelum mengirim laporan.</p></div></div>
            <div class="border border-gray-200 rounded-xl divide-y divide-gray-200">
                @foreach(['Identitas Pemeriksaan', 'Pemanasan & BBM', 'Tangki & Pompa', 'Perlengkapan Kendaraan'] as $ringkasan)
                    <div class="flex items-center justify-between px-4 py-3 text-sm">
                        <span>{{ $ringkasan }}</span>
                        <span class="text-emerald-600 font-bold flex items-center gap-1">Lengkap <span>✓</span></span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-start gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-lg px-4 py-3 font-semibold unit-confirmation-notice">
                <span class="unit-confirmation-icon shrink-0">✅</span>
                <span>Pastikan semua data sudah benar sebelum menyimpan pemeriksaan.</span>
            </div>
        </div>

        {{-- ===================== TOMBOL NAVIGASI ===================== --}}
        <div class="flex justify-between pt-4">
            <button type="button" data-action="prev"
                    class="hidden btn btn-outline">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </button>
            <button type="button" data-action="next"
                    class="ml-auto btn btn-primary">
                Lanjut <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
            <button type="submit" data-action="submit"
                    class="hidden ml-auto btn btn-primary">
                <i data-lucide="send" class="w-4 h-4"></i> Kirim Pemeriksaan
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
(function () {
    var wizard = document.getElementById('wizardCekHarianUnit');
    var totalSteps = 5;
    var currentStep = 1;

    var panels     = wizard.querySelectorAll('[data-step-panel]');
    var indicators = wizard.querySelectorAll('[data-step-indicator]');
    var lines      = wizard.querySelectorAll('[data-line]');
    var btnPrev    = wizard.querySelector('[data-action="prev"]');
    var btnNext    = wizard.querySelector('[data-action="next"]');
    var btnSubmit  = wizard.querySelector('[data-action="submit"]');

    function renderStepper() {
        indicators.forEach(function (el) {
            var num = parseInt(el.getAttribute('data-step-indicator'), 10);
            var circle = el.querySelector('[data-circle]');
            var label = el.querySelector('[data-label]');

            circle.classList.remove('bg-[#C0201F]', 'text-white', 'border-[#C0201F]', 'bg-emerald-600', 'border-emerald-600', 'border-gray-300', 'text-gray-400', 'bg-white');
            label.classList.remove('text-[#C0201F]', 'text-gray-700', 'text-gray-400', 'font-semibold');

            if (num < currentStep) {
                // selesai
                circle.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
                circle.innerHTML = '&#10003;';
                label.classList.add('text-gray-700');
            } else if (num === currentStep) {
                // aktif
                circle.classList.add('bg-[#C0201F]', 'text-white', 'border-[#C0201F]');
                circle.innerHTML = num;
                label.classList.add('text-[#C0201F]', 'font-semibold');
            } else {
                // belum sampai
                circle.classList.add('border-gray-300', 'text-gray-400', 'bg-white');
                circle.innerHTML = num;
                label.classList.add('text-gray-400');
            }
        });

        lines.forEach(function (line, idx) {
            var stepBoundary = idx + 1; // garis ke-idx menghubungkan step (idx+1) -> (idx+2)
            line.classList.remove('bg-[#C0201F]', 'bg-gray-200');
            line.classList.add(stepBoundary < currentStep ? 'bg-[#C0201F]' : 'bg-gray-200');
        });
    }

    function showStep(step) {
        panels.forEach(function (panel) {
            var num = parseInt(panel.getAttribute('data-step-panel'), 10);
            panel.classList.toggle('hidden', num !== step);
        });

        btnPrev.classList.toggle('hidden', step === 1);
        btnNext.classList.toggle('hidden', step === totalSteps);
        btnSubmit.classList.toggle('hidden', step !== totalSteps);

        renderStepper();
        wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    var MAX_SIZE = 10 * 1024 * 1024; // 10MB
    var ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    function validateSingleFile(file, fieldName) {
        if (!file) return { valid: false, error: (fieldName || 'File') + ' tidak ditemukan.' };
        if (ALLOWED_TYPES.indexOf(file.type) === -1) {
            return { valid: false, error: 'Format "' + file.name + '" tidak didukung. Gunakan format JPG, PNG, atau WebP.' };
        }
        if (file.size > MAX_SIZE) {
            var sizeMB = (file.size / 1024 / 1024).toFixed(1);
            return { valid: false, error: 'File "' + file.name + '" terlalu besar (' + sizeMB + ' MB). Maksimal 10 MB.' };
        }
        return { valid: true, error: null };
    }

    function validateCurrentStep() {
        var panel = wizard.querySelector('[data-step-panel="' + currentStep + '"]');
        var requiredFields = panel.querySelectorAll('input[required]:not([type="file"]), select[required], textarea[required]');
        for (var i = 0; i < requiredFields.length; i++) {
            if (!requiredFields[i].value) {
                requiredFields[i].reportValidity();
                return false;
            }
        }

        // Validasi Step 2: Lampiran Foto Pemanasan, BBM & Kebersihan Wajib Diisi
        if (currentStep === 2) {
            var inputPemanasan = document.getElementById('bukti_pemanasan');
            var inputBbm = document.getElementById('bukti_bbm');
            var inputPencucian = document.getElementById('bukti_pencucian');

            var errPemanasan = document.getElementById('err_bukti_pemanasan');
            var errBbm = document.getElementById('err_bukti_bbm');
            var errPencucian = document.getElementById('err_bukti_pencucian');

            var labelPemanasan = inputPemanasan ? document.querySelector('label[for="bukti_pemanasan"]') : null;
            var labelBbm = inputBbm ? document.querySelector('label[for="bukti_bbm"]') : null;
            var labelPencucian = inputPencucian ? document.querySelector('label[for="bukti_pencucian"]') : null;

            var isValid = true;
            var firstInvalid = null;

            // Reset visual errors
            if (errPemanasan) errPemanasan.classList.add('hidden');
            if (errBbm) errBbm.classList.add('hidden');
            if (errPencucian) errPencucian.classList.add('hidden');

            if (labelPemanasan) labelPemanasan.classList.remove('border-red-500', 'bg-red-50/50');
            if (labelBbm) labelBbm.classList.remove('border-red-500', 'bg-red-50/50');
            if (labelPencucian) labelPencucian.classList.remove('border-red-500', 'bg-red-50/50');

            if (!inputPemanasan || !inputPemanasan.files || inputPemanasan.files.length === 0) {
                isValid = false;
                if (!firstInvalid) firstInvalid = labelPemanasan;
                if (labelPemanasan) labelPemanasan.classList.add('border-red-500', 'bg-red-50/50');
                if (errPemanasan) {
                    errPemanasan.textContent = 'Foto bukti pemanasan kendaraan wajib dilampirkan.';
                    errPemanasan.classList.remove('hidden');
                }
            } else {
                var resPem = validateSingleFile(inputPemanasan.files[0], 'Foto bukti pemanasan');
                if (!resPem.valid) {
                    isValid = false;
                    if (!firstInvalid) firstInvalid = labelPemanasan;
                    if (labelPemanasan) labelPemanasan.classList.add('border-red-500', 'bg-red-50/50');
                    if (errPemanasan) {
                        errPemanasan.textContent = resPem.error;
                        errPemanasan.classList.remove('hidden');
                    }
                }
            }

            if (!inputBbm || !inputBbm.files || inputBbm.files.length === 0) {
                isValid = false;
                if (!firstInvalid) firstInvalid = labelBbm;
                if (labelBbm) labelBbm.classList.add('border-red-500', 'bg-red-50/50');
                if (errBbm) {
                    errBbm.textContent = 'Bukti foto level BBM wajib dilampirkan.';
                    errBbm.classList.remove('hidden');
                }
            } else {
                var resBbm = validateSingleFile(inputBbm.files[0], 'Bukti foto BBM');
                if (!resBbm.valid) {
                    isValid = false;
                    if (!firstInvalid) firstInvalid = labelBbm;
                    if (labelBbm) labelBbm.classList.add('border-red-500', 'bg-red-50/50');
                    if (errBbm) {
                        errBbm.textContent = resBbm.error;
                        errBbm.classList.remove('hidden');
                    }
                }
            }

            if (!inputPencucian || !inputPencucian.files || inputPencucian.files.length === 0) {
                isValid = false;
                if (!firstInvalid) firstInvalid = labelPencucian;
                if (labelPencucian) labelPencucian.classList.add('border-red-500', 'bg-red-50/50');
                if (errPencucian) {
                    errPencucian.textContent = 'Foto kegiatan pasukan membersihkan unit wajib dilampirkan.';
                    errPencucian.classList.remove('hidden');
                }
            } else {
                var resCuci = validateSingleFile(inputPencucian.files[0], 'Foto pembersihan unit');
                if (!resCuci.valid) {
                    isValid = false;
                    if (!firstInvalid) firstInvalid = labelPencucian;
                    if (labelPencucian) labelPencucian.classList.add('border-red-500', 'bg-red-50/50');
                    if (errPencucian) {
                        errPencucian.textContent = resCuci.error;
                        errPencucian.classList.remove('hidden');
                    }
                }
            }

            if (!isValid) {
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        }

        // Validasi Step 3: Dokumentasi Pengecekan Tangki & Pompa Wajib Diisi (Khusus Pemadam)
        if (currentStep === 3) {
            var inputTangki = document.getElementById('dokumentasi_tangki_pompa');
            var errTangki = document.getElementById('err_dokumentasi_tangki_pompa');
            var labelTangki = inputTangki ? inputTangki.previousElementSibling : null;

            if (errTangki) errTangki.classList.add('hidden');
            if (labelTangki) labelTangki.classList.remove('border-red-500', 'bg-red-50/50');

            if (!inputTangki || !inputTangki.files || inputTangki.files.length === 0) {
                if (labelTangki) labelTangki.classList.add('border-red-500', 'bg-red-50/50');
                if (errTangki) {
                    errTangki.textContent = 'Foto dokumentasi pengecekan tangki dan pompa wajib dilampirkan.';
                    errTangki.classList.remove('hidden');
                }
                if (labelTangki) {
                    labelTangki.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            } else if (inputTangki.files.length > 3) {
                if (labelTangki) labelTangki.classList.add('border-red-500', 'bg-red-50/50');
                if (errTangki) {
                    errTangki.textContent = 'Foto dokumentasi tangki dan pompa maksimal 3 foto.';
                    errTangki.classList.remove('hidden');
                }
                if (labelTangki) {
                    labelTangki.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            } else {
                for (var t = 0; t < inputTangki.files.length; t++) {
                    var resT = validateSingleFile(inputTangki.files[t], 'Foto dokumentasi');
                    if (!resT.valid) {
                        if (labelTangki) labelTangki.classList.add('border-red-500', 'bg-red-50/50');
                        if (errTangki) {
                            errTangki.textContent = resT.error;
                            errTangki.classList.remove('hidden');
                        }
                        if (labelTangki) {
                            labelTangki.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return false;
                    }
                }
            }
        }

        return true;
    }

    btnNext.addEventListener('click', function () {
        if (!validateCurrentStep()) return;
        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        }
    });

    btnPrev.addEventListener('click', function () {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    // Remove file from global window scope so it can be called from inline onclick
    window.removeFile = function(targetInputId, index) {
        var targetInput = document.getElementById(targetInputId);
        if (targetInput && targetInput.accumulatedFiles) {
            targetInput.accumulatedFiles.splice(index, 1);
            targetInput.dispatchEvent(new Event('render-preview'));
        }
    };

    // Update label & render thumbnail preview for file inputs
    function bindFilePreview(inputId, labelId, previewId, placeholder) {
        var input = document.getElementById(inputId);
        var labelEl = document.getElementById(labelId);
        var previewEl = document.getElementById(previewId);
        if (!input || !labelEl) return;

        input.accumulatedFiles = [];

        function renderFiles() {
            var errEl = document.getElementById('err_' + inputId);
            
            if (input.accumulatedFiles.length === 0) {
                labelEl.textContent = placeholder;
                labelEl.parentElement.classList.remove('border-emerald-500', 'bg-emerald-50/50', 'border-red-500', 'bg-red-50/50');
                if (previewEl) {
                    previewEl.innerHTML = '';
                    previewEl.classList.add('hidden');
                }
                var dt = new DataTransfer();
                input.files = dt.files;
                return;
            }

            if (errEl) errEl.classList.add('hidden');
            labelEl.parentElement.classList.remove('border-red-500', 'bg-red-50/50');
            labelEl.parentElement.classList.add('border-emerald-500', 'bg-emerald-50/50');

            if (input.accumulatedFiles.length === 1) {
                labelEl.textContent = '✓ ' + input.accumulatedFiles[0].name;
            } else {
                labelEl.textContent = '✓ ' + input.accumulatedFiles.length + ' file foto terpilih';
            }

            if (previewEl) {
                previewEl.innerHTML = '';
                previewEl.classList.remove('hidden');
                input.accumulatedFiles.forEach(function (file, index) {
                    if (file.type.startsWith('image/')) {
                        var sizeMB = (file.size / 1024 / 1024).toFixed(1);
                        var sizeText = file.size >= 1024 * 1024 ? sizeMB + ' MB' : (file.size / 1024).toFixed(1) + ' KB';
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var item = document.createElement('div');
                            item.className = 'relative border border-emerald-300 rounded-lg p-1.5 bg-emerald-50/30 flex items-center gap-2.5 shadow-2xs';
                            item.innerHTML = `
                                <img src="${e.target.result}" alt="Preview" class="w-12 h-12 object-cover rounded-md border border-emerald-200">
                                <div>
                                    <span class="block text-xs font-bold text-emerald-900 truncate max-w-[130px]">${file.name}</span>
                                    <span class="block text-[10px] text-emerald-700 font-semibold">${sizeText} · Foto Terpilih ✓</span>
                                </div>
                                <button type="button" class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center hover:bg-red-600 transition-colors shadow" onclick="removeFile('${inputId}', ${index})" title="Hapus foto">×</button>
                            `;
                            previewEl.appendChild(item);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            var dt = new DataTransfer();
            input.accumulatedFiles.forEach(f => dt.items.add(f));
            input.files = dt.files;
        }

        input.addEventListener('render-preview', renderFiles);

        input.addEventListener('change', function () {
            var errEl = document.getElementById('err_' + inputId);
            var maxFiles = input.hasAttribute('multiple') ? 3 : 1;

            if (input.files.length === 0 && input.accumulatedFiles.length === 0) {
                return;
            }

            var newFiles = Array.from(input.files);
            for (var f = 0; f < newFiles.length; f++) {
                var res = validateSingleFile(newFiles[f]);
                if (!res.valid) {
                    if (errEl) {
                        errEl.textContent = res.error;
                        errEl.classList.remove('hidden');
                    }
                    labelEl.parentElement.classList.add('border-red-500', 'bg-red-50/50');
                    labelEl.parentElement.classList.remove('border-emerald-500', 'bg-emerald-50/50');
                    labelEl.textContent = placeholder;
                    input.value = ''; 
                    renderFiles();
                    return;
                }
            }

            if (maxFiles > 1) {
                newFiles.forEach(f => {
                    if (input.accumulatedFiles.length < maxFiles) {
                        input.accumulatedFiles.push(f);
                    }
                });
            } else {
                input.accumulatedFiles = newFiles.slice(0, 1);
            }

            if (errEl) errEl.classList.add('hidden');
            renderFiles();
        });
    }

    bindFilePreview('bukti_pemanasan', 'buktiPemanasanLabel', 'buktiPemanasanPreview', 'Lampirkan Bukti Pemanasan');
    bindFilePreview('bukti_bbm', 'buktiBbmLabel', 'buktiBbmPreview', 'Lampirkan Bukti Level BBM');
    bindFilePreview('bukti_pencucian', 'buktiPencucianLabel', 'buktiPencucianPreview', 'Lampirkan Foto Pembersihan');
    bindFilePreview('dokumentasi_tangki_pompa', 'dokumentasiTangkiLabel', 'dokumentasiTangkiPreview', 'Lampirkan Foto (Maks. 3 file)');

    @if($errors->any())
        var firstError = wizard.querySelector('.text-red-600');
        if (firstError) {
            var errPanel = firstError.closest('[data-step-panel]');
            if (errPanel) {
                currentStep = parseInt(errPanel.getAttribute('data-step-panel'), 10);
            }
        }
    @endif

    // Dynamic Filter Unit Kendaraan berdasarkan Pos yang dipilih
    var posSelect = document.getElementById('pos');
    var unitSelect = document.getElementById('unit_id');
    var allUnitsData = @json($allUnits ?? $unitList ?? []);
    var allReguData = @json($allReguList ?? []);
    var danruUsersData = @json($danruUsers ?? []);

    function normalizeKeyPos(str) {
        return (str || '').trim().toLowerCase().replace(/[^a-z0-9]/g, '');
    }

    if (posSelect) {
        posSelect.addEventListener('change', function () {
            var selectedPosKey = normalizeKeyPos(this.value);

            if (unitSelect) {
                var currentUnitId = unitSelect.value;
                unitSelect.innerHTML = '<option value="" disabled selected>Pilih Unit / Kendaraan Pemadam</option>';

                var matchedUnits = allUnitsData.filter(function (u) {
                    if (!selectedPosKey) return true;
                    var uPosKey = normalizeKeyPos(u.pos || '');
                    return uPosKey === selectedPosKey || uPosKey.includes(selectedPosKey) || selectedPosKey.includes(uPosKey);
                });

                var matched = false;
                matchedUnits.forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.id;
                    var label = u.nomor_lambung ? (u.nomor_lambung + ' — ' + u.plat_nomor + (u.pos ? ' [' + u.pos + ']' : '')) : (u.nama || '');
                    opt.textContent = label;
                    if (currentUnitId && String(u.id) === String(currentUnitId)) {
                        opt.selected = true;
                        matched = true;
                    }
                    unitSelect.appendChild(opt);
                });

                if (!matched && matchedUnits.length > 0) {
                    unitSelect.value = matchedUnits[0].id;
                }
            }

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

    showStep(currentStep);
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
