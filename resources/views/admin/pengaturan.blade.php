@extends('layouts.admin')

@section('title', 'Pengaturan & Manajemen Akun — Admin')

@section('content')
<div x-data="pengaturanApp()" x-effect="document.body.classList.toggle('admin-modal-open', createModalOpen || editModalOpen || resetModalOpen || deleteModalOpen || docCreateModalOpen || docEditModalOpen || docDeleteModalOpen)">

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

    {{-- Sleek Compact Tab Switcher Bar --}}
    <div class="w-full flex items-center mb-6">
        <div class="inline-flex items-center gap-1 p-1 bg-white border border-slate-300 rounded-lg shadow-2xs">
            <button type="button" @click="activeTab = 'users'"
                    :class="activeTab === 'users' ? 'bg-[#1B2A6B] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-md transition-colors duration-150 whitespace-nowrap cursor-pointer border-0">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Manajemen &amp; Generate Akun</span>
                <span :class="activeTab === 'users' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-extrabold ml-0.5">
                    {{ $userList->total() }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'dokumen'"
                    :class="activeTab === 'dokumen' ? 'bg-[#1B2A6B] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-md transition-colors duration-150 whitespace-nowrap cursor-pointer border-0">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Pengaturan PKS &amp; SPK</span>
                <span :class="activeTab === 'dokumen' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-extrabold ml-0.5">
                    {{ $pengaturanDokumenList->count() }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'view_user'"
                    :class="activeTab === 'view_user' ? 'bg-[#1B2A6B] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-md transition-colors duration-150 whitespace-nowrap cursor-pointer border-0">
                <i data-lucide="eye" class="w-4 h-4"></i>
                <span>Akses Tampilan User</span>
            </button>
        </div>
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
                createJabatan = '';
                createBidang = '';
                createPos = '';
                createRegu = '';
                createNoHp = '';
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
            <form method="GET" action="{{ route('admin.pengaturan') }}" class="filter-form-responsive" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
                <div class="filter-items-grid" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    
                    {{-- Filter Role --}}
                    <div class="filter-item-cell" style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; font-weight:600; color:#64748B; white-space:nowrap;">Role:</span>
                        <select name="role" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600; width:100%;">
                            <option value="semua" @selected($roleFilter === 'semua')>Semua Role</option>
                            <option value="user" @selected($roleFilter === 'user')>Petugas / User</option>
                            <option value="admin" @selected($roleFilter === 'admin')>Administrator</option>
                        </select>
                    </div>

                    {{-- Filter Bidang --}}
                    <div class="filter-item-cell" style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; font-weight:600; color:#64748B; white-space:nowrap;">Bidang:</span>
                        <select name="bidang" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600; width:100%;">
                            <option value="semua" @selected($bidangFilter === 'semua')>Semua Bidang</option>
                            @foreach($existingBidangList as $b)
                                <option value="{{ $b }}" @selected($bidangFilter === $b)>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Pos --}}
                    <div class="filter-item-cell" style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; font-weight:600; color:#64748B; white-space:nowrap;">Pos:</span>
                        <select name="pos" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600; width:100%;">
                            <option value="semua" @selected($posFilter === 'semua')>Semua Pos</option>
                            @foreach($posList as $p)
                                @php $pName = is_string($p) ? $p : ($p->nama ?? ($p['nama'] ?? '')); @endphp
                                <option value="{{ $pName }}" @selected($posFilter === $pName)>{{ $pName }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Regu --}}
                    <div class="filter-item-cell" style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; font-weight:600; color:#64748B; white-space:nowrap;">Regu:</span>
                        <select name="regu" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600; width:100%;">
                            <option value="semua" @selected($reguFilter === 'semua')>Semua Regu</option>
                            @foreach($existingReguList as $r)
                                <option value="{{ $r }}" @selected($reguFilter === $r)>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div class="filter-item-cell" style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:12px; font-weight:600; color:#64748B; white-space:nowrap;">Status:</span>
                        <select name="status" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600; width:100%;">
                            <option value="semua" @selected($statusFilter === 'semua')>Semua Status</option>
                            <option value="aktif" @selected($statusFilter === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected($statusFilter === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>

                </div>

                {{-- Input Pencarian --}}
                <div class="filter-search-wrap-box" style="display:flex; align-items:center; gap:8px;">
                    <div style="position:relative; flex:1;">
                        <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari nama, NIP, jabatan..."
                               style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:100%; min-width:180px; background:#F8FAFC; box-sizing:border-box;">
                        <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                    </div>
                    <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap;">
                        Cari
                    </button>
                    @if($roleFilter !== 'semua' || $bidangFilter !== 'semua' || $posFilter !== 'semua' || $reguFilter !== 'semua' || $statusFilter !== 'semua' || !empty($searchQuery))
                        <a href="{{ route('admin.pengaturan') }}" style="padding:7px 12px; background:#E2E8F0; color:#475569; border-radius:8px; font-size:12px; text-decoration:none; font-weight:600; white-space:nowrap;">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Data Pengguna --}}
        <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
            @if($userList->isEmpty())
                <div style="padding:56px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
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
                <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                    <table style="width:100%; min-width:850px; border-collapse:collapse; font-size:13px; text-align:left;">
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
                                                resetPassword = generateNewPassword();
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

    {{-- ================= TAB 3: PENGATURAN DOKUMEN (PKS & SPK) ================= --}}
    <div x-show="activeTab === 'dokumen'" x-cloak>
        
        {{-- Header & Button --}}
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px;">
            <div>
                <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="file-text" style="width:24px; height:24px; color:#1B2A6B;"></i>
                    <span>Pengaturan PKS, SPK &amp; Bengkel Rekanan</span>
                </h1>
                <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                    Nomor PKS, SPK, tanggal perjanjian, serta bengkel rekanan yang otomatis dicetak pada <b>Surat Pesanan</b> dan <b>Surat Permohonan Bengkel</b> sesuai tahun anggaran.
                </p>
            </div>
            <div>
                <button type="button" @click="docCreateModalOpen = true"
                        style="padding:10px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                    <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
                    <span>Tambah Pengaturan Tahun Baru</span>
                </button>
            </div>
        </div>

        {{-- Info Alert Banner --}}
        <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:12px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:flex-start; gap:12px;">
            <i data-lucide="info" style="width:20px; height:20px; color:#2563EB; flex-shrink:0; margin-top:2px;"></i>
            <div style="font-size:12.5px; color:#1E40AF; line-height:1.5;">
                <strong>Otomatis Berdasarkan Tahun Dokumen:</strong> Sistem akan otomatis menggunakan nomor PKS, SPK, dan data bengkel yang sesuai dengan tahun pengajuan dokumen dibuat. Anda dapat memperbarui data untuk tahun berjalan atau menambahkan data baru jika berganti tahun anggaran (misal 2027).
            </div>
        </div>

        {{-- Cards Grid Per Tahun --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)); gap:20px;">
            @forelse($pengaturanDokumenList as $doc)
                @php
                    $isTahunIni = $doc->tahun == (int) date('Y');
                @endphp
                <div style="background:#FFFFFF; border:{{ $isTahunIni ? '2px solid #3B82F6' : '1px solid #E2E8F0' }}; border-radius:16px; padding:20px; box-shadow:0 4px 14px rgba(15,23,42,0.04); position:relative; display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        {{-- Top Header Card --}}
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:42px; height:42px; border-radius:12px; background:{{ $isTahunIni ? '#EFF6FF' : '#F1F5F9' }}; color:{{ $isTahunIni ? '#1D4ED8' : '#475569' }}; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:800;">
                                    {{ $doc->tahun }}
                                </div>
                                <div>
                                    <h4 style="font-size:15px; font-weight:800; color:#0F172A; margin:0;">Tahun Anggaran {{ $doc->tahun }}</h4>
                                    <span style="font-size:11.5px; color:#64748B;">Diperbarui: {{ $doc->updated_at->format('d M Y') }}</span>
                                </div>
                            </div>
                            <div>
                                @if($isTahunIni)
                                    <span style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:700; background:#DCFCE7; color:#15803D; padding:4px 10px; border-radius:20px; border:1px solid #86EFAC;">
                                        <span style="width:6px; height:6px; border-radius:50%; background:#16A34A;"></span>
                                        Tahun Berjalan
                                    </span>
                                @else
                                    <span style="font-size:11px; font-weight:600; background:#F1F5F9; color:#64748B; padding:4px 10px; border-radius:20px;">
                                        Arsip
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Details Table / Info --}}
                        <div style="display:flex; flex-direction:column; gap:10px; font-size:12.5px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:14px; margin-bottom:16px;">
                            <div>
                                <span style="color:#64748B; font-size:11px; font-weight:700; text-transform:uppercase; display:block; margin-bottom:2px;">Nomor PKS:</span>
                                <span style="font-weight:700; color:#0F172A; font-family:monospace; font-size:12px; word-break:break-all;">{{ $doc->nomor_pks }}</span>
                            </div>
                            <div>
                                <span style="color:#64748B; font-size:11px; font-weight:700; text-transform:uppercase; display:block; margin-bottom:2px;">Nomor SPK:</span>
                                <span style="font-weight:700; color:#0F172A; font-family:monospace; font-size:12px; word-break:break-all;">{{ $doc->nomor_spk }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; gap:8px;">
                                <div>
                                    <span style="color:#64748B; font-size:11px; font-weight:700; text-transform:uppercase; display:block; margin-bottom:2px;">Tanggal PKS &amp; SPK:</span>
                                    <span style="font-weight:700; color:#0F172A;">{{ $doc->tanggal_pks_spk_label }}</span>
                                </div>
                            </div>
                            <div style="border-top:1px dashed #CBD5E1; padding-top:8px;">
                                <span style="color:#64748B; font-size:11px; font-weight:700; text-transform:uppercase; display:block; margin-bottom:2px;">Bengkel Rekanan:</span>
                                <span style="font-weight:700; color:#0F172A; display:block;">{{ $doc->nama_bengkel }}</span>
                                <span style="color:#64748B; font-size:11.5px; display:block; margin-top:2px;">{{ $doc->alamat_bengkel ?? '-' }}</span>
                            </div>
                            <div>
                                <span style="color:#64748B; font-size:11px; font-weight:700; text-transform:uppercase; display:block; margin-bottom:2px;">Pimpinan / TTD:</span>
                                <span style="font-weight:600; color:#334155;">{{ $doc->nama_pimpinan_bengkel ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Button --}}
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px; border-top:1px solid #F1F5F9; padding-top:14px;">
                        <button type="button" @click="openDocEdit({{ Js::from($doc) }}, '{{ route('admin.pengaturan.dokumen.update', $doc->id) }}')"
                                style="padding:7px 14px; background:#F8FAFC; border:1px solid #CBD5E1; color:#0F172A; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.15s;">
                            <i data-lucide="edit-2" style="width:13px; height:13px; color:#2563EB;"></i>
                            <span>Edit Data</span>
                        </button>
                        <button type="button" @click="openDocDelete({{ Js::from($doc) }}, '{{ route('admin.pengaturan.dokumen.destroy', $doc->id) }}')"
                                style="padding:7px 12px; background:#FEF2F2; border:1px solid #FECACA; color:#DC2626; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.15s;">
                            <i data-lucide="trash-2" style="width:13px; height:13px;"></i>
                            <span>Hapus</span>
                        </button>
                    </div>
                </div>
            @empty
                <div style="grid-column:1/-1; background:#FFFFFF; border:1px dashed #CBD5E1; border-radius:16px; padding:48px 24px; text-align:center;">
                    <i data-lucide="file-x" style="width:48px; height:48px; color:#94A3B8; margin:0 auto 12px;"></i>
                    <h3 style="font-size:16px; font-weight:700; color:#0F172A; margin:0 0 6px;">Belum Ada Pengaturan Dokumen</h3>
                    <p style="font-size:13px; color:#64748B; margin:0 0 16px;">Tambahkan pengaturan PKS dan SPK untuk tahun anggaran saat ini.</p>
                    <button type="button" @click="docCreateModalOpen = true"
                            style="padding:9px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px;">
                        <i data-lucide="plus" style="width:16px; height:16px;"></i>
                        <span>Tambah Data Pertama</span>
                    </button>
                </div>
            @endforelse
        </div>

    </div>

    {{-- ===================== MODAL: TAMBAH PENGATURAN DOKUMEN BARU ===================== --}}
    <div x-show="docCreateModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="docCreateModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
                <div>
                    <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0;">Tambah Pengaturan PKS &amp; SPK</h3>
                    <p style="font-size:12px; color:#64748B; margin:2px 0 0;">Pengaturan dokumen resmi untuk tahun anggaran baru.</p>
                </div>
                <button type="button" @click="docCreateModalOpen = false" style="background:none; border:none; cursor:pointer; color:#64748B;">
                    <i data-lucide="x" style="width:20px; height:20px;"></i>
                </button>
            </div>
            <form action="{{ route('admin.pengaturan.dokumen.store') }}" method="POST" style="padding:20px;">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Tahun Anggaran <span style="color:#EF4444;">*</span></label>
                        <input type="number" name="tahun" value="{{ (int)date('Y') + 1 }}" min="2020" max="2099" required
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; font-weight:700;">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nomor PKS <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="nomor_pks" placeholder="Contoh: 000.4.7.2/001/PKS-Pem/Bid.SPI/{{ (int)date('Y') + 1 }}" required
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nomor SPK <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="nomor_spk" placeholder="Contoh: SPK-004/I/{{ (int)date('Y') + 1 }}/PRA" required
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Tanggal PKS &amp; SPK <span style="color:#EF4444;">*</span></label>
                        <input type="date" name="tanggal_pks_spk" value="{{ ((int)date('Y') + 1) . '-01-09' }}" required
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;">
                    </div>

                    <div style="border-top:1px dashed #E2E8F0; padding-top:12px; margin-top:4px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Bengkel Rekanan <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="nama_bengkel" value="CV. Pratama Motor" required
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Alamat Bengkel</label>
                        <textarea name="alamat_bengkel" rows="2" placeholder="Alamat lengkap bengkel..."
                                  style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;">Jl. Soekarno Hatta No. 463, Kota Bandung</textarea>
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Pimpinan / Tanda Tangan Bengkel</label>
                        <input type="text" name="nama_pimpinan_bengkel" value="CV. Pratama" placeholder="Contoh: CV. Pratama"
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:20px;">
                    <button type="button" @click="docCreateModalOpen = false"
                            style="padding:9px 16px; background:#F1F5F9; border:none; border-radius:8px; font-size:13px; font-weight:600; color:#475569; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:9px 18px; background:#1B2A6B; border:none; border-radius:8px; font-size:13px; font-weight:700; color:#FFFFFF; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                        <i data-lucide="check" style="width:16px; height:16px;"></i>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL: EDIT PENGATURAN DOKUMEN ===================== --}}
    <div x-show="docEditModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="docEditModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
                <div>
                    <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0;">Edit Pengaturan PKS &amp; SPK</h3>
                    <p style="font-size:12px; color:#64748B; margin:2px 0 0;" x-text="'Tahun Anggaran: ' + (activeDoc.tahun || '')"></p>
                </div>
                <button type="button" @click="docEditModalOpen = false" style="background:none; border:none; cursor:pointer; color:#64748B;">
                    <i data-lucide="x" style="width:20px; height:20px;"></i>
                </button>
            </div>
            <form :action="docEditUrl" method="POST" style="padding:20px;">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Tahun Anggaran <span style="color:#EF4444;">*</span></label>
                        <input type="number" name="tahun" x-model="activeDoc.tahun" min="2020" max="2099" required
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; font-weight:700;">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nomor PKS <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="nomor_pks" x-model="activeDoc.nomor_pks" required
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nomor SPK <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="nomor_spk" x-model="activeDoc.nomor_spk" required
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Tanggal PKS &amp; SPK <span style="color:#EF4444;">*</span></label>
                        <input type="date" name="tanggal_pks_spk" x-model="activeDoc.tanggal_pks_spk_input" required
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;">
                    </div>

                    <div style="border-top:1px dashed #E2E8F0; padding-top:12px; margin-top:4px;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Bengkel Rekanan <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="nama_bengkel" x-model="activeDoc.nama_bengkel" required
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Alamat Bengkel</label>
                        <textarea name="alamat_bengkel" x-model="activeDoc.alamat_bengkel" rows="2"
                                  style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;"></textarea>
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Pimpinan / Tanda Tangan Bengkel</label>
                        <input type="text" name="nama_pimpinan_bengkel" x-model="activeDoc.nama_pimpinan_bengkel"
                               style="width:100%; box-sizing:border-box; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px;">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:20px;">
                    <button type="button" @click="docEditModalOpen = false"
                            style="padding:9px 16px; background:#F1F5F9; border:none; border-radius:8px; font-size:13px; font-weight:600; color:#475569; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:9px 18px; background:#2563EB; border:none; border-radius:8px; font-size:13px; font-weight:700; color:#FFFFFF; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                        <i data-lucide="save" style="width:16px; height:16px;"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL: KONFIRMASI HAPUS PENGATURAN DOKUMEN ===================== --}}
    <div x-show="docDeleteModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="docDeleteModalOpen = false">
        <div class="admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:440px; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:24px; text-align:center;">
                <div style="width:52px; height:52px; border-radius:50%; background:#FEE2E2; color:#DC2626; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <i data-lucide="alert-triangle" style="width:26px; height:26px;"></i>
                </div>
                <h3 style="font-size:17px; font-weight:800; color:#0F172A; margin:0 0 8px;">Hapus Pengaturan Dokumen?</h3>
                <p style="font-size:13px; color:#64748B; margin:0 0 20px; line-height:1.5;">
                    Apakah Anda yakin ingin menghapus data PKS dan SPK untuk <strong style="color:#0F172A;" x-text="'Tahun Anggaran ' + (activeDoc.tahun || '')"></strong>?
                </p>
                <form :action="docDeleteUrl" method="POST" style="display:flex; justify-content:center; gap:10px;">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="docDeleteModalOpen = false"
                            style="padding:9px 18px; background:#F1F5F9; border:none; border-radius:8px; font-size:13px; font-weight:600; color:#475569; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:9px 18px; background:#DC2626; border:none; border-radius:8px; font-size:13px; font-weight:700; color:#FFFFFF; cursor:pointer;">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>


    {{-- ===================== MODAL: GENERATE / TAMBAH AKUN BARU ===================== --}}
    <div x-show="createModalOpen" x-cloak class="admin-modal-overlay admin-account-modal"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="createModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto; overscroll-behavior:contain; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
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
                    
                    {{-- Pilihan Otomatis dari Master Data Pegawai (Autocomplete) --}}
                    <div style="position:relative; background:#EFF6FF; border:1px solid #BFDBFE; border-radius:12px; padding:12px;"
                         @click.outside="pegawaiOpen = false">
                        
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                            <label style="font-size:12px; font-weight:800; color:#1E3A8A; margin:0; display:inline-flex; align-items:center; gap:6px;">
                                <i data-lucide="user-check" style="width:15px; height:15px; color:#2563EB;"></i>
                                <span>Ambil Data dari Master Pegawai <span style="font-weight:500; color:#475569;">(Auto-fill Cepat)</span></span>
                            </label>
                            <span style="font-size:11px; font-weight:600; color:#2563EB;">Ketik nama / NIP</span>
                        </div>

                        <div style="position:relative;">
                            <input type="text"
                                   x-model="pegawaiQuery"
                                   @focus="pegawaiOpen = true"
                                   @input="pegawaiOpen = true"
                                   placeholder="Ketik nama atau NIP pegawai untuk mengisi otomatis..."
                                   autocomplete="off"
                                   style="width:100%; padding:8px 32px 8px 34px; font-size:12.5px; border-radius:8px; border:1px solid #93C5FD; background:#FFFFFF; outline:none; box-sizing:border-box;">
                            <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#3B82F6;"></i>
                            <button type="button" @click.stop="pegawaiOpen = !pegawaiOpen" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:#3B82F6; cursor:pointer;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>

                        {{-- Autocomplete Dropdown List --}}
                        <div x-show="pegawaiOpen && filterPegawaiList(pegawaiQuery).length > 0" x-cloak
                             style="position:absolute; left:12px; right:12px; top:100%; margin-top:4px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; box-shadow:0 10px 25px rgba(15,23,42,0.18); max-height:210px; overflow-y:auto; z-index:99999;">
                            <template x-for="p in filterPegawaiList(pegawaiQuery)" :key="p.id">
                                <div @click="selectPegawai(p)"
                                     style="padding:9px 12px; border-bottom:1px solid #F1F5F9; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                     onmouseover="this.style.background='#EFF6FF'"
                                     onmouseout="this.style.background='transparent'">
                                    <div>
                                        <strong style="display:block; color:#0F172A; font-size:12.5px;" x-text="p.name"></strong>
                                        <span style="font-size:11px; color:#64748B;" x-text="'NIP: ' + (p.nip || '—') + ' • ' + (p.jabatan || 'Petugas')"></span>
                                    </div>
                                    <span style="font-size:10.5px; font-weight:700; color:#1E40AF; background:#DBEAFE; padding:2px 8px; border-radius:10px;" x-text="p.pos || 'Disdamkar'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    {{-- NIP & Nama --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">NIP <span style="color:#DC2626;">*</span></label>
                            <input type="text" name="nip" required x-model="createNip" autocomplete="off" placeholder="Contoh: 199501012020011001"
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; font-family:monospace; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Lengkap <span style="color:#DC2626;">*</span></label>
                            <input type="text" name="name" required x-model="createName" autocomplete="off" placeholder="Contoh: Ahmad Subagja"
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; box-sizing:border-box;">
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
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Role Hak Akses <span style="color:#DC2626;">*</span></label>
                            <select name="role" required style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                <option value="user" selected>Petugas / User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>

                    {{-- Jabatan & Bidang (Dropdown murni dari Data Pegawai) --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        {{-- Jabatan --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Jabatan</label>
                            <select name="jabatan" x-model="createJabatan"
                                    style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF; box-sizing:border-box;">
                                <option value="">— Pilih Jabatan —</option>
                                @foreach($existingJabatanList as $j)
                                    <option value="{{ $j }}">{{ $j }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Bidang --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Bidang</label>
                            <select name="bidang" x-model="createBidang"
                                    style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF; box-sizing:border-box;">
                                <option value="">— Pilih Bidang —</option>
                                @foreach($existingBidangList as $b)
                                    <option value="{{ $b }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Pos & Regu (Regu Sesuai Pos) --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Pos Penempatan</label>
                            <select name="pos" x-model="createPos" @change="onPosChangeCreate()" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                <option value="">— Pilih Pos —</option>
                                @foreach($posList as $p)
                                    @php $pName = is_string($p) ? $p : ($p->nama ?? ($p['nama'] ?? '')); @endphp
                                    <option value="{{ $pName }}">{{ $pName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                                <label style="font-size:12px; font-weight:700; color:#334155; margin:0;">Regu</label>
                                <span style="font-size:11px; color:#2563EB; font-weight:600;" x-show="createPos" x-text="'(Pos: ' + createPos + ')'"></span>
                            </div>
                            <select name="regu" x-model="createRegu" @change="onReguChangeCreate()" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                <option value="">— Pilih Regu Sesuai Pos —</option>
                                <template x-for="r in getRegusForPos(createPos)" :key="r.id">
                                    <option :value="r.nama" x-text="r.nama + ' — ' + r.bidang + (r.danru ? ' (Danru: ' + r.danru + ')' : '')"></option>
                                </template>
                                <template x-if="getRegusForPos(createPos).length === 0">
                                    <optgroup label="Pilihan Standar">
                                        <option value="Regu 1">Regu 1</option>
                                        <option value="Regu 2">Regu 2</option>
                                        <option value="Regu 3">Regu 3</option>
                                        <option value="Regu 4">Regu 4</option>
                                    </optgroup>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- No. HP & Status --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. WhatsApp / HP</label>
                            <input type="text" name="no_hp" x-model="createNoHp" placeholder="Contoh: 081234567890"
                                   style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; box-sizing:border-box;">
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

                    {{-- Jabatan & Bidang (Dropdown murni dari Data Pegawai) --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        {{-- Edit: Jabatan --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Jabatan</label>
                            <select name="jabatan" x-model="activeUser.jabatan"
                                    style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF; box-sizing:border-box;">
                                <option value="">— Pilih Jabatan —</option>
                                @foreach($existingJabatanList as $j)
                                    <option value="{{ $j }}">{{ $j }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Edit: Bidang --}}
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Bidang</label>
                            <select name="bidang" x-model="activeUser.bidang"
                                    style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF; box-sizing:border-box;">
                                <option value="">— Pilih Bidang —</option>
                                @foreach($existingBidangList as $b)
                                    <option value="{{ $b }}">{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Pos & Regu (Regu Sesuai Pos) --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Pos Penempatan</label>
                            <select name="pos" x-model="activeUser.pos" @change="onPosChangeEdit()" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                <option value="">— Pilih Pos —</option>
                                @foreach($posList as $p)
                                    @php $pName = is_string($p) ? $p : ($p->nama ?? ($p['nama'] ?? '')); @endphp
                                    <option value="{{ $pName }}">{{ $pName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                                <label style="font-size:12px; font-weight:700; color:#334155; margin:0;">Regu</label>
                                <span style="font-size:11px; color:#2563EB; font-weight:600;" x-show="activeUser.pos" x-text="'(Pos: ' + activeUser.pos + ')'"></span>
                            </div>
                            <select name="regu" x-model="activeUser.regu" @change="onReguChangeEdit()" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                                <option value="">— Pilih Regu Sesuai Pos —</option>
                                <template x-for="r in getRegusForPos(activeUser.pos)" :key="r.id">
                                    <option :value="r.nama" x-text="r.nama + ' — ' + r.bidang + (r.danru ? ' (Danru: ' + r.danru + ')' : '')"></option>
                                </template>
                                <template x-if="getRegusForPos(activeUser.pos).length === 0">
                                    <optgroup label="Pilihan Standar">
                                        <option value="Regu 1">Regu 1</option>
                                        <option value="Regu 2">Regu 2</option>
                                        <option value="Regu 3">Regu 3</option>
                                        <option value="Regu 4">Regu 4</option>
                                    </optgroup>
                                </template>
                            </select>
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
                    <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0;">Password Akun</h3>
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
                            if (rInput) { resetPassword = generateNewPassword(); rInput.value = resetPassword; }
                        " style="background:none; border:none; color:#1B2A6B; font-size:11.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px; text-decoration:underline;">
                            🎲 Acak Password
                        </button>
                    </div>
                          <input type="text" name="new_password" id="reset_password_input" x-model="resetPassword" required placeholder="Password baru otomatis dibuat..."
                           style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; font-family:monospace;">
                          <p style="font-size:11px; color:#64748B; margin:6px 0 0;">Password lama tidak dapat dilihat karena disimpan sebagai hash. Gunakan password baru ini untuk diberikan kepada user.</p>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" @click="resetModalOpen = false"
                            style="padding:8px 16px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:8px 18px; background:#D97706; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">
                        Simpan Password Baru
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
        docCreateModalOpen: false,
        docEditModalOpen: false,
        docDeleteModalOpen: false,
        activeDoc: {},
        docEditUrl: '',
        docDeleteUrl: '',
        openDocEdit(doc, updateUrl) {
            this.activeDoc = Object.assign({}, doc);
            this.activeDoc.tanggal_pks_spk_input = doc.tanggal_pks_spk_ymd || (doc.tanggal_pks_spk ? String(doc.tanggal_pks_spk).substring(0, 10) : '');
            this.docEditUrl = updateUrl;
            this.docEditModalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },
        openDocDelete(doc, deleteUrl) {
            this.activeDoc = Object.assign({}, doc);
            this.docDeleteUrl = deleteUrl;
            this.docDeleteModalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },
        init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tab') === 'dokumen') {
                this.activeTab = 'dokumen';
            }
            if (urlParams.has('generate') || urlParams.has('nip')) {
                this.activeTab = 'users';
                this.createNip = urlParams.get('nip') || '';
                this.createName = urlParams.get('name') || '';
                this.createJabatan = urlParams.get('jabatan') || '';
                this.createBidang = urlParams.get('bidang') || '';
                this.createPos = urlParams.get('pos') || '';
                this.createRegu = urlParams.get('regu') || '';
                this.createNoHp = urlParams.get('no_hp') || '';
                const cleanNip = this.createNip.replace(/[^0-9]/g, '');
                if (cleanNip) {
                    this.createEmail = cleanNip + '@disdamkar.go.id';
                }
                this.createModalOpen = true;
                this.$nextTick(() => {
                    const passInput = document.getElementById('create_password_input');
                    if (passInput) {
                        passInput.value = this.generateNewPassword();
                    }
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },
        activeUser: {},
        editUrl: '',
        resetUrl: '',
        resetPassword: '',
        deleteUrl: '',
        createNip: '',
        createName: '',
        createEmail: '',
        createJabatan: '',
        createBidang: '',
        createPos: '',
        createRegu: '',
        createNoHp: '',
        pegawaiList: @json($pegawaiList ?? []),
        pegawaiOpen: false,
        pegawaiQuery: '',
        filterPegawaiList(query) {
            if (!query || query.trim() === '') return [];
            let q = query.toLowerCase();
            return this.pegawaiList.filter(p => 
                (p.name && p.name.toLowerCase().includes(q)) || 
                (p.nip && p.nip.toLowerCase().includes(q)) ||
                (p.jabatan && p.jabatan.toLowerCase().includes(q))
            ).slice(0, 15);
        },
        selectPegawai(p) {
            this.createNip = p.nip || '';
            this.createName = p.name || '';
            this.createJabatan = p.jabatan || '';
            this.createBidang = p.bidang || '';
            this.createPos = p.pos || '';
            this.createRegu = p.regu || '';
            this.createNoHp = p.no_hp || '';
            this.createEmail = p.email || '';
            this.pegawaiQuery = '';
            this.pegawaiOpen = false;
        },
        allReguList: @json($allReguList ?? []),
        getRegusForPos(posName) {
            if (!posName || posName.trim() === '') {
                return this.allReguList;
            }
            let clean = posName.toLowerCase().replace(/[^a-z0-9]/g, '');
            let matched = this.allReguList.filter(r => {
                let rClean = (r.pos || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                return rClean.includes(clean) || clean.includes(rClean);
            });
            return matched;
        },
        onPosChangeCreate() {
            if (!this.createPos) return;
            let regus = this.getRegusForPos(this.createPos);
            if (regus.length > 0) {
                if (!this.createRegu || !regus.some(r => r.nama.toLowerCase() === this.createRegu.toLowerCase())) {
                    this.createRegu = regus[0].nama;
                    if (!this.createBidang || this.createBidang === 'Pemadam' || this.createBidang === 'Rescue') {
                        this.createBidang = regus[0].bidang;
                    }
                }
            }
        },
        onPosChangeEdit() {
            if (!this.activeUser.pos) return;
            let regus = this.getRegusForPos(this.activeUser.pos);
            if (regus.length > 0) {
                if (!this.activeUser.regu || !regus.some(r => r.nama.toLowerCase() === this.activeUser.regu.toLowerCase())) {
                    this.activeUser.regu = regus[0].nama;
                    if (!this.activeUser.bidang || this.activeUser.bidang === 'Pemadam' || this.activeUser.bidang === 'Rescue') {
                        this.activeUser.bidang = regus[0].bidang;
                    }
                }
            }
        },
        onReguChangeCreate() {
            let regus = this.getRegusForPos(this.createPos);
            let found = regus.find(r => r.nama === this.createRegu);
            if (found && found.bidang && (!this.createBidang || this.createBidang === 'Pemadam' || this.createBidang === 'Rescue')) {
                this.createBidang = found.bidang;
            }
        },
        onReguChangeEdit() {
            let regus = this.getRegusForPos(this.activeUser.pos);
            let found = regus.find(r => r.nama === this.activeUser.regu);
            if (found && found.bidang && (!this.activeUser.bidang || this.activeUser.bidang === 'Pemadam' || this.activeUser.bidang === 'Rescue')) {
                this.activeUser.bidang = found.bidang;
            }
        },
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

@push('styles')
<style>
@media (max-width: 768px) {
    .filter-form-responsive {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
    }
    .filter-items-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)) !important;
        gap: 10px !important;
        width: 100% !important;
    }
    .filter-item-cell {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 4px !important;
        width: 100% !important;
    }
    .filter-item-cell select {
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .filter-search-wrap-box {
        width: 100% !important;
    }
}
</style>
@endpush
@endsection
