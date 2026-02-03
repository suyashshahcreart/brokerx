@extends('admin.layouts.vertical', ['title' => 'Bookings Assigner Calender', 'subTitle' => 'Manage Booking Assigner Schedules'])
@section('css')
    <style>
        #Booking-list {
            padding: 10px;
            width: 100%;
            height: auto;
            overflow: auto;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 4px;
        }

        .booking-box {
            cursor: pointer;
            font-size: 14px;
            margin: 10px 0;
            padding: 8px 10px;
            color: #ffffff;
            border-radius: 4px;
        }

        /* Hide duplicate time display from FullCalendar */
        .fc-event-time {
            display: none !important;
        }

        /* Ensure event title is displayed properly */
        .fc-event-title {
            font-weight: 700;
            padding: 2px 3px;
        }

        /* Make sure event text is visible and not duplicated */
        .fc-daygrid-event-frame {
            margin-bottom: 1px;
        }

        .fc-event {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <!-- top navigation and title -->
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Booking</a></li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">{{ $title }}</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    @if (auth()->check() && auth()->user()->hasRole('admin'))
                        <a href="{{ route('admin.booking-assignees.index') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Booking Assignees
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card panel-card border-primary border-top">
                <div class="card-body">
                    <div class="row">
                        <!-- Filter => Photographer,status -->
                        <div class="col-12">
                            @if(auth()->check() && auth()->user()->hasRole('admin'))
                                <div class="mt-2 mb-3">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label for="filterPhotographer" class="form-label">Photographer</label>
                                            <select id="filterPhotographer" class="form-select form-select-sm">
                                                <option value="">All Photographers</option>
                                                @foreach($photographers as $photographer)
                                                    <option value="{{ $photographer->id }}">{{ $photographer->firstname }}
                                                        {{ $photographer->lastname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="filterStatus" class="form-label">Status</label>
                                            <select id="filterStatus" class="form-select form-select-sm">
                                                <option value="">All Statuses</option>
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <div class="w-100">
                                                <button id="btnClearFilters" type="button"
                                                    class="btn btn-sm btn-outline-secondary w-50">Clear filters</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- CALENDER MAIN Assigne Booking list only for admin -->
                        @if (auth()->check() && auth()->user()->hasRole('admin'))
                            <div class="col-xl-3">
                                <div id="Booking-list">
                                    <p class="fw-bold">Status Color Details</p>
                                    <div class="external-event bg-soft-warning text-warning" data-class="bg-warning">
                                        <i class="ri-circle-fill me-2 vertical-middle"></i>Schedule Pending
                                    </div>
                                    <div class="external-event bg-soft-info text-info" data-class="bg-info">
                                        <i class="ri-circle-fill me-2 vertical-middle"></i>schedule accpted
                                    </div>
                                    <div class="external-event bg-soft-primary text-primary" data-class="bg-primary">
                                        <i class="ri-circle-fill me-2 vertical-middle"></i>Schedule assigned
                                    </div>
                                    <div class="external-event bg-soft-success text-success" data-class="bg-success">
                                        <i class="ri-circle-fill me-2 vertical-middle"></i>Schedule completed
                                    </div>
                                    <div class="external-event bg-soft-danger text-danger" data-class="bg-danger">
                                        <i class="ri-circle-fill me-2 vertical-middle"></i>schedule cancelled | Declined
                                    </div>
                                </div>
                            </div> <!-- end col-->
                            <div class="col-xl-9">
                                <!-- calendar for Admin -->
                                <div class="mt-4 mt-lg-0">
                                    <div id="calendar" data-booking-api="{{ route('api.booking-assignees.all-bookings') }}"
                                        data-is-admin="{{ auth()->check() && auth()->user()->hasRole('admin') ? '1' : '0' }}"
                                        data-check-in-route="{{ route('admin.booking-assignees.check-in-form',':id') }}"
                                        data-check-out-route="{{ route('admin.booking-assignees.check-out-form',':id') }}"
                                        data-booking-show-route="{{ route('admin.bookings.index',':id') }}"></div>
                                </div>
                            </div> <!-- end col -->
                        @endif
                        <!-- CALENDER MAIN: calender for Photographer -->
                        <div class="col-xl-12">
                            <div class="mt-4 mt-lg-0">
                                <div id="calendar" data-booking-api="{{ route('api.booking-assignees.all-bookings') }}"
                                    data-is-admin="{{ auth()->check() && auth()->user()->hasRole('admin') ? '1' : '0' }}"
                                    data-check-in-route="{{ url('admin/booking-assignees') }}/:id/check-in"
                                    data-check-out-route="{{ url('admin/booking-assignees') }}/:id/check-out"
                                    data-booking-show-route="{{ url('admin/bookings') }}/:id"></div>
                                @if(auth()->check() && !auth()->user()->hasRole('admin'))
                                    <div id="photographer-details-card" class="card mt-3 d-none">
                                        <div class="card-header">
                                            <h5 class="mb-0">Booking Details</h5>
                                        </div>
                                        <div class="card-body small">
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <strong>Booking ID:</strong>
                                                    <div id="ph-booking-id">—</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Status:</strong>
                                                    <div id="ph-status">—</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Customer:</strong>
                                                    <div id="ph-customer">—</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Property:</strong>
                                                    <div id="ph-property">—</div>
                                                </div>
                                                <div class="col-md-12">
                                                    <strong>Address:</strong>
                                                    <div id="ph-address">—</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>City / State:</strong>
                                                    <div id="ph-city-state">—</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Pincode:</strong>
                                                    <div id="ph-pincode">—</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Scheduled Date:</strong>
                                                    <div id="ph-date">—</div>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Scheduled Time:</strong>
                                                    <div id="ph-time">—</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div> <!-- end col -->
                    </div> <!-- end row -->
                </div> <!-- end card body-->
            </div> <!-- end card -->

            <!-- Check-in and Check-Out MODAL -->
            <div class="modal fade" id="event-modal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form class="needs-validation" name="event-form" id="forms-event" novalidate>
                            <div class="modal-header p-3 border-bottom-0">
                                <h5 class="modal-title" id="modal-title">Booking Assigne - Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-3 pb-3 pt-0">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="alert alert-info mb-0">
                                            <strong>Selected Booking Details</strong>
                                        </div>
                                    </div>
                                    <!-- DETAILS TABLE OF BOOKING , PHOTOGRAPHER  -->
                                    <div class="col-12">
                                        <table class="table table-sm mb-0">
                                            <tbody>
                                                <tr>
                                                    <th style="width:35%">Booking ID</th>
                                                    <td id="modal-booking-id">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Customer</th>
                                                    <td id="modal-booking-customer">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Property</th>
                                                    <td id="modal-booking-property">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Address</th>
                                                    <td id="modal-booking-address">—</td>
                                                </tr>
                                                <tr>
                                                    <th>City / State</th>
                                                    <td id="modal-booking-city-state">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Pincode</th>
                                                    <td id="modal-booking-pincode">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Scheduled Date</th>
                                                    <td id="modal-schedule-date">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Scheduled Time</th>
                                                    <td id="modal-schedule-time">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Assigned Photographer</th>
                                                    <td id="modal-photographer-name">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Photographer Mobile</th>
                                                    <td id="modal-photographer-mobile">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Assigned Time</th>
                                                    <td id="modal-assigned-time">—</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- MODULE 1: Accept/Decline (for schedule_pending) -->
                                    <div class="col-12 mt-2" id="modal-accept-decline-module" style="display: none;">
                                        <div class="alert alert-warning mb-2">
                                            <small><strong>Action Required:</strong> Please accept or decline this booking
                                                request.</small>
                                        </div>
                                        <div class="my-1">
                                            <label for="modal-accept-notes" class="form-label">Admin Notes (Optional)</label>
                                            <textarea id="modal-accept-notes" name="notes" class="form-control form-control-sm" 
                                                rows="2" maxlength="500" placeholder="Add any notes for the photographer..."></textarea>
                                            <small class="text-muted">Max 500 characters</small>
                                        </div>
                                        
                                        <!-- Action Buttons (only show if both forms are hidden) -->
                                        <div class="d-flex flex-wrap gap-2" id="modal-accept-decline-buttons" style="display: flex;">
                                            <button type="button" class="btn btn-success btn-sm" id="modal-accept-btn">
                                                <i class="ri-check-line me-1"></i> Accept Schedule
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" id="modal-decline-btn">
                                                <i class="ri-close-line me-1"></i> Decline Schedule
                                            </button>
                                            <a id="modal-view-booking-link-pending" href="#" class="btn btn-info btn-sm">
                                                <i class="ri-eye-line me-1"></i> View Full Details
                                            </a>
                                        </div>
                                    </div>

                                    <!-- MODULE 2: Assign Photographer (for schedule_accepted) -->
                                    <div class="col-12 mt-2" id="modal-assign-photographer-module" style="display: none;">
                                        <div id="modal-buttons-container" class="d-flex flex-wrap gap-2">
                                            <!-- Assign button will be injected here by JS -->
                                        </div>
                                    </div>

                                    <!-- MODULE 2b: Cancel/Reassign (for schedule_assign) -->
                                    <div class="col-12 mt-2" id="modal-cancel-reassign-module" style="display: none;">
                                        <div class="alert alert-info mb-2">
                                            <small><strong>Assignment Options:</strong> Change the time or reassign to a different photographer.</small>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-warning btn-sm" id="modal-reassign-btn">
                                                <i class="ri-exchange-line me-1"></i> Reassign
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" id="modal-cancel-assignment-btn">
                                                <i class="ri-close-circle-line me-1"></i> Cancel Assignment
                                            </button>
                                            <a id="modal-view-booking-link-assign" href="#" class="btn btn-info btn-sm" style="display: none;">
                                                <i class="ri-eye-line me-1"></i> View Booking
                                            </a>
                                        </div>
                                    </div>

                                    <!-- MODULE 3: Check-in/Check-out (for schedule_assigned) -->
                                    <div class="col-12 mt-2" id="modal-checkin-checkout-module" style="display: none;">
                                        <div class="d-flex flex-wrap gap-2" id="modal-action-buttons">
                                            <a id="modal-check-in-link" href="#" class="btn btn-primary"
                                                style="display: none;">
                                                <i class="ri-box-arrow-in-right-line me-1"></i> Go to Check-In
                                            </a>
                                            <a id="modal-check-out-link" href="#" class="btn btn-warning"
                                                style="display: none;">
                                                <i class="ri-box-arrow-out-left-line me-1"></i> Go to Check-Out
                                            </a>
                                            <a id="modal-completed-link" href="#" class="btn btn-success"
                                                style="display: none;" disabled>
                                                <i class="ri-checkbox-circle-line me-1"></i> Completed
                                            </a>
                                            <a id="modal-view-booking-link" href="#" class="btn btn-info"
                                                style="display: none;">
                                                <i class="ri-eye-line me-1"></i> View Booking Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div> <!-- end modal-content-->
                </div> <!-- end modal dialog-->
            </div> <!-- end modal-->
            
            <!-- Assignment Modal (same logic as booking assigne module) -->
            <div class="modal fade" id="assignBookingModal" tabindex="-1" aria-labelledby="assignBookingModalLabel"
                aria-hidden="true"
                data-photographer-from="{{ \App\Models\Setting::where('name', 'photographer_available_from')->value('value') ?? '08:00' }}"
                data-photographer-to="{{ \App\Models\Setting::where('name', 'photographer_available_to')->value('value') ?? '21:00' }}"
                data-photographer-duration="{{ \App\Models\Setting::where('name', 'photographer_working_duration')->value('value') ?? '60' }}">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="assignBookingModalLabel">Assign Booking to Photographer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="assignBookingForm" method="POST" action="{{ route('admin.booking-assignees.store') }}">
                            @csrf
                            <input type="hidden" name="booking_id" id="assignBookingId" value="">
                            <input type="hidden" name="date" id="assignDate" value="">
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
                                            <strong>Customer Mobile:</strong>
                                            <p id="modalCustomerMobile" class="mb-1">-</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Property Type:</strong>
                                            <p id="modalPropertyType" class="mb-1">-</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Scheduled Date:</strong>
                                            <p id="modalScheduledDate" class="mb-1">-</p>
                                        </div>
                                        <div class="col-md-12">
                                            <strong>Address:</strong>
                                            <p id="modalAddress" class="mb-1">-</p>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>City:</strong>
                                                <p id="modalCity" class="mb-0">-</p>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>State:</strong>
                                                <p id="modalState" class="mb-0">-</p>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Pin Code:</strong>
                                                <p id="modalPincode" class="mb-0">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Photographer Select -->
                                <div class="mb-3 mt-3">
                                    <label for="assignPhotographer" class="form-label">Select Photographer <span
                                            class="text-danger">*</span></label>
                                    <select id="assignPhotographer" name="user_id" class="form-select" required>
                                        <option value="">-- Select Photographer --</option>
                                        @php $hasPhotographers = isset($photographers) && (is_iterable($photographers)) && count($photographers); @endphp
                                        @if($hasPhotographers)
                                            @foreach ($photographers as $photographer)
                                                @if(is_object($photographer))
                                                    <option value="{{ $photographer->id }}">{{ $photographer->firstname }}
                                                        {{ $photographer->lastname }}</option>
                                                @endif
                                            @endforeach
                                        @else
                                            <option value="">No photographers found</option>
                                        @endif
                                    </select>
                                </div>

                                <!-- Slot Mode Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Slot Selection Mode</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="slotMode" id="slotModeAvailable" value="available" checked>
                                            <label class="form-check-label" for="slotModeAvailable">
                                                Available Slots Only
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="slotMode" id="slotModeAny" value="any">
                                            <label class="form-check-label" for="slotModeAny">
                                                Pick Any Time
                                            </label>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        Choose "Available Slots Only" to see only free slots, or "Pick Any Time" to ignore existing assignments.
                                    </small>
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
                                        <div id="assignTimeHelper" class="form-text text-muted small">Select a photographer
                                            first to see available slots.</div>
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
        </div> <!-- end col -->
    </div> <!-- end row -->
    
    <!-- Reassignment Modal for Photographer Calendar -->
    <div class="modal fade" id="reassignPhotographerModal" tabindex="-1" aria-labelledby="reassignPhotographerModalLabel"
        aria-hidden="true"
        data-photographer-from="{{ \App\Models\Setting::where('name', 'photographer_available_from')->value('value') ?? '08:00' }}"
        data-photographer-to="{{ \App\Models\Setting::where('name', 'photographer_available_to')->value('value') ?? '21:00' }}"
        data-photographer-duration="{{ \App\Models\Setting::where('name', 'photographer_working_duration')->value('value') ?? '60' }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reassignPhotographerModalLabel">Reassign Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="reassignPhotographerForm" method="POST">
                    @csrf
                    <input type="hidden" id="reassignAssigneeId" name="assignee_id">
                    <div class="modal-body">
                        <!-- Current Assignment Info -->
                        <div class="alert alert-warning mb-3">
                            <h6 class="mb-2"><i class="ri-information-line me-1"></i>Current Assignment</h6>
                            <div class="small">
                                <strong>Photographer:</strong> <span id="reassignCurrentPhotographerName">-</span><br>
                                <strong>Time:</strong> <span id="reassignCurrentTime">-</span>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="alert alert-info mb-3">
                            <h6 class="mb-2">Booking Details</h6>
                            <div class="row g-2 small">
                                <div class="col-md-6">
                                    <strong>Customer:</strong>
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
                            <label for="reassignPhotographer" class="form-label">New Photographer <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select id="reassignPhotographer" name="user_id" class="form-select" required>
                                    <option value="">-- Select Photographer --</option>
                                    @foreach ($photographers as $photographer)
                                        <option value="{{ $photographer->id }}">{{ $photographer->firstname }} {{ $photographer->lastname }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary" id="keepSamePhotographerBtn" title="Keep same photographer and just change time">
                                    <i class="ri-checkbox-line"></i> Keep Same
                                </button>
                            </div>
                        </div>

                        <!-- New Time Assignment -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="reassignModalDate" class="form-label">Booking Date</label>
                                <input type="date" id="reassignModalDate" class="form-control" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="reassignTime" class="form-label">New Time <span class="text-danger">*</span></label>
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
    <script>
        window.PENDING_SCHEDULE_ACCEPT_URL = @json(route('admin.pending-schedules.accept', ['booking' => ':id']));
        window.PENDING_SCHEDULE_DECLINE_URL = @json(route('admin.pending-schedules.decline', ['booking' => ':id']));
    </script>
    @vite(['resources/js/pages/photographer-index.js',])
@endsection