<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pos;
use App\Models\Unit;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosManagementController extends Controller
{
    /**
     * Halaman Utama Data Pos (CRUD Index).
     */
    public function index(Request $request)
    {
        $searchQuery = $request->query('search', '');

        $query = Pos::orderBy('id', 'asc');

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nama', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('kode_pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('wilayah', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('alamat', 'LIKE', "%{$searchQuery}%");
            });
        }

        $posList = $query->paginate(15)->withQueryString();

        // 1. Ambil KPI seluruh unit dalam 1 query terpadu (cached 5 min)
        $kpi = CacheService::rememberStats('pos_kpi', function () {
            $kpiRaw = Unit::selectRaw("
                count(case when jenis_kendaraan = 'Pancar' then 1 end) as truck_pancar,
                count(case when jenis_kendaraan = 'R3' then 1 end) as motor_roda3,
                count(case when jenis_kendaraan = 'R2' then 1 end) as motor_roda2,
                count(case when jenis_kendaraan = 'Pompa' then 1 end) as unit_pompa,
                count(case when jenis_kendaraan = 'Rescue' then 1 end) as unit_rescue,
                count(case when jenis_kendaraan = 'Supply' then 1 end) as water_supply,
                count(case when jenis_kendaraan not in ('Pancar', 'R3', 'R2', 'Pompa', 'Rescue', 'Supply') or jenis_kendaraan is null then 1 end) as unit_lainnya,
                count(*) as total_unit
            ")->first();

            return [
                'total_pos'      => Pos::count(),
                'total_unit'     => (int) ($kpiRaw->total_unit ?? 0),
                'truck_pancar'   => (int) ($kpiRaw->truck_pancar ?? 0),
                'motor_roda3'    => (int) ($kpiRaw->motor_roda3 ?? 0),
                'motor_roda2'    => (int) ($kpiRaw->motor_roda2 ?? 0),
                'unit_pompa'     => (int) ($kpiRaw->unit_pompa ?? 0),
                'unit_rescue'    => (int) ($kpiRaw->unit_rescue ?? 0),
                'water_supply'   => (int) ($kpiRaw->water_supply ?? 0),
                'unit_lainnya'   => (int) ($kpiRaw->unit_lainnya ?? 0),
            ];
        });

        // 2. Pre-fetch distribusi unit per pos dalam 1 query GROUP BY (cached 5 min)
        $unitsByPos = CacheService::rememberStats('pos_unit_dist', function () {
            return Unit::selectRaw("
                lower(trim(pos)) as pos_key,
                count(case when jenis_kendaraan = 'Pancar' then 1 end) as truck_pancar,
                count(case when jenis_kendaraan = 'R3' then 1 end) as motor_roda3,
                count(case when jenis_kendaraan = 'R2' then 1 end) as motor_roda2,
                count(case when jenis_kendaraan = 'Pompa' then 1 end) as unit_pompa,
                count(case when jenis_kendaraan = 'Rescue' then 1 end) as unit_rescue,
                count(case when jenis_kendaraan = 'Supply' then 1 end) as water_supply,
                count(case when jenis_kendaraan not in ('Pancar', 'R3', 'R2', 'Pompa', 'Rescue', 'Supply') or jenis_kendaraan is null then 1 end) as unit_lainnya,
                count(*) as total_unit
            ")->groupBy(DB::raw('lower(trim(pos))'))->get()->keyBy('pos_key')->toArray();
        });

        // 3. Pre-fetch daftar armada kategori 'lainnya' dalam 1 query
        $unitLainnyaGrouped = Unit::where(function($q) {
                $q->whereNotIn('jenis_kendaraan', ['Pancar', 'R3', 'R2', 'Pompa', 'Rescue', 'Supply'])
                  ->orWhereNull('jenis_kendaraan');
            })
            ->get(['id', 'nomor_lambung', 'plat_nomor', 'nama', 'jenis_kendaraan', 'merk_tipe', 'pos'])
            ->groupBy(fn($u) => strtolower(trim($u->pos ?? '')));

        // 4. Pasang data precalculated ke tiap pos untuk mencegah N+1 accessor queries
        foreach ($posList as $pos) {
            $key = strtolower(trim($pos->nama ?? ''));
            $stat = is_array($unitsByPos) ? ($unitsByPos[$key] ?? null) : $unitsByPos->get($key);
            $pos->setAttribute('unit_truck_pancar', (int) (is_array($stat) ? ($stat['truck_pancar'] ?? 0) : ($stat->truck_pancar ?? 0)));
            $pos->setAttribute('unit_motor_roda3', (int) (is_array($stat) ? ($stat['motor_roda3'] ?? 0) : ($stat->motor_roda3 ?? 0)));
            $pos->setAttribute('unit_motor_roda2', (int) (is_array($stat) ? ($stat['motor_roda2'] ?? 0) : ($stat->motor_roda2 ?? 0)));
            $pos->setAttribute('unit_pompa', (int) (is_array($stat) ? ($stat['unit_pompa'] ?? 0) : ($stat->unit_pompa ?? 0)));
            $pos->setAttribute('unit_rescue', (int) (is_array($stat) ? ($stat['unit_rescue'] ?? 0) : ($stat->unit_rescue ?? 0)));
            $pos->setAttribute('unit_water_supply', (int) (is_array($stat) ? ($stat['water_supply'] ?? 0) : ($stat->water_supply ?? 0)));
            $pos->setAttribute('unit_lainnya', (int) (is_array($stat) ? ($stat['unit_lainnya'] ?? 0) : ($stat->unit_lainnya ?? 0)));
            $pos->setAttribute('total_unit', (int) (is_array($stat) ? ($stat['total_unit'] ?? 0) : ($stat->total_unit ?? 0)));
            $pos->setAttribute('unit_lainnya_list', $unitLainnyaGrouped->get($key, collect()));
        }

        return view('admin.pemeliharaan.data-pos.index', compact('posList', 'kpi', 'searchQuery'));
    }

    /**
     * Simpan data pos baru.
     */
    public function store(Request $request)
    {
        $messages = [
            'nama.required' => 'Nama pos Damkar wajib diisi.',
        ];

        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'kode_pos'  => 'nullable|string|max:100',
            'alamat'    => 'nullable|string|max:500',
            'wilayah'   => 'nullable|string|max:255',
            'telepon'   => 'nullable|string|max:100',
            'catatan'   => 'nullable|string',
        ], $messages);

        $validated['status'] = 'aktif';

        Pos::create($validated);
        CacheService::invalidate('pos');

        return redirect()
            ->route('admin.pemeliharaan.data-pos')
            ->with('success', "Data pos '{$validated['nama']}' berhasil ditambahkan.");
    }

    /**
     * Update data pos.
     */
    public function update(Request $request, $id)
    {
        $pos = Pos::findOrFail($id);

        $messages = [
            'nama.required' => 'Nama pos Damkar wajib diisi.',
        ];

        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'kode_pos'  => 'nullable|string|max:100',
            'alamat'    => 'nullable|string|max:500',
            'wilayah'   => 'nullable|string|max:255',
            'telepon'   => 'nullable|string|max:100',
            'catatan'   => 'nullable|string',
        ], $messages);

        $pos->update($validated);
        CacheService::invalidate('pos');

        return redirect()
            ->route('admin.pemeliharaan.data-pos')
            ->with('success', "Data pos '{$pos->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus data pos.
     */
    public function destroy($id)
    {
        $pos = Pos::findOrFail($id);
        $nama = $pos->nama;
        $pos->delete();
        CacheService::invalidate('pos');

        return redirect()
            ->route('admin.pemeliharaan.data-pos')
            ->with('success', "Data pos '{$nama}' berhasil dihapus.");
    }
}
