<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        @page { margin: 28px 32px; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; }

        .header { text-align: center; border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { font-size: 15px; margin: 0 0 2px; color: #065f46; }
        .header p { font-size: 10px; margin: 0; color: #6b7280; }

        table.info { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.info td { padding: 4px 6px; vertical-align: top; font-size: 10.5px; }
        table.info td.label { width: 150px; color: #6b7280; }
        table.info td.value { font-weight: bold; }

        h2.section { font-size: 12px; background: #ecfdf5; color: #065f46; padding: 6px 8px; border-left: 3px solid #059669; margin: 16px 0 8px; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 5px 7px; font-size: 10px; text-align: left; }
        table.data th { background: #f3f4f6; color: #374151; }
        table.data td.center { text-align: center; }
        table.data td.rusak-count { color: #dc2626; font-weight: bold; }
        table.data td.baik-count { color: #059669; font-weight: bold; }

        .summary-box { margin-top: 10px; padding: 8px 10px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; font-size: 11px; }
        .summary-box.ok { background: #ecfdf5; border-color: #a7f3d0; }

        .ttd { margin-top: 40px; width: 100%; }
        .ttd td { width: 50%; text-align: center; font-size: 10.5px; padding-top: 40px; }
        .ttd .garis { border-top: 1px solid #374151; padding-top: 4px; }

        .footer-note { margin-top: 18px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $judul }}</h1>
        <p>Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>

    <table class="info">
        <tr>
            <td class="label">Unit / Pos</td>
            <td class="value">: {{ $record->unit_nama ?? ($record->pos ?? '-') }}</td>
            <td class="label">Tanggal Pemeriksaan</td>
            <td class="value">: {{ $record->tanggal_pemeriksaan->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Nama Pemeriksa</td>
            <td class="value">: {{ $record->nama_pemeriksa }}</td>
            <td class="label">Jabatan</td>
            <td class="value">: {{ $record->jabatan }}</td>
        </tr>
        <tr>
            <td class="label">Pos</td>
            <td class="value">: {{ $record->pos ?? '-' }}</td>
            <td class="label"></td>
            <td class="value"></td>
        </tr>
    </table>

    <h2 class="section">Daftar Peralatan yang Diperiksa</h2>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Alat</th>
                <th style="width: 65px;">Baik</th>
                <th style="width: 65px;">Rusak</th>
                <th>Keterangan No. Rusak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->alat ?? [] as $item)
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td>{{ $item['nama'] ?? '-' }}</td>
                <td class="center baik-count">{{ $item['jumlah_baik'] ?? 0 }}</td>
                <td class="center rusak-count">{{ $item['jumlah_rusak'] ?? 0 }}</td>
                <td>{{ $item['nomor_rusak'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" style="text-align:right;">Total</th>
                <th class="center baik-count">{{ $record->total_baik }}</th>
                <th class="center rusak-count">{{ $record->total_rusak }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    @if($record->catatan_umum)
    <h2 class="section">Catatan Umum</h2>
    <p style="font-size: 10.5px;">{{ $record->catatan_umum }}</p>
    @endif

    <div class="summary-box {{ ($record->total_rusak ?? 0) > 0 ? '' : 'ok' }}">
        @if(($record->total_rusak ?? 0) > 0)
            Ditemukan <strong>{{ $record->total_rusak }} unit alat</strong> dalam kondisi RUSAK dan perlu tindak lanjut perbaikan.
        @else
            Seluruh peralatan yang diperiksa dalam kondisi BAIK.
        @endif
    </div>

    <table class="ttd">
        <tr>
            <td>
                <div class="garis">
                    {{ $record->nama_pemeriksa }}<br>
                    Petugas Pemeriksa ({{ $record->jabatan }})
                </div>
            </td>
            <td>
                <div class="garis">
                    Mengetahui,<br>
                    Kepala Pos / Regu
                </div>
            </td>
        </tr>
    </table>

    <p class="footer-note">Dokumen ini dihasilkan otomatis oleh sistem dan sah tanpa tanda tangan basah.</p>

</body>
</html>
