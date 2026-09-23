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

        {{-- Error Message for Photo --}}
        @if(isset($errors) && $errors->has('foto'))
            <div class="profile-alert" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.88rem;">
                {{ $errors->first('foto') }}
            </div>
        @endif

        {{-- Avatar Section --}}
        <div class="profile-avatar-section">
            <div class="profile-avatar" id="profile-avatar" onclick="document.getElementById('modal-photo-input').click()" style="cursor: pointer;" title="Klik untuk ubah foto">
                @if($user->foto_url)
                    <img src="{{ $user->foto_url }}" alt="{{ $user->nama }}" id="current-avatar-img" onerror="this.style.display='none'; document.getElementById('current-avatar-svg-fallback').style.display='block';">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" id="current-avatar-svg-fallback" style="display: none;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                @else
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" id="current-avatar-svg">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                @endif
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.25rem;">
                <button type="button" class="profile-avatar-link" id="change-photo-btn" onclick="document.getElementById('modal-photo-input').click()" style="background: none; border: none; padding: 0; font-family: inherit; font-size: 0.85rem; font-weight: 600; color: #1D67F2; cursor: pointer;">
                    Ubah Foto
                </button>

                @if($user->foto)
                    <span style="color: #d1d5db; font-size: 0.8rem;">|</span>
                    <form action="{{ route('profile.photo.delete') }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto profil ini dan kembali ke avatar default?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="profile-avatar-link" style="background: none; border: none; padding: 0; color: #ef4444; font-family: inherit; font-size: 0.85rem; cursor: pointer;">
                            Hapus Foto
                        </button>
                    </form>
                @endif
            </div>
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

{{-- Modal Konfirmasi Ubah Foto Profil --}}
<div class="modal-overlay" id="photoModal">
    <div class="modal-card" style="max-width: 420px; width: 92%; text-align: center; padding: 1.75rem; border-radius: 16px; background: #ffffff;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 0.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #111827; margin: 0;">Ubah Foto Profil</h3>
            <button type="button" onclick="closePhotoModal()" style="background: transparent; border: none; color: #9ca3af; font-size: 1.5rem; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data" id="photoUploadForm">
            @csrf
            @method('PUT')

            {{-- Hidden File Input inside Form --}}
            <input
                type="file"
                id="modal-photo-input"
                name="foto"
                accept="image/jpeg,image/png,image/jpg,image/webp"
                style="display: none;"
                onchange="handleProfilePhotoSelected(this)"
            >

            {{-- Circular Image Preview --}}
            <div style="display: flex; justify-content: center; margin: 1rem 0 0.85rem;">
                <div style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 3px solid #1D67F2; background: #f8fafc; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(29, 103, 242, 0.18);">
                    <img id="modalPhotoPreview" src="" alt="Pratinjau Foto" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                    <div id="modalPhotoPlaceholder" style="color: #94a3b8; font-size: 0.82rem; padding: 0.5rem;">
                        Belum ada foto dipilih
                    </div>
                </div>
            </div>

            <p id="modalPhotoDetails" style="font-size: 0.82rem; color: #374151; margin: 0.25rem 0 0.5rem; font-weight: 600;"></p>

            <button type="button" onclick="document.getElementById('modal-photo-input').click()" style="background: none; border: none; color: #1D67F2; font-size: 0.82rem; font-weight: 600; cursor: pointer; text-decoration: underline; margin-bottom: 1rem;">
                Pilih Berkas Lain
            </button>

            <p style="font-size: 0.76rem; color: #6b7280; margin: 0 0 1.25rem; background: #f9fafb; padding: 0.5rem; border-radius: 8px;">
                Mendukung format JPG, PNG, atau WEBP (Maks. 2MB).
            </p>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; border-top: 1px solid #f3f4f6; padding-top: 1.25rem;">
                <button type="button" onclick="closePhotoModal()" style="padding: 0.55rem 1.1rem; border-radius: 8px; border: 1px solid #d1d5db; background: #ffffff; color: #374151; font-weight: 600; font-size: 0.88rem; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" id="btnSubmitPhoto" style="padding: 0.55rem 1.35rem; border-radius: 8px; border: none; background: #1D67F2; color: #ffffff; font-weight: 600; font-size: 0.88rem; cursor: pointer;">
                    Simpan Foto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function handleProfilePhotoSelected(input) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];

        // Validasi ukuran client-side (maks 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran berkas melebihi batas maksimal 2MB.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('modalPhotoPreview');
            const placeholder = document.getElementById('modalPhotoPlaceholder');
            const details = document.getElementById('modalPhotoDetails');

            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            if (placeholder) placeholder.style.display = 'none';

            const sizeInKb = (file.size / 1024).toFixed(1);
            if (details) {
                details.textContent = `${file.name} (${sizeInKb} KB)`;
            }

            document.getElementById('photoModal').classList.add('modal-overlay--active');
        };
        reader.readAsDataURL(file);
    }

    function closePhotoModal() {
        document.getElementById('photoModal').classList.remove('modal-overlay--active');
        document.getElementById('modal-photo-input').value = '';
    }

    // Dismiss alert otomatis
    const alertBox = document.getElementById('profile-success-alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.3s ease';
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 300);
        }, 4000);
    }
</script>
@endsection
