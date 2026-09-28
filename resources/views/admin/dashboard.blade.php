{{-- 
  DASHBOARD ANALITIK ADMIN SARANA — SINFAS
  File: resources/views/admin/dashboard.blade.php
  Fitur:
  - 4 Kartu Metrik Utama: Pending Verification, Total Items (Barang), Currently Borrowed, dan Damaged Items (Rusak Berat).
  - Visualisasi Grafik Chart.js yang berubah sesuai jenis dan filter laporan.
  - Tabel peringkat 5 barang yang paling sering dipinjam pada periode dan kategori laporan.
--}}
@extends('layouts.admin-sarana')

@section('title', 'Dashboard - Admin Sarana SINFAS')
@section('page_title', 'Beranda')

@section('content')
<div class="sarana-dashboard-container">
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

    <div class="sarana-section">
        <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; margin-bottom: 0.75rem;">
            <div>
                <h2 class="sarana-section-heading" style="margin: 0 0 0.25rem;">Barang Paling Sering Dipinjam</h2>
                <p style="margin: 0; color: #64748b; font-size: 0.85rem;">Peringkat 5 barang berdasarkan periode {{ $startDate->locale('id')->translatedFormat('d F Y') }} sampai {{ $endDate->locale('id')->translatedFormat('d F Y') }} dan kategori {{ $categoryName }}.</p>
            </div>
            <a href="{{ route('admin.reports') }}" style="color: #1e40af; font-size: 0.85rem; font-weight: 600; text-decoration: none;">Atur laporan</a>
        </div>
        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th class="th-number">Peringkat</th>
                        <th>Kode barang</th>
                        <th>Nama barang</th>
                        <th>Kategori</th>
                        <th>Frekuensi dipinjam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topLoanItems as $item)
                        <tr>
                            <td class="td-number">{{ $loop->iteration }}</td>
                            <td>{{ $item->kode_barang }}</td>
                            <td class="td-name">{{ $item->nama_barang }}</td>
                            <td>{{ $item->nama_kategori ?? 'Tanpa kategori' }}</td>
                            <td>{{ number_format($item->total_peminjaman, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="system-table-empty-cell">Belum ada data peminjaman pada periode laporan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="sarana-section" style="margin-top: 2rem;">
        <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; margin-bottom: 0.75rem;">
            <div>
                <h2 class="sarana-section-heading" style="margin: 0 0 0.25rem;">{{ $chartTitle }}</h2>
                <p style="margin: 0; color: #64748b; font-size: 0.85rem;">
                    {{ $chartDescription }}
                    @if($reportType !== 'stock-summary')
                        ({{ $startDate->locale('id')->translatedFormat('d F Y') }} sampai {{ $endDate->locale('id')->translatedFormat('d F Y') }})
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.reports') }}" style="color: #1e40af; font-size: 0.85rem; font-weight: 600; text-decoration: none;">Ubah pengaturan grafik</a>
        </div>
        <div class="chart-container-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem 1.75rem; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);">
            <div style="position: relative; height: {{ $chartIndexAxis === 'y' ? '420px' : '380px' }}; width: 100%;">
                <canvas id="adminLoanChart" aria-label="{{ $chartTitle }}"></canvas>
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
        <a href="{{ route('admin.reports') }}" class="sarana-action-card" id="action-print-report">
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
        </a>

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

            const chartLabels = @json($chartLabels);
            const chartDatasets = @json($chartDatasets);
            const chartType = @json($chartType);
            const chartIndexAxis = @json($chartIndexAxis);
            const chartStacked = @json($reportType === 'damage-history');

            new Chart(ctx.getContext('2d'), {
                type: chartType,
                indexAxis: chartIndexAxis,
                data: {
                    labels: chartLabels,
                    datasets: chartDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: chartType === 'doughnut' || chartDatasets.length > 1,
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 16,
                                font: { family: 'Inter, system-ui, sans-serif', size: 11 },
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
                    scales: chartType === 'doughnut' ? {} : {
                        x: {
                            beginAtZero: true,
                            stacked: chartStacked,
                            ticks: { precision: chartIndexAxis === 'y' ? 0 : undefined, color: '#6b7280', font: { family: 'Inter, system-ui, sans-serif', size: 12 } },
                            grid: { display: chartIndexAxis === 'y', color: '#f1f5f9', borderDash: [4, 4] }
                        },
                        y: {
                            beginAtZero: true,
                            stacked: chartStacked,
                            ticks: { precision: chartIndexAxis === 'x' ? 0 : undefined, color: '#6b7280', font: { family: 'Inter, system-ui, sans-serif', size: 11 } },
                            grid: { display: chartIndexAxis === 'x', color: '#f1f5f9', borderDash: [4, 4] }
                        }
                    }
                }
            });
        }

        renderChart();
    });
</script>
@endsection
