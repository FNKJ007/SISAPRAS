<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        @page { margin: 20px 22px 18px; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111; }
        .kop { width: 100%; border-bottom: 3px solid #111; padding-bottom: 8px; margin-bottom: 7px; }
        .kop td { vertical-align: middle; }
        .logo { width: 52px; height: 52px; object-fit: contain; }
        .kop-title { text-align: center; font-size: 15px; font-weight: bold; line-height: 1.15; }
        .report-title { text-align: center; font-size: 12px; font-weight: bold; padding: 6px 0 10px; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { height: 15px; padding: 1px 3px; vertical-align: top; }
        .meta .group { font-weight: bold; padding-left: 0; padding-top: 3px; }
        .meta .key { width: 10%; }
        .meta .value { width: 25%; border-bottom: 1px dotted #777; }
        .meta .key-right { width: 12%; }
        .meta .value-right { width: 28%; border-bottom: 1px dotted #777; }
        table.inspection { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .inspection th, .inspection td { border: 1px solid #555; padding: 2px 3px; vertical-align: top; line-height: 1.15; }
        .inspection th { background: #bfbfbf; text-align: center; font-weight: bold; vertical-align: middle; height: 27px; }
        .inspection .no { width: 4%; text-align: center; }
        .inspection .item { width: 27%; }
        .inspection .standard { width: 27%; }
        .inspection .condition { width: 3.6%; text-align: center; padding: 1px; }
        .inspection .action { width: 12%; }
        .inspection .result { width: 12%; }
        .inspection .category td { font-weight: bold; background: #f2f2f2; padding: 3px; }
        .inspection .category .no { font-size: 9px; vertical-align: middle; }
        .check { font-weight: bold; font-size: 10px; text-align: center; }
        .muted { color: #555; }
        .legend { margin-top: 7px; width: 42%; border-collapse: collapse; }
        .legend td { padding: 1px 3px; }
        .evidence { page-break-inside: avoid; margin-top: 12px; }
        .evidence-title { font-size: 10px; font-weight: bold; border-bottom: 1px solid #555; padding-bottom: 3px; margin-bottom: 5px; }
        .evidence-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .evidence-grid td { width: 25%; padding: 3px; text-align: center; vertical-align: top; }
        .evidence-grid img { width: 100%; height: 105px; object-fit: contain; border: 1px solid #777; padding: 2px; }
        .evidence-label { font-size: 7px; font-weight: bold; margin-bottom: 3px; }
        .signatures { width: 100%; margin-top: 12px; border-collapse: collapse; }
        .signatures td { width: 33.33%; text-align: center; vertical-align: top; height: 75px; }
        .signature-line { padding-top: 48px; border-bottom: 1px solid #111; display: inline-block; min-width: 145px; }
        .signature-name { font-weight: bold; text-decoration: underline; margin-top: 3px; }
        .footer { text-align: right; color: #666; font-size: 7px; margin-top: 4px; }
    </style>
</head>
<body>
    @php
        $unit = $unit ?? $record->unit;
        $items = collect($record->perlengkapan ?? [])->filter(function ($item, $key) {
            return !in_array($key, ['kebersihan_bagian_dalam', 'kebersihan_bagian_luar']);
        });
        $specialItems = collect([
            ['label' => 'Kondisi Kebersihan Unit', 'status' => $record->kebersihan_unit === 'tidak_bersih' ? 'rusak' : 'baik', 'catatan' => null, 'show' => true],
            ['label' => 'Level Air Tangki', 'status' => $record->level_air, 'catatan' => $record->level_air, 'show' => $record->kategori === 'pemadam'],
            ['label' => 'Kondisi Tangki Air', 'status' => $record->kondisi_tangki_air, 'catatan' => null, 'show' => $record->kategori === 'pemadam'],
            ['label' => 'Kebocoran Tangki Air', 'status' => $record->kebocoran_tangki_air, 'catatan' => null, 'show' => $record->kategori === 'pemadam'],
            ['label' => 'Tekanan Pompa', 'status' => $record->tekanan_pompa, 'catatan' => null, 'show' => $record->kategori === 'pemadam'],
            ['label' => 'Selang Induk', 'status' => $record->selang_induk, 'catatan' => null, 'show' => $record->kategori === 'pemadam'],
        ])->filter(function ($item) {
            return $item['show'] && $item['status'] !== null && $item['status'] !== '';
        });
        $items = $specialItems->concat($items);
        $categoryNames = [
            'pemadam' => ['Pemeriksaan Mesin & Komponen Mekanikal', 'Pemeriksaan Kabin & Kelistrikan Utama', 'Pemeriksaan Sasis, Ban, & Eksterior', 'Pemeriksaan Pompa & Sistem Pemadam', 'Pemeriksaan Perlengkapan & Peralatan'],
            'rescue' => ['Pemeriksaan Mesin, Kabin & Kelistrikan', 'Pemeriksaan Sasis, Ban, & Eksterior', 'Pemeriksaan Perlengkapan Rescue'],
            'pencegahan' => ['Pemeriksaan Kendaraan & Kelistrikan', 'Pemeriksaan Sasis, Ban, & Eksterior', 'Pemeriksaan Perlengkapan Pencegahan'],
        ];
        $categories = $categoryNames[$record->kategori] ?? ['Pemeriksaan Kendaraan dan Perlengkapan'];
        $itemsPerCategory = (int) ceil(max($items->count(), 1) / count($categories));
        $chunks = $items->values()->chunk($itemsPerCategory);
        $tanggal = strtotime($record->tanggal_pemeriksaan);
        $hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];

        $pemeriksaNip = $pemeriksa_nip ?? ($record->user->nip ?? (\App\Models\User::where('name', $record->nama_pemeriksa)->value('nip') ?? ''));
        $danruNip = $danru_nip ?? (\App\Models\User::where('name', $record->nama_danru)->value('nip') ?? (\App\Models\User::where('name', 'LIKE', '%' . $record->nama_danru . '%')->value('nip') ?? ''));
        $kabidNip = $kabid_nip ?? (\App\Models\User::where('name', $record->nama_kabid)->value('nip') ?? (\App\Models\User::where('name', 'LIKE', '%' . $record->nama_kabid . '%')->value('nip') ?? ''));

        $kabidUser = !empty($record->nama_kabid)
            ? (\App\Models\User::where('name', $record->nama_kabid)->first()
                ?? \App\Models\User::where('name', 'LIKE', '%' . $record->nama_kabid . '%')->first())
            : null;

        $kabidLabel = $kabid_label ?? ($kabidUser->jabatan ?? match($record->kategori) {
            'rescue'     => 'Kepala Bidang Penyelamatan',
            'pencegahan' => 'Kepala Bidang Pencegahan Kebakaran',
            default      => 'Kepala Bidang Pemadaman',
        });

        $reportTitle = match($record->kategori) {
            'rescue'     => 'PEMERIKSAAN KENDARAAN RESCUE',
            'pencegahan' => 'PEMERIKSAAN KENDARAAN PENCEGAHAN',
            default      => 'PEMERIKSAAN KENDARAAN PEMADAM KEBAKARAN',
        };
    @endphp

    <table class="kop"><tr><td style="width: 13%; text-align: center;">@if($logo_data)<img class="logo" src="{{ $logo_data }}">@endif</td><td class="kop-title">Dinas Pemadam Kebakaran dan Penyelamatan<br>Kabupaten Bandung</td><td style="width: 13%;"></td></tr></table>
    <div class="report-title">{{ $reportTitle }}</div>

    <table class="meta">
        <tr><td class="group" colspan="4">DATA KENDARAAN :</td><td class="group" colspan="4">TANGGAL PEMELIHARAAN :</td></tr>
        <tr><td class="key">TNKB</td><td>:</td><td class="value">{{ $unit->plat_nomor ?? '-' }}</td><td></td><td class="key-right">Hari</td><td>:</td><td class="value-right">{{ $hari[date('l', $tanggal)] ?? date('l', $tanggal) }}</td><td></td></tr>
        <tr><td class="key">No. Rangka</td><td>:</td><td class="value">{{ $unit->no_rangka_mesin ?? '-' }}</td><td></td><td class="key-right">Tanggal</td><td>:</td><td class="value-right">{{ date('d-m-Y', $tanggal) }}</td><td></td></tr>
        <tr><td class="key">Merk</td><td>:</td><td class="value">{{ $unit->merk_tipe ?? '-' }}</td><td></td><td class="key-right">Kilometer</td><td>:</td><td class="value-right"></td><td></td></tr>
        <tr><td class="key">Tahun</td><td>:</td><td class="value">{{ $unit->tahun_pembuatan ?? '-' }}</td><td></td><td class="key-right">Pengemudi</td><td>:</td><td class="value-right">{{ $record->nama_pemeriksa }}</td><td></td></tr>
        <tr><td class="key">Kapasitas</td><td>:</td><td class="value">{{ !empty($unit->cc) ? $unit->cc . ' CC' : '-' }}</td><td></td><td class="key-right">NIP</td><td>:</td><td class="value-right">{{ $pemeriksaNip ?: '-' }}</td><td></td></tr>
        <tr><td class="key">No. Lambung</td><td>:</td><td class="value">{{ $unit->nomor_lambung ?? $record->unit_nama ?? '-' }}</td><td></td><td class="key-right">Penempatan</td><td>:</td><td class="value-right">{{ $record->pos ?? $unit->pos ?? '-' }}</td><td></td></tr>
    </table>

    <table class="inspection">
        <thead><tr><th class="no">No</th><th class="item">Item Pemeriksaan</th><th class="standard">Standar</th><th colspan="5">Kondisi</th><th class="action">Rencana Tindak<br>Lanjut</th><th class="result">Hasil Tindak<br>Lanjut</th></tr><tr><th></th><th></th><th></th><th class="condition">R</th><th class="condition">RR</th><th class="condition">RS</th><th class="condition">TF</th><th class="condition">T</th><th></th><th></th></tr></thead>
        <tbody>
        @foreach($chunks as $categoryIndex => $categoryItems)
            <tr class="category"><td class="no">{{ $categoryIndex + 1 }}</td><td colspan="9">{{ $categories[$categoryIndex] ?? end($categories) }}</td></tr>
            @foreach($categoryItems as $item)
                @php
                    $status = strtolower($item['status'] ?? 'baik');
                    $isRusak = $status === 'rusak';
                    $isPerluPerhatian = in_array($status, ['perlu_perhatian', 'perlu perhatian']);
                    $note = trim($item['catatan'] ?? '');
                @endphp
                <tr><td class="no"></td><td>- {{ $item['label'] ?? '-' }}</td><td class="muted">{{ $isRusak || $isPerluPerhatian ? 'Perlu tindak lanjut sesuai hasil pemeriksaan.' : 'Berfungsi baik dan sesuai standar.' }}</td><td class="condition check">{{ $isRusak ? 'X' : '' }}</td><td class="condition check">{{ $isPerluPerhatian ? 'X' : '' }}</td><td class="condition"></td><td class="condition"></td><td class="condition"></td><td class="action">{{ $isRusak || $isPerluPerhatian ? ($note ?: 'Perlu pemeriksaan/perbaikan') : '-' }}</td><td class="result">-</td></tr>
            @endforeach
        @endforeach
        </tbody>
    </table>

    @if($bukti_pemanasan_data || $bukti_bbm_data || $bukti_pencucian_data || count($dok_tangki_data ?? []) > 0)
        <div class="evidence">
            <div class="evidence-title">BUKTI PENGECEKAN / DOKUMENTASI FOTO</div>
            <table class="evidence-grid">
                <tr>
                    @if($bukti_pemanasan_data)
                        <td><div class="evidence-label">Bukti Pemanasan</div><img src="{{ $bukti_pemanasan_data }}"></td>
                    @endif
                    @if($bukti_bbm_data)
                        <td><div class="evidence-label">Bukti Level BBM</div><img src="{{ $bukti_bbm_data }}"></td>
                    @endif
                    @if($bukti_pencucian_data)
                        <td><div class="evidence-label">Bukti Pembersihan Unit</div><img src="{{ $bukti_pencucian_data }}"></td>
                    @endif
                    @foreach($dok_tangki_data ?? [] as $index => $image)
                        <td><div class="evidence-label">Tangki &amp; Pompa {{ $index + 1 }}</div><img src="{{ $image }}"></td>
                        @if(($index + 1) % 4 === 0 && !$loop->last)</tr><tr>@endif
                    @endforeach
                </tr>
            </table>
        </div>
    @endif

    <table class="legend"><tr><td>R</td><td>Rusak</td><td>RR</td><td>Rusak Ringan</td><td>RS</td><td>Rusak Sedang</td></tr><tr><td>TF</td><td>Tidak Berfungsi</td><td>T</td><td colspan="3">Tambahan</td></tr></table>
    <table class="signatures">
        <tr>
            <td>
                {{ $kabidLabel }}<br>
                <span class="signature-line"></span><br>
                <span class="signature-name">{{ $record->nama_kabid ?: '-' }}</span><br>
                {{ $kabidNip }}
            </td>
            <td>
                Komandan Regu<br>
                <span class="signature-line"></span><br>
                <span class="signature-name">{{ $record->nama_danru ?: '-' }}</span><br>
                {{ $danruNip }}
            </td>
            <td>
                Pengemudi<br>
                <span class="signature-line"></span><br>
                <span class="signature-name">{{ $record->nama_pemeriksa ?: '-' }}</span><br>
                {{ $pemeriksaNip }}
            </td>
        </tr>
    </table>
    <div class="footer">Dokumen ini dihasilkan otomatis oleh sistem.</div>
</body>
</html>
