@extends('layouts.admin-sarana')

@section('title', 'Dashboard - Admin Sarana SINFAS')
@section('page_title', 'Beranda')

@section('content')
<div class="sarana-dashboard-container">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.88rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- 4 Stat Cards --}}
    <div class="sarana-stats-grid">
        {{-- Card 1 --}}
        <div class="system-stat-card">
            <div class="system-stat-value">{{ $pendingCount }}</div>
            <div class="system-stat-label">Menunggu Verifikasi</div>
        </div>

        {{-- Card 2 --}}
        <div class="system-stat-card">
            <div class="system-stat-value">{{ $totalItems }}</div>
            <div class="system-stat-label">Total Barang</div>
        </div>

        {{-- Card 3 --}}
        <div class="system-stat-card">
            <div class="system-stat-value">{{ $borrowedCount }}</div>
            <div class="system-stat-label">Sedang Dipinjam</div>
        </div>

        {{-- Card 4: Damaged (amber highlight) --}}
        <div class="system-stat-card" style="border-color: #f59e0b;">
            <div class="system-stat-value" style="color: #d97706;">{{ $damagedCount }}</div>
            <div class="system-stat-label">Kondisi Rusak</div>
        </div>
    </div>

    {{-- Pending Loan Requests Section --}}
    <div class="sarana-section">
        <h2 class="sarana-section-heading">Permintaan Peminjaman Menunggu</h2>
        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Peminjam</th>
                        <th style="width: 35%;">Barang</th>
                        <th style="width: 20%;">Tanggal Pinjam</th>
                        <th style="width: 20%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingLoans as $loan)
                    <tr>
                        <td class="td-name">{{ $loan->siswa->nama ?? '-' }}</td>
                        <td class="td-item">{{ $loan->barang->nama_barang ?? '-' }}</td>
                        <td class="td-date">{{ $loan->tanggal_pinjam->format('Y-m-d') }}</td>
                        <td>
                            <div class="action-btn-group">
                                <form action="{{ route('admin.verifications.approve', $loan->kode_pinjam) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-approve" onclick="return confirm('Setujui peminjaman ini?')">Setujui</button>
                                </form>
                                <form action="{{ route('admin.verifications.reject', $loan->kode_pinjam) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-reject" onclick="return confirm('Tolak peminjaman ini?')">Tolak</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #9ca3af; padding: 2rem;">Tidak ada peminjaman yang menunggu verifikasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Diagram Peminjaman Barang Terbanyak --}}
    <div class="sarana-section" style="margin-top: 2rem;">
        <div style="margin-bottom: 0.75rem;">
            <h2 class="sarana-section-heading" style="font-size: 1.15rem; font-weight: 700; color: #111827; margin: 0 0 0.25rem;">Peminjaman Barang Terbanyak di Beberapa Waktu Terakhir</h2>
            <p style="font-size: 0.85rem; color: #6b7280; margin: 0;">Statistik frekuensi peminjaman alat dan fasilitas terpopuler</p>
        </div>
        <div class="chart-container-card" style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <div style="position: relative; height: 340px; width: 100%;">
                <canvas id="adminLoanChart"></canvas>
            </div>
        </div>
    </div>

    {{-- 3 Action Cards --}}
    <div class="sarana-actions-grid" style="margin-top: 2rem;">
        {{-- Card 1: Manage Items --}}
        <a href="{{ route('admin.items') }}" class="sarana-action-card" id="action-manage-items">
            <div class="sarana-action-icon-box icon-bg-red">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m7.5 4.27 9 5.15"/>
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                    <path d="m3.3 7 8.7 5 8.7-5"/>
                    <path d="M12 22V12"/>
                </svg>
            </div>
            <div class="sarana-action-text">Kelola Barang</div>
        </a>

        {{-- Card 2: History & Print Report --}}
        <div class="sarana-action-card" id="action-print-report">
            <div class="sarana-action-icon-box icon-bg-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <line x1="10" y1="9" x2="8" y2="9"/>
                </svg>
            </div>
            <div class="sarana-action-text">Riwayat & Cetak Laporan</div>
        </div>

        {{-- Card 3: Verify Returns --}}
        <a href="{{ route('admin.verifications') }}" class="sarana-action-card" id="action-verify-returns">
            <div class="sarana-action-icon-box icon-bg-slate">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="sarana-action-text">Verifikasi Pengembalian</div>
        </a>
    </div>
</div>

{{-- Load Chart.js CDN as guaranteed fallback in addition to Vite --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('adminLoanChart');
        if (!ctx) return;

        function renderChart() {
            if (typeof Chart === 'undefined') {
                setTimeout(renderChart, 100);
                return;
            }

            const chartLabels = {!! json_encode($chartLabels ?? []) !!};
            const chartDatasets = {!! json_encode($chartDatasets ?? []) !!};

            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: chartDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 16,
                                font: {
                                    family: 'Inter, system-ui, sans-serif',
                                    size: 11
                                },
                                color: '#4b5563'
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            titleFont: { family: 'Inter, system-ui, sans-serif', size: 12 },
                            bodyFont: { family: 'Inter, system-ui, sans-serif', size: 12 },
                            padding: 10,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#6b7280',
                                font: { family: 'Inter, system-ui, sans-serif', size: 11 }
                            },
                            grid: {
                                color: '#f1f5f9'
                            },
                            border: {
                                dash: [4, 4]
                            }
                        },
                        x: {
                            ticks: {
                                color: '#6b7280',
                                font: { family: 'Inter, system-ui, sans-serif', size: 12, weight: '500' }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        renderChart();
    });
</script>
@endsection
