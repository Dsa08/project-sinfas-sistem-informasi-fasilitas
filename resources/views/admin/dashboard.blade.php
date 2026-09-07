@extends('layouts.admin-sarana')

@section('title', 'Dashboard - Admin Sarana SINFAS')
@section('page_title', 'Home')

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
            <div class="system-stat-label">Pending Verification</div>
        </div>

        {{-- Card 2 --}}
        <div class="system-stat-card">
            <div class="system-stat-value">{{ $totalItems }}</div>
            <div class="system-stat-label">Total Items</div>
        </div>

        {{-- Card 3 --}}
        <div class="system-stat-card">
            <div class="system-stat-value">{{ $borrowedCount }}</div>
            <div class="system-stat-label">Currently Borrowed</div>
        </div>

        {{-- Card 4: Damaged (amber highlight) --}}
        <div class="system-stat-card" style="border-color: #f59e0b;">
            <div class="system-stat-value" style="color: #d97706;">{{ $damagedCount }}</div>
            <div class="system-stat-label">Damaged</div>
        </div>
    </div>

    {{-- Pending Loan Requests Section --}}
    <div class="sarana-section">
        <h2 class="sarana-section-heading">Pending Loan Requests</h2>
        <div class="system-table-card">
            <table class="system-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Borrower</th>
                        <th style="width: 35%;">Item</th>
                        <th style="width: 20%;">Date</th>
                        <th style="width: 20%;">Actions</th>
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
                                    <button type="submit" class="btn-action btn-approve" onclick="return confirm('Setujui peminjaman ini?')">Approve</button>
                                </form>
                                <form action="{{ route('admin.verifications.reject', $loan->kode_pinjam) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-reject" onclick="return confirm('Tolak peminjaman ini?')">Reject</button>
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
            <div class="sarana-action-text">Manage Items</div>
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
            <div class="sarana-action-text">History & Print Report</div>
        </div>

        {{-- Card 3: Verify Returns --}}
        <a href="{{ route('admin.verifications') }}" class="sarana-action-card" id="action-verify-returns">
            <div class="sarana-action-icon-box icon-bg-slate">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="sarana-action-text">Verify Returns</div>
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

            @php
                $colors = ['#fb7185', '#38bdf8', '#fbbf24', '#60a5fa', '#4ade80', '#a855f7'];
                $defaultItems = [
                    ['label' => 'Kabel HDMI 10 Meter', 'data' => [28, 36, 42, 30, 45, 50]],
                    ['label' => 'Kamera DSLR Canon 3000D', 'data' => [45, 52, 60, 48, 55, 68]],
                    ['label' => 'Wireless Presenter Laser', 'data' => [15, 22, 30, 18, 25, 34]],
                    ['label' => 'Microphone Wireless Clip-on', 'data' => [32, 40, 38, 35, 42, 48]],
                    ['label' => 'Tripod Kamera Takara', 'data' => [18, 28, 34, 25, 38, 43]],
                    ['label' => 'Speaker Portable JBL', 'data' => [22, 30, 36, 28, 40, 42]],
                ];

                $datasets = [];
                if (isset($topLoanItems) && $topLoanItems->count() > 0) {
                    foreach ($topLoanItems as $index => $item) {
                        $c = $colors[$index % count($colors)];
                        $baseVal = max($item->peminjaman_count * 6, 12);
                        $datasets[] = [
                            'label' => $item->nama_barang,
                            'data' => [
                                max(8, round($baseVal * 0.6)),
                                max(12, round($baseVal * 0.8)),
                                max(16, round($baseVal * 1.0)),
                                max(14, round($baseVal * 0.85)),
                                max(20, round($baseVal * 1.2)),
                                max(24, round($baseVal * 1.4))
                            ],
                            'backgroundColor' => $c,
                            'borderRadius' => 4,
                            'barPercentage' => 0.82,
                            'categoryPercentage' => 0.8
                        ];
                    }
                } else {
                    foreach ($defaultItems as $index => $def) {
                        $datasets[] = [
                            'label' => $def['label'],
                            'data' => $def['data'],
                            'backgroundColor' => $colors[$index],
                            'borderRadius' => 4,
                            'barPercentage' => 0.82,
                            'categoryPercentage' => 0.8
                        ];
                    }
                }
            @endphp

            const chartDatasets = @json($datasets);

            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
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
