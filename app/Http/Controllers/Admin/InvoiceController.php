<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Pengajuan;
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

    public function create(Request $request)
    {
        $pengajuans = Pengajuan::where('status', 'disetujui')->latest()->get();
        $units = Unit::orderBy('nomor_lambung')->get();
        $nomorInvoice = Invoice::generateNomorInvoice();
        $selectedPengajuanId = $request->query('pengajuan_id');

        return view('admin.pemeliharaan.invoice.create', compact('pengajuans', 'units', 'nomorInvoice', 'selectedPengajuanId'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvoice($request);

        DB::transaction(function () use ($validated, $request) {
            $unit = null;
            if (!empty($validated['unit_id'])) {
                $unit = Unit::find($validated['unit_id']);
            }

            $pengajuan = null;
            if ($request->filled('pengajuan_id')) {
                $pengajuan = Pengajuan::find($request->pengajuan_id);
            }

            $noLambung  = $request->input('no_lambung') ?: ($unit?->nomor_lambung ?? $pengajuan?->nomor_lambung ?? '');
            $noPol      = $request->input('no_pol') ?: ($unit?->plat_nomor ?? '');
            $jenisMobil = $request->input('jenis_mobil') ?: ($unit?->merk_tipe ?? $pengajuan?->jenis_kendaraan_label ?? '');
            $lokasi     = $request->input('lokasi') ?: ($unit?->pos ?? $pengajuan?->pos_label ?? '');

            if (!$unit && $noLambung) {
                $parts = explode('/', $noLambung);
                $lambungCode = trim($parts[0] ?? '');
                $platCode = trim($parts[1] ?? '');
                $unit = Unit::where('nomor_lambung', $lambungCode)
                    ->orWhere('plat_nomor', $platCode)
                    ->orWhere('nomor_lambung', $noLambung)
                    ->first();
            }

            $unitId = $unit ? $unit->id : (Unit::first()->id ?? 1);

            $invoice = Invoice::create([
                'nomor_invoice'   => $validated['nomor_invoice'],
                'tanggal_invoice' => $validated['tanggal_invoice'],
                'unit_id'         => $unitId,
                'no_pol'          => $noPol,
                'no_lambung'      => $noLambung,
                'jenis_mobil'     => $jenisMobil,
                'lokasi'          => $lokasi,
                'kode_rekening'   => $validated['kode_rekening'] ?? null,
                'tahun_anggaran'  => $validated['tahun_anggaran'],
                'status'          => $validated['status'],
                'catatan'         => $validated['catatan'] ?? null,
                'created_by'      => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'tanggal'         => $item['tanggal'],
                    'jenis_perbaikan' => $item['jenis_perbaikan'],
                    'vol'             => $item['vol'],
                    'satuan'          => $item['satuan'],
                    'harga_satuan'    => $item['harga_satuan'],
                    'total_biaya'     => $item['vol'] * $item['harga_satuan'],
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
        $pengajuans = Pengajuan::where('status', 'disetujui')->latest()->get();
        $units = Unit::orderBy('nomor_lambung')->get();

        return view('admin.pemeliharaan.invoice.edit', compact('invoice', 'pengajuans', 'units'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $this->validateInvoice($request, $invoice->id);

        DB::transaction(function () use ($validated, $request, $invoice) {
            $unit = null;
            if (!empty($validated['unit_id'])) {
                $unit = Unit::find($validated['unit_id']);
            }

            $pengajuan = null;
            if ($request->filled('pengajuan_id')) {
                $pengajuan = Pengajuan::find($request->pengajuan_id);
            }

            $noLambung  = $request->input('no_lambung') ?: ($unit?->nomor_lambung ?? $pengajuan?->nomor_lambung ?? $invoice->no_lambung);
            $noPol      = $request->input('no_pol') ?: ($unit?->plat_nomor ?? $invoice->no_pol);
            $jenisMobil = $request->input('jenis_mobil') ?: ($unit?->merk_tipe ?? $pengajuan?->jenis_kendaraan_label ?? $invoice->jenis_mobil);
            $lokasi     = $request->input('lokasi') ?: ($unit?->pos ?? $pengajuan?->pos_label ?? $invoice->lokasi);

            if (!$unit && $noLambung) {
                $parts = explode('/', $noLambung);
                $lambungCode = trim($parts[0] ?? '');
                $platCode = trim($parts[1] ?? '');
                $unit = Unit::where('nomor_lambung', $lambungCode)
                    ->orWhere('plat_nomor', $platCode)
                    ->orWhere('nomor_lambung', $noLambung)
                    ->first();
            }

            $unitId = $unit ? $unit->id : ($invoice->unit_id ?: (Unit::first()->id ?? 1));

            $invoice->update([
                'nomor_invoice'   => $validated['nomor_invoice'],
                'tanggal_invoice' => $validated['tanggal_invoice'],
                'unit_id'         => $unitId,
                'no_pol'          => $noPol,
                'no_lambung'      => $noLambung,
                'jenis_mobil'     => $jenisMobil,
                'lokasi'          => $lokasi,
                'kode_rekening'   => $validated['kode_rekening'] ?? null,
                'tahun_anggaran'  => $validated['tahun_anggaran'],
                'status'          => $validated['status'],
                'catatan'         => $validated['catatan'] ?? null,
            ]);

            // Ganti seluruh item
            $invoice->items()->delete();

            foreach ($validated['items'] as $item) {
                $invoice->items()->create([
                    'tanggal'         => $item['tanggal'],
                    'jenis_perbaikan' => $item['jenis_perbaikan'],
                    'vol'             => $item['vol'],
                    'satuan'          => $item['satuan'],
                    'harga_satuan'    => $item['harga_satuan'],
                    'total_biaya'     => $item['vol'] * $item['harga_satuan'],
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
            'nomor_invoice'   => [
                'required', 'string', 'max:50',
                Rule::unique('invoices', 'nomor_invoice')->ignore($ignoreId),
            ],
            'tanggal_invoice' => ['required', 'date'],
            'unit_id'         => ['nullable'],
            'pengajuan_id'    => ['nullable'],
            'no_lambung'      => ['nullable', 'string', 'max:50'],
            'no_pol'          => ['nullable', 'string', 'max:50'],
            'jenis_mobil'     => ['nullable', 'string', 'max:100'],
            'lokasi'          => ['nullable', 'string', 'max:100'],
            'kode_rekening'   => ['nullable', 'string', 'max:100'],
            'tahun_anggaran'  => ['required', 'digits:4'],
            'status'          => ['required', Rule::in(['draft', 'diajukan', 'disetujui', 'lunas'])],
            'catatan'         => ['nullable', 'string'],

            'items'                 => ['required', 'array', 'min:1'],
            'items.*.tanggal'       => ['required', 'date'],
            'items.*.jenis_perbaikan' => ['required', 'string', 'max:150'],
            'items.*.vol'           => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan'        => ['required', 'string', 'max:20'],
            'items.*.harga_satuan'  => ['required', 'numeric', 'min:0'],
        ]);
    }
}
