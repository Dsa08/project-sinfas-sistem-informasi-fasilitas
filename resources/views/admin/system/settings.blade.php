{{-- 
  HALAMAN PENGATURAN SISTEM (SYSTEM SETTINGS) — SINFAS
  File: resources/views/admin/system/settings.blade.php
  Fitur:
  - Pengaturan Jam Operasional Layanan Sarana (Start Time & End Time).
  - Status Konfigurasi & Pemeliharaan Aplikasi.
--}}
@extends('layouts.admin-system')

@section('title', 'Kelola Pengaturan Sistem - Admin Sistem SINFAS')
@section('page_title', 'Pengaturan Sistem')

@section('content')
<div class="system-settings-container">
    <div class="system-section-header">
        <h2 class="system-section-heading">Pengaturan Sistem</h2>
    </div>

    {{-- Card 1: Operational Hours --}}
    <div class="system-card">
        <h3 class="system-card-title">Jam Operasional</h3>
        <div class="system-card-divider"></div>
        <div class="operational-hours-form">
            <div class="form-row">
                <div class="form-group-flex">
                    <label class="system-label" for="start-time">Waktu Mulai</label>
                    <input type="text" class="system-input" id="start-time" placeholder="07:00">
                </div>
                <div class="form-group-flex">
                    <label class="system-label" for="end-time">Waktu Selesai</label>
                    <input type="text" class="system-input" id="end-time" placeholder="16:00">
                </div>
            </div>
            <div class="form-action-right">
                <button type="button" class="btn-system-save" id="save-hours-btn">Simpan</button>
            </div>
        </div>
    </div>

    {{-- Card 2: Backup & Restore --}}
    <div class="system-card">
        <h3 class="system-card-title">Pencadangan & Pemulihan Sistem (Backup & Restore)</h3>
        <div class="system-card-divider"></div>
        <div class="backup-restore-section">
            <div class="backup-btn-group">
                <button type="button" class="btn-backup-now" id="btn-backup-now">Cadangkan Sekarang</button>
                <button type="button" class="btn-restore" id="btn-restore">Pulihkan Data</button>
            </div>
            <div class="backup-last-info">Pencadangan terakhir: 2024-03-15, 02:00 WIB</div>
        </div>
    </div>

    {{-- Card 3: Activity Log --}}
    <div class="system-card">
        <h3 class="system-card-title no-border">Log Aktivitas Sistem</h3>
        <div class="system-table-card no-border mt-2">
            <table class="system-table activity-log-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Pengguna</th>
                        <th style="width: 50%;">Aktivitas / Aksi</th>
                        <th style="width: 25%;">Waktu Kejadian</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="td-user">Admin Sistem</td>
                        <td class="td-action">Membuat akun baru: Dewi Lestari (Siswa)</td>
                        <td class="td-timestamp">2024-03-15, 10:23 WIB</td>
                    </tr>
                    <tr>
                        <td class="td-user">Admin Sistem</td>
                        <td class="td-action">Pencadangan sistem berhasil diselesaikan</td>
                        <td class="td-timestamp">2024-03-15, 02:00 WIB</td>
                    </tr>
                    <tr>
                        <td class="td-user">Admin Sistem</td>
                        <td class="td-action">Menonaktifkan akun: Rudi Hartono</td>
                        <td class="td-timestamp">2024-03-14, 16:45 WIB</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
