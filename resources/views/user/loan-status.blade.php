{{-- 
  HALAMAN STATUS PENGAJUAN & RIWAYAT PEMINJAMAN â€” SINFAS
  File: resources/views/user/loan-status.blade.php
  Fitur:
  - Pelacakan status permohonan pinjam akun pengguna.
  - Badge status dengan styling warna indikator dinamis.
  - Penjelasan alasan penolakan dari admin sarana jika permohonan ditolak.
  - Tombol aksi pengembalian sarana (Return Item) dengan modal popup interaktif atau halaman mandiri.
  - Paginasi daftar riwayat transaksi peminjaman.
--}}
@extends('layouts.app')

@section('title', 'Status Pengajuan - SINFAS')
@section('navbar_title', 'Status Pengajuan')
@section('back_url', route('dashboard'))

@section('content')
<div class="loan-status-container">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="flash-msg flash-msg--success" id="flash-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
            {{ session('success') }}
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;margin-left:auto;font-size:1.1rem;">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-msg flash-msg--error" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;margin-left:auto;font-size:1.1rem;">&times;</button>
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 class="loan-status-title" style="margin: 0;">Status Pengajuan</h2>
    </div>

    <div class="loan-status-list">
        @forelse($loans as $loan)
        <div class="loan-status-card">
            <div class="loan-status-card-left">
                {{-- Item Image Placeholder --}}
                <div class="loan-status-image">
                    @if(!empty($loan->barang?->foto_url))
                        <img src="{{ $loan->barang->foto_url }}" alt="{{ $loan->barang->nama_barang }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                    @else
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Item Info --}}
                <div class="loan-status-info">
                    <h3 class="loan-status-item-name">{{ $loan->barang->nama_barang ?? 'Alat' }}</h3>
                    <p class="loan-status-meta">
                        Dipinjam: {{ $loan->tanggal_pinjam->format('d M Y') }} &nbsp;&bull;&nbsp; Kode Pinjam: <span style="font-weight: 600; color: #1f2937;">{{ $loan->kode_pinjam }}</span>
                    </p>
                    
                    @if($loan->lokasi_penggunaan)
                    <p class="loan-status-meta loan-status-detail" style="color: #6b7280; font-size: 0.78rem;">
                        <span class="loan-status-detail-icon" aria-hidden="true">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <span>{{ $loan->lokasi_penggunaan }}</span>
                    </p>
                    @endif

                    {{-- Return Status Details --}}
                    @if($loan->status_pengajuan === 'disetujui' && $loan->pengembalian)
                        @if(is_null($loan->pengembalian->kondisi_barang))
                            <p class="loan-status-meta loan-status-detail" style="color: #d97706; font-size: 0.78rem; margin-top: 0.2rem; font-weight: 500;">
                                <span class="loan-status-detail-icon" aria-hidden="true">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </span>
                                <span>Pengembalian diajukan pada {{ $loan->pengembalian->tanggal_kembali->format('d M Y') }} (Menunggu verifikasi admin)</span>
                            </p>
                        @else
                            <p class="loan-status-meta loan-status-detail" style="color: #059669; font-size: 0.78rem; margin-top: 0.2rem; font-weight: 500;">
                                <span class="loan-status-detail-icon" aria-hidden="true">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                <span>Telah dikembalikan pada {{ $loan->pengembalian->tanggal_kembali->format('d M Y') }} &bull; Kondisi: <strong>{{ $loan->pengembalian->kondisi_barang }}</strong></span>
                            </p>
                        @endif
                    @endif

                    {{-- Rejection reason --}}
                    @if($loan->status_pengajuan === 'ditolak')
                        @if($loan->alasan_penolakan)
                        <div class="loan-rejection-reason" style="margin-top: 0.5rem; padding: 0.45rem 0.75rem; background: #fef2f2; border-left: 3px solid #ef4444; border-radius: 4px;">
                            <span style="font-weight: 600; font-size: 0.78rem; color: #991b1b;">Alasan Penolakan:</span>
                            <p style="margin: 0.15rem 0 0; font-size: 0.82rem; color: #b91c1c;">{{ $loan->alasan_penolakan }}</p>
                        </div>
                        @else
                        <div class="loan-rejection-reason" style="margin-top: 0.4rem; font-size: 0.8rem; color: #9ca3af; font-style: italic;">
                            (Tidak ada keterangan alasan penolakan)
                        </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Actions & Status Badge --}}
            <div class="loan-status-badge-wrapper">
                <div class="loan-action-group">
                    @if($loan->status_pengajuan === 'menunggu')
                        <span class="loan-status-badge loan-badge--pending" onclick="openWaitingApprovalModal()" style="cursor: pointer;" title="Klik untuk melihat informasi antrean">Menunggu</span>
                        <form action="{{ route('loan.cancel', $loan->kode_pinjam) }}" method="POST" onsubmit="return confirmAppForm(this, 'Batalkan pengajuan peminjaman ini?', {title: 'Batalkan Pengajuan', confirmText: 'Ya, batalkan', danger: true}, event)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-loan-cancel">Batalkan</button>
                        </form>
                    @elseif($loan->status_pengajuan === 'ditolak')
                        <span class="loan-status-badge loan-badge--rejected">Ditolak</span>
                    @elseif($loan->status_pengajuan === 'disetujui')
                        @if(!$loan->pengembalian)
                            {{-- Sedang Dipinjam + Tombol Pengembalian --}}
                            <span class="loan-status-badge loan-badge--approved" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                                Sedang Dipinjam
                            </span>
                            <a href="{{ route('loan.return', $loan->kode_pinjam) }}" class="btn-loan-return">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 14 4 9 9 4"/>
                                    <path d="M20 20v-7a4 4 0 0 0-4-4H4"/>
                                </svg>
                                Kembalikan Barang
                            </a>
                        @elseif(is_null($loan->pengembalian->kondisi_barang))
                            {{-- Pengembalian Diajukan (Pending Admin Confirmation) --}}
                            <span class="loan-status-badge loan-badge--return-pending" title="Menunggu konfirmasi penerimaan oleh Admin Sarana">
                                Menunggu Verifikasi
                            </span>
                        @else
                            {{-- Pengembalian Selesai --}}
                            <span class="loan-status-badge loan-badge--returned">
                                Dikembalikan
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="text-align: center; padding: 3rem 1rem;">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin-bottom: 0.75rem;">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
            </svg>
            <p style="color: #6b7280; font-size: 0.95rem; margin: 0 0 0.5rem;">Belum ada pengajuan peminjaman.</p>
            <a href="{{ route('dashboard') }}" style="color: #1D67F2; font-size: 0.88rem; text-decoration: none; font-weight: 500;">Lihat Katalog Alat &rarr;</a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($loans->hasPages())
    <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
        {{ $loans->links('vendor.pagination.simple-default') }}
    </div>
    @endif
</div>

<script>
    // Auto dismiss flash message
    const flash = document.getElementById('flash-success');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.3s ease';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 300);
        }, 4000);
    }
</script>
@endsection
