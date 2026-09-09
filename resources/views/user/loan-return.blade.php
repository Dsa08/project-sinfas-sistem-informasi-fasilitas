@extends('layouts.app')

@section('title', 'Pengembalian Barang - ' . ($loan->barang->nama_barang ?? 'Barang') . ' - SINFAS')

@section('navbar_title')
    <a href="{{ route('loan.status') }}" style="color: #4b5563; text-decoration: none; font-size: 0.88rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.3rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m15 18-6-6 6-6"/>
        </svg>
        Kembali ke Status Pengajuan
    </a>
@endsection

@section('content')
<div class="loan-page-wrapper">
    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="flash-msg flash-msg--error" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            {{ session('error') }}
        </div>
    @endif

    <div class="loan-content-grid">
        {{-- Left Column: Item & Borrow Details --}}
        <div class="loan-item-column">
            <div class="loan-image-card">
                @if(!empty($loan->barang->foto) && file_exists(public_path($loan->barang->foto)))
                    <img src="{{ asset($loan->barang->foto) }}" alt="{{ $loan->barang->nama_barang }}" style="width: 100%; height: 100%; max-height: 380px; object-fit: contain; border-radius: 8px;">
                @else
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem 1.5rem; color: #9ca3af;">
                        <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span style="font-size: 0.82rem; color: #9ca3af; margin-top: 0.5rem; font-weight: 500;">Gambar tidak tersedia</span>
                    </div>
                @endif
            </div>

            <div class="loan-info-section">
                <h2 class="loan-product-name">{{ $loan->barang->nama_barang ?? 'Barang' }}</h2>
                <p class="loan-meta-text">Kategori: <span class="loan-meta-val">{{ $loan->barang->kategori->nama_kategori ?? '-' }}</span></p>
                @if($loan->barang->merk_model)
                    <p class="loan-meta-text">Merk/Model: <span class="loan-meta-val">{{ $loan->barang->merk_model }}</span></p>
                @endif
                <p class="loan-meta-text">Kode Peminjaman: <span class="loan-meta-val" style="font-weight: 600; color: #1D67F2;">{{ $loan->kode_pinjam }}</span></p>
                <p class="loan-meta-text">Dipinjam Sejak: <span class="loan-meta-val">{{ $loan->tanggal_pinjam->format('d F Y') }}</span></p>
                @if($loan->lokasi_penggunaan)
                    <p class="loan-meta-text">Lokasi Penggunaan: <span class="loan-meta-val">{{ $loan->lokasi_penggunaan }}</span></p>
                @endif

                <div class="loan-status-wrapper">
                    <span class="loan-status-pill loan-status-pill--available" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background-color: #10b981;"></span>
                        Sedang Dipinjam
                    </span>
                </div>
            </div>
        </div>

        {{-- Right Column: Return Form Card --}}
        <div class="loan-form-column">
            <div class="loan-form-card">
                <h3 class="loan-form-title">Form Pengembalian Barang</h3>

                @if($errors->any())
                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.65rem 0.85rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.82rem;">
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('loan.return.submit', $loan->kode_pinjam) }}" method="POST" enctype="multipart/form-data" class="loan-request-form" id="loanReturnForm">
                    @csrf

                    {{-- Tanggal Pengembalian --}}
                    <div class="loan-field-group">
                        <label class="loan-field-label" for="tanggal_kembali">Tanggal Pengembalian <span style="color: #ef4444;">*</span></label>
                        <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="loan-field-input" value="{{ old('tanggal_kembali', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                    </div>

                    {{-- Catatan Kondisi Barang --}}
                    <div class="loan-field-group">
                        <label class="loan-field-label" for="catatan">Catatan Kondisi Barang (Opsional)</label>
                        <textarea name="catatan" id="catatan" class="loan-field-textarea" placeholder="Contoh: Barang dalam kondisi lengkap dan bersih, berfungsi dengan baik..." rows="3">{{ old('catatan') }}</textarea>
                    </div>

                    {{-- Upload Bukti Foto / Video --}}
                    <div class="loan-field-group">
                        <label class="loan-field-label">Bukti Foto / Video Kondisi Barang (Opsional)</label>
                        <div class="return-dropzone" id="dropzoneBox" onclick="document.getElementById('bukti_foto_video').click()">
                            <input type="file" name="bukti_foto_video" id="bukti_foto_video" accept="image/jpeg,image/png,image/jpg,image/webp,video/mp4" style="display: none;">
                            
                            <div class="dropzone-content" id="dropzonePrompt">
                                <div class="dropzone-icon">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/>
                                        <path d="M12 12v9"/>
                                        <path d="m16 16-4-4-4 4"/>
                                    </svg>
                                </div>
                                <p class="dropzone-title">
                                    <span style="color: #1D67F2; font-weight: 600;">Klik untuk unggah</span> atau seret dan lepas file
                                </p>
                                <p class="dropzone-subtitle">Mendukung file JPG, PNG, WEBP, atau MP4 (Maks. 10MB)</p>
                            </div>

                            <div class="dropzone-preview" id="dropzonePreview" style="display: none;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1D67F2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                    </svg>
                                    <div style="text-align: left; overflow: hidden;">
                                        <p id="previewFileName" style="font-size: 0.85rem; font-weight: 600; color: #1f2937; margin: 0; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; max-width: 250px;"></p>
                                        <p id="previewFileSize" style="font-size: 0.75rem; color: #6b7280; margin: 0.15rem 0 0;"></p>
                                    </div>
                                </div>
                                <button type="button" id="btnRemoveFile" onclick="removeUpload(event)" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; font-weight: 500; cursor: pointer; padding: 0.25rem 0.5rem;">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" id="btnSubmitReturn" class="loan-btn-submit" style="margin-top: 0.75rem;">
                        Ajukan Pengembalian
                    </button>

                    <p class="loan-form-note">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 0.2rem;"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        Setelah diajukan, Admin Sarana akan memeriksa kondisi fisik barang dan melakukan konfirmasi penerimaan.
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .return-dropzone {
        border: 2px dashed #d1d5db;
        border-radius: 10px;
        padding: 1.5rem 1rem;
        text-align: center;
        background-color: #fafbfc;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .return-dropzone:hover, .return-dropzone.dragover {
        border-color: #1D67F2;
        background-color: #eff6ff;
    }
    .dropzone-icon {
        margin-bottom: 0.5rem;
    }
    .dropzone-title {
        font-size: 0.88rem;
        color: #374151;
        margin: 0 0 0.25rem;
    }
    .dropzone-subtitle {
        font-size: 0.76rem;
        color: #9ca3af;
        margin: 0;
    }
    .dropzone-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.6rem 0.85rem;
    }
</style>

<script>
    // File upload handling
    const fileInput = document.getElementById('bukti_foto_video');
    const dropzoneBox = document.getElementById('dropzoneBox');
    const dropzonePrompt = document.getElementById('dropzonePrompt');
    const dropzonePreview = document.getElementById('dropzonePreview');
    const previewFileName = document.getElementById('previewFileName');
    const previewFileSize = document.getElementById('previewFileSize');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });
    }

    if (dropzoneBox) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzoneBox.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzoneBox.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzoneBox.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzoneBox.classList.remove('dragover');
            }, false);
        });

        dropzoneBox.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFiles(files);
            }
        }, false);
    }

    function handleFiles(files) {
        if (files && files[0]) {
            const file = files[0];
            previewFileName.textContent = file.name;
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
            previewFileSize.textContent = sizeInMB + ' MB';
            dropzonePrompt.style.display = 'none';
            dropzonePreview.style.display = 'flex';
        }
    }

    function removeUpload(event) {
        event.stopPropagation();
        fileInput.value = '';
        dropzonePreview.style.display = 'none';
        dropzonePrompt.style.display = 'block';
    }

    // Confirmation Popup on Submit
    document.getElementById('loanReturnForm')?.addEventListener('submit', function(e) {
        const confirmed = confirm('Apakah Anda yakin ingin mengajukan pengembalian untuk alat "{{ addslashes($loan->barang->nama_barang ?? 'Barang') }}"?');
        if (!confirmed) {
            e.preventDefault();
            return false;
        }

        const btn = document.getElementById('btnSubmitReturn');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Mengirim Pengembalian...';
            btn.style.opacity = '0.75';
            btn.style.cursor = 'not-allowed';
        }
    });
</script>
@endsection
