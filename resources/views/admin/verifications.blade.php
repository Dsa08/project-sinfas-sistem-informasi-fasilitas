@extends('layouts.admin-sarana')

@section('title', 'Verifikasi Peminjaman & Pengembalian - Admin Sarana SINFAS')
@section('page_title', 'Verifikasi Peminjaman & Pengembalian')

@section('content')
<div class="sarana-verifications-container">
    {{-- Tab Navigation Bar --}}
    <div class="sarana-tab-bar" id="verification-tabs">
        <button type="button" class="sarana-tab-btn sarana-tab-btn--active" id="tab-btn-requests" data-tab="requests">
            Pending Requests
        </button>
        <button type="button" class="sarana-tab-btn" id="tab-btn-returns" data-tab="returns">
            Pending Returns
        </button>
    </div>

    {{-- Tab 1: Pending Requests Section --}}
    <div class="sarana-tab-content sarana-tab-content--active" id="tab-content-requests">
        <div class="system-section-header">
            <h2 class="sarana-section-heading">Pending Requests</h2>
        </div>

        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 22%;">Borrower</th>
                        <th style="width: 30%;">Item</th>
                        <th style="width: 18%;">Location</th>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Row 1: Ahmad Fadli --}}
                    <tr>
                        <td class="td-name">Ahmad Fadli</td>
                        <td class="td-item">Projector Epson X300</td>
                        <td class="td-location">Ruang 31</td>
                        <td class="td-date">2024-03-15</td>
                        <td>
                            <div class="action-btn-group">
                                <button type="button" class="btn-action btn-approve">Approve</button>
                                <button type="button" class="btn-action btn-reject">Reject</button>
                            </div>
                        </td>
                    </tr>

                    {{-- Row 2: Siti Nurhaliza --}}
                    <tr>
                        <td class="td-name">Siti Nurhaliza</td>
                        <td class="td-item">Portable Speaker</td>
                        <td class="td-location">Ruang 1</td>
                        <td class="td-date">2024-03-16</td>
                        <td>
                            <div class="action-btn-group">
                                <button type="button" class="btn-action btn-approve">Approve</button>
                                <button type="button" class="btn-action btn-reject">Reject</button>
                            </div>
                        </td>
                    </tr>

                    {{-- Row 3: Budi Santoso --}}
                    <tr>
                        <td class="td-name">Budi Santoso</td>
                        <td class="td-item">Folding Table (x3)</td>
                        <td class="td-location">Ruang 7</td>
                        <td class="td-date">2024-03-16</td>
                        <td>
                            <div class="action-btn-group">
                                <button type="button" class="btn-action btn-approve">Approve</button>
                                <button type="button" class="btn-action btn-reject">Reject</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="system-pagination-bar">
            <button class="pagination-btn pagination-btn--disabled">Prev</button>
            <button class="pagination-btn pagination-btn--active">1</button>
            <button class="pagination-btn">Next</button>
        </div>
    </div>

    {{-- Tab 2: Pending Returns Section --}}
    <div class="sarana-tab-content" id="tab-content-returns">
        <div class="system-section-header">
            <h2 class="sarana-section-heading">Pending Returns</h2>
        </div>

        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 22%;">Borrower</th>
                        <th style="width: 28%;">Item</th>
                        <th style="width: 18%;">Return Date</th>
                        <th style="width: 10%; text-align: center;">Evidence</th>
                        <th style="width: 12%;">Condition</th>
                        <th style="width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Row 1: Ahmad Fadli --}}
                    <tr>
                        <td class="td-name">Ahmad Fadli</td>
                        <td class="td-item">Projector Epson X300</td>
                        <td class="td-date">2024-03-18</td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-evidence" title="Lihat Bukti Foto">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-1.96-2.36L6.5 17h11l-3.54-4.71z"/>
                                </svg>
                            </button>
                        </td>
                        <td>
                            <select class="sarana-select-condition">
                                <option value="good" selected>Good</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn-confirm-return">Confirm</button>
                        </td>
                    </tr>

                    {{-- Row 2: Dewi Lestari --}}
                    <tr>
                        <td class="td-name">Dewi Lestari</td>
                        <td class="td-item">Whiteboard 120cm</td>
                        <td class="td-date">2024-03-19</td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-evidence" title="Lihat Bukti Foto">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-1.96-2.36L6.5 17h11l-3.54-4.71z"/>
                                </svg>
                            </button>
                        </td>
                        <td>
                            <select class="sarana-select-condition">
                                <option value="good" selected>Good</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn-confirm-return">Confirm</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="system-pagination-bar">
            <button class="pagination-btn pagination-btn--disabled">Prev</button>
            <button class="pagination-btn pagination-btn--active">1</button>
            <button class="pagination-btn">Next</button>
        </div>
    </div>
</div>

{{-- Interactive Tab Switching Script --}}
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
    });
</script>
@endsection
