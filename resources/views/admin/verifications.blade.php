@extends('layouts.admin-sarana')

@section('title', 'Verifikasi Peminjaman & Pengembalian - Admin Sarana SINFAS')
@section('page_title', 'Verifikasi Peminjaman & Pengembalian')

@section('content')
<div class="sarana-verifications-container">
    {{-- Tab Navigation Bar --}}
    <div class="sarana-tab-bar" id="verification-tabs" style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
        <button type="button" class="sarana-tab-btn sarana-tab-btn--active" id="tab-btn-requests" data-tab="requests">
            Pending Requests
        </button>
        <button type="button" class="sarana-tab-btn" id="tab-btn-returns" data-tab="returns">
            Pending Returns
        </button>
    </div>

    {{-- Tab 1: Pending Requests Section --}}
    <div class="sarana-tab-content sarana-tab-content--active" id="tab-content-requests">
        <div class="system-section-header" style="margin-bottom: 1rem;">
            <h2 class="sarana-section-heading" style="font-size: 1.15rem; font-weight: 700; color: #111827;">Pending Requests</h2>
        </div>

        <div class="system-table-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <table class="system-table" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                <thead>
                    <tr style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb; color: #4b5563; font-weight: 600; text-align: left;">
                        <th style="padding: 0.85rem 1rem;">Borrower</th>
                        <th style="padding: 0.85rem 1rem;">Item</th>
                        <th style="padding: 0.85rem 1rem;">Location</th>
                        <th style="padding: 0.85rem 1rem;">Reason</th>
                        <th style="padding: 0.85rem 1rem;">Date</th>
                        <th style="padding: 0.85rem 1rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Row 1: Ahmad Fadli --}}
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 0.85rem 1rem; font-weight: 500; color: #111827;">Ahmad Fadli</td>
                        <td style="padding: 0.85rem 1rem; color: #374151;">Projector Epson X300</td>
                        <td style="padding: 0.85rem 1rem; color: #4b5563;">Ruang 31</td>
                        <td style="padding: 0.85rem 1rem; color: #6b7280; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Projector kelas rusa...</td>
                        <td style="padding: 0.85rem 1rem; color: #4b5563;">2024-03-15</td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <button type="button" class="btn-action btn-approve" onclick="openApproveModal('Ahmad Fadli', 'Projector Epson X300')">Approve</button>
                                <button type="button" class="btn-action btn-reject" onclick="openRejectModal('Ahmad Fadli', 'Projector Epson X300')">Reject</button>
                            </div>
                        </td>
                    </tr>

                    {{-- Row 2: Siti Nurhaliza --}}
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 0.85rem 1rem; font-weight: 500; color: #111827;">Siti Nurhaliza</td>
                        <td style="padding: 0.85rem 1rem; color: #374151;">Portable Speaker</td>
                        <td style="padding: 0.85rem 1rem; color: #4b5563;">Ruang 1</td>
                        <td style="padding: 0.85rem 1rem; color: #6b7280; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Butuh speaker untu...</td>
                        <td style="padding: 0.85rem 1rem; color: #4b5563;">2024-03-16</td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <button type="button" class="btn-action btn-approve" onclick="openApproveModal('Siti Nurhaliza', 'Portable Speaker')">Approve</button>
                                <button type="button" class="btn-action btn-reject" onclick="openRejectModal('Siti Nurhaliza', 'Portable Speaker')">Reject</button>
                            </div>
                        </td>
                    </tr>

                    {{-- Row 3: Budi Santoso --}}
                    <tr>
                        <td style="padding: 0.85rem 1rem; font-weight: 500; color: #111827;">Budi Santoso</td>
                        <td style="padding: 0.85rem 1rem; color: #374151;">Folding Table (x3)</td>
                        <td style="padding: 0.85rem 1rem; color: #4b5563;">Ruang 7</td>
                        <td style="padding: 0.85rem 1rem; color: #6b7280; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Kebutuhan praktek...</td>
                        <td style="padding: 0.85rem 1rem; color: #4b5563;">2024-03-16</td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <button type="button" class="btn-action btn-approve" onclick="openApproveModal('Budi Santoso', 'Folding Table (x3)')">Approve</button>
                                <button type="button" class="btn-action btn-reject" onclick="openRejectModal('Budi Santoso', 'Folding Table (x3)')">Reject</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="system-pagination-bar" style="display: flex; justify-content: flex-end; align-items: center; gap: 0.35rem; margin-top: 1.25rem;">
            <button class="pagination-btn pagination-btn--disabled">Prev</button>
            <button class="pagination-btn pagination-btn--active">1</button>
            <button class="pagination-btn">Next</button>
        </div>
    </div>

    {{-- Tab 2: Pending Returns Section --}}
    <div class="sarana-tab-content" id="tab-content-returns" style="display: none;">
        <div class="system-section-header" style="margin-bottom: 1rem;">
            <h2 class="sarana-section-heading" style="font-size: 1.15rem; font-weight: 700; color: #111827;">Pending Returns</h2>
        </div>

        <div class="system-table-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <table class="system-table" style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                <thead>
                    <tr style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb; color: #4b5563; font-weight: 600; text-align: left;">
                        <th style="padding: 0.85rem 1rem;">Borrower</th>
                        <th style="padding: 0.85rem 1rem;">Item</th>
                        <th style="padding: 0.85rem 1rem;">Return Date</th>
                        <th style="padding: 0.85rem 1rem; text-align: center;">Evidence</th>
                        <th style="padding: 0.85rem 1rem;">Condition</th>
                        <th style="padding: 0.85rem 1rem; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Row 1: Ahmad Fadli --}}
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 0.85rem 1rem; font-weight: 500; color: #111827;">Ahmad Fadli</td>
                        <td style="padding: 0.85rem 1rem; color: #374151;">Projector Epson X300</td>
                        <td style="padding: 0.85rem 1rem; color: #4b5563;">2024-03-18</td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            <div style="display: inline-flex; gap: 0.4rem; justify-content: center;">
                                {{-- Photo evidence icon --}}
                                <button type="button" class="btn-evidence-icon" onclick="openEvidenceModal('Foto Bukti Pengembalian', 'image')" title="Lihat Foto Bukti" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.35rem; cursor: pointer; color: #334155;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                        <circle cx="9" cy="9" r="2"/>
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                    </svg>
                                </button>
                                {{-- Video evidence icon --}}
                                <button type="button" class="btn-evidence-icon" onclick="openEvidenceModal('Video Bukti Pengembalian', 'video')" title="Lihat Video Bukti" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.35rem; cursor: pointer; color: #334155;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2"/>
                                        <path d="M7 3v18"/>
                                        <path d="M3 7.5h4"/>
                                        <path d="M3 12h18"/>
                                        <path d="M3 16.5h4"/>
                                        <path d="M17 3v18"/>
                                        <path d="M17 7.5h4"/>
                                        <path d="M17 16.5h4"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td style="padding: 0.85rem 1rem;">
                            <select class="sarana-select-condition" style="padding: 0.4rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.85rem; outline: none; background: #ffffff;">
                                <option value="Baik" selected>Baik</option>
                                <option value="Kurang Baik">Kurang Baik</option>
                                <option value="Rusak berat">Rusak berat</option>
                            </select>
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            <button type="button" class="btn-confirm-return" onclick="confirmReturnAction('Ahmad Fadli', 'Projector Epson X300')" style="background-color: #16a34a; color: #ffffff; border: none; border-radius: 6px; padding: 0.45rem 1.1rem; font-weight: 600; cursor: pointer; font-size: 0.82rem;">Confirm</button>
                        </td>
                    </tr>

                    {{-- Row 2: Dewi Lestari --}}
                    <tr>
                        <td style="padding: 0.85rem 1rem; font-weight: 500; color: #111827;">Dewi Lestari</td>
                        <td style="padding: 0.85rem 1rem; color: #374151;">Whiteboard 120cm</td>
                        <td style="padding: 0.85rem 1rem; color: #4b5563;">2024-03-19</td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            <div style="display: inline-flex; gap: 0.4rem; justify-content: center;">
                                <button type="button" class="btn-evidence-icon" onclick="openEvidenceModal('Foto Bukti Pengembalian', 'image')" title="Lihat Foto Bukti" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.35rem; cursor: pointer; color: #334155;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                        <circle cx="9" cy="9" r="2"/>
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                    </svg>
                                </button>
                                <button type="button" class="btn-evidence-icon" onclick="openEvidenceModal('Video Bukti Pengembalian', 'video')" title="Lihat Video Bukti" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.35rem; cursor: pointer; color: #334155;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2"/>
                                        <path d="M7 3v18"/>
                                        <path d="M3 7.5h4"/>
                                        <path d="M3 12h18"/>
                                        <path d="M3 16.5h4"/>
                                        <path d="M17 3v18"/>
                                        <path d="M17 7.5h4"/>
                                        <path d="M17 16.5h4"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td style="padding: 0.85rem 1rem;">
                            <select class="sarana-select-condition" style="padding: 0.4rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.85rem; outline: none; background: #ffffff;">
                                <option value="Baik" selected>Baik</option>
                                <option value="Kurang Baik">Kurang Baik</option>
                                <option value="Rusak berat">Rusak berat</option>
                            </select>
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            <button type="button" class="btn-confirm-return" onclick="confirmReturnAction('Dewi Lestari', 'Whiteboard 120cm')" style="background-color: #16a34a; color: #ffffff; border: none; border-radius: 6px; padding: 0.45rem 1.1rem; font-weight: 600; cursor: pointer; font-size: 0.82rem;">Confirm</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="system-pagination-bar" style="display: flex; justify-content: flex-end; align-items: center; gap: 0.35rem; margin-top: 1.25rem;">
            <button class="pagination-btn pagination-btn--disabled">Prev</button>
            <button class="pagination-btn pagination-btn--active">1</button>
            <button class="pagination-btn">Next</button>
        </div>
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal-overlay" id="approveModal">
    <div class="modal-card" style="max-width: 420px; text-align: left; padding: 1.75rem;">
        <h3 class="modal-title" style="font-size: 1.15rem; font-weight: 700; color: #111827; margin-bottom: 0.75rem;">Konfirmasi Persetujuan</h3>
        <p style="font-size: 0.9rem; color: #4b5563; margin-bottom: 0.75rem;">Yakin ingin menyetujui pengajuan ini?</p>
        <div style="background: #f9fafb; border: 1px solid #f3f4f6; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: #4b5563;">
            <p style="margin: 0 0 0.25rem;">Peminjam: <strong id="approveModalBorrower" style="color: #111827;"></strong></p>
            <p style="margin: 0;">Barang: <strong id="approveModalItem" style="color: #111827;"></strong></p>
        </div>
        <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="modal-btn modal-btn--cancel" onclick="closeApproveModal()">Cancel</button>
            <button type="button" class="modal-btn" style="background-color: #16a34a; color: #ffffff; border: none; border-radius: 8px; padding: 0.55rem 1.25rem; font-weight: 600;" onclick="confirmApproveAction()">Ya, Setujui</button>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-overlay" id="rejectModal">
    <div class="modal-card" style="max-width: 440px; text-align: left; padding: 1.75rem;">
        <h3 class="modal-title" style="font-size: 1.15rem; font-weight: 700; color: #111827; margin-bottom: 0.75rem;">Reject Request</h3>
        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.85rem; font-weight: 500; color: #4b5563; margin-bottom: 0.4rem;">Alasan Penolakan (opsional)</label>
            <textarea id="rejectReasonInput" rows="3" style="width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.6rem 0.8rem; font-family: inherit; font-size: 0.85rem; outline: none;" placeholder="Tuliskan alasan jika perlu..."></textarea>
        </div>
        <div class="modal-actions" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="modal-btn modal-btn--cancel" onclick="closeRejectModal()">Cancel</button>
            <button type="button" class="modal-btn" style="background-color: #dc2626; color: #ffffff; border: none; border-radius: 8px; padding: 0.55rem 1.25rem; font-weight: 600;" onclick="confirmRejectAction()">Confirm Reject</button>
        </div>
    </div>
</div>

{{-- Evidence Modal --}}
<div class="modal-overlay" id="evidenceModal">
    <div class="modal-card" style="max-width: 480px; text-align: center; padding: 1.75rem;">
        <h3 class="modal-title" id="evidenceTitle" style="font-size: 1.15rem; font-weight: 700; color: #111827; margin-bottom: 1rem;">Bukti Pengembalian</h3>
        <div id="evidenceContainer" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; justify-content: center; min-height: 180px; margin-bottom: 1.25rem;">
            <img src="{{ asset('assets/pictures/projector_sample.jpg') }}" alt="Bukti Pengembalian" style="max-width: 100%; max-height: 240px; object-fit: contain; border-radius: 6px;">
        </div>
        <button type="button" class="modal-btn modal-btn--cancel" onclick="closeEvidenceModal()">Tutup</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabBtnRequests = document.getElementById('tab-btn-requests');
        const tabBtnReturns = document.getElementById('tab-btn-returns');
        const tabContentRequests = document.getElementById('tab-content-requests');
        const tabContentReturns = document.getElementById('tab-content-returns');

        if (tabBtnRequests && tabBtnReturns) {
            tabBtnRequests.addEventListener('click', function () {
                tabBtnRequests.classList.add('sarana-tab-btn--active');
                tabBtnReturns.classList.remove('sarana-tab-btn--active');
                tabContentRequests.style.display = 'block';
                tabContentReturns.style.display = 'none';
            });

            tabBtnReturns.addEventListener('click', function () {
                tabBtnReturns.classList.add('sarana-tab-btn--active');
                tabBtnRequests.classList.remove('sarana-tab-btn--active');
                tabContentReturns.style.display = 'block';
                tabContentRequests.style.display = 'none';
            });
        }
    });

    function openApproveModal(borrower, item) {
        document.getElementById('approveModalBorrower').textContent = borrower;
        document.getElementById('approveModalItem').textContent = item;
        document.getElementById('approveModal').classList.add('modal-overlay--active');
    }

    function closeApproveModal() {
        document.getElementById('approveModal').classList.remove('modal-overlay--active');
    }

    function confirmApproveAction() {
        closeApproveModal();
        alert('Pengajuan berhasil disetujui.');
    }

    function openRejectModal(borrower, item) {
        document.getElementById('rejectReasonInput').value = '';
        document.getElementById('rejectModal').classList.add('modal-overlay--active');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.remove('modal-overlay--active');
    }

    function confirmRejectAction() {
        closeRejectModal();
        alert('Pengajuan telah ditolak.');
    }

    function openEvidenceModal(title, type) {
        document.getElementById('evidenceTitle').textContent = title;
        document.getElementById('evidenceModal').classList.add('modal-overlay--active');
    }

    function closeEvidenceModal() {
        document.getElementById('evidenceModal').classList.remove('modal-overlay--active');
    }

    function confirmReturnAction(borrower, item) {
        alert('Pengembalian barang ' + item + ' oleh ' + borrower + ' berhasil diverifikasi!');
    }
</script>
@endsection
