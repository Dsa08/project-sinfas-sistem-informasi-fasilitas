@extends('layouts.app')

@section('title', 'Status Pengajuan - SINFAS')

@section('navbar_title')
    <a href="{{ route('dashboard') }}" style="color: #4b5563; text-decoration: none; font-size: 0.88rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.3rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m15 18-6-6 6-6"/>
        </svg>
        Kembali ke Beranda
    </a>
@endsection

@section('content')
<div class="loan-status-container">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="flash-msg flash-msg--success" id="flash-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
            {{ session('success') }}
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;margin-left:auto;font-size:1.1rem;">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-msg flash-msg--error" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;margin-left:auto;font-size:1.1rem;">&times;</button>
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 class="loan-status-title" style="margin: 0;">Status Pengajuan</h2>
    </div>

    <div class="loan-status-list">
        @forelse($loans as $loan)
        <div class="loan-status-card">
            <div class="loan-status-card-left">
                {{-- Item Image Placeholder --}}
                <div class="loan-status-image">
                    @if(!empty($loan->barang->foto) && file_exists(public_path($loan->barang->foto)))
                        <img src="{{ asset($loan->barang->foto) }}" alt="{{ $loan->barang->nama_barang }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                    @else
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Item Info --}}
                <div class="loan-status-info">
                    <h3 class="loan-status-item-name">{{ $loan->barang->nama_barang ?? 'Barang' }}</h3>
                    <p class="loan-status-meta">
                        Dipinjam: {{ $loan->tanggal_pinjam->format('d M Y') }} &nbsp;&bull;&nbsp; Kode: <span style="font-weight: 600; color: #1f2937;">{{ $loan->kode_pinjam }}</span>
                    </p>
                    
                    @if($loan->lokasi_penggunaan)
                    <p class="loan-status-meta" style="color: #6b7280; font-size: 0.78rem;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $loan->lokasi_penggunaan }}
                    </p>
                    @endif

                    {{-- Return Status Details --}}
                    @if($loan->status_pengajuan === 'disetujui' && $loan->pengembalian)
                        @if(is_null($loan->pengembalian->kondisi_barang))
                            <p class="loan-status-meta" style="color: #d97706; font-size: 0.78rem; margin-top: 0.2rem; font-weight: 500;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                Pengembalian diajukan pada {{ $loan->pengembalian->tanggal_kembali->format('d M Y') }} (Menunggu verifikasi admin)
                            </p>
                        @else
                            <p class="loan-status-meta" style="color: #059669; font-size: 0.78rem; margin-top: 0.2rem; font-weight: 500;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px;"><path d="M20 6 9 17l-5-5"/></svg>
                                Telah dikembalikan pada {{ $loan->pengembalian->tanggal_kembali->format('d M Y') }} &bull; Kondisi: <strong>{{ $loan->pengembalian->kondisi_barang }}</strong>
                            </p>
                        @endif
                    @endif

                    {{-- Rejection reason --}}
                    @if($loan->status_pengajuan === 'ditolak')
                        @if($loan->alasan_penolakan)
                        <div class="loan-rejection-reason" style="margin-top: 0.5rem; padding: 0.45rem 0.75rem; background: #fef2f2; border-left: 3px solid #ef4444; border-radius: 4px;">
                            <span style="font-weight: 600; font-size: 0.78rem; color: #991b1b;">Alasan Penolakan:</span>
                            <p style="margin: 0.15rem 0 0; font-size: 0.82rem; color: #b91c1c;">{{ $loan->alasan_penolakan }}</p>
                        </div>
                        @else
                        <div class="loan-rejection-reason" style="margin-top: 0.4rem; font-size: 0.8rem; color: #9ca3af; font-style: italic;">
                            (Tidak ada keterangan alasan penolakan)
                        </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Actions & Status Badge --}}
            <div class="loan-status-badge-wrapper">
                <div class="loan-action-group">
                    @if($loan->status_pengajuan === 'menunggu')
                        <span class="loan-status-badge loan-badge--pending" onclick="openWaitingApprovalModal()" style="cursor: pointer;" title="Klik untuk melihat informasi antrean">Menunggu</span>
                    @elseif($loan->status_pengajuan === 'ditolak')
                        <span class="loan-status-badge loan-badge--rejected">Ditolak</span>
                    @elseif($loan->status_pengajuan === 'disetujui')
                        @if(!$loan->pengembalian)
                            {{-- Sedang Dipinjam + Tombol Pengembalian --}}
                            <span class="loan-status-badge loan-badge--approved" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                                Sedang Dipinjam
                            </span>
                            <button type="button" 
                                class="btn-loan-return"
                                onclick="openReturnModal(
                                    '{{ $loan->kode_pinjam }}',
                                    '{{ addslashes($loan->barang->nama_barang ?? 'Barang') }}',
                                    '{{ $loan->tanggal_pinjam->format('d M Y') }}',
                                    '{{ addslashes($loan->barang->kategori->nama_kategori ?? '-') }}',
                                    '{{ !empty($loan->barang->foto) && file_exists(public_path($loan->barang->foto)) ? asset($loan->barang->foto) : '' }}'
                                )">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 14 4 9 9 4"/>
                                    <path d="M20 20v-7a4 4 0 0 0-4-4H4"/>
                                </svg>
                                Kembalikan Barang
                            </button>
                        @elseif(is_null($loan->pengembalian->kondisi_barang))
                            {{-- Pengembalian Diajukan (Pending Admin Confirmation) --}}
                            <span class="loan-status-badge loan-badge--return-pending" title="Menunggu konfirmasi penerimaan oleh Admin Sarana">
                                Menunggu Verifikasi
                            </span>
                        @else
                            {{-- Pengembalian Selesai --}}
                            <span class="loan-status-badge loan-badge--returned">
                                Dikembalikan
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="text-align: center; padding: 3rem 1rem;">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin-bottom: 0.75rem;">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
            </svg>
            <p style="color: #6b7280; font-size: 0.95rem; margin: 0 0 0.5rem;">Belum ada pengajuan peminjaman.</p>
            <a href="{{ route('dashboard') }}" style="color: #1D67F2; font-size: 0.88rem; text-decoration: none; font-weight: 500;">Lihat Katalog Barang →</a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($loans->hasPages())
    <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
        {{ $loans->links('vendor.pagination.simple-default') }}
    </div>
    @endif
</div>

{{-- Return Item Popup Modal --}}
<div id="returnModal" class="sinfas-modal-backdrop" onclick="handleBackdropClick(event)">
    <div class="sinfas-modal-dialog">
        <div class="sinfas-modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background-color: #eff6ff; display: flex; align-items: center; justify-content: center; color: #1D67F2;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 14 4 9 9 4"/>
                        <path d="M20 20v-7a4 4 0 0 0-4-4H4"/>
                    </svg>
                </div>
                <h3 class="sinfas-modal-title">Form Pengembalian Barang</h3>
            </div>
            <button type="button" class="sinfas-modal-close" onclick="closeReturnModal()">&times;</button>
        </div>

        <div class="sinfas-modal-body">
            {{-- Mini item preview banner --}}
            <div style="display: flex; align-items: center; gap: 0.85rem; padding: 0.75rem 1rem; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 1.25rem;">
                <div id="modalItemImgBox" style="width: 48px; height: 48px; border-radius: 6px; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden;">
                    <img id="modalItemImg" src="" alt="Item" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                    <svg id="modalItemIcon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <div>
                    <h4 id="modalItemName" style="font-size: 0.95rem; font-weight: 600; color: #0f172a; margin: 0 0 0.2rem;"></h4>
                    <p id="modalItemMeta" style="font-size: 0.78rem; color: #64748b; margin: 0;"></p>
                </div>
            </div>

            <form id="modalReturnForm" action="" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Tanggal Pengembalian --}}
                <div class="loan-field-group" style="margin-bottom: 1rem;">
                    <label class="loan-field-label" for="modal_tanggal_kembali">Tanggal Pengembalian <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="tanggal_kembali" id="modal_tanggal_kembali" class="loan-field-input" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                </div>

                {{-- Catatan Kondisi Barang --}}
                <div class="loan-field-group" style="margin-bottom: 1rem;">
                    <label class="loan-field-label" for="modal_catatan">Catatan Kondisi Barang (Opsional)</label>
                    <textarea name="catatan" id="modal_catatan" class="loan-field-textarea" placeholder="Contoh: Barang dalam kondisi lengkap dan bersih, berfungsi dengan baik..." rows="3"></textarea>
                </div>

                {{-- Upload Bukti Foto / Video --}}
                <div class="loan-field-group" style="margin-bottom: 1.25rem;">
                    <label class="loan-field-label">Bukti Foto / Video Kondisi Barang (Opsional)</label>
                    <div class="return-dropzone" id="modalDropzoneBox" onclick="document.getElementById('modal_bukti_file').click()">
                        <input type="file" name="bukti_foto_video" id="modal_bukti_file" accept="image/jpeg,image/png,image/jpg,image/webp,video/mp4" style="display: none;">
                        
                        <div class="dropzone-content" id="modalDropzonePrompt">
                            <div class="dropzone-icon" style="margin-bottom: 0.35rem;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/>
                                    <path d="M12 12v9"/>
                                    <path d="m16 16-4-4-4 4"/>
                                </svg>
                            </div>
                            <p class="dropzone-title" style="font-size: 0.84rem; margin: 0 0 0.15rem;">
                                <span style="color: #1D67F2; font-weight: 600;">Klik untuk unggah</span> atau seret dan lepas file
                            </p>
                            <p class="dropzone-subtitle" style="font-size: 0.72rem;">JPG, PNG, WEBP, atau MP4 (Maks. 10MB)</p>
                        </div>

                        <div class="dropzone-preview" id="modalDropzonePreview" style="display: none; padding: 0.5rem 0.75rem;">
                            <div style="display: flex; align-items: center; gap: 0.65rem;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1D67F2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <div style="text-align: left; overflow: hidden;">
                                    <p id="modalFileName" style="font-size: 0.82rem; font-weight: 600; color: #1f2937; margin: 0; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; max-width: 220px;"></p>
                                    <p id="modalFileSize" style="font-size: 0.72rem; color: #6b7280; margin: 0.1rem 0 0;"></p>
                                </div>
                            </div>
                            <button type="button" onclick="removeModalUpload(event)" style="background: none; border: none; color: #ef4444; font-size: 0.78rem; font-weight: 500; cursor: pointer;">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1rem;">
                    <button type="button" onclick="closeReturnModal()" style="padding: 0.65rem 1.15rem; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: background-color 0.15s ease;">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitModalReturn" class="loan-btn-submit" style="margin: 0; width: auto; padding: 0.65rem 1.4rem;">
                        Ajukan Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .return-dropzone {
        border: 2px dashed #d1d5db;
        border-radius: 10px;
        padding: 1.25rem 1rem;
        text-align: center;
        background-color: #fafbfc;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .return-dropzone:hover, .return-dropzone.dragover {
        border-color: #1D67F2;
        background-color: #eff6ff;
    }
    .dropzone-title {
        color: #374151;
    }
    .dropzone-subtitle {
        color: #9ca3af;
    }
    .dropzone-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }
</style>

<script>
    // Auto dismiss flash message
    const flash = document.getElementById('flash-success');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.3s ease';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 300);
        }, 4000);
    }

    // Return Modal Logic
    const returnModal = document.getElementById('returnModal');
    const modalReturnForm = document.getElementById('modalReturnForm');
    const modalItemName = document.getElementById('modalItemName');
    const modalItemMeta = document.getElementById('modalItemMeta');
    const modalItemImg = document.getElementById('modalItemImg');
    const modalItemIcon = document.getElementById('modalItemIcon');
    let currentItemTitle = '';

    function openReturnModal(kodePinjam, namaBarang, tanggalPinjam, kategori, fotoUrl) {
        currentItemTitle = namaBarang;
        modalReturnForm.action = `/loan-return/${kodePinjam}`;
        modalItemName.textContent = namaBarang;
        modalItemMeta.textContent = `Kode: ${kodePinjam} • Kategori: ${kategori} • Dipinjam: ${tanggalPinjam}`;

        if (fotoUrl) {
            modalItemImg.src = fotoUrl;
            modalItemImg.style.display = 'block';
            modalItemIcon.style.display = 'none';
        } else {
            modalItemImg.style.display = 'none';
            modalItemIcon.style.display = 'block';
        }

        // Reset form inputs
        document.getElementById('modal_tanggal_kembali').value = new Date().toISOString().split('T')[0];
        document.getElementById('modal_catatan').value = '';
        removeModalUpload();

        returnModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeReturnModal() {
        returnModal.classList.remove('show');
        document.body.style.overflow = '';
    }

    function handleBackdropClick(e) {
        if (e.target === returnModal) {
            closeReturnModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && returnModal.classList.contains('show')) {
            closeReturnModal();
        }
    });

    // Dropzone upload handling inside modal
    const modalFileInput = document.getElementById('modal_bukti_file');
    const modalDropzoneBox = document.getElementById('modalDropzoneBox');
    const modalDropzonePrompt = document.getElementById('modalDropzonePrompt');
    const modalDropzonePreview = document.getElementById('modalDropzonePreview');
    const modalFileName = document.getElementById('modalFileName');
    const modalFileSize = document.getElementById('modalFileSize');

    if (modalFileInput) {
        modalFileInput.addEventListener('change', function() {
            handleModalFiles(this.files);
        });
    }

    if (modalDropzoneBox) {
        ['dragenter', 'dragover'].forEach(eventName => {
            modalDropzoneBox.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                modalDropzoneBox.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            modalDropzoneBox.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                modalDropzoneBox.classList.remove('dragover');
            }, false);
        });

        modalDropzoneBox.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                modalFileInput.files = files;
                handleModalFiles(files);
            }
        }, false);
    }

    function handleModalFiles(files) {
        if (files && files[0]) {
            const file = files[0];
            modalFileName.textContent = file.name;
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
            modalFileSize.textContent = sizeInMB + ' MB';
            modalDropzonePrompt.style.display = 'none';
            modalDropzonePreview.style.display = 'flex';
        }
    }

    function removeModalUpload(event) {
        if (event) event.stopPropagation();
        if (modalFileInput) modalFileInput.value = '';
        if (modalDropzonePreview) modalDropzonePreview.style.display = 'none';
        if (modalDropzonePrompt) modalDropzonePrompt.style.display = 'block';
    }

    // Modal submit confirmation
    modalReturnForm.addEventListener('submit', function(e) {
        const confirmed = confirm(`Apakah Anda yakin ingin mengajukan pengembalian untuk alat "${currentItemTitle}"?`);
        if (!confirmed) {
            e.preventDefault();
            return false;
        }

        const btn = document.getElementById('btnSubmitModalReturn');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Mengirim Pengembalian...';
            btn.style.opacity = '0.75';
            btn.style.cursor = 'not-allowed';
        }
    });
</script>
@endsection
