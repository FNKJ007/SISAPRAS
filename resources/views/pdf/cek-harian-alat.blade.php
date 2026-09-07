<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        @page { margin: 24px 30px; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #1f2937; line-height: 1.35; }

        .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border-bottom: 2px solid #059669; padding-bottom: 8px; }
        .kop-table td { vertical-align: middle; }
        .logo-img { width: 48px; height: auto; max-height: 52px; }
        .kop-title { font-size: 10.5pt; font-weight: bold; line-height: 1.2; text-transform: uppercase; color: #000; }
        .kop-subtitle { font-size: 9pt; font-weight: bold; margin-top: 3px; text-transform: uppercase; color: #000; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h1 { font-size: 15px; margin: 0 0 2px; color: #065f46; font-weight: bold; }
        .header p { font-size: 9.5px; margin: 0; color: #6b7280; }

        table.info { width: 100%; border-collapse: collapse; margin-bottom: 12px; table-layout: fixed; }
        table.info td { padding: 3px 2px; vertical-align: top; font-size: 10px; }
        table.info td.lbl { width: 18%; color: #4b5563; }
        table.info td.sep { width: 2%; text-align: center; color: #4b5563; }
        table.info td.val { width: 30%; font-weight: bold; color: #111827; word-wrap: break-word; overflow-wrap: anywhere; }
        table.info td.lbl-r { width: 20%; color: #4b5563; padding-left: 10px; }
        table.info td.sep-r { width: 2%; text-align: center; color: #4b5563; }
        table.info td.val-r { width: 28%; font-weight: bold; color: #111827; word-wrap: break-word; overflow-wrap: anywhere; }

        h2.section { font-size: 11.5px; background: #ecfdf5; color: #065f46; padding: 5px 8px; border-left: 3px solid #059669; margin: 12px 0 6px; font-weight: bold; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 8px; table-layout: fixed; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 4px 5px; font-size: 9.5px; word-wrap: break-word; overflow-wrap: anywhere; }
        table.data th { background: #f3f4f6; color: #1f2937; text-align: center; vertical-align: middle; font-weight: bold; }
        
        .col-no { width: 4.5%; text-align: center; padding: 4px 1px !important; }
        .col-nama { width: 53.5%; text-align: left; }
        .col-baik { width: 8%; text-align: center; }
        .col-rusak { width: 8%; text-align: center; }
        .col-ket { width: 26%; text-align: left; }

        table.data td.center { text-align: center; }
        table.data td.rusak-count { color: #dc2626; font-weight: bold; }
        table.data td.baik-count { color: #059669; font-weight: bold; }

        .ttd { margin-top: 24px; width: 100%; border-collapse: collapse; page-break-inside: avoid; }
        .ttd td { width: 33.33%; text-align: center; font-size: 9.5px; vertical-align: top; padding: 0 8px; }
        .ttd .garis { border-top: 1px solid #374151; padding-top: 4px; margin-top: 45px; }

        .page-break { page-break-before: always; }
        
        table.foto-grid { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        table.foto-grid td { width: 50%; padding: 6px; text-align: center; vertical-align: top; }
        .foto-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 6px; }
        .foto-card img { max-width: 100%; height: 180px; object-fit: contain; border: 1px solid #d1d5db; background: #ffffff; }
    </style>
</head>
<body>

    {{-- Kop Surat --}}
    <table class="kop-table">
        <tr>
            <td style="width: 55px;">
                @if(!empty($logo_data))
                    <img src="{{ $logo_data }}" class="logo-img">
                @endif
            </td>
            <td style="padding-left: 8px;">
                <div class="kop-title">DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN</div>
                <div class="kop-title">KABUPATEN BANDUNG</div>
                <div class="kop-subtitle">{{ strtoupper($judul) }}</div>
            </td>
        </tr>
    </table>
    <div class="header">
        <p>Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>

    {{-- Informasi Pemeriksaan --}}
    <table class="info">
        <tr>
            <td class="lbl">Pos Damkar</td>
            <td class="sep">:</td>
            <td class="val">{{ $record->pos ?? '-' }}</td>
            <td class="lbl-r">Tanggal Pemeriksaan</td>
            <td class="sep-r">:</td>
            <td class="val-r">{{ $record->tanggal_pemeriksaan->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Nama Pemeriksa</td>
            <td class="sep">:</td>
            <td class="val">{{ $record->nama_pemeriksa }}</td>
            <td class="lbl-r">Jabatan</td>
            <td class="sep-r">:</td>
            <td class="val-r">{{ $record->jabatan ?? '-' }}</td>
        </tr>
        @if(!empty($record->regu))
        <tr>
            <td class="lbl">Regu</td>
            <td class="sep">:</td>
            <td class="val">{{ $record->regu }}</td>
            <td class="lbl-r"></td>
            <td class="sep-r"></td>
            <td class="val-r"></td>
        </tr>
        @endif
    </table>

    {{-- Tabel Data Peralatan --}}
    <h2 class="section">Daftar Peralatan yang Diperiksa</h2>
    <table class="data">
        <colgroup>
            <col style="width: 4.5%;">
            <col style="width: 53.5%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 26%;">
        </colgroup>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-nama">Nama Alat</th>
                <th class="col-baik">Baik</th>
                <th class="col-rusak">Rusak</th>
                <th class="col-ket">Keterangan No. Rusak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->alat ?? [] as $item)
            <tr>
                <td class="col-no">{{ $loop->iteration }}</td>
                <td class="col-nama">{{ $item['nama'] ?? '-' }}</td>
                <td class="center baik-count">{{ $item['jumlah_baik'] ?? 0 }}</td>
                <td class="center rusak-count">{{ $item['jumlah_rusak'] ?? 0 }}</td>
                <td class="col-ket">{{ $item['nomor_rusak'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f9fafb; font-weight:bold;">
                <td colspan="2" style="text-align:right; font-weight:bold;">Total</td>
                <td class="center baik-count">{{ $record->total_baik }}</td>
                <td class="center rusak-count">{{ $record->total_rusak }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- Catatan Umum --}}
    @if($record->catatan_umum)
    <h2 class="section">Catatan Umum</h2>
    <p style="font-size: 10px; margin: 4px 0 8px; color: #374151;">{{ $record->catatan_umum }}</p>
    @endif

    {{-- Tanda Tangan Pengesahan --}}
    @php
        $pemeriksaNip = $pemeriksa_nip ?? ($record->user->nip ?? (\App\Models\User::where('name', $record->nama_pemeriksa)->value('nip') ?? ''));
        $danruNip = $danru_nip ?? (\App\Models\User::where('name', $record->nama_danru)->value('nip') ?? (\App\Models\User::where('name', 'LIKE', '%' . $record->nama_danru . '%')->value('nip') ?? ''));
        $kabidNip = $kabid_nip ?? ($record->user_kabid->nip ?? (\App\Models\User::where('name', $record->nama_kabid)->value('nip') ?? ''));

        $kabidUser = !empty($record->nama_kabid)
            ? (\App\Models\User::where('name', $record->nama_kabid)->first()
                ?? \App\Models\User::where('name', 'LIKE', '%' . $record->nama_kabid . '%')->first())
            : null;

        $kabidLabel = $kabid_label ?? ($kabidUser->jabatan ?? match($record->kategori) {
            'rescue'         => 'Kepala Bidang Penyelamatan',
            'pencegahan'     => 'Kepala Bidang Pencegahan Kebakaran',
            'command_center' => 'Kepala Bidang SPI',
            default          => 'Kepala Bidang Pemadaman',
        });
    @endphp
    <table class="ttd">
        <tr>
            <td>
                <div class="garis">
                    <strong>{{ $record->nama_kabid ?? '-' }}</strong><br>
                    @if($kabidNip) <span style="font-size:8.5px; color:#4b5563;">NIP. {{ $kabidNip }}</span><br> @endif
                    <span>{{ $kabidLabel }}</span>
                </div>
            </td>
            <td>
                <div class="garis">
                    <strong>{{ $record->nama_danru ?? '-' }}</strong><br>
                    @if($danruNip) <span style="font-size:8.5px; color:#4b5563;">NIP. {{ $danruNip }}</span><br> @endif
                    <span>Komandan Regu</span>
                </div>
            </td>
            <td>
                <div class="garis">
                    <strong>{{ $record->nama_pemeriksa }}</strong><br>
                    @if($pemeriksaNip) <span style="font-size:8.5px; color:#4b5563;">NIP. {{ $pemeriksaNip }}</span><br> @endif
                    <span>Pemeriksa</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- Foto Dokumentasi diletakkan di halaman terpisah di bagian paling bawah --}}
    @if(!empty($foto_umum_data))
    <div class="page-break">
        <div class="header">
            <h1>Foto Dokumentasi Pemeriksaan Alat</h1>
            <p>{{ $judul }} — Pos {{ $record->pos ?? '-' }} ({{ $record->tanggal_pemeriksaan->translatedFormat('d F Y') }})</p>
        </div>

        <table class="foto-grid">
            @foreach(array_chunk($foto_umum_data, 2) as $row)
            <tr>
                @foreach($row as $fIdx => $foto)
                <td>
                    <div class="foto-card">
                        <img src="{{ $foto }}" alt="Dokumentasi Alat">
                    </div>
                </td>
                @endforeach
                @if(count($row) === 1)
                <td></td>
                @endif
            </tr>
            @endforeach
        </table>
    </div>
    @endif

</body>
</html>
