@extends('layouts.admin-sarana')

@section('title', 'Laporan Sarana - Admin SINFAS')
@section('page_title', 'Laporan Sarana')

@section('content')
<div class="sarana-report-container">
    <div class="report-screen-heading">
        <div>
            <p class="report-eyebrow">ADMIN SARANA</p>
            <h2>Modul Laporan</h2>
            <p class="report-subtitle">Pantau peminjaman, kondisi, keterlambatan, dan stok inventaris.</p>
        </div>
        <button class="report-button report-button--pdf" type="button" id="report-print-button">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>
            </svg>
            Export PDF
        </button>
    </div>

    <form class="report-filter-card" method="GET" action="{{ route('admin.reports') }}" id="report-filter-form">
        <div class="report-filter-field report-filter-field--type">
            <label for="report-type">Tipe laporan</label>
            <select name="type" id="report-type">
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" @selected($type === $key)>{{ $loop->iteration }}. {{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="report-filter-field report-filter-field--date" data-report-filter="dates">
            <label for="start-date">Rentang waktu</label>
            <div class="report-date-inputs">
                <input id="start-date" type="date" name="start_date" value="{{ $startDate->toDateString() }}" aria-label="Tanggal mulai">
                <span>sampai</span>
                <input id="end-date" type="date" name="end_date" value="{{ $endDate->toDateString() }}" aria-label="Tanggal akhir">
            </div>
        </div>

        <div class="report-filter-field" data-report-filter="category">
            <label for="report-category">Kategori barang</label>
            <select name="category_id" id="report-category">
                <option value="">Semua kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id_kategori }}" @selected((string) $categoryId === (string) $category->id_kategori)>{{ $category->nama_kategori }}</option>
                @endforeach
            </select>
        </div>

        <div class="report-filter-field" data-report-filter="condition" hidden>
            <label for="report-condition">Kondisi barang</label>
            <select name="condition" id="report-condition">
                <option value="">Semua kondisi bermasalah</option>
                <option value="Kurang Baik" @selected($condition === 'Kurang Baik')>Kurang Baik</option>
                <option value="Rusak Berat" @selected($condition === 'Rusak Berat')>Rusak Berat</option>
            </select>
        </div>

        <div class="report-filter-field" data-report-filter="return-status" hidden>
            <label for="report-return-status">Status pengembalian</label>
            <select name="return_status" id="report-return-status">
                <option value="">Semua status</option>
                <option value="returned" @selected($returnStatus === 'returned')>Sudah kembali</option>
                <option value="unreturned" @selected($returnStatus === 'unreturned')>Belum kembali</option>
            </select>
        </div>

        <button class="report-button report-button--show" type="submit">Tampilkan</button>
    </form>

    <section class="report-print-area" aria-labelledby="report-title">
        <header class="report-print-header">
            <div class="report-lettermark" aria-hidden="true">S</div>
            <div class="report-letterhead-copy">
                <h1>SISTEM INFORMASI FASILITAS</h1>
                <p>SINFAS · Laporan Administrasi Sarana dan Prasarana</p>
            </div>
            <div class="report-letterhead-rule"></div>
        </header>

        <div class="report-document-heading">
            <p class="report-eyebrow">LAPORAN ADMINISTRASI</p>
            <h2 id="report-title">{{ mb_strtoupper($title) }}</h2>
            <p class="report-period">
                @if($type === 'stock-summary')
                    Posisi inventaris per {{ now()->locale('id')->translatedFormat('d F Y') }}
                @else
                    Periode {{ $startDate->copy()->locale('id')->translatedFormat('d F Y') }} – {{ $endDate->copy()->locale('id')->translatedFormat('d F Y') }}
                @endif
                <span>· {{ $categoryName }}</span>
            </p>
            <div class="report-print-meta">
                <span>Tanggal cetak: {{ now()->locale('id')->translatedFormat('d F Y') }}</span>
                <span>Dicetak oleh: {{ Auth::user()->nama ?? 'Admin Sarana' }}</span>
            </div>
        </div>

        <section class="report-chart-card" aria-label="Grafik {{ $chartTitle }}">
            <div class="report-section-heading">
                <div>
                    <h3>{{ $chartTitle }}</h3>
                    <p>{{ $chartDescription }}</p>
                </div>
            </div>
            <div class="report-chart-wrap" style="height: {{ $chartIndexAxis === 'y' ? max(300, min(560, count($chartLabels) * 48)) : 340 }}px">
                <canvas id="report-chart" aria-label="{{ $chartTitle }}"></canvas>
            </div>
        </section>

        <section class="report-table-card">
            <div class="report-section-heading report-table-heading">
                <div>
                    <h3>Rincian {{ $title }}</h3>
                    <p>{{ number_format($rows->count(), 0, ',', '.') }} baris data</p>
                </div>
            </div>
            <div class="report-table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th class="report-number-cell">No</th>
                            @foreach($columns as $column)
                                <th>
                                    <button class="report-sort-button" type="button" data-sort-index="{{ $loop->index + 1 }}" data-sort-state="none" aria-label="Urutkan {{ $column }}" aria-sort="none">
                                        <span>{{ $column }}</span>
                                        <span class="report-sort-indicator" aria-hidden="true"></span>
                                    </button>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="report-number-cell">{{ $loop->iteration }}</td>
                                @foreach($row as $cell)
                                    <td>{{ $cell === '' || $cell === null ? '—' : $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td class="report-empty-cell" colspan="{{ count($columns) + 1 }}">
                                    Tidak ada data yang cocok dengan filter laporan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($type === 'stock-summary')
                        <tfoot>
                            <tr>
                                <th colspan="4">Total</th>
                                <th>{{ number_format($summary['Baik / tersedia'], 0, ',', '.') }}</th>
                                <th>{{ number_format($summary['Kurang baik'], 0, ',', '.') }}</th>
                                <th>{{ number_format($summary['Rusak berat'], 0, ',', '.') }}</th>
                                <th>{{ number_format($summary['Total aset'], 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>

        <footer class="report-signatures">
            <div class="report-signature-block">
                <p>Mengetahui,</p>
                <strong>Wakasek Bidang Sarpras /<br>Kepala Sarana</strong>
                <div class="report-signature-space"></div>
                <span>(........................................)</span>
                <small>NIP.</small>
            </div>
            <div class="report-signature-block report-signature-block--right">
                <p>Bandung, {{ now()->locale('id')->translatedFormat('d F Y') }}</p>
                <strong>Admin / Pengelola Data</strong>
                <div class="report-signature-space"></div>
                <span>(........................................)</span>
                <small>NIP.</small>
            </div>
        </footer>
        <p class="report-document-footer">Dokumen dicetak dari SINFAS · {{ now()->format('d-m-Y H:i') }}</p>
    </section>
</div>

<style>
    .sarana-report-container { max-width: 1500px; margin: 0 auto; padding: 1.5rem; color: #172033; }
    .report-screen-heading, .report-section-heading { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
    .report-screen-heading { margin-bottom: 1.25rem; }
    .report-screen-heading h2 { margin: 0; color: #111827; font-size: 1.5rem; }
    .report-eyebrow { margin: 0 0 .3rem; color: #64748b; font-size: .72rem; font-weight: 800; letter-spacing: .12em; }
    .report-subtitle, .report-section-heading p { margin: .35rem 0 0; color: #64748b; font-size: .88rem; }
    .report-button { display: inline-flex; align-items: center; justify-content: center; gap: .55rem; min-height: 42px; padding: .65rem 1rem; border: 0; border-radius: 8px; color: #fff; font: inherit; font-size: .9rem; font-weight: 700; cursor: pointer; white-space: nowrap; }
    .report-button--show { background: #1d4ed8; }
    .report-button--show:hover { background: #1e40af; }
    .report-button--pdf { background: #159447; }
    .report-button--pdf:hover { background: #11763a; }
    .report-filter-card, .report-chart-card, .report-table-card { border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15, 23, 42, .04); }
    .report-filter-card { display: flex; align-items: end; flex-wrap: wrap; gap: .9rem; padding: 1rem; margin-bottom: 1.25rem; }
    .report-filter-field { display: flex; flex: 1 1 160px; flex-direction: column; gap: .4rem; min-width: 150px; }
    .report-filter-field[hidden] { display: none; }
    .report-filter-field--type { flex: 1.5 1 250px; }
    .report-filter-field--date { flex: 1.5 1 330px; }
    .report-filter-field label { color: #334155; font-size: .8rem; font-weight: 700; }
    .report-filter-field select, .report-filter-field input { box-sizing: border-box; width: 100%; min-height: 42px; padding: .55rem .7rem; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; color: #1e293b; font: inherit; font-size: .88rem; }
    .report-date-inputs { display: flex; align-items: center; gap: .45rem; }
    .report-date-inputs span { color: #64748b; font-size: .8rem; }
    .report-chart-card, .report-table-card { margin-bottom: 1.1rem; overflow: hidden; }
    .report-section-heading { padding: 1rem 1.1rem; }
    .report-section-heading h3 { margin: 0; color: #1e293b; font-size: 1rem; }
    .report-chart-wrap { position: relative; padding: 0 1rem 1rem; }
    .report-table-wrap { width: 100%; overflow-x: auto; }
    .report-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
    .report-table th { padding: .75rem .8rem; border: 1px solid #d8dee8; background: #1e3a8a; color: #fff; font-weight: 700; text-align: left; white-space: nowrap; }
    .report-sort-button { display: inline-flex; align-items: center; justify-content: space-between; gap: .5rem; width: 100%; padding: 0; border: 0; background: transparent; color: inherit; font: inherit; font-weight: inherit; text-align: left; cursor: pointer; }
    .report-sort-button:hover { color: #dbeafe; }
    .report-sort-indicator { flex: 0 0 auto; opacity: .7; font-size: .9em; }
    .report-sort-button[data-sort-state="ascending"] .report-sort-indicator, .report-sort-button[data-sort-state="descending"] .report-sort-indicator { opacity: 1; }
    .report-sort-indicator::before { content: "\2195"; }
    .report-sort-button[data-sort-state="ascending"] .report-sort-indicator::before { content: "\2191"; }
    .report-sort-button[data-sort-state="descending"] .report-sort-indicator::before { content: "\2193"; }
    .report-table td { padding: .7rem .8rem; border: 1px solid #d8dee8; color: #263449; vertical-align: top; }
    .report-table tbody tr:nth-child(even) { background: #f8fafc; }
    .report-table tbody tr:hover { background: #eff6ff; }
    .report-number-cell { width: 1%; text-align: center !important; }
    .report-empty-cell { padding: 2rem !important; color: #64748b !important; text-align: center; }
    .report-print-header, .report-document-heading, .report-signatures, .report-document-footer { display: none; }
    @media (max-width: 900px) { .sarana-report-container { padding: 1rem; } .report-filter-card { align-items: stretch; } .report-button--show { flex: 1 1 100%; } .report-date-inputs input { min-width: 0; } }
    @media (max-width: 560px) { .report-screen-heading { align-items: flex-start; flex-direction: column; } .report-button--pdf { width: 100%; } .report-date-inputs { align-items: stretch; flex-direction: column; } .report-date-inputs span { display: none; } }
    @media print {
        @page { size: A4 landscape; margin: 0; }
        html, body.admin-system-body { width: auto !important; height: auto !important; overflow: visible !important; background: #fff !important; }
        .system-sidebar, .system-topbar, .report-screen-heading, .report-filter-card, .report-chart-card { display: none !important; }
        .admin-system-layout, .system-main-wrapper, .system-content { display: block !important; width: auto !important; height: auto !important; overflow: visible !important; background: #fff !important; }
        .sarana-report-container { max-width: none; padding: 12mm; color: #111827; }
        .report-print-header { display: flex; align-items: center; gap: 14px; padding-bottom: 10px; border-bottom: 3px double #1f2937; }
        .report-lettermark { display: grid; width: 48px; height: 48px; place-items: center; border: 2px solid #1f2937; border-radius: 50%; font-size: 25px; font-weight: 800; }
        .report-letterhead-copy h1 { margin: 0; font-size: 16px; letter-spacing: .06em; }
        .report-letterhead-copy p { margin: 3px 0 0; font-size: 10px; }
        .report-letterhead-rule { flex: 1; }
        .report-document-heading { display: block; padding: 14px 0 10px; text-align: center; }
        .report-document-heading .report-eyebrow { font-size: 9px; }
        .report-document-heading h2 { margin: 4px 0; font-size: 14px; }
        .report-period { margin: 3px 0 7px; font-size: 10px; }
        .report-print-meta { display: flex; justify-content: space-between; font-size: 9px; }
        .report-table-card { margin: 0; border: 0; border-radius: 0; box-shadow: none; overflow: visible; }
        .report-table-heading, .report-print-meta { display: none !important; }
        .report-sort-indicator { display: none !important; }
        .report-table-heading h3 { font-size: 10px; }
        .report-table-heading p { font-size: 8px; }
        .report-table-wrap { overflow: visible; }
        .report-table { font-size: 8px; }
        .report-table th { padding: 5px; border: 1px solid #64748b; background: #334155 !important; color: #fff !important; font-size: 8px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .report-table td { padding: 4px 5px; border: 1px solid #94a3b8; color: #111827; }
        .report-table tbody tr:nth-child(even) { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .report-table tr { break-inside: avoid; }
        .report-table tfoot th { padding: 6px 5px; border: 1px solid #64748b; background: #e2e8f0 !important; color: #111827 !important; font-weight: 800; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .report-signatures { display: flex; justify-content: space-between; gap: 30px; margin: 22px 15px 0; font-size: 9px; }
        .report-signature-block { width: 42%; text-align: center; }
        .report-signature-block p { margin: 0 0 3px; }
        .report-signature-block strong { display: block; min-height: 28px; }
        .report-signature-space { height: 42px; }
        .report-signature-block span, .report-signature-block small { display: block; }
        .report-document-footer { display: block; margin-top: 12px; color: #64748b; font-size: 8px; text-align: right; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('report-type');
        const filterGroups = document.querySelectorAll('[data-report-filter]');
        const visibility = {
            dates: ['loan-trends', 'damage-history', 'late-returns'],
            category: ['loan-trends', 'damage-history', 'late-returns', 'stock-summary'],
            condition: ['damage-history'],
            'return-status': ['late-returns']
        };

        function syncReportFilters() {
            filterGroups.forEach(function (group) {
                const allowedTypes = visibility[group.dataset.reportFilter] || [];
                group.hidden = !allowedTypes.includes(typeSelect.value);
            });
        }

        typeSelect.addEventListener('change', syncReportFilters);
        syncReportFilters();

        const reportTable = document.querySelector('.report-table');
        if (reportTable) {
            const tbody = reportTable.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
                return !row.querySelector('.report-empty-cell');
            });
            const originalOrder = new Map(rows.map(function (row, index) { return [row, index]; }));
            const sortButtons = reportTable.querySelectorAll('.report-sort-button');
            let activeButton = null;

            function sortValue(value) {
                const trimmed = value.trim();
                const dateMatch = trimmed.match(/^(\d{2})-(\d{2})-(\d{4})$/);
                if (dateMatch) return Date.UTC(Number(dateMatch[3]), Number(dateMatch[2]) - 1, Number(dateMatch[1]));
                const isoDateMatch = trimmed.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (isoDateMatch) return Date.UTC(Number(isoDateMatch[1]), Number(isoDateMatch[2]) - 1, Number(isoDateMatch[3]));
                if (trimmed !== '' && Number.isFinite(Number(trimmed))) return Number(trimmed);
                return trimmed;
            }

            sortButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const currentState = button.dataset.sortState;
                    const nextState = activeButton !== button || currentState === 'none'
                        ? 'ascending'
                        : currentState === 'ascending' ? 'descending' : 'none';
                    activeButton = nextState === 'none' ? null : button;

                    sortButtons.forEach(function (otherButton) {
                        const isActive = otherButton === activeButton;
                        otherButton.dataset.sortState = isActive ? nextState : 'none';
                        otherButton.setAttribute('aria-sort', isActive ? nextState : 'none');

                    });

                    const sortIndex = Number(button.dataset.sortIndex);
                    const sortedRows = rows.slice().sort(function (left, right) {
                        if (nextState === 'none') return originalOrder.get(left) - originalOrder.get(right);
                        const leftValue = sortValue(left.cells[sortIndex].textContent);
                        const rightValue = sortValue(right.cells[sortIndex].textContent);
                        const comparison = typeof leftValue === 'number' && typeof rightValue === 'number'
                            ? leftValue - rightValue
                            : String(leftValue).localeCompare(String(rightValue), 'id', { numeric: true, sensitivity: 'base' });
                        return nextState === 'ascending' ? comparison : -comparison;
                    });

                    sortedRows.forEach(function (row, index) {
                        tbody.appendChild(row);
                        row.cells[0].textContent = String(index + 1);
                    });
                });
            });
        }

        document.getElementById('report-print-button').addEventListener('click', function () {
            const originalTitle = document.title;
            const reportName = typeSelect.options[typeSelect.selectedIndex].textContent
                .replace(/^\s*\d+\.\s*/, '')
                .trim();

            document.title = 'Laporan Sarana - ' + reportName + ' - Admin SINFAS';
            window.addEventListener('afterprint', function restoreTitle() {
                document.title = originalTitle;
                window.removeEventListener('afterprint', restoreTitle);
            });
            window.print();
        });

        const chartCanvas = document.getElementById('report-chart');
        if (chartCanvas && window.Chart) {
            const chartType = @json($chartType);
            const chartIndexAxis = @json($chartIndexAxis);
            new window.Chart(chartCanvas, {
                type: chartType,
                data: {
                    labels: @json($chartLabels),
                    datasets: @json($chartDatasets)
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: chartIndexAxis,
                    plugins: {
                        legend: { display: chartType === 'doughnut', position: 'bottom' }
                    },
                    scales: chartType === 'doughnut' ? {} : {
                        x: {
                            beginAtZero: chartIndexAxis === 'y',
                            ticks: { precision: chartIndexAxis === 'y' ? 0 : undefined, color: '#64748b' },
                            grid: { display: chartIndexAxis === 'y', color: '#eaf0f8' }
                        },
                        y: {
                            beginAtZero: chartIndexAxis === 'x',
                            ticks: { precision: chartIndexAxis === 'x' ? 0 : undefined, color: '#64748b' },
                            grid: { display: chartIndexAxis === 'x', color: '#eaf0f8' }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
