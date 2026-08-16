@extends('layouts.admin')

@section('title', 'Pengaturan & Manajemen Akun — Admin')

@section('content')
<div x-data="pengaturanApp()">

    {{-- Flash Message --}}
    @if(session('success'))
        <div style="background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; padding:12px 16px; border-radius:12px; margin-bottom:20px; font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px;">
                <i data-lucide="check-circle-2" style="width:18px; height:18px; color:#059669;"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:12px 16px; border-radius:12px; margin-bottom:20px; font-size:13px; font-weight:600; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px;">
                <i data-lucide="alert-circle" style="width:18px; height:18px; color:#DC2626;"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Navigation Tabs --}}
    <div style="display:flex; align-items:center; gap:8px; border-bottom:2px solid #E2E8F0; margin-bottom:24px; padding-bottom:2px;">
        <button type="button" @click="activeTab = 'users'"
                :style="activeTab === 'users' ? 'color:#1B2A6B; border-bottom:3px solid #1B2A6B; font-weight:800;' : 'color:#64748B; font-weight:600;'"
                style="padding:10px 18px; background:none; border:none; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all 0.2s; margin-bottom:-2px;">
            <i data-lucide="users" style="width:18px; height:18px;"></i>
            <span>Manajemen &amp; Generate Akun</span>
        </button>

        <button type="button" @click="activeTab = 'view_user'"
                :style="activeTab === 'view_user' ? 'color:#1B2A6B; border-bottom:3px solid #1B2A6B; font-weight:800;' : 'color:#64748B; font-weight:600;'"
                style="padding:10px 18px; background:none; border:none; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:all 0.2s; margin-bottom:-2px;">
            <i data-lucide="eye" style="width:18px; height:18px;"></i>
            <span>Akses Tampilan User</span>
        </button>
    </div>

    {{-- ================= TAB 1: MANAJEMEN & GENERATE AKUN ================= --}}
    <div x-show="activeTab === 'users'">
        
        {{-- Header & Button --}}
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px;">
            <div>
                <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Manajemen &amp; Generate Akun</h1>
                <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                    Buat akun baru petugas, atur NIP, Jabatan, Bidang, Pos, Regu, dan kredensial login operasional.
                </p>
            </div>
            <button type="button" @click="
                createNip = '';
                createName = '';
                createEmail = '';
                createModalOpen = true;
                $nextTick(() => {
                    const passInput = document.getElementById('create_password_input');
                    if (passInput) {
                        passInput.value = generateNewPassword();
                    }
                });
            " style="padding:10px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(27,42,107,0.2); transition:all 0.2s;">
                <i data-lucide="user-plus" style="width:16px; height:16px;"></i>
                <span>+ Generate Akun Baru</span>
            </button>
        </div>

        {{-- KPI Cards --}}
        <div class="kpi-grid-container" style="margin-bottom:24px; display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:16px;">
            <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
                <div style="color:#64748B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Pengguna</div>
                <div style="font-size:24px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total'] }}</div>
            </div>
            <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
                <div style="color:#7C3AED; font-size:11.5px; font-weight:700; text-transform:uppercase;">Administrator</div>
                <div style="font-size:24px; font-weight:800; color:#7C3AED; margin-top:6px;">{{ $kpi['admin'] }}</div>
            </div>

            {{-- Dynamic Bidang Cards (Tanpa kata "Petugas") --}}
            @php
                $colorPalette = ['#DC2626', '#2563EB', '#D97706', '#0891B2', '#4F46E5', '#059669', '#7C3AED', '#DB2777'];
            @endphp
            @foreach($kpi['bidang'] as $bidangName => $count)
                @php
                    $color = match (strtolower(trim($bidangName))) {
                        'pemadam'            => '#DC2626',
                        'rescue'             => '#2563EB',
                        'command center'     => '#D97706',
                        'sekretariat'        => '#0891B2',
                        'sarana prasarana'   => '#4F46E5',
                        default              => $colorPalette[$loop->index % count($colorPalette)],
                    };
                @endphp
                <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
                    <div style="color:{{ $color }}; font-size:11.5px; font-weight:700; text-transform:uppercase;">{{ strtoupper($bidangName) }}</div>
                    <div style="font-size:24px; font-weight:800; color:{{ $color }}; margin-top:6px;">{{ $count }}</div>
                </div>
            @endforeach

            <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
                <div style="color:#059669; font-size:11.5px; font-weight:700; text-transform:uppercase;">Akun Aktif</div>
                <div style="font-size:24px; font-weight:800; color:#059669; margin-top:6px;">{{ $kpi['aktif'] }}</div>
            </div>
        </div>

        {{-- Search & Filter Bar --}}
        <div class="admin-filter-bar" style="background:#FFFFFF; padding:16px; border-radius:14px; border:1px solid #E2E8F0; margin-bottom:24px;">
            <form method="GET" action="{{ route('admin.pengaturan') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    
                    {{-- Filter Role --}}
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; font-weight:600; color:#64748B;">Role:</span>
                        <select name="role" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                            <option value="semua" @selected($roleFilter === 'semua')>Semua Role</option>
                            <option value="user" @selected($roleFilter === 'user')>Petugas / User</option>
                            <option value="admin" @selected($roleFilter === 'admin')>Administrator</option>
                        </select>
                    </div>

                    {{-- Filter Bidang --}}
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; font-weight:600; color:#64748B;">Bidang:</span>
                        <select name="bidang" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                            <option value="semua" @selected($bidangFilter === 'semua')>Semua Bidang</option>
                            @foreach($existingBidangList as $b)
                                <option value="{{ $b }}" @selected($bidangFilter === $b)>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Pos --}}
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; font-weight:600; color:#64748B;">Pos:</span>
                        <select name="pos" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                            <option value="semua" @selected($posFilter === 'semua')>Semua Pos</option>
                            @foreach($posList as $p)
                                <option value="{{ $p->nama }}" @selected($posFilter === $p->nama)>{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Regu --}}
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; font-weight:600; color:#64748B;">Regu:</span>
                        <select name="regu" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                            <option value="semua" @selected($reguFilter === 'semua')>Semua Regu</option>
                            @foreach($existingReguList as $r)
                                <option value="{{ $r }}" @selected($reguFilter === $r)>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; font-weight:600; color:#64748B;">Status:</span>
                        <select name="status" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                            <option value="semua" @selected($statusFilter === 'semua')>Semua Status</option>
                            <option value="aktif" @selected($statusFilter === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected($statusFilter === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>

                </div>

                {{-- Input Pencarian --}}
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="position:relative;">
                        <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari nama, NIP, jabatan..."
                               style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:220px; background:#F8FAFC;">
                        <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                    </div>
                    <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabel Data Pengguna --}}
        <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
            @if($userList->isEmpty())
                <div style="padding:56px 20px; text-align:center; background:#FFFFFF;">
                    <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px; border:1px solid #E2E8F0; margin-left:auto; margin-right:auto;">
                        <i data-lucide="users-round" style="width:30px; height:30px; color:#64748B;"></i>
                    </div>
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Data Akun Tidak Ditemukan</div>
                    <div style="font-size:13px; color:#64748B; margin-bottom:18px;">
                        Tidak ada akun pengguna yang sesuai dengan filter / pencarian Anda.
                    </div>
                    <a href="{{ route('admin.pengaturan') }}" style="display:inline-flex; align-items:center; gap:8px; padding:9px 20px; background:#1B2A6B; color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none;">
                        <i data-lucide="rotate-ccw" style="width:14px; height:14px;"></i>
                        <span>Reset Filter</span>
                    </a>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%; table-layout:fixed; border-collapse:collapse; font-size:13px; text-align:left;">
                        <thead>
                            <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">
                                <th style="padding:14px 12px; width:50px; text-align:center;">No</th>
                                <th style="padding:14px 16px; width:25%;">Nama &amp; NIP</th>
                                <th style="padding:14px 16px; width:20%;">Jabatan &amp; Bidang</th>
                                <th style="padding:14px 16px; width:18%;">Pos &amp; Regu</th>
                                <th style="padding:14px 16px; width:100px; text-align:center;">Role</th>
                                <th style="padding:14px 16px; width:90px; text-align:center;">Status</th>
                                <th style="padding:14px 16px; width:180px; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userList as $index => $item)
                                <tr style="border-bottom:1px solid #F1F5F9;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                    <td style="padding:12px; font-weight:600; color:#94A3B8; text-align:center;">
                                        {{ $userList->firstItem() + $index }}
                                    </td>
                                    
                                    {{-- Nama & NIP --}}
                                    <td style="padding:12px 16px;">
                                        <div style="font-weight:700; color:#0F172A; font-size:13.5px;">{{ $item->name }}</div>
                                        <div style="font-size:11.5px; color:#64748B; font-family:monospace; margin-top:2px;">
                                            NIP: {{ $item->nip ?: '—' }}
                                        </div>
                                    </td>

                                    {{-- Jabatan & Bidang --}}
                                    <td style="padding:12px 16px;">
                                        <div style="font-weight:700; color:#334155; font-size:12.5px;">{{ $item->jabatan ?: 'Petugas Operasional' }}</div>
                                        <div style="margin-top:3px;">
                                            @if(strtolower($item->bidang) === 'pemadam')
                                                <span style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:2px 8px; border-radius:12px; font-size:10.5px; font-weight:700;">Pemadam</span>
                                            @elseif(strtolower($item->bidang) === 'rescue')
                                                <span style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; padding:2px 8px; border-radius:12px; font-size:10.5px; font-weight:700;">Rescue</span>
                                            @else
                                                <span style="background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; padding:2px 8px; border-radius:12px; font-size:10.5px; font-weight:700;">{{ $item->bidang ?: 'Umum' }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Pos & Regu --}}
                                    <td style="padding:12px 16px;">
                                        <div style="font-weight:700; color:#1E293B; font-size:12.5px;">{{ $item->pos ?: '—' }}</div>
                                        <div style="font-size:11px; color:#64748B; font-weight:600; margin-top:2px;">
                                            {{ $item->regu ?: '—' }}
                                        </div>
                                    </td>

                                    {{-- Role --}}
                                    <td style="padding:12px 16px; text-align:center;">
                                        @if($item->role === 'admin')
                                            <span style="background:#F3E8FF; color:#6B21A8; border:1px solid #D8B4FE; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:800;">Admin</span>
                                        @else
                                            <span style="background:#E0F2FE; color:#0369A1; border:1px solid #BAE6FD; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">Petugas</span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td style="padding:12px 16px; text-align:center;">
                                        @if(($item->status ?? 'aktif') === 'aktif')
                                            <span style="background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">Aktif</span>
                                        @else
                                            <span style="background:#F3F4F6; color:#4B5563; border:1px solid #E5E7EB; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">Nonaktif</span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td style="padding:12px 16px; text-align:center; white-space:nowrap;">
                                        <div style="display:inline-flex; align-items:center; justify-content:flex-start; width:160px; gap:6px;">
                                            {{-- Edit Button --}}
                                            <button type="button" @click="
                                                activeUser = {{ json_encode($item) }};
                                                editUrl = '{{ route('admin.pengaturan.users.update', $item->id) }}';
                                                editModalOpen = true;
                                            " title="Edit Akun" style="padding:5px 9px; background:#F1F5F9; color:#334155; border:1px solid #CBD5E1; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                                Edit
                                            </button>

                                            {{-- Reset Pass Button --}}
                                            <button type="button" @click="
                                                activeUser = {{ json_encode($item) }};
                                                resetUrl = '{{ route('admin.pengaturan.users.reset-password', $item->id) }}';
                                                resetModalOpen = true;
                                            " title="Reset Password" style="padding:5px 9px; background:#FEF3C7; color:#92400E; border:1px solid #FDE68A; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                                Kunci
                                            </button>

                                            {{-- Hapus Button --}}
                                            @if($item->id !== auth()->id())
                                                <button type="button" @click="
                                                    activeUser = {{ json_encode($item) }};
                                                    deleteUrl = '{{ route('admin.pengaturan.users.destroy', $item->id) }}';
                                                    deleteModalOpen = true;
                                                " title="Hapus Akun" style="padding:5px 9px; background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                                    Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:16px 20px; border-top:1px solid #F1F5F9; background:#FAFCFE;">
                    {{ $userList->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- ================= TAB 2: AKSES TAMPILAN USER ================= --}}
    <div x-show="activeTab === 'view_user'" x-cloak>
        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 14px; padding: 24px; box-shadow:0 4px 12px rgba(15,23,42,0.03);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: #0F172A; margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="eye" style="width: 20px; height: 20px; color: #1B2A6B;"></i>
                        Lihat Halaman User (Mode Pratinjau Admin)
                    </h3>
                    <p style="font-size: 13px; color: #64748B; margin: 0; max-width:600px;">
                        Beralih ke tampilan pengguna untuk melihat simulasi alur pengajuan pemeliharaan dan pengecekan harian dari sudut pandang petugas operasional.
                    </p>
                </div>
                <form action="{{ route('admin.switch-to-user') }}" method="POST">
                    @csrf
                    <button type="submit" style="padding:10px 20px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                        <i data-lucide="external-link" style="width: 16px; height: 16px;"></i>
                        <span>Buka Halaman User</span>
                    </button>
                </form>
            </div>
        </div>
    </div>


    {{-- ===================== MODAL: GENERATE / TAMBAH AKUN BARU ===================== --}}
    <div x-show="createModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="createModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; sticky; top:0; background:#FFFFFF; z-index:10;">
                <div>
                    <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0;">Generate &amp; Tambah Akun Baru</h3>
                    <p style="font-size:12px; color:#64748B; margin:2px 0 0;">Isi kredensial NIP, Nama, Jabatan, Pos, dan Regu petugas.</p>
                </div>
                <button type="button" @click="createModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.pengaturan.users.store') }}" method="POST" autocomplete="off" style="padding:20px;">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:16px;">
                    
                    {{-- NIP & Nama --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">NIP <span style="color:#DC2626;">*</span></label>
                            <input type="text" name="nip" required x-model="createNip" autocomplete="off" placeholder="Contoh: 199501012020011001"
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; font-family:monospace;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Lengkap <span style="color:#DC2626;">*</span></label>
                            <input type="text" name="name" required x-model="createName" autocomplete="off" placeholder="Contoh: Ahmad Subagja"
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                        </div>
                    </div>

                    {{-- Password & Auto-Generate --}}
                    <div>
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                            <label style="font-size:12px; font-weight:700; color:#334155; margin:0;">Password <span style="color:#DC2626;">*</span></label>
                            <button type="button" @click="
                                const pInput = document.getElementById('create_password_input');
                                if (pInput) pInput.value = generateNewPassword();
                            " style="background:none; border:none; color:#1B2A6B; font-size:11.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px; text-decoration:underline;">
                                <i data-lucide="key-round" style="width:12px; height:12px;"></i>
                                <span>🎲 Generate Password Acak</span>
                            </button>
                        </div>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input :type="showPassCreate ? 'text' : 'password'" name="password" id="create_password_input" required autocomplete="new-password" placeholder="Masukkan atau generate password..."
                                   style="width:100%; padding:8px 36px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; font-family:monospace;">
                            <button type="button" @click="showPassCreate = !showPassCreate" tabindex="-1"
                                    style="position:absolute; right:8px; background:none; border:none; color:#64748B; cursor:pointer;">
                                <i :data-lucide="showPassCreate ? 'eye-off' : 'eye'" style="width:16px; height:16px;"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Email (Optional) & Role --}}
                    <div style="display:grid; grid-template-columns:1.2fr 0.8fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Email <span style="font-weight:400; color:#64748B;">(Opsional)</span></label>
                            <input type="email" name="email" x-model="createEmail" autocomplete="off" placeholder="Contoh: petugas@damkar.go.id (Kosongkan jika tidak ada)"
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Role Hak Akses <span style="color:#DC2626;">*</span></label>
                            <select name="role" required style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                <option value="user" selected>Petugas / User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>

                    {{-- Jabatan & Bidang --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        {{-- Jabatan (Input Manual + Dropdown Riwayat + Hapus) --}}
                        <div x-data="{ open: false, val: '' }" style="position:relative;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Jabatan</label>
                            <div style="position:relative; display:flex; align-items:center;">
                                <input type="text" name="jabatan" x-model="val" placeholder="Ketik atau pilih jabatan..."
                                       style="width:100%; padding:8px 34px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;"
                                       @focus="if(existingJabatanList.length > 0) open = true"
                                       @click.outside="open = false">
                                <button type="button" @click="open = !open" tabindex="-1"
                                        style="position:absolute; right:1px; top:1px; bottom:1px; width:32px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                    <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                                </button>
                            </div>
                            <div x-show="open && existingJabatanList.length > 0" x-cloak
                                 style="position:absolute; top:100%; left:0; right:0; max-height:170px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:1000; margin-top:2px;">
                                <template x-for="item in existingJabatanList" :key="item">
                                    <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer;"
                                         @click="val = item; open = false;"
                                         onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                         onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';">
                                        <span x-text="item"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Bidang (Input Manual + Dropdown Riwayat) --}}
                        <div x-data="{ open: false, val: '' }" style="position:relative;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Bidang</label>
                            <div style="position:relative; display:flex; align-items:center;">
                                <input type="text" name="bidang" x-model="val" placeholder="Ketik atau pilih bidang..."
                                       style="width:100%; padding:8px 34px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;"
                                       @focus="if(existingBidangList.length > 0) open = true"
                                       @click.outside="open = false">
                                <button type="button" @click="open = !open" tabindex="-1"
                                        style="position:absolute; right:1px; top:1px; bottom:1px; width:32px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                    <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                                </button>
                            </div>
                            <div x-show="open && existingBidangList.length > 0" x-cloak
                                 style="position:absolute; top:100%; left:0; right:0; max-height:170px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:1000; margin-top:2px;">
                                <template x-for="item in existingBidangList" :key="item">
                                    <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer;"
                                         @click="val = item; open = false;"
                                         onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                         onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';">
                                        <span x-text="item"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Pos & Regu --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Pos Penempatan</label>
                            <select name="pos" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                <option value="">— Pilih Pos —</option>
                                @foreach($posList as $p)
                                    <option value="{{ $p->nama }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Regu (Input Manual + Dropdown Riwayat) --}}
                        <div x-data="{ open: false, val: '' }" style="position:relative;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Regu</label>
                            <div style="position:relative; display:flex; align-items:center;">
                                <input type="text" name="regu" x-model="val" placeholder="Ketik atau pilih regu..."
                                       style="width:100%; padding:8px 34px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;"
                                       @focus="if(existingReguList.length > 0) open = true"
                                       @click.outside="open = false">
                                <button type="button" @click="open = !open" tabindex="-1"
                                        style="position:absolute; right:1px; top:1px; bottom:1px; width:32px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                    <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                                </button>
                            </div>
                            <div x-show="open && existingReguList.length > 0" x-cloak
                                 style="position:absolute; top:100%; left:0; right:0; max-height:170px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:1000; margin-top:2px;">
                                <template x-for="item in existingReguList" :key="item">
                                    <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer;"
                                         @click="val = item; open = false;"
                                         onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                         onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';">
                                        <span x-text="item"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- No. HP & Status --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. WhatsApp / HP</label>
                            <input type="text" name="no_hp" placeholder="Contoh: 081234567890"
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Status Akun <span style="color:#DC2626;">*</span></label>
                            <select name="status" required style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                <option value="aktif" selected>Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid #E2E8F0; padding-top:14px;">
                    <button type="button" @click="createModalOpen = false"
                            style="padding:8px 16px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:8px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">
                        Simpan &amp; Generate Akun
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- ===================== MODAL: EDIT AKUN PENGGUNA ===================== --}}
    <div x-show="editModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="editModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; sticky; top:0; background:#FFFFFF; z-index:10;">
                <div>
                    <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0;">Edit Data Akun Pengguna</h3>
                    <p style="font-size:12px; color:#64748B; margin:2px 0 0;" x-text="'Sunting akun ' + (activeUser.name || '')"></p>
                </div>
                <button type="button" @click="editModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            
            <form :action="editUrl" method="POST" style="padding:20px;">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:16px;">
                    
                    {{-- NIP & Nama --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">NIP <span style="color:#DC2626;">*</span></label>
                            <input type="text" name="nip" required x-model="activeUser.nip"
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; font-family:monospace;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Lengkap <span style="color:#DC2626;">*</span></label>
                            <input type="text" name="name" required x-model="activeUser.name"
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                        </div>
                    </div>

                    {{-- Email & Role --}}
                    <div style="display:grid; grid-template-columns:1.2fr 0.8fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Email</label>
                            <input type="email" name="email" x-model="activeUser.email"
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Role Hak Akses <span style="color:#DC2626;">*</span></label>
                            <select name="role" required x-model="activeUser.role" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                <option value="user">Petugas / User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>

                    {{-- Jabatan & Bidang --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        {{-- Edit: Jabatan (Input Manual + Dropdown Riwayat + Hapus) --}}
                        <div x-data="{ open: false }" style="position:relative;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Jabatan</label>
                            <div style="position:relative; display:flex; align-items:center;">
                                <input type="text" name="jabatan" x-model="activeUser.jabatan" placeholder="Ketik atau pilih jabatan..."
                                       style="width:100%; padding:8px 34px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;"
                                       @focus="if(existingJabatanList.length > 0) open = true"
                                       @click.outside="open = false">
                                <button type="button" @click="open = !open" tabindex="-1"
                                        style="position:absolute; right:1px; top:1px; bottom:1px; width:32px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                    <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                                </button>
                            </div>
                            <div x-show="open && existingJabatanList.length > 0" x-cloak
                                 style="position:absolute; top:100%; left:0; right:0; max-height:170px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:1000; margin-top:2px;">
                                <template x-for="item in existingJabatanList" :key="item">
                                    <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer;"
                                         @click="activeUser.jabatan = item; open = false;"
                                         onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                         onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';">
                                        <span x-text="item"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        {{-- Edit: Bidang (Input Manual + Dropdown Riwayat) --}}
                        <div x-data="{ open: false }" style="position:relative;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Bidang</label>
                            <div style="position:relative; display:flex; align-items:center;">
                                <input type="text" name="bidang" x-model="activeUser.bidang" placeholder="Ketik atau pilih bidang..."
                                       style="width:100%; padding:8px 34px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;"
                                       @focus="if(existingBidangList.length > 0) open = true"
                                       @click.outside="open = false">
                                <button type="button" @click="open = !open" tabindex="-1"
                                        style="position:absolute; right:1px; top:1px; bottom:1px; width:32px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                    <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                                </button>
                            </div>
                            <div x-show="open && existingBidangList.length > 0" x-cloak
                                 style="position:absolute; top:100%; left:0; right:0; max-height:170px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:1000; margin-top:2px;">
                                <template x-for="item in existingBidangList" :key="item">
                                    <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer;"
                                         @click="activeUser.bidang = item; open = false;"
                                         onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                         onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';">
                                        <span x-text="item"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Pos & Regu --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Pos Penempatan</label>
                            <select name="pos" x-model="activeUser.pos" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                <option value="">— Pilih Pos —</option>
                                @foreach($posList as $p)
                                    <option value="{{ $p->nama }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Edit: Regu (Input Manual + Dropdown Riwayat) --}}
                        <div x-data="{ open: false }" style="position:relative;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Regu</label>
                            <div style="position:relative; display:flex; align-items:center;">
                                <input type="text" name="regu" x-model="activeUser.regu" placeholder="Ketik atau pilih regu..."
                                       style="width:100%; padding:8px 34px 8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;"
                                       @focus="if(existingReguList.length > 0) open = true"
                                       @click.outside="open = false">
                                <button type="button" @click="open = !open" tabindex="-1"
                                        style="position:absolute; right:1px; top:1px; bottom:1px; width:32px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                    <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                                </button>
                            </div>
                            <div x-show="open && existingReguList.length > 0" x-cloak
                                 style="position:absolute; top:100%; left:0; right:0; max-height:170px; overflow-y:auto; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.15); z-index:1000; margin-top:2px;">
                                <template x-for="item in existingReguList" :key="item">
                                    <div style="padding:8px 12px; font-size:12.5px; border-bottom:1px solid #F1F5F9; cursor:pointer;"
                                         @click="activeUser.regu = item; open = false;"
                                         onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                         onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';">
                                        <span x-text="item"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- No. HP & Status --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. WhatsApp / HP</label>
                            <input type="text" name="no_hp" x-model="activeUser.no_hp"
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Status Akun <span style="color:#DC2626;">*</span></label>
                            <select name="status" required x-model="activeUser.status" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid #E2E8F0; padding-top:14px;">
                    <button type="button" @click="editModalOpen = false"
                            style="padding:8px 16px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:8px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- ===================== MODAL: RESET PASSWORD AKUN ===================== --}}
    <div x-show="resetModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="resetModalOpen = false">
        <div class="admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:440px; padding:24px; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                <div style="width:40px; height:40px; border-radius:50%; background:#FEF3C7; color:#D97706; display:flex; align-items:center; justify-content:center;">
                    <i data-lucide="key-round" style="width:20px; height:20px;"></i>
                </div>
                <div>
                    <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0;">Reset Password</h3>
                    <p style="font-size:12px; color:#64748B; margin:2px 0 0;" x-text="'Reset password untuk ' + (activeUser.name || '')"></p>
                </div>
            </div>

            <form :action="resetUrl" method="POST">
                @csrf
                <div style="margin-bottom:20px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                        <label style="font-size:12px; font-weight:700; color:#334155; margin:0;">Password Baru <span style="color:#DC2626;">*</span></label>
                        <button type="button" @click="
                            const rInput = document.getElementById('reset_password_input');
                            if (rInput) rInput.value = generateNewPassword();
                        " style="background:none; border:none; color:#1B2A6B; font-size:11.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px; text-decoration:underline;">
                            🎲 Acak Password
                        </button>
                    </div>
                    <input type="text" name="new_password" id="reset_password_input" required placeholder="Masukkan password baru..."
                           style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; font-family:monospace;">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" @click="resetModalOpen = false"
                            style="padding:8px 16px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:8px 18px; background:#D97706; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">
                        Reset Password Now
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- ===================== MODAL: KONFIRMASI HAPUS AKUN ===================== --}}
    <div x-show="deleteModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="deleteModalOpen = false">
        <div class="admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:420px; padding:24px; text-align:center; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="width:48px; height:48px; border-radius:50%; background:#FEE2E2; color:#DC2626; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                <i data-lucide="user-x" style="width:24px; height:24px;"></i>
            </div>
            <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0 0 6px;">Hapus Akun Pengguna?</h3>
            <p style="font-size:13px; color:#64748B; margin-bottom:20px;">
                Apakah Anda yakin ingin menghapus akun pengguna <strong style="color:#0F172A;" x-text="activeUser.name"></strong> (NIP: <span x-text="activeUser.nip"></span>)? Tindakan ini tidak dapat dibatalkan.
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
                    Ya, Hapus Akun
                </button>
            </form>
        </div>
    </div>

</div>

<script>
function pengaturanApp() {
    return {
        activeTab: 'users',
        createModalOpen: false,
        editModalOpen: false,
        resetModalOpen: false,
        deleteModalOpen: false,
        activeUser: {},
        editUrl: '',
        resetUrl: '',
        deleteUrl: '',
        createNip: '',
        createName: '',
        createEmail: '',
        generatedPass: 'Damkar' + Math.floor(1000 + Math.random() * 9000) + '!',
        showPassCreate: false,
        showPassEdit: false,
        existingJabatanList: @json($existingJabatanList ?? []),
        existingBidangList: @json($existingBidangList ?? []),
        existingReguList: @json($existingReguList ?? []),
        generateNewPassword() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$';
            let res = 'Dmk-';
            for (let i = 0; i < 6; i++) {
                res += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return res;
        }
    };
}
</script>
@endsection
