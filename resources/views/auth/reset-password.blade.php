{{-- 
  HALAMAN RESET PASSWORD (PEMBUATAN KATA SANDI BARU) — SINFAS
  File: resources/views/auth/reset-password.blade.php
  Fitur:
  - Validasi token kriptografis 64-karakter dari parameter URL.
  - Form pembaharuan kata sandi dengan verifikasi konfirmasi sandi.
  - Memastikan persyaratan kata sandi aman (minimal 8 karakter kombinasi huruf & angka).
--}}
@extends('layouts.auth')

@section('title', 'Reset Password - SINFAS')

@section('content')
<div class="auth-card auth-card--login">
    {{-- Logo Section --}}
    <div class="auth-logo-section">
        <div class="auth-logo-wrapper">
            <div class="auth-logo-placeholder">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="40" height="40" rx="8" fill="#1E40AF" fill-opacity="0.1"/>
                    <path d="M12 28V12h4l4 10 4-10h4v16h-3V17l-3.5 9h-3L15 17v11h-3z" fill="#1E40AF"/>
                </svg>
            </div>
            <h1 class="auth-brand">SINFAS</h1>
        </div>
        <p class="auth-subtitle">Buat Password Baru</p>
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

    <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 1.25rem; line-height: 1.45;">
        Masukkan password baru untuk akun <strong>{{ $email }}</strong>. Minimal 8 karakter, mengandung kombinasi huruf dan angka.
    </p>

    {{-- Reset Password Form --}}
    <form action="{{ route('password.update') }}" method="POST" class="auth-form" id="reset-password-form">
        @csrf

        {{-- Hidden Token & Email --}}
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        {{-- New Password --}}
        <div class="form-group">
            <label for="new_password" class="form-label">Password Baru</label>
            <div class="password-input-wrapper">
                <input
                    type="password"
                    id="new_password"
                    name="password"
                    class="form-input @error('password') form-input--error @enderror"
                    placeholder="Minimal 8 karakter (huruf & angka)"
                    required
                    autocomplete="new-password"
                    autofocus
                >
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('new_password', this)" title="Lihat password" tabindex="-1">
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

        {{-- Confirm Password --}}
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <div class="password-input-wrapper">
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-input"
                    placeholder="Ulangi password baru"
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

        <button type="submit" class="btn-auth" id="btn-submit-reset" style="margin-top: 0.5rem;">
            Perbarui Password
        </button>

        <p class="auth-link-text" style="margin-top: 1.25rem;">
            Batal dan kembali?
            <a href="{{ route('login') }}" class="auth-link">Kembali ke Login</a>
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
