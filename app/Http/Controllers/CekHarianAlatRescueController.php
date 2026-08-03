<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\Unit;
// use App\Models\AlatPemadam;
// use App\Models\CekHarianAlat;

class CekHarianAlatRescueController extends Controller
{
    /**
     * Menampilkan form Cek Harian Alat Pemadam.
     */
    public function index()
    {
        // Contoh data unit/kendaraan untuk dropdown (ganti dengan query Model asli)
        $unitList = collect([
            (object) ['id' => 1, 'nama' => 'Damkar 01 - Toyota Dyna'],
            (object) ['id' => 2, 'nama' => 'Damkar 02 - Hino Ranger'],
            (object) ['id' => 3, 'nama' => 'Damkar 03 - Isuzu Elf'],
        ]);

        // Daftar alat pemadam sesuai data yang diberikan (26 item, termasuk varian
        // ukuran/kapasitas yang dipisah jadi baris tersendiri: Y Connection 2 ukuran,
        // APAR 3 kapasitas). Ganti dengan query Model asli, mis. AlatPemadam::all(),
        // jika data ini nantinya disimpan di database.
        $namaAlat = 
           [
    'APAR',
'APD HAZMAT',
'APD TAWON',
'ASAP LOCK (PETZL)',
'ASCENDER',
'AUTO STOP',
'BAKRIK',
'BLOWER',
'BOLT CUTTER 36"',
'BOLT CUTTER 12"',
'CARABINER AUTO LOCK',
'CARABINER SCREW',
'CARABINER SNAP',
'CHAINSAW',
'CHEST ASCEND',
'COMPRESSOR SCBA',
'CONTAINER TOOLBOX 75 LT',
'CRIBBING',
'CUTTER SPREADER BATTERY',
'DONGKRAK',
'EMERGENCY KIT/P3K',
'FIGURE OF EIGHT (BESAR)',
'FIGURE OF EIGHT (KECIL)',
'FIRE HELMET',
'FIRE JACKET',
'FULL BODY HARNESS',
'GANJAL BAN MOBIL',
'GENSET',
'GRAB STICK',
'HAMMER BESAR',
'HAMMER KECIL',
'HAND ASCEND',
'HAND GLOVES',
'HEAD LAMP',
'HEAVY DUTY STRAP (DEREK)',
'HOOK',
'HOOLIGAN TOOLS',
'Ds',
'JACK HAMMER',
'JUMPSUIT RESCUE',
'KABEL JUMPER',
'KANTONG MAYAT',
'KNEE ASCEND',
'KOMPAN BBM',
'KUNCI RODA',
'LAMPU SENTER',
'LAMPU SOROT',
'LAMPU TRIPOD',
'LIFE JACKET (PELAMPUNG)',
'LINGGIS',
'MASKER RESPIRATOR (DRAGGER)',
'MASKER SCBA',
'MINI GRINDER',
'OXIGEN PORTABLE (KALENG)',
'PARANG',
'PELONTAR',
'PERCUSION RESCUE TOOL',
'PIPA KUNCI RODA',
'PLANA SCBA',
'PLATE HADE AXE',
'PRUSIK',
'PULLEY TANDEM',
'PUMP WEDGE',
'REEVE',
'RIGGING PLATE/PAW M',
'ROLL KABEL',
'ROTARY SAW',
'SAFETY BELT',
'SAFETY HELMET',
'SAFETY SHOES',
'SCBA SET',
'SEAT HARNESS',
'SEEM',
'SELANG + MASKER OKSIGEN',
'SINGLE PULLEY',
'SLING ANCHOR',
'SPEADER BATTERY (COMBI TOOL)',
'SPRAYER/SEMPROTAN',
'TABUNG OXIGEN',
'TABUNG SCBA (CADANGAN)',
'TALI CARNMANTEL (100 M)',
'TALI CARNMANTEL (50 M)',
'TALI PRUSIK',
'TALI SELING LORENG',
'TALI WEBBING (ROLL 4,5 M)',
'TANDU BASKET',
'TANDU LIPAT',
'TANDU SCOOP',
'TANGGA JULUR',
'TANGGA LIPAT',
'TOOL BOX',
'TRACKER',
'TRAFFIC CONE',
'TRIPOD RESCUE',
'TWIN PULLEY',
'TWIN REALEASE',

        ]; // total = 26 item

        $daftarAlat = collect($namaAlat)->map(function ($nama, $index) {
            return (object) [
                'id'     => $index + 1,
                'nama'   => $nama,
                'status' => 'baik',
            ];
        });

        return view('auth.alat-rescue.cek-harian-alat-rescue', compact('unitList', 'daftarAlat'));
    }

    /**
     * Menyimpan hasil pemeriksaan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pemeriksa'      => 'required|string|max:255',
            'jabatan'             => 'required|string|max:255',
            'unit_id'             => 'required|integer',
            'tanggal_pemeriksaan' => 'required|date',
            'alat'                => 'required|array|min:1',
            'alat.*.id'           => 'required|integer',
            'alat.*.status'       => 'required|in:baik,rusak',
            'alat.*.keterangan'   => 'nullable|string',
            'alat.*.foto'         => 'nullable|image|max:2048', // maks 2MB
        ]);

        // TODO: simpan header pemeriksaan, lalu loop $validated['alat']
        // untuk simpan tiap baris + upload foto ke storage, mis:
        //
        // foreach ($validated['alat'] as $item) {
        //     $fotoPath = null;
        //     if ($request->hasFile("alat.{$loopIndex}.foto")) {
        //         $fotoPath = $request->file("alat.{$loopIndex}.foto")->store('cek-harian-alat', 'public');
        //     }
        //     CekHarianAlat::create([...]);
        // }

        return redirect()
            ->route('alat-pemadam.cek-harian-alat')
            ->with('success', 'Pemeriksaan alat berhasil disimpan.');
    }
}