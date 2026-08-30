@extends('layouts.admin-system')

@section('title', 'Dashboard - Admin Sistem SINFAS')
@section('page_title', 'Home')

@section('content')
<div class="system-dashboard-container">
    {{-- Top 3 Stat Cards --}}
    <div class="system-stats-grid">
        {{-- Card 1: Total Accounts --}}
        <div class="system-stat-card">
            <div class="system-stat-value">156</div>
            <div class="system-stat-label">Total Accounts</div>
            <div class="system-stat-subtext">142 Siswa · 12 Admin Sarana · 2 Admin Sistem</div>
        </div>

        {{-- Card 2: Akun Baru Bulan Ini --}}
        <div class="system-stat-card">
            <div class="system-stat-value">8</div>
            <div class="system-stat-label">Akun Baru Bulan Ini</div>
            <div class="system-stat-subtext">Ditambahkan Agustus 2026</div>
        </div>

        {{-- Card 3: Backup Terakhir --}}
        <div class="system-stat-card">
            <div class="system-stat-value">2 hari lalu</div>
            <div class="system-stat-label">Backup Terakhir</div>
            <div class="system-stat-subtext">25 Agu 2026, 03:00</div>
        </div>
    </div>

    {{-- Bottom Action Cards Grid --}}
    <div class="system-actions-grid">
        {{-- Action Card 1: Manage Accounts --}}
        <a href="{{ route('admin.sistem.accounts') }}" class="system-action-card" id="action-manage-accounts">
            <div class="system-action-icon-box">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="system-action-info">
                <div class="system-action-title">Manage Accounts</div>
                <div class="system-action-desc">View, add, edit, and deactivate user accounts</div>
            </div>
            <div class="system-action-arrow">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                    <path d="m12 5 7 7-7 7"/>
                </svg>
            </div>
        </a>

        {{-- Action Card 2: System Settings --}}
        <a href="{{ route('admin.sistem.settings') }}" class="system-action-card" id="action-system-settings">
            <div class="system-action-icon-box">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </div>
            <div class="system-action-info">
                <div class="system-action-title">System Settings</div>
                <div class="system-action-desc">Configure operational hours, backups, and preferences</div>
            </div>
            <div class="system-action-arrow">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                    <path d="m12 5 7 7-7 7"/>
                </svg>
            </div>
        </a>
    </div>
</div>
@endsection
