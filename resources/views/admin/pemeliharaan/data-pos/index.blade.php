@extends('layouts.admin')

@section('title', 'Data Pos Damkar & Armada Unit — Admin')

@section('content')
<div x-data="{
    createModalOpen: {{ isset($errors) && $errors->any() ? 'true' : 'false' }},
    editModalOpen: false,
    deleteModalOpen: false,
    activePos: {},
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
            <h1 style="font-size:22px; font-weight:800; color:#0F172A; margin:0;">Data Pos Sektor &amp; Armada Unit Damkar</h1>
            <p style="font-size:13px; color:#64748B; margin-top:4px; margin-bottom:0;">
                Kelola master data pos sektor pemadam kebakaran dan pemantauan distribusi unit armada operasional.
            </p>
        </div>
        <button type="button" @click="createModalOpen = true"
                style="padding:10px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(27,42,107,0.2); transition:all 0.2s;">
            <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
            <span>Tambah Pos Damkar</span>
        </button>
    </div>

    {{-- Banner Rekapitulasi Infografis --}}
    <div class="kpi-grid-container-2" style="margin-bottom:24px;">
        {{-- Card Total Pos Damkar --}}
        <div style="background:linear-gradient(135deg, #1E3A8A 0%, #1B2A6B 100%); color:#FFFFFF; padding:20px; border-radius:16px; box-shadow:0 10px 25px rgba(27,42,107,0.25); display:flex; align-items:center; justify-content:space-between; position:relative; overflow:hidden;">
            <div>
                <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:#93C5FD;">Total Pos Sektor Damkar</div>
                <div style="font-size:36px; font-weight:900; margin-top:4px; display:flex; align-items:baseline; gap:8px;">
                    <span>{{ $kpi['total_pos'] }}</span>
                    <span style="font-size:13px; font-weight:500; color:#DBEAFE;">Pos Sektor</span>
                </div>
                <div style="margin-top:10px; font-size:11.5px; font-weight:600; color:#BFDBFE;">
                    <span>🏢 Pos Komando &amp; Sektor Wilayah Operasional</span>
                </div>
            </div>
            <div style="background:rgba(255,255,255,0.12); padding:16px; border-radius:14px; backdrop-filter:blur(4px);">
                <i data-lucide="map-pin" style="width:36px; height:36px; color:#93C5FD;"></i>
            </div>
        </div>

        {{-- Card Total Unit Operasional --}}
        <div style="background:linear-gradient(135deg, #991B1B 0%, #7F1D1D 100%); color:#FFFFFF; padding:20px; border-radius:16px; box-shadow:0 10px 25px rgba(153,27,27,0.25); display:flex; align-items:center; justify-content:space-between; position:relative; overflow:hidden;">
            <div>
                <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:#FCA5A5;">Total Unit Operasional</div>
                <div style="font-size:36px; font-weight:900; margin-top:4px; display:flex; align-items:baseline; gap:8px;">
                    <span>{{ $kpi['total_unit'] }}</span>
                    <span style="font-size:13px; font-weight:500; color:#FEE2E2;">Armada</span>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px; font-size:11px; color:#FECDD3; font-weight:600;">
                    <span>🚒 Pancar: <strong>{{ $kpi['truck_pancar'] }}</strong></span>
                    <span>🛵 M-3: <strong>{{ $kpi['motor_roda3'] }}</strong></span>
                    <span>🏍 M-2: <strong>{{ $kpi['motor_roda2'] }}</strong></span>
                    <span>⚙️ Pompa: <strong>{{ $kpi['unit_pompa'] }}</strong></span>
                    <span>🚑 Rescue: <strong>{{ $kpi['unit_rescue'] }}</strong></span>
                    <span>💧 Water: <strong>{{ $kpi['water_supply'] }}</strong></span>
                </div>
            </div>
            <div style="background:rgba(255,255,255,0.12); padding:16px; border-radius:14px; backdrop-filter:blur(4px);">
                <i data-lucide="truck" style="width:36px; height:36px; color:#FCA5A5;"></i>
            </div>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="admin-filter-bar">
        <form method="GET" action="{{ route('admin.pemeliharaan.data-pos') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; width:100%;">
            <div style="font-size:13px; font-weight:700; color:#334155;">
                Daftar Pos Sektor Damkar
            </div>

            {{-- Input Pencarian --}}
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="position:relative;">
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Cari nama pos atau wilayah..."
                           style="padding:7px 14px 7px 34px; font-size:12.5px; border-radius:8px; border:1px solid #CBD5E1; outline:none; width:260px; background:#F8FAFC;">
                    <i data-lucide="search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:14px; height:14px; color:#94A3B8;"></i>
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
                @if(!empty($searchQuery))
                    <a href="{{ route('admin.pemeliharaan.data-pos') }}" style="padding:7px 12px; background:#F1F5F9; color:#475569; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Data Pos Damkar --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">
        @if($posList->isEmpty())
            <div style="padding:56px 20px; text-align:center; background:#FFFFFF; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                <div style="width:64px; height:64px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin:0 auto 16px auto; border:1px solid #E2E8F0;">
                    <i data-lucide="search-x" style="width:30px; height:30px; color:#64748B;"></i>
                </div>
                @if(!empty($searchQuery))
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Data Tidak Ditemukan</div>
                    <div style="font-size:13px; color:#64748B; margin-bottom:18px; max-width:440px; margin-left:auto; margin-right:auto;">
                        Tidak ditemukan Pos Damkar yang sesuai dengan kriteria pencarian "<strong>{{ $searchQuery }}</strong>".
                    </div>
                    <a href="{{ route('admin.pemeliharaan.data-pos') }}" style="display:inline-flex; align-items:center; gap:8px; padding:9px 20px; background:#1B2A6B; color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(27,42,107,0.2);">
                        <i data-lucide="rotate-ccw" style="width:14px; height:14px;"></i>
                        <span>Reset Filter &amp; Pencarian</span>
                    </a>
                @else
                    <div style="font-size:16px; font-weight:800; color:#0F172A; margin-bottom:6px;">Belum Ada Data Pos</div>
                    <div style="font-size:12.5px; color:#94A3B8;">Belum ada data pos sektor Damkar yang tersimpan.</div>
                @endif
            </div>
        @else
            <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                <table style="width:100%; min-width:800px; border-collapse:collapse; font-size:12.5px; text-align:center;">
                    <thead>
                        {{-- Group Header --}}
                        <tr style="color:#FFFFFF; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; font-weight:800;">
                            <th rowspan="2" style="background:#0F172A; padding:12px; border:1px solid #1E293B; width:220px; text-align:left;">POS DAMKAR</th>
                            <th colspan="7" style="background:#991B1B; padding:10px; border:1px solid #B91C1C;">JUMLAH UNIT OPERASIONAL</th>
                            <th rowspan="2" style="background:#0F172A; padding:12px; border:1px solid #1E293B; width:100px;">TOTAL UNIT</th>
                            <th rowspan="2" style="background:#0F172A; padding:12px; border:1px solid #1E293B; width:100px;">AKSI</th>
                        </tr>
                        {{-- Sub Header --}}
                        <tr style="background:#F8FAFC; color:#334155; font-size:10px; font-weight:800; text-transform:uppercase;">
                            <th style="padding:8px; border:1px solid #E2E8F0; background:#FEF2F2; color:#991B1B;">TRUCK PANCAR</th>
                            <th style="padding:8px; border:1px solid #E2E8F0; background:#FEF2F2; color:#991B1B;">MOTOR R-3</th>
                            <th style="padding:8px; border:1px solid #E2E8F0; background:#FEF2F2; color:#991B1B;">MOTOR R-2</th>
                            <th style="padding:8px; border:1px solid #E2E8F0; background:#FEF2F2; color:#991B1B;">UNIT POMPA</th>
                            <th style="padding:8px; border:1px solid #E2E8F0; background:#FEF2F2; color:#991B1B;">RESCUE</th>
                            <th style="padding:8px; border:1px solid #E2E8F0; background:#FEF2F2; color:#991B1B;">WATER SUPPLY</th>
                            <th style="padding:8px; border:1px solid #E2E8F0; background:#FEF2F2; color:#991B1B;">LAINNYA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posList as $item)
                            <tr style="border-bottom:1px solid #E2E8F0; transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <td style="padding:12px 14px; text-align:left; font-weight:700; color:#0F172A; border-right:1px solid #E2E8F0;">
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <i data-lucide="map-pin" style="width:14px; height:14px; color:#DC2626; flex-shrink:0;"></i>
                                        <span>{{ $item->nama }}</span>
                                    </div>
                                    @if($item->wilayah)
                                        <div style="font-size:11px; color:#64748B; font-weight:500; margin-left:20px; margin-top:2px;">
                                            Wilayah: {{ $item->wilayah }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Unit --}}
                                <td style="padding:10px; font-weight:700; color:#1E293B; border-right:1px solid #F1F5F9;">{{ $item->unit_truck_pancar ?: '-' }}</td>
                                <td style="padding:10px; font-weight:700; color:#1E293B; border-right:1px solid #F1F5F9;">{{ $item->unit_motor_roda3 ?: '-' }}</td>
                                <td style="padding:10px; font-weight:700; color:#1E293B; border-right:1px solid #F1F5F9;">{{ $item->unit_motor_roda2 ?: '-' }}</td>
                                <td style="padding:10px; font-weight:700; color:#1E293B; border-right:1px solid #F1F5F9;">{{ $item->unit_pompa ?: '-' }}</td>
                                <td style="padding:10px; font-weight:700; color:#1E293B; border-right:1px solid #F1F5F9;">{{ $item->unit_rescue ?: '-' }}</td>
                                <td style="padding:10px; font-weight:700; color:#1E293B; border-right:1px solid #F1F5F9;">{{ $item->unit_water_supply ?: '-' }}</td>
                                <td style="padding:10px; font-weight:700; color:#1E293B; border-right:1px solid #E2E8F0; position:relative;" x-data="{ popoverOpen: false }">
                                    @if($item->unit_lainnya > 0)
                                        <button type="button" @click="popoverOpen = !popoverOpen" @click.outside="popoverOpen = false"
                                                style="background:#EEF2FF; color:#1B2A6B; border:1px solid #C7D2FE; border-radius:12px; padding:3px 9px; font-size:12px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; outline:none; transition:all 0.15s;"
                                                onmouseover="this.style.background='#1B2A6B'; this.style.color='#FFFFFF';"
                                                onmouseout="this.style.background='#EEF2FF'; this.style.color='#1B2A6B';"
                                                title="Klik untuk melihat rincian armada di kategori Lainnya">
                                            <span>{{ $item->unit_lainnya }}</span>
                                            <i data-lucide="more-horizontal" style="width:13px; height:13px;"></i>
                                        </button>

                                        {{-- Popover Card Detail Armada Lainnya --}}
                                        <div x-show="popoverOpen" x-cloak
                                             style="position:absolute; right:100%; top:50%; transform:translateY(-50%); width:270px; background:#FFFFFF; border:1px solid #CBD5E1; border-radius:14px; box-shadow:0 12px 30px rgba(15,23,42,0.22); z-index:1000; padding:14px; text-align:left; margin-right:10px;">
                                            <div style="font-size:12px; font-weight:800; color:#0F172A; margin-bottom:10px; border-bottom:1px solid #E2E8F0; padding-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
                                                <span>Armada Lainnya ({{ $item->nama }})</span>
                                                <span style="font-size:10px; background:#1B2A6B; color:#FFFFFF; padding:2px 7px; border-radius:10px; font-weight:800;">{{ $item->unit_lainnya }} Unit</span>
                                            </div>
                                            <div style="display:flex; flex-direction:column; gap:6px; max-height:160px; overflow-y:auto; margin-bottom:12px;" class="custom-scrollbar">
                                                @foreach($item->unit_lainnya_list as $uLain)
                                                    <div style="font-size:11px; background:#F8FAFC; padding:7px 10px; border-radius:8px; border:1px solid #E2E8F0;">
                                                        <div style="font-weight:800; color:#1E3A8A; display:flex; align-items:center; justify-content:space-between;">
                                                            <span>🚗 {{ $uLain->nomor_lambung }}</span>
                                                            <span style="color:#64748B; font-weight:600; font-size:10px;">[{{ $uLain->plat_nomor }}]</span>
                                                        </div>
                                                        <div style="color:#475569; font-size:10.5px; margin-top:2px; font-weight:600;">
                                                            {{ $uLain->nama }} · <span style="color:#D97706; font-weight:800;">{{ $uLain->jenis_kendaraan ?: 'Lainnya' }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <a href="{{ route('admin.pemeliharaan.data-unit', ['search' => $item->nama]) }}"
                                               style="display:flex; align-items:center; justify-content:center; gap:6px; padding:7px 12px; background:#1B2A6B; color:#FFFFFF; border-radius:8px; font-size:11px; font-weight:700; text-decoration:none; box-shadow:0 3px 8px rgba(27,42,107,0.25);">
                                                <i data-lucide="external-link" style="width:12px; height:12px;"></i>
                                                <span>Kelola di Data Unit ➔</span>
                                            </a>
                                        </div>
                                    @else
                                        <span style="color:#94A3B8;">-</span>
                                    @endif
                                </td>

                                {{-- Total Unit --}}
                                <td style="padding:10px; font-weight:900; color:#991B1B; background:#FEF2F2; border-right:1px solid #E2E8F0;">
                                    {{ $item->total_unit }}
                                </td>

                                {{-- Aksi --}}
                                <td style="padding:10px; text-align:center; white-space:nowrap;">
                                    <div style="display:inline-flex; align-items:center; gap:4px;">
                                        @php
                                            $posJson = json_encode([
                                                'id'       => $item->id,
                                                'nama'     => $item->nama,
                                                'kode_pos' => $item->kode_pos,
                                                'wilayah'  => $item->wilayah,
                                                'alamat'   => $item->alamat,
                                                'telepon'  => $item->telepon,
                                                'catatan'  => $item->catatan,
                                            ]);
                                        @endphp
                                        <button type="button" @click="
                                            activePos = {{ $posJson }};
                                            editUrl = '{{ route('admin.pemeliharaan.data-pos.update', $item->id) }}';
                                            editModalOpen = true;
                                        " style="padding:4px 9px; background:#F1F5F9; color:#334155; border:1px solid #CBD5E1; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">
                                            Edit
                                        </button>
                                        <button type="button" @click="
                                            activePos = {{ $posJson }};
                                            deleteUrl = '{{ route('admin.pemeliharaan.data-pos.destroy', $item->id) }}';
                                            deleteModalOpen = true;
                                        " style="padding:4px 9px; background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        {{-- Baris Rekapitulasi JUMLAH --}}
                        <tr style="background:#0F172A; color:#FFFFFF; font-weight:900; font-size:13px; text-transform:uppercase;">
                            <td style="padding:14px; text-align:left; border:1px solid #1E293B;">JUMLAH TOTAL</td>

                            {{-- Total Unit --}}
                            <td style="padding:12px; background:#991B1B; border:1px solid #B91C1C;">{{ $kpi['truck_pancar'] }}</td>
                            <td style="padding:12px; background:#991B1B; border:1px solid #B91C1C;">{{ $kpi['motor_roda3'] }}</td>
                            <td style="padding:12px; background:#991B1B; border:1px solid #B91C1C;">{{ $kpi['motor_roda2'] }}</td>
                            <td style="padding:12px; background:#991B1B; border:1px solid #B91C1C;">{{ $kpi['unit_pompa'] }}</td>
                            <td style="padding:12px; background:#991B1B; border:1px solid #B91C1C;">{{ $kpi['unit_rescue'] }}</td>
                            <td style="padding:12px; background:#991B1B; border:1px solid #B91C1C;">{{ $kpi['water_supply'] }}</td>
                            <td style="padding:12px; background:#991B1B; border:1px solid #B91C1C;">{{ $kpi['unit_lainnya'] }}</td>

                            {{-- Grand Total Unit --}}
                            <td style="padding:14px; background:#DC2626; border:1px solid #EF4444; font-size:15px;">{{ $kpi['total_unit'] }}</td>
                            <td style="background:#0F172A; border:1px solid #1E293B;">—</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style="padding:16px 20px; border-top:1px solid #F1F5F9; background:#FAFCFE;">
                {{ $posList->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL: TAMBAH POS ===================== --}}
    <div x-show="createModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Tambah Pos Sektor Damkar</h3>
                <button type="button" @click="createModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            <form action="{{ route('admin.pemeliharaan.data-pos.store') }}" method="POST" style="padding:20px;">
                @csrf
                @if(isset($errors) && $errors->any())
                    <div style="background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; padding:12px 16px; border-radius:12px; margin-bottom:18px; font-size:12.5px;">
                        <div style="display:flex; align-items:center; gap:6px; font-weight:800; color:#DC2626; margin-bottom:4px;">
                            <i data-lucide="alert-triangle" style="width:16px; height:16px;"></i>
                            <span>Gagal Menyimpan Data Pos:</span>
                        </div>
                        <ul style="margin:0; padding-left:20px; font-size:12px; font-weight:600;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="modal-form-grid" style="margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Pos Sektor <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nama" required value="{{ old('nama') }}" placeholder="Contoh: Soreang (MAKO) / Baleendah"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:{{ isset($errors) && $errors->has('nama') ? '1.5px solid #DC2626' : '1px solid #CBD5E1' }}; outline:none;">
                        @error('nama')
                            <span style="font-size:11px; color:#DC2626; font-weight:600; margin-top:3px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kode Pos</label>
                        <input type="text" name="kode_pos" value="{{ old('kode_pos') }}" placeholder="Contoh: POS-BLD"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:{{ isset($errors) && $errors->has('kode_pos') ? '1.5px solid #DC2626' : '1px solid #CBD5E1' }}; outline:none;">
                        @error('kode_pos')
                            <span style="font-size:11px; color:#DC2626; font-weight:600; margin-top:3px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-form-grid" style="margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Wilayah / Sektor</label>
                        <input type="text" name="wilayah" value="{{ old('wilayah') }}" placeholder="Contoh: Wilayah Selatan"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:{{ isset($errors) && $errors->has('wilayah') ? '1.5px solid #DC2626' : '1px solid #CBD5E1' }}; outline:none;">
                        @error('wilayah')
                            <span style="font-size:11px; color:#DC2626; font-weight:600; margin-top:3px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. Telepon / Kontak Pos</label>
                        <input type="text" name="telepon" value="{{ old('telepon') }}" placeholder="Contoh: (022) 5891113"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Alamat Kantor Pos</label>
                    <textarea name="alamat" rows="2" placeholder="Alamat lengkap pos pemadam kebakaran..."
                              style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; box-sizing:border-box;">{{ old('alamat') }}</textarea>
                </div>

                {{-- Section Jumlah Unit Operasional (Otomatis dari Data Unit) --}}
                <div style="background:#EFF6FF; padding:12px 14px; border-radius:10px; border:1px solid #BFDBFE; margin-bottom:16px;">
                    <div style="font-size:11.5px; font-weight:800; color:#1E3A8A; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="info" style="width:15px; height:15px; color:#2563EB;"></i>
                        <span>Distribusi Unit Armada Terhitung Otomatis</span>
                    </div>
                    <p style="font-size:11.5px; color:#1E40AF; margin:0; line-height:1.4;">
                        Jumlah armada unit operasional (Truck Pancar, Motor, Pompa, Rescue, Water Supply, &amp; Lainnya) dihitung <strong>secara otomatis dari Data Unit</strong> berdasarkan penempatan pos ini.
                    </p>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid #E2E8F0; padding-top:14px;">
                    <button type="button" @click="createModalOpen = false"
                            style="padding:8px 16px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit"
                            style="padding:8px 18px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer;">
                        Simpan Pos
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL: EDIT POS ===================== --}}
    <div x-show="editModalOpen" x-cloak class="admin-modal-overlay"
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);">
        <div class="custom-scrollbar admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:540px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; sticky; top:0; background:#FFFFFF; z-index:10;">
                <h3 style="font-size:15.5px; font-weight:800; color:#0F172A; margin:0;">Edit Data Pos Damkar</h3>
                <button type="button" @click="editModalOpen = false" style="background:none; border:none; color:#94A3B8; cursor:pointer; padding:4px;">
                    <i data-lucide="x" style="width:18px; height:18px;"></i>
                </button>
            </div>
            <form :action="editUrl" method="POST" style="padding:20px;">
                @csrf
                @method('PUT')
                <div class="modal-form-grid" style="margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Nama Pos Sektor <span style="color:#DC2626;">*</span></label>
                        <input type="text" name="nama" required x-model="activePos.nama"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Kode Pos</label>
                        <input type="text" name="kode_pos" x-model="activePos.kode_pos"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                </div>

                <div class="modal-form-grid" style="margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Wilayah / Sektor</label>
                        <input type="text" name="wilayah" x-model="activePos.wilayah"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">No. Telepon / Kontak Pos</label>
                        <input type="text" name="telepon" x-model="activePos.telepon"
                               style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none;">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">Alamat Kantor Pos</label>
                    <textarea name="alamat" rows="2" x-model="activePos.alamat" placeholder="Alamat lengkap pos pemadam kebakaran..."
                              style="width:100%; padding:8px 12px; font-size:13px; border-radius:8px; border:1px solid #CBD5E1; outline:none; box-sizing:border-box;"></textarea>
                </div>

                {{-- Section Jumlah Unit Operasional (Otomatis dari Data Unit) --}}
                <div style="background:#EFF6FF; padding:12px 14px; border-radius:10px; border:1px solid #BFDBFE; margin-bottom:16px;">
                    <div style="font-size:11.5px; font-weight:800; color:#1E3A8A; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                        <i data-lucide="info" style="width:15px; height:15px; color:#2563EB;"></i>
                        <span>Distribusi Unit Armada Terhitung Otomatis</span>
                    </div>
                    <p style="font-size:11.5px; color:#1E40AF; margin:0; line-height:1.4;">
                        Jumlah armada unit operasional (Truck Pancar, Motor, Pompa, Rescue, Water Supply, &amp; Lainnya) dihitung <strong>secara otomatis dari Data Unit</strong> berdasarkan penempatan pos ini.
                    </p>
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
         style="position:fixed; top:0; left:0; width:100vw; height:100vh; z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px; background-color:rgba(15,23,42,0.65);">
        <div class="admin-modal-dialog" style="background:#FFFFFF; border-radius:16px; width:100%; max-width:420px; padding:24px; text-align:center; box-shadow:0 20px 40px -10px rgba(0,0,0,0.25); border:1px solid #E2E8F0; margin:auto;" @click.stop>
            <div style="width:48px; height:48px; border-radius:50%; background:#FEE2E2; color:#DC2626; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                <i data-lucide="trash-2" style="width:24px; height:24px;"></i>
            </div>
            <h3 style="font-size:16px; font-weight:800; color:#0F172A; margin:0 0 6px;">Hapus Data Pos Damkar?</h3>
            <p style="font-size:13px; color:#64748B; margin-bottom:20px;">
                Apakah Anda yakin ingin menghapus pos <strong style="color:#0F172A;" x-text="activePos.nama"></strong>? Tindakan ini tidak dapat dibatalkan.
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
