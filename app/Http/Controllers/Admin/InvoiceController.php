<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Pengajuan;
use App\Models\Unit;
use App\Services\ExcelExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    /**
     * Helper untuk menentukan apakah request saat ini adalah SPJ Pembayaran atau Aktual Pembayaran
     */
    protected function isSpjRequest(Request $request, ?Invoice $invoice = null): bool
    {
        if ($request->routeIs('admin.pemeliharaan.spj-pembayaran.*')) {
            return true;
        }
        if ($request->routeIs('admin.pemeliharaan.aktual-pembayaran.*') || $request->routeIs('admin.pemeliharaan.monitoring-aktual.*')) {
            return false;
        }
        if ($request->query('kategori') === 'spj' || $request->input('kategori_monitoring') === 'invoice') {
            return true;
        }
        if ($request->query('kategori') === 'aktual' || $request->input('kategori_monitoring') === 'aktual') {
            return false;
        }
        if ($invoice) {
            return ($invoice->kategori_monitoring ?? 'invoice') !== 'aktual';
        }
        return false;
    }

    public function index(Request $request)
    {
        $isSpj = $this->isSpjRequest($request);
        $isAktual = !$isSpj;

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

        $viewFolder = $isSpj ? 'spj-pembayaran' : 'aktual-pembayaran';

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
        $isSpj = $this->isSpjRequest($request);
        $nomorInvoice = Invoice::generateNomorInvoice(null, $isSpj ? 'INV' : 'INV-AKTUAL');
        $selectedPengajuanId = $request->query('pengajuan_id');

        $viewFolder = $isSpj ? 'spj-pembayaran' : 'aktual-pembayaran';

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
            $isSpj = $this->isSpjRequest($request);

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
                'kategori_monitoring' => $isSpj ? 'invoice' : 'aktual',
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

        $isSpj = $this->isSpjRequest($request);
        $routeTarget = $isSpj ? 'admin.pemeliharaan.spj-pembayaran.index' : 'admin.pemeliharaan.aktual-pembayaran.index';

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

        $isSpj = $this->isSpjRequest($request, $invoice);
        $viewFolder = $isSpj ? 'spj-pembayaran' : 'aktual-pembayaran';

        return view("admin.pemeliharaan.{$viewFolder}.show", compact('invoice', 'pejabatKasi'));
    }

    public function exportPdf(Request $request, Invoice $invoice)
    {
        $invoice->load('unit', 'items', 'creator');
        $pejabatKasi = \App\Models\User::where('jabatan', 'ILIKE', '%pemeliharaan sarana%')
            ->orWhere('jabatan', 'ILIKE', '%seksi pemeliharaan%')
            ->orWhere('jabatan', 'ILIKE', '%pemeliharaan%')
            ->first();

        $logoKabPath = public_path('images/logo-kabupaten.png');
        $logoDamkarPath = public_path('images/logo-damkar.png');
        $logoKabData = file_exists($logoKabPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoKabPath)) : null;
        $logoDamkarData = file_exists($logoDamkarPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoDamkarPath)) : null;

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'pejabatKasi', 'logoKabData', 'logoDamkarData'))
            ->setPaper('a4', 'portrait');

        $cleanNo = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $invoice->nomor_invoice);
        return $pdf->download("Invoice_{$cleanNo}.pdf");
    }

    public function edit(Request $request, Invoice $invoice)
    {
        $invoice->load('items');
        $pengajuans = Pengajuan::where('status', 'disetujui')->latest()->get();
        $units = Unit::orderBy('nomor_lambung')->get();

        $isSpj = $this->isSpjRequest($request, $invoice);
        $viewFolder = $isSpj ? 'spj-pembayaran' : 'aktual-pembayaran';

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

        $isSpj = $this->isSpjRequest($request, $invoice);
        $routeTarget = $isSpj ? 'admin.pemeliharaan.spj-pembayaran.index' : 'admin.pemeliharaan.aktual-pembayaran.index';

        return redirect()
            ->route($routeTarget)
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $isSpj = $this->isSpjRequest($request, $invoice);
        $invoice->delete();

        $routeTarget = $isSpj ? 'admin.pemeliharaan.spj-pembayaran.index' : 'admin.pemeliharaan.aktual-pembayaran.index';

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

    /**
     * Export data Invoice / Monitoring Pembayaran ke format Excel (.xlsx)
     */
    public function exportExcel(Request $request)
    {
        $isSpj = $this->isSpjRequest($request);
        $isAktual = !$isSpj;

        $title = $isAktual ? 'REKAP PEMBAYARAN AKTUAL (MONITORING PEMELIHARAAN)' : 'REKAP SPJ PEMBAYARAN PEMELIHARAAN';
        $filename = $isAktual ? 'Rekap_Aktual_Pembayaran_' . date('Ymd_His') : 'Rekap_SPJ_Pembayaran_' . date('Ymd_His');

        $query = Invoice::with(['unit', 'items'])
            ->when($isAktual, function ($q) {
                $q->where('kategori_monitoring', 'aktual');
            }, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('kategori_monitoring', 'invoice')
                       ->orWhereNull('kategori_monitoring');
                });
            })
            ->latest('tanggal_invoice');

        if ($search = trim($request->get('q') ?? $request->get('search') ?? '')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_invoice', 'ILIKE', "%{$search}%")
                  ->orWhere('no_pol', 'ILIKE', "%{$search}%")
                  ->orWhere('no_lambung', 'ILIKE', "%{$search}%")
                  ->orWhere('lokasi', 'ILIKE', "%{$search}%")
                  ->orWhere('nama_bengkel', 'ILIKE', "%{$search}%");
            });
        }

        if ($tahun = $request->get('tahun')) {
            $query->where(function ($q) use ($tahun) {
                $q->where('tahun_anggaran', $tahun)
                  ->orWhereYear('tanggal_invoice', $tahun);
            });
        }

        $invoices = $query->get();

        [$spreadsheet, $sheet, $startRow] = ExcelExportService::createWithHeader(
            $title,
            'Dicetak pada: ' . date('d/m/Y H:i') . ' WIB' . ($tahun ? ' | Tahun Anggaran: ' . $tahun : '')
        );

        $headers = $isAktual ? [
            'NO',
            'NO. INVOICE',
            'TANGGAL',
            'NAMA BENGKEL / VENDOR',
            'NO. POLISI',
            'NO. LAMBUNG',
            'JENIS KENDARAAN',
            'POS / LOKASI',
            'JUMLAH ITEM',
            'SUBTOTAL (RP)',
            'DISKON (RP)',
            'PAJAK / PPN (RP)',
            'BIAYA LAINNYA (RP)',
            'TOTAL BIAYA (RP)',
            'STATUS',
            'CATATAN',
        ] : [
            'NO',
            'NO. SPJ / INVOICE',
            'TANGGAL',
            'NAMA TOKO / BENGKEL',
            'NO. POLISI',
            'NO. LAMBUNG',
            'JENIS KENDARAAN',
            'POS / LOKASI',
            'TAHUN ANGGARAN',
            'JUMLAH ITEM',
            'SUBTOTAL (RP)',
            'DISKON (RP)',
            'PAJAK / PPN (RP)',
            'BIAYA LAINNYA (RP)',
            'TOTAL BIAYA (RP)',
            'STATUS',
            'CATATAN',
        ];

        ExcelExportService::setTableHeaders($sheet, $startRow, $headers);

        $row = $startRow + 1;
        $totalSubtotal  = 0;
        $totalDiskon    = 0;
        $totalPajak     = 0;
        $totalBiayaLain = 0;
        $totalBiaya     = 0;

        foreach ($invoices as $idx => $inv) {
            $tgl             = $inv->tanggal_invoice ? Carbon::parse($inv->tanggal_invoice)->translatedFormat('d/m/Y') : '—';
            $sub             = (float) ($inv->subtotal ?: $inv->items->sum('total_biaya'));
            $potonganPct     = (float) ($inv->potongan ?? 0);
            $potonganNominal = $sub * ($potonganPct / 100);
            $dpp             = max(0, $sub - $potonganNominal);
            $pajakPct        = (float) ($inv->pajak ?? 0);
            $pajakNominal    = $dpp * ($pajakPct / 100);
            $biayaLain       = (float) ($inv->biaya_lain ?? 0);
            $tot             = (float) ($inv->total_biaya ?: max(0, $dpp + $pajakNominal + $biayaLain));

            $totalSubtotal  += $sub;
            $totalDiskon    += $potonganNominal;
            $totalPajak     += $pajakNominal;
            $totalBiayaLain += $biayaLain;
            $totalBiaya     += $tot;

            $colIdx = 1;
            $sheet->setCellValue([$colIdx++, $row], $idx + 1);
            $sheet->setCellValue([$colIdx++, $row], $inv->nomor_invoice ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $tgl);
            $sheet->setCellValue([$colIdx++, $row], $inv->nama_bengkel ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $inv->no_pol ?? ($inv->unit->plat_nomor ?? '—'));
            $sheet->setCellValue([$colIdx++, $row], $inv->no_lambung ?? ($inv->unit->nomor_lambung ?? '—'));
            $sheet->setCellValue([$colIdx++, $row], $inv->jenis_mobil ?? ($inv->unit->merk_tipe ?? '—'));
            $sheet->setCellValue([$colIdx++, $row], $this->formatLokasi($inv->lokasi, $inv->unit));

            if (!$isAktual) {
                $sheet->setCellValue([$colIdx++, $row], $inv->tahun_anggaran ?? date('Y'));
            }

            $sheet->setCellValue([$colIdx++, $row], $inv->items->count());
            $sheet->setCellValue([$colIdx++, $row], $sub);
            $sheet->setCellValue([$colIdx++, $row], $potonganNominal);
            $sheet->setCellValue([$colIdx++, $row], $pajakNominal);
            $sheet->setCellValue([$colIdx++, $row], $biayaLain);
            $sheet->setCellValue([$colIdx++, $row], $tot);
            $sheet->setCellValue([$colIdx++, $row], strtoupper($inv->status ?? 'disetujui'));
            $sheet->setCellValue([$colIdx++, $row], $inv->catatan ?? '');

            $row++;
        }

        $endDataRow = $row - 1;
        $totalCols = count($headers);

        if ($endDataRow >= $startRow + 1) {
            $currencyCols = $isAktual ? [10, 11, 12, 13, 14] : [11, 12, 13, 14, 15];
            $centerCols   = $isAktual ? [1, 2, 3, 5, 6, 8, 9, 15] : [1, 2, 3, 5, 6, 8, 9, 10, 16];

            ExcelExportService::styleDataRows(
                $sheet,
                $startRow + 1,
                $endDataRow,
                $totalCols,
                $currencyCols,
                $centerCols
            );

            // Row Total
            $totalRow = $row;
            $spanColIndex = $isAktual ? 9 : 10;
            $spanColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($spanColIndex);
            $sheet->mergeCells("A{$totalRow}:{$spanColLetter}{$totalRow}");
            $sheet->setCellValue("A{$totalRow}", 'TOTAL KESELURUHAN');
            
            $startCurColIdx = $isAktual ? 10 : 11;
            $endCurColIdx   = $isAktual ? 14 : 15;
            $startCurLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCurColIdx);
            $endCurLetter   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endCurCurIdx = $endCurColIdx);

            $sheet->setCellValue("{$startCurLetter}{$totalRow}", $totalSubtotal);
            $sheet->setCellValue((\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCurColIdx + 1)) . $totalRow, $totalDiskon);
            $sheet->setCellValue((\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCurColIdx + 2)) . $totalRow, $totalPajak);
            $sheet->setCellValue((\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCurColIdx + 3)) . $totalRow, $totalBiayaLain);
            $sheet->setCellValue("{$endCurLetter}{$totalRow}", $totalBiaya);

            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
            $sheet->getStyle("A{$totalRow}:{$lastColLetter}{$totalRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '94A3B8'],
                    ],
                ],
            ]);
            $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("{$startCurLetter}{$totalRow}:{$endCurLetter}{$totalRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        }

        ExcelExportService::autoFitColumns($sheet, $totalCols);

        return ExcelExportService::streamDownload($spreadsheet, $filename);
    }
}
