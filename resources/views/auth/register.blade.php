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
                placeholder=""
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
                placeholder=""
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
                placeholder=""
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
            <input
                type="password"
                id="password"
                name="password"
                class="form-input @error('password') form-input--error @enderror"
                placeholder="Minimal 8 karakter (huruf & angka)"
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
