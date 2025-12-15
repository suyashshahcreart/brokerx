@extends('admin.layouts.vertical', ['title' => 'Settings', 'subTitle' => 'System'])

@section('css')
    
@endsection

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
            <div class="col-12">
                <div class="card panel-card border-primary border-top" data-panel-card>
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

@section('script')
    <script>
        // Function to get active tab
        function getActiveTab() {
            const activeTab = document.querySelector('#vl-pills-tab .nav-link.active');
            if (activeTab) {
                const href = activeTab.getAttribute('href');
                return href || activeTab.id;
            }
            return null;
        }
        
        // Function to set active tab using Bootstrap Tab API
        function setActiveTab(tabSelector) {
            if (!tabSelector) return;
            
            // Find the tab trigger element
            const tabTrigger = document.querySelector(`#vl-pills-tab .nav-link[href="${tabSelector}"]`);
            if (tabTrigger && typeof bootstrap !== 'undefined') {
                // Use Bootstrap Tab API to switch tabs
                const tab = new bootstrap.Tab(tabTrigger);
                tab.show();
            } else if (tabTrigger) {
                // Fallback if Bootstrap is not available
                // Remove active class from all tabs
                document.querySelectorAll('#vl-pills-tab .nav-link').forEach(tab => {
                    tab.classList.remove('active', 'show');
                    tab.setAttribute('aria-selected', 'false');
                });
                
                // Remove active class from all tab panes
                document.querySelectorAll('#vl-pills-tabContent .tab-pane').forEach(pane => {
                    pane.classList.remove('active', 'show');
                });
                
                // Activate the target tab
                tabTrigger.classList.add('active', 'show');
                tabTrigger.setAttribute('aria-selected', 'true');
                
                // Activate the target pane
                const targetPane = document.querySelector(tabSelector);
                if (targetPane) {
                    targetPane.classList.add('active', 'show');
                }
            }
        }
        
        // Function to handle form submission
        function handleFormSubmit(form, submitBtn) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                
                // Validate form
                if (!form.checkValidity()) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }
                
                // Store current active tab before submission
                const activeTab = getActiveTab();
                
                // Disable submit button and show loading state
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i> Updating...';
                
                // Get form data
                const formData = new FormData(form);
                const csrfToken = form.getAttribute('data-csrf');
                
                // Handle checkbox values - if unchecked, set to "0"
                if (form.id === 'cashfreeForm' || form.id === 'payuForm' || form.id === 'razorpayForm') {
                    // Get the status checkbox for this specific form
                    let checkboxId = null;
                    if (form.id === 'cashfreeForm') {
                        checkboxId = 'cashfree_status';
                        // For Cashfree, include all fields - they will be saved to .env
                    } else if (form.id === 'payuForm') {
                        checkboxId = 'payu_status';
                    } else if (form.id === 'razorpayForm') {
                        checkboxId = 'razorpay_status';
                    }
                    
                    if (checkboxId) {
                        const checkbox = document.getElementById(checkboxId);
                        if (checkbox) {
                            if (!checkbox.checked) {
                                formData.set(checkboxId, '0');
                            } else {
                                formData.set(checkboxId, '1');
                            }
                        }
                    }
                }
                
                // Add CSRF token to form data if not already present
                if (!formData.has('_token')) {
                    formData.append('_token', csrfToken);
                }
                
                // Make AJAX request
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    let data;
                    
                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        throw { 
                            status: response.status, 
                            data: { message: text || 'An error occurred' } 
                        };
                    }
                    
                    if (!response.ok) {
                        throw { status: response.status, data: data };
                    }
                    return data;
                })
                .then(data => {
                    // Success response
                    if (data.success) {
                        // Determine gateway name for message
                        let gatewayName = 'Settings';
                        if (form.id === 'cashfreeForm') {
                            gatewayName = 'Cashfree';
                        } else if (form.id === 'payuForm') {
                            gatewayName = 'PayU Money';
                        } else if (form.id === 'razorpayForm') {
                            gatewayName = 'Razorpay';
                        }
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: gatewayName + ' settings updated successfully',
                                timer: 2000,
                                showConfirmButton: false,
                                timerProgressBar: true
                            });
                        } else {
                            alert(gatewayName + ' settings updated successfully');
                        }
                        
                        // Store active tab in localStorage before reload
                        if (activeTab) {
                            localStorage.setItem('settingsActiveTab', activeTab);
                        }
                        
                        // Reload the page after a short delay to show updated values
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        throw { data: data };
                    }
                })
                .catch(error => {
                    // Error handling
                    let errorMessage = 'An error occurred while updating settings.';
                    
                    // Network error
                    if (error instanceof TypeError && error.message.includes('fetch')) {
                        errorMessage = 'Network error. Please check your internet connection and try again.';
                    } else if (error.data) {
                        if (error.data.message) {
                            errorMessage = error.data.message;
                        } else if (error.data.errors) {
                            // Validation errors
                            const errors = Object.values(error.data.errors).flat();
                            errorMessage = errors.join('<br>');
                        } else if (error.status === 422) {
                            errorMessage = 'Validation error. Please check your input.';
                        } else if (error.status === 500) {
                            errorMessage = 'Server error. Please try again later.';
                        } else if (error.status === 403 || error.status === 401) {
                            errorMessage = 'You do not have permission to perform this action.';
                        }
                    } else if (error.message) {
                        errorMessage = error.message;
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            html: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(errorMessage);
                    }
                })
                .finally(() => {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            });
        }

        // Initialize panel-card actions (collapse, fullscreen, close)
        // This ensures the card action buttons work properly on this page
        // Use event delegation on document level to catch all clicks
        function initPanelCardActions() {
            // Remove any existing listeners by cloning and replacing
            const newHandler = function(event) {
                const button = event.target.closest('[data-panel-action]');
                if (!button) return;
                
                const card = button.closest('[data-panel-card]');
                if (!card) return;
                
                // Only handle cards on this page
                if (!card.closest('.page-content')) return;
                
                const action = button.getAttribute('data-panel-action');
                
                // Only handle if action is one we support
                if (['collapse', 'fullscreen', 'close'].indexOf(action) === -1) {
                    return;
                }
                
                // Stop event from propagating to other handlers
                event.stopImmediatePropagation();
                event.preventDefault();
                
                switch (action) {
                    case 'collapse':
                        handleCollapse(card, button);
                        break;
                    case 'fullscreen':
                        handleFullscreen(card, button);
                        break;
                    case 'close':
                        handleClose(card);
                        break;
                }
            };
            
            // Add listener with high priority (capture phase)
            document.addEventListener('click', newHandler, true);
        }
        
        // Initialize immediately and also on DOMContentLoaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPanelCardActions);
        } else {
            initPanelCardActions();
        }
        
        // Collapse handler
        function handleCollapse(card, button) {
            const sections = card.querySelectorAll('.card-body, .card-footer');
            if (sections.length === 0) return;
            
            const isCollapsed = sections[0].classList.contains('d-none');
            
            sections.forEach(section => {
                if (isCollapsed) {
                    section.classList.remove('d-none');
                    section.style.display = '';
                } else {
                    section.classList.add('d-none');
                    section.style.display = 'none';
                }
            });
            
            const icon = button.querySelector('i');
            if (icon) {
                if (isCollapsed) {
                    icon.classList.remove('ri-arrow-down-s-line');
                    icon.classList.add('ri-arrow-up-s-line');
                } else {
                    icon.classList.remove('ri-arrow-up-s-line');
                    icon.classList.add('ri-arrow-down-s-line');
                }
            }
        }
        
        // Fullscreen handler - matches SCSS styles
        function handleFullscreen(card, button) {
            const isFullscreen = card.classList.contains('card-fullscreen') || 
                                 card.classList.contains('panel-card-fullscreen');
            
            if (isFullscreen) {
                card.classList.remove('card-fullscreen', 'panel-card-fullscreen');
                document.body.style.overflow = '';
            } else {
                card.classList.add('card-fullscreen');
                document.body.style.overflow = 'hidden';
            }
            
            const icon = button.querySelector('i');
            if (icon) {
                if (isFullscreen) {
                    icon.classList.remove('ri-fullscreen-exit-line');
                    icon.classList.add('ri-fullscreen-line');
                } else {
                    icon.classList.remove('ri-fullscreen-line');
                    icon.classList.add('ri-fullscreen-exit-line');
                }
            }
        }
        
        // Close handler
        function handleClose(card) {
            if (confirm('Are you sure you want to close this card?')) {
                card.style.transition = 'opacity 0.3s ease';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                }, 300);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            
            // Restore active tab from localStorage on page load (for manual reloads)
            const savedTab = localStorage.getItem('settingsActiveTab');
            if (savedTab) {
                // Small delay to ensure Bootstrap is ready
                setTimeout(() => {
                    setActiveTab(savedTab);
                }, 100);
            }
            
            // Save active tab to localStorage whenever a tab is clicked
            const tabLinks = document.querySelectorAll('#vl-pills-tab .nav-link');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function (e) {
                    // This event fires after Bootstrap switches the tab
                    const activeTab = e.target.getAttribute('href');
                    if (activeTab) {
                        localStorage.setItem('settingsActiveTab', activeTab);
                    }
                });
                
                // Also handle click for immediate save (fallback)
                tabLink.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href) {
                        localStorage.setItem('settingsActiveTab', href);
                    }
                });
            });
            
            // Handle Booking Schedule Date form
            const settingsForm = document.getElementById('settingsForm');
            const updateBtn = document.getElementById('updateSettingsBtn');
            if (settingsForm && updateBtn) {
                handleFormSubmit(settingsForm, updateBtn);
            }
            
            // Handle Base Price form
            const basePriceForm = document.getElementById('basePriceForm');
            const updateBasePriceBtn = document.getElementById('updateBasePriceBtn');
            if (basePriceForm && updateBasePriceBtn) {
                handleFormSubmit(basePriceForm, updateBasePriceBtn);
            }
            
            // Function to auto-save active payment gateway when toggle changes
            // Supports multiple active gateways (comma-separated)
            // Also saves individual gateway statuses
            function saveActivePaymentGateway() {
                // Get CSRF token from any form
                const cashfreeForm = document.getElementById('cashfreeForm');
                const csrfToken = cashfreeForm ? cashfreeForm.getAttribute('data-csrf') : '';
                
                // Get status of all gateways
                const cashfreeStatus = document.getElementById('cashfree_status')?.checked || false;
                const payuStatus = document.getElementById('payu_status')?.checked || false;
                const razorpayStatus = document.getElementById('razorpay_status')?.checked || false;
                
                // Build array of active gateways for display
                const activeGateways = [];
                if (cashfreeStatus) {
                    activeGateways.push('Cashfree');
                }
                if (payuStatus) {
                    activeGateways.push('PayU Money');
                }
                if (razorpayStatus) {
                    activeGateways.push('Razorpay');
                }
                
                // Save as comma-separated string (e.g., "cashfree,payu" or "cashfree" or "")
                const activeGatewayValue = activeGateways.map(g => g.toLowerCase().replace(' money', '').replace(' ', '')).join(',');
                const activeGatewayDisplay = activeGateways.length > 0 ? activeGateways.join(', ') : 'None';
                
                const formData = new FormData();
                // Save active_payment_gateway (comma-separated list)
                formData.append('active_payment_gateway', activeGatewayValue);
                // Save individual gateway statuses
                formData.append('cashfree_status', cashfreeStatus ? '1' : '0');
                formData.append('payu_status', payuStatus ? '1' : '0');
                formData.append('razorpay_status', razorpayStatus ? '1' : '0');
                formData.append('_token', csrfToken);
                
                fetch('{{ route("api.settings.update") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    let data;
                    
                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        throw { 
                            status: response.status, 
                            data: { message: text || 'An error occurred' } 
                        };
                    }
                    
                    if (!response.ok) {
                        throw { status: response.status, data: data };
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        // Success - show success message
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: `Active payment gateway${activeGateways.length > 1 ? 's' : ''}: ${activeGatewayDisplay}`,
                                timer: 2000,
                                showConfirmButton: false,
                                timerProgressBar: true,
                                toast: true,
                                position: 'top-end'
                            });
                        } else {
                            alert(`Active payment gateway${activeGateways.length > 1 ? 's' : ''} updated: ${activeGatewayDisplay}`);
                        }
                    } else {
                        throw { data: data };
                    }
                })
                .catch(error => {
                    // Error handling
                    let errorMessage = 'Failed to update active payment gateway.';
                    
                    if (error instanceof TypeError && error.message.includes('fetch')) {
                        errorMessage = 'Network error. Please check your internet connection.';
                    } else if (error.data) {
                        if (error.data.message) {
                            errorMessage = error.data.message;
                        } else if (error.data.errors) {
                            const errors = Object.values(error.data.errors).flat();
                            errorMessage = errors.join('<br>');
                        } else if (error.status === 500) {
                            errorMessage = 'Server error. Please try again later.';
                        } else if (error.status === 403 || error.status === 401) {
                            errorMessage = 'You do not have permission to perform this action.';
                        }
                    }
                    
                    // Show error message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            html: errorMessage,
                            timer: 3000,
                            showConfirmButton: false,
                            timerProgressBar: true,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        alert(errorMessage);
                    }
                    
                    // Revert toggle if save failed
                    // Note: We don't revert here as user might want to try again
                });
            }
            
            // Handle Payment Gateway forms individually
            // Cashfree Form - Credentials will be saved to .env file
            const cashfreeForm = document.getElementById('cashfreeForm');
            const saveCashfreeBtn = document.getElementById('saveCashfreeBtn');
            if (cashfreeForm && saveCashfreeBtn) {
                const cashfreeEnv = document.getElementById('cashfree_env');
                const cashfreeBaseUrl = document.getElementById('cashfree_base_url');
                const cashfreeStatus = document.getElementById('cashfree_status');
                
                // Auto-update base URL based on environment selection
                const updateBaseUrl = () => {
                    const env = cashfreeEnv.value;
                    if (env === 'production') {
                        cashfreeBaseUrl.value = 'https://api.cashfree.com/pg';
                    } else {
                        cashfreeBaseUrl.value = 'https://sandbox.cashfree.com/pg';
                    }
                };
                
                // Update base URL when environment changes
                if (cashfreeEnv) {
                    cashfreeEnv.addEventListener('change', updateBaseUrl);
                }
                
                const toggleCashfreeRequired = () => {
                    const status = cashfreeStatus.checked;
                    document.getElementById('cashfree_app_id').required = status;
                    document.getElementById('cashfree_secret_key').required = status;
                    document.querySelectorAll('.cashfree-required').forEach(el => {
                        el.style.display = status ? 'inline' : 'none';
                    });
                };
                toggleCashfreeRequired();
                
                // Auto-save active payment gateway when toggle changes
                cashfreeStatus.addEventListener('change', function() {
                    toggleCashfreeRequired();
                    saveActivePaymentGateway();
                });
                
                handleFormSubmit(cashfreeForm, saveCashfreeBtn);
            }
            
            // PayU Form
            const payuForm = document.getElementById('payuForm');
            const savePayuBtn = document.getElementById('savePayuBtn');
            if (payuForm && savePayuBtn) {
                const payuStatus = document.getElementById('payu_status');
                
                const togglePayuRequired = () => {
                    const status = payuStatus.checked;
                    document.getElementById('payu_merchant_key').required = status;
                    document.getElementById('payu_merchant_salt').required = status;
                    document.querySelectorAll('.payu-required').forEach(el => {
                        el.style.display = status ? 'inline' : 'none';
                    });
                };
                togglePayuRequired();
                
                // Auto-save active payment gateway when toggle changes
                payuStatus.addEventListener('change', function() {
                    togglePayuRequired();
                    saveActivePaymentGateway();
                });
                
                handleFormSubmit(payuForm, savePayuBtn);
            }
            
            // Razorpay Form
            const razorpayForm = document.getElementById('razorpayForm');
            const saveRazorpayBtn = document.getElementById('saveRazorpayBtn');
            if (razorpayForm && saveRazorpayBtn) {
                const razorpayStatus = document.getElementById('razorpay_status');
                
                const toggleRazorpayRequired = () => {
                    const status = razorpayStatus.checked;
                    document.getElementById('razorpay_key').required = status;
                    document.getElementById('razorpay_secret').required = status;
                    document.querySelectorAll('.razorpay-required').forEach(el => {
                        el.style.display = status ? 'inline' : 'none';
                    });
                };
                toggleRazorpayRequired();
                
                // Auto-save active payment gateway when toggle changes
                razorpayStatus.addEventListener('change', function() {
                    toggleRazorpayRequired();
                    saveActivePaymentGateway();
                });
                
                handleFormSubmit(razorpayForm, saveRazorpayBtn);
            }
            
            // Handle SMS Gateway forms
            document.querySelectorAll('form[id$="SmsForm"]').forEach(form => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    handleFormSubmit(form, submitBtn);
                }
            });
            
            // Handle set active SMS gateway button
            document.querySelectorAll('.set-active-sms-gateway-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const gatewayKey = this.getAttribute('data-gateway');
                    const form = this.closest('form');
                    const csrfToken = form.getAttribute('data-csrf');
                    
                    const formData = new FormData();
                    formData.append('active_sms_gateway', gatewayKey);
                    formData.append('_token', csrfToken);
                    
                    this.disabled = true;
                    this.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i> Setting...';
                    
                    fetch('{{ route("api.settings.update") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw { status: response.status, data: data };
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: gatewayKey.toUpperCase() + ' is now your active SMS gateway',
                                    timer: 2000,
                                    showConfirmButton: false,
                                    timerProgressBar: true
                                });
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            throw { data: data };
                        }
                    })
                    .catch(error => {
                        let errorMessage = 'Failed to set active gateway.';
                        if (error.data && error.data.message) {
                            errorMessage = error.data.message;
                        }
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(errorMessage);
                        }
                        
                        this.disabled = false;
                        this.innerHTML = '<i class="ri-check-line me-1"></i> Set as Active';
                    });
                });
            });
            
            // Handle SMS gateway status toggles
            document.querySelectorAll('.gateway-status-toggle').forEach(checkbox => {
                // Initialize required fields visibility for SMS gateways
                const gatewayKey = checkbox.getAttribute('data-gateway');
                const form = checkbox.closest('form');
                if (form) {
                    const requiredFields = form.querySelectorAll('.' + gatewayKey + '-sms-required');
                    requiredFields.forEach(el => {
                        el.style.display = checkbox.checked ? 'inline' : 'none';
                    });
                    
                    // Set required attribute on inputs
                    const requiredInputs = form.querySelectorAll('input[type="text"], input[type="password"], input[type="number"], select');
                    requiredInputs.forEach(input => {
                        const fieldContainer = input.closest('.mb-3');
                        if (fieldContainer && fieldContainer.querySelector('.' + gatewayKey + '-sms-required')) {
                            input.required = checkbox.checked;
                        }
                    });
                }
                
                checkbox.addEventListener('change', function() {
                    const gatewayKey = this.getAttribute('data-gateway');
                    const form = this.closest('form');
                    const csrfToken = form ? form.getAttribute('data-csrf') : '';
                    
                    // Store original state for revert
                    const originalChecked = !this.checked;
                    
                    const formData = new FormData();
                    formData.append(this.name, this.checked ? '1' : '0');
                    formData.append('_token', csrfToken);
                    
                    fetch('{{ route("api.settings.update") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type');
                        let data;
                        
                        if (contentType && contentType.includes('application/json')) {
                            data = await response.json();
                        } else {
                            const text = await response.text();
                            throw { 
                                status: response.status, 
                                data: { message: text || 'An error occurred' } 
                            };
                        }
                        
                        if (!response.ok) {
                            throw { status: response.status, data: data };
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            // Toggle required fields visibility
                            if (form) {
                                const requiredFields = form.querySelectorAll('.' + gatewayKey + '-sms-required');
                                requiredFields.forEach(el => {
                                    el.style.display = this.checked ? 'inline' : 'none';
                                });
                                
                                // Set required attribute on inputs
                                const requiredInputs = form.querySelectorAll('input[type="text"], input[type="password"], input[type="number"], select');
                                requiredInputs.forEach(input => {
                                    const fieldContainer = input.closest('.mb-3');
                                    if (fieldContainer && fieldContainer.querySelector('.' + gatewayKey + '-sms-required')) {
                                        input.required = this.checked;
                                    }
                                });
                            }
                            
                            // Show success message
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Updated!',
                                    text: 'SMS gateway status updated successfully',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    timerProgressBar: true,
                                    toast: true,
                                    position: 'top-end'
                                });
                            }
                        } else {
                            throw { data: data };
                        }
                    })
                    .catch(error => {
                        // Revert checkbox on error
                        this.checked = originalChecked;
                        
                        let errorMessage = 'Failed to update gateway status.';
                        if (error instanceof TypeError && error.message.includes('fetch')) {
                            errorMessage = 'Network error. Please check your internet connection.';
                        } else if (error.data) {
                            if (error.data.message) {
                                errorMessage = error.data.message;
                            } else if (error.data.errors) {
                                const errors = Object.values(error.data.errors).flat();
                                errorMessage = errors.join('<br>');
                            } else if (error.status === 500) {
                                errorMessage = 'Server error. Please try again later.';
                            } else if (error.status === 403 || error.status === 401) {
                                errorMessage = 'You do not have permission to perform this action.';
                            }
                        }
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                html: errorMessage,
                                timer: 3000,
                                showConfirmButton: false,
                                timerProgressBar: true,
                                toast: true,
                                position: 'top-end'
                            });
                        } else {
                            alert(errorMessage);
                        }
                    });
                });
            });
            
            // MSG91 Templates Management - Initialize when modal is shown
            const msg91TemplatesModal = document.getElementById('msg91TemplatesModal');
            const templatesTableBody = document.getElementById('templatesTableBody');
            
            // Function to initialize template management
            function initTemplateManagement() {
                const addTemplateBtn = document.getElementById('addTemplateBtn');
                const saveTemplatesBtn = document.getElementById('saveTemplatesBtn');
                
                // Add Template functionality
                if (addTemplateBtn) {
                    // Remove existing listeners
                    const newAddBtn = addTemplateBtn.cloneNode(true);
                    addTemplateBtn.parentNode.replaceChild(newAddBtn, addTemplateBtn);
                    
                    newAddBtn.addEventListener('click', function() {
                        const newRow = document.createElement('tr');
                        newRow.innerHTML = `
                            <td>
                                <input type="text" 
                                    class="form-control form-control-sm template-key" 
                                    value="" 
                                    data-original=""
                                    placeholder="e.g., login_otp">
                            </td>
                            <td>
                                <input type="text" 
                                    class="form-control form-control-sm template-id" 
                                    value="" 
                                    placeholder="Template ID">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger delete-template-btn">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        `;
                        templatesTableBody.appendChild(newRow);
                        
                        // Attach delete handler to new row
                        attachDeleteHandler(newRow.querySelector('.delete-template-btn'));
                        
                        // Scroll to new row
                        newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        newRow.querySelector('.template-key').focus();
                    });
                }
                
                // Delete Template functionality
                function attachDeleteHandler(btn) {
                    btn.addEventListener('click', function() {
                        const row = this.closest('tr');
                        if (confirm('Are you sure you want to delete this template?')) {
                            row.style.transition = 'opacity 0.3s';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                            }, 300);
                        }
                    });
                }
                
                // Attach delete handlers to existing buttons
                templatesTableBody.querySelectorAll('.delete-template-btn').forEach(btn => {
                    attachDeleteHandler(btn);
                });
                
                // Save Templates functionality
                if (saveTemplatesBtn) {
                    // Remove existing listeners
                    const newSaveBtn = saveTemplatesBtn.cloneNode(true);
                    saveTemplatesBtn.parentNode.replaceChild(newSaveBtn, saveTemplatesBtn);
                    
                    newSaveBtn.addEventListener('click', function() {
                        saveTemplates();
                    });
                }
            }
            
            // Initialize when modal is shown
            if (msg91TemplatesModal) {
                msg91TemplatesModal.addEventListener('shown.bs.modal', function() {
                    initTemplateManagement();
                });
            }
            
            // Also initialize on page load (in case modal is already open)
            initTemplateManagement();
            
            // Save Templates function
            function saveTemplates() {
                const saveTemplatesBtn = document.getElementById('saveTemplatesBtn');
                const templates = {};
                let hasError = false;
                const errors = [];
                
                // Collect all templates from table
                templatesTableBody.querySelectorAll('tr').forEach(row => {
                    const keyInput = row.querySelector('.template-key');
                    const idInput = row.querySelector('.template-id');
                    
                    if (keyInput && idInput) {
                        const key = keyInput.value.trim();
                        const id = idInput.value.trim();
                        
                        if (key && id) {
                            // Validate key format (should be lowercase with underscores)
                            if (!/^[a-z0-9_]+$/.test(key)) {
                                hasError = true;
                                errors.push(`Template key "${key}" is invalid. Use only lowercase letters, numbers, and underscores.`);
                                keyInput.classList.add('is-invalid');
                            } else if (templates[key]) {
                                hasError = true;
                                errors.push(`Duplicate template key: "${key}"`);
                                keyInput.classList.add('is-invalid');
                            } else {
                                templates[key] = id;
                                keyInput.classList.remove('is-invalid');
                                idInput.classList.remove('is-invalid');
                            }
                        } else if (key || id) {
                            // One field filled but not both
                            hasError = true;
                            errors.push('Both Template Key and Template ID are required.');
                            if (!key) keyInput.classList.add('is-invalid');
                            if (!id) idInput.classList.add('is-invalid');
                        }
                    }
                });
                
                if (hasError) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error!',
                            html: errors.join('<br>'),
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(errors.join('\n'));
                    }
                    return;
                }
                
                // Disable button and show loading
                const originalBtnText = saveTemplatesBtn.innerHTML;
                saveTemplatesBtn.disabled = true;
                saveTemplatesBtn.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i> Saving...';
                
                // Get CSRF token from any form
                const csrfToken = document.querySelector('form[data-csrf]')?.getAttribute('data-csrf') || '';
                
                const formData = new FormData();
                formData.append('msg91_templates', JSON.stringify(templates));
                formData.append('_token', csrfToken);
                
                fetch('{{ route("api.settings.update") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    let data;
                    
                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        throw { 
                            status: response.status, 
                            data: { message: text || 'An error occurred' } 
                        };
                    }
                    
                    if (!response.ok) {
                        throw { status: response.status, data: data };
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Templates saved successfully',
                                timer: 2000,
                                showConfirmButton: false,
                                timerProgressBar: true
                            });
                        } else {
                            alert('Templates saved successfully');
                        }
                        
                        // Close modal and reload after short delay
                        const modal = bootstrap.Modal.getInstance(msg91TemplatesModal);
                        if (modal) {
                            setTimeout(() => {
                                modal.hide();
                                window.location.reload();
                            }, 1500);
                        } else {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    } else {
                        throw { data: data };
                    }
                })
                .catch(error => {
                    let errorMessage = 'Failed to save templates.';
                    
                    if (error instanceof TypeError && error.message.includes('fetch')) {
                        errorMessage = 'Network error. Please check your internet connection.';
                    } else if (error.data) {
                        if (error.data.message) {
                            errorMessage = error.data.message;
                        } else if (error.data.errors) {
                            const errors = Object.values(error.data.errors).flat();
                            errorMessage = errors.join('<br>');
                        } else if (error.status === 500) {
                            errorMessage = 'Server error. Please try again later.';
                        } else if (error.status === 403 || error.status === 401) {
                            errorMessage = 'You do not have permission to perform this action.';
                        }
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            html: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(errorMessage);
                    }
                })
                .finally(() => {
                    saveTemplatesBtn.disabled = false;
                    saveTemplatesBtn.innerHTML = originalBtnText;
                });
            }
            
            // Save templates
            if (saveTemplatesBtn) {
                saveTemplatesBtn.addEventListener('click', function() {
                    const templates = {};
                    let hasError = false;
                    const errors = [];
                    
                    // Collect all templates from table
                    templatesTableBody.querySelectorAll('tr').forEach(row => {
                        const keyInput = row.querySelector('.template-key');
                        const idInput = row.querySelector('.template-id');
                        
                        if (keyInput && idInput) {
                            const key = keyInput.value.trim();
                            const id = idInput.value.trim();
                            
                            if (key && id) {
                                // Validate key format (should be lowercase with underscores)
                                if (!/^[a-z0-9_]+$/.test(key)) {
                                    hasError = true;
                                    errors.push(`Template key "${key}" is invalid. Use only lowercase letters, numbers, and underscores.`);
                                    keyInput.classList.add('is-invalid');
                                } else if (templates[key]) {
                                    hasError = true;
                                    errors.push(`Duplicate template key: "${key}"`);
                                    keyInput.classList.add('is-invalid');
                                } else {
                                    templates[key] = id;
                                    keyInput.classList.remove('is-invalid');
                                    idInput.classList.remove('is-invalid');
                                }
                            } else if (key || id) {
                                // One field filled but not both
                                hasError = true;
                                errors.push('Both Template Key and Template ID are required.');
                                if (!key) keyInput.classList.add('is-invalid');
                                if (!id) idInput.classList.add('is-invalid');
                            }
                        }
                    });
                    
                    if (hasError) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error!',
                                html: errors.join('<br>'),
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(errors.join('\n'));
                        }
                        return;
                    }
                    
                    // Disable button and show loading
                    const originalBtnText = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i> Saving...';
                    
                    // Get CSRF token from any form
                    const csrfToken = document.querySelector('form[data-csrf]')?.getAttribute('data-csrf') || '';
                    
                    const formData = new FormData();
                    formData.append('msg91_templates', JSON.stringify(templates));
                    formData.append('_token', csrfToken);
                    
                    fetch('{{ route("api.settings.update") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type');
                        let data;
                        
                        if (contentType && contentType.includes('application/json')) {
                            data = await response.json();
                        } else {
                            const text = await response.text();
                            throw { 
                                status: response.status, 
                                data: { message: text || 'An error occurred' } 
                            };
                        }
                        
                        if (!response.ok) {
                            throw { status: response.status, data: data };
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: 'Templates saved successfully',
                                    timer: 2000,
                                    showConfirmButton: false,
                                    timerProgressBar: true
                                });
                            } else {
                                alert('Templates saved successfully');
                            }
                            
                            // Update original values for tracking
                            templatesTableBody.querySelectorAll('tr').forEach(row => {
                                const keyInput = row.querySelector('.template-key');
                                if (keyInput) {
                                    keyInput.setAttribute('data-original', keyInput.value.trim());
                                }
                            });
                            
                            // Reload after short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            throw { data: data };
                        }
                    })
                    .catch(error => {
                        let errorMessage = 'Failed to save templates.';
                        
                        if (error instanceof TypeError && error.message.includes('fetch')) {
                            errorMessage = 'Network error. Please check your internet connection.';
                        } else if (error.data) {
                            if (error.data.message) {
                                errorMessage = error.data.message;
                            } else if (error.data.errors) {
                                const errors = Object.values(error.data.errors).flat();
                                errorMessage = errors.join('<br>');
                            } else if (error.status === 500) {
                                errorMessage = 'Server error. Please try again later.';
                            } else if (error.status === 403 || error.status === 401) {
                                errorMessage = 'You do not have permission to perform this action.';
                            }
                        }
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                html: errorMessage,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(errorMessage);
                        }
                    })
                    .finally(() => {
                        this.disabled = false;
                        this.innerHTML = originalBtnText;
                    });
                });
            }
        });
    </script>
    
    <style>
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Settings Tab Navigation Styling */
        .settings-nav-pills {
            gap: 0.5rem;
        }
        
        .settings-nav-pills .nav-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e9ecef;
            background-color: #ffffff;
            color: #495057;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            text-decoration: none;
            cursor: pointer;
        }
        
        .settings-nav-pills .nav-link:hover:not(.active) {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #495057;
        }
        
        .settings-nav-pills .nav-link.active,
        .settings-nav-pills .nav-link.active.show {
            background-color: var(--bs-primary, #6366f1) !important;
            border-color: var(--bs-primary, #6366f1) !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
        }
        
        /* Fallback if CSS variables not available */
        .settings-nav-pills .nav-link.active,
        .settings-nav-pills .nav-link.active.show {
            background-color: #6366f1;
            border-color: #6366f1;
        }
        
        .settings-nav-pills .nav-link i {
            font-size: 1.25rem;
            width: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s ease;
        }
        
        .settings-nav-pills .nav-link.active i,
        .settings-nav-pills .nav-link.active.show i {
            color: #ffffff !important;
        }
        
        .settings-nav-pills .nav-link:not(.active) i {
            color: #6c757d;
        }
        
        .settings-nav-pills .nav-link:not(.active):hover i {
            color: #495057;
        }
        
        /* Payment Gateway Cards Styling */
        #vl-pills-settings .card {
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        #vl-pills-settings .card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        #vl-pills-settings .card-header {
            border-bottom: 1px solid #e9ecef;
        }
        
        #vl-pills-settings .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }
        
        #vl-pills-settings .form-check-input {
            width: 3rem;
            height: 1.5rem;
            cursor: pointer;
        }
        
        /* Panel Card Fullscreen Styles - matches SCSS */
        .panel-card.card-fullscreen,
        .panel-card.panel-card-fullscreen {
            position: fixed !important;
            inset: 1.5rem !important;
            width: auto !important;
            height: auto !important;
            z-index: 1055 !important;
            background: var(--bs-body-bg, #fff) !important;
            box-shadow: 0 0 1rem rgba(0, 0, 0, .15) !important;
        }
        
        .panel-card.card-fullscreen .card-body,
        .panel-card.panel-card-fullscreen .card-body {
            overflow: auto !important;
            max-height: calc(100vh - 8rem) !important;
        }
        
        /* Panel Card Collapse Animation */
        .panel-card .card-body,
        .panel-card .card-footer {
            transition: all 0.3s ease;
        }
        
        .panel-card .card-body.d-none,
        .panel-card .card-footer.d-none {
            display: none !important;
        }
        
        /* Templates Table Styling */
        #templatesTable {
            font-size: 0.875rem;
        }
        
        #templatesTable thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        #templatesTable tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        #templatesTable .template-key,
        #templatesTable .template-id {
            border: none;
            padding: 0.375rem 0.5rem;
        }
        
        #templatesTable .template-key:focus,
        #templatesTable .template-id:focus {
            outline: 2px solid #6366f1;
            outline-offset: -2px;
        }
        
        #templatesTable .is-invalid {
            border: 1px solid #dc3545;
            background-color: #fff5f5;
        }
        
        #templatesTable .delete-template-btn {
            padding: 0.25rem 0.5rem;
        }
    </style>
@endsection
