@extends('layouts.app')

@section('title', 'Admin Sarana Dashboard - SINFAS')
@section('brand_name', 'Admin Sarana')

@section('content')
<div class="admin-dashboard-container">
    {{-- Top Stats Cards Grid (4 Cards) --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">5</div>
            <div class="stat-label">Pending Verification</div>
        </div>

        <div class="stat-card">
            <div class="stat-value">42</div>
            <div class="stat-label">Total Items</div>
        </div>

        <div class="stat-card">
            <div class="stat-value">12</div>
            <div class="stat-label">Currently Borrowed</div>
        </div>

        <div class="stat-card">
            <div class="stat-value">3</div>
            <div class="stat-label">Damaged</div>
        </div>
    </div>

    {{-- Pending Loan Requests Section --}}
    <div class="admin-section">
        <h2 class="admin-section-title">Pending Loan Requests</h2>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Borrower</th>
                        <th>Item</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="cell-borrower">Ahmad Fadli</td>
                        <td class="cell-item">Projector Epson X300</td>
                        <td class="cell-date">2024-03-15</td>
                        <td class="cell-actions">
                            <button type="button" class="btn-action btn-approve">Approve</button>
                            <button type="button" class="btn-action btn-reject">Reject</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell-borrower">Siti Nurhaliza</td>
                        <td class="cell-item">Portable Speaker</td>
                        <td class="cell-date">2024-03-15</td>
                        <td class="cell-actions">
                            <button type="button" class="btn-action btn-approve">Approve</button>
                            <button type="button" class="btn-action btn-reject">Reject</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell-borrower">Budi Santoso</td>
                        <td class="cell-item">Folding Table (x3)</td>
                        <td class="cell-date">2024-03-14</td>
                        <td class="cell-actions">
                            <button type="button" class="btn-action btn-approve">Approve</button>
                            <button type="button" class="btn-action btn-reject">Reject</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bottom Action Navigation Cards Grid (3 Cards) --}}
    <div class="admin-actions-grid">
        {{-- Card 1: Manage Items --}}
        <a href="#" class="admin-action-card">
            <div class="action-card-icon icon-bg-red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m7.5 4.27 9 5.15"/>
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                    <path d="m3.3 7 8.7 5 8.7-5"/>
                    <path d="M12 22V12"/>
                </svg>
            </div>
            <span class="action-card-text">Manage Items</span>
        </a>

        {{-- Card 2: History & Print Report --}}
        <a href="#" class="admin-action-card">
            <div class="action-card-icon icon-bg-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
                    <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                    <path d="M10 9H8"/>
                    <path d="M16 13H8"/>
                    <path d="M16 17H8"/>
                </svg>
            </div>
            <span class="action-card-text">History & Print Report</span>
        </a>

        {{-- Card 3: Verify Returns --}}
        <a href="#" class="admin-action-card">
            <div class="action-card-icon icon-bg-slate">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 2v4"/>
                    <path d="M16 2v4"/>
                    <rect width="18" height="18" x="3" y="4" rx="2"/>
                    <path d="m9 14 2 2 4-4"/>
                </svg>
            </div>
            <span class="action-card-text">Verify Returns</span>
        </a>
    </div>
</div>
@endsection
