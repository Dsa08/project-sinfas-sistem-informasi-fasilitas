<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SINFAS - Sistem Informasi Fasilitas & Sarana Prasarana SMK Negeri 11 Bandung. Peminjaman sarana sekolah secara online, cepat, dan transparan.">
    <title>@yield('title', 'SINFAS - Sistem Informasi Fasilitas SMK Negeri 11 Bandung')</title>
    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/logo-sinfas.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/logo-sinfas.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-body">
    @yield('content')
</body>
</html>
