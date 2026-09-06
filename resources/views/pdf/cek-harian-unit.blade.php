<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="utf-8">
    <title>{{ $judul ?? 'Pemeriksaan Harian Kendaraan' }}</title>
    <style>
        @page {
            margin: 18px 22px 18px 22px;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 7.5pt;
            color: #000;
            line-height: 1.15;
        }
        
        /* Kop Surat Header */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .logo-img {
            width: 48px;
            height: auto;
            max-height: 52px;
        }
        .kop-title {
            font-size: 10.5pt;
            font-weight: bold;
            line-height: 1.2;
            text-transform: uppercase;
            color: #000;
        }
        .kop-subtitle {
            font-size: 9pt;
            font-weight: bold;
            margin-top: 3px;
            text-transform: uppercase;
            color: #000;
        }

        /* Metadata 2 Kolom */
        .meta-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 7.5pt;
        }
        .meta-container > tbody > tr > td {
            vertical-align: top;
        }
        .meta-subtable {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-subtable td {
            padding: 1px 0;
            vertical-align: middle;
        }
        .meta-title {
            font-weight: bold;
            font-size: 8pt;
            padding-bottom: 2px !important;
        }
        .meta-val-line {
            border-bottom: 1px dotted #888;
            padding-left: 3px;
        }

        /* Tabel Pemeriksaan */
        table.inspection-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
        }
        table.inspection-table th, 
        table.inspection-table td {
            border: 1px solid #333;
            padding: 2px 4px;
            vertical-align: middle;
        }
        table.inspection-table thead th {
            background-color: #002060;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 7pt;
            letter-spacing: 0.3px;
        }
        .sub-header-row th {
            background-color: #002060 !important;
            color: #ffffff !important;
            font-size: 6.5pt !important;
            padding: 1px 2px !important;
        }
        .group-header td {
            background-color: #D9E1F2;
            font-weight: bold;
            font-size: 7.5pt;
            padding: 3px 5px;
            color: #000;
        }
        .text-center {
            text-align: center;
        }
        .check-mark {
            font-weight: bold;
            font-size: 8pt;
            text-align: center;
        }

        /* Footer & Signatures */
        .legend-text {
            font-size: 7pt;
            font-weight: bold;
            margin-top: 6px;
            margin-bottom: 15px;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
            font-size: 7.5pt;
        }
        .signatures-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
        }
        .sig-space {
            height: 48px;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Dokumentasi (Page 3) */
        .doc-page {
            page-break-before: always;
            padding-top: 5px;
        }
        .doc-header {
            font-size: 10pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .doc-label {
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .doc-box {
            border: 1px solid #555;
            height: 185px;
            text-align: center;
            padding: 3px;
            background: #fafafa;
        }
        .doc-box img {
            max-width: 100%;
            max-height: 179px;
            object-fit: contain;
        }
        .doc-box-empty {
            border: 1px dashed #999;
            height: 185px;
            line-height: 185px;
            text-align: center;
            color: #888;
            font-size: 8pt;
        }
    </style>
</head>
<body>
    @php
        $unit = $unit ?? $record->unit;
        $tanggal = strtotime($record->tanggal_pemeriksaan ?? $record->created_at);
        $hari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];

        $pemeriksaNip = $pemeriksa_nip ?? ($record->user->nip ?? (\App\Models\User::where('name', $record->nama_pemeriksa)->value('nip') ?? ''));
        $danruNip     = $danru_nip ?? (\App\Models\User::where('name', $record->nama_danru)->value('nip') ?? (\App\Models\User::where('name', 'LIKE', '%' . $record->nama_danru . '%')->value('nip') ?? ''));
        $kabidNip     = $kabid_nip ?? (\App\Models\User::where('name', $record->nama_kabid)->value('nip') ?? (\App\Models\User::where('name', 'LIKE', '%' . $record->nama_kabid . '%')->value('nip') ?? ''));

        $kabidUser = !empty($record->nama_kabid)
            ? (\App\Models\User::where('name', $record->nama_kabid)->first()
                ?? \App\Models\User::where('name', 'LIKE', '%' . $record->nama_kabid . '%')->first())
            : null;

        $kategoriRaw = strtolower($kategori ?? ($record->kategori ?? ''));

        $kabidLabel = $kabid_label ?? ($kabidUser->jabatan ?? match($kategoriRaw) {
            'rescue'     => 'Kepala Bidang Penyelamatan',
            'pencegahan' => 'Kepala Bidang Pencegahan Kebakaran',
            default      => 'Kepala Bidang Pemadaman',
        });

        $kategoriNama = match($kategoriRaw) {
            'rescue'     => 'RESCUE',
            'pencegahan' => 'PENCEGAHAN',
            default      => 'PEMADAM',
        };

        // Standard perlengkapan labels
        $standardPerlengkapan = [
            'engine_starter'             => 'Engine Starter',
            'rem_tangan'                 => 'Rem Tangan',
            'rem_kaki'                   => 'Rem Kaki',
            'kelistrikan'                => 'Kelistrikan',
            'klakson'                    => 'Klakson',
            'sirine_tunggal'             => 'Sirine Tunggal',
            'sirine'                     => 'Sirine',
            'speedometer'                => 'Speedometer',
            'dashboard_camera'           => 'Dashboard Camera',
            'gps_tracker'                => 'GPS Tracker',
            'flasher_sein_kanan_kiri'    => 'Flasher Sein Kanan-Kiri',
            'spion_dalam'                => 'Spion Dalam',
            'rig'                        => 'RIG',
            'speaker'                    => 'Speaker',
            'megaphone_toa'              => 'Megaphone (TOA)',
            'oli_power_steering'         => 'Oli Power Steering',
            'air_radiator'               => 'Air Radiator',
            'minyak_rem'                 => 'Minyak Rem',
            'oli_mesin'                  => 'Oli Mesin',
            'air_wiper'                  => 'Air Wiper',
            'ac'                         => 'AC',
            'lampu_depan_dim_kanan'      => 'Lampu Depan (Dim) Kanan',
            'lampu_depan_dim_kiri'       => 'Lampu Depan (Dim) Kiri',
            'lampu_belakang_kanan'       => 'Lampu Belakang Kanan',
            'lampu_belakang_kiri'        => 'Lampu Belakang Kiri',
            'lampu_belakang_hazard'      => 'Lampu Belakang Hazard',
            'lampu_sein_depan_kanan'     => 'Lampu Sein Depan Kanan',
            'lampu_sein_depan_kiri'      => 'Lampu Sein Depan Kiri',
            'lampu_sein_belakang_kanan'  => 'Lampu Sein Belakang Kanan',
            'lampu_sein_belakang_kiri'   => 'Lampu Sein Belakang Kiri',
            'spion_kanan'                => 'Spion Kanan',
            'spion_kiri'                 => 'Spion Kiri',
            'wiper'                      => 'Wiper',
            'winch'                      => 'Winch',
            'ban_depan_kanan'            => 'Ban Depan Kanan',
            'ban_depan_kiri'             => 'Ban Depan Kiri',
            'ban_belakang_kanan'         => 'Ban Belakang Kanan',
            'ban_belakang_kiri'          => 'Ban Belakang Kiri',
            'ban_cadangan'               => 'Ban Cadangan',
            'lampu_rotary'               => 'Lampu Rotary',
            'lampu_rem_kanan'            => 'Lampu Rem Kanan',
            'lampu_rem_kiri'             => 'Lampu Rem Kiri',
            'pintu_kompartemen_kanan'    => 'Pintu Kompartemen Kanan',
            'pintu_kompartemen_kiri'     => 'Pintu Kompartemen Kiri',
            'pintu_kompartemen_belakang' => 'Pintu Kompartemen Belakang',
            'ganjal_ban'                 => 'Ganjal Ban',
            'dongkrak'                   => 'Dongkrak',
            'kabin'                      => 'Kabin',
            'body_unit'                  => 'Body Unit',
            'kunci_kunci'                => 'Kunci-Kunci',
        ];

        // Merge existing saved perlengkapan
        $perlengkapanSaved = $record->perlengkapan ?? [];
    @endphp

    {{-- KOP SURAT --}}
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
                <div class="kop-subtitle">PEMERIKSAAN HARIAN KENDARAAN ( {{ $kategoriNama }} )</div>
            </td>
        </tr>
    </table>

    {{-- METADATA DUA KOLOM --}}
    <table class="meta-container">
        <tr>
            {{-- Kolom Kiri: Data Kendaraan --}}
            <td style="width: 48%; padding-right: 12px;">
                <table class="meta-subtable">
                    <tr>
                        <td colspan="3" class="meta-title">Data Kendaraan</td>
                    </tr>
                    <tr>
                        <td style="width: 85px;">TNKB</td>
                        <td style="width: 8px;">:</td>
                        <td class="meta-val-line">{{ $unit->plat_nomor ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Nomor Rangka</td>
                        <td>:</td>
                        <td class="meta-val-line">{{ $unit->no_rangka_mesin ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Merk</td>
                        <td>:</td>
                        <td class="meta-val-line">{{ $unit->merk_tipe ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Tahun</td>
                        <td>:</td>
                        <td class="meta-val-line">{{ $unit->tahun_pembuatan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Kapasitas</td>
                        <td>:</td>
                        <td class="meta-val-line">{{ !empty($unit->cc) ? $unit->cc . ' CC' : '-' }}</td>
                    </tr>
                    <tr>
                        <td>Nomor Lambung</td>
                        <td>:</td>
                        <td class="meta-val-line">{{ $unit->nomor_lambung ?? $record->unit_nama ?? '-' }}</td>
                    </tr>
                </table>
            </td>

            {{-- Kolom Kanan: Waktu Pemeriksaan --}}
            <td style="width: 52%; padding-left: 12px;">
                <table class="meta-subtable">
                    <tr>
                        <td colspan="3" class="meta-title">Waktu Pemeriksaan</td>
                    </tr>
                    <tr>
                        <td style="width: 105px;">Hari</td>
                        <td style="width: 8px;">:</td>
                        <td class="meta-val-line">{{ $hari[date('l', $tanggal)] ?? date('l', $tanggal) }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal Pemeriksaan</td>
                        <td>:</td>
                        <td class="meta-val-line">{{ date('d-m-Y', $tanggal) }}</td>
                    </tr>
                    <tr>
                        <td>Kilometer</td>
                        <td>:</td>
                        <td class="meta-val-line">{{ $record->level_bbm ? $record->level_bbm : '-' }}</td>
                    </tr>
                    <tr>
                        <td>Pengemudi</td>
                        <td>:</td>
                        <td class="meta-val-line">{{ $record->nama_pemeriksa }}</td>
                    </tr>
                    <tr>
                        <td>NIP Pengemudi</td>
                        <td>:</td>
                        <td class="meta-val-line">{{ $pemeriksaNip ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td>Penempatan</td>
                        <td>:</td>
                        <td class="meta-val-line">{{ $record->pos ?? $unit->pos ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TABEL CHECKLIST PEMERIKSAAN --}}
    <table class="inspection-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 26%;">PEMERIKSAAN</th>
                <th colspan="2" style="width: 14%;">KONDISI</th>
                <th rowspan="2" style="width: 24%;">KETERANGAN</th>
                <th rowspan="2" style="width: 18%;">RENCANA TINDAK LANJUT</th>
                <th rowspan="2" style="width: 18%;">HASIL TINDAK LANJUT</th>
            </tr>
            <tr class="sub-header-row">
                <th style="width: 7%;">B / Br / P</th>
                <th style="width: 7%;">R / K / Ks</th>
            </tr>
        </thead>
        <tbody>
            {{-- SEKSI 1: Pemanasan, BBM, Kebersihan --}}
            <tr class="group-header">
                <td colspan="6">Pemanasan, BBM, Kebersihan</td>
            </tr>
            @php
                $isPemanasanBaik = !empty($record->bukti_pemanasan);
                $isBbmBaik = true; // default terisi
                $isBersih = ($record->kebersihan_unit !== 'tidak_bersih');
            @endphp
            <tr>
                <td>Pemanasan</td>
                <td class="check-mark">{{ $isPemanasanBaik ? '✓' : '' }}</td>
                <td class="check-mark">{{ !$isPemanasanBaik ? '✓' : '' }}</td>
                <td>{{ $isPemanasanBaik ? 'Dilakukan pemanasan mesin' : 'Belum dipanaskan' }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>BBM</td>
                <td class="check-mark">✓</td>
                <td class="check-mark"></td>
                <td>Jenis: {{ ucfirst($record->jenis_bbm ?? 'Solar') }}</td>
                <td></td>
                <td></td>
            </tr>
            @php
                $catatanKebersihan = $perlengkapanSaved['kebersihan_unit']['catatan'] ?? ($perlengkapanSaved['kebersihan']['catatan'] ?? '');
            @endphp
            <tr>
                <td>Kebersihan</td>
                <td class="check-mark">{{ $isBersih ? '✓' : '' }}</td>
                <td class="check-mark">{{ !$isBersih ? '✓' : '' }}</td>
                <td>{{ $catatanKebersihan ? $catatanKebersihan : ($isBersih ? 'Unit Bersih' : 'Unit Perlu Pembersihan') }}</td>
                <td>{{ !$isBersih ? ($catatanKebersihan ? 'Pencucian / ' . $catatanKebersihan : 'Pencucian ulang unit') : '' }}</td>
                <td></td>
            </tr>

            {{-- SEKSI 2: Tangki dan Pompa (Khusus Pemadam atau jika ada data tangki) --}}
            @if(strtolower($record->kategori ?? '') === 'pemadam' || !empty($record->level_air) || !empty($record->tekanan_pompa))
                <tr class="group-header">
                    <td colspan="6">Tangki dan Pompa</td>
                </tr>
                @php
                    $isLevelAirBaik = ($record->level_air !== 'kosong');
                    $levelAirKet = \App\Models\CekHarianUnit::$levelMap[$record->level_air] ?? ucfirst(str_replace('_', ' ', $record->level_air ?? 'Penuh'));
                    
                    $isBocor = ($record->kebocoran_tangki_air === 'ada');
                    $isTangkiBaik = ($record->kondisi_tangki_air === 'baik' || empty($record->kondisi_tangki_air));
                    $isPompaBaik = ($record->tekanan_pompa === 'baik' || empty($record->tekanan_pompa));
                    $isSelangBaik = ($record->selang_induk === 'baik' || empty($record->selang_induk));
                @endphp
                <tr>
                    <td>Level Air</td>
                    <td class="check-mark">{{ $isLevelAirBaik ? '✓' : '' }}</td>
                    <td class="check-mark">{{ !$isLevelAirBaik ? '✓' : '' }}</td>
                    <td>{{ $levelAirKet }}</td>
                    <td>{{ !$isLevelAirBaik ? 'Pengisian tangki air' : '' }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Kebocoran Tangki</td>
                    <td class="check-mark">{{ !$isBocor ? '✓' : '' }}</td>
                    <td class="check-mark">{{ $isBocor ? '✓' : '' }}</td>
                    <td>{{ $isBocor ? 'Ada kebocoran pada tangki' : 'Tidak bocor' }}</td>
                    <td>{{ $isBocor ? 'Perbaikan kebocoran tangki' : '' }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Kondisi Tangki</td>
                    <td class="check-mark">{{ $isTangkiBaik ? '✓' : '' }}</td>
                    <td class="check-mark">{{ !$isTangkiBaik ? '✓' : '' }}</td>
                    <td>{{ $isTangkiBaik ? 'Baik' : 'Perlu perhatian' }}</td>
                    <td>{{ !$isTangkiBaik ? 'Perawatan tangki' : '' }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Tekanan Pompa</td>
                    <td class="check-mark">{{ $isPompaBaik ? '✓' : '' }}</td>
                    <td class="check-mark">{{ !$isPompaBaik ? '✓' : '' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $record->tekanan_pompa ?? 'Normal')) }}</td>
                    <td>{{ !$isPompaBaik ? 'Pengecekan sistem pompa' : '' }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Selang Induk</td>
                    <td class="check-mark">{{ $isSelangBaik ? '✓' : '' }}</td>
                    <td class="check-mark">{{ !$isSelangBaik ? '✓' : '' }}</td>
                    <td>{{ $isSelangBaik ? 'Baik' : 'Perlu penggantian' }}</td>
                    <td>{{ !$isSelangBaik ? 'Penggantian selang induk' : '' }}</td>
                    <td></td>
                </tr>
                @if(!empty($record->catatan_tangki_pompa))
                <tr>
                    <td>Catatan Tangki &amp; Pompa</td>
                    <td class="check-mark"></td>
                    <td class="check-mark"></td>
                    <td>{{ $record->catatan_tangki_pompa }}</td>
                    <td></td>
                    <td></td>
                </tr>
                @endif
            @endif

            {{-- SEKSI 3: Perlengkapan Kendaraan --}}
            <tr class="group-header">
                <td colspan="6">Perlengkapan Kendaraan</td>
            </tr>
            @foreach($standardPerlengkapan as $key => $label)
                @php
                    $itemData = $perlengkapanSaved[$key] ?? null;
                    $status = strtolower($itemData['status'] ?? 'baik');
                    $isRusak = ($status === 'rusak' || $status === 'tidak_baik');
                    $catatan = trim($itemData['catatan'] ?? '');
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="check-mark">{{ !$isRusak ? '✓' : '' }}</td>
                    <td class="check-mark">{{ $isRusak ? '✓' : '' }}</td>
                    <td>{{ $catatan ? $catatan : ($isRusak ? 'Rusak / Perlu Perbaikan' : 'Baik') }}</td>
                    <td>{{ $isRusak ? ($catatan ? 'Perbaikan / ' . $catatan : 'Perbaikan / Penggantian') : '' }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- KETERANGAN & TANDA TANGAN (PAGE 2 BOTTOM) --}}
    <div class="legend-text">
        Keterangan: &nbsp;&nbsp;&nbsp;&nbsp;
        <strong>B / Br / P :</strong> Baik / Bersih / Penuh &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <strong>R / K / Ks :</strong> Rusak / Kotor / Kosong
    </div>

    <table class="signatures-table">
        <tr>
            <td>
                {{ $kabidLabel }}<br>
                <div class="sig-space"></div>
                <span class="sig-name">{{ $record->nama_kabid ?: '—' }}</span><br>
                NIP. {{ $kabidNip ?: '—' }}
            </td>
            <td>
                Komandan Regu<br>
                <div class="sig-space"></div>
                <span class="sig-name">{{ $record->nama_danru ?: '—' }}</span><br>
                NIP. {{ $danruNip ?: '—' }}
            </td>
            <td>
                Pengemudi<br>
                <div class="sig-space"></div>
                <span class="sig-name">{{ $record->nama_pemeriksa ?: '—' }}</span><br>
                NIP. {{ $pemeriksaNip ?: '—' }}
            </td>
        </tr>
    </table>

    {{-- ===================== PAGE 3: DOKUMENTASI FOTO ===================== --}}
    <div class="doc-page">
        <div class="doc-header">DOKUMENTASI</div>

        {{-- Baris Atas 3 Kolom: Kondisi BBM, Pemanasan & Kilometer, Kebersihan --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 22px;">
            <tr>
                <td style="width: 33.33%; vertical-align: top; padding-right: 6px;">
                    <div class="doc-label">KONDISI BBM</div>
                    @if(!empty($bukti_bbm_data))
                        <div class="doc-box">
                            <img src="{{ $bukti_bbm_data }}">
                        </div>
                    @else
                        <div class="doc-box-empty">(Tidak Ada Foto)</div>
                    @endif
                </td>
                <td style="width: 33.33%; vertical-align: top; padding: 0 3px;">
                    <div class="doc-label">PEMANASAN &amp; KILOMETER</div>
                    @if(!empty($bukti_pemanasan_data))
                        <div class="doc-box">
                            <img src="{{ $bukti_pemanasan_data }}">
                        </div>
                    @else
                        <div class="doc-box-empty">(Tidak Ada Foto)</div>
                    @endif
                </td>
                <td style="width: 33.33%; vertical-align: top; padding-left: 6px;">
                    <div class="doc-label">KEBERSIHAN</div>
                    @if(!empty($bukti_pencucian_data))
                        <div class="doc-box">
                            <img src="{{ $bukti_pencucian_data }}">
                        </div>
                    @else
                        <div class="doc-box-empty">(Tidak Ada Foto)</div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- Baris Bawah: Pemeriksaan Unit / Tangki & Pompa --}}
        <div class="doc-label" style="margin-top: 10px;">PEMERIKSAAN UNIT</div>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                @if(!empty($dok_tangki_data) && count($dok_tangki_data) > 0)
                    @foreach($dok_tangki_data as $index => $imgData)
                        <td style="width: {{ 100 / max(1, min(3, count($dok_tangki_data))) }}%; vertical-align: top; padding: 4px;">
                            <div class="doc-box">
                                <img src="{{ $imgData }}">
                            </div>
                        </td>
                    @endforeach
                @else
                    <td style="width: 33.33%; padding-right: 6px;">
                        <div class="doc-box-empty">(Dokumentasi Tambahan 1)</div>
                    </td>
                    <td style="width: 33.33%; padding: 0 3px;">
                        <div class="doc-box-empty">(Dokumentasi Tambahan 2)</div>
                    </td>
                    <td style="width: 33.33%; padding-left: 6px;">
                        <div class="doc-box-empty">(Dokumentasi Tambahan 3)</div>
                    </td>
                @endif
            </tr>
        </table>
    </div>
</body>
</html>

