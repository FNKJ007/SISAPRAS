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
                Jadwal &amp; status kondisi unit kendaraan dan peralatan secara real-time.
                <span class="hidden sm:inline text-gray-400">— Klik tanggal bertanda untuk melihat detail.</span>
            </p>
        </div>

        {{-- Ringkasan KPI Status Badges --}}
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-2 text-xs font-semibold bg-amber-50 border border-amber-200 text-amber-900 rounded-xl px-3.5 py-2 shadow-xs">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span>Menunggu:</span>
                <span class="bg-amber-200/60 text-amber-950 px-2 py-0.5 rounded-md text-[11px] font-bold">{{ $ringkasan['menunggu'] }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold bg-blue-50 border border-blue-200 text-blue-900 rounded-xl px-3.5 py-2 shadow-xs">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                <span>Di Bengkel:</span>
                <span class="bg-blue-200/60 text-blue-950 px-2 py-0.5 rounded-md text-[11px] font-bold">{{ $ringkasan['dibengkel'] }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl px-3.5 py-2 shadow-xs">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                <span>Selesai:</span>
                <span class="bg-emerald-200/60 text-emerald-950 px-2 py-0.5 rounded-md text-[11px] font-bold">{{ $ringkasan['selesai'] }}</span>
            </div>
        </div>
    </div>

    {{-- Navigasi Bulan --}}
    <div class="flex items-center justify-between mb-6 bg-slate-50 border border-slate-200/60 rounded-xl p-2 sm:p-2.5">
        <a href="{{ route('home', ['bulan' => $bulanSebelumnya->month, 'tahun' => $bulanSebelumnya->year]) }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-lg text-slate-700 bg-white border border-slate-200 shadow-xs hover:bg-slate-100 hover:text-slate-900 transition-all">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
            <span class="hidden sm:inline">Bulan Sebelumnya</span>
        </a>

        <div class="flex items-center gap-2.5">
            <i data-lucide="calendar" class="w-5 h-5 text-red-700"></i>
            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-wide">
                {{ $bulanAktif->translatedFormat('F Y') }}
            </h2>
            @unless($bulanAktif->isCurrentMonth())
                <a href="{{ route('home') }}"
                   class="text-[11px] px-2.5 py-1 rounded-full bg-red-100 text-red-800 hover:bg-red-200 transition-colors font-bold shadow-xs">
                    Hari Ini
                </a>
            @endunless
        </div>

        <a href="{{ route('home', ['bulan' => $bulanBerikutnya->month, 'tahun' => $bulanBerikutnya->year]) }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-semibold rounded-lg text-slate-700 bg-white border border-slate-200 shadow-xs hover:bg-slate-100 hover:text-slate-900 transition-all">
            <span class="hidden sm:inline">Bulan Berikutnya</span>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
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
                                    $adaRusak = $eventsHariIni->contains('status', 'dibengkel');
                                @endphp
                                <td class="align-top border-r border-gray-100 last:border-r-0 p-1 sm:p-2 h-16 sm:h-20 w-[14.28%] relative transition-all duration-150
                                           {{ $isBulanIni ? 'bg-white' : 'bg-slate-50/70 opacity-60' }}
                                           {{ $adaData ? 'cursor-pointer hover:bg-red-50/70 hover:ring-2 hover:ring-inset hover:ring-red-400/50' : '' }}"
                                    @if($adaData)
                                        @click="openModal({{ Js::from($hari->translatedFormat('l, d F Y')) }}, {{ Js::from($eventsHariIni->values()) }})"
                                        title="Klik untuk melihat detail pengajuan"
                                    @endif
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs sm:text-sm font-semibold
                                            {{ $isBulanIni ? 'text-slate-800' : 'text-slate-400' }}
                                            {{ $isHariIni ? 'inline-flex items-center justify-center w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-gradient-to-r from-red-600 to-red-800 text-white shadow-sm text-[10px] sm:text-xs font-bold' : '' }}">
                                            {{ $hari->day }}
                                        </span>

                                        @if($adaRusak)
                                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping" title="Ada unit sedang di bengkel"></span>
                                        @endif
                                    </div>

                                    {{-- Event Badges (Presisi & Sangat Jelas) --}}
                                    <div class="space-y-1">
                                        @foreach($eventsHariIni->take(2) as $event)
                                            @php
                                                $badgeStyle = match($event->status) {
                                                    'menunggu'  => 'bg-amber-100/90 text-amber-900 border-amber-300',
                                                    'dibengkel' => 'bg-blue-100/90 text-blue-900 border-blue-300',
                                                    'selesai'   => 'bg-emerald-100/90 text-emerald-900 border-emerald-300',
                                                    default     => 'bg-slate-100 text-slate-800 border-slate-300',
                                                };
                                            @endphp
                                            <div class="text-[9px] sm:text-[11px] leading-tight px-1.5 py-0.5 rounded-md border {{ $badgeStyle }} truncate font-bold shadow-2xs">
                                                {{ $event->unit_nama }}
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

    {{-- ===================== MODAL DETAIL ===================== --}}
    <div x-show="modalOpen"
         x-cloak
         class="fixed inset-0 z-[1200] flex items-center justify-center p-4"
         style="background-color: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="closeModal()">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto overscroll-contain border border-gray-100"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-700 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="calendar-days" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Detail Pengajuan Pemeliharaan</h3>
                        <p class="text-xs font-semibold text-red-700" x-text="selectedDate"></p>
                    </div>
                </div>
                <button type="button" @click="closeModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-3.5">
                <template x-for="(event, idx) in selectedEvents" :key="idx">
                    <div class="border border-gray-200 rounded-xl p-4 hover:border-red-300 hover:shadow-sm transition-all bg-slate-50/50">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <i data-lucide="truck" class="w-4 h-4 text-slate-500 flex-shrink-0"></i>
                                <h4 class="font-bold text-slate-900 text-sm" x-text="event.unit_nama"></h4>
                            </div>
                            <span class="text-[11px] font-bold px-2.5 py-1 rounded-full whitespace-nowrap flex items-center gap-1.5 shadow-2xs"
                                  :class="{
                                      'bg-amber-100 text-amber-900 border border-amber-300': event.status === 'menunggu',
                                      'bg-blue-100 text-blue-900 border border-blue-300': event.status === 'dibengkel',
                                      'bg-emerald-100 text-emerald-900 border border-emerald-300': event.status === 'selesai'
                                  }">
                                <span class="w-2 h-2 rounded-full"
                                      :class="{
                                          'bg-amber-500': event.status === 'menunggu',
                                          'bg-blue-600': event.status === 'dibengkel',
                                          'bg-emerald-600': event.status === 'selesai'
                                      }"></span>
                                <span x-text="event.status === 'menunggu' ? 'Menunggu Pengajuan' : (event.status === 'dibengkel' ? 'Sedang di Bengkel' : 'Pengajuan Selesai')"></span>
                            </span>
                        </div>

                        <template x-if="event.keterangan">
                            <div class="mt-3 pt-2.5 border-t border-gray-200/80 flex gap-2">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <p class="text-xs font-bold text-gray-700 mb-0.5">Catatan Kerusakan / Keterangan</p>
                                    <p class="text-sm text-gray-800 leading-relaxed font-medium" x-text="event.keterangan"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="!event.keterangan">
                            <p class="text-xs text-gray-500 mt-2.5 pt-2.5 border-t border-gray-200/80 italic flex items-center gap-1.5 font-medium">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                                Tidak ada catatan khusus
                            </p>
                        </template>
                    </div>
                </template>
            </div>

            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                <p class="text-[11px] text-gray-400 text-center font-medium">Tekan <kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded text-gray-600 font-bold">Esc</kbd> atau klik di luar untuk menutup modal</p>
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