@extends('layouts.admin-sarana')

@section('title', 'Kelola Data Alat - Admin Sarana SINFAS')
@section('page_title', 'Kelola data alat')

@section('content')
<div class="sarana-items-container">
    <div class="system-section-header">
        <h2 class="system-section-heading">Kelola data alat</h2>
    </div>

    {{-- Filter & Add Item Bar --}}
    <div class="system-filter-bar">
        <div class="system-search-box">
            <svg class="system-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            <input
                type="text"
                class="system-search-input"
                id="search-items-input"
                placeholder="Search items..."
            >
        </div>
        <button type="button" class="btn-add-account" id="btn-add-item">
            + Add Item
        </button>
    </div>

    {{-- Items Table --}}
    <div class="system-table-card">
        <table class="system-table">
            <thead>
                <tr>
                    <th style="width: 28%;">Item Name</th>
                    <th style="width: 18%;">Category</th>
                    <th style="width: 14%;">Condition</th>
                    <th style="width: 10%;">Stock</th>
                    <th style="width: 14%;">Status</th>
                    <th style="width: 16%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- Row 1: Projector Epson X300 --}}
                <tr>
                    <td class="td-name">Projector Epson X300</td>
                    <td class="td-category">Electronics</td>
                    <td class="td-condition">Good</td>
                    <td class="td-stock">5</td>
                    <td><span class="sarana-status-badge sarana-status-badge--available">Available</span></td>
                    <td>
                        <div class="action-btn-group">
                            <button type="button" class="btn-item-action btn-item-edit">Edit</button>
                            <button type="button" class="btn-item-action btn-item-delete">Delete</button>
                        </div>
                    </td>
                </tr>

                {{-- Row 2: Portable Speaker JBL --}}
                <tr>
                    <td class="td-name">Portable Speaker JBL</td>
                    <td class="td-category">Electronics</td>
                    <td class="td-condition">Good</td>
                    <td class="td-stock">3</td>
                    <td><span class="sarana-status-badge sarana-status-badge--available">Available</span></td>
                    <td>
                        <div class="action-btn-group">
                            <button type="button" class="btn-item-action btn-item-edit">Edit</button>
                            <button type="button" class="btn-item-action btn-item-delete">Delete</button>
                        </div>
                    </td>
                </tr>

                {{-- Row 3: Folding Table 180cm --}}
                <tr>
                    <td class="td-name">Folding Table 180cm</td>
                    <td class="td-category">Furniture</td>
                    <td class="td-condition">Good</td>
                    <td class="td-stock">10</td>
                    <td><span class="sarana-status-badge sarana-status-badge--available">Available</span></td>
                    <td>
                        <div class="action-btn-group">
                            <button type="button" class="btn-item-action btn-item-edit">Edit</button>
                            <button type="button" class="btn-item-action btn-item-delete">Delete</button>
                        </div>
                    </td>
                </tr>

                {{-- Row 4: Whiteboard 120cm --}}
                <tr>
                    <td class="td-name">Whiteboard 120cm</td>
                    <td class="td-category">Equipment</td>
                    <td class="td-condition td-condition--damaged">Damaged</td>
                    <td class="td-stock">2</td>
                    <td><span class="sarana-status-badge sarana-status-badge--unavailable">Unavailable</span></td>
                    <td>
                        <div class="action-btn-group">
                            <button type="button" class="btn-item-action btn-item-edit">Edit</button>
                            <button type="button" class="btn-item-action btn-item-delete">Delete</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination Bar --}}
    <div class="system-pagination-bar">
        <button class="pagination-btn pagination-btn--disabled">Prev</button>
        <button class="pagination-btn pagination-btn--active">1</button>
        <button class="pagination-btn">Next</button>
    </div>
</div>
@endsection
