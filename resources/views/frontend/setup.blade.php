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
        'booking' => null,
        'property' => null,
        'address' => null,
        'verify' => null,
        'payment' => null,
    ];
    $currentStepNumber = 1;
    if (!$isLoggedIn) {
        $stepNumbers['contact'] = $currentStepNumber++;
    }
    if ($hasBookings ?? false) {
        $stepNumbers['booking'] = $currentStepNumber++;
    }
    $stepNumbers['property'] = $currentStepNumber++;
    $stepNumbers['address'] = $currentStepNumber++;
    $stepNumbers['verify'] = $currentStepNumber++;
    $stepNumbers['payment'] = $currentStepNumber++;

    $initialStepKey = !$isLoggedIn
        ? 'contact'
        : (($hasBookings ?? false) ? 'booking' : 'property');
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
        .booking-card {
            cursor: pointer;
            border: 1px solid #e5e7eb;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
            background: #ffffff;
        }
        .booking-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 1rem 2rem rgba(13, 110, 253, 0.15);
            transform: translateY(-2px);
        }
        .booking-card-active {
            border-color: #0d6efd;
            box-shadow: 0 1rem 2rem rgba(13, 110, 253, 0.2);
            background: #eef5ff;
        }
        .booking-card .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .booking-card .status-paid {
            color: #0f5132;
            background: #d1e7dd;
        }
        .booking-card .status-pending {
            color: #664d03;
            background: #fff3cd;
        }
        .booking-card .status-unpaid,
        .booking-card .status-failed {
            color: #842029;
            background: #f8d7da;
        }
        .booking-card .status-pill {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .booking-card-add {
            border-style: dashed;
            border-color: #94a3b8;
            background: #f8fafc;
        }
        .booking-card-add:hover {
            border-color: #0d6efd;
            background: #eef5ff;
        }
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
                        @if($stepNumbers['booking'])
                            <button class="step-btn {{ $initialStepNumber === $stepNumbers['booking'] ? 'active' : '' }}" data-step="{{ $stepNumbers['booking'] }}" id="btn-step-booking">Booking</button>
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
                                    <div class="col-12 muted-small">Didn't receive? <a href="#" class="text-primary fw-semibold" id="resendOtp">Resend</a></div>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-4">
                                <button type="button" class="btn btn-outline-secondary me-2" id="skipContact">Clear</button>
                                <button type="button" class="btn btn-primary" id="toStep2" disabled>Proceed to {{ ($hasBookings ?? false) ? 'Booking' : 'Property' }}</button>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($stepNumbers['booking'])
                    <!-- STEP: BOOKINGS -->
                    <div id="step-{{ $stepNumbers['booking'] }}" class="step-pane {{ $initialStepNumber === $stepNumbers['booking'] ? '' : 'hidden' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h2 class="app-title">Your bookings</h2>
                                <div class="muted-small">Select an existing property or add a new one to continue.</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="refreshBookingsBtn">Refresh</button>
                            </div>
                        </div>
                        <div class="card p-3 mb-3" style="border-radius:12px;">
                            <div id="bookingGridLoader" class="text-center py-3 d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="bookingGrid" class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3"></div>
                            <div id="bookingGridEmpty" class="muted-small text-muted mt-2"></div>
                        </div>
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                            <div class="muted-small text-muted">Tip: choose a saved booking or click “Add new booking”.</div>
                            <button type="button" class="btn btn-primary" id="bookingToProperty" disabled>Proceed to Booking</button>
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
                        <div id="propertyCard" class="card p-3 mb-3" style="border-radius:12px; max-height:62vh; overflow:auto;">
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
                                <div class="d-flex gap-2">
                                    <div class="top-pill" data-group="ownerType" data-value="Owner" onclick="topPillClick(this)">Owner</div>
                                    <div class="top-pill" data-group="ownerType" data-value="Broker" onclick="topPillClick(this)">Broker</div>
                                </div>
                            </div>
                            <!-- PROPERTY TYPE TAB -->
                            <div class="mb-3">
                                <div class="section-title">Property Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2">
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
                            </div>

                            <!-- PROPERTY SUB TYPE TAB -->
                            <div id="tab-res">
                                <div class="section-title">Property Sub Type<span class="text-danger">*</span></div>
                                <div class="d-wrap gap-2 mb-3">
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
                                <div class="section-title">Furnish Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                                    <div class="chip" data-group="resFurnish" data-value="Fully Furnished" onclick="selectChip(this)"><i class="bi bi-sofa"></i> Fully Furnished</div>
                                    <div class="chip" data-group="resFurnish" data-value="Semi Furnished" onclick="selectChip(this)"><i class="bi bi-lamp"></i> Semi Furnished</div>
                                    <div class="chip" data-group="resFurnish" data-value="Unfurnished" onclick="selectChip(this)"><i class="bi bi-door-closed"></i> Unfurnished</div>
                                </div>
                                <div class="section-title">Size (BHK / RK)</div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                                    @forelse($bhk as $bhkItem)
                                    <div class="chip" data-group="resSize" data-value="{{ $bhkItem->id }}" onclick="selectChip(this)">{{ $bhkItem->name }}</div>
                                    @empty
                                        <div class="chip" data-group="resSize" data-value="null" onclick="selectChip(this)">Not Found</div>
                                    @endforelse
                                </div>
                                <div class="mb-3">
                                    <div class="section-title">Super Built-up Area (sq. ft.) <span class="text-danger">*</span></div>
                                    <input id="resArea" name="residential_area" class="form-control" type="number" min="1" placeholder="e.g., 1200" />
                                    <div id="err-resArea" class="error">Area is required.</div>
                                </div>
                            </div>

                            <!-- COMMERCIAL TAB -->
                            <div id="tab-com" style="display:none;">
                                <div class="section-title">Property Sub Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
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
                                <!-- FURNATURE TABL -->
                                <div class="section-title">Furnish Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                                    <div class="chip" data-group="comFurnish" data-value="Fully Furnished" onclick="selectChip(this)">Fully Furnished</div>
                                    <div class="chip" data-group="comFurnish" data-value="Semi Furnished" onclick="selectChip(this)">Semi Furnished</div>
                                    <div class="chip" data-group="comFurnish" data-value="Unfurnished" onclick="selectChip(this)">Unfurnished</div>
                                </div>
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
                                    <div class="d-flex flex-column flex-sm-row gap-2">
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
                                    <div class="section-title">Other Option</div>
                                    <textarea id="othDesc" name="other_description" class="form-control" rows="3"></textarea>
                                    <div id="err-othDesc" class="error">Other option is required if none of the options are selected.</div>
                                </div>
                                <div class="mb-3">
                                    <div class="section-title">Super Built-up Area (sq. ft.)<span class="text-danger">*</span></div>
                                    <input id="othArea" name="other_area" class="form-control" type="number" min="1" />
                                    <div id="err-othArea" class="error">Area is required.</div>
                                </div>
                            </div>

                                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between mt-3">
                                    @unless($isLoggedIn)
                                        <button type="button" class="btn btn-outline-secondary" id="backToContact">Back to Contact</button>
                                    @endunless
                                    @if($stepNumbers['booking'])
                                        <button type="button" class="btn btn-outline-secondary" id="backToBooking">Back to Booking</button>
                                    @endif
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
                                    <textarea id="addrFull" name="full_address" class="form-control" rows="3" placeholder="Complete address..."></textarea>
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
    <script>
        window.SetupContext = {
            authenticated: @json($isLoggedIn),
            user: @json($setupUserPayload),
            steps: @json($stepNumbers),
            initialStep: @json($initialStepNumber),
            bookingTabEnabled: @json($hasBookings ?? false),
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
