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
    .borrower-type-badge--pegawai { color:#7c3aed; background:#f5f3ff; }
    .borrower-type-badge--active { color:#047857; background:#ecfdf5; }
    .borrower-sub-badge { display:inline-block; padding:.1rem .45rem; border-radius:4px; font-size:.72rem; font-weight:700; line-height:1.2; letter-spacing:.02em; }
    .borrower-sub-badge--class { color:#1d4ed8; background:#eff6ff; border:1px solid #bfdbfe; }
    .borrower-sub-badge--role { color:#047857; background:#ecfdf5; border:1px solid #a7f3d0; }
    .sarana-tab-count { display:inline-flex; min-width:1.25rem; height:1.25rem; margin-left:.35rem; padding:0 .25rem; align-items:center; justify-content:center; border-radius:999px; background:rgba(255,255,255,.7); font-size:.72rem; }
    .loan-overdue-label { display:block; margin-top:.2rem; color:#b91c1c; font-size:.75rem; font-weight:700; }
    .btn-active-loan-detail { border:1px solid #bfdbfe; border-radius:7px; padding:.4rem .7rem; background:#eff6ff; color:#1d4ed8; font-weight:600; cursor:pointer; }
    .btn-active-loan-detail:hover { background:#dbeafe; }
    .btn-borrower-name-link { background:none; border:none; padding:0; margin:0; font-weight:600; font-size:inherit; color:#0f172a; cursor:pointer; text-align:left; transition:color 0.15s ease; }
    .btn-borrower-name-link:hover { color:#1e40af; text-decoration:underline; }
    .verification-modal-overlay.active .account-modal-card { transform:scale(1); }
    @media (max-width: 768px) { .sarana-tab-bar { flex-wrap:wrap; } .sarana-tab-btn { flex:1 1 11rem; } }
</style>
<div class="sarana-verifications-container">
    {{-- Tab Navigation Bar --}}
    <div class="sarana-tab-bar" id="verification-tabs">
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'requests' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-requests" data-tab="requests">
            Permintaan Peminjaman <span class="sarana-tab-count">{{ $pendingRequests->total() }}</span>
        </button>
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'returns' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-returns" data-tab="returns">
            Menunggu Pengembalian <span class="sarana-tab-count">{{ $pendingReturns->total() }}</span>
        </button>
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'active' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-active" data-tab="active">
            Sedang Dipinjam <span class="sarana-tab-count">{{ $activeLoans->total() }}</span>
        </button>
    </div>

    {{-- Live Real-time Alert Banner --}}
    <div id="live-new-loan-banner" style="display: none; margin-bottom: 1.25rem; padding: 0.9rem 1.25rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #dbeafe; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </div>
            <div>
                <strong id="live-new-loan-title" style="color: #1e40af; font-size: 0.92rem; display: block;">Ada permohonan baru yang baru saja masuk!</strong>
                <span id="live-new-loan-desc" style="color: #3b82f6; font-size: 0.82rem;">Data pada tabel perlu dimuat ulang agar pengajuan terbaru tampil.</span>
            </div>
        </div>
        <button type="button" onclick="location.reload()" style="background: #2563eb; color: #ffffff; border: none; padding: 0.55rem 1.15rem; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; white-space: nowrap; transition: background 0.15s ease; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);">
            Muat Ulang Sekarang
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
                        <th><x-sortable-table-heading label="Alat" column="barang" sort-key="req_sort" dir-key="req_dir" page-key="req_page" tab="requests" /></th>
                        <th><x-sortable-table-heading label="Lokasi" column="lokasi_penggunaan" sort-key="req_sort" dir-key="req_dir" page-key="req_page" tab="requests" /></th>
                        <th><x-sortable-table-heading label="Keperluan" column="keterangan_penggunaan" sort-key="req_sort" dir-key="req_dir" page-key="req_page" tab="requests" /></th>
                        <th><x-sortable-table-heading label="Tanggal Pinjam" column="tanggal_pinjam" sort-key="req_sort" dir-key="req_dir" page-key="req_page" tab="requests" /></th>
                        <th style="width: 14%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $req)
                        @php
                            $studentClassFallback = [
                                '10223001' => 'XII RPL 1',
                                '10223002' => 'XII TKJ 2',
                                '10223003' => 'XI RPL 2',
                                '10223004' => 'XI DKV 1',
                            ];
                            $reqNisNip = $req->akun?->nis_nip ?? $req->nis ?? '-';
                            $reqKelas = $req->akun?->siswa?->kelas ?? $req->siswa?->kelas;
                            if (!$reqKelas && isset($studentClassFallback[$reqNisNip])) {
                                $reqKelas = $studentClassFallback[$reqNisNip];
                            }
                            $reqJabatan = $req->akun?->pegawai?->jabatan;
                            $reqClassOrRole = $reqKelas ?: ($reqJabatan ?: '-');
                        @endphp
                    <tr>
                        <td class="td-number">{{ $loop->iteration + ($pendingRequests->currentPage() - 1) * $pendingRequests->perPage() }}</td>
                        <td class="td-name">
                            <button type="button" class="btn-borrower-name-link"
                                onclick="openActiveLoanDetail({
                                    name: @js($req->peminjam_nama),
                                    id: @js($reqNisNip),
                                    role: @js($req->akun?->role_label ?? $req->peminjam_status),
                                    classOrRole: @js($reqClassOrRole),
                                    contact: @js($req->akun?->nomor_kontak ?: ($req->siswa?->no_hp ?: '-')),
                                    username: @js($req->akun?->username ?: '-'),
                                    email: @js($req->akun?->email ?: '-'),
                                    photo: @js($req->akun?->foto_url),
                                    item: @js($req->barang->nama_barang ?? '-'),
                                    date: @js($req->tanggal_pinjam->format('d M Y')),
                                    duration: 'Menunggu Persetujuan',
                                    purpose: @js($req->keterangan_penggunaan ?: '-'),
                                    location: @js($req->lokasi_penggunaan ?: '-')
                                })" title="Klik untuk lihat detail akun">
                                {{ $req->peminjam_nama }}
                            </button>
                            <div style="display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; margin-top: .15rem;">
                                <small style="color: #64748b; font-size: 0.78rem;">{{ $reqNisNip }}</small>
                                @if($reqKelas)
                                    <span class="borrower-sub-badge borrower-sub-badge--class">{{ $reqKelas }}</span>
                                @elseif($reqJabatan)
                                    <span class="borrower-sub-badge borrower-sub-badge--role">{{ $reqJabatan }}</span>
                                @endif
                            </div>
                        </td>
                        <td><span class="borrower-type-badge {{ strtolower($req->peminjam_status) === 'pegawai' ? 'borrower-type-badge--pegawai' : 'borrower-type-badge--student' }}">{{ $req->peminjam_status }}</span></td>
                        <td>{{ $req->akun?->nomor_kontak ?: ($req->siswa?->no_hp ?: '-') }}</td>
                        <td class="td-item">{{ $req->barang->nama_barang ?? '-' }}</td>
                        <td class="td-location">{{ $req->lokasi_penggunaan ?? '-' }}</td>
                        <td class="td-category" style="font-size: 0.82rem;">{{ Str::limit($req->keterangan_penggunaan, 30) ?? '-' }}</td>
                        <td class="td-date">{{ $req->tanggal_pinjam->format('d M Y') }}</td>
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
                        <th><x-sortable-table-heading label="Alat" column="barang" sort-key="ret_sort" dir-key="ret_dir" page-key="ret_page" tab="returns" /></th>
                        <th><x-sortable-table-heading label="Tanggal Pinjam" column="tanggal_pinjam" sort-key="ret_sort" dir-key="ret_dir" page-key="ret_page" tab="returns" /></th>
                        <th><x-sortable-table-heading label="Tanggal Kembali" column="tanggal_kembali" sort-key="ret_sort" dir-key="ret_dir" page-key="ret_page" tab="returns" /></th>
                        <th style="width: 11%; text-align: center;"><x-sortable-table-heading label="Bukti" column="bukti" sort-key="ret_sort" dir-key="ret_dir" page-key="ret_page" tab="returns" /></th>
                        <th style="width: 11%;">Kondisi</th>
                        <th style="width: 11%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingReturns as $ret)
                        @php
                            $studentClassFallback = [
                                '10223001' => 'XII RPL 1',
                                '10223002' => 'XII TKJ 2',
                                '10223003' => 'XI RPL 2',
                                '10223004' => 'XI DKV 1',
                            ];
                            $retBorrowerName = $ret->akun?->nama ?? $ret->peminjam_nama;
                            $retNisNip = $ret->akun?->nis_nip ?? $ret->nis ?? '-';
                            $retKelas = $ret->akun?->siswa?->kelas ?? $ret->siswa?->kelas;
                            if (!$retKelas && isset($studentClassFallback[$retNisNip])) {
                                $retKelas = $studentClassFallback[$retNisNip];
                            }
                            $retJabatan = $ret->akun?->pegawai?->jabatan;
                            $retClassOrRole = $retKelas ?: ($retJabatan ?: '-');
                        @endphp
                    <tr>
                        <td class="td-number">{{ $loop->iteration + ($pendingReturns->currentPage() - 1) * $pendingReturns->perPage() }}</td>
                        <td class="td-name">
                            <button type="button" class="btn-borrower-name-link"
                                onclick="openActiveLoanDetail({
                                    name: @js($retBorrowerName),
                                    id: @js($retNisNip),
                                    role: @js($ret->akun?->role_label ?? $ret->peminjam_status),
                                    classOrRole: @js($retClassOrRole),
                                    contact: @js($ret->akun?->nomor_kontak ?: ($ret->siswa?->no_hp ?: '-')),
                                    username: @js($ret->akun?->username ?: '-'),
                                    email: @js($ret->akun?->email ?: '-'),
                                    photo: @js($ret->akun?->foto_url),
                                    item: @js($ret->barang->nama_barang ?? '-'),
                                    date: @js($ret->tanggal_pinjam ? $ret->tanggal_pinjam->format('d M Y') : '-'),
                                    duration: 'Menunggu Konfirmasi Kembali',
                                    purpose: @js($ret->keterangan_penggunaan ?: '-'),
                                    location: @js($ret->lokasi_penggunaan ?: '-')
                                })" title="Klik untuk lihat detail akun">
                                {{ $retBorrowerName }}
                            </button>
                            <div style="display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; margin-top: .15rem;">
                                <small style="color: #64748b; font-size: 0.78rem;">{{ $retNisNip }}</small>
                                @if($retKelas)
                                    <span class="borrower-sub-badge borrower-sub-badge--class">{{ $retKelas }}</span>
                                @elseif($retJabatan)
                                    <span class="borrower-sub-badge borrower-sub-badge--role">{{ $retJabatan }}</span>
                                @endif
                            </div>
                        </td>
                        <td><span class="borrower-type-badge {{ strtolower($ret->peminjam_status) === 'pegawai' ? 'borrower-type-badge--pegawai' : 'borrower-type-badge--student' }}">{{ $ret->peminjam_status }}</span></td>
                        <td>{{ $ret->akun?->nomor_kontak ?: ($ret->siswa?->no_hp ?: '-') }}</td>
                        <td class="td-item">
                            {{ $ret->barang->nama_barang ?? '-' }}
                            @if(!empty($ret->pengembalian->catatan))
                                <div style="font-size: 0.76rem; color: #6b7280; margin-top: 0.25rem; font-style: italic; line-height: 1.3;">
                                    <span style="font-weight: 600; color: #4b5563; font-style: normal;">Catatan:</span> "{{ $ret->pengembalian->catatan }}"
                                </div>
                            @endif
                        </td>
                        <td class="td-date">{{ $ret->tanggal_pinjam ? $ret->tanggal_pinjam->format('d M Y') : '-' }}</td>
                        <td class="td-date">{{ $ret->pengembalian->tanggal_kembali ? $ret->pengembalian->tanggal_kembali->format('d M Y') : '-' }}</td>
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
                        <th><x-sortable-table-heading label="Status Peminjam" column="status_peminjam" sort-key="active_sort" dir-key="active_dir" page-key="active_page" tab="active" /></th>
                        <th><x-sortable-table-heading label="Nomor Telepon" column="nomor_kontak" sort-key="active_sort" dir-key="active_dir" page-key="active_page" tab="active" /></th>
                        <th><x-sortable-table-heading label="Alat" column="barang" sort-key="active_sort" dir-key="active_dir" page-key="active_page" tab="active" /></th>
                        <th><x-sortable-table-heading label="Tanggal Pinjam" column="tanggal_pinjam" sort-key="active_sort" dir-key="active_dir" page-key="active_page" tab="active" /></th>
                        <th><x-sortable-table-heading label="Lama Dipinjam" column="lama_dipinjam" sort-key="active_sort" dir-key="active_dir" page-key="active_page" tab="active" /></th>
                        <th>Status</th>
                        <th>Aksi</th>
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
                            $studentClassFallback = [
                                '10223001' => 'XII RPL 1',
                                '10223002' => 'XII TKJ 2',
                                '10223003' => 'XI RPL 2',
                                '10223004' => 'XI DKV 1',
                            ];
                            $loanNisNip = $loan->akun?->nis_nip ?? $loan->nis ?? '-';
                            $loanKelas = $loan->akun?->siswa?->kelas ?? $loan->siswa?->kelas;
                            if (!$loanKelas && isset($studentClassFallback[$loanNisNip])) {
                                $loanKelas = $studentClassFallback[$loanNisNip];
                            }
                            $loanJabatan = $loan->akun?->pegawai?->jabatan;
                            $loanClassOrRole = $loanKelas ?: ($loanJabatan ?: '-');
                            $loanRole = $loan->akun?->role_label ?? $loan->peminjam_status;
                            $loanContact = $loan->akun?->nomor_kontak ?: ($loan->siswa?->no_hp ?: '-');
                            $loanUsername = $loan->akun?->username ?: '-';
                            $loanEmail = $loan->akun?->email ?: '-';
                            $loanPhoto = $loan->akun?->foto_url;
                            $loanItem = $loan->barang->nama_barang ?? 'Alat tidak ditemukan';
                            $loanDate = $loan->tanggal_pinjam?->format('d M Y') ?? '-';
                            $loanDuration = $daysBorrowed . ' hari' . ($lateDays > 0 ? ' (Terlambat ' . $lateDays . ' hari)' : '');
                            $loanPurpose = $loan->keterangan_penggunaan ?: '-';
                            $loanLocation = $loan->lokasi_penggunaan ?: '-';
                        @endphp
                        <tr>
                            <td class="td-number">{{ $loop->iteration + ($activeLoans->currentPage() - 1) * $activeLoans->perPage() }}</td>
                            <td class="td-name">
                                <button type="button" class="btn-borrower-name-link"
                                    onclick="openActiveLoanDetail({
                                        name: @js($borrowerName),
                                        id: @js($loanNisNip),
                                        role: @js($loanRole),
                                        classOrRole: @js($loanClassOrRole),
                                        contact: @js($loanContact),
                                        username: @js($loanUsername),
                                        email: @js($loanEmail),
                                        photo: @js($loanPhoto),
                                        item: @js($loanItem),
                                        date: @js($loanDate),
                                        duration: @js($loanDuration),
                                        purpose: @js($loanPurpose),
                                        location: @js($loanLocation)
                                    })" title="Klik untuk lihat detail akun">
                                    {{ $borrowerName }}
                                </button>
                                <div style="display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; margin-top: .15rem;">
                                    <small style="color: #64748b; font-size: 0.78rem;">{{ $loanNisNip }}</small>
                                    @if($loanKelas)
                                        <span class="borrower-sub-badge borrower-sub-badge--class">{{ $loanKelas }}</span>
                                    @elseif($loanJabatan)
                                        <span class="borrower-sub-badge borrower-sub-badge--role">{{ $loanJabatan }}</span>
                                    @endif
                                </div>
                            </td>
                            <td><span class="borrower-type-badge {{ strtolower($loan->peminjam_status) === 'pegawai' ? 'borrower-type-badge--pegawai' : 'borrower-type-badge--student' }}">{{ $loan->peminjam_status }}</span></td>
                            <td>{{ $loanContact }}</td>
                            <td>{{ $loanItem }}</td>
                            <td class="td-date">{{ $loanDate }}</td>
                            <td>
                                <strong>{{ $daysBorrowed }} hari</strong>
                                @if($lateDays > 0)
                                    <span class="loan-overdue-label">Terlambat {{ $lateDays }} hari</span>
                                @endif
                            </td>
                            <td><span class="borrower-type-badge borrower-type-badge--active">Masih dipinjam</span></td>
                            <td>
                                <button type="button" class="btn-active-loan-detail"
                                    onclick="openActiveLoanDetail({
                                        name: @js($borrowerName),
                                        id: @js($loanNisNip),
                                        role: @js($loanRole),
                                        classOrRole: @js($loanClassOrRole),
                                        contact: @js($loanContact),
                                        username: @js($loanUsername),
                                        email: @js($loanEmail),
                                        photo: @js($loanPhoto),
                                        item: @js($loanItem),
                                        date: @js($loanDate),
                                        duration: @js($loanDuration),
                                        purpose: @js($loanPurpose),
                                        location: @js($loanLocation)
                                    })">
                                    Lihat Detail
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
    <div class="account-modal-card" style="width: 480px; max-width: 94vw; max-height: 90vh;">
        <div class="account-modal-body" style="padding: 1.75rem 1.85rem 1.5rem; overflow-y: auto;">
            <h3 class="account-modal-title">Detail Akun</h3>
            <div class="account-modal-divider"></div>

            {{-- Avatar Box (Foto Profil di Atas Sesuai Admin Sistem) --}}
            <div class="detail-avatar-container" style="margin: 0.5rem 0;">
                <div class="detail-avatar-box">
                    <img src="" id="active-detail-avatar" alt="Foto Profil" style="display: none;">
                    <div id="active-detail-avatar-placeholder" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">
                        <svg class="w-16 h-16" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Form Readonly: Data Identitas Akun --}}
            <div class="account-form-group">
                <label class="account-form-label">Nama</label>
                <input type="text" id="active-detail-name-input" class="account-form-input account-form-input--readonly" readonly>
            </div>

            <div class="account-form-row">
                <div class="account-form-col">
                    <label class="account-form-label">NIS/NIP</label>
                    <input type="text" id="active-detail-id-input" class="account-form-input account-form-input--readonly" readonly>
                </div>
                <div class="account-form-col">
                    <label class="account-form-label">Peran</label>
                    <input type="text" id="active-detail-role-input" class="account-form-input account-form-input--readonly" readonly>
                </div>
            </div>

            <div class="account-form-row">
                <div class="account-form-col">
                    <label class="account-form-label">Kelas / Jabatan</label>
                    <input type="text" id="active-detail-class-input" class="account-form-input account-form-input--readonly" readonly>
                </div>
                <div class="account-form-col">
                    <label class="account-form-label">Nomor Kontak</label>
                    <input type="text" id="active-detail-contact-input" class="account-form-input account-form-input--readonly" readonly>
                </div>
            </div>

            <div class="account-form-row">
                <div class="account-form-col">
                    <label class="account-form-label">Username</label>
                    <input type="text" id="active-detail-username-input" class="account-form-input account-form-input--readonly" readonly>
                </div>
                <div class="account-form-col">
                    <label class="account-form-label">Email</label>
                    <input type="text" id="active-detail-email-input" class="account-form-input account-form-input--readonly" readonly>
                </div>
            </div>

            <div class="account-modal-divider"></div>

            {{-- Section Peminjaman Terkait --}}
            <section class="account-loan-history" id="active-detail-loan-section">
                <h4 style="margin: 0 0 0.5rem; color: #1e293b; font-size: 0.9rem; font-weight: 700;">Informasi Peminjaman Sarana</h4>
                <div class="account-loan-history-item" style="border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; padding: 0.75rem 0.9rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; flex-wrap: wrap;">
                        <strong id="active-detail-item-text" style="color: #0f172a; font-size: 0.88rem;">-</strong>
                        <span id="active-detail-duration-badge" class="borrower-type-badge borrower-type-badge--active" style="font-size: 0.72rem;">-</span>
                    </div>
                    <p id="active-detail-date-text" style="margin: 0.35rem 0 0; color: #64748b; font-size: 0.78rem;">-</p>
                    <p id="active-detail-meta-text" style="margin: 0.25rem 0 0; color: #64748b; font-size: 0.78rem;">-</p>
                </div>
            </section>

            <div class="account-modal-footer" style="margin-top: 0.5rem; display: flex; justify-content: flex-end;">
                <button type="button" class="btn-modal-exit" onclick="closeActiveLoanDetail()">Tutup</button>
            </div>
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

    function openActiveLoanDetail(firstArg, identity, classOrRole, item, date, purpose, location, photoUrl) {
        let d = {};
        if (typeof firstArg === 'object' && firstArg !== null) {
            d = firstArg;
        } else {
            d = {
                name: firstArg,
                id: identity,
                classOrRole: classOrRole,
                item: item,
                date: date,
                purpose: purpose,
                location: location,
                photo: photoUrl || '',
                role: 'Siswa',
                contact: '-',
                username: '-',
                email: '-',
                duration: 'Sedang dipinjam'
            };
        }

        const avatar = document.getElementById('active-detail-avatar');
        const avatarPlaceholder = document.getElementById('active-detail-avatar-placeholder');
        if (d.photo) {
            avatar.src = d.photo;
            avatar.style.display = 'block';
            avatarPlaceholder.style.display = 'none';
        } else {
            avatar.src = '';
            avatar.style.display = 'none';
            avatarPlaceholder.style.display = 'flex';
        }

        document.getElementById('active-detail-name-input').value = d.name || '-';
        document.getElementById('active-detail-id-input').value = d.id || '-';
        document.getElementById('active-detail-role-input').value = d.role || '-';
        document.getElementById('active-detail-class-input').value = d.classOrRole || '-';
        document.getElementById('active-detail-contact-input').value = d.contact || '-';
        document.getElementById('active-detail-username-input').value = d.username || '-';
        document.getElementById('active-detail-email-input').value = d.email || '-';

        const loanSection = document.getElementById('active-detail-loan-section');
        if (d.item && d.item !== '-') {
            loanSection.style.display = 'block';
            document.getElementById('active-detail-item-text').textContent = d.item;
            document.getElementById('active-detail-date-text').textContent = 'Waktu Pinjam: ' + (d.date || '-');
            document.getElementById('active-detail-meta-text').textContent = 'Lokasi: ' + (d.location || '-') + ' · Keperluan: ' + (d.purpose || '-');
            const badge = document.getElementById('active-detail-duration-badge');
            badge.textContent = d.duration || 'Aktif';
            if ((d.duration || '').toLowerCase().includes('terlambat')) {
                badge.style.color = '#b91c1c';
                badge.style.background = '#fef2f2';
            } else {
                badge.style.color = '#047857';
                badge.style.background = '#ecfdf5';
            }
        } else {
            loanSection.style.display = 'none';
        }

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

    // Handle real-time push notification alert banner
    window.addEventListener('sinfas:new-notification', function (e) {
        const banner = document.getElementById('live-new-loan-banner');
        if (banner) {
            const item = e.detail;
            if (item && item.judul) {
                const titleEl = document.getElementById('live-new-loan-title');
                const descEl = document.getElementById('live-new-loan-desc');
                if (titleEl) titleEl.textContent = item.judul;
                if (descEl) descEl.textContent = item.pesan || 'Ada permohonan baru yang menunggu diverifikasi.';
            }
            banner.style.display = 'flex';
        }
    });
</script>
@endsection
