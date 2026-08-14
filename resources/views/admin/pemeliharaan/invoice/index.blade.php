@extends('layouts.admin')

@section('title', 'Monitoring Invoice — Admin')

@section('content')
<style>
.invoice-index-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}
.invoice-filter-form {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    width: 100%;
    justify-content: space-between;
}
.invoice-filter-inputs {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.invoice-search-wrap {
    position: relative;
    width: 270px;
}
.invoice-search-input {
    padding: 8px 14px 8px 34px;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    font-size: 13px;
    outline: none;
    width: 100%;
    background: #FFFFFF;
    color: #0F172A;
    box-sizing: border-box;
    transition: border-color 0.2s;
}

@media (max-width: 768px) {
    .invoice-index-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
    }
    .invoice-index-header a {
        width: 100% !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }
    .invoice-filter-inputs {
        flex-direction: column !important;
        align-items: stretch !important;
        width: 100% !important;
        gap: 8px !important;
    }
    .invoice-search-wrap {
        width: 100% !important;
    }
    .invoice-filter-inputs select,
    .invoice-filter-inputs button,
    .invoice-filter-inputs a {
        width: 100% !important;
        justify-content: center !important;
        box-sizing: border-box !important;
    }
}
</style>

<div class="form-card" style="max-width:100%; box-shadow:none; padding:0; background:transparent;">

    {{-- Page Header --}}
    <div class="invoice-index-header">
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#121E4E; margin:0 0 2px 0;">Monitoring Invoice</h1>
            <p style="font-size:13px; color:#64748B; margin:0;">Monitoring data invoice pemeliharaan unit.</p>
        </div>
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('admin.pemeliharaan.invoice.create') }}"
               style="display:inline-flex; align-items:center; gap:8px; padding:9px 18px; background:#1B2A6B; color:#FFFFFF; border-radius:10px; font-size:12.5px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(27,42,107,0.25); transition:all 0.2s ease;">
                <i data-lucide="plus-circle" style="width:16px; height:16px;"></i>
                <span>Buat Invoice</span>
            </a>
        </div>
    </div>

    {{-- Alert Flash Success --}}
    @if(session('success'))
        <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:12px 18px; border-radius:12px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:10px; margin-bottom:20px; box-shadow:0 2px 8px rgba(16,185,129,0.1);">
            <i data-lucide="check-circle-2" style="width:18px; height:18px; color:#10B981; flex-shrink:0;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Main Data Card --}}
    <div style="background:#FFFFFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0px 18px 40px rgba(112,144,176,0.08); overflow:hidden;">

        {{-- Filter & Search Toolbar --}}
        <div style="padding:16px 20px; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; background:#FAFAFA;">
            <form method="GET" action="{{ route('admin.pemeliharaan.invoice.index') }}" class="invoice-filter-form">
                <div class="invoice-filter-inputs">
                    {{-- Search Input --}}
                    <div class="invoice-search-wrap">
                        <i data-lucide="search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:#94A3B8;"></i>
                        <input type="text"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="Cari no. invoice / no. pol / lambung..."
                               class="invoice-search-input"
                               onfocus="this.style.borderColor='#1B2A6B';"
                               onblur="this.style.borderColor='#CBD5E1';">
                    </div>

                    {{-- Status Select Filter --}}
                    <select name="status"
                            style="padding:8px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; outline:none; background:#FFFFFF; color:#1E293B; font-weight:600; cursor:pointer; transition:border-color 0.2s;"
                            onfocus="this.style.borderColor='#1B2A6B';"
                            onblur="this.style.borderColor='#CBD5E1';">
                        <option value="">Semua Status</option>
                        @foreach (['draft' => 'Draft', 'diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'lunas' => 'Lunas'] as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                            style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:#1B2A6B; color:#FFFFFF; border:none; border-radius:8px; font-size:12.5px; font-weight:700; cursor:pointer; box-shadow:0 2px 6px rgba(27,42,107,0.18); transition:all 0.2s;">
                        <i data-lucide="search" style="width:14px; height:14px;"></i>
                        <span>Cari</span>
                    </button>

                    @if(request('q') || request('status'))
                        <a href="{{ route('admin.pemeliharaan.invoice.index') }}"
                           style="display:inline-flex; align-items:center; gap:4px; padding:8px 14px; background:#F1F5F9; color:#475569; border:1px solid #CBD5E1; border-radius:8px; font-size:12.5px; text-decoration:none; font-weight:600; transition:all 0.2s;">
                            <i data-lucide="rotate-ccw" style="width:13px; height:13px;"></i>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>

                <div>
                    <span style="font-size:12px; color:#64748B; font-weight:600;">
                        Total: <strong style="color:#0F172A;">{{ $invoices->total() }}</strong> Invoice
                    </span>
                </div>
            </form>
        </div>

        {{-- Table Data --}}
        <div style="overflow-x:auto;">
            <table style="width:100%; min-width:900px; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="background:#1B2A6B; color:#FFFFFF;">
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B;">
                            No. Invoice
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; width:120px;">
                            Tanggal
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B;">
                            Unit Kendaraan
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; width:130px;">
                            No. Polisi
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; text-align:right; width:160px;">
                            Total Biaya
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; text-align:center; width:120px;">
                            Status
                        </th>
                        <th style="padding:14px 18px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #101B4B; text-align:center; width:140px;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s ease;"
                            onmouseover="this.style.background='#F8FAFC';"
                            onmouseout="this.style.background='transparent';">
                            <td style="padding:14px 18px; font-size:13px; font-weight:700; color:#1B2A6B;">
                                <a href="{{ route('admin.pemeliharaan.invoice.show', $invoice) }}"
                                   style="color:#1B2A6B; text-decoration:none; font-weight:700;"
                                   onmouseover="this.style.textDecoration='underline';"
                                   onmouseout="this.style.textDecoration='none';">
                                    {{ $invoice->nomor_invoice }}
                                </a>
                            </td>
                            <td style="padding:14px 18px; font-size:12.5px; color:#475569; white-space:nowrap;">
                                {{ $invoice->tanggal_invoice ? $invoice->tanggal_invoice->format('d/m/Y') : '—' }}
                            </td>
                            <td style="padding:14px 18px;">
                                <div style="font-size:13px; font-weight:700; color:#0F172A;">{{ $invoice->no_lambung ?? '—' }}</div>
                                @if($invoice->jenis_mobil)
                                    <div style="font-size:11.5px; color:#64748B; margin-top:2px;">{{ $invoice->jenis_mobil }}</div>
                                @endif
                            </td>
                            <td style="padding:14px 18px; font-size:12.5px; font-weight:600; color:#1E293B;">
                                <span style="background:#F1F5F9; color:#334155; padding:3px 8px; border-radius:6px; font-size:12px; font-weight:700; border:1px solid #E2E8F0;">
                                    {{ $invoice->no_pol ?? '—' }}
                                </span>
                            </td>
                            <td style="padding:14px 18px; font-size:13px; font-weight:800; color:#0F172A; text-align:right; white-space:nowrap;">
                                Rp {{ number_format($invoice->total_biaya, 0, ',', '.') }}
                            </td>
                            <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                @php
                                    $statusStyles = [
                                        'draft' => 'background:#F1F5F9; color:#475569; border:1px solid #CBD5E1;',
                                        'diajukan' => 'background:#FEF3C7; color:#92400E; border:1px solid #FDE68A;',
                                        'disetujui' => 'background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE;',
                                        'lunas' => 'background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0;',
                                    ];
                                    $style = $statusStyles[$invoice->status] ?? 'background:#F1F5F9; color:#475569; border:1px solid #CBD5E1;';
                                @endphp
                                <span style="display:inline-flex; align-items:center; justify-content:center; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; {{ $style }}">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                            <td style="padding:14px 18px; text-align:center; white-space:nowrap;">
                                <div style="display:inline-flex; align-items:center; gap:6px; justify-content:center;">
                                    <a href="{{ route('admin.pemeliharaan.invoice.show', ['invoice' => $invoice, 'download' => 1]) }}"
                                       target="_blank"
                                       title="Unduh Invoice (PDF)"
                                       style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; background:#FFFFFF; color:#1B2A6B; border:1px solid #1B2A6B; border-radius:8px; text-decoration:none; transition:all 0.2s;"
                                       onmouseover="this.style.background='#1B2A6B'; this.style.color='#FFFFFF';"
                                       onmouseout="this.style.background='#FFFFFF'; this.style.color='#1B2A6B';">
                                        <i data-lucide="download" style="width:15px; height:15px;"></i>
                                    </a>

                                    <a href="{{ route('admin.pemeliharaan.invoice.edit', $invoice) }}"
                                       title="Edit Invoice"
                                       style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; background:#FFFFFF; color:#1B2A6B; border:1px solid #1B2A6B; border-radius:8px; text-decoration:none; transition:all 0.2s;"
                                       onmouseover="this.style.background='#1B2A6B'; this.style.color='#FFFFFF';"
                                       onmouseout="this.style.background='#FFFFFF'; this.style.color='#1B2A6B';">
                                        <i data-lucide="pencil" style="width:15px; height:15px;"></i>
                                    </a>

                                    <form action="{{ route('admin.pemeliharaan.invoice.destroy', $invoice) }}"
                                          method="POST"
                                          style="display:inline-block; margin:0;"
                                          onsubmit="return confirm('Hapus invoice {{ $invoice->nomor_invoice }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Hapus Invoice"
                                                style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; background:#FFFFFF; color:#C0201F; border:1px solid #FCA5A5; border-radius:8px; cursor:pointer; transition:all 0.2s;"
                                                onmouseover="this.style.background='#C0201F'; this.style.color='#FFFFFF'; this.style.borderColor='#C0201F';"
                                                onmouseout="this.style.background='#FFFFFF'; this.style.color='#C0201F'; this.style.borderColor='#FCA5A5';">
                                            <i data-lucide="trash-2" style="width:15px; height:15px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:48px 20px; text-align:center; color:#64748B;">
                                <div style="width:56px; height:56px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px; border:1px solid #E2E8F0;">
                                    <i data-lucide="file-x" style="width:26px; height:26px; color:#94A3B8;"></i>
                                </div>
                                <div style="font-size:15px; font-weight:700; color:#334155; margin-bottom:4px;">Belum Ada Data Invoice</div>
                                <div style="font-size:12.5px; color:#94A3B8;">Belum ada catatan invoice yang tersimpan atau sesuai dengan kriteria filter.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($invoices->hasPages())
            <div style="padding:16px 20px; border-top:1px solid #F1F5F9; background:#FAFCFE;">
                {{ $invoices->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
