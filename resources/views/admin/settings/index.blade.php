@extends('admin.layouts.vertical', ['title' => 'Settings', 'subTitle' => 'System'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">System</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Settings</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">General Settings Management</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    @if(!empty($canCreate) && $canCreate)
                        <a href="{{ route('admin.settings.create') }}" class="btn btn-primary" title="Add Setting"
                            data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add Setting">
                            <i class="ri-add-line me-1"></i> New Setting
                        </a>
                    @endif
                </div>
            </div>
            <!-- main pannel Card  Settings -->
            <div class="col-12">
                <div class="card panel-card border-primary border-top" data-panel-card>
                    <!-- Heaer -->
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 px-2">
                        <div>
                            <h4 class="card-title mb-0">General Settings </h4>
                        </div>
                        <div class="panel-actions d-flex gap-2">
                            <button type="button" class="btn btn-light border" data-panel-action="collapse"
                                title="Collapse">
                                <i class="ri-arrow-up-s-line"></i>
                            </button>
                            <button type="button" class="btn btn-light border" data-panel-action="fullscreen"
                                title="Fullscreen">
                                <i class="ri-fullscreen-line"></i>
                            </button>
                            <button type="button" class="btn btn-light border" data-panel-action="close" title="Close">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-0 px-2">
                        <div class="row mb-5">
                            <div class="col-sm-3 col-md-3 col-lg-3 mb-2 mb-sm-0">
                                <div class="nav flex-column nav-pills settings-nav-pills" id="vl-pills-tab" role="tablist" aria-orientation="vertical">
                                    <a class="nav-link active show" id="vl-pills-home-tab" data-bs-toggle="pill" href="#vl-pills-home" role="tab" aria-controls="vl-pills-home" aria-selected="true">
                                        <i class="ri-calendar-event-line me-2"></i>
                                        <span>Booking Schedule Date</span>
                                    </a>
                                    <a class="nav-link" id="vl-pills-photographer-tab" data-bs-toggle="pill" href="#vl-pills-photographer" role="tab" aria-controls="vl-pills-photographer" aria-selected="false">
                                        <i class="ri-camera-2-line me-2"></i>
                                        <span>Photographer Settings</span>
                                    </a>
                                    <a class="nav-link" id="vl-pills-profile-tab" data-bs-toggle="pill" href="#vl-pills-profile" role="tab" aria-controls="vl-pills-profile" aria-selected="false">
                                        <i class="ri-money-dollar-circle-line me-2"></i>
                                        <span>Base Price</span>
                                    </a>
                                    <a class="nav-link" id="vl-pills-settings-tab" data-bs-toggle="pill" href="#vl-pills-settings" role="tab" aria-controls="vl-pills-settings" aria-selected="false">
                                        <i class="ri-bank-card-line me-2"></i>
                                        <span>Payment Gateway</span>
                                    </a>
                                    <a class="nav-link" id="vl-pills-sms-tab" data-bs-toggle="pill" href="#vl-pills-sms" role="tab" aria-controls="vl-pills-sms" aria-selected="false">
                                        <i class="ri-message-3-line me-2"></i>
                                        <span>SMS Configuration</span>
                                    </a>
                                </div>
                            </div>
                        
                            <div class="col-sm-9 col-md-9 col-lg-9">
                                <div class="tab-content pt-0" id="vl-pills-tabContent">
                                    <div class="tab-pane fade active show" id="vl-pills-home" role="tabpanel" aria-labelledby="vl-pills-home-tab">
                                        <form id="settingsForm" action="{{ route('api.settings.update') }}" method="POST"
                                            class="needs-validation" novalidate data-csrf="{{ csrf_token() }}">
                                            @csrf
                                            <!-- AVALIABLE DAY -->
                                            <div class="mb-3">
                                                <label for="avaliable_days" class="form-label">Avaliable Day <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="avaliable_days" id="avaliable_days"
                                                    value="{{ $settings['avaliable_days'] ?? '' }}" class="form-control"
                                                    placeholder="e.g., 7" required minlength="1" maxlength="255">
                                                <small class="form-text text-muted">Booking schedule dates will be available starting from next day + this number of days. For example, if set to 7, bookings will be available from 7 days from next day onwards.</small>
                                            </div>

                                            <!-- PER DAY BOOKING -->
                                            <div class="mb-3">
                                                <label for="per_day_booking" class="form-label">Per Day Booking <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="per_day_booking" id="per_day_booking"
                                                    value="{{ $settings['per_day_booking'] ?? '20' }}" class="form-control"
                                                    placeholder="e.g., 20" required min="1" max="1000">
                                                <small class="form-text text-muted">Maximum number of bookings allowed per day. If a date reaches this limit, it will be automatically disabled in the calendar. This count includes all booking statuses except declined schedules (schedul_decline).</small>
                                            </div>

                                            <!-- CUSTOMER SCHEDULE ATTEMPTS -->
                                            <div class="mb-3">
                                                <label for="customer_attempt" class="form-label">Customer Schedule Attempts <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="customer_attempt" id="customer_attempt"
                                                    value="{{ $settings['customer_attempt'] ?? '3' }}" class="form-control"
                                                    placeholder="e.g., 3" required min="1" max="10">
                                                <small class="form-text text-muted">Maximum number of times admin can ACCEPT a customer's schedule/reschedule request. After reaching this limit, booking will be blocked automatically and customer must contact admin. Example: If set to 3, after 3 accepted schedules, no more schedules allowed.</small>
                                            </div>

                                            <!-- CUSTOMER ATTEMPT NOTE -->
                                            <div class="mb-3">
                                                <label for="customer_attempt_note" class="form-label">Customer Blocked Message</label>
                                                <textarea name="customer_attempt_note" id="customer_attempt_note" 
                                                    class="form-control" rows="3"
                                                    placeholder="Message to show when customer reaches attempt limit">{{ $settings['customer_attempt_note'] ?? 'You have reached the maximum number of schedule attempts. Please contact admin for further assistance.' }}</textarea>
                                                <small class="form-text text-muted">This message will be displayed to customers when they reach the maximum schedule attempts limit.</small>
                                            </div>
                                            <!-- // submit buttons -->
                                            <div class="d-flex gap-2 justify-content-end pt-4">
                                                <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">
                                                    <i class="ri-close-line me-1"></i> Cancel
                                                </a>
                                                <button type="submit" class="btn btn-primary" id="updateSettingsBtn">
                                                    <i class="ri-save-line me-1"></i> Update Settings
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="vl-pills-photographer" role="tabpanel" aria-labelledby="vl-pills-photographer-tab">
                                        <form id="photographerSettingsForm" action="{{ route('api.settings.update') }}" method="POST"
                                            class="needs-validation" novalidate data-csrf="{{ csrf_token() }}">
                                            @csrf

                                            <div class="mb-3">
                                                <label for="photographer_available_from" class="form-label">Daily Available From</label>
                                                <input type="time" name="photographer_available_from" id="photographer_available_from"
                                                    value="{{ $settings['photographer_available_from'] ?? '08:00' }}" class="form-control" required>
                                                <small class="form-text text-muted">Start time for photographers (e.g., 08:00 for 8 AM).</small>
                                            </div>

                                            <div class="mb-3">
                                                <label for="photographer_available_to" class="form-label">Daily Available To</label>
                                                <input type="time" name="photographer_available_to" id="photographer_available_to"
                                                    value="{{ $settings['photographer_available_to'] ?? '21:00' }}" class="form-control" required>
                                                <small class="form-text text-muted">End time for photographers (e.g., 21:00 for 9 PM).</small>
                                            </div>

                                            <div class="mb-3">
                                                <label for="photographer_working_duration" class="form-label">Working Duration (minutes)</label>
                                                <input type="number" name="photographer_working_duration" id="photographer_working_duration"
                                                    value="{{ $settings['photographer_working_duration'] ?? '60' }}" min="1" class="form-control" required>
                                                <small class="form-text text-muted">Duration of each working slot in minutes (e.g., 60).</small>
                                            </div>

                                            <div class="d-flex gap-2 justify-content-end pt-4">
                                                <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">
                                                    <i class="ri-close-line me-1"></i> Cancel
                                                </a>
                                                <button type="submit" class="btn btn-primary" id="savePhotographerSettingsBtn">
                                                    <i class="ri-save-line me-1"></i> Save Photographer Settings
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="vl-pills-profile" role="tabpanel" aria-labelledby="vl-pills-profile-tab">
                                        <form id="basePriceForm" action="{{ route('api.settings.update') }}" method="POST"
                                            class="needs-validation" novalidate data-csrf="{{ csrf_token() }}">
                                            @csrf
                                            <!-- BASE PRICE -->
                                            <div class="mb-3">
                                                <label for="base_price" class="form-label">Base Price (₹) <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="base_price" id="base_price" step="0.01" min="0"
                                                    value="{{ $settings['base_price'] ?? '599' }}" class="form-control"
                                                    placeholder="e.g., 599" required>
                                                <small class="form-text text-muted">The base price for properties up to the base area.</small>
                                            </div>
                                            
                                            <!-- BASE AREA -->
                                            <div class="mb-3">
                                                <label for="base_area" class="form-label">Base Area (sq ft) <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="base_area" id="base_area" step="1" min="0"
                                                    value="{{ $settings['base_area'] ?? '1500' }}" class="form-control"
                                                    placeholder="e.g., 1500" required>
                                                <small class="form-text text-muted">The base area in square feet. Properties above this area will have additional charges.</small>
                                            </div>
                                            
                                            <!-- EXTRA AREA -->
                                            <div class="mb-3">
                                                <label for="extra_area" class="form-label">Extra Area Block (sq ft) <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="extra_area" id="extra_area" step="1" min="0"
                                                    value="{{ $settings['extra_area'] ?? '500' }}" class="form-control"
                                                    placeholder="e.g., 500" required>
                                                <small class="form-text text-muted">The area block size in square feet. Each block above base area will add extra price.</small>
                                            </div>
                                            
                                            <!-- EXTRA AREA PRICE -->
                                            <div class="mb-3">
                                                <label for="extra_area_price" class="form-label">Extra Area Price (₹) <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="extra_area_price" id="extra_area_price" step="0.01" min="0"
                                                    value="{{ $settings['extra_area_price'] ?? '200' }}" class="form-control"
                                                    placeholder="e.g., 200" required>
                                                <small class="form-text text-muted">The price added for each extra area block.</small>
                                            </div>
                                            
                                            <!-- // submit buttons -->
                                            <div class="d-flex gap-2 justify-content-end pt-4">
                                                <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">
                                                    <i class="ri-close-line me-1"></i> Cancel
                                                </a>
                                                <button type="submit" class="btn btn-primary" id="updateBasePriceBtn">
                                                    <i class="ri-save-line me-1"></i> Update Settings
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="vl-pills-settings" role="tabpanel" aria-labelledby="vl-pills-settings-tab">
                                        <div class="row g-4">
                                            <!-- Cashfree Card -->
                                            <div class="col-md-6 col-lg-4">
                                                <form id="cashfreeForm" action="{{ route('api.settings.update') }}" method="POST"
                                                    class="needs-validation" novalidate data-csrf="{{ csrf_token() }}">
                                                    @csrf
                                                    <div class="card h-100 border">
                                                        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="bg-primary text-white rounded p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                                    <strong class="text-white">CF</strong>
                                                                </div>
                                                                <h5 class="mb-0">Cashfree</h5>
                                                            </div>
                                                            <div class="form-check form-switch">
                                                                @php
                                                                    // Check if cashfree is in active_payment_gateway or has individual status
                                                                    $activeGateways = explode(',', $settings['active_payment_gateway'] ?? '');
                                                                    $cashfreeActive = in_array('cashfree', $activeGateways) || ($settings['cashfree_status'] ?? '0') == '1';
                                                                @endphp
                                                                <input class="form-check-input" type="checkbox" id="cashfree_status" 
                                                                    name="cashfree_status" value="1" 
                                                                    {{ $cashfreeActive ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="cashfree_status"></label>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="alert alert-info mb-3 py-2">
                                                                <small><i class="ri-information-line me-1"></i> Cashfree credentials will be updated in <code>.env</code> file when saved.</small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="cashfree_app_id" class="form-label">Cashfree App ID <span class="text-danger cashfree-required">*</span></label>
                                                                <input type="text" name="cashfree_app_id" id="cashfree_app_id" 
                                                                    value="{{ config('cashfree.app_id', '') }}" 
                                                                    class="form-control" placeholder="Cashfree App ID" required>
                                                                <small class="form-text text-muted">Will be saved to .env as <code>CASHFREE_APP_ID</code></small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="cashfree_secret_key" class="form-label">Cashfree Secret Key <span class="text-danger cashfree-required">*</span></label>
                                                                <input type="password" name="cashfree_secret_key" id="cashfree_secret_key" 
                                                                    value="{{ config('cashfree.secret_key', '') }}" 
                                                                    class="form-control" placeholder="Cashfree Secret Key" required>
                                                                <small class="form-text text-muted">Will be saved to .env as <code>CASHFREE_SECRET_KEY</code></small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="cashfree_env" class="form-label">Environment</label>
                                                                <select name="cashfree_env" id="cashfree_env" class="form-select">
                                                                    <option value="sandbox" {{ config('cashfree.env', 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                                                    <option value="production" {{ config('cashfree.env') == 'production' ? 'selected' : '' }}>Production</option>
                                                                </select>
                                                                <small class="form-text text-muted">Will be saved to .env as <code>CASHFREE_ENV</code>. Base URL will auto-update based on selection.</small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="cashfree_base_url" class="form-label">API Base URL</label>
                                                                @php
                                                                    $currentEnv = config('cashfree.env', 'sandbox');
                                                                    $baseUrl = config('cashfree.base_url');
                                                                    // Auto-set base URL if not set or if env changed
                                                                    if (!$baseUrl || ($currentEnv == 'production' && strpos($baseUrl, 'api.cashfree.com') === false) || ($currentEnv == 'sandbox' && strpos($baseUrl, 'sandbox.cashfree.com') === false)) {
                                                                        $baseUrl = $currentEnv == 'production' ? 'https://api.cashfree.com/pg' : 'https://sandbox.cashfree.com/pg';
                                                                    }
                                                                @endphp
                                                                <input type="text" name="cashfree_base_url" id="cashfree_base_url" 
                                                                    value="{{ $baseUrl }}" 
                                                                    class="form-control" placeholder="https://sandbox.cashfree.com/pg" readonly>
                                                                <small class="form-text text-muted">Auto-updates based on Environment selection. Saved to .env as <code>CASHFREE_BASE_URL</code></small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="cashfree_return_url" class="form-label">Return URL Route Name</label>
                                                                <input type="text" name="cashfree_return_url" id="cashfree_return_url" 
                                                                    value="{{ config('cashfree.return_url_route', 'frontend.cashfree.callback') }}" 
                                                                    class="form-control" placeholder="frontend.cashfree.callback">
                                                                <small class="form-text text-muted">Route name (e.g., frontend.cashfree.callback). Will be saved to .env as <code>CASHFREE_RETURN_URL</code></small>
                                                            </div>
                                                            <div class="d-flex justify-content-end">
                                                                <button type="submit" class="btn btn-primary btn-sm" id="saveCashfreeBtn">
                                                                    <i class="ri-save-line me-1"></i> Save
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- PayU Money Card -->
                                            <div class="col-md-6 col-lg-4">
                                                <form id="payuForm" action="{{ route('api.settings.update') }}" method="POST"
                                                    class="needs-validation" novalidate data-csrf="{{ csrf_token() }}">
                                                    @csrf
                                                    <div class="card h-100 border">
                                                        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="bg-success text-white rounded p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                                    <strong class="text-white">PU</strong>
                                                                </div>
                                                                <h5 class="mb-0">PayU Money</h5>
                                                            </div>
                                                            <div class="form-check form-switch">
                                                                @php
                                                                    // Check if payu is in active_payment_gateway or has individual status
                                                                    $activeGateways = explode(',', $settings['active_payment_gateway'] ?? '');
                                                                    $payuActive = in_array('payu', $activeGateways) || ($settings['payu_status'] ?? '0') == '1';
                                                                @endphp
                                                                <input class="form-check-input" type="checkbox" id="payu_status" 
                                                                    name="payu_status" value="1" 
                                                                    {{ $payuActive ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="payu_status"></label>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="mb-3">
                                                                <label for="payu_merchant_key" class="form-label">PayU Merchant Key <span class="text-danger payu-required">*</span></label>
                                                                <input type="text" name="payu_merchant_key" id="payu_merchant_key" 
                                                                    value="{{ $settings['payu_merchant_key'] ?? '' }}" 
                                                                    class="form-control" placeholder="PayU Merchant Key">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="payu_merchant_salt" class="form-label">PayU Merchant Salt <span class="text-danger payu-required">*</span></label>
                                                                <input type="password" name="payu_merchant_salt" id="payu_merchant_salt" 
                                                                    value="{{ $settings['payu_merchant_salt'] ?? '' }}" 
                                                                    class="form-control" placeholder="PayU Merchant Salt">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="payu_mode" class="form-label">Mode</label>
                                                                <select name="payu_mode" id="payu_mode" class="form-select">
                                                                    <option value="test" {{ ($settings['payu_mode'] ?? 'test') == 'test' ? 'selected' : '' }}>Test</option>
                                                                    <option value="live" {{ ($settings['payu_mode'] ?? '') == 'live' ? 'selected' : '' }}>Live</option>
                                                                </select>
                                                                <small class="form-text text-muted">Select test mode for sandbox testing</small>
                                                            </div>
                                                            <div class="d-flex justify-content-end">
                                                                <button type="submit" class="btn btn-primary btn-sm" id="savePayuBtn">
                                                                    <i class="ri-save-line me-1"></i> Save
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- Razorpay Card -->
                                            <div class="col-md-6 col-lg-4">
                                                <form id="razorpayForm" action="{{ route('api.settings.update') }}" method="POST"
                                                    class="needs-validation" novalidate data-csrf="{{ csrf_token() }}">
                                                    @csrf
                                                    <div class="card h-100 border">
                                                        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="bg-info text-white rounded p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                                    <strong class="text-white">RZ</strong>
                                                                </div>
                                                                <h5 class="mb-0">Razorpay</h5>
                                                            </div>
                                                            <div class="form-check form-switch">
                                                                @php
                                                                    // Check if razorpay is in active_payment_gateway or has individual status
                                                                    $activeGateways = explode(',', $settings['active_payment_gateway'] ?? '');
                                                                    $razorpayActive = in_array('razorpay', $activeGateways) || ($settings['razorpay_status'] ?? '0') == '1';
                                                                @endphp
                                                                <input class="form-check-input" type="checkbox" id="razorpay_status" 
                                                                    name="razorpay_status" value="1" 
                                                                    {{ $razorpayActive ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="razorpay_status"></label>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="mb-3">
                                                                <label for="razorpay_key" class="form-label">Razorpay Key <span class="text-danger razorpay-required">*</span></label>
                                                                <input type="text" name="razorpay_key" id="razorpay_key" 
                                                                    value="{{ $settings['razorpay_key'] ?? '' }}" 
                                                                    class="form-control" placeholder="Razorpay Key">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="razorpay_secret" class="form-label">Razorpay Secret <span class="text-danger razorpay-required">*</span></label>
                                                                <input type="password" name="razorpay_secret" id="razorpay_secret" 
                                                                    value="{{ $settings['razorpay_secret'] ?? '' }}" 
                                                                    class="form-control" placeholder="Razorpay Secret">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="razorpay_mode" class="form-label">Mode</label>
                                                                <select name="razorpay_mode" id="razorpay_mode" class="form-select">
                                                                    <option value="test" {{ ($settings['razorpay_mode'] ?? 'test') == 'test' ? 'selected' : '' }}>Test</option>
                                                                    <option value="live" {{ ($settings['razorpay_mode'] ?? '') == 'live' ? 'selected' : '' }}>Live</option>
                                                                </select>
                                                                <small class="form-text text-muted">Select test mode for sandbox testing</small>
                                                            </div>
                                                            <div class="d-flex justify-content-end">
                                                                <button type="submit" class="btn btn-primary btn-sm" id="saveRazorpayBtn">
                                                                    <i class="ri-save-line me-1"></i> Save
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="vl-pills-sms" role="tabpanel" aria-labelledby="vl-pills-sms-tab">
                                        <div class="row g-4">
                                            @php
                                                // Show only MSG91 gateway
                                                $msg91Gateway = $gatewayInstances['msg91'] ?? null;
                                            @endphp
                                            @if($msg91Gateway)
                                                <div class="col-6">
                                                    <form id="msg91SmsForm" action="{{ route('api.settings.update') }}" method="POST"
                                                        class="needs-validation" novalidate data-csrf="{{ csrf_token() }}">
                                                        @csrf
                                                        <div class="card h-100 border {{ $msg91Gateway['isActive'] ? 'border-success' : '' }}">
                                                            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                                                                <h5 class="mb-0">MSG91</h5>
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input gateway-status-toggle" 
                                                                        type="checkbox" 
                                                                        id="msg91_sms_status" 
                                                                        name="sms_gateway_msg91_status" 
                                                                        value="1" 
                                                                        data-gateway="msg91"
                                                                        {{ $msg91Gateway['status'] ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="msg91_sms_status"></label>
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                @foreach($msg91Gateway['configFields'] as $field)
                                                                    @php
                                                                        $fieldValue = $settings[$field['key']] ?? ($field['default'] ?? '');
                                                                        $fieldId = $field['key'] . '_sms';
                                                                        $isRequired = $field['required'] ?? false;
                                                                    @endphp
                                                                    <div class="mb-3">
                                                                        <label for="{{ $fieldId }}" class="form-label">
                                                                            {{ strtoupper(str_replace('_', ' ', $field['key'])) }}
                                                                            @if($isRequired)
                                                                                <span class="text-danger msg91-sms-required">*</span>
                                                                            @endif
                                                                        </label>
                                                                        @if($field['type'] === 'select')
                                                                            <select name="{{ $field['key'] }}" 
                                                                                id="{{ $fieldId }}" 
                                                                                class="form-select {{ $isRequired ? 'required' : '' }}"
                                                                                {{ $isRequired ? 'required' : '' }}>
                                                                                @foreach($field['options'] ?? [] as $optionValue => $optionLabel)
                                                                                    <option value="{{ $optionValue }}" {{ $fieldValue == $optionValue ? 'selected' : '' }}>
                                                                                        {{ $optionLabel }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        @elseif($field['type'] === 'password')
                                                                            <input type="password" 
                                                                                name="{{ $field['key'] }}" 
                                                                                id="{{ $fieldId }}" 
                                                                                value="{{ $fieldValue }}" 
                                                                                class="form-control {{ $isRequired ? 'required' : '' }}"
                                                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                                {{ $isRequired ? 'required' : '' }}>
                                                                        @elseif($field['type'] === 'number')
                                                                            <input type="number" 
                                                                                name="{{ $field['key'] }}" 
                                                                                id="{{ $fieldId }}" 
                                                                                value="{{ $fieldValue }}" 
                                                                                class="form-control {{ $isRequired ? 'required' : '' }}"
                                                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                                {{ $isRequired ? 'required' : '' }}
                                                                                step="{{ $field['step'] ?? '1' }}"
                                                                                min="{{ $field['min'] ?? '' }}">
                                                                        @else
                                                                            <input type="{{ $field['type'] ?? 'text' }}" 
                                                                                name="{{ $field['key'] }}" 
                                                                                id="{{ $fieldId }}" 
                                                                                value="{{ $fieldValue }}" 
                                                                                class="form-control {{ $isRequired ? 'required' : '' }}"
                                                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                                {{ $isRequired ? 'required' : '' }}>
                                                                        @endif
                                                                        @if(isset($field['help']))
                                                                            <small class="form-text text-muted">{{ $field['help'] }}</small>
                                                                        @endif
                                                                    </div>
                                                                @endforeach

                                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#msg91TemplatesModal">
                                                                        <i class="ri-settings-3-line me-1"></i> Manage Templates
                                                                    </button>
                                                                    <button type="submit" class="btn btn-primary btn-sm" id="saveMsg91SmsBtn">
                                                                        <i class="ri-save-line me-1"></i> Save
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- MSG91 Templates Management Modal -->
                        <div class="modal fade" id="msg91TemplatesModal" tabindex="-1" aria-labelledby="msg91TemplatesModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="msg91TemplatesModalLabel">
                                            <i class="ri-settings-3-line me-2"></i>MSG91 Templates Management
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <p class="text-muted mb-0">Manage your MSG91 Flow template IDs</p>
                                                <small class="text-info">
                                                    <i class="ri-file-text-line me-1"></i> Templates are saved directly to <strong>config/msg91.php</strong>
                                                </small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-primary" id="addTemplateBtn">
                                                <i class="ri-add-line me-1"></i> Add Template
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover mb-0" id="templatesTable">
                                                <thead>
                                                    <tr>
                                                        <th width="40%">Template Key</th>
                                                        <th width="50%">Template ID</th>
                                                        <th width="10%" class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="templatesTableBody">
                                                    @foreach($msg91Templates ?? [] as $key => $templateId)
                                                        <tr data-template-key="{{ $key }}">
                                                            <td>
                                                                <input type="text" 
                                                                    class="form-control form-control-sm template-key" 
                                                                    value="{{ $key }}" 
                                                                    data-original="{{ $key }}"
                                                                    placeholder="e.g., login_otp">
                                                            </td>
                                                            <td>
                                                                <input type="text" 
                                                                    class="form-control form-control-sm template-id" 
                                                                    value="{{ $templateId }}" 
                                                                    placeholder="Template ID">
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm btn-danger delete-template-btn" data-key="{{ $key }}">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if(empty($msg91Templates))
                                            <div class="text-center py-4">
                                                <i class="ri-inbox-line fs-48 text-muted"></i>
                                                <p class="text-muted mt-2">No templates found. Click "Add Template" to create one.</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="saveTemplatesBtn">
                                            <i class="ri-save-line me-1"></i> Save Templates
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
@vite(['resources/js/pages/setting-index.js'])
@endsection
