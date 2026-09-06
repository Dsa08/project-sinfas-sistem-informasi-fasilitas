@extends('layouts.admin-sarana')

@section('title', 'Verifikasi Peminjaman & Pengembalian - Admin Sarana SINFAS')
@section('page_title', 'Verifikasi Peminjaman & Pengembalian')

@section('content')
<div class="sarana-verifications-container">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.88rem;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert-error" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.88rem;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tab Navigation Bar --}}
    <div class="sarana-tab-bar" id="verification-tabs">
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'requests' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-requests" data-tab="requests">
            Pending Requests
        </button>
        <button type="button" class="sarana-tab-btn {{ $activeTab === 'returns' ? 'sarana-tab-btn--active' : '' }}" id="tab-btn-returns" data-tab="returns">
            Pending Returns
        </button>
    </div>

    {{-- Tab 1: Pending Requests Section --}}
    <div class="sarana-tab-content {{ $activeTab === 'requests' ? 'sarana-tab-content--active' : '' }}" id="tab-content-requests">
        <div class="system-section-header">
            <h2 class="sarana-section-heading">Pending Requests</h2>
        </div>

        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">Borrower</th>
                        <th style="width: 22%;">Item</th>
                        <th style="width: 15%;">Location</th>
                        <th style="width: 15%;">Reason</th>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 18%;">Actions</th>
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
                                <form action="{{ route('admin.verifications.approve', $req->kode_pinjam) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-approve" onclick="return confirm('Setujui peminjaman ini?')">Approve</button>
                                </form>
                                <form action="{{ route('admin.verifications.reject', $req->kode_pinjam) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-reject" onclick="return confirm('Tolak peminjaman ini?')">Reject</button>
                                </form>
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
    <div class="sarana-tab-content {{ $activeTab === 'returns' ? 'sarana-tab-content--active' : '' }}" id="tab-content-returns">
        <div class="system-section-header">
            <h2 class="sarana-section-heading">Pending Returns</h2>
        </div>

        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 22%;">Borrower</th>
                        <th style="width: 28%;">Item</th>
                        <th style="width: 15%;">Return Date</th>
                        <th style="width: 10%; text-align: center;">Evidence</th>
                        <th style="width: 13%;">Condition</th>
                        <th style="width: 12%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingReturns as $ret)
                    <tr>
                        <td class="td-name">{{ $ret->siswa->nama ?? '-' }}</td>
                        <td class="td-item">{{ $ret->barang->nama_barang ?? '-' }}</td>
                        <td class="td-date">{{ $ret->pengembalian->tanggal_kembali->format('Y-m-d') }}</td>
                        <td style="text-align: center;">
                            @if($ret->pengembalian->bukti_foto_video)
                            <button type="button" class="btn-evidence" title="Lihat Bukti Foto" onclick="window.open('{{ asset('storage/' . $ret->pengembalian->bukti_foto_video) }}', '_blank')">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75 3.54-1.96-2.36L6.5 17h11l-3.54-4.71z"/>
                                </svg>
                            </button>
                            @else
                            <span style="color: #9ca3af; font-size: 0.82rem;">-</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.verifications.confirm-return', $ret->kode_pinjam) }}" method="POST" class="confirm-return-form" style="display: flex; align-items: center; gap: 0.5rem;">
                                @csrf
                                <select name="kondisi_barang" class="sarana-select-condition" required>
                                    <option value="Baik">Baik</option>
                                    <option value="Kurang Baik">Kurang Baik</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                        </td>
                        <td>
                                <button type="submit" class="btn-confirm-return" onclick="return confirm('Konfirmasi pengembalian ini?')">Confirm</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #9ca3af; padding: 2rem;">Tidak ada pengembalian yang menunggu konfirmasi.</td>
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

{{-- Interactive Tab Switching Script --}}
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
</script>
@endsection
