{{-- 
  KOMPONEN PAGINASI KUSTOM — SINFAS
  File: resources/views/vendor/pagination/simple-default.blade.php
  Fungsi:
  - Template paginasi ramah pengguna dengan tombol "Sebelumnya" dan "Berikutnya".
  - Penomoran halaman aktif dan pemisah elipsis ("...") untuk navigasi tabel data besar.
--}}
@if ($paginator->hasPages())
    <nav>
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.35rem; margin-top: 1.25rem;">
            {{-- Tombol Halaman Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn pagination-btn--disabled">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" style="text-decoration: none;">Sebelumnya</a>
            @endif

            {{-- Elemen Nomor Halaman --}}
            @foreach ($elements as $element)
                {{-- Pemisah Tiga Titik (...) --}}
                @if (is_string($element))
                    <span class="pagination-btn pagination-btn--disabled">{{ $element }}</span>
                @endif

                {{-- Tautan Nomor Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-btn pagination-btn--active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-btn" style="text-decoration: none;">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Halaman Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn" style="text-decoration: none;">Berikutnya</a>
            @else
                <span class="pagination-btn pagination-btn--disabled">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
