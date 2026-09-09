@extends('layouts.app')

@section('title', 'Pengajuan Pinjaman - ' . $item->nama_barang . ' - SINFAS')

@section('navbar_title')
    <a href="{{ route('dashboard') }}" style="color: #4b5563; text-decoration: none; font-size: 0.88rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.3rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m15 18-6-6 6-6"/>
        </svg>
        Kembali ke Beranda
    </a>
@endsection

@section('content')
<div class="loan-page-wrapper">
    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="flash-msg flash-msg--error" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <div class="loan-content-grid">
        {{-- Left Column: Item Details --}}
        <div class="loan-item-column">
            <div class="loan-image-card">
                @if(!empty($item->foto) && file_exists(public_path($item->foto)))
                    <img src="{{ asset($item->foto) }}" alt="{{ $item->nama_barang }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                @else
                    {{-- Placeholder abu-abu --}}
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; color: #9ca3af;">
                        <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span style="font-size: 0.78rem; color: #9ca3af; margin-top: 0.5rem; font-weight: 500;">Gambar tidak tersedia</span>
                    </div>
                @endif
            </div>

            <div class="loan-info-section">
                <h2 class="loan-product-name">{{ $item->nama_barang }}</h2>
                <p class="loan-meta-text">Kategori: <span class="loan-meta-val">{{ $item->kategori->nama_kategori ?? '-' }}</span></p>
                @if($item->merk_model)
                <p class="loan-meta-text">Merk/Model: <span class="loan-meta-val">{{ $item->merk_model }}</span></p>
                @endif
                <p class="loan-meta-text">Kondisi: <span class="loan-meta-val">{{ $item->jumlah_baik }} baik, {{ $item->jumlah_kurang_baik }} kurang baik, {{ $item->jumlah_rusak_berat }} rusak</span></p>
                <div class="loan-status-wrapper">
                    <span class="loan-status-pill {{ in_array($item->status, ['Available', 'Tersedia']) ? 'loan-status-pill--available' : 'loan-status-pill--unavailable' }}">
                        {{ in_array($item->status, ['Available', 'Tersedia']) ? 'Tersedia' : 'Tidak Tersedia' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Right Column: Loan Form --}}
        <div class="loan-form-column">
            <div class="loan-form-card">
                <h3 class="loan-form-title">Formulir Pengajuan Pinjaman</h3>

                @if($errors->any())
                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.65rem 0.85rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.82rem;">
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(isset($activeLoansCount) && $activeLoansCount >= 2)
                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.88rem; line-height: 1.4;">
                        <strong>Batas Kuota Tercapai:</strong> Anda saat ini memiliki {{ $activeLoansCount }} peminjaman aktif. Harap selesaikan peminjaman sebelumnya sebelum meminjam alat baru.
                    </div>
                @elseif(isset($alreadyPending) && $alreadyPending)
                    <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.88rem; line-height: 1.4;">
                        <strong>Permohonan Sedang Diproses:</strong> Anda sudah mengajukan alat ini dan statusnya masih menunggu verifikasi admin.
                    </div>
                @endif

                <form action="{{ route('loan.submit', $item->kode_barang) }}" method="POST" class="loan-request-form" id="loanRequestForm">
                    @csrf

                    {{-- Loan Date --}}
                    <div class="loan-field-group">
                        <label class="loan-field-label" for="tanggal_pinjam">Tanggal Pinjam</label>
                        <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" class="loan-field-input" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                    </div>

                    {{-- Location --}}
                    <div class="loan-field-group">
                        <label class="loan-field-label" for="lokasi_penggunaan">Lokasi Penggunaan</label>
                        <input type="text" name="lokasi_penggunaan" id="lokasi_penggunaan" class="loan-field-input" placeholder="Contoh: Ruang 31, Aula Utama" value="{{ old('lokasi_penggunaan') }}" required>
                    </div>

                    {{-- Purpose / Reason --}}
                    <div class="loan-field-group">
                        <label class="loan-field-label" for="keterangan_penggunaan">Keperluan / Alasan Peminjaman</label>
                        <textarea name="keterangan_penggunaan" id="keterangan_penggunaan" class="loan-field-textarea" placeholder="Jelaskan keperluan peminjaman..." rows="4" required>{{ old('keterangan_penggunaan') }}</textarea>
                    </div>

                    {{-- Submit with Anti-Spam Check --}}
                    @if(isset($activeLoansCount) && $activeLoansCount >= 2)
                        <button type="button" class="loan-btn-submit" disabled style="background-color: #9ca3af; cursor: not-allowed;">Batas Kuota Pinjaman Penuh (2/2)</button>
                    @elseif(isset($alreadyPending) && $alreadyPending)
                        <button type="button" class="loan-btn-submit" onclick="openWaitingApprovalModal()" style="background-color: #0284c7; cursor: pointer;">Pengajuan Sedang Menunggu (Lihat Info)</button>
                    @elseif(in_array($item->status, ['Available', 'Tersedia']))
                        <button type="submit" id="btnSubmitLoan" class="loan-btn-submit">Kirim Pengajuan Pinjaman</button>
                    @else
                        <button type="button" class="loan-btn-submit" disabled style="background-color: #9ca3af; cursor: not-allowed;">Barang Tidak Tersedia</button>
                    @endif

                    <p class="loan-form-note">Catatan: Kuota peminjaman maksimal 2 alat aktif per siswa. Pengajuan memerlukan persetujuan Admin Sarana.</p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('loanRequestForm')?.addEventListener('submit', function(e) {
        const confirmed = confirm('Apakah Anda yakin ingin mengajukan permohonan peminjaman untuk alat "{{ addslashes($item->nama_barang) }}"?');
        if (!confirmed) {
            e.preventDefault();
            return false;
        }

        const btn = document.getElementById('btnSubmitLoan');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Memproses Permintaan...';
            btn.style.opacity = '0.75';
            btn.style.cursor = 'not-allowed';
        }
    });
</script>
@endsection
