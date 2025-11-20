/**********************
     State & helpers
    ***********************/
const setupContext = window.SetupContext || {};
const stepNumbers = setupContext.steps || {};
const stepKeyByNumber = {};
Object.entries(stepNumbers).forEach(([key, value]) => {
    if (value) stepKeyByNumber[value] = key;
});
const enabledStepNumbers = Object.values(stepNumbers).filter(Boolean).sort((a, b) => a - b);
const initialStepNumber = setupContext.initialStep || enabledStepNumbers[0] || 1;

const state = {
    step: initialStepNumber,
    currentStepKey: stepKeyByNumber[initialStepNumber] || null,
    highestStepUnlocked: initialStepNumber,
    otp: 524163,
    otpVerified: setupContext.authenticated ? true : false, // Only verified if already authenticated
    activePropertyTab: null,
    propertyTypeInitialized: false, // Track if property type has been set initially
    contactLocked: setupContext.authenticated ? true : false,
    currentPrice: 0,
    bookingId: null,
    returningFromPayment: false,
    isAuthenticated: setupContext.authenticated || false,
    attemptedOtherTabs: false, // Track if user tried to access other tabs without verification
};

// Resend OTP countdown timer
let resendCountdown = null;
let resendTimerInterval = null;

const cashfreeSelectors = {};

const cashfreeState = {
    initializing: false,
    orderId: null,
    paymentSessionId: null,
    amount: 0,
    currency: 'INR',
    status: 'NOT_STARTED',
    completed: false,
    lastSummary: null,
    autoAttempted: false,
};

const urlParams = new URLSearchParams(window.location.search);
const bookingIdParam = urlParams.get('booking_id');
const openPaymentParam = urlParams.get('open_payment') === '1';
const orderIdParam = urlParams.get('order_id');

if (bookingIdParam) {
    state.bookingId = bookingIdParam;
    const hiddenBookingInput = document.getElementById('bookingId');
    if (hiddenBookingInput) {
        hiddenBookingInput.value = bookingIdParam;
    }
}

if (openPaymentParam && bookingIdParam) {
    const paymentStep = getStepNumber('payment');
    if (paymentStep) {
        state.step = paymentStep;
        state.currentStepKey = getStepKeyByNumber(paymentStep);
        state.highestStepUnlocked = paymentStep;
    }
    state.returningFromPayment = true;
}

if (orderIdParam) {
    cashfreeState.orderId = orderIdParam;
}

const el = id => document.getElementById(id);
function safeInputValue(id, fallback = '') {
    const node = el(id);
    if (node && typeof node.value !== 'undefined') {
        return (node.value ?? '').toString().trim();
    }
    return typeof fallback === 'function' ? fallback() : (fallback ?? '');
}
function getContactNameValue() {
    return safeInputValue('inputName', () => (setupContext.user?.name || '').trim());
}
function getContactPhoneValue() {
    return safeInputValue('inputPhone', () => (setupContext.user?.mobile || '').trim());
}
const propertyCard = el('propertyCard');
const addressCard = el('addressCard');
const propertyReadOnlyNotice = el('propertyReadOnlyNotice');
const addressReadOnlyNotice = el('addressReadOnlyNotice');
const propertyInputs = ['resArea', 'comArea', 'othArea', 'othDesc'];
const addressInputs = ['addrHouse', 'addrBuilding', 'addrPincode', 'addrFull'];
const appUrlMeta = document.querySelector('meta[name="app-url"]');
const baseUrl = (appUrlMeta?.content || window.location.origin || '').replace(/\/+$/, '');
let readOnlyBypass = false;
const contactStepNumber = getStepNumber('contact');
const propertyStepNumber = getStepNumber('property');

function getStepNumber(key) {
    return stepNumbers[key] || null;
}

function getStepKeyByNumber(num) {
    return stepKeyByNumber[num] || null;
}

function hasStep(key) {
    return Boolean(getStepNumber(key));
}

function buildUrl(path = '') {
    if (!path) return baseUrl;
    if (/^https?:\/\//i.test(path)) return path;
    const normalized = path.replace(/^\/+/, '');
    return baseUrl ? `${baseUrl}/${normalized}` : `/${normalized}`;
}

function runWithReadOnlyBypass(fn) {
    const prev = readOnlyBypass;
    readOnlyBypass = true;
    try {
        fn();
    } finally {
        readOnlyBypass = prev;
    }
}

// Helper functions for form validation and error display
function showFieldError(fieldId, errorId, message = null) {
    const field = el(fieldId);
    const errorEl = el(errorId);
    
    if (field) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
    }
    
    if (errorEl) {
        if (message) errorEl.textContent = message;
        errorEl.style.display = 'block';
        errorEl.classList.add('show');
    }
}

function hideFieldError(fieldId, errorId) {
    const field = el(fieldId);
    const errorEl = el(errorId);
    
    if (field) {
        field.classList.remove('is-invalid');
        // Don't add is-valid automatically - let user input trigger it
    }
    
    if (errorEl) {
        errorEl.style.display = 'none';
        errorEl.classList.remove('show');
    }
}

function markFieldValid(fieldId) {
    const field = el(fieldId);
    if (field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    }
}

function showPillContainerError(containerId, errorId, message = null) {
    const container = el(containerId);
    const errorEl = el(errorId);
    
    if (container) {
        container.classList.add('has-error');
    }
    
    if (errorEl) {
        if (message) errorEl.textContent = message;
        errorEl.style.display = 'block';
        errorEl.classList.add('show');
    }
}

function hidePillContainerError(containerId, errorId) {
    const container = el(containerId);
    const errorEl = el(errorId);
    
    if (container) {
        container.classList.remove('has-error');
    }
    
    if (errorEl) {
        errorEl.style.display = 'none';
        errorEl.classList.remove('show');
    }
}

function clearAllFieldErrors() {
    // Clear all error classes and hide error messages
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.is-valid').forEach(el => {
        el.classList.remove('is-valid');
    });
    document.querySelectorAll('.has-error').forEach(el => {
        el.classList.remove('has-error');
    });
    document.querySelectorAll('.error').forEach(el => {
        el.style.display = 'none';
        el.classList.remove('show');
    });
}

function canMutateForm() {
    return true;
}

function updateReadOnlyUI() {
    const ro = false;
    if (propertyCard) propertyCard.classList.toggle('form-readonly', ro);
    if (addressCard) addressCard.classList.toggle('form-readonly', ro);
    if (propertyReadOnlyNotice) propertyReadOnlyNotice.classList.toggle('d-none', !ro);
    if (addressReadOnlyNotice) addressReadOnlyNotice.classList.toggle('d-none', !ro);
    propertyInputs.forEach(id => {
        const node = el(id);
        if (node) node.readOnly = ro;
    });
    addressInputs.forEach(id => {
        const node = el(id);
        if (node) node.readOnly = ro;
    });
}

function updateProgress() {
    const totalSegments = enabledStepNumbers.length > 1 ? enabledStepNumbers.length - 1 : 1;
    const currentIndex = Math.max(enabledStepNumbers.indexOf(state.step), 0);
    const percent = Math.round((currentIndex / totalSegments) * 100);
    el('progressBar').style.width = percent + '%';
    document.querySelectorAll('.step-btn').forEach(b => b.classList.remove('active'));
    const btn = document.querySelector(`.step-btn[data-step="${state.step}"]`);
    if (btn) btn.classList.add('active');
}

function showStep(n) {
    if (!n) return;
    state.step = n;
    state.currentStepKey = getStepKeyByNumber(n);
    state.highestStepUnlocked = Math.max(state.highestStepUnlocked ?? n, n);
    document.querySelectorAll('.step-pane').forEach(p => p.classList.add('hidden'));
    const pane = el(`step-${n}`);
    if (pane) pane.classList.remove('hidden');
    updateProgress();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    if (state.currentStepKey === 'payment') {
        prepareCashfreeStep();
    }
}

async function goToStep(key) {
    const num = getStepNumber(key);
    if (!num) return;
    
    const targetKey = key;
    const currentKey = state.currentStepKey;
    
    // If going to same step, do nothing
    if (num === state.step) {
        return;
    }
    
    // If going backward to an already unlocked step, allow it without validation
    // BUT: Always check address completion before allowing navigation to verify
    // Also check OTP verification for property/address/verify/payment steps
    if (num <= (state.highestStepUnlocked ?? state.step)) {
        // Check OTP verification for steps other than contact
        if (targetKey !== 'contact' && !state.otpVerified) {
            const contactStepNum = getStepNumber('contact');
            if (contactStepNum) {
                await showSweetAlert('info', 'First Verify Account', 'Please verify your account by completing the Contact details & verification step first.');
                showStep(contactStepNum);
                return;
            }
        }
        // Special check: If trying to go to verify, ensure address is completed
        if (targetKey === 'verify') {
            if (!isAddressStepCompleted()) {
                await showSweetAlert('warning', 'Address Required', 'Please complete the Address step before viewing the verification summary.');
                // Navigate to address step instead
                const addressStepNum = getStepNumber('address');
                if (addressStepNum) {
                    showStep(addressStepNum);
                }
                return;
            }
        }
        // Special check: If trying to go to payment, ensure address is completed
        if (targetKey === 'payment') {
            if (!isAddressStepCompleted()) {
                await showSweetAlert('warning', 'Address Required', 'Please complete the Address step before proceeding to payment.');
                // Navigate to address step instead
                const addressStepNum = getStepNumber('address');
                if (addressStepNum) {
                    showStep(addressStepNum);
                }
                return;
            }
        }
        showStep(num);
        // If going to verify from a previous step, refresh summary
        if (targetKey === 'verify') {
            await fetchAndRenderSummary();
        }
        return;
    }
    
    // If going forward, validate current step first
    let validationPassed = true;
    
    if (currentKey === 'property') {
        validationPassed = await validateAndSavePropertyStep();
    } else if (currentKey === 'address') {
        validationPassed = await validateAndSaveAddressStep();
    } else if (currentKey === 'contact' && num > state.step) {
        validationPassed = validateContactStep();
        if (validationPassed) {
            lockContactSection();
        }
    }
    
    if (!validationPassed) {
        return;
    }
    
    // Special check: If trying to go to verify, ensure address is completed
    if (targetKey === 'verify') {
        if (!isAddressStepCompleted()) {
            await showSweetAlert('warning', 'Address Required', 'Please complete the Address step before viewing the verification summary.');
            // Navigate to address step instead
            const addressStepNum = getStepNumber('address');
            if (addressStepNum) {
                showStep(addressStepNum);
            }
            return;
        }
    }
    
    // Special check: If trying to go to payment, ensure address is completed
    if (targetKey === 'payment') {
        if (!isAddressStepCompleted()) {
            await showSweetAlert('warning', 'Address Required', 'Please complete the Address step before proceeding to payment.');
            // Navigate to address step instead
            const addressStepNum = getStepNumber('address');
            if (addressStepNum) {
                showStep(addressStepNum);
            }
            return;
        }
    }
    
    // Update highest step unlocked if going forward
    if (num > state.step) {
        state.highestStepUnlocked = Math.max(state.highestStepUnlocked ?? state.step, num);
    }
    
    showStep(num);
    
    // If navigating to verify step, always fetch fresh summary
    if (targetKey === 'verify') {
        await fetchAndRenderSummary();
    }
}

function hydrateAuthUser() {
    if (!setupContext.authenticated || !setupContext.user) return;
    const { name, mobile } = setupContext.user;
    if (el('inputName')) el('inputName').value = name || '';
    if (el('inputPhone')) el('inputPhone').value = mobile || '';
    const badge = el('otpSentBadge');
    if (badge) {
        badge.classList.remove('hidden');
        badge.textContent = 'Verified ✓';
    }
    const toStep2Btn = el('toStep2');
    if (toStep2Btn) {
        toStep2Btn.disabled = false;
    }
}

function isUserAuthenticated() {
    return Boolean(state.isAuthenticated || setupContext.authenticated);
}

function applyOwnerTypeSelection(value) {
    const nodes = document.querySelectorAll('[data-group="ownerType"]');
    nodes.forEach(node => {
        if (node.dataset.value === value) node.classList.add('active');
        else node.classList.remove('active');
    });
    const hiddenOwner = el('choice_ownerType');
    if (hiddenOwner) hiddenOwner.value = value || '';
}

function setGroupValue(group, value) {
    if (!group) return;
    document.querySelectorAll(`[data-group="${group}"]`).forEach(node => {
        if (value && node.dataset.value == value) node.classList.add('active');
        else node.classList.remove('active');
    });
    const hiddenMap = {
        ownerType: 'choice_ownerType',
        resType: 'choice_resType',
        resFurnish: 'choice_resFurnish',
        resSize: 'choice_resSize',
        comType: 'choice_comType',
        comFurnish: 'choice_comFurnish',
        othLooking: 'choice_othLooking',
    };
    const hidden = el(hiddenMap[group]);
    if (hidden) hidden.value = value || '';
}

function setInputValue(id, value) {
    const input = el(id);
    if (input) input.value = value ?? '';
}

function applyAddressFromBooking(booking) {
    if (!booking || !booking.address) return;
    setInputValue('addrHouse', booking.address.house_number || '');
    setInputValue('addrBuilding', booking.address.building_name || '');
    setInputValue('addrFull', booking.address.full_address || '');
    setInputValue('addrPincode', booking.address.pincode || '');
    // City is static - always Ahmedabad, so no need to change it
    // const citySelect = el('addrCity');
    // if (citySelect) {
    //     citySelect.value = 'Ahmedabad';
    // }
}


// init
showStep(state.step);
// Don't show any tab initially - wait for user to select property type
hideAllPropertyTabs();
hydrateAuthUser();
updateReadOnlyUI();

// Dynamic data rendering from SetupData
const setupData = window.SetupData || { types: [], states: [], cities: [] };

function renderTypePills(containerId, groupKey, subTypes) {
    const wrap = document.getElementById(containerId);
    if (!wrap) return;
    wrap.innerHTML = '';
    (subTypes || []).forEach(st => {
        const div = document.createElement('div');
        div.className = 'top-pill';
        div.dataset.group = groupKey;
        div.dataset.value = st.name;
        div.textContent = st.name;
        div.onclick = () => selectCard(div);
        wrap.appendChild(div);
    });
}

function getSubTypesByTypeName(typeName) {
    const t = (setupData.types || []).find(x => x.name === typeName);
    return t?.sub_types || t?.subTypes || [];
}

function initDynamicPropertyPills() {
    // Residential
    renderTypePills('resTypesContainer', 'resType', getSubTypesByTypeName('Residential'));
    // Commercial
    renderTypePills('comTypesContainer', 'comType', getSubTypesByTypeName('Commercial'));
    // Other
    renderTypePills('othTypesContainer', 'othLooking', getSubTypesByTypeName('Other'));
}

function initCitySelect() {
    const citySel = document.getElementById('addrCity');
    if (!citySel) return;
    // Set static city to Ahmedabad (already in HTML, just ensure it's selected)
    citySel.value = 'Ahmedabad';
    citySel.disabled = false;
    // No dynamic loading - using static option from HTML
}

initDynamicPropertyPills();
initCitySelect();

/**********************
 Step 1: OTP flow with API integration
***********************/
// Helper to get CSRF token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ||
           document.querySelector('input[name="_token"]')?.value || '';
}

async function postJson(url, data) {
    const resp = await fetch(buildUrl(url), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    });
    return resp.json();
}

const sendOtpBtn = el('sendOtpBtn');
if (sendOtpBtn) sendOtpBtn.addEventListener('click', async () => {
    // validate name & phone
    const name = el('inputName').value.trim();
    const phone = el('inputPhone').value.trim();
    let ok = true;
    if (!name) {
        el('err-name').style.display = 'block';
        ok = false;
    } else el('err-name').style.display = 'none';
    if (!/^[0-9]{10}$/.test(phone)) {
        el('err-phone').style.display = 'block';
        ok = false;
    } else el('err-phone').style.display = 'none';
    if (!ok) return;

    // Disable button and show loading
    const sendBtn = el('sendOtpBtn');
    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending...';

    try {
        // Call API to check user and send OTP
        const response = await fetch(buildUrl('/frontend/check-user-send-otp'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                mobile: phone,
                name: name
            })
        });

        const result = await response.json();

        if (result.success && result.data) {
            state.otpVerified = false;
            el('otpRow').classList.remove('hidden');
            el('otpSentBadge').classList.remove('hidden');
            el('toStep2').disabled = true;
            
            // Show demo OTP if available (for development)
            if (result.data.otp) {
                const demoOtpEl = el('demoOtp');
                if (demoOtpEl) {
                    demoOtpEl.classList.remove('hidden');
                    const demoOtpCodeEl = el('demoOtpCode');
                    if (demoOtpCodeEl) {
                        demoOtpCodeEl.innerText = result.data.otp;
                    }
                }
            }
            
            // Start resend countdown timer
            startResendCountdown();
            
            // Optional status log without OTP content
            if (result.data.is_new_user) {
                console.log('✅ New user created and OTP sent');
            } else {
                console.log('👤 Existing user - OTP sent');
            }
        } else {
            await showSweetAlert('error', 'Error', result.message || 'Failed to send OTP. Please try again.');
        }
    } catch (error) {
        console.error('Error sending OTP:', error);
        await showSweetAlert('error', 'Network Error', 'Network error. Please check your connection and try again.');
    } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = 'Send OTP';
    }
});

const resendOtpBtn = el('resendOtp');
if (resendOtpBtn) resendOtpBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    
    // Check if countdown is active
    if (resendCountdown > 0) {
        return; // Don't allow resend during countdown
    }
    
    const phone = el('inputPhone').value.trim();
    const name = el('inputName').value.trim();
    
    if (!phone) { 
        await showSweetAlert('warning', 'Phone Required', 'Enter phone to resend OTP'); 
        return; 
    }
    if (!name) {
        await showSweetAlert('warning', 'Name Required', 'Enter name to resend OTP');
        return;
    }

    try {
        const response = await fetch(buildUrl('/frontend/check-user-send-otp'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                mobile: phone,
                name: name
            })
        });

        const result = await response.json();

        if (result.success && result.data) {
            el('otpSentBadge').classList.remove('hidden');
            
            // Show demo OTP if available (for development)
            if (result.data.otp) {
                const demoOtpEl = el('demoOtp');
                if (demoOtpEl) {
                    demoOtpEl.classList.remove('hidden');
                    const demoOtpCodeEl = el('demoOtpCode');
                    if (demoOtpCodeEl) {
                        demoOtpCodeEl.innerText = result.data.otp;
                    }
                }
            }
            
            // Clear OTP input and restart countdown
            const inputOtp = el('inputOtp');
            if (inputOtp) {
                inputOtp.value = '';
                inputOtp.focus();
            }
            el('err-otp').style.display = 'none';
            
            // Restart countdown timer after successful resend
            startResendCountdown();
            
            console.log('📱 Resent OTP');
        } else {
            await showSweetAlert('error', 'Error', result.message || 'Failed to resend OTP.');
        }
    } catch (error) {
        console.error('Error resending OTP:', error);
        await showSweetAlert('error', 'Network Error', 'Network error. Please try again.');
    }
});

const verifyOtpBtn = el('verifyOtpBtn');
if (verifyOtpBtn) verifyOtpBtn.addEventListener('click', async () => {
    const entered = el('inputOtp').value.trim();
    const phone = el('inputPhone').value.trim();

    if (!entered || entered.length !== 6) {
        el('err-otp').style.display = 'block';
        el('err-otp').textContent = 'Please enter 6-digit OTP';
        return;
    }

    // Disable button and show loading
    const verifyBtn = el('verifyOtpBtn');
    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Verifying...';

    try {
        // Call API to verify OTP
        const response = await fetch(buildUrl('/frontend/verify-user-otp'), {
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

        const result = await response.json();

        if (result.success) {
            // Clear resend countdown timer
            if (resendTimerInterval) {
                clearInterval(resendTimerInterval);
                resendTimerInterval = null;
            }
            
            if (result.csrf_token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.content = result.csrf_token;
            }
            el('err-otp').style.display = 'none';
            state.otpVerified = true;
            state.attemptedOtherTabs = false; // Reset flag when OTP is verified
            el('otpRow').classList.add('hidden');
            el('otpSentBadge').classList.remove('hidden');
            el('otpSentBadge').innerText = 'Verified ✓';
            el('toStep2').disabled = false;
            console.log('✅ OTP verified successfully:', result.user);
            state.isAuthenticated = true;
            setupContext.authenticated = true;
            updateReadOnlyUI();
            lockContactSection();
            // Unlock and navigate to property step
            const propertyStepNumber = getStepNumber('property');
            if (propertyStepNumber) {
                state.highestStepUnlocked = Math.max(state.highestStepUnlocked ?? state.step, propertyStepNumber);
                showStep(propertyStepNumber);
            }
        } else {
            el('err-otp').style.display = 'block';
            el('err-otp').textContent = result.message || 'Invalid OTP';
        }
    } catch (error) {
        console.error('Error verifying OTP:', error);
        el('err-otp').style.display = 'block';
        el('err-otp').textContent = 'Network error. Please try again.';
    } finally {
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify OTP';
    }
});

const toStep2Btn = el('toStep2');
if (toStep2Btn) toStep2Btn.addEventListener('click', async () => {
    if (!state.otpVerified) {
        await showSweetAlert('warning', 'OTP Verification Required', 'Please verify OTP before proceeding.');
        return;
    }
    lockContactSection();
    const nextKey = 'property';
    const nextNumber = getStepNumber(nextKey);
    if (nextNumber) {
        state.highestStepUnlocked = Math.max(state.highestStepUnlocked ?? state.step, nextNumber);
        showStep(nextNumber);
    }
});

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

const skipContactBtn = el('skipContact');
if (skipContactBtn) skipContactBtn.addEventListener('click', () => {
    // Clear resend countdown timer
    if (resendTimerInterval) {
        clearInterval(resendTimerInterval);
        resendTimerInterval = null;
    }
    resendCountdown = null;
    
    // clear contact fields
    el('inputName').value = '';
    el('inputPhone').value = '';
    el('inputOtp').value = '';
    state.otp = null;
    state.otpVerified = false;
    el('otpRow').classList.add('hidden');
    el('otpSentBadge').classList.add('hidden');
    el('toStep2').disabled = true;
    state.contactLocked = false;
    
    // Reset resend button
    const resendOtpBtn = el('resendOtp');
    if (resendOtpBtn) {
        resendOtpBtn.style.pointerEvents = 'auto';
        resendOtpBtn.style.opacity = '1';
        resendOtpBtn.textContent = 'Resend';
    }
    
    ['btn-step-1', 'backToContact', 'sendOtpBtn', 'verifyOtpBtn', 'resendOtp', 'skipContact'].forEach(id => {
        const node = el(id);
        if (node) node.disabled = false;
    });
    ['inputName', 'inputPhone', 'inputOtp'].forEach(id => {
        const input = el(id);
        if (input) input.disabled = false;
    });
});

/**********************
 Step 2: property selections
***********************/
function hideAllPropertyTabs() {
    el('tab-res').style.display = 'none';
    el('tab-com').style.display = 'none';
    el('tab-oth').style.display = 'none';
    // Remove active state from all property type pills
    ['pillResidential', 'pillCommercial', 'pillOther'].forEach(id => {
        const pill = el(id);
        if (pill) pill.classList.remove('active');
    });
    // Clear main property type hidden field
    el('mainPropertyType').value = '';
    state.activePropertyTab = null;
}

function switchMainTab(key) {
    if (!key) {
        hideAllPropertyTabs();
        state.propertyTypeInitialized = false;
        return;
    }
    state.activePropertyTab = key;
    el('tab-res').style.display = (key === 'res') ? 'block' : 'none';
    el('tab-com').style.display = (key === 'com') ? 'block' : 'none';
    el('tab-oth').style.display = (key === 'oth') ? 'block' : 'none';
    ['pillResidential', 'pillCommercial', 'pillOther'].forEach(id => {
        const pill = el(id);
        if (pill) pill.classList.remove('active');
    });
    if (key === 'res') {
        const pill = el('pillResidential');
        if (pill) pill.classList.add('active');
    }
    if (key === 'com') {
        const pill = el('pillCommercial');
        if (pill) pill.classList.add('active');
    }
    if (key === 'oth') {
        const pill = el('pillOther');
        if (pill) pill.classList.add('active');
    }
    
    // Update main property type hidden field
    const typeMap = { 'res': 'Residential', 'com': 'Commercial', 'oth': 'Other' };
    el('mainPropertyType').value = typeMap[key] || 'Residential';
}

function selectCard(dom) {
    if (!canMutateForm()) return;
    const group = dom.dataset.group;
    document.querySelectorAll(`[data-group="${group}"]`).forEach(n => n.classList.remove('active'));
    dom.classList.add('active');
    const v = dom.dataset.value;
    if (group === 'resType') {
        el('choice_resType').value = v;
        // Clear error when residential sub type is selected
        hidePillContainerError('resTypeContainer', 'err-resType');
    }
    if (group === 'comType') {
        el('choice_comType').value = v;
        // Clear error when commercial sub type is selected
        hidePillContainerError('comTypeContainer', 'err-comType');
    }
}

function resetAddressFields(){
    ['addrHouse','addrBuilding','addrPincode','addrFull'].forEach(id=>{
        const input = el(id);
        if(input) input.value = '';
    });
    ['err-addrHouse','err-addrBuilding','err-addrPincode','err-addrFull'].forEach(id=>{
        if(el(id)) el(id).style.display='none';
    });
}

// Check if any property data has been filled
function hasPropertyDataFilled() {
    if (!state.activePropertyTab) return false;
    
    // Check based on active tab
    if (state.activePropertyTab === 'res') {
        const resType = el('choice_resType')?.value;
        const resFurnish = el('choice_resFurnish')?.value;
        const resSize = el('choice_resSize')?.value;
        const resArea = el('resArea')?.value?.trim();
        return !!(resType || resFurnish || resSize || resArea);
    } else if (state.activePropertyTab === 'com') {
        const comType = el('choice_comType')?.value;
        const comFurnish = el('choice_comFurnish')?.value;
        const comArea = el('comArea')?.value?.trim();
        return !!(comType || comFurnish || comArea);
    } else if (state.activePropertyTab === 'oth') {
        const othLooking = el('choice_othLooking')?.value;
        const othDesc = el('othDesc')?.value?.trim();
        const othArea = el('othArea')?.value?.trim();
        return !!(othLooking || othDesc || othArea);
    }
    
    return false;
}

// Check if address data has been filled
function hasAddressDataFilled() {
    const h = el('addrHouse')?.value?.trim();
    const b = el('addrBuilding')?.value?.trim();
    const p = el('addrPincode')?.value?.trim();
    const f = el('addrFull')?.value?.trim();
    return !!(h || b || p || f);
}

function clearAllPropertySelections() {
    // Clear property-related selections
    ['choice_resType', 'choice_resFurnish', 'choice_resSize', 'choice_comType', 'choice_comFurnish', 'choice_othLooking'].forEach(id => {
        if (el(id)) el(id).value = '';
    });
    ['resArea', 'comArea', 'othArea', 'othDesc'].forEach(id => {
        if (el(id)) el(id).value = '';
    });
    document.querySelectorAll('[data-group="resType"],[data-group="resFurnish"],[data-group="resSize"],[data-group="comType"],[data-group="comFurnish"],[data-group="othLooking"]').forEach(node => {
        node.classList.remove('active');
    });
    ['err-resArea', 'err-comArea', 'err-othDesc', 'err-othArea', 'err-othLooking'].forEach(id => {
        if (el(id)) el(id).style.display = 'none';
    });
    
    // Also clear address fields when property type changes
    resetAddressFields();
    
    // Note: Billing details (firm name, GST) are NOT cleared - they persist when property type changes
    updatePriceDisplay();
}

function lockContactSection() {
    if (state.contactLocked) return;
    state.contactLocked = true;
    ['inputName', 'inputPhone', 'inputOtp'].forEach(id => {
        const input = el(id);
        if (input) input.disabled = true;
    });
    ['sendOtpBtn', 'verifyOtpBtn', 'resendOtp', 'skipContact'].forEach(id => {
        const btn = el(id);
        if (btn) btn.disabled = true;
    });
    const contactStepBtn = el('btn-step-1');
    if (contactStepBtn) contactStepBtn.disabled = true;
    const backBtn = el('backToContact');
    if (backBtn) backBtn.disabled = true;
}

async function handlePropertyTabChange(key) {
    if (!canMutateForm()) return;
    
    // Check if property type was already set, user is trying to change it, AND there's actual data filled
    if (state.activePropertyTab && state.activePropertyTab !== key && (hasPropertyDataFilled() || hasAddressDataFilled())) {
        // Get current property type name
        const typeMap = { 'res': 'Residential', 'com': 'Commercial', 'oth': 'Other' };
        const currentType = typeMap[state.activePropertyTab] || 'Current';
        const newType = typeMap[key] || 'New';
        
        // Build message based on what data exists
        let messageParts = [];
        messageParts.push(`You are changing Property Type from <strong>${currentType}</strong> to <strong>${newType}</strong>.<br><br>`);
        
        if (hasPropertyDataFilled()) {
            messageParts.push(`This will clear the following property details:<br>
                • Property Sub Type<br>
                • Furnish Type<br>
                • Size (BHK/RK)<br>
                • Super Built-up Area<br>`);
        }
        
        if (hasAddressDataFilled()) {
            if (hasPropertyDataFilled()) {
                messageParts.push(`<br>This will also clear the following address details:<br>
                    • House / Office No.<br>
                    • Society / Building Name<br>
                    • Pincode<br>
                    • Full Address<br>`);
            } else {
                messageParts.push(`This will clear the following address details:<br>
                    • House / Office No.<br>
                    • Society / Building Name<br>
                    • Pincode<br>
                    • Full Address<br>`);
            }
        }
        
        messageParts.push(`<br><strong>Note:</strong> Your billing details (Company Name, GST No) will be preserved.`);
        
        // Show confirmation dialog only if there's data that will be lost
        const result = await Swal.fire({
            icon: 'warning',
            title: 'Change Property Type?',
            html: `<div style="text-align: left; padding: 10px 0; color: #333; font-size: 14px; line-height: 1.8;">
                ${messageParts.join('')}
            </div>`,
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Change It',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'sweetalert-popup',
                title: 'sweetalert-title',
                content: 'sweetalert-content',
                confirmButton: 'sweetalert-confirm-btn'
            },
            buttonsStyling: true,
            allowOutsideClick: true,
            allowEscapeKey: true
        });
        
        // If user cancels, don't change property type
        if (!result.isConfirmed) {
            return;
        }
    }
    
    // Proceed with property type change
    clearAllPropertySelections();
    switchMainTab(key);
    // Mark property type as initialized
    state.propertyTypeInitialized = true;
    // Clear error when property type is selected
    hidePillContainerError('propertyTypeContainer', 'err-propertyType');
    updatePriceDisplay();
}

function getActiveAreaValue() {
    if (!state.activePropertyTab) return '';
    if (state.activePropertyTab === 'com') return el('comArea').value.trim();
    if (state.activePropertyTab === 'oth') return el('othArea').value.trim();
    return el('resArea').value.trim();
}

function calculateDynamicPrice(areaValue) {
    const area = Number(areaValue);
    if (!area || area <= 0) return 0;
    const baseArea = 1500;
    const basePrice = 599;
    const extraBlockPrice = 200;
    let price = basePrice;
    if (area > baseArea) {
        const extra = area - baseArea;
        const blocks = Math.ceil(extra / 500);
        price += blocks * extraBlockPrice;
    }
    return price;
}

function updatePriceDisplay() {
    const areaValue = getActiveAreaValue();
    const price = calculateDynamicPrice(areaValue);
    state.currentPrice = price;
    const priceNode = el('priceDisplay');
    if (priceNode) {
        priceNode.innerText = price ? `₹${price.toLocaleString('en-IN')}` : '₹0';
    }
    updateCashfreeAmountDisplay(price);
}

function selectChip(dom) {
    if (!canMutateForm()) return;
    const group = dom.dataset.group;
    document.querySelectorAll(`[data-group="${group}"]`).forEach(n => n.classList.remove('active'));
    dom.classList.add('active');
    const v = dom.dataset.value;
    if (group === 'resFurnish') {
        el('choice_resFurnish').value = v;
        // Clear error when residential furnish is selected
        hidePillContainerError('resFurnishContainer', 'err-resFurnish');
    }
    if (group === 'resSize') {
        el('choice_resSize').value = v;
        // Clear error when residential size is selected
        hidePillContainerError('resSizeContainer', 'err-resSize');
    }
    if (group === 'comFurnish') {
        el('choice_comFurnish').value = v;
        // Clear error when commercial furnish is selected
        hidePillContainerError('comFurnishContainer', 'err-comFurnish');
    }
}

['resArea', 'comArea', 'othArea'].forEach(id => {
    const input = el(id);
    if (input) {
        input.addEventListener('input', function() {
            updatePriceDisplay();
            // Clear error when user starts typing
            if (this.value.trim() && Number(this.value.trim()) > 0) {
                hideFieldError(id, 'err-' + id);
                markFieldValid(id);
            }
            // If user is on payment tab, update the payment amount display immediately
            if (state.currentStepKey === 'payment') {
                const areaValue = getActiveAreaValue();
                const latestPrice = calculateDynamicPrice(areaValue);
                updateCashfreeAmountDisplay(latestPrice);
            }
        });
    }
});
updatePriceDisplay();

// Add real-time validation for billing fields
['firmName', 'gstNo'].forEach(id => {
    const input = el(id);
    if (input) {
        input.addEventListener('input', function() {
            // Clear error when user starts typing
            if (this.value.trim()) {
                hideFieldError(id, 'err-' + id);
                markFieldValid(id);
            }
        });
    }
});

// Add real-time validation for address fields
['addrHouse', 'addrBuilding', 'addrPincode', 'addrFull'].forEach(id => {
    const input = el(id);
    if (input) {
        input.addEventListener('input', function() {
            // Clear error when user starts typing
            const value = this.value.trim();
            if (value) {
                // Special validation for pincode
                if (id === 'addrPincode') {
                    if (/^[0-9]{6}$/.test(value)) {
                        hideFieldError(id, 'err-' + id);
                        markFieldValid(id);
                    }
                } else {
                    hideFieldError(id, 'err-' + id);
                    markFieldValid(id);
                }
            }
        });
    }
});

function topPillClick(dom) {
    if (!canMutateForm()) return;
    const group = dom.dataset.group;
    document.querySelectorAll(`[data-group="${group}"]`).forEach(n => n.classList.remove('active'));
    dom.classList.add('active');
    if (group === 'othLooking') {
        el('choice_othLooking').value = dom.dataset.value;
        // Clear error when other looking option is selected
        hidePillContainerError('othLookingContainer', 'err-othLooking');
        hideFieldError('othDesc', 'err-othDesc');
    }
    if (group === 'ownerType') {
        // Only update owner type value, don't clear other fields
        el('choice_ownerType').value = dom.dataset.value;
        // Clear error when owner type is selected
        hidePillContainerError('ownerTypeContainer', 'err-ownerType');
    }
}

const backToContactBtn = el('backToContact');
if (backToContactBtn) backToContactBtn.addEventListener('click', async () => {
    if (state.contactLocked) {
        await showSweetAlert('info', 'Contact Locked', 'Contact details are locked after verification.');
        return;
    }
    goToStep('contact');
});

// Different Billing Name checkbox - show/hide billing details
const differentBillingNameCheckbox = el('differentBillingName');
if (differentBillingNameCheckbox) {
    differentBillingNameCheckbox.addEventListener('change', function() {
        const billingDetailsRow = el('billingDetailsRow');
        const firmNameInput = el('firmName');
        const gstNoInput = el('gstNo');
        
        if (this.checked) {
            // Show billing details and make required
            if (billingDetailsRow) {
                // Remove inline style to show (Bootstrap row will use flex by default)
                billingDetailsRow.style.display = '';
            }
            if (firmNameInput) {
                firmNameInput.required = true;
                firmNameInput.removeAttribute('readonly');
            }
            if (gstNoInput) {
                gstNoInput.required = true;
                gstNoInput.removeAttribute('readonly');
            }
            // Clear any previous errors
            hideFieldError('firmName', 'err-firmName');
            hideFieldError('gstNo', 'err-gstNo');
        } else {
            // Hide billing details and make optional
            if (billingDetailsRow) billingDetailsRow.style.display = 'none';
            if (firmNameInput) {
                firmNameInput.required = false;
                firmNameInput.value = '';
            }
            if (gstNoInput) {
                gstNoInput.required = false;
                gstNoInput.value = '';
            }
            // Clear errors
            hideFieldError('firmName', 'err-firmName');
            hideFieldError('gstNo', 'err-gstNo');
        }
    });
}


const agreeTermsCheckbox = el('agreeTerms');
if (agreeTermsCheckbox) {
    agreeTermsCheckbox.addEventListener('change', function() {
        const errorEl = el('err-agreeTerms');
        if (errorEl && this.checked) {
            errorEl.style.display = 'none';
        }
    });
}

el('toStepAddress').addEventListener('click', async () => {
    const validationPassed = await validateAndSavePropertyStep();
    if (validationPassed) {
        goToStep('address');
    }
});

/**********************
 Step 3: Address validations
***********************/
el('backToProp').addEventListener('click', () => goToStep('property'));

el('toStepVerify').addEventListener('click', async () => {
    const validationPassed = await validateAndSaveAddressStep();
    if (validationPassed) {
        goToStep('verify');
        await fetchAndRenderSummary();
    }
});

/**********************
 Step 4: summary and edit
***********************/
async function fetchAndRenderSummary() {
        if (!state.bookingId) { buildSummaryFallback(); return; }
        try {
                const result = await postJson('/frontend/setup/get-booking-summary', { booking_id: state.bookingId });
                if (result.success) {
                        renderSummary(result.booking);
                } else {
                        buildSummaryFallback();
                }
        } catch (e) {
                console.error(e);
                buildSummaryFallback();
        }
}

function renderSummary(b) {
        const s = el('summaryArea');
        const editIcon = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"></path>
                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
            </svg>`;
        const contactEditBtn = state.contactLocked || !hasStep('contact') ? '' : `<button type="button" class="summary-edit-btn" onclick="window.goToStep && window.goToStep('contact')" aria-label="Edit contact">${editIcon}</button>`;
        const propertyEditBtn = `<button type="button" class="summary-edit-btn" onclick="window.goToStep && window.goToStep('property')" aria-label="Edit property">${editIcon}</button>`;
        const addressEditBtn = `<button type="button" class="summary-edit-btn" onclick="window.goToStep && window.goToStep('address')" aria-label="Edit address">${editIcon}</button>`;
        const formattedPrice = state.currentPrice ? `₹${state.currentPrice.toLocaleString('en-IN')}` : '₹0';
        const contactName = getContactNameValue() || b?.user?.name || '-';
        const contactPhone = getContactPhoneValue() || b?.user?.mobile || '-';
        
        // Build property details based on property type
        const propertyType = (b.property_type || '').toLowerCase();
        let propertyDetails = `
            <div>Owner Type: ${b.owner_type || '-'} </div>
            <div>Main Type: ${b.property_type || '-'} </div>
            <div>Sub Type: ${b.property_sub_type || '-'} </div>
        `;
        
        if (propertyType === 'other') {
            // For Other type: show Other Option instead of Furnish and BHK
            propertyDetails += `
                <div>Other Option: ${b.other_details || '-'} </div>
                <div>Area: ${b.area || '-'} </div>
            `;
        } else if (propertyType === 'commercial') {
            // For Commercial: show Furnish but not BHK
            propertyDetails += `
                <div>Furnish: ${b.furniture_type || '-'} </div>
                <div>Area: ${b.area || '-'} </div>
            `;
        } else {
            // For Residential: show both Furnish and BHK
            propertyDetails += `
                <div>Furnish: ${b.furniture_type || '-'} </div>
                <div>BHK: ${b.bhk || '-'} </div>
                <div>Area: ${b.area || '-'} </div>
            `;
        }
        
        s.innerHTML = `
                <div class="mb-2 summary-title-row"><strong>Contact</strong>${contactEditBtn}</div>
                    <div>Name: ${contactName}</div>
                    <div>Phone: ${contactPhone}</div>
                </div>
                <hr/>
                <div class="mb-2 summary-title-row"><strong>Property</strong>${propertyEditBtn}</div>
                    ${propertyDetails}
                <hr/>
                <div class="mb-2 summary-title-row"><strong>Address</strong>${addressEditBtn}</div>
                    <div>House/Office: ${b.house_number || '-'} </div>
                    <div>Building/Society: ${b.building_name || '-'} </div>
                    <div>City: ${b.city || '-'} </div>
                    <div>Pincode: ${b.pincode || '-'} </div>
                    <div>Full address: ${b.full_address || '-'} </div>
                <div class="summary-price-box mt-3">
                    <div class="label">Estimated Price</div>
                    <div class="amount">${formattedPrice}</div>
                </div>
            `;
}

function buildSummaryFallback() {
    const payload = collectPayload();
    const s = el('summaryArea');
    const editIcon = `
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 20h9"></path>
        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
      </svg>`;
    const contactEditBtn = state.contactLocked || !hasStep('contact') ? '' : `<button type="button" class="summary-edit-btn" onclick="window.goToStep && window.goToStep('contact')" aria-label="Edit contact">${editIcon}</button>`;
    const propertyEditBtn = `<button type="button" class="summary-edit-btn" onclick="window.goToStep && window.goToStep('property')" aria-label="Edit property">${editIcon}</button>`;
    const addressEditBtn = `<button type="button" class="summary-edit-btn" onclick="window.goToStep && window.goToStep('address')" aria-label="Edit address">${editIcon}</button>`;
    const formattedPrice = state.currentPrice ? `₹${state.currentPrice.toLocaleString('en-IN')}` : '₹0';
    const propertyDetailSection = (() => {
        if (state.activePropertyTab === 'com') {
            return `
            <div class="mb-2"><strong>Commercial</strong>
              <div>Type: ${payload.commercial.propertyType || '-'}</div>
              <div>Furnish: ${payload.commercial.furnish || '-'}</div>
              <div>Area: ${payload.commercial.area || '-'}</div>
            </div>
          `;
        }
        if (state.activePropertyTab === 'oth') {
            return `
            <div class="mb-2"><strong>Other</strong>
              <div>Looking: ${payload.other.looking || '-'}</div>
              <div>Area: ${payload.other.area || '-'}</div>
              <div>Other Description: ${payload.other.desc || '-'}</div>
            </div>
          `;
        }
        return `
          <div class="mb-2"><strong>Residential</strong>
            <div>Type: ${payload.residential.propertyType || '-'}</div>
            <div>Furnish: ${payload.residential.furnish || '-'}</div>
            <div>Size: ${payload.residential.size || '-'}</div>
            <div>Area: ${payload.residential.area || '-'}</div>
          </div>
        `;
    })();
        s.innerHTML = `
        <div class="mb-2 summary-title-row"><strong>Contact</strong>${contactEditBtn}</div>
          <div>Name: ${payload.name}</div>
          <div>Phone: ${payload.phone}</div>
        </div>
        <hr/>
        <div class="mb-2 summary-title-row"><strong>Property</strong>${propertyEditBtn}</div>
          <div>Owner Type: ${payload.ownerType || '-'}</div>
          <div>Main Type: ${payload.mainType}</div>
        </div>
        ${propertyDetailSection}
        <hr/>
        <div class="mb-2 summary-title-row"><strong>Address</strong>${addressEditBtn}</div>
          <div>House/Office: ${payload.address.house}</div>
          <div>Building/Society: ${payload.address.building}</div>
          <div>City: ${payload.address.city}</div>
          <div>Pincode: ${payload.address.pincode}</div>
          <div>Full address: ${payload.address.full}</div>
        </div>
        <div class="summary-price-box mt-3">
          <div class="label">Estimated Price</div>
          <div class="amount">${formattedPrice}</div>
        </div>
      `;
}

function collectPayload() {
    return {
        name: getContactNameValue(),
        phone: getContactPhoneValue(),
        ownerType: el('choice_ownerType').value || null,
        mainType: state.activePropertyTab === 'com' ? 'Commercial' : state.activePropertyTab === 'oth' ? 'Other' : (state.activePropertyTab === 'res' ? 'Residential' : null),
        residential: {
            propertyType: el('choice_resType').value || null,
            furnish: el('choice_resFurnish').value || null,
            size: el('choice_resSize').value || null,
            area: el('resArea').value || null
        },
        commercial: {
            propertyType: el('choice_comType').value || null,
            furnish: el('choice_comFurnish').value || null,
            area: el('comArea').value || null
        },
        other: {
            looking: el('choice_othLooking').value || null,
            desc: el('othDesc').value || null,
            area: el('othArea').value || null
        },
        address: {
            house: el('addrHouse').value.trim(),
            building: el('addrBuilding').value.trim(),
            city: el('addrCity').value.trim(),
            pincode: el('addrPincode').value.trim(),
            full: el('addrFull').value.trim()
        }
    };
}

el('backToAddress').addEventListener('click', () => goToStep('address'));

el('toStepPayment').addEventListener('click', async () => {
    // Optionally re-fetch summary before payment
    if (state.bookingId) await fetchAndRenderSummary();
    goToStep('payment');
});

/**********************
 Step 5: Payment
***********************/
cashfreeSelectors.statusValue = el('cashfreeStatusValue');
cashfreeSelectors.statusMessage = el('cashfreeStatusMessage');
cashfreeSelectors.orderId = el('cashfreeOrderId');
cashfreeSelectors.amount = el('cashfreeAmountLabel');
cashfreeSelectors.reference = el('cashfreeReferenceId');
cashfreeSelectors.method = el('cashfreeMethod');
cashfreeSelectors.alert = el('cashfreeAlert');
cashfreeSelectors.loader = el('cashfreeLoader');

function updateCashfreeAmountDisplay(price) {
    const node = cashfreeSelectors.amount;
    if (node) {
        node.innerText = price ? `₹${price.toLocaleString('en-IN')}` : '₹0';
    }
}

function toggleCashfreeLoader(show) {
    const loader = cashfreeSelectors.loader;
    if (!loader) return;
    loader.style.display = show ? 'block' : 'none';
}

function setCashfreeStatus(statusText, message, theme = 'pending') {
    const statusEl = cashfreeSelectors.statusValue;
    const msgEl = cashfreeSelectors.statusMessage;
    if (statusEl) {
        statusEl.textContent = statusText;
        statusEl.classList.remove('text-success', 'text-danger', 'text-warning');
        if (theme === 'success') statusEl.classList.add('text-success');
        else if (theme === 'failed') statusEl.classList.add('text-danger');
        else statusEl.classList.add('text-warning');
    }
    if (msgEl) {
        msgEl.textContent = message || '';
    }
}

function updateCashfreeMeta(summary = null) {
    // Always use the latest calculated price from current area for display
    const areaValue = getActiveAreaValue();
    const latestPrice = calculateDynamicPrice(areaValue);
    state.currentPrice = latestPrice;
    
    if (!summary) {
        summary = {
            order_id: cashfreeState.orderId,
            amount: latestPrice, // Use latest price instead of cashfreeState.amount
            currency: cashfreeState.currency,
            reference_id: null,
            payment_method: null,
        };
    }
    if (cashfreeSelectors.orderId) {
        cashfreeSelectors.orderId.textContent = summary.order_id || '-';
    }
    // Always use the latest calculated price for display (even if summary has different amount)
    // This ensures the displayed amount matches the current area value
    updateCashfreeAmountDisplay(latestPrice);
    if (cashfreeSelectors.reference) {
        cashfreeSelectors.reference.textContent = summary.reference_id || '-';
    }
    if (cashfreeSelectors.method) {
        cashfreeSelectors.method.textContent = summary.payment_method || '-';
    }
}

function ensureCashfreeInstance() {
    if (!window.cashfreeInstance && typeof Cashfree === 'function') {
        const mode = (window.CashfreeConfig?.mode === 'production') ? 'production' : 'sandbox';
        window.cashfreeInstance = Cashfree({ mode });
    }
    return window.cashfreeInstance;
}

function prepareCashfreeStep() {
    if (!state.bookingId) {
        setCashfreeStatus('Missing booking', 'Please complete previous steps before paying.', 'failed');
        return;
    }
    // Always recalculate price from current area value to ensure it's up to date
    const areaValue = getActiveAreaValue();
    const latestPrice = calculateDynamicPrice(areaValue);
    state.currentPrice = latestPrice;
    updateCashfreeAmountDisplay(latestPrice);
    updateCashfreeMeta(cashfreeState.lastSummary);
    if (state.returningFromPayment) {
        setCashfreeStatus('Checking payment status', 'Fetching latest update from Cashfree...', 'pending');
        state.returningFromPayment = false;
        if (cashfreeState.orderId) {
            pollCashfreeStatus(true);
        } else {
            initCashfreeSession({ autoLaunch: false, force: true });
        }
        return;
    }
    if (!cashfreeState.orderId && !cashfreeState.initializing && !cashfreeState.completed) {
        initCashfreeSession({ autoLaunch: true });
    } else if (!cashfreeState.completed && !cashfreeState.autoAttempted) {
        initCashfreeSession({ autoLaunch: true });
    }
}

async function initCashfreeSession({ autoLaunch = false, force = false } = {}) {
    if (!state.bookingId) {
        await showSweetAlert('warning', 'Booking Required', 'Booking not ready for payment.');
        return;
    }

    if (cashfreeState.completed) {
        setCashfreeStatus('Already paid', 'Payment for this booking is completed.', 'success');
        return;
    }

    if (cashfreeState.orderId && !force) {
        if (autoLaunch) {
            launchCashfreeCheckout();
        }
        return;
    }

    cashfreeState.initializing = true;
    setCashfreeStatus('Preparing payment', 'Creating payment session with Cashfree...', 'pending');
    toggleCashfreeLoader(true);

    try {
        const result = await postJson('/frontend/setup/payment/create-session', {
            booking_id: state.bookingId,
        });
        if (result.success) {
            const data = result.data;
            cashfreeState.orderId = data.order_id;
            cashfreeState.paymentSessionId = data.payment_session_id;
            cashfreeState.amount = data.amount;
            cashfreeState.currency = data.currency;
            cashfreeState.autoAttempted = true;
            // Update meta with server data, but display will use latest calculated price
            updateCashfreeMeta({
                order_id: data.order_id,
                amount: data.amount,
                currency: data.currency,
            });
            // Ensure displayed amount matches current area calculation
            const areaValue = getActiveAreaValue();
            const latestPrice = calculateDynamicPrice(areaValue);
            if (latestPrice > 0) {
                updateCashfreeAmountDisplay(latestPrice);
            }
            setCashfreeStatus('Ready for payment', 'Click the button below to pay securely with Cashfree.', 'pending');
            if (autoLaunch) {
                launchCashfreeCheckout();
            }
        } else {
            setCashfreeStatus('Unable to start', result.message || 'Please try again later.', 'failed');
            showCashfreeAlert(result.message || 'Cashfree session could not be created.');
        }
    } catch (error) {
        console.error(error);
        setCashfreeStatus('Network error', 'Please check your connection and try again.', 'failed');
        showCashfreeAlert('Network error while creating payment session.');
    } finally {
        cashfreeState.initializing = false;
        toggleCashfreeLoader(false);
    }
}

function showCashfreeAlert(message) {
    if (!cashfreeSelectors.alert) return;
    cashfreeSelectors.alert.textContent = message;
    cashfreeSelectors.alert.style.display = message ? 'block' : 'none';
}

function launchCashfreeCheckout() {
    if (!cashfreeState.paymentSessionId) {
        initCashfreeSession({ autoLaunch: true, force: true });
        return;
    }
    const instance = ensureCashfreeInstance();
    if (!instance) {
        showCashfreeAlert('Cashfree SDK not loaded. Please refresh the page.');
        return;
    }
    setCashfreeStatus('Opening Cashfree checkout', 'Complete the payment in the popup window.', 'pending');
    instance.checkout({
        paymentSessionId: cashfreeState.paymentSessionId,
    }).then(result => {
        if (result?.error) {
            console.error('Cashfree error', result.error);
            setCashfreeStatus('Payment not completed', result.error.message || 'Checkout was closed.', 'failed');
            showCashfreeAlert(result.error.message || 'Payment was cancelled.');
        } else {
            setCashfreeStatus('Processing confirmation', 'Please wait while we confirm your payment...', 'pending');
            pollCashfreeStatus(false);
        }
    }).catch(error => {
        console.error('Cashfree checkout failed', error);
        setCashfreeStatus('Checkout error', 'Could not open Cashfree checkout. Please retry.', 'failed');
        showCashfreeAlert('Could not open Cashfree checkout.');
    });
}

async function pollCashfreeStatus(showLoader = true) {
    if (!state.bookingId || !cashfreeState.orderId) {
        showCashfreeAlert('Payment session not found.');
        return;
    }
    if (showLoader) toggleCashfreeLoader(true);
    try {
        const result = await postJson('/frontend/setup/payment/status', {
            booking_id: state.bookingId,
        });
        if (result.success) {
            const data = result.data;
            cashfreeState.lastSummary = data;
            let theme = 'pending';
            if (data.order_status === 'PAID') {
                theme = 'success';
                cashfreeState.completed = true;
            } else if (['FAILED', 'EXPIRED', 'TERMINATED', 'TERMINATION_REQUESTED'].includes(data.order_status)) {
                theme = 'failed';
            }
            setCashfreeStatus(data.order_status || 'PENDING', data.status_message || 'Status updated.', theme);
            updateCashfreeMeta(data);
            showCashfreeAlert('');
        } else {
            showCashfreeAlert(result.message || 'Unable to fetch payment status.');
        }
    } catch (error) {
        console.error(error);
        showCashfreeAlert('Network error while fetching payment status.');
    } finally {
        if (showLoader) toggleCashfreeLoader(false);
    }
}

const cashfreePayBtn = el('cashfreePayBtn');
if (cashfreePayBtn) {
    cashfreePayBtn.addEventListener('click', () => initCashfreeSession({ autoLaunch: true, force: true }));
}

const cashfreeRefreshBtn = el('cashfreeStatusRefreshBtn');
if (cashfreeRefreshBtn) {
    cashfreeRefreshBtn.addEventListener('click', () => pollCashfreeStatus(true));
}

const backToVerifyBtn = el('backToVerify');
if (backToVerifyBtn) {
    backToVerifyBtn.addEventListener('click', () => goToStep('verify'));
}

/**********************
 Helper function for SweetAlert
***********************/
function showSweetAlert(icon, title, message, html = false) {
    if (typeof Swal !== 'undefined') {
        const config = {
            icon: icon,
            title: title,
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'sweetalert-popup',
                title: 'sweetalert-title',
                content: 'sweetalert-content',
                confirmButton: 'sweetalert-confirm-btn'
            },
            buttonsStyling: true,
            allowOutsideClick: true,
            allowEscapeKey: true
        };
        
        // Enhanced styling for error messages
        if (icon === 'error') {
            config.confirmButtonColor = '#dc3545';
            config.iconColor = '#dc3545';
            config.width = '500px';
            if (html) {
                // Format error message with better styling
                const formattedMessage = message.split('<br>').map(err => {
                    return `<div style="display: flex; align-items: flex-start;">
                        <span style="color: #dc3545; margin-right: 8px; font-weight: 600;">•</span>
                        <span style="flex: 1; color: #333;">${err.replace(/^•\s*/, '')}</span>
                    </div>`;
                }).join('');
                config.html = `<div style="text-align: left; padding: 10px 0; max-height: 400px; overflow-y: auto;">${formattedMessage}</div>`;
            } else {
                config.html = `<div style="text-align: left; padding: 10px 0; color: #333; font-size: 14px; line-height: 1.8;">${message}</div>`;
            }
        } else if (html) {
            config.html = `<div style="text-align: left; padding: 10px 0; color: #333; font-size: 14px; line-height: 1.8;">${message}</div>`;
        } else {
            config.text = message;
        }
        
        return Swal.fire(config);
    } else {
        // Fallback to regular alert if SweetAlert not available
        const cleanMessage = message.replace(/<br>/g, '\n').replace(/• /g, '- ');
        alert(cleanMessage);
        return Promise.resolve();
    }
}

/**********************
 Validation functions for each step
***********************/
async function validateAndSavePropertyStep() {
    // Validate depending on active tab
    const tabResVisible = el('tab-res').style.display !== 'none';
    const tabComVisible = el('tab-com').style.display !== 'none';
    const tabOthVisible = el('tab-oth').style.display !== 'none';

    // Clear previous errors
    clearAllFieldErrors();
    const errors = [];
    const errorFields = [];

    // Owner Type validation
    const ownerType = el('choice_ownerType').value;
    if (!ownerType) {
        errors.push('Owner Type is required');
        showPillContainerError('ownerTypeContainer', 'err-ownerType', 'Owner Type is required.');
    } else {
        hidePillContainerError('ownerTypeContainer', 'err-ownerType');
    }

    // Property Type validation - must be selected
    if (!state.activePropertyTab) {
        errors.push('Property Type is required');
        showPillContainerError('propertyTypeContainer', 'err-propertyType', 'Property Type is required.');
    } else {
        hidePillContainerError('propertyTypeContainer', 'err-propertyType');
    }

    // Residential validations
    if (tabResVisible) {
        const rType = el('choice_resType').value;
        const rFurn = el('choice_resFurnish').value;
        const rSize = el('choice_resSize').value;
        const rArea = el('resArea').value.trim();
        
        if (!rType) {
            errors.push('Residential Property Sub Type is required');
            showPillContainerError('resTypeContainer', 'err-resType', 'Property Sub Type is required.');
        } else {
            hidePillContainerError('resTypeContainer', 'err-resType');
        }
        
        if (!rFurn) {
            errors.push('Furnish Type is required');
            showPillContainerError('resFurnishContainer', 'err-resFurnish', 'Furnish Type is required.');
        } else {
            hidePillContainerError('resFurnishContainer', 'err-resFurnish');
        }
        
        if (!rSize) {
            errors.push('Size (BHK/RK) is required');
            showPillContainerError('resSizeContainer', 'err-resSize', 'Size (BHK / RK) is required.');
        } else {
            hidePillContainerError('resSizeContainer', 'err-resSize');
        }
        
        if (!rArea || Number(rArea) <= 0) {
            errors.push('Super Built-up Area is required and must be greater than 0');
            showFieldError('resArea', 'err-resArea', 'Super Built-up Area is required and must be greater than 0');
        } else {
            markFieldValid('resArea');
        }
    }

    // Commercial validations
    if (tabComVisible) {
        const cType = el('choice_comType').value;
        const cFurn = el('choice_comFurnish').value;
        const cArea = el('comArea').value.trim();
        
        if (!cType) {
            errors.push('Commercial Property Sub Type is required');
            showPillContainerError('comTypeContainer', 'err-comType', 'Property Sub Type is required.');
        } else {
            hidePillContainerError('comTypeContainer', 'err-comType');
        }
        
        if (!cFurn) {
            errors.push('Furnish Type is required');
            showPillContainerError('comFurnishContainer', 'err-comFurnish', 'Furnish Type is required.');
        } else {
            hidePillContainerError('comFurnishContainer', 'err-comFurnish');
        }
        
        if (!cArea || Number(cArea) <= 0) {
            errors.push('Super Built-up Area is required and must be greater than 0');
            showFieldError('comArea', 'err-comArea', 'Super Built-up Area is required and must be greater than 0');
        } else {
            markFieldValid('comArea');
        }
    }

    // Other validations
    if (tabOthVisible) {
        const oLooking = el('choice_othLooking').value;
        const oDesc = el('othDesc').value.trim();
        const oArea = el('othArea').value.trim();
        const hasSelection = Boolean(oLooking);
        const hasOther = Boolean(oDesc);
        
        if (!hasSelection && !hasOther) {
            errors.push('Please select an option or enter Other option');
            showPillContainerError('othLookingContainer', 'err-othLooking', 'Select an option or enter Other option.');
            showFieldError('othDesc', 'err-othDesc', 'Other option is required if none of the options are selected.');
        } else {
            hidePillContainerError('othLookingContainer', 'err-othLooking');
            hideFieldError('othDesc', 'err-othDesc');
        }
        
        if (!oArea || Number(oArea) <= 0) {
            errors.push('Super Built-up Area is required and must be greater than 0');
            showFieldError('othArea', 'err-othArea', 'Super Built-up Area is required and must be greater than 0');
        } else {
            markFieldValid('othArea');
        }
    }

    // Contact validation
    const contactName = getContactNameValue();
    const contactPhone = getContactPhoneValue();
    if (!contactName || !contactPhone) {
        errors.push('Contact name and phone are required. Please complete the Contact step first.');
    }

    // Different Billing Name validation - if checked, Company Name and GST No are required
    const differentBillingName = el('differentBillingName');
    if (differentBillingName && differentBillingName.checked) {
        const firmName = el('firmName')?.value?.trim();
        const gstNo = el('gstNo')?.value?.trim();
        
        if (!firmName) {
            errors.push('Company Name is required when using company billing details');
            showFieldError('firmName', 'err-firmName', 'Company Name is required.');
        } else {
            markFieldValid('firmName');
        }
        if (!gstNo) {
            errors.push('GST No is required when using company billing details');
            showFieldError('gstNo', 'err-gstNo', 'GST No is required.');
        } else {
            markFieldValid('gstNo');
        }
    } else {
        // Clear billing field errors if checkbox is not checked
        hideFieldError('firmName', 'err-firmName');
        hideFieldError('gstNo', 'err-gstNo');
    }


    // Terms and Conditions checkbox validation
    const agreeTerms = el('agreeTerms');
    if (!agreeTerms || !agreeTerms.checked) {
        errors.push('You must agree to the Terms and Conditions, Refund Policy, and Privacy Policy to continue');
        const errorEl = el('err-agreeTerms');
        if (errorEl) {
            errorEl.style.display = 'block';
            errorEl.classList.add('show');
        }
    } else {
        // Hide error if checkbox is checked
        hideFieldError('agreeTerms', 'err-agreeTerms');
    }

    // Show all errors at once if any
    if (errors.length > 0) {
        // Show error fields
        const errorMessage = '• ' + errors.join('<br>• ');
        await showSweetAlert('error', 'Validation Error', errorMessage, true);
        return false;
    }

    const payload = {
        booking_id: state.bookingId || el('bookingId').value || null,
        name: contactName,
        phone: contactPhone,
        owner_type: el('choice_ownerType').value,
        main_property_type: el('mainPropertyType').value,
        residential_property_type: el('choice_resType').value || null,
        residential_furnish: el('choice_resFurnish').value || null,
        residential_size: el('choice_resSize').value || null,
        residential_area: el('resArea').value || null,
        commercial_property_type: el('choice_comType').value || null,
        commercial_furnish: el('choice_comFurnish').value || null,
        commercial_area: el('comArea').value || null,
        other_looking: el('choice_othLooking').value || null,
        other_option_details: el('othDesc').value || null,
        other_area: el('othArea').value || null,
        firm_name: el('firmName')?.value?.trim() || null,
        gst_no: el('gstNo')?.value?.trim() || null,
    };
    try {
        const result = await postJson('/frontend/setup/save-property-step', payload);
        if (result.success) {
            state.bookingId = result.booking_id;
            el('bookingId').value = state.bookingId;
            // Mark property type as initialized after successful save
            if (state.activePropertyTab) {
                state.propertyTypeInitialized = true;
            }
            return true;
        } else {
            await showSweetAlert('error', 'Error', result.message || 'Failed to save property details');
            return false;
        }
    } catch (e) {
        console.error(e);
        await showSweetAlert('error', 'Network Error', 'Network error saving property details. Please try again.');
        return false;
    }
}

async function validateAndSaveAddressStep() {
    // Clear previous errors
    clearAllFieldErrors();
    const errors = [];
    const h = el('addrHouse').value.trim();
    const b = el('addrBuilding').value.trim();
    const p = el('addrPincode').value.trim();
    const f = el('addrFull').value.trim();

    if (!h) {
        errors.push('House / Office No. is required');
        showFieldError('addrHouse', 'err-addrHouse', 'House / Office No. is required.');
    } else {
        markFieldValid('addrHouse');
    }
    
    if (!b) {
        errors.push('Society / Building Name is required');
        showFieldError('addrBuilding', 'err-addrBuilding', 'Society / Building Name is required.');
    } else {
        markFieldValid('addrBuilding');
    }
    
    if (!p) {
        errors.push('Pincode is required');
        showFieldError('addrPincode', 'err-addrPincode', 'Pincode is required.');
    } else if (!/^[0-9]{6}$/.test(p)) {
        errors.push('Pincode must be a valid 6-digit number');
        showFieldError('addrPincode', 'err-addrPincode', 'Pincode must be a valid 6-digit number');
    } else {
        markFieldValid('addrPincode');
    }
    
    if (!f) {
        errors.push('Full address is required');
        showFieldError('addrFull', 'err-addrFull', 'Full address is required.');
    } else {
        markFieldValid('addrFull');
    }

    // Show all errors at once if any
    if (errors.length > 0) {
        // Show error fields
        const errorMessage = '• ' + errors.join('<br>• ');
        await showSweetAlert('error', 'Validation Error', errorMessage, true);
        return false;
    }

    // Save address step via AJAX
    const payload = {
        booking_id: state.bookingId || el('bookingId').value,
        house_number: el('addrHouse').value.trim(),
        building_name: el('addrBuilding').value.trim(),
        pincode: el('addrPincode').value.trim(),
        city: el('addrCity').value.trim(),
        full_address: el('addrFull').value.trim(),
    };
    try {
        const result = await postJson('/frontend/setup/save-address-step', payload);
        if (result.success) {
            return true;
        } else {
            await showSweetAlert('error', 'Error', result.message || 'Failed to save address');
            return false;
        }
    } catch (e) {
        console.error(e);
        await showSweetAlert('error', 'Network Error', 'Network error saving address. Please try again.');
        return false;
    }
}

async function validateContactStep() {
    if (!state.otpVerified) {
        await showSweetAlert('warning', 'OTP Verification Required', 'Please verify OTP before proceeding.');
        return false;
    }
    return true;
}

/**********************
 Check if address step is completed
***********************/
function isAddressStepCompleted() {
    const h = el('addrHouse').value.trim();
    const b = el('addrBuilding').value.trim();
    const p = el('addrPincode').value.trim();
    const f = el('addrFull').value.trim();
    
    // Check if all required address fields are filled
    return h && b && p && /^[0-9]{6}$/.test(p) && f;
}

/**********************
 Quick buttons: clicking top step buttons
***********************/
document.querySelectorAll('.step-btn').forEach(b => {
    b.addEventListener('click', async () => {
        const target = Number(b.dataset.step);
        const targetKey = getStepKeyByNumber(target);
        const currentKey = state.currentStepKey;
        
        // Get step numbers
        const contactStepNumber = getStepNumber('contact');
        const propertyStepNumber = getStepNumber('property');
        const addressStepNumber = getStepNumber('address');
        const verifyStepNumber = getStepNumber('verify');
        const paymentStepNumber = getStepNumber('payment');
        
        // If clicking on current step, do nothing
        if (target === state.step) {
            return;
        }
        
        // If trying to go to contact step but it's locked
        if (contactStepNumber && state.contactLocked && target === contactStepNumber) {
            showSweetAlert('info', 'Contact Locked', 'Contact details are locked after verification.');
            return;
        }
        
        // If trying to go to property/address/verify/payment steps but OTP not verified
        if (!state.otpVerified && contactStepNumber) {
            const addressStepNum = getStepNumber('address');
            const verifyStepNum = getStepNumber('verify');
            const paymentStepNum = getStepNumber('payment');
            
            // Check if trying to access any step other than contact
            if ((propertyStepNumber && target === propertyStepNumber) ||
                (addressStepNum && target === addressStepNum) ||
                (verifyStepNum && target === verifyStepNum) ||
                (paymentStepNum && target === paymentStepNum)) {
                // Mark that user attempted to access other tabs
                state.attemptedOtherTabs = true;
                // Show notification
                await showSweetAlert('info', 'First Verify Account', 'Please verify your account by completing the Contact details & verification step first.');
                // Navigate back to contact step
                showStep(contactStepNumber);
                return;
            }
        }
        
        // If returning to contact tab after attempting other tabs, show popup
        if (targetKey === 'contact' && state.attemptedOtherTabs && !state.otpVerified) {
            await showSweetAlert('info', 'Contact Locked', 'Contact details are locked after verification. Please verify your OTP to continue.');
            state.attemptedOtherTabs = false; // Reset flag after showing popup
        }
        
        // Always validate and save current step before navigating (if it's a form step)
        // This ensures changes are saved even when going to already-unlocked steps
        let validationPassed = true;
        
        if (currentKey === 'property') {
            // Always validate and save property step before navigating away (forward or backward)
            validationPassed = await validateAndSavePropertyStep();
        } else if (currentKey === 'address') {
            // Always validate and save address step before navigating away (forward or backward)
            validationPassed = await validateAndSaveAddressStep();
        } else if (currentKey === 'contact') {
            // Only validate contact if going forward
            if (target > state.step) {
                validationPassed = validateContactStep();
                if (validationPassed) {
                    lockContactSection();
                }
            }
        }
        // For verify and payment steps, no validation needed before navigating
        
        // If validation failed, stay on current step
        if (!validationPassed) {
            return;
        }
        
        // If going forward and validation passed, update highest step unlocked
        if (target > state.step) {
            state.highestStepUnlocked = Math.max(state.highestStepUnlocked ?? state.step, target);
        }
        
        // Special check: If trying to go to verify, ensure address is completed
        if (targetKey === 'verify') {
            if (!isAddressStepCompleted()) {
                await showSweetAlert('warning', 'Address Required', 'Please complete the Address step before viewing the verification summary.');
                // Navigate to address step instead
                const addressStepNum = getStepNumber('address');
                if (addressStepNum) {
                    showStep(addressStepNum);
                }
                return;
            }
        }
        
        // Special check: If trying to go to payment, ensure address is completed
        if (targetKey === 'payment') {
            if (!isAddressStepCompleted()) {
                await showSweetAlert('warning', 'Address Required', 'Please complete the Address step before proceeding to payment.');
                // Navigate to address step instead
                const addressStepNum = getStepNumber('address');
                if (addressStepNum) {
                    showStep(addressStepNum);
                }
                return;
            }
        }
        
        // Navigate to target step
        showStep(target);
        
        // If navigating to verify step, always fetch fresh summary to show latest data
        if (targetKey === 'verify') {
            await fetchAndRenderSummary();
        }
        
        // If navigating to payment from verify, refresh summary
        if (currentKey === 'verify' && targetKey === 'payment') {
            await fetchAndRenderSummary();
        }
    });
});

// Make showStep global for onclick handlers
window.showStep = showStep;
window.selectCard = selectCard;
window.selectChip = selectChip;
window.topPillClick = topPillClick;
window.handlePropertyTabChange = handlePropertyTabChange;
window.goToStep = goToStep;
