@extends('layouts.admin-sarana')

@section('title', 'Kelola Kategori - Admin Sarana SINFAS')
@section('page_title', 'Kelola Kategori')

@section('content')
<div class="sarana-categories-container">
    {{-- Header & Subtitle --}}
    <div class="system-section-header" style="margin-bottom: 1.25rem;">
        <h2 class="system-section-heading" style="font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0 0 0.25rem;">Kelola Kategori</h2>
        <p style="font-size: 0.88rem; color: #6b7280; margin: 0;">Kelola kategori aset dan jumlah barang</p>
    </div>

    {{-- Filter & Add Category Bar --}}
    <div class="system-filter-bar">
        <form action="{{ route('admin.categories') }}" method="GET" class="system-search-box">
            <svg class="system-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            <input
                type="text"
                class="system-search-input"
                id="search-categories-input"
                name="search"
                placeholder="Cari kategori..."
                value="{{ request('search') }}"
            >
        </form>
        <button type="button" class="btn-add-account" id="btn-add-category" onclick="openAddCategoryModal()">
            + Tambah Kategori
        </button>
    </div>

    {{-- Categories Table Card --}}
    <div class="system-table-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <table class="system-table" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
            <thead>
                <tr style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb; color: #4b5563; font-weight: 600; text-align: left;">
                    <th style="padding: 0.85rem 1.25rem; width: 45%;">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama_kategori', 'dir' => request('sort') === 'nama_kategori' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="th-content {{ request('sort') === 'nama_kategori' ? 'th-content--active' : '' }}">
                            <span>Nama Kategori</span>
                            @if(request('sort') === 'nama_kategori')
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
                    <th style="padding: 0.85rem 1.25rem; width: 35%;">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'barang_count', 'dir' => request('sort') === 'barang_count' && request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="th-content {{ request('sort') === 'barang_count' ? 'th-content--active' : '' }}">
                            <span>Jumlah Barang</span>
                            @if(request('sort') === 'barang_count')
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
                    <th style="padding: 0.85rem 1.25rem; width: 20%; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="categoriesTableBody">
                @forelse($categories as $cat)
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 0.85rem 1.25rem; font-weight: 500; color: #111827;">{{ $cat->nama_kategori }}</td>
                    <td style="padding: 0.85rem 1.25rem; color: #4b5563;">{{ $cat->barang_count }} barang</td>
                    <td style="padding: 0.85rem 1.25rem; text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <button type="button" class="btn-table-outline-blue" onclick="openEditCategoryModal({{ $cat->id_kategori }}, '{{ addslashes($cat->nama_kategori) }}')">Ubah</button>
                            <button type="button" class="btn-table-outline-red" onclick="openDeleteCategoryModal({{ $cat->id_kategori }}, '{{ addslashes($cat->nama_kategori) }}')">Hapus</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #9ca3af; padding: 2rem;">Belum ada kategori.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($categories->hasPages())
    <div class="system-pagination-bar" style="display: flex; justify-content: flex-end; align-items: center; gap: 0.35rem; margin-top: 1.25rem;">
        {{ $categories->links('vendor.pagination.simple-default') }}
    </div>
    @endif
</div>

{{-- Add/Edit Category Modal --}}
<div class="modal-overlay" id="categoryModal">
    <div class="modal-card" style="max-width: 440px; width: 90%; text-align: left; padding: 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f3f4f6;">
            <h3 class="modal-title" id="categoryModalTitle" style="font-size: 1.15rem; font-weight: 700; color: #111827; margin: 0;">Tambah Kategori</h3>
            <button type="button" onclick="closeCategoryModal()" style="background: transparent; border: none; color: #9ca3af; font-size: 1.25rem; cursor: pointer; padding: 0.25rem;">&times;</button>
        </div>

        @if($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.65rem 0.85rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.82rem;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="categoryForm" method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <input type="hidden" name="_method" id="categoryFormMethod" value="POST">

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Nama Kategori</label>
                <input type="text" name="nama_kategori" id="categoryNameInput" placeholder="Masukkan nama kategori" required style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.88rem; outline: none;">
            </div>

            <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="modal-btn modal-btn--cancel" onclick="closeCategoryModal()">Batal</button>
                <button type="submit" class="modal-btn" style="background-color: #1D67F2; color: #ffffff; border: none; border-radius: 8px; padding: 0.55rem 1.4rem; font-weight: 600;">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Category Modal --}}
<div class="modal-overlay" id="deleteCategoryModal">
    <div class="modal-card" style="max-width: 400px; text-align: center; padding: 1.75rem;">
        <h3 class="modal-title" style="font-size: 1.15rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;">Hapus Kategori</h3>
        <p style="font-size: 0.9rem; color: #6b7280; margin-bottom: 1.5rem;">Yakin ingin menghapus kategori <strong id="deleteCategoryName" style="color: #111827;"></strong>?</p>
        <form id="deleteCategoryForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-actions" style="display: flex; justify-content: center; gap: 0.75rem;">
                <button type="button" class="modal-btn modal-btn--cancel" onclick="closeDeleteCategoryModal()">Batal</button>
                <button type="submit" class="modal-btn" style="background-color: #dc2626; color: #ffffff; border: none; border-radius: 8px; padding: 0.55rem 1.4rem; font-weight: 600;">Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddCategoryModal() {
        document.getElementById('categoryModalTitle').textContent = 'Tambah Kategori';
        document.getElementById('categoryForm').action = '{{ route("admin.categories.store") }}';
        document.getElementById('categoryFormMethod').value = 'POST';
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryModal').classList.add('modal-overlay--active');
    }

    function openEditCategoryModal(id, name) {
        document.getElementById('categoryModalTitle').textContent = 'Ubah Kategori';
        document.getElementById('categoryForm').action = '/admin/categories/' + id;
        document.getElementById('categoryFormMethod').value = 'PUT';
        document.getElementById('categoryNameInput').value = name;
        document.getElementById('categoryModal').classList.add('modal-overlay--active');
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').classList.remove('modal-overlay--active');
    }

    function openDeleteCategoryModal(id, name) {
        document.getElementById('deleteCategoryName').textContent = name;
        document.getElementById('deleteCategoryForm').action = '/admin/categories/' + id;
        document.getElementById('deleteCategoryModal').classList.add('modal-overlay--active');
    }

    function closeDeleteCategoryModal() {
        document.getElementById('deleteCategoryModal').classList.remove('modal-overlay--active');
    }

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('categoryModal').classList.add('modal-overlay--active');
        });
    @endif
</script>
@endsection
