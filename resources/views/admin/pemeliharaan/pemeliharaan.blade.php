@extends('layouts.admin')
@section('title', 'Surat Permohonan — Admin')

@section('content')

<div class="form-card" style="max-width:100%; box-shadow:none; padding:0; background:transparent;">

    {{-- Page Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#121E4E; margin-bottom:2px;">Surat Permohonan Pemeliharaan Kendaraan</h1>
            <p style="font-size:13px; color:#64748B; margin:0;">Cetak dan kelola surat permohonan bidang, bengkel, dan surat pesanan pemeliharaan unit.</p>
        </div>
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('admin.view-as-user', 'pengajuan') }}"
               target="_blank"
               style="display:inline-flex; align-items:center; gap:6px; padding:9px 18px; background:#1B2A6B; color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(27,42,107,0.25); transition:all 0.2s ease;">
                <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
                Input Data Pemeliharaan
            </a>
        </div>
    </div>

    {{-- Main Data Card --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">

        {{-- Table Toolbar / Filter & Search --}}
        <div style="padding:16px 24px; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; background:#FAFAFA;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(27,42,107,0.08); color:#1B2A6B; display:flex; align-items:center; justify-content:center;">
                    <i data-lucide="file-text" style="width:18px; height:18px;"></i>
                </div>
                <div>
                    <div style="font-size:14px; font-weight:700; color:#0F172A;">Daftar Permohonan Pemeliharaan</div>
                    <div style="font-size:11.5px; color:#64748B;">Klik tombol surat pada kolom Aksi Unduh untuk mencetak PDF.</div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.pemeliharaan.pemeliharaan') }}" style="display:flex; align-items:center; gap:8px;">
                <div style="position:relative;">
                    <i data-lucide="search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:#94A3B8;"></i>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari nopol / lambung..."
                           style="padding:7px 12px 7px 34px; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; width:220px; outline:none; transition:border 0.2s;"
                           onfocus="this.style.borderColor='#1B2A6B';"
                           onblur="this.style.borderColor='#CBD5E1';">
                </div>
                <button type="submit" style="padding:7px 14px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    Cari
                </button>
                @if(!empty($search))
                    <a href="{{ route('admin.pemeliharaan.pemeliharaan') }}" style="padding:7px 12px; background:#E2E8F0; color:#475569; border-radius:8px; font-size:12px; text-decoration:none; font-weight:600;">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Table Data --}}
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="background:#1B2A6B; color:#FFFFFF;">
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; width:170px;">
                            Waktu Input
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B;">
                            Nomor Lambung
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; width:210px;">
                            Kode Verifikasi
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; text-align:center; width:410px;">
                            Aksi Unduh (PDF)
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                        <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s ease;" onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='transparent';">
                            <td style="padding:14px 18px; font-size:13px; color:#334155; font-weight:500;">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <i data-lucide="clock" style="width:14px; height:14px; color:#94A3B8;"></i>
                                    <span>{{ $rec->created_at ? $rec->created_at->format('d/m/Y H:i') : '-' }}</span>
                                </div>
                            </td>
                            <td style="padding:14px 18px; font-size:13.5px; font-weight:700; color:#0F172A;">
                                {{ $rec->nomor_lambung ?? '-' }}
                                @if(!empty($rec->pos))
                                    <span style="display:block; font-size:11px; font-weight:500; color:#64748B; margin-top:2px;">
                                        {{ $rec->pos }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding:14px 18px;">
                                <span style="font-family:monospace; font-weight:800; font-size:12.5px; color:#C0201F; background:#FEF2F2; padding:4px 10px; border-radius:6px; border:1px solid #FCA5A5; display:inline-block;">
                                    {{ $rec->kode_verifikasi }}
                                </span>
                            </td>
                            <td style="padding:14px 18px; text-align:center;">
                                <div style="display:inline-flex; gap:6px; flex-wrap:wrap; justify-content:center;">
                                    <a href="{{ route('admin.pemeliharaan.cetak-dokumen', ['id' => $rec->id, 'type' => 'permohonanbidang']) }}"
                                       target="_blank"
                                       title="Cetak Surat Permohonan Bidang"
                                       style="display:inline-flex; align-items:center; gap:4px; padding:6px 10px; background:#FFFFFF; color:#1B2A6B; border:1px solid #1B2A6B; border-radius:6px; font-size:11.5px; font-weight:600; text-decoration:none; transition:all 0.2s;"
                                       onmouseover="this.style.background='#1B2A6B'; this.style.color='#FFFFFF';"
                                       onmouseout="this.style.background='#FFFFFF'; this.style.color='#1B2A6B';">
                                        <i data-lucide="file-text" style="width:13px; height:13px;"></i>
                                        Permohonan Bidang
                                    </a>
                                    <a href="{{ route('admin.pemeliharaan.cetak-dokumen', ['id' => $rec->id, 'type' => 'permohonanbengkel']) }}"
                                       target="_blank"
                                       title="Cetak Surat Permohonan Bengkel"
                                       style="display:inline-flex; align-items:center; gap:4px; padding:6px 10px; background:#FFFFFF; color:#C0201F; border:1px solid #C0201F; border-radius:6px; font-size:11.5px; font-weight:600; text-decoration:none; transition:all 0.2s;"
                                       onmouseover="this.style.background='#C0201F'; this.style.color='#FFFFFF';"
                                       onmouseout="this.style.background='#FFFFFF'; this.style.color='#C0201F';">
                                        <i data-lucide="wrench" style="width:13px; height:13px;"></i>
                                        Permohonan Bengkel
                                    </a>
                                    <a href="{{ route('admin.pemeliharaan.cetak-dokumen', ['id' => $rec->id, 'type' => 'Suratpesanan']) }}"
                                       target="_blank"
                                       title="Cetak Surat Pesanan"
                                       style="display:inline-flex; align-items:center; gap:4px; padding:6px 10px; background:#FFFFFF; color:#D97706; border:1px solid #D97706; border-radius:6px; font-size:11.5px; font-weight:600; text-decoration:none; transition:all 0.2s;"
                                       onmouseover="this.style.background='#D97706'; this.style.color='#FFFFFF';"
                                       onmouseout="this.style.background='#FFFFFF'; this.style.color='#D97706';">
                                        <i data-lucide="shopping-bag" style="width:13px; height:13px;"></i>
                                        Surat Pesanan
                                    </a>
                                    <a href="{{ route('admin.pemeliharaan.invoice.create', ['pengajuan_id' => $rec->id]) }}"
                                       title="Buat Invoice dari Permohonan Ini"
                                       style="display:inline-flex; align-items:center; gap:4px; padding:6px 10px; background:#1B2A6B; color:#FFFFFF; border:1px solid #1B2A6B; border-radius:6px; font-size:11.5px; font-weight:600; text-decoration:none; transition:all 0.2s; box-shadow:0 2px 6px rgba(27,42,107,0.2);"
                                       onmouseover="this.style.background='#0F172A';"
                                       onmouseout="this.style.background='#1B2A6B';">
                                        <i data-lucide="receipt" style="width:13px; height:13px;"></i>
                                        + Invoice
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:40px 20px; text-align:center; color:#94A3B8;">
                                <i data-lucide="inbox" style="width:36px; height:36px; margin-bottom:8px; opacity:0.5;"></i>
                                <div style="font-size:13.5px; font-weight:600; color:#475569;">Belum ada data permohonan pemeliharaan.</div>
                                <div style="font-size:12px; margin-top:4px;">Data permohonan yang diajukan oleh user akan tampil di tabel ini secara otomatis.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if($records->hasPages())
            <div style="padding:16px 24px; border-top:1px solid #F1F5F9; background:#FAFAFA; display:flex; align-items:center; justify-content:space-between;">
                <div style="font-size:12px; color:#64748B;">
                    Menampilkan <strong>{{ $records->firstItem() }}</strong> - <strong>{{ $records->lastItem() }}</strong> dari <strong>{{ $records->total() }}</strong> pengajuan
                </div>
                <div>
                    {{ $records->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

@endsection
