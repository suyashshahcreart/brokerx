@extends('admin.layouts.vertical', ['title' => 'Create Booking', 'subTitle' => 'Property'])

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
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Create Booking</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.bookings.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Booking Details</h4>
                    <p class="text-muted mb-0">Fill in property and user details</p>
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
            <div class="card-body pt-0">
                
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

                {{-- Display Success Message --}}
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                
                <form method="POST" action="{{ route('admin.bookings.store') }}" class="needs-validation" novalidate>
                    @csrf
                    
                    <!-- Hidden Fields for Dynamic Data -->
                    <input type="hidden" id="choice_ownerType" name="owner_type" value="{{ old('owner_type') }}">
                    <input type="hidden" id="mainPropertyType" name="main_property_type" value="{{ old('main_property_type') }}">
                    
                    <!-- User Selection, Status, and Payment Status - Three Columns -->
                    <div class="row">
                        <div class="col-4">
                            <div class="mb-1">
                                <label class="form-label" for="user_id">Select Customer <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id" data-choices class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">Choose a customer...</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" @selected(old('user_id')==$u->id)>
                                            {{ $u->firstname }} {{ $u->lastname }} | {{ $u->mobile }}@if($u->email) | {{ $u->email }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">@error('user_id'){{ $message }}@else Please select a customer.@enderror</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-1">
                                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="">Select status...</option>
                                    @php $defaultStatus = old('status', 'confirmed'); @endphp
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" @selected($defaultStatus==$status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">@error('status'){{ $message }}@else Please select a status.@enderror</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-1">
                                <label class="form-label" for="payment_status">Payment Status <span class="text-danger">*</span></label>
                                <select name="payment_status" id="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                                    <option value="">Select payment status...</option>
                                    @php $defaultPaymentStatus = old('payment_status', 'paid'); @endphp
                                    @foreach($paymentStatuses as $ps)
                                        <option value="{{ $ps }}" @selected($defaultPaymentStatus==$ps)>{{ ucfirst($ps) }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">@error('payment_status'){{ $message }}@else Please select a payment status.@enderror</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- LEFT COLUMN: Property Details -->
                        <div class="col-lg-12">
                            <div class="card border bg-light-subtle mb-3">
                                <div class="card-header bg-primary-subtle border-primary">
                                    <h5 class="card-title mb-0"><i class="ri-building-line me-2"></i>Property Details</h5>
                                </div>
                                <div class="card-body">
                                    
                                    <div class="row">
                                        <div class="col-3">
                                            <!-- Owner Type -->
                                            <div class="mb-0">
                                                <div class="section-title m-0">Owner Type <span class="text-danger">*</span></div>
                                                <div class="d-flex gap" id="ownerTypeContainer">
                                                    <div class="top-pill" data-group="ownerType" data-value="Owner" onclick="topPillClick(this)">
                                                        <i class="ri-user-line me-1"></i> Owner
                                                    </div>
                                                    <div class="top-pill" data-group="ownerType" data-value="Broker" onclick="topPillClick(this)">
                                                        <i class="ri-briefcase-line me-1"></i> Broker
                                                    </div>
                                                    <div class="top-pill" data-group="ownerType" data-value="Other" onclick="topPillClick(this)">
                                                        <i class="ri-briefcase-line me-1"></i> Other
                                                    </div>
                                                </div>
                                                <div id="err-ownerType" class="error">Owner Type is required.</div>
                                            </div>
                                        </div>
                                        <div class="col-9">
                                            <!-- Property Type Tabs -->
                                            <div class="mb-1">
                                                <div class="section-title m-0">Property Type <span class="text-danger">*</span></div>
                                                <div class="d-flex gap mb-0" id="propertyTypeContainer">
                                                   
                                                    @foreach($propertyTypes as $pt)
                                                        <div
                                                            class="top-pill"
                                                            id="pill{{ \Illuminate\Support\Str::studly($pt->name) }}"
                                                            data-value="{{ $pt->name }}"
                                                            data-type-id="{{ $pt->id }}"
                                                            data-tab-connect="tab-{{ $pt->name }}"
                                                            onclick="handlePropertyTabChange(this)"
                                                        >
                                                            <i class="{{ $pt->icon }}"></i>
                                                            {{ $pt->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div id="err-propertyType" class="error">Property Type is required.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- PROPERTY SUB TYPE AND OTHER DETAILS TAB -->
                                    <div id="propertySubTypetab" class="hidden">
                                        <div class="row">
                                            <div class="col-6">
                                                <!-- Property Sub Type -->
                                                <div class="mb-1">
                                                    <div class="section-title mb-0">Property Sub Type <span class="text-danger">*</span></div>
                                                    @foreach($propertySubTypes as $typeId => $subTypes)
                                                        <div class="d-wrap mt-0" id="tab-{{collect($propertyTypes)->firstWhere('id', $typeId)->name}}">
                                                            @foreach ($subTypes as $subType)
                                                                <div class="top-pill" data-group="resType" data-value="{{ $subType->id }}" onclick="selectCard(this)">
                                                                            <i class="{{ $subType->icon }}"></i>
                                                                            {{ $subType->name }}
                                                                </div>
                                                             @endforeach
                                                         </div>
                                                    @endforeach
                                                    <div id="err-resType" class="error">Property Sub Type is required.</div>
                                                </div>
                                            </div>
                                            <div class="col-6" id="furnishRow">
                                                <!-- Furnish Type -->
                                                <div class="mb-1">
                                                    <div class="section-title mb-0">Furnish Type <span class="text-danger">*</span></div>
                                                    <div class="d-flex flex-wrap gap" id="resFurnishContainer">
                                                        <div class="chip" data-group="resFurnish" data-value="Furnished" onclick="selectChip(this)"><i class="ri-sofa-line"></i> Fully Furnished</div>
                                                        <div class="chip" data-group="resFurnish" data-value="Semi-Furnished" onclick="selectChip(this)"><i class="ri-lightbulb-line"></i> Semi Furnished</div>
                                                        <div class="chip" data-group="resFurnish" data-value="Unfurnished" onclick="selectChip(this)"><i class="ri-door-line"></i> Unfurnished</div>
                                                    </div>
                                                    <div id="err-resFurnish" class="error">Furnish Type is required.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12" id="sizeRow" >
                                                <!-- Size (BHK/RK) -->
                                                <div class="mb-1">
                                                    <div class="section-title mb-0">Size (BHK / RK) <span class="text-danger">*</span></div>
                                                    <div class="d-flex flex-wrap gap" id="resSizeContainer">
                                                        @foreach($bhks as $bhk)
                                                            <div class="chip" data-group="resSize" data-value="{{ $bhk->id }}" onclick="selectChip(this)">{{ $bhk->name }}</div>
                                                        @endforeach
                                                    </div>
                                                    <div id="err-resSize" class="error">Size (BHK / RK) is required.</div>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <!-- Area (Always Visible - Common Field) -->
                                            <div class="mb-1">
                                                <label class="form-label fw-semibold mb-0" for="area">Super Built-up Area (sq ft) <span class="text-danger">*</span></label>
                                                <input type="number" name="area" id="area" class="form-control @error('area') is-invalid @enderror" value="{{ old('area') }}" placeholder="e.g., 1200" required min="1">
                                                <div class="invalid-feedback">@error('area'){{ $message }}@else Please enter a valid area.@enderror</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <!-- Price, Dates, Status (Always Visible) -->
                                            <div class="mb-1">
                                                <label class="form-label fw-semibold mb-0" for="price">Price (₹) <span class="text-danger">*</span> <small class="text-muted">(Auto-calculated)</small></label>
                                                <input type="number" name="price" id="price" class="form-control bg-light @error('price') is-invalid @enderror" value="{{ old('price') }}" placeholder="Enter area to calculate" required min="0">
                                                <div class="invalid-feedback">@error('price'){{ $message }}@else Please enter a valid price.@enderror</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Use Company Billing Details Checkbox -->
                                    <div class="mb-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="differentBillingName" name="different_billing_name" {{ old('different_billing_name') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="differentBillingName">
                                                Use company billing details
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Company Billing Fields (Hidden by default) -->
                                    <div id="billingDetailsRow" class="{{ old('different_billing_name') ? '' : 'hidden' }}">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="m-0">
                                                    <div class="section-title m-0">Company Name <span class="text-danger">*</span></div>
                                                    <input type="text" name="firm_name" id="firmName" class="form-control @error('firm_name') is-invalid @enderror" placeholder="Enter Company Name" value="{{ old('firm_name') }}">
                                                    <div id="err-firmName" class="error @error('firm_name') @else hidden @enderror">@error('firm_name'){{ $message }}@else Company Name is required.@enderror</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="m-0">
                                                    <div class="section-title m-0">GST No <span class="text-danger">*</span></div>
                                                    <input type="text" name="gst_no" id="gstNo" class="form-control @error('gst_no') is-invalid @enderror" placeholder="Enter GST number" value="{{ old('gst_no') }}">
                                                    <div id="err-gstNo" class="error @error('gst_no') @else hidden @enderror">@error('gst_no'){{ $message }}@else GST No is required.@enderror</div>
                                                </div>
                                            </div>
                                        </div>
                                                
                                        
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Address Details -->
                        <div class="col-lg-12">
                            <div class="card border bg-light-subtle mb-3">
                                <div class="card-header bg-success-subtle border-success">
                                    <h5 class="card-title mb-0"><i class="ri-map-pin-line me-2"></i>Address Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4">
                                            <!-- House No -->
                                            <div class="mb-1">
                                                <label class="form-label fw-semibold mb-0" for="house_no">House / Office No <span class="text-danger">*</span></label>
                                                <input type="text" name="house_no" id="house_no" class="form-control @error('house_no') is-invalid @enderror" value="{{ old('house_no') }}" placeholder="e.g., H-123, 12A" required>
                                                <div class="invalid-feedback">@error('house_no'){{ $message }}@else House / Office No is required.@enderror</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <!-- Building -->
                                            <div class="mb-1">
                                                <label class="form-label fw-semibold mb-0" for="building">Society / Building Name <span class="text-danger">*</span></label>
                                                <input type="text" name="building" id="building" class="form-control @error('building') is-invalid @enderror" value="{{ old('building') }}" placeholder="Building or Tower name" required>
                                                <div class="invalid-feedback">@error('building'){{ $message }}@else Society / Building Name is required.@enderror</div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <!-- Society Name -->
                                            <div class="mb-1">
                                                <label class="form-label fw-semibold mb-0" for="society_name">Society / Complex Name</label>
                                                <input type="text" name="society_name" id="society_name" class="form-control" value="{{ old('society_name') }}" placeholder="Society or complex name">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <!-- Area / Locality -->
                                            <div class="mb-1">
                                                <label class="form-label fw-semibold mb-0" for="address_area">Area / Locality</label>
                                                <input type="text" name="address_area" id="address_area" class="form-control" value="{{ old('address_area') }}" placeholder="e.g., Vastrapur">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <!-- Landmark -->
                                            <div class="mb-1">
                                                <label class="form-label fw-semibold mb-0" for="landmark">Landmark</label>
                                                <input type="text" name="landmark" id="landmark" class="form-control" value="{{ old('landmark') }}" placeholder="Nearby landmark">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <!-- PIN Code -->
                                            <div class="mb-1">
                                                <label class="form-label fw-semibold mb-0" for="pin_code">PIN Code <span class="text-danger">*</span></label>
                                                <input type="text" name="pin_code" id="pin_code" class="form-control @error('pin_code') is-invalid @enderror" value="{{ old('pin_code') }}" placeholder="e.g., 380015" maxlength="6" required>
                                                <div class="invalid-feedback">@error('pin_code'){{ $message }}@else Valid 6-digit PIN Code is required.@enderror</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-4">
                                            <div class="row">
                                                <div class="col-12">
                                                    <!-- State -->
                                                    <div class="mb-1">
                                                        @php
                                                            // Determine default State and City when no old values are present
                                                            $defaultStateId = old('state_id');
                                                            $defaultCityId = old('city_id');
                                                            if (!$defaultStateId) {
                                                                $gujarat = collect($states ?? [])->first(function($st){
                                                                    return strcasecmp($st->name, 'Gujarat') === 0 || strcasecmp($st->name, 'Gujrat') === 0;
                                                                });
                                                                $defaultStateId = $gujarat->id ?? null;
                                                            }
                                                            if (!$defaultCityId) {
                                                                $defaultCityId = optional(collect($cities ?? [])->first(function($city){
                                                                    return strcasecmp($city->name, 'Ahmedabad') === 0;
                                                                }))->id;
                                                            }
                                                        @endphp
                                                        <label class="form-label fw-semibold mb-0" for="state_id">State</label>
                                                        <select name="state_id" id="state_id" class="form-select">
                                                            <option value="">Select state</option>
                                                            @foreach($states as $s)
                                                                <option value="{{ $s->id }}" @selected(($defaultStateId)==$s->id)>{{ $s->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <!-- City -->
                                                    <div class="mb-1">
                                                        <label class="form-label fw-semibold mb-0" for="city_id">City</label>
                                                        <select name="city_id" id="city_id" class="form-select">
                                                            <option value="">Select city</option>
                                                            @foreach($cities as $c)
                                                                <option value="{{ $c->id }}" data-state-id="{{ $c->state_id }}" @selected(($defaultCityId)==$c->id)>{{ $c->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>   
                                        </div>

                                        <div class="col-8">
                                            <div class="row">
                                                <div class="col-12">
                                                    <!-- Full Address -->
                                                    <div class="mb-0">
                                                        <label class="form-label fw-semibold mb-0" for="full_address">Full Address <span class="text-danger">*</span></label>
                                                        <textarea name="full_address" id="full_address" class="form-control @error('full_address') is-invalid @enderror" rows="4" placeholder="Complete address with street details..." required>{{ old('full_address') }}</textarea>
                                                        <div class="invalid-feedback">@error('full_address'){{ $message }}@else Full Address is required.@enderror</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    

                                            

                                            

                                            

                                            

                                            

                                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.bookings.index') }}" class="btn btn-soft-secondary"><i class="ri-close-line me-1"></i> Cancel</a>
                                <button class="btn btn-primary" type="submit"><i class="ri-check-line me-1"></i> Save Booking</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/js/pages/bookings-form.js'])
    
    <script>
        // Store old values for restoration after main JS loads
        window.bookingOldValues = {
            owner_type: '{{ old("owner_type") }}',
            main_property_type: '{{ old("main_property_type") }}',
            property_sub_type_id: '{{ old("property_sub_type_id") }}',
            furniture_type: '{{ old("furniture_type") }}',
            bhk_id: '{{ old("bhk_id") }}',
            state_id: '{{ $defaultStateId ?? '' }}',
            city_id: '{{ $defaultCityId ?? '' }}',
            different_billing_name: '{{ old("different_billing_name") }}',
            has_old_data: {{ old('owner_type') || old('main_property_type') ? 'true' : 'false' }}
        };
        
        // Flag to prevent clearing fields during restoration
        window.isRestoringOldValues = false;

        // Restore old values after main JS loads
        @if(old('owner_type') || old('main_property_type'))
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
                                            // Determine which group based on property type
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
