@extends('layouts.admin')
@section('title', 'Dashboard Admin')

@section('content')

<div class="form-card" style="max-width:100%; box-shadow:none; padding:0; background:transparent;">

    {{-- Page Header --}}
    <div style="display:flex; align-items:baseline; gap:12px; margin-bottom:22px; flex-wrap:wrap;">
        <h1 style="font-size:20px; font-weight:700; color:#111;">Dashboard</h1>
        <span style="font-size:13px; color:#757575;">Selamat datang, Admin!</span>
    </div>

    {{-- Stat Cards --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:20px; margin-bottom:24px;">

        {{-- Total Unit --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 18px 40px rgba(112, 144, 176, 0.10); padding:22px 24px; border:1px solid #EAEFF8; border-top:4px solid #1B2A6B;">
            <div style="font-size:11px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:#718096; margin-bottom:6px;">Total Unit</div>
            <div style="font-size:36px; font-weight:700; line-height:1; color:#1A202C;">{{ $totalUnit ?? 12 }}</div>
            <a href="#" style="font-size:12px; color:#1B2A6B; font-weight:600; text-decoration:none; display:inline-block; margin-top:10px;">Lihat detail →</a>
        </div>

        {{-- Pemeliharaan --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 18px 40px rgba(112, 144, 176, 0.10); padding:22px 24px; border:1px solid #EAEFF8; border-top:4px solid #C0201F;">
            <div style="font-size:11px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:#718096; margin-bottom:6px;">Pemeliharaan</div>
            <div style="font-size:36px; font-weight:700; line-height:1; color:#1A202C;">{{ $totalPemeliharaan ?? 5 }}</div>
            <a href="{{ route('admin.pemeliharaan.pemeliharaan') }}" style="font-size:12px; color:#C0201F; font-weight:600; text-decoration:none; display:inline-block; margin-top:10px;">Lihat detail →</a>
        </div>

        {{-- Pemeriksaan --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 18px 40px rgba(112, 144, 176, 0.10); padding:22px 24px; border:1px solid #EAEFF8; border-top:4px solid #718096;">
            <div style="font-size:11px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:#718096; margin-bottom:6px;">Pemeriksaan</div>
            <div style="font-size:36px; font-weight:700; line-height:1; color:#1A202C;">{{ $totalPemeriksaan ?? 3 }}</div>
            <a href="{{ route('admin.pemeliharaan.pemeriksaan') }}" style="font-size:12px; color:#4A5568; font-weight:600; text-decoration:none; display:inline-block; margin-top:10px;">Lihat detail →</a>
        </div>

    </div>

    {{-- Bottom: Chart + Activity --}}
    <div class="dash-bottom-grid" style="display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start;">

        {{-- Grafik Pemeliharaan --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 18px 40px rgba(112, 144, 176, 0.10); border:1px solid #EAEFF8; overflow:hidden;">
            <div style="padding:18px 24px 14px; border-bottom:1px solid #EDF2F7; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:14px; font-weight:700; color:#1A202C;">Grafik Pemeliharaan Kendaraan</span>
                <span style="font-size:12px; color:#A0AEC0; font-weight:500;">{{ now()->format('Y') }}</span>
            </div>
            <div style="padding:24px;">
                {{-- Bar chart sederhana --}}
                <div style="display:flex; align-items:flex-end; gap:8px; height:200px;
                            background: repeating-linear-gradient(0deg,transparent,transparent 19px,#EDF2F7 19px,#EDF2F7 20px);
                            border-radius:8px; padding:0 12px;">
                    <div style="flex:1; height:55%; background:#1B2A6B; border-radius:6px 6px 0 0; opacity:.85;"></div>
                    <div style="flex:1; height:75%; background:#C0201F; border-radius:6px 6px 0 0; opacity:.85;"></div>
                    <div style="flex:1; height:40%; background:#1B2A6B; border-radius:6px 6px 0 0; opacity:.85;"></div>
                    <div style="flex:1; height:88%; background:#C0201F; border-radius:6px 6px 0 0; opacity:.85;"></div>
                    <div style="flex:1; height:62%; background:#1B2A6B; border-radius:6px 6px 0 0; opacity:.85;"></div>
                    <div style="flex:1; height:32%; background:#718096; border-radius:6px 6px 0 0; opacity:.85;"></div>
                    <div style="flex:1; height:70%; background:#C0201F; border-radius:6px 6px 0 0; opacity:.85;"></div>
                    <div style="flex:1; height:90%; background:#C0201F; border-radius:6px 6px 0 0; opacity:.85;"></div>
                </div>
                <div style="display:flex; gap:8px; margin-top:8px; padding:0 8px;">
                    @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags'] as $bulan)
                    <span style="flex:1; text-align:center; font-size:11px; color:#A0AEC0; font-weight:500;">{{ $bulan }}</span>
                    @endforeach
                </div>
            </div>
            <div style="display:flex; gap:16px; padding:12px 24px; border-top:1px solid #EDF2F7; flex-wrap:wrap; background:#FAFCFE;">
                <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:#4A5568;">
                    <span style="width:10px;height:10px;border-radius:3px;background:#1B2A6B;display:inline-block;"></span> Unit Pemadam
                </div>
                <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:#4A5568;">
                    <span style="width:10px;height:10px;border-radius:3px;background:#C0201F;display:inline-block;"></span> Unit Rescue
                </div>
                <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:#4A5568;">
                    <span style="width:10px;height:10px;border-radius:3px;background:#718096;display:inline-block;"></span> Command Center
                </div>
            </div>
        </div>

        {{-- Aktivitas Terbaru --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 18px 40px rgba(112, 144, 176, 0.10); border:1px solid #EAEFF8; overflow:hidden;">
            <div style="padding:18px 24px 14px; border-bottom:1px solid #EDF2F7;">
                <span style="font-size:14px; font-weight:700; color:#1A202C;">Aktivitas Terbaru</span>
            </div>
            <div style="padding:0 20px;">
                @php
                $activities = [
                    ['icon'=>'wrench',          'color'=>'#C0201F', 'bg'=>'rgba(192,32,31,.10)', 'text'=>'Pengajuan pemeliharaan Unit Damkar-01', 'time'=>'2 menit lalu'],
                    ['icon'=>'truck',           'color'=>'#1B2A6B', 'bg'=>'rgba(27,42,107,.10)',  'text'=>'Cek harian Unit Pemadam selesai',        'time'=>'30 menit lalu'],
                    ['icon'=>'clipboard-check', 'color'=>'#757575', 'bg'=>'rgba(117,117,117,.10)','text'=>'Pemeriksaan APAR Pos-3 selesai',         'time'=>'1 jam lalu'],
                    ['icon'=>'radio-tower',     'color'=>'#1B2A6B', 'bg'=>'rgba(27,42,107,.10)',  'text'=>'Cek alat Command Center diperbarui',     'time'=>'3 jam lalu'],
                    ['icon'=>'life-buoy',       'color'=>'#C0201F', 'bg'=>'rgba(192,32,31,.10)', 'text'=>'Riwayat Unit Rescue baru ditambahkan',   'time'=>'Kemarin'],
                ];
                @endphp
                @foreach($activities as $act)
                <div style="display:flex; align-items:flex-start; gap:12px; padding:11px 0; border-bottom:1px solid #D9D9D9;">
                    <div style="width:30px;height:30px;border-radius:50%;background:{{ $act['bg'] }};color:{{ $act['color'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                        <i data-lucide="{{ $act['icon'] }}" style="width:14px;height:14px;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $act['text'] }}</div>
                        <div style="font-size:10.5px;color:#757575;margin-top:2px;">{{ $act['time'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>

@push('styles')
<style>
@media (max-width: 900px) {
    /* Stack chart dan activity di layar HP/tablet */
    .dash-bottom-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush

@endsection
