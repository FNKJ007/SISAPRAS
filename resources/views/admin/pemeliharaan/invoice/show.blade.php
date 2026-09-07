{{-- resources/views/admin/pemeliharaan/invoice/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Invoice')

@section('content')
<div class="page-header mb-4 d-print-none">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h2 class="fw-bold text-primary mb-0">Invoice {{ $invoice->nomor_invoice }}</h2>
            <nav style="--bs-breadcrumb-divider: '›';">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.pemeliharaan.invoice.index') }}">Invoice</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pemeliharaan.invoice.edit', $invoice) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Cetak
            </button>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5" id="invoice-print-area">

        {{-- KOP SURAT --}}
        <div class="d-flex align-items-center border-bottom border-2 border-dark pb-3 mb-4">
            <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo" style="height:60px" class="me-3"
                 onerror="this.style.display='none'">
            <div>
                <div class="fw-bold" style="font-size:1.1rem">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                <div class="fw-bold">KABUPATEN BANDUNG</div>
                <div class="small text-muted">Bidang Sarana, Prasarana dan Informasi — Seksi Pemeliharaan Sarana dan Prasarana</div>
            </div>
        </div>

        <h5 class="text-center fw-bold text-uppercase mb-4">Invoice Pemeliharaan Kendaraan</h5>

        {{-- DATA INVOICE & UNIT --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td style="width:160px" class="text-muted">No. Invoice</td>
                        <td class="fw-semibold">: {{ $invoice->nomor_invoice }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal</td>
                        <td>: {{ $invoice->tanggal_invoice->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tahun Anggaran</td>
                        <td>: {{ $invoice->tahun_anggaran }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kode Rekening</td>
                        <td>: {{ $invoice->kode_rekening ?: '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td style="width:160px" class="text-muted">No. Lambung</td>
                        <td class="fw-semibold">: {{ $invoice->no_lambung }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. Polisi</td>
                        <td>: {{ $invoice->no_pol }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis Kendaraan</td>
                        <td>: {{ $invoice->jenis_mobil }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lokasi / Pos</td>
                        <td>: {{ $invoice->lokasi ?: '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- RINCIAN ITEM --}}
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr class="text-center">
                        <th style="width:4%">No</th>
                        <th style="width:12%">Tanggal</th>
                        <th>Jenis Perbaikan</th>
                        <th style="width:8%">Vol</th>
                        <th style="width:8%">Satuan</th>
                        <th style="width:16%">Harga Satuan (Rp)</th>
                        <th style="width:16%">Total Biaya (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $i => $item)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td class="text-center">{{ $item->tanggal->format('d-m-Y') }}</td>
                            <td>{{ $item->jenis_perbaikan }}</td>
                            <td class="text-center">{{ rtrim(rtrim(number_format($item->vol, 2, ',', '.'), '0'), ',') }}</td>
                            <td class="text-center">{{ $item->satuan }}</td>
                            <td class="text-end">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-end fw-bold">TOTAL BIAYA</td>
                        <td class="text-end fw-bold">Rp {{ number_format($invoice->total_biaya, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($invoice->catatan)
            <div class="mb-4">
                <div class="text-muted small">Catatan:</div>
                <div>{{ $invoice->catatan }}</div>
            </div>
        @endif

        {{-- STATUS & TANDA TANGAN --}}
        <div class="row mt-5">
            <div class="col-md-6">
                <span class="badge bg-secondary text-uppercase">Status: {{ $invoice->status }}</span>
            </div>
            <div class="col-md-6 text-center">
                <div>Soreang, {{ $invoice->tanggal_invoice->translatedFormat('d F Y') }}</div>
                <div class="mb-5">Kepala Seksi Pemeliharaan Sarana dan Prasarana</div>
                <div class="fw-bold text-decoration-underline">( ....................................... )</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    @media print {
        .d-print-none, aside, nav.navbar, .app-sidebar, .app-header { display: none !important; }
        #invoice-print-area { padding: 0 !important; }
        body { background: #fff !important; }
    }
</style>
@endpush
