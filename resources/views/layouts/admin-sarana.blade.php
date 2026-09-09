<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SINFAS - Panel Admin Sarana & Fasilitas">
    <title>@yield('title', 'Admin Sarana - SINFAS')</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-system-body">
    <div class="admin-system-layout">
        {{-- Sidebar Kiri --}}
        <aside class="system-sidebar" id="sarana-sidebar">
            <div class="system-sidebar-header">
                <div class="system-brand-link">
                    <div class="system-brand-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1D67F2" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M9 3v18"/>
                            <path d="M15 3v18"/>
                            <path d="M3 9h18"/>
                            <path d="M3 15h18"/>
                        </svg>
                    </div>
                    <span class="system-brand-name">SINFAS Admin</span>
                </div>
            </div>

            <nav class="system-sidebar-nav">
                <ul class="system-nav-list">
                    <li class="system-nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="system-nav-link {{ request()->routeIs('admin.dashboard') ? 'system-nav-link--active' : '' }}" id="nav-home">
                            <svg class="system-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                            <span>Beranda</span>
                        </a>
                    </li>
                    <li class="system-nav-item">
                        <a href="{{ route('admin.items') }}" class="system-nav-link {{ request()->routeIs('admin.items') ? 'system-nav-link--active' : '' }}" id="nav-items">
                            <svg class="system-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                <path d="m3.3 7 8.7 5 8.7-5"/>
                                <path d="M12 22V12"/>
                            </svg>
                            <span>Kelola Barang</span>
                        </a>
                    </li>
                    <li class="system-nav-item">
                        <a href="{{ route('admin.categories') }}" class="system-nav-link {{ request()->routeIs('admin.categories') ? 'system-nav-link--active' : '' }}" id="nav-categories">
                            <svg class="system-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
                            </svg>
                            <span>Kelola Kategori</span>
                        </a>
                    </li>
                    <li class="system-nav-item">
                        <a href="{{ route('admin.verifications') }}" class="system-nav-link {{ request()->routeIs('admin.verifications') ? 'system-nav-link--active' : '' }}" id="nav-verifications">
                            <svg class="system-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 11 3 3L22 4"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                            <span>Verifikasi Peminjaman</span>
                        </a>
                    </li>
                </ul>

                <div class="system-nav-divider"></div>

                <ul class="system-nav-list">
                    <li class="system-nav-item">
                        <a href="{{ route('profile') }}" class="system-nav-link {{ request()->routeIs('profile') ? 'system-nav-link--active' : '' }}" id="nav-profile">
                            <svg class="system-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span>Profil</span>
                        </a>
                    </li>
                    <li class="system-nav-item">
                        <button type="button" class="system-nav-link system-nav-btn-logout" id="logout-trigger-btn">
                            <svg class="system-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </li>
                </ul>
            </nav>

            {{-- User Profile Bottom Indicator --}}
            <div class="system-sidebar-footer">
                <div class="system-user-badge">
                    <div class="system-user-avatar" style="background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="#ffffff">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <div class="system-user-info">
                        <span class="system-user-name">{{ Auth::user()->nama ?? 'Admin Sarana' }}</span>
                        <span class="system-user-role" style="color: #bfdbfe; font-size: 0.75rem;">Operator</span>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Area Konten Utama --}}
        <div class="system-main-wrapper">
            {{-- Top Header Bar --}}
            <header class="system-topbar">
                <div class="system-topbar-left">
                    <h1 class="system-page-title">@yield('page_title', 'Beranda')</h1>
                </div>
                <div class="system-topbar-right">
                    {{-- Bell Notifikasi --}}
                    <button class="system-topbar-btn" id="sarana-notif-btn" title="Notifikasi" style="color: #111827;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                        </svg>
                    </button>
                    {{-- Help / Bantuan --}}
                    <button class="system-topbar-btn" id="sarana-help-btn" title="Bantuan & Info" style="color: #111827;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 16h-2v-2h2v2zm1.07-7.75l-.9.92C12.45 11.9 12 12.5 12 14h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/>
                        </svg>
                    </button>
                </div>
            </header>

            {{-- Main Body Content --}}
            <main class="system-content">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Logout Confirmation Modal --}}
    <div class="modal-overlay" id="logout-modal">
        <div class="modal-card">
            <div class="modal-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </div>
            <h3 class="modal-title">Konfirmasi Keluar</h3>
            <p class="modal-message">Apakah Anda yakin ingin keluar dari Admin Sarana SINFAS?</p>
            <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn--cancel" id="logout-cancel-btn">Batal</button>
                <button type="button" class="modal-btn modal-btn--confirm" id="logout-confirm-btn">Ya, Keluar</button>
            </div>
        </div>
    </div>

    {{-- Hidden Logout Form --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const triggerBtn = document.getElementById('logout-trigger-btn');
            const modal = document.getElementById('logout-modal');
            const cancelBtn = document.getElementById('logout-cancel-btn');
            const confirmBtn = document.getElementById('logout-confirm-btn');
            const logoutForm = document.getElementById('logout-form');

            if (triggerBtn && modal) {
                triggerBtn.addEventListener('click', function () {
                    modal.classList.add('modal-overlay--active');
                });

                if (cancelBtn) {
                    cancelBtn.addEventListener('click', function () {
                        modal.classList.remove('modal-overlay--active');
                    });
                }

                if (confirmBtn && logoutForm) {
                    confirmBtn.addEventListener('click', function () {
                        logoutForm.submit();
                    });
                }

                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        modal.classList.remove('modal-overlay--active');
                    }
                });
            }
        });
    </script>
</body>
</html>
