@extends('layouts.admin')
@section('title', ($pageTitle ?? 'Admin') . ' — Admin')

@section('content')

<div class="form-card">
    <div style="display:flex; align-items:baseline; gap:10px; margin-bottom:22px;">
        <h1 class="form-card-title" style="margin-bottom:0;">{{ $pageTitle ?? 'Halaman Admin' }}</h1>
        @if(!empty($breadcrumb))
            <span style="font-size:12px; color:#757575;">{{ implode(' › ', $breadcrumb) }}</span>
        @endif
    </div>

    <div style="text-align:center; padding:40px 20px; color:#757575; border:1px dashed #DEDEDE; border-radius:5px;">
        <i data-lucide="construction" style="width:40px;height:40px;opacity:.3;margin-bottom:10px;"></i>
        <p style="font-size:14px;font-weight:600;">Halaman dalam pengembangan</p>
        <p style="font-size:12px;margin-top:6px;">Konten untuk <strong>{{ $pageTitle ?? '-' }}</strong> akan ditambahkan di sini.</p>
    </div>
</div>

@endsection
