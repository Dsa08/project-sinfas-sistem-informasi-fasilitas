{{--
  HALAMAN TANTANGAN DUA FAKTOR (2FA CHALLENGE) — SINFAS
  File: resources/views/auth/two-factor-challenge.blade.php
  Fitur:
  - Verifikasi token 6 digit dari Google Authenticator / Microsoft Authenticator.
  - Opsi fallback menggunakan kode darurat (Recovery Code).
  - Tampilan visual modern, responsif, dan ramah pengguna.
--}}
@extends('layouts.auth')

@section('title', 'Verifikasi Dua Faktor - SINFAS')

@section('content')
<div class="auth-card auth-card--2fa">
    {{-- Header Section --}}
    <div class="auth-logo-section">
        <div style="width: 56px; height: 56px; border-radius: 14px; background: #EFF6FF; color: #1D67F2; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
        </div>
        <h1 class="auth-brand" style="font-size: 1.35rem; margin-bottom: 0.35rem;">Verifikasi Dua Faktor</h1>
        <p class="auth-subtitle" style="font-size: 0.85rem; line-height: 1.45; color: #64748b; margin: 0 auto; max-width: 320px;">
            Akun <strong>{{ $user->nama }}</strong> ({{ $user->role_label }}) dilindungi oleh keamanan 2FA. Masukkan kode verifikasi untuk melanjutkan.
        </p>
    </div>

    {{-- Error Alerts --}}
    @if($errors->any())
        <div class="auth-alert auth-alert--danger" style="margin-top: 1rem;">
            <ul class="auth-alert-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 2FA Form --}}
    <form action="{{ route('login.2fa.post') }}" method="POST" class="auth-form" id="form-2fa" style="margin-top: 1.25rem;">
        @csrf

        {{-- Opsi 1: Mode OTP 6-Digit (Default) --}}
        <div id="section-otp-code">
            <div class="form-group">
                <label for="input_2fa_code" class="form-label" style="text-align: center; display: block; font-size: 0.85rem; color: #475569;">
                    Kode 6 Digit dari Aplikasi Authenticator
                </label>
                <input
                    type="text"
                    id="input_2fa_code"
                    name="code"
                    class="form-input @error('code') form-input--error @enderror"
                    placeholder="000000"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="one-time-code"
                    autofocus
                    style="text-align: center; font-size: 1.5rem; letter-spacing: 0.35em; font-family: monospace; font-weight: 700; height: 52px;"
                >
            </div>
        </div>

        {{-- Opsi 2: Mode Kode Pemulihan Darurat (Hidden by default) --}}
        <div id="section-recovery-code" style="display: none;">
            <div class="form-group">
                <label for="input_recovery_code" class="form-label" style="text-align: center; display: block; font-size: 0.85rem; color: #475569;">
                    Kode Pemulihan Darurat (Recovery Code)
                </label>
                <input
                    type="text"
                    id="input_recovery_code"
                    name="recovery_code"
                    class="form-input @error('recovery_code') form-input--error @enderror"
                    placeholder="ABCD-1234"
                    maxlength="15"
                    autocomplete="off"
                    style="text-align: center; font-size: 1.15rem; letter-spacing: 0.15em; font-family: monospace; font-weight: 600; text-transform: uppercase;"
                >
                <small style="display: block; text-align: center; color: #64748b; font-size: 0.76rem; margin-top: 0.35rem;">
                    Gunakan salah satu dari 8 kode cadangan yang Anda simpan saat mengaktifkan 2FA.
                </small>
            </div>
        </div>

        <button type="submit" class="btn-auth" id="btn-submit-2fa" style="margin-top: 0.5rem;">
            Verifikasi & Masuk
        </button>

        {{-- Switch Mode Link --}}
        <div style="text-align: center; margin-top: 1rem;">
            <button
                type="button"
                id="btn-toggle-recovery"
                onclick="toggleRecoveryMode()"
                style="background: none; border: none; color: #1D67F2; font-size: 0.83rem; font-weight: 500; cursor: pointer; text-decoration: underline;"
            >
                Kehilangan akses authenticator? Masuk dengan kode pemulihan
            </button>
        </div>

        {{-- Back to Login Link --}}
        <div style="text-align: center; margin-top: 0.85rem; padding-top: 0.85rem; border-top: 1px solid #f1f5f9;">
            <a href="{{ route('login') }}" class="auth-link" style="font-size: 0.82rem; color: #64748b;">
                &larr; Batalkan dan kembali ke halaman login
            </a>
        </div>
    </form>
</div>

<script>
    let isRecoveryMode = false;

    function toggleRecoveryMode() {
        isRecoveryMode = !isRecoveryMode;
        const otpSection = document.getElementById('section-otp-code');
        const recoverySection = document.getElementById('section-recovery-code');
        const toggleBtn = document.getElementById('btn-toggle-recovery');
        const otpInput = document.getElementById('input_2fa_code');
        const recoveryInput = document.getElementById('input_recovery_code');

        if (isRecoveryMode) {
            otpSection.style.display = 'none';
            recoverySection.style.display = 'block';
            otpInput.value = '';
            recoveryInput.focus();
            toggleBtn.textContent = 'Gunakan kode 6-digit dari aplikasi Authenticator';
        } else {
            recoverySection.style.display = 'none';
            otpSection.style.display = 'block';
            recoveryInput.value = '';
            otpInput.focus();
            toggleBtn.textContent = 'Kehilangan akses authenticator? Masuk dengan kode pemulihan';
        }
    }
</script>
@endsection
