@extends('layouts.admin-system')

@section('title', 'Kelola Akun User - Admin Sistem SINFAS')
@section('page_title', 'Kelola Akun')

@section('content')
<div class="system-accounts-container">
    <div class="system-section-header">
        <h2 class="system-section-heading">Akun Pengguna</h2>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="system-alert system-alert--success" id="flash-success">
            <span>{{ session('success') }}</span>
            <button type="button" class="system-alert-close" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif
    @if(session('error'))
        <div class="system-alert system-alert--error" id="flash-error">
            <span>{{ session('error') }}</span>
            <button type="button" class="system-alert-close" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    {{-- Filter & Action Bar --}}
    <div class="system-filter-bar">
        <form method="GET" action="{{ route('admin.sistem.accounts') }}" class="system-search-box" id="search-form">
            <x-heroicon-o-magnifying-glass class="system-search-icon" />
            <input
                type="text"
                class="system-search-input"
                id="search-user-input"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari berdasarkan nama, NIS/NIP, atau email..."
            >
        </form>
        <button type="button" class="btn-add-account" id="btn-add-account">
            + Tambah Akun
        </button>
    </div>

    {{-- Accounts Table Card --}}
    <div class="system-table-card">
        <table class="system-table" id="accounts-table">
            <thead>
                <tr>
                    <th style="width: 35%;">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama', 'dir' => request('sort') === 'nama' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="th-content {{ request('sort') === 'nama' ? 'th-content--active' : '' }}">
                            <span>Nama</span>
                            @if(request('sort') === 'nama')
                                @if(request('dir') === 'desc')
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #1D67F2;"><path d="m6 9 6 6 6-6"/></svg>
                                @else
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #1D67F2;"><path d="m18 15-6-6-6 6"/></svg>
                                @endif
                            @else
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5;"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                            @endif
                        </a>
                    </th>
                    <th style="width: 25%;">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'nis_nip', 'dir' => request('sort') === 'nis_nip' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="th-content {{ request('sort') === 'nis_nip' ? 'th-content--active' : '' }}">
                            <span>NIS / NIP</span>
                            @if(request('sort') === 'nis_nip')
                                @if(request('dir') === 'desc')
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #1D67F2;"><path d="m6 9 6 6 6-6"/></svg>
                                @else
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #1D67F2;"><path d="m18 15-6-6-6 6"/></svg>
                                @endif
                            @else
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5;"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                            @endif
                        </a>
                    </th>
                    <th style="width: 25%;">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'role', 'dir' => request('sort') === 'role' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="th-content {{ request('sort') === 'role' ? 'th-content--active' : '' }}">
                            <span>Peran</span>
                            @if(request('sort') === 'role')
                                @if(request('dir') === 'desc')
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #1D67F2;"><path d="m6 9 6 6 6-6"/></svg>
                                @else
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #1D67F2;"><path d="m18 15-6-6-6 6"/></svg>
                                @endif
                            @else
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5;"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="th-actions" style="width: 15%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accounts as $akun)
                <tr data-id="{{ $akun->id_akun }}">
                    <td class="td-name">
                        {{ $akun->nama }}
                        @if(!$akun->is_active)
                            <span style="color:#ef4444; font-size:0.75rem; font-weight:600;">(Nonaktif)</span>
                        @endif
                    </td>
                    <td class="td-nis-nip">{{ $akun->nis_nip }}</td>
                    <td class="td-role">{{ $akun->role_label }}</td>
                    <td class="td-actions">
                        <div class="action-btn-group">
                            {{-- Edit Button --}}
                            <button type="button" class="table-action-icon-btn btn-edit-account"
                                title="Edit Akun"
                                data-id="{{ $akun->id_akun }}"
                                data-nama="{{ $akun->nama }}"
                                data-nisnip="{{ $akun->nis_nip }}"
                                data-role="{{ $akun->role }}"
                                data-kontak="{{ $akun->nomor_kontak }}"
                                data-username="{{ $akun->username }}"
                                data-email="{{ $akun->email }}"
                                data-foto="{{ $akun->foto ? asset('storage/' . $akun->foto) : '' }}"
                            >
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                            </button>
                            {{-- View Detail Button --}}
                            <button type="button" class="table-action-icon-btn btn-view-account"
                                title="Lihat Detail"
                                data-id="{{ $akun->id_akun }}"
                            >
                                <x-heroicon-o-eye class="w-4 h-4" />
                            </button>
                            {{-- Dropdown Menu --}}
                            <div class="action-dropdown-wrapper">
                                <button type="button" class="table-action-icon-btn action-menu-trigger" title="Menu Lainnya">
                                    <x-heroicon-o-bars-3 class="w-4 h-4" />
                                </button>
                                <div class="action-dropdown-menu">
                                    {{-- Reset Password --}}
                                    <form method="POST" action="{{ route('admin.sistem.accounts.reset', $akun->id_akun) }}" class="inline-form">
                                        @csrf
                                        <button type="submit" class="action-dropdown-item btn-action-reset" onclick="return confirm('Reset password {{ $akun->nama }} ke default?')">
                                            <x-heroicon-o-key class="w-4 h-4" />
                                            <span>Reset Password</span>
                                        </button>
                                    </form>
                                    {{-- Deactivate/Activate Account --}}
                                    <form method="POST" action="{{ route('admin.sistem.accounts.destroy', $akun->id_akun) }}" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-dropdown-item action-dropdown-item--danger btn-action-deactivate"
                                            onclick="return confirm('{{ $akun->is_active ? 'Nonaktifkan' : 'Aktifkan kembali' }} akun {{ $akun->nama }}?')">
                                            <x-heroicon-o-user-minus class="w-4 h-4" />
                                            <span>{{ $akun->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 2.5rem 1rem; color: #6b7280;">
                        <x-heroicon-o-users class="w-10 h-10" style="margin: 0 auto 0.75rem; display: block; color: #cbd5e1;" />
                        @if(request('search'))
                            Tidak ada akun ditemukan untuk pencarian "<strong>{{ request('search') }}</strong>".
                        @else
                            Belum ada data akun.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($accounts->hasPages())
    <div class="system-pagination-bar">
        {{-- Previous --}}
        @if($accounts->onFirstPage())
            <button class="pagination-btn pagination-btn--disabled" disabled>Sebelumnya</button>
        @else
            <a href="{{ $accounts->previousPageUrl() }}" class="pagination-btn">Sebelumnya</a>
        @endif

        {{-- Page Numbers --}}
        @foreach($accounts->getUrlRange(1, $accounts->lastPage()) as $page => $url)
            @if($page == $accounts->currentPage())
                <span class="pagination-btn pagination-btn--active">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next --}}
        @if($accounts->hasMorePages())
            <a href="{{ $accounts->nextPageUrl() }}" class="pagination-btn">Berikutnya</a>
        @else
            <button class="pagination-btn pagination-btn--disabled" disabled>Berikutnya</button>
        @endif
    </div>
    @endif
</div>

{{-- ============================================
     Modal Form: Add Account
     ============================================ --}}
<div class="modal-overlay" id="add-account-modal">
    <div class="account-modal-card">
        <form method="POST" action="{{ route('admin.sistem.accounts.store') }}" enctype="multipart/form-data" class="account-modal-body" id="add-account-form">
            @csrf
            <h3 class="account-modal-title">Tambah Akun</h3>

            <div class="account-form-group">
                <label class="account-form-label" for="add-nama">Nama</label>
                <input type="text" id="add-nama" name="nama" class="account-form-input" placeholder="Masukkan nama lengkap" required value="{{ old('nama') }}">
            </div>

            <div class="account-form-row">
                <div class="account-form-col">
                    <label class="account-form-label" for="add-nis-nip">NIS/NIP</label>
                    <input type="text" id="add-nis-nip" name="nis_nip" class="account-form-input" placeholder="Masukkan NIS atau NIP" required value="{{ old('nis_nip') }}">
                </div>
                <div class="account-form-col">
                    <label class="account-form-label" for="add-role">Peran</label>
                    <select id="add-role" name="role" class="account-form-select" required>
                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih peran</option>
                        <option value="siswa" {{ old('role') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="admin_sarana" {{ old('role') === 'admin_sarana' ? 'selected' : '' }}>Admin Sarana</option>
                    </select>
                </div>
            </div>

            <div class="account-form-group">
                <label class="account-form-label" for="add-kontak">Nomor Kontak</label>
                <input type="text" id="add-kontak" name="nomor_kontak" class="account-form-input" placeholder="Contoh: 08123456789" value="{{ old('nomor_kontak') }}">
            </div>

            <div class="account-form-group">
                <label class="account-form-label" for="add-username">Username</label>
                <input type="text" id="add-username" name="username" class="account-form-input" placeholder="Contoh: ahmad.fadli" required value="{{ old('username') }}">
            </div>

            <div class="account-form-group">
                <label class="account-form-label" for="add-email">Email</label>
                <input type="email" id="add-email" name="email" class="account-form-input" placeholder="Contoh: ahmad.fadli@mail.com" value="{{ old('email') }}">
            </div>

            <div class="account-form-group">
                <label class="account-form-label" for="add-password">Password</label>
                <input type="password" id="add-password" name="password" class="account-form-input" placeholder="••••••••" required>
            </div>

            <div class="account-form-group">
                <label class="account-form-label" for="add-confirm-password">Konfirmasi Password</label>
                <input type="password" id="add-confirm-password" name="password_confirmation" class="account-form-input" placeholder="••••••••" required>
            </div>

            <div class="account-form-group">
                <label class="account-form-label">Foto Profil</label>
                <div class="account-picture-box" id="add-picture-preview-container">
                    <span class="account-picture-text" id="add-picture-placeholder">Pratinjau Foto</span>
                    <img src="" alt="Preview" id="add-picture-preview-img" class="account-picture-preview-img" style="display: none;">
                </div>
                <input type="file" id="add-picture-file" name="foto" accept="image/*" style="display: none;">
                <button type="button" class="btn-add-file" id="btn-add-file-trigger">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    <span>Pilih Foto</span>
                </button>
            </div>

            @if($errors->any() && !old('_edit_mode'))
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:0.75rem 1rem; font-size:0.82rem; color:#dc2626;">
                    <ul style="margin:0; padding-left:1.2rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="account-modal-footer">
                <button type="button" class="btn-modal-cancel" id="btn-cancel-add">Batal</button>
                <button type="submit" class="btn-modal-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================
     Modal Form: Edit Account
     ============================================ --}}
<div class="modal-overlay" id="edit-account-modal">
    <div class="account-modal-card">
        <form method="POST" action="" enctype="multipart/form-data" class="account-modal-body" id="edit-account-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="_edit_mode" value="1">
            <h3 class="account-modal-title">Edit Akun</h3>

            <div class="account-form-group">
                <label class="account-form-label" for="edit-nama">Nama</label>
                <input type="text" id="edit-nama" name="nama" class="account-form-input" placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="account-form-row">
                <div class="account-form-col">
                    <label class="account-form-label" for="edit-nis-nip">NIS/NIP</label>
                    <input type="text" id="edit-nis-nip" name="nis_nip" class="account-form-input" placeholder="Masukkan NIS atau NIP" required>
                </div>
                <div class="account-form-col">
                    <label class="account-form-label" for="edit-role">Peran</label>
                    <select id="edit-role" name="role" class="account-form-select" required>
                        <option value="" disabled>Pilih peran</option>
                        <option value="siswa">Siswa</option>
                        <option value="admin_sarana">Admin Sarana</option>
                    </select>
                </div>
            </div>

            <div class="account-form-group">
                <label class="account-form-label" for="edit-kontak">Nomor Kontak</label>
                <input type="text" id="edit-kontak" name="nomor_kontak" class="account-form-input" placeholder="Contoh: 08123456789">
            </div>

            <div class="account-form-group">
                <label class="account-form-label" for="edit-username">Username</label>
                <input type="text" id="edit-username" name="username" class="account-form-input" placeholder="Contoh: ahmad.fadli" required>
            </div>

            <div class="account-form-group">
                <label class="account-form-label" for="edit-email">Email</label>
                <input type="email" id="edit-email" name="email" class="account-form-input" placeholder="Contoh: ahmad.fadli@mail.com">
            </div>

            <div class="account-form-group">
                <label class="account-form-label" for="edit-password">Password <span style="font-weight:400; color:#9ca3af;">(kosongkan jika tidak diubah)</span></label>
                <input type="password" id="edit-password" name="password" class="account-form-input" placeholder="••••••••">
            </div>

            <div class="account-form-group">
                <label class="account-form-label" for="edit-confirm-password">Konfirmasi Password</label>
                <input type="password" id="edit-confirm-password" name="password_confirmation" class="account-form-input" placeholder="••••••••">
            </div>

            <div class="account-form-group">
                <label class="account-form-label">Foto Profil</label>
                <div class="account-picture-box" id="edit-picture-preview-container">
                    <span class="account-picture-text" id="edit-picture-placeholder">Pratinjau Foto</span>
                    <img src="" alt="Preview" id="edit-picture-preview-img" class="account-picture-preview-img" style="display: none;">
                </div>
                <input type="file" id="edit-picture-file" name="foto" accept="image/*" style="display: none;">
                <button type="button" class="btn-add-file" id="btn-edit-file-trigger">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    <span>Ubah Foto</span>
                </button>
            </div>

            @if($errors->any() && old('_edit_mode'))
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:0.75rem 1rem; font-size:0.82rem; color:#dc2626;">
                    <ul style="margin:0; padding-left:1.2rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="account-modal-footer">
                <button type="button" class="btn-modal-cancel" id="btn-cancel-edit">Batal</button>
                <button type="submit" class="btn-modal-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================
     Modal: Account Detail (View)
     ============================================ --}}
<div class="modal-overlay" id="account-detail-modal">
    <div class="account-modal-card">
        <div class="account-modal-body" id="detail-modal-body">
            <h3 class="account-modal-title">Detail Akun</h3>
            <div class="account-modal-divider"></div>

            <div class="detail-avatar-container">
                <div class="detail-avatar-box">
                    <img src="" id="detail-view-avatar" alt="Avatar" style="display: none;">
                    <div id="detail-view-avatar-placeholder">
                        <x-heroicon-s-user class="w-16 h-16" style="color: #94a3b8;" />
                    </div>
                </div>
            </div>

            <div class="account-form-group">
                <label class="account-form-label">Nama</label>
                <input type="text" id="detail-view-nama" class="account-form-input account-form-input--readonly" readonly>
            </div>

            <div class="account-form-row">
                <div class="account-form-col">
                    <label class="account-form-label">NIS/NIP</label>
                    <input type="text" id="detail-view-nisnip" class="account-form-input account-form-input--readonly" readonly>
                </div>
                <div class="account-form-col">
                    <label class="account-form-label">Peran</label>
                    <input type="text" id="detail-view-role" class="account-form-input account-form-input--readonly" readonly>
                </div>
            </div>

            <div class="account-form-group">
                <label class="account-form-label">Nomor Kontak</label>
                <input type="text" id="detail-view-kontak" class="account-form-input account-form-input--readonly" readonly>
            </div>

            <div class="account-form-group">
                <label class="account-form-label">Username</label>
                <input type="text" id="detail-view-username" class="account-form-input account-form-input--readonly" readonly>
            </div>

            <div class="account-form-group">
                <label class="account-form-label">Email</label>
                <input type="text" id="detail-view-email" class="account-form-input account-form-input--readonly" readonly>
            </div>

            <div class="account-modal-divider"></div>

            <div class="account-modal-footer">
                <button type="button" class="btn-modal-exit" id="btn-exit-detail-modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================
     JavaScript: Modals, Dropdown, AJAX Detail
     ============================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ─── Dropdown Action Menu ───────────────────────────────────────
    const dropdownMenus = document.querySelectorAll('.action-dropdown-menu');

    document.querySelectorAll('.action-menu-trigger').forEach(trigger => {
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const menu = this.closest('.action-dropdown-wrapper').querySelector('.action-dropdown-menu');
            const isActive = menu.classList.contains('action-dropdown-menu--active');
            dropdownMenus.forEach(m => m.classList.remove('action-dropdown-menu--active'));
            if (!isActive) menu.classList.add('action-dropdown-menu--active');
        });
    });

    document.addEventListener('click', () => {
        dropdownMenus.forEach(m => m.classList.remove('action-dropdown-menu--active'));
    });

    // ─── Add Account Modal ──────────────────────────────────────────
    const addModal = document.getElementById('add-account-modal');
    const btnAdd = document.getElementById('btn-add-account');
    const btnCancelAdd = document.getElementById('btn-cancel-add');
    const addFileInput = document.getElementById('add-picture-file');
    const addFileTrigger = document.getElementById('btn-add-file-trigger');
    const addPreviewImg = document.getElementById('add-picture-preview-img');
    const addPlaceholder = document.getElementById('add-picture-placeholder');

    if (btnAdd) btnAdd.addEventListener('click', () => addModal.classList.add('modal-overlay--active'));
    if (btnCancelAdd) btnCancelAdd.addEventListener('click', () => addModal.classList.remove('modal-overlay--active'));
    if (addFileTrigger) addFileTrigger.addEventListener('click', () => addFileInput.click());
    if (addFileInput) {
        addFileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    addPreviewImg.src = e.target.result;
                    addPreviewImg.style.display = 'block';
                    addPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // ─── Edit Account Modal ─────────────────────────────────────────
    const editModal = document.getElementById('edit-account-modal');
    const editForm = document.getElementById('edit-account-form');
    const btnCancelEdit = document.getElementById('btn-cancel-edit');
    const editFileInput = document.getElementById('edit-picture-file');
    const editFileTrigger = document.getElementById('btn-edit-file-trigger');
    const editPreviewImg = document.getElementById('edit-picture-preview-img');
    const editPlaceholder = document.getElementById('edit-picture-placeholder');

    if (btnCancelEdit) btnCancelEdit.addEventListener('click', () => editModal.classList.remove('modal-overlay--active'));
    if (editFileTrigger) editFileTrigger.addEventListener('click', () => editFileInput.click());
    if (editFileInput) {
        editFileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    editPreviewImg.src = e.target.result;
                    editPreviewImg.style.display = 'block';
                    editPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    document.querySelectorAll('.btn-edit-account').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            editForm.action = `/admin-sistem/accounts/${id}`;
            document.getElementById('edit-nama').value = this.dataset.nama || '';
            document.getElementById('edit-nis-nip').value = this.dataset.nisnip || '';
            document.getElementById('edit-role').value = this.dataset.role || '';
            document.getElementById('edit-kontak').value = this.dataset.kontak || '';
            document.getElementById('edit-username').value = this.dataset.username || '';
            document.getElementById('edit-email').value = this.dataset.email || '';
            document.getElementById('edit-password').value = '';
            document.getElementById('edit-confirm-password').value = '';

            if (this.dataset.foto) {
                editPreviewImg.src = this.dataset.foto;
                editPreviewImg.style.display = 'block';
                editPlaceholder.style.display = 'none';
            } else {
                editPreviewImg.style.display = 'none';
                editPlaceholder.style.display = 'block';
            }

            editModal.classList.add('modal-overlay--active');
        });
    });

    // ─── Account Detail Modal (AJAX) ────────────────────────────────
    const detailModal = document.getElementById('account-detail-modal');
    const btnExitDetail = document.getElementById('btn-exit-detail-modal');

    if (btnExitDetail) btnExitDetail.addEventListener('click', () => detailModal.classList.remove('modal-overlay--active'));

    document.querySelectorAll('.btn-view-account').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;

            fetch(`/admin-sistem/accounts/${id}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('detail-view-nama').value = data.nama || '';
                document.getElementById('detail-view-nisnip').value = data.nis_nip || '-';
                document.getElementById('detail-view-role').value = data.role_label || '';
                document.getElementById('detail-view-kontak').value = data.nomor_kontak || '-';
                document.getElementById('detail-view-username').value = data.username || '';
                document.getElementById('detail-view-email').value = data.email || '-';

                const avatar = document.getElementById('detail-view-avatar');
                const avatarPlaceholder = document.getElementById('detail-view-avatar-placeholder');

                if (data.foto) {
                    avatar.src = data.foto;
                    avatar.style.display = 'block';
                    avatarPlaceholder.style.display = 'none';
                } else {
                    avatar.style.display = 'none';
                    avatarPlaceholder.style.display = 'block';
                }

                detailModal.classList.add('modal-overlay--active');
            })
            .catch(() => alert('Gagal memuat data akun.'));
        });
    });

    // ─── Close on overlay click / Escape ────────────────────────────
    [addModal, editModal, detailModal].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) modal.classList.remove('modal-overlay--active');
            });
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            [addModal, editModal, detailModal].forEach(m => { if (m) m.classList.remove('modal-overlay--active'); });
            dropdownMenus.forEach(m => m.classList.remove('action-dropdown-menu--active'));
        }
    });

    // ─── Auto-open Add modal on validation error ────────────────────
    @if($errors->any() && !old('_edit_mode'))
        addModal.classList.add('modal-overlay--active');
    @endif

    // ─── Auto-dismiss flash messages ────────────────────────────────
    setTimeout(() => {
        document.querySelectorAll('.system-alert').forEach(el => {
            el.style.transition = 'opacity 0.3s ease';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 300);
        });
    }, 4000);
});
</script>
@endsection
