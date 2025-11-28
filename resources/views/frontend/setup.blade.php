@extends('frontend.layouts.base', ['title' => 'Setup Virtual Tour - PROP PIK'])

@php
    $isLoggedIn = auth()->check();
    $authUser = auth()->user();
    $authUserName = $isLoggedIn ? trim(($authUser->firstname ?? '') . ' ' . ($authUser->lastname ?? '')) : null;
    $setupUserPayload = $isLoggedIn ? [
        'name' => $authUserName,
        'mobile' => $authUser->mobile ?? null,
        'email' => $authUser->email ?? null,
    ] : null;

    $stepNumbers = [
        'contact' => null,
        'property' => null,
        'address' => null,
        'verify' => null,
        'payment' => null,
    ];
    $currentStepNumber = 1;
    if (!$isLoggedIn) {
        $stepNumbers['contact'] = $currentStepNumber++;
    }
    $stepNumbers['property'] = $currentStepNumber++;
    $stepNumbers['address'] = $currentStepNumber++;
    $stepNumbers['verify'] = $currentStepNumber++;
    $stepNumbers['payment'] = $currentStepNumber++;

    $initialStepKey = !$isLoggedIn ? 'contact' : 'property';
    $initialStepNumber = $stepNumbers[$initialStepKey] ?? $stepNumbers['property'];
@endphp

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ config('app.url') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400..800&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
    <style>
        .form-readonly {
            position: relative;
        }
        .form-readonly .top-pill,
        .form-readonly .chip,
        .form-readonly textarea,
        .form-readonly input {
            pointer-events: none;
        }
        .form-readonly .top-pill,
        .form-readonly .chip {
            opacity: 0.6;
        }
        
        /* SweetAlert Custom Styling */
        .swal2-popup {
            border-radius: 16px !important;
            padding: 2rem !important;
            font-family: 'Urbanist', sans-serif !important;
        }
        
        .swal2-title {
            font-size: 1.5rem !important;
            font-weight: 600 !important;
            color: #1a1a1a !important;
            margin-bottom: 1rem !important;
        }
        
        .swal2-content {
            font-size: 0.95rem !important;
            color: #555 !important;
            line-height: 1.6 !important;
        }
        
        .swal2-icon.swal2-error {
            border-color: #dc3545 !important;
            color: #dc3545 !important;
            margin: 1.5rem auto 1rem !important;
        }
        
        .swal2-icon.swal2-error [class^=swal2-x-line] {
            background-color: #dc3545 !important;
        }
        
        .swal2-icon.swal2-warning {
            border-color: #ffc107 !important;
            color: #ffc107 !important;
        }
        
        .swal2-icon.swal2-info {
            border-color: #0dcaf0 !important;
            color: #0dcaf0 !important;
        }
        
        .swal2-confirm {
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
        
        .swal2-html-container {
            text-align: left !important;
            padding: 0.5rem 0 !important;
            margin: 1rem 0 !important;
        }
        
        .swal2-html-container ul {
            list-style: none !important;
            padding-left: 0 !important;
            margin: 0 !important;
        }
        
        .swal2-html-container li,
        .swal2-html-container div {
            padding: 0.4rem 0 !important;
            color: #333 !important;
            font-size: 0.95rem !important;
            line-height: 1.8 !important;
        }
        
        .swal2-html-container div {
            padding-left: 0.5rem !important;
        }
        
        /* Form Validation Error Styling */
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
        #propertyTypeContainer.has-error {
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 8px;
            background-color: rgba(220, 53, 69, 0.05);
        }
        
        #ownerTypeContainer.has-error .top-pill,
        #propertyTypeContainer.has-error .top-pill {
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        /* Error styling for chip and pill containers */
        #resTypeContainer.has-error,
        #comTypeContainer.has-error,
        #resFurnishContainer.has-error,
        #comFurnishContainer.has-error,
        #resSizeContainer.has-error,
        #othLookingContainer.has-error {
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 8px;
            background-color: rgba(220, 53, 69, 0.05);
        }
        
        #resTypeContainer.has-error .top-pill,
        #comTypeContainer.has-error .top-pill,
        #resFurnishContainer.has-error .chip,
        #comFurnishContainer.has-error .chip,
        #resSizeContainer.has-error .chip,
        #othLookingContainer.has-error .top-pill {
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
    </style>
@endsection

@section('content')
 <section class="page-header section-padding-bottom-b section-padding-top-t page-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="wow page-title" data-splitting data-delay="100"> Setup Your Virtual Tour</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="preloader-bg"></div>
    <div id="preloader">
        <div id="preloader-status">
            <div class="preloader-position loader"> <span></span> </div>
        </div>
    </div>

    <div class="page bg-light section-padding-bottom section-padding-top">
        <div class="panel container">
            <!-- TOP PROGRESS -->
            <div class="prog-wrap">
                <div class="steps-bar">
                    <div class="progress-top"><i id="progressBar"></i></div>
                </div>
                <div class="step-row">
                    <div class="step-buttons">
                        @if($stepNumbers['contact'])
                            <button class="step-btn {{ $initialStepNumber === $stepNumbers['contact'] ? 'active' : '' }}" data-step="{{ $stepNumbers['contact'] }}" id="btn-step-contact">Contact</button>
                        @endif
                        <button class="step-btn {{ $initialStepNumber === $stepNumbers['property'] ? 'active' : '' }}" data-step="{{ $stepNumbers['property'] }}" id="btn-step-property">Property</button>
                        <button class="step-btn" data-step="{{ $stepNumbers['address'] }}" id="btn-step-address">Address</button>
                        <button class="step-btn" data-step="{{ $stepNumbers['verify'] }}" id="btn-step-verify">Verify</button>
                        <button class="step-btn" data-step="{{ $stepNumbers['payment'] }}" id="btn-step-payment">Payment</button>
                    </div>
                </div>
            </div>

            <!-- MAIN CONTENT -->
            <form id="setupForm" method="POST" action="{{ route('frontend.setup.store') }}">
                @csrf
                <div class="content">
                    @if($stepNumbers['contact'])
                    <!-- STEP: CONTACT + OTP -->
                    <div id="step-{{ $stepNumbers['contact'] }}" class="step-pane {{ $initialStepNumber === $stepNumbers['contact'] ? '' : 'hidden' }}">
                        <h2 class="app-title">Contact details & verification</h2>
                        <div class="muted-small mb-3">Enter your name and phone. We'll send an OTP to verify.</div>
                        <div class="card p-3 mb-3" style="border-radius:12px;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full name <span class="text-danger">*</span></label>
                                    <input id="inputName" name="name" class="form-control" placeholder="e.g., John Doe" value="{{ $isLoggedIn ? $authUserName : request('prefillName') }}" />
                                    <div id="err-name" class="error">Name is required.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone number <span class="text-danger">*</span></label>
                                    <input id="inputPhone" name="phone" class="form-control" placeholder="10-digit mobile" maxlength="10" value="{{ $isLoggedIn ? ($authUser->mobile ?? '') : request('prefillPhone') }}" />
                                    <div id="err-phone" class="error">Enter a valid 10-digit mobile number.</div>
                                </div>
                            </div>
                            <div class="mt-3 d-flex flex-column flex-sm-row gap-2 align-items-center">
                                <button type="button" class="btn btn-primary" id="sendOtpBtn">Send OTP</button>
                                <div id="otpSentBadge" class="muted-small text-success hidden">OTP sent</div>
                            </div>
                            <div id="otpRow" class="mt-3 hidden">
                                
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <input id="inputOtp" class="form-control" maxlength="6" placeholder="Enter 6-digit OTP" />
                                        <div id="err-otp" class="error">Enter the correct OTP.</div>
                                    </div>
                                    <div class="col-12 col-sm-auto text-center text-sm-start">
                                        <button type="button" class="btn btn-primary" id="verifyOtpBtn">Verify OTP</button>
                                    </div>
                                    <div id="otpInfoAlert" class="alert alert-info mb-3" role="alert">
                                        <i class="fa-solid fa-circle-info me-2"></i>
                                        <span id="otpInfoMessage">
                                            <strong>OTP Sent!</strong> Your verification code has been delivered to your Phone and WhatsApp. Enter the OTP to continue.
                                        </span>
                                    </div>
                                    <div id="demoOtp" class="muted-small text-muted hidden mt-2 mb-2">[demo OTP: <strong id="demoOtpCode"></strong>]</div>
                                    <div class="col-12 muted-small">Didn't receive? <a href="#" class="text-primary fw-semibold" id="resendOtp">Resend</a></div>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-4">
                                <button type="button" class="btn btn-outline-secondary me-2" id="skipContact">Clear</button>
                                <button type="button" class="btn btn-primary" id="toStep2" disabled>Proceed to Property</button>
                            </div>
                        </div>
                    </div>

                    @endif

                    <!-- STEP: PROPERTY DETAILS -->
                    <div id="step-{{ $stepNumbers['property'] }}" class="step-pane {{ $initialStepNumber === $stepNumbers['property'] ? '' : 'hidden' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h2 class="app-title">Property details</h2>
                                <div class="muted-small">All selections below are required to continue.</div>
                            </div>
                        </div>
                         <div id="propertyCard" class="card p-3 mb-3" style="border-radius:12px; "> {{--max-height:62vh; overflow:auto; --}}
                            <input type="hidden" id="choice_ownerType" name="owner_type">
                            <input type="hidden" id="choice_resType" name="residential_property_type">
                            <input type="hidden" id="choice_resFurnish" name="residential_furnish">
                            <input type="hidden" id="choice_resSize" name="residential_size">
                            <input type="hidden" id="choice_comType" name="commercial_property_type">
                            <input type="hidden" id="choice_comFurnish" name="commercial_furnish">
                            <input type="hidden" id="choice_othLooking" name="other_looking">
                            <input type="hidden" id="mainPropertyType" name="main_property_type" value="Residential">
                            <!-- Draft booking id for step-by-step persistence -->
                            <input type="hidden" id="bookingId" name="booking_id" value="">

                            <div id="propertyReadOnlyNotice" class="alert alert-info d-none mb-3">
                                This booking is already paid, so property details are locked.
                            </div>

                            <div class="mb-3">
                                <div class="section-title">Owner Type <span class="text-danger">*</span></div>
                                <div class="d-flex gap-2" id="ownerTypeContainer">
                                    <div class="top-pill" data-group="ownerType" data-value="Owner" onclick="topPillClick(this)">Owner</div>
                                    <div class="top-pill" data-group="ownerType" data-value="Broker" onclick="topPillClick(this)">Broker</div>
                                </div>
                                <div id="err-ownerType" class="error">Owner Type is required.</div>
                            </div>
                            
                            <!-- PROPERTY TYPE TAB -->
                            <div class="mb-3">
                                <div class="section-title">Property Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2" id="propertyTypeContainer">
                                    @foreach($propTypes as $type)
                                        @php
                                            $map = [
                                                'Residential' => 'res',
                                                'Commercial'  => 'com',
                                                'Other'       => 'oth',
                                            ];
                                            $tabKey = $map[$type->name] ?? strtolower(substr(preg_replace('/\s+/', '', $type->name), 0, 3));
                                        @endphp
                                        <div
                                            class="top-pill"
                                            id="pill{{ \Illuminate\Support\Str::studly($type->name) }}"
                                            data-value="{{ $type->name }}"
                                            data-type-id="{{ $type->id ?? '' }}"
                                            onclick="handlePropertyTabChange('{{ $tabKey }}')"
                                        >
                                            @if(!empty($type->icon))
                                                <i class="fa {{ $type->icon }}"></i>
                                            @endif
                                            {{ $type->name }}
                                        </div>
                                    @endforeach
                                </div>
                                <div id="err-propertyType" class="error">Property Type is required.</div>
                            </div>

                            <!-- PROPERTY SUB TYPE TAB -->
                            <div id="tab-res" style="display:none;">
                                <div class="section-title">Property Sub Type<span class="text-danger">*</span></div>
                                <div class="d-wrap gap-2 mb-3" id="resTypeContainer">
                                    @php
                                        $residentialType = $propTypes->firstWhere('name', 'Residential');
                                        $residentialSubTypes = $residentialType ? $residentialType->subTypes : [];
                                    @endphp
                                    @forelse($residentialSubTypes as $subType)
                                        <div class="top-pill m-1" data-group="resType" data-value="{{ $subType->name }}" onclick="selectCard(this)">
                                            @if($subType->icon)
                                                <i class="fa {{ $subType->icon }}"></i>
                                            @endif
                                            {{ $subType->name }}
                                        </div>
                                    @empty
                                        <div class="text-muted">No residential types available</div>
                                    @endforelse
                                </div>
                                <div id="err-resType" class="error">Property Sub Type is required.</div>
                                <div class="section-title">Furnish Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3" id="resFurnishContainer">
                                    <div class="chip" data-group="resFurnish" data-value="Fully Furnished" onclick="selectChip(this)"><i class="bi bi-sofa"></i> Fully Furnished</div>
                                    <div class="chip" data-group="resFurnish" data-value="Semi Furnished" onclick="selectChip(this)"><i class="bi bi-lamp"></i> Semi Furnished</div>
                                    <div class="chip" data-group="resFurnish" data-value="Unfurnished" onclick="selectChip(this)"><i class="bi bi-door-closed"></i> Unfurnished</div>
                                </div>
                                <div id="err-resFurnish" class="error">Furnish Type is required.</div>
                                <div class="section-title">Size (BHK / RK) <span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3" id="resSizeContainer">
                                    @forelse($bhk as $bhkItem)
                                    <div class="chip" data-group="resSize" data-value="{{ $bhkItem->id }}" onclick="selectChip(this)">{{ $bhkItem->name }}</div>
                                    @empty
                                        <div class="chip" data-group="resSize" data-value="null" onclick="selectChip(this)">Not Found</div>
                                    @endforelse
                                </div>
                                <div id="err-resSize" class="error">Size (BHK / RK) is required.</div>
                                <div class="mb-3">
                                    <div class="section-title">Super Built-up Area (sq. ft.) <span class="text-danger">*</span></div>
                                    <input id="resArea" name="residential_area" class="form-control" type="number" min="1" placeholder="e.g., 1200" />
                                    <div id="err-resArea" class="error">Area is required.</div>
                                </div>
                            </div>

                            <!-- COMMERCIAL TAB -->
                            <div id="tab-com" style="display:none;">
                                <div class="section-title">Property Sub Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3" id="comTypeContainer">
                                    @php
                                        $commercialType = $propTypes->firstWhere('name', 'Commercial');
                                        $commercialSubTypes = $commercialType ? $commercialType->subTypes : [];
                                    @endphp
                                    @forelse($commercialSubTypes as $subType)
                                        <div class="top-pill" data-group="comType" data-value="{{ $subType->name }}" onclick="selectCard(this)">
                                            @if($subType->icon)
                                                <i class="fa {{ $subType->icon }}"></i>
                                            @endif
                                            {{ $subType->name }}
                                        </div>
                                    @empty
                                        <div class="text-muted">No commercial types available</div>
                                    @endforelse
                                </div>
                                <div id="err-comType" class="error">Property Sub Type is required.</div>
                                <!-- FURNATURE TABL -->
                                <div class="section-title">Furnish Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3" id="comFurnishContainer">
                                    <div class="chip" data-group="comFurnish" data-value="Fully Furnished" onclick="selectChip(this)">Fully Furnished</div>
                                    <div class="chip" data-group="comFurnish" data-value="Semi Furnished" onclick="selectChip(this)">Semi Furnished</div>
                                    <div class="chip" data-group="comFurnish" data-value="Unfurnished" onclick="selectChip(this)">Unfurnished</div>
                                </div>
                                <div id="err-comFurnish" class="error">Furnish Type is required.</div>
                                <div class="mb-3">
                                    <div class="section-title">Super Built-up Area (sq. ft.) <span class="text-danger">*</span></div>
                                    <input id="comArea" name="commercial_area" class="form-control" type="number" min="1" placeholder="e.g., 900" />
                                    <div id="err-comArea" class="error">Area is required.</div>
                                </div>
                            </div>

                            <!-- OTHER TAB -->
                            <div id="tab-oth" style="display:none;">
                                <div class="mb-3">
                                    <div style="font-weight:700; margin-bottom:8px">Select Option <span class="text-danger">*</span></div>
                                    <div class="d-flex flex-column flex-sm-row gap-2" id="othLookingContainer">
                                        @php
                                            $otherType = $propTypes->firstWhere('name', 'Other');
                                            $otherSubTypes = $otherType ? $otherType->subTypes : [];
                                        @endphp
                                        @forelse($otherSubTypes as $subType)
                                            <div class="top-pill" data-group="othLooking" data-value="{{ $subType->name }}" onclick="topPillClick(this)">
                                                @if($subType->icon)
                                                    <i class="fa {{ $subType->icon }}"></i>
                                                @endif
                                                {{ $subType->name }}
                                            </div>
                                        @empty
                                            <div class="text-muted">No other types available</div>
                                        @endforelse
                                    </div>
                                    <div id="err-othLooking" class="error">Select an option or enter Other option.</div>
                                </div>
                                <div class="mb-3">
                                    <div class="section-title">Other Option Details</div>
                                    <textarea id="othDesc" name="other_option_details" class="form-control" rows="3" placeholder="Enter other option details"></textarea>
                                    <div id="err-othDesc" class="error">Other option is required if none of the options are selected.</div>
                                </div>
                                <div class="mb-3">
                                    <div class="section-title">Super Built-up Area (sq. ft.)<span class="text-danger">*</span></div>
                                    <input id="othArea" name="other_area" class="form-control" type="number" min="1" />
                                    <div id="err-othArea" class="error">Area is required.</div>
                                </div>
                            </div>

                            
                            <!-- Different Billing Name Checkbox -->
                            <div class="mb-3 mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="differentBillingName" name="different_billing_name">
                                    <label class="form-check-label" for="differentBillingName">
                                        Use company billing details
                                    </label>
                                </div>
                            </div>

                            <!-- Firm Name and GST No (hidden by default) -->
                            <div id="billingDetailsRow" class="row" style="display:none;">
                                <div class="col-md-6">
                                    <div class="">
                                        <div class="section-title">Company Name <span class="text-danger">*</span></div>
                                        <input id="firmName" name="firm_name" class="form-control mb-0" type="text" placeholder="Enter Company Name" />
                                        <div id="err-firmName" class="error">Company Name is required.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="">
                                        <div class="section-title">GST No <span class="text-danger">*</span></div>
                                        <input id="gstNo" name="gst_no" class="form-control mb-0" type="text" placeholder="Enter GST number" />
                                        <div id="err-gstNo" class="error">GST No is required.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms and Conditions Checkbox -->
                            <div class="mb-3 mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agreeTerms" name="agree_terms" checked required>
                                    <label class="form-check-label" for="agreeTerms">
                                        I agree to PROP PIK 
                                        <a href="{{ route('frontend.terms') }}" target="_blank" class="text-primary fw-semibold">Terms and Conditions</a> AND 
                                        <a href="{{ route('frontend.refund-policy') }}" target="_blank" class="text-primary fw-semibold">Refund Policy</a> AND 
                                        <a href="{{ route('frontend.privacy-policy') }}" target="_blank" class="text-primary fw-semibold">Privacy Policy</a>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div id="err-agreeTerms" class="error">You must agree to the terms and conditions to continue.</div>
                                </div>
                            </div>

                                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between mt-3">
                                    @unless($isLoggedIn)
                                        <button type="button" class="btn btn-outline-secondary" id="backToContact">Back to Contact</button>
                                    @endunless
                                    <button type="button" class="btn btn-primary" id="toStepAddress">Proceed to Address</button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP: ADDRESS -->
                    <div id="step-{{ $stepNumbers['address'] }}" class="step-pane hidden">
                        <h2 class="app-title">Address</h2>
                        <div class="muted-small mb-2">Provide full address details (all required).</div>
                        <div id="addressCard" class="card p-3 mb-3" style="border-radius:12px;">
                            <div id="addressReadOnlyNotice" class="alert alert-info d-none mb-3">
                                This booking is already paid, so address details are locked.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">House / Office No. <span class="text-danger">*</span></label>
                                    <input id="addrHouse" name="house_number" class="form-control" placeholder="e.g., H-123 / 12A" />
                                    <div id="err-addrHouse" class="error">House/Office number is required.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Society / Building Name <span class="text-danger">*</span></label>
                                    <input id="addrBuilding" name="building_name" class="form-control" placeholder="Society / Building" />
                                    <div id="err-addrBuilding" class="error">Building/society name required.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pincode <span class="text-danger">*</span></label>
                                    <input id="addrPincode" name="pincode" class="form-control" maxlength="6" placeholder="e.g., 380009" />
                                    <div id="err-addrPincode" class="error">Valid 6-digit pincode required.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <select id="addrCity" name="city" class="form-select">

                                        {{-- <option value="">Select City</option> --}}
                                        <option value="Ahmedabad" selected>Ahmedabad</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Full address (street / area) <span class="text-danger">*</span></label>
                                    <textarea id="addrFull" name="full_address" class="form-control mb-0" rows="3" placeholder="Complete address..."></textarea>
                                    <div id="err-addrFull" class="error">Full address is required.</div>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between mt-3">
                                <button type="button" class="btn btn-outline-secondary" id="backToProp">Back to Property</button>
                                <button type="button" class="btn btn-primary" id="toStepVerify">Proceed to Verify</button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP: VERIFY -->
                    <div id="step-{{ $stepNumbers['verify'] }}" class="step-pane hidden">
                        <h2 class="app-title">Verify & Review</h2>
                        <div class="muted-small mb-3">Check all details. Use Edit buttons to go back and change if needed.</div>
                        <div class="card p-3 mb-3" style="border-radius:12px;">
                            <div id="summaryArea"></div>
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between mt-3">
                                <button type="button" class="btn btn-outline-secondary" id="backToAddress">Back to Address</button>
                                <button type="button" class="btn btn-success" id="toStepPayment">Proceed to Payment</button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP: PAYMENT -->
                    <div id="step-{{ $stepNumbers['payment'] }}" class="step-pane hidden">
                        <h2 class="app-title">Payment</h2>
                        <div class="muted-small mb-3">Secure checkout powered by Cashfree. You will be prompted with a modal to complete payment.</div>
                        <div class="card p-3 mb-3" style="border-radius:12px;">
                            <input type="hidden" name="payment_method" id="paymentMethodInput" value="cashfree">
                            <div class="mb-3">
                                <div class="section-title mb-1">Cashfree Payment</div>
                                <div class="muted-small">The modal will open automatically once the session is ready. You can retry anytime.</div>
                            </div>
                            <div class="summary-price-box" id="cashfreeStatusBox">
                                <div class="label">Payment Status</div>
                                <div class="amount text-warning" id="cashfreeStatusValue">Not started</div>
                                <div class="muted-small" id="cashfreeStatusMessage">Click Pay with Cashfree to continue.</div>
                            </div>
                            <div class="mt-3">
                                <div class="muted-small">Order ID: <span id="cashfreeOrderId">-</span></div>
                                <div class="muted-small">Amount: <span id="cashfreeAmountLabel">₹0</span></div>
                                <div class="muted-small">Reference: <span id="cashfreeReferenceId">-</span></div>
                                <div class="muted-small">Method: <span id="cashfreeMethod">-</span></div>
                            </div>
                            <div class="alert alert-warning mt-3" id="cashfreeAlert" style="display:none;" role="alert"></div>
                            <div class="text-center mt-3" id="cashfreeLoader" style="display:none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="muted-small mt-2">Connecting to Cashfree...</div>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
                                <button type="button" class="btn btn-primary flex-fill" id="cashfreePayBtn">Pay with Cashfree</button>
                                <button type="button" class="btn btn-outline-primary flex-fill" id="cashfreeStatusRefreshBtn">Refresh Status</button>
                                <button type="button" class="btn btn-outline-secondary flex-fill" id="backToVerify">Back to Verify</button>
                            </div>
                            <div class="muted-small mt-3 text-center text-muted">Powered by Cashfree Payments</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-body p-4 text-center">
                    <h5 class="mb-3">Payment successful!</h5>
                    <p class="muted-small">Your property virtual tour request has been received successfully. Our team will get in touch with you shortly.</p>
                    <button class="btn btn-primary mt-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonials -->
    <section id="testimonials" data-scroll-index="5" class="testimonials">
        <div class="background bg-img bg-fixed section-padding section-padding-top section-padding-bottom" data-overlay-dark="5" data-background="{{ asset('frontend/images/demo.jpg') }}">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-5 mb-30">
                        <h4 class="wow" data-splitting>Let's create a virtual tour of your property together.</h4>
                        <div class="btn-wrap mt-30 text-left wow fadeInUp" data-wow-delay=".6s">
                            <div class="btn-link"><a class="white" href="mailto:hello@proppik.com">hello@proppik.com</a><span class="btn-block color3 animation-bounce"></span></div>
                        </div>
                    </div>
                    <div class="col-md-5 offset-md-2">
                        <div class="testimonials-box">
                            <h5>What Are Clients Saying?</h5>
                            <div class="owl-carousel owl-theme">
                                <div class="item">
                                    <p>PROP PIK is a great way to showcase your property. It's easy to use and looks great.</p>
                                    <span class="quote"><img src="{{ asset('frontend/images/quot.png') }}" alt="" loading="lazy"></span>
                                    <div class="info">
                                        <div class="author-img"> <img src="{{ asset('frontend/images/team/1.jpg') }}" alt="" loading="lazy"></div>
                                        <div class="cont"><h6>Emily Brown</h6> <span>Customer</span></div>
                                    </div>
                                </div>
                                <div class="item">
                                    <p>PROP PIK is a great way to showcase your property. It's easy to use and looks great.</p>
                                    <span class="quote"><img src="{{ asset('frontend/images/quot.png') }}" alt="" loading="lazy"></span>
                                    <div class="info">
                                        <div class="author-img"> <img src="{{ asset('frontend/images/team/2.jpg') }}" alt="" loading="lazy"></div>
                                        <div class="cont"><h6>Jason White</h6> <span>Customer</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scrolling -->
    <div class="scrolling scrolling-ticker">
        <div class="wrapper">
            <div class="content">
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Capture</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Create</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Showcase</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Explore</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Scan</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Stitch</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Publish</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">View</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Experience</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Transform</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Present</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Share</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Visualize</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Engage</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Impress</span>
            </div>
            <div class="content">
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Capture</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Create</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Showcase</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Explore</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Scan</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Stitch</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Publish</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">View</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Experience</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Transform</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Present</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Share</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Visualize</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Engage</span>
                <span><img src="{{ asset('frontend/images/asterisk-icon.svg') }}" alt="" loading="lazy">Impress</span>
            </div>
        </div>
    </div>
@endsection

@section('script-bottom')
    <script src="{{ asset('frontend/js/plugins/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/jquery-migrate-3.5.0.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/YouTubePopUp.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/jquery.easing.1.3.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/jquery.isotope.v3.0.2.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/smooth-scroll.min.js') }}"></script>
    <script src="{{ asset('frontend/js/plugins/wow.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.SetupContext = {
            authenticated: @json($isLoggedIn),
            user: @json($setupUserPayload),
            steps: @json($stepNumbers),
            initialStep: @json($initialStepNumber),
        };
        window.SetupData = {
            types: @json($propTypes ?? []),
            states: @json($states ?? []),
            cities: @json($cities ?? [])
        };
        window.CashfreeConfig = {
            mode: "{{ config('cashfree.env') === 'production' ? 'production' : 'sandbox' }}"
        };
    </script>
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
    <script src="{{ asset('frontend/js/custom.js') }}"></script>
    <script src="{{ asset('frontend/js/setup.js') }}"></script>
@endsection
