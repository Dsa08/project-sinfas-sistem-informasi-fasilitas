{{-- 
  KOMPONEN JUDUL KOLOM TABEL DENGAN PENYORTIRAN (SORTABLE HEADING) — SINFAS
  File: resources/views/components/sortable-table-heading.blade.php
  Fitur:
  - Menyediakan tautan penyortiran 3 kondisi (asc -> desc -> default) pada header tabel.
  - Menampilkan indikator panah naik/turun interaktif dengan status aktif yang berwarna kontras.
  - Menjaga parameter query string lain (pencarian, tab, filter) tetap utuh saat URL disortir.
--}}
@props([
    'label',
    'column',
    'sortKey',
    'dirKey',
    'pageKey',
    'tab',
])

@php
    $active = request($sortKey) === $column;
    $direction = request($dirKey);
    $nextDirection = !$active ? 'asc' : ($direction === 'asc' ? 'desc' : null);
    $query = request()->except([$sortKey, $dirKey, $pageKey]);
    $query['tab'] = $tab;

    if ($nextDirection) {
        $query[$sortKey] = $column;
        $query[$dirKey] = $nextDirection;
    }

    $sortUrl = url()->current() . '?' . http_build_query($query);
    $sortTitle = !$active
        ? 'Urutkan menaik'
        : ($direction === 'asc' ? 'Urutkan menurun' : 'Kembali ke urutan awal');
@endphp

<a href="{{ $sortUrl }}" class="th-content {{ $active ? 'th-content--active' : '' }}" title="{{ $sortTitle }}">
    <span>{{ $label }}</span>
    <svg width="11" height="14" viewBox="0 0 12 14" fill="none" aria-hidden="true" style="flex-shrink: 0; vertical-align: middle;">
        <path d="M6 1L1.5 6.5H10.5L6 1Z" fill="{{ $active && $direction === 'asc' ? '#1D67F2' : '#111827' }}" opacity="{{ $active && $direction === 'desc' ? '0.2' : '0.85' }}"/>
        <path d="M6 13L10.5 7.5H1.5L6 13Z" fill="{{ $active && $direction === 'desc' ? '#1D67F2' : '#111827' }}" opacity="{{ $active && $direction === 'asc' ? '0.2' : '0.85' }}"/>
    </svg>
</a>
