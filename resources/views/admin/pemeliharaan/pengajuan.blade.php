@extends('layouts.admin')
@section('title', 'Verifikasi Pengajuan Pemeliharaan — Admin')

@section('content')
<div class="space-y-6" x-data="pengajuanAdminModal()">

    {{-- Header Page --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#121E4E; margin:0 0 2px 0;">Verifikasi Pengajuan Pemeliharaan</h1>
            <p style="font-size:13px; color:#64748B; margin:0;">Kelola dan verifikasi usulan pemeliharaan unit operasional dari pengguna/pos.</p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; background:#FFFFFF; padding:6px 14px; border-radius:10px; border:1px solid #E2E8F0; box-shadow:0 2px 6px rgba(0,0,0,0.04);">
            <i data-lucide="shield-check" style="width:16px; height:16px; color:#1B2A6B;"></i>
            <span style="font-size:12.5px; font-weight:600; color:#1E293B;">Panel Verifikasi Admin</span>
        </div>
    </div>

    {{-- Alert Flash Success --}}
    @if(session('success'))
        <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:12px 18px; border-radius:12px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:10px; margin-bottom:20px; box-shadow:0 2px 8px rgba(16,185,129,0.1);">
            <i data-lucide="check-circle-2" style="width:18px; height:18px; color:#10B981;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Ringkasan KPI Cards (Di Mobile tampil 2x2 Kesamping / Ke Kanan) --}}
    <div class="kpi-grid-container">

        {{-- Total --}}
        <a href="{{ route('admin.pemeliharaan.pengajuan', ['status' => 'semua']) }}" style="text-decoration:none; color:inherit;">
            <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #E2E8F0; box-shadow:0 4px 14px rgba(0,0,0,0.03); transition:all 0.2s ease; {{ $statusFilter == 'semua' ? 'border-color:#1B2A6B; ring:2px solid #1B2A6B;' : '' }}">
                <div class="kpi-title" style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Total Pengajuan</div>
                <div class="kpi-number" style="font-size:28px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total'] }}</div>
            </div>
        </a>

        {{-- Menunggu --}}
        <a href="{{ route('admin.pemeliharaan.pengajuan', ['status' => 'menunggu']) }}" style="text-decoration:none; color:inherit;">
            <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #FEF3C7; background:linear-gradient(180deg, #FFFFFF 0%, #FFFBEB 100%); box-shadow:0 4px 14px rgba(217,119,6,0.06); transition:all 0.2s ease; {{ $statusFilter == 'menunggu' ? 'border-color:#D97706;' : '' }}">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div class="kpi-title" style="font-size:11px; font-weight:700; color:#92400E; text-transform:uppercase; letter-spacing:0.5px;">Menunggu Verifikasi</div>
                    <span style="width:8px; height:8px; border-radius:50%; background:#F59E0B;" class="animate-ping"></span>
                </div>
                <div class="kpi-number" style="font-size:28px; font-weight:800; color:#B45309; margin-top:6px;">{{ $kpi['menunggu'] }}</div>
            </div>
        </a>

        {{-- Disetujui --}}
        <a href="{{ route('admin.pemeliharaan.pengajuan', ['status' => 'disetujui']) }}" style="text-decoration:none; color:inherit;">
            <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #D1FAE5; background:linear-gradient(180deg, #FFFFFF 0%, #ECFDF5 100%); box-shadow:0 4px 14px rgba(16,185,129,0.06); transition:all 0.2s ease; {{ $statusFilter == 'disetujui' ? 'border-color:#10B981;' : '' }}">
                <div class="kpi-title" style="font-size:11px; font-weight:700; color:#065F46; text-transform:uppercase; letter-spacing:0.5px;">Disetujui / Diproses</div>
                <div class="kpi-number" style="font-size:28px; font-weight:800; color:#047857; margin-top:6px;">{{ $kpi['disetujui'] }}</div>
            </div>
        </a>

        {{-- Ditolak --}}
        <a href="{{ route('admin.pemeliharaan.pengajuan', ['status' => 'ditolak']) }}" style="text-decoration:none; color:inherit;">
            <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #FEE2E2; background:linear-gradient(180deg, #FFFFFF 0%, #FEF2F2 100%); box-shadow:0 4px 14px rgba(239,68,68,0.06); transition:all 0.2s ease; {{ $statusFilter == 'ditolak' ? 'border-color:#EF4444;' : '' }}">
                <div class="kpi-title" style="font-size:11px; font-weight:700; color:#991B1B; text-transform:uppercase; letter-spacing:0.5px;">Ditolak</div>
                <div class="kpi-number" style="font-size:28px; font-weight:800; color:#B91C1C; margin-top:6px;">{{ $kpi['ditolak'] }}</div>
            </div>
        </a>

    </div>

    {{-- Filter & Search Bar --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; padding:18px 20px; box-shadow:0px 10px 25px rgba(0,0,0,0.03); margin-bottom:20px;">
        <form action="{{ route('admin.pemeliharaan.pengajuan') }}" method="GET" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;">

            {{-- Tabs Status --}}
            <div style="display:flex; align-items:center; gap:6px; background:#F8FAFC; padding:4px; border-radius:10px; border:1px solid #E2E8F0; flex-wrap:wrap;">
                <a href="{{ route('admin.pemeliharaan.pengajuan', ['status' => 'semua', 'search' => $searchQuery]) }}"
                   style="padding:6px 14px; font-size:12px; font-weight:600; border-radius:8px; text-decoration:none; transition:all 0.15s ease; {{ $statusFilter == 'semua' ? 'background:#1B2A6B; color:#FFFFFF; box-shadow:0 2px 6px rgba(27,42,107,0.2);' : 'color:#64748B;' }}">
                    Semua
                </a>
                <a href="{{ route('admin.pemeliharaan.pengajuan', ['status' => 'menunggu', 'search' => $searchQuery]) }}"
                   style="padding:6px 14px; font-size:12px; font-weight:600; border-radius:8px; text-decoration:none; transition:all 0.15s ease; {{ $statusFilter == 'menunggu' ? 'background:#D97706; color:#FFFFFF; box-shadow:0 2px 6px rgba(217,119,6,0.2);' : 'color:#64748B;' }}">
                    Menunggu ({{ $kpi['menunggu'] }})
                </a>
                <a href="{{ route('admin.pemeliharaan.pengajuan', ['status' => 'disetujui', 'search' => $searchQuery]) }}"
                   style="padding:6px 14px; font-size:12px; font-weight:600; border-radius:8px; text-decoration:none; transition:all 0.15s ease; {{ $statusFilter == 'disetujui' ? 'background:#059669; color:#FFFFFF; box-shadow:0 2px 6px rgba(5,150,105,0.2);' : 'color:#64748B;' }}">
                    Disetujui
                </a>
                <a href="{{ route('admin.pemeliharaan.pengajuan', ['status' => 'ditolak', 'search' => $searchQuery]) }}"
                   style="padding:6px 14px; font-size:12px; font-weight:600; border-radius:8px; text-decoration:none; transition:all 0.15s ease; {{ $statusFilter == 'ditolak' ? 'background:#DC2626; color:#FFFFFF; box-shadow:0 2px 6px rgba(220,38,38,0.2);' : 'color:#64748B;' }}">
                    Ditolak
                </a>
            </div>

            {{-- Input Pencarian --}}
            <div style="display:flex; align-items:center; gap:8px;">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <div style="position:relative;">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari unit, pemegang, atau pos..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:240px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
            </div>
        </form>
    </div>

    {{-- Tabel Pengajuan --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
        @if($pengajuanList->isEmpty())
            <div style="padding:48px 20px; text-align:center; color:#64748B;">
                <i data-lucide="inbox" style="width:44px; height:44px; color:#CBD5E1; margin-bottom:12px;"></i>
                <div style="font-size:15px; font-weight:700; color:#334155;">Belum Ada Pengajuan</div>
                <div style="font-size:12.5px; color:#94A3B8; margin-top:4px;">Tidak ada data pengajuan pemeliharaan sesuai kriteria filter saat ini.</div>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">
                            <th style="padding:14px 18px;">No</th>
                            <th style="padding:14px 18px;">Tanggal Pengajuan</th>
                            <th style="padding:14px 18px;">Unit &amp; Pos</th>
                            <th style="padding:14px 18px;">Item Perbaikan &amp; Status Item</th>
                            <th style="padding:14px 18px;">Pemegang Unit</th>
                            <th style="padding:14px 18px;">Status &amp; Jadwal</th>
                            <th style="padding:14px 18px; text-align:center;">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody style="divide-y:1px solid #F1F5F9;">
                        @foreach($pengajuanList as $index => $item)
                            <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s ease;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:14px 18px; font-weight:600; color:#94A3B8;">{{ $pengajuanList->firstItem() + $index }}</td>
                                <td style="padding:14px 18px; white-space:nowrap;">
                                    <div style="font-weight:600; color:#1E293B;">{{ $item->created_at->format('d/m/Y') }}</div>
                                    <div style="font-size:11px; color:#94A3B8;">{{ $item->created_at->format('H:i') }} WIB</div>
                                </td>
                                <td style="padding:14px 18px;">
                                    <div style="font-weight:700; color:#0F172A; text-transform:uppercase;">{{ $item->nomor_lambung }}</div>
                                    <div style="font-size:11.5px; color:#64748B;">Pos {{ $item->pos }} &bull; {{ $item->regu }}</div>
                                </td>
                                <td style="padding:14px 18px; max-width:240px;">
                                    @if(!empty($item->item_verifikasis) && is_array($item->item_verifikasis))
                                        <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                            @foreach($item->item_verifikasis as $itemName => $itemStatus)
                                                @if($itemStatus === 'disetujui')
                                                    <span style="background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:3px;">
                                                        ✓ {{ $itemName }}
                                                    </span>
                                                @else
                                                    <span style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:3px;">
                                                        ✕ {{ $itemName }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="font-weight:600; color:#1E293B;" title="{{ $item->item_perbaikan }}">
                                            {{ $item->item_perbaikan }}
                                        </div>
                                    @endif
                                    <div style="font-size:11px; color:#64748B; margin-top:3px;">Jenis: {{ ucfirst($item->jenis_kendaraan) }}</div>
                                </td>
                                <td style="padding:14px 18px;">
                                    <div style="font-weight:600; color:#1E293B;">{{ $item->nama_pemegang }}</div>
                                    <div style="font-size:11px; color:#94A3B8;">NIP. {{ $item->nip_pemegang }}</div>
                                </td>
                                <td style="padding:14px 18px; white-space:nowrap;">
                                    @if($item->status === 'disetujui')
                                        <span style="background:#D1FAE5; color:#065F46; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; border:1px solid #A7F3D0; display:inline-flex; align-items:center; gap:5px;">
                                            <span style="width:6px; height:6px; border-radius:50%; background:#10B981;"></span> Disetujui
                                        </span>
                                        @if($item->tanggal_keberangkatan)
                                            <div style="font-size:11px; color:#047857; font-weight:600; margin-top:4px; display:flex; align-items:center; gap:4px;">
                                                <i data-lucide="calendar" style="width:12px; height:12px;"></i>
                                                <span>Bengkel: {{ $item->tanggal_keberangkatan->format('d/m/Y') }}</span>
                                            </div>
                                        @endif
                                    @elseif($item->status === 'ditolak')
                                        <span style="background:#FEE2E2; color:#991B1B; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; border:1px solid #FCA5A5; display:inline-flex; align-items:center; gap:5px;">
                                            <span style="width:6px; height:6px; border-radius:50%; background:#EF4444;"></span> Ditolak
                                        </span>
                                    @else
                                        <span style="background:#FEF3C7; color:#92400E; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; border:1px solid #FDE68A; display:inline-flex; align-items:center; gap:5px;">
                                            <span style="width:6px; height:6px; border-radius:50%; background:#F59E0B;"></span> Menunggu
                                        </span>
                                    @endif
                                </td>
                                <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                    <button type="button" @click="openModal({{ json_encode($item) }})"
                                            style="padding:6px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(27,42,107,0.15);">
                                        <i data-lucide="eye" style="width:14px; height:14px;"></i>
                                        <span>Detail &amp; Verifikasi</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Link Pagination --}}
            <div style="padding:16px 20px; border-top:1px solid #F1F5F9; background:#FAFCFE;">
                {{ $pengajuanList->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL VERIFIKASI ADMIN ULTRA-SIMPEL & BERSIH (LOCKED DEAD-CENTER ON MOBILE) --}}
    <div x-show="modalOpen"
         x-cloak
         class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="closeModal()">

        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:540px; max-height:88vh; overflow-y:auto; overflow-x:hidden; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto; text-align:left;" @click.stop>

            {{-- Header Modal --}}
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
                <div>
                    <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Verifikasi Pengajuan Pemeliharaan</h3>
                </div>
                <button type="button" @click="closeModal()" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>

            {{-- Body Detail Ringkas --}}
            <div style="padding:18px 20px; font-size:12.5px;">

                {{-- Detail Informasi Ringkas 2 Kolom --}}
                <div style="display:grid; grid-template-columns:1.1fr 0.9fr; gap:14px; margin-bottom:16px; background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0;">
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Unit &amp; Pos:</span>
                            <strong style="color:#0F172A;" x-text="(activeItem.nomor_lambung || '-') + ' — Pos ' + (activeItem.pos || '-')"></strong>
                        </div>
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Regu:</span>
                            <span style="color:#1E293B; font-weight:700;" x-text="activeItem.regu || '-'"></span>
                        </div>
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Jenis Kendaraan:</span>
                            <span style="color:#1E293B; font-weight:700;" x-text="activeItem.jenis_kendaraan || '-'"></span>
                        </div>
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Bidang:</span>
                            <span style="color:#1E293B; font-weight:700;" x-text="activeItem.bidang || '-'"></span>
                        </div>
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Item Perbaikan:</span>
                            <div style="margin-top:2px;">
                                <template x-if="itemList.length <= 1">
                                    <strong style="color:#C0201F;" x-text="activeItem.item_perbaikan || '-'"></strong>
                                </template>
                                <template x-if="itemList.length > 1">
                                    <span style="color:#C0201F; font-weight:700;" x-text="activeItem.item_perbaikan || '-'"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:8px; border-left:1px solid #E2E8F0; padding-left:12px; font-size:11.5px;">
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Pemegang Unit:</span>
                            <strong style="color:#1E293B;" x-text="activeItem.nama_pemegang || '-'"></strong>
                            <span style="color:#94A3B8; font-size:10.5px; display:block;" x-text="'NIP. ' + (activeItem.nip_pemegang || '-')"></span>
                        </div>
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Komandan Regu:</span>
                            <strong style="color:#1E293B;" x-text="activeItem.nama_komandan_regu || '-'"></strong>
                            <span style="color:#94A3B8; font-size:10.5px; display:block;" x-text="'NIP. ' + (activeItem.nip_komandan_regu || '-')"></span>
                        </div>
                        <div>
                            <span style="color:#64748B; font-size:10.5px; display:block;">Kepala Bidang:</span>
                            <strong style="color:#1E293B;" x-text="activeItem.nama_kepala_bidang || '-'"></strong>
                            <span style="color:#94A3B8; font-size:10.5px; display:block;" x-text="'NIP. ' + (activeItem.nip_kepala_bidang || '-')"></span>
                        </div>
                    </div>
                </div>

                {{-- Form Keputusan Admin --}}
                <form :action="'/admin/pemeliharaan/pengajuan/' + activeItem.id + '/verifikasi'" method="POST">
                    @csrf

                    {{-- VERIFIKASI PER ITEM PERBAIKAN (Hanya Tampil Jika Item Lebih Dari 1) --}}
                    <template x-if="itemList.length > 1">
                        <div style="margin-bottom:14px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:12px;">
                            <div style="font-size:11.5px; font-weight:700; color:#0F172A; margin-bottom:8px; display:flex; align-items:center; justify-content:space-between;">
                                <span x-text="'Verifikasi Keputusan Per Item (' + itemList.length + ' Item)'"></span>
                                <span style="font-size:10.5px; color:#64748B; font-weight:500;">Pilih status perbaikan per item:</span>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:6px;">
                                <template x-for="(itemText, idx) in itemList" :key="idx">
                                    <div style="display:flex; align-items:center; justify-content:space-between; padding:6px 10px; border-radius:8px; background:#FFFFFF; border:1px solid #E2E8F0;">
                                        <span style="font-weight:700; color:#1E293B; font-size:12px;" x-text="itemText"></span>
                                        
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <label style="cursor:pointer; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:700; display:flex; align-items:center; gap:4px; transition:all 0.15s ease;"
                                                   :style="itemVerifikasis[itemText] === 'disetujui' ? 'background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0;' : 'color:#94A3B8; background:#F8FAFC; border:1px solid #E2E8F0;'">
                                                <input type="radio" :name="'item_verifikasis[' + itemText + ']'" value="disetujui" x-model="itemVerifikasis[itemText]" style="accent-color:#10B981;">
                                                <span>Setujui</span>
                                            </label>

                                            <label style="cursor:pointer; padding:3px 8px; border-radius:6px; font-size:11px; font-weight:700; display:flex; align-items:center; gap:4px; transition:all 0.15s ease;"
                                                   :style="itemVerifikasis[itemText] === 'ditolak' ? 'background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5;' : 'color:#94A3B8; background:#F8FAFC; border:1px solid #E2E8F0;'">
                                                <input type="radio" :name="'item_verifikasis[' + itemText + ']'" value="ditolak" x-model="itemVerifikasis[itemText]" style="accent-color:#EF4444;">
                                                <span>Tolak</span>
                                            </label>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Keputusan Keseluruhan Admin --}}
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#0F172A; margin-bottom:6px;">Status Pengajuan Keseluruhan</label>
                        <div style="display:flex; gap:10px;">
                            <label style="flex:1; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; font-weight:700; transition:all 0.15s ease;"
                                   :style="selectedStatus === 'disetujui' ? 'border-color:#10B981; background:#ECFDF5; color:#065F46;' : 'color:#475569; background:#FFFFFF;'">
                                <input type="radio" name="status" value="disetujui" x-model="selectedStatus" required style="accent-color:#10B981;">
                                <span>Disetujui / Ke Bengkel</span>
                            </label>

                            <label style="flex:1; padding:9px 12px; border-radius:8px; border:1px solid #CBD5E1; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; font-weight:700; transition:all 0.15s ease;"
                                   :style="selectedStatus === 'ditolak' ? 'border-color:#EF4444; background:#FEF2F2; color:#991B1B;' : 'color:#475569; background:#FFFFFF;'">
                                <input type="radio" name="status" value="ditolak" x-model="selectedStatus" required style="accent-color:#EF4444;">
                                <span>Ditolak Semua</span>
                            </label>
                        </div>
                    </div>

                    {{-- Tanggal Keberangkatan --}}
                    <div x-show="selectedStatus === 'disetujui'" style="margin-bottom:12px; background:#F0FDF4; padding:10px 12px; border-radius:8px; border:1px solid #BBF7D0;">
                        <label for="tanggal_keberangkatan" style="display:block; font-size:11.5px; font-weight:700; color:#166534; margin-bottom:4px;">Jadwal Keberangkatan Mobil ke Bengkel</label>
                        <input type="date" name="tanggal_keberangkatan" id="tanggal_keberangkatan" x-model="tanggalKeberangkatan"
                               style="width:100%; padding:7px 10px; border-radius:6px; border:1px solid #86EFAC; font-size:12px; font-weight:700; color:#14532D; background:#FFFFFF; outline:none;"
                               :required="selectedStatus === 'disetujui'">
                    </div>

                    {{-- Catatan --}}
                    <div style="margin-bottom:16px;">
                        <label for="catatan_admin" style="display:block; font-size:11.5px; font-weight:600; color:#475569; margin-bottom:4px;">Catatan Admin (Opsional)</label>
                        <textarea name="catatan_admin" id="catatan_admin" rows="2" x-model="catatanAdmin" placeholder="Tambahkan catatan atau alasan..."
                                  style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #CBD5E1; font-size:12px; outline:none; background:#FAFCFE; box-sizing:border-box;"></textarea>
                    </div>

                    {{-- Action Buttons --}}
                    <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid #E2E8F0; padding-top:12px;">
                        <button type="button" @click="closeModal()" style="padding:8px 14px; background:#F1F5F9; color:#475569; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                            Batal
                        </button>
                        <button type="submit" style="padding:8px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; box-shadow:0 2px 6px rgba(27,42,107,0.2);">
                            Simpan Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
.kpi-grid-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

@media (max-width: 768px) {
    .kpi-grid-container {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
        margin-bottom: 18px !important;
    }

    .kpi-card {
        padding: 12px 14px !important;
        height: 100% !important;
        box-sizing: border-box !important;
    }

    .kpi-title {
        font-size: 10px !important;
        line-height: 1.2 !important;
    }

    .kpi-number {
        font-size: 22px !important;
        margin-top: 4px !important;
    }

    .admin-modal-overlay {
        padding: 12px !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .admin-modal-dialog {
        max-height: 85vh !important;
        margin: auto !important;
        width: 100% !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function pengajuanAdminModal() {
    return {
        modalOpen: false,
        activeItem: {},
        selectedStatus: 'disetujui',
        tanggalKeberangkatan: '',
        catatanAdmin: '',
        itemList: [],
        itemVerifikasis: {},

        openModal(item) {
            this.activeItem = item;
            this.selectedStatus = item.status === 'menunggu' ? 'disetujui' : item.status;
            this.tanggalKeberangkatan = item.tanggal_keberangkatan ? item.tanggal_keberangkatan.substring(0, 10) : new Date().toISOString().substring(0, 10);
            this.catatanAdmin = item.catatan_admin || '';

            // Split item_perbaikan by comma / newline
            let items = [];
            if (item.item_perbaikan) {
                items = item.item_perbaikan.split(/[,;\n\r]+/).map(s => s.trim()).filter(Boolean);
            }
            this.itemList = items;

            // Map existing item verifications
            let existingVerifs = item.item_verifikasis || {};
            let map = {};
            items.forEach(it => {
                map[it] = existingVerifs[it] || (this.selectedStatus === 'ditolak' ? 'ditolak' : 'disetujui');
            });
            this.itemVerifikasis = map;

            this.modalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) lucide.createIcons();
            });
        },

        closeModal() {
            this.modalOpen = false;
        }
    };
}
</script>
@endpush
@endsection
