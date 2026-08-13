<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Peralatan;

class PeralatanSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kosongkan seluruh data peralatan
        Peralatan::truncate();

        // 2. Peralatan Pemadam (26 Item)
        $pemadamItems = [
            'SELANG KANVAS 1,5"',
            'SELANG KANVAS 2,5"',
            'SELANG RUBBER 1,5"',
            'SELANG RUBBER 2,5"',
            'NOZZLE GUN 1,5"',
            'NOZZLE GUN 2,5"',
            'NOZZLE VARIABEL / NOZZLE JET 1,5"',
            'NOZZLE VARIABEL / NOZZLE JET 2,5"',
            'NOZZLE FOAM',
            'Y CONNECTION/ADAPTOR 2,5" X 1,5"',
            'Y CONNECTION/ADAPTOR 2,5" X 2,5"',
            'POMPA PORTABLE',
            'SELANG HISAP POMPA PORTABLE',
            'TANGKI AIR PORTABLE',
            'FLOATING PUMP / POMPA APUNG',
            'FIRE BLANKET / SELIMUT API',
            'BAKRIK',
            'SELANG HISAP PTO',
            'KUNCI SELANG HISAP',
            'JET SHOOTER',
            'ALAT PEMADAM API RINGAN (APAR) 3KG',
            'ALAT PEMADAM API RINGAN (APAR) 6KG',
            'ALAT PEMADAM API RINGAN (APAR) 9KG',
            'EXHAUSE PORTABLE',
            'GAS DETECTOR KAMERA',
            'TANGGA',
        ];
        natcasesort($pemadamItems);
        foreach (array_values($pemadamItems) as $nama) {
            Peralatan::create([
                'nama'         => $nama,
                'kategori'     => 'pemadam',
                'jumlah_total' => 5,
                'status'       => 'baik',
                'catatan'      => 'Peralatan operasional unit pemadam.',
            ]);
        }

        // 3. Peralatan Command Center (13 Item)
        $commandCenterItems = [
            'Telepon',
            'Tablet / Handphone',
            'Handy Talky (HT)',
            'Walky Talky',
            'Radio RIG',
            'Komputer Operasional',
            'Speaker Komputer',
            'UPS',
            'Headphone / Headset',
            'Megaphone (TOA)',
            'TV Monitoring',
            'Monitor Display',
            'Laser Distance Meter',
        ];
        natcasesort($commandCenterItems);
        foreach (array_values($commandCenterItems) as $nama) {
            Peralatan::create([
                'nama'         => $nama,
                'kategori'     => 'command_center',
                'jumlah_total' => 2,
                'status'       => 'baik',
                'catatan'      => 'Peralatan operasional ruang Command Center.',
            ]);
        }

        // 4. Daftar 96 Peralatan Rescue Resmi dari Dinas Damkar & Penyelamatan
        $rescueItems = [
            'Apar',
            'APD Hazmat',
            'APD Tawon',
            'Asap lock (Petzl)',
            'Ascender',
            'Auto Stop',
            'Bakrik',
            'Blower',
            'Bolt Cutter 36"',
            'Bolt Cutter 12"',
            'Carabiner Auto Lock',
            'Carabiner Screw',
            'Carabiner Snap',
            'Chainsaw',
            'Chest Ascend',
            'Compressor SCBA',
            'Container Toolbox 75 lt',
            'Cribbing',
            'Cutter Spreader Battery',
            'Dongkrak',
            'Emergency Kit/P3K',
            'Figure of Eight (Besar)',
            'Figure of Eight (Kecil)',
            'Fire Helmet',
            'Fire Jacket',
            'Full Body Harness',
            'Ganjal Ban Mobil',
            'Genset',
            'Grab Stick',
            'Hammer Besar',
            'Hammer Kecil',
            'Hand Ascend',
            'Hand Gloves',
            'Head lamp',
            'Heavy Duty Strap (Derek)',
            'Hook',
            'Hooligan Tools',
            "I'Ds",
            'Jack Hammer',
            'Jumpsuit Rescue',
            'Kabel Jumper',
            'Kantong Mayat',
            'Knee Ascend',
            'Kompan BBM',
            'Kunci Roda',
            'Lampu Senter',
            'Lampu Sorot',
            'Lampu Tripod',
            'Life Jacket (Pelampung)',
            'Linggis',
            'Masker Respirator (Dragger)',
            'Masker SCBA',
            'Mini Grinder',
            'Oxigen portable (kaleng)',
            'Parang',
            'Pelontar',
            'Percusion Rescue Tool',
            'Pipa Kunci roda',
            'Plana SCBA',
            'Plate Hade AXE',
            'Prusik',
            'Pulley tandem',
            'Pump Wedge',
            'Reeve',
            'Rigging Plate/PAW M',
            'Roll Kabel',
            'Rotary saw',
            'Safety Belt',
            'Safety Helmet',
            'Safety Shoes',
            'SCBA set',
            'Seat harness',
            'Seem',
            'Selang + Masker Oksigen',
            'Single Pulley',
            'Sling Anchor',
            'Speader Battery (Combi Tool)',
            'Sprayer/Semprotan',
            'Tabung Oxigen',
            'Tabung SCBA (cadangan)',
            'Tali Carnmantel ( 100 M )',
            'Tali Carnmantel ( 50 M )',
            'Tali Prusik',
            'Tali Seling loreng',
            'Tali Webbing (Roll 4,5m)',
            'Tandu Basket',
            'Tandu Lipat',
            'Tandu Scoop',
            'Tangga Julur',
            'Tangga Lipat',
            'Tool Box',
            'Tracker',
            'Traffic Cone',
            'Tripod Rescue',
            'Twin Pulley',
            'Twin Realease',
        ];

        natcasesort($rescueItems);

        foreach (array_values($rescueItems) as $nama) {
            Peralatan::create([
                'nama'         => $nama,
                'kategori'     => 'rescue',
                'jumlah_total' => 4,
                'status'       => 'baik',
                'catatan'      => 'Peralatan operasional tim rescue dinas.',
            ]);
        }
    }
}
