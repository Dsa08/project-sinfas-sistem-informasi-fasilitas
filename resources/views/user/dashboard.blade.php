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

    {{-- Hero Banner --}}
    <div class="dashboard-hero">
        <h2 class="dashboard-hero-text">Mau pinjam apa hari ini?</h2>
    </div>

    {{-- Search & Filter --}}
    <div class="search-section" id="search-section">
        <form action="{{ route('dashboard') }}" method="GET" class="search-bar" id="search-form">
            <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            <input
                type="text"
                class="search-input"
                id="search-input"
                name="search"
                placeholder="Search or filter items..."
                value="{{ request('search') }}"
                autocomplete="off"
            >
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
                {{ request('kategori') ? $categories->firstWhere('id_kategori', request('kategori'))->nama_kategori ?? 'Filter' : 'Filter' }}
            </button>
            <div class="filter-dropdown" id="filter-dropdown">
                <a href="{{ route('dashboard', request()->except('kategori')) }}" class="filter-dropdown-item {{ !request('kategori') ? 'filter-dropdown-item--active' : '' }}">
                    Semua Kategori
                </a>
                @foreach($categories as $cat)
                <a href="{{ route('dashboard', array_merge(request()->except('kategori'), ['kategori' => $cat->id_kategori])) }}" class="filter-dropdown-item {{ request('kategori') == $cat->id_kategori ? 'filter-dropdown-item--active' : '' }}">
                    {{ $cat->nama_kategori }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Active Filters Info --}}
    @if(request('search') || request('kategori'))
    <div class="active-filters" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap;">
        <span style="font-size: 0.82rem; color: #6b7280;">Showing results for:</span>
        @if(request('search'))
        <span class="filter-tag">
            "{{ request('search') }}"
            <a href="{{ route('dashboard', request()->except('search')) }}" style="color: #6b7280; text-decoration: none; margin-left: 0.25rem;">&times;</a>
        </span>
        @endif
        @if(request('kategori'))
        <span class="filter-tag">
            {{ $categories->firstWhere('id_kategori', request('kategori'))->nama_kategori ?? '' }}
            <a href="{{ route('dashboard', request()->except('kategori')) }}" style="color: #6b7280; text-decoration: none; margin-left: 0.25rem;">&times;</a>
        </span>
        @endif
        <a href="{{ route('dashboard') }}" style="font-size: 0.82rem; color: #1D67F2; text-decoration: none; font-weight: 500;">Clear all</a>
    </div>
    @endif

    {{-- Items Grid --}}
    <div class="items-grid" id="items-grid">
        @forelse($items as $item)
        <div class="item-card" id="item-{{ $item->kode_barang }}">
            <div class="item-image">
                {{-- Placeholder abu-abu dengan ikon gambar tidak tersedia --}}
                <div class="item-image-placeholder">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#b0b0b0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <span style="font-size: 0.7rem; color: #9ca3af; margin-top: 0.25rem;">No Image</span>
                </div>
            </div>
            <div class="item-info">
                <h3 class="item-name">{{ $item->nama_barang }}</h3>
                <p class="item-category">Category: {{ $item->kategori->nama_kategori ?? '-' }}</p>
                <span class="item-status {{ $item->status === 'Available' ? 'item-status--available' : 'item-status--unavailable' }}">
                    @if($item->status === 'Available')
                        {{ $item->jumlah_baik }} tersedia
                    @else
                        0 tersedia
                    @endif
                </span>
                @if($item->status === 'Available')
                    <a href="{{ route('loan.request', $item->kode_barang) }}" class="btn-request" id="request-btn-{{ $item->kode_barang }}">Request Loan</a>
                @else
                    <button class="btn-request" disabled style="background-color: #9ca3af; cursor: not-allowed;">Unavailable</button>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 3rem 1rem;">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin-bottom: 0.75rem;">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            <p style="color: #6b7280; font-size: 0.95rem; margin: 0;">Tidak ada barang ditemukan.</p>
            @if(request('search') || request('kategori'))
            <a href="{{ route('dashboard') }}" style="color: #1D67F2; font-size: 0.88rem; text-decoration: none; font-weight: 500; margin-top: 0.5rem; display: inline-block;">Reset pencarian</a>
            @endif
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
        {{ $items->links('vendor.pagination.simple-default') }}
    </div>
    @endif
</div>

<script>
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
</script>
@endsection
