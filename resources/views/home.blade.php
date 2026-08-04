@php
    use Illuminate\Support\Js;
@endphp
@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 max-w-6xl mx-auto" x-data="calendarModal()" @keydown.escape.window="closeModal()">

    <div class="flex items-start justify-between flex-wrap gap-2 mb-1">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Kalender Pengajuan Pemeliharaan</h1>
            <p class="text-gray-500 text-sm mt-1">
                Status unit berdasarkan tanggal pengajuan pemeliharaan.
                <span class="hidden sm:inline">Klik tanggal yang bertanda untuk lihat detail lengkap.</span>
            </p>
        </div>
    </div>

    {{-- Navigasi bulan --}}
    <div class="flex items-center justify-between my-5 bg-gray-50 rounded-lg p-2">
        <a href="{{ route('home', ['bulan' => $bulanSebelumnya->month, 'tahun' => $bulanSebelumnya->year]) }}"
           class="inline-flex items-center gap-1 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-white hover:shadow-sm transition-all">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
            <span class="hidden sm:inline">Sebelumnya</span>
        </a>

        <div class="flex items-center gap-2">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                {{ $bulanAktif->translatedFormat('F Y') }}
            </h2>
            @unless($bulanAktif->isCurrentMonth())
                <a href="{{ route('home') }}"
                   class="text-[11px] px-2 py-1 rounded-full bg-red-50 text-red-700 hover:bg-red-100 transition-colors font-medium">
                    Hari Ini
                </a>
            @endunless
        </div>

        <a href="{{ route('home', ['bulan' => $bulanBerikutnya->month, 'tahun' => $bulanBerikutnya->year]) }}"
           class="inline-flex items-center gap-1 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-white hover:shadow-sm transition-all">
            <span class="hidden sm:inline">Berikutnya</span>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </a>
    </div>

    {{-- Legenda status + ringkasan jumlah --}}
    <div class="flex flex-wrap gap-2 mb-5">
        <div class="flex items-center gap-2 text-xs sm:text-sm bg-amber-50 border border-amber-200 rounded-lg px-3 py-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block"></span>
            <span class="text-amber-800 font-medium">Menunggu Pengajuan</span>
            <span class="text-amber-600 font-semibold">{{ $ringkasan['menunggu'] }}</span>
        </div>
        <div class="flex items-center gap-2 text-xs sm:text-sm bg-blue-50 border border-blue-200 rounded-lg px-3 py-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>
            <span class="text-blue-800 font-medium">Sedang di Bengkel</span>
            <span class="text-blue-600 font-semibold">{{ $ringkasan['dibengkel'] }}</span>
        </div>
        <div class="flex items-center gap-2 text-xs sm:text-sm bg-green-50 border border-green-200 rounded-lg px-3 py-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-green-600 inline-block"></span>
            <span class="text-green-800 font-medium">Pengajuan Selesai</span>
            <span class="text-green-600 font-semibold">{{ $ringkasan['selesai'] }}</span>
        </div>
    </div>

    @if($totalPengajuan === 0)
        <div class="flex flex-col items-center justify-center py-14 text-center border border-dashed border-gray-200 rounded-xl">
            <i data-lucide="calendar-x" class="w-10 h-10 text-gray-300 mb-3"></i>
            <p class="text-sm font-medium text-gray-500">Belum ada pengajuan pada bulan ini</p>
            <p class="text-xs text-gray-400 mt-1">Data akan muncul otomatis begitu ada pengajuan baru.</p>
        </div>
    @else
        <div class="rounded-lg border border-gray-100 overflow-x-auto">
            <table class="w-full border-collapse table-fixed min-w-[560px]">
                <thead>
                    <tr class="bg-gray-50">
                        @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $hari)
                            <th class="text-[10px] sm:text-sm font-semibold text-gray-500 uppercase tracking-wide py-2.5 border-b border-gray-200 text-center px-1">
                                {{ $hari }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
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
                                <td class="align-top border border-gray-100 p-1 sm:p-1.5 h-20 sm:h-28 w-[14.28%] relative group
                                           {{ $isBulanIni ? 'bg-white' : 'bg-gray-50/60' }}
                                           {{ $adaData ? 'cursor-pointer hover:bg-red-50/60 hover:ring-1 hover:ring-inset hover:ring-red-200 transition-all duration-150' : '' }}"
                                    @if($adaData)
                                        @click="openModal({{ Js::from($hari->translatedFormat('l, d F Y')) }}, {{ Js::from($eventsHariIni->values()) }})"
                                        title="Klik untuk lihat detail"
                                    @endif
                                >
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[11px] sm:text-sm font-medium
                                            {{ $isBulanIni ? 'text-gray-800' : 'text-gray-300' }}
                                            {{ $isHariIni ? 'inline-flex items-center justify-center w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-red-700 text-white shadow-sm' : '' }}">
                                            {{ $hari->day }}
                                        </span>

                                        @if($adaRusak)
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse" title="Ada unit di bengkel"></span>
                                        @endif
                                    </div>

                                    <div class="space-y-1">
                                        @foreach($eventsHariIni->take(2) as $event)
                                            @php
                                                $warna = match($event->status) {
                                                    'menunggu'  => 'bg-amber-100 text-amber-800 border-amber-300',
                                                    'dibengkel' => 'bg-blue-100 text-blue-800 border-blue-300',
                                                    'selesai'   => 'bg-green-100 text-green-800 border-green-300',
                                                    default     => 'bg-gray-100 text-gray-700 border-gray-300',
                                                };
                                            @endphp
                                            <div class="text-[9px] sm:text-[11px] leading-tight px-1 sm:px-1.5 py-0.5 sm:py-1 rounded border {{ $warna }} truncate">
                                                {{ $event->unit_nama }}
                                            </div>
                                        @endforeach

                                        @if($eventsHariIni->count() > 2)
                                            <div class="text-[9px] sm:text-[10px] text-red-600 font-medium px-1 group-hover:underline">
                                                +{{ $eventsHariIni->count() - 2 }} →
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
         style="background-color: rgba(17,24,39,0.55);"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="closeModal()">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto overscroll-contain"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>

            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="calendar-days" class="w-4.5 h-4.5 text-red-700"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Detail Pengajuan</h3>
                        <p class="text-xs text-gray-500" x-text="selectedDate"></p>
                    </div>
                </div>
                <button type="button" @click="closeModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <i data-lucide="x" class="w-4.5 h-4.5"></i>
                </button>
            </div>

            <div class="p-5 space-y-3">
                <template x-for="(event, idx) in selectedEvents" :key="idx">
                    <div class="border border-gray-200 rounded-xl p-4 hover:border-gray-300 transition-colors">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <i data-lucide="truck" class="w-4 h-4 text-gray-400 flex-shrink-0"></i>
                                <h4 class="font-medium text-gray-900 text-sm" x-text="event.unit_nama"></h4>
                            </div>
                            <span class="text-[11px] font-medium px-2.5 py-1 rounded-full whitespace-nowrap flex items-center gap-1"
                                  :class="{
                                      'bg-amber-100 text-amber-800': event.status === 'menunggu',
                                      'bg-blue-100 text-blue-800': event.status === 'dibengkel',
                                      'bg-green-100 text-green-800': event.status === 'selesai'
                                  }">
                                <span class="w-1.5 h-1.5 rounded-full"
                                      :class="{
                                          'bg-amber-500': event.status === 'menunggu',
                                          'bg-blue-500': event.status === 'dibengkel',
                                          'bg-green-500': event.status === 'selesai'
                                      }"></span>
                                <span x-text="event.status === 'menunggu' ? 'Menunggu Pengajuan' : (event.status === 'dibengkel' ? 'Sedang di Bengkel' : 'Selesai')"></span>
                            </span>
                        </div>

                        <template x-if="event.keterangan">
                            <div class="mt-2.5 pt-2.5 border-t border-gray-100 flex gap-2">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 mb-0.5">Keterangan Kerusakan</p>
                                    <p class="text-sm text-gray-700 leading-relaxed" x-text="event.keterangan"></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="!event.keterangan">
                            <p class="text-xs text-gray-400 mt-2.5 pt-2.5 border-t border-gray-100 italic flex items-center gap-1.5">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                Tidak ada catatan kerusakan
                            </p>
                        </template>
                    </div>
                </template>
            </div>

            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                <p class="text-[11px] text-gray-400 text-center">Tekan <kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded text-gray-500">Esc</kbd> atau klik di luar untuk menutup</p>
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

            // Kunci scroll body HANYA saat modal terbuka, dan pastikan
            // selalu dikembalikan (tidak "nyangkut" overflow-hidden
            // yang menyebabkan halaman tidak bisa discroll ke bawah).
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
                // Jaga-jaga: kalau user pindah halaman (klik navigasi bulan)
                // saat modal masih terbuka, scroll lock tetap dilepas dulu.
                window.addEventListener('beforeunload', () => this.unlockScroll());
            }
        };
    }
</script>
@endpush
@endsection