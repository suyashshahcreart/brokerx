@extends('admin.layouts.vertical', ['title' => 'Bookings Report', 'subTitle' => 'Reports'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bookings</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Bookings Report</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-primary">
                        <i class="ri-line-chart-fill me-1"></i> Sales Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Breakdown Cards -->
    <div class="row g-3 mb-4">
        @forelse ($statusBreakdown as $status)
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 text-uppercase">{{ $status->status ?? 'Unknown' }}</p>
                                <h4 class="fw-bold text-dark mb-0">{{ number_format($status->total) }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <i class="ri-bookmark-line text-primary fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted mb-0">No booking data found.</p>
            </div>
        @endforelse
    </div>

    <!-- Filters & Datatable -->
    <div class="row">
        <div class="col-12">
            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Bookings List</h4>
                        <p class="text-muted mb-0">Manage all bookings</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-success btn-sm" id="exportBookings">
                            <i class="ri-file-excel-2-line me-1"></i> Export to Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters Section -->
                    <div class="row mb-4 g-3" id="filtersSection">
                        <div class="col-md-3">
                            <label for="filterState" class="form-label">State</label>
                            <select id="filterState" class="form-select form-select-sm">
                                <option value="">All States</option>
                                @foreach ($states ?? [] as $state)
                                    <option value="{{ $state->id }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterCity" class="form-label">City</label>
                            <select id="filterCity" class="form-select form-select-sm">
                                <option value="">All Cities</option>
                                @foreach ($cities ?? [] as $city)
                                    <option value="{{ $city->id }}" data-state="{{ $city->state_id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterStatus" class="form-label">Status</label>
                            <select id="filterStatus" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterDateRange" class="form-label">Date Range</label>
                            <input type="text" id="filterDateRange" class="form-control form-control-sm"
                                placeholder="Select date range" />
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-sm btn-primary" id="applyFilters">
                                <i class="ri-search-line me-2"></i>Apply Filters
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" id="clearFilters">
                                <i class="ri-close-line me-2"></i>Clear Filters
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="bookings-report-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Type / Subtype</th>
                                    <th>City</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        window.bookingReportUrl = '{{ route('admin.reports.bookings') }}';
        window.exportBookingsUrl = '{{ route('admin.reports.bookings.export') }}';
    </script>
    @vite(['resources/js/pages/bookings-report-index.js'])
@endsection
