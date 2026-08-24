@extends('layouts.auth')

@section('title', 'Login - SINFAS')

@section('content')
<div class="auth-card auth-card--login">
    {{-- Logo Section --}}
    <div class="auth-logo-section">
        <div class="auth-logo-wrapper">
            {{-- Logo Placeholder - Ganti dengan logo nanti --}}
            {{-- Contoh: <img src="{{ asset('assets/logo-sinfas.png') }}" alt="SINFAS Logo" class="auth-logo-img"> --}}
            <div class="auth-logo-placeholder">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="40" height="40" rx="8" fill="#B91C1C" fill-opacity="0.1"/>
                    <path d="M12 28V12h4l4 10 4-10h4v16h-3V17l-3.5 9h-3L15 17v11h-3z" fill="#B91C1C"/>
                </svg>
            </div>
            <h1 class="auth-brand">SINFAS</h1>
        </div>
        <p class="auth-subtitle">Sistem Informasi Fasilitas</p>
    </div>

    {{-- Login Form --}}
    <form action="#" method="POST" class="auth-form" id="login-form">
        @csrf

        <div class="form-group">
            <label for="login_email" class="form-label">Username / Email</label>
            <input
                type="text"
                id="login_email"
                name="email"
                class="form-input"
                placeholder=""
                required
                autocomplete="email"
            >
        </div>

        <div class="form-group">
            <label for="login_password" class="form-label">Password</label>
            <input
                type="password"
                id="login_password"
                name="password"
                class="form-input"
                placeholder=""
                required
                autocomplete="current-password"
            >
            <div class="form-forgot">
                <a href="#" class="auth-link" id="forgot-password-link">Forgot password?</a>
            </div>
        </div>

        <button type="submit" class="btn-auth" id="login-btn">
            Login
        </button>

        <p class="auth-link-text">
            Don't have an account?
            <a href="{{ route('register') }}" class="auth-link">Register</a>
        </p>
    </form>
</div>
@endsection
