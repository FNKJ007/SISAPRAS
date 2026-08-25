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

        .status-baik { color: #059669; font-weight: bold; }
        .status-rusak { color: #dc2626; font-weight: bold; }

        .summary-box { margin-top: 10px; padding: 8px 10px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; font-size: 11px; }
        .summary-box.ok { background: #ecfdf5; border-color: #a7f3d0; }

        .ttd { margin-top: 40px; width: 100%; }
        .ttd td { width: 33.33%; text-align: center; font-size: 10.5px; padding-top: 40px; }
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
            <td class="label">Unit Kendaraan</td>
            <td class="value">: {{ $record->unit_nama }}</td>
            <td class="label">Tanggal Pemeriksaan</td>
            <td class="value">: {{ \Carbon\Carbon::parse($record->tanggal_pemeriksaan)->translatedFormat('d F Y') }}</td>
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
            <td class="label">Kebersihan Unit</td>
            <td class="value">: <span style="{{ ($record->kebersihan_unit ?? 'bersih') === 'tidak_bersih' ? 'color:#dc2626;' : 'color:#059669;' }}">{{ ($record->kebersihan_unit ?? 'bersih') === 'tidak_bersih' ? 'Tidak Bersih' : 'Bersih' }}</span></td>
        </tr>
        <tr>
            <td class="label">Jenis BBM</td>
            <td class="value">: {{ ucfirst($record->jenis_bbm ?? '-') }}</td>
            <td class="label"></td>
            <td class="value"></td>
        </tr>
    </table>

    @if(!empty($bukti_pemanasan_data) || !empty($bukti_bbm_data) || !empty($bukti_pencucian_data) || (!empty($dok_tangki_data) && count($dok_tangki_data) > 0))
    <h2 class="section">Dokumentasi Foto</h2>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            @if(!empty($bukti_pencucian_data))
            <td style="width: 33.33%; vertical-align: top; padding: 2px 4px;">
                <div style="font-weight: bold; margin-bottom: 4px;">Bukti Kebersihan Unit</div>
                <div><img src="{{ $bukti_pencucian_data }}" style="width: 100%; max-height: 135px; border: 1px solid #ddd; padding: 3px;"></div>
            </td>
            @endif

            @if(!empty($bukti_pemanasan_data))
            <td style="width: 33.33%; vertical-align: top; padding: 2px 4px;">
                <div style="font-weight: bold; margin-bottom: 4px;">Bukti Pemanasan</div>
                <div><img src="{{ $bukti_pemanasan_data }}" style="width: 100%; max-height: 135px; border: 1px solid #ddd; padding: 3px;"></div>
            </td>
            @endif

            @if(!empty($bukti_bbm_data))
            <td style="width: 33.33%; vertical-align: top; padding: 2px 4px;">
                <div style="font-weight: bold; margin-bottom: 4px;">Bukti Level BBM</div>
                <div><img src="{{ $bukti_bbm_data }}" style="width: 100%; max-height: 135px; border: 1px solid #ddd; padding: 3px;"></div>
            </td>
            @endif
        </tr>
    </table>

    @if(!empty($dok_tangki_data) && count($dok_tangki_data) > 0)
    <div style="margin-top: 6px; margin-bottom: 10px;">
        <div style="font-weight: bold; margin-bottom: 4px;">Dokumentasi Pengecekan Tangki dan Pompa (maksimal 3 foto)</div>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                @foreach($dok_tangki_data as $index => $img)
                <td style="width: 33.33%; vertical-align: top; padding: 2px 4px;">
                    <div><img src="{{ $img }}" style="width: 100%; max-height: 135px; border: 1px solid #ddd; padding: 3px;"></div>
                </td>
                @endforeach
                @for($i = count($dok_tangki_data); $i < 3; $i++)
                <td style="width: 33.33%; padding: 2px 4px;"></td>
                @endfor
            </tr>
        </table>
    </div>
    @endif
    @endif

    <h2 class="section">Tangki &amp; Pompa</h2>
    <table class="info">
        <tr>
            <td class="label">Level Air</td>
            <td class="value">: {{ $record->level_air ?? '-' }}</td>
            <td class="label">Kondisi Tangki Air</td>
            <td class="value">: {{ $record->kondisi_tangki_air ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kebocoran Tangki Air</td>
            <td class="value">: {{ $record->kebocoran_tangki_air ?? '-' }}</td>
            <td class="label">Tekanan Pompa</td>
            <td class="value">: {{ $record->tekanan_pompa ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Selang Induk</td>
            <td class="value">: {{ $record->selang_induk ?? '-' }}</td>
            <td class="label"></td>
            <td class="value"></td>
        </tr>
        @if($record->catatan_tangki_pompa)
        <tr>
            <td class="label">Catatan</td>
            <td class="value" colspan="3">: {{ $record->catatan_tangki_pompa }}</td>
        </tr>
        @endif
    </table>

    <h2 class="section">Kelengkapan &amp; Kondisi Kendaraan</h2>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Item Pemeriksaan</th>
                <th style="width: 70px;">Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="center">1</td>
                <td>Kondisi Kebersihan Unit</td>
                <td class="center {{ ($record->kebersihan_unit ?? 'bersih') === 'tidak_bersih' ? 'status-rusak' : 'status-baik' }}">
                    {{ ($record->kebersihan_unit ?? 'bersih') === 'tidak_bersih' ? 'TIDAK BERSIH' : 'BERSIH' }}
                </td>
                <td>-</td>
            </tr>
            @php $no = 2; @endphp
            @foreach($record->perlengkapan ?? [] as $key => $item)
                @if(!in_array($key, ['kebersihan_bagian_dalam', 'kebersihan_bagian_luar']))
                <tr>
                    <td class="center">{{ $no++ }}</td>
                    <td>{{ $item['label'] ?? '-' }}</td>
                    <td class="center {{ ($item['status'] ?? '') === 'rusak' ? 'status-rusak' : 'status-baik' }}">
                        {{ strtoupper($item['status'] ?? 'baik') }}
                    </td>
                    <td>{{ $item['catatan'] ?? '-' }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="summary-box {{ ($record->jumlah_rusak ?? 0) > 0 ? '' : 'ok' }}">
        @if(($record->jumlah_rusak ?? 0) > 0)
            <strong>{{ $record->jumlah_rusak }} item</strong> dalam kondisi RUSAK dan perlu tindak lanjut perbaikan.
        @else
            Seluruh item kelengkapan kendaraan dalam kondisi BAIK.
        @endif
    </div>

    <table class="ttd">
        <tr>
            <td>
                <div class="garis">
                    {{ $record->nama_kabid ?? '-' }}<br>
                    Kepala Bidang
                </div>
            </td>
            <td>
                <div class="garis">
                    {{ $record->nama_danru ?? '-' }}<br>
                    Danru
                </div>
            </td>
            <td>
                <div class="garis">
                    {{ $record->nama_pemeriksa }}<br>
                    Pengemudi
                </div>
            </td>
        </tr>
    </table>

    <p class="footer-note">Dokumen ini dihasilkan otomatis oleh sistem dan sah tanpa tanda tangan basah.</p>

</body>
</html>
