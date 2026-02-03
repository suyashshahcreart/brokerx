@extends('admin.layouts.vertical', ['title' => 'Sales Report', 'subTitle' => 'Reports'])

@section('content')
    @php
        $formatRupees = fn($amount) => '₹' . number_format(($amount ?? 0) / 100, 2);
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Sales</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Sales Report</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                    <a href="{{ route('admin.reports.bookings') }}" class="btn btn-primary">
                        <i class="ri-booklet-line me-1"></i> Booking Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card panel-card border-primary border-top" data-panel-card>
        <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h4 class="card-title mb-1">Sales by Date</h4>
                <p class="text-muted mb-0">Filter by date range to see totals</p>
            </div>
            <div class="panel-actions d-flex gap-2">
                <button type="button" class="btn btn-light border" data-panel-action="refresh" title="Refresh">
                    <i class="ri-refresh-line"></i>
                </button>
                <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                    <i class="ri-arrow-up-s-line"></i>
                </button>
                <button type="button" class="btn btn-light border" data-panel-action="fullscreen" title="Fullscreen">
                    <i class="ri-fullscreen-line"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-4">
                    <label for="dateRange" class="form-label">Date Range</label>
                    <input type="text" class="form-control" id="dateRange" name="dateRange" placeholder="Select date range">
                    <input type="hidden" id="from" name="from" value="{{ $from }}">
                    <input type="hidden" id="to" name="to" value="{{ $to }}">
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-light border mt-1" id="resetFilters">
                        <i class="ri-refresh-line me-1"></i> Reset
                    </button>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Total Sales</p>
                            <h4 class="fw-bold text-dark mb-0" id="totalSalesDisplay">{{ $formatRupees($totalSales) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Total Bookings</p>
                            <h4 class="fw-bold text-dark mb-0" id="totalBookingsDisplay">{{ number_format($totalBookings) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Average Booking Price</p>
                            @php
                                $avg = $totalBookings > 0 ? $totalSales / $totalBookings : 0;
                            @endphp
                            <h4 class="fw-bold text-dark mb-0" id="avgTicketDisplay">{{ $formatRupees($avg) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="salesDataTable" data-url-featch="{{ route('admin.reports.sales') }}">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Customer</th>
                            <th scope="col">Booking ID</th>
                            <th scope="col" class="text-end">Payment Amount</th>
                            <th scope="col" class="text-end">Booking Price</th>
                            <th scope="col">Booking Date</th>
                            <th scope="col">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
@vite(['resources/js/pages/report-sales-index.js'])
@endsection
