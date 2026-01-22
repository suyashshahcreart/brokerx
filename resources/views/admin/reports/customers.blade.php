@extends('admin.layouts.vertical', ['title' => 'Customers Report', 'subTitle' => 'Reports'])

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
                            <li class="breadcrumb-item active" aria-current="page">Customers</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Customers Report</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-primary">
                        <i class="ri-line-chart-line me-1"></i> Sales Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Customers</p>
                    <h4 class="fw-bold text-dark mb-0">{{ number_format($totalCustomers) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card panel-card border-primary border-top" data-panel-card>
        <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h4 class="card-title mb-1">Top Customers</h4>
                <p class="text-muted mb-0">By bookings and revenue</p>
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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Customer</th>
                            <th scope="col" class="text-end">Bookings</th>
                            <th scope="col" class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topCustomers as $row)
                            <tr>
                                <td>{{ $row->user->name ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($row->bookings) }}</td>
                                <td class="text-end">{{ $formatRupees($row->revenue) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No customer data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
