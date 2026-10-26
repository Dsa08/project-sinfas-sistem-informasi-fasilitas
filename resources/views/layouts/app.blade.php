{{-- 
  LAYOUT UTAMA PENGGUNA â€” SINFAS
  File: resources/views/layouts/app.blade.php
  Fungsi:
  - Kerangka layout induk antarmuka pengguna (Dashboard katalog, status peminjaman, form pinjam & kembali).
  - Memuat Google Fonts (Inter & Poppins), bundle asset Vite (app.css & app.js).
  - Menyediakan Navbar atas interaktif: Tombol status pengajuan, indikator lonceng notifikasi, dan avatar profil dropdown.
  - Menampilkan global flash messages (success, error, status).
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SINFAS - Sistem Informasi Fasilitas. Aplikasi peminjaman alat sarana secara online.">
    <title>@yield('title', 'SINFAS - Sistem Informasi Fasilitas')</title>
    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/logo-sinfas.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/logo-sinfas.png') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(Auth::user()?->isPeminjam())
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <meta name="theme-color" content="#1e40af">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-body">
    {{-- Navbar --}}
    <nav class="navbar {{ request()->routeIs('dashboard') ? 'navbar--dashboard' : '' }}" id="main-navbar">
        <div class="navbar-main-row">
        <div class="navbar-left">
            @hasSection('back_url')
                <a href="@yield('back_url')" class="navbar-back-btn" title="Kembali">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    <span>Kembali</span>
                </a>
                @hasSection('navbar_title')
                    <span class="navbar-page-title">@yield('navbar_title')</span>
                @endif
            @else
                <img src="{{ asset('assets/logo-sinfas.png') }}" alt="SINFAS Logo" class="navbar-logo-img">
                <span class="navbar-brand">@yield('brand_name', 'SINFAS')</span>
            @endif
        </div>

        {{-- Center: Search Bar on Dashboard (Shopee-style) --}}
        @if(request()->routeIs('dashboard'))
            @include('components.dashboard-search')
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

            {{-- Notification Icon with Badge & Dropdown --}}
            <div class="navbar-notif-wrapper" style="position: relative;">
                <button type="button" class="navbar-icon-btn" id="notification-btn" title="Notifikasi" onclick="toggleNotifDropdown(event)">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="navbar-notif-badge" id="navbar-notif-badge" style="display: none;">0</span>
                </button>

                {{-- Notification Dropdown Popover --}}
                <div class="navbar-notif-dropdown" id="navbar-notif-dropdown">
                    <div class="navbar-notif-dropdown-header">
                        <span class="navbar-notif-dropdown-title">Notifikasi</span>
                        <a href="{{ route('notifications') }}" class="navbar-notif-dropdown-link">Lihat Semua</a>
                    </div>
                    <div class="navbar-notif-dropdown-body" id="navbar-notif-dropdown-body">
                        <div class="navbar-notif-loading">Memuat notifikasi...</div>
                    </div>
                    <div class="navbar-notif-dropdown-footer">
                        <a href="{{ route('notifications') }}" class="btn-navbar-notif-all">Buka Pusat Notifikasi &rarr;</a>
                    </div>
                </div>
            </div>

            {{-- User Profile Icon --}}
            <a href="{{ route('profile') }}" class="navbar-icon-btn" id="profile-btn" title="Profil ({{ Auth::user()->nama ?? 'Pengguna' }})">
                @if(Auth::user() && Auth::user()->foto_url)
                    <img src="{{ Auth::user()->foto_url }}" alt="{{ Auth::user()->nama }}" style="width: 26px; height: 26px; border-radius: 50%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                @else
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                @endif
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

    {{-- Waiting Approval Confirmation Pop Up (Sesuai Desain Figma) --}}
    <div class="modal-overlay" id="waiting-approval-modal">
        <div class="waiting-popup-card">
            <div class="waiting-popup-body">
                {{-- Clock Icon --}}
                <div class="waiting-popup-icon">
                    <svg width="86" height="86" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        {{-- Outer Cyan Ring & Face --}}
                        <circle cx="50" cy="50" r="41" stroke="#00A3C4" stroke-width="11" fill="#f1f5f9"/>
                        {{-- Minute Hand (Points to 12) --}}
                        <line x1="50" y1="50" x2="50" y2="24" stroke="#0f172a" stroke-width="6" stroke-linecap="round"/>
                        {{-- Hour Hand (Points to ~4:20) --}}
                        <line x1="50" y1="50" x2="70" y2="70" stroke="#0f172a" stroke-width="6" stroke-linecap="round"/>
                        {{-- Center Pivot --}}
                        <circle cx="50" cy="50" r="4.5" fill="#00A3C4" stroke="#0f172a" stroke-width="2"/>
                    </svg>
                </div>
                {{-- Text Content --}}
                <div class="waiting-popup-text">
                    <h3 class="waiting-popup-title">Menunggu Persetujuan Admin</h3>
                    <p class="waiting-popup-desc">
                        Permintaan peminjaman Anda telah masuk ke dalam antrean. Mohon tunggu konfirmasi dari Admin Sarana.
                    </p>
                </div>
            </div>
            {{-- Action Button --}}
            <div class="waiting-popup-actions">
                <button type="button" class="btn-waiting-popup-close" onclick="closeWaitingApprovalModal()">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <main class="app-main">
        @yield('content')
    </main>

    <style>
        .waiting-popup-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem 2.25rem;
            max-width: 540px;
            width: 90%;
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.2), 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            animation: waitingPopIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes waitingPopIn {
            from {
                opacity: 0;
                transform: scale(0.92);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        .waiting-popup-body {
            display: flex;
            align-items: center;
            gap: 1.75rem;
        }
        .waiting-popup-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .waiting-popup-text {
            flex: 1;
            min-width: 0;
        }
        .waiting-popup-title {
            font-size: 1.28rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 0.5rem 0;
            line-height: 1.3;
        }
        .waiting-popup-desc {
            font-size: 0.95rem;
            color: #475569;
            line-height: 1.55;
            margin: 0;
        }
        .waiting-popup-actions {
            display: flex;
            justify-content: flex-end;
        }
        .btn-waiting-popup-close {
            background-color: #1e40af;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 0.65rem 2rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.25);
            transition: background-color 0.15s ease, transform 0.1s ease;
        }
        .btn-waiting-popup-close:hover {
            background-color: #1d4ed8;
        }
        .btn-waiting-popup-close:active {
            transform: scale(0.98);
        }
        @media (max-width: 600px) {
            .waiting-popup-body {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            .waiting-popup-actions {
                justify-content: center;
            }
            .btn-waiting-popup-close {
                width: 100%;
            }
        }
        /* SINFAS In-App Floating Toast Engine */
        .sinfas-user-toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            pointer-events: none;
        }
        .sinfas-user-toast-card {
            display: flex;
            align-items: flex-start;
            gap: 1.15rem;
            background: #ffffff;
            min-width: 320px;
            max-width: 440px;
            padding: 1.1rem 1.35rem;
            border-radius: 16px;
            box-shadow: 0 16px 36px -4px rgba(0, 0, 0, 0.14), 0 4px 12px -2px rgba(0, 0, 0, 0.06);
            border: 1px solid #edf2f7;
            animation: userToastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            position: relative;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: auto;
        }
        .sinfas-user-toast-card--clickable {
            cursor: pointer;
        }
        .sinfas-user-toast-card--clickable:hover {
            transform: translateX(-4px) scale(1.02);
            box-shadow: 0 20px 40px -4px rgba(0, 0, 0, 0.18), 0 8px 16px -2px rgba(0, 0, 0, 0.08);
        }
        .sinfas-user-toast-card.toast--hiding {
            opacity: 0;
            transform: translateX(40px) scale(0.95);
        }
        @keyframes userToastSlideIn {
            from {
                opacity: 0;
                transform: translateX(50px) scale(0.92);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }
        .sinfas-user-toast-card--success { border-left: 4px solid #10b981; }
        .sinfas-user-toast-card--error { border-left: 4px solid #ef4444; }
        .sinfas-user-toast-card--info { border-left: 4px solid #3b82f6; }
        .sinfas-user-toast-card--warning { border-left: 4px solid #f59e0b; }

        .sinfas-user-toast-icon {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sinfas-user-toast-icon--success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }
        .sinfas-user-toast-icon--error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
        }
        .sinfas-user-toast-icon--info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        .sinfas-user-toast-icon--warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
        }
        .sinfas-user-toast-content {
            flex: 1;
            min-width: 0;
        }
        .sinfas-user-toast-title {
            font-size: 0.96rem;
            font-weight: 700;
            color: #111827;
            line-height: 1.35;
        }
        .sinfas-user-toast-desc {
            font-size: 0.84rem;
            color: #4b5563;
            margin-top: 0.25rem;
            line-height: 1.4;
            word-break: break-word;
        }
        .sinfas-user-toast-link {
            margin-top: 0.35rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: #2563eb;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .sinfas-user-toast-close {
            background: transparent;
            border: none;
            color: #9ca3af;
            font-size: 1.35rem;
            line-height: 1;
            cursor: pointer;
            padding: 0.2rem;
            border-radius: 6px;
            transition: color 0.15s ease;
            margin-left: -0.25rem;
            align-self: flex-start;
        }
        .sinfas-user-toast-close:hover {
            color: #374151;
        }
        .pulse-badge {
            animation: sinfasPulse 1.8s infinite;
        }
        @keyframes sinfasPulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1.08); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>

    <script>
        function closeUserToast(btnOrEl) {
            const card = btnOrEl.closest('.sinfas-user-toast-card');
            if (!card) return;
            card.classList.add('toast--hiding');
            setTimeout(() => { card.remove(); }, 300);
        }

        // Web Audio Chime Sound (Melodic notification cue for students)
        function playUserNotificationChime() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
                const now = ctx.currentTime;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                // Harmonic two-tone chime: E5 (659.25Hz) -> G#5 (830.61Hz)
                osc.frequency.setValueAtTime(659.25, now);
                osc.frequency.setValueAtTime(830.61, now + 0.12);

                gain.gain.setValueAtTime(0.2, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.45);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.45);
            } catch (_) {}
        }

        // Desktop OS Notification API
        function sendUserDesktopNotification(title, body, url) {
            if (!('Notification' in window)) return;
            if (Notification.permission === 'granted') {
                try {
                    const notif = new Notification(title, {
                        body: body,
                        icon: '{{ asset("assets/logo-sinfas.png") }}',
                        badge: '{{ asset("assets/logo-sinfas.png") }}',
                        data: { url: url || '{{ route("loan.status") }}' },
                        tag: 'sinfas-user-notif'
                    });
                    notif.onclick = function(e) {
                        e.preventDefault();
                        window.focus();
                        if (url) window.location.href = url;
                    };
                } catch (_) {}
            }
        }

        window.showUserToast = function(type, title, message, url) {
            let container = document.getElementById('user-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'user-toast-container';
                container.className = 'sinfas-user-toast-container';
                document.body.appendChild(container);
            }

            const card = document.createElement('div');
            card.className = `sinfas-user-toast-card sinfas-user-toast-card--${type} ${url ? 'sinfas-user-toast-card--clickable' : ''}`;

            let iconSvg = '';
            if (type === 'success') {
                iconSvg = `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
            } else if (type === 'error') {
                iconSvg = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;
            } else if (type === 'warning') {
                iconSvg = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;
            } else {
                iconSvg = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>`;
            }

            card.innerHTML = `
                <div class="sinfas-user-toast-icon sinfas-user-toast-icon--${type}">
                    ${iconSvg}
                </div>
                <div class="sinfas-user-toast-content">
                    <div class="sinfas-user-toast-title">${title || 'Informasi Notifikasi'}</div>
                    ${message ? `<div class="sinfas-user-toast-desc">${message}</div>` : ''}
                    ${url ? `<div class="sinfas-user-toast-link">Lihat Status &rarr;</div>` : ''}
                </div>
                <button type="button" class="sinfas-user-toast-close" onclick="event.stopPropagation(); closeUserToast(this)">&times;</button>
            `;

            if (url) {
                card.addEventListener('click', (e) => {
                    if (e.target.closest('.sinfas-user-toast-close')) return;
                    window.location.href = url;
                });
            }

            container.appendChild(card);

            setTimeout(() => {
                if (card.parentElement) {
                    closeUserToast(card);
                }
            }, 6000);
        };

        window.openWaitingApprovalModal = function() {
            const modal = document.getElementById('waiting-approval-modal');
            if (modal) {
                modal.classList.add('modal-overlay--active');
            }
        };

        window.closeWaitingApprovalModal = function() {
            const modal = document.getElementById('waiting-approval-modal');
            if (modal) {
                modal.classList.remove('modal-overlay--active');
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            // Auto open waiting approval modal if flash session is set
            @if(session('show_waiting_modal'))
                openWaitingApprovalModal();
            @endif

            const waitingModal = document.getElementById('waiting-approval-modal');
            if (waitingModal) {
                waitingModal.addEventListener('click', function (e) {
                    if (e.target === waitingModal) {
                        closeWaitingApprovalModal();
                    }
                });
            }

            // Close on escape
            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeWaitingApprovalModal();
                }
            });

            // Logout modal logic
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

            // Helper: dapatkan icon SVG berdasarkan tipe notifikasi
            function getNotifIcon(tipe) {
                const baseStyle = 'flex-shrink:0;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;';
                if (tipe === 'ditolak') {
                    // Merah â€” pengajuan ditolak
                    return `<div style="${baseStyle}background:#fee2e2;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>`;
                } else if (tipe === 'disetujui' || tipe === 'pengembalian_dikonfirmasi') {
                    // Hijau â€” disetujui / pengembalian dikonfirmasi
                    return `<div style="${baseStyle}background:#dcfce7;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>`;
                } else {
                    // Kuning â€” menunggu / info / default
                    return `<div style="${baseStyle}background:#fef9c3;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>`;
                }
            }

            // Notification Dropdown Logic â€” buka dropdown + langsung mark-all-read
            window.toggleNotifDropdown = function(e) {
                if (e) e.stopPropagation();
                const dropdown = document.getElementById('navbar-notif-dropdown');
                if (!dropdown) return;

                const isOpening = !dropdown.classList.contains('navbar-notif-dropdown--active');
                dropdown.classList.toggle('navbar-notif-dropdown--active');

                // Jika baru dibuka dan ada badge merah, langsung mark-all-read via AJAX
                if (isOpening) {
                    const badge = document.getElementById('navbar-notif-badge');
                    const hasUnread = badge && badge.style.display !== 'none' && parseInt(badge.textContent || '0') > 0;
                    if (hasUnread) {
                        // Sembunyikan badge secara instan (UX responsif)
                        badge.style.display = 'none';

                        // Kirim request mark-all-read ke server
                        fetch('{{ route("notifications.readAll") }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') 
                                    ? document.querySelector('meta[name="csrf-token"]').content
                                    : '{{ csrf_token() }}'
                            }
                        }).catch(() => {});
                    }

                    // Refresh daftar notifikasi agar item tampil sudah-dibaca
                    refreshNotifications(false);
                }
            };

            // Close notification dropdown when clicking outside
            document.addEventListener('click', function(e) {
                const wrapper = document.querySelector('.navbar-notif-wrapper');
                const dropdown = document.getElementById('navbar-notif-dropdown');
                if (wrapper && dropdown && !wrapper.contains(e.target)) {
                    dropdown.classList.remove('navbar-notif-dropdown--active');
                }
            });

            // Web Push & Service Worker Setup for Siswa/Pegawai (User)
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('{{ asset("sw.js") }}').then(async (reg) => {
                    const vapidPublicKey = @json(config('services.webpush.public_key'));
                    if (vapidPublicKey && 'PushManager' in window && Notification.permission === 'granted') {
                        try {
                            const sub = await reg.pushManager.getSubscription();
                            if (!sub) {
                                const padding = '='.repeat((4 - vapidPublicKey.length % 4) % 4);
                                const base64 = (vapidPublicKey + padding).replace(/-/g, '+').replace(/_/g, '/');
                                const appServerKey = Uint8Array.from(atob(base64), c => c.charCodeAt(0));
                                const newSub = await reg.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey: appServerKey
                                });
                                fetch('{{ route("push.subscriptions.store") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                    },
                                    body: JSON.stringify(newSub.toJSON())
                                });
                            }
                        } catch (_) {}
                    }
                }).catch(() => {});
            }

            // Notification Permission: Request permission on user's first interaction
            function tryRequestNotificationPermission() {
                if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission();
                }
            }
            document.addEventListener('click', tryRequestNotificationPermission, { once: true });

            // Real-time Push Notification Engine for User (Siswa/Pegawai)
            let lastKnownUserNotifId = parseInt(sessionStorage.getItem('sinfas_last_user_notif_id') || '0', 10);
            let userNotifInitialized = sessionStorage.getItem('sinfas_user_notif_initialized') === 'true';

            // Fetch and update notification count and preview
            // updateBadge=true untuk refresh angka badge, false untuk hanya update list item
            function refreshNotifications(updateBadge = true) {
                fetch('{{ route("notifications.count") }}', {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    const badge = document.getElementById('navbar-notif-badge');
                    const count = data.unread_count || 0;
                    const text = count > 99 ? '99+' : count;

                    if (updateBadge && badge) {
                        if (count > 0) {
                            badge.textContent = text;
                            badge.style.display = 'flex';
                            badge.classList.add('pulse-badge');
                        } else {
                            badge.style.display = 'none';
                            badge.classList.remove('pulse-badge');
                        }
                    }

                    const body = document.getElementById('navbar-notif-dropdown-body');
                    const recent = Array.isArray(data.recent) ? data.recent : [];
                    if (body && recent) {
                        if (recent.length === 0) {
                            body.innerHTML = '<div class="navbar-notif-empty">Tidak ada notifikasi</div>';
                        } else {
                            body.innerHTML = recent.map(n => {
                                const iconHtml = getNotifIcon(n.tipe);
                                return `
                                <a href="{{ route('notifications') }}" class="navbar-notif-item ${!n.status_baca ? 'navbar-notif-item--unread' : ''}">
                                    <div style="display:flex;gap:10px;align-items:flex-start;">
                                        ${iconHtml}
                                        <div style="flex:1;min-width:0;">
                                            <div class="navbar-notif-item-header">
                                                <span class="navbar-notif-item-title">${n.judul}</span>
                                                <span class="navbar-notif-item-time">${window.formatNotificationTimestamp(n.created_at_iso)}</span>
                                            </div>
                                            <p class="navbar-notif-item-desc">${n.pesan}</p>
                                            ${n.hari_berlalu ? `<span class="badge-duration-mini badge-duration--${n.warna_durasi}">${n.hari_berlalu} hari</span>` : ''}
                                        </div>
                                    </div>
                                </a>`;
                            }).join('');
                        }
                    }

                    // Deteksi notifikasi baru yang masuk secara real-time
                    if (recent.length > 0) {
                        const currentMaxId = Math.max(...recent.map(r => r.id_notifikasi || 0));

                        if (!userNotifInitialized) {
                            // Sesi pertama dibuka: catat ID tertinggi tanpa memunculkan toast spam untuk notifikasi lama
                            lastKnownUserNotifId = currentMaxId;
                            sessionStorage.setItem('sinfas_last_user_notif_id', lastKnownUserNotifId);
                            sessionStorage.setItem('sinfas_user_notif_initialized', 'true');
                            userNotifInitialized = true;
                        } else if (currentMaxId > lastKnownUserNotifId) {
                            // Temukan notifikasi baru yang belum dibaca
                            const newNotifs = recent.filter(r => (r.id_notifikasi || 0) > lastKnownUserNotifId && !r.status_baca);

                            if (newNotifs.length > 0) {
                                lastKnownUserNotifId = currentMaxId;
                                sessionStorage.setItem('sinfas_last_user_notif_id', lastKnownUserNotifId);

                                // Mainkan nada lonceng Web Audio API
                                playUserNotificationChime();

                                newNotifs.forEach(item => {
                                    // Tentukan tipe toast dan link aksi
                                    let toastType = 'info';
                                    if (item.tipe === 'ditolak') {
                                        toastType = 'error';
                                    } else if (item.tipe === 'disetujui' || item.tipe === 'pengembalian_dikonfirmasi') {
                                        toastType = 'success';
                                    } else if (item.tipe === 'batas_waktu') {
                                        toastType = 'warning';
                                    }

                                    const actionUrl = '{{ route("loan.status") }}';

                                    // 1. Munculkan In-App Floating Toast
                                    window.showUserToast(toastType, item.judul, item.pesan, actionUrl);

                                    // 2. Kirim Notifikasi Desktop OS
                                    sendUserDesktopNotification(item.judul, item.pesan, actionUrl);

                                    // 3. Dispatch event agar halaman aktif (seperti status peminjaman) bisa merespons seketika
                                    window.dispatchEvent(new CustomEvent('sinfas:user-notification', { detail: item }));
                                });
                            }
                        }
                    }
                })
                .catch(() => {});
            }

            // Polling adaptif: 5 detik saat tab aktif, 15 detik saat tab di latar belakang
            let userPollTimer = null;
            function startUserPolling(intervalMs) {
                if (userPollTimer) clearInterval(userPollTimer);
                userPollTimer = setInterval(refreshNotifications, intervalMs);
            }

            refreshNotifications();
            startUserPolling(5000);

            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    refreshNotifications();
                    startUserPolling(5000);
                } else {
                    startUserPolling(15000);
                }
            });
        });
    </script>

    {{-- Mobile Bottom Navigation Bar (Khusus Layar HP / Mobile) --}}
    <nav class="mobile-bottom-nav" id="mobile-bottom-nav" aria-label="Navigasi Bawah">
        {{-- 1. Beranda --}}
        <a href="{{ route('dashboard') }}" class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'mobile-nav-item--active' : '' }}">
            <div class="mobile-nav-icon-wrap">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <span class="mobile-nav-label">Beranda</span>
        </a>

        {{-- 2. Status Pinjaman --}}
        <a href="{{ route('loan.status') }}" class="mobile-nav-item {{ request()->routeIs('loan.status') || request()->routeIs('loan.return') ? 'mobile-nav-item--active' : '' }}">
            <div class="mobile-nav-icon-wrap">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                    <path d="m9 14 2 2 4-4"/>
                </svg>
            </div>
            <span class="mobile-nav-label">Status</span>
        </a>

        {{-- 3. Profil Saya --}}
        <a href="{{ route('profile') }}" class="mobile-nav-item {{ request()->routeIs('profile') ? 'mobile-nav-item--active' : '' }}">
            <div class="mobile-nav-icon-wrap">
                @if(Auth::user() && Auth::user()->foto_url)
                    <img src="{{ Auth::user()->foto_url }}" alt="Profil" class="mobile-nav-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                @else
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                @endif
            </div>
            <span class="mobile-nav-label">Profil</span>
        </a>
    </nav>
    @if(Auth::user()?->isPeminjam())
        <div class="pwa-install-overlay" id="pwa-install-overlay" hidden>
            <section class="pwa-install-dialog" role="dialog" aria-modal="true" aria-labelledby="pwa-install-title" aria-describedby="pwa-install-description">
                <button class="pwa-install-close" type="button" id="pwa-install-close" aria-label="Tutup">&times;</button>
                <div class="pwa-install-mark" aria-hidden="true">S</div>
                <span class="pwa-install-eyebrow">Akses cepat SINFAS</span>
                <h2 id="pwa-install-title">Tambahkan pintasan SINFAS?</h2>
                <p id="pwa-install-description">Letakkan pintasan SINFAS di perangkat ini agar lebih cepat dibuka kapan saja.</p>
                <div class="pwa-install-ios-guide" id="pwa-install-ios-guide" hidden>
                    <span>Gunakan menu berbagi atau menu browser, lalu pilih opsi untuk menambahkan SINFAS ke layar utama atau memasang aplikasi.</span>
                </div>
                <div class="pwa-install-actions">
                    <button class="pwa-install-button" type="button" id="pwa-install-button">Pasang pintasan</button>
                    <button class="pwa-install-later" type="button" id="pwa-install-later">Nanti saja</button>
                </div>
            </section>
        </div>
    @endif
    @include('components.app-dialogs')
    @if(Auth::user()?->isPeminjam())
        <script>
            (() => {
                const overlay = document.getElementById('pwa-install-overlay');
                const installButton = document.getElementById('pwa-install-button');
                const laterButton = document.getElementById('pwa-install-later');
                const closeButton = document.getElementById('pwa-install-close');
                const iosGuide = document.getElementById('pwa-install-ios-guide');
                if (!overlay || !installButton || !laterButton || !closeButton) return;

                const dismissedKey = 'sinfas-pwa-install-dismissed';
                const installedKey = 'sinfas-pwa-install-installed';
                const legacyDismissedKey = 'sinfas-pwa-install-dismissed-at';
                let installPrompt = null;
                let isIosSafari = false;
                let shown = false;

                const isInstalled = () => window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
                const wasDismissed = () => localStorage.getItem(dismissedKey) === '1'
                    || localStorage.getItem(installedKey) === '1'
                    || Boolean(localStorage.getItem(legacyDismissedKey));
                const hidePrompt = (remember = false) => {
                    overlay.hidden = true;
                    if (remember) localStorage.setItem(dismissedKey, '1');
                };
                const showPrompt = () => {
                    if (shown || isInstalled() || wasDismissed()) return;
                    shown = true;
                    overlay.hidden = false;
                    installButton.focus();
                };

                const userAgent = navigator.userAgent || '';
                isIosSafari = /iphone|ipad|ipod/i.test(userAgent)
                    && /safari/i.test(userAgent)
                    && !/crios|fxios|edgios|opios/i.test(userAgent);

                if (isIosSafari && iosGuide) {
                    iosGuide.hidden = false;
                    installButton.hidden = true;
                    window.setTimeout(showPrompt, 1200);
                }

                window.addEventListener('beforeinstallprompt', (event) => {
                    event.preventDefault();
                    installPrompt = event;
                    window.setTimeout(showPrompt, 1200);
                });

                if (isInstalled()) localStorage.setItem(installedKey, '1');
                window.addEventListener('appinstalled', () => {
                    localStorage.setItem(installedKey, '1');
                    hidePrompt(true);
                });
                installButton.addEventListener('click', async () => {
                    if (isIosSafari) {
                        hidePrompt(true);
                        return;
                    }
                    if (!installPrompt) return;

                    installButton.disabled = true;
                    try {
                        await installPrompt.prompt();
                        const choice = await installPrompt.userChoice;
                        installPrompt = null;
                        if (choice.outcome === 'accepted') {
                            localStorage.setItem(installedKey, '1');
                            hidePrompt(true);
                        } else {
                            hidePrompt();
                        }
                    } finally {
                        installButton.disabled = false;
                    }
                });

                laterButton.addEventListener('click', () => hidePrompt(true));
                closeButton.addEventListener('click', () => hidePrompt(true));
                overlay.addEventListener('click', (event) => {
                    if (event.target === overlay) hidePrompt(true);
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !overlay.hidden) hidePrompt(true);
                });
            })();

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(() => {}));
            }
        </script>
    @endif
    @include('components.table-aura-scroll')
</body>
</html>
