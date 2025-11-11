@extends('layouts.vertical', ['title' => 'Shedule An Appointment', 'subTitle' => 'Real Estate'])
@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ri-check-line me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ri-error-warning-line me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3">
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" id="btn-new-event" data-bs-toggle="modal" data-bs-target="#event-modal">
                                    <i class="ri-add-line fs-18 me-2"></i> Add New Appointment
                                </button>
                            </div>
                            <div id="external-events">
                                <br>
                                <p class="text-muted">Click to schedule a new appointment</p>
                                <div class="external-event bg-soft-primary text-primary" data-class="bg-primary">
                                    <i class="ri-circle-fill me-2 vertical-middle"></i>Pending Appointments
                                </div>
                                <div class="external-event bg-soft-success text-success" data-class="bg-success">
                                    <i class="ri-circle-fill me-2 vertical-middle"></i>Confirmed Appointments
                                </div>
                                <div class="external-event bg-soft-info text-info" data-class="bg-info">
                                    <i class="ri-circle-fill me-2 vertical-middle"></i>Completed Appointments
                                </div>
                                <div class="external-event bg-soft-danger text-danger" data-class="bg-danger">
                                    <i class="ri-circle-fill me-2 vertical-middle"></i>Cancelled Appointments
                                </div>
                            </div>
                        </div> <!-- end col-->

                        <div class="col-xl-9">
                            <div class="mt-4 mt-lg-0">
                                <div id="calendar"></div>
                            </div>
                        </div> <!-- end col -->

                    </div> <!-- end row -->
                </div> <!-- end card body-->
            </div> <!-- end card -->

            <!-- Add New Appointment MODAL -->
            <div class="modal fade" id="event-modal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form class="needs-validation" action="{{ route('appointments.store') }}" method="POST" id="forms-appointment" novalidate>
                            @csrf
                            <div class="modal-header p-3 border-bottom-0">
                                <h5 class="modal-title" id="modal-title">Schedule New Appointment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-3 pb-3 pt-0">
                                <!-- Hidden Scheduler ID (from logged-in scheduler) -->
                                <input type="hidden" name="scheduler_id" value="{{ $loggedInScheduler->id ?? '' }}">
                                
                                <!-- Scheduler Info Display -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="alert alert-info mb-3">
                                            <i class="ri-user-line me-2"></i>
                                            <strong>Scheduler:</strong> {{ $loggedInScheduler->name ?? 'N/A' }}
                                            @if($loggedInScheduler->mobile ?? false)
                                                <span class="ms-2">| <i class="ri-phone-line"></i> {{ $loggedInScheduler->mobile }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Date and Time -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="control-label form-label">Date <span class="text-danger">*</span></label>
                                            <input class="form-control" type="date" name="date" id="appointment-date" 
                                                   min="{{ date('Y-m-d') }}" required />
                                            <div class="invalid-feedback">Please provide a valid date</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="control-label form-label">Start Time <span class="text-danger">*</span></label>
                                            <input class="form-control" type="time" name="start_time" id="start-time" required />
                                            <div class="invalid-feedback">Please provide start time</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="control-label form-label">End Time</label>
                                            <input class="form-control" type="time" name="end_time" id="end-time" />
                                            <div class="invalid-feedback">End time must be after start time</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="control-label form-label">Address <span class="text-danger">*</span></label>
                                            <input class="form-control" placeholder="Street Address" type="text"
                                                name="address" id="appointment-address" required />
                                            <div class="invalid-feedback">Please provide an address</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- City, State -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="control-label form-label">City <span class="text-danger">*</span></label>
                                            <input class="form-control" placeholder="City" type="text"
                                                name="city" id="appointment-city" required />
                                            <div class="invalid-feedback">Please provide a city</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="control-label form-label">State <span class="text-danger">*</span></label>
                                            <input class="form-control" placeholder="State" type="text"
                                                name="state" id="appointment-state" required />
                                            <div class="invalid-feedback">Please provide a state</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Country, Pin Code -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="control-label form-label">Country <span class="text-danger">*</span></label>
                                            <input class="form-control" placeholder="Country" type="text"
                                                name="country" id="appointment-country" value="" required />
                                            <div class="invalid-feedback">Please provide a country</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="control-label form-label">Pin Code <span class="text-danger">*</span></label>
                                            <input class="form-control" placeholder="Pin Code" type="text"
                                                name="pin_code" id="appointment-pincode" required />
                                            <div class="invalid-feedback">Please provide a pin code</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="control-label form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select" name="status" id="appointment-status" required>
                                                <option value="pending" selected>Pending</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="cancelled">Cancelled</option>
                                                <option value="completed">Completed</option>
                                            </select>
                                            <div class="invalid-feedback">Please select a status</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-light me-1"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" id="btn-save-appointment">
                                            <i class="ri-save-line me-1"></i> Save Appointment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div> <!-- end modal-content-->
                </div> <!-- end modal dialog-->
            </div> <!-- end modal-->
        </div> <!-- end col -->
    </div> <!-- end row -->

@endsection

@section('script-bottom')
    @vite(['resources/js/pages/app-calendar.js'])
    
    <script>
        // Form validation for appointment
        (function() {
            'use strict';
            
            // Fetch all the forms we want to apply custom Bootstrap validation to
            var forms = document.querySelectorAll('.needs-validation');
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });

            // Validate end time is after start time
            const startTime = document.getElementById('start-time');
            const endTime = document.getElementById('end-time');
            
            if (startTime && endTime) {
                endTime.addEventListener('change', function() {
                    if (startTime.value && endTime.value) {
                        if (endTime.value <= startTime.value) {
                            endTime.setCustomValidity('End time must be after start time');
                        } else {
                            endTime.setCustomValidity('');
                        }
                    }
                });
            }
        })();

        // Show success/error messages
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Error!',
                html: '<ul style="text-align: left;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
            });
        @endif
    </script>
@endsection
