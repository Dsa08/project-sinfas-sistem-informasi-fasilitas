@extends('layouts.admin-sarana')

@section('title', 'Kelola Data Alat - Admin Sarana SINFAS')
@section('page_title', 'Kelola data alat')

@section('content')
<div class="sarana-items-container">
    <div class="system-section-header">
        <h2 class="system-section-heading">Kelola Barang</h2>
    </div>

    {{-- Filter & Add Item Bar --}}
    <div class="system-filter-bar">
        <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
            <form action="{{ route('admin.items') }}" method="GET" class="system-search-box" id="search-items-form" style="margin: 0;">
                @if(request('kategori'))
                    <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                @endif
                <svg class="system-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <input
                    type="text"
                    class="system-search-input"
                    id="search-items-input"
                    name="search"
                    placeholder="Cari barang..."
                    value="{{ request('search') }}"
                >
            </form>

            {{-- Category Filter Dropdown --}}
            <div style="position: relative;" id="category-filter-container">
                <button type="button" class="btn-filter-trigger {{ request('kategori') ? 'active' : '' }}" id="btn-filter-category" onclick="toggleCategoryDropdown(event)" title="Filter Kategori">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="4" y1="7" x2="20" y2="7"/>
                        <line x1="7" y1="12" x2="17" y2="12"/>
                        <line x1="10" y1="17" x2="14" y2="17"/>
                    </svg>
                </button>

                <div class="filter-dropdown-menu" id="categoryDropdownMenu" style="display: none;">
                    <div class="filter-dropdown-header">
                        <span>Filter Kategori</span>
                        @if(request('kategori'))
                            <a href="{{ route('admin.items', request()->only('search')) }}" class="filter-clear-link">Reset</a>
                        @endif
                    </div>
                    <div class="filter-dropdown-list">
                        <a href="{{ route('admin.items', request()->only('search')) }}" class="filter-dropdown-item {{ !request('kategori') ? 'active' : '' }}">
                            <span>Semua Kategori</span>
                            @if(!request('kategori'))
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            @endif
                        </a>
                        @foreach($categories as $cat)
                        <a href="{{ route('admin.items', array_merge(request()->only('search'), ['kategori' => $cat->id_kategori])) }}" class="filter-dropdown-item {{ request('kategori') == $cat->id_kategori ? 'active' : '' }}">
                            <span>{{ $cat->nama_kategori }}</span>
                            @if(request('kategori') == $cat->id_kategori)
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            @endif
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Active Filter Pill --}}
            @if(request('kategori'))
                @php
                    $activeCat = $categories->firstWhere('id_kategori', request('kategori'));
                @endphp
                @if($activeCat)
                    <div class="filter-active-pill">
                        <span>Kategori: <strong>{{ $activeCat->nama_kategori }}</strong></span>
                        <a href="{{ route('admin.items', request()->only('search')) }}" class="filter-pill-remove" title="Hapus Filter">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </a>
                    </div>
                @endif
            @endif
        </div>

        <button type="button" class="btn-add-account" id="btn-add-item" onclick="openAddItemModal()">
            + Tambah Barang
        </button>
    </div>

    {{-- Items Table --}}
    <div class="system-table-card">
        <table class="system-table">
            <thead>
                <tr>
                    <th style="width: 22%;">Nama Barang</th>
                    <th style="width: 15%;">Kategori</th>
                    <th style="width: 10%;">Baik</th>
                    <th style="width: 10%;">K. Baik</th>
                    <th style="width: 10%;">R. Berat</th>
                    <th style="width: 13%;">Status</th>
                    <th style="width: 20%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td class="td-name">{{ $item->nama_barang }}</td>
                    <td class="td-category">{{ $item->kategori->nama_kategori ?? '-' }}</td>
                    <td class="td-stock">{{ $item->jumlah_baik }}</td>
                    <td class="td-stock">{{ $item->jumlah_kurang_baik }}</td>
                    <td class="td-stock" style="{{ $item->jumlah_rusak_berat > 0 ? 'color: #b91c1c; font-weight: 600;' : '' }}">{{ $item->jumlah_rusak_berat }}</td>
                    <td>
                        <span class="sarana-status-badge {{ in_array($item->status, ['Available', 'Tersedia']) ? 'sarana-status-badge--available' : 'sarana-status-badge--unavailable' }}">
                            {{ in_array($item->status, ['Available', 'Tersedia']) ? 'Tersedia' : 'Tidak Tersedia' }}
                        </span>
                    </td>
                    <td>
                        <div class="action-btn-group">
                            <button type="button" class="btn-table-outline-blue" onclick="openEditItemModal('{{ $item->kode_barang }}')">Ubah</button>
                            <form action="{{ route('admin.items.destroy', $item->kode_barang) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data barang {{ addslashes($item->nama_barang) }}? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-table-outline-red">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #9ca3af; padding: 2rem;">Belum ada data barang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div class="system-pagination-bar">
        {{ $items->links('vendor.pagination.simple-default') }}
    </div>
    @endif
</div>

{{-- Add / Edit Item Modal --}}
<div class="modal-overlay" id="itemModal">
    <div class="modal-card" style="max-width: 640px; width: 92%; text-align: left; padding: 1.75rem; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f3f4f6;">
            <h3 class="modal-title" id="itemModalTitle" style="font-size: 1.15rem; font-weight: 700; color: #111827; margin: 0;">Tambah Barang</h3>
            <button type="button" onclick="closeItemModal()" style="background: transparent; border: none; color: #9ca3af; font-size: 1.25rem; cursor: pointer; padding: 0.25rem;">&times;</button>
        </div>

        {{-- Validation Errors --}}
        @if($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.65rem 0.85rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.82rem;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="itemForm" method="POST" action="{{ route('admin.items.store') }}" enctype="multipart/form-data" onsubmit="return confirmItemFormSubmit()">
            @csrf
            <input type="hidden" name="_method" id="itemFormMethod" value="POST">

            {{-- Kode Barang (hanya tampil saat Add) --}}
            <div id="kodeBarangField" style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">Kode Barang</label>
                <input type="text" name="kode_barang" id="input_kode_barang" placeholder="Contoh: BRG-001" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none;">
            </div>

            {{-- Nama Barang --}}
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">Nama Barang</label>
                <input type="text" name="nama_barang" id="input_nama_barang" placeholder="Nama barang" required style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none;">
            </div>

            {{-- Kategori --}}
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">Kategori</label>
                <select name="id_kategori" id="input_id_kategori" required style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none; background: #fff;">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id_kategori }}">{{ $cat->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Merk / Model --}}
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">Merk / Model</label>
                <input type="text" name="merk_model" id="input_merk_model" placeholder="Merk atau model" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none;">
            </div>

            {{-- 2-column row: No Seri & Dimensi --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">No Seri Pabrik</label>
                    <input type="text" name="no_seri_pabrik" id="input_no_seri_pabrik" placeholder="No. seri" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">Ukuran / Dimensi</label>
                    <input type="text" name="ukuran_dimensi" id="input_ukuran_dimensi" placeholder="Dimensi" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none;">
                </div>
            </div>

            {{-- 2-column row: Bahan & Tahun Pembelian --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">Bahan</label>
                    <input type="text" name="bahan" id="input_bahan" placeholder="Bahan material" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">Tahun Pembelian</label>
                    <input type="number" name="tahun_pembelian" id="input_tahun_pembelian" placeholder="2024" min="1900" max="{{ date('Y') + 1 }}" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none;">
                </div>
            </div>

            {{-- 3-column row: Jumlah Baik / Kurang Baik / Rusak Berat --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">Jumlah Baik</label>
                    <input type="number" name="jumlah_baik" id="input_jumlah_baik" value="0" min="0" required style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">Jumlah K. Baik</label>
                    <input type="number" name="jumlah_kurang_baik" id="input_jumlah_kurang_baik" value="0" min="0" required style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">Jumlah R. Berat</label>
                    <input type="number" name="jumlah_rusak_berat" id="input_jumlah_rusak_berat" value="0" min="0" required style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none;">
                </div>
            </div>

            {{-- Keterangan --}}
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 0.3rem;">Keterangan</label>
                <textarea name="keterangan" id="input_keterangan" rows="3" placeholder="Catatan tambahan..." style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.8rem; font-size: 0.88rem; outline: none; resize: vertical;"></textarea>
            </div>

            {{-- Picture Upload (Sesuai Desain Mockup) --}}
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.88rem; font-weight: 500; color: #475569; margin-bottom: 0.4rem;">Foto Barang</label>
                
                {{-- Preview Box --}}
                <div id="itemPicturePreviewBox" style="width: 100%; height: 180px; border: 1.5px solid #cbd5e1; border-radius: 12px; background: #ffffff; display: flex; align-items: center; justify-content: center; margin-bottom: 0.65rem; overflow: hidden; position: relative;">
                    <span id="itemImagePlaceholder" style="color: #64748b; font-size: 0.95rem; font-weight: 500;">Pratinjau Foto</span>
                    <img id="itemImagePreview" src="" alt="Pratinjau Gambar" style="display: none; width: 100%; height: 100%; object-fit: contain; background: #f8fafc;">
                </div>

                {{-- Add File Button --}}
                <button type="button" class="btn-item-add-file" onclick="document.getElementById('input_item_foto').click()" style="width: 100%; display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 1rem; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer; transition: all 0.15s ease;">
                    <div style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; border: 1.5px solid #1e293b; border-radius: 4px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="16" y2="12"/>
                        </svg>
                    </div>
                    <span style="font-size: 0.92rem; font-weight: 500; color: #475569;">Pilih file foto</span>
                </button>

                <input type="file" name="foto" id="input_item_foto" accept="image/jpeg,image/png,image/jpg,image/webp" style="display: none;" onchange="handleItemImageChange(this)">
            </div>

            {{-- Action Buttons --}}
            <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="modal-btn modal-btn--cancel" onclick="closeItemModal()">Batal</button>
                <button type="submit" class="modal-btn" style="background-color: #1D67F2; color: #ffffff; border: none; border-radius: 8px; padding: 0.55rem 1.4rem; font-weight: 600;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function handleItemImageChange(input) {
        const preview = document.getElementById('itemImagePreview');
        const placeholder = document.getElementById('itemImagePlaceholder');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function resetItemImagePreview(existingUrl = null) {
        const preview = document.getElementById('itemImagePreview');
        const placeholder = document.getElementById('itemImagePlaceholder');
        document.getElementById('input_item_foto').value = '';

        if (existingUrl) {
            preview.src = existingUrl;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        } else {
            preview.src = '';
            preview.style.display = 'none';
            placeholder.style.display = 'block';
        }
    }

    function confirmItemFormSubmit() {
        const isEdit = document.getElementById('itemFormMethod').value === 'PUT';
        const nameInput = document.getElementById('input_nama_barang');
        const itemName = nameInput ? nameInput.value.trim() : '';
        const msg = isEdit 
            ? (itemName ? `Apakah Anda yakin ingin menyimpan perubahan data barang "${itemName}"?` : 'Apakah Anda yakin ingin menyimpan perubahan data barang ini?')
            : (itemName ? `Apakah Anda yakin ingin menambahkan data barang baru "${itemName}"?` : 'Apakah Anda yakin ingin menambahkan data barang baru ini?');
        return confirm(msg);
    }

    function openAddItemModal() {
        document.getElementById('itemModalTitle').textContent = 'Tambah Barang';
        document.getElementById('itemForm').action = '{{ route("admin.items.store") }}';
        document.getElementById('itemFormMethod').value = 'POST';
        document.getElementById('kodeBarangField').style.display = 'block';
        document.getElementById('itemForm').reset();
        document.getElementById('input_jumlah_baik').value = '0';
        document.getElementById('input_jumlah_kurang_baik').value = '0';
        document.getElementById('input_jumlah_rusak_berat').value = '0';
        resetItemImagePreview(null);
        document.getElementById('itemModal').classList.add('modal-overlay--active');
    }

    function openEditItemModal(kode) {
        document.getElementById('itemModalTitle').textContent = 'Ubah Data Barang';
        document.getElementById('itemFormMethod').value = 'PUT';
        document.getElementById('kodeBarangField').style.display = 'none';

        // Fetch item data via AJAX
        fetch('/admin/items/' + kode)
            .then(response => response.json())
            .then(data => {
                document.getElementById('itemForm').action = '/admin/items/' + kode;
                document.getElementById('input_kode_barang').value = data.kode_barang;
                document.getElementById('input_nama_barang').value = data.nama_barang;
                document.getElementById('input_id_kategori').value = data.id_kategori;
                document.getElementById('input_merk_model').value = data.merk_model || '';
                document.getElementById('input_no_seri_pabrik').value = data.no_seri_pabrik || '';
                document.getElementById('input_ukuran_dimensi').value = data.ukuran_dimensi || '';
                document.getElementById('input_bahan').value = data.bahan || '';
                document.getElementById('input_tahun_pembelian').value = data.tahun_pembelian || '';
                document.getElementById('input_jumlah_baik').value = data.jumlah_baik;
                document.getElementById('input_jumlah_kurang_baik').value = data.jumlah_kurang_baik;
                document.getElementById('input_jumlah_rusak_berat').value = data.jumlah_rusak_berat;
                document.getElementById('input_keterangan').value = data.keterangan || '';
                resetItemImagePreview(data.foto);
                document.getElementById('itemModal').classList.add('modal-overlay--active');
            })
            .catch(err => alert('Gagal memuat data barang.'));
    }

    function closeItemModal() {
        document.getElementById('itemModal').classList.remove('modal-overlay--active');
    }

    // Category Filter Dropdown Handler
    function toggleCategoryDropdown(event) {
        event.stopPropagation();
        const menu = document.getElementById('categoryDropdownMenu');
        if (menu) {
            menu.style.display = menu.style.display === 'none' || menu.style.display === '' ? 'block' : 'none';
        }
    }

    document.addEventListener('click', function(e) {
        const container = document.getElementById('category-filter-container');
        const menu = document.getElementById('categoryDropdownMenu');
        if (container && menu && !container.contains(e.target)) {
            menu.style.display = 'none';
        }
    });

    // Auto-open modal jika ada validation errors
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('itemModal').classList.add('modal-overlay--active');
        });
    @endif
</script>
@endsection
