{{-- resources/views/admin/pemeliharaan/invoice/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Buat Invoice')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
@endpush

@section('content')
<div class="page-header mb-4">
    <h2 class="fw-bold text-primary mb-0">Buat Invoice</h2>
    <nav style="--bs-breadcrumb-divider: '›';">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.pemeliharaan.invoice.index') }}">Invoice</a></li>
            <li class="breadcrumb-item active">Buat Baru</li>
        </ol>
    </nav>
</div>

<form action="{{ route('admin.pemeliharaan.invoice.store') }}" method="POST">
    @csrf
    @include('admin.pemeliharaan.invoice._form')
</form>
@endsection
