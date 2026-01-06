                    <!-- Quick Actions Card -->
                    @if($hasAnyQuickActionPermission ?? false)
                    <div class="card border mb-3 ">
                        <div class="card-header bg-dark text-white">
                            <h5 class="card-title mb-0"><i class="ri-flashlight-line me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body p-2">
                            <!-- Payment Status -->
                            @if($canUpdatePaymentStatus ?? false)
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted" style="font-size: 11px;">PAYMENT
                                    STATUS</label>
                                <select class="form-select form-select-sm" id="quickPaymentStatus"
                                    onchange="updatePaymentStatus(this.value)">
                                    <option value="unpaid" {{ $booking->payment_status == 'unpaid' ? 'selected' : '' }}>
                                        Unpaid</option>
                                    <option value="pending" {{ $booking->payment_status == 'pending' ? 'selected' : '' }}>
                                        Pending</option>
                                    <option value="paid" {{ $booking->payment_status == 'paid' ? 'selected' : '' }}>Paid
                                    </option>
                                    <option value="failed" {{ $booking->payment_status == 'failed' ? 'selected' : '' }}>
                                        Failed</option>
                                    <option value="refunded" {{ $booking->payment_status == 'refunded' ? 'selected' : '' }}>
                                        Refunded                                    </option>
                                </select>
                            </div>
                            @endif

                            <!-- Booking Status -->
                            @if($canUpdateStatus ?? false)
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted" style="font-size: 11px;">BOOKING
                                    STATUS</label>
                                <select class="form-select form-select-sm" id="quickBookingStatus"
                                    onchange="updateBookingStatus(this.value)">
                                    <option value="inquiry" {{ $booking->status == 'inquiry' ? 'selected' : '' }}>Inquiry
                                    </option>
                                    <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>
                                        Confirmed</option>
                                    <option value="schedul_pending"
                                        {{ $booking->status == 'schedul_pending' ? 'selected' : '' }}>Schedule Pending
                                    </option>
                                    <option value="schedul_accepted"
                                        {{ $booking->status == 'schedul_accepted' ? 'selected' : '' }}>Schedule Accepted
                                    </option>
                                    <option value="schedul_decline"
                                        {{ $booking->status == 'schedul_decline' ? 'selected' : '' }}>Schedule Declined
                                    </option>
                                    <option value="reschedul_pending"
                                        {{ $booking->status == 'reschedul_pending' ? 'selected' : '' }}>Reschedule Pending
                                    </option>
                                    <option value="reschedul_accepted"
                                        {{ $booking->status == 'reschedul_accepted' ? 'selected' : '' }}>Reschedule Accepted
                                    </option>
                                    <option value="reschedul_decline"
                                        {{ $booking->status == 'reschedul_decline' ? 'selected' : '' }}>Reschedule Declined
                                    </option>
                                    <option value="reschedul_blocked"
                                        {{ $booking->status == 'reschedul_blocked' ? 'selected' : '' }}>Reschedule Blocked
                                    </option>
                                    <option value="schedul_assign"
                                        {{ $booking->status == 'schedul_assign' ? 'selected' : '' }}>Schedule Assigned
                                    </option>
                                    <option value="schedul_completed"
                                        {{ $booking->status == 'schedul_completed' ? 'selected' : '' }}>Schedule Completed
                                    </option>
                                    <option value="tour_pending" {{ $booking->status == 'tour_pending' ? 'selected' : '' }}>
                                        Tour Pending</option>
                                    <option value="tour_completed"
                                        {{ $booking->status == 'tour_completed' ? 'selected' : '' }}>Tour Completed</option>
                                    <option value="tour_live" {{ $booking->status == 'tour_live' ? 'selected' : '' }}>Tour
                                        Live</option>
                                    <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                    <option value="maintenance" {{ $booking->status == 'maintenance' ? 'selected' : '' }}>
                                        Maintenance</option>
                                    <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled</option>
                                    <option value="expired" {{ $booking->status == 'expired' ? 'selected' : '' }}>Expired
                                    </option>
                                </select>
                            </div>
                            @endif

                            <hr class="my-3">

                            <!-- Schedule Date -->
                            @if($canSchedule ?? false)
                            <div class="mb-3">
                                <button class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal"
                                    data-bs-target="#scheduleModal">
                                    <i class="ri-calendar-check-line me-1"></i> Schedule Date
                                </button>
                            </div>
                            @endif


                            <!-- Accept/Decline Schedule (if pending) -->
                            @if(in_array($booking->status, ['schedul_pending', 'reschedul_pending']) && ($canApproval ?? false))
                                <hr class="my-3">

                                <div class="card border-warning mb-3">
                                    <div class="card-header bg-warning-subtle border-warning py-2">
                                        <h6 class="mb-0 text-warning">
                                            <i class="ri-alert-line me-1"></i>
                                            {{ $booking->status === 'reschedul_pending' ? 'Reschedule' : 'Schedule' }} Approval
                                            Required
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1"
                                                style="font-size: 10px; font-weight: 600;">REQUESTED DATE</small>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ri-calendar-line text-primary"></i>
                                                @if($booking->booking_date)
                                                    <strong class="text-dark">{{ $booking->booking_date->format('d M, Y') }}</strong>
                                                    <small class="text-muted">({{ $booking->booking_date->format('l') }})</small>
                                                @else
                                                    <strong class="text-muted fst-italic">Not specified</strong>
                                                @endif
                                            </div>
                                        </div>

                                        @if($booking->booking_notes)
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1"
                                                    style="font-size: 10px; font-weight: 600;">CUSTOMER NOTES</small>
                                                <div class="alert alert-light border mb-0 py-2">
                                                    <small><i
                                                            class="ri-message-3-line me-1"></i>{{ $booking->booking_notes }}</small>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="d-grid gap-2">
                                            <button class="btn btn-success btn-sm" onclick="acceptScheduleFromShow()">
                                                <i class="ri-check-line me-1"></i> Accept
                                                {{ $booking->status === 'reschedul_pending' ? 'Reschedule' : 'Schedule' }}
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="declineScheduleFromShow()">
                                                <i class="ri-close-line me-1"></i> Decline
                                                {{ $booking->status === 'reschedul_pending' ? 'Reschedule' : 'Schedule' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Booking Assignees (if schedule accepted) -->
                            @if(in_array($booking->status, ['schedul_accepted', 'reschedul_accepted']) && ($canManageAssignees ?? false))
                                <hr class="my-3">

                                <div class="card border-info mb-3">
                                    <div class="card-header bg-info-subtle border-info py-2">
                                        <h6 class="mb-0 text-info">
                                            <i class="ri-user-add-line me-1"></i> Booking Assignees
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">
                                        @php
                                            $photographerAssignee = $booking->assignees->first(function ($assignee) {
                                                return $assignee->user && $assignee->user->hasRole('photographer');
                                            });
                                        @endphp

                                        @if($photographerAssignee)
                                            <div class="alert alert-success mb-3 py-2">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <strong class="d-block mb-1">Assigned Photographer</strong>
                                                        <small class="d-block mb-1">
                                                            <i
                                                                class="ri-user-line me-1"></i>{{ $photographerAssignee->user->firstname }}
                                                            {{ $photographerAssignee->user->lastname }}
                                                        </small>
                                                        @if($photographerAssignee->date)
                                                            <small class="d-block mb-1">
                                                                <i class="ri-calendar-line me-1"></i>Date:
                                                                {{ \Carbon\Carbon::parse($photographerAssignee->date)->format('d M, Y') }}
                                                            </small>
                                                        @endif
                                                        @if($photographerAssignee->time)
                                                            <small class="d-block">
                                                                <i class="ri-time-line me-1"></i>Time:
                                                                {{ \Carbon\Carbon::parse($photographerAssignee->time)->format('h:i A') }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-muted small mb-3">No photographer assigned yet. Click the button below to
                                                assign one.</p>
                                        @endif

                                        @if($canManageAssignees ?? false)
                                        <button class="btn btn-info btn-sm w-100" data-bs-toggle="modal"
                                            data-bs-target="#assignBookingModal">
                                            <i class="ri-user-add-line me-1"></i> Assign Photographer
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            @endif

                           

                        </div>
                    </div>

                    <!-- Schedule Modal -->
                    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="scheduleModalLabel">Schedule Booking</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="scheduleForm">
                                        <div class="mb-3">
                                            <label class="form-label">Current Booking Date</label>
                                            <div id="current-booking-date" class="form-control-plaintext text-primary mb-2">
                                                {{ $booking->booking_date ? $booking->booking_date->format('Y-m-d') : 'Not set' }}
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Schedule Mode</label>
                                                <div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="schedule_mode"
                                                            id="schedule-mode-default" value="default" checked>
                                                        <label class="form-check-label" for="schedule-mode-default">Default</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="schedule_mode"
                                                            id="schedule-mode-any" value="any">
                                                        <label class="form-check-label" for="schedule-mode-any">Pick Any Day</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <label for="schedule-date" class="form-label mt-3">Select Date</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-primary text-white" id="calendar-icon-trigger"
                                                    style="cursor: pointer;" title="Open calendar">
                                                    <i class="ri-calendar-line"></i>
                                                </span>
                                                <input type="text" class="form-control" id="schedule-date" name="schedule_date"
                                                    placeholder="Click to select date" required autocomplete="off" style="background-color: #fff;">
                                            </div>
                                        </div>
                                        <input type="hidden" id="schedule-booking-id" name="booking_id" value="{{ $booking->id }}">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" id="scheduleSubmitBtn">Schedule</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assign Booking to Photographer Modal -->
                    <div class="modal fade" id="assignBookingModal" tabindex="-1" aria-labelledby="assignBookingModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="assignBookingModalLabel">Assign Booking to Photographer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="assignBookingForm" method="POST" action="{{ route('admin.booking-assignees.store') }}">
                                    @csrf
                                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                    <div class="modal-body">
                                        <!-- Booking Details Section -->
                                        <div class="alert alert-info mb-3">
                                            <h6 class="mb-2">Booking Details</h6>
                                            <div class="row g-2 small">
                                                <div class="col-md-6">
                                                    <strong>Customer Name:</strong>
                                                    <p id="modalCustomer" class="mb-1">{{ $booking->user->firstname }}
                                                        {{ $booking->user->lastname }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Pin Code:</strong>
                                                    <p id="modalPincode" class="mb-1">{{ $booking->pin_code ?? '-' }}</p>
                                                </div>
                                                <div class="col-md-12">
                                                    <strong>Address:</strong>
                                                    <p id="modalAddress" class="mb-1">
                                                        {{ $booking->full_address ?? ($booking->house_no . ', ' . $booking->building . ', ' . ($booking->society_name ?? '') . ', ' . ($booking->address_area ?? '')) }}
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>City:</strong>
                                                    <p id="modalCity" class="mb-0">{{ $booking->city?->name ?? '-' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>State:</strong>
                                                    <p id="modalState" class="mb-0">{{ $booking->state?->name ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Assignment Details Section -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="modalDate" class="form-label">Booking Date</label>
                                                <input type="date" id="modalDate" class="form-control"
                                                    value="{{ $booking->booking_date ? $booking->booking_date->format('Y-m-d') : '' }}"
                                                    disabled>
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
                                                @foreach ($photographers ?? [] as $photographer)
                                                    <option value="{{ $photographer->id }}">{{ $photographer->name }}</option>
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
                    @endif
