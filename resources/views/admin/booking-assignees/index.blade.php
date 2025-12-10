@extends('admin.layouts.vertical', ['title' => 'Booking Assignees', 'subTitle' => 'Property'])

@section('css')
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DateRangePicker CSS (from CDN for proper jQuery plugin integration) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Booking Assignees</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Booking Assignees</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :fallback="route('admin.bookings.index')" :classes="['btn', 'btn-soft-secondary']"
                        :merge="false" icon="ri-arrow-go-back-line" />
                </div>
            </div>

            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">
                            <i class="ri-book-line me-2"></i>Bookings Assign
                        </h4>
                        <p class="text-muted mb-0">Assigne Booking to Photographer</p>
                    </div>
                    <div class="panel-actions d-flex gap-2">
                        <button type="button" class="btn btn-light border" data-panel-action="refresh" title="Refresh">
                            <i class="ri-refresh-line"></i>
                        </button>
                        <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                            <i class="ri-arrow-up-s-line"></i>
                        </button>
                        <button type="button" class="btn btn-light border" data-panel-action="fullscreen"
                            title="Fullscreen">
                            <i class="ri-fullscreen-line"></i>
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
                                    <option value="{{ $city->id }}" data-state="{{ $city->state_id }}">{{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterStatus" class="form-label">Booking Status</label>
                            <select id="filterStatus" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="Schedul_accepted">Schedul Accepted</option>
                                <option value="Reschedul_accepted">Reschedul Accepted</option>
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
                        <table id="bookingAssigneesTable" class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Property</th>
                                    <th>Location</th>
                                    <th>Booking Date</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th class="text-center">Assign</th>
                                    <th class="text-center">View</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Assignment Modal -->
    <div class="modal fade" id="assignBookingModal" tabindex="-1" aria-labelledby="assignBookingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignBookingModalLabel">Assign Booking to Photographer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignBookingForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- Booking Details Section -->
                        <div class="alert alert-info mb-3">
                            <h6 class="mb-2">Booking Details</h6>
                            <div class="row g-2 small">
                                <div class="col-md-6">
                                    <strong>Customer Name:</strong>
                                    <p id="modalCustomer" class="mb-1">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Pin Code:</strong>
                                    <p id="modalPincode" class="mb-1">-</p>
                                </div>
                                <div class="col-md-12">
                                    <strong>Address:</strong>
                                    <p id="modalAddress" class="mb-1">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>City:</strong>
                                    <p id="modalCity" class="mb-0">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>State:</strong>
                                    <p id="modalState" class="mb-0">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Details Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="modalDate" class="form-label">Booking Date</label>
                                <input type="date" id="modalDate" class="form-control" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="assignTime" class="form-label">Assign Time <span
                                        class="text-danger">*</span></label>
                                <input type="time" id="assignTime" name="time" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label for="assignPhotographer" class="form-label">Select Photographer <span
                                    class="text-danger">*</span></label>
                            <select id="assignPhotographer" name="user_id" class="form-select" required>
                                <option value="">-- Select Photographer --</option>
                                @foreach ($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- jQuery (must be loaded before DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Moment.js (required by DateRangePicker) -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js"></script>
    <!-- DateRangePicker (must be after jQuery and moment) -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Wait for all external libraries to load, then initialize DataTable
        document.addEventListener('DOMContentLoaded', function () {
            // Ensure jQuery and DataTables are available
            if (typeof jQuery !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
                console.log('jQuery and DataTables are loaded, initializing...');
            } else {
                console.warn('jQuery or DataTables not fully loaded yet');
            }
        });
    </script>

    @vite(['resources/js/pages/booking-assignees-index.js'])
@endsection