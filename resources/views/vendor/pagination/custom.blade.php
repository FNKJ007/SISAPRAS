@if ($paginator->hasPages())
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; font-size:12.5px; color:#64748B;">
        
        {{-- Info Menampilkan Data --}}
        <div>
            Menampilkan <strong>{{ $paginator->firstItem() ?? 0 }}</strong> - <strong>{{ $paginator->lastItem() ?? 0 }}</strong> dari <strong>{{ $paginator->total() }}</strong> data
        </div>

        {{-- Navigasi Tombol Halaman --}}
        <div style="display:flex; align-items:center; gap:4px; flex-wrap:wrap;">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span style="padding:6px 12px; background:#F8FAFC; color:#CBD5E1; border:1px solid #E2E8F0; border-radius:8px; font-weight:600; cursor:not-allowed; user-select:none;">
                    &laquo; Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" style="padding:6px 12px; background:#FFFFFF; color:#1E293B; border:1px solid #CBD5E1; border-radius:8px; font-weight:600; text-decoration:none; transition:all 0.15s;" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='#FFFFFF'">
                    &laquo; Sebelumnya
                </a>
            @endif

            {{-- Nomor Halaman --}}
            @foreach ($elements as $element)
                {{-- String "..." --}}
                @if (is_string($element))
                    <span style="padding:6px 10px; color:#94A3B8; font-weight:600;">{{ $element }}</span>
                @endif

                {{-- Array Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span style="padding:6px 12px; background:#1B2A6B; color:#FFFFFF; border:1px solid #1B2A6B; border-radius:8px; font-weight:700;">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" style="padding:6px 12px; background:#FFFFFF; color:#334155; border:1px solid #CBD5E1; border-radius:8px; font-weight:600; text-decoration:none; transition:all 0.15s;" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='#FFFFFF'">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" style="padding:6px 12px; background:#FFFFFF; color:#1E293B; border:1px solid #CBD5E1; border-radius:8px; font-weight:600; text-decoration:none; transition:all 0.15s;" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='#FFFFFF'">
                    Selanjutnya &raquo;
                </a>
            @else
                <span style="padding:6px 12px; background:#F8FAFC; color:#CBD5E1; border:1px solid #E2E8F0; border-radius:8px; font-weight:600; cursor:not-allowed; user-select:none;">
                    Selanjutnya &raquo;
                </span>
            @endif
        </div>

    </div>
@elseif($paginator->total() > 0)
    <div style="font-size:12.5px; color:#64748B;">
        Menampilkan seluruh <strong>{{ $paginator->total() }}</strong> data
    </div>
@endif
