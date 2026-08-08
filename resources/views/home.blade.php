@php
    use Illuminate\Support\Js;
@endphp
@extends('layouts.app')

@section('content')
<div class="bg-white rounded-2xl shadow-sm p-4 sm:p-7 max-w-6xl mx-auto border border-gray-100" x-data="calendarModal()" @keydown.escape.window="closeModal()">

    {{-- Header Banner --}}
    <div class="flex items-start justify-between flex-wrap gap-4 mb-6 pb-5 border-b border-gray-100">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-red-600 animate-pulse"></span>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Kalender Pemeliharaan Sarana Prasarana</h1>
            </div>
            <p class="text-gray-500 text-sm">
                Jadwal &amp; status verifikasi pengajuan unit operasional secara real-time.
                <span class="hidden sm:inline text-gray-400">— Klik tanggal bertanda untuk melihat detail.</span>
            </p>
        </div>

        {{-- Ringkasan KPI Status Badges (Menunggu, Disetujui, Ditolak) --}}
        <div class="grid grid-cols-1 sm:flex sm:flex-wrap items-center gap-2 w-full sm:w-auto">
            <div class="flex items-center justify-between sm:justify-start gap-2 text-xs font-semibold bg-amber-50 border border-amber-200 text-amber-900 rounded-xl px-3 py-1.5 shadow-2xs">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Menunggu:</span>
                </div>
                <span class="bg-amber-200/80 text-amber-950 px-2 py-0.5 rounded-md text-[11px] font-bold">{{ $ringkasan['menunggu'] }}</span>
            </div>
            <div class="flex items-center justify-between sm:justify-start gap-2 text-xs font-semibold bg-blue-50 border border-blue-200 text-blue-900 rounded-xl px-3 py-1.5 shadow-2xs">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    <span>Disetujui / Bengkel:</span>
                </div>
                <span class="bg-blue-200/80 text-blue-950 px-2 py-0.5 rounded-md text-[11px] font-bold">{{ $ringkasan['disetujui'] }}</span>
            </div>
            <div class="flex items-center justify-between sm:justify-start gap-2 text-xs font-semibold bg-red-50 border border-red-200 text-red-900 rounded-xl px-3 py-1.5 shadow-2xs">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-600"></span>
                    <span>Ditolak:</span>
                </div>
                <span class="bg-red-200/80 text-red-950 px-2 py-0.5 rounded-md text-[11px] font-bold">{{ $ringkasan['ditolak'] }}</span>
            </div>
        </div>
    </div>

    {{-- Navigasi Bulan & Dropdown Pilih Bulan/Tahun (Mobile Single Row Layout) --}}
    <div class="flex items-center justify-between gap-1.5 sm:gap-3 mb-6 bg-slate-50 border border-slate-200/80 rounded-2xl p-2 sm:p-3 shadow-2xs">

        {{-- Tombol Bulan Sebelumnya --}}
        <a href="{{ route('home', ['month' => $bulanSebelumnya->month, 'year' => $bulanSebelumnya->year]) }}"
           class="inline-flex items-center justify-center gap-1 px-2.5 sm:px-3.5 py-2 text-xs sm:text-sm font-bold rounded-xl text-slate-700 bg-white border border-slate-200 shadow-2xs hover:bg-slate-100 hover:text-slate-900 transition-all flex-shrink-0"
           title="Bulan Sebelumnya">
            <i data-lucide="chevron-left" class="w-4 h-4 text-slate-600"></i>
            <span class="hidden sm:inline">Sebelumnya</span>
        </a>

        {{-- Dropdown Form Pilih Bulan & Tahun langsung --}}
        <form action="{{ route('home') }}" method="GET" class="flex items-center gap-1.5 justify-center flex-1 min-w-0">
            <div class="flex items-center gap-1 sm:gap-1.5 bg-white border border-slate-200 rounded-xl px-2 sm:px-3 py-1.5 shadow-2xs max-w-full overflow-hidden">
                <i data-lucide="calendar" class="w-4 h-4 text-red-600 flex-shrink-0"></i>

                {{-- Select Bulan --}}
                <select name="month" onchange="this.form.submit()"
                        class="bg-transparent text-xs sm:text-sm font-bold text-slate-800 focus:outline-none cursor-pointer py-0.5 font-sans truncate max-w-[90px] xs:max-w-[110px] sm:max-w-none">
                    @foreach([
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ] as $mNum => $mName)
                        <option value="{{ $mNum }}" @selected($bulanAktif->month == $mNum)>{{ $mName }}</option>
                    @endforeach
                </select>

                {{-- Select Tahun Dinamis --}}
                <select name="year" onchange="this.form.submit()"
                        class="bg-transparent text-xs sm:text-sm font-bold text-slate-800 focus:outline-none cursor-pointer py-0.5 border-l border-slate-200 pl-1 sm:pl-2 font-sans">
                    @foreach(($availableYears ?? range(2020, (int)date('Y') + 10)) as $y)
                        <option value="{{ $y }}" @selected($bulanAktif->year == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            @unless($bulanAktif->isCurrentMonth())
                <a href="{{ route('home') }}"
                   class="text-[10px] sm:text-[11px] px-2 sm:px-2.5 py-1.5 rounded-xl bg-red-100/90 text-red-800 hover:bg-red-200 transition-colors font-bold shadow-2xs inline-flex items-center gap-1 flex-shrink-0"
                   title="Kembali ke Bulan Saat Ini">
                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                    <span class="hidden md:inline">Hari Ini</span>
                </a>
            @endunless
        </form>

        {{-- Tombol Bulan Berikutnya --}}
        <a href="{{ route('home', ['month' => $bulanBerikutnya->month, 'year' => $bulanBerikutnya->year]) }}"
           class="inline-flex items-center justify-center gap-1 px-2.5 sm:px-3.5 py-2 text-xs sm:text-sm font-bold rounded-xl text-slate-700 bg-white border border-slate-200 shadow-2xs hover:bg-slate-100 hover:text-slate-900 transition-all flex-shrink-0"
           title="Bulan Berikutnya">
            <span class="hidden sm:inline">Berikutnya</span>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-600"></i>
        </a>
    </div>

    @if($totalPengajuan === 0)
        <div class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3 text-gray-400">
                <i data-lucide="calendar-x" class="w-6 h-6"></i>
            </div>
            <p class="text-sm font-bold text-gray-700">Belum Ada Pengajuan Pemeliharaan</p>
            <p class="text-xs text-gray-400 mt-1 max-w-sm">Data pengajuan perbaikan atau pemeriksaan unit pada bulan {{ $bulanAktif->translatedFormat('F Y') }} akan tampil di sini.</p>
        </div>
    @else
        <div class="rounded-xl border border-gray-200 w-full overflow-hidden shadow-xs">
            <table class="w-full border-collapse table-fixed">
                <thead>
                    <tr class="bg-slate-100/80 border-b border-gray-200">
                        @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $hari)
                            <th class="text-[11px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider py-2.5 text-center px-1">
                                {{ $hari }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($calendarWeeks as $week)
                        <tr>
                            @foreach($week as $hari)
                                @php
                                    $tanggalKey = $hari->toDateString();
                                    $eventsHariIni = $eventsByDate->get($tanggalKey, collect());
                                    $isBulanIni = $hari->month === $bulanAktif->month;
                                    $isHariIni = $hari->isToday();
                                    $adaData = $eventsHariIni->count() > 0;
                                    $adaDisetujui = $eventsHariIni->contains('status', 'disetujui');
                                @endphp
                                <td class="align-top border-r border-gray-100 last:border-r-0 p-1 sm:p-2 h-16 sm:h-20 w-[14.28%] relative transition-all duration-150
                                           {{ $isBulanIni ? 'bg-white' : 'bg-slate-50/70 opacity-60' }}
                                           {{ $adaData ? 'cursor-pointer hover:bg-red-50/70 hover:ring-2 hover:ring-inset hover:ring-red-400/50' : '' }}"
                                    @if($adaData)
                                        @click="openModal({{ Js::from($hari->translatedFormat('l, d F Y')) }}, {{ Js::from($eventsHariIni->values()) }})"
                                        title="Klik untuk melihat detail status verifikasi"
                                    @endif
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs sm:text-sm font-semibold
                                            {{ $isBulanIni ? 'text-slate-800' : 'text-slate-400' }}
                                            {{ $isHariIni ? 'inline-flex items-center justify-center w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-gradient-to-r from-red-600 to-red-800 text-white shadow-sm text-[10px] sm:text-xs font-bold' : '' }}">
                                            {{ $hari->day }}
                                        </span>

                                        @if($adaDisetujui)
                                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping" title="Unit disetujui ke bengkel"></span>
                                        @endif
                                    </div>

                                    {{-- Event Badges (Sangat Jelas dengan Status Menunggu, Disetujui, Ditolak) --}}
                                    <div class="space-y-1">
                                        @foreach($eventsHariIni->take(2) as $event)
                                            @php
                                                $badgeStyle = match($event->status) {
                                                    'menunggu'  => 'bg-amber-100/90 text-amber-900 border-amber-300',
                                                    'disetujui' => 'bg-blue-100/90 text-blue-900 border-blue-300',
                                                    'ditolak'   => 'bg-red-100/90 text-red-900 border-red-300',
                                                    default     => 'bg-slate-100 text-slate-800 border-slate-300',
                                                };
                                                $prefixLabel = match($event->status) {
                                                    'menunggu'  => '⏳ ',
                                                    'disetujui' => '✅ ',
                                                    'ditolak'   => '❌ ',
                                                    default     => '',
                                                };
                                            @endphp
                                            <div class="text-[9px] sm:text-[11px] leading-tight px-1.5 py-0.5 rounded-md border {{ $badgeStyle }} truncate font-bold shadow-2xs"
                                                 title="{{ $prefixLabel }}{{ $event->unit_nama }} ({{ ucfirst($event->status) }})">
                                                {{ $prefixLabel }}{{ $event->unit_nama }}
                                            </div>
                                        @endforeach

                                        @if($eventsHariIni->count() > 2)
                                            <div class="text-[9px] sm:text-[10px] text-red-700 font-extrabold px-0.5">
                                                +{{ $eventsHariIni->count() - 2 }} detail →
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ===================== SUMMARY STATUS ARMADA (READY VS DI BENGKEL) ===================== --}}
    <div class="mt-8 pt-6 border-t border-gray-100">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-4">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-blue-700"></i>
                    <span>Status Kesiapan Armada &amp; Pemeliharaan Unit</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Ringkasan unit operasional yang siap bertugas dan unit yang sedang berada di bengkel per hari ini.
                </p>
            </div>
            <div class="text-xs text-gray-500 font-medium bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-lg">
                Per hari ini: <strong class="text-gray-900 font-bold">{{ $hariIniString }}</strong>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Card Unit Ready --}}
            <div class="bg-gradient-to-br from-emerald-50/80 to-teal-50/40 border border-emerald-200/80 rounded-2xl p-4 sm:p-5 shadow-xs transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-base shadow-xs flex-shrink-0">
                            ✓
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-emerald-950">Total Unit Ready / Siap</h4>
                            <p class="text-[11px] text-emerald-700">Armada siap bertugas operasional</p>
                        </div>
                    </div>
                    <span class="text-3xl font-black text-emerald-700">{{ $summaryArmada['total_ready'] }}</span>
                </div>
                <div class="mt-3 pt-3 border-t border-emerald-200/60 flex items-center justify-between text-xs text-emerald-900 font-medium">
                    <span>Status Kesiapan:</span>
                    <span class="inline-flex items-center gap-1.5 font-bold text-emerald-700 bg-emerald-100/90 px-2.5 py-1 rounded-full text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Siap Bertugas (Ready)
                    </span>
                </div>
            </div>

            {{-- Card Unit Di Bengkel --}}
            <div class="bg-gradient-to-br from-amber-50/80 to-orange-50/40 border border-amber-200/80 rounded-2xl p-4 sm:p-5 shadow-xs transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold text-base shadow-xs flex-shrink-0">
                            🚛
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-amber-950">Total Unit di Bengkel</h4>
                            <p class="text-[11px] text-amber-700">Dalam pemeliharaan / perbaikan</p>
                        </div>
                    </div>
                    <span class="text-3xl font-black text-amber-700">{{ $summaryArmada['total_bengkel'] }}</span>
                </div>
                <div class="mt-3 pt-3 border-t border-amber-200/60 flex items-center justify-between text-xs text-amber-900 font-medium">
                    <span>Status Pemeliharaan:</span>
                    @if($summaryArmada['total_bengkel'] > 0)
                        <span class="inline-flex items-center gap-1.5 font-bold text-amber-800 bg-amber-100/90 px-2.5 py-1 rounded-full text-[11px]">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            {{ $summaryArmada['total_bengkel'] }} Unit di Bengkel
                        </span>
                    @else
                        <span class="text-xs text-amber-700 font-semibold">Tidak Ada Unit di Bengkel</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Detail List Unit di Bengkel (jika ada) --}}
        @if(!empty($summaryArmada['list_bengkel']) && count($summaryArmada['list_bengkel']) > 0)
            <div class="mt-4 bg-amber-50/50 border border-amber-200/80 rounded-xl p-3.5 sm:p-4">
                <h5 class="text-xs font-bold text-amber-950 mb-2.5 flex items-center gap-2">
                    <i data-lucide="wrench" class="w-3.5 h-3.5 text-amber-700"></i>
                    <span>Daftar Unit Aktif Berada di Bengkel Per Hari Ini:</span>
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach($summaryArmada['list_bengkel'] as $unitBengkel)
                        <div class="bg-white border border-amber-200 rounded-lg p-2.5 flex items-center justify-between text-xs shadow-2xs">
                            <div>
                                <strong class="text-gray-900 font-bold block">{{ $unitBengkel['unit_nama'] }}</strong>
                                <span class="text-gray-500 text-[11px]">Tgl Keberangkatan: {{ $unitBengkel['tanggal_keberangkatan'] }}</span>
                            </div>
                            <span class="bg-amber-100 text-amber-900 font-extrabold px-2.5 py-1 rounded-md text-[10.5px]">Di Bengkel</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- ===================== MODAL DETAIL SIMPEL & TANPA SCROLL KANAN-KIRI ===================== --}}
    <div x-show="modalOpen"
         x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center p-3 sm:p-4"
         style="background-color: rgba(15, 23, 42, 0.65);"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="closeModal()">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[85vh] overflow-y-auto overflow-x-hidden border border-gray-100 custom-scrollbar m-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>

            {{-- Header Modal --}}
            <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl z-10">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-red-50 text-red-700 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="calendar" class="w-4.5 h-4.5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm sm:text-base leading-tight">Detail Pengajuan</h3>
                        <p class="text-[11px] font-semibold text-red-700 mt-0.5" x-text="selectedDate"></p>
                    </div>
                </div>
                <button type="button" @click="closeModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                    <i data-lucide="x" class="w-4.5 h-4.5"></i>
                </button>
            </div>

            {{-- Body List Events --}}
            <div class="p-4 sm:p-5 space-y-3">
                <template x-for="(event, idx) in selectedEvents" :key="idx">
                    <div class="border border-slate-200 rounded-xl p-3.5 sm:p-4 bg-white shadow-xs space-y-3">
                        
                        {{-- Header Unit & Status Badge --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                            <div class="flex items-center gap-2 min-w-0">
                                <i data-lucide="truck" class="w-4 h-4 text-slate-700 flex-shrink-0"></i>
                                <h4 class="font-bold text-slate-900 text-xs sm:text-sm truncate" x-text="event.unit_nama"></h4>
                            </div>

                            <span class="inline-flex items-center gap-1.5 text-[10.5px] font-extrabold px-2.5 py-1 rounded-full w-fit shadow-2xs"
                                  :class="{
                                      'bg-amber-50 text-amber-900 border border-amber-300': event.status === 'menunggu',
                                      'bg-blue-50 text-blue-900 border border-blue-300': event.status === 'disetujui',
                                      'bg-red-50 text-red-900 border border-red-300': event.status === 'ditolak'
                                  }">
                                <span class="w-1.5 h-1.5 rounded-full"
                                      :class="{
                                          'bg-amber-500': event.status === 'menunggu',
                                          'bg-blue-600': event.status === 'disetujui',
                                          'bg-red-600': event.status === 'ditolak'
                                      }"></span>
                                <span x-text="event.status === 'menunggu' ? 'Menunggu Verifikasi' : (event.status === 'disetujui' ? 'Disetujui ke Bengkel' : 'Ditolak Admin')"></span>
                            </span>
                        </div>

                        {{-- Jadwal Keberangkatan Bengkel --}}
                        <template x-if="event.tanggal_keberangkatan && event.status === 'disetujui'">
                            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-2.5 flex items-center gap-2 text-xs font-semibold text-emerald-900">
                                <span class="text-base">🚛</span>
                                <div>
                                    <span class="text-[10px] font-bold text-emerald-700 block uppercase">Jadwal Ke Bengkel</span>
                                    <span class="font-bold text-emerald-900" x-text="event.tanggal_keberangkatan"></span>
                                </div>
                            </div>
                        </template>

                        {{-- Item Perbaikan & Badges --}}
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Item Perbaikan</span>
                            
                            {{-- Jika Ada Rincian Verifikasi Per Item --}}
                            <template x-if="event.item_verifikasis && event.item_verifikasis.length > 0">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="(it, i) in event.item_verifikasis" :key="i">
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-md border"
                                              :class="it.status === 'disetujui' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200'">
                                            <span x-text="it.status === 'disetujui' ? '✓' : '✕'"></span>
                                            <span x-text="it.nama"></span>
                                            <span class="text-[10px] font-normal" x-text="'(' + (it.status === 'disetujui' ? 'Disetujui' : 'Ditolak') + ')'"></span>
                                        </span>
                                    </template>
                                </div>
                            </template>

                            {{-- Jika Tidak Ada Rincian Per Item --}}
                            <template x-if="!event.item_verifikasis || event.item_verifikasis.length === 0">
                                <p class="text-xs font-bold text-slate-800" x-text="event.item_perbaikan || '-'"></p>
                            </template>
                        </div>

                        {{-- Catatan Admin --}}
                        <template x-if="event.catatan_admin">
                            <div class="bg-slate-50 border border-slate-200/80 rounded-lg p-2.5 text-xs text-slate-700">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block mb-0.5">Catatan Admin</span>
                                <p class="italic text-slate-800" x-text="event.catatan_admin"></p>
                            </div>
                        </template>

                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/80 rounded-b-2xl text-center">
                <p class="text-[10.5px] text-gray-400 font-medium">Tekan <kbd class="px-1 py-0.5 bg-white border border-gray-200 rounded text-gray-600 font-bold">Esc</kbd> atau klik luar untuk menutup</p>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function calendarModal() {
        return {
            modalOpen: false,
            selectedDate: '',
            selectedEvents: [],
            _scrollY: 0,

            openModal(tanggal, events) {
                this.selectedDate = tanggal;
                this.selectedEvents = events;
                this.modalOpen = true;
                this.lockScroll();
                this.$nextTick(() => {
                    if (window.lucide) lucide.createIcons();
                });
            },

            closeModal() {
                this.modalOpen = false;
                this.unlockScroll();
            },

            lockScroll() {
                this._scrollY = window.scrollY || window.pageYOffset || 0;
                document.body.style.position = 'fixed';
                document.body.style.top = `-${this._scrollY}px`;
                document.body.style.left = '0';
                document.body.style.right = '0';
                document.body.style.width = '100%';
                document.body.style.overflow = 'hidden';
            },

            unlockScroll() {
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.left = '';
                document.body.style.right = '';
                document.body.style.width = '';
                document.body.style.overflow = '';
                window.scrollTo(0, this._scrollY);
            },

            init() {
                window.addEventListener('beforeunload', () => this.unlockScroll());
            }
        };
    }
</script>
@endpush
@endsection