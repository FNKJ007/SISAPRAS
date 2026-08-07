@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 max-w-4xl mx-auto" id="wizardCekHarianUnit">

    {{-- Flash Message Success --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">✓</div>
                <div>
                    <h4 class="font-bold text-sm">Pemeriksaan Berhasil Disimpan!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Cek Harian Unit Kendaraan Pemadam</h1>

    {{-- ===================== STEPPER ===================== --}}
    <div class="flex items-start justify-between mt-6 mb-8 select-none overflow-x-auto">
        @php
            $steps = [
                1 => 'Identitas',
                2 => 'Pemanasan & BBM',
                3 => 'Tangki & Pompa',
                4 => 'Kendaraan',
                5 => 'Konfirmasi',
            ];
        @endphp
        @foreach($steps as $num => $label)
            <div class="flex items-center {{ $num < count($steps) ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center min-w-[70px]" data-step-indicator="{{ $num }}">
                    <div data-circle
                         class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold border-2 border-gray-300 text-gray-400 bg-white transition-colors">
                        {{ $num }}
                    </div>
                    <span data-label class="text-xs mt-2 text-gray-400 text-center whitespace-nowrap">{{ $label }}</span>
                </div>
                @if($num < count($steps))
                    <div data-line class="flex-1 h-0.5 bg-gray-200 mx-2 mt-[18px] transition-colors"></div>
                @endif
            </div>
        @endforeach
    </div>

    <form action="{{ route('unit-pemadam.cek-harian-unit.store') }}" method="POST" enctype="multipart/form-data" id="formCekHarianUnit" class="space-y-6">
        @csrf

        {{-- ===================== STEP 1 - IDENTITAS ===================== --}}
        <div data-step-panel="1">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nama_pemeriksa" class="block text-sm font-medium mb-1">Nama Pemeriksa <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_pemeriksa" name="nama_pemeriksa" value="{{ old('nama_pemeriksa', auth()->user()->name ?? '') }}"
                           placeholder="Masukkan nama pemeriksa" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('nama_pemeriksa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="jabatan" class="block text-sm font-medium mb-1">Jabatan <span class="text-red-500">*</span></label>
                    <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', 'Petugas Regu') }}"
                           placeholder="Masukkan jabatan" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('jabatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="unit_id" class="block text-sm font-medium mb-1">Unit Kendaraan <span class="text-red-500">*</span></label>
                    <select id="unit_id" name="unit_id" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" selected disabled>Pilih Unit / Kendaraan</option>
                        @foreach($unitList ?? [] as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                    @error('unit_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ===================== STEP 2 - PEMANASAN & BBM ===================== --}}
        <div data-step-panel="2" class="hidden">
            <div class="mb-5">
                <p class="font-medium text-sm mb-1">Pemanasan Kendaraan</p>
                <p class="text-xs text-gray-500 mb-2">(Unit harus dioperasikan dan dikendarai minimal sejauh 1 KM. Silakan lampirkan dokumentasi sebagai bukti)</p>
                <label for="bukti_pemanasan"
                       class="flex items-center justify-between border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-500 cursor-pointer hover:border-blue-500 transition-colors">
                    <span id="buktiPemanasanLabel">Lampirkan Bukti Pemanasan</span>
                    <span>📎</span>
                </label>
                <input id="bukti_pemanasan" type="file" name="bukti_pemanasan" accept="image/*" class="hidden">
                <div id="buktiPemanasanPreview" class="mt-2.5 flex flex-wrap gap-2.5 hidden"></div>
                @error('bukti_pemanasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <p class="font-medium text-sm mb-2">BBM</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="jenis_bbm" class="block text-sm font-medium mb-1">Jenis BBM <span class="text-red-500">*</span></label>
                    <select id="jenis_bbm" name="jenis_bbm" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" selected disabled>Pilih Jenis BBM</option>
                        <option value="solar" @selected(old('jenis_bbm', 'solar') === 'solar')>Solar</option>
                        <option value="bensin" @selected(old('jenis_bbm') === 'bensin')>Bensin</option>
                    </select>
                    @error('jenis_bbm') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mb-5">
                    <p class="font-medium text-sm mb-1">Bukti Foto Level BBM</p>
                    <p class="text-xs text-gray-500 mb-2">(fotokan Speedometer untuk bukti level bbm)</p>
                    <label for="bukti_bbm"
                           class="flex items-center justify-between border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-500 cursor-pointer hover:border-blue-500 transition-colors">
                        <span id="buktiBbmLabel">Lampirkan Bukti Level BBM</span>
                        <span>📎</span>
                    </label>
                    <input id="bukti_bbm" type="file" name="bukti_bbm" accept="image/*" class="hidden">
                    <div id="buktiBbmPreview" class="mt-2.5 flex flex-wrap gap-2.5 hidden"></div>
                    @error('bukti_bbm') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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
                <label for="dokumentasi_tangki_pompa" class="block text-sm font-medium mb-1">Dokumentasi Pengecekan Tangki dan Pompa (maksimal 3 foto)</label>
                <label for="dokumentasi_tangki_pompa"
                       class="flex items-center justify-between border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-500 cursor-pointer hover:border-blue-500">
                    <span id="dokumentasiTangkiLabel">Lampirkan Foto (Maks. 3 file)</span>
                    <span>📎</span>
                </label>
                <input id="dokumentasi_tangki_pompa" type="file" name="dokumentasi_tangki_pompa[]" accept="image/*" multiple class="hidden">
                <div id="dokumentasiTangkiPreview" class="mt-2.5 flex flex-wrap gap-2.5 hidden"></div>
                @error('dokumentasi_tangki_pompa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ===================== STEP 4 - PERLENGKAPAN ===================== --}}
        <div data-step-panel="4" class="hidden">
            <p class="font-medium text-sm mb-3">Pemeriksaan Perlengkapan Kendaraan</p>

            <div class="space-y-3">
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
                        'kebersihan_bagian_dalam'    => 'Kebersihan Bagian Dalam',
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
                        'kebersihan_bagian_luar'     => 'Kebersihan Bagian Luar',
                    ];
                @endphp
                @foreach($perlengkapan as $key => $label)
                    <div class="grid grid-cols-1 sm:grid-cols-[160px_140px_1fr] gap-3 items-center">
                        <span class="text-sm font-medium">{{ $label }}</span>
                        <select name="perlengkapan[{{ $key }}][status]"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="baik" selected>Baik</option>
                            <option value="rusak">Rusak</option>
                        </select>
                        <input type="text" name="perlengkapan[{{ $key }}][catatan]"
                               placeholder="Catatan (jika ada)"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===================== STEP 5 - KONFIRMASI ===================== --}}
        <div data-step-panel="5" class="hidden">
            <p class="font-medium text-base mb-3">Ringkasan Pemeriksaan</p>
            <div class="border border-gray-200 rounded-xl divide-y divide-gray-200">
                @foreach(['Identitas Pemeriksaan', 'Pemanasan & BBM', 'Tangki & Pompa', 'Perlengkapan Kendaraan'] as $ringkasan)
                    <div class="flex items-center justify-between px-4 py-3 text-sm">
                        <span>{{ $ringkasan }}</span>
                        <span class="text-emerald-600 font-bold flex items-center gap-1">Lengkap <span>✓</span></span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-start gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-lg px-4 py-3 font-semibold">
                <span>✅</span>
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

    function validateCurrentStep() {
        var panel = wizard.querySelector('[data-step-panel="' + currentStep + '"]');
        var requiredFields = panel.querySelectorAll('[required]');
        for (var i = 0; i < requiredFields.length; i++) {
            if (!requiredFields[i].value) {
                requiredFields[i].reportValidity();
                return false;
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

    // Update label & render thumbnail preview for file inputs
    function bindFilePreview(inputId, labelId, previewId, placeholder) {
        var input = document.getElementById(inputId);
        var labelEl = document.getElementById(labelId);
        var previewEl = document.getElementById(previewId);
        if (!input || !labelEl) return;

        input.addEventListener('change', function () {
            if (previewEl) previewEl.innerHTML = '';

            if (input.files.length === 0) {
                labelEl.textContent = placeholder;
                labelEl.parentElement.classList.remove('border-emerald-500', 'bg-emerald-50/50');
                if (previewEl) previewEl.classList.add('hidden');
                return;
            }

            labelEl.parentElement.classList.add('border-emerald-500', 'bg-emerald-50/50');

            if (input.files.length === 1) {
                labelEl.textContent = '✓ ' + input.files[0].name;
            } else {
                labelEl.textContent = '✓ ' + input.files.length + ' file foto terpilih';
            }

            if (previewEl) {
                previewEl.classList.remove('hidden');
                Array.from(input.files).forEach(function (file) {
                    if (file.type.startsWith('image/')) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var item = document.createElement('div');
                            item.className = 'relative border border-emerald-300 rounded-lg p-1.5 bg-emerald-50/30 flex items-center gap-2.5 shadow-2xs';
                            item.innerHTML = `
                                <img src="${e.target.result}" alt="Preview" class="w-12 h-12 object-cover rounded-md border border-emerald-200">
                                <div>
                                    <span class="block text-xs font-bold text-emerald-900 truncate max-w-[160px]">${file.name}</span>
                                    <span class="block text-[10px] text-emerald-700 font-semibold">${(file.size / 1024).toFixed(1)} KB · Foto Terpilih ✓</span>
                                </div>
                            `;
                            previewEl.appendChild(item);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    }

    bindFilePreview('bukti_pemanasan', 'buktiPemanasanLabel', 'buktiPemanasanPreview', 'Lampirkan Bukti Pemanasan');
    bindFilePreview('bukti_bbm', 'buktiBbmLabel', 'buktiBbmPreview', 'Lampirkan Bukti Level BBM');
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

    showStep(currentStep);
})();
</script>
@endpush
@endsection
