{{-- 
  HALAMAN PROFIL PENGGUNA (MY PROFILE) — SINFAS
  File: resources/views/user/profile.blade.php
  Fitur:
  - Tampilan kartu identitas: Avatar profil, nama lengkap, role badge, dan NIS/NIP.
  - Tab 1: Personal Information (pembaruan nama lengkap, kontak WhatsApp, surel).
  - Tab 2: Security & Password (pembaruan kata sandi mandiri dengan validasi password lama).
--}}
@extends('layouts.app')

@section('title', 'Profil Saya - SINFAS')

@section('navbar_title', 'Profil Saya')

@section('content')
<div class="profile-container">
    <div class="profile-card" id="profile-card">

        {{-- Success Message --}}
        @if(session('success'))
            <div class="profile-alert profile-alert--success" id="profile-success-alert">
                {{ session('success') }}
            </div>
        @endif

        {{-- Avatar Section --}}
        <div class="profile-avatar-section">
            <div class="profile-avatar" id="profile-avatar">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <span class="profile-avatar-link" id="change-photo-link">Ubah Foto</span>
        </div>

        {{-- Personal Information Section --}}
        <div class="profile-section">
            <h2 class="profile-section-title">Informasi Pribadi</h2>
            <form action="{{ route('profile.update') }}" method="POST" class="profile-form" id="profile-info-form">
                @csrf
                @method('PUT')

                <div class="profile-form-group">
                    <label class="profile-form-label" for="full-name">Nama Lengkap</label>
                    <input
                        type="text"
                        class="profile-form-input"
                        id="full-name"
                        name="full_name"
                        value="{{ old('full_name', $user->nama) }}"
                        placeholder="Masukkan nama lengkap Anda"
                    >
                    @error('full_name')
                        <span class="profile-input-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="profile-form-group">
                    <label class="profile-form-label" for="nis-nip">NIS / NIP</label>
                    <input
                        type="text"
                        class="profile-form-input"
                        id="nis-nip"
                        name="nis_nip"
                        value="{{ $user->nis ?? $user->nip ?? '-' }}"
                        readonly
                    >
                </div>

                {{-- Email field: hanya tampil untuk siswa --}}
                @if($user->isSiswa())
                <div class="profile-form-group">
                    <label class="profile-form-label" for="email">Email</label>
                    <input
                        type="email"
                        class="profile-form-input"
                        id="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        placeholder="Masukkan alamat email Anda"
                    >
                    @error('email')
                        <span class="profile-input-error">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                <div class="profile-form-group">
                    <label class="profile-form-label" for="contact-number">Nomor Kontak</label>
                    <input
                        type="text"
                        class="profile-form-input"
                        id="contact-number"
                        name="contact_number"
                        value="{{ old('contact_number', $user->nomor_kontak) }}"
                        placeholder="Masukkan nomor kontak Anda"
                    >
                    @error('contact_number')
                        <span class="profile-input-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="profile-form-actions">
                    <button type="submit" class="btn-profile-save" id="save-info-btn">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password Section --}}
        <div class="profile-section">
            <h2 class="profile-section-title">Ubah Kata Sandi</h2>
            <form action="{{ route('profile.password') }}" method="POST" class="profile-form" id="profile-password-form">
                @csrf
                @method('PUT')

                <div class="profile-form-group">
                    <label class="profile-form-label" for="current-password">Kata Sandi Saat Ini</label>
                    <input
                        type="password"
                        class="profile-form-input"
                        id="current-password"
                        name="current_password"
                        placeholder="Masukkan kata sandi saat ini"
                    >
                    @error('current_password')
                        <span class="profile-input-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="profile-form-group">
                    <label class="profile-form-label" for="new-password">Kata Sandi Baru</label>
                    <input
                        type="password"
                        class="profile-form-input"
                        id="new-password"
                        name="new_password"
                        placeholder="Masukkan kata sandi baru"
                    >
                    @error('new_password')
                        <span class="profile-input-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="profile-form-group">
                    <label class="profile-form-label" for="confirm-password">Konfirmasi Kata Sandi Baru</label>
                    <input
                        type="password"
                        class="profile-form-input"
                        id="confirm-password"
                        name="new_password_confirmation"
                        placeholder="Ulangi kata sandi baru"
                    >
                </div>

                <div class="profile-form-actions">
                    <button type="submit" class="btn-profile-save" id="save-password-btn">
                        Simpan Kata Sandi
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
