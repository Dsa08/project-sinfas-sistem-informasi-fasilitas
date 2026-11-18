{{-- 
  HALAMAN PROFIL ADMIN SISTEM — SINFAS
  File: resources/views/admin/system/profile.blade.php
  Fungsi: Pengaturan biodata profil, foto avatar, dan ganti kata sandi mandiri Admin Sistem.
--}}
@extends('layouts.admin-system')

@section('title', 'Profil Saya - Admin Sistem SINFAS')
@section('page_title', 'Profil Saya')

@section('content')
<div class="system-settings-container" style="max-width: 960px;">

    {{-- Breadcrumb --}}
    <nav style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; color: #64748b; margin-bottom: 1rem;" aria-label="Breadcrumb">
        <a href="{{ route('admin.sistem.dashboard') }}" style="color: #2563eb; text-decoration: none; font-weight: 500;">Beranda</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5">
            <polyline points="9 18 15 12 9 6"/>
        </svg>
        <span style="color: #0f172a; font-weight: 600;">Profil Saya</span>
    </nav>

    <div class="system-section-header" style="margin-bottom: 1.5rem;">
        <h2 class="system-section-heading">Profil Admin Sistem</h2>
        <p style="font-size: 0.88rem; color: #64748b; margin: 0.25rem 0 0;">Kelola identitas akun administrator sistem, foto profil, dan kata sandi akses Anda.</p>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div id="profile-success-alert" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.88rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.86rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Layout Grid: Tab Navigation + Main Panel --}}
    <div style="display: grid; grid-template-columns: 220px 1fr; gap: 1.5rem; align-items: start;" class="sys-profile-grid">

        {{-- Sidebar Tab Navigation --}}
        <aside class="system-card" style="padding: 0.75rem;">
            <nav style="display: flex; flex-direction: column; gap: 0.35rem;">
                <a href="{{ route('admin.sistem.profile', ['tab' => 'profile']) }}"
                   id="tab-profile"
                   style="display: flex; align-items: center; gap: 0.65rem; padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: all 0.15s; {{ $activeTab === 'profile' ? 'background: #eff6ff; color: #1d4ed8;' : 'color: #475569;' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Pengaturan Profil</span>
                </a>

                <a href="{{ route('admin.sistem.profile', ['tab' => 'password']) }}"
                   id="tab-password"
                   style="display: flex; align-items: center; gap: 0.65rem; padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: all 0.15s; {{ $activeTab === 'password' ? 'background: #eff6ff; color: #1d4ed8;' : 'color: #475569;' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <span>Kata Sandi</span>
                </a>
            </nav>
        </aside>

        {{-- Content Panel --}}
        <div>
            {{-- ===== TAB 1: PENGATURAN PROFIL ===== --}}
            @if($activeTab === 'profile')
            <div class="system-card" id="panel-profile">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                    <div>
                        <h3 class="system-card-title" style="margin: 0;">Identitas &amp; Biodata Akun</h3>
                        <p style="font-size: 0.82rem; color: #64748b; margin: 0.2rem 0 0;">Perbarui informasi profil administrator sistem.</p>
                    </div>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #1d4ed8; background: #eff6ff; padding: 0.25rem 0.7rem; border-radius: 9999px;">
                        Role: Admin Sistem
                    </span>
                </div>

                {{-- Foto Avatar --}}
                <div style="display: flex; align-items: center; gap: 1.25rem; margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; flex-wrap: wrap;">
                    <div style="width: 72px; height: 72px; border-radius: 50%; background: #e2e8f0; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        @if($user->foto_url)
                            <img src="{{ $user->foto_url }}" alt="{{ $user->nama }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="#64748b">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        @endif
                    </div>

                    <div style="flex: 1; min-width: 200px;">
                        <div style="font-weight: 600; font-size: 0.92rem; color: #0f172a; margin-bottom: 0.25rem;">Foto Profil</div>
                        <div style="font-size: 0.78rem; color: #64748b; margin-bottom: 0.65rem;">Format JPG, PNG, atau WebP maksimal 2MB.</div>

                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <form action="{{ route('admin.sistem.profile.photo.update') }}" method="POST" enctype="multipart/form-data" id="photo-upload-form">
                                @csrf
                                @method('PUT')
                                <input type="file" name="foto" id="foto-input" accept="image/*" style="display: none;" onchange="document.getElementById('photo-upload-form').submit();">
                                <button type="button" onclick="document.getElementById('foto-input').click();" style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155; font-size: 0.8rem; font-weight: 600; padding: 0.4rem 0.85rem; border-radius: 6px; cursor: pointer;">
                                    Unggah Foto
                                </button>
                            </form>

                            @if($user->foto)
                                <form action="{{ route('admin.sistem.profile.photo.delete') }}" method="POST" onsubmit="return confirmAppForm(this, 'Apakah Anda yakin ingin menghapus foto profil ini?', {title: 'Hapus Foto Profil', confirmText: 'Hapus', danger: true}, event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 0.8rem; font-weight: 600; padding: 0.4rem 0.85rem; border-radius: 6px; cursor: pointer;">
                                        Hapus Foto
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Form Data Profil --}}
                <form action="{{ route('admin.sistem.profile.update') }}" method="POST" id="sys-profile-edit-form">
                    @csrf
                    @method('PUT')

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;" class="sys-form-grid">
                        <div>
                            <label for="nama" style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Nama Lengkap <span style="color: #dc2626;">*</span></label>
                            <input type="text" name="nama" id="nama" value="{{ old('nama', $user->nama) }}" required style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; box-sizing: border-box;">
                        </div>

                        <div>
                            <label for="username" style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Username</label>
                            <input type="text" id="username" value="{{ $user->username }}" readonly style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; border-radius: 8px; font-size: 0.88rem; box-sizing: border-box;">
                        </div>

                        <div>
                            <label for="nomor_kontak" style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Nomor Kontak</label>
                            <input type="text" name="nomor_kontak" id="nomor_kontak" value="{{ old('nomor_kontak', $user->nomor_kontak) }}" placeholder="Contoh: 08123456789" style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; box-sizing: border-box;">
                        </div>

                        <div>
                            <label for="email" style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" placeholder="admin@sinfas.sch.id" style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                        <button type="submit" id="btn-save-sys-profile" style="background: #2563eb; color: #ffffff; border: none; padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer;">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- ===== TAB 2: KATA SANDI ===== --}}
            @if($activeTab === 'password')
            <div class="system-card" id="panel-password">
                <div style="margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                    <h3 class="system-card-title" style="margin: 0;">Ubah Kata Sandi</h3>
                    <p style="font-size: 0.82rem; color: #64748b; margin: 0.2rem 0 0;">Gunakan minimal 8 karakter dengan kombinasi huruf dan angka.</p>
                </div>

                <form action="{{ route('admin.sistem.profile.password') }}" method="POST" id="sys-password-form">
                    @csrf
                    @method('PUT')

                    <div style="display: flex; flex-direction: column; gap: 1rem; max-width: 460px;">
                        <div>
                            <label for="current_password" style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Kata Sandi Saat Ini <span style="color: #dc2626;">*</span></label>
                            <input type="password" name="current_password" id="current_password" required style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; box-sizing: border-box;">
                        </div>

                        <div>
                            <label for="new_password" style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Kata Sandi Baru <span style="color: #dc2626;">*</span></label>
                            <input type="password" name="new_password" id="new_password" required style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; box-sizing: border-box;">
                        </div>

                        <div>
                            <label for="new_password_confirmation" style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Konfirmasi Kata Sandi Baru <span style="color: #dc2626;">*</span></label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" required style="width: 100%; padding: 0.55rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem;">
                        <button type="submit" id="btn-save-sys-password" style="background: #2563eb; color: #ffffff; border: none; padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer;">
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .sys-profile-grid,
        .sys-form-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection
