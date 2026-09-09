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
<div class="sarana-verifications-container">
    {{-- Tab Navigation Bar --}}
    <div class="sarana-tab-bar" id="verification-tabs">
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'requests' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-requests" data-tab="requests">
            Permintaan Peminjaman
        </button>
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'returns' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-returns" data-tab="returns">
            Menunggu Pengembalian
        </button>
    </div>

    {{-- Tab 1: Pending Requests Section --}}
    @php
        $getReqSortUrl = function($col) {
            if (request('req_sort') === $col) {
                if (request('req_dir') === 'asc') {
                    return request()->fullUrlWithQuery(['tab' => 'requests', 'req_sort' => $col, 'req_dir' => 'desc']);
                }
                $params = request()->except(['req_sort', 'req_dir']);
                $params['tab'] = 'requests';
                return url()->current() . '?' . http_build_query($params);
            }
            return request()->fullUrlWithQuery(['tab' => 'requests', 'req_sort' => $col, 'req_dir' => 'asc']);
        };
        $getReqSortTitle = function($col) {
            if (request('req_sort') === $col) {
                return request('req_dir') === 'asc' 
                    ? 'Klik untuk mengurutkan menurun (Z-A / 9-0)' 
                    : 'Klik untuk mengembalikan ke urutan default (Terbaru di atas)';
            }
            return 'Klik untuk mengurutkan menaik (A-Z / 0-9)';
        };
    @endphp
    <div class="sarana-tab-content {{ $activeTab === 'requests' ? 'sarana-tab-content--active' : '' }}" id="tab-content-requests">
        <div class="system-section-header">
            <h2 class="sarana-section-heading">Permintaan Peminjaman</h2>
        </div>

        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">
                            <a href="{{ $getReqSortUrl('siswa') }}" class="th-content {{ request('req_sort') === 'siswa' ? 'th-content--active' : '' }}" title="{{ $getReqSortTitle('siswa') }}">
                                <span>Peminjam</span>
                                <svg width="11" height="14" viewBox="0 0 12 14" fill="none" style="flex-shrink: 0; vertical-align: middle;">
                                    <path d="M6 1L1.5 6.5H10.5L6 1Z" fill="{{ request('req_sort') === 'siswa' && request('req_dir') === 'asc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('req_sort') === 'siswa' && request('req_dir') === 'desc' ? '0.2' : '0.85' }}"/>
                                    <path d="M6 13L10.5 7.5H1.5L6 13Z" fill="{{ request('req_sort') === 'siswa' && request('req_dir') === 'desc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('req_sort') === 'siswa' && request('req_dir') === 'asc' ? '0.2' : '0.85' }}"/>
                                </svg>
                            </a>
                        </th>
                        <th style="width: 22%;">
                            <a href="{{ $getReqSortUrl('barang') }}" class="th-content {{ request('req_sort') === 'barang' ? 'th-content--active' : '' }}" title="{{ $getReqSortTitle('barang') }}">
                                <span>Barang</span>
                                <svg width="11" height="14" viewBox="0 0 12 14" fill="none" style="flex-shrink: 0; vertical-align: middle;">
                                    <path d="M6 1L1.5 6.5H10.5L6 1Z" fill="{{ request('req_sort') === 'barang' && request('req_dir') === 'asc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('req_sort') === 'barang' && request('req_dir') === 'desc' ? '0.2' : '0.85' }}"/>
                                    <path d="M6 13L10.5 7.5H1.5L6 13Z" fill="{{ request('req_sort') === 'barang' && request('req_dir') === 'desc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('req_sort') === 'barang' && request('req_dir') === 'asc' ? '0.2' : '0.85' }}"/>
                                </svg>
                            </a>
                        </th>
                        <th style="width: 15%;">
                            <a href="{{ $getReqSortUrl('lokasi_penggunaan') }}" class="th-content {{ request('req_sort') === 'lokasi_penggunaan' ? 'th-content--active' : '' }}" title="{{ $getReqSortTitle('lokasi_penggunaan') }}">
                                <span>Lokasi</span>
                                <svg width="11" height="14" viewBox="0 0 12 14" fill="none" style="flex-shrink: 0; vertical-align: middle;">
                                    <path d="M6 1L1.5 6.5H10.5L6 1Z" fill="{{ request('req_sort') === 'lokasi_penggunaan' && request('req_dir') === 'asc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('req_sort') === 'lokasi_penggunaan' && request('req_dir') === 'desc' ? '0.2' : '0.85' }}"/>
                                    <path d="M6 13L10.5 7.5H1.5L6 13Z" fill="{{ request('req_sort') === 'lokasi_penggunaan' && request('req_dir') === 'desc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('req_sort') === 'lokasi_penggunaan' && request('req_dir') === 'asc' ? '0.2' : '0.85' }}"/>
                                </svg>
                            </a>
                        </th>
                        <th style="width: 17%;">
                            <a href="{{ $getReqSortUrl('keterangan_penggunaan') }}" class="th-content {{ request('req_sort') === 'keterangan_penggunaan' ? 'th-content--active' : '' }}" title="{{ $getReqSortTitle('keterangan_penggunaan') }}">
                                <span>Keperluan</span>
                                <svg width="11" height="14" viewBox="0 0 12 14" fill="none" style="flex-shrink: 0; vertical-align: middle;">
                                    <path d="M6 1L1.5 6.5H10.5L6 1Z" fill="{{ request('req_sort') === 'keterangan_penggunaan' && request('req_dir') === 'asc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('req_sort') === 'keterangan_penggunaan' && request('req_dir') === 'desc' ? '0.2' : '0.85' }}"/>
                                    <path d="M6 13L10.5 7.5H1.5L6 13Z" fill="{{ request('req_sort') === 'keterangan_penggunaan' && request('req_dir') === 'desc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('req_sort') === 'keterangan_penggunaan' && request('req_dir') === 'asc' ? '0.2' : '0.85' }}"/>
                                </svg>
                            </a>
                        </th>
                        <th style="width: 14%;">
                            <a href="{{ $getReqSortUrl('tanggal_pinjam') }}" class="th-content {{ request('req_sort') === 'tanggal_pinjam' ? 'th-content--active' : '' }}" title="{{ $getReqSortTitle('tanggal_pinjam') }}">
                                <span>Tanggal Pinjam</span>
                                <svg width="11" height="14" viewBox="0 0 12 14" fill="none" style="flex-shrink: 0; vertical-align: middle;">
                                    <path d="M6 1L1.5 6.5H10.5L6 1Z" fill="{{ request('req_sort') === 'tanggal_pinjam' && request('req_dir') === 'asc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('req_sort') === 'tanggal_pinjam' && request('req_dir') === 'desc' ? '0.2' : '0.85' }}"/>
                                    <path d="M6 13L10.5 7.5H1.5L6 13Z" fill="{{ request('req_sort') === 'tanggal_pinjam' && request('req_dir') === 'desc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('req_sort') === 'tanggal_pinjam' && request('req_dir') === 'asc' ? '0.2' : '0.85' }}"/>
                                </svg>
                            </a>
                        </th>
                        <th style="width: 14%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $req)
                    <tr>
                        <td class="td-name">{{ $req->siswa->nama ?? '-' }}</td>
                        <td class="td-item">{{ $req->barang->nama_barang ?? '-' }}</td>
                        <td class="td-location">{{ $req->lokasi_penggunaan ?? '-' }}</td>
                        <td class="td-category" style="font-size: 0.82rem;">{{ Str::limit($req->keterangan_penggunaan, 30) ?? '-' }}</td>
                        <td class="td-date">{{ $req->tanggal_pinjam->format('Y-m-d') }}</td>
                        <td>
                            <div class="action-btn-group">
                                <button 
                                    type="button" 
                                    class="btn-action btn-approve"
                                    onclick="openApproveModal('{{ $req->kode_pinjam }}', '{{ addslashes($req->siswa->nama ?? 'Siswa') }}', '{{ addslashes($req->barang->nama_barang ?? 'Barang') }}')">
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
                        <td colspan="6" style="text-align: center; color: #9ca3af; padding: 2rem;">Tidak ada permintaan peminjaman yang menunggu.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($pendingRequests->hasPages())
        <div class="system-pagination-bar">
            {{ $pendingRequests->links('vendor.pagination.simple-default') }}
        </div>
        @endif
    </div>

    {{-- Tab 2: Pending Returns Section --}}
    @php
        $getRetSortUrl = function($col) {
            if (request('ret_sort') === $col) {
                if (request('ret_dir') === 'asc') {
                    return request()->fullUrlWithQuery(['tab' => 'returns', 'ret_sort' => $col, 'ret_dir' => 'desc']);
                }
                $params = request()->except(['ret_sort', 'ret_dir']);
                $params['tab'] = 'returns';
                return url()->current() . '?' . http_build_query($params);
            }
            return request()->fullUrlWithQuery(['tab' => 'returns', 'ret_sort' => $col, 'ret_dir' => 'asc']);
        };
        $getRetSortTitle = function($col) {
            if (request('ret_sort') === $col) {
                return request('ret_dir') === 'asc' 
                    ? 'Klik untuk mengurutkan menurun (Z-A / 9-0)' 
                    : 'Klik untuk mengembalikan ke urutan default (Terbaru di atas)';
            }
            return 'Klik untuk mengurutkan menaik (A-Z / 0-9)';
        };
    @endphp
    <div class="sarana-tab-content {{ $activeTab === 'returns' ? 'sarana-tab-content--active' : '' }}" id="tab-content-returns">
        <div class="system-section-header">
            <h2 class="sarana-section-heading">Menunggu Pengembalian</h2>
        </div>

        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">
                            <a href="{{ $getRetSortUrl('siswa') }}" class="th-content {{ request('ret_sort') === 'siswa' ? 'th-content--active' : '' }}" title="{{ $getRetSortTitle('siswa') }}">
                                <span>Peminjam</span>
                                <svg width="11" height="14" viewBox="0 0 12 14" fill="none" style="flex-shrink: 0; vertical-align: middle;">
                                    <path d="M6 1L1.5 6.5H10.5L6 1Z" fill="{{ request('ret_sort') === 'siswa' && request('ret_dir') === 'asc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('ret_sort') === 'siswa' && request('ret_dir') === 'desc' ? '0.2' : '0.85' }}"/>
                                    <path d="M6 13L10.5 7.5H1.5L6 13Z" fill="{{ request('ret_sort') === 'siswa' && request('ret_dir') === 'desc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('ret_sort') === 'siswa' && request('ret_dir') === 'asc' ? '0.2' : '0.85' }}"/>
                                </svg>
                            </a>
                        </th>
                        <th style="width: 22%;">
                            <a href="{{ $getRetSortUrl('barang') }}" class="th-content {{ request('ret_sort') === 'barang' ? 'th-content--active' : '' }}" title="{{ $getRetSortTitle('barang') }}">
                                <span>Barang</span>
                                <svg width="11" height="14" viewBox="0 0 12 14" fill="none" style="flex-shrink: 0; vertical-align: middle;">
                                    <path d="M6 1L1.5 6.5H10.5L6 1Z" fill="{{ request('ret_sort') === 'barang' && request('ret_dir') === 'asc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('ret_sort') === 'barang' && request('ret_dir') === 'desc' ? '0.2' : '0.85' }}"/>
                                    <path d="M6 13L10.5 7.5H1.5L6 13Z" fill="{{ request('ret_sort') === 'barang' && request('ret_dir') === 'desc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('ret_sort') === 'barang' && request('ret_dir') === 'asc' ? '0.2' : '0.85' }}"/>
                                </svg>
                            </a>
                        </th>
                        <th style="width: 14%;">
                            <a href="{{ $getRetSortUrl('tanggal_pinjam') }}" class="th-content {{ request('ret_sort') === 'tanggal_pinjam' ? 'th-content--active' : '' }}" title="{{ $getRetSortTitle('tanggal_pinjam') }}">
                                <span>Tanggal Pinjam</span>
                                <svg width="11" height="14" viewBox="0 0 12 14" fill="none" style="flex-shrink: 0; vertical-align: middle;">
                                    <path d="M6 1L1.5 6.5H10.5L6 1Z" fill="{{ request('ret_sort') === 'tanggal_pinjam' && request('ret_dir') === 'asc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('ret_sort') === 'tanggal_pinjam' && request('ret_dir') === 'desc' ? '0.2' : '0.85' }}"/>
                                    <path d="M6 13L10.5 7.5H1.5L6 13Z" fill="{{ request('ret_sort') === 'tanggal_pinjam' && request('ret_dir') === 'desc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('ret_sort') === 'tanggal_pinjam' && request('ret_dir') === 'asc' ? '0.2' : '0.85' }}"/>
                                </svg>
                            </a>
                        </th>
                        <th style="width: 14%;">
                            <a href="{{ $getRetSortUrl('tanggal_kembali') }}" class="th-content {{ request('ret_sort') === 'tanggal_kembali' ? 'th-content--active' : '' }}" title="{{ $getRetSortTitle('tanggal_kembali') }}">
                                <span>Tanggal Kembali</span>
                                <svg width="11" height="14" viewBox="0 0 12 14" fill="none" style="flex-shrink: 0; vertical-align: middle;">
                                    <path d="M6 1L1.5 6.5H10.5L6 1Z" fill="{{ request('ret_sort') === 'tanggal_kembali' && request('ret_dir') === 'asc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('ret_sort') === 'tanggal_kembali' && request('ret_dir') === 'desc' ? '0.2' : '0.85' }}"/>
                                    <path d="M6 13L10.5 7.5H1.5L6 13Z" fill="{{ request('ret_sort') === 'tanggal_kembali' && request('ret_dir') === 'desc' ? '#1D67F2' : '#111827' }}" opacity="{{ request('ret_sort') === 'tanggal_kembali' && request('ret_dir') === 'asc' ? '0.2' : '0.85' }}"/>
                                </svg>
                            </a>
                        </th>
                        <th style="width: 12%; text-align: center;">Bukti</th>
                        <th style="width: 10%;">Kondisi</th>
                        <th style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingReturns as $ret)
                    <tr>
                        <td class="td-name">{{ $ret->siswa->nama ?? '-' }}</td>
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
                                    $rawBukti = $ret->pengembalian->bukti_foto_video;
                                    $buktiUrl = str_starts_with($rawBukti, 'uploads/') 
                                        ? asset($rawBukti) 
                                        : (str_starts_with($rawBukti, 'http') ? $rawBukti : asset('storage/' . $rawBukti));
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
                            <button type="button" class="btn-confirm-return" onclick="openConfirmReturnModal('{{ $ret->kode_pinjam }}', '{{ addslashes($ret->siswa->nama ?? 'Siswa') }}', '{{ addslashes($ret->barang->nama_barang ?? 'Barang') }}', 'kondisi-select-{{ $ret->kode_pinjam }}')">Konfirmasi</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #9ca3af; padding: 2rem;">Tidak ada pengembalian yang menunggu konfirmasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($pendingReturns->hasPages())
        <div class="system-pagination-bar">
            {{ $pendingReturns->links('vendor.pagination.simple-default') }}
        </div>
        @endif
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

    window.addEventListener('click', function (e) {
        if (e.target === document.getElementById('approve-modal-overlay')) closeApproveModal();
        if (e.target === document.getElementById('reject-modal-overlay')) closeRejectModal();
        if (e.target === document.getElementById('return-modal-overlay')) closeConfirmReturnModal();
    });

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeApproveModal();
            closeRejectModal();
            closeConfirmReturnModal();
        }
    });
</script>
@endsection
