/**********************
     State & helpers
    ***********************/
const state = {
    step: 1,
    otp: null,
    otpVerified: false,
    paymentMethod: null,
    activePropertyTab: 'res',
    contactLocked: false,
    currentPrice: 0
};

const el = id => document.getElementById(id);

function updateProgress() {
    const percent = Math.round(((state.step - 1) / 4) * 100);
    el('progressBar').style.width = percent + '%';
    // highlight step button
    document.querySelectorAll('.step-btn').forEach(b => b.classList.remove('active'));
    const btn = document.querySelector(`.step-btn[data-step="${state.step}"]`);
    if (btn) btn.classList.add('active');
}

function showStep(n) {
    state.step = n;
    // hide all panes
    document.querySelectorAll('.step-pane').forEach(p => p.classList.add('hidden'));
    el(`step-${n}`).classList.remove('hidden');
    updateProgress();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// init
updateProgress();
showStep(1);
switchMainTab('res');

/**********************
 Step 1: OTP flow with API integration
***********************/
// Helper to get CSRF token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || 
           document.querySelector('input[name="_token"]')?.value || '';
}

el('sendOtpBtn').addEventListener('click', async () => {
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
        const response = await fetch('/frontend/check-user-send-otp', {
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

el('resendOtp').addEventListener('click', async (e) => {
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
        const response = await fetch('/frontend/check-user-send-otp', {
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

el('verifyOtpBtn').addEventListener('click', async () => {
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
        const response = await fetch('/frontend/verify-user-otp', {
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
            el('err-otp').style.display = 'none';
            state.otpVerified = true;
            el('otpRow').classList.add('hidden');
            el('otpSentBadge').classList.remove('hidden');
            el('otpSentBadge').innerText = 'Verified ✓';
            el('toStep2').disabled = false;
            console.log('✅ OTP verified successfully:', result.user);
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

el('toStep2').addEventListener('click', () => {
    if (!state.otpVerified) {
        alert('Please verify OTP before proceeding.');
        return;
    }
    lockContactSection();
    showStep(2);
});

el('skipContact').addEventListener('click', () => {
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

function updatePaymentEstimate() {
    const box = el('payEstimateBox');
    const valNode = el('payEstimateValue');
    if (!box || !valNode) return;
    const amountField = el('payAmount');
    const amount = amountField ? Number(amountField.value) || 0 : 0;
    valNode.innerText = amount ? `₹${amount.toLocaleString('en-IN')}` : '₹0';
}

function updatePriceDisplay() {
    const areaValue = getActiveAreaValue();
    const price = calculateDynamicPrice(areaValue);
    state.currentPrice = price;
    const priceNode = el('priceDisplay');
    if (priceNode) {
        priceNode.innerText = price ? `₹${price.toLocaleString('en-IN')}` : '₹0';
    }
    const amountField = el('payAmount');
    if (amountField && state.paymentMethod) {
        amountField.value = price || '';
    }
    const estimateBox = el('payEstimateBox');
    if (estimateBox && state.paymentMethod) {
        estimateBox.classList.remove('hidden');
        updatePaymentEstimate();
    }
}

function selectChip(dom) {
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

el('backToContact').addEventListener('click', () => {
    if (state.contactLocked) {
        alert('Contact details are locked after verification.');
        return;
    }
    showStep(1);
});

el('toStep3').addEventListener('click', () => {
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

    showStep(3);
});

/**********************
 Step 3: Address validations
***********************/
el('backToProp').addEventListener('click', () => showStep(2));

el('toStep4').addEventListener('click', () => {
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

    // build summary for step 4
    buildSummary();
    showStep(4);
});

/**********************
 Step 4: summary and edit
***********************/
function buildSummary() {
    const payload = collectPayload();
    const s = el('summaryArea');
    const editIcon = `
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 20h9"></path>
        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
      </svg>`;
    const contactEditBtn = state.contactLocked ? '' : `<button type="button" class="summary-edit-btn" onclick="showStep(1)" aria-label="Edit contact">${editIcon}</button>`;
    const propertyEditBtn = `<button type="button" class="summary-edit-btn" onclick="showStep(2)" aria-label="Edit property">${editIcon}</button>`;
    const addressEditBtn = `<button type="button" class="summary-edit-btn" onclick="showStep(3)" aria-label="Edit address">${editIcon}</button>`;
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
        name: el('inputName').value.trim(),
        phone: el('inputPhone').value.trim(),
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
            city: 'Ahmedabad',
            pincode: el('addrPincode').value.trim(),
            full: el('addrFull').value.trim()
        }
    };
}

el('backToAddress').addEventListener('click', () => showStep(3));

el('toStep5').addEventListener('click', () => {
    showStep(5);
});

/**********************
 Step 5: Payment
***********************/
function selectPay(dom) {
    document.querySelectorAll('[data-pay]').forEach(n => n.classList.remove('active'));
    dom.classList.add('active');
    state.paymentMethod = dom.dataset.pay;
    el('paymentMethodInput').value = dom.dataset.pay;
    el('payFields').classList.remove('hidden');
    el('doPay').disabled = false;
    updatePriceDisplay();
}

window.selectPay = selectPay; // expose for inline usage

el('backToVerify').addEventListener('click', () => showStep(4));

// Intercept form submission
document.getElementById('setupForm').addEventListener('submit', (e) => {
    e.preventDefault();
    // simulate payment success
    const modal = new bootstrap.Modal(el('successModal'));
    modal.show();
    
    // After showing modal, submit the form
    setTimeout(() => {
        e.target.submit();
    }, 2000);
});

/**********************
 Quick buttons: clicking top step buttons
***********************/
document.querySelectorAll('.step-btn').forEach(b => {
    b.addEventListener('click', () => {
        const s = Number(b.dataset.step);
        if (state.contactLocked && s === 1) {
            alert('Contact details are locked after verification.');
            return;
        }
        if (s <= state.step) { showStep(s); return; }
        if (s === 2 && state.otpVerified) { showStep(2); return; }
        if (s === 3) {
            alert('Please complete previous steps to go to Address.');
            return;
        }
        if (s === 4) { alert('Please complete previous steps first.'); return; }
        if (s === 5) { alert('Please complete previous steps first.'); return; }
    });
});

// Make showStep global for onclick handlers
window.showStep = showStep;
window.selectCard = selectCard;
window.selectChip = selectChip;
window.topPillClick = topPillClick;
window.handlePropertyTabChange = handlePropertyTabChange;
