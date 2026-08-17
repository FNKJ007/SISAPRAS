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

        // ===================== DASHBOARD MONITORING =====================
        $units = Unit::orderBy('nomor_lambung')->get();

        $bulanList = [
            '01' => 'JAN', '02' => 'FEB', '03' => 'MAR', '04' => 'APR',
            '05' => 'MEI', '06' => 'JUN', '07' => 'JUL', '08' => 'AGS',
            '09' => 'SEP', '10' => 'OKT', '11' => 'NOV', '12' => 'DES',
        ];

        $availableTahun = Invoice::whereNotNull('tahun_anggaran')
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran')
            ->filter()
            ->values()
            ->toArray();
        if (empty($availableTahun)) {
            $availableTahun = [date('Y')];
        }

        $selectedTahun   = $request->query('tahun') ?: $availableTahun[0];
        $selectedUnitIds = array_values(array_filter((array) $request->query('unit', [])));
        $selectedBulan   = array_values(array_filter((array) $request->query('bulan', [])));

        $selectedLambungs = Unit::whereIn('id', $selectedUnitIds)->pluck('nomor_lambung')->filter()->toArray();

        $dashboardInvoices = Invoice::with('unit')
            ->where(function ($q) use ($selectedTahun) {
                $q->where('tahun_anggaran', $selectedTahun)
                  ->orWhereYear('tanggal_invoice', $selectedTahun);
            })
            ->when(!empty($selectedUnitIds), function ($q) use ($selectedUnitIds, $selectedLambungs) {
                $q->where(function ($qq) use ($selectedUnitIds, $selectedLambungs) {
                    $qq->whereIn('unit_id', $selectedUnitIds);
                    if (!empty($selectedLambungs)) {
                        $qq->orWhereIn('no_lambung', $selectedLambungs);
                    }
                });
            })
            ->when(!empty($selectedBulan), function ($q) use ($selectedBulan) {
                $q->where(function ($qq) use ($selectedBulan) {
                    foreach ($selectedBulan as $bulan) {
                        $qq->orWhereMonth('tanggal_invoice', (int) $bulan);
                    }
                });
            })
            ->get();

        $dashboardTotalAnggaran = (float) $dashboardInvoices->sum('total_biaya');
        $dashboardTotalUnit     = $dashboardInvoices->pluck('unit_id')->filter()->unique()->count();

        $biayaPerUnit = $dashboardInvoices
            ->groupBy('unit_id')
            ->map(function ($group) {
                $first = $group->first();
                $label = optional($first->unit)->nomor_lambung ?: ($first->no_lambung ?: '—');

                return [
                    'label' => $label,
                    'total' => (float) $group->sum('total_biaya'),
                ];
            })
            ->sortBy('label')
            ->values();

        $bulanKeys = !empty($selectedBulan) ? $selectedBulan : array_keys($bulanList);
        sort($bulanKeys);

        $biayaPerBulan = collect($bulanKeys)->map(function ($bulanKey) use ($dashboardInvoices, $bulanList) {
            $total = $dashboardInvoices
                ->filter(fn ($inv) => $inv->tanggal_invoice && $inv->tanggal_invoice->format('m') === $bulanKey)
                ->sum('total_biaya');

            return [
                'label' => ($bulanList[$bulanKey] ?? $bulanKey),
                'total' => (float) $total,
            ];
        })->values();

        return view('admin.pemeliharaan.invoice.index', compact(
            'invoices',
            'units',
            'bulanList',
            'availableTahun',
            'selectedTahun',
            'selectedUnitIds',
            'selectedBulan',
            'dashboardTotalAnggaran',
            'dashboardTotalUnit',
            'biayaPerUnit',
            'biayaPerBulan'
        ));
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
            $rawLokasi  = $request->input('lokasi') ?: ($unit?->pos ?? $pengajuan?->pos_label ?? '');
            $lokasi     = $this->formatLokasi($rawLokasi, $unit);

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
                    'jenis_perbaikan' => ucwords(strtolower(trim($item['jenis_perbaikan']))),
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
            $rawLokasi  = $request->input('lokasi') ?: ($unit?->pos ?? $pengajuan?->pos_label ?? $invoice->lokasi);
            $lokasi     = $this->formatLokasi($rawLokasi, $unit);

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
                    'jenis_perbaikan' => ucwords(strtolower(trim($item['jenis_perbaikan']))),
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
        $messages = [
            'nomor_invoice.required'           => 'Nomor invoice / kuitansi wajib diisi.',
            'nomor_invoice.unique'             => 'Nomor invoice ini sudah pernah digunakan.',
            'tanggal_invoice.required'         => 'Tanggal invoice wajib diisi.',
            'tanggal_invoice.date'             => 'Format tanggal invoice tidak valid.',
            'tahun_anggaran.required'          => 'Tahun anggaran wajib diisi.',
            'tahun_anggaran.digits'            => 'Tahun anggaran harus 4 digit angka (contoh: 2026).',
            'status.required'                  => 'Status invoice wajib dipilih.',
            'items.required'                   => 'Rincian perbaikan / suku cadang minimal 1 item.',
            'items.min'                        => 'Rincian perbaikan / suku cadang minimal 1 item.',
            'items.*.tanggal.required'         => 'Tanggal item perbaikan wajib diisi.',
            'items.*.jenis_perbaikan.required' => 'Uraian jenis perbaikan wajib diisi.',
            'items.*.vol.required'             => 'Volume / jumlah item wajib diisi.',
            'items.*.satuan.required'          => 'Satuan item (pcs/unit/stel) wajib diisi.',
            'items.*.harga_satuan.required'     => 'Harga satuan barang/jasa wajib diisi.',
        ];

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
        ], $messages);
    }

    private function formatLokasi(?string $rawLokasi, ?Unit $unit = null): string
    {
        $input = trim($rawLokasi ?? '');
        if (empty($input) && $unit) {
            $input = trim($unit->pos ?? '');
        }

        if (empty($input)) return '—';

        // Match against official Pos model
        $posList = \App\Models\Pos::all();
        $matched = $posList->first(function ($p) use ($input) {
            $pNameClean = strtolower(str_replace([' ', '(', ')', '-'], '', $p->nama));
            $inputClean = strtolower(str_replace([' ', '(', ')', '-'], '', $input));
            return $pNameClean === $inputClean || str_contains($pNameClean, $inputClean) || str_contains($inputClean, $pNameClean);
        });

        if ($matched) {
            return $matched->nama;
        }

        if (str_starts_with(strtolower($input), 'pos ')) {
            $input = trim(substr($input, 4));
        }

        return ucwords(strtolower($input));
    }
}
