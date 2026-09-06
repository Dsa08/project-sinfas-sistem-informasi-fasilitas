@extends('layouts.admin-sarana')

@section('title', 'Dashboard - Admin Sarana SINFAS')
@section('page_title', 'Home')

@section('content')
<div class="sarana-dashboard-container">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.88rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- 4 Stat Cards --}}
    <div class="sarana-stats-grid">
        {{-- Card 1 --}}
        <div class="system-stat-card">
            <div class="system-stat-value">{{ $pendingCount }}</div>
            <div class="system-stat-label">Pending Verification</div>
        </div>

        {{-- Card 2 --}}
        <div class="system-stat-card">
            <div class="system-stat-value">{{ $totalItems }}</div>
            <div class="system-stat-label">Total Items</div>
        </div>

        {{-- Card 3 --}}
        <div class="system-stat-card">
            <div class="system-stat-value">{{ $borrowedCount }}</div>
            <div class="system-stat-label">Currently Borrowed</div>
        </div>

        {{-- Card 4: Damaged (amber highlight) --}}
        <div class="system-stat-card" style="border-color: #f59e0b;">
            <div class="system-stat-value" style="color: #d97706;">{{ $damagedCount }}</div>
            <div class="system-stat-label">Damaged</div>
        </div>
    </div>

    {{-- Pending Loan Requests Section --}}
    <div class="sarana-section">
        <h2 class="sarana-section-heading">Pending Loan Requests</h2>
        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Borrower</th>
                        <th style="width: 35%;">Item</th>
                        <th style="width: 20%;">Date</th>
                        <th style="width: 20%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingLoans as $loan)
                    <tr>
                        <td class="td-name">{{ $loan->siswa->nama ?? '-' }}</td>
                        <td class="td-item">{{ $loan->barang->nama_barang ?? '-' }}</td>
                        <td class="td-date">{{ $loan->tanggal_pinjam->format('Y-m-d') }}</td>
                        <td>
                            <div class="action-btn-group">
                                <form action="{{ route('admin.verifications.approve', $loan->kode_pinjam) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-approve" onclick="return confirm('Setujui peminjaman ini?')">Approve</button>
                                </form>
                                <form action="{{ route('admin.verifications.reject', $loan->kode_pinjam) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-reject" onclick="return confirm('Tolak peminjaman ini?')">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #9ca3af; padding: 2rem;">Tidak ada peminjaman yang menunggu verifikasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3 Action Cards --}}
    <div class="sarana-actions-grid">
        {{-- Card 1: Manage Items --}}
        <a href="{{ route('admin.items') }}" class="sarana-action-card" id="action-manage-items">
            <div class="sarana-action-icon-box icon-bg-red">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m7.5 4.27 9 5.15"/>
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                    <path d="m3.3 7 8.7 5 8.7-5"/>
                    <path d="M12 22V12"/>
                </svg>
            </div>
            <div class="sarana-action-text">Manage Items</div>
        </a>

        {{-- Card 2: History & Print Report --}}
        <div class="sarana-action-card" id="action-print-report">
            <div class="sarana-action-icon-box icon-bg-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <line x1="10" y1="9" x2="8" y2="9"/>
                </svg>
            </div>
            <div class="sarana-action-text">History & Print Report</div>
        </div>

        {{-- Card 3: Verify Returns --}}
        <a href="{{ route('admin.verifications') }}" class="sarana-action-card" id="action-verify-returns">
            <div class="sarana-action-icon-box icon-bg-slate">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="sarana-action-text">Verify Returns</div>
        </a>
    </div>
</div>
@endsection
