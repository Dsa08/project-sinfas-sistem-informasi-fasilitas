    {{-- Search & Filter Bar --}}
    <div class="search-section navbar-search-section" id="search-section">
        <div class="search-control-shell">
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
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @if(request('ketersediaan'))
                <input type="hidden" name="ketersediaan" value="{{ request('ketersediaan') }}">
            @endif
        </form>
        <div class="filter-dropdown-wrapper">
            <button aria-label="Filter dan urutkan" title="Filter dan urutkan" class="filter-btn {{ request('sort') || request('ketersediaan') || request('kategori') ? 'filter-btn--has-filter' : '' }}" id="filter-btn" type="button" onclick="toggleFilterDropdown()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="6" x2="20" y2="6"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                    <line x1="11" y1="18" x2="13" y2="18"/>
                </svg>
                <span>Filter</span>
                @if(request('sort') || request('ketersediaan') || request('kategori'))
                    <span class="filter-active-dot"></span>
                @endif
            </button>

            {{-- Backdrop Modal untuk Layar HP --}}
            <div class="filter-backdrop" id="filter-backdrop" onclick="toggleFilterDropdown()"></div>

            <div class="filter-dropdown" id="filter-dropdown">
                <form action="{{ route('dashboard') }}" method="GET" id="filter-form">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <div class="filter-modal-header">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1D67F2" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" y1="6" x2="20" y2="6"/>
                                <line x1="8" y1="12" x2="16" y2="12"/>
                                <line x1="11" y1="18" x2="13" y2="18"/>
                            </svg>
                            <span class="filter-modal-title">Filter & Urutkan</span>
                        </div>
                        <button type="button" class="filter-modal-close" onclick="toggleFilterDropdown()" title="Tutup">&times;</button>
                    </div>

                    <div class="filter-modal-body">
                        {{-- 1. URUTKAN NAMA ABJAD (PALING ATAS) --}}
                        <div class="filter-group">
                            <label class="filter-group-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"/><path d="M7 20V4"/><path d="M20 8h-4"/><path d="M16 12h4"/><path d="M16 16h4"/></svg>
                                Urutkan Nama (Abjad)
                            </label>
                            <div class="filter-options-grid">
                                <label class="filter-option-btn {{ request('sort', 'nama_asc') === 'nama_asc' ? 'filter-option-btn--active' : '' }}">
                                    <input type="radio" name="sort" value="nama_asc" {{ request('sort', 'nama_asc') === 'nama_asc' ? 'checked' : '' }}>
                                    <span>A &rarr; Z (Nama A ke Z)</span>
                                </label>
                                <label class="filter-option-btn {{ request('sort') === 'nama_desc' ? 'filter-option-btn--active' : '' }}">
                                    <input type="radio" name="sort" value="nama_desc" {{ request('sort') === 'nama_desc' ? 'checked' : '' }}>
                                    <span>Z &rarr; A (Nama Z ke A)</span>
                                </label>
                            </div>
                        </div>

                        {{-- 2. KETERSEDIAAN STOK --}}
                        <div class="filter-group">
                            <label class="filter-group-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                Status Ketersediaan
                            </label>
                            <div class="filter-options-grid">
                                <label class="filter-option-btn {{ empty(request('ketersediaan')) ? 'filter-option-btn--active' : '' }}">
                                    <input type="radio" name="ketersediaan" value="" {{ empty(request('ketersediaan')) ? 'checked' : '' }}>
                                    <span>Semua Status</span>
                                </label>
                                <label class="filter-option-btn {{ request('ketersediaan') === 'tersedia' ? 'filter-option-btn--active' : '' }}">
                                    <input type="radio" name="ketersediaan" value="tersedia" {{ request('ketersediaan') === 'tersedia' ? 'checked' : '' }}>
                                    <span>🟢 Hanya Yang Tersedia</span>
                                </label>
                                <label class="filter-option-btn {{ request('ketersediaan') === 'tidak_tersedia' ? 'filter-option-btn--active' : '' }}">
                                    <input type="radio" name="ketersediaan" value="tidak_tersedia" {{ request('ketersediaan') === 'tidak_tersedia' ? 'checked' : '' }}>
                                    <span>🔴 Tidak Tersedia (Habis)</span>
                                </label>
                            </div>
                        </div>

                        {{-- 3. KATEGORI (DI BAWAHNYA) --}}
                        <div class="filter-group">
                            <label class="filter-group-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                Kategori Barang
                            </label>
                            <div class="filter-options-grid filter-options-grid--cats">
                                <label class="filter-option-btn {{ empty(request('kategori')) ? 'filter-option-btn--active' : '' }}">
                                    <input type="radio" name="kategori" value="" {{ empty(request('kategori')) ? 'checked' : '' }}>
                                    <span>✨ Semua Kategori</span>
                                </label>
                                @if(isset($popularItems) && $popularItems->isNotEmpty())
                                <label class="filter-option-btn {{ request('kategori') === 'popular' ? 'filter-option-btn--active' : '' }}">
                                    <input type="radio" name="kategori" value="popular" {{ request('kategori') === 'popular' ? 'checked' : '' }}>
                                    <span>🔥 Sering Dipinjam</span>
                                </label>
                                @endif
                                @foreach($categories as $cat)
                                <label class="filter-option-btn {{ request('kategori') == $cat->id_kategori ? 'filter-option-btn--active' : '' }}">
                                    <input type="radio" name="kategori" value="{{ $cat->id_kategori }}" {{ request('kategori') == $cat->id_kategori ? 'checked' : '' }}>
                                    <span>{{ $cat->nama_kategori }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="filter-modal-footer">
                        <a href="{{ route('dashboard') }}" class="btn-filter-reset">Reset</a>
                        <button type="submit" class="btn-filter-apply">Terapkan Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
