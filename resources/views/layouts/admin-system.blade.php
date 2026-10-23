{{-- 
  LAYOUT ADMIN SISTEM (SUPERADMIN / IT) — SINFAS
  File: resources/views/layouts/admin-system.blade.php
  Fungsi:
  - Kerangka layout panel kontrol administrator sistem / IT.
  - Sidebar Navigasi: Dashboard Overview Sistem, Manajemen Master Akun Pengguna, dan Pengaturan Sistem.
  - Topbar dengan profil admin, session info, dan tombol logout.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SINFAS - Panel Admin Sistem Informasi Fasilitas">
    <title>@yield('title', 'Admin Sistem - SINFAS')</title>
    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/logo-sinfas.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/logo-sinfas.png') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-system-body">
    <div class="admin-system-layout">
        {{-- Sidebar Kiri --}}
        <aside class="system-sidebar" id="system-sidebar">
            <div class="system-sidebar-header">
                <div class="system-brand-wrapper">
                    <img src="{{ asset('assets/logo-sinfas.png') }}" alt="SINFAS Logo" class="sidebar-logo-img">
                    <span class="system-brand-name">SINFAS Admin</span>
                </div>
            </div>

            <nav class="system-sidebar-nav">
                <ul class="system-nav-list">
                    <li class="system-nav-item">
                        <a href="{{ route('admin.sistem.dashboard') }}" class="system-nav-link {{ request()->routeIs('admin.sistem.dashboard') ? 'system-nav-link--active' : '' }}" id="nav-home">
                            <x-heroicon-o-home class="system-nav-icon" />
                            <span>Beranda</span>
                        </a>
                    </li>
                    <li class="system-nav-item">
                        <a href="{{ route('admin.sistem.accounts') }}" class="system-nav-link {{ request()->routeIs('admin.sistem.accounts') ? 'system-nav-link--active' : '' }}" id="nav-accounts">
                            <x-heroicon-o-user-circle class="system-nav-icon" />
                            <span>Kelola Akun</span>
                        </a>
                    </li>
                    <li class="system-nav-item">
                        <a href="{{ route('admin.sistem.settings') }}" class="system-nav-link {{ request()->routeIs('admin.sistem.settings') ? 'system-nav-link--active' : '' }}" id="nav-settings">
                            <x-heroicon-o-cog-6-tooth class="system-nav-icon" />
                            <span>Pengaturan Sistem</span>
                        </a>
                    </li>
                </ul>

                <div class="system-nav-divider"></div>

                <ul class="system-nav-list">
                    <li class="system-nav-item">
                        <a href="{{ route('profile') }}" class="system-nav-link {{ request()->routeIs('profile') ? 'system-nav-link--active' : '' }}" id="nav-profile">
                            <x-heroicon-o-user class="system-nav-icon" />
                            <span>Profil</span>
                        </a>
                    </li>
                    <li class="system-nav-item">
                        <button type="button" class="system-nav-link system-nav-btn-logout" id="logout-trigger-btn">
                            <x-heroicon-o-arrow-right-on-rectangle class="system-nav-icon" />
                            <span>Keluar</span>
                        </button>
                    </li>
                </ul>
            </nav>

            {{-- User Profile Bottom Indicator --}}
            <div class="system-sidebar-footer">
                <div class="system-user-badge">
                    <div class="system-user-avatar">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <div class="system-user-info">
                        <span class="system-user-name">{{ Auth::user()->nama ?? 'Admin Sistem' }}</span>
                        <span class="system-user-role">Admin Sistem</span>
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
                    <button class="system-topbar-btn" id="system-notif-btn" title="Notifikasi">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                        </svg>
                    </button>
                    {{-- Help / Bantuan --}}
                    <button class="system-topbar-btn" id="system-help-btn" title="Bantuan & Info">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
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
            <p class="modal-message">Apakah Anda yakin ingin keluar dari Admin Sistem SINFAS?</p>
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

    {{-- Modal Informasi Sistem & Bantuan --}}
    <div class="modal-overlay" id="system-help-modal">
        <div class="modal-card" style="max-width: 520px; text-align: left; padding: 1.75rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: #1d4ed8;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 16h-2v-2h2v2zm1.07-7.75l-.9.92C12.45 11.9 12 12.5 12 14h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0;">Informasi &amp; Bantuan Sistem</h3>
                </div>
                <button type="button" id="system-help-close" style="background: none; border: none; font-size: 1.25rem; color: #94a3b8; cursor: pointer;">&times;</button>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.84rem;">
                    <span style="color: #64748b;">Aplikasi:</span>
                    <strong style="color: #0f172a;">SINFAS (Sistem Informasi Fasilitas)</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.84rem;">
                    <span style="color: #64748b;">Instansi:</span>
                    <strong style="color: #0f172a;">SMK Negeri 11 Bandung</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.84rem;">
                    <span style="color: #64748b;">Versi Sistem:</span>
                    <span style="font-weight: 600; color: #166534; background: #dcfce7; padding: 0.1rem 0.5rem; border-radius: 4px; font-size: 0.78rem;">v1.2-stable</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.84rem;">
                    <span style="color: #64748b;">Status Basis Data:</span>
                    <span style="color: #166534; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #22c55e;"></span>
                        MySQL Terhubung
                    </span>
                </div>
            </div>

            <div style="font-size: 0.84rem; color: #475569; line-height: 1.5; margin-bottom: 1.25rem;">
                <strong style="display: block; color: #1e293b; margin-bottom: 0.35rem;">Kewenangan Administrator Sistem:</strong>
                <ul style="margin: 0; padding-left: 1.2rem;">
                    <li><strong>Kelola Akun:</strong> Menambah, mengubah hak akses, dan menonaktifkan pengguna.</li>
                    <li><strong>Reset Sandi:</strong> Membuat sandi sementara saat pengguna lupa password.</li>
                    <li><strong>Pencadangan Data:</strong> Mengunduh arsip database SQL dan memulihkan data saat dibutuhkan.</li>
                    <li><strong>Jam Operasional:</strong> Mengatur batasan rentang waktu layanan fasilitas.</li>
                </ul>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="button" id="system-help-ok-btn" style="padding: 0.55rem 1.2rem; background: #1d4ed8; color: #fff; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">Tutup Panduan</button>
            </div>
        </div>
    </div>

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

            // Topbar Notification Click Handler
            const notifBtn = document.getElementById('system-notif-btn');
            if (notifBtn) {
                notifBtn.addEventListener('click', function () {
                    if (window.showAppToast) {
                        window.showAppToast('Layanan sistem, database, dan web server beroperasi normal tanpa peringatan.', {
                            title: 'Status Sistem Normal',
                            type: 'info'
                        });
                    } else {
                        alert('Status Sistem Normal: Semua layanan beroperasi optimal.');
                    }
                });
            }

            // Topbar Help Modal Handler
            const helpBtn = document.getElementById('system-help-btn');
            const helpModal = document.getElementById('system-help-modal');
            const helpCloseBtn = document.getElementById('system-help-close');
            const helpOkBtn = document.getElementById('system-help-ok-btn');

            if (helpBtn && helpModal) {
                helpBtn.addEventListener('click', function () {
                    helpModal.classList.add('modal-overlay--active');
                });

                const closeHelp = function () {
                    helpModal.classList.remove('modal-overlay--active');
                };

                if (helpCloseBtn) helpCloseBtn.addEventListener('click', closeHelp);
                if (helpOkBtn) helpOkBtn.addEventListener('click', closeHelp);

                helpModal.addEventListener('click', function (e) {
                    if (e.target === helpModal) closeHelp();
                });
            }
        });
    </script>
    @include('components.app-dialogs')
    @include('components.table-aura-scroll')
</body>
</html>
