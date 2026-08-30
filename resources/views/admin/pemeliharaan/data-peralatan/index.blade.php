@extends('layouts.admin')

@section('title', 'Data Peralatan — Admin')

@section('content')
<div x-data="dataPeralatanApp()">

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
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Data Peralatan &amp; Perlengkapan</h1>
            <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                Kelola daftar master peralatan operasional unit pemadam, rescue, pencegahan, dan command center.
            </p>
        </div>
        <button type="button" @click="createModalOpen = true"
                style="padding:10px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(27,42,107,0.2); transition:all 0.2s;">
            <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
            <span>Tambah Peralatan Baru</span>
        </button>
    </div>

    {{-- Ringkasan KPI Cards (5 Cards Grid) --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin-bottom:24px;">
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#64748B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Peralatan</div>
            <div style="font-size:24px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#DC2626; font-size:11.5px; font-weight:700; text-transform:uppercase;">Pemadam</div>
            <div style="font-size:24px; font-weight:800; color:#DC2626; margin-top:6px;">{{ $kpi['pemadam'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#2563EB; font-size:11.5px; font-weight:700; text-transform:uppercase;">Rescue</div>
            <div style="font-size:24px; font-weight:800; color:#2563EB; margin-top:6px;">{{ $kpi['rescue'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#059669; font-size:11.5px; font-weight:700; text-transform:uppercase;">Pencegahan</div>
            <div style="font-size:24px; font-weight:800; color:#059669; margin-top:6px;">{{ $kpi['pencegahan'] ?? 0 }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#7C3AED; font-size:11.5px; font-weight:700; text-transform:uppercase;">Command Center</div>
            <div style="font-size:24px; font-weight:800; color:#7C3AED; margin-top:6px;">{{ $kpi['command_center'] }}</div>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="admin-filter-bar">
        <form method="GET" action="{{ route('admin.pemeliharaan.data-peralatan') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                {{-- Filter Kategori --}}
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:12px; font-weight:600; color:#64748B;">Kategori:</span>
                    <select name="kategori" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                        <option value="semua" @selected($kategoriFilter === 'semua')>Semua Kategori</option>
                        <option value="pemadam" @selected($kategoriFilter === 'pemadam')>Pemadam</option>
                        <option value="rescue" @selected($kategoriFilter === 'rescue')>Rescue</option>
                        <option value="pencegahan" @selected($kategoriFilter === 'pencegahan')>Pencegahan</option>
                        <option value="command_center" @selected($kategoriFilter === 'command_center')>Command Center</option>
                    </select>
                </div>
            </div>

            {{-- Input Pencarian --}}
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="position:relative;">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari nama peralatan..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:220px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
                @if(!empty($searchQuery) || $kategoriFilter !== 'semua')
                    <a href="{{ route('admin.pemeliharaan.data-peralatan') }}" style="padding:7px 12px; background:#F1F5F9; color:#64748B; border:1px solid #CBD5E1; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Data Peralatan --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
        @if($peralatanList->isEmpty())
            <div style="padding:56px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
                    <i data-lucide="search-x" style="width:30px; height:30px; color:#64748B;"></i>
                </div>
                @if(!empty($searchQuery) || $kategoriFilter !== 'semua')
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Data Tidak Ditemukan</div>
                    <div style="font-size:13px; color:#64748B; margin-bottom:18px; max-width:440px; margin-left:auto; margin-right:auto;">
                        Tidak ada peralatan yang sesuai dengan kriteria pencarian / filter Anda.
                    </div>
                    <a href="{{ route('admin.pemeliharaan.data-peralatan') }}" style="display:inline-flex; align-items:center; gap:8px; padding:9px 20px; background:#1B2A6B; color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                        <i data-lucide="rotate-ccw" style="width:14px; height:14px;"></i>
                        <span>Reset Filter &amp; Pencarian</span>
                    </a>
                @else
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Peralatan</div>
                    <div style="font-size:12.5px; color:#94A3B8;">Belum ada data peralatan yang tersimpan.</div>
                @endif
            </div>
        @else
            <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                <table style="width:100%; min-width:680px; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">
                            <th style="padding:14px 12px; width:50px; text-align:center;">No</th>
                            <th style="padding:14px 16px; width:260px;">Nama Peralatan</th>
                            <th style="padding:14px 16px; width:160px;">Kategori</th>
                            <th style="padding:14px 16px;">Catatan</th>
                            <th style="padding:14px 16px; width:130px; text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($peralatanList as $index => $item)
                            <tr style="border-bottom:1px solid #F1F5F9;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:12px; font-weight:600; color:#94A3B8; text-align:center;">{{ $peralatanList->firstItem() + $index }}</td>
                                <td style="padding:12px 16px;">
                                    <div style="font-weight:700; color:#0F172A; word-break:break-word;">{{ $item->nama }}</div>
                                </td>
                                <td style="padding:12px 16px; font-weight:600; color:#334155;">
                                    {{ $item->kategori ?: '—' }}
                                </td>
                                <td style="padding:12px 16px; color:#64748B; font-size:12px; line-height:1.4; word-break:break-word;">
                                    {{ $item->catatan ?? '—' }}
                                </td>
                                <td style="padding:12px 16px; text-align:center; white-space:nowrap;">
                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                        <button type="button" @click="
                                            activeAlat = {{ json_encode($item) }};
                                            editUrl = '{{ route('admin.pemeliharaan.data-peralatan.update', $item->id) }}';
                                            editModalOpen = true;
                                        " style="padding:5px 11px; background:#F1F5F9; color:#334155; border:1px solid #CBD5E1; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                            Edit
                                        </button>
                                        <button type="button" @click="
                                            activeAlat = {{ json_encode($item) }};
                                            deleteUrl = '{{ route('admin.pemeliharaan.data-peralatan.destroy', $item->id) }}';
                                            deleteModalOpen = true;
                                        " style="padding:5px 11px; background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 20px; border-top:1px solid #F1F5F9; background:#FAFCFE;">
                {{ $peralatanList->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL: TAMBAH PERALATAN ===================== --}}
    <div x-show="createModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="createModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:480px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Tambah Data Peralatan Baru</h3>
                <button type="button" @click="createModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            <form action="{{ route('admin.pemeliharaan.data-peralatan.store') }}" method="POST" style="padding:20px;">
                @csrf
                @if(isset($errors) && $errors->any())
                    <div style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:12px 16px; border-radius:12px; margin-bottom:18px; font-size:12.5px;">
                        <div style="display:flex; align-items:center; gap:6px; font-weight:800; color:#DC2626; margin-bottom:4px;">
                            <i data-lucide="alert-triangle" style="width:16px; height:16px;"></i>
                            <span>Gagal Menyimpan Data Peralatan:</span>
                        </div>
                        <ul style="margin:0; padding-left:20px; font-size:12px; font-weight:600;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Peralatan <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nama" required value="{{ old('nama') }}" placeholder="Contoh: Apar / Handy Talky"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:{{ isset($errors) && $errors->has('nama') ? '1.5px solid #DC2626' : '1px solid #CBD5E1' }}; outline:none;">
                        @error('nama')
                            <span style="font-size:11px; color:#DC2626; font-weight:600; margin-top:3px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    {{-- Kategori Peralatan (Input Manual + Dropdown Riwayat + Hapus) --}}
                    <div x-data="{ open: false, val: '{{ old('kategori', 'Pemadam') }}' }" style="position:relative;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kategori Peralatan <span style="color:#DC2626;">*</span></label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" name="kategori" required x-model="val" placeholder="Ketik atau pilih kategori..."
                                   style="width:100%; padding:8px 34px 8px 12px; font-size:13px; border-radius:8px; border:{{ isset($errors) && $errors->has('kategori') ? '1.5px solid #DC2626' : '1px solid #CBD5E1' }}; outline:none;"
                                   @focus="if(existingKategoriList.length > 0) open = true"
                                   @click.outside="open = false">
                            <button type="button" @click="open = !open" tabindex="-1"
                                    style="position:absolute; right:1px; top:1px; bottom:1px; width:32px; background:#F8FAFC; border:none; border-left:1px solid #CBD5E1; border-top-right-radius:7px; border-bottom-right-radius:7px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                                <i data-lucide="chevron-down" style="width:14px; height:14px;"></i>
                            </button>
                        </div>
                        @error('kategori')
                            <span style="font-size:11px; color:#DC2626; font-weight:600; margin-top:3px; display:block;">{{ $message }}</span>
                        @enderror
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
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Catatan Khusus</label>
                        <textarea name="catatan" rows="3" placeholder="Tuliskan catatan tambahan peralatan..."
                                  style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:{{ isset($errors) && $errors->has('catatan') ? '1.5px solid #DC2626' : '1px solid #CBD5E1' }}; outline:none; resize:none;">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <span style="font-size:11px; color:#DC2626; font-weight:600; margin-top:3px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid #E2E8F0; padding-top:14px;">
                    <button type="button" @click="createModalOpen = false"
                            style="padding:8px 16px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:8px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">
                        Simpan Peralatan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL: EDIT PERALATAN ===================== --}}
    <div x-show="editModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="editModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:480px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Edit Data Peralatan</h3>
                <button type="button" @click="editModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            <form :action="editUrl" method="POST" style="padding:20px;">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Peralatan <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nama" required x-model="activeAlat.nama"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    
                    {{-- Edit: Kategori Peralatan (Input Manual + Dropdown Riwayat + Hapus) --}}
                    <div x-data="{ open: false }" style="position:relative;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kategori Peralatan <span style="color:#DC2626;">*</span></label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" name="kategori" required x-model="activeAlat.kategori" placeholder="Ketik atau pilih kategori..."
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
                                     @click="activeAlat.kategori = item; open = false;"
                                     onmouseover="this.style.background='#EFF6FF'; this.style.color='#1B2A6B';"
                                     onmouseout="this.style.background='#FFFFFF'; this.style.color='#1E293B';"
                                     x-text="item">
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Catatan Khusus</label>
                        <textarea name="catatan" rows="3" x-model="activeAlat.catatan"
                                  style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; resize:none;"></textarea>
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

    {{-- ===================== MODAL: KONFIRMASI HAPUS ===================== --}}
    <div x-show="deleteModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="deleteModalOpen = false">
        <div class="admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:420px; padding:24px; text-align:center; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="width:48px; height:48px; border-radius:50%; background:#FEE2E2; color:#DC2626; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                <i data-lucide="trash-2" style="width:24px; height:24px;"></i>
            </div>
            <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0 0 6px;">Hapus Data Peralatan?</h3>
            <p style="font-size:13px; color:#64748B; margin-bottom:20px;">
                Apakah Anda yakin ingin menghapus peralatan <strong style="color:#0F172A;" x-text="activeAlat.nama"></strong>? Tindakan ini tidak dapat dibatalkan.
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

</div>

<script>
function dataPeralatanApp() {
    return {
        createModalOpen: {{ isset($errors) && $errors->any() ? 'true' : 'false' }},
        editModalOpen: false,
        deleteModalOpen: false,
        activeAlat: {},
        editUrl: '',
        deleteUrl: '',
        existingKategoriList: @json($existingKategoriList ?? []),
        removeOption(type, value) {
            if (!confirm("Hapus '" + value + "' dari daftar pilihan riwayat?")) return;

            if (type === 'kategori') {
                this.existingKategoriList = this.existingKategoriList.filter(item => item !== value);
            }

            fetch("{{ route('admin.pemeliharaan.data-peralatan.remove-history-option') }}", {
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
