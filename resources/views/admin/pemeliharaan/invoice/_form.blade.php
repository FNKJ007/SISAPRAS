{{-- resources/views/admin/pemeliharaan/invoice/_form.blade.php --}}
@php
    // $invoice ada saat mode edit, null saat mode create
    $isEdit = isset($invoice);
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

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-bold text-uppercase text-muted mb-3">Informasi Invoice</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Nomor Invoice</label>
                <input type="text" name="nomor_invoice" class="form-control @error('nomor_invoice') is-invalid @enderror"
                       value="{{ old('nomor_invoice', $isEdit ? $invoice->nomor_invoice : $nomorInvoice) }}" required>
                @error('nomor_invoice') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Tanggal Invoice</label>
                <input type="date" name="tanggal_invoice" class="form-control @error('tanggal_invoice') is-invalid @enderror"
                       value="{{ old('tanggal_invoice', $isEdit ? $invoice->tanggal_invoice->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                @error('tanggal_invoice') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Tahun Anggaran</label>
                <input type="text" name="tahun_anggaran" maxlength="4" class="form-control @error('tahun_anggaran') is-invalid @enderror"
                       value="{{ old('tahun_anggaran', $isEdit ? $invoice->tahun_anggaran : now()->format('Y')) }}" required>
                @error('tahun_anggaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Unit Kendaraan</label>
                <select name="unit_id" id="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Unit --</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}"
                            data-nopol="{{ $unit->no_pol }}"
                            data-lambung="{{ $unit->no_lambung }}"
                            data-lokasi="{{ $unit->lokasi }}"
                            {{ (string) old('unit_id', $isEdit ? $invoice->unit_id : '') === (string) $unit->id ? 'selected' : '' }}>
                            {{ $unit->no_lambung }} — {{ $unit->no_pol }} ({{ $unit->jenis_mobil }})
                        </option>
                    @endforeach
                </select>
                @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text" id="unit-preview">Lokasi: <span id="preview-lokasi">-</span></div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Kode Rekening Belanja</label>
                <input type="text" name="kode_rekening" class="form-control @error('kode_rekening') is-invalid @enderror"
                       value="{{ old('kode_rekening', $isEdit ? $invoice->kode_rekening : '5.1.02.03.002.00040') }}">
                @error('kode_rekening') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    @foreach (['draft' => 'Draft', 'diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'lunas' => 'Lunas'] as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $isEdit ? $invoice->status : 'draft') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label class="form-label">Catatan</label>
                <textarea name="catatan" rows="2" class="form-control @error('catatan') is-invalid @enderror">{{ old('catatan', $isEdit ? $invoice->catatan : '') }}</textarea>
                @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-uppercase text-muted mb-0">Rincian Item Perbaikan</h6>
            <button type="button" id="btn-add-item" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-lg"></i> Tambah Item
            </button>
        </div>

        @error('items') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="items-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:14%">Tanggal</th>
                        <th>Jenis Perbaikan</th>
                        <th style="width:9%">Vol</th>
                        <th style="width:10%">Satuan</th>
                        <th style="width:16%">Harga Satuan (Rp)</th>
                        <th style="width:16%">Total (Rp)</th>
                        <th style="width:5%"></th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    @foreach ($items as $i => $item)
                        <tr class="item-row">
                            <td>
                                <input type="date" name="items[{{ $i }}][tanggal]" class="form-control form-control-sm" value="{{ $item['tanggal'] }}" required>
                            </td>
                            <td>
                                <input type="text" name="items[{{ $i }}][jenis_perbaikan]" class="form-control form-control-sm" placeholder="Contoh: BOHLAM" value="{{ $item['jenis_perbaikan'] }}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0.01" name="items[{{ $i }}][vol]" class="form-control form-control-sm input-vol" value="{{ $item['vol'] }}" required>
                            </td>
                            <td>
                                <input type="text" name="items[{{ $i }}][satuan]" class="form-control form-control-sm" value="{{ $item['satuan'] }}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[{{ $i }}][harga_satuan]" class="form-control form-control-sm input-harga" value="{{ $item['harga_satuan'] }}" required>
                            </td>
                            <td class="text-end fw-semibold cell-total">Rp 0</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">TOTAL BIAYA</td>
                        <td class="text-end fw-bold" id="grand-total">Rp 0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('admin.pemeliharaan.invoice.index') }}" class="btn btn-light border">Batal</a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> Simpan Invoice
    </button>
</div>

@push('scripts')
<script>
(function () {
    const unitSelect = document.getElementById('unit_id');
    const previewLokasi = document.getElementById('preview-lokasi');
    const itemsBody = document.getElementById('items-body');
    const addBtn = document.getElementById('btn-add-item');
    const grandTotalEl = document.getElementById('grand-total');
    let rowIndex = {{ count($items) }};

    function formatRupiah(num) {
        return 'Rp ' + (Number(num) || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
    }

    function updateUnitPreview() {
        const opt = unitSelect.options[unitSelect.selectedIndex];
        previewLokasi.textContent = opt ? (opt.dataset.lokasi || '-') : '-';
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

    function newRow() {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td><input type="date" name="items[${rowIndex}][tanggal]" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required></td>
            <td><input type="text" name="items[${rowIndex}][jenis_perbaikan]" class="form-control form-control-sm" placeholder="Contoh: BOHLAM" required></td>
            <td><input type="number" step="0.01" min="0.01" name="items[${rowIndex}][vol]" class="form-control form-control-sm input-vol" value="1" required></td>
            <td><input type="text" name="items[${rowIndex}][satuan]" class="form-control form-control-sm" value="PCS" required></td>
            <td><input type="number" step="0.01" min="0" name="items[${rowIndex}][harga_satuan]" class="form-control form-control-sm input-harga" value="0" required></td>
            <td class="text-end fw-semibold cell-total">Rp 0</td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button></td>
        `;
        itemsBody.appendChild(tr);
        rowIndex++;
    }

    addBtn.addEventListener('click', () => {
        newRow();
    });

    itemsBody.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-remove-item');
        if (!btn) return;
        if (itemsBody.querySelectorAll('.item-row').length <= 1) {
            alert('Minimal harus ada 1 item.');
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

    unitSelect.addEventListener('change', updateUnitPreview);

    updateUnitPreview();
    recalcAll();
})();
</script>
@endpush
