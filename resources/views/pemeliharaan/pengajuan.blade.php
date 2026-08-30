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
                    <option value="" disabled {{ !old('bidang') && !($currentUser->bidang ?? false) ? 'selected' : '' }}>— Pilih Bidang —</option>
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
                    <option value="" disabled {{ !old('pos') && !($currentUser->pos ?? false) ? 'selected' : '' }}>— Pilih Pos Damkar —</option>
                    @foreach ($posList as $key => $item)
                        @php
                            $posName = is_string($item) ? $item : ($item->nama ?? ($item['nama'] ?? (is_string($key) ? $key : '')));
                            $userPosClean = strtolower(str_replace(' ', '', $currentUser->pos ?? ''));
                            $valPosClean = strtolower(str_replace(' ', '', $posName));
                            $isUserPos = $userPosClean && $valPosClean === $userPosClean;
                            $isSelected = old('pos') ? (old('pos') == $posName) : $isUserPos;
                        @endphp
                        <option value="{{ $posName }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $posName }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ============ Regu ============ --}}
            <div class="form-group has-caret">
                <label for="regu">Regu</label>
                <select name="regu" id="regu" required>
                    <option value="" disabled {{ !old('regu') && !($currentUser->regu ?? false) ? 'selected' : '' }}>— Pilih Regu Sesuai Pos —</option>
                    @if(!empty($currentUser->regu))
                        <option value="{{ $currentUser->regu }}" selected>{{ $currentUser->regu }}</option>
                    @endif
                </select>
            </div>

            {{-- ============ Jenis Kendaraan ============ --}}
            <div class="form-group has-caret">
                <label for="jenis_kendaraan">Jenis Kendaraan</label>
                <select name="jenis_kendaraan" id="jenis_kendaraan" required>
                    <option value="" disabled {{ !old('jenis_kendaraan') && !($defaultUnit['jenis_kendaraan'] ?? false) ? 'selected' : '' }}>— Pilih Jenis Kendaraan —</option>
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
                    <option value="" disabled {{ !old('nomor_lambung') && !($defaultUnit['key'] ?? false) ? 'selected' : '' }}>— Pilih No. Lambung —</option>
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
            <div class="form-group" x-data="comboboxDanru()" style="position:relative;">
                <label for="nama_komandan_regu">Nama Komandan Regu/Kepala Seksi</label>
                <div style="position:relative;">
                    <input type="text"
                           name="nama_komandan_regu"
                           id="nama_komandan_regu"
                           x-model="searchQuery"
                           @focus="open = true"
                           @input="open = true; onType()"
                           placeholder="Ketik atau pilih nama Danru/Kasi..."
                           autocomplete="off"
                           style="padding-right:36px;"
                           required>
                    <button type="button" @click.stop="open = !open"
                            tabindex="-1"
                            style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748B; cursor:pointer; padding:4px; display:flex; align-items:center;">
                        <i data-lucide="chevron-down" style="width:16px; height:16px;"></i>
                    </button>
                </div>

                {{-- Floating Dropdown Suggestion List --}}
                <div x-show="open && filteredList().length > 0"
                     x-cloak
                     @click.outside="open = false"
                     style="position:absolute; left:0; right:0; top:100%; margin-top:4px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; box-shadow:0 10px 25px rgba(15,23,42,0.15); max-height:220px; overflow-y:auto; z-index:99999;">
                    <template x-for="item in filteredList()" :key="item.name">
                        <div @click="selectItem(item)"
                             style="padding:9px 14px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                             onmouseover="this.style.background='#EFF6FF'"
                             onmouseout="this.style.background='transparent'">
                            <div>
                                <strong style="display:block; color:#0F172A; font-size:13px;" x-text="item.name"></strong>
                                <span style="font-size:11px; color:#64748B;" x-text="'NIP: ' + (item.nip || '—') + (item.pos ? ' • Pos ' + item.pos : '')"></span>
                            </div>
                            <span x-show="item.jabatan"
                                  style="font-size:10.5px; font-weight:700; color:#1E40AF; background:#DBEAFE; padding:2px 8px; border-radius:10px;"
                                  x-text="item.jabatan || 'Danru'"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ============ NIP Komandan Regu/Kepala Seksi ============ --}}
            <div class="form-group">
                <label for="nip_komandan_regu">NIP Komandan Regu/Kepala Seksi</label>
                <input type="text" name="nip_komandan_regu" id="nip_komandan_regu"
                       value="{{ old('nip_komandan_regu', $defaultDanru->nip ?? '') }}" readonly required>
            </div>

            {{-- ============ Nama Kepala Bidang ============ --}}
            <div class="form-group" x-data="comboboxKabid()" style="position:relative;">
                <label for="nama_kepala_bidang">Nama Kepala Bidang</label>
                <div style="position:relative;">
                    <input type="text"
                           name="nama_kepala_bidang"
                           id="nama_kepala_bidang"
                           x-model="searchQuery"
                           @focus="open = true"
                           @input="open = true; onType()"
                           placeholder="Ketik atau pilih nama Kepala Bidang..."
                           autocomplete="off"
                           style="padding-right:36px;"
                           required>
                    <button type="button" @click.stop="open = !open"
                            tabindex="-1"
                            style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748B; cursor:pointer; padding:4px; display:flex; align-items:center;">
                        <i data-lucide="chevron-down" style="width:16px; height:16px;"></i>
                    </button>
                </div>

                {{-- Floating Dropdown Suggestion List --}}
                <div x-show="open && filteredList().length > 0"
                     x-cloak
                     @click.outside="open = false"
                     style="position:absolute; left:0; right:0; top:100%; margin-top:4px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; box-shadow:0 10px 25px rgba(15,23,42,0.15); max-height:220px; overflow-y:auto; z-index:99999;">
                    <template x-for="item in filteredList()" :key="item.name">
                        <div @click="selectItem(item)"
                             style="padding:9px 14px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                             onmouseover="this.style.background='#EFF6FF'"
                             onmouseout="this.style.background='transparent'">
                            <div>
                                <strong style="display:block; color:#0F172A; font-size:13px;" x-text="item.name"></strong>
                                <span style="font-size:11px; color:#64748B;" x-text="'NIP: ' + (item.nip || '—') + (item.bidang ? ' • ' + item.bidang : '')"></span>
                            </div>
                            <span x-show="item.jabatan"
                                  style="font-size:10.5px; font-weight:700; color:#065F46; background:#D1FAE5; padding:2px 8px; border-radius:10px;"
                                  x-text="item.jabatan || 'Pejabat'"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ============ NIP Kepala Bidang ============ --}}
            <div class="form-group">
                <label for="nip_kepala_bidang">NIP Kepala Bidang</label>
                <input type="text" name="nip_kepala_bidang" id="nip_kepala_bidang"
                       value="{{ old('nip_kepala_bidang', $defaultKabid->nip ?? '') }}" readonly required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Kirim</button>
            </div>

        </form>
    </div>

    <script>
        window.danruComp = null;
        window.kabidComp = null;

        function comboboxDanru() {
            return {
                open: false,
                searchQuery: @json(old('nama_komandan_regu', $defaultDanru->name ?? '')),
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
                        (item.nip && item.nip.toLowerCase().includes(q)) ||
                        (item.jabatan && item.jabatan.toLowerCase().includes(q)) ||
                        (item.pos && item.pos.toLowerCase().includes(q))
                    );
                },
                selectItem(item) {
                    this.searchQuery = item.name;
                    const nipInput = document.getElementById('nip_komandan_regu');
                    if (nipInput) {
                        nipInput.value = item.nip || '';
                    }
                    this.open = false;
                },
                onType() {
                    const match = this.items.find(i => i.name.toLowerCase() === this.searchQuery.trim().toLowerCase());
                    const nipInput = document.getElementById('nip_komandan_regu');
                    if (nipInput) {
                        nipInput.value = match ? (match.nip || '') : '';
                    }
                }
            };
        }

        function comboboxKabid() {
            return {
                open: false,
                searchQuery: @json(old('nama_kepala_bidang', $defaultKabid->name ?? '')),
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
                        (item.nip && item.nip.toLowerCase().includes(q)) ||
                        (item.jabatan && item.jabatan.toLowerCase().includes(q)) ||
                        (item.bidang && item.bidang.toLowerCase().includes(q))
                    );
                },
                selectItem(item) {
                    this.searchQuery = item.name;
                    const nipInput = document.getElementById('nip_kepala_bidang');
                    if (nipInput) {
                        nipInput.value = item.nip || '';
                    }
                    this.open = false;
                },
                onType() {
                    const match = this.items.find(i => i.name.toLowerCase() === this.searchQuery.trim().toLowerCase());
                    const nipInput = document.getElementById('nip_kepala_bidang');
                    if (nipInput) {
                        nipInput.value = match ? (match.nip || '') : '';
                    }
                }
            };
        }

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
            const nipDanruInput = document.getElementById('nip_komandan_regu');
            const nipKabidInput = document.getElementById('nip_kepala_bidang');

            const userReguDefault = @json(old('regu', $currentUser->regu ?? ''));
            const userBidangDefault = @json(old('bidang', $currentUser->bidang ?? ''));

            function normalizeKey(str) {
                return (str || '').trim().toLowerCase().replace(/[^a-z0-9]/g, '');
            }

            // Filter opsi Regu berdasarkan Pos yang dipilih dari Master Data Regu
            function filterReguOptions(preserveSelected = true) {
                if (!reguSelect) return;
                const currentVal = reguSelect.value;
                const selectedPosText = posSelect && posSelect.selectedIndex >= 0 ? posSelect.options[posSelect.selectedIndex].text : (posSelect ? posSelect.value : '');
                const selectedPosKey = normalizeKey(selectedPosText);
                const userReguKey = normalizeKey(userReguDefault);
                const currentValKey = normalizeKey(currentVal);

                reguSelect.innerHTML = '';

                const placeholderOpt = document.createElement('option');
                placeholderOpt.value = '';
                placeholderOpt.disabled = true;
                placeholderOpt.textContent = '— Pilih Regu Sesuai Pos —';
                reguSelect.appendChild(placeholderOpt);

                // Filter regu yang terdaftar di pos ini dari Master Data Regu
                let matchingRegus = allReguList.filter(r => {
                    const rPosKey = normalizeKey(r.pos || '');
                    return !selectedPosKey || rPosKey === selectedPosKey || rPosKey.includes(selectedPosKey) || selectedPosKey.includes(rPosKey);
                });

                let selectedOptValue = null;

                if (matchingRegus.length > 0) {
                    matchingRegus.forEach(r => {
                        const opt = document.createElement('option');
                        opt.value = r.nama;
                        let label = r.nama;
                        if (r.bidang) label += ' (' + r.bidang + ')';
                        if (r.danru) label += ' — Danru: ' + r.danru;
                        opt.textContent = label;

                        const rKey = normalizeKey(r.nama);
                        // Prioritas 1: Sesuai nilai yang sudah dipilih sebelumnya jika masih valid di pos ini
                        // Prioritas 2: Sesuai data Regu dari akun pengguna yang sedang login
                        if (preserveSelected && currentValKey && (rKey === currentValKey || rKey.includes(currentValKey) || currentValKey.includes(rKey))) {
                            opt.selected = true;
                            selectedOptValue = r.nama;
                        } else if (!selectedOptValue && userReguKey && (rKey === userReguKey || rKey.includes(userReguKey) || userReguKey.includes(rKey))) {
                            opt.selected = true;
                            selectedOptValue = r.nama;
                        }

                        reguSelect.appendChild(opt);
                    });
                } else {
                    // Fallback opsi standar jika data belum diisi di Master Data
                    ['Regu 1', 'Regu 2', 'Regu 3', 'Regu 4'].forEach(nama => {
                        const opt = document.createElement('option');
                        opt.value = nama;
                        opt.textContent = nama;
                        const nKey = normalizeKey(nama);

                        if (preserveSelected && currentValKey && (nKey === currentValKey || nKey.includes(currentValKey) || currentValKey.includes(nKey))) {
                            opt.selected = true;
                            selectedOptValue = nama;
                        } else if (!selectedOptValue && userReguKey && (nKey === userReguKey || nKey.includes(userReguKey) || userReguKey.includes(nKey))) {
                            opt.selected = true;
                            selectedOptValue = nama;
                        }

                        reguSelect.appendChild(opt);
                    });
                }

                if (selectedOptValue) {
                    reguSelect.value = selectedOptValue;
                } else {
                    placeholderOpt.selected = true;
                    reguSelect.value = '';
                }

                updateOfficialsFromProfile();
            }

            // Auto-match pejabat (Danru & Kabid) berdasarkan Pos/Regu/Bidang dari Master Data
            function updateOfficialsFromProfile() {
                const selectedPos = posSelect && posSelect.selectedIndex >= 0 ? normalizeKey(posSelect.options[posSelect.selectedIndex].text) : '';
                const selectedRegu = reguSelect && reguSelect.selectedIndex >= 0 ? normalizeKey(reguSelect.options[reguSelect.selectedIndex].value || reguSelect.options[reguSelect.selectedIndex].text) : '';
                const selectedBidang = bidangSelect && bidangSelect.selectedIndex >= 0 ? bidangSelect.options[bidangSelect.selectedIndex].text.trim().toLowerCase() : '';

                // 1. Cari Danru dari Master Data Regu sesuai Pos & Regu yang dipilih
                if (allReguList.length > 0 && selectedPos && selectedRegu) {
                    let matchedRegu = allReguList.find(r => {
                        const rPos = normalizeKey(r.pos || '');
                        const rRegu = normalizeKey(r.nama || '');
                        return (rPos && selectedPos && (rPos.includes(selectedPos) || selectedPos.includes(rPos))) &&
                               (rRegu && selectedRegu && rRegu === selectedRegu);
                    });

                    if (matchedRegu && matchedRegu.danru) {
                        if (window.danruComp) {
                            window.danruComp.searchQuery = matchedRegu.danru;
                        }
                        if (nipDanruInput) {
                            nipDanruInput.value = matchedRegu.nip_danru || '';
                        }
                    }
                } else if (!selectedRegu) {
                    if (window.danruComp && !userReguDefault) {
                        window.danruComp.searchQuery = '';
                    }
                    if (nipDanruInput && !userReguDefault) {
                        nipDanruInput.value = '';
                    }
                }

                // 2. Cari Kabid dari Data Pejabat, lalu cocokkan ke input Kabid
                if (kabidUsers.length > 0) {
                    let matchedKabid = kabidUsers.find(u => {
                        const uBidang = (u.bidang || '').toLowerCase();
                        const uJabatan = (u.jabatan || '').toLowerCase();
                        return (uBidang && selectedBidang && (uBidang.includes(selectedBidang) || selectedBidang.includes(uBidang))) ||
                               (uJabatan && selectedBidang && uJabatan.includes(selectedBidang));
                    }) || kabidUsers[0];

                    if (matchedKabid) {
                        if (window.kabidComp) {
                            window.kabidComp.searchQuery = matchedKabid.name;
                        }
                        if (nipKabidInput) {
                            nipKabidInput.value = matchedKabid.nip || '';
                        }
                    }
                }
            }

            // Filter opsi Nomor Lambung berdasarkan Pos yang dipilih
            function filterNomorLambung(preserveSelected = true) {
                if (!lambungSelect) return;

                const currentKey = lambungSelect.value;
                
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

                // Filter unit sesuai Pos yang dipilih
                const matchingUnits = allUnits.filter(u => {
                    const uPosKey = normalizeKey(u.pos || '');
                    const matchPos = !selectedPosKey || uPosKey === selectedPosKey || uPosKey.includes(selectedPosKey) || selectedPosKey.includes(uPosKey);
                    return matchPos;
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
                    if (defaultUnitKey && matchingUnits.length > 0) {
                        // Hanya auto-select jika user adalah pengemudi yang punya unit default
                        const targetUnit = matchingUnits.find(u => u.key === defaultUnitKey) || matchingUnits[0];
                        if (targetUnit) {
                            lambungSelect.value = targetUnit.key;
                            syncUnitDetails();
                        }
                    } else {
                        // User bukan pengemudi -> biarkan kosong (placeholder)
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

                // 3. Auto-select Bidang (Hanya jika pengguna belum memiliki Bidang di profil akunnya)
                if (bidangSelect && !userBidangDefault && (data.kategori || data.bidang)) {
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

            // Event: Saat Pos diganti -> filter nomor lambung & regu yang sesuai
            if (posSelect) {
                posSelect.addEventListener('change', function () {
                    if (!isSyncing) {
                        filterReguOptions(false);
                        filterNomorLambung(false);
                    }
                    updateOfficialsFromProfile();
                });
            }

            // Event: Saat Regu diganti -> sinkronkan Danru
            if (reguSelect) {
                reguSelect.addEventListener('change', function () {
                    updateOfficialsFromProfile();
                });
            }

            // Event: Saat Bidang diganti -> update Kabid & filter regu
            if (bidangSelect) {
                bidangSelect.addEventListener('change', function () {
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
            filterReguOptions(true);
            filterNomorLambung(true);
            syncUnitDetails();
            updateOfficialsFromProfile();
        });
    </script>
@endsection
