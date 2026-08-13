{{-- resources/views/admin/pemeliharaan/invoice/index.blade.php --}}
@extends('layouts.admin') {{-- sesuaikan dengan layout master project Anda --}}

@section('title', 'Invoice')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
@endpush

@section('content')
<div class="page-header mb-4">
    <h2 class="fw-bold text-primary mb-0">Invoice</h2>
    <nav style="--bs-breadcrumb-divider: '›';">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">Pemeliharaan</li>
            <li class="breadcrumb-item active">Invoice</li>
        </ol>
    </nav>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                       placeholder="Cari no. invoice / no. pol / no. lambung" style="width:260px">

                <select name="status" class="form-select form-select-sm" style="width:160px">
                    <option value="">Semua Status</option>
                    @foreach (['draft' => 'Draft', 'diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'lunas' => 'Lunas'] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i> Cari</button>
            </form>

            <a href="{{ route('admin.pemeliharaan.invoice.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> Buat Invoice
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. Invoice</th>
                        <th>Tanggal</th>
                        <th>Unit</th>
                        <th>No. Pol</th>
                        <th class="text-end">Total Biaya</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width:140px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="fw-semibold">{{ $invoice->nomor_invoice }}</td>
                            <td>{{ $invoice->tanggal_invoice->format('d-m-Y') }}</td>
                            <td>{{ $invoice->no_lambung }} <div class="text-muted small">{{ $invoice->jenis_mobil }}</div></td>
                            <td>{{ $invoice->no_pol }}</td>
                            <td class="text-end">Rp {{ number_format($invoice->total_biaya, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @php
                                    $badge = [
                                        'draft' => 'secondary',
                                        'diajukan' => 'warning',
                                        'disetujui' => 'info',
                                        'lunas' => 'success',
                                    ][$invoice->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $badge }} text-uppercase">{{ $invoice->status }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.pemeliharaan.invoice.show', $invoice) }}" class="btn btn-sm btn-outline-secondary" title="Lihat / Cetak">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.pemeliharaan.invoice.edit', $invoice) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.pemeliharaan.invoice.destroy', $invoice) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus invoice {{ $invoice->nomor_invoice }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data invoice.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection
