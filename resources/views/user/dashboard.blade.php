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
                <img src="{{ asset('assets/pictures/projector_sample.jpg') }}" alt="Projector Epson X300">
            </div>
            <div class="item-info">
                <h3 class="item-name">Projector Epson X300</h3>
                <p class="item-category">Category: Electronics / Projector</p>
                <span class="item-status item-status--available">Available</span>
                <a href="{{ route('loan.request', 1) }}" class="btn-request" id="request-btn-1" style="text-decoration: none; text-align: center;">Request Loan</a>
            </div>
        </div>

        {{-- Item Card 2: Portable Speaker JBL --}}
        <div class="item-card" id="item-2">
            <div class="item-image">
                <img src="{{ asset('assets/pictures/projector_sample.jpg') }}" alt="Portable Speaker JBL">
            </div>
            <div class="item-info">
                <h3 class="item-name">Portable Speaker JBL</h3>
                <p class="item-category">Category: Audio / Speaker</p>
                <span class="item-status item-status--available">Available</span>
                <a href="{{ route('loan.request', 2) }}" class="btn-request" id="request-btn-2" style="text-decoration: none; text-align: center;">Request Loan</a>
            </div>
        </div>

        {{-- Item Card 3: Folding Table 180cm --}}
        <div class="item-card" id="item-3">
            <div class="item-image">
                <img src="{{ asset('assets/pictures/projector_sample.jpg') }}" alt="Folding Table 180cm">
            </div>
            <div class="item-info">
                <h3 class="item-name">Folding Table 180cm</h3>
                <p class="item-category">Category: Furniture / Table</p>
                <span class="item-status item-status--unavailable">Unavailable</span>
                <button class="btn-request" id="request-btn-3" disabled style="opacity: 0.5; cursor: not-allowed;">Request Loan</button>
            </div>
        </div>

        {{-- Item Card 4: Whiteboard 120cm --}}
        <div class="item-card" id="item-4">
            <div class="item-image">
                <img src="{{ asset('assets/pictures/projector_sample.jpg') }}" alt="Whiteboard 120cm">
            </div>
            <div class="item-info">
                <h3 class="item-name">Whiteboard 120cm</h3>
                <p class="item-category">Category: Equipment / Board</p>
                <span class="item-status item-status--available">Available</span>
                <a href="{{ route('loan.request', 4) }}" class="btn-request" id="request-btn-4" style="text-decoration: none; text-align: center;">Request Loan</a>
            </div>
        </div>

    </div>
</div>
@endsection
