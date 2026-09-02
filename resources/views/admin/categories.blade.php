@extends('layouts.admin-sarana')

@section('title', 'Kelola Kategori - Admin Sarana SINFAS')
@section('page_title', 'Kelola Kategori')

@section('content')
<div class="sarana-categories-container">
    {{-- Header & Subtitle --}}
    <div class="system-section-header" style="margin-bottom: 1.25rem;">
        <h2 class="system-section-heading" style="font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0 0 0.25rem;">Kelola Kategori</h2>
        <p style="font-size: 0.88rem; color: #6b7280; margin: 0;">Manage asset categories and item counts</p>
    </div>

    {{-- Filter & Add Category Bar --}}
    <div class="system-filter-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; gap: 1rem;">
        <div class="system-search-box" style="flex: 1; max-width: 320px; position: relative;">
            <input
                type="text"
                class="system-search-input"
                id="search-categories-input"
                placeholder="Search categories..."
                style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 0.88rem; outline: none; background: #ffffff;"
            >
        </div>
        <button type="button" class="btn-add-primary" id="btn-add-category" onclick="openAddCategoryModal()" style="background-color: #1D67F2; color: #ffffff; border: none; border-radius: 8px; padding: 0.6rem 1.2rem; font-size: 0.88rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
            + Add Category
        </button>
    </div>

    {{-- Categories Table Card --}}
    <div class="system-table-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <table class="system-table" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
            <thead>
                <tr style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb; color: #4b5563; font-weight: 600; text-align: left;">
                    <th style="padding: 0.85rem 1.25rem; width: 45%;">Nama Kategori</th>
                    <th style="padding: 0.85rem 1.25rem; width: 35%;">Jumlah Barang</th>
                    <th style="padding: 0.85rem 1.25rem; width: 20%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="categoriesTableBody">
                {{-- Row 1: Mic --}}
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 0.85rem 1.25rem; font-weight: 500; color: #111827;">Mic</td>
                    <td style="padding: 0.85rem 1.25rem; color: #4b5563;">12 items</td>
                    <td style="padding: 0.85rem 1.25rem; text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <button type="button" class="btn-table-outline-blue" onclick="openEditCategoryModal('Mic')">Edit</button>
                            <button type="button" class="btn-table-outline-red" onclick="openDeleteCategoryModal('Mic')">Delete</button>
                        </div>
                    </td>
                </tr>

                {{-- Row 2: Kabel HDMI --}}
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 0.85rem 1.25rem; font-weight: 500; color: #111827;">Kabel HDMI</td>
                    <td style="padding: 0.85rem 1.25rem; color: #4b5563;">8 items</td>
                    <td style="padding: 0.85rem 1.25rem; text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <button type="button" class="btn-table-outline-blue" onclick="openEditCategoryModal('Kabel HDMI')">Edit</button>
                            <button type="button" class="btn-table-outline-red" onclick="openDeleteCategoryModal('Kabel HDMI')">Delete</button>
                        </div>
                    </td>
                </tr>

                {{-- Row 3: Proyektor --}}
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 0.85rem 1.25rem; font-weight: 500; color: #111827;">Proyektor</td>
                    <td style="padding: 0.85rem 1.25rem; color: #4b5563;">5 items</td>
                    <td style="padding: 0.85rem 1.25rem; text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <button type="button" class="btn-table-outline-blue" onclick="openEditCategoryModal('Proyektor')">Edit</button>
                            <button type="button" class="btn-table-outline-red" onclick="openDeleteCategoryModal('Proyektor')">Delete</button>
                        </div>
                    </td>
                </tr>

                {{-- Row 4: Converter --}}
                <tr>
                    <td style="padding: 0.85rem 1.25rem; font-weight: 500; color: #111827;">Converter</td>
                    <td style="padding: 0.85rem 1.25rem; color: #4b5563;">3 items</td>
                    <td style="padding: 0.85rem 1.25rem; text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <button type="button" class="btn-table-outline-blue" onclick="openEditCategoryModal('Converter')">Edit</button>
                            <button type="button" class="btn-table-outline-red" onclick="openDeleteCategoryModal('Converter')">Delete</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination Bar --}}
    <div class="system-pagination-bar" style="display: flex; justify-content: flex-end; align-items: center; gap: 0.35rem; margin-top: 1.25rem;">
        <button class="pagination-btn pagination-btn--disabled">Prev</button>
        <button class="pagination-btn pagination-btn--active">1</button>
        <button class="pagination-btn">2</button>
        <button class="pagination-btn">3</button>
        <button class="pagination-btn">Next</button>
    </div>
</div>

{{-- Add/Edit Category Modal --}}
<div class="modal-overlay" id="categoryModal">
    <div class="modal-card" style="max-width: 440px; width: 90%; text-align: left; padding: 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f3f4f6;">
            <h3 class="modal-title" id="categoryModalTitle" style="font-size: 1.15rem; font-weight: 700; color: #111827; margin: 0;">Add Category</h3>
            <button type="button" onclick="closeCategoryModal()" style="background: transparent; border: none; color: #9ca3af; font-size: 1.25rem; cursor: pointer; padding: 0.25rem;">&times;</button>
        </div>

        <form id="categoryForm" onsubmit="handleCategorySave(event)">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Nama Kategori</label>
                <input type="text" id="categoryNameInput" placeholder="Enter category name" required style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.88rem; outline: none;">
            </div>

            <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="modal-btn modal-btn--cancel" onclick="closeCategoryModal()">Cancel</button>
                <button type="submit" class="modal-btn" style="background-color: #1D67F2; color: #ffffff; border: none; border-radius: 8px; padding: 0.55rem 1.4rem; font-weight: 600;">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Category Modal --}}
<div class="modal-overlay" id="deleteCategoryModal">
    <div class="modal-card" style="max-width: 400px; text-align: center; padding: 1.75rem;">
        <h3 class="modal-title" style="font-size: 1.15rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;">Hapus Kategori</h3>
        <p style="font-size: 0.9rem; color: #6b7280; margin-bottom: 1.5rem;">Yakin ingin menghapus kategori <strong id="deleteCategoryName" style="color: #111827;"></strong>?</p>
        <div class="modal-actions" style="display: flex; justify-content: center; gap: 0.75rem;">
            <button type="button" class="modal-btn modal-btn--cancel" onclick="closeDeleteCategoryModal()">Cancel</button>
            <button type="button" class="modal-btn" style="background-color: #dc2626; color: #ffffff; border: none; border-radius: 8px; padding: 0.55rem 1.4rem; font-weight: 600;" onclick="confirmDeleteCategoryAction()">Delete</button>
        </div>
    </div>
</div>

<script>
    function openAddCategoryModal() {
        document.getElementById('categoryModalTitle').textContent = 'Add Category';
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryModal').classList.add('modal-overlay--active');
    }

    function openEditCategoryModal(name) {
        document.getElementById('categoryModalTitle').textContent = 'Edit Category';
        document.getElementById('categoryNameInput').value = name;
        document.getElementById('categoryModal').classList.add('modal-overlay--active');
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').classList.remove('modal-overlay--active');
    }

    function handleCategorySave(e) {
        e.preventDefault();
        closeCategoryModal();
        alert('Kategori berhasil disimpan!');
    }

    function openDeleteCategoryModal(name) {
        document.getElementById('deleteCategoryName').textContent = name;
        document.getElementById('deleteCategoryModal').classList.add('modal-overlay--active');
    }

    function closeDeleteCategoryModal() {
        document.getElementById('deleteCategoryModal').classList.remove('modal-overlay--active');
    }

    function confirmDeleteCategoryAction() {
        closeDeleteCategoryModal();
        alert('Kategori berhasil dihapus.');
    }
</script>
@endsection
