{{-- 
  HALAMAN LUPA PASSWORD (PERMOHONAN RESET KATA SANDI) — SINFAS
  File: resources/views/auth/forgot-password.blade.php
  Fitur:
  - Mengirimkan tautan reset kata sandi melalui surel pengguna.
  - Mendukung input Email, Username, atau NIS/NIP.
  - Tidak menampilkan token atau tautan reset pada halaman; pemulihan hanya lewat email terdaftar.
--}}
@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi - SINFAS')

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
        <p class="auth-subtitle">Atur Ulang Kata Sandi Akun Anda</p>
    </div>

    {{-- Status Alerts --}}
    @if(session('status'))
        <div class="auth-alert auth-alert--success" style="background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.88rem; line-height: 1.4;">
            <div style="display: flex; align-items: flex-start; gap: 0.5rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-top: 0.15rem; flex-shrink: 0;"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
                <div>
                    {{ session('status') }}
                </div>
            </div>
        </div>
    @endif

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
        Masukkan <strong>Email</strong>, <strong>Username</strong>, atau <strong>NIS / NIP</strong>. Tautan pemulihan hanya dapat dikirim ke email yang terdaftar pada akun.
    </p>

    {{-- Forgot Password Form --}}
    <form action="{{ route('password.email') }}" method="POST" class="auth-form" id="forgot-password-form">
        @csrf

        <div class="form-group">
            <label for="forgot_email" class="form-label">Email / Username / NIS / NIP</label>
            <input
                type="text"
                id="forgot_email"
                name="email"
                class="form-input @error('email') form-input--error @enderror"
                placeholder="Contoh: ahmad@gmail.com / siti / 10223001"
                value="{{ old('email') }}"
                required
                autofocus
            >
        </div>

        <button type="submit" class="btn-auth" id="btn-submit-forgot" style="margin-top: 0.5rem;">
            Kirim Tautan Atur Ulang Kata Sandi
        </button>

        <p class="auth-link-text" style="margin-top: 1.25rem;">
            Sudah ingat kata sandi Anda?
            <a href="{{ route('login') }}" class="auth-link">Kembali untuk Masuk</a>
        </p>
    </form>
</div>
@endsection
