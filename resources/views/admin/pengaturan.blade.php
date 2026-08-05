@extends('layouts.admin')
@section('title', 'Pengaturan — Admin')

@section('content')

<div class="form-card">
    <div style="display:flex; align-items:baseline; gap:10px; margin-bottom:22px;">
        <h1 class="form-card-title" style="margin-bottom:0;">Pengaturan</h1>
    </div>

    {{-- === Lihat Halaman User === --}}
    <div style="background: #FAFAFA; border: 1px solid #E8E8E8; border-radius: 10px; padding: 24px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 style="font-size: 15px; font-weight: 600; color: #333; margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="eye" style="width: 18px; height: 18px; color: #C0201F;"></i>
                    Lihat Halaman User
                </h3>
                <p style="font-size: 12.5px; color: #757575; margin: 0;">
                    Masuk ke tampilan halaman user untuk melihat dan mengecek kondisi halaman dari sisi pengguna.
                </p>
            </div>
            <form action="{{ route('admin.switch-to-user') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="external-link" style="width: 16px; height: 16px;"></i>
                    Buka Halaman User
                </button>
            </form>
        </div>
    </div>

    {{-- === Placeholder untuk pengaturan lainnya === --}}
    <div style="text-align:center; padding:40px 20px; color:#757575; border:1px dashed #DEDEDE; border-radius:5px;">
        <i data-lucide="construction" style="width:40px;height:40px;opacity:.3;margin-bottom:10px;"></i>
        <p style="font-size:14px;font-weight:600;">Pengaturan Lainnya</p>
        <p style="font-size:12px;margin-top:6px;">Fitur pengaturan tambahan akan ditambahkan di sini.</p>
    </div>
</div>

@endsection
