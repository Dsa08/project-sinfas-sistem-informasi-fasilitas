@extends('layouts.admin-sarana')

@section('title', 'Kelola Data Alat - Admin Sarana SINFAS')
@section('page_title', 'Kelola data alat')

@section('content')
<div class="sarana-items-container">
    <div class="system-section-header" style="margin-bottom: 1.25rem;">
        <h2 class="system-section-heading" style="font-size: 1.25rem; font-weight: 700; color: #111827;">Kelola data alat</h2>
    </div>

    {{-- Filter & Add Item Bar --}}
    <div class="system-filter-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; gap: 1rem;">
        <div class="system-search-box" style="flex: 1; max-width: 320px; position: relative;">
            <input
                type="text"
                class="system-search-input"
                id="search-items-input"
                placeholder="Search items..."
                style="width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 0.88rem; outline: none; background: #ffffff;"
            >
        </div>
        <button type="button" class="btn-add-primary" id="btn-add-item" onclick="openAddItemModal()" style="background-color: #1D67F2; color: #ffffff; border: none; border-radius: 8px; padding: 0.6rem 1.2rem; font-size: 0.88rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
            + Add Item
        </button>
    </div>

    {{-- Items Table Card --}}
    <div class="system-table-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <table class="system-table" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
            <thead>
                <tr style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb; color: #4b5563; font-weight: 600; text-align: left;">
                    <th style="padding: 0.85rem 1rem;">Item Name</th>
                    <th style="padding: 0.85rem 1rem;">Category</th>
                    <th style="padding: 0.85rem 1rem; text-align: center;">Baik</th>
                    <th style="padding: 0.85rem 1rem; text-align: center;">K. Baik</th>
                    <th style="padding: 0.85rem 1rem; text-align: center;">R. Berat</th>
                    <th style="padding: 0.85rem 1rem;">Status</th>
                    <th style="padding: 0.85rem 1rem; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="itemsTableBody">
                {{-- Row 1: Projector Epson X300 --}}
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 0.85rem 1rem; font-weight: 500; color: #111827;">Projector Epson X300</td>
                    <td style="padding: 0.85rem 1rem; color: #4b5563;">Electronics</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">4</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">1</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">0</td>
                    <td style="padding: 0.85rem 1rem;"><span style="color: #16a34a; font-weight: 600;">Available</span></td>
                    <td style="padding: 0.85rem 1rem; text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <button type="button" class="btn-table-outline-blue" onclick="openEditItemModal('Projector Epson X300', 'Proyektor', 'Epson', 'SN1294819', '30x20x10 cm', 'Plastik/Aluminium', '2023', 4, 1, 0, '')">Edit</button>
                            <button type="button" class="btn-table-outline-red" onclick="openDeleteItemModal('Projector Epson X300')">Delete</button>
                        </div>
                    </td>
                </tr>

                {{-- Row 2: Portable Speaker JBL --}}
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 0.85rem 1rem; font-weight: 500; color: #111827;">Portable Speaker JBL</td>
                    <td style="padding: 0.85rem 1rem; color: #4b5563;">Electronics</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">2</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">1</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">0</td>
                    <td style="padding: 0.85rem 1rem;"><span style="color: #16a34a; font-weight: 600;">Available</span></td>
                    <td style="padding: 0.85rem 1rem; text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <button type="button" class="btn-table-outline-blue" onclick="openEditItemModal('Portable Speaker JBL', 'Speaker', 'JBL', 'SN883921', '25x15x15 cm', 'Plastik', '2023', 2, 1, 0, '')">Edit</button>
                            <button type="button" class="btn-table-outline-red" onclick="openDeleteItemModal('Portable Speaker JBL')">Delete</button>
                        </div>
                    </td>
                </tr>

                {{-- Row 3: Folding Table 180cm --}}
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 0.85rem 1rem; font-weight: 500; color: #111827;">Folding Table 180cm</td>
                    <td style="padding: 0.85rem 1rem; color: #4b5563;">Furniture</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">8</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">1</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">1</td>
                    <td style="padding: 0.85rem 1rem;"><span style="color: #16a34a; font-weight: 600;">Available</span></td>
                    <td style="padding: 0.85rem 1rem; text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <button type="button" class="btn-table-outline-blue" onclick="openEditItemModal('Folding Table 180cm', 'Furniture', 'Informa', 'SN554311', '180x80x75 cm', 'Besi & Kayu', '2022', 8, 1, 1, '')">Edit</button>
                            <button type="button" class="btn-table-outline-red" onclick="openDeleteItemModal('Folding Table 180cm')">Delete</button>
                        </div>
                    </td>
                </tr>

                {{-- Row 4: Whiteboard 120cm --}}
                <tr>
                    <td style="padding: 0.85rem 1rem; font-weight: 500; color: #111827;">Whiteboard 120cm</td>
                    <td style="padding: 0.85rem 1rem; color: #4b5563;">Equipment</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">0</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">1</td>
                    <td style="padding: 0.85rem 1rem; text-align: center; color: #111827;">1</td>
                    <td style="padding: 0.85rem 1rem;"><span style="color: #dc2626; font-weight: 600;">Unavailable</span></td>
                    <td style="padding: 0.85rem 1rem; text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <button type="button" class="btn-table-outline-blue" onclick="openEditItemModal('Whiteboard 120cm', 'Equipment', 'Sakura', 'SN992812', '120x90 cm', 'Aluminium/Melamin', '2021', 0, 1, 1, '')">Edit</button>
                            <button type="button" class="btn-table-outline-red" onclick="openDeleteItemModal('Whiteboard 120cm')">Delete</button>
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
        <button class="pagination-btn">Next</button>
    </div>
</div>

{{-- Add/Edit Item Modal --}}
<div class="modal-overlay" id="itemModal">
    <div class="modal-card" style="max-width: 580px; width: 90%; text-align: left; padding: 2rem; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f3f4f6;">
            <h3 class="modal-title" id="itemModalTitle" style="font-size: 1.2rem; font-weight: 700; color: #111827; margin: 0;">Add Item</h3>
            <button type="button" onclick="closeItemModal()" style="background: transparent; border: none; color: #9ca3af; font-size: 1.25rem; cursor: pointer; padding: 0.25rem;">&times;</button>
        </div>

        <form id="itemForm" onsubmit="handleItemSave(event)">
            {{-- Nama Barang --}}
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Nama Barang</label>
                <input type="text" id="itemNameInput" placeholder="e.g. Projector Epson X300" required style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.88rem; outline: none;">
            </div>

            {{-- Kategori --}}
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Kategori</label>
                <select id="itemCategorySelect" required style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.88rem; outline: none; background: #ffffff;">
                    <option value="" disabled selected>Select category</option>
                    <option value="Mic">Mic</option>
                    <option value="Proyektor">Proyektor</option>
                    <option value="Speaker">Speaker</option>
                    <option value="Kabel HDMI">Kabel HDMI</option>
                    <option value="Converter">Converter</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Equipment">Equipment</option>
                </select>
            </div>

            {{-- Merk/Model --}}
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Merk/Model</label>
                <input type="text" id="itemBrandInput" placeholder="e.g. Epson" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.88rem; outline: none;">
            </div>

            {{-- 2 Col: No Seri & Ukuran --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">No Seri Pabrik</label>
                    <input type="text" id="itemSerialInput" placeholder="e.g. SN1294819" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.88rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Ukuran/Dimensi</label>
                    <input type="text" id="itemDimensionInput" placeholder="e.g. 30×20×10 cm" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.88rem; outline: none;">
                </div>
            </div>

            {{-- 2 Col: Bahan & Tahun --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Bahan</label>
                    <input type="text" id="itemMaterialInput" placeholder="e.g. Plastik/Aluminium" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.88rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Tahun Pembelian</label>
                    <input type="text" id="itemYearInput" placeholder="e.g. 2023" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.88rem; outline: none;">
                </div>
            </div>

            {{-- Jumlah Kondisi: Baik, Kurang Baik, Rusak Berat --}}
            <div style="margin-bottom: 0.75rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Jumlah Baik</label>
                <input type="number" id="itemGoodQtyInput" min="0" value="0" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.85rem; font-size: 0.88rem; outline: none;">
            </div>
            <div style="margin-bottom: 0.75rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Jumlah Kurang Baik</label>
                <input type="number" id="itemFairQtyInput" min="0" value="0" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.85rem; font-size: 0.88rem; outline: none;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Jumlah Rusak Berat</label>
                <input type="number" id="itemDamagedQtyInput" min="0" value="0" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.85rem; font-size: 0.88rem; outline: none;">
            </div>

            {{-- Keterangan --}}
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem;">Keterangan</label>
                <textarea id="itemNotesInput" rows="2" placeholder="Keterangan tambahan..." style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.85rem; font-size: 0.88rem; outline: none;"></textarea>
            </div>

            <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="modal-btn modal-btn--cancel" onclick="closeItemModal()">Cancel</button>
                <button type="submit" class="modal-btn" style="background-color: #1D67F2; color: #ffffff; border: none; border-radius: 8px; padding: 0.55rem 1.4rem; font-weight: 600;">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal-card" style="max-width: 400px; text-align: center; padding: 1.75rem;">
        <h3 class="modal-title" style="font-size: 1.15rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;">Hapus Data Alat</h3>
        <p style="font-size: 0.9rem; color: #6b7280; margin-bottom: 1.5rem;">Yakin ingin menghapus <strong id="deleteItemName" style="color: #111827;"></strong> dari daftar alat?</p>
        <div class="modal-actions" style="display: flex; justify-content: center; gap: 0.75rem;">
            <button type="button" class="modal-btn modal-btn--cancel" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="modal-btn" style="background-color: #dc2626; color: #ffffff; border: none; border-radius: 8px; padding: 0.55rem 1.4rem; font-weight: 600;" onclick="confirmDeleteAction()">Delete</button>
        </div>
    </div>
</div>

<script>
    function openAddItemModal() {
        document.getElementById('itemModalTitle').textContent = 'Add Item';
        document.getElementById('itemForm').reset();
        document.getElementById('itemModal').classList.add('modal-overlay--active');
    }

    function openEditItemModal(name, category, brand, serial, dimension, material, year, good, fair, damaged, notes) {
        document.getElementById('itemModalTitle').textContent = 'Edit Item';
        document.getElementById('itemNameInput').value = name;
        document.getElementById('itemCategorySelect').value = category;
        document.getElementById('itemBrandInput').value = brand || '';
        document.getElementById('itemSerialInput').value = serial || '';
        document.getElementById('itemDimensionInput').value = dimension || '';
        document.getElementById('itemMaterialInput').value = material || '';
        document.getElementById('itemYearInput').value = year || '';
        document.getElementById('itemGoodQtyInput').value = good || 0;
        document.getElementById('itemFairQtyInput').value = fair || 0;
        document.getElementById('itemDamagedQtyInput').value = damaged || 0;
        document.getElementById('itemNotesInput').value = notes || '';
        document.getElementById('itemModal').classList.add('modal-overlay--active');
    }

    function closeItemModal() {
        document.getElementById('itemModal').classList.remove('modal-overlay--active');
    }

    function handleItemSave(e) {
        e.preventDefault();
        closeItemModal();
        alert('Data alat berhasil disimpan!');
    }

    function openDeleteItemModal(name) {
        document.getElementById('deleteItemName').textContent = name;
        document.getElementById('deleteModal').classList.add('modal-overlay--active');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('modal-overlay--active');
    }

    function confirmDeleteAction() {
        closeDeleteModal();
        alert('Data alat berhasil dihapus.');
    }
</script>
@endsection
