<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SINFAS - Sistem Informasi Fasilitas. Aplikasi peminjaman alat sarana secara online.">
    <title>@yield('title', 'SINFAS - Sistem Informasi Fasilitas')</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-body">
    {{-- Navbar --}}
    <nav class="navbar" id="main-navbar" style="position: relative;">
        <div class="navbar-left">
            {{-- Logo Placeholder - Ganti dengan logo nanti --}}
            {{-- Contoh: <img src="{{ asset('assets/logo-sinfas.png') }}" alt="SINFAS Logo" class="navbar-logo-img"> --}}
            <div class="navbar-logo-placeholder">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="6" fill="#1E40AF"/>
                    <path d="M9 22V10h3l3 8 3-8h3v12h-2.5V13.5L15.5 20h-2L10.5 13.5V22H9z" fill="#ffffff"/>
                </svg>
            </div>
            <span class="navbar-brand">@yield('brand_name', 'SINFAS')</span>
        </div>

        {{-- Navbar Center Title --}}
        @hasSection('navbar_title')
            <div class="navbar-center">
                @yield('navbar_title')
            </div>
        @endif

        <div class="navbar-right">
            {{-- Loan Status Icon --}}
            <a href="{{ route('loan.status') }}" class="navbar-icon-btn" id="loan-status-btn" title="Status Pengajuan">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                    <path d="m9 14 2 2 4-4"/>
                </svg>
            </a>

            {{-- Notification Icon --}}
            <button class="navbar-icon-btn" id="notification-btn" title="Notifikasi">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </button>

            {{-- User Profile Icon --}}
            <a href="{{ route('profile') }}" class="navbar-icon-btn" id="profile-btn" title="Profil ({{ Auth::user()->nama ?? 'Pengguna' }})">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </a>

            {{-- Logout Button (triggers modal) --}}
            <button type="button" class="navbar-icon-btn" id="logout-trigger-btn" title="Keluar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>

            {{-- Hidden Logout Form --}}
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </nav>

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
            <p class="modal-message">Apakah Anda yakin ingin keluar dari SINFAS?</p>
            <div class="modal-actions">
                <button type="button" class="modal-btn modal-btn--cancel" id="logout-cancel-btn">Batal</button>
                <button type="button" class="modal-btn modal-btn--confirm" id="logout-confirm-btn">Ya, Keluar</button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <main class="app-main">
        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const triggerBtn = document.getElementById('logout-trigger-btn');
            const modal = document.getElementById('logout-modal');
            const cancelBtn = document.getElementById('logout-cancel-btn');
            const confirmBtn = document.getElementById('logout-confirm-btn');
            const logoutForm = document.getElementById('logout-form');

            triggerBtn.addEventListener('click', function () {
                modal.classList.add('modal-overlay--active');
            });

            cancelBtn.addEventListener('click', function () {
                modal.classList.remove('modal-overlay--active');
            });

            confirmBtn.addEventListener('click', function () {
                logoutForm.submit();
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.classList.remove('modal-overlay--active');
                }
            });
        });
    </script>
</body>
</html>
