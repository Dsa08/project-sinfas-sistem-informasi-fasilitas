{{-- 
  HALAMAN PUSAT NOTIFIKASI ADMIN SARANA — SINFAS
  File: resources/views/admin/notifications.blade.php
  Fitur:
  - Notifikasi terpisah khusus Admin Sarana (tidak bercampur dengan akun siswa).
  - Menerima permohonan peminjaman baru dari siswa secara real-time.
  - Menerima pengajuan konfirmasi fisik barang kembali.
  - Tombol aksi cepat langsung menuju tab verifikasi terkait (Verifikasi Pinjam / Konfirmasi Kembali).
  - Filter: Semua, Belum Dibaca, Sudah Dibaca.
  - Tandai semua notifikasi sudah dibaca.
--}}
@extends('layouts.admin-sarana')

@section('title', 'Notifikasi Admin - SINFAS')
@section('page_title', 'Pusat Notifikasi Admin')

@section('content')
<div class="admin-notifications-container" style="max-width: 1060px; margin: 0 auto; padding-bottom: 3rem;">
    {{-- Header & Aksi Cepat --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem 0;">Pemberitahuan Sistem</h2>
            <p style="font-size: 0.88rem; color: #64748b; margin: 0;">Pantau antrean pengajuan peminjaman dan pengembalian sarana dari siswa.</p>
        </div>

        @if($unreadCount > 0)
            <form action="{{ route('notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" class="btn-mark-all-read" style="background: #ffffff; border: 1px solid #cbd5e1; padding: 0.55rem 1.15rem; border-radius: 8px; font-size: 0.84rem; font-weight: 600; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.15s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Tandai Semua Dibaca ({{ $unreadCount }})
                </button>
            </form>
        @endif
    </div>

    {{-- Filter Tabs --}}
    <div class="notif-filter-tabs" style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
        <a href="{{ route('admin.notifications') }}" class="notif-tab-item {{ empty($filter) ? 'notif-tab-item--active' : '' }}">
            Semua
        </a>
        <a href="{{ route('admin.notifications', ['filter' => 'unread']) }}" class="notif-tab-item {{ $filter === 'unread' ? 'notif-tab-item--active' : '' }}">
            Belum Dibaca
            @if($unreadCount > 0)
                <span class="notif-tab-badge">{{ $unreadCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.notifications', ['filter' => 'read']) }}" class="notif-tab-item {{ $filter === 'read' ? 'notif-tab-item--active' : '' }}">
            Sudah Dibaca
        </a>
    </div>

    {{-- Daftar Kartu Notifikasi Admin --}}
    <div class="notif-card-list">
        @forelse($notifikasi as $item)
            @php
                $isUnread = !$item->status_baca;
                $loan = $item->peminjaman;
                $barang = $loan ? $loan->barang : null;
                $siswa = $loan ? $loan->siswa : null;
            @endphp

            <div class="notif-card {{ $isUnread ? 'notif-card--unread' : '' }}" style="background: #ffffff; border: 1px solid {{ $isUnread ? '#bfdbfe' : '#e2e8f0' }}; border-radius: 14px; padding: 1.25rem 1.5rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: flex-start; gap: 1.25rem; transition: all 0.2s ease;">
                <div style="display: flex; gap: 1.15rem; flex: 1; min-width: 0;">
                    {{-- Thumbnail Icon --}}
                    <div style="width: 48px; height: 48px; border-radius: 10px; background: {{ $item->tipe === 'pengajuan_baru' ? '#eff6ff' : ($item->tipe === 'pengembalian_diajukan' ? '#f0fdf4' : '#f8fafc') }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        @if($item->tipe === 'pengajuan_baru')
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                                <path d="m9 14 2 2 4-4"/>
                            </svg>
                        @elseif($item->tipe === 'pengembalian_diajukan')
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 14 4 9 9 4"/>
                                <path d="M20 20v-7a4 4 0 0 0-4-4H4"/>
                            </svg>
                        @else
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                        @endif
                    </div>

                    {{-- Isi Notifikasi --}}
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.25rem;">
                            <h4 style="font-size: 1.02rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $item->judul }}</h4>
                            @if($isUnread)
                                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: #2563eb; display: inline-block;" title="Belum dibaca"></span>
                            @endif
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.65rem; font-size: 0.78rem; color: #64748b; margin-bottom: 0.55rem; flex-wrap: wrap;">
                            <span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $item->created_at->diffForHumans() }} ({{ $item->created_at->format('d M Y, H:i') }})
                            </span>
                            @if($item->kode_pinjam)
                                <span>&bull;</span>
                                <span style="font-weight: 600; color: #1e293b;">Kode: {{ $item->kode_pinjam }}</span>
                            @endif
                            @if($siswa)
                                <span>&bull;</span>
                                <span style="color: #475569;">Peminjam: <strong>{{ $siswa->nama }}</strong> ({{ $siswa->nis }})</span>
                            @endif
                        </div>

                        <p style="font-size: 0.88rem; color: #334155; line-height: 1.5; margin: 0 0 0.85rem 0;">{{ $item->pesan }}</p>

                        {{-- Tombol Aksi Langsung ke Tab Verifikasi --}}
                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            @if($item->tipe === 'pengajuan_baru')
                                <a href="{{ route('admin.verifications') }}" class="btn-notif-action-admin" style="background-color: #1D67F2; color: #ffffff; padding: 0.45rem 0.95rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    Buka Antrean Verifikasi &rarr;
                                </a>
                            @elseif($item->tipe === 'pengembalian_diajukan')
                                <a href="{{ route('admin.verifications', ['tab' => 'returns']) }}" class="btn-notif-action-admin" style="background-color: #059669; color: #ffffff; padding: 0.45rem 0.95rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    Konfirmasi Pengembalian Fisik &rarr;
                                </a>
                            @endif

                            @if($isUnread)
                                <form action="{{ route('notifications.read', $item->id_notifikasi) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" style="background: none; border: none; font-size: 0.8rem; color: #64748b; font-weight: 500; cursor: pointer; text-decoration: underline; padding: 0;">
                                        Tandai sudah dibaca
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Status Tag --}}
                <div>
                    @if($item->tipe === 'pengajuan_baru')
                        <span style="background: #fef3c7; color: #b45309; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">Permohonan Baru</span>
                    @elseif($item->tipe === 'pengembalian_diajukan')
                        <span style="background: #dcfce7; color: #15803d; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">Konfirmasi Pengembalian</span>
                    @else
                        <span style="background: #f1f5f9; color: #475569; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">{{ $item->tipe_label }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 4rem 1.5rem; text-align: center;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom: 0.75rem;">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <h3 style="font-size: 1.05rem; font-weight: 600; color: #334155; margin: 0 0 0.35rem 0;">Belum Ada Notifikasi</h3>
                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Seluruh permohonan pinjam atau pengembalian sarana dari siswa akan muncul di sini.</p>
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
