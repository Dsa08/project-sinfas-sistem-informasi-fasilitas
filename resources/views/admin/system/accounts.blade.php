@extends('layouts.admin-system')

@section('title', 'Kelola Akun User - Admin Sistem SINFAS')
@section('page_title', 'Manage Accounts')

@section('content')
<div class="system-accounts-container">
    <div class="system-section-header">
        <h2 class="system-section-heading">User Accounts</h2>
    </div>

    {{-- Filter & Action Bar --}}
    <div class="system-filter-bar">
        <div class="system-search-box">
            <svg class="system-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            <input
                type="text"
                class="system-search-input"
                id="search-user-input"
                placeholder="Search by name, NIS, or email..."
            >
        </div>
        <button type="button" class="btn-add-account" id="btn-add-account">
            + Add Account
        </button>
    </div>

    {{-- Accounts Table Card --}}
    <div class="system-table-card">
        <table class="system-table">
            <thead>
                <tr>
                    <th>
                        <div class="th-content">
                            <span>Name</span>
                            <svg class="sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m7 15 5 5 5-5"/>
                                <path d="m7 9 5-5 5 5"/>
                            </svg>
                        </div>
                    </th>
                    <th>
                        <div class="th-content">
                            <span>NIS / NIP</span>
                            <svg class="sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m7 15 5 5 5-5"/>
                                <path d="m7 9 5-5 5 5"/>
                            </svg>
                        </div>
                    </th>
                    <th>
                        <div class="th-content">
                            <span>Role</span>
                            <svg class="sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m7 15 5 5 5-5"/>
                                <path d="m7 9 5-5 5 5"/>
                            </svg>
                        </div>
                    </th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- Row 1: Ahmad Fadli --}}
                <tr>
                    <td class="td-name">Ahmad Fadli</td>
                    <td class="td-nis-nip">20240001</td>
                    <td class="td-role">Student</td>
                    <td class="td-actions">
                        <div class="action-btn-group">
                            <button type="button" class="table-action-icon-btn" title="Edit Akun">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                    <path d="m15 5 4 4"/>
                                </svg>
                            </button>
                            <button type="button" class="table-action-icon-btn" title="Lihat Detail">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <button type="button" class="table-action-icon-btn" title="Menu Lainnya">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="4" x2="20" y1="12" y2="12"/>
                                    <line x1="4" x2="20" y1="6" y2="6"/>
                                    <line x1="4" x2="20" y1="18" y2="18"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>

                {{-- Row 2: Siti Nurhaliza --}}
                <tr>
                    <td class="td-name">Siti Nurhaliza</td>
                    <td class="td-nis-nip">20240002</td>
                    <td class="td-role">Student</td>
                    <td class="td-actions">
                        <div class="action-btn-group">
                            <button type="button" class="table-action-icon-btn" title="Edit Akun">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                    <path d="m15 5 4 4"/>
                                </svg>
                            </button>
                            <button type="button" class="table-action-icon-btn" title="Lihat Detail">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <button type="button" class="table-action-icon-btn" title="Menu Lainnya">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="4" x2="20" y1="12" y2="12"/>
                                    <line x1="4" x2="20" y1="6" y2="6"/>
                                    <line x1="4" x2="20" y1="18" y2="18"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>

                {{-- Row 3: Budi Santoso --}}
                <tr>
                    <td class="td-name">Budi Santoso</td>
                    <td class="td-nis-nip">19850103</td>
                    <td class="td-role">Teacher</td>
                    <td class="td-actions">
                        <div class="action-btn-group">
                            <button type="button" class="table-action-icon-btn" title="Edit Akun">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                    <path d="m15 5 4 4"/>
                                </svg>
                            </button>
                            <button type="button" class="table-action-icon-btn" title="Lihat Detail">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <button type="button" class="table-action-icon-btn" title="Menu Lainnya">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="4" x2="20" y1="12" y2="12"/>
                                    <line x1="4" x2="20" y1="6" y2="6"/>
                                    <line x1="4" x2="20" y1="18" y2="18"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>

                {{-- Row 4: Dewi Lestari --}}
                <tr>
                    <td class="td-name">Dewi Lestari</td>
                    <td class="td-nis-nip">20240015</td>
                    <td class="td-role">Student</td>
                    <td class="td-actions">
                        <div class="action-btn-group">
                            <button type="button" class="table-action-icon-btn" title="Edit Akun">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                    <path d="m15 5 4 4"/>
                                </svg>
                            </button>
                            <button type="button" class="table-action-icon-btn" title="Lihat Detail">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <button type="button" class="table-action-icon-btn" title="Menu Lainnya">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="4" x2="20" y1="12" y2="12"/>
                                    <line x1="4" x2="20" y1="6" y2="6"/>
                                    <line x1="4" x2="20" y1="18" y2="18"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination Bar --}}
    <div class="system-pagination-bar">
        <button class="pagination-btn pagination-btn--disabled">Prev</button>
        <button class="pagination-btn pagination-btn--active">1</button>
        <button class="pagination-btn">2</button>
        <button class="pagination-btn">Next</button>
    </div>
</div>
@endsection
