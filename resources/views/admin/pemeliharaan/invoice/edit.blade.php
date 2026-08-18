{{-- resources/views/admin/pemeliharaan/invoice/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Invoice')

@section('content')
<style>
.invoice-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}
.invoice-btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    background: #FFFFFF;
    color: #1B2A6B;
    border: 1px solid #CBD5E1;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}
@media (max-width: 768px) {
    .invoice-page-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
        margin-bottom: 16px !important;
    }
    .invoice-btn-back {
        width: 100% !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }
}
</style>

<div class="form-card" style="max-width:100%; box-shadow:none; padding:0; background:transparent;">

    {{-- Page Header --}}
    <div class="invoice-page-header">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#121E4E; margin:0 0 6px 0;">Edit Invoice</h1>
            <div style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:#64748B;">
                <a href="{{ route('admin.pemeliharaan.invoice.index') }}" style="color:#1B2A6B; text-decoration:none; font-weight:600;">Monitoring Invoice</a>
                <span>›</span>
                <span style="color:#0F172A; font-weight:600;">{{ $invoice->nomor_invoice }}</span>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('admin.pemeliharaan.invoice.index') }}" class="invoice-btn-back">
                <i data-lucide="arrow-left" style="width:16px; height:16px;"></i>
                <span>Kembali ke Daftar Invoice</span>
            </a>
        </div>
    </div>

    {{-- Form Edit Invoice --}}
    <form action="{{ route('admin.pemeliharaan.invoice.update', $invoice) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.pemeliharaan.invoice._form')
    </form>

</div>
@endsection
