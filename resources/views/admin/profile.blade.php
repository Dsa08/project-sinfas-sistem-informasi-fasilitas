{{--
  HALAMAN PENGATURAN AKUN — ADMIN SARANA SINFAS
  File: resources/views/admin/profile.blade.php
  Fitur Tab:
  - Pengaturan Profil: Edit nama, nomor kontak, email, foto profil
  - Kata Sandi: Ganti password dengan verifikasi password lama
  - Notifikasi: Preferensi notifikasi in-app
  - Verifikasi Akun: Informasi identitas & status akun
  TERPISAH dari halaman profil siswa (user/profile.blade.php)
--}}
@extends('layouts.admin-sarana')

@section('title', 'Pengaturan Akun - Admin Sarana SINFAS')
@section('page_title', 'Pengaturan Akun')

@section('content')
<div class="admin-profile-wrapper">

    {{-- Breadcrumb --}}
    <nav class="admin-profile-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Beranda</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5">
            <polyline points="9 18 15 12 9 6"/>
        </svg>
        <span class="breadcrumb-current">Pengaturan Akun</span>
    </nav>

    <h2 class="admin-profile-heading">Pengaturan Akun</h2>

    {{-- Main Layout: Sidebar Tab + Content Panel --}}
    <div class="admin-profile-layout">

        {{-- =================== SIDEBAR TAB NAVIGATION =================== --}}
        <aside class="admin-profile-sidebar">
            <nav class="admin-profile-tab-nav">
                <a href="{{ route('admin.profile', ['tab' => 'profile']) }}"
                   class="admin-profile-tab {{ $activeTab === 'profile' ? 'admin-profile-tab--active' : '' }}"
                   id="tab-profile">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Pengaturan Profil</span>
                </a>

                <a href="{{ route('admin.profile', ['tab' => 'password']) }}"
                   class="admin-profile-tab {{ $activeTab === 'password' ? 'admin-profile-tab--active' : '' }}"
                   id="tab-password">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <span>Kata Sandi</span>
                </a>

                <a href="{{ route('admin.profile', ['tab' => 'notifications']) }}"
                   class="admin-profile-tab {{ $activeTab === 'notifications' ? 'admin-profile-tab--active' : '' }}"
                   id="tab-notifications">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span>Notifikasi</span>
                </a>

                <a href="{{ route('admin.profile', ['tab' => 'verification']) }}"
                   class="admin-profile-tab {{ $activeTab === 'verification' ? 'admin-profile-tab--active' : '' }}"
                   id="tab-verification">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span>Verifikasi Akun</span>
                </a>
            </nav>
        </aside>

        {{-- =================== CONTENT PANEL =================== --}}
        <div class="admin-profile-content">

            {{-- ===== TAB 1: PENGATURAN PROFIL ===== --}}
            @if($activeTab === 'profile')
            <div class="admin-profile-panel" id="panel-profile">
                <div class="admin-profile-panel-header">
                    <h3 class="admin-profile-panel-title">Pengaturan Profil</h3>
                    <p class="admin-profile-panel-desc">Perbarui informasi identitas dan foto profil Anda.</p>
                </div>

                {{-- Avatar Section --}}
                <div class="admin-profile-avatar-section">
                    <div class="admin-profile-avatar-wrap" id="avatar-preview-wrap">
                        @if($user->foto)
                            <img src="{{ asset('storage/' . $user->foto) }}"
                                 alt="Foto profil {{ $user->nama }}"
                                 class="admin-profile-avatar-img"
                                 id="avatar-preview-img">
                        @else
                            <div class="admin-profile-avatar-placeholder" id="avatar-placeholder">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="#ffffff">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                        @endif
                        {{-- Overlay upload on hover --}}
                        <label for="foto-upload-input" class="admin-avatar-upload-overlay" title="Ganti Foto">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                        </label>
                    </div>
                    <div class="admin-profile-avatar-meta">
                        <span class="admin-profile-nama">{{ $user->nama }}</span>
                        <span class="admin-profile-role-badge">
                            {{ $user->role === 'admin_sarana' ? 'Admin Sarana' : 'Admin Sistem' }}
                        </span>
                        <div class="admin-avatar-btn-group">
                            {{-- Hidden Upload Form --}}
                            <form action="{{ route('admin.profile.photo.update') }}" method="POST" enctype="multipart/form-data" id="photo-upload-form">
                                @csrf
                                @method('PUT')
                                <input type="file" name="foto" id="foto-upload-input"
                                       accept="image/jpeg,image/png,image/webp"
                                       style="display:none;"
                                       onchange="document.getElementById('photo-upload-form').submit()">
                                <label for="foto-upload-input" class="btn-admin-profile-secondary" style="cursor:pointer;" id="btn-change-photo">
                                    Ganti Foto
                                </label>
                            </form>

                            @if($user->foto)
                            <form action="{{ route('admin.profile.photo.delete') }}" method="POST" id="delete-photo-form"
                                  onsubmit="return confirm('Hapus foto profil?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-admin-profile-danger-outline" id="btn-delete-photo">
                                    Hapus Foto
                                </button>
                            </form>
                            @endif
                        </div>
                        <p class="admin-avatar-hint">Format: JPG, PNG, WebP. Maks. 2MB.</p>
                    </div>
                </div>

                <div class="admin-profile-divider"></div>

                {{-- Profile Edit Form --}}
                <form action="{{ route('admin.profile.update') }}" method="POST" class="admin-profile-form" id="profile-edit-form">
                    @csrf
                    @method('PUT')

                    @if(session('success'))
                        <div class="admin-profile-alert admin-profile-alert--success" id="profile-success-alert">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="admin-profile-alert admin-profile-alert--error">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="admin-profile-form-grid">
                        {{-- Nama Lengkap --}}
                        <div class="admin-profile-field">
                            <label class="admin-profile-label" for="nama">Nama Lengkap <span class="field-required">*</span></label>
                            <input type="text"
                                   name="nama"
                                   id="nama"
                                   class="admin-profile-input {{ $errors->has('nama') ? 'input-error' : '' }}"
                                   value="{{ old('nama', $user->nama) }}"
                                   placeholder="Masukkan nama lengkap"
                                   required>
                            @error('nama')
                                <span class="admin-profile-field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- NIP --}}
                        <div class="admin-profile-field">
                            <label class="admin-profile-label" for="nip">NIP</label>
                            <input type="text"
                                   id="nip"
                                   class="admin-profile-input admin-profile-input--readonly"
                                   value="{{ $user->nip ?? '-' }}"
                                   readonly
                                   title="NIP tidak dapat diubah">
                            <span class="admin-profile-field-hint">NIP tidak dapat diubah secara mandiri.</span>
                        </div>

                        {{-- Nomor Kontak --}}
                        <div class="admin-profile-field">
                            <label class="admin-profile-label" for="nomor_kontak">Nomor Kontak</label>
                            <input type="text"
                                   name="nomor_kontak"
                                   id="nomor_kontak"
                                   class="admin-profile-input {{ $errors->has('nomor_kontak') ? 'input-error' : '' }}"
                                   value="{{ old('nomor_kontak', $user->nomor_kontak) }}"
                                   placeholder="Contoh: 08123456789">
                            @error('nomor_kontak')
                                <span class="admin-profile-field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="admin-profile-field">
                            <label class="admin-profile-label" for="email">Email</label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   class="admin-profile-input {{ $errors->has('email') ? 'input-error' : '' }}"
                                   value="{{ old('email', $user->email) }}"
                                   placeholder="Masukkan alamat email">
                            @error('email')
                                <span class="admin-profile-field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Username --}}
                        <div class="admin-profile-field">
                            <label class="admin-profile-label" for="username">Username</label>
                            <input type="text"
                                   id="username"
                                   class="admin-profile-input admin-profile-input--readonly"
                                   value="{{ $user->username }}"
                                   readonly
                                   title="Username tidak dapat diubah">
                            <span class="admin-profile-field-hint">Username tidak dapat diubah.</span>
                        </div>
                    </div>

                    <div class="admin-profile-form-action">
                        <button type="submit" class="btn-admin-profile-primary" id="btn-save-profile">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- ===== TAB 2: KATA SANDI ===== --}}
            @if($activeTab === 'password')
            <div class="admin-profile-panel" id="panel-password">
                <div class="admin-profile-panel-header">
                    <h3 class="admin-profile-panel-title">Ganti Kata Sandi</h3>
                    <p class="admin-profile-panel-desc">Pastikan kata sandi Anda kuat dan minimal terdiri dari 8 karakter.</p>
                </div>

                @if(session('success'))
                    <div class="admin-profile-alert admin-profile-alert--success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="admin-profile-alert admin-profile-alert--error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('admin.profile.password') }}" method="POST" class="admin-profile-form" id="password-change-form">
                    @csrf
                    @method('PUT')

                    {{-- Kata Sandi Saat Ini --}}
                    <div class="admin-profile-field">
                        <label class="admin-profile-label" for="current_password">Kata Sandi Saat Ini <span class="field-required">*</span></label>
                        <div class="admin-profile-password-wrap">
                            <input type="password"
                                   name="current_password"
                                   id="current_password"
                                   class="admin-profile-input {{ $errors->has('current_password') ? 'input-error' : '' }}"
                                   placeholder="Masukkan kata sandi saat ini"
                                   autocomplete="current-password">
                            <button type="button" class="admin-pwd-toggle" onclick="togglePassword('current_password', this)" title="Tampilkan/sembunyikan">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <span class="admin-profile-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="admin-profile-form-grid">
                        {{-- Kata Sandi Baru --}}
                        <div class="admin-profile-field">
                            <label class="admin-profile-label" for="new_password">Kata Sandi Baru <span class="field-required">*</span></label>
                            <div class="admin-profile-password-wrap">
                                <input type="password"
                                       name="new_password"
                                       id="new_password"
                                       class="admin-profile-input {{ $errors->has('new_password') ? 'input-error' : '' }}"
                                       placeholder="Minimal 8 karakter"
                                       autocomplete="new-password"
                                       oninput="checkPasswordStrength(this.value)">
                                <button type="button" class="admin-pwd-toggle" onclick="togglePassword('new_password', this)" title="Tampilkan/sembunyikan">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            @error('new_password')
                                <span class="admin-profile-field-error">{{ $message }}</span>
                            @enderror
                            {{-- Password Strength Indicator --}}
                            <div class="admin-pwd-strength-bar" id="pwd-strength-bar">
                                <div class="admin-pwd-strength-fill" id="pwd-strength-fill"></div>
                            </div>
                            <span class="admin-pwd-strength-label" id="pwd-strength-label"></span>
                        </div>

                        {{-- Konfirmasi Kata Sandi Baru --}}
                        <div class="admin-profile-field">
                            <label class="admin-profile-label" for="new_password_confirmation">Konfirmasi Kata Sandi Baru <span class="field-required">*</span></label>
                            <div class="admin-profile-password-wrap">
                                <input type="password"
                                       name="new_password_confirmation"
                                       id="new_password_confirmation"
                                       class="admin-profile-input"
                                       placeholder="Ulangi kata sandi baru"
                                       autocomplete="new-password">
                                <button type="button" class="admin-pwd-toggle" onclick="togglePassword('new_password_confirmation', this)" title="Tampilkan/sembunyikan">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="admin-profile-form-action">
                        <button type="submit" class="btn-admin-profile-primary" id="btn-save-password">
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- ===== TAB 3: NOTIFIKASI ===== --}}
            @if($activeTab === 'notifications')
            <div class="admin-profile-panel" id="panel-notifications">
                <div class="admin-profile-panel-header">
                    <h3 class="admin-profile-panel-title">Preferensi Notifikasi</h3>
                    <p class="admin-profile-panel-desc">Atur pemberi tahuan peminjaman fasilitas yang ingin Anda terima.</p>
                </div>

                <div class="admin-notif-list">
                    {{-- Toggle: Pengajuan Pinjaman Baru --}}
                    <div class="admin-notif-item">
                        <div class="admin-notif-info">
                            <span class="admin-notif-title">Pengajuan Peminjaman Baru</span>
                            <span class="admin-notif-desc">Terima pemberitahuan saat ada siswa yang mengajukan peminjaman.</span>
                        </div>
                        <label class="admin-notif-toggle" for="notif-borrow">
                            <input type="checkbox" id="notif-borrow" checked>
                            <span class="admin-notif-slider"></span>
                        </label>
                    </div>

                    <div class="admin-notif-divider"></div>

                    {{-- Toggle: Konfirmasi Pengembalian Barang --}}
                    <div class="admin-notif-item">
                        <div class="admin-notif-info">
                            <span class="admin-notif-title">Konfirmasi Pengembalian Barang</span>
                            <span class="admin-notif-desc">Dapatkan notifikasi saat siswa menginggah bukti pengembalian fisik barang.</span>
                        </div>
                        <label class="admin-notif-toggle" for="notif-return">
                            <input type="checkbox" id="notif-return" checked>
                            <span class="admin-notif-slider"></span>
                        </label>
                    </div>

                    <div class="admin-notif-divider"></div>

                    {{-- Toggle: Peringatan Stok Fasilitas Menipis --}}
                    <div class="admin-notif-item">
                        <div class="admin-notif-info">
                            <span class="admin-notif-title">Peringatan Stok Fasilitas Menipis</span>
                            <span class="admin-notif-desc">Peringatan otomatis saat stok barang siap pakai terasa kurang dari 2 unit.</span>
                        </div>
                        <label class="admin-notif-toggle" for="notif-stock">
                            <input type="checkbox" id="notif-stock" checked>
                            <span class="admin-notif-slider"></span>
                        </label>
                    </div>

                    <div class="admin-notif-divider"></div>

                    {{-- Toggle: Laporan Mingguan Sistem --}}
                    <div class="admin-notif-item">
                        <div class="admin-notif-info">
                            <span class="admin-notif-title">Laporan Mingguan Sistem</span>
                            <span class="admin-notif-desc">Ringkasan aktivitas peminjaman dan barang rusak yang perlu perhatian.</span>
                        </div>
                        <label class="admin-notif-toggle" for="notif-weekly">
                            <input type="checkbox" id="notif-weekly">
                            <span class="admin-notif-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="admin-profile-form-action" style="margin-top: 2rem;">
                    <button type="button" class="btn-admin-profile-primary" id="btn-save-notif" onclick="saveNotifPreferences()">
                        Simpan Preferensi Notifikasi
                    </button>
                </div>
            </div>
            @endif

            {{-- ===== TAB 4: VERIFIKASI AKUN ===== --}}
            @if($activeTab === 'verification')
            <div class="admin-profile-panel" id="panel-verification">
                <div class="admin-profile-panel-header">
                    <h3 class="admin-profile-panel-title">Verifikasi Akun</h3>
                    <p class="admin-profile-panel-desc">Informasi identitas resmi dan status akun Anda dalam sistem SINFAS.</p>
                </div>

                <div class="admin-verification-grid">
                    {{-- Status Akun --}}
                    <div class="admin-verification-card">
                        <div class="admin-verification-card-icon admin-verification-card-icon--{{ $user->is_active ? 'success' : 'danger' }}">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                @if($user->is_active)
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                @else
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                @endif
                            </svg>
                        </div>
                        <div class="admin-verification-card-body">
                            <span class="admin-verification-label">Status Akun</span>
                            <span class="admin-verification-value admin-verification-value--{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Aktif & Terverifikasi' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>

                    {{-- Role --}}
                    <div class="admin-verification-card">
                        <div class="admin-verification-card-icon admin-verification-card-icon--info">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div class="admin-verification-card-body">
                            <span class="admin-verification-label">Role / Jabatan</span>
                            <span class="admin-verification-value">
                                {{ $user->role === 'admin_sarana' ? 'Admin Sarana Prasarana' : 'Admin Sistem' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Identity Details --}}
                <div class="admin-verification-detail-card">
                    <h4 class="admin-verification-detail-title">Informasi Identitas Resmi</h4>
                    <div class="admin-verification-detail-grid">
                        <div class="admin-verification-detail-row">
                            <span class="detail-label">Nama Lengkap</span>
                            <span class="detail-value">{{ $user->nama }}</span>
                        </div>
                        <div class="admin-verification-detail-row">
                            <span class="detail-label">NIP</span>
                            <span class="detail-value">{{ $user->nip ?? '-' }}</span>
                        </div>
                        <div class="admin-verification-detail-row">
                            <span class="detail-label">Username</span>
                            <span class="detail-value">{{ $user->username }}</span>
                        </div>
                        <div class="admin-verification-detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-value">{{ $user->email ?? '-' }}</span>
                        </div>
                        <div class="admin-verification-detail-row">
                            <span class="detail-label">Nomor Kontak</span>
                            <span class="detail-value">{{ $user->nomor_kontak ?? '-' }}</span>
                        </div>
                        <div class="admin-verification-detail-row">
                            <span class="detail-label">Akun Dibuat</span>
                            <span class="detail-value">{{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                        <div class="admin-verification-detail-row">
                            <span class="detail-label">Terakhir Diperbarui</span>
                            <span class="detail-value">{{ $user->updated_at ? $user->updated_at->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="admin-verification-info-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1D67F2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>Untuk perubahan data identitas resmi (NIP, username, atau role), silakan hubungi <strong>Admin Sistem</strong> SINFAS.</span>
                </div>
            </div>
            @endif

        </div>{{-- .admin-profile-content --}}
    </div>{{-- .admin-profile-layout --}}
</div>{{-- .admin-profile-wrapper --}}

<style>
/* =====================================================
   ADMIN PROFILE PAGE STYLES — SINFAS
   Scope: Khusus halaman admin/profile.blade.php
   Mengikuti design system warna biru minimalis profesional
   ===================================================== */

/* Wrapper */
.admin-profile-wrapper {
    padding: 0 0 3rem;
    max-width: 1100px;
}

/* Breadcrumb */
.admin-profile-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
    color: #94a3b8;
}
.breadcrumb-link {
    color: #1D67F2;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.15s;
}
.breadcrumb-link:hover {
    color: #1E40AF;
    text-decoration: underline;
}
.breadcrumb-current {
    color: #64748b;
}

/* Heading */
.admin-profile-heading {
    font-size: 1.6rem;
    font-weight: 700;
    color: #0F172A;
    margin: 0 0 1.75rem;
    letter-spacing: -0.02em;
}

/* Layout: sidebar + content */
.admin-profile-layout {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

/* ---- Sidebar ---- */
.admin-profile-sidebar {
    flex-shrink: 0;
    width: 220px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 0.5rem;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    position: sticky;
    top: 1rem;
}
.admin-profile-tab-nav {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.admin-profile-tab {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.7rem 1rem;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 500;
    color: #475569;
    text-decoration: none;
    transition: all 0.15s ease;
}
.admin-profile-tab:hover {
    background: #EFF6FF;
    color: #1D67F2;
}
.admin-profile-tab--active {
    background: #EFF6FF;
    color: #1D67F2;
    font-weight: 600;
    border-left: 3px solid #1D67F2;
    padding-left: calc(1rem - 3px);
}

/* ---- Content Panel ---- */
.admin-profile-content {
    flex: 1;
    min-width: 0;
}
.admin-profile-panel {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 2rem;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.admin-profile-panel-header {
    margin-bottom: 1.5rem;
}
.admin-profile-panel-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0F172A;
    margin: 0 0 0.35rem;
}
.admin-profile-panel-desc {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
}

/* ---- Avatar Section ---- */
.admin-profile-avatar-section {
    display: flex;
    align-items: center;
    gap: 1.75rem;
    margin-bottom: 1.75rem;
}
.admin-profile-avatar-wrap {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    flex-shrink: 0;
    overflow: hidden;
    border: 3px solid #e5e7eb;
    box-shadow: 0 4px 16px rgba(0,0,0,0.10);
    cursor: pointer;
}
.admin-profile-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.admin-profile-avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #60a5fa 0%, #1D67F2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}
.admin-avatar-upload-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.50);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    border-radius: 50%;
    cursor: pointer;
    transition: opacity 0.2s ease;
}
.admin-profile-avatar-wrap:hover .admin-avatar-upload-overlay {
    opacity: 1;
}

.admin-profile-avatar-meta {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.admin-profile-nama {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0F172A;
}
.admin-profile-role-badge {
    display: inline-block;
    background: #EFF6FF;
    color: #1D67F2;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid #bfdbfe;
}
.admin-avatar-btn-group {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-top: 0.4rem;
    flex-wrap: wrap;
}
.admin-avatar-hint {
    font-size: 0.78rem;
    color: #94a3b8;
    margin: 0;
}

/* ---- Form ---- */
.admin-profile-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 1.5rem 0;
}
.admin-profile-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}
.admin-profile-field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.admin-profile-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}
.field-required {
    color: #ef4444;
}
.admin-profile-input {
    width: 100%;
    padding: 0.625rem 0.9rem;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #0F172A;
    background: #ffffff;
    transition: border-color 0.15s, box-shadow 0.15s;
    outline: none;
    box-sizing: border-box;
    font-family: inherit;
}
.admin-profile-input:focus {
    border-color: #1D67F2;
    box-shadow: 0 0 0 3px rgba(29, 103, 242, 0.12);
}
.admin-profile-input.input-error {
    border-color: #ef4444;
}
.admin-profile-input--readonly {
    background: #f8fafc;
    color: #64748b;
    cursor: not-allowed;
}
.admin-profile-field-error {
    font-size: 0.8rem;
    color: #ef4444;
}
.admin-profile-field-hint {
    font-size: 0.78rem;
    color: #94a3b8;
}

/* Password Wrap */
.admin-profile-password-wrap {
    position: relative;
}
.admin-profile-password-wrap .admin-profile-input {
    padding-right: 2.75rem;
}
.admin-pwd-toggle {
    position: absolute;
    right: 0.7rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    padding: 0;
    display: flex;
    align-items: center;
    transition: color 0.15s;
}
.admin-pwd-toggle:hover {
    color: #1D67F2;
}

/* Password Strength */
.admin-pwd-strength-bar {
    height: 5px;
    background: #f1f5f9;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 0.4rem;
}
.admin-pwd-strength-fill {
    height: 100%;
    width: 0%;
    border-radius: 999px;
    transition: width 0.35s ease, background 0.35s ease;
}
.admin-pwd-strength-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748b;
}

/* Alerts */
.admin-profile-alert {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.85rem 1rem;
    border-radius: 10px;
    font-size: 0.875rem;
    margin-bottom: 1.25rem;
    font-weight: 500;
}
.admin-profile-alert--success {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.admin-profile-alert--error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

/* Form Action */
.admin-profile-form-action {
    margin-top: 1.75rem;
    display: flex;
    justify-content: flex-start;
}

/* Buttons */
.btn-admin-profile-primary {
    padding: 0.65rem 1.5rem;
    background: #1D67F2;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
    font-family: inherit;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.btn-admin-profile-primary:hover {
    background: #1E40AF;
    box-shadow: 0 4px 12px rgba(29, 103, 242, 0.3);
}
.btn-admin-profile-primary:active {
    transform: scale(0.98);
}
.btn-admin-profile-secondary {
    padding: 0.5rem 1.1rem;
    background: #f1f5f9;
    color: #374151;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
}
.btn-admin-profile-secondary:hover {
    background: #e2e8f0;
    border-color: #94a3b8;
}
.btn-admin-profile-danger-outline {
    padding: 0.5rem 1.1rem;
    background: transparent;
    color: #ef4444;
    border: 1.5px solid #fca5a5;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
}
.btn-admin-profile-danger-outline:hover {
    background: #fef2f2;
    border-color: #ef4444;
}

/* ---- Notifications Tab ---- */
.admin-notif-list {
    display: flex;
    flex-direction: column;
}
.admin-notif-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.1rem 0;
}
.admin-notif-info {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}
.admin-notif-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
}
.admin-notif-desc {
    font-size: 0.82rem;
    color: #64748b;
}
.admin-notif-divider {
    height: 1px;
    background: #f1f5f9;
}

/* Toggle switch */
.admin-notif-toggle {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 26px;
    flex-shrink: 0;
    cursor: pointer;
}
.admin-notif-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.admin-notif-slider {
    position: absolute;
    inset: 0;
    background: #d1d5db;
    border-radius: 999px;
    transition: background 0.2s ease;
}
.admin-notif-slider::before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    background: #ffffff;
    border-radius: 50%;
    left: 3px;
    top: 3px;
    transition: transform 0.2s ease;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
}
.admin-notif-toggle input:checked + .admin-notif-slider {
    background: #1D67F2;
}
.admin-notif-toggle input:checked + .admin-notif-slider::before {
    transform: translateX(20px);
}

/* ---- Verification Tab ---- */
.admin-verification-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.admin-verification-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
}
.admin-verification-card-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.admin-verification-card-icon--success {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.admin-verification-card-icon--danger {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}
.admin-verification-card-icon--info {
    background: #EFF6FF;
    color: #1D67F2;
    border: 1px solid #bfdbfe;
}
.admin-verification-card-body {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.admin-verification-label {
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 500;
}
.admin-verification-value {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0F172A;
}
.admin-verification-value--success { color: #15803d; }
.admin-verification-value--danger { color: #b91c1c; }

.admin-verification-detail-card {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
}
.admin-verification-detail-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 1rem;
}
.admin-verification-detail-grid {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.admin-verification-detail-row {
    display: flex;
    gap: 1rem;
}
.detail-label {
    width: 180px;
    flex-shrink: 0;
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
}
.detail-value {
    font-size: 0.875rem;
    color: #1e293b;
    font-weight: 600;
}
.admin-verification-info-box {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    background: #EFF6FF;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 0.85rem 1rem;
    font-size: 0.85rem;
    color: #1e40af;
    line-height: 1.5;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-profile-layout {
        flex-direction: column;
    }
    .admin-profile-sidebar {
        width: 100%;
        position: static;
    }
    .admin-profile-tab-nav {
        flex-direction: row;
        flex-wrap: wrap;
    }
    .admin-profile-tab {
        flex: 1;
        min-width: 0;
        justify-content: center;
        font-size: 0.8rem;
    }
    .admin-profile-tab--active {
        border-left: none;
        border-bottom: 3px solid #1D67F2;
        padding-left: 1rem;
        padding-bottom: calc(0.7rem - 3px);
    }
    .admin-profile-form-grid {
        grid-template-columns: 1fr;
    }
    .admin-verification-grid {
        grid-template-columns: 1fr;
    }
    .admin-profile-avatar-section {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss success alert
    const successAlert = document.getElementById('profile-success-alert');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.5s ease';
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.remove(), 500);
        }, 4000);
    }
});

/**
 * Toggle password visibility (show/hide)
 * @param {string} fieldId - ID input password
 * @param {HTMLElement} btn - Tombol toggle
 */
function togglePassword(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;

    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = `
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>`;
    } else {
        input.type = 'password';
        btn.innerHTML = `
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>`;
    }
}

/**
 * Cek kekuatan password dan update strength bar
 * @param {string} value - Nilai password
 */
function checkPasswordStrength(value) {
    const fill  = document.getElementById('pwd-strength-fill');
    const label = document.getElementById('pwd-strength-label');
    if (!fill || !label) return;

    let strength = 0;
    if (value.length >= 8)                    strength++;
    if (/[A-Z]/.test(value))                  strength++;
    if (/[0-9]/.test(value))                  strength++;
    if (/[^A-Za-z0-9]/.test(value))           strength++;

    const levels = [
        { pct: '0%',   color: '#e5e7eb', text: '' },
        { pct: '25%',  color: '#ef4444', text: 'Lemah' },
        { pct: '50%',  color: '#f59e0b', text: 'Sedang' },
        { pct: '75%',  color: '#3b82f6', text: 'Kuat' },
        { pct: '100%', color: '#10b981', text: 'Sangat Kuat' },
    ];

    const lvl = levels[strength] || levels[0];
    fill.style.width      = lvl.pct;
    fill.style.background = lvl.color;
    label.textContent     = lvl.text;
    label.style.color     = lvl.color;
}

/**
 * Simpan preferensi notifikasi (placeholder — bisa dikembangkan dengan API)
 */
function saveNotifPreferences() {
    const btn = document.getElementById('btn-save-notif');
    if (!btn) return;

    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    setTimeout(() => {
        btn.textContent = 'Simpan Preferensi Notifikasi';
        btn.disabled = false;
        window.showAdminSaranaToast('success', 'Preferensi notifikasi disimpan', 'Pengaturan notifikasi Anda telah diperbarui.');
    }, 800);
}
</script>
@endsection
