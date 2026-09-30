<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SINFAS membantu siswa melihat ketersediaan barang dan mengajukan peminjaman sarana sekolah secara online.">
    <title>@yield('title', 'SINFAS - Sistem Informasi Fasilitas')</title>
    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/logo-sinfas.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/logo-sinfas.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-body">
    {{-- Background Image --}}
    <div class="landing-bg" aria-hidden="true">
        <img src="{{ asset('assets/pictures/bg_login_register.jpg') }}" alt="Latar Belakang SMK Negeri 11 Bandung" class="landing-bg-img">
    </div>
    <div class="landing-overlay" aria-hidden="true"></div>

    <div class="landing-wrapper">
        @yield('content')
    </div>
</body>
</html>
