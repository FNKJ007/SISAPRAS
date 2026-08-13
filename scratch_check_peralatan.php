<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Peralatan;

$rescue = Peralatan::where('kategori', 'rescue')->get();
echo "Total Peralatan Rescue di DB saat ini: " . $rescue->count() . "\n";
foreach ($rescue as $item) {
    echo "- ID: {$item->id} | {$item->nama} | Kode: {$item->kode_alat} | Stock: {$item->jumlah_total}\n";
}
