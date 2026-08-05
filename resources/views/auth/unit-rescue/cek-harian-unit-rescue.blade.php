@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 max-w-4xl mx-auto" id="wizardCekHarianUnit">

    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Cek Harian Unit Kendaraan Rescue</h1>

    {{-- ===================== STEPPER ===================== --}}
    <div class="flex items-start justify-between mt-6 mb-8 select-none overflow-x-auto">
        @php
            $steps = [
                1 => 'Identitas',
                2 => 'Pemanasan & BBM',
                3 => 'Perlengkapan',
                4 => 'Konfirmasi',
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

    <form action="{{ route('unit-rescue.cek-harian-unit-rescue.store') }}" method="POST" enctype="multipart/form-data" id="formCekHarianUnit" class="space-y-6">
        @csrf

        {{-- ===================== STEP 1 - IDENTITAS ===================== --}}
        <div data-step-panel="1">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nama_pemeriksa" class="block text-sm font-medium mb-1">Nama Pemeriksa</label>
                    <input type="text" id="nama_pemeriksa" name="nama_pemeriksa" value="{{ old('nama_pemeriksa') }}"
                           placeholder="Masukkan nama pemeriksa"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('nama_pemeriksa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="jabatan" class="block text-sm font-medium mb-1">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan') }}"
                           placeholder="Masukkan jabatan"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                    @error('jabatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="unit_id" class="block text-sm font-medium mb-1">Unit</label>
                    <select id="unit_id" name="unit_id"
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
                       class="flex items-center justify-between border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-500 cursor-pointer hover:border-blue-500">
                    <span id="buktiPemanasanLabel">Lampirkan Bukti Pemanasan</span>
                    <span>📎</span>
                </label>
                <input id="bukti_pemanasan" type="file" name="bukti_pemanasan" accept="image/*" class="hidden">
                @error('bukti_pemanasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <p class="font-medium text-sm mb-2">BBM</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="jenis_bbm" class="block text-sm font-medium mb-1">Jenis BBM</label>
                    <select id="jenis_bbm" name="jenis_bbm"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" selected disabled>Pilih Jenis BBM</option>
                        <option value="solar" @selected(old('jenis_bbm') === 'solar')>Solar</option>
                        <option value="bensin" @selected(old('jenis_bbm') === 'bensin')>Bensin</option>
                    </select>
                    @error('jenis_bbm') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div></div>
                <div class="mb-5">
                <p class="font-medium text-sm mb-1">Level BBM</p>
                <p class="text-xs text-gray-500 mb-2">(fotokan Speedometer untuk bukti level bbm)</p>
                <label for="bukti_pemanasan"
                       class="flex items-center justify-between border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-500 cursor-pointer hover:border-blue-500">
                    <span id="buktiPemanasanLabel">Lampirkan Bukti Level BBM</span>
                    <span>📎</span>
                </label>
                <input id="bukti_bbm" type="file" name="bukti_bbm" accept="image/*" class="hidden">
                @error('bukti_bbm') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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

        {{-- ===================== STEP 5 - KONFIRMASI ===================== --}}
        <div data-step-panel="4" class="hidden">
            <p class="font-medium text-base mb-3">Ringkasan Pemeriksaan</p>
            <div class="border border-gray-200 rounded-xl divide-y divide-gray-200">
                @foreach(['Identitas Pemeriksaan', 'Pemanasan & BBM', 'Perlengkapan Kendaraan'] as $ringkasan)
                    <div class="flex items-center justify-between px-4 py-3 text-sm">
                        <span>{{ $ringkasan }}</span>
                        <span class="text-green-600 font-medium flex items-center gap-1">Lengkap <span>✓</span></span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-start gap-2 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
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

    // Update label nama file yang dipilih pada input file
    function bindFileLabel(inputId, labelId, placeholder) {
        var input = document.getElementById(inputId);
        var labelEl = document.getElementById(labelId);
        if (!input || !labelEl) return;
        input.addEventListener('change', function () {
            if (input.files.length === 0) {
                labelEl.textContent = placeholder;
            } else if (input.files.length === 1) {
                labelEl.textContent = input.files[0].name;
            } else {
                labelEl.textContent = input.files.length + ' file dipilih';
            }
        });
    }
    bindFileLabel('bukti_pemanasan', 'buktiPemanasanLabel', 'Lampirkan Bukti Pemanasan');
    bindFileLabel('dokumentasi_tangki_pompa', 'dokumentasiTangkiLabel', 'Lampirkan Foto (Maks. 3 file)');

    showStep(currentStep);
})();
</script>
@endpush
@endsection
