@extends('admin.layouts.vertical', ['title' => 'Reports', 'subTitle' => 'Reports'])

@section('content')
    @php
        $formatRupees = fn($amount) => '₹' . number_format($amount , 2);
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Reports</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Reports</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-primary">
                        <i class="ri-bar-chart-2-line me-1"></i> Sales Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Revenue</p>
                    <h4 class="fw-bold text-dark mb-0">{{ $formatRupees($totalRevenue) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Bookings</p>
                    <h4 class="fw-bold text-dark mb-0">{{ number_format($totalBookings) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Customers</p>
                    <h4 class="fw-bold text-dark mb-0">{{ number_format($totalCustomers) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Tours</p>
                    <h4 class="fw-bold text-dark mb-0">{{ number_format($totalTours) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Report Library</h4>
                        <p class="text-muted mb-0">Jump to detailed reports</p>
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
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.reports.sales') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Sales Report</h5>
                                <small class="text-muted">Revenue by day with totals</small>
                            </div>
                            <i class="ri-arrow-right-line"></i>
                        </a>
                        <a href="{{ route('admin.reports.bookings') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Bookings Report</h5>
                                <small class="text-muted">Status breakdown and recent bookings</small>
                            </div>
                            <i class="ri-arrow-right-line"></i>
                        </a>
                        <a href="{{ route('admin.reports.customers') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Customers Report</h5>
                                <small class="text-muted">Top customers by bookings and revenue</small>
                            </div>
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">Recent Sales</h4>
                        <p class="text-muted mb-0">Latest orders</p>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($recentSales as $sale)
                        @php
                            $amount = $sale->cashfree_payment_amount ?? $sale->price ?? 0;
                        @endphp
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <p class="mb-0 fw-semibold">Booking #{{ $sale->id }}</p>
                                <small class="text-muted">{{ $sale->created_at?->format('d M Y') }}</small>
                            </div>
                            <div class="text-end">
                                <p class="mb-0 text-dark fw-semibold">{{ $formatRupees($amount) }}</p>
                                <small class="text-muted text-uppercase">{{ $sale->status ?? 'NA' }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No recent sales found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
