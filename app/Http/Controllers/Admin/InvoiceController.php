<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Pengajuan;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\InvoiceItem;

class InvoiceController extends Controller
{
    /**
     * Auto-sync single Pengajuan to Invoice for Monitoring Aktual
     */
    public static function syncPengajuanToAktualInvoice(Pengajuan $p): Invoice
    {
        $cleanLambung = trim(explode('/', $p->nomor_lambung)[0]);
        $unit = Unit::where('nomor_lambung', $cleanLambung)->first()
             ?? Unit::where('nomor_lambung', 'ILIKE', "%{$cleanLambung}%")->first()
             ?? Unit::first();

        $unitId = $unit ? $unit->id : 1;
        $kodeVerif = $p->kode_verifikasi ?? ('HAR-' . ($p->created_at ? $p->created_at->format('Ymd') : date('Ymd')) . '-' . sprintf('%04d', $p->id));
        $nomorInvoice = str_starts_with($kodeVerif, 'HAR-')
            ? 'INV-SPJ-' . substr($kodeVerif, 4)
            : 'INV-SPJ-' . $kodeVerif;

        $inv = Invoice::firstOrCreate(
            [
                'pengajuan_id' => $p->id,
                'kategori_monitoring' => 'aktual',
            ],
            [
                'nomor_invoice'    => $nomorInvoice,
                'tanggal_invoice'  => $p->created_at ? $p->created_at->format('Y-m-d') : date('Y-m-d'),
                'nama_bengkel'     => 'CV. Pratama Motor',
                'unit_id'          => $unitId,
                'no_pol'           => $unit ? $unit->plat_nomor : ($p->plat ?? ''),
                'no_lambung'       => $unit ? $unit->nomor_lambung : ($p->nomor_lambung ?? ''),
                'jenis_mobil'      => $unit ? $unit->merk_tipe : ($p->jenis_kendaraan ?? ''),
                'lokasi'           => $p->pos ?? ($unit ? $unit->pos_penempatan : ''),
                'kode_rekening'    => '5.1.02.03.02.0035',
                'tahun_anggaran'   => $p->created_at ? $p->created_at->format('Y') : date('Y'),
                'subtotal'         => 0,
                'total_biaya'      => 0,
                'status'           => 'disetujui',
                'catatan'          => 'Otomatis bersumber dari Pengajuan ' . $kodeVerif,
            ]
        );

        if ($inv->wasRecentlyCreated && $inv->items()->count() === 0) {
            $itemsList = is_array($p->item_list) ? $p->item_list : explode("\n", $p->item_perbaikan ?? '');
            $cleanItems = array_values(array_filter(array_map('trim', $itemsList)));

            $subtotal = 0;
            foreach ($cleanItems as $idx => $itemText) {
                if (!$itemText) continue;
                $hargaSatuan = 500000;
                InvoiceItem::create([
                    'invoice_id'      => $inv->id,
                    'tanggal'         => $inv->tanggal_invoice,
                    'kode_item'       => 'P-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                    'jenis_perbaikan' => $itemText,
                    'vol'             => 1,
                    'satuan'          => 'Pcs',
                    'harga_satuan'    => $hargaSatuan,
                    'potongan_persen' => 0,
                    'total_biaya'     => $hargaSatuan,
                ]);
                $subtotal += $hargaSatuan;
            }

            $inv->subtotal = $subtotal;
            $inv->total_biaya = $subtotal;
            $inv->save();
        }

        return $inv;
    }

    public function index(Request $request)
    {
        $isAktual = $request->routeIs('admin.pemeliharaan.spj-pembayaran.*') || $request->routeIs('admin.pemeliharaan.monitoring-aktual.*');

        $query = Invoice::with('unit')
            ->when($isAktual, function ($q) {
                $q->where('kategori_monitoring', 'aktual');
            }, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('kategori_monitoring', 'invoice')
                       ->orWhereNull('kategori_monitoring');
                });
            })
            ->latest('tanggal_invoice');

        if ($search = trim($request->get('q') ?? '')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_invoice', 'ILIKE', "%{$search}%")
                  ->orWhere('no_pol', 'ILIKE', "%{$search}%")
                  ->orWhere('no_lambung', 'ILIKE', "%{$search}%")
                  ->orWhere('lokasi', 'ILIKE', "%{$search}%");
            });
        }

        $invoices = $query->paginate(10)->withQueryString();

        // ===================== DASHBOARD MONITORING =====================
        $units = Unit::orderBy('nomor_lambung')->get();

        $bulanList = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agt',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
        ];

        $availableTahun = Invoice::whereNotNull('tahun_anggaran')
            ->when($isAktual, function ($q) {
                $q->where('kategori_monitoring', 'aktual');
            }, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('kategori_monitoring', 'invoice')
                       ->orWhereNull('kategori_monitoring');
                });
            })
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
            ->when($isAktual, function ($q) {
                $q->where('kategori_monitoring', 'aktual');
            }, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('kategori_monitoring', 'invoice')
                       ->orWhereNull('kategori_monitoring');
                });
            })
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

        $viewFolder = $isAktual ? 'spj-pembayaran' : 'aktual-pembayaran';

        return view("admin.pemeliharaan.{$viewFolder}.index", compact(
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
        $isAktual = $request->routeIs('admin.pemeliharaan.spj-pembayaran.*') || $request->routeIs('admin.pemeliharaan.monitoring-aktual.*');
        $nomorInvoice = Invoice::generateNomorInvoice(null, $isAktual ? 'INV-SPJ' : 'INV');
        $selectedPengajuanId = $request->query('pengajuan_id');

        $viewFolder = $isAktual ? 'spj-pembayaran' : 'aktual-pembayaran';

        return view("admin.pemeliharaan.{$viewFolder}.create", compact('pengajuans', 'units', 'nomorInvoice', 'selectedPengajuanId'));
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
            $isAktual = $request->routeIs('admin.pemeliharaan.spj-pembayaran.*') || $request->routeIs('admin.pemeliharaan.monitoring-aktual.*');

            $invoice = Invoice::create([
                'nomor_invoice'       => $validated['nomor_invoice'],
                'tanggal_invoice'     => $validated['tanggal_invoice'],
                'nama_bengkel'        => $request->input('nama_bengkel') ?: 'CV. PRATAMA MOTOR',
                'unit_id'             => $unitId,
                'no_pol'              => $noPol,
                'no_lambung'          => $noLambung,
                'jenis_mobil'         => $jenisMobil,
                'lokasi'              => $lokasi,
                'kode_rekening'       => $validated['kode_rekening'] ?? null,
                'tahun_anggaran'      => $validated['tahun_anggaran'],
                'potongan'            => $validated['potongan'] ?? 0,
                'pajak'               => $validated['pajak'] ?? 0,
                'biaya_lain'          => $validated['biaya_lain'] ?? 0,
                'status'              => $validated['status'] ?? $request->input('status', 'disetujui'),
                'kategori_monitoring' => $isAktual ? 'aktual' : 'invoice',
                'pengajuan_id'        => $request->input('pengajuan_id'),
                'catatan'             => $validated['catatan'] ?? null,
                'created_by'          => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $vol = (float) $item['vol'];
                $harga = (float) $item['harga_satuan'];
                $discPct = (float) ($item['potongan_persen'] ?? 0);
                $totalItem = ($vol * $harga) * (1 - ($discPct / 100));

                $invoice->items()->create([
                    'tanggal'         => $validated['tanggal_invoice'],
                    'kode_item'       => !empty($item['kode_item']) ? strtoupper(trim($item['kode_item'])) : null,
                    'jenis_perbaikan' => ucwords(strtolower(trim($item['jenis_perbaikan']))),
                    'vol'             => $vol,
                    'satuan'          => ucwords(strtolower(trim($item['satuan']))),
                    'harga_satuan'    => $harga,
                    'potongan_persen' => $discPct,
                    'total_biaya'     => $totalItem,
                ]);
            }

            $invoice->recalculateTotals();
        });

        $isAktual = $request->routeIs('admin.pemeliharaan.spj-pembayaran.*') || $request->routeIs('admin.pemeliharaan.monitoring-aktual.*');
        $routeTarget = $isAktual ? 'admin.pemeliharaan.spj-pembayaran.index' : 'admin.pemeliharaan.aktual-pembayaran.index';

        return redirect()
            ->route($routeTarget)
            ->with('success', 'Data berhasil dibuat.');
    }

    public function show(Request $request, Invoice $invoice)
    {
        $invoice->load('unit', 'items', 'creator');
        $pejabatKasi = \App\Models\User::where('jabatan', 'ILIKE', '%pemeliharaan sarana%')
            ->orWhere('jabatan', 'ILIKE', '%seksi pemeliharaan%')
            ->orWhere('jabatan', 'ILIKE', '%pemeliharaan%')
            ->first();

        $isAktual = $request->routeIs('admin.pemeliharaan.spj-pembayaran.*') || $request->routeIs('admin.pemeliharaan.monitoring-aktual.*');
        $viewFolder = $isAktual ? 'spj-pembayaran' : 'aktual-pembayaran';

        return view("admin.pemeliharaan.{$viewFolder}.show", compact('invoice', 'pejabatKasi'));
    }

    public function edit(Request $request, Invoice $invoice)
    {
        $invoice->load('items');
        $pengajuans = Pengajuan::where('status', 'disetujui')->latest()->get();
        $units = Unit::orderBy('nomor_lambung')->get();

        $isAktual = $request->routeIs('admin.pemeliharaan.spj-pembayaran.*') || $request->routeIs('admin.pemeliharaan.monitoring-aktual.*');
        $viewFolder = $isAktual ? 'spj-pembayaran' : 'aktual-pembayaran';

        return view("admin.pemeliharaan.{$viewFolder}.edit", compact('invoice', 'pengajuans', 'units'));
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
                'nama_bengkel'    => $request->input('nama_bengkel') ?: ($invoice->nama_bengkel ?: 'CV. PRATAMA MOTOR'),
                'unit_id'         => $unitId,
                'no_pol'          => $noPol,
                'no_lambung'      => $noLambung,
                'jenis_mobil'     => $jenisMobil,
                'lokasi'          => $lokasi,
                'kode_rekening'   => $validated['kode_rekening'] ?? null,
                'tahun_anggaran'  => $validated['tahun_anggaran'],
                'potongan'        => $validated['potongan'] ?? 0,
                'pajak'           => $validated['pajak'] ?? 0,
                'biaya_lain'      => $validated['biaya_lain'] ?? 0,
                'status'          => $validated['status'] ?? $request->input('status', $invoice->status ?? 'disetujui'),
                'catatan'         => $validated['catatan'] ?? null,
            ]);

            // Ganti seluruh item
            $invoice->items()->delete();

            foreach ($validated['items'] as $item) {
                $vol = (float) $item['vol'];
                $harga = (float) $item['harga_satuan'];
                $discPct = (float) ($item['potongan_persen'] ?? 0);
                $totalItem = ($vol * $harga) * (1 - ($discPct / 100));

                $invoice->items()->create([
                    'tanggal'         => $validated['tanggal_invoice'],
                    'kode_item'       => !empty($item['kode_item']) ? strtoupper(trim($item['kode_item'])) : null,
                    'jenis_perbaikan' => ucwords(strtolower(trim($item['jenis_perbaikan']))),
                    'vol'             => $vol,
                    'satuan'          => ucwords(strtolower(trim($item['satuan']))),
                    'harga_satuan'    => $harga,
                    'potongan_persen' => $discPct,
                    'total_biaya'     => $totalItem,
                ]);
            }

            $invoice->recalculateTotals();
        });

        $isAktual = $request->routeIs('admin.pemeliharaan.spj-pembayaran.*') || $request->routeIs('admin.pemeliharaan.monitoring-aktual.*');
        $routeTarget = $isAktual ? 'admin.pemeliharaan.spj-pembayaran.index' : 'admin.pemeliharaan.aktual-pembayaran.index';

        return redirect()
            ->route($routeTarget)
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $isAktual = $request->routeIs('admin.pemeliharaan.spj-pembayaran.*') || $request->routeIs('admin.pemeliharaan.monitoring-aktual.*');
        $invoice->delete();

        $routeTarget = $isAktual ? 'admin.pemeliharaan.spj-pembayaran.index' : 'admin.pemeliharaan.aktual-pembayaran.index';

        return redirect()
            ->route($routeTarget)
            ->with('success', 'Data berhasil dihapus.');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status' => 'required|in:draft,diajukan,disetujui,lunas',
        ]);

        $invoice->update(['status' => $request->status]);

        $statusLabels = [
            'draft'     => 'Draft',
            'diajukan'  => 'Diajukan',
            'disetujui' => 'Disetujui',
            'lunas'     => 'Lunas',
        ];

        return redirect()->back()->with('success', 'Status Invoice ' . $invoice->nomor_invoice . ' berhasil diubah menjadi ' . ($statusLabels[$request->status] ?? $request->status) . '.');
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
            'items.required'                   => 'Rincian perbaikan / suku cadang minimal 1 item.',
            'items.min'                        => 'Rincian perbaikan / suku cadang minimal 1 item.',
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
            'nama_bengkel'    => ['nullable', 'string', 'max:150'],
            'unit_id'         => ['nullable'],
            'pengajuan_id'    => ['nullable'],
            'no_lambung'      => ['nullable', 'string', 'max:50'],
            'no_pol'          => ['nullable', 'string', 'max:50'],
            'jenis_mobil'     => ['nullable', 'string', 'max:100'],
            'lokasi'          => ['nullable', 'string', 'max:100'],
            'kode_rekening'   => ['nullable', 'string', 'max:100'],
            'tahun_anggaran'  => ['required', 'digits:4'],
            'potongan'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pajak'           => ['nullable', 'numeric', 'min:0', 'max:100'],
            'biaya_lain'      => ['nullable', 'numeric', 'min:0'],
            'status'          => ['nullable', Rule::in(['draft', 'diajukan', 'disetujui', 'lunas'])],
            'catatan'         => ['nullable', 'string'],

            'items'                 => ['required', 'array', 'min:1'],
            'items.*.kode_item'     => ['nullable', 'string', 'max:50'],
            'items.*.jenis_perbaikan' => ['required', 'string', 'max:150'],
            'items.*.vol'           => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan'        => ['required', 'string', 'max:20'],
            'items.*.harga_satuan'  => ['required', 'numeric', 'min:0'],
            'items.*.potongan_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
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
