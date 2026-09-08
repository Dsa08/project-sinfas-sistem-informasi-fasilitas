{{-- 
  HALAMAN REGISTRASI SISWA BARU — SINFAS
  File: resources/views/auth/register.blade.php
  Fitur:
  - Pendaftaran akun mandiri untuk siswa terdaftar.
  - Validasi NIS terhadap database master siswa sekolah.
  - Input nama lengkap, nomor kontak, username unik, dan email aktif.
  - Validasi kata sandi kuat (minimal 8 karakter kombinasi huruf & angka) serta konfirmasi sandi.
--}}
@extends('layouts.auth')

@section('title', 'Register - SINFAS')

@section('content')
<div class="auth-card auth-card--register">
    {{-- Logo Section --}}
    <div class="auth-logo-section">
        <div class="auth-logo-wrapper">
            {{-- Logo Placeholder - Ganti dengan logo nanti --}}
            {{-- Contoh: <img src="{{ asset('assets/logo-sinfas.png') }}" alt="SINFAS Logo" class="auth-logo-img"> --}}
            <div class="auth-logo-placeholder">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="40" height="40" rx="8" fill="#1E40AF" fill-opacity="0.1"/>
                    <path d="M12 28V12h4l4 10 4-10h4v16h-3V17l-3.5 9h-3L15 17v11h-3z" fill="#1E40AF"/>
                </svg>
            </div>
            <h1 class="auth-brand">SINFAS</h1>
        </div>
        <p class="auth-subtitle">Sistem Informasi Fasilitas</p>
    </div>

    {{-- Error Alerts --}}
    @if($errors->any())
        <div class="auth-alert auth-alert--danger">
            <ul class="auth-alert-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Register Form --}}
    <form action="{{ route('register.post') }}" method="POST" class="auth-form" id="register-form">
        @csrf

        <div class="form-group">
            <label for="full_name" class="form-label">Full Name</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                class="form-input @error('full_name') form-input--error @enderror"
                placeholder="Masukkan nama lengkap"
                value="{{ old('full_name') }}"
                required
                autocomplete="name"
            >
        </div>

        <div class="form-group">
            <label for="nis_nip" class="form-label">NIS / NIP</label>
            <input
                type="text"
                id="nis_nip"
                name="nis_nip"
                class="form-input @error('nis_nip') form-input--error @enderror"
                placeholder="Masukkan NIS (Siswa) atau NIP (Pegawai)"
                value="{{ old('nis_nip') }}"
                required
            >
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-input @error('email') form-input--error @enderror"
                placeholder="contoh: nama@email.com"
                value="{{ old('email') }}"
                required
                autocomplete="email"
            >
        </div>

        <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                class="form-input @error('username') form-input--error @enderror"
                placeholder="Masukkan username"
                value="{{ old('username') }}"
                required
                autocomplete="username"
            >
        </div>

        <div class="form-group">
            <label for="contact_number" class="form-label">Contact Number</label>
            <input
                type="tel"
                id="contact_number"
                name="contact_number"
                class="form-input @error('contact_number') form-input--error @enderror"
                placeholder="Contoh: 08123456789"
                value="{{ old('contact_number') }}"
                autocomplete="tel"
            >
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="password-input-wrapper">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input @error('password') form-input--error @enderror"
                    placeholder="Minimal 8 karakter (huruf & angka)"
                    required
                    autocomplete="new-password"
                >
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" title="Lihat password" tabindex="-1">
                    <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg class="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                        <line x1="2" y1="2" x2="22" y2="22"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="password-input-wrapper">
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-input"
                    placeholder="Ulangi password"
                    required
                    autocomplete="new-password"
                >
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password_confirmation', this)" title="Lihat password" tabindex="-1">
                    <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg class="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                        <line x1="2" y1="2" x2="22" y2="22"/>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-auth" id="register-btn">
            Register
        </button>

        <p class="auth-link-text">
            Already have an account?
            <a href="{{ route('login') }}" class="auth-link">Login</a>
        </p>
    </form>
</div>

<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const eyeIcon = btn.querySelector('.eye-icon');
        const eyeOffIcon = btn.querySelector('.eye-off-icon');

        if (input.type === 'password') {
            input.type = 'text';
            if (eyeIcon) eyeIcon.style.display = 'none';
            if (eyeOffIcon) eyeOffIcon.style.display = 'block';
            btn.setAttribute('title', 'Sembunyikan password');
        } else {
            input.type = 'password';
            if (eyeIcon) eyeIcon.style.display = 'block';
            if (eyeOffIcon) eyeOffIcon.style.display = 'none';
            btn.setAttribute('title', 'Lihat password');
        }
    }
</script>
@endsection
