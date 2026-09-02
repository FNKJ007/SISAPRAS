@extends('layouts.admin')
@section('title', 'Pengecekan Unit Pencegahan — Admin')

@section('content')
<div class="admin-pengecekan-wrapper" x-data="pengecekanPencegahanAdmin('{{ $tab }}')">

    {{-- Header Page --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#121E4E; margin:0 0 2px 0;">Pengecekan Unit Pencegahan</h1>
            <p style="font-size:13px; color:#64748B; margin:0;">Hasil pengecekan harian unit kendaraan dan alat pencegahan yang diinput oleh petugas.</p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; background:#FFFFFF; padding:6px 14px; border-radius:10px; border:1px solid #E2E8F0; box-shadow:0 2px 6px rgba(0,0,0,0.04);">
            <i data-lucide="clipboard-check" style="width:16px; height:16px; color:#1B2A6B;"></i>
            <span style="font-size:12.5px; font-weight:600; color:#1E293B;">Panel Pengecekan Admin</span>
        </div>
    </div>

    {{-- Ringkasan KPI Cards --}}
    <div class="kpi-grid-container">
        <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #E2E8F0; box-shadow:0 4px 14px rgba(0,0,0,0.03);">
            <div class="kpi-title" style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Cek Harian Unit</div>
            <div class="kpi-number" style="font-size:28px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total_cek_unit'] }}</div>
        </div>

        <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #FEE2E2; background:linear-gradient(180deg, #FFFFFF 0%, #FEF2F2 100%); box-shadow:0 4px 14px rgba(239,68,68,0.06);">
            <div class="kpi-title" style="font-size:11px; font-weight:700; color:#991B1B; text-transform:uppercase; letter-spacing:0.5px;">Unit Ada Perlengkapan Rusak</div>
            <div class="kpi-number" style="font-size:28px; font-weight:800; color:#B91C1C; margin-top:6px;">{{ $kpi['unit_ada_rusak'] }}</div>
        </div>

        <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #E2E8F0; box-shadow:0 4px 14px rgba(0,0,0,0.03);">
            <div class="kpi-title" style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Cek Harian Alat</div>
            <div class="kpi-number" style="font-size:28px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total_cek_alat'] }}</div>
        </div>

        <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #FEE2E2; background:linear-gradient(180deg, #FFFFFF 0%, #FEF2F2 100%); box-shadow:0 4px 14px rgba(239,68,68,0.06);">
            <div class="kpi-title" style="font-size:11px; font-weight:700; color:#991B1B; text-transform:uppercase; letter-spacing:0.5px;">Total Unit Alat Rusak</div>
            <div class="kpi-number" style="font-size:28px; font-weight:800; color:#B91C1C; margin-top:6px;">{{ $kpi['alat_rusak_total'] }}</div>
        </div>
    </div>

    {{-- Search Bar --}}
    <div style="margin-bottom:18px;">
        <form method="GET" action="{{ route('admin.unit-pencegahan.pengecekan') }}" style="display:flex; align-items:center; gap:8px; width:100%; max-width:520px;">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div style="position:relative; flex:1;">
                <input type="text" name="search" value="{{ $searchQuery ?? '' }}" placeholder="Cari berdasarkan Pos, Pemeriksa, atau Unit..."
                       style="width:100%; padding:8px 14px 8px 36px; font-size:12.5px; border-radius:10px; border:1px solid #CBD5E1; outline:none; background:#FFFFFF; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                <i data-lucide="search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:#94A3B8;"></i>
            </div>
            <button type="submit" style="padding:8px 16px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:12.5px; font-weight:700; cursor:pointer;">
                Cari
            </button>
            @if(!empty($searchQuery))
                <a href="{{ route('admin.unit-pencegahan.pengecekan') }}?tab={{ $tab }}" style="padding:8px 12px; background:#F1F5F9; color:#64748B; border:1px solid #CBD5E1; border-radius:10px; font-size:12px; font-weight:600; text-decoration:none;">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Sleek Compact Tab Switcher Bar --}}
    <div class="w-full flex items-center mb-4">
        <div class="inline-flex items-center gap-1 p-1 bg-white border border-slate-300 rounded-lg shadow-2xs">
            <button type="button" @click="activeTab = 'unit'"
                    :class="activeTab === 'unit' ? 'bg-[#1B2A6B] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md transition-colors duration-150 whitespace-nowrap cursor-pointer border-0">
                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                <span>Unit Kendaraan</span>
                <span :class="activeTab === 'unit' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                      class="px-1.5 py-0.25 rounded-full text-[10px] font-extrabold ml-0.5">
                    {{ $cekUnitList->total() }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'alat'"
                    :class="activeTab === 'alat' ? 'bg-[#1B2A6B] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md transition-colors duration-150 whitespace-nowrap cursor-pointer border-0">
                <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                <span>Alat Pencegahan</span>
                <span :class="activeTab === 'alat' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                      class="px-1.5 py-0.25 rounded-full text-[10px] font-extrabold ml-0.5">
                    {{ $cekAlatList->total() }}
                </span>
            </button>
        </div>
    </div>

    {{-- ===================== TAB: UNIT KENDARAAN ===================== --}}
    <div x-show="activeTab === 'unit'" x-cloak>
        <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
            @if($cekUnitList->isEmpty())
                <div style="padding:56px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
                        <i data-lucide="search-x" style="width:30px; height:30px; color:#64748B;"></i>
                    </div>
                    @if(!empty($searchQuery))
                        <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Data Tidak Ditemukan</div>
                        <div style="font-size:13px; color:#64748B; margin-bottom:18px; max-width:440px; margin-left:auto; margin-right:auto;">
                            Tidak ditemukan data pengecekan unit pencegahan yang cocok dengan kata kunci <strong style="color:#1E293B;">"{{ $searchQuery }}"</strong>.
                        </div>
                        <a href="{{ route('admin.unit-pencegahan.pengecekan') }}?tab=unit" style="display:inline-flex; align-items:center; gap:8px; padding:9px 20px; background:#1B2A6B; color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                            <i data-lucide="rotate-ccw" style="width:14px; height:14px;"></i>
                            <span>Reset Pencarian</span>
                        </a>
                    @else
                        <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Pengecekan</div>
                        <div style="font-size:12.5px; color:#94A3B8;">Belum ada petugas yang mengisi Cek Harian Unit Kendaraan Pencegahan.</div>
                    @endif
                </div>
            @else
                <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                    <table style="width:100%; min-width:900px; border-collapse:collapse; font-size:13px; text-align:left;">
                        <thead>
                            <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">
                                <th style="padding:14px 18px;">No</th>
                                <th style="padding:14px 18px;">Tanggal</th>
                                <th style="padding:14px 18px;">Pos Damkar</th>
                                <th style="padding:14px 18px;">Unit</th>
                                <th style="padding:14px 18px;">Pemeriksa</th>
                                <th style="padding:14px 18px;">BBM</th>
                                <th style="padding:14px 18px;">Perlengkapan Rusak</th>
                                <th style="padding:14px 18px; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cekUnitList as $index => $item)
                                <tr style="border-bottom:1px solid #F1F5F9;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                    <td style="padding:14px 18px; font-weight:600; color:#94A3B8;">{{ $cekUnitList->firstItem() + $index }}</td>
                                    <td style="padding:14px 18px; white-space:nowrap;">
                                        <div style="font-weight:600; color:#1E293B;">{{ $item->created_at->format('d/m/Y') }}</div>
                                        <div style="font-size:11px; color:#94A3B8;">{{ $item->created_at->format('H:i') }} WIB</div>
                                    </td>
                                    <td style="padding:14px 18px; white-space:nowrap; font-weight:600; color:#334155;">
                                        <span style="color:#64748B; margin-right:4px;">📍</span>{{ $item->pos ?? '—' }}
                                    </td>
                                    <td style="padding:14px 18px;">
                                        <div style="font-weight:700; color:#0F172A;">{{ $item->unit_nama ?? '—' }}</div>
                                    </td>
                                    <td style="padding:14px 18px;">
                                        <div style="font-weight:600; color:#1E293B;">{{ $item->nama_pemeriksa }}</div>
                                        <div style="font-size:11px; color:#94A3B8;">{{ $item->jabatan }}</div>
                                    </td>
                                    <td style="padding:14px 18px; white-space:nowrap;">
                                        <div style="font-size:12px; color:#334155; font-weight:500;">BBM: <strong style="color:#0F172A;">{{ ucfirst($item->jenis_bbm) }}</strong></div>
                                    </td>
                                    <td style="padding:14px 18px; white-space:nowrap;">
                                        @if(($item->jumlah_rusak ?? 0) > 0)
                                            <span style="display:inline-flex; align-items:center; gap:5px; font-weight:700; color:#DC2626; font-size:12px;">
                                                <span style="width:7px; height:7px; border-radius:50%; background:#EF4444; display:inline-block;"></span>
                                                {{ $item->jumlah_rusak }} Rusak
                                            </span>
                                        @else
                                            <span style="display:inline-flex; align-items:center; gap:5px; font-weight:600; color:#059669; font-size:12px;">
                                                <span style="width:7px; height:7px; border-radius:50%; background:#10B981; display:inline-block;"></span>
                                                Lengkap (0)
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                        <button type="button" @click="openUnitModal({{ json_encode($item) }})"
                                                style="padding:6px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                                            <i data-lucide="eye" style="width:14px; height:14px;"></i>
                                            <span>Detail</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:16px 20px; border-top:1px solid #F1F5F9; background:#FAFCFE;">
                    {{ $cekUnitList->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===================== TAB: ALAT PENCEGAHAN ===================== --}}
    <div x-show="activeTab === 'alat'" x-cloak>
        <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
            @if($cekAlatList->isEmpty())
                <div style="padding:56px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
                        <i data-lucide="search-x" style="width:30px; height:30px; color:#64748B;"></i>
                    </div>
                    @if(!empty($searchQuery))
                        <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Data Tidak Ditemukan</div>
                        <div style="font-size:13px; color:#64748B; margin-bottom:18px; max-width:440px; margin-left:auto; margin-right:auto;">
                            Tidak ditemukan data pengecekan alat pencegahan yang cocok dengan kata kunci <strong style="color:#1E293B;">"{{ $searchQuery }}"</strong>.
                        </div>
                        <a href="{{ route('admin.unit-pencegahan.pengecekan') }}?tab=alat" style="display:inline-flex; align-items:center; gap:8px; padding:9px 20px; background:#1B2A6B; color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                            <i data-lucide="rotate-ccw" style="width:14px; height:14px;"></i>
                            <span>Reset Pencarian</span>
                        </a>
                    @else
                        <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Pengecekan</div>
                        <div style="font-size:12.5px; color:#94A3B8;">Belum ada petugas yang mengisi Cek Harian Alat Pencegahan.</div>
                    @endif
                </div>
            @else
                <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                    <table style="width:100%; min-width:900px; border-collapse:collapse; font-size:13px; text-align:left;">
                        <thead>
                            <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">
                                <th style="padding:14px 18px;">No</th>
                                <th style="padding:14px 18px;">Tanggal Pemeriksaan</th>
                                <th style="padding:14px 18px;">Pos Damkar</th>
                                <th style="padding:14px 18px;">Pemeriksa</th>
                                <th style="padding:14px 18px;">Jumlah Baik</th>
                                <th style="padding:14px 18px;">Jumlah Rusak</th>
                                <th style="padding:14px 18px; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cekAlatList as $index => $item)
                                <tr style="border-bottom:1px solid #F1F5F9;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                    <td style="padding:14px 18px; font-weight:600; color:#94A3B8;">{{ $cekAlatList->firstItem() + $index }}</td>
                                    <td style="padding:14px 18px; white-space:nowrap;">
                                        <div style="font-weight:600; color:#1E293B;">{{ \Illuminate\Support\Carbon::parse($item->tanggal_pemeriksaan)->format('d/m/Y') }}</div>
                                        <div style="font-size:11px; color:#94A3B8;">{{ $item->created_at->format('H:i') }} WIB</div>
                                    </td>
                                    <td style="padding:14px 18px; white-space:nowrap; font-weight:600; color:#334155;">
                                        <span style="color:#64748B; margin-right:4px;">📍</span>{{ $item->pos ?? '—' }}
                                    </td>
                                    <td style="padding:14px 18px;">
                                        <div style="font-weight:600; color:#1E293B;">{{ $item->nama_pemeriksa }}</div>
                                        <div style="font-size:11px; color:#94A3B8;">{{ $item->jabatan }}</div>
                                    </td>
                                    <td style="padding:14px 18px; white-space:nowrap;">
                                        <span style="display:inline-flex; align-items:center; gap:5px; font-weight:700; color:#059669; font-size:12px;">
                                            <span style="width:7px; height:7px; border-radius:50%; background:#10B981; display:inline-block;"></span>
                                            {{ $item->total_baik }} Baik
                                        </span>
                                    </td>
                                    <td style="padding:14px 18px; white-space:nowrap;">
                                        @if($item->total_rusak > 0)
                                            <span style="display:inline-flex; align-items:center; gap:5px; font-weight:700; color:#DC2626; font-size:12px;">
                                                <span style="width:7px; height:7px; border-radius:50%; background:#EF4444; display:inline-block;"></span>
                                                {{ $item->total_rusak }} Rusak
                                            </span>
                                        @else
                                            <span style="color:#64748B; font-weight:600; font-size:12px;">
                                                0 Rusak
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                        <button type="button" @click="openAlatModal({{ json_encode($item) }})"
                                                style="padding:6px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                                            <i data-lucide="eye" style="width:14px; height:14px;"></i>
                                            <span>Detail</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="padding:16px 20px; border-top:1px solid #F1F5F9; background:#FAFCFE;">
                    {{ $cekAlatList->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===================== MODAL DETAIL: UNIT KENDARAAN PENCEGAHAN ===================== --}}
    <div x-show="unitModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:640px; max-height:88vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>

            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Detail Cek Harian Unit Kendaraan Pencegahan</h3>
                <div style="display:flex; gap:8px; align-items:center;">
                    <a :href="`/admin/unit-pencegahan/cek-harian-unit/${activeUnit.id}/export-pdf`" target="_blank"
                       style="display:inline-flex; align-items:center; gap:8px; padding:6px 10px; background:#059669; color:#FFFFFF; border-radius:8px; font-size:12px; font-weight:700; text-decoration:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v8.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V4a1 1 0 011-1zM4 15a1 1 0 011 1v1h10v-1a1 1 0 112 0v1a2 2 0 01-2 2H5a2 2 0 01-2-2v-1a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <span style="font-size:12px;">Unduh PDF</span>
                    </a>
                    <button type="button" @click="unitModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                        <i data-lucide="x" style="width:18px; height:18px;"></i>
                    </button>
                </div>
            </div>

            <div style="padding:18px 20px; font-size:12.5px;">

                {{-- Identitas --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px; background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0;">
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Pos Damkar:</span>
                        <strong style="color:#1D4ED8; font-weight:800;" x-text="activeUnit.pos || '-'"></strong>
                    </div>
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Unit:</span>
                        <strong style="color:#0F172A;" x-text="activeUnit.unit_nama || '-'"></strong>
                    </div>
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Tanggal:</span>
                        <strong style="color:#0F172A;" x-text="formatDate(activeUnit.created_at)"></strong>
                    </div>
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Nama Pemeriksa:</span>
                        <strong style="color:#0F172A;" x-text="activeUnit.nama_pemeriksa || '-'"></strong>
                    </div>
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Jabatan:</span>
                        <strong style="color:#0F172A;" x-text="activeUnit.jabatan || '-'"></strong>
                    </div>
                </div>

                {{-- Pemanasan & BBM --}}
                <div style="margin-bottom:14px;">
                    <div style="font-size:12px; font-weight:800; color:#0F172A; margin-bottom:8px; border-bottom:1px solid #E2E8F0; padding-bottom:6px;">Pemanasan &amp; BBM</div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div><span style="color:#64748B;">Jenis BBM:</span> <strong x-text="capitalize(activeUnit.jenis_bbm)"></strong></div>
                    </div>

                    {{-- Foto Pemanasan & Foto BBM --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:10px;">
                        <template x-if="activeUnit.bukti_pemanasan">
                            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:10px;"
                                 x-data="{ imgError: false }" x-effect="if(activeUnit) imgError = false">
                                <div style="font-size:11px; font-weight:700; color:#475569; margin-bottom:6px; display:flex; align-items:center; gap:4px;">
                                    <i data-lucide="image" style="width:14px; height:14px; color:#1B2A6B;"></i> Bukti Pemanasan
                                </div>
                                <div x-show="!imgError">
                                    <a :href="'/storage/' + activeUnit.bukti_pemanasan" target="_blank" title="Klik untuk lihat ukuran penuh">
                                        <img :src="'/storage/' + activeUnit.bukti_pemanasan" alt="Bukti Pemanasan"
                                             x-on:error="imgError = true"
                                             style="width:100%; height:120px; object-fit:cover; border-radius:8px; border:1px solid #CBD5E1; transition:transform 0.2s;"
                                             onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                                    </a>
                                </div>
                                <div x-show="imgError" style="height:120px; display:flex; flex-direction:column; align-items:center; justify-content:center; background:#F1F5F9; border-radius:8px; border:1px dashed #CBD5E1; color:#94A3B8; text-align:center; padding:10px;">
                                    <i data-lucide="image-off" style="width:22px; height:22px; color:#94A3B8; margin-bottom:4px;"></i>
                                    <span style="font-size:11px; font-weight:600; color:#64748B;">Foto tidak ada di server ini</span>
                                    <span style="font-size:9.5px; color:#94A3B8;">(File tersimpan di perangkat pengunggah)</span>
                                </div>
                            </div>
                        </template>

                        <template x-if="activeUnit.bukti_bbm">
                            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:10px;"
                                 x-data="{ imgError: false }" x-effect="if(activeUnit) imgError = false">
                                <div style="font-size:11px; font-weight:700; color:#475569; margin-bottom:6px; display:flex; align-items:center; gap:4px;">
                                    <i data-lucide="image" style="width:14px; height:14px; color:#1B2A6B;"></i> Bukti Level BBM
                                </div>
                                <div x-show="!imgError">
                                    <a :href="'/storage/' + activeUnit.bukti_bbm" target="_blank" title="Klik untuk lihat ukuran penuh">
                                        <img :src="'/storage/' + activeUnit.bukti_bbm" alt="Bukti Level BBM"
                                             x-on:error="imgError = true"
                                             style="width:100%; height:120px; object-fit:cover; border-radius:8px; border:1px solid #CBD5E1; transition:transform 0.2s;"
                                             onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                                    </a>
                                </div>
                                <div x-show="imgError" style="height:120px; display:flex; flex-direction:column; align-items:center; justify-content:center; background:#F1F5F9; border-radius:8px; border:1px dashed #CBD5E1; color:#94A3B8; text-align:center; padding:10px;">
                                    <i data-lucide="image-off" style="width:22px; height:22px; color:#94A3B8; margin-bottom:4px;"></i>
                                    <span style="font-size:11px; font-weight:600; color:#64748B;">Foto tidak ada di server ini</span>
                                    <span style="font-size:9.5px; color:#94A3B8;">(File tersimpan di perangkat pengunggah)</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Perlengkapan --}}
                <div>
                    <div style="font-size:12px; font-weight:800; color:#0F172A; margin-bottom:8px; border-bottom:1px solid #E2E8F0; padding-bottom:6px; display:flex; justify-content:space-between; align-items:center;">
                        <span>Perlengkapan Kendaraan</span>
                        <span style="font-size:10.5px; color:#64748B; font-weight:600;" x-text="Object.keys(activeUnit.perlengkapan || {}).length + ' item'"></span>
                    </div>
                    <div class="custom-scrollbar" style="display:flex; flex-direction:column; gap:6px; max-height:240px; overflow-y:auto; padding-right:2px;">
                        <template x-for="(val, key) in (activeUnit.perlengkapan || {})" :key="key">
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:7px 12px; border-radius:8px; background:#F8FAFC; border:1px solid #E2E8F0; transition:all 0.15s ease;">
                                <span style="font-weight:700; color:#0F172A; font-size:11.5px; text-transform:uppercase;" x-text="val.label"></span>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span x-show="val.catatan" style="font-size:11px; color:#64748B; font-style:italic;" x-text="val.catatan"></span>
                                    <span :class="val.status === 'rusak' ? 'badge-pill-rusak' : 'badge-pill-baik'"
                                          x-text="val.status === 'rusak' ? '✕ Rusak' : '✓ Baik'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ===================== MODAL DETAIL: ALAT PENCEGAHAN ===================== --}}
    <div x-show="alatModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:640px; max-height:88vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>

            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Detail Cek Harian Alat Pencegahan</h3>
                <div style="display:flex; gap:8px; align-items:center;">
                    <a :href="`/admin/unit-pencegahan/cek-harian-alat/${activeAlat.id}/export-pdf`" target="_blank"
                       style="display:inline-flex; align-items:center; gap:8px; padding:6px 10px; background:#059669; color:#FFFFFF; border-radius:8px; font-size:12px; font-weight:700; text-decoration:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v8.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V4a1 1 0 011-1zM4 15a1 1 0 011 1v1h10v-1a1 1 0 112 0v1a2 2 0 01-2 2H5a2 2 0 01-2-2v-1a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <span style="font-size:12px;">Unduh PDF</span>
                    </a>
                    <button type="button" @click="alatModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                        <i data-lucide="x" style="width:18px; height:18px;"></i>
                    </button>
                </div>
            </div>

            <div style="padding:18px 20px; font-size:12.5px;">

                {{-- Identitas --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px; background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0;">
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Pos Damkar:</span>
                        <strong style="color:#1D4ED8; font-weight:800;" x-text="activeAlat.pos || '-'"></strong>
                    </div>
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Tanggal Pemeriksaan:</span>
                        <strong style="color:#0F172A;" x-text="formatDate(activeAlat.tanggal_pemeriksaan)"></strong>
                    </div>
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Nama Pemeriksa:</span>
                        <strong style="color:#0F172A;" x-text="activeAlat.nama_pemeriksa || '-'"></strong>
                    </div>
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Jabatan:</span>
                        <strong style="color:#0F172A;" x-text="activeAlat.jabatan || '-'"></strong>
                    </div>
                </div>

                {{-- Daftar Alat --}}
                <div style="margin-bottom:14px;">
                    <div style="font-size:12px; font-weight:800; color:#0F172A; margin-bottom:8px; border-bottom:1px solid #E2E8F0; padding-bottom:6px; display:flex; justify-content:space-between; align-items:center;">
                        <span>Daftar Alat</span>
                        <span style="font-size:10.5px; color:#64748B; font-weight:600;" x-text="(activeAlat.alat || []).length + ' item'"></span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px; max-height:300px; overflow-y:auto; padding-right:4px;">
                        <template x-for="(alat, idx) in (activeAlat.alat || [])" :key="idx">
                            <div style="padding:8px 12px; border-radius:8px; border:1px solid #E2E8F0; background:#F8FAFC;">
                                <div style="display:grid; grid-template-columns: 1fr 70px 70px; gap:8px; align-items:center;">
                                    <span style="font-weight:700; color:#1E293B; font-size:12px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" :title="alat.nama" x-text="alat.nama"></span>

                                    <span style="display:inline-flex; align-items:center; justify-content:center; background:#D1FAE5; color:#065F46; padding:3px 8px; border-radius:20px; font-size:10.5px; font-weight:700; text-align:center; width:70px;" x-text="'Baik: ' + alat.jumlah_baik"></span>

                                    <span style="display:inline-flex; align-items:center; justify-content:center; background:#FEE2E2; color:#991B1B; padding:3px 8px; border-radius:20px; font-size:10.5px; font-weight:700; text-align:center; width:70px;" x-text="'Rusak: ' + alat.jumlah_rusak"></span>
                                </div>

                                <template x-if="alat.jumlah_rusak > 0 && alat.nomor_rusak">
                                    <div style="font-size:11px; color:#DC2626; margin-top:4px; font-style:italic;" x-text="'Keterangan: ' + alat.nomor_rusak"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Catatan & Foto --}}
                <div>
                    <div style="font-size:12px; font-weight:800; color:#0F172A; margin-bottom:8px; border-bottom:1px solid #E2E8F0; padding-bottom:6px;">Catatan &amp; Dokumentasi</div>
                    <template x-if="activeAlat.catatan_umum">
                        <div style="background:#F8FAFC; border:1px solid #E2E8F0; padding:8px 10px; border-radius:8px; color:#334155; margin-bottom:8px;">
                            <span x-text="activeAlat.catatan_umum"></span>
                        </div>
                    </template>
                    <template x-if="!activeAlat.catatan_umum">
                        <div style="color:#94A3B8; font-style:italic; margin-bottom:8px;">Tidak ada catatan.</div>
                    </template>

                    {{-- Preview Foto Umum Alat --}}
                    <template x-if="fotoUmumList(activeAlat).length > 0">
                        <div style="margin-top:10px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:10px;">
                            <div style="font-size:11px; font-weight:700; color:#475569; margin-bottom:6px; display:flex; align-items:center; gap:4px;">
                                <i data-lucide="image" style="width:14px; height:14px; color:#1B2A6B;"></i> Dokumentasi Foto Umum
                            </div>
                            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                                <template x-for="(foto, fi) in fotoUmumList(activeAlat)" :key="fi">
                                    <a :href="'/storage/' + foto" target="_blank" title="Klik untuk lihat ukuran penuh" style="width:calc(50% - 4px); min-width:110px;">
                                        <img :src="'/storage/' + foto" alt="Foto Umum Dokumentasi Alat"
                                             style="width:100%; height:110px; object-fit:cover; border-radius:8px; border:1px solid #CBD5E1; transition:transform 0.2s;"
                                             onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

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
    .kpi-card { padding: 12px 14px !important; }
    .kpi-title { font-size: 10px !important; line-height: 1.2 !important; }
    .kpi-number { font-size: 22px !important; margin-top: 4px !important; }
    .admin-modal-overlay { padding: 12px !important; }
    .admin-modal-dialog { max-height: 85vh !important; width: 100% !important; }
}
</style>
@endpush

@push('scripts')
<script>
function pengecekanPencegahanAdmin(initialTab) {
    return {
        activeTab: initialTab === 'alat' ? 'alat' : 'unit',

        unitModalOpen: false,
        activeUnit: {},

        alatModalOpen: false,
        activeAlat: {},

        openUnitModal(item) {
            this.activeUnit = item;
            this.unitModalOpen = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        openAlatModal(item) {
            this.activeAlat = item;
            this.alatModalOpen = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        capitalize(val) {
            if (!val) return '-';
            return val.charAt(0).toUpperCase() + val.slice(1).replace(/_/g, ' ');
        },

        formatDate(val) {
            if (!val) return '-';
            const d = new Date(val);
            if (isNaN(d)) return val;
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },

        fotoUmumList(item) {
            if (!item || !item.foto_umum) return [];
            if (Array.isArray(item.foto_umum)) return item.foto_umum;
            if (typeof item.foto_umum === 'string') {
                try {
                    const parsed = JSON.parse(item.foto_umum);
                    return Array.isArray(parsed) ? parsed : [item.foto_umum];
                } catch (e) {
                    return [item.foto_umum];
                }
            }
            return [];
        }
    };
}
</script>
@endpush
@endsection
