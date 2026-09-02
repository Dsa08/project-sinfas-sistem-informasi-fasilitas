@extends('layouts.admin-sarana')

@section('title', 'Dashboard - Admin Sarana SINFAS')
@section('page_title', 'Home')

@section('content')
<div class="sarana-dashboard-container">
    {{-- 4 Stat Cards --}}
    <div class="sarana-stats-grid">
        {{-- Card 1: Pending Verification --}}
        <div class="system-stat-card">
            <div class="system-stat-value">5</div>
            <div class="system-stat-label">Pending Verification</div>
        </div>

        {{-- Card 2: Total Items --}}
        <div class="system-stat-card">
            <div class="system-stat-value">42</div>
            <div class="system-stat-label">Total Items</div>
        </div>

        {{-- Card 3: Currently Borrowed --}}
        <div class="system-stat-card">
            <div class="system-stat-value">12</div>
            <div class="system-stat-label">Currently Borrowed</div>
        </div>

        {{-- Card 4: Damaged (Highlighted in Amber/Orange) --}}
        <div class="system-stat-card">
            <div class="system-stat-value text-amber-500" style="color: #f59e0b;">3</div>
            <div class="system-stat-label text-amber-600" style="color: #d97706; font-weight: 600;">Damaged</div>
        </div>
    </div>

    {{-- Pending Loan Requests Section --}}
    <div class="sarana-section" style="margin-top: 1.5rem;">
        <h2 class="sarana-section-heading">Pending Loan Requests</h2>
        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Borrower</th>
                        <th style="width: 35%;">Item</th>
                        <th style="width: 20%;">Date</th>
                        <th style="width: 20%; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="td-name">Ahmad Fadli</td>
                        <td class="td-item">Projector Epson X300</td>
                        <td class="td-date">2024-03-15</td>
                        <td>
                            <div class="action-btn-group" style="justify-content: center;">
                                <button type="button" class="btn-action btn-approve" onclick="openApproveModal('Ahmad Fadli', 'Projector Epson X300')">Approve</button>
                                <button type="button" class="btn-action btn-reject" onclick="openRejectModal('Ahmad Fadli', 'Projector Epson X300')">Reject</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="td-name">Siti Nurhaliza</td>
                        <td class="td-item">Portable Speaker</td>
                        <td class="td-date">2024-03-15</td>
                        <td>
                            <div class="action-btn-group" style="justify-content: center;">
                                <button type="button" class="btn-action btn-approve" onclick="openApproveModal('Siti Nurhaliza', 'Portable Speaker')">Approve</button>
                                <button type="button" class="btn-action btn-reject" onclick="openRejectModal('Siti Nurhaliza', 'Portable Speaker')">Reject</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="td-name">Budi Santoso</td>
                        <td class="td-item">Folding Table (x3)</td>
                        <td class="td-date">2024-03-14</td>
                        <td>
                            <div class="action-btn-group" style="justify-content: center;">
                                <button type="button" class="btn-action btn-approve" onclick="openApproveModal('Budi Santoso', 'Folding Table (x3)')">Approve</button>
                                <button type="button" class="btn-action btn-reject" onclick="openRejectModal('Budi Santoso', 'Folding Table (x3)')">Reject</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Grouped Bar Chart Section --}}
    <div class="sarana-section" style="margin-top: 2rem;">
        <div class="chart-container-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 0.85rem; font-weight: 600; color: #94a3b8; margin-bottom: 0.5rem; letter-spacing: 0.05em;">
                BarLineChart
            </div>
            <div style="position: relative; height: 320px; width: 100%;">
                <canvas id="adminLoanChart"></canvas>
            </div>
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
        <div class="modal-actions" style="justify-content: flex-end; gap: 0.75rem;">
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
        <div class="modal-actions" style="justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="modal-btn modal-btn--cancel" onclick="closeRejectModal()">Cancel</button>
            <button type="button" class="modal-btn" style="background-color: #dc2626; color: #ffffff; border: none; border-radius: 8px; padding: 0.55rem 1.25rem; font-weight: 600;" onclick="confirmRejectAction()">Confirm Reject</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('adminLoanChart');
        if (ctx && window.Chart) {
            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                    datasets: [
                        {
                            label: 'Kabel HDMI 10 Meter',
                            data: [28, 36, 42, 30, 45, 50],
                            backgroundColor: '#fb7185',
                            borderRadius: 3,
                            barPercentage: 0.85,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Kamera DSLR Canon 3000D',
                            data: [45, 52, 60, 48, 55, 68],
                            backgroundColor: '#38bdf8',
                            borderRadius: 3,
                            barPercentage: 0.85,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Wireless Presenter Laser',
                            data: [15, 22, 30, 18, 25, 34],
                            backgroundColor: '#fbbf24',
                            borderRadius: 3,
                            barPercentage: 0.85,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Microphone Wireless Clip-on',
                            data: [32, 40, 38, 35, 42, 48],
                            backgroundColor: '#60a5fa',
                            borderRadius: 3,
                            barPercentage: 0.85,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Tripod Kamera Takara',
                            data: [18, 28, 34, 25, 38, 43],
                            backgroundColor: '#4ade80',
                            borderRadius: 3,
                            barPercentage: 0.85,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Speaker',
                            data: [22, 30, 36, 28, 40, 42],
                            backgroundColor: '#a855f7',
                            borderRadius: 3,
                            barPercentage: 0.85,
                            categoryPercentage: 0.8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 16,
                                font: {
                                    family: 'Inter',
                                    size: 11
                                },
                                color: '#4b5563'
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            titleFont: { family: 'Inter', size: 12 },
                            bodyFont: { family: 'Inter', size: 12 },
                            padding: 10,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                stepSize: 20,
                                color: '#6b7280',
                                font: { family: 'Inter', size: 11 }
                            },
                            grid: {
                                color: '#f1f5f9',
                                borderDash: [4, 4]
                            },
                            border: {
                                dash: [4, 4]
                            }
                        },
                        x: {
                            ticks: {
                                color: '#6b7280',
                                font: { family: 'Inter', size: 12 }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
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
        alert('Pengajuan peminjaman telah disetujui.');
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
        alert('Pengajuan peminjaman telah ditolak.');
    }
</script>
@endsection
