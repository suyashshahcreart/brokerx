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
    {{-- New theme uses global fonts from base layout; keep icon font for existing <i class="fa ..."> usage --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('proppik/assets/css/setup_page.css') }}">

@endsection

@section('content')
    
    <section class="py-5 bg-primary text-white mt-5">
        <div class="container pt-5 pb-2">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <p class="text-uppercase fw-bold small mb-2">Setup</p>
                    <h1 class="display-5 fw-bold mb-3">Setup Your Virtual Tour</h1>
                    <p class="lead mb-0">Share your details and property info to schedule your PROP PIK virtual tour.</p>
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

    <div class="page bg-setup-form py-5">
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
                            <div id="contactActionButtons" class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-4 {{ $isLoggedIn ? '' : 'hidden' }}">
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

    <!-- Testimonials Section (New Theme - matches Home page) -->
    <section id="testimonials" class="testimonials-section py-5">
            <div class="container">
            <div class="row align-items-start">
                <!-- Left Side: Static Content (40%) -->
                <div class="col-lg-5 mb-5 mb-lg-0">
                    <div class="about-text-content">
                        <div class="about-headline d-flex align-items-center gap-3 mb-4">
                            <div class="about-headline-line"></div>
                            <span class="about-headline-text small text-uppercase fw-bold">PROP PIK Testimonials</span>
                        </div>
                        <h2 class="about-main-title mb-4">
                            <span>What Our</span>
                            <span class="about-title-highlight">Clients Say</span>
                        </h2>
                        <p class="about-description mb-4">
                            See what our clients have to say about their experience with PROP PIK. We transcend expectations, creating bespoke virtual tour solutions for visionaries.
                        </p>
                    </div>

                    <!-- Statistics & Visual Element -->
                    <div class="testimonials-stats-box mt-5">
                        <div class="row g-4">
                            <div class="col-6">
                                <div class="stat-item text-center">
                                    <div class="stat-number">500+</div>
                                    <div class="stat-label">Happy Clients</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item text-center">
                                    <div class="stat-number">98%</div>
                                    <div class="stat-label">Satisfaction Rate</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item text-center">
                                    <div class="stat-number">1000+</div>
                                    <div class="stat-label">Virtual Tours</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item text-center">
                                    <div class="stat-number">4.9★</div>
                                    <div class="stat-label">Average Rating</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Scrolling Testimonials (60%) -->
                <div class="col-lg-7">
                    <div class="testimonials-scrolling-viewport">
                        <div class="row g-4">
                            <!-- Left Scrolling Column (Scrolls UP) -->
                            <div class="col-md-6 testimonials-scrolling-column testimonials-scroll-up d-none d-md-block">
                                <div class="testimonials-scroll-content">
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">PROP PIK has been responsive and professional throughout the entire virtual tour creation process. They supported us with our real estate listings and we are excited to see where it can take our business!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Rajesh Kumar" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Rajesh Kumar</h4>
                                                <p class="small text-muted mb-0">Real Estate Agent</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">I've been working with PROP PIK for a while now, and they're an absolute gem. Their platform is incredibly creative and technically sound. The virtual tours we create are stunning!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Priya Mehta" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Priya Mehta</h4>
                                                <p class="small text-muted mb-0">Architect, Design Studio</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">Highly talented platform with extensive features that has vastly improved our property showcases this year. We have opted for the monthly subscription and would recommend to any real estate company.</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Anjali Sharma" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Anjali Sharma</h4>
                                                <p class="small text-muted mb-0">CEO, Property Solutions</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Duplicate set for seamless loop -->
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">PROP PIK has been responsive and professional throughout the entire virtual tour creation process. They supported us with our real estate listings and we are excited to see where it can take our business!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Rajesh Kumar" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Rajesh Kumar</h4>
                                                <p class="small text-muted mb-0">Real Estate Agent</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Scrolling Column (Scrolls DOWN) -->
                            <div class="col-md-6 testimonials-scrolling-column testimonials-scroll-down">
                                <div class="testimonials-scroll-content">
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">Partnering with PROP PIK has reduced our marketing costs by 40%, boosted property viewings and client engagement, and helped us open doors to new markets!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Rohit Patel" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Rohit Patel</h4>
                                                <p class="small text-muted mb-0">CEO, Luxury Homes</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">Love the personal account relationship we have and we are made to feel like the best customer. The enthusiasm, cheerfulness, and speed of the team is awesome and the communication is always incredible!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Kavita Singh" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Kavita Singh</h4>
                                                <p class="small text-muted mb-0">Head of Marketing, Hotel Group</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">It's so refreshing to have a platform that breaks the stereotypical approach and works with their clients in a collaborative way. PROP PIK truly understands our needs.</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Vikram Desai" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Vikram Desai</h4>
                                                <p class="small text-muted mb-0">Creative Director, Art Gallery</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Duplicate set for seamless loop -->
                                    <div class="testimonial-card-scroll mb-4">
                                        <svg class="testimonial-quote-icon-scroll mb-3" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.995 3.638-3.995 5.849h3.983v10h-9.983z" />
                                        </svg>
                                        <p class="testimonial-text-scroll mb-4">Partnering with PROP PIK has reduced our marketing costs by 40%, boosted property viewings and client engagement, and helped us open doors to new markets!</p>
                                        <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                            <img src="{{ asset('proppik/assets/images/testimonial.png') }}" alt="Rohit Patel" class="testimonial-avatar-scroll rounded-circle" width="50" height="50">
                                            <div>
                                                <h4 class="h6 mb-0 fw-bold">Rohit Patel</h4>
                                                <p class="small text-muted mb-0">CEO, Luxury Homes</p>
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
    </section>

   
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
