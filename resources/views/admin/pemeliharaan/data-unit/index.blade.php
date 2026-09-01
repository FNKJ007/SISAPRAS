@extends('layouts.admin')

@section('title', 'Data Unit Kendaraan — Admin')

@section('content')
<div x-data="dataUnitApp()">

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
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Data Unit Kendaraan &amp; Armada Operasional</h1>
            <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                Kelola data armada unit pemadam kebakaran dan unit rescue dinas.
            </p>
        </div>
        <button type="button" @click="createModalOpen = true"
                style="padding:10px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(27,42,107,0.2); transition:all 0.2s;">
            <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
            <span>Tambah Unit Kendaraan</span>
        </button>
    </div>

    {{-- Ringkasan KPI Cards --}}
    <div class="kpi-grid-container" style="margin-bottom:24px;">
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#64748B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Armada Unit</div>
            <div style="font-size:24px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#DC2626; font-size:11.5px; font-weight:700; text-transform:uppercase;">Unit Pemadam</div>
            <div style="font-size:24px; font-weight:800; color:#DC2626; margin-top:6px;">{{ $kpi['pemadam'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#2563EB; font-size:11.5px; font-weight:700; text-transform:uppercase;">Unit Rescue</div>
            <div style="font-size:24px; font-weight:800; color:#2563EB; margin-top:6px;">{{ $kpi['rescue'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#059669; font-size:11.5px; font-weight:700; text-transform:uppercase;">Status Siap / Aktif</div>
            <div style="font-size:24px; font-weight:800; color:#059669; margin-top:6px;">{{ $kpi['aktif'] }}</div>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="admin-filter-bar">
        <form method="GET" action="{{ route('admin.pemeliharaan.data-unit') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                {{-- Filter Kategori --}}
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Kategori:</span>
                    <select name="kategori" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        <option value="semua" @selected($kategoriFilter === 'semua')>Semua Kategori</option>
                        <option value="pemadam" @selected($kategoriFilter === 'pemadam')>Unit Pemadam</option>
                        <option value="rescue" @selected($kategoriFilter === 'rescue')>Unit Rescue</option>
                        <option value="pencegahan" @selected($kategoriFilter === 'pencegahan')>Unit Pencegahan</option>
                        <option value="komando" @selected($kategoriFilter === 'komando')>Unit Komando</option>
                    </select>
                </div>

                {{-- Filter Status --}}
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Status:</span>
                    <select name="status" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        <option value="semua" @selected($statusFilter === 'semua')>Semua Status</option>
                        <option value="aktif" @selected($statusFilter === 'aktif')>Aktif (Siap Operasi)</option>
                        <option value="perbaikan" @selected($statusFilter === 'perbaikan')>Dalam Perbaikan</option>
                        <option value="nonaktif" @selected($statusFilter === 'nonaktif')>Non-Aktif</option>
                    </select>
                </div>

                {{-- Filter Posko (Pos Damkar Kabupaten Bandung) --}}
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Posko:</span>
                    <select name="pos" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        <option value="semua" @selected($posFilter === 'semua')>Semua Posko</option>
                        @foreach($posList as $pos)
                            @php $pName = is_string($pos) ? $pos : ($pos->nama ?? ($pos['nama'] ?? '')); @endphp
                            <option value="{{ $pName }}" @selected($posFilter === $pName)>{{ $pName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Input Pencarian --}}
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="position:relative;">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari lambung, TNKB, merk, pengemudi..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:250px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
            </div>
        </form>
    </div>    {{-- Tabel Data Unit Resmi Infografis --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
        @if($unitList->isEmpty())
            <div style="padding:56px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
                    <i data-lucide="search-x" style="width:30px; height:30px; color:#64748B;"></i>
                </div>
                @if(!empty($searchQuery) || $statusFilter !== 'semua')
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Data Tidak Ditemukan</div>
                    <div style="font-size:13px; color:#64748B; margin-bottom:18px; max-width:440px; margin-left:auto; margin-right:auto;">
                        Tidak ada armada unit yang sesuai dengan kriteria pencarian / filter Anda.
                    </div>
                    <a href="{{ route('admin.pemeliharaan.data-unit') }}" style="display:inline-flex; align-items:center; gap:8px; padding:9px 20px; background:#1B2A6B; color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                        <i data-lucide="rotate-ccw" style="width:14px; height:14px;"></i>
                        <span>Reset Filter &amp; Pencarian</span>
                    </a>
                @else
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Unit</div>
                    <div style="font-size:12.5px; color:#94A3B8;">Belum ada data armada unit kendaraan yang tersimpan.</div>
                @endif
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; min-width:980px; table-layout:auto; border-collapse:collapse; font-size:12.5px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; font-weight:800;">
                            <th style="padding:12px 14px; width:110px;">NO. LAMBUNG</th>
                            <th style="padding:12px 14px; width:110px;">TNKB</th>
                            <th style="padding:12px 14px; width:140px;">JENIS KENDARAAN</th>
                            <th style="padding:12px 14px; width:125px;">KATEGORI</th>
                            <th style="padding:12px 14px; width:140px;">PENEMPATAN</th>
                            <th style="padding:12px 14px; width:180px;">PENGEMUDI 1 &amp; 2</th>
                            <th style="padding:12px 14px; width:145px; text-align:center;">STATUS UNIT</th>
                            <th style="padding:12px 14px; width:140px; text-align:center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unitList as $item)
                            <tr style="border-bottom:1px solid #F1F5F9;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                {{-- No. Lambung --}}
                                <td style="padding:12px 14px; font-weight:800; color:#1E3A8A;">
                                    <span style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; padding:3px 9px; border-radius:6px; font-size:12px; display:inline-block;">
                                        {{ $item->nomor_lambung ?? '—' }}
                                    </span>
                                </td>

                                {{-- TNKB / Plat Nomor --}}
                                <td style="padding:12px 14px; font-weight:700; color:#0F172A; white-space:nowrap;">
                                    {{ $item->plat_nomor ?? '—' }}
                                </td>

                                {{-- Jenis Kendaraan --}}
                                <td style="padding:12px 14px;">
                                    <span style="font-weight:700; font-size:11.5px; color:#0F172A; background:#F1F5F9; padding:3px 8px; border-radius:6px; border:1px solid #E2E8F0; display:inline-block;">
                                        {{ $item->jenis_kendaraan ?: '—' }}
                                    </span>
                                </td>

                                {{-- Kategori --}}
                                <td style="padding:12px 14px; font-weight:600; color:#334155;">
                                    {{ $item->kategori ?: '—' }}
                                </td>

                                {{-- Penempatan (Pos) --}}
                                <td style="padding:12px 14px; font-weight:700; color:#334155;">
                                    <div style="display:flex; align-items:center; gap:4px;">
                                        <i data-lucide="map-pin" style="width:13px; height:13px; color:#DC2626; flex-shrink:0;"></i>
                                        <span>{{ $item->pos ?? '—' }}</span>
                                    </div>
                                </td>

                                {{-- Pengemudi 1 & 2 --}}
                                <td style="padding:12px 14px; font-size:11.5px;">
                                    <div style="font-weight:700; color:#0F172A;">👤 1: {{ $item->pengemudi_1 && $item->pengemudi_1 !== '—' ? $item->pengemudi_1 : '—' }}</div>
                                    <div style="font-weight:600; color:#64748B; margin-top:2px;">👤 2: {{ $item->pengemudi_2 && $item->pengemudi_2 !== '—' && $item->pengemudi_2 !== '0' ? $item->pengemudi_2 : '—' }}</div>
                                </td>

                                {{-- Status Unit (Ready vs Di Bengkel) --}}
                                <td style="padding:12px 14px; text-align:center;">
                                    @if($item->status === 'perbaikan')
                                        <span style="background:#FEF3C7; color:#92400E; border:1px solid #FDE68A; padding:4px 9px; border-radius:20px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;">
                                            <span style="width:6px; height:6px; border-radius:50%; background:#D97706; display:inline-block;"></span>
                                            <span>Dalam Perbaikan</span>
                                        </span>
                                    @elseif($item->status === 'nonaktif')
                                        <span style="background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; padding:4px 9px; border-radius:20px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;">
                                            <span style="width:6px; height:6px; border-radius:50%; background:#64748B; display:inline-block;"></span>
                                            <span>Non-Aktif</span>
                                        </span>
                                    @else
                                        <span style="background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0; padding:4px 9px; border-radius:20px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:4px; white-space:nowrap;">
                                            <span style="width:6px; height:6px; border-radius:50%; background:#10B981; display:inline-block;"></span>
                                            <span>Siap Operasi</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi (Detail, Set Ready, & Hapus) --}}
                                <td style="padding:12px 14px; text-align:center; white-space:nowrap;">
                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                        @if($item->status === 'perbaikan')
                                            <form action="{{ route('admin.pemeliharaan.data-unit.set-ready', $item->id) }}" method="POST" style="display:inline-block; margin:0;">
                                                @csrf
                                                <button type="submit"
                                                        onclick="return confirm('Tandai unit {{ $item->nomor_lambung }} sudah selesai servis di bengkel dan kembali Siap Operasi (Ready)?')"
                                                        title="Tandai unit sudah selesai perbaikan dan kembali Siap Operasi"
                                                        style="padding:5px 10px; background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0; border-radius:7px; font-size:11.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px; box-shadow:0 2px 4px rgba(16,185,129,0.15);">
                                                    <i data-lucide="check-circle" style="width:13px; height:13px; color:#10B981;"></i>
                                                    <span>Set Ready</span>
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" @click="openDetailModal({{ json_encode($item) }})"
                                                style="padding:5px 12px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:7px; font-size:11.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px; box-shadow:0 2px 6px rgba(27,42,107,0.2); transition:all 0.15s;">
                                            <i data-lucide="eye" style="width:13px; height:13px;"></i>
                                            <span>Detail</span>
                                        </button>
                                        <button type="button" @click="
                                            activeUnit = {{ json_encode($item) }};
                                            deleteUrl = '{{ route('admin.pemeliharaan.data-unit.destroy', $item->id) }}';
                                            deleteModalOpen = true;
                                        " style="padding:5px 10px; background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; border-radius:7px; font-size:11.5px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:3px;">
                                            <i data-lucide="trash-2" style="width:12px; height:12px;"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 20px; border-top:1px solid #F1F5F9; background:#FAFCFE;">
                {{ $unitList->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL: TAMBAH UNIT ===================== --}}
    <div x-show="createModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="createModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:580px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Tambah Unit Kendaraan Baru</h3>
                <button type="button" @click="createModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            <form action="{{ route('admin.pemeliharaan.data-unit.store') }}" method="POST" style="padding:20px;">
                @csrf
                @if(isset($errors) && $errors->any())
                    <div style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:12px 16px; border-radius:12px; margin-bottom:18px; font-size:12.5px;">
                        <div style="display:flex; align-items:center; gap:6px; font-weight:800; color:#DC2626; margin-bottom:4px;">
                            <i data-lucide="alert-triangle" style="width:16px; height:16px;"></i>
                            <span>Gagal Menyimpan Data Unit:</span>
                        </div>
                        <ul style="margin:0; padding-left:20px; font-size:12px; font-weight:600;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="modal-form-grid">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. Lambung <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nomor_lambung" required value="{{ old('nomor_lambung') }}" placeholder="Contoh: P-01"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:{{ isset($errors) && $errors->has('nomor_lambung') ? '1.5px solid #DC2626' : '1px solid #CBD5E1' }}; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">TNKB (Plat Nomor) <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="plat_nomor" required value="{{ old('plat_nomor') }}" placeholder="Contoh: D 8518 V"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:{{ isset($errors) && $errors->has('plat_nomor') ? '1.5px solid #DC2626' : '1px solid #CBD5E1' }}; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Unit <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nama" required value="{{ old('nama') }}" placeholder="Contoh: P-01 - HINO (4X4)"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:{{ isset($errors) && $errors->has('nama') ? '1.5px solid #DC2626' : '1px solid #CBD5E1' }}; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Merk / Tipe</label>
                        <input type="text" name="merk_tipe" value="{{ old('merk_tipe') }}" placeholder="Contoh: HINO (4X4)"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. Rangka Mesin</label>
                        <input type="text" name="no_rangka_mesin" value="{{ old('no_rangka_mesin') }}" placeholder="Contoh: FG8JJ1D-BGJ"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>

                    {{-- Jenis Kendaraan (Input Manual + Dropdown Riwayat + Hapus) --}}
                    <div x-data="{ open: false, val: '{{ old('jenis_kendaraan') }}' }" style="position:relative;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Jenis Kendaraan</label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" name="jenis_kendaraan" x-model="val" placeholder="Ketik atau pilih jenis..."
                                   style="width:100%; padding:8px 34px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;"
                                   @focus="if(existingJenisList.length > 0) open = true"
                                   @click.outside="open = false">
                            <button type="button" @click="open = !open" tabindex="-1"
                                    style="position:absolute; right:1px; top:1px; bottom:1px; width:32px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                        <div x-show="open && existingJenisList.length > 0" x-cloak
                             style="position:absolute; top:100%; left:0; right:0; max-height:170px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:1000; margin-top:2px;">
                            <template x-for="item in existingJenisList" :key="item">
                                <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer;"
                                     @click="val = item; open = false;"
                                     onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                     onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';"
                                     x-text="item">
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Kategori (Input Manual + Dropdown Riwayat + Hapus) --}}
                    <div x-data="{ open: false, val: '{{ old('kategori', 'Pemadam') }}' }" style="position:relative;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kategori <span style="color:#DC2626;">*</span></label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" name="kategori" required x-model="val" placeholder="Ketik atau pilih kategori..."
                                   style="width:100%; padding:8px 34px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;"
                                   @focus="if(existingKategoriList.length > 0) open = true"
                                   @click.outside="open = false">
                            <button type="button" @click="open = !open" tabindex="-1"
                                    style="position:absolute; right:1px; top:1px; bottom:1px; width:32px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                        <div x-show="open && existingKategoriList.length > 0" x-cloak
                             style="position:absolute; top:100%; left:0; right:0; max-height:170px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:1000; margin-top:2px;">
                            <template x-for="item in existingKategoriList" :key="item">
                                <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer;"
                                     @click="val = item; open = false;"
                                     onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                     onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';"
                                     x-text="item">
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Penempatan (Pos)</label>
                        <select name="pos" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                            <option value="">— Pilih Pos —</option>
                            @foreach($posList as $pos)
                                @php $pName = is_string($pos) ? $pos : ($pos->nama ?? ($pos['nama'] ?? '')); @endphp
                                <option value="{{ $pName }}" @selected(old('pos') == $pName)>{{ $pName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Tahun Pembuatan</label>
                        <input type="number" name="tahun_pembuatan" value="{{ old('tahun_pembuatan') }}" placeholder="2018"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">CC (Kapasitas Mesin)</label>
                        <input type="number" name="cc" value="{{ old('cc') }}" placeholder="Contoh: 7684" min="0"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    {{-- Pengemudi 1 (Ketik Autocomplete dari DB) --}}
                    <div style="position:relative;"
                         x-data="{
                             open: false,
                             search: '{{ old('pengemudi_1', '') }}',
                             get filteredPegawai() {
                                 if (!this.search || this.search.trim() === '') return [];
                                 let q = this.search.toLowerCase();
                                 return {{ json_encode($pegawaiList) }}.filter(p => 
                                     p.name.toLowerCase().includes(q) || 
                                     (p.nip && p.nip.toLowerCase().includes(q)) ||
                                     (p.jabatan && p.jabatan.toLowerCase().includes(q))
                                 );
                             },
                             select(p) {
                                 this.search = p.name;
                                 this.open = false;
                             }
                         }"
                         @click.away="open = false">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">
                            Pengemudi 1 <span style="font-size:11px; font-weight:500; color:#2563EB;">(Ketik nama)</span>
                        </label>
                        <div style="position:relative;">
                            <input type="text" name="pengemudi_1" x-model="search"
                                   @focus="open = true"
                                   @input="open = true"
                                   placeholder="Ketik nama pengemudi 1..."
                                   autocomplete="off"
                                   style="width:100%; padding:8px 30px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; box-sizing:border-box;">
                            <button type="button" @click="open = !open" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:transparent; border:none; color:#64748B; cursor:pointer;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                        <div x-show="open && filteredPegawai.length > 0"
                             style="display:none; position:absolute; left:0; right:0; top:100%; margin-top:3px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(15,23,42,0.15); max-height:180px; overflow-y:auto; z-index:1000;">
                            <template x-for="item in filteredPegawai" :key="item.id">
                                <div @click="select(item)"
                                     style="padding:8px 12px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                     onmouseover="this.style.background='#EFF6FF'"
                                     onmouseout="this.style.background='transparent'">
                                    <div>
                                        <strong style="display:block; color:#0F172A; font-size:12px;" x-text="item.name"></strong>
                                        <span style="font-size:11px; color:#64748B;" x-text="(item.jabatan || 'Pegawai') + (item.nip ? ' • NIP: ' + item.nip : '')"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Pengemudi 2 (Ketik Autocomplete dari DB) --}}
                    <div style="position:relative;"
                         x-data="{
                             open: false,
                             search: '{{ old('pengemudi_2', '') }}',
                             get filteredPegawai() {
                                 if (!this.search || this.search.trim() === '') return [];
                                 let q = this.search.toLowerCase();
                                 return {{ json_encode($pegawaiList) }}.filter(p => 
                                     p.name.toLowerCase().includes(q) || 
                                     (p.nip && p.nip.toLowerCase().includes(q)) ||
                                     (p.jabatan && p.jabatan.toLowerCase().includes(q))
                                 );
                             },
                             select(p) {
                                 this.search = p.name;
                                 this.open = false;
                             }
                         }"
                         @click.away="open = false">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">
                            Pengemudi 2 <span style="font-size:11px; font-weight:500; color:#2563EB;">(Ketik nama)</span>
                        </label>
                        <div style="position:relative;">
                            <input type="text" name="pengemudi_2" x-model="search"
                                   @focus="open = true"
                                   @input="open = true"
                                   placeholder="Ketik nama pengemudi 2..."
                                   autocomplete="off"
                                   style="width:100%; padding:8px 30px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; box-sizing:border-box;">
                            <button type="button" @click="open = !open" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:transparent; border:none; color:#64748B; cursor:pointer;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                        <div x-show="open && filteredPegawai.length > 0"
                             style="display:none; position:absolute; left:0; right:0; top:100%; margin-top:3px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(15,23,42,0.15); max-height:180px; overflow-y:auto; z-index:1000;">
                            <template x-for="item in filteredPegawai" :key="item.id">
                                <div @click="select(item)"
                                     style="padding:8px 12px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                     onmouseover="this.style.background='#EFF6FF'"
                                     onmouseout="this.style.background='transparent'">
                                    <div>
                                        <strong style="display:block; color:#0F172A; font-size:12px;" x-text="item.name"></strong>
                                        <span style="font-size:11px; color:#64748B;" x-text="(item.jabatan || 'Pegawai') + (item.nip ? ' • NIP: ' + item.nip : '')"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Status Armada --}}
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Status Armada</label>
                        <select name="status" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                            <option value="aktif" @selected(old('status') === 'aktif')>🟢 Siap Operasi / Ready</option>
                            <option value="perbaikan" @selected(old('status') === 'perbaikan')>🟡 Dalam Perbaikan (Bengkel)</option>
                            <option value="nonaktif" @selected(old('status') === 'nonaktif')>⚪ Non-Aktif</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid #E2E8F0; padding-top:14px;">
                    <button type="button" @click="createModalOpen = false"
                            style="padding:8px 16px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:8px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">
                        Simpan Unit
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL: DETAIL & EDIT UNIT KENDARAAN ===================== --}}
    <div x-show="detailModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="detailModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog"
             style="background:#FFFFFF; border-radius:18px; width:100%; max-width:680px; max-height:92vh; overflow-y:auto; box-shadow:0 24px 50px -12px rgba(15,23,42,0.3); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            
            {{-- Modal Header --}}
            <div style="padding:18px 24px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px; height:42px; border-radius:12px; background:#EEF2FF; border:1px solid #C7D2FE; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="truck" style="width:22px; height:22px; color:#1B2A6B;"></i>
                    </div>
                    <div>
                        <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                            <span x-text="detailMode === 'view' ? 'Detail Unit Kendaraan' : 'Edit Data Unit Kendaraan'"></span>
                            <span x-text="activeUnit.nomor_lambung || '—'" style="background:#1B2A6B; color:#FFFFFF; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:700;"></span>
                        </h3>
                        <p style="font-size:12px; color:#64748B; margin:2px 0 0 0;" x-text="detailMode === 'view' ? 'Informasi spesifikasi lengkap & rekam operasional unit armada' : 'Perbarui data spesifikasi dan personel pengemudi unit'"></p>
                    </div>
                </div>
                <button type="button" @click="detailModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:6px; border-radius:8px;">
                    <i data-lucide="x" style="width:20px; height:20px;"></i>
                </button>
            </div>

            {{-- ==================== MODE 1: TAMPILAN DETAIL LENGKAP ==================== --}}
            <template x-if="detailMode === 'view'">
                <div>
                    <div style="padding:22px;">
                        {{-- Identity & Status Banner --}}
                        <div style="background:linear-gradient(135deg, #1B2A6B 0%, #1E3A8A 100%); color:#FFFFFF; border-radius:14px; padding:18px 20px; margin-bottom:20px; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; box-shadow:0 4px 14px rgba(27,42,107,0.25);">
                            <div>
                                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; opacity:0.85;">NOMOR LAMBUNG &amp; TNKB</div>
                                <div style="font-size:22px; font-weight:800; margin-top:2px; display:flex; align-items:center; gap:10px;">
                                    <span x-text="activeUnit.nomor_lambung || '—'"></span>
                                    <span style="font-size:15px; font-weight:700; background:rgba(255,255,255,0.2); padding:2px 10px; border-radius:8px; letter-spacing:0.5px;" x-text="activeUnit.plat_nomor || '—'"></span>
                                </div>
                                <div style="font-size:13px; font-weight:600; opacity:0.9; margin-top:4px;" x-text="activeUnit.nama || '—'"></div>
                            </div>
                            <div>
                                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; opacity:0.85; margin-bottom:4px; text-align:right;">Status Armada</div>
                                <template x-if="activeUnit.status === 'perbaikan'">
                                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px;">
                                        <span style="background:#FEF3C7; color:#92400E; border:1px solid #FDE68A; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:800; display:inline-flex; align-items:center; gap:6px;">
                                            <span style="width:8px; height:8px; border-radius:50%; background:#D97706; display:inline-block;"></span>
                                            <span>Dalam Perbaikan (Bengkel)</span>
                                        </span>
                                        <form :action="'/admin/pemeliharaan/data-unit/' + activeUnit.id + '/set-ready'" method="POST" style="margin:0;">
                                            @csrf
                                            <button type="submit"
                                                    onclick="return confirm('Apakah unit ini sudah selesai perbaikan dan kembali siap beroperasi (Ready)?')"
                                                    style="background:#10B981; color:#FFFFFF; border:none; padding:5px 12px; border-radius:7px; font-size:11.5px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px; box-shadow:0 2px 6px rgba(16,185,129,0.3); transition:all 0.15s ease;">
                                                <i data-lucide="check-circle-2" style="width:13px; height:13px;"></i>
                                                <span>Tandai Selesai Servis (Ready)</span>
                                            </button>
                                        </form>
                                    </div>
                                </template>
                                <template x-if="activeUnit.status === 'nonaktif'">
                                    <span style="background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:800; display:inline-flex; align-items:center; gap:6px;">
                                        <span style="width:8px; height:8px; border-radius:50%; background:#64748B; display:inline-block;"></span>
                                        <span>Non-Aktif</span>
                                    </span>
                                </template>
                                <template x-if="activeUnit.status !== 'perbaikan' && activeUnit.status !== 'nonaktif'">
                                    <span style="background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:800; display:inline-flex; align-items:center; gap:6px;">
                                        <span style="width:8px; height:8px; border-radius:50%; background:#10B981; display:inline-block;"></span>
                                        <span>Siap Operasi / Ready</span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        {{-- Section 1: Identitas & Penempatan --}}
                        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:14px; padding:18px; margin-bottom:16px;">
                            <div style="font-size:12px; font-weight:800; color:#1B2A6B; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                                <i data-lucide="info" style="width:15px; height:15px;"></i>
                                <span>Informasi Umum &amp; Penempatan Pos</span>
                            </div>
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px;">
                                <div>
                                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Jenis Kendaraan</div>
                                    <div style="font-size:13.5px; font-weight:800; color:#0F172A; margin-top:2px;" x-text="activeUnit.jenis_kendaraan || '—'"></div>
                                </div>
                                <div>
                                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Kategori Armada</div>
                                    <div style="font-size:13.5px; font-weight:800; color:#0F172A; margin-top:2px;" x-text="activeUnit.kategori || '—'"></div>
                                </div>
                                <div>
                                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Pos Penempatan</div>
                                    <div style="font-size:13.5px; font-weight:800; color:#DC2626; margin-top:2px; display:flex; align-items:center; gap:4px;">
                                        <i data-lucide="map-pin" style="width:14px; height:14px;"></i>
                                        <span x-text="activeUnit.pos || '—'"></span>
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Merk / Tipe</div>
                                    <div style="font-size:13.5px; font-weight:800; color:#0F172A; margin-top:2px;" x-text="activeUnit.merk_tipe || '—'"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Spesifikasi Teknis --}}
                        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:14px; padding:18px; margin-bottom:16px;">
                            <div style="font-size:12px; font-weight:800; color:#1B2A6B; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                                <i data-lucide="settings-2" style="width:15px; height:15px;"></i>
                                <span>Spesifikasi Mesin &amp; Rangka</span>
                            </div>
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px;">
                                <div>
                                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">No. Rangka Mesin</div>
                                    <div style="font-size:13px; font-weight:800; color:#1E293B; font-family:monospace; margin-top:2px;" x-text="activeUnit.no_rangka_mesin || '—'"></div>
                                </div>
                                <div>
                                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Tahun Pembuatan</div>
                                    <div style="font-size:13.5px; font-weight:800; color:#0F172A; margin-top:2px;" x-text="activeUnit.tahun_pembuatan || '—'"></div>
                                </div>
                                <div>
                                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Kapasitas Mesin (CC)</div>
                                    <div style="font-size:13.5px; font-weight:800; color:#0F172A; margin-top:2px;" x-text="activeUnit.cc ? activeUnit.cc + ' CC' : '—'"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Personel Pengemudi --}}
                        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:14px; padding:18px;">
                            <div style="font-size:12px; font-weight:800; color:#1B2A6B; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                                <i data-lucide="users" style="width:15px; height:15px;"></i>
                                <span>Personel Pengemudi Kendaraan</span>
                            </div>
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px;">
                                <div>
                                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Pengemudi 1</div>
                                    <div style="font-size:13.5px; font-weight:800; color:#0F172A; margin-top:2px;" x-text="'👤 ' + (activeUnit.pengemudi_1 && activeUnit.pengemudi_1 !== '—' ? activeUnit.pengemudi_1 : 'Belum Ditugaskan')"></div>
                                </div>
                                <div>
                                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Pengemudi 2</div>
                                    <div style="font-size:13.5px; font-weight:800; color:#0F172A; margin-top:2px;" x-text="'👤 ' + (activeUnit.pengemudi_2 && activeUnit.pengemudi_2 !== '—' && activeUnit.pengemudi_2 !== '0' ? activeUnit.pengemudi_2 : '—')"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer on Detail View --}}
                    <div style="padding:16px 24px; border-top:1px solid #E2E8F0; background:#FAFAFA; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                        <button type="button" @click="openBukuServis(activeUnit.id); detailModalOpen = false;"
                                style="padding:8px 16px; background:#EEF2FF; color:#1B2A6B; border:1px solid #C7D2FE; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                            <i data-lucide="book-open" style="width:15px; height:15px;"></i>
                            <span>Buka Buku Servis Digital</span>
                        </button>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <template x-if="activeUnit.status === 'perbaikan'">
                                <form :action="'/admin/pemeliharaan/data-unit/' + activeUnit.id + '/set-ready'" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Apakah unit ini sudah selesai perbaikan dan kembali siap beroperasi (Ready)?')"
                                            style="padding:8px 16px; background:#059669; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(5,150,105,0.25);">
                                        <i data-lucide="check-circle" style="width:15px; height:15px;"></i>
                                        <span>Tandai Selesai Servis</span>
                                    </button>
                                </form>
                            </template>
                            <button type="button" @click="detailModalOpen = false"
                                    style="padding:8px 16px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                                Tutup
                            </button>
                            <button type="button" @click="detailMode = 'edit'"
                                    style="padding:8px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                                <i data-lucide="edit-3" style="width:15px; height:15px;"></i>
                                <span>Edit Data Unit</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- ==================== MODE 2: FORMULIR EDIT UNIT ==================== --}}
            <template x-if="detailMode === 'edit'">
                <form :action="editUrl" method="POST">
                    @csrf
                    @method('PUT')
                    <div style="padding:22px;">
                        <div class="modal-form-grid">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. Lambung <span style="color:#DC2626;">*</span></label>
                                <input type="text" name="nomor_lambung" required x-model="activeUnit.nomor_lambung"
                                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">TNKB (Plat Nomor) <span style="color:#DC2626;">*</span></label>
                                <input type="text" name="plat_nomor" required x-model="activeUnit.plat_nomor"
                                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Unit <span style="color:#DC2626;">*</span></label>
                                <input type="text" name="nama" required x-model="activeUnit.nama"
                                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Merk / Tipe</label>
                                <input type="text" name="merk_tipe" x-model="activeUnit.merk_tipe"
                                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. Rangka Mesin</label>
                                <input type="text" name="no_rangka_mesin" x-model="activeUnit.no_rangka_mesin"
                                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                            </div>

                            {{-- Edit: Jenis Kendaraan --}}
                            <div x-data="{ open: false }" style="position:relative;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Jenis Kendaraan</label>
                                <div style="position:relative; display:flex; align-items:center;">
                                    <input type="text" name="jenis_kendaraan" x-model="activeUnit.jenis_kendaraan" placeholder="Ketik atau pilih jenis..."
                                           style="width:100%; padding:8px 34px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;"
                                           @focus="if(existingJenisList.length > 0) open = true"
                                           @click.outside="open = false">
                                    <button type="button" @click="open = !open" tabindex="-1"
                                            style="position:absolute; right:1px; top:1px; bottom:1px; width:32px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                        <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                                    </button>
                                </div>
                                <div x-show="open && existingJenisList.length > 0" x-cloak
                                     style="position:absolute; top:100%; left:0; right:0; max-height:170px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:1000; margin-top:2px;">
                                    <template x-for="item in existingJenisList" :key="item">
                                        <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer;"
                                             @click="activeUnit.jenis_kendaraan = item; open = false;"
                                             onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                             onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';"
                                             x-text="item">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Edit: Kategori --}}
                            <div x-data="{ open: false }" style="position:relative;">
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kategori <span style="color:#DC2626;">*</span></label>
                                <div style="position:relative; display:flex; align-items:center;">
                                    <input type="text" name="kategori" required x-model="activeUnit.kategori" placeholder="Ketik atau pilih kategori..."
                                           style="width:100%; padding:8px 34px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;"
                                           @focus="if(existingKategoriList.length > 0) open = true"
                                           @click.outside="open = false">
                                    <button type="button" @click="open = !open" tabindex="-1"
                                            style="position:absolute; right:1px; top:1px; bottom:1px; width:32px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                        <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                                    </button>
                                </div>
                                <div x-show="open && existingKategoriList.length > 0" x-cloak
                                     style="position:absolute; top:100%; left:0; right:0; max-height:170px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:1000; margin-top:2px;">
                                    <template x-for="item in existingKategoriList" :key="item">
                                        <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer;"
                                             @click="activeUnit.kategori = item; open = false;"
                                             onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                             onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';"
                                             x-text="item">
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Penempatan (Pos)</label>
                                <select name="pos" x-model="activeUnit.pos" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                    <option value="">— Pilih Pos —</option>
                                    @foreach($posList as $pos)
                                        @php $pName = is_string($pos) ? $pos : ($pos->nama ?? ($pos['nama'] ?? '')); @endphp
                                        <option value="{{ $pName }}" :selected="activeUnit.pos === '{{ $pName }}'">{{ $pName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Tahun Pembuatan</label>
                                <input type="number" name="tahun_pembuatan" x-model="activeUnit.tahun_pembuatan"
                                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">CC (Kapasitas Mesin)</label>
                                <input type="text" name="cc" x-model="activeUnit.cc"
                                       style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                            </div>

                            {{-- Edit: Pengemudi 1 --}}
                            <div style="position:relative;"
                                 x-data="{
                                     open: false,
                                     get filteredPegawai() {
                                         if (!activeUnit.pengemudi_1 || activeUnit.pengemudi_1.trim() === '') return [];
                                         let q = activeUnit.pengemudi_1.toLowerCase();
                                         return {{ json_encode($pegawaiList) }}.filter(p => 
                                             p.name.toLowerCase().includes(q) || 
                                             (p.nip && p.nip.toLowerCase().includes(q)) ||
                                             (p.jabatan && p.jabatan.toLowerCase().includes(q))
                                         );
                                     },
                                     select(p) {
                                         activeUnit.pengemudi_1 = p.name;
                                         this.open = false;
                                     }
                                 }"
                                 @click.away="open = false">
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">
                                    Pengemudi 1 <span style="font-size:11px; font-weight:500; color:#2563EB;">(Ketik nama)</span>
                                </label>
                                <div style="position:relative;">
                                    <input type="text" name="pengemudi_1" x-model="activeUnit.pengemudi_1"
                                           @focus="open = true"
                                           @input="open = true"
                                           placeholder="Ketik nama pengemudi 1..."
                                           autocomplete="off"
                                           style="width:100%; padding:8px 30px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; box-sizing:border-box;">
                                    <button type="button" @click="open = !open" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:transparent; border:none; color:#64748B; cursor:pointer;">
                                        <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                                    </button>
                                </div>
                                <div x-show="open && filteredPegawai.length > 0"
                                     style="display:none; position:absolute; left:0; right:0; top:100%; margin-top:3px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(15,23,42,0.15); max-height:180px; overflow-y:auto; z-index:1000;">
                                    <template x-for="item in filteredPegawai" :key="item.id">
                                        <div @click="select(item)"
                                             style="padding:8px 12px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                             onmouseover="this.style.background='#EFF6FF'"
                                             onmouseout="this.style.background='transparent'">
                                            <div>
                                                <strong style="display:block; color:#0F172A; font-size:12px;" x-text="item.name"></strong>
                                                <span style="font-size:11px; color:#64748B;" x-text="(item.jabatan || 'Pegawai') + (item.nip ? ' • NIP: ' + item.nip : '')"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Edit: Pengemudi 2 --}}
                            <div style="position:relative;"
                                 x-data="{
                                     open: false,
                                     get filteredPegawai() {
                                         if (!activeUnit.pengemudi_2 || activeUnit.pengemudi_2.trim() === '') return [];
                                         let q = activeUnit.pengemudi_2.toLowerCase();
                                         return {{ json_encode($pegawaiList) }}.filter(p => 
                                             p.name.toLowerCase().includes(q) || 
                                             (p.nip && p.nip.toLowerCase().includes(q)) ||
                                             (p.jabatan && p.jabatan.toLowerCase().includes(q))
                                         );
                                     },
                                     select(p) {
                                         activeUnit.pengemudi_2 = p.name;
                                         this.open = false;
                                     }
                                 }"
                                 @click.away="open = false">
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">
                                    Pengemudi 2 <span style="font-size:11px; font-weight:500; color:#2563EB;">(Ketik nama)</span>
                                </label>
                                <div style="position:relative;">
                                    <input type="text" name="pengemudi_2" x-model="activeUnit.pengemudi_2"
                                           @focus="open = true"
                                           @input="open = true"
                                           placeholder="Ketik nama pengemudi 2..."
                                           autocomplete="off"
                                           style="width:100%; padding:8px 30px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; box-sizing:border-box;">
                                    <button type="button" @click="open = !open" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:transparent; border:none; color:#64748B; cursor:pointer;">
                                        <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                                    </button>
                                </div>
                                <div x-show="open && filteredPegawai.length > 0"
                                     style="display:none; position:absolute; left:0; right:0; top:100%; margin-top:3px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(15,23,42,0.15); max-height:180px; overflow-y:auto; z-index:1000;">
                                    <template x-for="item in filteredPegawai" :key="item.id">
                                        <div @click="select(item)"
                                             style="padding:8px 12px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                             onmouseover="this.style.background='#EFF6FF'"
                                             onmouseout="this.style.background='transparent'">
                                            <div>
                                                <strong style="display:block; color:#0F172A; font-size:12px;" x-text="item.name"></strong>
                                                <span style="font-size:11px; color:#64748B;" x-text="(item.jabatan || 'Pegawai') + (item.nip ? ' • NIP: ' + item.nip : '')"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Edit: Status Unit / Kesiapan --}}
                            <div>
                                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Status Armada / Kesiapan</label>
                                <select name="status" x-model="activeUnit.status" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                    <option value="aktif">🟢 Siap Operasi / Ready</option>
                                    <option value="perbaikan">🟡 Dalam Perbaikan (Bengkel)</option>
                                    <option value="nonaktif">⚪ Non-Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #E2E8F0; padding-top:16px; margin-top:14px;">
                            <button type="button" @click="detailMode = 'view'"
                                    style="padding:8px 16px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                                <i data-lucide="arrow-left" style="width:14px; height:14px;"></i>
                                <span>Kembali ke Detail</span>
                            </button>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <button type="button" @click="detailModalOpen = false"
                                        style="padding:8px 16px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                                    Batal
                                </button>
                                <button type="submit"
                                        style="padding:8px 20px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                                    <i data-lucide="check" style="width:15px; height:15px;"></i>
                                    <span>Simpan Perubahan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </template>
        </div>
    </div>

    {{-- ===================== MODAL: KONFIRMASI HAPUS ===================== --}}
    <div x-show="deleteModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="deleteModalOpen = false">
        <div class="admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:420px; padding:24px; text-align:center; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="width:48px; height:48px; border-radius:50%; background:#FEE2E2; color:#DC2626; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                <i data-lucide="trash-2" style="width:24px; height:24px;"></i>
            </div>
            <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0 0 6px;">Hapus Data Unit Kendaraan?</h3>
            <p style="font-size:13px; color:#64748B; margin-bottom:20px;">
                Apakah Anda yakin ingin menghapus unit <strong style="color:#0F172A;" x-text="(activeUnit.nomor_lambung || '') + ' (' + (activeUnit.plat_nomor || '') + ')'"></strong>? Tindakan ini tidak dapat dibatalkan.
            </p>
            <form :action="deleteUrl" method="POST" style="display:flex; justify-content:center; gap:10px;">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false"
                        style="padding:8px 18px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                    Batal
                </button>
                <button type="submit"
                        style="padding:8px 18px; background:#DC2626; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">
                    Ya, Hapus Data
                </button>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL: BUKU SERVIS DIGITAL ===================== --}}
    <div x-show="bukuServisModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="bukuServisModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog"
             style="background:#FFFFFF; border-radius:18px; width:100%; max-width:820px; max-height:90vh; overflow-y:auto; box-shadow:0 24px 50px -12px rgba(15,23,42,0.3); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            
            {{-- Modal Header --}}
            <div style="padding:18px 24px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; border-radius:12px; background:#EEF2FF; border:1px solid #C7D2FE; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i data-lucide="book-open" style="width:22px; height:22px; color:#1B2A6B;"></i>
                    </div>
                    <div>
                        <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                            <span>Buku Servis Digital Armada</span>
                            <span x-text="bukuServisData?.unit?.nomor_lambung" style="background:#1B2A6B; color:#FFFFFF; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:700;"></span>
                        </h3>
                        <p style="font-size:12px; color:#64748B; margin:2px 0 0 0;">
                            Rekam Medis Perbaikan &amp; Pemeliharaan Kendaraan Dinas <strong style="color:#1E293B;" x-text="bukuServisData?.unit?.plat_nomor"></strong>
                        </p>
                    </div>
                </div>
                <button type="button" @click="bukuServisModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:6px; border-radius:8px;">
                    <i data-lucide="x" style="width:20px; height:20px;"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div style="padding:22px;">

                {{-- Loading State --}}
                <div x-show="bukuServisLoading" style="text-align:center; padding:40px 20px; color:#64748B;">
                    <div style="display:inline-block; width:32px; height:32px; border:3px solid #E2E8F0; border-top-color:#1B2A6B; border-radius:50%; animation:spin 0.8s linear infinite; margin-bottom:12px;"></div>
                    <div style="font-size:13px; font-weight:600;">Memuat Rekam Medis Servis Armada...</div>
                </div>

                <template x-if="!bukuServisLoading && bukuServisData">
                    <div>
                        {{-- Identity Banner --}}
                        <div style="background:linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 100%); border:1px solid #E2E8F0; border-radius:14px; padding:16px 20px; margin-bottom:20px; display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:14px;">
                            <div>
                                <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Pos Penempatan</div>
                                <div style="font-size:13px; font-weight:800; color:#0F172A; margin-top:2px;" x-text="bukuServisData.unit.pos || '—'"></div>
                            </div>
                            <div>
                                <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Jenis Kendaraan</div>
                                <div style="font-size:13px; font-weight:800; color:#1B2A6B; margin-top:2px;" x-text="bukuServisData.unit.jenis_kendaraan"></div>
                            </div>
                            <div>
                                <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Merk / Tipe</div>
                                <div style="font-size:13px; font-weight:800; color:#334155; margin-top:2px;" x-text="bukuServisData.unit.merk_tipe"></div>
                            </div>
                            <div>
                                <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Pengemudi 1 &amp; 2</div>
                                <div style="font-size:12.5px; font-weight:800; color:#334155; margin-top:2px;" x-text="'1: ' + (bukuServisData.unit.pengemudi_1 || '—') + ' • 2: ' + (bukuServisData.unit.pengemudi_2 || '—')"></div>
                            </div>
                        </div>

                        {{-- KPI Cards --}}
                        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin-bottom:24px;">
                            <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:12px; padding:14px 16px; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
                                <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Total Pengajuan Perbaikan</div>
                                <div style="font-size:20px; font-weight:800; color:#1B2A6B; margin-top:4px;" x-text="bukuServisData.summary.total_pengajuan + ' Kali'"></div>
                            </div>
                            <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:12px; padding:14px 16px; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
                                <div style="font-size:11px; font-weight:700; color:#1B2A6B; text-transform:uppercase;">Total Kartu Kendali Pembayaran</div>
                                <div style="font-size:18px; font-weight:800; color:#1B2A6B; margin-top:4px;" x-text="bukuServisData.summary.total_biaya_pembayaran"></div>
                            </div>
                            <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:12px; padding:14px 16px; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
                                <div style="font-size:11px; font-weight:700; color:#059669; text-transform:uppercase;">Total Kartu Kendali Aktual</div>
                                <div style="font-size:18px; font-weight:800; color:#059669; margin-top:4px;" x-text="bukuServisData.summary.total_biaya_aktual"></div>
                            </div>
                        </div>

                        {{-- Section: Riwayat Pengajuan Perbaikan --}}
                        <div style="margin-bottom:24px;">
                            <h4 style="font-size:13.5px; font-weight:800; color:#0F172A; margin:0 0 10px 0; display:flex; align-items:center; gap:6px;">
                                <i data-lucide="wrench" style="width:16px; height:16px; color:#1B2A6B;"></i>
                                <span>Riwayat Pengajuan Perbaikan</span>
                            </h4>
                            
                            <template x-if="bukuServisData.pengajuan_history.length === 0">
                                <div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:10px; padding:20px; text-align:center; color:#64748B; font-size:12.5px;">
                                    Belum ada rekam medis pengajuan perbaikan untuk unit kendaraan ini.
                                </div>
                            </template>

                            <template x-if="bukuServisData.pengajuan_history.length > 0">
                                <div style="border:1px solid #E2E8F0; border-radius:12px; overflow:hidden;">
                                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                                        <thead style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; color:#475569; font-weight:700;">
                                            <tr>
                                                <th style="padding:10px 14px; text-align:left; width:120px;">Tanggal</th>
                                                <th style="padding:10px 14px; text-align:left;">Item / Kerusakan Perbaikan</th>
                                                <th style="padding:10px 14px; text-align:left;">Pemohon &amp; Pos</th>
                                                <th style="padding:10px 14px; text-align:center; width:110px;">Cetak Dokumen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="p in bukuServisData.pengajuan_history" :key="p.id">
                                                <tr style="border-bottom:1px solid #F1F5F9;">
                                                    <td style="padding:10px 14px; font-weight:700; color:#1E293B; white-space:nowrap;" x-text="p.created_at"></td>
                                                    <td style="padding:10px 14px; font-weight:700; color:#1B2A6B;" x-text="p.item_perbaikan"></td>
                                                    <td style="padding:10px 14px; color:#475569;">
                                                        <div style="font-weight:600;" x-text="p.nama_pemegang"></div>
                                                        <div style="font-size:11px; color:#64748B;" x-text="p.pos"></div>
                                                    </td>
                                                    <td style="padding:10px 14px; text-align:center; white-space:nowrap;">
                                                        <a :href="'/admin/pemeliharaan/cetak-dokumen/' + p.id + '/spk'" target="_blank"
                                                           style="padding:4px 10px; background:#EEF2FF; color:#1B2A6B; border:1px solid #C7D2FE; border-radius:6px; font-size:11px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                                            📄 SPK
                                                        </a>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                        </div>

                        {{-- Section: Hasil Kartu Kendali Pembayaran --}}
                        <div style="margin-bottom:24px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                                <h4 style="font-size:13.5px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:6px;">
                                    <i data-lucide="receipt" style="width:16px; height:16px; color:#1B2A6B;"></i>
                                    <span>Hasil Kartu Kendali Pembayaran (Monitoring Invoice)</span>
                                </h4>
                                <span style="font-size:11.5px; font-weight:700; color:#1B2A6B; background:#EEF2FF; padding:3px 10px; border-radius:8px;" x-text="'Subtotal: ' + bukuServisData.summary.total_biaya_pembayaran"></span>
                            </div>
                            
                            <template x-if="bukuServisData.kendali_pembayaran_history.length === 0">
                                <div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:10px; padding:18px; text-align:center; color:#64748B; font-size:12.5px;">
                                    Belum ada catatan realisasi pada Kartu Kendali Pembayaran untuk unit ini.
                                </div>
                            </template>

                            <template x-if="bukuServisData.kendali_pembayaran_history.length > 0">
                                <div style="border:1px solid #E2E8F0; border-radius:12px; overflow:hidden;">
                                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                                        <thead style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; color:#475569; font-weight:700;">
                                            <tr>
                                                <th style="padding:10px 14px; text-align:left; width:120px;">Tanggal Invoice</th>
                                                <th style="padding:10px 14px; text-align:left;">Nomor Invoice</th>
                                                <th style="padding:10px 14px; text-align:left;">Nama Bengkel</th>
                                                <th style="padding:10px 14px; text-align:right; width:160px;">Total Biaya Realisasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="inv in bukuServisData.kendali_pembayaran_history" :key="inv.id">
                                                <tr style="border-bottom:1px solid #F1F5F9;">
                                                    <td style="padding:10px 14px; font-weight:700; color:#1E293B; white-space:nowrap;" x-text="inv.tanggal_invoice"></td>
                                                    <td style="padding:10px 14px; font-weight:700; color:#1B2A6B;" x-text="inv.nomor_invoice"></td>
                                                    <td style="padding:10px 14px; color:#475569;" x-text="inv.nama_bengkel"></td>
                                                    <td style="padding:10px 14px; text-align:right; font-weight:800; color:#1B2A6B;" x-text="inv.total_biaya"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                        </div>

                        {{-- Section: Hasil Kartu Kendali Aktual --}}
                        <div style="margin-top:20px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                                <h4 style="font-size:13.5px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:6px;">
                                    <i data-lucide="clipboard-list" style="width:16px; height:16px; color:#059669;"></i>
                                    <span>Hasil Kartu Kendali Aktual (Monitoring Aktual)</span>
                                </h4>
                                <span style="font-size:11.5px; font-weight:700; color:#059669; background:#ECFDF5; padding:3px 10px; border-radius:8px;" x-text="'Subtotal: ' + bukuServisData.summary.total_biaya_aktual"></span>
                            </div>
                            
                            <template x-if="bukuServisData.kendali_aktual_history.length === 0">
                                <div style="background:#F8FAFC; border:1px dashed #CBD5E1; border-radius:10px; padding:18px; text-align:center; color:#64748B; font-size:12.5px;">
                                    Belum ada catatan pemeliharaan pada Kartu Kendali Aktual untuk unit ini.
                                </div>
                            </template>

                            <template x-if="bukuServisData.kendali_aktual_history.length > 0">
                                <div style="border:1px solid #E2E8F0; border-radius:12px; overflow:hidden;">
                                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                                        <thead style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; color:#475569; font-weight:700;">
                                            <tr>
                                                <th style="padding:10px 14px; text-align:left; width:120px;">Tanggal</th>
                                                <th style="padding:10px 14px; text-align:left;">Nomor Rujukan / Invoice</th>
                                                <th style="padding:10px 14px; text-align:left;">Nama Bengkel / Pelaksana</th>
                                                <th style="padding:10px 14px; text-align:right; width:160px;">Total Biaya Aktual</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="inv in bukuServisData.kendali_aktual_history" :key="inv.id">
                                                <tr style="border-bottom:1px solid #F1F5F9;">
                                                    <td style="padding:10px 14px; font-weight:700; color:#1E293B; white-space:nowrap;" x-text="inv.tanggal_invoice"></td>
                                                    <td style="padding:10px 14px; font-weight:700; color:#065F46;" x-text="inv.nomor_invoice"></td>
                                                    <td style="padding:10px 14px; color:#475569;" x-text="inv.nama_bengkel"></td>
                                                    <td style="padding:10px 14px; text-align:right; font-weight:800; color:#059669;" x-text="inv.total_biaya"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div style="padding:14px 24px; border-top:1px solid #E2E8F0; background:#FAFAFA; display:flex; align-items:center; justify-content:space-between;">
                        <a :href="'/admin/pemeliharaan/data-unit/' + (bukuServisData?.unit?.id || '') + '/cetak-buku-servis'" target="_blank"
                           style="padding:8px 16px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; text-decoration:none;">
                            <i data-lucide="printer" style="width:14px; height:14px;"></i>
                            <span>Cetak Buku Servis</span>
                        </a>
                        <button type="button" @click="bukuServisModalOpen = false"
                                style="padding:8px 18px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer;">
                            Tutup Buku Servis
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

<script>
function dataUnitApp() {
    return {
        createModalOpen: {{ isset($errors) && $errors->any() ? 'true' : 'false' }},
        detailModalOpen: false,
        detailMode: 'view',
        deleteModalOpen: false,
        bukuServisModalOpen: false,
        bukuServisLoading: false,
        bukuServisData: null,
        activeUnit: {},
        editUrl: '',
        deleteUrl: '',
        existingJenisList: @json($existingJenisList ?? []),
        existingKategoriList: @json($existingKategoriList ?? []),
        openDetailModal(item) {
            this.activeUnit = JSON.parse(JSON.stringify(item));
            this.editUrl = `/admin/pemeliharaan/data-unit/${item.id}`;
            this.detailMode = 'view';
            this.detailModalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });
        },
        openBukuServis(unitId) {
            this.bukuServisModalOpen = true;
            this.bukuServisLoading = true;
            this.bukuServisData = null;

            fetch(`/admin/pemeliharaan/data-unit/${unitId}/riwayat-servis`)
                .then(res => res.json())
                .then(data => {
                    this.bukuServisLoading = false;
                    if (data.success) {
                        this.bukuServisData = data;
                        this.$nextTick(() => {
                            if (window.lucide) lucide.createIcons();
                        });
                    }
                })
                .catch(err => {
                    this.bukuServisLoading = false;
                    console.error("Gagal memuat buku servis:", err);
                });
        },
        removeOption(type, value) {
            if (!confirm("Hapus '" + value + "' dari daftar pilihan riwayat?")) return;

            if (type === 'jenis_kendaraan') {
                this.existingJenisList = this.existingJenisList.filter(item => item !== value);
            } else if (type === 'kategori') {
                this.existingKategoriList = this.existingKategoriList.filter(item => item !== value);
            }

            fetch("{{ route('admin.pemeliharaan.data-unit.remove-history-option') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ type: type, value: value })
            }).then(res => res.json()).then(data => {
                console.log(data.message);
            }).catch(err => console.error(err));
        }
    };
}
</script>
@endsection
