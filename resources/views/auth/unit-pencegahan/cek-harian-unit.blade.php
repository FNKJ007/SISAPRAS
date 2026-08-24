@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 max-w-4xl mx-auto" id="wizardCekHarianUnit">

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
                <a href="{{ route('unit-pencegahan.cek-harian-unit.export-pdf', session('cek_id')) }}"
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

    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Cek Harian Unit Kendaraan Pencegahan</h1>

    {{-- ===================== STEPPER ===================== --}}
    <div class="mt-6 mb-8 select-none">
        <div class="grid grid-cols-4 gap-0 relative">
            @php
                $steps = [
                    1 => 'Identitas',
                    2 => 'Pemanasan, BBM & Kebersihan',
                    3 => 'Perlengkapan',
                    4 => 'Konfirmasi',
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

    <form action="{{ route('unit-pencegahan.cek-harian-unit.store') }}" method="POST" enctype="multipart/form-data" id="formCekHarianUnit" class="space-y-6">
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
                    <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', auth()->user()->jabatan ?? 'Petugas Regu') }}"
                           placeholder="Masukkan jabatan" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('jabatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="unit_id" class="block text-sm font-medium mb-1">Unit Kendaraan Pencegahan <span class="text-red-500">*</span></label>
                    <select id="unit_id" name="unit_id" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" selected disabled>Pilih Unit / Kendaraan Pencegahan</option>
                        @foreach($unitList ?? [] as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>
                                {{ $unit->nomor_lambung ? $unit->nomor_lambung . ' — ' . $unit->plat_nomor . ($unit->pos ? ' [' . $unit->pos . ']' : '') . ($unit->merk_tipe ? ' (' . $unit->merk_tipe . ')' : '') : $unit->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ===================== STEP 2 - PEMANASAN, BBM & KEBERSIHAN ===================== --}}
        <div data-step-panel="2" class="hidden space-y-6">
            {{-- 1. Pemanasan Kendaraan --}}
            <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/30">
                <h3 class="font-bold text-sm text-blue-950 flex items-center gap-2 mb-1">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">1</span>
                    <span>Pemanasan Kendaraan</span>
                </h3>
                <p class="text-xs text-gray-600 mb-3">(Unit harus dioperasikan dan dikendarai minimal sejauh 1 KM. Silakan lampirkan dokumentasi sebagai bukti)</p>
                <label for="bukti_pemanasan"
                       class="flex items-center justify-between border border-gray-300 bg-white rounded-lg px-3 py-2.5 text-sm text-gray-500 cursor-pointer hover:border-blue-500 transition-colors">
                    <span id="buktiPemanasanLabel">Lampirkan Bukti Pemanasan</span>
                    <span>📎</span>
                </label>
                <input id="bukti_pemanasan" type="file" name="bukti_pemanasan" accept="image/*" class="hidden">
                <div id="buktiPemanasanPreview" class="mt-2.5 flex flex-wrap gap-2.5 hidden"></div>
                @error('bukti_pemanasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 2. Bahan Bakar Minyak (BBM) --}}
            <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/30">
                <h3 class="font-bold text-sm text-amber-950 flex items-center gap-2 mb-3">
                    <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs">2</span>
                    <span>Bahan Bakar Minyak (BBM)</span>
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
                        <p class="font-medium text-sm mb-1">Bukti Foto Level BBM</p>
                        <p class="text-xs text-gray-500 mb-2">(Fotokan Speedometer untuk bukti level BBM)</p>
                        <label for="bukti_bbm"
                               class="flex items-center justify-between border border-gray-300 bg-white rounded-lg px-3 py-2.5 text-sm text-gray-500 cursor-pointer hover:border-blue-500 transition-colors">
                            <span id="buktiBbmLabel">Lampirkan Bukti Level BBM</span>
                            <span>📎</span>
                        </label>
                        <input id="bukti_bbm" type="file" name="bukti_bbm" accept="image/*" class="hidden">
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
                        <span>Pemeriksaan Kebersihan Unit</span>
                    </h3>
                    <span class="text-xs font-semibold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full">Wajib Pasukan</span>
                </div>
                <p class="text-xs text-gray-600 mb-3">Pemeriksaan kondisi kebersihan unit kendaraan pencegahan dan dokumentasi kegiatan pembersihan/pencucian oleh pasukan.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Kondisi Kebersihan Unit <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2.5">
                            <label class="flex items-center gap-2 border border-emerald-300 bg-white rounded-lg p-2.5 cursor-pointer hover:bg-emerald-50 transition">
                                <input type="radio" name="kebersihan_unit" value="bersih" @checked(old('kebersihan_unit', 'bersih') === 'bersih') class="text-emerald-600 focus:ring-emerald-500">
                                <span class="text-xs font-bold text-emerald-900">✨ Bersih</span>
                            </label>
                            <label class="flex items-center gap-2 border border-gray-300 bg-white rounded-lg p-2.5 cursor-pointer hover:bg-red-50 transition">
                                <input type="radio" name="kebersihan_unit" value="tidak_bersih" @checked(old('kebersihan_unit') === 'tidak_bersih') class="text-red-600 focus:ring-red-500">
                                <span class="text-xs font-bold text-gray-700">⚠️ Tidak Bersih</span>
                            </label>
                        </div>
                        @error('kebersihan_unit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Foto Kegiatan Pasukan Membersihkan Unit</label>
                        <label for="bukti_pencucian"
                               class="flex items-center justify-between border border-gray-300 bg-white rounded-lg px-3 py-2 text-xs text-gray-600 cursor-pointer hover:border-emerald-500 transition">
                            <span id="buktiPencucianLabel">Lampirkan Foto Pembersihan</span>
                            <span>📎</span>
                        </label>
                        <input id="bukti_pencucian" type="file" name="bukti_pencucian" accept="image/*" class="hidden">
                        <div id="buktiPencucianPreview" class="mt-2 flex flex-wrap gap-2 hidden"></div>
                        @error('bukti_pencucian') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>


        {{-- ===================== STEP 4 - PERLENGKAPAN ===================== --}}
        <div data-step-panel="3" class="hidden">
            <p class="font-medium text-sm mb-3">Pemeriksaan Perlengkapan Kendaraan</p>

            <div class="space-y-3">
                @php
                    $perlengkapan = [
                        'ban_cadangan'                => 'Ban Cadangan',
    'ban_belakang_kanan'          => 'Ban Mobil Belakang Kanan',
    'ban_belakang_kiri'           => 'Ban Mobil Belakang Kiri',
    'ban_depan_kanan'             => 'Ban Mobil Depan Kanan',
    'ban_depan_kiri'              => 'Ban Mobil Depan Kiri',
    'dop_pelek'                   => 'Dop Pelek',
    'electric_winch'              => 'Electric Winch',
    'handel_kanan_belakang'       => 'Handel Kanan Belakang',
    'handel_kanan_depan'          => 'Handel Kanan Depan',
    'handel_kiri_belakang'        => 'Handel Kiri Belakang',
    'handel_kiri_depan'           => 'Handel Kiri Depan',
    'kaca_spion_kanan'            => 'Kaca Spion Kanan',
    'kaca_spion_kiri'             => 'Kaca Spion Kiri',
    'lampu_kabut_kanan'           => 'Lampu Kabut Kanan',
    'lampu_kabut_kiri'            => 'Lampu Kabut Kiri',
    'lampu_parkir_kanan'          => 'Lampu Parkir Kanan',
    'lampu_parkir_kiri'           => 'Lampu Parkir Kiri',
    'lampu_penerangan'            => 'Lampu Penerangan',
    'lampu_peringatan_belakang_kanan' => 'Lampu Peringatan Belakang Kanan',
    'lampu_peringatan_belakang_kiri'  => 'Lampu Peringatan Belakang Kiri',
    'lampu_peringatan_depan_kanan'    => 'Lampu Peringatan Depan Kanan',
    'lampu_peringatan_depan_kiri'     => 'Lampu Peringatan Depan Kiri',
    'lampu_rem_kanan'             => 'Lampu Rem Kanan',
    'lampu_rem_kiri'              => 'Lampu Rem Kiri',
    'lampu_rotari_atas_belakang'  => 'Lampu Rotari Atas Belakang',
    'lampu_rotari_atas_depan'     => 'Lampu Rotari Atas Depan',
    'lampu_rotator_atas_belakang' => 'Lampu Rotator Atas Belakang',
    'lampu_rotator_atas_depan'    => 'Lampu Rotator Atas Depan',
    'lampu_sein_belakang_kanan'   => 'Lampu Sein Belakang Kanan',
    'lampu_sein_belakang_kiri'    => 'Lampu Sein Belakang Kiri',
    'lampu_sein_depan_kanan'      => 'Lampu Sein Depan Kanan',
    'lampu_sein_depan_kiri'       => 'Lampu Sein Depan Kiri',
    'lampu_sorot_belakang'        => 'Lampu Sorot Belakang',
    'lampu_sorot_kanan_atas'      => 'Lampu Sorot Kanan Atas',
    'lampu_sorot_kanan_samping'   => 'Lampu Sorot Kanan Samping',
    'lampu_sorot_kiri_atas'       => 'Lampu Sorot Kiri Atas',
    'lampu_sorot_kiri_samping'    => 'Lampu Sorot Kiri Samping',
    'lampu_utama_depan_kanan'     => 'Lampu Utama Depan Kanan',
    'lampu_utama_depan_kiri'      => 'Lampu Utama Depan Kiri',
    'lighting_remote'             => 'Lighting + Remote',
    'modulator_sirine'            => 'Modulator Sirine',
    'plat_nomor_belakang'         => 'Plat Nomor Kendaraan Belakang',
    'plat_nomor_depan'            => 'Plat Nomor Kendaraan Depan',
    'radio_pesawat_rig'           => 'Radio Pesawat (RIG)',
    'radio_tape'                  => 'Radio Tape',
    'rolling_belakang'            => 'Rolling Belakang',
    'rolling_kanan'               => 'Rolling Kanan',
    'rolling_kiri'                => 'Rolling Kiri',
    'sirine_tunggal'              => 'Sirine Tunggal',
    'toa_sirine'                  => 'TOA Sirine',
    'wiper_kanan'                 => 'Wiper Kanan',
    'wiper_kiri'                  => 'Wiper Kiri',
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

        {{-- ===================== STEP 4 - KONFIRMASI ===================== --}}
        <div data-step-panel="4" class="hidden">
            <p class="font-medium text-base mb-3">Ringkasan Pemeriksaan</p>
            <div class="border border-gray-200 rounded-xl divide-y divide-gray-200">
                @foreach(['Identitas Pemeriksaan', 'Pemanasan & BBM', 'Perlengkapan Kendaraan'] as $ringkasan)
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
    var totalSteps = 4;
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
    bindFilePreview('bukti_pencucian', 'buktiPencucianLabel', 'buktiPencucianPreview', 'Lampirkan Bukti Pencucian');

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
