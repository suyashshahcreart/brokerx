@extends('admin.layouts.vertical', ['title' => 'Booking Assignees', 'subTitle' => 'Property'])

@section('css')
    <style>
        .booking-filters-panel {
            position: relative;
            background: var(--bs-light-bg-subtle, #f8f9fa);
            border: 1px solid var(--bs-border-color);
            border-radius: 0.375rem;
            padding: 0.35rem 0.75rem 0.625rem;
        }

        .booking-filter-clear {
            position: absolute;
            top: 2px;
            right: 0.5rem;
            z-index: 2;
        }

        .booking-filter-clear .btn {
            padding: 0.1rem 0.35rem;
            font-size: 0.6875rem;
            height: 22px;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
        }

        #filtersSection .form-label {
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: var(--bs-secondary-color);
        }

        @include('admin.partials.mobile-filters-toggle-styles')
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
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
                    <div class="booking-filters-panel mb-3" id="filtersSection">
                        @include('admin.partials.mobile-filters-toggle-button')

                        <div class="booking-filters-body" id="mobileFiltersBody">
                            <div class="booking-filter-clear">
                                <button type="button" class="btn btn-sm btn-soft-secondary" id="clearFilters" title="Clear All">
                                    <i class="ri-filter-off-line"></i><span>Clear All</span>
                                </button>
                            </div>

                            <div class="row g-3">
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
                            <select id="filterCity" class="form-select form-select-sm" disabled>
                                <option value="">All Cities</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterStatus" class="form-label">Booking Status</label>
                            <select id="filterStatus" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="schedul_assign">Schedul Assign</option>
                                <option value="schedul_accepted">Schedul Accepted</option>
                                <option value="reschedul_accepted">Reschedul Accepted</option>
                                <option value="reschedul_assign">Reschedul Assign</option>
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
                        </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="bookingAssigneesTable" class="table table-hover dt-responsive nowrap w-100">
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
        aria-hidden="true"
        data-photographer-from="{{ $photographerSettings['photographer_available_from'] ?? '08:00' }}"
        data-photographer-to="{{ $photographerSettings['photographer_available_to'] ?? '21:00' }}"
        data-photographer-duration="{{ $photographerSettings['photographer_working_duration'] ?? '60' }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignBookingModalLabel">Assign Booking to Photographer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignBookingForm" method="POST" action="{{ route('admin.booking-assignees.store') }}">
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

                        <!-- Photographer Select -->
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

                        <!-- Assignment Details Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="modalDate" class="form-label">Booking Date</label>
                                <input type="date" id="modalDate" class="form-control" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="assignTime" class="form-label">Assign Time <span
                                        class="text-danger">*</span></label>
                                <select id="assignTime" name="time" class="form-select" disabled required>
                                    <option value="">Select a time</option>
                                </select>
                                <div class="mt-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="slotMode" id="slotModeAvailable"
                                            value="available" checked>
                                        <label class="form-check-label" for="slotModeAvailable">Available slots
                                            (default)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="slotMode" id="slotModeAny"
                                            value="any">
                                        <label class="form-check-label" for="slotModeAny">Pick any</label>
                                    </div>
                                </div>
                                <div id="assignTimeHelper" class="form-text text-muted small">Select a photographer first to
                                    see available slots from the API, or choose "Pick any" to ignore conflicts.</div>
                            </div>
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
    
    <!-- Reassignment Modal -->
    <div class="modal fade" id="reassignBookingModal" tabindex="-1" aria-labelledby="reassignBookingModalLabel"
        aria-hidden="true"
        data-photographer-from="{{ $photographerSettings['photographer_available_from'] ?? '08:00' }}"
        data-photographer-to="{{ $photographerSettings['photographer_available_to'] ?? '21:00' }}"
        data-photographer-duration="{{ $photographerSettings['photographer_working_duration'] ?? '60' }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reassignBookingModalLabel">Reassign Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="reassignBookingForm" method="POST">
                    @csrf
                    <input type="hidden" id="reassignAssigneeId" name="assignee_id">
                    <div class="modal-body">
                        <!-- Current Assignment Info -->
                        <div class="alert alert-warning mb-3">
                            <h6 class="mb-2"><i class="ri-information-line me-1"></i>Current Assignment</h6>
                            <div class="small">
                                <strong>Photographer:</strong> <span id="currentPhotographerName">-</span><br>
                                <strong>Time:</strong> <span id="currentAssignedTime">-</span>
                            </div>
                        </div>
                        
                        <!-- Booking Details Section -->
                        <div class="alert alert-info mb-3">
                            <h6 class="mb-2">Booking Details</h6>
                            <div class="row g-2 small">
                                <div class="col-md-6">
                                    <strong>Customer Name:</strong>
                                    <p id="reassignModalCustomer" class="mb-1">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Pin Code:</strong>
                                    <p id="reassignModalPincode" class="mb-1">-</p>
                                </div>
                                <div class="col-md-12">
                                    <strong>Address:</strong>
                                    <p id="reassignModalAddress" class="mb-1">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>City:</strong>
                                    <p id="reassignModalCity" class="mb-0">-</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>State:</strong>
                                    <p id="reassignModalState" class="mb-0">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- New Photographer Select -->
                        <div class="mb-3">
                            <label for="reassignPhotographer" class="form-label">New Photographer <span
                                    class="text-danger">*</span></label>
                            <select id="reassignPhotographer" name="user_id" class="form-select" required>
                                <option value="">-- Select Photographer --</option>
                                @foreach ($users ?? [] as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- New Time Assignment -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="reassignModalDate" class="form-label">Booking Date</label>
                                <input type="date" id="reassignModalDate" class="form-control" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="reassignTime" class="form-label">New Time <span
                                        class="text-danger">*</span></label>
                                <select id="reassignTime" name="time" class="form-select" disabled required>
                                    <option value="">Select a time</option>
                                </select>
                                <div class="mt-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="reassignSlotMode" id="reassignSlotModeAvailable"
                                            value="available" checked>
                                        <label class="form-check-label" for="reassignSlotModeAvailable">Available slots</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="reassignSlotMode" id="reassignSlotModeAny"
                                            value="any">
                                        <label class="form-check-label" for="reassignSlotModeAny">Pick any</label>
                                    </div>
                                </div>
                                <div id="reassignTimeHelper" class="form-text text-muted small">Select a photographer first.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Reassign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/pages/booking-assignees-index.js'])
    <script>
        window.citiesOptionsUrl = @json(route('admin.api.cities.options'));
    </script>
@endsection