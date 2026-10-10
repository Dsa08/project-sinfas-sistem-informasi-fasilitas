@extends('layouts.auth')

@section('title', 'Ganti Kata Sandi Sementara - SINFAS')

@section('content')
<div class="auth-card auth-card--login">
    <div class="auth-logo-section">
        <h1 class="auth-brand">Ganti Kata Sandi</h1>
        <p class="auth-subtitle">Kata sandi sementara harus diganti sebelum menggunakan SINFAS.</p>
    </div>

    @if($errors->any())
        <div class="auth-alert auth-alert--danger">
            <ul class="auth-alert-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('password.change.update') }}" method="POST" class="auth-form">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="current_password" class="form-label">Kata sandi sementara</label>
            <input id="current_password" name="current_password" type="password" class="form-input" autocomplete="current-password" required autofocus>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">Kata sandi baru</label>
            <input id="password" name="password" type="password" class="form-input" autocomplete="new-password" minlength="8" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Ulangi kata sandi baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-input" autocomplete="new-password" minlength="8" required>
        </div>

        <button type="submit" class="btn-auth">Simpan Kata Sandi Baru</button>
    </form>
</div>
@endsection
