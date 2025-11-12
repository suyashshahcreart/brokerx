@extends('sheduler.layout.vertical', ['title' => 'Scheduler Details', 'subTitle' => 'Appointments'])
@section('content')

<!-- Scheduler Details Card -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Scheduler Info -->
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative">
                                <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:user-bold-duotone" class="fs-1 text-primary"></iconify-icon>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h4 class="mb-1 text-dark">{{ $scheduler->name ?? 'N/A' }}</h4>
                                <div class="d-flex flex-column gap-1">
                                    <p class="mb-0 d-flex align-items-center gap-2">
                                        <iconify-icon icon="solar:phone-calling-rounded-bold-duotone" class="fs-18 text-primary"></iconify-icon>
                                        <span class="text-muted">{{ $scheduler->mobile ?? 'N/A' }}</span>
                                        @if($scheduler->mobile_verified_at)
                                            <span class="badge bg-success-subtle text-success fs-11">
                                                <i class="ri-verified-badge-line"></i> Verified
                                            </span>
                                        @endif
                                    </p>
                                    @if($scheduler->email)
                                    <p class="mb-0 d-flex align-items-center gap-2">
                                        <iconify-icon icon="solar:letter-bold-duotone" class="fs-18 text-primary"></iconify-icon>
                                        <span class="text-muted">{{ $scheduler->email }}</span>
                                        @if($scheduler->email_verified_at)
                                            <span class="badge bg-success-subtle text-success fs-11">
                                                <i class="ri-verified-badge-line"></i> Verified
                                            </span>
                                        @endif
                                    </p>
                                    @endif
                                    <p class="mb-0 d-flex align-items-center gap-2">
                                        <iconify-icon icon="solar:calendar-bold-duotone" class="fs-18 text-primary"></iconify-icon>
                                        <span class="text-muted">Joined {{ $scheduler->created_at->format('M d, Y') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appointment Statistics -->
                    <div class="col-lg-4">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="card bg-warning-subtle border-0 mb-0">
                                    <div class="card-body p-3 text-center">
                                        <h3 class="mb-1 text-warning">{{ $pendingAppointments }}</h3>
                                        <p class="mb-0 fs-13 text-muted">Pending</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-info-subtle border-0 mb-0">
                                    <div class="card-body p-3 text-center">
                                        <h3 class="mb-1 text-info">{{ $confirmedAppointments }}</h3>
                                        <p class="mb-0 fs-13 text-muted">Confirmed</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-success-subtle border-0 mb-0">
                                    <div class="card-body p-3 text-center">
                                        <h3 class="mb-1 text-success">{{ $completedAppointments }}</h3>
                                        <p class="mb-0 fs-13 text-muted">Completed</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-danger-subtle border-0 mb-0">
                                    <div class="card-body p-3 text-center">
                                        <h3 class="mb-1 text-danger">{{ $cancelledAppointments }}</h3>
                                        <p class="mb-0 fs-13 text-muted">Cancelled</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointments List Section -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Appointments</h4>
                <div class="d-flex gap-2">
                    <span class="badge bg-warning">Pending: {{ $pendingAppointments }}</span>
                    <span class="badge bg-info">Confirmed: {{ $confirmedAppointments }}</span>
                    <span class="badge bg-success">Completed: {{ $completedAppointments }}</span>
                    <span class="badge bg-danger">Cancelled: {{ $cancelledAppointments }}</span>
                </div>
            </div>
            <div class="card-body">
                @if($appointments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Date & Time</th>
                                <th>Address</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointments as $index => $appointment)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium text-dark">{{ $appointment->date->format('M d, Y') }}</span>
                                        <small class="text-muted">{{ $appointment->start_time }} - {{ $appointment->end_time }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium">{{ $appointment->address }}</span>
                                        <small class="text-muted">{{ $appointment->city }}, {{ $appointment->state }} {{ $appointment->pin_code }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($appointment->assignedTo)
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ asset('images/users/avatar-2.jpg') }}" alt="" class="avatar-sm rounded-circle">
                                            <div>
                                                <span class="fw-medium text-dark">{{ $appointment->assignedTo->name }}</span>
                                                <br><small class="text-muted">{{ $appointment->assignedTo->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary">Not Assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($appointment->status)
                                        @case('pending')
                                            <span class="badge bg-warning-subtle text-warning">
                                                <i class="ri-time-line me-1"></i>Pending
                                            </span>
                                            @break
                                        @case('confirmed')
                                            <span class="badge bg-info-subtle text-info">
                                                <i class="ri-checkbox-circle-line me-1"></i>Confirmed
                                            </span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="ri-check-double-line me-1"></i>Completed
                                            </span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger-subtle text-danger">
                                                <i class="ri-close-circle-line me-1"></i>Cancelled
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="#" class="btn btn-sm btn-soft-primary" data-bs-toggle="tooltip" title="View Details">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <a href="javascript:void(0);" 
                                           class="btn btn-sm btn-soft-info edit-appointment-btn" 
                                           data-bs-toggle="modal" 
                                           data-bs-target="#editAppointmentModal"
                                           data-appointment-id="{{ $appointment->id }}"
                                           data-date="{{ $appointment->date->format('Y-m-d') }}"
                                           data-start-time="{{ $appointment->start_time }}"
                                           data-end-time="{{ $appointment->end_time }}"
                                           data-address="{{ $appointment->address }}"
                                           data-city="{{ $appointment->city }}"
                                           data-state="{{ $appointment->state }}"
                                           data-country="{{ $appointment->country }}"
                                           data-pin-code="{{ $appointment->pin_code }}"
                                           data-status="{{ $appointment->status }}"
                                           data-assigned-to="{{ $appointment->assigne_to }}"
                                           title="Edit">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        @if($appointment->status == 'pending')
                                        <a href="#" class="btn btn-sm btn-soft-success" data-bs-toggle="tooltip" title="Confirm">
                                            <i class="ri-check-line"></i>
                                        </a>
                                        @endif
                                        @if(in_array($appointment->status, ['pending', 'confirmed']))
                                        <a href="#" class="btn btn-sm btn-soft-danger" data-bs-toggle="tooltip" title="Cancel">
                                            <i class="ri-close-line"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="avatar-title bg-light text-muted rounded-circle fs-1">
                            <i class="ri-calendar-line"></i>
                        </div>
                    </div>
                    <h5 class="text-muted">No Appointments Found</h5>
                    <p class="text-muted mb-0">This scheduler doesn't have any appointments yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Appointment Modal -->
<div class="modal fade" id="editAppointmentModal" tabindex="-1" aria-labelledby="editAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAppointmentModalLabel">
                    <i class="ri-edit-line me-2"></i>Update Appointment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAppointmentForm">
                @csrf
                <input type="hidden" id="appointment_id" name="appointment_id">
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        You can only update the time and location details of the appointment.
                    </div>
                    
                    <div class="row g-3">
                        <!-- Start Time -->
                        <div class="col-md-6">
                            <label for="edit_start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                            <div class="invalid-feedback" id="start_time-error"></div>
                        </div>

                        <!-- End Time -->
                        <div class="col-md-6">
                            <label for="edit_end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
                            <div class="invalid-feedback" id="end_time-error"></div>
                        </div>

                        <!-- Address -->
                        <div class="col-12">
                            <label for="edit_address" class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_address" name="address" required>
                            <div class="invalid-feedback" id="address-error"></div>
                        </div>

                        <!-- City -->
                        <div class="col-md-6">
                            <label for="edit_city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_city" name="city" required>
                            <div class="invalid-feedback" id="city-error"></div>
                        </div>

                        <!-- State -->
                        <div class="col-md-6">
                            <label for="edit_state" class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_state" name="state" required>
                            <div class="invalid-feedback" id="state-error"></div>
                        </div>

                        <!-- Country -->
                        <div class="col-md-6">
                            <label for="edit_country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_country" name="country" required>
                            <div class="invalid-feedback" id="country-error"></div>
                        </div>

                        <!-- Pin Code -->
                        <div class="col-md-6">
                            <label for="edit_pin_code" class="form-label">Pin Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_pin_code" name="pin_code" required>
                            <div class="invalid-feedback" id="pin_code-error"></div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="updateAppointmentBtn">
                        <i class="ri-save-line me-1"></i>Update Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script-bottom')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit appointment button click handler
    document.querySelectorAll('.edit-appointment-btn').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.dataset.appointmentId;
            const startTime = this.dataset.startTime;
            const endTime = this.dataset.endTime;
            const address = this.dataset.address;
            const city = this.dataset.city;
            const state = this.dataset.state;
            const country = this.dataset.country;
            const pinCode = this.dataset.pinCode;
            
            // Populate modal fields
            document.getElementById('appointment_id').value = appointmentId;
            document.getElementById('edit_start_time').value = startTime;
            document.getElementById('edit_end_time').value = endTime;
            document.getElementById('edit_address').value = address;
            document.getElementById('edit_city').value = city;
            document.getElementById('edit_state').value = state;
            document.getElementById('edit_country').value = country;
            document.getElementById('edit_pin_code').value = pinCode;
            
            // Clear previous errors
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        });
    });

    // Form submit handler
    document.getElementById('editAppointmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const appointmentId = document.getElementById('appointment_id').value;
        const formData = new FormData(this);
        const updateBtn = document.getElementById('updateAppointmentBtn');
        const originalBtnText = updateBtn.innerHTML;
        
        // Disable button and show loading
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
        
        // Clear previous errors
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        
        fetch(`/schedulers/appointments/${appointmentId}/update`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('editAppointmentModal')).hide();
                
                // Show success message
                showToast('Success', data.message || 'Appointment updated successfully!', 'success');
                
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Handle validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const input = document.getElementById(`edit_${key}`);
                        const errorDiv = document.getElementById(`${key}-error`);
                        if (input && errorDiv) {
                            input.classList.add('is-invalid');
                            errorDiv.textContent = data.errors[key][0];
                        }
                    });
                }
                showToast('Error', data.message || 'Failed to update appointment', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'An error occurred while updating the appointment', 'error');
        })
        .finally(() => {
            // Re-enable button
            updateBtn.disabled = false;
            updateBtn.innerHTML = originalBtnText;
        });
    });
    
    // Toast notification function
    function showToast(title, message, type) {
        // You can integrate with your existing toast notification system
        // For now, using a simple alert
        if (type === 'success') {
            alert(`${title}: ${message}`);
        } else {
            alert(`${title}: ${message}`);
        }
    }
});
</script>
@endsection
