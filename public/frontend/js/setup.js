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
const hasBookingStep = Boolean(stepNumbers.booking);

const state = {
    step: initialStepNumber,
    currentStepKey: stepKeyByNumber[initialStepNumber] || null,
    highestStepUnlocked: initialStepNumber,
    otp: 524163,
    otpVerified: true,
    activePropertyTab: 'res',
    contactLocked: setupContext.authenticated ? true : false,
    currentPrice: 0,
    bookingId: null,
    returningFromPayment: false,
    isAuthenticated: setupContext.authenticated || false,
    bookings: [],
    bookingsInitialized: false,
    bookingsLoading: false,
    selectedBookingId: null,
    isAddingNewProperty: false,
    bookingsEnabled: hasBookingStep,
    bookingSelectionReady: !hasBookingStep,
    bookingReadOnly: false,
};

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
    state.selectedBookingId = bookingIdParam;
    state.isAddingNewProperty = false;
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
const bookingGrid = el('bookingGrid');
const bookingGridEmpty = el('bookingGridEmpty');
const bookingGridLoader = el('bookingGridLoader');
const refreshBookingsBtn = el('refreshBookingsBtn');
const bookingProceedBtn = el('bookingToProperty');
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
const bookingStepNumber = getStepNumber('booking');
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

function canMutateForm() {
    return !state.bookingReadOnly || readOnlyBypass;
}

function updateReadOnlyUI() {
    const ro = state.bookingReadOnly;
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
    if (state.currentStepKey === 'booking') {
        maybeInitBookingGrid();
    }
    if (state.currentStepKey === 'payment') {
        prepareCashfreeStep();
    }
}

function goToStep(key) {
    const num = getStepNumber(key);
    if (num) {
        showStep(num);
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

function toggleBookingGridLoader(show) {
    if (!bookingGridLoader) return;
    bookingGridLoader.classList.toggle('d-none', !show);
}

function maybeInitBookingGrid(force = false) {
    if (!hasStep('booking')) return;
    if (!isUserAuthenticated()) {
        state.bookings = [];
        state.bookingReadOnly = false;
        updateReadOnlyUI();
        renderBookingGrid();
        return;
    }
    if (state.bookingsInitialized && !force) {
        renderBookingGrid();
        return;
    }
    state.bookingsInitialized = true;
    fetchUserBookings(true);
}

async function fetchUserBookings(force = false) {
    if (!isUserAuthenticated()) return;
    if (state.bookingsLoading && !force) return;
    state.bookingsLoading = true;
    toggleBookingGridLoader(true);
    try {
        const response = await fetch(buildUrl('/frontend/setup/user-bookings'), {
            headers: { 'Accept': 'application/json' },
        });
        if (!response.ok) {
            if (response.status === 401) {
                state.bookings = [];
                return;
            }
            throw new Error('Failed to fetch bookings');
        }
        const result = await response.json();
        state.bookings = result.bookings || [];
        if (state.bookingId && !state.selectedBookingId) {
            const match = state.bookings.find(b => Number(b.id) === Number(state.bookingId));
            if (match) state.selectedBookingId = match.id;
        }
        if (!state.bookings.length) {
            state.selectedBookingId = null;
            state.isAddingNewProperty = true;
            state.bookingSelectionReady = true;
            state.bookingReadOnly = false;
        } else if (!state.selectedBookingId) {
            state.isAddingNewProperty = false;
            state.bookingSelectionReady = false;
            state.bookingReadOnly = false;
        }
        updateReadOnlyUI();
        renderBookingGrid();
    } catch (error) {
        console.error('Error loading bookings', error);
    } finally {
        state.bookingsLoading = false;
        toggleBookingGridLoader(false);
    }
}

function renderBookingGrid() {
    if (!bookingGrid) return;
    bookingGrid.innerHTML = '';
    if (!isUserAuthenticated()) {
        if (bookingGridEmpty) bookingGridEmpty.textContent = 'Sign in to view your saved properties.';
        return;
    }
    const addCol = document.createElement('div');
    addCol.className = 'col';
    addCol.appendChild(buildAddNewBookingCard());
    bookingGrid.appendChild(addCol);
    if (!state.bookings.length) {
        if (bookingGridEmpty) bookingGridEmpty.textContent = 'No saved properties yet. Start by adding a new one.';
        return;
    }
    if (bookingGridEmpty) bookingGridEmpty.textContent = '';
    state.bookings.forEach(booking => {
        const col = document.createElement('div');
        col.className = 'col';
        col.appendChild(buildBookingCard(booking));
        bookingGrid.appendChild(col);
    });
    updateBookingProceedState();
}

function isBookingSelectionSatisfied() {
    return !hasStep('booking') || state.bookingSelectionReady;
}

function updateBookingProceedState() {
    if (!bookingProceedBtn) return;
    bookingProceedBtn.disabled = !isBookingSelectionSatisfied();
}

function buildAddNewBookingCard() {
    const card = document.createElement('div');
    card.className = 'booking-card booking-card-add rounded-3 p-3 h-100';
    if (state.isAddingNewProperty || !state.selectedBookingId) {
        card.classList.add('booking-card-active');
    }
    card.innerHTML = `
        <div class="fw-semibold mb-1 d-flex align-items-center gap-2">
            <span class="text-primary fw-bold" style="font-size:1.1rem;">+</span>
            Add new booking
        </div>
        <div class="muted-small">Start a fresh property booking flow.</div>
    `;
    card.addEventListener('click', () => startNewPropertyFlow(true));
    return card;
}

function buildBookingCard(booking) {
    const status = (booking.payment_status || '').toLowerCase();
    let badgeClass = 'status-pending';
    if (status === 'paid') badgeClass = 'status-paid';
    else if (status === 'failed' || status === 'unpaid') badgeClass = 'status-unpaid';
    const statusLabel = status ? status.toUpperCase() : 'PENDING';
    const priceLabel = booking.price ? `₹${Number(booking.price).toLocaleString('en-IN')}` : '-';
    const card = document.createElement('div');
    card.className = 'booking-card rounded-3 p-3 h-100';
    if (state.selectedBookingId && Number(state.selectedBookingId) === Number(booking.id)) {
        card.classList.add('booking-card-active');
    }
    card.innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="fw-semibold">${booking.main_property_type || 'Property'}</div>
                <div class="text-muted small">${booking.property_sub_type || 'No subtype'}</div>
            </div>
            <span class="status-badge ${badgeClass}">${statusLabel}</span>
        </div>
        <div class="small mb-1">Owner: <strong>${booking.owner_type || '-'}</strong></div>
        <div class="small mb-1">Area: <strong>${booking.area ? `${booking.area} sq.ft` : '-'}</strong></div>
        <div class="small mb-1">Price: <strong>${priceLabel}</strong></div>
        <div class="small mb-1 d-none">BHK: <strong>${booking.bhk_label || '-'}</strong></div>
        <div class="small text-muted">Updated: ${booking.updated_at ? new Date(booking.updated_at).toLocaleDateString() : '-'}</div>
    `;
    card.addEventListener('click', () => selectBookingFromGrid(booking.id));
    return card;
}

function startNewPropertyFlow(autoAdvance = false) {
    state.isAddingNewProperty = true;
    state.selectedBookingId = null;
    state.bookingId = null;
    state.bookingSelectionReady = true;
    state.bookingReadOnly = false;
    updateReadOnlyUI();
    const hiddenBookingInput = el('bookingId');
    if (hiddenBookingInput) hiddenBookingInput.value = '';
    ['choice_ownerType','choice_resType','choice_resFurnish','choice_resSize','choice_comType','choice_comFurnish','choice_othLooking'].forEach(id => {
        const input = el(id);
        if (input) input.value = '';
    });
    document.querySelectorAll('[data-group]').forEach(node => node.classList.remove('active'));
    clearAllPropertySelections();
    resetAddressFields();
    switchMainTab('res');
    updatePriceDisplay();
    renderBookingGrid();
    updateBookingProceedState();
    if (autoAdvance && hasStep('property')) {
        const propertyNumber = getStepNumber('property');
        state.highestStepUnlocked = Math.max(state.highestStepUnlocked, propertyNumber);
        goToStep('property');
    }
}

function selectBookingFromGrid(id) {
    const booking = state.bookings.find(b => Number(b.id) === Number(id));
    if (!booking) return;
    state.selectedBookingId = booking.id;
    state.bookingId = booking.id;
    state.isAddingNewProperty = false;
    state.bookingSelectionReady = true;
    state.bookingReadOnly = (booking.payment_status || '').toLowerCase() === 'paid';
    updateReadOnlyUI();
    const hiddenBookingInput = el('bookingId');
    if (hiddenBookingInput) hiddenBookingInput.value = booking.id;
    populatePropertyFormFromBooking(booking);
    renderBookingGrid();
    updateBookingProceedState();
    // auto open property tab when selecting
    if (hasStep('property')) {
        const propertyNumber = getStepNumber('property');
        state.highestStepUnlocked = Math.max(state.highestStepUnlocked, propertyNumber);
        goToStep('property');
    }
}

function populatePropertyFormFromBooking(booking) {
    runWithReadOnlyBypass(() => {
        applyOwnerTypeSelection(booking.owner_type);
        const mainType = booking.main_property_type || 'Residential';
        const tabKey = mainType === 'Commercial' ? 'com' : (mainType === 'Other' ? 'oth' : 'res');
        switchMainTab(tabKey);
        if (tabKey === 'res') {
            setGroupValue('resType', booking.property_sub_type);
            setGroupValue('resFurnish', booking.furniture_type);
            setGroupValue('resSize', booking.bhk_id ? String(booking.bhk_id) : '');
            setInputValue('resArea', booking.area || '');
        } else if (tabKey === 'com') {
            setGroupValue('comType', booking.property_sub_type);
            setGroupValue('comFurnish', booking.furniture_type);
            setInputValue('comArea', booking.area || '');
        } else {
            setGroupValue('othLooking', booking.property_sub_type);
            const othDesc = el('othDesc');
            if (othDesc) othDesc.value = booking.other_details || '';
            setInputValue('othArea', booking.area || '');
        }
        applyAddressFromBooking(booking);
        updatePriceDisplay();
    });
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
    const citySelect = el('addrCity');
    if (citySelect && booking.address.city) {
        const existing = Array.from(citySelect.options).some(opt => opt.value === booking.address.city);
        if (!existing) {
            const opt = document.createElement('option');
            opt.value = booking.address.city;
            opt.textContent = booking.address.city;
            citySelect.appendChild(opt);
        }
        citySelect.value = booking.address.city;
    }
}

if (refreshBookingsBtn) {
    refreshBookingsBtn.addEventListener('click', () => fetchUserBookings(true));
}

if (bookingProceedBtn) {
    bookingProceedBtn.addEventListener('click', () => {
        if (!isBookingSelectionSatisfied()) {
            alert('Please select an existing booking or add a new property to continue.');
            return;
        }
        if (hasStep('property')) {
            const propertyNumber = getStepNumber('property');
            state.highestStepUnlocked = Math.max(state.highestStepUnlocked, propertyNumber);
            goToStep('property');
        }
    });
}
updateBookingProceedState();

// init
showStep(state.step);
switchMainTab('res');
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
    citySel.disabled = false;
    const cities = setupData.cities || [];
    citySel.innerHTML = '';
    cities
        .slice()
        .sort((a,b) => (a.name||'').localeCompare(b.name||''))
        .forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.name;
            opt.textContent = c.name;
            citySel.appendChild(opt);
        });
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
            // Optional status log without OTP content
            if (result.data.is_new_user) {
                console.log('✅ New user created and OTP sent');
            } else {
                console.log('👤 Existing user - OTP sent');
            }
        } else {
            alert(result.message || 'Failed to send OTP. Please try again.');
        }
    } catch (error) {
        console.error('Error sending OTP:', error);
        alert('Network error. Please check your connection and try again.');
    } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = 'Send OTP';
    }
});

const resendOtpBtn = el('resendOtp');
if (resendOtpBtn) resendOtpBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    const phone = el('inputPhone').value.trim();
    const name = el('inputName').value.trim();
    
    if (!phone) { 
        alert('Enter phone to resend OTP'); 
        return; 
    }
    if (!name) {
        alert('Enter name to resend OTP');
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
            console.log('📱 Resent OTP');
        } else {
            alert(result.message || 'Failed to resend OTP.');
        }
    } catch (error) {
        console.error('Error resending OTP:', error);
        alert('Network error. Please try again.');
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
            if (result.csrf_token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.content = result.csrf_token;
            }
            el('err-otp').style.display = 'none';
            state.otpVerified = true;
            el('otpRow').classList.add('hidden');
            el('otpSentBadge').classList.remove('hidden');
            el('otpSentBadge').innerText = 'Verified ✓';
            el('toStep2').disabled = false;
            console.log('✅ OTP verified successfully:', result.user);
            state.isAuthenticated = true;
            setupContext.authenticated = true;
            state.bookingsInitialized = false;
            maybeInitBookingGrid(true);
            state.bookingReadOnly = false;
            updateReadOnlyUI();
            const propertyStepNumber = getStepNumber('property');
            if (propertyStepNumber) {
                state.step = propertyStepNumber;
                state.highestStepUnlocked = propertyStepNumber;
                goToStep('property');
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
if (toStep2Btn) toStep2Btn.addEventListener('click', () => {
    if (!state.otpVerified) {
        alert('Please verify OTP before proceeding.');
        return;
    }
    lockContactSection();
    const nextKey = hasStep('booking') ? 'booking' : 'property';
    const nextNumber = getStepNumber(nextKey);
    if (nextNumber) {
        state.highestStepUnlocked = Math.max(state.highestStepUnlocked, nextNumber);
        goToStep(nextKey);
    }
});

const skipContactBtn = el('skipContact');
if (skipContactBtn) skipContactBtn.addEventListener('click', () => {
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
function switchMainTab(key) {
    state.activePropertyTab = key;
    el('tab-res').style.display = (key === 'res') ? 'block' : 'none';
    el('tab-com').style.display = (key === 'com') ? 'block' : 'none';
    el('tab-oth').style.display = (key === 'oth') ? 'block' : 'none';
    // active pill
    ['pillResidential', 'pillCommercial', 'pillOther'].forEach(id => el(id).classList.remove('active'));
    if (key === 'res') el('pillResidential').classList.add('active');
    if (key === 'com') el('pillCommercial').classList.add('active');
    if (key === 'oth') el('pillOther').classList.add('active');
    
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
    if (group === 'resType') el('choice_resType').value = v;
    if (group === 'comType') el('choice_comType').value = v;
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

function clearAllPropertySelections() {
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

function handlePropertyTabChange(key) {
    if (!canMutateForm()) return;
    clearAllPropertySelections();
    resetAddressFields();
    switchMainTab(key);
    updatePriceDisplay();
}

function getActiveAreaValue() {
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
    if (group === 'resFurnish') el('choice_resFurnish').value = v;
    if (group === 'resSize') el('choice_resSize').value = v;
    if (group === 'comFurnish') el('choice_comFurnish').value = v;
}

['resArea', 'comArea', 'othArea'].forEach(id => {
    const input = el(id);
    if (input) {
        input.addEventListener('input', updatePriceDisplay);
    }
});
updatePriceDisplay();

function topPillClick(dom) {
    if (!canMutateForm()) return;
    const group = dom.dataset.group;
    document.querySelectorAll(`[data-group="${group}"]`).forEach(n => n.classList.remove('active'));
    dom.classList.add('active');
    if (group === 'othLooking') el('choice_othLooking').value = dom.dataset.value;
    if (group === 'ownerType') {
        el('choice_ownerType').value = dom.dataset.value;
        clearAllPropertySelections();
        resetAddressFields();
        switchMainTab('res');
    }
}

const backToContactBtn = el('backToContact');
if (backToContactBtn) backToContactBtn.addEventListener('click', () => {
    if (state.contactLocked) {
        alert('Contact details are locked after verification.');
        return;
    }
    goToStep('contact');
});

el('toStepAddress').addEventListener('click', async () => {
    if (state.bookingReadOnly) {
        goToStep('address');
        return;
    }
    // Validate depending on active tab
    const tabResVisible = el('tab-res').style.display !== 'none';
    const tabComVisible = el('tab-com').style.display !== 'none';
    const tabOthVisible = el('tab-oth').style.display !== 'none';

    // clear previous errors
    document.querySelectorAll('.error').forEach(e => e.style.display = 'none');

    const ownerType = el('choice_ownerType').value;
    if (!ownerType) { alert('Select owner type'); return; }

    // Residential validations
    if (tabResVisible) {
        const rType = el('choice_resType').value;
        const rFurn = el('choice_resFurnish').value;
        const rSize = el('choice_resSize').value;
        const rArea = el('resArea').value.trim();
        if (!rType) { alert('Select residential property type'); return; }
        if (!rFurn) { alert('Select furnish type'); return; }
        if (!rSize) { alert('Select size (BHK/RK)'); return; }
        if (!rArea || Number(rArea) <= 0) { el('err-resArea').style.display = 'block'; return; }
    }

    // Commercial validations
    if (tabComVisible) {
        const cType = el('choice_comType').value;
        const cFurn = el('choice_comFurnish').value;
        const cArea = el('comArea').value.trim();
        if (!cType) { alert('Select commercial property type'); return; }
        if (!cFurn) { alert('Select furnish type'); return; }
        if (!cArea || Number(cArea) <= 0) { el('err-comArea').style.display = 'block'; return; }
    }

    // Other validations
    if (tabOthVisible) {
        const oLooking = el('choice_othLooking').value;
        const oDesc = el('othDesc').value.trim();
        const oArea = el('othArea').value.trim();
        const hasSelection = Boolean(oLooking);
        const hasOther = Boolean(oDesc);
        if (!hasSelection && !hasOther) {
            el('err-othLooking').style.display = 'block';
            el('err-othDesc').style.display = 'block';
            return;
        } else {
            el('err-othLooking').style.display = 'none';
            el('err-othDesc').style.display = 'none';
        }
        if (!oArea || Number(oArea) <= 0) { el('err-othArea').style.display = 'block'; return; }
    }

    // Build payload for property step
    const contactName = getContactNameValue();
    const contactPhone = getContactPhoneValue();

    if (!contactName || !contactPhone) {
        alert('Contact name and phone are required. Please complete the Contact step first.');
        goToStep('contact');
        return;
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
        other_description: el('othDesc').value || null,
        other_area: el('othArea').value || null,
    };
    try {
        const result = await postJson('/frontend/setup/save-property-step', payload);
        if (result.success) {
            state.bookingId = result.booking_id;
            el('bookingId').value = state.bookingId;
            state.selectedBookingId = state.bookingId;
            state.bookingSelectionReady = true;
            if (hasStep('booking') && isUserAuthenticated()) {
                fetchUserBookings(true);
            }
            goToStep('address');
        } else {
            alert(result.message || 'Failed to save property details');
        }
    } catch (e) {
        console.error(e);
        alert('Network error saving property details');
    }
});

/**********************
 Step 3: Address validations
***********************/
const backToBookingBtn = el('backToBooking');
if (backToBookingBtn) backToBookingBtn.addEventListener('click', () => goToStep('booking'));

el('backToProp').addEventListener('click', () => goToStep('property'));

el('toStepVerify').addEventListener('click', async () => {
    if (state.bookingReadOnly) {
        await fetchAndRenderSummary();
        goToStep('verify');
        return;
    }
    // clear errors
    document.querySelectorAll('.error').forEach(e => e.style.display = 'none');

    const h = el('addrHouse').value.trim();
    const b = el('addrBuilding').value.trim();
    const p = el('addrPincode').value.trim();
    const f = el('addrFull').value.trim();

    if (!h) { el('err-addrHouse').style.display = 'block'; return; }
    if (!b) { el('err-addrBuilding').style.display = 'block'; return; }
    if (!/^[0-9]{6}$/.test(p)) { el('err-addrPincode').style.display = 'block'; return; }
    if (!f) { el('err-addrFull').style.display = 'block'; return; }

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
            goToStep('verify');
            await fetchAndRenderSummary();
        } else {
            alert(result.message || 'Failed to save address');
        }
    } catch (e) {
        console.error(e);
        alert('Network error saving address');
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
        s.innerHTML = `
                <div class="mb-2 summary-title-row"><strong>Contact</strong>${contactEditBtn}</div>
                    <div>Name: ${contactName}</div>
                    <div>Phone: ${contactPhone}</div>
                </div>
                <hr/>
                <div class="mb-2 summary-title-row"><strong>Property</strong>${propertyEditBtn}</div>
                    <div>Owner Type: ${b.owner_type || '-'} </div>
                    <div>Main Type: ${b.property_type || '-'} </div>
                    <div>Sub Type: ${b.property_sub_type || '-'} </div>
                    <div>Furnish: ${b.furniture_type || '-'} </div>
                    <div>BHK: ${b.bhk || '-'} </div>
                    <div>Area: ${b.area || '-'} </div>
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
        mainType: state.activePropertyTab === 'com' ? 'Commercial' : state.activePropertyTab === 'oth' ? 'Other' : 'Residential',
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
    if (!summary) {
        summary = {
            order_id: cashfreeState.orderId,
            amount: cashfreeState.amount,
            currency: cashfreeState.currency,
            reference_id: null,
            payment_method: null,
        };
    }
    if (cashfreeSelectors.orderId) {
        cashfreeSelectors.orderId.textContent = summary.order_id || '-';
    }
    updateCashfreeAmountDisplay(summary.amount || cashfreeState.amount || state.currentPrice);
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
    updateCashfreeAmountDisplay(state.currentPrice);
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
        alert('Booking not ready for payment.');
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
            updateCashfreeMeta({
                order_id: data.order_id,
                amount: data.amount,
                currency: data.currency,
            });
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
 Quick buttons: clicking top step buttons
***********************/
document.querySelectorAll('.step-btn').forEach(b => {
    b.addEventListener('click', () => {
        const target = Number(b.dataset.step);
        if (contactStepNumber && state.contactLocked && target === contactStepNumber) {
            alert('Contact details are locked after verification.');
            return;
        }
        if (bookingStepNumber && target >= (propertyStepNumber || bookingStepNumber) && !isBookingSelectionSatisfied()) {
            alert('Please select an existing booking or add a new property first.');
            return;
        }
        if (target <= (state.highestStepUnlocked ?? state.step)) {
            showStep(target);
            return;
        }
        if (propertyStepNumber && target === propertyStepNumber && state.otpVerified) {
            showStep(propertyStepNumber);
            return;
        }
        alert('Please complete previous steps first.');
    });
});

// Make showStep global for onclick handlers
window.showStep = showStep;
window.selectCard = selectCard;
window.selectChip = selectChip;
window.topPillClick = topPillClick;
window.handlePropertyTabChange = handlePropertyTabChange;
window.goToStep = goToStep;
