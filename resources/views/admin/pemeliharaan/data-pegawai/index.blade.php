@extends('layouts.admin')

@section('title', 'Data Pegawai & Pejabat — Admin')

@section('content')
<div x-data="pegawaiApp()">

    {{-- Flash Message --}}
    @if(session('success'))
        <div style="background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; padding:12px 16px; border-radius:12px; margin-bottom:20px; font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px;">
                <i data-lucide="check-circle-2" style="width:18px; height:18px; color:#059669;"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Header Section --}}
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Data Pegawai, Pejabat &amp; Petugas Disdamkar</h1>
            <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                Kelola data kepegawaian personil, pejabat pimpinan struktural, dan petugas lapangan.
            </p>
        </div>
        <button type="button" @click="openCreateModal()"
                style="padding:10px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(27,42,107,0.2); transition:all 0.2s;">
            <i data-lucide="user-plus" style="width:16px; height:16px;"></i>
            <span>Tambah Pegawai Baru</span>
        </button>
    </div>

    {{-- Ringkasan KPI Cards --}}
    <div class="kpi-grid-container" style="margin-bottom:24px;">
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#64748B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Pegawai / Personil</div>
            <div style="font-size:24px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total_pegawai'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#1B2A6B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Pejabat Struktural (Petinggi)</div>
            <div style="font-size:24px; font-weight:800; color:#1B2A6B; margin-top:6px;">{{ $kpi['pejabat'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#2563EB; font-size:11.5px; font-weight:700; text-transform:uppercase;">Komandan Regu (Danru)</div>
            <div style="font-size:24px; font-weight:800; color:#2563EB; margin-top:6px;">{{ $kpi['danru'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#059669; font-size:11.5px; font-weight:700; text-transform:uppercase;">Petugas / Pasukan</div>
            <div style="font-size:24px; font-weight:800; color:#059669; margin-top:6px;">{{ $kpi['petugas'] }}</div>
        </div>
    </div>    {{-- Search & Filter Bar --}}
    <div class="admin-filter-bar">
        <form method="GET" action="{{ route('admin.pemeliharaan.data-pegawai') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; width:100%;">
            <div style="font-size:13px; font-weight:700; color:#334155; display:flex; align-items:center; gap:6px;">
                <i data-lucide="users" style="width:16px; height:16px; color:#64748B;"></i>
                <span>Daftar Pegawai Terdaftar ({{ $pegawaiList->total() }} Data)</span>
            </div>

            {{-- Input Pencarian & Filter --}}
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                {{-- Filter Bidang --}}
                <select name="bidang" onchange="this.form.submit()"
                        style="padding:7px 12px; font-size:12px; font-weight:600; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#334155; cursor:pointer;">
                    <option value="semua">Semua Bidang</option>
                    @foreach($existingBidangList as $b)
                        <option value="{{ $b }}" {{ ($bidangFilter ?? '') === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>

                {{-- Filter Pos --}}
                <select name="pos" onchange="this.form.submit()"
                        style="padding:7px 12px; font-size:12px; font-weight:600; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#334155; cursor:pointer;">
                    <option value="semua">Semua Pos</option>
                    @foreach($posList as $p)
                        @php $pName = is_string($p) ? $p : ($p->nama ?? ($p['nama'] ?? '')); @endphp
                        <option value="{{ $pName }}" {{ ($posFilter ?? '') === $pName ? 'selected' : '' }}>{{ $pName }}</option>
                    @endforeach
                </select>

                <div style="position:relative;">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari nama, NIP, atau jabatan..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:220px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
                @if(!empty($searchQuery) || ($bidangFilter ?? 'semua') !== 'semua' || ($posFilter ?? 'semua') !== 'semua')
                    <a href="{{ route('admin.pemeliharaan.data-pegawai') }}" style="padding:7px 12px; background:#F1F5F9; color:#475569; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Data Pegawai --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
        @if($pegawaiList->isEmpty())
            <div style="padding:56px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
                    <i data-lucide="search-x" style="width:30px; height:30px; color:#64748B;"></i>
                </div>
                @if(!empty($searchQuery) || ($bidangFilter ?? 'semua') !== 'semua' || ($posFilter ?? 'semua') !== 'semua')
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Pegawai Tidak Ditemukan</div>
                    <div style="font-size:13px; color:#64748B; margin-bottom:18px; max-width:440px; margin-left:auto; margin-right:auto;">
                        Tidak ditemukan data pegawai yang sesuai dengan filter atau kata kunci yang dipilih.
                    </div>
                    <a href="{{ route('admin.pemeliharaan.data-pegawai') }}" style="display:inline-flex; align-items:center; gap:8px; padding:9px 20px; background:#1B2A6B; color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                        <i data-lucide="rotate-ccw" style="width:14px; height:14px;"></i>
                        <span>Reset Pencarian</span>
                    </a>
                @else
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Pegawai</div>
                    <div style="font-size:12.5px; color:#94A3B8;">Silakan tambahkan data pegawai / pejabat baru.</div>
                @endif
            </div>
        @else
            <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                <table style="width:100%; min-width:880px; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#0F172A; color:#FFFFFF; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px; font-weight:800;">
                            <th style="padding:14px 16px; width:50px; text-align:center;">No</th>
                            <th style="padding:14px 16px;">Nama Pegawai</th>
                            <th style="padding:14px 16px;">NIP</th>
                            <th style="padding:14px 16px;">Jabatan</th>
                            <th style="padding:14px 16px;">Bidang</th>
                            <th style="padding:14px 16px;">Pos Penempatan</th>
                            <th style="padding:14px 16px; width:120px; text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="divide-y:1px solid #F1F5F9;">
                        @foreach($pegawaiList as $index => $item)
                            <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:14px 16px; text-align:center; color:#64748B; font-weight:600;">
                                    {{ $pegawaiList->firstItem() + $index }}
                                </td>
                                <td style="padding:14px 16px; font-weight:700; color:#0F172A;">
                                    {{ $item->name }}
                                </td>
                                <td style="padding:14px 16px;">
                                    @if($item->nip)
                                        <span style="display:inline-block; padding:3px 8px; border-radius:6px; background:#F1F5F9; font-family:monospace; font-weight:700; color:#334155; font-size:12px;">
                                            {{ str_replace(' ', '', $item->nip) }}
                                        </span>
                                    @else
                                        <span style="color:#94A3B8; font-style:italic;">—</span>
                                    @endif
                                </td>
                                <td style="padding:14px 16px;">
                                    @php
                                        $jabatanLower = strtolower($item->jabatan ?? '');
                                        $isPetinggi = preg_match('/(kepala|kabid|kasi|sekretaris|kadis)/i', $jabatanLower);
                                        $isDanru = preg_match('/(danru|komandan)/i', $jabatanLower);
                                    @endphp
                                    @if($isPetinggi)
                                        <span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; background:#FEF3C7; color:#92400E;">
                                            {{ $item->jabatan ?: 'Pejabat' }}
                                        </span>
                                    @elseif($isDanru)
                                        <span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; background:#DBEAFE; color:#1E40AF;">
                                            {{ $item->jabatan ?: 'Danru' }}
                                        </span>
                                    @else
                                        <span style="font-weight:600; color:#334155;">
                                            {{ $item->jabatan ?: 'Petugas' }}
                                        </span>
                                    @endif
                                </td>
                                <td style="padding:14px 16px;">
                                    @php
                                        $bLower = strtolower($item->bidang ?? '');
                                    @endphp
                                    @if(str_contains($bLower, 'pemadam'))
                                        <span style="display:inline-flex; align-items:center; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#FEE2E2; color:#DC2626;">
                                            Pemadam
                                        </span>
                                    @elseif(str_contains($bLower, 'rescue') || str_contains($bLower, 'penyelamatan'))
                                        <span style="display:inline-flex; align-items:center; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#DBEAFE; color:#2563EB;">
                                            Rescue
                                        </span>
                                    @elseif(str_contains($bLower, 'pencegah'))
                                        <span style="display:inline-flex; align-items:center; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#FEF3C7; color:#D97706;">
                                            Pencegahan
                                        </span>
                                    @elseif(str_contains($bLower, 'sarana') || str_contains($bLower, 'spi'))
                                        <span style="display:inline-flex; align-items:center; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#E0E7FF; color:#4F46E5;">
                                            Sarana Prasarana
                                        </span>
                                    @elseif(str_contains($bLower, 'sekretariat'))
                                        <span style="display:inline-flex; align-items:center; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#CFFAFE; color:#0891B2;">
                                            Sekretariat
                                        </span>
                                    @elseif(str_contains($bLower, 'command') || str_contains($bLower, 'cc'))
                                        <span style="display:inline-flex; align-items:center; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#D1FAE5; color:#059669;">
                                            Command Center
                                        </span>
                                    @elseif($item->bidang)
                                        <span style="display:inline-block; padding:3px 8px; border-radius:6px; background:#F1F5F9; font-weight:700; color:#475569; font-size:11.5px;">
                                            {{ $item->bidang }}
                                        </span>
                                    @else
                                        <span style="color:#94A3B8; font-style:italic;">—</span>
                                    @endif
                                </td>
                                <td style="padding:14px 16px;">
                                    @if($item->pos)
                                        <span style="display:inline-flex; align-items:center; gap:5px; font-weight:600; color:#334155; font-size:12px;">
                                            <i data-lucide="map-pin" style="width:13px; height:13px; color:#64748B;"></i>
                                            <span>{{ $item->pos }}</span>
                                        </span>
                                    @else
                                        <span style="color:#94A3B8; font-style:italic;">—</span>
                                    @endif
                                </td>
                                <td style="padding:14px 16px; text-align:center;">
                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                        @if($item->has_account)
                                            {{-- Ikon Ceklis: Pegawai Sudah Memiliki Akun (Tombol buat akun otomatis hilang) --}}
                                            <a href="{{ route('admin.pengaturan', ['search' => $item->nip]) }}"
                                               style="padding:6px 9px; background:#ECFDF5; border:1px solid #A7F3D0; border-radius:8px; color:#059669; cursor:pointer; font-size:12px; transition:all 0.15s; display:inline-flex; align-items:center; justify-content:center; text-decoration:none;"
                                               title="Pegawai sudah memiliki akun login (Klik untuk kelola di Pengaturan Akun)">
                                                <i data-lucide="user-check" style="width:14px; height:14px; color:#059669;"></i>
                                            </a>
                                        @else
                                            {{-- Tombol Buat Akun (Hanya tampil jika belum punya akun) --}}
                                            <a href="{{ route('admin.pengaturan', [
                                                    'generate' => 1,
                                                    'nip'      => $item->nip,
                                                    'name'     => $item->name,
                                                    'jabatan'  => $item->jabatan,
                                                    'bidang'   => $item->bidang,
                                                    'pos'      => $item->pos,
                                                    'regu'     => $item->regu,
                                                    'no_hp'    => $item->no_hp,
                                                ]) }}"
                                               style="padding:6px 9px; background:#F8FAFC; border:1px solid #CBD5E1; border-radius:8px; color:#059669; cursor:pointer; font-size:12px; transition:all 0.15s; display:inline-flex; align-items:center; justify-content:center; text-decoration:none;"
                                               title="Belum Memiliki Akun — Klik untuk Buat / Generate Akun Pengguna">
                                                <i data-lucide="user-plus" style="width:14px; height:14px; color:#059669;"></i>
                                            </a>
                                        @endif

                                        {{-- Tombol Edit --}}
                                        <button type="button"
                                                @click="openEditModal({
                                                    id: {{ $item->id }},
                                                    name: '{{ addslashes($item->name) }}',
                                                    nip: '{{ addslashes($item->nip ?? '') }}',
                                                    jabatan: '{{ addslashes($item->jabatan ?? '') }}',
                                                    bidang: '{{ addslashes($item->bidang ?? '') }}',
                                                    pos: '{{ addslashes($item->pos ?? '') }}',
                                                    regu: '{{ addslashes($item->regu ?? '') }}'
                                                }, '{{ route('admin.pemeliharaan.data-pegawai.update', $item->id) }}')"
                                                style="padding:6px 9px; background:#F8FAFC; border:1px solid #CBD5E1; border-radius:8px; color:#0F172A; cursor:pointer; font-size:12px; transition:all 0.15s;"
                                                title="Edit Data Pegawai">
                                            <i data-lucide="edit-3" style="width:14px; height:14px; color:#2563EB;"></i>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <button type="button"
                                                @click="openDeleteModal({
                                                    id: {{ $item->id }},
                                                    name: '{{ addslashes($item->name) }}',
                                                    nip: '{{ addslashes($item->nip ?? '') }}'
                                                }, '{{ route('admin.pemeliharaan.data-pegawai.destroy', $item->id) }}')"
                                                style="padding:6px 9px; background:#F8FAFC; border:1px solid #CBD5E1; border-radius:8px; color:#DC2626; cursor:pointer; font-size:12px; transition:all 0.15s;"
                                                title="Hapus Pegawai">
                                            <i data-lucide="trash-2" style="width:14px; height:14px; color:#DC2626;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if($pegawaiList->hasPages())
                <div style="padding:16px 20px; border-top:1px solid #E2E8F0; background:#F8FAFC;">
                    {{ $pegawaiList->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- ======================== MODAL TAMBAH PEGAWAI ======================== --}}
    <div x-show="createModalOpen" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px);"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px;">
            <div @click.away="createModalOpen = false" style="background:#FFFFFF; border-radius:20px; max-width:540px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); position:relative; overflow:visible;">
                <div style="padding:20px 24px; background:#0F172A; color:#FFFFFF; display:flex; align-items:center; justify-content:space-between; border-top-left-radius:20px; border-top-right-radius:20px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="user-plus" style="width:18px; height:18px; color:#93C5FD;"></i>
                        </div>
                        <div>
                            <h3 style="font-size:16px; font-weight:800; margin:0;">Tambah Pegawai / Pejabat Baru</h3>
                            <p style="font-size:11.5px; color:#94A3B8; margin:2px 0 0 0;">Data identitas, NIP, jabatan &amp; bidang dinas</p>
                        </div>
                    </div>
                    <button type="button" @click="createModalOpen = false" style="background:transparent; border:none; color:#94A3B8; cursor:pointer; font-size:18px;">✕</button>
                </div>

                <form method="POST" action="{{ route('admin.pemeliharaan.data-pegawai.store') }}" style="padding:24px;">
                    @csrf

                    {{-- Nama Pegawai --}}
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Nama Lengkap &amp; Gelar <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Erpi Suwandi, S.T., M.M." required
                                style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                        @error('name') <p style="font-size:11px; color:#DC2626; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    {{-- NIP --}}
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">NIP (Nomor Induk Pegawai) <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nip" value="{{ old('nip') }}" placeholder="Contoh: 197508142006041009" required
                                style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                        @error('nip') <p style="font-size:11px; color:#DC2626; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    {{-- Jabatan (Ketik Bebas / Dropdown Riwayat) --}}
                    <div x-data="{ open: false }" @click.outside="open = false" style="margin-bottom:14px; position:relative;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                            <label style="font-size:12px; font-weight:700; color:#334155; margin:0;">
                                Jabatan <span style="color:#DC2626;">*</span>
                            </label>
                            <span style="font-size:11px; color:#64748B;">Ketik bebas atau pilih dari dropdown</span>
                        </div>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" name="jabatan" x-model="createJabatan"
                                   placeholder="Contoh: Kepala Bidang SPI / Danru / Petugas" required
                                   style="width:100%; padding:9px 36px 9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;"
                                   @focus="open = true"
                                   @input="open = true">
                            <button type="button" @click.stop="open = !open" tabindex="-1"
                                    style="position:absolute; right:1px; top:1px; bottom:1px; width:34px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                        <div x-show="open && existingJabatanList.length > 0" x-cloak
                             style="position:absolute; top:100%; left:0; right:0; max-height:180px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 12px 28px rgba(15,23,42,0.2); z-index:99999; margin-top:2px; -webkit-overflow-scrolling:touch;">
                            <template x-for="item in (createJabatan ? existingJabatanList.filter(i => i.toLowerCase().includes(createJabatan.toLowerCase())) : existingJabatanList)" :key="item">
                                <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between;"
                                     @click="createJabatan = item; open = false;"
                                     onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                     onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';">
                                    <span x-text="item"></span>
                                    <span x-show="createJabatan === item" style="color:#2563EB; font-size:11px; font-weight:700;">✓</span>
                                </div>
                            </template>
                        </div>
                        @error('jabatan') <p style="font-size:11px; color:#DC2626; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    {{-- Bidang / Bagian (Ketik Bebas / Dropdown Riwayat - Full Width Serupa Jabatan) --}}
                    <div x-data="{ open: false }" @click.outside="open = false" style="margin-bottom:14px; position:relative;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                            <label style="font-size:12px; font-weight:700; color:#334155; margin:0;">
                                Bidang / Bagian <span style="color:#DC2626;">*</span>
                            </label>
                            <span style="font-size:11px; color:#64748B;">Ketik bebas atau pilih dari dropdown</span>
                        </div>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" name="bidang" x-model="createBidang"
                                   placeholder="Contoh: Sarana Prasarana Dan Informasi / Pemadam / Rescue" required
                                   style="width:100%; padding:9px 36px 9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;"
                                   @focus="open = true"
                                   @input="open = true">
                            <button type="button" @click.stop="open = !open" tabindex="-1"
                                    style="position:absolute; right:1px; top:1px; bottom:1px; width:34px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                        <div x-show="open && existingBidangList.length > 0" x-cloak
                             style="position:absolute; top:100%; left:0; right:0; max-height:180px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 12px 28px rgba(15,23,42,0.2); z-index:99999; margin-top:2px; -webkit-overflow-scrolling:touch;">
                            <template x-for="item in (createBidang ? existingBidangList.filter(i => i.toLowerCase().includes(createBidang.toLowerCase())) : existingBidangList)" :key="item">
                                <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between;"
                                     @click="createBidang = item; open = false;"
                                     onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                     onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';">
                                    <span x-text="item"></span>
                                    <span x-show="createBidang === item" style="color:#2563EB; font-size:11px; font-weight:700;">✓</span>
                                </div>
                            </template>
                        </div>
                        @error('bidang') <p style="font-size:11px; color:#DC2626; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    {{-- Pos Penempatan --}}
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Pos Penempatan</label>
                        <select name="pos" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; background:#FFFFFF; box-sizing:border-box;">
                            <option value="">— Pilih Pos Penempatan —</option>
                            @foreach($posList as $p)
                                @php $pName = is_string($p) ? $p : ($p->nama ?? ($p['nama'] ?? '')); @endphp
                                <option value="{{ $pName }}" {{ old('pos') == $pName ? 'selected' : '' }}>{{ $pName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Regu</label>
                            <input type="text" name="regu" value="{{ old('regu') }}" placeholder="Contoh: Regu 1"
                                   style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">No. WhatsApp / HP</label>
                            <input type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="Contoh: 081234567890"
                                   style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                        <button type="button" @click="createModalOpen = false" style="padding:9px 18px; border-radius:8px; border:1px solid #CBD5E1; background:#F8FAFC; color:#475569; font-size:13px; font-weight:600; cursor:pointer;">
                            Batal
                        </button>
                        <button type="submit" style="padding:9px 20px; border-radius:8px; border:none; background:#1B2A6B; color:#FFFFFF; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                            Simpan Data Pegawai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ======================== MODAL EDIT PEGAWAI ======================== --}}
    <div x-show="editModalOpen" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px);"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px;">
            <div @click.away="editModalOpen = false" style="background:#FFFFFF; border-radius:20px; max-width:540px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); position:relative; overflow:visible;">
                <div style="padding:20px 24px; background:#1E3A8A; color:#FFFFFF; display:flex; align-items:center; justify-content:space-between; border-top-left-radius:20px; border-top-right-radius:20px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:34px; height:34px; border-radius:10px; background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="edit-3" style="width:18px; height:18px; color:#93C5FD;"></i>
                        </div>
                        <div>
                            <h3 style="font-size:16px; font-weight:800; margin:0;">Edit Data Pegawai</h3>
                            <p style="font-size:11.5px; color:#94A3B8; margin:2px 0 0 0;">Perbarui nama, NIP, jabatan, bidang &amp; pos dinas</p>
                        </div>
                    </div>
                    <button type="button" @click="editModalOpen = false" style="background:transparent; border:none; color:#94A3B8; cursor:pointer; font-size:18px;">✕</button>
                </div>

                <form method="POST" :action="editUrl" style="padding:24px;">
                    @csrf
                    @method('PUT')

                    {{-- Nama Pegawai --}}
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Nama Lengkap &amp; Gelar <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="name" x-model="activePegawai.name" placeholder="Nama Lengkap" required
                                style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                    </div>

                    {{-- NIP --}}
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">NIP (Nomor Induk Pegawai) <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nip" x-model="activePegawai.nip" placeholder="NIP Pegawai" required
                                style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                    </div>

                    {{-- Jabatan (Ketik Bebas / Dropdown Riwayat) --}}
                    <div x-data="{ open: false }" @click.outside="open = false" style="margin-bottom:14px; position:relative;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                            <label style="font-size:12px; font-weight:700; color:#334155; margin:0;">
                                Jabatan <span style="color:#DC2626;">*</span>
                            </label>
                            <span style="font-size:11px; color:#64748B;">Ketik bebas atau pilih dari dropdown</span>
                        </div>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" name="jabatan" x-model="activePegawai.jabatan" placeholder="Jabatan" required
                                    style="width:100%; padding:9px 36px 9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;"
                                    @focus="open = true"
                                    @input="open = true">
                            <button type="button" @click.stop="open = !open" tabindex="-1"
                                    style="position:absolute; right:1px; top:1px; bottom:1px; width:34px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                        <div x-show="open && existingJabatanList.length > 0" x-cloak
                             style="position:absolute; top:100%; left:0; right:0; max-height:180px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 12px 28px rgba(15,23,42,0.2); z-index:99999; margin-top:2px; -webkit-overflow-scrolling:touch;">
                            <template x-for="item in (activePegawai.jabatan ? existingJabatanList.filter(i => i.toLowerCase().includes(activePegawai.jabatan.toLowerCase())) : existingJabatanList)" :key="item">
                                <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between;"
                                     @click="activePegawai.jabatan = item; open = false;"
                                     onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                     onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';">
                                    <span x-text="item"></span>
                                    <span x-show="activePegawai.jabatan === item" style="color:#2563EB; font-size:11px; font-weight:700;">✓</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Bidang / Bagian (Ketik Bebas / Dropdown Riwayat - Full Width Serupa Jabatan) --}}
                    <div x-data="{ open: false }" @click.outside="open = false" style="margin-bottom:14px; position:relative;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                            <label style="font-size:12px; font-weight:700; color:#334155; margin:0;">
                                Bidang / Bagian <span style="color:#DC2626;">*</span>
                            </label>
                            <span style="font-size:11px; color:#64748B;">Ketik bebas atau pilih dari dropdown</span>
                        </div>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" name="bidang" x-model="activePegawai.bidang" placeholder="Ketik atau pilih bidang..." required
                                   style="width:100%; padding:9px 36px 9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;"
                                   @focus="open = true"
                                   @input="open = true">
                            <button type="button" @click.stop="open = !open" tabindex="-1"
                                    style="position:absolute; right:1px; top:1px; bottom:1px; width:34px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                        <div x-show="open && existingBidangList.length > 0" x-cloak
                             style="position:absolute; top:100%; left:0; right:0; max-height:180px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 12px 28px rgba(15,23,42,0.2); z-index:99999; margin-top:2px; -webkit-overflow-scrolling:touch;">
                            <template x-for="item in (activePegawai.bidang ? existingBidangList.filter(i => i.toLowerCase().includes(activePegawai.bidang.toLowerCase())) : existingBidangList)" :key="item">
                                <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between;"
                                     @click="activePegawai.bidang = item; open = false;"
                                     onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                     onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';">
                                    <span x-text="item"></span>
                                    <span x-show="activePegawai.bidang === item" style="color:#2563EB; font-size:11px; font-weight:700;">✓</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Pos Penempatan --}}
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Pos Penempatan</label>
                        <select name="pos" x-model="activePegawai.pos" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; background:#FFFFFF; box-sizing:border-box;">
                            <option value="">— Pilih Pos Penempatan —</option>
                            @foreach($posList as $p)
                                @php $pName = is_string($p) ? $p : ($p->nama ?? ($p['nama'] ?? '')); @endphp
                                <option value="{{ $pName }}">{{ $pName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Regu</label>
                            <input type="text" name="regu" x-model="activePegawai.regu" placeholder="Contoh: Regu 1"
                                   style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">No. WhatsApp / HP</label>
                            <input type="text" name="no_hp" x-model="activePegawai.no_hp" placeholder="Contoh: 081234567890"
                                   style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px;">
                        <button type="button" @click="editModalOpen = false" style="padding:9px 18px; border-radius:8px; border:1px solid #CBD5E1; background:#F8FAFC; color:#475569; font-size:13px; font-weight:600; cursor:pointer;">
                            Batal
                        </button>
                        <button type="submit" style="padding:9px 20px; border-radius:8px; border:none; background:#1E3A8A; color:#FFFFFF; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(30,58,138,0.25);">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ======================== MODAL HAPUS PEGAWAI ======================== --}}
    <div x-show="deleteModalOpen" style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px);"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px;">
            <div @click.away="deleteModalOpen = false" style="background:#FFFFFF; border-radius:20px; max-width:440px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden;">
                <div style="padding:24px; text-align:center;">
                    <div style="width:52px; height:52px; background:#FEE2E2; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto;">
                        <i data-lucide="alert-triangle" style="width:26px; height:26px; color:#DC2626;"></i>
                    </div>
                    <h3 style="font-size:17px; font-weight:800; color:#0F172A; margin:0 0 6px 0;">Hapus Data Pegawai?</h3>
                    <p style="font-size:13px; color:#64748B; margin:0 0 20px 0;">
                        Apakah Anda yakin ingin menghapus data pegawai <strong style="color:#0F172A;" x-text="activePegawai.name"></strong>?
                    </p>

                    <form method="POST" :action="deleteUrl" style="display:flex; justify-content:center; gap:10px;">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="deleteModalOpen = false" style="padding:9px 18px; border-radius:8px; border:1px solid #CBD5E1; background:#F8FAFC; color:#475569; font-size:13px; font-weight:600; cursor:pointer;">
                            Batal
                        </button>
                        <button type="submit" style="padding:9px 20px; border-radius:8px; border:none; background:#DC2626; color:#FFFFFF; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(220,38,38,0.25);">
                            Ya, Hapus Pegawai
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function pegawaiApp() {
    return {
        createModalOpen: {{ isset($errors) && $errors->any() ? 'true' : 'false' }},
        editModalOpen: false,
        deleteModalOpen: false,
        activePegawai: {
            id: null,
            name: '',
            nip: '',
            jabatan: '',
            bidang: '',
            pos: '',
            regu: ''
        },
        editUrl: '',
        deleteUrl: '',
        createJabatan: @json(old('jabatan', '')),
        createBidang: @json(old('bidang', '')),
        existingJabatanList: @json($existingJabatanList ?? []),
        existingBidangList: @json($existingBidangList ?? []),
        openCreateModal() {
            this.createJabatan = '';
            this.createBidang = '';
            this.createModalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },
        openEditModal(item, updateUrl) {
            this.activePegawai = Object.assign({
                id: null,
                name: '',
                nip: '',
                jabatan: '',
                bidang: '',
                pos: '',
                regu: ''
            }, item);
            this.editUrl = updateUrl;
            this.editModalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },
        openDeleteModal(item, destroyUrl) {
            this.activePegawai = Object.assign({}, item);
            this.deleteUrl = destroyUrl;
            this.deleteModalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        }
    };
}
</script>
@endsection