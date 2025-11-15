@extends('frontend.layouts.base', ['title' => 'Setup Virtual Tour - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400..800&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
@endsection

@section('content')
    <div class="preloader-bg"></div>
    <div id="preloader">
        <div id="preloader-status">
            <div class="preloader-position loader"> <span></span> </div>
        </div>
    </div>

    <div class="progress-wrap cursor-pointer">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div>

    <div class="cursor js-cursor"></div>

    <div class="social-ico-block">
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-instagram"></i></a>
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-x-twitter"></i></a>
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-youtube"></i></a>
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-tiktok"></i></a>
        <a href="https://duruthemes.com/demo/html/gloom/" target="_blank" class="social-ico"><i class="fa-brands fa-flickr"></i></a>
    </div>

    <div class="page">
        <div class="panel">
            <!-- TOP PROGRESS -->
            <div class="prog-wrap">
                <div class="steps-bar">
                    <div class="progress-top"><i id="progressBar"></i></div>
                </div>
                <div class="step-row">
                    <div class="step-buttons">
                        <button class="step-btn active" data-step="1" id="btn-step-1">Contact</button>
                        <button class="step-btn" data-step="2" id="btn-step-2">Property</button>
                        <button class="step-btn" data-step="3" id="btn-step-3">Address</button>
                        <button class="step-btn" data-step="4" id="btn-step-4">Verify</button>
                        <button class="step-btn" data-step="5" id="btn-step-5">Payment</button>
                    </div>
                </div>
            </div>

            <!-- MAIN CONTENT -->
            <form id="setupForm" method="POST" action="{{ route('frontend.setup.store') }}">
                @csrf
                <div class="content">
                    <!-- STEP 1: CONTACT + OTP -->
                    <div id="step-1" class="step-pane">
                        <h2 class="app-title">Contact details & verification</h2>
                        <div class="muted-small mb-3">Enter your name and phone. We'll send an OTP to verify.</div>
                        <div class="card p-3 mb-3" style="border-radius:12px;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full name <span class="text-danger">*</span></label>
                                    <input id="inputName" name="name" class="form-control" placeholder="e.g., John Doe" value="{{ request('prefillName') }}" />
                                    <div id="err-name" class="error">Name is required.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone number <span class="text-danger">*</span></label>
                                    <input id="inputPhone" name="phone" class="form-control" placeholder="10-digit mobile" maxlength="10" value="{{ request('prefillPhone') }}" />
                                    <div id="err-phone" class="error">Enter a valid 10-digit mobile number.</div>
                                </div>
                            </div>
                            <div class="mt-3 d-flex flex-column flex-sm-row gap-2 align-items-center">
                                <button type="button" class="btn btn-primary" id="sendOtpBtn">Send OTP</button>
                                <div id="otpSentBadge" class="muted-small text-success hidden">OTP sent</div>
                                <div id="demoOtp" class="muted-small text-muted hidden">[demo OTP: <strong id="demoOtpCode"></strong>]</div>
                            </div>
                            <div id="otpRow" class="mt-3 hidden">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <input id="inputOtp" class="form-control" maxlength="6" placeholder="Enter OTP" />
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
                                <button type="button" class="btn btn-primary" id="toStep2" disabled>Proceed to Property</button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: PROPERTY DETAILS -->
                    <div id="step-2" class="step-pane hidden">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h2 class="app-title">Property details</h2>
                                <div class="muted-small">All selections below are required to continue.</div>
                            </div>
                        </div>
                        <div class="card p-3 mb-3" style="border-radius:12px; max-height:62vh; overflow:auto;">
                            <input type="hidden" id="choice_ownerType" name="owner_type">
                            <input type="hidden" id="choice_resType" name="residential_property_type">
                            <input type="hidden" id="choice_resFurnish" name="residential_furnish">
                            <input type="hidden" id="choice_resSize" name="residential_size">
                            <input type="hidden" id="choice_comType" name="commercial_property_type">
                            <input type="hidden" id="choice_comFurnish" name="commercial_furnish">
                            <input type="hidden" id="choice_othLooking" name="other_looking">
                            <input type="hidden" id="mainPropertyType" name="main_property_type" value="Residential">

                            <div class="mb-3">
                                <div class="section-title">Owner Type <span class="text-danger">*</span></div>
                                <div class="d-flex gap-2">
                                    <div class="top-pill" data-group="ownerType" data-value="Owner" onclick="topPillClick(this)">Owner</div>
                                    <div class="top-pill" data-group="ownerType" data-value="Broker" onclick="topPillClick(this)">Broker</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="section-title">Property Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <div class="top-pill" id="pillResidential" onclick="handlePropertyTabChange('res')">Residential</div>
                                    <div class="top-pill" id="pillCommercial" onclick="handlePropertyTabChange('com')">Commercial</div>
                                    <div class="top-pill" id="pillOther" onclick="handlePropertyTabChange('oth')">Other</div>
                                </div>
                            </div>

                            <!-- RESIDENTIAL TAB -->
                            <div id="tab-res">
                                <div class="section-title">Property Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                                    <div class="top-pill" data-group="resType" data-value="Apartment" onclick="selectCard(this)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="5" y="3" width="8" height="18" rx="1.2"></rect>
                                            <path d="M13 7h4v14"></path>
                                            <path d="M4 21h16"></path>
                                            <path d="M8 7h2M8 11h2M8 15h2"></path>
                                        </svg>
                                        Apartment
                                    </div>
                                    <div class="top-pill" data-group="resType" data-value="House" onclick="selectCard(this)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 11L12 4l8 7"></path>
                                            <path d="M5 10v10h4v-6h6v6h4V10"></path>
                                        </svg>
                                        House
                                    </div>
                                    <div class="top-pill" data-group="resType" data-value="Duplex" onclick="selectCard(this)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 9l9-6 9 6"></path>
                                            <path d="M5 9v11h6V9"></path>
                                            <path d="M13 9v11h6V9"></path>
                                            <path d="M8 13h2M16 13h2"></path>
                                        </svg>
                                        Duplex
                                    </div>
                                </div>
                                <div class="section-title">Furnish Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                                    <div class="chip" data-group="resFurnish" data-value="Fully Furnished" onclick="selectChip(this)"><i class="bi bi-sofa"></i> Fully Furnished</div>
                                    <div class="chip" data-group="resFurnish" data-value="Semi Furnished" onclick="selectChip(this)"><i class="bi bi-lamp"></i> Semi Furnished</div>
                                    <div class="chip" data-group="resFurnish" data-value="Unfurnished" onclick="selectChip(this)"><i class="bi bi-door-closed"></i> Unfurnished</div>
                                </div>
                                <div class="section-title">Size (BHK / RK)</div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                                    <div class="chip" data-group="resSize" data-value="1 BHK" onclick="selectChip(this)">1 BHK</div>
                                    <div class="chip" data-group="resSize" data-value="2 BHK" onclick="selectChip(this)">2 BHK</div>
                                    <div class="chip" data-group="resSize" data-value="3 BHK" onclick="selectChip(this)">3 BHK</div>
                                    <div class="chip" data-group="resSize" data-value="4+ BHK" onclick="selectChip(this)">4+ BHK</div>
                                </div>
                                <div class="mb-3">
                                    <div class="section-title">Super Built-up Area (sq. ft.) <span class="text-danger">*</span></div>
                                    <input id="resArea" name="residential_area" class="form-control" type="number" min="1" placeholder="e.g., 1200" />
                                    <div id="err-resArea" class="error">Area is required.</div>
                                </div>
                            </div>

                            <!-- COMMERCIAL TAB -->
                            <div id="tab-com" style="display:none;">
                                <div class="section-title">Property Type<span class="text-danger">*</span></div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                                    <div class="top-pill" data-group="comType" data-value="Office" onclick="selectCard(this)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 9l9-6 9 6v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                            <path d="M7 21v-6h10v6"></path>
                                            <path d="M9 13v4M15 13v4"></path>
                                        </svg>
                                        Office
                                    </div>
                                    <div class="top-pill" data-group="comType" data-value="Shop" onclick="selectCard(this)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 9l2-5h14l2 5"></path>
                                            <path d="M4 9v9h16V9"></path>
                                            <path d="M8 14h4"></path>
                                            <path d="M12 18v-4"></path>
                                        </svg>
                                        Shop
                                    </div>
                                    <div class="top-pill" data-group="comType" data-value="Showroom" onclick="selectCard(this)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="6" width="18" height="14" rx="2"></rect>
                                            <path d="M7 10h10v6H7z"></path>
                                            <path d="M5 6V3h4v3"></path>
                                            <path d="M15 6V3h4v3"></path>
                                        </svg>
                                        Showroom
                                    </div>
                                </div>
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
                                        <div class="top-pill" data-group="othLooking" data-value="Warehouse" onclick="topPillClick(this)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 9l9-6 9 6v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                <path d="M7 21v-6h10v6"></path>
                                                <path d="M9 13v4M15 13v4"></path>
                                            </svg>
                                            Warehouse
                                        </div>
                                        <div class="top-pill" data-group="othLooking" data-value="Restaurant" onclick="topPillClick(this)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M6 3v6a3 3 0 0 0 3 3"></path>
                                                <path d="M6 3c3 0 5 2 5 5v4"></path>
                                                <path d="M18 3v18"></path>
                                                <path d="M14 7h4"></path>
                                            </svg>
                                            Restaurant
                                        </div>
                                        <div class="top-pill" data-group="othLooking" data-value="Cafe" onclick="topPillClick(this)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 10h12v5a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4z"></path>
                                                <path d="M16 12h2.5a2.5 2.5 0 0 1 0 5H16"></path>
                                                <path d="M6 3c0 2 2 2 2 4s-2 2-2 4"></path>
                                            </svg>
                                            Cafe
                                        </div>
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
                                <button type="button" class="btn btn-outline-secondary" id="backToContact">Back to Contact</button>
                                <button type="button" class="btn btn-primary" id="toStep3">Proceed to Address</button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: ADDRESS -->
                    <div id="step-3" class="step-pane hidden">
                        <h2 class="app-title">Address</h2>
                        <div class="muted-small mb-2">Provide full address details (all required).</div>
                        <div class="card p-3 mb-3" style="border-radius:12px;">
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
                                    <label class="form-label">City</label>
                                    <select id="addrCity" name="city" class="form-select" disabled>
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
                                <button type="button" class="btn btn-primary" id="toStep4">Proceed to Verify</button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: VERIFY -->
                    <div id="step-4" class="step-pane hidden">
                        <h2 class="app-title">Verify & Review</h2>
                        <div class="muted-small mb-3">Check all details. Use Edit buttons to go back and change if needed.</div>
                        <div class="card p-3 mb-3" style="border-radius:12px;">
                            <div id="summaryArea"></div>
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between mt-3">
                                <button type="button" class="btn btn-outline-secondary" id="backToAddress">Back to Address</button>
                                <button type="button" class="btn btn-success" id="toStep5">Proceed to Payment</button>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 5: PAYMENT -->
                    <div id="step-5" class="step-pane hidden">
                        <h2 class="app-title">Payment</h2>
                        <div class="muted-small mb-3">This is a demo payment screen. Choose a method and click Pay.</div>
                        <div class="card p-3 mb-3" style="border-radius:12px;">
                            <input type="hidden" name="payment_method" id="paymentMethodInput">
                            <div class="mb-3">
                                <label class="form-label">Select Payment Method</label>
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <div class="top-pill" data-pay="card" onclick="selectPay(this)">Credit / Debit Card</div>
                                    <div class="top-pill" data-pay="upi" onclick="selectPay(this)">UPI</div>
                                    <div class="top-pill" data-pay="netbanking" onclick="selectPay(this)">Netbanking</div>
                                </div>
                            </div>
                            <div id="payFields" class="mt-3 hidden">
                                <div class="mb-3">
                                    <label class="form-label">Amount (INR)</label>
                                    <input id="payAmount" name="amount" class="form-control" type="number" value="999" />
                                </div>
                            </div>
                            <div id="payEstimateBox" class="summary-price-box hidden">
                                <div class="label">Estimated Price</div>
                                <div class="amount" id="payEstimateValue">₹0</div>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-outline-secondary" id="backToVerify">Back to Verify</button>
                                <button type="submit" class="btn btn-primary" id="doPay" disabled>Pay</button>
                            </div>
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
    <script src="{{ asset('frontend/js/custom.js') }}"></script>
    <script src="{{ asset('frontend/js/setup.js') }}"></script>
@endsection
