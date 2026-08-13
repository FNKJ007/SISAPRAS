<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('unit')->latest('tanggal_invoice');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_invoice', 'ilike', "%{$search}%")
                  ->orWhere('no_pol', 'ilike', "%{$search}%")
                  ->orWhere('no_lambung', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(10)->withQueryString();

        return view('admin.pemeliharaan.invoice.index', compact('invoices'));
    }

    public function create()
    {
        $units = Unit::orderBy('no_lambung')->get();
        $nomorInvoice = Invoice::generateNomorInvoice();

        return view('admin.pemeliharaan.invoice.create', compact('units', 'nomorInvoice'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvoice($request);

        DB::transaction(function () use ($validated) {
            $unit = Unit::findOrFail($validated['unit_id']);

            $invoice = Invoice::create([
                'nomor_invoice' => $validated['nomor_invoice'],
                'tanggal_invoice' => $validated['tanggal_invoice'],
                'unit_id' => $unit->id,
                'no_pol' => $unit->no_pol,
                'no_lambung' => $unit->no_lambung,
                'jenis_mobil' => $unit->jenis_mobil,
                'lokasi' => $unit->lokasi,
                'kode_rekening' => $validated['kode_rekening'] ?? null,
                'tahun_anggaran' => $validated['tahun_anggaran'],
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'tanggal' => $item['tanggal'],
                    'jenis_perbaikan' => $item['jenis_perbaikan'],
                    'vol' => $item['vol'],
                    'satuan' => $item['satuan'],
                    'harga_satuan' => $item['harga_satuan'],
                    'total_biaya' => $item['vol'] * $item['harga_satuan'],
                ]);
            }

            $invoice->recalculateTotals();
        });

        return redirect()
            ->route('admin.pemeliharaan.invoice.index')
            ->with('success', 'Invoice berhasil dibuat.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('unit', 'items', 'creator');

        return view('admin.pemeliharaan.invoice.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');
        $units = Unit::orderBy('no_lambung')->get();

        return view('admin.pemeliharaan.invoice.edit', compact('invoice', 'units'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $this->validateInvoice($request, $invoice->id);

        DB::transaction(function () use ($validated, $invoice) {
            $unit = Unit::findOrFail($validated['unit_id']);

            $invoice->update([
                'nomor_invoice' => $validated['nomor_invoice'],
                'tanggal_invoice' => $validated['tanggal_invoice'],
                'unit_id' => $unit->id,
                'no_pol' => $unit->no_pol,
                'no_lambung' => $unit->no_lambung,
                'jenis_mobil' => $unit->jenis_mobil,
                'lokasi' => $unit->lokasi,
                'kode_rekening' => $validated['kode_rekening'] ?? null,
                'tahun_anggaran' => $validated['tahun_anggaran'],
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Ganti seluruh item (paling sederhana untuk form dinamis)
            $invoice->items()->delete();

            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'tanggal' => $item['tanggal'],
                    'jenis_perbaikan' => $item['jenis_perbaikan'],
                    'vol' => $item['vol'],
                    'satuan' => $item['satuan'],
                    'harga_satuan' => $item['harga_satuan'],
                    'total_biaya' => $item['vol'] * $item['harga_satuan'],
                ]);
            }

            $invoice->recalculateTotals();
        });

        return redirect()
            ->route('admin.pemeliharaan.invoice.index')
            ->with('success', 'Invoice berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()
            ->route('admin.pemeliharaan.invoice.index')
            ->with('success', 'Invoice berhasil dihapus.');
    }

    private function validateInvoice(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nomor_invoice' => [
                'required', 'string', 'max:50',
                Rule::unique('invoices', 'nomor_invoice')->ignore($ignoreId),
            ],
            'tanggal_invoice' => ['required', 'date'],
            'unit_id' => ['required', 'exists:units,id'],
            'kode_rekening' => ['nullable', 'string', 'max:100'],
            'tahun_anggaran' => ['required', 'digits:4'],
            'status' => ['required', Rule::in(['draft', 'diajukan', 'disetujui', 'lunas'])],
            'catatan' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.tanggal' => ['required', 'date'],
            'items.*.jenis_perbaikan' => ['required', 'string', 'max:150'],
            'items.*.vol' => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan' => ['required', 'string', 'max:20'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
