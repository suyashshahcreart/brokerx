@extends('admin.layouts.vertical', ['title' => 'Edit Booking', 'subTitle' => 'Property'])

@section('css')
<!-- Font Awesome for dynamic icons from database -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Choices.js CSS -->
@vite(['node_modules/choices.js/public/assets/styles/choices.min.css'])

    <style>
        /* Pill and Chip Styles */
        .top-pill, .chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 18px;
            margin: 2px;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: #fff;
            font-size: 13px;
            font-weight: 500;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        
        .top-pill:hover, .chip:hover {
            border-color: #0d6efd;
            background-color: #f0f7ff;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.15);
        }
        
        .top-pill.active, .chip.active {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border-color: #0d6efd;
            color: #fff;
            box-shadow: 0 3px 6px rgba(13, 110, 253, 0.3);
            transform: translateY(-1px);
        }
        
        .top-pill i, .chip i {
            margin-right: 6px;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .top-pill.active i, .chip.active i {
            color: #fff !important;
        }
        
        /* Font Awesome and Remix Icon support */
        .top-pill .fa, .top-pill .fas, .top-pill .far, .top-pill .fab, .top-pill .fal, .top-pill .ri,
        .chip .fa, .chip .fas, .chip .far, .chip .fab, .chip .fal, .chip .ri {
            margin-right: 6px;
            font-size: 16px;
        }
        
        .d-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        
        .section-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            margin-top: 12px;
            color: #2c3e50;
        }
        
        /* Property Type Container Specific */
        #propertyTypeContainer .top-pill {
            padding: 10px 22px;
            font-size: 14px;
            font-weight: 600;
            min-width: 140px;
        }
        
        #ownerTypeContainer .top-pill {
            min-width: 120px;
        }
        
        .hidden {
            display: none;
        }
        
        /* Property type tabs container */
        #propertyTypeContainer {
            margin-bottom: 20px;
        }
        
        /* Readonly price field */
        #price[readonly] {
            background-color: #f8f9fa !important;
            cursor: not-allowed;
        }
        
        /* Card improvements */
        .card.border.bg-light-subtle {
            border: 1px solid #e3e6f0 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        
        .card-header.bg-primary-subtle {
            background: linear-gradient(135deg, #e7f1ff 0%, #d3e5ff 100%) !important;
            border-bottom: 2px solid #0d6efd !important;
        }
        
        .card-header.bg-success-subtle {
            background: linear-gradient(135deg, #d1e7dd 0%, #badbcc 100%) !important;
            border-bottom: 2px solid #198754 !important;
        }
        
        /* Form spacing improvements */
        .form-control, .form-select {
            font-size: 13px;
            padding: 8px 12px;
        }
        
        .form-label {
            font-size: 13px;
            margin-bottom: 4px;
        }
        
        /* Gap utilities */
        .gap {
            gap: 6px !important;
        }
        
        /* Tabs visibility animation */
        #tab-res, #tab-com, #tab-oth {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Validation Error Styling for Pills/Chips */
        .error {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            font-weight: 500;
        }
        
        .error.show {
            display: block;
        }
        
        /* Form Control Validation Styling */
        .form-control.is-invalid,
        .form-select.is-invalid,
        textarea.form-control.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        
        .form-control.is-valid,
        .form-select.is-valid,
        textarea.form-control.is-valid {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
        }
        
        .form-control:focus.is-invalid,
        .form-select:focus.is-invalid,
        textarea.form-control:focus.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        
        .form-control:focus.is-valid,
        .form-select:focus.is-valid,
        textarea.form-control:focus.is-valid {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
        }
        
        /* Error styling for pill containers */
        #ownerTypeContainer.has-error,
        #propertyTypeContainer.has-error,
        #resTypeContainer.has-error,
        #comTypeContainer.has-error,
        #othLookingContainer.has-error,
        #resFurnishContainer.has-error,
        #comFurnishContainer.has-error,
        #resSizeContainer.has-error {
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 8px;
            background-color: rgba(220, 53, 69, 0.05);
        }
        
        #ownerTypeContainer.has-error .top-pill,
        #propertyTypeContainer.has-error .top-pill,
        #resTypeContainer.has-error .top-pill,
        #comTypeContainer.has-error .top-pill,
        #othLookingContainer.has-error .top-pill,
        #resFurnishContainer.has-error .chip,
        #comFurnishContainer.has-error .chip,
        #resSizeContainer.has-error .chip {
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        /* SweetAlert Custom Styling */
        .swal2-popup {
            border-radius: 16px !important;
            padding: 2rem !important;
        }
        
        .swal2-title {
            font-size: 1.5rem !important;
            font-weight: 600 !important;
            color: #1a1a1a !important;
            margin-bottom: 1rem !important;
        }
        
        .swal2-html-container {
            text-align: left !important;
            padding: 0.5rem 0 !important;
            margin: 1rem 0 !important;
        }
        
        .swal2-icon.swal2-warning {
            border-color: #ffc107 !important;
            color: #ffc107 !important;
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

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit #{{ $booking->id }}</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Edit Booking #{{ $booking->id }}</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :fallback="route('admin.bookings.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                </div>
            </div>
            
            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="bookingEditTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="booking-tab" data-bs-toggle="tab" data-bs-target="#booking-pane" type="button" role="tab" aria-controls="booking-pane" aria-selected="true">
                                <i class="ri-file-list-3-line me-1"></i> Booking Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tour-tab" data-bs-toggle="tab" data-bs-target="#tour-pane" type="button" role="tab" aria-controls="tour-pane" aria-selected="false">
                                <i class="ri-map-pin-line me-1"></i> Tour Details
                                @if($tour ?? null)
                                    <span class="badge bg-success ms-1">Linked</span>
                                @else
                                    <span class="badge bg-warning ms-1">Not Linked</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-pane" type="button" role="tab" aria-controls="seo-pane" aria-selected="false">
                                <i class="ri-search-eye-line me-1"></i> SEO
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="json-tab" data-bs-toggle="tab" data-bs-target="#json-pane" type="button" role="tab" aria-controls="json-pane" aria-selected="false">
                                <i class="ri-code-s-slash-line me-1"></i> JSON
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body pt-0">
                    <div class="tab-content" id="bookingEditTabsContent">

                        <!-- Booking Tab -->
                        <div class="tab-pane fade show active" id="booking-pane" role="tabpanel" aria-labelledby="booking-tab" tabindex="0">
                            {{-- Display Validation Errors --}}
                            @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h5 class="alert-heading"><i class="ri-error-warning-line me-2"></i>Validation Errors</h5>
                                <hr>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            @endif
                            <!-- Booking Form Partial -->
                            <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')
                                
                                @include('admin.bookings.partials.ajax-form-fields')

                                <!-- Submit Buttons -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('admin.bookings.index') }}" class="btn btn-soft-secondary"><i class="ri-close-line me-1"></i> Cancel</a>
                                            <button class="btn btn-primary" type="submit"><i class="ri-save-line me-1"></i> Update Booking</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tour Tab -->
                        <div class="tab-pane fade" id="tour-pane" role="tabpanel" aria-labelledby="tour-tab" tabindex="0">
                            @if($tour ?? null)
                                @include('admin.bookings.partials.tour-edit-form')
                            @else
                                @include('admin.bookings.partials.tour-create-form')
                            @endif
                        </div>

                        <!-- SEO Tab -->
                        <div class="tab-pane fade" id="seo-pane" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">
                            @include('admin.bookings.partials.seo-form')
                        </div>

                        <!-- JSON Tab -->
                        <div class="tab-pane fade" id="json-pane" role="tabpanel" aria-labelledby="json-tab" tabindex="0">
                            <div class="mt-3">
                                <h5>Booking JSON Data</h5>
                                <pre class="bg-light p-3 rounded border" style="font-size: 13px; max-height: 90%; overflow: auto;">{!! is_array($tour->final_json) ? json_encode($tour->final_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $tour->final_json !!}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- script section  -->
@section('script')
    @vite(['resources/js/pages/bookings-edit.js','resources/js/pages/edit-booking-tour.js'])
    <script>
        // Pass data to JavaScript
        window.bookingData = {
            id: {{ $booking->id }},
            @if($tour ?? null)
            tourId: {{ $tour->id }},
            hasTour: true
            @else
            tourId: null,
            hasTour: false
            @endif
        };

        // Store old values for restoration after main JS loads (for validation errors)
        window.bookingOldValues = {
            owner_type: '{{ old("owner_type", $booking->owner_type) }}',
            main_property_type: '{{ old("main_property_type", $booking->propertyType->name ?? "") }}',
            property_sub_type_id: '{{ old("property_sub_type_id", $booking->property_sub_type_id) }}',
            furniture_type: '{{ old("furniture_type", $booking->furniture_type) }}',
            bhk_id: '{{ old("bhk_id", $booking->bhk_id) }}',
            state_id: '{{ old("state_id", $booking->state_id) }}',
            city_id: '{{ old("city_id", $booking->city_id) }}',
            different_billing_name: '{{ old("different_billing_name", ($booking->firm_name || $booking->gst_no) ? "on" : "") }}',
            has_old_data: {{ $errors->any() ? 'true' : 'false' }}
        };
        
        // Flag to prevent clearing fields during restoration
        window.isRestoringOldValues = false;

        // Restore old values after validation errors
        @if($errors->any() && (old('owner_type') || old('main_property_type')))
        document.addEventListener('DOMContentLoaded', function() {
            // Function to wait for element to exist
            function waitForElement(selector, callback, maxWait = 5000) {
                const startTime = Date.now();
                const checkExist = setInterval(function() {
                    const element = document.querySelector(selector);
                    if (element) {
                        clearInterval(checkExist);
                        callback(element);
                    } else if (Date.now() - startTime > maxWait) {
                        clearInterval(checkExist);
                    }
                }, 100);
            }

            // Set flag to prevent clearing fields during restoration
            window.isRestoringOldValues = true;

            // Step 1: Restore Owner Type first
            if (window.bookingOldValues.owner_type) {
                waitForElement(`[data-group="ownerType"][data-value="${window.bookingOldValues.owner_type}"]`, function(ownerPill) {
                    ownerPill.click();
                    
                    // Step 2: Restore Property Type after Owner Type
                    setTimeout(function() {
                        if (window.bookingOldValues.main_property_type) {
                            const propertyTab = document.querySelector(`#propertyTypeContainer [data-value="${window.bookingOldValues.main_property_type}"]`);
                            if (propertyTab) {
                                propertyTab.click();
                                
                                // Step 3: Restore other fields after Property Type
                                setTimeout(function() {
                                    // Restore Property Sub Type (for all property types)
                                    if (window.bookingOldValues.property_sub_type_id) {
                                        // Try residential first
                                        let subTypeChip = document.querySelector(`[data-group="resType"][data-value="${window.bookingOldValues.property_sub_type_id}"]`);
                                        // If not found, try commercial
                                        if (!subTypeChip) {
                                            subTypeChip = document.querySelector(`[data-group="comType"][data-value="${window.bookingOldValues.property_sub_type_id}"]`);
                                        }
                                        // If not found, try "other"
                                        if (!subTypeChip) {
                                            subTypeChip = document.querySelector(`[data-group="othLooking"][data-value="${window.bookingOldValues.property_sub_type_id}"]`);
                                        }
                                        if (subTypeChip) {
                                            subTypeChip.click();
                                        }
                                    }
                                    
                                    // Restore Furniture Type (for Residential and Commercial)
                                    // Need longer delay for Commercial tab to be fully visible
                                    if (window.bookingOldValues.furniture_type) {
                                        setTimeout(function() {
                                            // Determine correct group based on property type
                                            let furnitureGroup = 'resFurnish';
                                            if (window.bookingOldValues.main_property_type === 'Commercial') {
                                                furnitureGroup = 'comFurnish';
                                            }
                                            
                                            // Find and click the furniture chip
                                            const furnitureChip = document.querySelector(`[data-group="${furnitureGroup}"][data-value="${window.bookingOldValues.furniture_type}"]`);
                                            if (furnitureChip) {
                                                furnitureChip.click();
                                            } else {
                                                // Fallback: try the other group
                                                const fallbackGroup = furnitureGroup === 'resFurnish' ? 'comFurnish' : 'resFurnish';
                                                const fallbackChip = document.querySelector(`[data-group="${fallbackGroup}"][data-value="${window.bookingOldValues.furniture_type}"]`);
                                                if (fallbackChip) {
                                                    fallbackChip.click();
                                                }
                                            }
                                        }, 600);
                                    }
                                    
                                    // Restore BHK Size (for Residential only)
                                    if (window.bookingOldValues.bhk_id) {
                                        setTimeout(function() {
                                            const bhkChip = document.querySelector(`[data-group="resSize"][data-value="${window.bookingOldValues.bhk_id}"]`);
                                            if (bhkChip) {
                                                bhkChip.click();
                                            }
                                        }, 600);
                                    }
                                }, 500);
                            }
                        }
                    }, 300);
                });
            }

            // Restore State and City independently
            setTimeout(function() {
                if (window.bookingOldValues.state_id) {
                    const stateSelect = document.getElementById('state_id');
                    if (stateSelect) {
                        stateSelect.value = window.bookingOldValues.state_id;
                        stateSelect.dispatchEvent(new Event('change'));
                        
                        // Restore city after state cities are loaded
                        if (window.bookingOldValues.city_id) {
                            setTimeout(function() {
                                const citySelect = document.getElementById('city_id');
                                if (citySelect) {
                                    citySelect.value = window.bookingOldValues.city_id;
                                }
                            }, 600);
                        }
                    }
                }
            }, 1000);

            // Restore billing checkbox independently
            setTimeout(function() {
                if (window.bookingOldValues.different_billing_name) {
                    const checkbox = document.getElementById('differentBillingName');
                    if (checkbox && !checkbox.checked) {
                        checkbox.checked = true;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                }
                
                // Clear restoration flag after everything is done
                setTimeout(function() {
                    window.isRestoringOldValues = false;
                }, 500);
            }, 1000);
        });
        @endif

        // Auto-scroll to validation errors if present
        @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            const alertElement = document.querySelector('.alert-danger');
            if (alertElement) {
                alertElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Highlight fields with errors
                const errorFields = @json($errors->keys());
                errorFields.forEach(function(fieldName) {
                    // Try to find the field by name
                    let field = document.querySelector(`[name="${fieldName}"]`);
                    if (field) {
                        field.classList.add('is-invalid');
                        
                        // For select elements, also add error class to parent
                        if (field.tagName === 'SELECT') {
                            field.closest('.mb-1, .mb-3')?.classList.add('has-error');
                        }
                    }
                    
                    // Special handling for hidden pill fields
                    if (fieldName === 'owner_type') {
                        document.getElementById('err-owner')?.classList.remove('hidden');
                    }
                    if (fieldName === 'main_property_type') {
                        document.getElementById('err-tab')?.classList.remove('hidden');
                    }
                    if (fieldName === 'property_sub_type_id') {
                        document.getElementById('err-subtype')?.classList.remove('hidden');
                    }
                    if (fieldName === 'furniture_type') {
                        document.getElementById('err-furnish')?.classList.remove('hidden');
                    }
                    if (fieldName === 'bhk_id') {
                        document.getElementById('err-bhk')?.classList.remove('hidden');
                    }
                });
                
                // Show SweetAlert for better visibility
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Failed',
                    html: '<div style="text-align: left;"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>',
                    confirmButtonColor: '#dc3545',
                    width: '600px'
                });
            }
        });
        @endif
    </script>
@endsection