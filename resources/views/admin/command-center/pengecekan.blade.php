@extends('layouts.admin')
@section('title', 'Pengecekan Command Center — Admin')

@section('content')
<div class="admin-pengecekan-container" x-data="{
    activeAlat: null,
    alatModalOpen: false,
    openAlatModal(item) {
        this.activeAlat = item;
        this.alatModalOpen = true;
    },
    formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }
}">

    {{-- Page Header --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <div>
            <h1 style="font-size:24px; font-weight:800; color:#0F172A; margin:0; letter-spacing:-0.5px;">Pengecekan Command Center</h1>
            <p style="color:#64748B; font-size:13.5px; margin-top:4px; margin-bottom:0;">Hasil pengecekan harian peralatan dan fasilitas Command Center yang diinput oleh petugas.</p>
        </div>
        <div style="display:inline-flex; align-items:center; gap:8px; background:#FFFFFF; border:1px solid #E2E8F0; padding:8px 14px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
            <i data-lucide="clipboard-check" style="width:16px; height:16px; color:#1B2A6B;"></i>
            <span style="font-size:12.5px; font-weight:700; color:#1B2A6B;">Panel Pengecekan Admin</span>
        </div>
    </div>

    {{-- Ringkasan KPI Cards --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:24px;">
        <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #E2E8F0; box-shadow:0 4px 14px rgba(0,0,0,0.03);">
            <div class="kpi-title" style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Total Pengecekan CC</div>
            <div class="kpi-number" style="font-size:28px; font-weight:800; color:#1B2A6B; margin-top:6px;">{{ $kpi['total_cek_cc'] }}</div>
        </div>

        <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #D1FAE5; background:linear-gradient(180deg, #FFFFFF 0%, #F0FDF4 100%); box-shadow:0 4px 14px rgba(16,185,129,0.06);">
            <div class="kpi-title" style="font-size:11px; font-weight:700; color:#065F46; text-transform:uppercase; letter-spacing:0.5px;">Total Peralatan Baik</div>
            <div class="kpi-number" style="font-size:28px; font-weight:800; color:#047857; margin-top:6px;">{{ $kpi['total_baik_cc'] }}</div>
        </div>

        <div class="kpi-card" style="background:#FFFFFF; border-radius:14px; padding:18px 20px; border:1px solid #FEE2E2; background:linear-gradient(180deg, #FFFFFF 0%, #FEF2F2 100%); box-shadow:0 4px 14px rgba(239,68,68,0.06);">
            <div class="kpi-title" style="font-size:11px; font-weight:700; color:#991B1B; text-transform:uppercase; letter-spacing:0.5px;">Total Peralatan Rusak</div>
            <div class="kpi-number" style="font-size:28px; font-weight:800; color:#B91C1C; margin-top:6px;">{{ $kpi['total_rusak_cc'] }}</div>
        </div>
    </div>

    {{-- Table Container --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
        @if($cekAlatList->isEmpty())
            <div style="padding:48px 20px; text-align:center; color:#64748B;">
                <i data-lucide="inbox" style="width:44px; height:44px; color:#CBD5E1; margin-bottom:12px;"></i>
                <div style="font-size:15px; font-weight:700; color:#334155;">Belum Ada Data Pengecekan</div>
                <div style="font-size:12.5px; color:#94A3B8; margin-top:4px;">Belum ada petugas yang mengisi Cek Harian Alat Command Center.</div>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11.5px; text-transform:uppercase; letter-spacing:0.5px;">
                            <th style="padding:14px 18px;">No</th>
                            <th style="padding:14px 18px;">Tanggal Pemeriksaan</th>
                            <th style="padding:14px 18px;">Regu / Unit</th>
                            <th style="padding:14px 18px;">Pemeriksa</th>
                            <th style="padding:14px 18px;">Jumlah Baik</th>
                            <th style="padding:14px 18px;">Jumlah Rusak</th>
                            <th style="padding:14px 18px; text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="divide-y:1px solid #E2E8F0;">
                        @foreach($cekAlatList as $index => $item)
                            <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:14px 18px; font-weight:700; color:#64748B;">{{ $index + 1 }}</td>
                                <td style="padding:14px 18px;">
                                    <div style="font-weight:700; color:#0F172A;">{{ $item->tanggal_pemeriksaan->format('d/m/Y') }}</div>
                                    <div style="font-size:11px; color:#94A3B8;">Diinput {{ $item->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td style="padding:14px 18px;">
                                    <span style="background:#F1F5F9; color:#1E293B; padding:4px 10px; border-radius:8px; font-weight:700; font-size:12px; border:1px solid #E2E8F0;">
                                        {{ $item->unit_nama ?? 'Regu ' . $item->unit_id }}
                                    </span>
                                </td>
                                <td style="padding:14px 18px;">
                                    <div style="font-weight:600; color:#1E293B;">{{ $item->nama_pemeriksa }}</div>
                                    <div style="font-size:11px; color:#94A3B8;">{{ $item->jabatan }}</div>
                                </td>
                                <td style="padding:14px 18px;">
                                    <span style="background:#D1FAE5; color:#065F46; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700;">
                                        {{ $item->total_baik }}
                                    </span>
                                </td>
                                <td style="padding:14px 18px;">
                                    <span style="background:#FEE2E2; color:#991B1B; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700;">
                                        {{ $item->total_rusak }}
                                    </span>
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
        @endif
    </div>

    {{-- ===================== MODAL DETAIL: ALAT COMMAND CENTER ===================== --}}
    <div x-show="alatModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="alatModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:640px; max-height:88vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>

            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Detail Cek Harian Alat Command Center</h3>
                <button type="button" @click="alatModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>

            <div style="padding:18px 20px; font-size:12.5px;" x-if="activeAlat">

                {{-- Identitas --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px; background:#F8FAFC; padding:14px; border-radius:10px; border:1px solid #E2E8F0;">
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Regu / Unit:</span>
                        <strong style="color:#0F172A;" x-text="activeAlat ? activeAlat.unit_nama : '-'"></strong>
                    </div>
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Tanggal Pemeriksaan:</span>
                        <strong style="color:#0F172A;" x-text="activeAlat ? formatDate(activeAlat.tanggal_pemeriksaan) : '-'"></strong>
                    </div>
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Nama Pemeriksa:</span>
                        <strong style="color:#0F172A;" x-text="activeAlat ? activeAlat.nama_pemeriksa : '-'"></strong>
                    </div>
                    <div>
                        <span style="color:#64748B; font-size:10.5px; display:block;">Jabatan:</span>
                        <strong style="color:#0F172A;" x-text="activeAlat ? activeAlat.jabatan : '-'"></strong>
                    </div>
                </div>

                {{-- Daftar Alat --}}
                <div style="margin-bottom:14px;">
                    <div style="font-size:12px; font-weight:800; color:#0F172A; margin-bottom:8px; border-bottom:1px solid #E2E8F0; padding-bottom:6px; display:flex; justify-content:space-between; align-items:center;">
                        <span>Daftar Alat Command Center</span>
                        <span style="font-size:10.5px; color:#64748B; font-weight:600;" x-text="activeAlat ? (activeAlat.alat || []).length + ' item' : '0 item'"></span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:6px; max-height:300px; overflow-y:auto; padding-right:4px;">
                        <template x-for="(alat, idx) in (activeAlat ? (activeAlat.alat || []) : [])" :key="idx">
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
                    <template x-if="activeAlat && activeAlat.catatan_umum">
                        <div style="background:#F8FAFC; border:1px solid #E2E8F0; padding:8px 10px; border-radius:8px; color:#334155; margin-bottom:8px;">
                            <span x-text="activeAlat.catatan_umum"></span>
                        </div>
                    </template>
                    <template x-if="!activeAlat || !activeAlat.catatan_umum">
                        <div style="color:#94A3B8; font-style:italic; margin-bottom:8px;">Tidak ada catatan.</div>
                    </template>
                    
                    {{-- Preview Foto Umum Alat --}}
                    <template x-if="activeAlat && activeAlat.foto_umum">
                        <div style="margin-top:10px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:10px; max-width:240px;">
                            <div style="font-size:11px; font-weight:700; color:#475569; margin-bottom:6px; display:flex; align-items:center; gap:4px;">
                                <i data-lucide="image" style="width:14px; height:14px; color:#1B2A6B;"></i> Dokumentasi Foto Umum
                            </div>
                            <a :href="'/storage/' + activeAlat.foto_umum" target="_blank" title="Klik untuk lihat ukuran penuh">
                                <img :src="'/storage/' + activeAlat.foto_umum" alt="Foto Dokumentasi Alat Command Center"
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
@endsection
