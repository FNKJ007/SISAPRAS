{{-- resources/views/admin/pemeliharaan/invoice/_form.blade.php --}}
@php
    $isEdit = isset($invoice);
    $selectedPengajuanId = $selectedPengajuanId ?? old('pengajuan_id', null);

    $items = old('items', $isEdit ? $invoice->items->map(fn ($i) => [
        'tanggal' => $i->tanggal->format('Y-m-d'),
        'jenis_perbaikan' => $i->jenis_perbaikan,
        'vol' => (float) $i->vol,
        'satuan' => $i->satuan,
        'harga_satuan' => (float) $i->harga_satuan,
    ])->toArray() : [
        ['tanggal' => now()->format('Y-m-d'), 'jenis_perbaikan' => '', 'vol' => 1, 'satuan' => 'PCS', 'harga_satuan' => 0],
    ]);
@endphp

{{-- Hidden fields for selected unit & pengajuan metadata --}}
<input type="hidden" name="pengajuan_id" id="pengajuan_id" value="{{ old('pengajuan_id', $isEdit ? ($invoice->pengajuan_id ?? '') : $selectedPengajuanId) }}">
<input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id', $isEdit ? $invoice->unit_id : '') }}">
<input type="hidden" name="no_lambung" id="input_no_lambung" value="{{ old('no_lambung', $isEdit ? $invoice->no_lambung : '') }}">
<input type="hidden" name="no_pol" id="input_no_pol" value="{{ old('no_pol', $isEdit ? $invoice->no_pol : '') }}">
<input type="hidden" name="jenis_mobil" id="input_jenis_mobil" value="{{ old('jenis_mobil', $isEdit ? $invoice->jenis_mobil : '') }}">
<input type="hidden" name="lokasi" id="input_lokasi" value="{{ old('lokasi', $isEdit ? $invoice->lokasi : '') }}">

<style>
.invoice-card {
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid #E2E8F0;
    box-shadow: 0px 18px 40px rgba(112,144,176,0.08);
    padding: 24px;
    margin-bottom: 16px;
}
.invoice-form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.invoice-col-1 { grid-column: span 1; }
.invoice-col-2 { grid-column: span 2; }
.invoice-col-3 { grid-column: span 3; }
.invoice-col-full { grid-column: 1 / -1; }

.invoice-preview-box {
    margin-top: 10px;
    padding: 12px 16px;
    background: #F1F5F9;
    border-radius: 10px;
    border: 1px solid #E2E8F0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 12px;
    font-size: 12px;
    color: #334155;
}

.invoice-header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 10px;
}
.invoice-btn-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.invoice-action-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

@media (max-width: 768px) {
    .invoice-card {
        padding: 16px !important;
        border-radius: 12px !important;
        margin-bottom: 14px !important;
    }
    .invoice-form-grid {
        grid-template-columns: 1fr !important;
        gap: 14px !important;
    }
    .invoice-col-1,
    .invoice-col-2,
    .invoice-col-3,
    .invoice-col-full {
        grid-column: span 1 !important;
    }
    .invoice-preview-box {
        grid-template-columns: 1fr 1fr !important;
        gap: 8px 12px !important;
        padding: 10px 12px !important;
    }
    .invoice-preview-box > div {
        word-break: break-word;
    }
    .invoice-header-flex {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
    }
    .invoice-btn-group {
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        width: 100% !important;
    }
    .invoice-btn-group button,
    .invoice-btn-group a {
        width: 100% !important;
        justify-content: center !important;
    }
    .invoice-action-footer {
        flex-direction: column-reverse !important;
        width: 100% !important;
        gap: 8px !important;
    }
    .invoice-action-footer button,
    .invoice-action-footer a {
        width: 100% !important;
        justify-content: center !important;
    }
}
</style>

<div class="invoice-card">
    <h6 style="font-weight:700; text-transform:uppercase; color:#64748B; margin-bottom:16px; margin-top:0; font-size:13px; letter-spacing:0.5px;">Informasi Invoice</h6>
    
    <div class="invoice-form-grid">
        <div class="invoice-col-1">
            <label style="font-size:12.5px; font-weight:600; color:#475569; margin-bottom:4px; display:block;">Nomor Invoice</label>
            <input type="text" name="nomor_invoice" style="padding:9px 14px; border:1px solid {{ $errors->has('nomor_invoice') ? '#C0201F' : '#CBD5E1' }}; border-radius:8px; font-size:13px; width:100%; outline:none; box-sizing:border-box;"
                   value="{{ old('nomor_invoice', $isEdit ? $invoice->nomor_invoice : $nomorInvoice) }}" required>
            @error('nomor_invoice') <div style="color:#C0201F; font-size:11px; margin-top:4px;">{{ $message }}</div> @enderror
        </div>

        <div class="invoice-col-1">
            <label style="font-size:12.5px; font-weight:600; color:#475569; margin-bottom:4px; display:block;">Tanggal Invoice</label>
            <input type="date" name="tanggal_invoice" id="tanggal_invoice" style="padding:9px 14px; border:1px solid {{ $errors->has('tanggal_invoice') ? '#C0201F' : '#CBD5E1' }}; border-radius:8px; font-size:13px; width:100%; outline:none; box-sizing:border-box;"
                   value="{{ old('tanggal_invoice', $isEdit ? $invoice->tanggal_invoice->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
            @error('tanggal_invoice') <div style="color:#C0201F; font-size:11px; margin-top:4px;">{{ $message }}</div> @enderror
        </div>

        <div class="invoice-col-1">
            <label style="font-size:12.5px; font-weight:600; color:#475569; margin-bottom:4px; display:block;">Tahun Anggaran</label>
            <input type="text" name="tahun_anggaran" maxlength="4" style="padding:9px 14px; border:1px solid {{ $errors->has('tahun_anggaran') ? '#C0201F' : '#CBD5E1' }}; border-radius:8px; font-size:13px; width:100%; outline:none; box-sizing:border-box;"
                   value="{{ old('tahun_anggaran', $isEdit ? $invoice->tahun_anggaran : now()->format('Y')) }}" required>
            @error('tahun_anggaran') <div style="color:#C0201F; font-size:11px; margin-top:4px;">{{ $message }}</div> @enderror
        </div>

        {{-- Dropdown Pilih Unit dari Pengajuan yang Disetujui --}}
        <div class="invoice-col-full">
            <label style="font-size:12.5px; font-weight:600; color:#475569; margin-bottom:4px; display:block;">Unit Kendaraan</label>
            <select id="select_pengajuan_unit" style="padding:10px 14px; border:1px solid {{ $errors->has('unit_id') || $errors->has('no_lambung') ? '#C0201F' : '#CBD5E1' }}; border-radius:8px; font-size:13px; width:100%; outline:none; background:#FFFFFF; box-sizing:border-box;" required>
                <option value="">-- Pilih Unit Kendaraan --</option>
                
                @if(isset($pengajuans) && $pengajuans->count() > 0)
                    @foreach ($pengajuans as $p)
                        @php
                            $parts = explode('/', $p->nomor_lambung);
                            $lambungCode = trim($parts[0] ?? '');
                            $platCode = trim($parts[1] ?? '');
                            $matched = $units->first(function($u) use ($lambungCode, $platCode, $p) {
                                return $u->nomor_lambung === $lambungCode || $u->plat_nomor === $platCode || $u->nomor_lambung === $p->nomor_lambung;
                            });
                            $unitIdVal = $matched?->id ?? ($units->first()->id ?? 1);
                            $isSelected = (string)$selectedPengajuanId === (string)$p->id || 
                                          ($isEdit && ($invoice->no_lambung === $p->nomor_lambung || $invoice->no_lambung === $lambungCode));
                            $posName = $p->pos_label ?: ($matched?->pos ?: 'Pos Dinas');
                            $jenisName = $p->jenis_kendaraan_label ?: ($matched?->merk_tipe ?: 'Unit Operasional');
                        @endphp
                        <option value="pengajuan_{{ $p->id }}"
                            data-type="pengajuan"
                            data-pengajuan-id="{{ $p->id }}"
                            data-unit-id="{{ $unitIdVal }}"
                            data-lambung="{{ $lambungCode ?: $p->nomor_lambung }}"
                            data-nopol="{{ $platCode ?: ($matched?->plat_nomor ?? '') }}"
                            data-jenismobil="{{ $jenisName }}"
                            data-lokasi="{{ $posName }}"
                            data-kode="{{ $p->kode_verifikasi }}"
                            data-pemegang="{{ $p->nama_pemegang ?: '-' }}"
                            data-tanggal="{{ $p->tanggal_keberangkatan ? $p->tanggal_keberangkatan->format('Y-m-d') : now()->format('Y-m-d') }}"
                            data-items="{{ json_encode($p->item_list ?? []) }}"
                            {{ $isSelected ? 'selected' : '' }}>
                            {{ $p->kode_verifikasi }} — {{ $p->nomor_lambung }} [{{ $posName }}] ({{ $jenisName }})
                        </option>
                    @endforeach
                @else
                    <option value="" disabled>Tidak ada pengajuan yang berstatus disetujui</option>
                @endif
            </select>

            {{-- Detail Preview Card Unit Terpilih --}}
            <div class="invoice-preview-box" id="unit-detail-box">
                <div><span style="color:#64748B;">No. Lambung:</span> <strong id="preview-lambung" style="color:#1B2A6B; display:block; font-size:13px; margin-top:2px;">-</strong></div>
                <div><span style="color:#64748B;">Plat Nomor:</span> <strong id="preview-nopol" style="color:#1B2A6B; display:block; font-size:13px; margin-top:2px;">-</strong></div>
                <div><span style="color:#64748B;">Jenis Unit:</span> <span id="preview-jenis" style="display:block; font-weight:600; color:#0F172A; margin-top:2px;">-</span></div>
                <div><span style="color:#64748B;">Lokasi/Pos:</span> <span id="preview-lokasi" style="display:block; font-weight:600; color:#0F172A; margin-top:2px;">-</span></div>
                <div><span style="color:#64748B;">Ref Surat:</span> <span id="preview-kode" style="display:inline-block; background:#E2E8F0; padding:2px 8px; border-radius:4px; font-weight:700; color:#1E293B; margin-top:2px; font-size:11px;">-</span></div>
            </div>
            @error('unit_id') <div style="color:#C0201F; font-size:11px; margin-top:4px;">{{ $message }}</div> @enderror
        </div>

        <div class="invoice-col-2">
            <label style="font-size:12.5px; font-weight:600; color:#475569; margin-bottom:4px; display:block;">Kode Rekening Belanja</label>
            <input type="text" name="kode_rekening" style="padding:9px 14px; border:1px solid {{ $errors->has('kode_rekening') ? '#C0201F' : '#CBD5E1' }}; border-radius:8px; font-size:13px; width:100%; outline:none; box-sizing:border-box;"
                   value="{{ old('kode_rekening', $isEdit ? $invoice->kode_rekening : '5.1.02.03.002.00040') }}">
            @error('kode_rekening') <div style="color:#C0201F; font-size:11px; margin-top:4px;">{{ $message }}</div> @enderror
        </div>

        <div class="invoice-col-1">
            <label style="font-size:12.5px; font-weight:600; color:#475569; margin-bottom:4px; display:block;">Status</label>
            <select name="status" style="padding:9px 14px; border:1px solid {{ $errors->has('status') ? '#C0201F' : '#CBD5E1' }}; border-radius:8px; font-size:13px; width:100%; outline:none; box-sizing:border-box; background:#FFFFFF;" required>
                @foreach (['draft' => 'Draft', 'diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'lunas' => 'Lunas'] as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $isEdit ? $invoice->status : 'disetujui') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('status') <div style="color:#C0201F; font-size:11px; margin-top:4px;">{{ $message }}</div> @enderror
        </div>

        <div class="invoice-col-full">
            <label style="font-size:12.5px; font-weight:600; color:#475569; margin-bottom:4px; display:block;">Catatan / Keterangan</label>
            <textarea name="catatan" rows="2" style="padding:9px 14px; border:1px solid {{ $errors->has('catatan') ? '#C0201F' : '#CBD5E1' }}; border-radius:8px; font-size:13px; width:100%; outline:none; resize:vertical; box-sizing:border-box;" placeholder="Catatan opsional untuk invoice ini...">{{ old('catatan', $isEdit ? $invoice->catatan : '') }}</textarea>
            @error('catatan') <div style="color:#C0201F; font-size:11px; margin-top:4px;">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="invoice-card">
    <div class="invoice-header-flex">
        <div>
            <h6 style="font-weight:700; text-transform:uppercase; color:#64748B; margin:0 0 2px 0; font-size:13px; letter-spacing:0.5px;">Rincian Item Perbaikan</h6>
            <p style="font-size:12px; color:#94A3B8; margin:0;">Item terisi otomatis dari permohonan pengajuan yang dipilih, Anda dapat menambah atau mengubah item.</p>
        </div>
        <div class="invoice-btn-group">
            <button type="button" id="btn-sync-items" style="background:#EFF6FF; color:#1B2A6B; border:1px solid #BFDBFE; padding:8px 14px; border-radius:10px; font-weight:700; font-size:12px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                <i data-lucide="refresh-cw" style="width:14px; height:14px;"></i> Isi Ulang dari Pengajuan
            </button>
            <button type="button" id="btn-add-item" style="background:#1B2A6B; color:#FFFFFF; border:none; padding:8px 16px; border-radius:10px; font-weight:700; font-size:12px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(27,42,107,0.2);">
                <i data-lucide="plus" style="width:14px; height:14px;"></i> Tambah Baris Item
            </button>
        </div>
    </div>

    @error('items') <div style="background:#FEF2F2; border:1px solid #FCA5A5; color:#C0201F; padding:10px 14px; border-radius:8px; margin-bottom:16px;">{{ $message }}</div> @enderror

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:750px;" id="items-table">
            <thead>
                <tr>
                    <th style="background:#1B2A6B; color:#FFFFFF; font-size:11.5px; font-weight:700; text-transform:uppercase; padding:10px 12px; text-align:left; width:14%;">Tanggal</th>
                    <th style="background:#1B2A6B; color:#FFFFFF; font-size:11.5px; font-weight:700; text-transform:uppercase; padding:10px 12px; text-align:left;">Jenis Perbaikan / Onderdil</th>
                    <th style="background:#1B2A6B; color:#FFFFFF; font-size:11.5px; font-weight:700; text-transform:uppercase; padding:10px 12px; text-align:left; width:10%;">Vol</th>
                    <th style="background:#1B2A6B; color:#FFFFFF; font-size:11.5px; font-weight:700; text-transform:uppercase; padding:10px 12px; text-align:left; width:11%;">Satuan</th>
                    <th style="background:#1B2A6B; color:#FFFFFF; font-size:11.5px; font-weight:700; text-transform:uppercase; padding:10px 12px; text-align:left; width:16%;">Harga Satuan (Rp)</th>
                    <th style="background:#1B2A6B; color:#FFFFFF; font-size:11.5px; font-weight:700; text-transform:uppercase; padding:10px 12px; text-align:right; width:16%;">Total (Rp)</th>
                    <th style="background:#1B2A6B; color:#FFFFFF; font-size:11.5px; font-weight:700; text-transform:uppercase; padding:10px 12px; text-align:center; width:5%;"></th>
                </tr>
            </thead>
            <tbody id="items-body">
                @foreach ($items as $i => $item)
                    <tr class="item-row">
                        <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px;">
                            <input type="date" name="items[{{ $i }}][tanggal]" style="padding:8px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; width:100%; outline:none; box-sizing:border-box;" value="{{ $item['tanggal'] }}" required>
                        </td>
                        <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px;">
                            <input type="text" name="items[{{ $i }}][jenis_perbaikan]" style="padding:8px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; width:100%; outline:none; box-sizing:border-box;" placeholder="Contoh: GANTI OLI / BOHLAM" value="{{ $item['jenis_perbaikan'] }}" required>
                        </td>
                        <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px;">
                            <input type="number" step="0.01" min="0.01" name="items[{{ $i }}][vol]" class="input-vol" style="padding:8px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; width:100%; outline:none; box-sizing:border-box;" value="{{ $item['vol'] }}" required>
                        </td>
                        <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px;">
                            <input type="text" name="items[{{ $i }}][satuan]" style="padding:8px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; width:100%; outline:none; box-sizing:border-box;" value="{{ $item['satuan'] }}" required>
                        </td>
                        <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px;">
                            <input type="number" step="0.01" min="0" name="items[{{ $i }}][harga_satuan]" class="input-harga" style="padding:8px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; width:100%; outline:none; box-sizing:border-box;" value="{{ $item['harga_satuan'] }}" required>
                        </td>
                        <td class="cell-total" style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px; text-align:right; font-weight:700; color:#1E293B;">Rp 0</td>
                        <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px; text-align:center;">
                            <button type="button" class="btn-remove-item" style="background:#FFFFFF; color:#C0201F; border:1px solid #C0201F; padding:6px 10px; border-radius:8px; font-weight:700; font-size:12.5px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;">
                                <i data-lucide="trash-2" style="width:16px; height:16px;"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#F8FAFC;">
                    <td colspan="5" style="padding:12px 14px; font-size:13px; text-align:right; font-weight:800; color:#0F172A; text-transform:uppercase;">TOTAL BIAYA INVOICE</td>
                    <td id="grand-total" style="padding:12px 14px; font-size:14px; text-align:right; font-weight:800; color:#1B2A6B;">Rp 0</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="invoice-action-footer">
    <a href="{{ route('admin.pemeliharaan.invoice.index') }}" style="background:#F1F5F9; color:#475569; border:1px solid #E2E8F0; padding:10px 20px; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; cursor:pointer;">Batal</a>
    <button type="submit" style="background:#1B2A6B; color:#FFFFFF; border:none; padding:10px 24px; border-radius:10px; font-weight:700; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 4px 12px rgba(27,42,107,0.25);">
        <i data-lucide="save" style="width:16px; height:16px;"></i> Simpan Invoice
    </button>
</div>

@push('scripts')
<script>
(function () {
    const unitSelector = document.getElementById('select_pengajuan_unit');
    const inputPengajuanId = document.getElementById('pengajuan_id');
    const inputUnitId = document.getElementById('unit_id');
    const inputNoLambung = document.getElementById('input_no_lambung');
    const inputNoPol = document.getElementById('input_no_pol');
    const inputJenisMobil = document.getElementById('input_jenis_mobil');
    const inputLokasi = document.getElementById('input_lokasi');

    const previewLambung = document.getElementById('preview-lambung');
    const previewNopol = document.getElementById('preview-nopol');
    const previewJenis = document.getElementById('preview-jenis');
    const previewLokasi = document.getElementById('preview-lokasi');
    const previewKode = document.getElementById('preview-kode');

    const itemsBody = document.getElementById('items-body');
    const addBtn = document.getElementById('btn-add-item');
    const syncBtn = document.getElementById('btn-sync-items');
    const grandTotalEl = document.getElementById('grand-total');
    let rowIndex = {{ count($items) }};

    function formatRupiah(num) {
        return 'Rp ' + (Number(num) || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
    }

    function recalcRow(row) {
        const vol = parseFloat(row.querySelector('.input-vol').value) || 0;
        const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
        const total = vol * harga;
        row.querySelector('.cell-total').textContent = formatRupiah(total);
        return total;
    }

    function recalcAll() {
        let grand = 0;
        itemsBody.querySelectorAll('.item-row').forEach(row => {
            grand += recalcRow(row);
        });
        grandTotalEl.textContent = formatRupiah(grand);
    }

    function renderRow(tgl, jenis, vol = 1, satuan = 'PCS', harga = 0) {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px;"><input type="date" name="items[${rowIndex}][tanggal]" style="padding:8px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; width:100%; outline:none;" value="${tgl || '{{ now()->format('Y-m-d') }}'}" required></td>
            <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px;"><input type="text" name="items[${rowIndex}][jenis_perbaikan]" style="padding:8px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; width:100%; outline:none;" placeholder="Contoh: GANTI OLI / BOHLAM" value="${jenis || ''}" required></td>
            <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px;"><input type="number" step="0.01" min="0.01" name="items[${rowIndex}][vol]" class="input-vol" style="padding:8px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; width:100%; outline:none;" value="${vol}" required></td>
            <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px;"><input type="text" name="items[${rowIndex}][satuan]" style="padding:8px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; width:100%; outline:none;" value="${satuan}" required></td>
            <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px;"><input type="number" step="0.01" min="0" name="items[${rowIndex}][harga_satuan]" class="input-harga" style="padding:8px 14px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; width:100%; outline:none;" value="${harga}" required></td>
            <td class="cell-total" style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px; text-align:right; font-weight:700; color:#1E293B;">${formatRupiah(vol * harga)}</td>
            <td style="padding:10px 12px; border-bottom:1px solid #E2E8F0; font-size:12.5px; text-align:center;"><button type="button" class="btn-remove-item" style="background:#FFFFFF; color:#C0201F; border:1px solid #C0201F; padding:6px 10px; border-radius:8px; font-weight:700; font-size:12.5px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;"><i data-lucide="trash-2" style="width:16px; height:16px;"></i></button></td>
        `;
        itemsBody.appendChild(tr);
        if (typeof lucide !== 'undefined') {
            lucide.createIcons({ root: tr });
        }
        rowIndex++;
    }

    function populateItemsFromSelected(opt, force = false) {
        if (!opt) return;
        let itemsData = [];
        try {
            itemsData = JSON.parse(opt.dataset.items || '[]');
        } catch(e) {
            itemsData = [];
        }

        if (!Array.isArray(itemsData) || itemsData.length === 0) return;

        const currentRows = itemsBody.querySelectorAll('.item-row');
        // Jika hanya 1 baris dan jenis_perbaikan masih kosong, atau force = true
        let isBlank = currentRows.length === 1 && !currentRows[0].querySelector('input[name*="jenis_perbaikan"]').value.trim();

        if (isBlank || force) {
            itemsBody.innerHTML = '';
            rowIndex = 0;
            const tgl = opt.dataset.tanggal || '{{ now()->format('Y-m-d') }}';
            itemsData.forEach(itemText => {
                if (typeof itemText === 'string' && itemText.trim().length > 0) {
                    renderRow(tgl, itemText.toUpperCase(), 1, 'PCS', 0);
                }
            });
            recalcAll();
        }
    }

    function updateSelection(shouldPopulateItems = false) {
        const opt = unitSelector.options[unitSelector.selectedIndex];
        if (!opt || !opt.value) {
            previewLambung.textContent = '-';
            previewNopol.textContent = '-';
            previewJenis.textContent = '-';
            previewLokasi.textContent = '-';
            previewKode.textContent = '-';
            return;
        }

        const pengajuanId = opt.dataset.pengajuanId || '';
        const unitId = opt.dataset.unitId || '';
        const lambung = opt.dataset.lambung || '';
        const nopol = opt.dataset.nopol || '';
        const jenis = opt.dataset.jenismobil || '';
        const lokasi = opt.dataset.lokasi || '';
        const kode = opt.dataset.kode || '-';

        inputPengajuanId.value = pengajuanId;
        inputUnitId.value = unitId;
        inputNoLambung.value = lambung;
        inputNoPol.value = nopol;
        inputJenisMobil.value = jenis;
        inputLokasi.value = lokasi;

        previewLambung.textContent = lambung || '-';
        previewNopol.textContent = nopol || '-';
        previewJenis.textContent = jenis || '-';
        previewLokasi.textContent = lokasi || '-';
        previewKode.textContent = kode;

        if (shouldPopulateItems) {
            populateItemsFromSelected(opt, false);
        }
    }

    addBtn.addEventListener('click', () => {
        renderRow('{{ now()->format('Y-m-d') }}', '', 1, 'PCS', 0);
        recalcAll();
    });

    syncBtn.addEventListener('click', () => {
        const opt = unitSelector.options[unitSelector.selectedIndex];
        if (!opt || !opt.value) {
            alert('Pilih unit pengajuan terlebih dahulu.');
            return;
        }
        if (confirm('Isi ulang tabel rincian dengan item dari pengajuan terpilih? (Data yang sudah diketik akan ditimpa)')) {
            populateItemsFromSelected(opt, true);
        }
    });

    itemsBody.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-remove-item');
        if (!btn) return;
        if (itemsBody.querySelectorAll('.item-row').length <= 1) {
            alert('Minimal harus ada 1 item perbaikan.');
            return;
        }
        btn.closest('tr').remove();
        recalcAll();
    });

    itemsBody.addEventListener('input', (e) => {
        if (e.target.classList.contains('input-vol') || e.target.classList.contains('input-harga')) {
            recalcAll();
        }
    });

    unitSelector.addEventListener('change', () => {
        updateSelection(true);
    });

    // Inisialisasi awal
    updateSelection(false);
    recalcAll();
})();
</script>
@endpush
