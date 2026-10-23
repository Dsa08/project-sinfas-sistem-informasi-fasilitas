{{-- 
  HALAMAN PENGATURAN SISTEM (SYSTEM SETTINGS) — SINFAS
  File: resources/views/admin/system/settings.blade.php
  Fitur Profesional:
  - Pengaturan Jam Operasional Layanan Sarana (disimpan secara persisten).
  - Pencadangan Database SQL instan & unduh langsung ke komputer administrator.
  - Pemulihan Database dari file dump SQL dengan proteksi validasi.
  - Riwayat Aktivitas Nyata yang diagregasi dari sistem registrasi, transaksi sarana, dan pengaturan.
--}}
@extends('layouts.admin-system')

@section('title', 'Pengaturan Sistem - Admin Sistem SINFAS')
@section('page_title', 'Pengaturan Sistem')

@section('content')
<div class="system-settings-container">
    <div class="system-section-header">
        <h2 class="system-section-heading">Pengaturan Sistem</h2>
        <p style="font-size: 0.88rem; color: #64748b; margin: 0.25rem 0 0;">Kelola parameter operasional, keamanan data, dan pemantauan aktivitas aplikasi SINFAS.</p>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.88rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.88rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.84rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Card 1: Operational Hours --}}
    <div class="system-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="system-card-title">Jam Operasional Layanan</h3>
            <span style="font-size: 0.78rem; font-weight: 600; color: #166534; background: #dcfce7; padding: 0.2rem 0.6rem; border-radius: 9999px;">
                Aktif: {{ $startTime }} - {{ $endTime }} WIB
            </span>
        </div>
        <div class="system-card-divider"></div>
        <form method="POST" action="{{ route('admin.sistem.settings.hours') }}" class="operational-hours-form" onsubmit="return confirmAppForm(this, 'Simpan perubahan jam operasional layanan sarana?', {title: 'Ubah Jam Operasional', confirmText: 'Simpan'}, event)">
            @csrf
            <div class="form-row">
                <div class="form-group-flex">
                    <label class="system-label" for="start-time">Waktu Mulai Pelayanan</label>
                    <input type="time" name="start_time" class="system-input" id="start-time" value="{{ old('start_time', $startTime) }}" required>
                </div>
                <div class="form-group-flex">
                    <label class="system-label" for="end-time">Waktu Selesai Pelayanan</label>
                    <input type="time" name="end_time" class="system-input" id="end-time" value="{{ old('end_time', $endTime) }}" required>
                </div>
            </div>
            <div class="form-action-right" style="margin-top: 1rem;">
                <button type="submit" class="btn-system-save" id="save-hours-btn">Simpan Jam Operasional</button>
            </div>
        </form>
    </div>

    {{-- Card 2: Backup & Restore --}}
    <div class="system-card">
        <h3 class="system-card-title">Pencadangan & Pemulihan Basis Data (MySQL)</h3>
        <div class="system-card-divider"></div>
        <div class="backup-restore-section">
            <p style="font-size: 0.85rem; color: #64748b; margin: 0 0 1rem; line-height: 1.5;">
                Ekspor seluruh struktur tabel dan data inventaris ke file <code>.sql</code> terenkripsi standar untuk arsip keamanan berkala atau migrasi server.
            </p>
            <div class="backup-btn-group" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <form method="POST" action="{{ route('admin.sistem.settings.backup') }}" onsubmit="return confirmAppForm(this, 'Buat dan unduh file cadangan basis data SINFAS sekarang?', {title: 'Pencadangan Database', confirmText: 'Cadangkan Sekarang'}, event)">
                    @csrf
                    <button type="submit" class="btn-backup-now" id="btn-backup-now" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Cadangkan &amp; Unduh SQL</span>
                    </button>
                </form>
                <button type="button" class="btn-restore" id="btn-restore" onclick="openRestoreModal()" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    <span>Pulihkan dari File SQL</span>
                </button>
            </div>
            <div class="backup-last-info" style="margin-top: 0.85rem; font-size: 0.82rem; color: #475569; display: flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Pencadangan terakhir: <strong>{{ $lastBackupFormatted }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Card 3: Activity Log --}}
    <div class="system-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="system-card-title no-border">Riwayat Aktivitas Sistem</h3>
            <span style="font-size: 0.78rem; color: #64748b;">Memantau 10 aktivitas terbaru</span>
        </div>
        <div class="system-table-card no-border mt-2">
            <table class="system-table activity-log-table">
                <thead>
                    <tr>
                        <th class="th-number">No.</th>
                        <th style="width: 25%;">Pengguna / Aktor</th>
                        <th style="width: 48%;">Aktivitas / Aksi Sistem</th>
                        <th style="width: 22%;">Waktu Kejadian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activityLogs as $idx => $log)
                        <tr>
                            <td class="td-number">{{ $idx + 1 }}</td>
                            <td class="td-user">
                                <span style="font-weight: 600; color: #0f172a;">{{ $log['user'] }}</span>
                            </td>
                            <td class="td-action">{{ $log['action'] }}</td>
                            <td class="td-timestamp" style="font-size: 0.82rem; color: #64748b;">{{ $log['timestamp'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                Belum ada riwayat aktivitas sistem yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Pulihkan Database --}}
<div class="modal-overlay" id="restoreModal">
    <div class="modal-card" style="max-width: 520px; text-align: left; padding: 1.75rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 34px; height: 34px; border-radius: 8px; background: #fef2f2; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">Pulihkan Basis Data</h3>
            </div>
            <button type="button" onclick="closeRestoreModal()" style="background: none; border: none; font-size: 1.25rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>

        <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 0.75rem 0.9rem; margin-bottom: 1.25rem;">
            <p style="font-size: 0.82rem; color: #92400e; margin: 0; line-height: 1.45;">
                <strong>Perhatian:</strong> Tindakan ini akan menimpa struktur dan isi data saat ini dengan isi file SQL yang Anda unggah. Pastikan Anda telah membuat cadangan sebelum melanjutkan.
            </p>
        </div>

        <form action="{{ route('admin.sistem.settings.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirmAppForm(this, 'PERINGATAN: Apakah Anda benar-benar yakin ingin memulihkan database dari file SQL ini? Data lama akan ditimpa.', {title: 'Konfirmasi Pemulihan', confirmText: 'Ya, Pulihkan Sekarang', danger: true}, event)">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem;">Pilih Berkas Backup (.sql)</label>
                <input type="file" name="backup_file" accept=".sql" required style="width: 100%; font-size: 0.88rem; padding: 0.55rem; border: 1.5px dashed #cbd5e1; border-radius: 8px; background: #f8fafc;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeRestoreModal()" style="padding: 0.55rem 1rem; border: 1px solid #cbd5e1; background: #fff; border-radius: 6px; font-size: 0.85rem; font-weight: 600; color: #475569; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 0.55rem 1.25rem; border: none; background: #dc2626; color: #fff; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">Mulai Pemulihan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRestoreModal() {
        document.getElementById('restoreModal').classList.add('modal-overlay--active');
    }
    function closeRestoreModal() {
        document.getElementById('restoreModal').classList.remove('modal-overlay--active');
    }
</script>
@endsection
