@extends('frontend.layouts.base', ['title' => 'Login - PROP PIK'])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ config('app.url') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400..800&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/css/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
@endsection

@section('body_attribute') class="setup-flow" @endsection

@section('content')
    <section class="page-header section-padding-bottom-b section-padding-top-t page-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="wow page-title" data-splitting data-delay="100"> Dashboard Login</h1>
                </div>
            </div>
        </div>
    </section>
    <div class="page bg-light section-padding-bottom section-padding-top">
        <div class="panel container">
            <div class="content">
                <div class="login-container">
                    <h2 class="app-title text-center mb-2">Dashboard Login</h2>
                    <p class="muted-small text-center mb-4">Enter your phone number. We'll send an OTP to verify.</p>
                    
                    <form id="loginForm" class="login-form" onsubmit="return false;">
                        <div id="phoneInputSection" class="mb-3">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input id="loginPhone" class="form-control" type="text" placeholder="10-digit mobile number" maxlength="10" required />
                            <div id="err-phone" class="error">Enter a valid 10-digit mobile number.</div>
                        </div>
                        
                        <div id="sendOtpSection" class="mb-3">
                            <button type="button" class="btn btn-primary w-100" id="sendOtpBtn">Send OTP</button>
                            <div id="otpSentBadge" class="muted-small text-success hidden mt-2">OTP sent</div>
                        </div>
                        
                        <!-- OTP input -->
                            <div id="otpRow" class="mb-3 hidden">
                                <label class="form-label">Enter OTP <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <input id="inputOtp" class="form-control" maxlength="6" placeholder="Enter 6-digit OTP" />
                                        <div id="err-otp" class="error">Enter the correct OTP.</div>
                                    </div>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary w-100" id="verifyOtpBtn">Verify OTP</button>
                                    </div>
                                    <div id="otpInfoAlert" class="alert alert-info mb-3" role="alert">
                                        <i class="fa-solid fa-circle-info me-2"></i>
                                        <span id="otpInfoMessage">
                                            <strong>OTP Sent!</strong> Your verification code has been delivered to your Phone and WhatsApp. Enter the OTP to continue.
                                        </span>
                                    </div>
                                    <div class="col-12">
                                        <div class="muted-small text-center">Didn't receive? <a href="#" class="text-primary fw-semibold" id="resendOtp">Resend</a></div>
                                    </div>
                                </div>
                            </div>
                        
                        <div class="text-center mt-3">
                            <p class="muted-small mb-0">Don't have an account? <a href="{{ route('frontend.setup') }}" style="color: var(--color-primary); text-decoration: none; font-weight: 600;">Get Started</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="loginSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #28a745;">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                            <path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h5 class="mb-3">Login Successful!</h5>
                    <p class="muted-small mb-3">Redirecting to your dashboard...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script-bottom')
    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Helper function
            function el(id) { 
                const element = document.getElementById(id);
                if (!element) {
                    console.error('Element not found:', id);
                }
                return element;
            }

            // Get CSRF token
            function getCsrfToken() {
                const meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.content : '';
            }

            // Get base URL
            function getBaseUrl() {
                const meta = document.querySelector('meta[name="app-url"]');
                return (meta?.content || window.location.origin || '').replace(/\/+$/, '');
            }

            // Build URL
            function buildUrl(path = '') {
                if (!path) return getBaseUrl();
                if (/^https?:\/\//i.test(path)) return path;
                const normalized = path.replace(/^\/+/, '');
                const base = getBaseUrl();
                return base ? `${base}/${normalized}` : `/${normalized}`;
            }

            // State management
            const loginState = {
                otp: null,
                otpVerified: false,
                phone: null
            };

            // Resend OTP countdown timer
            let resendCountdown = null;
            let resendTimerInterval = null;

            // Error display helper
            function showError(elementId, message) {
                const errorEl = el(elementId);
                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.style.display = 'block';
                }
            }

            function hideError(elementId) {
                const errorEl = el(elementId);
                if (errorEl) {
                    errorEl.style.display = 'none';
                }
            }

            // Simple alert function (using SweetAlert2 if available, otherwise native alert)
            async function showAlert(type, title, message) {
                if (typeof Swal !== 'undefined') {
                    await Swal.fire({
                        icon: type,
                        title: title,
                        text: message,
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(`${title}: ${message}`);
                }
            }

            // Validate phone number
            function validatePhone(phone) {
                const trimmed = phone.trim();
                if (!trimmed) {
                    return { valid: false, message: 'Phone number is required.' };
                }
                if (!/^[0-9]{10}$/.test(trimmed)) {
                    return { valid: false, message: 'Enter a valid 10-digit mobile number.' };
                }
                return { valid: true, phone: trimmed };
            }

            // Start resend OTP countdown timer
            function startResendCountdown() {
                const resendOtpBtn = el('resendOtp');
                if (!resendOtpBtn) return;

                // Clear any existing timer
                if (resendTimerInterval) {
                    clearInterval(resendTimerInterval);
                }

                // Set initial countdown
                resendCountdown = 60;
                resendOtpBtn.style.pointerEvents = 'none';
                resendOtpBtn.style.opacity = '0.6';
                resendOtpBtn.textContent = `Resend in ${resendCountdown}s`;

                // Update countdown every second
                resendTimerInterval = setInterval(function() {
                    resendCountdown--;
                    
                    if (resendCountdown > 0) {
                        resendOtpBtn.textContent = `Resend in ${resendCountdown}s`;
                    } else {
                        // Countdown finished, enable resend
                        clearInterval(resendTimerInterval);
                        resendTimerInterval = null;
                        resendOtpBtn.style.pointerEvents = 'auto';
                        resendOtpBtn.style.opacity = '1';
                        resendOtpBtn.textContent = 'Resend';
                    }
                }, 1000);
            }

            // Send OTP button handler
            const sendOtpBtn = el('sendOtpBtn');
            if (!sendOtpBtn) {
                console.error('Send OTP button not found!');
                return;
            }

            sendOtpBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                
                // Prevent action if button is disabled
                if (sendOtpBtn.disabled) {
                    return;
                }
                
                const phoneInput = el('loginPhone');
                if (!phoneInput) {
                    console.error('Phone input not found!');
                    return;
                }

                const phone = phoneInput.value.trim();
                
                // Clear previous error
                hideError('err-phone');
                
                // Validate phone
                const validation = validatePhone(phone);
                if (!validation.valid) {
                    showError('err-phone', validation.message);
                    phoneInput.focus();
                    return;
                }

                // Disable button and show loading
                sendOtpBtn.disabled = true;
                const originalText = sendOtpBtn.textContent;
                sendOtpBtn.textContent = 'Sending...';

            try {
                // Call API to send OTP (will create user if doesn't exist)
                const sendOtpUrl = '{{ route("frontend.login.send-otp") }}';
                const response = await fetch(sendOtpUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        mobile: validation.phone
                    })
                });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.success && result.data) {
                        loginState.phone = validation.phone;
                        loginState.otpVerified = false;
                        
                    // Show OTP input section
                    const otpRow = el('otpRow');
                    const otpSentBadge = el('otpSentBadge');
                    const otpInfoAlert = el('otpInfoAlert');
                    const otpInfoMessage = el('otpInfoMessage');
                    if (otpRow) otpRow.classList.remove('hidden');
                    if (otpSentBadge) otpSentBadge.classList.remove('hidden');
                    if (otpInfoAlert) otpInfoAlert.classList.remove('hidden');
                    
                    // Update message based on SMS status
                    const smsSent = result.sms_sent || result.data.sms_sent || false;
                    if (otpInfoMessage) {
                        if (smsSent) {
                            otpInfoMessage.innerHTML = '<strong>OTP Sent!</strong> Your verification code has been delivered to your Phone and WhatsApp. Enter the OTP to continue.';
                        } else {
                            otpInfoMessage.innerHTML = '<strong>OTP Generated!</strong> Your verification code has been generated. Enter the OTP to continue.';
                        }
                    }
                    
                    // Demo OTP removed for security - OTP is only sent via SMS
                        
                        // Disable phone input and send OTP button (keep visible but read-only)
                        phoneInput.disabled = true;
                        phoneInput.readOnly = true;
                        phoneInput.style.backgroundColor = '#f5f5f5';
                        phoneInput.style.cursor = 'not-allowed';
                        phoneInput.setAttribute('disabled', 'disabled');
                        
                        sendOtpBtn.disabled = true;
                        sendOtpBtn.setAttribute('disabled', 'disabled');
                        sendOtpBtn.textContent = 'OTP Sent';
                        sendOtpBtn.style.opacity = '0.6';
                        sendOtpBtn.style.cursor = 'not-allowed';
                        sendOtpBtn.style.pointerEvents = 'none';
                        
                        // Focus on OTP input
                        const inputOtp = el('inputOtp');
                        if (inputOtp) {
                            setTimeout(() => inputOtp.focus(), 100);
                        }
                        
                        // Start resend countdown timer
                        startResendCountdown();
                        
                        // Log success
                        if (result.data.is_new_user) {
                            console.log('✅ New user registered and OTP sent to:', validation.phone);
                        } else {
                            console.log('📱 OTP sent to existing user:', validation.phone);
                        }
                    } else {
                        const errorMsg = result.message || 'Failed to send OTP. Please try again.';
                        showError('err-phone', errorMsg);
                        await showAlert('error', 'Error', errorMsg);
                        sendOtpBtn.disabled = false;
                        sendOtpBtn.textContent = originalText;
                        phoneInput.focus();
                    }
                } catch (error) {
                    console.error('Error sending OTP:', error);
                    const errorMsg = error.message || 'Network error. Please check your connection and try again.';
                    showError('err-phone', errorMsg);
                    await showAlert('error', 'Network Error', errorMsg);
                    sendOtpBtn.disabled = false;
                    sendOtpBtn.textContent = originalText;
                    phoneInput.focus();
                }
            });

            // Resend OTP button handler
            const resendOtpBtn = el('resendOtp');
            if (resendOtpBtn) {
                resendOtpBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    
                    // Check if countdown is active
                    if (resendCountdown > 0) {
                        return; // Don't allow resend during countdown
                    }
                    
                    const phoneInput = el('loginPhone');
                    if (!phoneInput) return;
                    
                    const phone = phoneInput.value.trim();
                    const validation = validatePhone(phone);
                    
                    if (!validation.valid) {
                        await showAlert('warning', 'Phone Required', validation.message);
                        return;
                    }

                try {
                    const sendOtpUrl = '{{ route("frontend.login.send-otp") }}';
                    const response = await fetch(sendOtpUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            mobile: validation.phone
                        })
                    });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const result = await response.json();

                        if (result.success && result.data) {
                            const otpSentBadge = el('otpSentBadge');
                            const otpInfoAlert = el('otpInfoAlert');
                            const otpInfoMessage = el('otpInfoMessage');
                            if (otpSentBadge) otpSentBadge.classList.remove('hidden');
                            if (otpInfoAlert) otpInfoAlert.classList.remove('hidden');
                            
                            // Update message based on SMS status
                            const smsSent = result.sms_sent || result.data.sms_sent || false;
                            if (otpInfoMessage) {
                                if (smsSent) {
                                    otpInfoMessage.innerHTML = '<strong>OTP Sent!</strong> Your verification code has been delivered to your Phone and WhatsApp. Enter the OTP to continue.';
                                } else {
                                    otpInfoMessage.innerHTML = '<strong>OTP Generated!</strong> Your verification code has been generated. Enter the OTP to continue.';
                                }
                            }
                            
                            // Demo OTP removed for security - OTP is only sent via SMS
                            
                            const inputOtp = el('inputOtp');
                            if (inputOtp) {
                                inputOtp.value = '';
                                inputOtp.focus();
                            }
                            hideError('err-otp');
                            
                            // Restart countdown timer after successful resend
                            startResendCountdown();
                            
                            console.log('📱 Resent OTP');
                        } else {
                            await showAlert('error', 'Error', result.message || 'Failed to resend OTP.');
                        }
                    } catch (error) {
                        console.error('Error resending OTP:', error);
                        await showAlert('error', 'Network Error', 'Network error. Please try again.');
                    }
                });
            }

            // Verify OTP button handler
            const verifyOtpBtn = el('verifyOtpBtn');
            if (!verifyOtpBtn) {
                console.error('Verify OTP button not found!');
                return;
            }

            verifyOtpBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                const inputOtp = el('inputOtp');
                const phoneInput = el('loginPhone');
                
                if (!inputOtp || !phoneInput) {
                    console.error('OTP input or phone input not found!');
                    return;
                }

                const entered = inputOtp.value.trim();
                const phone = phoneInput.value.trim();
                
                // Clear previous error
                hideError('err-otp');
                
                // Validate OTP
                if (!entered) {
                    showError('err-otp', 'Please enter OTP');
                    inputOtp.focus();
                    return;
                }
                
                if (entered.length !== 6 || !/^[0-9]{6}$/.test(entered)) {
                    showError('err-otp', 'Please enter a valid 6-digit OTP');
                    inputOtp.focus();
                    return;
                }

                // Disable button and show loading
                verifyOtpBtn.disabled = true;
                const originalText = verifyOtpBtn.textContent;
                verifyOtpBtn.textContent = 'Verifying...';

            try {
                // Call API to verify OTP
                const verifyOtpUrl = '{{ route("frontend.verify-user-otp") }}';
                const response = await fetch(verifyOtpUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        mobile: phone,
                        otp: entered
                    })
                });

                // Parse JSON response (even for error status codes like 422)
                let result;
                try {
                    result = await response.json();
                } catch (parseError) {
                    // If JSON parsing fails, throw a generic error
                    throw new Error(`Network error. Please try again.`);
                }

                // Check if request was successful
                if (response.ok && result.success) {
                    // Clear resend countdown timer
                    if (resendTimerInterval) {
                        clearInterval(resendTimerInterval);
                        resendTimerInterval = null;
                    }
                    
                    // Update CSRF token if provided
                    if (result.csrf_token) {
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) meta.content = result.csrf_token;
                    }
                    
                    // OTP verified
                    loginState.otpVerified = true;
                    const otpRow = el('otpRow');
                    const otpSentBadge = el('otpSentBadge');
                    if (otpRow) otpRow.classList.add('hidden');
                    if (otpSentBadge) {
                        otpSentBadge.innerText = 'Verified ✓';
                        otpSentBadge.classList.remove('hidden');
                    }
                    
                    // Disable OTP input
                    inputOtp.disabled = true;
                    verifyOtpBtn.disabled = true;
                    verifyOtpBtn.textContent = 'Verified';
                    
                    // Show success modal and redirect
                    const loginSuccessModal = el('loginSuccessModal');
                    if (loginSuccessModal && typeof bootstrap !== 'undefined') {
                        const modal = new bootstrap.Modal(loginSuccessModal);
                        modal.show();
                    }
                    
                    // Redirect to booking dashboard after 1.5 seconds
                    setTimeout(function() {
                        window.location.href = '{{ route("frontend.booking-dashboard") }}';
                    }, 1500);
                } else {
                    // Show error message from server response
                    const errorMsg = result.message || 'Invalid OTP. Please try again.';
                    showError('err-otp', errorMsg);
                    verifyOtpBtn.disabled = false;
                    verifyOtpBtn.textContent = originalText;
                    inputOtp.focus();
                }
            } catch (error) {
                console.error('Error verifying OTP:', error);
                const errorMsg = error.message || 'Network error. Please try again.';
                showError('err-otp', errorMsg);
                verifyOtpBtn.disabled = false;
                verifyOtpBtn.textContent = originalText;
                inputOtp.focus();
            }
            });
            
            // Clear errors on input
            const phoneInputField = el('loginPhone');
            if (phoneInputField) {
                phoneInputField.addEventListener('input', function() {
                    hideError('err-phone');
                });
                
                phoneInputField.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        sendOtpBtn.click();
                    }
                });
            }
            
            const inputOtpField = el('inputOtp');
            if (inputOtpField) {
                inputOtpField.addEventListener('input', function() {
                    hideError('err-otp');
                });
                
                inputOtpField.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        verifyOtpBtn.click();
                    }
                });
            }
        });
    </script>
@endsection 

