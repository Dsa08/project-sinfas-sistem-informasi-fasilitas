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
    <nav class="navbar" id="main-navbar">
        <div class="navbar-left">
            {{-- Logo Placeholder - Ganti dengan logo nanti --}}
            {{-- Contoh: <img src="{{ asset('assets/logo-sinfas.png') }}" alt="SINFAS Logo" class="navbar-logo-img"> --}}
            <div class="navbar-logo-placeholder">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="6" fill="#1D67F2"/>
                    <path d="M9 22V10h3l3 8 3-8h3v12h-2.5V13.5L15.5 20h-2L10.5 13.5V22H9z" fill="#ffffff"/>
                </svg>
            </div>
            <span class="navbar-brand">@yield('brand_name', 'SINFAS')</span>
        </div>

        <div class="navbar-right">
            {{-- Notification Icon --}}
            <button class="navbar-icon-btn" id="notification-btn" title="Notifikasi">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </button>

            {{-- User Profile Icon --}}
            <button class="navbar-icon-btn" id="profile-btn" title="Profil">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </button>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="app-main">
        @yield('content')
    </main>
</body>
</html>
