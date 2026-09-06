@php
    $isAktual = request()->routeIs('admin.pemeliharaan.spj-pembayaran.*') || request()->routeIs('admin.pemeliharaan.monitoring-aktual.*');
    $pageTitle = $isAktual ? 'SPJ Pembayaran' : 'Aktual Pembayaran';
    $routePrefix = $isAktual ? 'admin.pemeliharaan.spj-pembayaran' : 'admin.pemeliharaan.aktual-pembayaran';
@endphp

@extends('layouts.admin')

@section('title', 'Buat Invoice — ' . $pageTitle)

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
    color: #1E293B;
    border: 1px solid #E2E8F0;
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

    {{-- Header Section --}}
    <div class="invoice-page-header">
        <div>
            <div style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:#64748B; margin-bottom:6px;">
                <a href="{{ route($routePrefix . '.index') }}" style="color:#64748B; text-decoration:none; transition:color 0.2s;">{{ $pageTitle }}</a>
                <span style="color:#94A3B8;">/</span>
                <span style="color:#1B2A6B; font-weight:600;">Buat Baru</span>
            </div>
            <h1 style="font-size:22px; font-weight:800; color:#121E4E; margin:0 0 4px 0;">Buat Invoice</h1>
            <p style="font-size:13px; color:#64748B; margin:0;">Isi formulir data dan rincian item pemeliharaan untuk menerbitkan invoice baru.</p>
        </div>

        <a href="{{ route($routePrefix . '.index') }}" class="invoice-btn-back">
            <i data-lucide="arrow-left" style="width:16px; height:16px; color:#64748B;"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    {{-- Form --}}
    <form action="{{ route($routePrefix . '.store') }}" method="POST">
        @csrf
        @include('admin.pemeliharaan.spj-pembayaran._form')
    </form>

</div>
@endsection
