{{-- 
  HALAMAN PUSAT NOTIFIKASI PENGGUNA (SISWA) — SINFAS
  File: resources/views/user/notifications.blade.php
  Fitur Sesuai Desain Figma:
  - Header navigasi "Kembali ke Beranda" & judul halaman "Pusat Notifikasi"
  - Filter status: Semua, Belum Dibaca, Sudah Dibaca
  - Tombol "Tandai Semua Sudah Dibaca"
  - Tampilan kartu notifikasi mirip riwayat peminjaman (Foto barang, Nama sarana, Waktu, Durasi hari berjalan)
  - Color-coded durasi peminjaman otomatis:
    * 1 - 9 hari: Abu-abu (Gray)
    * 10 - 50 hari: Kuning (Yellow)
    * > 50 hari: Merah (Red)
  - Badge status vertikal kanan sesuai Figma (Approved/Hijau, Pending/Kuning, Rejected/Merah, Returned/Biru)
  - Tombol aksi kontekstual (Lihat Status, Kembalikan Sarana, Tandai Dibaca)
--}}
@extends('layouts.app')

@section('title', 'Pusat Notifikasi - SINFAS')

@section('navbar_title')
    <a href="{{ route('dashboard') }}" style="color: #4b5563; text-decoration: none; font-size: 0.88rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.3rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m15 18-6-6 6-6"/>
        </svg>
        Kembali ke Beranda
    </a>
@endsection

@section('content')
<div class="notifications-page-container">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="flash-msg flash-msg--success" id="flash-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
            {{ session('success') }}
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;margin-left:auto;font-size:1.1rem;">&times;</button>
        </div>
    @endif

    {{-- Page Header & Actions --}}
    <div class="notif-header-row">
        <div>
            <h2 class="notif-page-title">Pusat Notifikasi</h2>
            <p class="notif-page-subtitle">Informasi status permohonan sarana, peringatan batas waktu, dan konfirmasi pengembalian.</p>
        </div>

        <div class="notif-header-actions">
            @if($unreadCount > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-mark-all-read">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Tandai Semua Dibaca ({{ $unreadCount }})
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="notif-filter-tabs">
        <a href="{{ route('notifications') }}" class="notif-tab-item {{ empty($filter) ? 'notif-tab-item--active' : '' }}">
            Semua
        </a>
        <a href="{{ route('notifications', ['filter' => 'unread']) }}" class="notif-tab-item {{ $filter === 'unread' ? 'notif-tab-item--active' : '' }}">
            Belum Dibaca
            @if($unreadCount > 0)
                <span class="notif-tab-badge">{{ $unreadCount }}</span>
            @endif
        </a>
        <a href="{{ route('notifications', ['filter' => 'read']) }}" class="notif-tab-item {{ $filter === 'read' ? 'notif-tab-item--active' : '' }}">
            Sudah Dibaca
        </a>
    </div>

    {{-- Notification Card List --}}
    <div class="notif-card-list">
        @forelse($notifikasi as $item)
            @php
                $isUnread = !$item->status_baca;
                $loan = $item->peminjaman;
                $barang = $loan ? $loan->barang : null;
                $hariBerlalu = $item->hari_berlalu;
                $warnaDurasi = $item->warna_durasi;
            @endphp

            <div class="notif-card {{ $isUnread ? 'notif-card--unread' : '' }}" id="notif-card-{{ $item->id_notifikasi }}">
                <div class="notif-card-body">
                    {{-- Item Thumbnail / Icon --}}
                    <div class="notif-card-thumb">
                        @if($barang && !empty($barang->foto) && file_exists(public_path($barang->foto)))
                            <img src="{{ asset($barang->foto) }}" alt="{{ $barang->nama_barang }}" class="notif-thumb-img">
                        @else
                            <div class="notif-thumb-placeholder notif-thumb-placeholder--{{ $item->tipe }}">
                                @if($item->tipe === 'pengajuan_disetujui')
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                @elseif($item->tipe === 'pengajuan_ditolak')
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                @elseif($item->tipe === 'batas_waktu')
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                @elseif($item->tipe === 'pengembalian_dikonfirmasi')
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                @else
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Item Description Content --}}
                    <div class="notif-card-content">
                        <div class="notif-title-row">
                            <h4 class="notif-title">{{ $item->judul }}</h4>
                            @if($isUnread)
                                <span class="notif-unread-dot" title="Belum dibaca"></span>
                            @endif
                        </div>

                        {{-- Meta: Timestamp, Kode Pinjam & Durasi Hari Berjalan --}}
                        <div class="notif-meta-row">
                            <span class="notif-time">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $item->created_at->diffForHumans() }} ({{ $item->created_at->format('d M Y, H:i') }})
                            </span>

                            @if($item->kode_pinjam)
                                <span class="notif-meta-divider">&bull;</span>
                                <span class="notif-kode-badge">Kode: {{ $item->kode_pinjam }}</span>
                            @endif

                            {{-- Indikator Durasi Hari Berjalan Otomatis (Abu-abu 1-9, Kuning 10-50, Merah >50) --}}
                            @if($hariBerlalu !== null && $hariBerlalu >= 1)
                                <span class="notif-meta-divider">&bull;</span>
                                <span class="badge-duration-counter badge-duration--{{ $warnaDurasi }}" title="Dihitung otomatis sejak tanggal peminjaman">
                                    Dipinjam {{ $hariBerlalu }} Hari Lalu
                                </span>
                            @endif
                        </div>

                        {{-- Message Text --}}
                        <p class="notif-message">{{ $item->pesan }}</p>

                        {{-- Alasan Penolakan Jika Ada --}}
                        @if(!empty($item->data_tambahan['alasan_penolakan']))
                            <div class="notif-rejection-box">
                                <span class="notif-rejection-label">Alasan Penolakan:</span>
                                <p class="notif-rejection-text">{{ $item->data_tambahan['alasan_penolakan'] }}</p>
                            </div>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="notif-actions-bottom">
                            @if($item->kode_pinjam)
                                <a href="{{ route('loan.status') }}" class="btn-notif-action btn-notif-action--outline">
                                    Lihat di Status Pengajuan &rarr;
                                </a>
                            @endif

                            @if($isUnread)
                                <form action="{{ route('notifications.read', $item->id_notifikasi) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn-notif-action btn-notif-action--text">
                                        Tandai sudah dibaca
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Status Pill Sesuai Mockup Figma (Vertikal Kanan) --}}
                <div class="notif-card-badge-wrap">
                    @if($item->tipe === 'pengajuan_disetujui')
                        <span class="figma-status-badge figma-badge--approved">Approved</span>
                    @elseif($item->tipe === 'pengajuan_ditolak')
                        <span class="figma-status-badge figma-badge--rejected">Rejected</span>
                    @elseif($item->tipe === 'batas_waktu')
                        <span class="figma-status-badge figma-badge--overdue-{{ $warnaDurasi }}">Overdue</span>
                    @elseif($item->tipe === 'pengembalian_dikonfirmasi')
                        <span class="figma-status-badge figma-badge--returned">Returned</span>
                    @elseif($item->tipe === 'pengajuan_baru')
                        <span class="figma-status-badge figma-badge--pending">Pending</span>
                    @else
                        <span class="figma-status-badge figma-badge--info">Info</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="notif-empty-state">
                <div class="notif-empty-icon">
                    <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </div>
                <h3 class="notif-empty-title">Tidak Ada Notifikasi</h3>
                <p class="notif-empty-desc">
                    @if($filter === 'unread')
                        Semua notifikasi sudah dibaca. Anda sudah mengetahui seluruh pembaruan terkini!
                    @else
                        Saat ini belum ada notifikasi baru untuk Anda.
                    @endif
                </p>
                <a href="{{ route('dashboard') }}" class="btn-notif-primary">Kembali ke Katalog Sarana</a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifikasi->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 2rem;">
            {{ $notifikasi->links('vendor.pagination.simple-default') }}
        </div>
    @endif
</div>
@endsection
