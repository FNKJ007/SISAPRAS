@extends('layouts.admin')
@section('title', 'Monitoring Aktual — Admin')

@section('content')
<div x-data="{
    updateModalOpen: false,
    activeItem: {},
    updateUrl: ''
}">

    {{-- Flash Message --}}
    @if(session('success'))
        <div style="background:#D1FAE5; color:#065F46; border:1px solid #A7F3D0; padding:12px 16px; border-radius:12px; margin-bottom:20px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px;">
            <i data-lucide="check-circle-2" style="width:18px; height:18px; color:#059669;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div style="margin-bottom:20px;">
        <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Monitoring Aktual Pemeliharaan</h1>
        <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
            Pantau progres pengerjaan fisik unit yang sedang diperbaiki di bengkel — terpisah dari status pembayaran/invoice.
        </p>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid-container" style="margin-bottom:24px;">
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#64748B; font-size:11.5px; font-weight:700; text-transform:uppercase;">Total Unit Dalam Pipeline</div>
            <div style="font-size:24px; font-weight:800; color:#0F172A; margin-top:6px;">{{ $kpi['total'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#94A3B8; font-size:11.5px; font-weight:700; text-transform:uppercase;">Belum Mulai</div>
            <div style="font-size:24px; font-weight:800; color:#475569; margin-top:6px;">{{ $kpi['belum_mulai'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#D97706; font-size:11.5px; font-weight:700; text-transform:uppercase;">Dalam Pengerjaan</div>
            <div style="font-size:24px; font-weight:800; color:#D97706; margin-top:6px;">{{ $kpi['proses'] }}</div>
        </div>
        <div style="background:#FFFFFF; padding:18px; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
            <div style="color:#059669; font-size:11.5px; font-weight:700; text-transform:uppercase;">Selesai</div>
            <div style="font-size:24px; font-weight:800; color:#059669; margin-top:6px;">{{ $kpi['selesai'] }}</div>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="admin-filter-bar">
        <form method="GET" action="{{ route('admin.pemeliharaan.monitoring-aktual') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px;">
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="font-size:12px; font-weight:600; color:#64748B;">Status Pengerjaan:</span>
                <select name="status_pengerjaan" onchange="this.form.submit()" style="padding:6px 12px; font-size:12px; border-radius:8px; border:1px solid #CBD5E1; outline:none; background:#F8FAFC; color:#1E293B; font-weight:600;">
                    <option value="semua" @selected($statusFilter === 'semua')>Semua Status</option>
                    <option value="belum_mulai" @selected($statusFilter === 'belum_mulai')>Belum Mulai</option>
                    <option value="proses" @selected($statusFilter === 'proses')>Dalam Pengerjaan</option>
                    <option value="selesai" @selected($statusFilter === 'selesai')>Selesai</option>
                </select>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="position:relative;">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari lambung, pos, pemegang..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:230px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden; margin-top:16px;">
        @if($records->isEmpty())
            <div style="padding:56px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
                    <i data-lucide="wrench" style="width:30px; height:30px; color:#64748B;"></i>
                </div>
                <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Unit Dalam Pipeline</div>
                <div style="font-size:13px; color:#64748B; max-width:400px; margin:0 auto;">Unit akan muncul di sini setelah pengajuan pemeliharaannya disetujui.</div>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; min-width:1000px; border-collapse:collapse; font-size:12.5px; text-align:left;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:1.5px solid #E2E8F0; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; font-weight:800;">
                            <th style="padding:12px 14px;">Unit / Lambung</th>
                            <th style="padding:12px 14px;">Pos</th>
                            <th style="padding:12px 14px;">Item Perbaikan</th>
                            <th style="padding:12px 14px;">Mulai — Selesai</th>
                            <th style="padding:12px 14px; width:180px;">Progres</th>
                            <th style="padding:12px 14px; text-align:center;">Status</th>
                            <th style="padding:12px 14px; text-align:center; width:100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $rec)
                            @php
                                $badgeColor = match($rec->status_pengerjaan) {
                                    'proses'  => ['#FFFBEB', '#B45309', '#FDE68A'],
                                    'selesai' => ['#ECFDF5', '#047857', '#A7F3D0'],
                                    default   => ['#F8FAFC', '#64748B', '#E2E8F0'],
                                };
                            @endphp
                            <tr style="border-bottom:1px solid #F1F5F9;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:12px 14px;">
                                    <div style="font-weight:800; color:#1E3A8A;">{{ $rec->nomor_lambung }}</div>
                                    <div style="font-size:11px; color:#64748B;">{{ $rec->nama_pemegang }}</div>
                                </td>
                                <td style="padding:12px 14px; font-weight:600; color:#334155;">{{ $rec->pos }}</td>
                                <td style="padding:12px 14px; color:#475569; max-width:220px;">
                                    {{ \Illuminate\Support\Str::limit($rec->item_perbaikan, 60) }}
                                </td>
                                <td style="padding:12px 14px; font-size:11.5px; color:#475569;">
                                    <div>Mulai: <strong>{{ ($rec->tanggal_mulai_pengerjaan ?? $rec->tanggal_keberangkatan)?->format('d/m/Y') ?? '—' }}</strong></div>
                                    <div>Selesai: <strong>{{ $rec->tanggal_selesai_pengerjaan?->format('d/m/Y') ?? '—' }}</strong></div>
                                </td>
                                <td style="padding:12px 14px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="flex:1; height:8px; background:#F1F5F9; border-radius:6px; overflow:hidden;">
                                            <div style="height:100%; width:{{ $rec->progress_persen }}%; background:{{ $badgeColor[1] }}; border-radius:6px;"></div>
                                        </div>
                                        <span style="font-size:11px; font-weight:700; color:#334155;">{{ $rec->progress_persen }}%</span>
                                    </div>
                                    @if($rec->progress_catatan)
                                        <div style="font-size:10.5px; color:#94A3B8; margin-top:4px;">{{ \Illuminate\Support\Str::limit($rec->progress_catatan, 40) }}</div>
                                    @endif
                                </td>
                                <td style="padding:12px 14px; text-align:center;">
                                    <span style="background:{{ $badgeColor[0] }}; color:{{ $badgeColor[1] }}; border:1px solid {{ $badgeColor[2] }}; padding:3px 10px; border-radius:6px; font-size:11px; font-weight:700; white-space:nowrap;">
                                        {{ \App\Models\Pengajuan::$statusPengerjaanMap[$rec->status_pengerjaan] ?? $rec->status_pengerjaan }}
                                    </span>
                                </td>
                                <td style="padding:12px 14px; text-align:center;">
                                    <button type="button" @click="
                                        activeItem = {{ json_encode($rec->only(['id','nomor_lambung','status_pengerjaan','progress_persen','progress_catatan']) + [
                                            'tanggal_mulai_pengerjaan' => $rec->tanggal_mulai_pengerjaan?->format('Y-m-d'),
                                            'tanggal_selesai_pengerjaan' => $rec->tanggal_selesai_pengerjaan?->format('Y-m-d'),
                                        ]) }};
                                        updateUrl = '{{ route('admin.pemeliharaan.monitoring-aktual.progres', $rec->id) }}';
                                        updateModalOpen = true;
                                    " style="padding:5px 10px; background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:6px; font-size:11.5px; font-weight:700; cursor:pointer;">
                                        Update
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 20px; border-top:1px solid #F1F5F9; background:#FAFCFE;">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL: UPDATE PROGRES ===================== --}}
    <div x-show="updateModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);"
         @click.self="updateModalOpen = false">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:480px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Update Progres Pengerjaan — <span x-text="activeItem.nomor_lambung"></span></h3>
                <button type="button" @click="updateModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            <form :action="updateUrl" method="POST" style="padding:20px;">
                @csrf
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Status Pengerjaan <span style="color:#DC2626;">*</span></label>
                    <select name="status_pengerjaan" x-model="activeItem.status_pengerjaan" required
                            style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                        <option value="belum_mulai">Belum Mulai</option>
                        <option value="proses">Dalam Pengerjaan</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai_pengerjaan" x-model="activeItem.tanggal_mulai_pengerjaan"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai_pengerjaan" x-model="activeItem.tanggal_selesai_pengerjaan"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">
                        Progres: <span x-text="activeItem.progress_persen"></span>%
                    </label>
                    <input type="range" name="progress_persen" x-model="activeItem.progress_persen" min="0" max="100" step="5"
                           style="width:100%;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Catatan Progres</label>
                    <textarea name="progress_catatan" x-model="activeItem.progress_catatan" rows="3" placeholder="Contoh: menunggu spare part filter oli..."
                              style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; resize:vertical;"></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" @click="updateModalOpen = false" style="padding:9px 16px; background:#F1F5F9; color:#334155; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">Batal</button>
                    <button type="submit" style="padding:9px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">Simpan Progres</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
