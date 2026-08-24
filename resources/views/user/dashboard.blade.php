@extends('layouts.app')

@section('title', 'Dashboard - SINFAS')

@section('content')
<div class="dashboard-container">
    {{-- Search & Filter --}}
    <div class="search-section" id="search-section">
        <div class="search-bar">
            <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            <input
                type="text"
                class="search-input"
                id="search-input"
                placeholder="Search or filter items..."
                autocomplete="off"
            >
        </div>
        <button class="filter-btn" id="filter-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" y1="6" x2="20" y2="6"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
                <line x1="11" y1="18" x2="13" y2="18"/>
            </svg>
            Filter
        </button>
    </div>

    {{-- Items Grid --}}
    <div class="items-grid" id="items-grid">

        {{-- Item Card 1: Projector Epson X300 --}}
        <div class="item-card" id="item-1">
            <div class="item-image">
                {{-- Placeholder gambar item - ganti dengan gambar asli nanti --}}
                {{-- Contoh: <img src="{{ asset('assets/items/projector.jpg') }}" alt="Projector Epson X300"> --}}
                <div class="item-image-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="10" rx="2"/>
                        <circle cx="12" cy="12" r="3"/>
                        <line x1="22" y1="12" x2="24" y2="12"/>
                    </svg>
                </div>
            </div>
            <div class="item-info">
                <h3 class="item-name">Projector Epson X300</h3>
                <p class="item-category">Category: Equipment</p>
                <span class="item-status item-status--available">Available</span>
                <button class="btn-request" id="request-btn-1">Request Loan</button>
            </div>
        </div>

        {{-- Item Card 2: Portable Speaker JBL --}}
        <div class="item-card" id="item-2">
            <div class="item-image">
                <div class="item-image-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="2" width="16" height="20" rx="4"/>
                        <circle cx="12" cy="14" r="4"/>
                        <circle cx="12" cy="6" r="1"/>
                    </svg>
                </div>
            </div>
            <div class="item-info">
                <h3 class="item-name">Portable Speaker JBL</h3>
                <p class="item-category">Category: Speaker</p>
                <span class="item-status item-status--available">Available</span>
                <button class="btn-request" id="request-btn-2">Request Loan</button>
            </div>
        </div>

        {{-- Item Card 3: Folding Table 180cm --}}
        <div class="item-card" id="item-3">
            <div class="item-image">
                <div class="item-image-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="8" width="20" height="3" rx="1"/>
                        <line x1="5" y1="11" x2="4" y2="20"/>
                        <line x1="19" y1="11" x2="20" y2="20"/>
                    </svg>
                </div>
            </div>
            <div class="item-info">
                <h3 class="item-name">Folding Table 180cm</h3>
                <p class="item-category">Category: Equipment</p>
                <span class="item-status item-status--unavailable">Unavailable</span>
                <button class="btn-request" id="request-btn-3">Request Loan</button>
            </div>
        </div>

        {{-- Item Card 4: Whiteboard 120cm --}}
        <div class="item-card" id="item-4">
            <div class="item-image">
                <div class="item-image-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="14" rx="2"/>
                        <line x1="9" y1="17" x2="9" y2="21"/>
                        <line x1="15" y1="17" x2="15" y2="21"/>
                        <line x1="6" y1="21" x2="18" y2="21"/>
                    </svg>
                </div>
            </div>
            <div class="item-info">
                <h3 class="item-name">Whiteboard 120cm</h3>
                <p class="item-category">Category: Equipment</p>
                <span class="item-status item-status--available">Available</span>
                <button class="btn-request" id="request-btn-4">Request Loan</button>
            </div>
        </div>

    </div>
</div>
@endsection
