@extends('layouts.app')

@section('title', 'Peminjaman Alat - ' . ($item['name'] ?? 'SINFAS'))

@section('content')
<div class="loan-page-wrapper">
    {{-- Back to items link --}}
    <div class="loan-nav-bar">
        <a href="{{ route('dashboard') }}" class="loan-back-link" id="back-to-items-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Back to items</span>
        </a>
    </div>

    {{-- Main 2-Column Content --}}
    <div class="loan-content-grid">
        {{-- Left Column: Item Details --}}
        <div class="loan-item-column">
            <div class="loan-image-card">
                <img 
                    src="{{ asset($item['image'] ?? 'assets/pictures/projector_sample.jpg') }}" 
                    alt="{{ $item['name'] ?? 'Projector Epson X300' }}" 
                    class="loan-product-image"
                >
            </div>

            <div class="loan-info-section">
                <h1 class="loan-product-name">{{ $item['name'] ?? 'Projector Epson X300' }}</h1>
                <p class="loan-meta-text">Category: <span class="loan-meta-val">{{ $item['category'] ?? 'Electronics / Projector' }}</span></p>
                <p class="loan-meta-text">Condition: <span class="loan-meta-val">{{ $item['condition'] ?? 'Good' }}</span></p>
                
                <div class="loan-status-wrapper">
                    <span class="loan-status-pill loan-status-pill--available">
                        {{ $item['status'] ?? 'Available' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Right Column: Loan Request Form --}}
        <div class="loan-form-column">
            <div class="loan-form-card">
                <h2 class="loan-form-title">Loan Request Form</h2>

                <form id="loanRequestForm" class="loan-request-form" onsubmit="handleLoanSubmit(event)">
                    @csrf
                    {{-- Hidden Item ID --}}
                    <input type="hidden" name="item_id" value="{{ $item['id'] ?? 1 }}">

                    {{-- Loan Date --}}
                    <div class="loan-field-group">
                        <label for="loan_date" class="loan-field-label">Loan Date</label>
                        <input 
                            type="date" 
                            id="loan_date" 
                            name="loan_date" 
                            class="loan-field-input" 
                            required
                        >
                    </div>

                    {{-- Planned Return Date --}}
                    <div class="loan-field-group">
                        <label for="planned_return_date" class="loan-field-label">Planned Return Date</label>
                        <input 
                            type="date" 
                            id="planned_return_date" 
                            name="planned_return_date" 
                            class="loan-field-input" 
                            required
                        >
                    </div>

                    {{-- Location --}}
                    <div class="loan-field-group">
                        <label for="location" class="loan-field-label">Location</label>
                        <input 
                            type="text" 
                            id="location" 
                            name="location" 
                            class="loan-field-input" 
                            placeholder="e.g. Ruang Rapat Lt. 2 / Lab Multimedia" 
                            required
                        >
                    </div>

                    {{-- Purpose / Reason --}}
                    <div class="loan-field-group">
                        <label for="purpose" class="loan-field-label">Purpose / Reason</label>
                        <textarea 
                            id="purpose" 
                            name="purpose" 
                            rows="4" 
                            class="loan-field-textarea" 
                            placeholder="Tuliskan tujuan peminjaman barang..." 
                            required
                        ></textarea>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="loan-btn-submit" id="submit-loan-btn">
                        Submit Loan Request
                    </button>

                    {{-- Note text --}}
                    <p class="loan-form-note">Note: Loan requests require admin approval.</p>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Success Confirmation Modal --}}
<div class="modal-overlay" id="loanSuccessModal">
    <div class="modal-card">
        <div class="modal-icon" style="background: rgba(34, 197, 94, 0.1); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <h3 class="modal-title">Pengajuan Berhasil Dikirim!</h3>
        <p class="modal-message">Permohonan peminjaman <strong>{{ $item['name'] ?? 'alat' }}</strong> telah diajukan dan menunggu persetujuan Admin Sarana.</p>
        <div class="modal-actions" style="margin-top: 1.5rem;">
            <a href="{{ route('dashboard') }}" class="modal-btn modal-btn--confirm" style="background-color: #1D67F2; border-color: #1D67F2; text-decoration: none;">Kembali ke Daftar Alat</a>
            <button type="button" class="modal-btn modal-btn--cancel" onclick="closeSuccessModal()">Tutup</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Set default date to today & tomorrow
        const today = new Date().toISOString().split('T')[0];
        const loanDateInput = document.getElementById('loan_date');
        const returnDateInput = document.getElementById('planned_return_date');

        if (loanDateInput) {
            loanDateInput.min = today;
            loanDateInput.value = today;
        }

        if (returnDateInput) {
            returnDateInput.min = today;
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            returnDateInput.value = tomorrow.toISOString().split('T')[0];
        }
    });

    function handleLoanSubmit(e) {
        e.preventDefault();
        const modal = document.getElementById('loanSuccessModal');
        if (modal) {
            modal.classList.add('modal-overlay--active');
        }
    }

    function closeSuccessModal() {
        const modal = document.getElementById('loanSuccessModal');
        if (modal) {
            modal.classList.remove('modal-overlay--active');
        }
    }
</script>
@endsection
