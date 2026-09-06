@extends('layouts.app')

@section('title', 'Status Pengajuan - SINFAS')

@section('navbar_title')
    <a href="{{ route('dashboard') }}" style="color: #4b5563; text-decoration: none; font-size: 0.88rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.3rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m15 18-6-6 6-6"/>
        </svg>
        Back to Home
    </a>
@endsection

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

    <h2 class="loan-status-title">Status Pengajuan</h2>

    <div class="loan-status-list">
        @forelse($loans as $loan)
        <div class="loan-status-card">
            <div class="loan-status-card-left">
                {{-- Item Image Placeholder --}}
                <div class="loan-status-image">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#b0b0b0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                </div>

                {{-- Item Info --}}
                <div class="loan-status-info">
                    <h3 class="loan-status-item-name">{{ $loan->barang->nama_barang ?? 'Unknown Item' }}</h3>
                    <p class="loan-status-meta">{{ $loan->tanggal_pinjam->format('Y-m-d') }} &nbsp;&bull;&nbsp; Kode: {{ $loan->kode_pinjam }}</p>
                    @if($loan->lokasi_penggunaan)
                    <p class="loan-status-meta" style="color: #6b7280; font-size: 0.78rem;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $loan->lokasi_penggunaan }}
                    </p>
                    @endif

                    {{-- Rejection reason --}}
                    @if($loan->status_pengajuan === 'ditolak' && $loan->keterangan_penggunaan)
                    <div class="loan-rejection-reason">
                        <span style="font-weight: 600; font-size: 0.78rem; color: #4b5563;">Alasan Penolakan:</span>
                        <p style="margin: 0.15rem 0 0; font-size: 0.82rem; color: #6b7280;">{{ $loan->keterangan_penggunaan }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Status Badge --}}
            <div class="loan-status-badge-wrapper">
                @php
                    $statusClass = match($loan->status_pengajuan) {
                        'menunggu'  => 'loan-badge--pending',
                        'disetujui' => 'loan-badge--approved',
                        'ditolak'   => 'loan-badge--rejected',
                        default     => 'loan-badge--pending',
                    };
                    $statusLabel = match($loan->status_pengajuan) {
                        'menunggu'  => 'Pending',
                        'disetujui' => 'Approved',
                        'ditolak'   => 'Rejected',
                        default     => $loan->status_pengajuan,
                    };
                @endphp
                <span class="loan-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
        </div>
        @empty
        <div class="empty-state" style="text-align: center; padding: 3rem 1rem;">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin-bottom: 0.75rem;">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
            </svg>
            <p style="color: #6b7280; font-size: 0.95rem; margin: 0 0 0.5rem;">Belum ada pengajuan peminjaman.</p>
            <a href="{{ route('dashboard') }}" style="color: #1D67F2; font-size: 0.88rem; text-decoration: none; font-weight: 500;">Lihat Katalog Barang →</a>
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
