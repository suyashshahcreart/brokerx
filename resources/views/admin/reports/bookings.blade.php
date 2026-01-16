@extends('admin.layouts.vertical', ['title' => 'Bookings Report', 'subTitle' => 'Reports'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
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

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">Status Breakdown</h4>
                        <p class="text-muted mb-0">Bookings by status</p>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($statusBreakdown as $row)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-uppercase text-muted">{{ $row->status ?? 'unknown' }}</span>
                            <span class="fw-semibold text-dark">{{ number_format($row->total) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No booking data found.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">Recent Bookings</h4>
                        <p class="text-muted mb-0">Latest 20 bookings</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end">Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentBookings as $booking)
                                    <tr>
                                        <td>#{{ $booking->id }}</td>
                                        <td>{{ $booking->user->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-primary-subtle text-primary">{{ $booking->status ?? 'N/A' }}</span></td>
                                        <td class="text-end">{{ $booking->created_at?->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No bookings found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
