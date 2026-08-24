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
                    <rect width="40" height="40" rx="8" fill="#B91C1C" fill-opacity="0.1"/>
                    <path d="M12 28V12h4l4 10 4-10h4v16h-3V17l-3.5 9h-3L15 17v11h-3z" fill="#B91C1C"/>
                </svg>
            </div>
            <h1 class="auth-brand">SINFAS</h1>
        </div>
        <p class="auth-subtitle">Sistem Informasi Fasilitas</p>
    </div>

    {{-- Register Form --}}
    <form action="#" method="POST" class="auth-form" id="register-form">
        @csrf

        <div class="form-group">
            <label for="full_name" class="form-label">Full Name</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                class="form-input"
                placeholder=""
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
                class="form-input"
                placeholder=""
                required
            >
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-input"
                placeholder=""
                required
                autocomplete="email"
            >
        </div>

        <div class="form-group">
            <label for="contact_number" class="form-label">Contact Number</label>
            <input
                type="tel"
                id="contact_number"
                name="contact_number"
                class="form-input"
                placeholder=""
                required
                autocomplete="tel"
            >
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-input"
                placeholder=""
                required
                autocomplete="new-password"
            >
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="form-input"
                placeholder=""
                required
                autocomplete="new-password"
            >
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
@endsection
