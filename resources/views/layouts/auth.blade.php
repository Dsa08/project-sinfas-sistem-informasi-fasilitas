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
<body class="auth-body">
    {{-- Background Image --}}
    <div class="auth-bg">
        <img src="{{ asset('assets/pictures/bg_login_register.jpg') }}" alt="Background" class="auth-bg-img">
    </div>

    {{-- Overlay gradient --}}
    <div class="auth-overlay"></div>

    <main class="auth-main">
        @yield('content')
    </main>
</body>
</html>
