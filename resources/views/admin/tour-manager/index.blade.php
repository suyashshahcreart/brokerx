@extends('admin.layouts.vertical', ['title' => 'Tour Manager'])

@section('content')
    <div class="">
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <div>
                        <nav aria-label="breadcrumb" class="mb-0">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Tour Manager</li>
                            </ol>
                        </nav>
                        <h3 class="mb-0">Tour Management</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Bookings List</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filter-status">
                                    <option value="">All Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Payment Status</label>
                                <select class="form-select" id="filter-payment-status">
                                    <option value="">All Payment Status</option>
                                    @foreach($paymentStatuses as $paymentStatus)
                                        <option value="{{ $paymentStatus }}">{{ ucfirst($paymentStatus) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date Range</label>
                                <input type="text" class="form-control" id="filter-date-range" placeholder="Select date range">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary" id="apply-filters">
                                    <i class="ri-filter-line me-1"></i>Apply Filters
                                </button>
                                <button type="button" class="btn btn-secondary" id="reset-filters">
                                    <i class="ri-refresh-line me-1"></i>Reset
                                </button>
                            </div>
                        </div>

                        <!-- DataTable -->
                        <div class="table-responsive">
                               <table id="bookings-table" class="table table-hover dt-responsive nowrap w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Booking</th>
                                        <th>Customer</th>
                                        <th>Location</th>
                                        <th>City / State</th>
                                        <th>QR Code</th>
                                        <th>Created</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Tour Modal -->
    <div class="modal fade" id="scheduleTourModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="schedule-tour-form">
                    @csrf
                    <input type="hidden" id="booking-id" name="booking_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Schedule Tour</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tour-date" class="form-label">Tour Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="tour-date" name="tour_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="tour-notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="tour-notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule Tour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @vite(['resources/js/pages/tour-manager.js'])
@endsection