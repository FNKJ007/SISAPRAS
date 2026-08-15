@extends('layouts.app')

@section('title', 'Pengajuan Pemeliharaan')

@section('content')

    @if (session('success'))
        <div class="alert-success" style="margin-bottom:16px;padding:10px 16px;background:#e6f4ea;color:#1e7e34;border-radius:5px;font-size:14px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="form-card">

        <div class="form-card-title">Pengajuan Pemeliharaan Unit Operasional</div>

        <form action="{{ route('pemeliharaan.pengajuan.store') }}" method="POST">
            @csrf

            {{-- ============ Bidang ============ --}}
            <div class="form-group has-caret">
                <label for="bidang">Bidang</label>
                <select name="bidang" id="bidang" required>
                    <option value="" disabled {{ old('bidang') ? '' : 'selected' }}></option>
                    @foreach ($bidangList as $value => $label)
                        <option value="{{ $value }}" {{ old('bidang') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ============ Pos ============ --}}
            <div class="form-group has-caret">
                <label for="pos">Pos</label>
                <select name="pos" id="pos" required>
                    <option value="" disabled {{ old('pos') ? '' : 'selected' }}></option>
                    @foreach ($posList as $value => $label)
                        <option value="{{ $value }}" {{ old('pos') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ============ Regu ============ --}}
            <div class="form-group has-caret">
                <label for="regu">Regu</label>
                <select name="regu" id="regu" required>
                    <option value="" disabled {{ old('regu') ? '' : 'selected' }}></option>
                    @foreach ($reguList as $value => $label)
                        <option value="{{ $value }}" {{ old('regu') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ============ Jenis Kendaraan ============ --}}
            <div class="form-group has-caret">
                <label for="jenis_kendaraan">Jenis Kendaraan</label>
                <select name="jenis_kendaraan" id="jenis_kendaraan" required>
                    <option value="" disabled {{ old('jenis_kendaraan') ? '' : 'selected' }}></option>
                    @foreach ($jenisKendaraanList as $value => $label)
                        <option value="{{ $value }}" {{ old('jenis_kendaraan') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ============ Nomor Lambung ============ --}}
            <div class="form-group has-caret">
                <label for="nomor_lambung">Nomor Lambung</label>
                <select name="nomor_lambung" id="nomor_lambung" required>
                    <option value="" disabled {{ old('nomor_lambung') ? '' : 'selected' }}></option>
                    @foreach ($nomorLambungList as $value => $label)
                        <option value="{{ $value }}" {{ old('nomor_lambung') == $value ? 'selected' : '' }}>
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
                       value="{{ old('nama_pemegang') }}" required>
            </div>

            {{-- ============ NIP Pemegang/Penanggung Jawab Kendaraan ============ --}}
            <div class="form-group">
                <label for="nip_pemegang">NIP Pemegang/Penanggung Jawab Kendaraan</label>
                <input type="text" name="nip_pemegang" id="nip_pemegang"
                       value="{{ old('nip_pemegang') }}" required>
            </div>

            {{-- ============ Nama Komandan Regu/Kepala Seksi ============ --}}
            <div class="form-group">
                <label for="nama_komandan_regu">Nama Komandan Regu/Kepala Seksi</label>
                <input type="text" name="nama_komandan_regu" id="nama_komandan_regu"
                       value="{{ old('nama_komandan_regu') }}" required>
            </div>

            {{-- ============ NIP Komandan Regu/Kepala Seksi ============ --}}
            <div class="form-group">
                <label for="nip_komandan_regu">NIP Komandan Regu/Kepala Seksi</label>
                <input type="text" name="nip_komandan_regu" id="nip_komandan_regu"
                       value="{{ old('nip_komandan_regu') }}" required>
            </div>

            {{-- ============ Nama Kepala Bidang ============ --}}
            <div class="form-group">
                <label for="nama_kepala_bidang">Nama Kepala Bidang</label>
                <input type="text" name="nama_kepala_bidang" id="nama_kepala_bidang"
                       value="{{ old('nama_kepala_bidang') }}" required>
            </div>

            {{-- ============ NIP Kepala Bidang ============ --}}
            <div class="form-group">
                <label for="nip_kepala_bidang">NIP Kepala Bidang</label>
                <input type="text" name="nip_kepala_bidang" id="nip_kepala_bidang"
                       value="{{ old('nip_kepala_bidang') }}" required>
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
            const lambungSelect = document.getElementById('nomor_lambung');
            const jenisSelect = document.getElementById('jenis_kendaraan');
            const posSelect = document.getElementById('pos');
            const bidangSelect = document.getElementById('bidang');
            const namaPemegangInput = document.getElementById('nama_pemegang');

            let isSyncing = false;

            // Filter opsi Nomor Lambung berdasarkan Jenis Kendaraan yang dipilih
            function filterNomorLambung(preserveSelected = true) {
                if (!lambungSelect) return;

                const currentKey = lambungSelect.value;
                const selectedJenis = jenisSelect ? jenisSelect.value.trim().toUpperCase() : '';

                // Simpan daftar opsi lama untuk fallback
                lambungSelect.innerHTML = '';

                const placeholderOpt = document.createElement('option');
                placeholderOpt.value = '';
                placeholderOpt.disabled = true;
                placeholderOpt.textContent = '';
                lambungSelect.appendChild(placeholderOpt);

                // Filter unit sesuai jenis_kendaraan
                const matchingUnits = selectedJenis
                    ? allUnits.filter(u => (u.jenis_kendaraan || '').trim().toUpperCase() === selectedJenis)
                    : allUnits;

                let hasMatched = false;
                matchingUnits.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.key;
                    opt.textContent = u.label;
                    if (preserveSelected && u.key === currentKey) {
                        opt.selected = true;
                        hasMatched = true;
                    }
                    lambungSelect.appendChild(opt);
                });

                if (!hasMatched) {
                    placeholderOpt.selected = true;
                    lambungSelect.value = '';
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
                        if (opt.value.trim().toUpperCase() === targetJenis || opt.text.trim().toUpperCase() === targetJenis) {
                            jenisSelect.selectedIndex = i;
                            break;
                        }
                    }
                }

                // 2. Auto-select Pos
                if (data.pos && posSelect) {
                    const targetPos = data.pos.trim().toLowerCase().replace(/\s+/g, '');
                    for (let i = 0; i < posSelect.options.length; i++) {
                        const opt = posSelect.options[i];
                        const optVal = opt.value.trim().toLowerCase().replace(/\s+/g, '');
                        const optText = opt.text.trim().toLowerCase().replace(/\s+/g, '');
                        if (optVal === targetPos || optText.includes(targetPos) || targetPos.includes(optVal)) {
                            posSelect.selectedIndex = i;
                            break;
                        }
                    }
                }

                // 3. Auto-select Bidang
                if (bidangSelect) {
                    const targetBidang = (data.kategori || '').trim().toLowerCase();
                    for (let i = 0; i < bidangSelect.options.length; i++) {
                        const opt = bidangSelect.options[i];
                        if (opt.value.toLowerCase() === targetBidang) {
                            bidangSelect.selectedIndex = i;
                            break;
                        }
                    }
                }

                // 4. Auto-fill Nama Pemegang jika masih kosong
                if (namaPemegangInput && !namaPemegangInput.value && data.pengemudi_1) {
                    namaPemegangInput.value = data.pengemudi_1;
                }

                isSyncing = false;
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
            if (jenisSelect && jenisSelect.value) {
                filterNomorLambung(true);
            }
        });
    </script>
@endsection
