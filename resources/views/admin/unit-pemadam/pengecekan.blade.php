@extends('layouts.admin')
@section('title', 'Pengecekan Unit Pemadam — Admin')

@section('content')
<div class="space-y-6" x-data="pengecekanPemadamAdmin('{{ $tab }}')">

    {{-- Header Page --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#121E4E; margin:0 0 2px 0;">Pengecekan Unit Pemadam</h1>
            <p style="font-size:13px; color:#64748B; margin:0;">Hasil pengecekan harian unit kendaraan dan alat pemadam yang diinput oleh petugas.</p>
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

    {{-- Compact Modern Switcher Bar --}}
    <div style="display:inline-flex; align-items:center; background:#FFFFFF; padding:4px; border-radius:10px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.05); margin-bottom:18px;">
        <div style="display:flex; align-items:center; gap:4px;">
            <button type="button" @click="activeTab = 'unit'"
                    :style="activeTab === 'unit' 
                        ? 'background:linear-gradient(135deg, #1B2A6B 0%, #2563EB 100%); color:#FFFFFF; box-shadow:0 2px 8px rgba(37,99,235,0.25); font-weight:700;' 
                        : 'color:#64748B; background:transparent; font-weight:600;'"
                    style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; font-size:12.5px; border:none; border-radius:8px; cursor:pointer; transition:all 0.2s ease;">
                <i data-lucide="truck" style="width:14px; height:14px;"></i>
                <span>Unit Kendaraan</span>
                <span :style="activeTab === 'unit' ? 'background:rgba(255,255,255,0.25); color:#FFFFFF;' : 'background:#F1F5F9; color:#64748B;'"
                      style="padding:1px 6px; border-radius:12px; font-size:10px; font-weight:700; margin-left:2px;">
                    {{ count($cekUnitList) }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'alat'"
                    :style="activeTab === 'alat' 
                        ? 'background:linear-gradient(135deg, #1B2A6B 0%, #2563EB 100%); color:#FFFFFF; box-shadow:0 2px 8px rgba(37,99,235,0.25); font-weight:700;' 
                        : 'color:#64748B; background:transparent; font-weight:600;'"
                    style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; font-size:12.5px; border:none; border-radius:8px; cursor:pointer; transition:all 0.2s ease;">
                <i data-lucide="shield-alert" style="width:14px; height:14px;"></i>
                <span>Alat Pemadam</span>
                <span :style="activeTab === 'alat' ? 'background:rgba(255,255,255,0.25); color:#FFFFFF;' : 'background:#F1F5F9; color:#64748B;'"
                      style="padding:1px 6px; border-radius:12px; font-size:10px; font-weight:700; margin-left:2px;">
                    {{ count($cekAlatList) }}
                </span>
            </button>
        </div>
    </div>

    {{-- ===================== TAB: UNIT KENDARAAN ===================== --}}
    <div x-show="activeTab === 'unit'" x-cloak>
        <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
            @if($cekUnitList->isEmpty())
                <div style="padding:48px 20px; text-align:center; color:#64748B;">
                    <i data-lucide="inbox" style="width:44px; height:44px; color:#CBD5E1; margin-bottom:12px;"></i>
                    <div style="font-size:15px; font-weight:700; color:#334155;">Belum Ada Data Pengecekan</div>
                    <div style="font-size:12.5px; color:#94A3B8; margin-top:4px;">Belum ada petugas yang mengisi Cek Harian Unit Kendaraan Pemadam.</div>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                        <thead>
                            <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">
                                <th style="padding:14px 18px;">No</th>
                                <th style="padding:14px 18px;">Tanggal &amp; Shift</th>
                                <th style="padding:14px 18px;">Unit</th>
                                <th style="padding:14px 18px;">Pemeriksa</th>
                                <th style="padding:14px 18px;">BBM &amp; Air</th>
                                <th style="padding:14px 18px;">Tangki &amp; Pompa</th>
                                <th style="padding:14px 18px;">Perlengkapan</th>
                                <th style="padding:14px 18px; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cekUnitList as $index => $item)
                                <tr style="border-bottom:1px solid #F1F5F9;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                    <td style="padding:14px 18px; font-weight:600; color:#94A3B8;">{{ $cekUnitList->firstItem() + $index }}</td>
                                    <td style="padding:14px 18px; white-space:nowrap;">
                                        <div style="font-weight:600; color:#1E293B;">{{ $item->created_at->format('d/m/Y') }}</div>
                                        <div style="font-size:11px; color:#94A3B8;">Shift {{ \App\Models\CekHarianUnit::$shiftMap[$item->shift] ?? $item->shift }}</div>
                                    </td>
                                    <td style="padding:14px 18px;">
                                        <div style="font-weight:700; color:#0F172A;">{{ $item->unit_nama ?? '—' }}</div>
                                    </td>
                                    <td style="padding:14px 18px;">
                                        <div style="font-weight:600; color:#1E293B;">{{ $item->nama_pemeriksa }}</div>
                                        <div style="font-size:11px; color:#94A3B8;">{{ $item->jabatan }}</div>
                                    </td>
                                    <td style="padding:14px 18px;">
                                        <div style="font-size:11.5px; color:#334155;">BBM: <strong>{{ ucfirst($item->jenis_bbm) }} · {{ \App\Models\CekHarianUnit::$levelMap[$item->level_bbm] ?? $item->level_bbm }}</strong></div>
                                        <div style="font-size:11.5px; color:#334155;">Air: <strong>{{ \App\Models\CekHarianUnit::$levelMap[$item->level_air] ?? $item->level_air }}</strong></div>
                                    </td>
                                    <td style="padding:14px 18px;">
                                        @if($item->kondisi_tangki === 'baik' && $item->tekanan_pompa === 'baik')
                                            <span style="background:#D1FAE5; color:#065F46; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; border:1px solid #A7F3D0;">Baik</span>
                                        @else
                                            <span style="background:#FEF3C7; color:#92400E; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; border:1px solid #FDE68A;">Perlu Perhatian</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 18px;">
                                        <span style="background:#FEE2E2; color:#991B1B; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700;">
                                            {{ $item->jumlah_rusak }}
                                        </span>
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

    {{-- ===================== TAB: ALAT PEMADAM ===================== --}}
    <div x-show="activeTab === 'alat'" x-cloak>
        <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
            @if($cekAlatList->isEmpty())
                <div style="padding:48px 20px; text-align:center; color:#64748B;">
                    <i data-lucide="inbox" style="width:44px; height:44px; color:#CBD5E1; margin-bottom:12px;"></i>
                    <div style="font-size:15px; font-weight:700; color:#334155;">Belum Ada Data Pengecekan</div>
                    <div style="font-size:12.5px; color:#94A3B8; margin-top:4px;">Belum ada petugas yang mengisi Cek Harian Alat Pemadam.</div>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                        <thead>
                            <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">
                                <th style="padding:14px 18px;">No</th>
                                <th style="padding:14px 18px;">Tanggal Pemeriksaan</th>
                                <th style="padding:14px 18px;">Unit</th>
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
                                        <div style="font-size:11px; color:#94A3B8;">Diinput {{ $item->created_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td style="padding:14px 18px;">
                                        <div style="font-weight:700; color:#0F172A;">{{ $item->unit_nama ?? '—' }}</div>
                                    </td>
                                    <td style="padding:14px 18px;">
                                        <div style="font-weight:600; color:#1E293B;">{{ $item->nama_pemeriksa }}</div>
                                        <div style="font-size:11px; color:#94A3B8;">{{ $item->jabatan }}</div>
                                    </td>
                                    <td style="padding:14px 18px;">
                                        <span style="background:#D1FAE5; color:#065F46; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; border:1px solid #A7F3D0;">
                                            {{ $item->total_baik }}
                                        </span>
                                    </td>
                                    <td style="padding:14px 18px;">
                                        @if($item->total_rusak > 0)
                                            <span style="background:#FEE2E2; color:#991B1B; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; border:1px solid #FCA5A5;">
                                                {{ $item->total_rusak }}
                                            </span>
                                        @else
                                            <span style="background:#D1FAE5; color:#065F46; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; border:1px solid #A7F3D0;">
                                                0
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

    {{-- ===================== MODAL DETAIL: UNIT KENDARAAN ===================== --}}
    <div x-show="unitModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="unitModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:640px; max-height:88vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>

            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Detail Cek Harian Unit Kendaraan</h3>
                <button type="button" @click="unitModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>

            <div style="padding:18px 20px; font-size:12.5px;">

                {{-- Identitas --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px; background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0;">
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Unit:</span>
                        <strong style="color:#0F172A;" x-text="activeUnit.unit_nama || '-'"></strong>
                    </div>
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Shift:</span>
                        <strong style="color:#0F172A;" x-text="shiftLabel(activeUnit.shift)"></strong>
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
                        <div><span style="color:#64748B;">Level Air:</span> <strong x-text="levelLabel(activeUnit.level_air)"></strong></div>
                    </div>
                    
                    {{-- Foto Pemanasan & Foto BBM --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:10px;">
                        <template x-if="activeUnit.bukti_pemanasan">
                            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:10px;">
                                <div style="font-size:11px; font-weight:700; color:#475569; margin-bottom:6px; display:flex; align-items:center; gap:4px;">
                                    <i data-lucide="image" style="width:14px; height:14px; color:#1B2A6B;"></i> Bukti Pemanasan
                                </div>
                                <a :href="'/storage/' + activeUnit.bukti_pemanasan" target="_blank" title="Klik untuk lihat ukuran penuh">
                                    <img :src="'/storage/' + activeUnit.bukti_pemanasan" alt="Bukti Pemanasan"
                                         style="width:100%; height:120px; object-fit:cover; border-radius:8px; border:1px solid #CBD5E1; transition:transform 0.2s;"
                                         onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                                </a>
                            </div>
                        </template>

                        <template x-if="activeUnit.bukti_bbm">
                            <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:10px;">
                                <div style="font-size:11px; font-weight:700; color:#475569; margin-bottom:6px; display:flex; align-items:center; gap:4px;">
                                    <i data-lucide="image" style="width:14px; height:14px; color:#1B2A6B;"></i> Bukti Level BBM
                                </div>
                                <a :href="'/storage/' + activeUnit.bukti_bbm" target="_blank" title="Klik untuk lihat ukuran penuh">
                                    <img :src="'/storage/' + activeUnit.bukti_bbm" alt="Bukti Level BBM"
                                         style="width:100%; height:120px; object-fit:cover; border-radius:8px; border:1px solid #CBD5E1; transition:transform 0.2s;"
                                         onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                                </a>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Tangki & Pompa --}}
                <div style="margin-bottom:14px;">
                    <div style="font-size:12px; font-weight:800; color:#0F172A; margin-bottom:8px; border-bottom:1px solid #E2E8F0; padding-bottom:6px;">Tangki &amp; Pompa</div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div><span style="color:#64748B;">Level Air:</span> <strong x-text="levelLabel(activeUnit.level_air)"></strong></div>
                        <div><span style="color:#64748B;">Kondisi Tangki:</span> <strong x-text="capitalize(activeUnit.kondisi_tangki_air)"></strong></div>
                        <div><span style="color:#64748B;">Kebocoran Tangki:</span> <strong x-text="activeUnit.kebocoran_tangki_air === 'ada' ? 'Ada' : 'Tidak Ada'"></strong></div>
                        <div><span style="color:#64748B;">Tekanan Pompa:</span> <strong x-text="capitalize(activeUnit.tekanan_pompa)"></strong></div>
                        <div><span style="color:#64748B;">Selang Induk:</span> <strong x-text="capitalize(activeUnit.selang_induk)"></strong></div>
                    </div>
                    <template x-if="activeUnit.catatan_tangki_pompa">
                        <div style="margin-top:8px; background:#FFFBEB; border:1px solid #FDE68A; padding:8px 10px; border-radius:8px; color:#92400E;">
                            <span x-text="activeUnit.catatan_tangki_pompa"></span>
                        </div>
                    </template>
                    
                    {{-- Foto Tangki & Pompa --}}
                    <template x-if="activeUnit.dokumentasi_tangki_pompa && activeUnit.dokumentasi_tangki_pompa.length > 0">
                        <div style="margin-top:12px;">
                            <div style="font-size:11px; font-weight:700; color:#475569; margin-bottom:6px;">Dokumentasi Tangki &amp; Pompa:</div>
                            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px;">
                                <template x-for="(foto, idx) in activeUnit.dokumentasi_tangki_pompa" :key="idx">
                                    <a :href="'/storage/' + foto" target="_blank" title="Klik untuk lihat ukuran penuh">
                                        <img :src="'/storage/' + foto" :alt="'Foto ' + (idx + 1)"
                                             style="width:100%; height:90px; object-fit:cover; border-radius:8px; border:1px solid #CBD5E1; transition:transform 0.2s;"
                                             onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Perlengkapan --}}
                <div>
                    <div style="font-size:12px; font-weight:800; color:#0F172A; margin-bottom:8px; border-bottom:1px solid #E2E8F0; padding-bottom:6px; display:flex; justify-content:space-between; align-items:center;">
                        <span>Perlengkapan Kendaraan</span>
                        <span style="font-size:10.5px; color:#64748B; font-weight:600;" x-text="Object.keys(activeUnit.perlengkapan || {}).length + ' item'"></span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:4px; max-height:220px; overflow-y:auto;">
                        <template x-for="(val, key) in (activeUnit.perlengkapan || {})" :key="key">
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:6px 10px; border-radius:8px;"
                                 :style="val.status === 'rusak' ? 'background:#FEF2F2;' : 'background:#F8FAFC;'">
                                <span style="font-weight:600; color:#1E293B;" x-text="val.label"></span>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span x-show="val.catatan" style="font-size:11px; color:#64748B; font-style:italic;" x-text="val.catatan"></span>
                                    <span :style="val.status === 'rusak' ? 'background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5;' : 'background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0;'"
                                          style="padding:2px 9px; border-radius:20px; font-size:10.5px; font-weight:700;"
                                          x-text="val.status === 'rusak' ? 'Rusak' : 'Baik'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ===================== MODAL DETAIL: ALAT PEMADAM ===================== --}}
    <div x-show="alatModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="alatModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:640px; max-height:88vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>

            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Detail Cek Harian Alat Pemadam</h3>
                <button type="button" @click="alatModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>

            <div style="padding:18px 20px; font-size:12.5px;">

                {{-- Identitas --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px; background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0;">
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Unit:</span>
                        <strong style="color:#0F172A;" x-text="activeAlat.unit_nama || '-'"></strong>
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
                    <template x-if="activeAlat.foto_umum">
                        <div style="margin-top:10px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:10px; max-width:240px;">
                            <div style="font-size:11px; font-weight:700; color:#475569; margin-bottom:6px; display:flex; align-items:center; gap:4px;">
                                <i data-lucide="image" style="width:14px; height:14px; color:#1B2A6B;"></i> Dokumentasi Foto Umum
                            </div>
                            <a :href="'/storage/' + activeAlat.foto_umum" target="_blank" title="Klik untuk lihat ukuran penuh">
                                <img :src="'/storage/' + activeAlat.foto_umum" alt="Foto Umum Dokumentasi Alat"
                                     style="width:100%; height:130px; object-fit:cover; border-radius:8px; border:1px solid #CBD5E1; transition:transform 0.2s;"
                                     onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                            </a>
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
function pengecekanPemadamAdmin(initialTab) {
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

        shiftLabel(val) {
            const map = { pagi: 'Pagi', siang: 'Siang', malam: 'Malam' };
            return map[val] || val || '-';
        },

        levelLabel(val) {
            const map = { penuh: 'Penuh', '3_4': '3/4', '1_2': '1/2', kosong: 'Kosong' };
            return map[val] || val || '-';
        },

        formatDate(val) {
            if (!val) return '-';
            const d = new Date(val);
            if (isNaN(d)) return val;
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }
    };
}
</script>
@endpush
@endsection
