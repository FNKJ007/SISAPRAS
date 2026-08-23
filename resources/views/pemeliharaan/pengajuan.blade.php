@extends('layouts.app')

@section('title', 'Pengajuan Pemeliharaan')

@section('content')

    @if (session('success'))
        <div class="alert-success" style="margin-bottom:16px;padding:10px 16px;background:#e6f4ea;color:#1e7e34;border-radius:5px;font-size:14px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="form-card">
        @if(isset($errors) && $errors->any())
            <div style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:12px 16px; border-radius:10px; margin-bottom:18px; font-size:13px;">
                <div style="display:flex; align-items:center; gap:6px; font-weight:800; color:#DC2626; margin-bottom:4px;">
                    <i data-lucide="alert-triangle" style="width:16px; height:16px;"></i>
                    <span>Gagal Mengirim Pengajuan:</span>
                </div>
                <ul style="margin:0; padding-left:20px; font-size:12.5px; font-weight:500;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-card-title">Pengajuan Pemeliharaan Unit Operasional</div>

        <form action="{{ route('pemeliharaan.pengajuan.store') }}" method="POST">
            @csrf

            {{-- ============ Bidang ============ --}}
            <div class="form-group has-caret">
                <label for="bidang">Bidang</label>
                <select name="bidang" id="bidang" required>
                    <option value="" disabled {{ !old('bidang') && !($currentUser->bidang ?? false) ? 'selected' : '' }}></option>
                    @foreach ($bidangList as $value => $label)
                        @php
                            $userBidang = strtolower($currentUser->bidang ?? '');
                            $isUserBidang = $userBidang && (strtolower($value) === $userBidang || strtolower($label) === $userBidang);
                            $isSelected = old('bidang') ? (old('bidang') == $value) : $isUserBidang;
                        @endphp
                        <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ============ Pos ============ --}}
            <div class="form-group has-caret">
                <label for="pos">Pos</label>
                <select name="pos" id="pos" required>
                    <option value="" disabled {{ !old('pos') && !($currentUser->pos ?? false) ? 'selected' : '' }}></option>
                    @foreach ($posList as $value => $label)
                        @php
                            $userPosClean = strtolower(str_replace(' ', '', $currentUser->pos ?? ''));
                            $valPosClean = strtolower(str_replace(' ', '', $value));
                            $labelPosClean = strtolower(str_replace(' ', '', $label));
                            $isUserPos = $userPosClean && ($valPosClean === $userPosClean || $labelPosClean === $userPosClean);
                            $isSelected = old('pos') ? (old('pos') == $value) : $isUserPos;
                        @endphp
                        <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ============ Regu ============ --}}
            <div class="form-group has-caret">
                <label for="regu">Regu</label>
                <select name="regu" id="regu" required>
                    <option value="" disabled {{ !old('regu') && !($currentUser->regu ?? false) ? 'selected' : '' }}></option>
                    @foreach ($reguList as $value => $label)
                        @php
                            $userRegu = strtolower(str_replace(' ', '', $currentUser->regu ?? ''));
                            $valRegu = strtolower(str_replace(' ', '', $value));
                            $labelRegu = strtolower(str_replace(' ', '', $label));
                            $isUserRegu = $userRegu && ($valRegu === $userRegu || $labelRegu === $userRegu);
                            $isSelected = old('regu') ? (old('regu') == $value) : $isUserRegu;
                        @endphp
                        <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ============ Jenis Kendaraan ============ --}}
            <div class="form-group has-caret">
                <label for="jenis_kendaraan">Jenis Kendaraan</label>
                <select name="jenis_kendaraan" id="jenis_kendaraan" required>
                    <option value="" disabled {{ !old('jenis_kendaraan') && !($defaultUnit['jenis_kendaraan'] ?? false) ? 'selected' : '' }}></option>
                    @foreach ($jenisKendaraanList as $value => $label)
                        @php
                            $isDefaultJenis = isset($defaultUnit['jenis_kendaraan']) && (
                                strtolower(str_replace(' ', '', $defaultUnit['jenis_kendaraan'])) === strtolower(str_replace(' ', '', $value)) ||
                                strtolower(str_replace(' ', '', $defaultUnit['jenis_kendaraan'])) === strtolower(str_replace(' ', '', $label))
                            );
                            $isSelected = old('jenis_kendaraan') ? (old('jenis_kendaraan') == $value) : $isDefaultJenis;
                        @endphp
                        <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ============ Nomor Lambung ============ --}}
            <div class="form-group has-caret">
                <label for="nomor_lambung">Nomor Lambung</label>
                <select name="nomor_lambung" id="nomor_lambung" required>
                    <option value="" disabled {{ !old('nomor_lambung') && !($defaultUnit['key'] ?? false) ? 'selected' : '' }}></option>
                    @foreach ($nomorLambungList as $value => $label)
                        @php
                            $isDefaultLambung = isset($defaultUnit['key']) && $defaultUnit['key'] === $value;
                            $isSelected = old('nomor_lambung') ? (old('nomor_lambung') == $value) : $isDefaultLambung;
                        @endphp
                        <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ============ Item Perbaikan ============ --}}
            <div class="form-group">
                <label for="item_perbaikan">Item Perbaikan</label>
                <input type="text" name="item_perbaikan" id="item_perbaikan"
                       value="{{ old('item_perbaikan') }}" required>
            </div>

            {{-- ============ Nama Pemegang/Penanggung Jawab Kendaraan ============ --}}
            <div class="form-group">
                <label for="nama_pemegang">Nama Pemegang/Penanggung Jawab Kendaraan</label>
                <input type="text" name="nama_pemegang" id="nama_pemegang"
                       value="{{ old('nama_pemegang', $currentUser->name ?? '') }}" required>
            </div>

            {{-- ============ NIP Pemegang/Penanggung Jawab Kendaraan ============ --}}
            <div class="form-group">
                <label for="nip_pemegang">NIP Pemegang/Penanggung Jawab Kendaraan</label>
                <input type="text" name="nip_pemegang" id="nip_pemegang"
                       value="{{ old('nip_pemegang', $currentUser->nip ?? '') }}" required>
            </div>

            {{-- ============ Nama Komandan Regu/Kepala Seksi ============ --}}
            <div class="form-group">
                <label for="nama_komandan_regu">Nama Komandan Regu/Kepala Seksi</label>
                <input type="text" name="nama_komandan_regu" id="nama_komandan_regu"
                       value="{{ old('nama_komandan_regu', $defaultDanru->name ?? '') }}" data-autofilled="true" required>
            </div>

            {{-- ============ NIP Komandan Regu/Kepala Seksi ============ --}}
            <div class="form-group">
                <label for="nip_komandan_regu">NIP Komandan Regu/Kepala Seksi</label>
                <input type="text" name="nip_komandan_regu" id="nip_komandan_regu"
                       value="{{ old('nip_komandan_regu', $defaultDanru->nip ?? '') }}" data-autofilled="true" required>
            </div>

            {{-- ============ Nama Kepala Bidang ============ --}}
            <div class="form-group">
                <label for="nama_kepala_bidang">Nama Kepala Bidang</label>
                <input type="text" name="nama_kepala_bidang" id="nama_kepala_bidang"
                       value="{{ old('nama_kepala_bidang', $defaultKabid->name ?? '') }}" data-autofilled="true" required>
            </div>

            {{-- ============ NIP Kepala Bidang ============ --}}
            <div class="form-group">
                <label for="nip_kepala_bidang">NIP Kepala Bidang</label>
                <input type="text" name="nip_kepala_bidang" id="nip_kepala_bidang"
                       value="{{ old('nip_kepala_bidang', $defaultKabid->nip ?? '') }}" data-autofilled="true" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Kirim</button>
            </div>

        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const allUnits = @json($unitList ?? []);
            const unitDetails = @json($unitDetails ?? []);
            const defaultUnitKey = @json($defaultUnit['key'] ?? '');
            const allReguList = @json($allReguList ?? []);
            const danruUsers = @json($danruUsers ?? []);
            const kabidUsers = @json($kabidUsers ?? []);
            const lambungSelect = document.getElementById('nomor_lambung');
            const jenisSelect = document.getElementById('jenis_kendaraan');
            const posSelect = document.getElementById('pos');
            const reguSelect = document.getElementById('regu');
            const bidangSelect = document.getElementById('bidang');
            const namaPemegangInput = document.getElementById('nama_pemegang');
            const namaDanruInput = document.getElementById('nama_komandan_regu');
            const nipDanruInput = document.getElementById('nip_komandan_regu');
            const namaKabidInput = document.getElementById('nama_kepala_bidang');
            const nipKabidInput = document.getElementById('nip_kepala_bidang');

            let isSyncing = false;

            function normalizeKey(str) {
                return (str || '').trim().toLowerCase().replace(/[^a-z0-9]/g, '');
            }

            // Auto-match pejabat (Danru & Kabid) berdasarkan Pos/Regu/Bidang dari Master Data
            function updateOfficialsFromProfile() {
                const selectedPos = posSelect && posSelect.selectedIndex >= 0 ? normalizeKey(posSelect.options[posSelect.selectedIndex].text) : '';
                const selectedRegu = reguSelect && reguSelect.selectedIndex >= 0 ? normalizeKey(reguSelect.options[reguSelect.selectedIndex].text) : '';
                const selectedBidang = bidangSelect && bidangSelect.selectedIndex >= 0 ? bidangSelect.options[bidangSelect.selectedIndex].text.trim().toLowerCase() : '';

                // 1. Cari Danru dari Master Data Regu
                if (allReguList.length > 0) {
                    let matchedRegu = allReguList.find(r => {
                        const rPos = normalizeKey(r.pos || '');
                        const rRegu = normalizeKey(r.nama || '');
                        return (rPos && selectedPos && (rPos.includes(selectedPos) || selectedPos.includes(rPos))) &&
                               (rRegu && selectedRegu && rRegu === selectedRegu);
                    });

                    if (matchedRegu && matchedRegu.danru) {
                        if (namaDanruInput) namaDanruInput.value = matchedRegu.danru;
                        if (nipDanruInput) nipDanruInput.value = matchedRegu.nip_danru || '';
                    } else if (danruUsers.length > 0) {
                        let fallbackDanru = danruUsers.find(u => {
                            const uPos = normalizeKey(u.pos || '');
                            return uPos && selectedPos && (uPos.includes(selectedPos) || selectedPos.includes(uPos));
                        }) || danruUsers[0];

                        if (fallbackDanru) {
                            if (namaDanruInput) namaDanruInput.value = fallbackDanru.name;
                            if (nipDanruInput) nipDanruInput.value = fallbackDanru.nip;
                        }
                    }
                }

                // 2. Cari Kabid dari Data Pejabat
                if (kabidUsers.length > 0) {
                    let matchedKabid = kabidUsers.find(u => {
                        const uBidang = (u.bidang || '').toLowerCase();
                        const uJabatan = (u.jabatan || '').toLowerCase();
                        return (uBidang && selectedBidang && (uBidang.includes(selectedBidang) || selectedBidang.includes(uBidang))) ||
                               (uJabatan && selectedBidang && uJabatan.includes(selectedBidang));
                    }) || kabidUsers[0];

                    if (matchedKabid) {
                        if (namaKabidInput) namaKabidInput.value = matchedKabid.name;
                        if (nipKabidInput) nipKabidInput.value = matchedKabid.nip;
                    }
                }
            }

            if (posSelect) posSelect.addEventListener('change', updateOfficialsFromProfile);
            if (reguSelect) reguSelect.addEventListener('change', updateOfficialsFromProfile);
            if (bidangSelect) bidangSelect.addEventListener('change', updateOfficialsFromProfile);

            // Filter opsi Nomor Lambung berdasarkan Pos & Jenis Kendaraan yang dipilih
            function filterNomorLambung(preserveSelected = true) {
                if (!lambungSelect) return;

                const currentKey = lambungSelect.value;
                const selectedJenis = jenisSelect && jenisSelect.value ? jenisSelect.value.trim().toUpperCase() : '';
                
                let selectedPosKey = '';
                if (posSelect && posSelect.value && posSelect.selectedIndex >= 0) {
                    const optText = posSelect.options[posSelect.selectedIndex].text;
                    selectedPosKey = normalizeKey(optText || posSelect.value);
                }

                lambungSelect.innerHTML = '';

                const placeholderOpt = document.createElement('option');
                placeholderOpt.value = '';
                placeholderOpt.disabled = true;
                placeholderOpt.textContent = '— Pilih No. Lambung —';
                lambungSelect.appendChild(placeholderOpt);

                // Filter unit sesuai Pos DAN Jenis Kendaraan
                const matchingUnits = allUnits.filter(u => {
                    const uJenis = (u.jenis_kendaraan || '').trim().toUpperCase();
                    const uPosKey = normalizeKey(u.pos || '');

                    const matchJenis = !selectedJenis || uJenis === selectedJenis || uJenis.includes(selectedJenis);
                    const matchPos = !selectedPosKey || uPosKey === selectedPosKey || uPosKey.includes(selectedPosKey) || selectedPosKey.includes(uPosKey);

                    return matchJenis && matchPos;
                });

                let hasMatched = false;
                matchingUnits.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.key;
                    opt.textContent = u.clean_label || u.label;
                    if (preserveSelected && ((currentKey && u.key === currentKey) || (!currentKey && defaultUnitKey && u.key === defaultUnitKey))) {
                        opt.selected = true;
                        hasMatched = true;
                    }
                    lambungSelect.appendChild(opt);
                });

                if (!hasMatched) {
                    if (matchingUnits.length > 0) {
                        const targetUnit = (defaultUnitKey && matchingUnits.find(u => u.key === defaultUnitKey)) || matchingUnits[0];
                        targetUnit ? (lambungSelect.value = targetUnit.key) : null;
                        syncUnitDetails();
                    } else {
                        placeholderOpt.selected = true;
                        lambungSelect.value = '';
                    }
                }
            }

            // Sinkronisasi data detail unit (Pos, Bidang, Nama Pemegang) saat Nomor Lambung dipilih
            function syncUnitDetails() {
                const selectedKey = lambungSelect.value;
                if (!selectedKey || !unitDetails[selectedKey]) return;

                isSyncing = true;
                const data = unitDetails[selectedKey];

                // 1. Auto-select Jenis Kendaraan jika belum sesuai
                if (data.jenis_kendaraan && jenisSelect) {
                    const targetJenis = data.jenis_kendaraan.trim().toUpperCase();
                    for (let i = 0; i < jenisSelect.options.length; i++) {
                        const opt = jenisSelect.options[i];
                        if (!opt.value) continue;
                        if (opt.value.trim().toUpperCase() === targetJenis || opt.text.trim().toUpperCase() === targetJenis) {
                            jenisSelect.selectedIndex = i;
                            break;
                        }
                    }
                }

                // 2. Auto-select Pos
                if (data.pos && posSelect) {
                    const targetPos = normalizeKey(data.pos);
                    for (let i = 0; i < posSelect.options.length; i++) {
                        const opt = posSelect.options[i];
                        if (!opt.value) continue;
                        const optVal = normalizeKey(opt.value);
                        const optText = normalizeKey(opt.text);
                        if (optVal === targetPos || optText === targetPos || (optText && optText.includes(targetPos)) || (optVal && targetPos.includes(optVal))) {
                            posSelect.selectedIndex = i;
                            break;
                        }
                    }
                }

                // 3. Auto-select Bidang
                if (bidangSelect && (data.kategori || data.bidang)) {
                    const targetBidang = (data.kategori || data.bidang || '').trim().toLowerCase();
                    if (targetBidang) {
                        for (let i = 0; i < bidangSelect.options.length; i++) {
                            const opt = bidangSelect.options[i];
                            if (!opt.value) continue;
                            if (opt.value.toLowerCase() === targetBidang || opt.text.toLowerCase() === targetBidang) {
                                bidangSelect.selectedIndex = i;
                                break;
                            }
                        }
                    }
                }

                // 4. Auto-fill Nama Pemegang jika masih kosong
                if (namaPemegangInput && !namaPemegangInput.value && data.pengemudi_1) {
                    namaPemegangInput.value = data.pengemudi_1;
                }

                isSyncing = false;
            }

            // Event: Saat Pos diganti -> filter nomor lambung yang sesuai
            if (posSelect) {
                posSelect.addEventListener('change', function () {
                    if (!isSyncing) {
                        filterNomorLambung(false);
                    }
                    updateOfficialsFromProfile();
                });
            }

            // Event: Saat Jenis Kendaraan diganti -> filter nomor lambung yang sesuai
            if (jenisSelect) {
                jenisSelect.addEventListener('change', function () {
                    if (!isSyncing) {
                        filterNomorLambung(false);
                    }
                });
            }

            // Event: Saat Nomor Lambung dipilih -> sinkronkan data unit
            if (lambungSelect) {
                lambungSelect.addEventListener('change', function () {
                    syncUnitDetails();
                });
            }

            // Inisialisasi awal saat load
            updateOfficialsFromProfile();
            filterNomorLambung(true);
            syncUnitDetails();
        });
    </script>
@endsection
