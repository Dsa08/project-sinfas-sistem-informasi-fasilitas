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
    <section class="push-prompt" id="push-prompt" aria-labelledby="push-prompt-title" hidden>
        <div class="push-prompt-copy">
            <span class="push-prompt-icon" aria-hidden="true">&#128276;</span>
            <div>
                <h2 id="push-prompt-title">Dapatkan notifikasi SINFAS di HP</h2>
                <p id="push-prompt-message">Aktifkan notifikasi untuk mengetahui perubahan status peminjaman dan pengembalian.</p>
                <p class="push-prompt-status" id="push-prompt-status" role="status" aria-live="polite"></p>
            </div>
        </div>
        <div class="push-prompt-actions">
            <button type="button" class="push-button push-button--primary" id="push-enable">Aktifkan notifikasi</button>
            <button type="button" class="push-button push-button--secondary" id="push-test" hidden>Kirim notifikasi uji coba</button>
            <button type="button" class="push-button push-button--secondary" id="push-disable" hidden>Nonaktifkan</button>
            <button type="button" class="push-button push-button--quiet" id="push-dismiss" aria-label="Tutup penawaran">Nanti</button>
        </div>
    </section>

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

    {{-- Widget Aktivitas Pinjaman Siswa (Hanya tampil jika ada pinjaman aktif atau pengajuan menunggu) --}}
    @if((isset($activeLoans) && $activeLoans->isNotEmpty()) || (isset($pendingLoans) && $pendingLoans->isNotEmpty()))
    <div class="active-loans-widget" id="active-loans-widget">
        {{-- 1. Pinjaman Sedang Berjalan (Disetujui) --}}
        @foreach($activeLoans as $activeLoan)
            @php
                $isPendingConfirmation = $activeLoan->pengembalian !== null;
                $tglPinjam = $activeLoan->tanggal_pinjam 
                    ? \Carbon\Carbon::parse($activeLoan->tanggal_pinjam) 
                    : ($activeLoan->created_at ? \Carbon\Carbon::parse($activeLoan->created_at) : now());
                
                $daysBorrowed = (int) $tglPinjam->startOfDay()->diffInDays(now()->startOfDay());
                $isOverdue = $daysBorrowed > 1;
                $isToday = $tglPinjam->isToday();
            @endphp
            <div class="active-loan-card {{ $isOverdue && !$isPendingConfirmation ? 'active-loan-card--overdue' : '' }}">
                <div class="active-loan-icon-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                        <path d="m3.3 7 8.7 5 8.7-5"/>
                        <path d="M12 22V12"/>
                    </svg>
                </div>
                <div class="active-loan-info">
                    <div class="active-loan-badge-row">
                        @if($isPendingConfirmation)
                            <span class="active-loan-pill active-loan-pill--warning">Menunggu Konfirmasi Pengembalian</span>
                        @elseif($isToday)
                            <span class="active-loan-pill active-loan-pill--approved">Sedang Dipinjam</span>
                            <span class="active-loan-pill active-loan-pill--warning">Batas Kembali Hari Ini</span>
                        @elseif($isOverdue)
                            <span class="active-loan-pill active-loan-pill--danger">Dipinjam {{ $daysBorrowed }} Hari Lalu</span>
                        @else
                            <span class="active-loan-pill active-loan-pill--approved">Sedang Dipinjam</span>
                        @endif
                    </div>
                    <h4 class="active-loan-title">{{ $activeLoan->barang->nama_barang ?? 'Barang Sarana' }}</h4>
                    <p class="active-loan-desc">
                        @if($isPendingConfirmation)
                            Pengembalian diajukan pada <strong>{{ $activeLoan->pengembalian->tanggal_kembali ? \Carbon\Carbon::parse($activeLoan->pengembalian->tanggal_kembali)->format('d M Y') : now()->format('d M Y') }}</strong> &bull; Kode: <code>{{ $activeLoan->kode_pinjam }}</code>
                        @else
                            Dipinjam sejak <strong>{{ $tglPinjam->format('d M Y') }}</strong> &bull; Kode Pinjam: <code>{{ $activeLoan->kode_pinjam }}</code>
                        @endif
                    </p>
                </div>
                <div class="active-loan-action">
                    @if($isPendingConfirmation)
                        <a href="{{ route('loan.status') }}" class="btn-active-loan-cta btn-active-loan-cta--secondary">
                            Lihat Status
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/>
                                <path d="m12 5 7 7-7 7"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('loan.return', $activeLoan->kode_pinjam) }}" class="btn-active-loan-cta">
                            Kembalikan Alat
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/>
                                <path d="m12 5 7 7-7 7"/>
                            </svg>
                        </a>
                    @endif
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

    {{-- ================================================================= --}}
    {{-- MODE 1: TAMPILAN FILTERED (Pencarian atau "Lihat Semua" Kategori) --}}
    {{-- ================================================================= --}}
    @if(!empty($isFiltered))
        <section class="category-section filtered-results-section">
            <div class="filtered-header">
                <div style="display: flex; align-items: center; gap: 0.85rem; flex-wrap: wrap;">
                    <a href="{{ route('dashboard') }}" class="btn-back-katalog">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        Kembali ke Beranda Katalog
                    </a>
                    <span style="color: #cbd5e1;">|</span>
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

            <div class="shopee-grid" id="filtered-items-grid">
                @forelse($items as $item)
                <div class="shopee-card" id="item-{{ $item->kode_barang }}">
                    {{-- Foto Barang --}}
                    <div class="shopee-card-img">
                        <span class="shopee-img-badge {{ $item->jumlah_baik > 0 ? 'shopee-img-badge--available' : 'shopee-img-badge--empty' }}">
                            <span class="shopee-img-badge-dot"></span>
                            {{ $item->jumlah_baik > 0 ? $item->jumlah_baik . ' unit' : 'Habis' }}
                        </span>
                        @if($item->foto_url)
                            <img src="{{ $item->foto_url }}" alt="{{ $item->nama_barang }}" loading="lazy">
                        @else
                            <div class="shopee-card-img-placeholder">
                                <div class="placeholder-icon-wrap">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                                <span class="placeholder-text">Foto Belum Ada</span>
                            </div>
                        @endif
                    </div>

                    {{-- Detail --}}
                    <div class="shopee-card-body">
                        <span class="shopee-card-cat">{{ $item->kategori->nama_kategori ?? 'Sarana' }}</span>
                        <h4 class="shopee-card-name" title="{{ $item->nama_barang }}">{{ $item->nama_barang }}</h4>

                        <div class="shopee-card-footer">
                            <div class="shopee-stock-badge-wrap">
                                @if($item->jumlah_baik > 0)
                                    <span class="shopee-badge-stock shopee-badge--ok">
                                        <span class="badge-dot"></span> {{ $item->jumlah_baik }} tersedia
                                    </span>
                                @else
                                    <span class="shopee-badge-stock shopee-badge--habis">
                                        <span class="badge-dot"></span> Stok Habis
                                    </span>
                                @endif
                            </div>

                            @if($item->jumlah_baik > 0)
                                <a href="{{ route('loan.request', $item->kode_barang) }}" class="shopee-btn-pinjam" id="btn-pinjam-{{ $item->kode_barang }}">
                                    <span>Pinjam</span>
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </a>
                            @else
                                <button type="button" class="shopee-btn-pinjam shopee-btn-pinjam--disabled" disabled id="btn-pinjam-{{ $item->kode_barang }}">
                                    <span>Habis</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 3.5rem 1rem; background: #ffffff; border-radius: 12px; border: 1px dashed #d1d5db;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" style="margin-bottom: 0.75rem;">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>
                    <h4 style="color: #374151; font-size: 1rem; margin: 0 0 0.4rem 0;">Tidak Ada Sarana Ditemukan</h4>
                    <p style="color: #6b7280; font-size: 0.85rem; margin: 0 0 1rem 0;">Coba kata kunci lain atau pilih kategori berbeda.</p>
                    <a href="{{ route('dashboard') }}" class="shopee-btn-pinjam">Lihat Semua</a>
                </div>
                @endforelse
            </div>

            {{-- Pagination untuk Mode Filter / View All --}}
            @if($items->hasPages())
            <div style="display: flex; justify-content: center; margin-top: 2rem;">
                {{ $items->links('vendor.pagination.simple-default') }}
            </div>
            @endif
        </section>

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
                    <div class="category-title-wrap">
                        <span style="font-size: 1.15rem; flex-shrink: 0;">🔥</span>
                        <h3 class="category-title" title="Sering Dipinjam">Sering Dipinjam</h3>
                    </div>
                    <a href="{{ route('dashboard', ['kategori' => 'popular', 'view' => 'all']) }}" class="category-view-all" title="Buka seluruh alat sering dipinjam">
                        <span>Lihat Semua</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                </div>

                <div class="shopee-grid" id="track-popular">
                    @foreach($popularItems as $item)
                    <div class="shopee-card" id="item-popular-{{ $item->kode_barang }}">
                        <div class="shopee-card-img">
                            <span class="shopee-img-badge {{ $item->jumlah_baik > 0 ? 'shopee-img-badge--available' : 'shopee-img-badge--empty' }}">
                                <span class="shopee-img-badge-dot"></span>
                                {{ $item->jumlah_baik > 0 ? $item->jumlah_baik . ' unit' : 'Habis' }}
                            </span>
                            @if($item->foto_url)
                                <img src="{{ $item->foto_url }}" alt="{{ $item->nama_barang }}" loading="lazy">
                            @else
                                <div class="shopee-card-img-placeholder">
                                    <div class="placeholder-icon-wrap">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                            <circle cx="8.5" cy="8.5" r="1.5"/>
                                            <polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    </div>
                                    <span class="placeholder-text">Foto Belum Ada</span>
                                </div>
                            @endif
                        </div>
                        <div class="shopee-card-body">
                            <span class="shopee-card-cat">{{ $item->kategori->nama_kategori ?? 'Umum' }}</span>
                            <h4 class="shopee-card-name" title="{{ $item->nama_barang }}">{{ $item->nama_barang }}</h4>

                            <div class="shopee-card-footer">
                                <div class="shopee-stock-badge-wrap">
                                    @if($item->jumlah_baik > 0)
                                        <span class="shopee-badge-stock shopee-badge--ok">
                                            <span class="badge-dot"></span> {{ $item->jumlah_baik }} tersedia
                                        </span>
                                    @else
                                        <span class="shopee-badge-stock shopee-badge--habis">
                                            <span class="badge-dot"></span> Stok Habis
                                        </span>
                                    @endif
                                </div>

                                @if($item->jumlah_baik > 0)
                                    <a href="{{ route('loan.request', $item->kode_barang) }}" class="shopee-btn-pinjam" id="btn-pinjam-popular-{{ $item->kode_barang }}">
                                        <span>Pinjam</span>
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </a>
                                @else
                                    <button type="button" class="shopee-btn-pinjam shopee-btn-pinjam--disabled" disabled id="btn-pinjam-popular-{{ $item->kode_barang }}">
                                        <span>Habis</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
        @endif

        @foreach($categoriesWithItems as $cat)
            @if($cat->barang && $cat->barang->count() > 0)
                @php $hasCategoriesWithItems = true; @endphp
                <section class="category-section" id="category-{{ $cat->id_kategori }}" data-category-id="{{ $cat->id_kategori }}" data-category-name="{{ $cat->nama_kategori }}">
                    {{-- Judul Kategori & Tombol "Lihat Semua >" --}}
                    <div class="category-section-header">
                        <div class="category-title-wrap">
                            <h3 class="category-title" title="{{ $cat->nama_kategori }}">{{ $cat->nama_kategori }}</h3>
                        </div>
                        <a href="{{ route('dashboard', ['kategori' => $cat->id_kategori, 'view' => 'all']) }}" class="category-view-all" title="Buka seluruh inventaris {{ $cat->nama_kategori }}">
                            <span>Lihat Semua</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </a>
                    </div>

                    {{-- Grid Kotak Barang (Shopee-style) --}}
                    <div class="shopee-grid" id="track-{{ $cat->id_kategori }}">
                        @foreach($cat->barang as $item)
                        <div class="shopee-card" id="item-{{ $item->kode_barang }}">
                            <div class="shopee-card-img">
                                <span class="shopee-img-badge {{ $item->jumlah_baik > 0 ? 'shopee-img-badge--available' : 'shopee-img-badge--empty' }}">
                                    <span class="shopee-img-badge-dot"></span>
                                    {{ $item->jumlah_baik > 0 ? $item->jumlah_baik . ' unit' : 'Habis' }}
                                </span>
                                @if($item->foto_url)
                                    <img src="{{ $item->foto_url }}" alt="{{ $item->nama_barang }}" loading="lazy">
                                @else
                                    <div class="shopee-card-img-placeholder">
                                        <div class="placeholder-icon-wrap">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <polyline points="21 15 16 10 5 21"/>
                                            </svg>
                                        </div>
                                        <span class="placeholder-text">Foto Belum Ada</span>
                                    </div>
                                @endif
                            </div>
                            <div class="shopee-card-body">
                                <span class="shopee-card-cat">{{ $cat->nama_kategori }}</span>
                                <h4 class="shopee-card-name" title="{{ $item->nama_barang }}">{{ $item->nama_barang }}</h4>

                                <div class="shopee-card-footer">
                                    <div class="shopee-stock-badge-wrap">
                                        @if($item->jumlah_baik > 0)
                                            <span class="shopee-badge-stock shopee-badge--ok">
                                                <span class="badge-dot"></span> {{ $item->jumlah_baik }} tersedia
                                            </span>
                                        @else
                                            <span class="shopee-badge-stock shopee-badge--habis">
                                                <span class="badge-dot"></span> Stok Habis
                                            </span>
                                        @endif
                                    </div>

                                    @if($item->jumlah_baik > 0)
                                        <a href="{{ route('loan.request', $item->kode_barang) }}" class="shopee-btn-pinjam" id="btn-pinjam-{{ $item->kode_barang }}">
                                            <span>Pinjam</span>
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            </svg>
                                        </a>
                                    @else
                                        <button type="button" class="shopee-btn-pinjam shopee-btn-pinjam--disabled" disabled id="btn-pinjam-{{ $item->kode_barang }}">
                                            <span>Habis</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
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


</div>

<script>
    // Filter Kategori Interaktif (Semua tampilan sudah grid, hanya perlu show/hide section)
    function filterCategory(catId) {
        const isFilteredMode = {{ !empty($isFiltered) ? 'true' : 'false' }};

        // Jika sedang dalam mode pencarian / view=all, arahkan ke route URL
        if (isFilteredMode) {
            if (catId === 'all') {
                window.location.href = "{{ route('dashboard') }}";
            } else {
                window.location.href = "{{ route('dashboard') }}?kategori=" + catId + "&view=all";
            }
            return;
        }

        // Mode Beranda: Filter langsung di client tanpa reload
        const sections = document.querySelectorAll('.category-section');
        const banner = document.getElementById('categoryActiveBanner');
        const bannerName = document.getElementById('categoryActiveBannerName');
        const emptyNotice = document.getElementById('noFilteredCategoryNotice');

        if (catId === 'all') {
            if (banner) banner.style.display = 'none';
            if (emptyNotice) emptyNotice.style.display = 'none';

            // Tampilkan semua section
            sections.forEach(sec => {
                sec.style.display = 'block';
                sec.classList.remove('category-section--fadein');
                void sec.offsetWidth;
                sec.classList.add('category-section--fadein');
            });

            if (window.history.pushState) {
                const url = new URL(window.location);
                url.searchParams.delete('kategori');
                window.history.pushState({}, '', url.pathname);
            }
        } else {
            let catName = 'Kategori';
            const targetSec = document.querySelector(`.category-section[data-category-id="${catId}"]`);
            if (targetSec) {
                catName = targetSec.getAttribute('data-category-name') || 'Kategori';
            }
            if (banner && bannerName) {
                bannerName.textContent = catName;
                banner.style.display = 'flex';
            }

            let foundCount = 0;
            let firstFound = null;

            // Hanya tampilkan section kategori yang dipilih
            sections.forEach(sec => {
                const secCatId = sec.getAttribute('data-category-id');
                if (secCatId == catId) {
                    sec.style.display = 'block';
                    sec.classList.remove('category-section--fadein');
                    void sec.offsetWidth;
                    sec.classList.add('category-section--fadein');
                    foundCount++;
                    if (!firstFound) firstFound = sec;
                } else {
                    sec.style.display = 'none';
                }
            });

            if (emptyNotice) {
                emptyNotice.style.display = foundCount === 0 ? 'block' : 'none';
            }

            const scrollTarget = firstFound || banner;
            if (scrollTarget) {
                const navbarOffset = document.getElementById('main-navbar')?.offsetHeight ?? 95;
                const targetTop = scrollTarget.getBoundingClientRect().top + window.pageYOffset - navbarOffset;
                window.scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
            }

            if (window.history.pushState) {
                const url = new URL(window.location);
                url.searchParams.set('kategori', catId);
                window.history.pushState({}, '', url.toString());
            }
        }
    }

    // Filter dropdown toggle
    function toggleFilterDropdown() {
        const dropdown = document.getElementById('filter-dropdown');
        const backdrop = document.getElementById('filter-backdrop');
        if (dropdown) dropdown.classList.toggle('filter-dropdown--active');
        if (backdrop) backdrop.classList.toggle('filter-backdrop--active');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const wrapper = document.querySelector('.filter-dropdown-wrapper');
        const backdrop = document.getElementById('filter-backdrop');
        const dropdown = document.getElementById('filter-dropdown');
        if (wrapper && !wrapper.contains(e.target) && (!backdrop || !backdrop.contains(e.target))) {
            if (dropdown) dropdown.classList.remove('filter-dropdown--active');
            if (backdrop) backdrop.classList.remove('filter-backdrop--active');
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

<script>
(() => {
    const card = document.getElementById('push-prompt');
    if (!card) return;

    const publicKey = @json(config('services.webpush.public_key'));
    const enableButton = document.getElementById('push-enable');
    const testButton = document.getElementById('push-test');
    const disableButton = document.getElementById('push-disable');
    const dismissButton = document.getElementById('push-dismiss');
    const status = document.getElementById('push-prompt-status');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const dismissedKey = 'sinfas-push-prompt-dismissed';

    function setStatus(message) { status.textContent = message; }
    function decodeVapidKey(value) {
        const padding = '='.repeat((4 - value.length % 4) % 4);
        const base64 = (value + padding).replace(/-/g, '+').replace(/_/g, '/');
        return Uint8Array.from(atob(base64), (character) => character.charCodeAt(0));
    }
    async function getRegistration() {
        return navigator.serviceWorker.register(@json(asset('sw.js')));
    }
    async function saveSubscription(subscription) {
        const response = await fetch(@json(route('push.subscriptions.store')), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(subscription.toJSON()),
        });
        if (!response.ok) throw new Error('Server menolak pendaftaran notifikasi.');
    }
    async function showSubscribed(subscription) {
        await saveSubscription(subscription);
        setStatus('Notifikasi aktif di perangkat ini.');
        enableButton.hidden = true;
        testButton.hidden = false;
        disableButton.hidden = false;
    }

    async function initialize() {
        const supported = window.isSecureContext && 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
        if (!supported) {
            card.hidden = false;
            enableButton.hidden = true;
            dismissButton.hidden = true;
            setStatus('Push memerlukan HTTPS (atau localhost) dan browser yang mendukung notifikasi.');
            return;
        }
        if (Notification.permission === 'denied') {
            card.hidden = false;
            enableButton.hidden = true;
            setStatus('Izin notifikasi diblokir. Ubah izin SINFAS melalui pengaturan situs di browser.');
            return;
        }

        const registration = await getRegistration();
        const existing = await registration.pushManager.getSubscription();
        if (existing && Notification.permission === 'granted') {
            card.hidden = false;
            await showSubscribed(existing);
            return;
        }
        if (!sessionStorage.getItem(dismissedKey)) card.hidden = false;
        if (!publicKey) {
            enableButton.disabled = true;
            setStatus('Server belum dikonfigurasi. Admin perlu mengisi kunci VAPID terlebih dahulu.');
        }
    }

    enableButton.addEventListener('click', async () => {
        enableButton.disabled = true;
        try {
            const permission = await Notification.requestPermission();
            if (permission !== 'granted') {
                setStatus('Izin notifikasi belum diberikan.');
                return;
            }
            if (!publicKey) throw new Error('Kunci VAPID server belum dikonfigurasi.');
            const registration = await getRegistration();
            let subscription = await registration.pushManager.getSubscription();
            if (!subscription) {
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: decodeVapidKey(publicKey),
                });
            }
            await showSubscribed(subscription);
            sessionStorage.removeItem(dismissedKey);
        } catch (error) {
            setStatus(error.message || 'Notifikasi gagal diaktifkan. Coba lagi.');
        } finally {
            enableButton.disabled = false;
        }
    });

    testButton.addEventListener('click', async () => {
        testButton.disabled = true;
        try {
            const response = await fetch(@json(route('push.notifications.test')), {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Notifikasi uji coba gagal dikirim.');
            setStatus(data.message);
        } catch (error) {
            setStatus(error.message || 'Notifikasi uji coba gagal dikirim.');
        } finally {
            testButton.disabled = false;
        }
    });

    disableButton.addEventListener('click', async () => {
        disableButton.disabled = true;
        try {
            const registration = await navigator.serviceWorker.getRegistration(@json(asset('sw.js')));
            const subscription = await registration?.pushManager.getSubscription();
            if (subscription) {
                await fetch(@json(route('push.subscriptions.destroy')), {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ endpoint: subscription.endpoint }),
                });
                await subscription.unsubscribe();
            }
            setStatus('Notifikasi push dinonaktifkan di perangkat ini.');
            testButton.hidden = true;
            disableButton.hidden = true;
            enableButton.hidden = false;
        } catch (_) {
            setStatus('Notifikasi gagal dinonaktifkan. Coba lagi.');
        } finally {
            disableButton.disabled = false;
        }
    });

    dismissButton.addEventListener('click', () => {
        sessionStorage.setItem(dismissedKey, '1');
        card.hidden = true;
    });

    initialize().catch(() => {
        card.hidden = false;
        setStatus('Status push tidak dapat diperiksa. Coba muat ulang halaman.');
    });
})();
</script>
@endsection
