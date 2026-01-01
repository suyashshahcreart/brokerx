@extends('admin.layouts.vertical', ['title' => 'Bookings', 'subTitle' => 'Property'])

@section('css')
<style>
    /* Schedule Modal Input Styling */
    #scheduleModal .input-group-text {
        border-radius: 0.375rem 0 0 0.375rem;
    }
    
    #scheduleModal .input-group .form-control {
        border-left: 0;
    }
    
    #scheduleModal .input-group .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    #scheduleModal .input-group-text:hover {
        background-color: #0b5ed7 !important;
    }
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Property</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bookings</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Bookings</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    @can('booking_create')
                    <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> New Booking
                    </a>
                    @endcan
                </div>
            </div>

            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Bookings List</h4>
                        <p class="text-muted mb-0">Manage customer property bookings</p>
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
                        <!-- <button type="button" class="btn btn-light border" data-panel-action="close" title="Close">
                            <i class="ri-close-line"></i>
                        </button> -->
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
                        <div class="d-none col-md-3">
                            <label for="filterStatus" class="form-label">Booking Status</label>
                            <select id="filterStatus" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="cancelled">Cancelled</option>
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
                        <table class="table table-hover align-middle mb-0" id="bookings-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Type / Subtype</th>
                                    <th>BHK</th>
                                    <th>City / State</th>
                                    <th>Area</th>
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

                    <!-- Schedule Modal -->
                    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="scheduleModalLabel">Schedule Booking</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="scheduleForm">
                                        <div class="mb-3">
                                            <label class="form-label">Current Booking Date</label>
                                            <div id="current-booking-date" class="form-control-plaintext text-primary mb-2"></div>
                                            <div class="mb-2">
                                                <label class="form-label">Schedule Mode</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="schedule_mode" id="schedule-mode-default" value="default" checked>
                                                        <label class="form-check-label" for="schedule-mode-default">Default</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="schedule_mode" id="schedule-mode-any" value="any">
                                                        <label class="form-check-label" for="schedule-mode-any">Pick Any Day</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <label for="schedule-date" class="form-label">Select Date</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white" id="calendar-icon-trigger" style="cursor: pointer;" title="Open calendar">
                                                    <i class="ri-calendar-line"></i>
                                                </span>
                                                <input type="text" class="form-control" id="schedule-date" name="schedule_date" placeholder="Click to select date" required autocomplete="off">
                                            </div>
                                        </div>
                                        <input type="hidden" id="schedule-booking-id" name="booking_id">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" id="scheduleSubmitBtn">Schedule</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    // Set base URL and API routes for JavaScript
    window.appBaseUrl = '{{ url("/") }}';
    window.apiBaseUrl = '{{ url("/api") }}';
    window.bookingIndexUrl = '{{ route("admin.bookings.index") }}';
    window.bookingCsrfToken = '{{ csrf_token() }}';
</script>
@vite(['resources/js/pages/booking-index.js'])
@endsection
