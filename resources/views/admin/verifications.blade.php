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
<style>
    .borrower-type-badge { display:inline-flex; align-items:center; padding:.25rem .6rem; border-radius:999px; font-size:.75rem; font-weight:700; line-height:1.2; white-space:nowrap; }
    .borrower-type-badge--student { color:#1d4ed8; background:#eff6ff; }
    .borrower-type-badge--active { color:#047857; background:#ecfdf5; }
    .sarana-tab-count { display:inline-flex; min-width:1.25rem; height:1.25rem; margin-left:.35rem; padding:0 .25rem; align-items:center; justify-content:center; border-radius:999px; background:rgba(255,255,255,.7); font-size:.72rem; }
    .loan-overdue-label { display:block; margin-top:.2rem; color:#b91c1c; font-size:.75rem; font-weight:700; }
    .btn-active-loan-detail { border:1px solid #bfdbfe; border-radius:7px; padding:.4rem .7rem; background:#eff6ff; color:#1d4ed8; font-weight:600; cursor:pointer; }
    .btn-active-loan-detail:hover { background:#dbeafe; }
    .active-loan-detail-list { display:grid; gap:.7rem; margin:0 0 1.4rem; }
    .active-loan-detail-list div { display:grid; grid-template-columns: minmax(110px, .7fr) 1fr; gap:.75rem; padding-bottom:.55rem; border-bottom:1px solid #e2e8f0; }
    .active-loan-detail-list dt { color:#64748b; font-size:.85rem; }
    .active-loan-detail-list dd { margin:0; color:#0f172a; font-size:.9rem; font-weight:600; overflow-wrap:anywhere; }
    @media (max-width: 768px) { .sarana-tab-bar { flex-wrap:wrap; } .sarana-tab-btn { flex:1 1 11rem; } }
</style>
<div class="sarana-verifications-container">
    {{-- Tab Navigation Bar --}}
    <div class="sarana-tab-bar" id="verification-tabs">
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'requests' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-requests" data-tab="requests">
            Permintaan Peminjaman
        </button>
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'returns' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-returns" data-tab="returns">
            Menunggu Pengembalian
        </button>
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'active' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-active" data-tab="active">
            Sedang Dipinjam <span class="sarana-tab-count">{{ $activeLoans->total() }}</span>
        </button>
    </div>

    {{-- Tab 1: Pending Requests Section --}}
    <div class="sarana-tab-content {{ $activeTab === 'requests' ? 'sarana-tab-content--active' : '' }}" id="tab-content-requests">
        <div class="system-section-header">
            <h2 class="sarana-section-heading">Permintaan Peminjaman</h2>
        </div>

        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th class="th-number">No.</th>
                        <th><x-sortable-table-heading label="Peminjam" column="siswa" sort-key="req_sort" dir-key="req_dir" page-key="req_page" tab="requests" /></th>
                        <th><x-sortable-table-heading label="Status Peminjam" column="status_peminjam" sort-key="req_sort" dir-key="req_dir" page-key="req_page" tab="requests" /></th>
                        <th><x-sortable-table-heading label="Nomor Telepon" column="nomor_kontak" sort-key="req_sort" dir-key="req_dir" page-key="req_page" tab="requests" /></th>
                        <th><x-sortable-table-heading label="Barang" column="barang" sort-key="req_sort" dir-key="req_dir" page-key="req_page" tab="requests" /></th>
                        <th><x-sortable-table-heading label="Lokasi" column="lokasi_penggunaan" sort-key="req_sort" dir-key="req_dir" page-key="req_page" tab="requests" /></th>
                        <th><x-sortable-table-heading label="Keperluan" column="keterangan_penggunaan" sort-key="req_sort" dir-key="req_dir" page-key="req_page" tab="requests" /></th>
                        <th><x-sortable-table-heading label="Tanggal Pinjam" column="tanggal_pinjam" sort-key="req_sort" dir-key="req_dir" page-key="req_page" tab="requests" /></th>
                        <th style="width: 14%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $req)
                    <tr>
                        <td class="td-number">{{ $loop->iteration + ($pendingRequests->currentPage() - 1) * $pendingRequests->perPage() }}</td>
                        <td class="td-name">{{ $req->peminjam_nama }}</td>
                        <td><span class="borrower-type-badge borrower-type-badge--student">{{ $req->peminjam_status }}</span></td>
                        <td>{{ $req->akun?->nomor_kontak ?: ($req->siswa?->no_hp ?: '-') }}</td>
                        <td class="td-item">{{ $req->barang->nama_barang ?? '-' }}</td>
                        <td class="td-location">{{ $req->lokasi_penggunaan ?? '-' }}</td>
                        <td class="td-category" style="font-size: 0.82rem;">{{ Str::limit($req->keterangan_penggunaan, 30) ?? '-' }}</td>
                        <td class="td-date">{{ $req->tanggal_pinjam->format('Y-m-d') }}</td>
                        <td>
                            <div class="action-btn-group">
                                <button 
                                    type="button" 
                                    class="btn-action btn-approve"
                                    onclick="openApproveModal('{{ $req->kode_pinjam }}', '{{ addslashes($req->peminjam_nama) }}', '{{ addslashes($req->barang->nama_barang ?? 'Barang') }}')">
                                    Setujui
                                </button>
                                <button 
                                    type="button" 
                                    class="btn-action btn-reject"
                                    onclick="openRejectModal('{{ $req->kode_pinjam }}')">
                                    Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="system-table-empty-state">
                                <div class="system-empty-icon-box">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 14 14"></polyline>
                                    </svg>
                                </div>
                                <div class="system-empty-title">Tidak ada permintaan peminjaman</div>
                                <div class="system-empty-desc">Saat ini tidak ada permohonan peminjaman sarana yang sedang menunggu verifikasi dari Admin.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Footer (Per-Page Selector + Entries Info + Pagination) --}}
        <div class="system-table-footer">
            <div class="system-table-meta">
                <div class="system-per-page-wrapper">
                    <span>Tampilkan</span>
                    <select class="system-per-page-select" onchange="window.location.href=this.value">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ request()->fullUrlWithQuery(['req_per_page' => $size, 'req_page' => 1]) }}" {{ request('req_per_page', request('per_page', 10)) == $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                    <span>data per halaman</span>
                </div>
                @if($pendingRequests->total() > 0)
                    <span class="system-table-meta-dot">&bull;</span>
                    <div class="system-table-entries-info">
                        Menampilkan <strong>{{ $pendingRequests->firstItem() }}</strong> - <strong>{{ $pendingRequests->lastItem() }}</strong> dari <strong>{{ $pendingRequests->total() }}</strong> data
                    </div>
                @endif
            </div>

            @if($pendingRequests->hasPages())
                <div class="system-pagination-bar">
                    {{ $pendingRequests->links('vendor.pagination.simple-default') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Tab 2: Pending Returns Section --}}
    <div class="sarana-tab-content {{ $activeTab === 'returns' ? 'sarana-tab-content--active' : '' }}" id="tab-content-returns">
        <div class="system-section-header">
            <h2 class="sarana-section-heading">Menunggu Pengembalian</h2>
        </div>

        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th class="th-number">No.</th>
                        <th><x-sortable-table-heading label="Peminjam" column="siswa" sort-key="ret_sort" dir-key="ret_dir" page-key="ret_page" tab="returns" /></th>
                        <th><x-sortable-table-heading label="Status Peminjam" column="status_peminjam" sort-key="ret_sort" dir-key="ret_dir" page-key="ret_page" tab="returns" /></th>
                        <th><x-sortable-table-heading label="Nomor Telepon" column="nomor_kontak" sort-key="ret_sort" dir-key="ret_dir" page-key="ret_page" tab="returns" /></th>
                        <th><x-sortable-table-heading label="Barang" column="barang" sort-key="ret_sort" dir-key="ret_dir" page-key="ret_page" tab="returns" /></th>
                        <th><x-sortable-table-heading label="Tanggal Pinjam" column="tanggal_pinjam" sort-key="ret_sort" dir-key="ret_dir" page-key="ret_page" tab="returns" /></th>
                        <th><x-sortable-table-heading label="Tanggal Kembali" column="tanggal_kembali" sort-key="ret_sort" dir-key="ret_dir" page-key="ret_page" tab="returns" /></th>
                        <th style="width: 11%; text-align: center;"><x-sortable-table-heading label="Bukti" column="bukti" sort-key="ret_sort" dir-key="ret_dir" page-key="ret_page" tab="returns" /></th>
                        <th style="width: 11%;">Kondisi</th>
                        <th style="width: 11%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingReturns as $ret)
                    <tr>
                        <td class="td-number">{{ $loop->iteration + ($pendingReturns->currentPage() - 1) * $pendingReturns->perPage() }}</td>
                        <td class="td-name">{{ $ret->peminjam_nama }}</td>
                        <td><span class="borrower-type-badge borrower-type-badge--student">{{ $ret->peminjam_status }}</span></td>
                        <td>{{ $ret->akun?->nomor_kontak ?: ($ret->siswa?->no_hp ?: '-') }}</td>
                        <td class="td-item">
                            {{ $ret->barang->nama_barang ?? '-' }}
                            @if(!empty($ret->pengembalian->catatan))
                                <div style="font-size: 0.76rem; color: #6b7280; margin-top: 0.25rem; font-style: italic; line-height: 1.3;">
                                    <span style="font-weight: 600; color: #4b5563; font-style: normal;">Catatan:</span> "{{ $ret->pengembalian->catatan }}"
                                </div>
                            @endif
                        </td>
                        <td class="td-date">{{ $ret->tanggal_pinjam ? $ret->tanggal_pinjam->format('Y-m-d') : '-' }}</td>
                        <td class="td-date">{{ $ret->pengembalian->tanggal_kembali ? $ret->pengembalian->tanggal_kembali->format('Y-m-d') : '-' }}</td>
                        <td style="text-align: center;">
                            @if($ret->pengembalian->bukti_foto_video)
                                @php
                                    $buktiUrl = $ret->pengembalian->bukti_foto_video_url;
                                @endphp
                                <div style="display: flex; gap: 0.35rem; justify-content: center; align-items: center;">
                                    <button type="button" title="Lihat Bukti Foto" onclick="window.open('{{ $buktiUrl }}', '_blank')" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.3rem 0.45rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="color: #1e293b;">
                                            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                                        </svg>
                                    </button>
                                    <button type="button" title="Lihat Bukti Video" onclick="window.open('{{ $buktiUrl }}', '_blank')" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.3rem 0.45rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="color: #1e293b;">
                                            <path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8 12.5v-9l6 4.5-6 4.5z"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <span style="color: #9ca3af; font-size: 0.82rem;">-</span>
                            @endif
                        </td>
                        <td>
                            <select id="kondisi-select-{{ $ret->kode_pinjam }}" class="sarana-select-condition" required>
                                <option value="Baik">Baik</option>
                                <option value="Kurang Baik">Kurang Baik</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn-confirm-return" onclick="openConfirmReturnModal('{{ $ret->kode_pinjam }}', '{{ addslashes($ret->peminjam_nama) }}', '{{ addslashes($ret->barang->nama_barang ?? 'Barang') }}', 'kondisi-select-{{ $ret->kode_pinjam }}')">Konfirmasi</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="system-table-empty-state">
                                <div class="system-empty-icon-box">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </div>
                                <div class="system-empty-title">Tidak ada pengembalian menunggu</div>
                                <div class="system-empty-desc">Seluruh pengembalian barang pinjaman telah selesai dikonfirmasi oleh Admin.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Footer (Per-Page Selector + Entries Info + Pagination) --}}
        <div class="system-table-footer">
            <div class="system-table-meta">
                <div class="system-per-page-wrapper">
                    <span>Tampilkan</span>
                    <select class="system-per-page-select" onchange="window.location.href=this.value">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ request()->fullUrlWithQuery(['tab' => 'returns', 'ret_per_page' => $size, 'ret_page' => 1]) }}" {{ request('ret_per_page', request('per_page', 10)) == $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                    <span>data per halaman</span>
                </div>
                @if($pendingReturns->total() > 0)
                    <span class="system-table-meta-dot">&bull;</span>
                    <div class="system-table-entries-info">
                        Menampilkan <strong>{{ $pendingReturns->firstItem() }}</strong> - <strong>{{ $pendingReturns->lastItem() }}</strong> dari <strong>{{ $pendingReturns->total() }}</strong> data
                    </div>
                @endif
            </div>

            @if($pendingReturns->hasPages())
                <div class="system-pagination-bar">
                    {{ $pendingReturns->links('vendor.pagination.simple-default') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Tab 3: Barang yang masih berada pada peminjam --}}
    <div class="sarana-tab-content {{ $activeTab === 'active' ? 'sarana-tab-content--active' : '' }}" id="tab-content-active">
        <div class="system-section-header">
            <div>
                <h2 class="sarana-section-heading">Barang Sedang Dipinjam</h2>
                <p class="system-section-subtitle">Peminjaman dihitung terlambat setelah melewati batas 3 hari.</p>
            </div>
        </div>

        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th class="th-number">No.</th>
                        <th><x-sortable-table-heading label="Peminjam" column="siswa" sort-key="active_sort" dir-key="active_dir" page-key="active_page" tab="active" /></th>
                        <th><x-sortable-table-heading label="NIS/NIP" column="identitas" sort-key="active_sort" dir-key="active_dir" page-key="active_page" tab="active" /></th>
                        <th><x-sortable-table-heading label="Nomor Telepon" column="nomor_kontak" sort-key="active_sort" dir-key="active_dir" page-key="active_page" tab="active" /></th>
                        <th><x-sortable-table-heading label="Waktu Peminjaman" column="tanggal_pinjam" sort-key="active_sort" dir-key="active_dir" page-key="active_page" tab="active" /></th>
                        <th><x-sortable-table-heading label="Barang" column="barang" sort-key="active_sort" dir-key="active_dir" page-key="active_page" tab="active" /></th>
                        <th><x-sortable-table-heading label="Lama Dipinjam" column="lama_dipinjam" sort-key="active_sort" dir-key="active_dir" page-key="active_page" tab="active" /></th>
                        <th>Status</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeLoans as $loan)
                        @php
                            $daysBorrowed = $loan->tanggal_pinjam
                                ? max(0, (int) \Carbon\Carbon::parse($loan->tanggal_pinjam)->startOfDay()->diffInDays(now()->startOfDay()))
                                : 0;
                            $lateDays = max(0, $daysBorrowed - 3);
                            $borrowerName = $loan->akun?->nama ?? $loan->peminjam_nama;
                        @endphp
                        <tr>
                            <td class="td-number">{{ $loop->iteration + ($activeLoans->currentPage() - 1) * $activeLoans->perPage() }}</td>
                            <td class="td-name">{{ $borrowerName }}</td>
                            <td>{{ $loan->akun?->nis_nip ?? $loan->nis ?? '-' }}</td>
                            <td>{{ $loan->akun?->nomor_kontak ?: ($loan->siswa?->no_hp ?: '-') }}</td>
                            <td class="td-date">{{ $loan->tanggal_pinjam?->format('d M Y') ?? '-' }}</td>
                            <td>{{ $loan->barang->nama_barang ?? 'Barang tidak ditemukan' }}</td>
                            <td>
                                <strong>{{ $daysBorrowed }} hari</strong>
                                @if($lateDays > 0)
                                    <span class="loan-overdue-label">Terlambat {{ $lateDays }} hari</span>
                                @endif
                            </td>
                            <td><span class="borrower-type-badge borrower-type-badge--active">Masih dipinjam</span></td>
                            <td>
                                <button type="button" class="btn-active-loan-detail"
                                    onclick="openActiveLoanDetail(@js($borrowerName), @js($loan->akun?->nis_nip ?? $loan->nis ?? '-'), @js($loan->barang->nama_barang ?? 'Barang tidak ditemukan'), @js($loan->tanggal_pinjam?->format('d M Y') ?? '-'), @js($loan->keterangan_penggunaan ?: '-'), @js($loan->lokasi_penggunaan ?: '-'))">
                                    Lihat
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="system-table-empty-state">
                                    <div class="system-empty-title">Tidak ada barang yang sedang dipinjam</div>
                                    <div class="system-empty-desc">Peminjaman aktif akan muncul di tabel ini setelah disetujui.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="system-table-footer">
            <div class="system-table-meta">
                <div class="system-per-page-wrapper">
                    <span>Tampilkan</span>
                    <select class="system-per-page-select" onchange="window.location.href=this.value">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ request()->fullUrlWithQuery(['tab' => 'active', 'active_per_page' => $size, 'active_page' => 1]) }}" {{ request('active_per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>data per halaman</span>
                </div>
                @if($activeLoans->total() > 0)
                    <span class="system-table-meta-dot">&bull;</span>
                    <div class="system-table-entries-info">Menampilkan <strong>{{ $activeLoans->firstItem() }}</strong> - <strong>{{ $activeLoans->lastItem() }}</strong> dari <strong>{{ $activeLoans->total() }}</strong> data</div>
                @endif
            </div>
            @if($activeLoans->hasPages())
                <div class="system-pagination-bar">{{ $activeLoans->links('vendor.pagination.simple-default') }}</div>
            @endif
        </div>
    </div>
</div>

<div class="verification-modal-overlay" id="active-loan-detail-modal" style="display:none;">
    <div class="verification-modal-card">
        <h3 class="verification-modal-title">Detail Peminjaman Aktif</h3>
        <dl class="active-loan-detail-list">
            <div><dt>Peminjam</dt><dd id="active-detail-name"></dd></div>
            <div><dt>NIS/NIP</dt><dd id="active-detail-id"></dd></div>
            <div><dt>Barang</dt><dd id="active-detail-item"></dd></div>
            <div><dt>Waktu peminjaman</dt><dd id="active-detail-date"></dd></div>
            <div><dt>Keperluan</dt><dd id="active-detail-purpose"></dd></div>
            <div><dt>Lokasi</dt><dd id="active-detail-location"></dd></div>
        </dl>
        <div class="verification-modal-actions">
            <button type="button" class="verification-btn-cancel" onclick="closeActiveLoanDetail()">Tutup</button>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL 1: REJECT REQUEST MODAL (Sesuai Desain Mockup)          --}}
{{-- ============================================================ --}}
<div class="verification-modal-overlay" id="reject-modal-overlay" style="display: none;">
    <div class="verification-modal-card" id="reject-modal-card">
        <h3 class="verification-modal-title">Tolak Pengajuan Peminjaman</h3>
        
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
                <button type="button" class="verification-btn-cancel" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="verification-btn-reject">Tolak Pengajuan</button>
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
                <button type="button" class="verification-btn-cancel" onclick="closeApproveModal()">Batal</button>
                <button type="submit" class="verification-btn-approve">Ya, Setujui</button>
            </div>
        </form>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL 3: CONFIRM RETURN MODAL (Sesuai Desain Mockup Image 1)  --}}
{{-- ============================================================ --}}
<div class="verification-modal-overlay" id="return-modal-overlay" style="display: none;">
    <div class="verification-modal-card" id="return-modal-card">
        <h3 class="verification-modal-title">Konfirmasi Pengembalian</h3>
        
        <div class="verification-modal-body">
            <p class="verification-modal-question">Apakah Anda yakin ingin mengonfirmasi pengembalian barang ini?</p>
            <div class="verification-modal-meta">
                <p>Peminjam: <span id="return-borrower-name">-</span></p>
                <p>Barang: <span id="return-item-name">-</span></p>
                <p>Kondisi: <span id="return-condition-display" style="font-weight: 600; color: #16a34a;">-</span></p>
            </div>
        </div>

        <form id="confirm-return-form" method="POST" action="">
            @csrf
            <input type="hidden" name="kondisi_barang" id="return-form-kondisi" value="Baik">
            <div class="verification-modal-actions">
                <button type="button" class="verification-btn-cancel" onclick="closeConfirmReturnModal()">Batal</button>
                <button type="submit" class="verification-btn-approve">Ya, Konfirmasi</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* --- Verification Modals --- */
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
    }
    .verification-modal-overlay.active .verification-modal-card {
        transform: scale(1);
    }
    .verification-modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 1rem 0;
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
        font-size: 0.9rem;
        color: #1e293b;
        resize: vertical;
        outline: none;
        min-height: 90px;
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
        font-weight: 500;
        border-radius: 8px;
        cursor: pointer;
    }
    .verification-btn-approve, .verification-btn-reject {
        padding: 0.6rem 1.4rem;
        border: none;
        color: #ffffff;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
    }
    .verification-btn-approve { background: #16a34a; }
    .verification-btn-reject { background: #dc2626; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabBtnRequests = document.getElementById('tab-btn-requests');
        const tabBtnReturns = document.getElementById('tab-btn-returns');
        const tabBtnActive = document.getElementById('tab-btn-active');
        const tabContentRequests = document.getElementById('tab-content-requests');
        const tabContentReturns = document.getElementById('tab-content-returns');
        const tabContentActive = document.getElementById('tab-content-active');

        const tabs = [
            [tabBtnRequests, tabContentRequests],
            [tabBtnReturns, tabContentReturns],
            [tabBtnActive, tabContentActive],
        ];
        tabs.forEach(([button]) => button?.addEventListener('click', () => {
            tabs.forEach(([tabButton, content]) => {
                tabButton?.classList.toggle('sarana-tab-btn--active', tabButton === button);
                content?.classList.toggle('sarana-tab-content--active', tabButton === button);
            });
        }));
    });

    function openApproveModal(kodePinjam, borrowerName, itemName) {
        const form = document.getElementById('approve-request-form');
        form.action = "{{ url('/admin/verifications') }}/" + encodeURIComponent(kodePinjam) + "/approve";
        document.getElementById('approve-borrower-name').textContent = borrowerName;
        document.getElementById('approve-item-name').textContent = itemName;
        const overlay = document.getElementById('approve-modal-overlay');
        overlay.style.display = 'flex';
        void overlay.offsetWidth;
        overlay.classList.add('active');
    }

    function closeApproveModal() {
        const overlay = document.getElementById('approve-modal-overlay');
        overlay.classList.remove('active');
        setTimeout(() => overlay.style.display = 'none', 200);
    }

    function openRejectModal(kodePinjam) {
        const form = document.getElementById('reject-request-form');
        form.action = "{{ url('/admin/verifications') }}/" + encodeURIComponent(kodePinjam) + "/reject";
        document.getElementById('reject-modal-overlay').style.display = 'flex';
        void document.getElementById('reject-modal-overlay').offsetWidth;
        document.getElementById('reject-modal-overlay').classList.add('active');
    }

    function closeRejectModal() {
        const overlay = document.getElementById('reject-modal-overlay');
        overlay.classList.remove('active');
        setTimeout(() => overlay.style.display = 'none', 200);
    }

    function openConfirmReturnModal(kodePinjam, borrowerName, itemName, conditionSelectId) {
        const form = document.getElementById('confirm-return-form');
        form.action = "{{ url('/admin/verifications') }}/" + encodeURIComponent(kodePinjam) + "/confirm-return";
        const conditionSelect = document.getElementById(conditionSelectId);
        const selectedCondition = conditionSelect ? conditionSelect.value : 'Baik';
        document.getElementById('return-borrower-name').textContent = borrowerName;
        document.getElementById('return-item-name').textContent = itemName;
        document.getElementById('return-condition-display').textContent = selectedCondition;
        document.getElementById('return-form-kondisi').value = selectedCondition;
        const overlay = document.getElementById('return-modal-overlay');
        overlay.style.display = 'flex';
        void overlay.offsetWidth;
        overlay.classList.add('active');
    }

    function closeConfirmReturnModal() {
        const overlay = document.getElementById('return-modal-overlay');
        overlay.classList.remove('active');
        setTimeout(() => overlay.style.display = 'none', 200);
    }

    function openActiveLoanDetail(name, identity, item, date, purpose, location) {
        const values = {
            'active-detail-name': name,
            'active-detail-id': identity,
            'active-detail-item': item,
            'active-detail-date': date,
            'active-detail-purpose': purpose,
            'active-detail-location': location,
        };
        Object.entries(values).forEach(([id, value]) => { document.getElementById(id).textContent = value || '-'; });
        const overlay = document.getElementById('active-loan-detail-modal');
        overlay.style.display = 'flex';
        requestAnimationFrame(() => overlay.classList.add('active'));
    }

    function closeActiveLoanDetail() {
        const overlay = document.getElementById('active-loan-detail-modal');
        overlay.classList.remove('active');
        setTimeout(() => overlay.style.display = 'none', 200);
    }

    window.addEventListener('click', function (e) {
        if (e.target === document.getElementById('approve-modal-overlay')) closeApproveModal();
        if (e.target === document.getElementById('reject-modal-overlay')) closeRejectModal();
        if (e.target === document.getElementById('return-modal-overlay')) closeConfirmReturnModal();
        if (e.target === document.getElementById('active-loan-detail-modal')) closeActiveLoanDetail();
    });

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeApproveModal();
            closeRejectModal();
            closeConfirmReturnModal();
            closeActiveLoanDetail();
        }
    });
</script>
@endsection
