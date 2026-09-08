{{-- 
  HALAMAN VERIFIKASI PEMINJAMAN & PENGEMBALIAN — SINFAS
  File: resources/views/admin/verifications.blade.php
  Fitur:
  - Tab 1 (Pending Requests): Daftar antrean permohonan pinjam baru, aksi Setujui (Approve), dan Modal Tolak dengan input Alasan Penolakan.
  - Tab 2 (Pending Returns): Daftar permohonan pengembalian, preview bukti foto/video, dan Modal Konfirmasi Pengembalian dengan seleksi kondisi fisik (Baik, Kurang Baik, Rusak Berat).
  - Sinkronisasi stok otomatis saat pengembalian berhasil diverifikasi.
--}}
@extends('layouts.admin-sarana')

@section('title', 'Verifikasi Peminjaman & Pengembalian - Admin Sarana SINFAS')
@section('page_title', 'Verifikasi Peminjaman & Pengembalian')

@section('content')
<div class="sarana-verifications-container">
    {{-- Toast Notification / Flash Pop-up --}}
    @if(session('success'))
        <div class="toast-popup toast-popup--success" id="action-toast">
            <div class="toast-icon-wrapper">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="toast-text">
                <div class="toast-title">Aksi Berhasil Disimpan</div>
                <div class="toast-msg">{{ session('success') }}</div>
            </div>
            <button type="button" class="toast-close-btn" onclick="dismissToast()">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="toast-popup toast-popup--error" id="action-toast">
            <div class="toast-icon-wrapper toast-icon-wrapper--error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div class="toast-text">
                <div class="toast-title">Terjadi Kendala</div>
                <div class="toast-msg">{{ session('error') }}</div>
            </div>
            <button type="button" class="toast-close-btn" onclick="dismissToast()">&times;</button>
        </div>
    @endif

    {{-- Tab Navigation Bar --}}
    <div class="sarana-tab-bar" id="verification-tabs">
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'requests' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-requests" data-tab="requests">
            Pending Requests
        </button>
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'returns' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-returns" data-tab="returns">
            Pending Returns
        </button>
    </div>

    {{-- Tab 1: Pending Requests Section --}}
    <div class="sarana-tab-content {{ $activeTab === 'requests' ? 'sarana-tab-content--active' : '' }}" id="tab-content-requests">
        <div class="system-section-header">
            <h2 class="sarana-section-heading">Pending Requests</h2>
        </div>

        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">Borrower</th>
                        <th style="width: 22%;">Item</th>
                        <th style="width: 15%;">Location</th>
                        <th style="width: 15%;">Reason</th>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 18%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $req)
                    <tr>
                        <td class="td-name">{{ $req->siswa->nama ?? '-' }}</td>
                        <td class="td-item">{{ $req->barang->nama_barang ?? '-' }}</td>
                        <td class="td-location">{{ $req->lokasi_penggunaan ?? '-' }}</td>
                        <td class="td-category" style="font-size: 0.82rem;">{{ Str::limit($req->keterangan_penggunaan, 30) ?? '-' }}</td>
                        <td class="td-date">{{ $req->tanggal_pinjam->format('Y-m-d') }}</td>
                        <td>
                            <div class="action-btn-group">
                                <button 
                                    type="button" 
                                    class="btn-action btn-approve"
                                    onclick="openApproveModal('{{ $req->kode_pinjam }}', '{{ addslashes($req->siswa->nama ?? 'Siswa') }}', '{{ addslashes($req->barang->nama_barang ?? 'Barang') }}')">
                                    Approve
                                </button>
                                <button 
                                    type="button" 
                                    class="btn-action btn-reject"
                                    onclick="openRejectModal('{{ $req->kode_pinjam }}')">
                                    Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #9ca3af; padding: 2rem;">Tidak ada permintaan peminjaman yang menunggu.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($pendingRequests->hasPages())
        <div class="system-pagination-bar">
            {{ $pendingRequests->links('vendor.pagination.simple-default') }}
        </div>
        @endif
    </div>

    {{-- Tab 2: Pending Returns Section --}}
    <div class="sarana-tab-content {{ $activeTab === 'returns' ? 'sarana-tab-content--active' : '' }}" id="tab-content-returns">
        <div class="system-section-header">
            <h2 class="sarana-section-heading">Pending Returns</h2>
        </div>

        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 22%;">Borrower</th>
                        <th style="width: 28%;">Item</th>
                        <th style="width: 15%;">Return Date</th>
                        <th style="width: 10%; text-align: center;">Evidence</th>
                        <th style="width: 13%;">Condition</th>
                        <th style="width: 12%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingReturns as $ret)
                    <tr>
                        <td class="td-name">{{ $ret->siswa->nama ?? '-' }}</td>
                        <td class="td-item">
                            {{ $ret->barang->nama_barang ?? '-' }}
                            @if(!empty($ret->pengembalian->catatan))
                                <div style="font-size: 0.76rem; color: #6b7280; margin-top: 0.25rem; font-style: italic; line-height: 1.3;">
                                    <span style="font-weight: 600; color: #4b5563; font-style: normal;">Catatan:</span> "{{ $ret->pengembalian->catatan }}"
                                </div>
                            @endif
                        </td>
                        <td class="td-date">{{ $ret->pengembalian->tanggal_kembali->format('Y-m-d') }}</td>
                        <td style="text-align: center;">
                            @if($ret->pengembalian->bukti_foto_video)
                                @php
                                    $rawBukti = $ret->pengembalian->bukti_foto_video;
                                    $buktiUrl = str_starts_with($rawBukti, 'uploads/') 
                                        ? asset($rawBukti) 
                                        : (str_starts_with($rawBukti, 'http') ? $rawBukti : asset('storage/' . $rawBukti));
                                @endphp
                                <button type="button" class="btn-evidence" title="Lihat Bukti Foto/Video" onclick="window.open('{{ $buktiUrl }}', '_blank')">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-1.96-2.36L6.5 17h11l-3.54-4.71z"/>
                                    </svg>
                                </button>
                            @else
                                <span style="color: #9ca3af; font-size: 0.82rem;">-</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.verifications.confirm-return', $ret->kode_pinjam) }}" method="POST" class="confirm-return-form" style="display: flex; align-items: center; gap: 0.5rem;">
                                @csrf
                                <select name="kondisi_barang" class="sarana-select-condition" required>
                                    <option value="Baik">Baik</option>
                                    <option value="Kurang Baik">Kurang Baik</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                        </td>
                        <td>
                                <button type="submit" class="btn-confirm-return" onclick="return confirm('Konfirmasi pengembalian ini?')">Confirm</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #9ca3af; padding: 2rem;">Tidak ada pengembalian yang menunggu konfirmasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($pendingReturns->hasPages())
        <div class="system-pagination-bar">
            {{ $pendingReturns->links('vendor.pagination.simple-default') }}
        </div>
        @endif
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL 1: REJECT REQUEST MODAL (Sesuai Desain Mockup)          --}}
{{-- ============================================================ --}}
<div class="verification-modal-overlay" id="reject-modal-overlay" style="display: none;">
    <div class="verification-modal-card" id="reject-modal-card">
        <h3 class="verification-modal-title">Reject Request</h3>
        
        <form id="reject-request-form" method="POST" action="">
            @csrf
            <div class="verification-modal-form-group">
                <label for="alasan_penolakan" class="verification-modal-label">Alasan Penolakan (opsional)</label>
                <textarea 
                    name="alasan_penolakan" 
                    id="alasan_penolakan" 
                    class="verification-modal-textarea" 
                    rows="4" 
                    placeholder="Tuliskan alasan jika perlu..."></textarea>
            </div>

            <div class="verification-modal-actions">
                <button type="button" class="verification-btn-cancel" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="verification-btn-reject">Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL 2: APPROVE REQUEST MODAL (Sesuai Desain Mockup)         --}}
{{-- ============================================================ --}}
<div class="verification-modal-overlay" id="approve-modal-overlay" style="display: none;">
    <div class="verification-modal-card" id="approve-modal-card">
        <h3 class="verification-modal-title">Konfirmasi Persetujuan</h3>
        
        <div class="verification-modal-body">
            <p class="verification-modal-question">Yakin ingin menyetujui pengajuan ini?</p>
            <div class="verification-modal-meta">
                <p>Peminjam: <span id="approve-borrower-name">-</span></p>
                <p>Barang: <span id="approve-item-name">-</span></p>
            </div>
        </div>

        <form id="approve-request-form" method="POST" action="">
            @csrf
            <div class="verification-modal-actions">
                <button type="button" class="verification-btn-cancel" onclick="closeApproveModal()">Cancel</button>
                <button type="submit" class="verification-btn-approve">Ya, Setujui</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* --- Floating Toast Notification --- */
    .toast-popup {
        position: fixed;
        top: 1.5rem;
        right: 1.5rem;
        z-index: 9999;
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        background: #ffffff;
        padding: 1rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12), 0 1px 3px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
        max-width: 420px;
        animation: toastSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .toast-popup--success {
        border-left: 4px solid #16a34a;
    }
    .toast-popup--error {
        border-left: 4px solid #dc2626;
    }
    .toast-icon-wrapper {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #dcfce7;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .toast-icon-wrapper--error {
        background: #fee2e2;
    }
    .toast-text {
        flex: 1;
    }
    .toast-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.2rem;
    }
    .toast-msg {
        font-size: 0.84rem;
        color: #475569;
        line-height: 1.4;
    }
    .toast-close-btn {
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 1.25rem;
        cursor: pointer;
        line-height: 1;
        padding: 0;
        margin-left: 0.5rem;
        transition: color 0.15s ease;
    }
    .toast-close-btn:hover {
        color: #334155;
    }
    @keyframes toastSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* --- Verification Modals (Exact Mockup Match) --- */
    .verification-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(3px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }
    .verification-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .verification-modal-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 1.75rem 2rem;
        width: 100%;
        max-width: 440px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        transform: scale(0.95);
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-sizing: border-box;
    }
    .verification-modal-overlay.active .verification-modal-card {
        transform: scale(1);
    }
    .verification-modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 1rem 0;
        line-height: 1.3;
    }
    .verification-modal-form-group {
        margin: 1.25rem 0 1.5rem;
    }
    .verification-modal-label {
        display: block;
        font-size: 0.9rem;
        color: #475569;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    .verification-modal-textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.75rem 0.85rem;
        font-family: inherit;
        font-size: 0.9rem;
        color: #1e293b;
        box-sizing: border-box;
        resize: vertical;
        outline: none;
        min-height: 90px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .verification-modal-textarea:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .verification-modal-question {
        font-size: 0.95rem;
        color: #1e293b;
        margin: 0 0 0.65rem;
        font-weight: 500;
    }
    .verification-modal-meta {
        margin-bottom: 1.75rem;
    }
    .verification-modal-meta p {
        margin: 0.25rem 0;
        font-size: 0.9rem;
        color: #64748b;
    }
    .verification-modal-meta span {
        color: #1e293b;
        font-weight: 600;
    }
    .verification-modal-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }
    .verification-btn-cancel {
        padding: 0.6rem 1.4rem;
        background: #ffffff;
        border: 1px solid #d1d5db;
        color: #4b5563;
        font-size: 0.9rem;
        font-weight: 500;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .verification-btn-cancel:hover {
        background: #f8fafc;
        border-color: #9ca3af;
        color: #1f2937;
    }
    .verification-btn-approve {
        padding: 0.6rem 1.4rem;
        background: #16a34a;
        border: 1px solid #16a34a;
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .verification-btn-approve:hover {
        background: #15803d;
        border-color: #15803d;
    }
    .verification-btn-reject {
        padding: 0.6rem 1.4rem;
        background: #dc2626;
        border: 1px solid #dc2626;
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .verification-btn-reject:hover {
        background: #b91c1c;
        border-color: #b91c1c;
    }
</style>

{{-- Interactive Scripts: Tab Switching & Modals --}}
<script>
    // Tab switching
    document.addEventListener('DOMContentLoaded', function () {
        const tabBtnRequests = document.getElementById('tab-btn-requests');
        const tabBtnReturns = document.getElementById('tab-btn-returns');
        const tabContentRequests = document.getElementById('tab-content-requests');
        const tabContentReturns = document.getElementById('tab-content-returns');

        if (tabBtnRequests && tabBtnReturns) {
            tabBtnRequests.addEventListener('click', function () {
                tabBtnRequests.classList.add('sarana-tab-btn--active');
                tabBtnReturns.classList.remove('sarana-tab-btn--active');
                tabContentRequests.classList.add('sarana-tab-content--active');
                tabContentReturns.classList.remove('sarana-tab-content--active');
            });

            tabBtnReturns.addEventListener('click', function () {
                tabBtnReturns.classList.add('sarana-tab-btn--active');
                tabBtnRequests.classList.remove('sarana-tab-btn--active');
                tabContentReturns.classList.add('sarana-tab-content--active');
                tabContentRequests.classList.remove('sarana-tab-content--active');
            });
        }

        // Auto dismiss toast after 4.5s
        const toast = document.getElementById('action-toast');
        if (toast) {
            setTimeout(dismissToast, 4500);
        }
    });

    // Dismiss toast function
    function dismissToast() {
        const toast = document.getElementById('action-toast');
        if (toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-15px) scale(0.95)';
            setTimeout(() => toast.remove(), 300);
        }
    }

    // Modal Approve Functions
    function openApproveModal(kodePinjam, borrowerName, itemName) {
        const form = document.getElementById('approve-request-form');
        form.action = "{{ url('/admin/verifications') }}/" + encodeURIComponent(kodePinjam) + "/approve";
        document.getElementById('approve-borrower-name').textContent = borrowerName;
        document.getElementById('approve-item-name').textContent = itemName;

        const overlay = document.getElementById('approve-modal-overlay');
        overlay.style.display = 'flex';
        // Force reflow for animation
        void overlay.offsetWidth;
        overlay.classList.add('active');
    }

    function closeApproveModal() {
        const overlay = document.getElementById('approve-modal-overlay');
        overlay.classList.remove('active');
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 200);
    }

    // Modal Reject Functions
    function openRejectModal(kodePinjam) {
        const form = document.getElementById('reject-request-form');
        form.action = "{{ url('/admin/verifications') }}/" + encodeURIComponent(kodePinjam) + "/reject";
        document.getElementById('alasan_penolakan').value = '';

        const overlay = document.getElementById('reject-modal-overlay');
        overlay.style.display = 'flex';
        void overlay.offsetWidth;
        overlay.classList.add('active');
        document.getElementById('alasan_penolakan').focus();
    }

    function closeRejectModal() {
        const overlay = document.getElementById('reject-modal-overlay');
        overlay.classList.remove('active');
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 200);
    }

    // Close on overlay click or Escape key
    window.addEventListener('click', function (e) {
        const approveOverlay = document.getElementById('approve-modal-overlay');
        const rejectOverlay = document.getElementById('reject-modal-overlay');
        if (e.target === approveOverlay) closeApproveModal();
        if (e.target === rejectOverlay) closeRejectModal();
    });

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeApproveModal();
            closeRejectModal();
        }
    });
</script>
@endsection
