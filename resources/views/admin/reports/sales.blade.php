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
            <form method="GET" action="{{ route('admin.reports.sales') }}" class="row g-3 align-items-end mb-3">
                <div class="col-md-3">
                    <label for="from" class="form-label">From</label>
                    <input type="date" class="form-control" id="from" name="from" value="{{ $from }}">
                </div>
                <div class="col-md-3">
                    <label for="to" class="form-label">To</label>
                    <input type="date" class="form-control" id="to" name="to" value="{{ $to }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary mt-1">
                        <i class="ri-search-line me-1"></i> Apply
                    </button>
                </div>
            </form>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Total Sales</p>
                            <h4 class="fw-bold text-dark mb-0">{{ $formatRupees($totalSales) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Total Bookings</p>
                            <h4 class="fw-bold text-dark mb-0">{{ number_format($totalBookings) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-muted mb-1">Average Ticket Size</p>
                            @php
                                $avg = $totalBookings > 0 ? $totalSales / $totalBookings : 0;
                            @endphp
                            <h4 class="fw-bold text-dark mb-0">{{ $formatRupees($avg) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col" class="text-end">Sales</th>
                            <th scope="col" class="text-end">Bookings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dailySales as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->sale_date)->format('d M Y') }}</td>
                                <td class="text-end">{{ $formatRupees($row->total_amount) }}</td>
                                <td class="text-end">{{ number_format($row->booking_count) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No sales found for the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
