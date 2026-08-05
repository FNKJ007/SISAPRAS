@extends('layouts.admin')
@section('title', 'Dashboard Admin')

@section('content')

<div class="form-card" style="max-width:100%; box-shadow:none; padding:0; background:transparent;">

    {{-- Page Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#121E4E; margin-bottom:2px;">Dashboard Analitik</h1>
            <p style="font-size:13px; color:#64748B; margin:0;">Ringkasan status sarana prasarana &amp; aktivitas pemeliharaan.</p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; background:#FFFFFF; padding:6px 14px; border-radius:10px; border:1px solid #E2E8F0; box-shadow:0 2px 6px rgba(0,0,0,0.04);">
            <i data-lucide="calendar" style="width:16px; height:16px; color:#C0201F;"></i>
            <span style="font-size:12.5px; font-weight:600; color:#1E293B;">{{ date('d F Y') }}</span>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:24px;">

        {{-- Total Unit --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 18px 40px rgba(112, 144, 176, 0.10); padding:22px 24px; border:1px solid #EAEFF8; position:relative; overflow:hidden;">
            <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, #1B2A6B, #3B82F6);"></div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:11.5px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:#64748B;">Total Unit</div>
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(27, 42, 107, 0.1); color:#1B2A6B; display:flex; align-items:center; justify-content:center;">
                    <i data-lucide="truck" style="width:18px; height:18px;"></i>
                </div>
            </div>
            <div style="font-size:36px; font-weight:800; line-height:1.1; color:#0F172A; margin:12px 0 6px 0;">{{ $totalUnit ?? 12 }}</div>
            <a href="#" style="font-size:12.5px; color:#1B2A6B; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                Lihat detail <i data-lucide="arrow-right" style="width:14px; height:14px;"></i>
            </a>
        </div>

        {{-- Pemeliharaan --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 18px 40px rgba(112, 144, 176, 0.10); padding:22px 24px; border:1px solid #EAEFF8; position:relative; overflow:hidden;">
            <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, #C0201F, #EF4444);"></div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:11.5px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:#64748B;">Pemeliharaan</div>
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(192, 32, 31, 0.1); color:#C0201F; display:flex; align-items:center; justify-content:center;">
                    <i data-lucide="wrench" style="width:18px; height:18px;"></i>
                </div>
            </div>
            <div style="font-size:36px; font-weight:800; line-height:1.1; color:#0F172A; margin:12px 0 6px 0;">{{ $totalPemeliharaan ?? 5 }}</div>
            <a href="{{ route('admin.pemeliharaan.pemeliharaan') }}" style="font-size:12.5px; color:#C0201F; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                Lihat detail <i data-lucide="arrow-right" style="width:14px; height:14px;"></i>
            </a>
        </div>

        {{-- Pemeriksaan --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 18px 40px rgba(112, 144, 176, 0.10); padding:22px 24px; border:1px solid #EAEFF8; position:relative; overflow:hidden;">
            <div style="position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg, #D97706, #F59E0B);"></div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:11.5px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:#64748B;">Pemeriksaan</div>
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(217, 119, 6, 0.1); color:#D97706; display:flex; align-items:center; justify-content:center;">
                    <i data-lucide="clipboard-check" style="width:18px; height:18px;"></i>
                </div>
            </div>
            <div style="font-size:36px; font-weight:800; line-height:1.1; color:#0F172A; margin:12px 0 6px 0;">{{ $totalPemeriksaan ?? 3 }}</div>
            <a href="{{ route('admin.pemeliharaan.pemeriksaan') }}" style="font-size:12.5px; color:#D97706; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                Lihat detail <i data-lucide="arrow-right" style="width:14px; height:14px;"></i>
            </a>
        </div>

    </div>

    {{-- Bottom: Modern Interactive Chart + Activity --}}
    <div class="dash-bottom-grid" style="display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start;">

        {{-- Grafik Pemeliharaan Interactive Chart --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 18px 40px rgba(112, 144, 176, 0.10); border:1px solid #EAEFF8; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <span style="font-size:15px; font-weight:700; color:#0F172A; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="bar-chart-3" style="width:18px; height:18px; color:#C0201F;"></i>
                        Grafik Pemeliharaan Kendaraan &amp; Alat
                    </span>
                    <span style="font-size:12px; color:#64748B; margin-top:2px; display:block;">Tren riwayat pemeriksaan dan pengajuan per bulan.</span>
                </div>
                <select style="padding:6px 12px; font-size:12px; font-weight:600; border-radius:8px; border:1px solid #CBD5E1; background:#F8FAFC; color:#334155; outline:none; cursor:pointer;">
                    <option value="2026">Tahun {{ date('Y') }}</option>
                    <option value="2025">Tahun 2025</option>
                </select>
            </div>

            {{-- Canvas Chart.js --}}
            <div style="padding:20px 24px; position:relative; min-height:260px;">
                <canvas id="chartPemeliharaan" style="max-height:260px; width:100%;"></canvas>
            </div>

            {{-- Legend Footer --}}
            <div style="display:flex; gap:20px; padding:14px 24px; border-top:1px solid #F1F5F9; flex-wrap:wrap; background:#FAFCFE; justify-content:center;">
                <div style="display:flex; align-items:center; gap:8px; font-size:12.5px; font-weight:600; color:#334155;">
                    <span style="width:12px; height:12px; border-radius:3px; background:#1B2A6B; display:inline-block;"></span> Unit Pemadam
                </div>
                <div style="display:flex; align-items:center; gap:8px; font-size:12.5px; font-weight:600; color:#334155;">
                    <span style="width:12px; height:12px; border-radius:3px; background:#C0201F; display:inline-block;"></span> Unit Rescue
                </div>
                <div style="display:flex; align-items:center; gap:8px; font-size:12.5px; font-weight:600; color:#334155;">
                    <span style="width:12px; height:12px; border-radius:3px; background:#D97706; display:inline-block;"></span> Command Center
                </div>
            </div>
        </div>

        {{-- Aktivitas Terbaru --}}
        <div style="background:#fff; border-radius:16px; box-shadow:0px 18px 40px rgba(112, 144, 176, 0.10); border:1px solid #EAEFF8; overflow:hidden;">
            <div style="padding:18px 24px 14px; border-bottom:1px solid #EDF2F7; display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:15px; font-weight:700; color:#0F172A; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="activity" style="width:18px; height:18px; color:#1B2A6B;"></i>
                    Aktivitas Terbaru
                </span>
            </div>
            <div style="padding:0 20px;">
                @php
                $activities = [
                    ['icon'=>'wrench',          'color'=>'#C0201F', 'bg'=>'rgba(192,32,31,.10)', 'text'=>'Pengajuan pemeliharaan Unit Damkar-01', 'time'=>'2 menit lalu'],
                    ['icon'=>'truck',           'color'=>'#1B2A6B', 'bg'=>'rgba(27,42,107,.10)',  'text'=>'Cek harian Unit Pemadam selesai',        'time'=>'30 menit lalu'],
                    ['icon'=>'clipboard-check', 'color'=>'#D97706', 'bg'=>'rgba(217,119,6,.10)',  'text'=>'Pemeriksaan APAR Pos-3 selesai',         'time'=>'1 jam lalu'],
                    ['icon'=>'radio-tower',     'color'=>'#1B2A6B', 'bg'=>'rgba(27,42,107,.10)',  'text'=>'Cek alat Command Center diperbarui',     'time'=>'3 jam lalu'],
                    ['icon'=>'life-buoy',       'color'=>'#C0201F', 'bg'=>'rgba(192,32,31,.10)', 'text'=>'Riwayat Unit Rescue baru ditambahkan',   'time'=>'Kemarin'],
                ];
                @endphp
                @foreach($activities as $act)
                <div style="display:flex; align-items:flex-start; gap:12px; padding:12px 0; border-bottom:1px solid #F1F5F9;">
                    <div style="width:32px; height:32px; border-radius:10px; background:{{ $act['bg'] }}; color:{{ $act['color'] }}; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;">
                        <i data-lucide="{{ $act['icon'] }}" style="width:15px; height:15px;"></i>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:12.5px; font-weight:600; color:#1E293B; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $act['text'] }}</div>
                        <div style="font-size:11px; color:#64748B; margin-top:2px;">{{ $act['time'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>

@push('scripts')
{{-- Chart.js Library --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('chartPemeliharaan').getContext('2d');

    // Gradien Warna untuk Datasets Chart
    const gradientBlue = ctx.createLinearGradient(0, 0, 0, 260);
    gradientBlue.addColorStop(0, 'rgba(27, 42, 107, 0.85)');
    gradientBlue.addColorStop(1, 'rgba(27, 42, 107, 0.15)');

    const gradientRed = ctx.createLinearGradient(0, 0, 0, 260);
    gradientRed.addColorStop(0, 'rgba(192, 32, 31, 0.85)');
    gradientRed.addColorStop(1, 'rgba(192, 32, 31, 0.15)');

    const gradientAmber = ctx.createLinearGradient(0, 0, 0, 260);
    gradientAmber.addColorStop(0, 'rgba(217, 119, 6, 0.85)');
    gradientAmber.addColorStop(1, 'rgba(217, 119, 6, 0.15)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [
                {
                    label: 'Unit Pemadam',
                    data: [12, 19, 14, 25, 18, 12, 22, 28, 15, 20, 24, 30],
                    backgroundColor: gradientBlue,
                    borderColor: '#1B2A6B',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Unit Rescue',
                    data: [8, 15, 10, 20, 14, 9, 17, 24, 11, 16, 19, 25],
                    backgroundColor: gradientRed,
                    borderColor: '#C0201F',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Command Center',
                    data: [5, 9, 6, 12, 8, 5, 11, 15, 7, 10, 13, 18],
                    backgroundColor: gradientAmber,
                    borderColor: '#D97706',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1200,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    display: false // Menggunakan legend custom HTML di bawah
                },
                tooltip: {
                    backgroundColor: '#0F172A',
                    titleFont: { family: 'Poppins', size: 13, weight: 'bold' },
                    bodyFont: { family: 'Poppins', size: 12 },
                    padding: 12,
                    cornerRadius: 10,
                    boxPadding: 6,
                    usePointStyle: true,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Poppins', size: 11, weight: '500' },
                        color: '#64748B'
                    }
                },
                y: {
                    grid: {
                        color: '#F1F5F9',
                        drawBorder: false
                    },
                    ticks: {
                        font: { family: 'Poppins', size: 11, weight: '500' },
                        color: '#64748B',
                        stepSize: 5
                    }
                }
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
@media (max-width: 900px) {
    .dash-bottom-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush

@endsection
