@extends('admin.layouts.vertical', ['title' => 'Edit QR Code', 'subTitle' => 'System'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">System</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.qr.index') }}">QR Codes</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Edit QR Code</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.qr.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">QR Code Details</h4>
                    <p class="text-muted mb-0">Modify the QR code information and upload a new image if needed</p>
                </div>
                <div class="panel-actions d-flex gap-2">
                    <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                        <i class="ri-arrow-up-s-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="fullscreen" title="Fullscreen">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="close" title="Close">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.qr.update', $qr->id) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate id="qrEditForm">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" value="{{ $qr->code }}" required maxlength="8" minlength="8" pattern="[A-Za-z0-9]{8}">
                        <div class="form-text">Code must be exactly 8 characters (A-Z, a-z, 0-9 only)</div>
                        <div class="invalid-feedback">Please enter a valid 8-character code (A-Z, a-z, 0-9 only)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $qr->name }}" placeholder="Optional name (any text)">
                        <div class="form-text">Name is optional and can be any text</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Booking</label>
                        <select name="booking_id" id="booking_id" class="form-control">
                            <option value="">Select Booking</option>
                            @foreach($bookings as $booking)
                                @php
                                    // Build customer name
                                    $customerName = trim(($booking->user->firstname ?? '') . ' ' . ($booking->user->lastname ?? ''));
                                    if (empty($customerName)) {
                                        $customerName = 'N/A';
                                    }
                                    
                                    // Build property details
                                    $propertyDetails = [];
                                    if ($booking->propertyType) {
                                        $propertyDetails[] = $booking->propertyType->name;
                                    }
                                    if ($booking->propertySubType) {
                                        $propertyDetails[] = $booking->propertySubType->name;
                                    }
                                    if ($booking->bhk) {
                                        $propertyDetails[] = $booking->bhk->name;
                                    }
                                    if ($booking->furniture_type) {
                                        $propertyDetails[] = $booking->furniture_type;
                                    }
                                    if ($booking->area) {
                                        $propertyDetails[] = number_format($booking->area) . ' sq.ft';
                                    }
                                    $propertyText = !empty($propertyDetails) ? implode(' | ', $propertyDetails) : 'N/A';
                                    
                                    // Build address
                                    $addressParts = [];
                                    if ($booking->house_no) {
                                        $addressParts[] = $booking->house_no;
                                    }
                                    if ($booking->building) {
                                        $addressParts[] = $booking->building;
                                    }
                                    if ($booking->city) {
                                        $addressParts[] = $booking->city->name;
                                    }
                                    if ($booking->state) {
                                        $addressParts[] = $booking->state->name;
                                    }
                                    if ($booking->pin_code) {
                                        $addressParts[] = $booking->pin_code;
                                    }
                                    $addressText = !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
                                    
                                    // Build display text
                                    $displayParts = [
                                        'ID: ' . $booking->id,
                                        $customerName,
                                        $booking->user->mobile ?? 'N/A',
                                        ($booking->user->email ? $booking->user->email : ''),
                                        $propertyText,
                                        $addressText
                                    ];
                                    $displayText = implode(' | ', array_filter($displayParts));
                                @endphp
                                <option value="{{ $booking->id }}" @if($qr->booking_id == $booking->id) selected @endif>{{ $displayText }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- now just comment this here  --}}
                    {{-- <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control">
                        @if($qr->image)
                            <img src="/storage/{{ $qr->image }}" width="100" class="mt-2"/>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">QR Link</label>
                        <input type="text" name="qr_link" class="form-control" value="{{ $qr->qr_link }}">
                    </div> --}}
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('qrEditForm');
        const bookingSelect = document.getElementById('booking_id');
        
        // Store the original booking ID value
        const originalBookingId = '{{ $qr->booking_id ?? '' }}';
        let bookingChanged = false;
        
        // Track changes to booking select
        if (bookingSelect) {
            bookingSelect.addEventListener('change', function() {
                const currentValue = this.value || '';
                if (currentValue !== originalBookingId) {
                    bookingChanged = true;
                } else {
                    bookingChanged = false;
                }
            });
        }
        
        // Intercept form submission
        if (form) {
            form.addEventListener('submit', function(e) {
                // Only show confirmation if booking was changed
                if (bookingChanged) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Get the selected booking text for display
                    const selectedOption = bookingSelect.options[bookingSelect.selectedIndex];
                    const selectedBookingText = selectedOption ? selectedOption.text : 'the new booking';
                    const oldBookingId = originalBookingId || 'None';
                    const newBookingId = bookingSelect.value || 'None';
                    
                    // First confirmation
                    Swal.fire({
                        title: '⚠️ Warning: Booking Change',
                        html: `
                            <div style="text-align: left; padding: 1rem 0;">
                                <p style="margin-bottom: 1rem; font-weight: 600;">You are about to change the booking assignment for this QR code.</p>
                                <div style="background-color: #fff3cd; padding: 1rem; border-radius: 8px; border-left: 4px solid #ffc107; margin: 1rem 0;">
                                    <p style="margin: 0.5rem 0;"><strong>Current Booking ID:</strong> ${oldBookingId}</p>
                                    <p style="margin: 0.5rem 0;"><strong>New Booking ID:</strong> ${newBookingId}</p>
                                </div>
                                <div style="margin-top: 1rem;">
                                    <p style="color: #dc3545; font-weight: 600; margin-bottom: 0.5rem;">⚠️ This action will:</p>
                                    <ul style="margin: 0.5rem 0; padding-left: 1.5rem; color: #6c757d;">
                                        <li>Update the booking's tour code</li>
                                        <li>Affect tour-related functionality</li>
                                        <li>Impact other booking-dependent features</li>
                                    </ul>
                                </div>
                                <p style="margin-top: 1rem; color: #6c757d;">Are you absolutely sure you want to proceed?</p>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, I understand. Continue',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#ffc107',
                        cancelButtonColor: '#6c757d',
                        customClass: {
                            popup: 'swal2-popup-custom',
                            title: 'swal2-title-custom',
                            htmlContainer: 'swal2-html-container-custom',
                            confirmButton: 'btn btn-warning me-2',
                            cancelButton: 'btn btn-outline-secondary'
                        },
                        buttonsStyling: false,
                        reverseButtons: true,
                        focusConfirm: false,
                        allowOutsideClick: false
                    }).then((firstResult) => {
                        if (firstResult.isConfirmed) {
                            // Second confirmation (double check)
                            Swal.fire({
                                title: '⚠️ Final Confirmation Required',
                                html: `
                                    <div style="text-align: left; padding: 1rem 0;">
                                        <p style="margin-bottom: 1rem; font-weight: 600; color: #dc3545;">This is your final confirmation!</p>
                                        <div style="background-color: #f8d7da; padding: 1rem; border-radius: 8px; border-left: 4px solid #dc3545; margin: 1rem 0;">
                                            <p style="margin: 0.5rem 0;"><strong>Current Booking ID:</strong> ${oldBookingId}</p>
                                            <p style="margin: 0.5rem 0;"><strong>New Booking ID:</strong> ${newBookingId}</p>
                                            <p style="margin: 0.5rem 0;"><strong>Selected Booking:</strong> ${selectedBookingText}</p>
                                        </div>
                                        <p style="margin-top: 1rem; color: #6c757d;">
                                            By confirming, you acknowledge that you understand the implications of changing the booking assignment 
                                            and that this action will update the booking's tour code and related functionality.
                                        </p>
                                        <p style="margin-top: 1rem; font-weight: 600; color: #dc3545;">
                                            Click "Confirm Change" only if you are certain this is correct.
                                        </p>
                                    </div>
                                `,
                                icon: 'error',
                                showCancelButton: true,
                                confirmButtonText: 'Confirm Change',
                                cancelButtonText: 'Cancel',
                                confirmButtonColor: '#dc3545',
                                cancelButtonColor: '#6c757d',
                                customClass: {
                                    popup: 'swal2-popup-custom',
                                    title: 'swal2-title-custom',
                                    htmlContainer: 'swal2-html-container-custom',
                                    confirmButton: 'btn btn-danger me-2',
                                    cancelButton: 'btn btn-outline-secondary'
                                },
                                buttonsStyling: false,
                                reverseButtons: true,
                                focusConfirm: false,
                                allowOutsideClick: false
                            }).then((secondResult) => {
                                if (secondResult.isConfirmed) {
                                    // Both confirmations accepted, submit the form
                                    form.submit();
                                } else {
                                    // Reset booking select to original value if cancelled
                                    bookingSelect.value = originalBookingId;
                                    bookingChanged = false;
                                }
                            });
                        } else {
                            // Reset booking select to original value if cancelled
                            bookingSelect.value = originalBookingId;
                            bookingChanged = false;
                        }
                    });
                } else {
                    // Booking not changed, allow normal form submission
                    return true;
                }
            });
        }
    });
</script>

<style>
    /* SweetAlert Custom Styling */
    .swal2-popup-custom {
        border-radius: 16px !important;
        padding: 2rem !important;
        max-width: 600px !important;
    }
    
    .swal2-title-custom {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
        color: #1a1a1a !important;
        margin-bottom: 1rem !important;
    }
    
    .swal2-html-container-custom {
        text-align: left !important;
        padding: 0.5rem 0 !important;
        margin: 1rem 0 !important;
        font-size: 0.95rem !important;
        line-height: 1.6 !important;
    }
    
    .swal2-icon.swal2-warning {
        border-color: #ffc107 !important;
        color: #ffc107 !important;
    }
    
    .swal2-icon.swal2-error {
        border-color: #dc3545 !important;
        color: #dc3545 !important;
    }
    
    .swal2-confirm, .swal2-cancel {
        border-radius: 8px !important;
        padding: 0.6rem 2rem !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
        transition: all 0.3s ease !important;
    }
    
    .swal2-confirm:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
    }
</style>
@endsection
