@extends('layouts.admin')

@section('title', 'Data Unit Kendaraan — Admin')

@section('content')
<div x-data="{
    createModalOpen: false,
    editModalOpen: false,
    deleteModalOpen: false,
    activeUnit: {},
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
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Data Unit Kendaraan</h1>
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
            </div>

            {{-- Input Pencarian --}}
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="position:relative;">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari nama unit, lambung, pos..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:220px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
            </div>
        </form>
    </div>

    {{-- Tabel Data Unit --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
        @if($unitList->isEmpty())
            <div style="padding:48px 20px; text-align:center; color:#64748B;">
                <i data-lucide="inbox" style="width:44px; height:44px; color:#CBD5E1; margin-bottom:12px;"></i>
                <div style="font-size:15px; font-weight:700; color:#334155;">Belum Ada Data Unit</div>
                <div style="font-size:12.5px; color:#94A3B8; margin-top:4px;">Tidak ada data armada unit kendaraan sesuai filter pencarian.</div>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; min-width:900px; table-layout:fixed; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">
                            <th style="padding:14px 16px; width:60px; text-align:center;">No</th>
                            <th style="padding:14px 18px; width:26%;">Nama Unit</th>
                            <th style="padding:14px 18px; width:15%;">Kategori</th>
                            <th style="padding:14px 18px; width:18%;">No. Lambung / Plat</th>
                            <th style="padding:14px 18px; width:18%;">Pos / Lokasi</th>
                            <th style="padding:14px 18px;">Status Operasional</th>
                            <th style="padding:14px 18px; width:130px; text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unitList as $index => $item)
                            <tr style="border-bottom:1px solid #F1F5F9;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:14px 16px; font-weight:600; color:#94A3B8; text-align:center;">{{ $unitList->firstItem() + $index }}</td>
                                <td style="padding:14px 18px;">
                                    <div style="font-weight:700; color:#0F172A;">{{ $item->nama }}</div>
                                    <div style="font-size:11px; color:#94A3B8;">{{ $item->merk_tipe ?? 'Tipe —' }} @if($item->tahun_pembuatan) (Th {{ $item->tahun_pembuatan }}) @endif</div>
                                </td>
                                <td style="padding:14px 18px;">
                                    @if($item->kategori === 'pemadam')
                                        <span style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">Pemadam</span>
                                    @else
                                        <span style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">Rescue</span>
                                    @endif
                                </td>
                                <td style="padding:14px 18px;">
                                    <div style="font-weight:600; color:#1E293B;">{{ $item->nomor_lambung ?? '—' }}</div>
                                    <div style="font-size:11px; color:#64748B;">{{ $item->plat_nomor ?? '—' }}</div>
                                </td>
                                <td style="padding:14px 18px;">
                                    <div style="font-weight:600; color:#334155;">{{ $item->pos ?? '—' }}</div>
                                </td>
                                <td style="padding:14px 18px;">
                                    @if($item->status === 'aktif')
                                        <span style="background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700;">✓ Aktif</span>
                                    @elseif($item->status === 'perbaikan')
                                        <span style="background:#FEF3C7; color:#92400E; border:1px solid #FDE68A; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700;">🛠 Perbaikan</span>
                                    @else
                                        <span style="background:#F1F5F9; color:#64748B; border:1px solid #E2E8F0; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700;">Non-Aktif</span>
                                    @endif
                                </td>
                                <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                        <button type="button" @click="
                                            activeUnit = {{ json_encode($item) }};
                                            editUrl = '{{ route('admin.pemeliharaan.data-unit.update', $item->id) }}';
                                            editModalOpen = true;
                                        " style="padding:5px 11px; background:#F1F5F9; color:#334155; border:1px solid #CBD5E1; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                            Edit
                                        </button>
                                        <button type="button" @click="
                                            activeUnit = {{ json_encode($item) }};
                                            deleteUrl = '{{ route('admin.pemeliharaan.data-unit.destroy', $item->id) }}';
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
                {{ $unitList->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL: TAMBAH UNIT ===================== --}}
    <div x-show="createModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="createModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Tambah Unit Kendaraan Baru</h3>
                <button type="button" @click="createModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            <form action="{{ route('admin.pemeliharaan.data-unit.store') }}" method="POST" style="padding:20px;">
                @csrf
                <div class="modal-form-grid">
                    <div class="full-mobile" style="grid-column: span 2;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Unit <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nama" required placeholder="Contoh: Damkar 04 - Hino Ranger"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kategori <span style="color:#DC2626;">*</span></label>
                        <select name="kategori" required style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                            <option value="pemadam">Pemadam</option>
                            <option value="rescue">Rescue</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Status Operasional <span style="color:#DC2626;">*</span></label>
                        <select name="status" required style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                            <option value="aktif">Aktif (Siap Operasi)</option>
                            <option value="perbaikan">Dalam Perbaikan</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. Lambung</label>
                        <input type="text" name="nomor_lambung" placeholder="Contoh: DK-04"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Plat Nomor</label>
                        <input type="text" name="plat_nomor" placeholder="Contoh: B 9004 DBA"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Pos / Sektor</label>
                        <input type="text" name="pos" placeholder="Contoh: Pos Mako"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Merk / Tipe</label>
                        <input type="text" name="merk_tipe" placeholder="Contoh: Hino 500 FM"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                </div>
                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Catatan Khusus</label>
                    <textarea name="catatan" rows="3" placeholder="Tuliskan catatan tambahan armada..."
                              style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; resize:none;"></textarea>
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

    {{-- ===================== MODAL: EDIT UNIT ===================== --}}
    <div x-show="editModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="editModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Edit Data Unit Kendaraan</h3>
                <button type="button" @click="editModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            <form :action="editUrl" method="POST" style="padding:20px;">
                @csrf
                @method('PUT')
                <div class="modal-form-grid">
                    <div class="full-mobile" style="grid-column: span 2;">
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Unit <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nama" required x-model="activeUnit.nama"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kategori <span style="color:#DC2626;">*</span></label>
                        <select name="kategori" required x-model="activeUnit.kategori" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                            <option value="pemadam">Pemadam</option>
                            <option value="rescue">Rescue</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Status Operasional <span style="color:#DC2626;">*</span></label>
                        <select name="status" required x-model="activeUnit.status" style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF;">
                            <option value="aktif">Aktif (Siap Operasi)</option>
                            <option value="perbaikan">Dalam Perbaikan</option>
                            <option value="nonaktif">Non-Aktif</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. Lambung</label>
                        <input type="text" name="nomor_lambung" x-model="activeUnit.nomor_lambung"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Plat Nomor</label>
                        <input type="text" name="plat_nomor" x-model="activeUnit.plat_nomor"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Pos / Sektor</label>
                        <input type="text" name="pos" x-model="activeUnit.pos"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Merk / Tipe</label>
                        <input type="text" name="merk_tipe" x-model="activeUnit.merk_tipe"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                </div>
                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Catatan Khusus</label>
                    <textarea name="catatan" rows="3" x-model="activeUnit.catatan"
                              style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; resize:none;"></textarea>
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
            <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0 0 6px;">Hapus Data Unit?</h3>
            <p style="font-size:13px; color:#64748B; margin-bottom:20px;">
                Apakah Anda yakin ingin menghapus unit <strong style="color:#0F172A;" x-text="activeUnit.nama"></strong>? Tindakan ini tidak dapat dibatalkan.
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
@endsection
