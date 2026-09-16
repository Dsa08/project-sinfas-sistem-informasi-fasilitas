{{-- 
  DASHBOARD SISWA (KATALOG SARANA PRASARANA) — SINFAS
  File: resources/views/user/dashboard.blade.php
  Fitur Sesuai Desain Figma:
  - Hero banner sambutan: "Mau Pinjam Apa Hari Ini?"
  - Search bar & Filter dropdown interaktif.
  - Navigasi Kategori Cepat (Kapsul/Chips): Tombol cepat sesuai kategori di database (🎤 Audio & Sound System, 💡 Proyektor & Presentasi, 📷 Kamera & Dokumentasi, 🔌 Kabel & Adapter, 💻 Peralatan Lab & Multimedia).
  - Carousel Kategori Horizontal (Pengganti Pagination di beranda utama) dengan tombol panah navigasi (< dan >) dan smooth horizontal scrolling.
  - Tombol "Lihat Semua >" di setiap sudut kanan judul kategori untuk membuka seluruh inventaris secara penuh di halaman terpisah.
  - Detail & Feedback Visual:
    * Label "Category: [Nama Kategori]"
    * Badge stok "X tersedia" hijau dan "0 tersedia" merah
    * Tombol aksi "Pinjam Alat" (biru) dan "Stok Habis" (abu-abu disabled untuk stok 0)
--}}
@extends('layouts.app')

@section('title', 'Dashboard - SINFAS')

@section('content')
<div class="dashboard-container">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="flash-msg flash-msg--success" id="flash-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
            {{ session('success') }}
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;margin-left:auto;font-size:1.1rem;">&times;</button>
        </div>
    @endif

    {{-- Hero Banner (Personalized & Modern Minimalist Blue) --}}
    @php
        $siswaNama = Auth::user()->siswa->nama ?? Auth::user()->nama ?? Auth::user()->username ?? 'Siswa';
        $firstName = explode(' ', trim($siswaNama))[0];
    @endphp
    <div class="dashboard-hero">
        <div class="dashboard-hero-content">
            <div class="dashboard-hero-tag">
                <span class="dashboard-hero-tag-dot"></span> Selamat Datang di SINFAS
            </div>
            <h2 class="dashboard-hero-text">Halo, {{ $firstName }}! 👋</h2>
            <p class="dashboard-hero-subtext">Mau pinjam sarana atau peralatan apa hari ini?</p>
        </div>
        <div class="dashboard-hero-ornament">
            <svg width="180" height="110" viewBox="0 0 180 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="140" cy="20" r="70" fill="white" fill-opacity="0.08"/>
                <circle cx="90" cy="85" r="45" fill="white" fill-opacity="0.06"/>
                <path d="M40 70 L65 30 L90 70 Z" stroke="white" stroke-opacity="0.12" stroke-width="2" fill="none"/>
            </svg>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="search-section" id="search-section">
        <form action="{{ route('dashboard') }}" method="GET" class="search-bar" id="search-form">
            <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer; display: flex; align-items: center;" title="Cari">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
            </button>
            <input
                type="text"
                class="search-input"
                id="search-input"
                name="search"
                placeholder="Cari alat, kategori, atau status..."
                value="{{ request('search') }}"
                autocomplete="off"
            >
            @if(request('search'))
                <a href="{{ route('dashboard', request()->except('search')) }}" style="color: #9ca3af; text-decoration: none; font-size: 1.15rem; padding: 0 4px; line-height: 1;" title="Hapus pencarian">&times;</a>
            @endif
            @if(request('kategori'))
                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
            @endif
        </form>
        <div class="filter-dropdown-wrapper">
            <button class="filter-btn" id="filter-btn" type="button" onclick="toggleFilterDropdown()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="6" x2="20" y2="6"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                    <line x1="11" y1="18" x2="13" y2="18"/>
                </svg>
                Filter
            </button>
            <div class="filter-dropdown" id="filter-dropdown">
                <a href="{{ route('dashboard') }}" class="filter-dropdown-item {{ !request('kategori') && !request('search') ? 'filter-dropdown-item--active' : '' }}">
                    Semua Kategori (Beranda)
                </a>
                @if(isset($popularItems) && $popularItems->isNotEmpty())
                <a href="{{ route('dashboard', ['kategori' => 'popular', 'view' => 'all']) }}" class="filter-dropdown-item {{ request('kategori') === 'popular' ? 'filter-dropdown-item--active' : '' }}">
                    🔥 Sering Dipinjam
                </a>
                @endif
                @foreach($categories as $cat)
                <a href="{{ route('dashboard', ['kategori' => $cat->id_kategori, 'view' => 'all']) }}" class="filter-dropdown-item {{ request('kategori') == $cat->id_kategori ? 'filter-dropdown-item--active' : '' }}">
                    {{ $cat->nama_kategori }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Widget Aktivitas Pinjaman Siswa (Hanya tampil jika ada pinjaman aktif atau pengajuan menunggu) --}}
    @if((isset($activeLoans) && $activeLoans->isNotEmpty()) || (isset($pendingLoans) && $pendingLoans->isNotEmpty()))
    <div class="active-loans-widget" id="active-loans-widget">
        {{-- 1. Pinjaman Sedang Berjalan (Disetujui) --}}
        @foreach($activeLoans as $activeLoan)
            @php
                $tglKembali = $activeLoan->tanggal_kembali;
                $diffDays = now()->startOfDay()->diffInDays($tglKembali->copy()->startOfDay(), false);
                $isOverdue = $diffDays < 0;
                $isToday = $diffDays == 0;
            @endphp
            <div class="active-loan-card {{ $isOverdue ? 'active-loan-card--overdue' : '' }}">
                <div class="active-loan-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                        <path d="m3.3 7 8.7 5 8.7-5"/>
                        <path d="M12 22V12"/>
                    </svg>
                </div>
                <div class="active-loan-info">
                    <div class="active-loan-badge-row">
                        <span class="active-loan-pill active-loan-pill--approved">Sedang Dipinjam</span>
                        @if($isOverdue)
                            <span class="active-loan-pill active-loan-pill--danger">Terlambat {{ abs($diffDays) }} Hari</span>
                        @elseif($isToday)
                            <span class="active-loan-pill active-loan-pill--warning">Batas Pengembalian Hari Ini</span>
                        @else
                            <span class="active-loan-pill active-loan-pill--info">Sisa {{ $diffDays }} Hari</span>
                        @endif
                    </div>
                    <h4 class="active-loan-title">{{ $activeLoan->barang->nama_barang ?? 'Barang Sarana' }}</h4>
                    <p class="active-loan-desc">
                        Batas kembali: <strong>{{ $tglKembali->format('d M Y') }}</strong> &bull; Kode Pinjam: <code>{{ $activeLoan->kode_pinjam }}</code>
                    </p>
                </div>
                <div class="active-loan-action">
                    <a href="{{ route('loan.status') }}" class="btn-active-loan-cta">
                        Kembalikan Alat
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/>
                            <path d="m12 5 7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach

        {{-- 2. Pengajuan Menunggu Verifikasi --}}
        @foreach($pendingLoans as $pendingLoan)
            <div class="active-loan-card active-loan-card--pending">
                <div class="active-loan-icon-box active-loan-icon-box--pending">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="active-loan-info">
                    <div class="active-loan-badge-row">
                        <span class="active-loan-pill active-loan-pill--pending">Menunggu Verifikasi Admin</span>
                    </div>
                    <h4 class="active-loan-title">{{ $pendingLoan->barang->nama_barang ?? 'Barang Sarana' }}</h4>
                    <p class="active-loan-desc">
                        Diajukan pada {{ $pendingLoan->created_at->format('d M Y, H:i') }} &bull; Kode Pinjam: <code>{{ $pendingLoan->kode_pinjam }}</code>
                    </p>
                </div>
                <div class="active-loan-action">
                    <a href="{{ route('loan.status') }}" class="btn-active-loan-cta btn-active-loan-cta--secondary">
                        Pantau Status
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/>
                            <path d="m12 5 7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- Kategori Cepat (Kapsul / Chips) --}}
    @php
        $categoryIconMap = [
            'Audio & Sound System'       => '🎤',
            'Proyektor & Presentasi'     => '💡',
            'Kamera & Dokumentasi'       => '📷',
            'Kabel & Adapter'            => '🔌',
            'Peralatan Lab & Multimedia' => '💻',
            'Multimedia & Elektronik'    => '💡',
            'Sarana & Peralatan Kelas'   => '📦',
            'Audio & Video'              => '🎤',
            'Olahraga & Seni'            => '⚽',
        ];
    @endphp

    <div class="quick-categories-bar" id="quick-categories-bar">
        <span class="quick-categories-label">Kategori Cepat</span>
        <div class="quick-chips-list" id="quick-chips-list">
            <button type="button" 
                    class="quick-chip-btn {{ empty(request('kategori')) ? 'quick-chip-btn--active' : '' }}" 
                    id="chip-cat-all" 
                    onclick="filterCategory('all')">
                <span>✨</span> Semua
            </button>
            @if(isset($popularItems) && $popularItems->isNotEmpty())
            <button type="button" 
                    class="quick-chip-btn {{ request('kategori') === 'popular' ? 'quick-chip-btn--active' : '' }}" 
                    id="chip-cat-popular" 
                    data-cat-id="popular"
                    data-cat-name="Sering Dipinjam"
                    onclick="filterCategory('popular')">
                <span>🔥</span> Sering Dipinjam
            </button>
            @endif
            @foreach($categories as $cat)
                @php
                    $icon = $categoryIconMap[$cat->nama_kategori] ?? '🏷️';
                    $isActive = request('kategori') == $cat->id_kategori;
                @endphp
                <button type="button" 
                        class="quick-chip-btn {{ $isActive ? 'quick-chip-btn--active' : '' }}" 
                        id="chip-cat-{{ $cat->id_kategori }}" 
                        data-cat-id="{{ $cat->id_kategori }}"
                        data-cat-name="{{ $cat->nama_kategori }}"
                        onclick="filterCategory('{{ $cat->id_kategori }}')">
                    <span>{{ $icon }}</span> {{ $cat->nama_kategori }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- MODE 1: TAMPILAN FILTERED (Pencarian atau "Lihat Semua" Kategori) --}}
    {{-- ================================================================= --}}
    @if(!empty($isFiltered))
        <div class="filtered-header">
            <div style="display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap;">
                <a href="{{ route('dashboard') }}" class="btn-back-katalog">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Kembali ke Beranda Katalog
                </a>
                <span style="color: #9ca3af;">|</span>
                <span style="font-size: 0.92rem; color: #4b5563;">
                    @if(request('search'))
                        Hasil pencarian untuk: <strong>"{{ request('search') }}"</strong>
                    @elseif($activeCategory)
                        Daftar Lengkap: <strong>{{ $activeCategory->nama_kategori }}</strong>
                    @else
                        Seluruh Inventaris
                    @endif
                    ({{ $items->total() }} barang ditemukan)
                </span>
            </div>

            <a href="{{ route('dashboard') }}" style="font-size: 0.85rem; color: #1D67F2; text-decoration: none; font-weight: 600;">
                Reset Filter
            </a>
        </div>

        <div class="items-grid-container" id="filtered-items-grid">
            @forelse($items as $item)
            <div class="item-card-horizontal" id="item-{{ $item->kode_barang }}">
                {{-- Foto Barang --}}
                <div class="card-thumb">
                    @if(!empty($item->foto) && file_exists(public_path($item->foto)))
                        <img src="{{ asset($item->foto) }}" alt="{{ $item->nama_barang }}" loading="lazy">
                    @else
                        <div class="card-thumb-placeholder">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                            <span style="font-size: 0.68rem; margin-top: 0.25rem;">Foto Belum Ada</span>
                        </div>
                    @endif
                </div>

                {{-- Detail Informasi Barang --}}
                <div class="card-details">
                    <h4 class="card-title" title="{{ $item->nama_barang }}">{{ $item->nama_barang }}</h4>
                    <p class="card-category">Category: {{ $item->kategori->nama_kategori ?? 'Equipment' }}</p>

                    @if($item->jumlah_baik > 0)
                        <span class="badge-stock badge-stock--available">{{ $item->jumlah_baik }} tersedia</span>
                        <a href="{{ route('loan.request', $item->kode_barang) }}" class="btn-pinjam-pill btn-pinjam-pill--primary" id="btn-pinjam-{{ $item->kode_barang }}">
                            Pinjam Alat
                        </a>
                    @else
                        <span class="badge-stock badge-stock--empty">0 tersedia</span>
                        <button type="button" class="btn-pinjam-pill btn-pinjam-pill--disabled" disabled id="btn-pinjam-{{ $item->kode_barang }}">
                            Stok Habis
                        </button>
                    @endif
                </div>
            </div>
            @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem; background: #ffffff; border-radius: 12px; border: 1px dashed #d1d5db;">
                <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" style="margin-bottom: 0.75rem;">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <h4 style="color: #374151; font-size: 1.05rem; margin: 0 0 0.4rem 0;">Tidak Ada Sarana Ditemukan</h4>
                <p style="color: #6b7280; font-size: 0.88rem; margin: 0 0 1rem 0;">Coba kata kunci pencarian lain atau pilih kategori yang berbeda.</p>
                <a href="{{ route('dashboard') }}" class="btn-pinjam-pill btn-pinjam-pill--primary">Lihat Beranda Katalog</a>
            </div>
            @endforelse
        </div>

        {{-- Pagination untuk Mode Filter / View All --}}
        @if($items->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 2rem;">
            {{ $items->links('vendor.pagination.simple-default') }}
        </div>
        @endif

    {{-- ================================================================= --}}
    {{-- MODE 2: TAMPILAN BERANDA (CAROUSEL HORIZONTAL PER KATEGORI)       --}}
    {{-- ================================================================= --}}
    @else
        {{-- Banner Notifikasi Filter Kategori Aktif --}}
        <div class="category-active-banner" id="categoryActiveBanner" style="display: none;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span class="category-active-dot"></span>
                <span>Hanya menampilkan kategori: <strong id="categoryActiveBannerName"></strong></span>
            </div>
            <button type="button" class="btn-reset-category-filter" onclick="filterCategory('all')">
                Tampilkan Semua Kategori &times;
            </button>
        </div>

        @php
            $hasCategoriesWithItems = false;
        @endphp

        {{-- SECTION KHUSUS: SERING DIPINJAM (TERPOPULER) --}}
        @if(isset($popularItems) && $popularItems->count() > 0)
            @php $hasCategoriesWithItems = true; @endphp
            <section class="category-section" id="category-popular" data-category-id="popular" data-category-name="Sering Dipinjam">
                <div class="category-section-header">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 1.25rem;">🔥</span>
                        <h3 class="category-title">Sering Dipinjam</h3>
                    </div>
                    <a href="{{ route('dashboard', ['kategori' => 'popular', 'view' => 'all']) }}" class="category-view-all" title="Buka seluruh alat sering dipinjam">
                        Lihat Semua
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                </div>

                <div class="carousel-container">
                    <button
                        type="button"
                        class="carousel-arrow carousel-arrow--prev"
                        onclick="scrollCarousel('track-popular', -360)"
                        aria-label="Geser ke kiri"
                        title="Geser ke kiri"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </button>

                    <div class="carousel-track" id="track-popular">
                        @foreach($popularItems as $item)
                        <div class="item-card-horizontal" id="item-popular-{{ $item->kode_barang }}">
                            <div class="card-thumb">
                                @if(!empty($item->foto) && file_exists(public_path($item->foto)))
                                    <img src="{{ asset($item->foto) }}" alt="{{ $item->nama_barang }}" loading="lazy">
                                @else
                                    <div class="card-thumb-placeholder">
                                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                            <circle cx="8.5" cy="8.5" r="1.5"/>
                                            <polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                        <span style="font-size: 0.68rem; margin-top: 0.25rem;">Foto Belum Ada</span>
                                    </div>
                                @endif
                            </div>

                            <div class="card-details">
                                <h4 class="card-title" title="{{ $item->nama_barang }}">{{ $item->nama_barang }}</h4>
                                <p class="card-category">Category: {{ $item->kategori->nama_kategori ?? 'Umum' }}</p>

                                @if($item->jumlah_baik > 0)
                                    <span class="badge-stock badge-stock--available">{{ $item->jumlah_baik }} tersedia</span>
                                    <a href="{{ route('loan.request', $item->kode_barang) }}" class="btn-pinjam-pill btn-pinjam-pill--primary" id="btn-pinjam-popular-{{ $item->kode_barang }}">
                                        Pinjam Alat
                                    </a>
                                @else
                                    <span class="badge-stock badge-stock--empty">0 tersedia</span>
                                    <button type="button" class="btn-pinjam-pill btn-pinjam-pill--disabled" disabled id="btn-pinjam-popular-{{ $item->kode_barang }}">
                                        Stok Habis
                                    </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button
                        type="button"
                        class="carousel-arrow carousel-arrow--next"
                        onclick="scrollCarousel('track-popular', 360)"
                        aria-label="Geser ke kanan"
                        title="Geser ke kanan"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>
                </div>
            </section>
        @endif

        @foreach($categoriesWithItems as $cat)
            @if($cat->barang && $cat->barang->count() > 0)
                @php $hasCategoriesWithItems = true; @endphp
                <section class="category-section" id="category-{{ $cat->id_kategori }}" data-category-id="{{ $cat->id_kategori }}" data-category-name="{{ $cat->nama_kategori }}">
                    {{-- Judul Kategori & Tombol "Lihat Semua >" --}}
                    <div class="category-section-header">
                        <h3 class="category-title">{{ $cat->nama_kategori }}</h3>
                        <a href="{{ route('dashboard', ['kategori' => $cat->id_kategori, 'view' => 'all']) }}" class="category-view-all" title="Buka seluruh inventaris {{ $cat->nama_kategori }}">
                            Lihat Semua
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </a>
                    </div>

                    {{-- Carousel Baris Barang Horizontal --}}
                    <div class="carousel-container">
                        {{-- Tombol Navigasi Kiri (<) --}}
                        <button
                            type="button"
                            class="carousel-arrow carousel-arrow--prev"
                            onclick="scrollCarousel('track-{{ $cat->id_kategori }}', -360)"
                            aria-label="Geser ke kiri"
                            title="Geser ke kiri"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                        </button>

                        {{-- Track Scroll Horizontal --}}
                        <div class="carousel-track" id="track-{{ $cat->id_kategori }}">
                            @foreach($cat->barang as $item)
                            <div class="item-card-horizontal" id="item-{{ $item->kode_barang }}">
                                {{-- Foto Barang --}}
                                <div class="card-thumb">
                                    @if(!empty($item->foto) && file_exists(public_path($item->foto)))
                                        <img src="{{ asset($item->foto) }}" alt="{{ $item->nama_barang }}" loading="lazy">
                                    @else
                                        <div class="card-thumb-placeholder">
                                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <polyline points="21 15 16 10 5 21"/>
                                            </svg>
                                            <span style="font-size: 0.68rem; margin-top: 0.25rem;">Foto Belum Ada</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Detail Card --}}
                                <div class="card-details">
                                    <h4 class="card-title" title="{{ $item->nama_barang }}">{{ $item->nama_barang }}</h4>
                                    <p class="card-category">Category: {{ $cat->nama_kategori }}</p>

                                    @if($item->jumlah_baik > 0)
                                        <span class="badge-stock badge-stock--available">{{ $item->jumlah_baik }} tersedia</span>
                                        <a href="{{ route('loan.request', $item->kode_barang) }}" class="btn-pinjam-pill btn-pinjam-pill--primary" id="btn-pinjam-{{ $item->kode_barang }}">
                                            Pinjam Alat
                                        </a>
                                    @else
                                        <span class="badge-stock badge-stock--empty">0 tersedia</span>
                                        <button type="button" class="btn-pinjam-pill btn-pinjam-pill--disabled" disabled id="btn-pinjam-{{ $item->kode_barang }}">
                                            Stok Habis
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Tombol Navigasi Kanan (>) --}}
                        <button
                            type="button"
                            class="carousel-arrow carousel-arrow--next"
                            onclick="scrollCarousel('track-{{ $cat->id_kategori }}', 360)"
                            aria-label="Geser ke kanan"
                            title="Geser ke kanan"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </button>
                    </div>
                </section>
            @endif
        @endforeach

        <div id="noFilteredCategoryNotice" style="display: none; text-align: center; padding: 4rem 1rem; background: #ffffff; border-radius: 12px; border: 1px dashed #d1d5db;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" style="margin-bottom: 0.5rem;">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            <p style="color: #6b7280; font-size: 0.95rem; margin: 0 0 1rem;">Belum ada inventaris pada kategori ini.</p>
            <button type="button" class="btn-reset-category-filter" onclick="filterCategory('all')">Tampilkan Semua Kategori</button>
        </div>

        @if(!$hasCategoriesWithItems)
            <div style="text-align: center; padding: 4rem 1rem; background: #ffffff; border-radius: 12px; border: 1px dashed #d1d5db;">
                <p style="color: #6b7280; font-size: 0.95rem; margin: 0;">Belum ada sarana prasarana yang didaftarkan.</p>
            </div>
        @endif
    @endif

    {{-- ================================================================= --}}
    {{-- SECTION: PANDUAN & SOP ALUR PEMINJAMAN SARANA                     --}}
    {{-- ================================================================= --}}
    <section class="sop-section">
        <div class="sop-header">
            <span class="sop-tag">Panduan Pengguna</span>
            <h3 class="sop-title">3 Langkah Mudah Peminjaman Sarana di SINFAS</h3>
            <p class="sop-subtitle">Ikuti alur resmi peminjaman fasilitas sekolah agar kegiatan belajar mengajar berjalan lancar.</p>
        </div>
        <div class="sop-grid">
            <div class="sop-card">
                <span class="sop-step-badge">01</span>
                <div class="sop-icon-wrap">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>
                </div>
                <h4 class="sop-card-title">Pilih & Ajukan</h4>
                <p class="sop-card-desc">Cari sarana yang dibutuhkan di katalog, tentukan tanggal serta keperluan penggunaan, lalu kirim formulir peminjaman.</p>
            </div>

            <div class="sop-card">
                <span class="sop-step-badge">02</span>
                <div class="sop-icon-wrap">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                </div>
                <h4 class="sop-card-title">Verifikasi Admin</h4>
                <p class="sop-card-desc">Admin Sarana akan meninjau ketersediaan fisik alat dan menyetujui permohonan pinjam Anda secara sistematis.</p>
            </div>

            <div class="sop-card">
                <span class="sop-step-badge">03</span>
                <div class="sop-icon-wrap">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                        <path d="m3.3 7 8.7 5 8.7-5"/>
                        <path d="M12 22V12"/>
                    </svg>
                </div>
                <h4 class="sop-card-title">Ambil & Kembalikan</h4>
                <p class="sop-card-desc">Ambil alat di Ruang Sarpras dengan menunjukkan status disetujui, dan kembalikan tepat waktu dalam kondisi baik.</p>
            </div>
        </div>

        {{-- Bantuan / Kontak Cepat Sarpras --}}
        <div class="sop-help-card">
            <div class="sop-help-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div class="sop-help-content">
                <h5 class="sop-help-title">Mengalami kendala pada alat atau butuh bantuan darurat?</h5>
                <p class="sop-help-desc">Kunjungi Ruang Sarana Prasarana (Sarpras) Gedung A Lt. 1 atau hubungi petugas piket fasilitas sekolah.</p>
            </div>
            <a href="{{ route('profile') }}" class="sop-help-btn">Bantuan & Profil</a>
        </div>
    </section>
</div>

<script>
    // Smooth scroll horizontal carousel
    function scrollCarousel(trackId, distance) {
        const track = document.getElementById(trackId);
        if (track) {
            track.scrollBy({
                left: distance,
                behavior: 'smooth'
            });
        }
    }

    // Scroll ke section kategori tertentu dari tombol Kategori Cepat
    function scrollToCategory(categoryId) {
        const section = document.getElementById(categoryId);
        if (section) {
            const navbarOffset = 80;
            const elementPosition = section.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - navbarOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
    }

    // Filter Kategori Interaktif & Bersinar
    function filterCategory(catId) {
        const isFilteredMode = {{ !empty($isFiltered) ? 'true' : 'false' }};

        // Jika sedang dalam mode pencarian atau filter query string lain, arahkan ke route URL
        if (isFilteredMode) {
            if (catId === 'all') {
                window.location.href = "{{ route('dashboard') }}";
            } else {
                window.location.href = "{{ route('dashboard') }}?kategori=" + catId + "&view=all";
            }
            return;
        }

        // Mode Beranda (Carousel): Filter langsung di client tanpa reload (Instant & Glowing!)
        const allChips = document.querySelectorAll('.quick-chip-btn');
        const sections = document.querySelectorAll('.category-section');
        const banner = document.getElementById('categoryActiveBanner');
        const bannerName = document.getElementById('categoryActiveBannerName');
        const emptyNotice = document.getElementById('noFilteredCategoryNotice');
        const currentActive = document.querySelector('.quick-chip-btn--active');

        // Toggle: Jika mengklik kembali chip kategori yang sudah aktif, kembalikan ke "Semua"
        if (catId !== 'all' && currentActive && currentActive.id === 'chip-cat-' + catId) {
            catId = 'all';
        }

        // Hapus kelas aktif bersinar dari semua chip
        allChips.forEach(chip => chip.classList.remove('quick-chip-btn--active'));

        if (catId === 'all') {
            // Aktifkan chip "Semua"
            const allBtn = document.getElementById('chip-cat-all');
            if (allBtn) allBtn.classList.add('quick-chip-btn--active');

            // Sembunyikan banner & notice kosong
            if (banner) banner.style.display = 'none';
            if (emptyNotice) emptyNotice.style.display = 'none';

            // Tampilkan kembali seluruh section kategori dengan mode SLIDE / CAROUSEL
            sections.forEach(sec => {
                sec.classList.remove('category-section--grid');
                sec.style.display = 'block';
                sec.classList.remove('category-section--fadein');
                void sec.offsetWidth; // Reflow trigger
                sec.classList.add('category-section--fadein');
            });

            // Update URL query string tanpa reload halaman
            if (window.history.pushState) {
                const url = new URL(window.location);
                url.searchParams.delete('kategori');
                window.history.pushState({}, '', url.pathname);
            }
        } else {
            // Aktifkan chip yang dipilih dengan warna biru minimalis
            const targetChip = document.getElementById('chip-cat-' + catId);
            if (targetChip) {
                targetChip.classList.add('quick-chip-btn--active');
                const catName = targetChip.getAttribute('data-cat-name') || targetChip.textContent.trim();
                if (banner && bannerName) {
                    bannerName.textContent = catName;
                    banner.style.display = 'flex';
                }
            }

            let foundCount = 0;
            let firstFound = null;

            // Tampilkan HANYA section kategori yang dipilih dengan tampilan GRID (bukan tombol slide)
            sections.forEach(sec => {
                const secCatId = sec.getAttribute('data-category-id');
                if (secCatId == catId) {
                    sec.classList.add('category-section--grid'); // Ubah ke tampilan Grid
                    sec.style.display = 'block';
                    sec.classList.remove('category-section--fadein');
                    void sec.offsetWidth;
                    sec.classList.add('category-section--fadein');
                    foundCount++;
                    if (!firstFound) firstFound = sec;
                } else {
                    sec.classList.remove('category-section--grid');
                    sec.style.display = 'none';
                }
            });

            if (emptyNotice) {
                emptyNotice.style.display = foundCount === 0 ? 'block' : 'none';
            }

            // Scroll halus ke section yang ditampilkan
            const scrollTarget = firstFound || banner;
            if (scrollTarget) {
                const navbarOffset = 95;
                const targetTop = scrollTarget.getBoundingClientRect().top + window.pageYOffset - navbarOffset;
                window.scrollTo({
                    top: Math.max(0, targetTop),
                    behavior: 'smooth'
                });
            }

            // Update URL query string tanpa reload
            if (window.history.pushState) {
                const url = new URL(window.location);
                url.searchParams.set('kategori', catId);
                window.history.pushState({}, '', url.toString());
            }
        }
    }

    // Filter dropdown toggle
    function toggleFilterDropdown() {
        document.getElementById('filter-dropdown').classList.toggle('filter-dropdown--active');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const wrapper = document.querySelector('.filter-dropdown-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            document.getElementById('filter-dropdown').classList.remove('filter-dropdown--active');
        }
    });

    // Auto-dismiss flash message
    const flash = document.getElementById('flash-success');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.3s ease';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 300);
        }, 4000);
    }

    // Inisialisasi otomatis jika ada parameter kategori di URL saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const catParam = urlParams.get('kategori');
        const isFilteredMode = {{ !empty($isFiltered) ? 'true' : 'false' }};
        if (catParam && !isFilteredMode) {
            filterCategory(catParam);
        }
    });
</script>
@endsection
