<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kejadian;
use Illuminate\Http\Request;

class MonitoringKejadianController extends Controller
{
    public function index(Request $request)
    {
        $query = Kejadian::query();

        // Filter Jenis Kejadian
        if ($request->filled('jenis')) {
            $query->where('jenis_kejadian', $request->jenis);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_kejadian', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('kategori_detail', 'like', "%{$search}%")
                  ->orWhere('nama_pelapor', 'like', "%{$search}%");
            });
        }

        $dataKejadian = $query->orderBy('waktu_kejadian', 'desc')->paginate(10);

        // Kartu Ringkasan
        $totalKejadian  = Kejadian::count();
        $totalKebakaran = Kejadian::where('jenis_kejadian', 'Kebakaran')->count();
        $totalRescue    = Kejadian::whereIn('jenis_kejadian', ['Rescue', 'Penyelamatan'])->count();
        $totalKerugian  = Kejadian::sum('estimasi_kerugian');

        return view('admin.apar-kejadian.monitoring-kejadian', compact(
            'dataKejadian', 
            'totalKejadian', 
            'totalKebakaran', 
            'totalRescue', 
            'totalKerugian'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_kejadian'   => 'required|unique:kejadian,kode_kejadian',
            'waktu_kejadian'  => 'required|date',
            'jenis_kejadian'  => 'required',
            'kategori_detail' => 'required|string',
            'lokasi'          => 'required|string',
            'status'          => 'required',
        ]);

        Kejadian::create($request->all());

        return redirect()->back()->with('success', 'Data kejadian berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $kejadian = Kejadian::findOrFail($id);
        $kejadian->delete();

        return redirect()->back()->with('success', 'Data kejadian berhasil dihapus.');
    }
}