@extends('layouts.admin')

@section('title', 'Data Pegawai & Pejabat — Admin')

@section('content')
<div x-data="{
    createModalOpen: {{ isset($errors) && $errors->any() ? 'true' : 'false' }},
    editModalOpen: false,
    deleteModalOpen: false,
    activePegawai: {},
    editUrl: '',
    deleteUrl: ''
}">

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
        <button type="button" @click="createModalOpen = true"
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
                        <option value="{{ $p->nama }}" {{ ($posFilter ?? '') === $p->nama ? 'selected' : '' }}>{{ $p->nama }}</option>
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
                                <td style="padding:14px 16px;">
                                    <span style="font-weight:800; color:#0F172A; display:inline-flex; align-items:center; gap:8px;">
                                        <div style="width:28px; height:28px; border-radius:50%; background:#1B2A6B; color:#FFFFFF; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;">
                                            {{ strtoupper(substr($item->name, 0, 1)) }}
                                        </div>
                                        <span>{{ $item->name }}</span>
                                    </span>
                                </td>
                                <td style="padding:14px 16px;">
                                    @if($item->nip)
                                        <span style="display:inline-block; padding:3px 8px; border-radius:6px; background:#F1F5F9; font-family:monospace; font-weight:700; color:#334155; font-size:12px;">
                                            {{ $item->nip }}
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
                                        <span style="display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; background:#FEF3C7; color:#92400E;">
                                            <i data-lucide="award" style="width:13px; height:13px;"></i>
                                            <span>{{ $item->jabatan ?: 'Pejabat' }}</span>
                                        </span>
                                    @elseif($isDanru)
                                        <span style="display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; background:#DBEAFE; color:#1E40AF;">
                                            <i data-lucide="shield" style="width:13px; height:13px;"></i>
                                            <span>{{ $item->jabatan ?: 'Danru' }}</span>
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
                                        <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#FEE2E2; color:#DC2626;">
                                            <i data-lucide="flame" style="width:12px; height:12px;"></i>
                                            <span>Pemadam</span>
                                        </span>
                                    @elseif(str_contains($bLower, 'rescue') || str_contains($bLower, 'penyelamatan'))
                                        <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#DBEAFE; color:#2563EB;">
                                            <i data-lucide="life-buoy" style="width:12px; height:12px;"></i>
                                            <span>Rescue</span>
                                        </span>
                                    @elseif(str_contains($bLower, 'pencegah'))
                                        <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#FEF3C7; color:#D97706;">
                                            <i data-lucide="shield-alert" style="width:12px; height:12px;"></i>
                                            <span>Pencegahan</span>
                                        </span>
                                    @elseif(str_contains($bLower, 'sarana') || str_contains($bLower, 'spi'))
                                        <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#E0E7FF; color:#4F46E5;">
                                            <i data-lucide="wrench" style="width:12px; height:12px;"></i>
                                            <span>Sarana Prasarana</span>
                                        </span>
                                    @elseif(str_contains($bLower, 'sekretariat'))
                                        <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#CFFAFE; color:#0891B2;">
                                            <i data-lucide="building-2" style="width:12px; height:12px;"></i>
                                            <span>Sekretariat</span>
                                        </span>
                                    @elseif(str_contains($bLower, 'command') || str_contains($bLower, 'cc'))
                                        <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:6px; font-size:11.5px; font-weight:700; background:#D1FAE5; color:#059669;">
                                            <i data-lucide="radio" style="width:12px; height:12px;"></i>
                                            <span>Command Center</span>
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
                                        {{-- Tombol Edit --}}
                                        <button type="button"
                                                @click="
                                                    activePegawai = {{ json_encode($item) }};
                                                    editUrl = '{{ route('admin.pemeliharaan.data-pegawai.update', $item->id) }}';
                                                    editModalOpen = true;
                                                "
                                                style="padding:6px 9px; background:#F8FAFC; border:1px solid #CBD5E1; border-radius:8px; color:#0F172A; cursor:pointer; font-size:12px; transition:all 0.15s;"
                                                title="Edit Data Pegawai">
                                            <i data-lucide="edit-3" style="width:14px; height:14px; color:#2563EB;"></i>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <button type="button"
                                                @click="
                                                    activePegawai = {{ json_encode($item) }};
                                                    deleteUrl = '{{ route('admin.pemeliharaan.data-pegawai.destroy', $item->id) }}';
                                                    deleteModalOpen = true;
                                                "
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
            <div @click.away="createModalOpen = false" style="background:#FFFFFF; border-radius:20px; max-width:540px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden;">
                <div style="padding:20px 24px; background:#0F172A; color:#FFFFFF; display:flex; align-items:center; justify-content:space-between;">
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

                    {{-- Jabatan --}}
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Jabatan <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="jabatan" value="{{ old('jabatan') }}" placeholder="Contoh: Kepala Bidang SPI / Danru / Petugas" required
                                style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                        @error('jabatan') <p style="font-size:11px; color:#DC2626; margin-top:4px;">{{ $message }}</p> @enderror
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px;">
                        {{-- Bidang --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Bidang / Bagian</label>
                            <select name="bidang" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; background:#FFFFFF; box-sizing:border-box;">
                                <option value="">— Pilih Bidang —</option>
                                @foreach($existingBidangList as $b)
                                    <option value="{{ $b }}" {{ old('bidang') == $b ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Pos Penempatan --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Pos Penempatan</label>
                            <select name="pos" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; background:#FFFFFF; box-sizing:border-box;">
                                <option value="">— Pilih Pos —</option>
                                @foreach($posList as $p)
                                    <option value="{{ $p->nama }}" {{ old('pos') == $p->nama ? 'selected' : '' }}>{{ $p->nama }}</option>
                                @endforeach
                            </select>
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
            <div @click.away="editModalOpen = false" style="background:#FFFFFF; border-radius:20px; max-width:540px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden;">
                <div style="padding:20px 24px; background:#1E3A8A; color:#FFFFFF; display:flex; align-items:center; justify-content:space-between;">
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

                    {{-- Jabatan --}}
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Jabatan <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="jabatan" x-model="activePegawai.jabatan" placeholder="Jabatan" required
                                style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; box-sizing:border-box;">
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:20px;">
                        {{-- Bidang --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Bidang / Bagian</label>
                            <select name="bidang" x-model="activePegawai.bidang" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; background:#FFFFFF; box-sizing:border-box;">
                                <option value="">— Pilih Bidang —</option>
                                @foreach($existingBidangList as $b)
                                    <option value="{{ $b }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Pos Penempatan --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Pos Penempatan</label>
                            <select name="pos" x-model="activePegawai.pos" style="width:100%; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; font-size:13px; outline:none; background:#FFFFFF; box-sizing:border-box;">
                                <option value="">— Pilih Pos —</option>
                                @foreach($posList as $p)
                                    <option value="{{ $p->nama }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
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
@endsection