/**
 * Bookings Form - Create Page
 * Dynamic property selection with pill/chip UI (Same logic as setup page)
 */

// ========================
// STATE MANAGEMENT
// ========================
const state = {
    activePropertyTab: null,
    ownerType: null
};

// ========================
// UTILITY FUNCTIONS
// ========================
const el = id => document.getElementById(id);

function hideAllPropertyTabs() {
    // Hide the entire property subtype section
    const subTypeSection = el('propertySubTypetab');
    if (subTypeSection) {
        subTypeSection.classList.add('hidden');
    }

    // Hide furnish and size rows by default
    const furnishRow = el('furnishRow');
    if (furnishRow) furnishRow.classList.add('hidden');
    const sizeRow = el('sizeRow');
    if (sizeRow) sizeRow.classList.add('hidden');
    
    // Hide all property subtype tabs
    document.querySelectorAll('[id^="tab-"]').forEach(tab => {
        if (tab.id.startsWith('tab-') && tab.id !== 'propertySubTypetab') {
            tab.classList.add('hidden');
        }
    });
    
    // Remove active state from all property type pills
    document.querySelectorAll('[data-tab-connect]').forEach(pill => {
        pill.classList.remove('active');
    });
    
    // Clear main property type hidden field
    const mainType = el('mainPropertyType');
    if (mainType) mainType.value = '';
    state.activePropertyTab = null;
}

// ========================
// DATA CHECKING FUNCTIONS
// ========================
function hasPropertyDataFilled() {
    if (!state.activePropertyTab) return false;
    
    // Check based on active tab
    if (state.activePropertyTab === 'res') {
        const resType = document.querySelector('#resTypeContainer .top-pill.active');
        const resFurnish = document.querySelector('#resFurnishContainer .chip.active');
        const resSize = document.querySelector('#resSizeContainer .chip.active');
        const resArea = el('area')?.value?.trim();
        return !!(resType || resFurnish || resSize || resArea);
    } else if (state.activePropertyTab === 'com') {
        const comType = document.querySelector('#comTypeContainer .top-pill.active');
        const comFurnish = document.querySelector('#comFurnishContainer .chip.active');
        const comArea = el('area')?.value?.trim();
        return !!(comType || comFurnish || comArea);
    } else if (state.activePropertyTab === 'oth') {
        const othLooking = document.querySelector('#othLookingContainer .top-pill.active');
        const othDesc = el('othDesc')?.value?.trim();
        const othArea = el('area')?.value?.trim();
        return !!(othLooking || othDesc || othArea);
    }
    
    return false;
}

function hasAddressDataFilled() {
    const c = el('country_id')?.value?.trim();
    const h = el('house_no')?.value?.trim();
    const b = el('building')?.value?.trim();
    const s = el('society_name')?.value?.trim();
    const a = el('address_area')?.value?.trim();
    const l = el('landmark')?.value?.trim();
    const p = el('pin_code')?.value?.trim();
    const f = el('full_address')?.value?.trim();
    return !!(c || h || b || s || a || l || p || f);
}

function clearAllPropertySelections() {
    // Skip clearing if we're restoring old values from validation error
    if (window.isRestoringOldValues) {
        return;
    }
    
    // Clear property-related selections
    document.querySelectorAll('[data-group="resType"],[data-group="resFurnish"],[data-group="resSize"],[data-group="comType"],[data-group="comFurnish"],[data-group="othLooking"]').forEach(node => {
        node.classList.remove('active');
    });
    
    // Clear area field
    const areaField = el('area');
    if (areaField) {
        areaField.value = '';
        areaField.classList.remove('is-valid', 'is-invalid');
    }
    
    // Clear other option details
    const othDesc = el('othDesc');
    if (othDesc) {
        othDesc.value = '';
        othDesc.classList.remove('is-valid', 'is-invalid');
    }
    
    // Clear all property errors
    ['err-resType', 'err-resFurnish', 'err-resSize', 'err-comType', 'err-comFurnish', 'err-othLooking', 'err-othDesc'].forEach(id => {
        const errorEl = el(id);
        if (errorEl) {
            errorEl.style.display = 'none';
            errorEl.classList.remove('show');
        }
    });
    
    ['resTypeContainer', 'resFurnishContainer', 'resSizeContainer', 'comTypeContainer', 'comFurnishContainer', 'othLookingContainer'].forEach(id => {
        const container = el(id);
        if (container) container.classList.remove('has-error');
    });
    
    // Clear address fields
    resetAddressFields();
    
    // Note: Billing details (firm name, GST) are NOT cleared - they persist when property type changes
}

function resetAddressFields() {
    ['house_no', 'building', 'society_name', 'address_area', 'landmark', 'pin_code', 'full_address', 'city_id', 'state_id', 'country_id'].forEach(id => {
        const input = el(id);
        if (input) {
            input.value = '';
            input.classList.remove('is-valid', 'is-invalid');
        }
    });
}

// ========================
// GLOBAL FUNCTIONS (Called from HTML onclick)
// ========================

// Handle property type tab changes (Residential/Commercial/Other)
window.handlePropertyTabChange = async function(domElement) {
    // Extract the tab ID from data-tab-connect attribute
    const tabId = domElement.dataset.tabConnect;
    const propertyTypeName = domElement.dataset.value;
    const propertyTypeId = domElement.dataset.typeId;
    
    // Skip confirmation if we're restoring old values from validation error
    const skipConfirmation = window.isRestoringOldValues;
    
    // Check if property type was already set, user is trying to change it, AND there's actual data filled
    if (!skipConfirmation && state.activePropertyTab && state.activePropertyTab !== tabId && (hasPropertyDataFilled() || hasAddressDataFilled())) {
        // Get current and new property type names
        const currentPill = document.querySelector('[data-tab-connect].active');
        const currentType = currentPill ? currentPill.dataset.value : 'Current';
        const newType = propertyTypeName || 'New';
        
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
                    • Area / Locality<br>
                    • Landmark<br>
                    • Pincode<br>
                    • Full Address<br>`);
            } else {
                messageParts.push(`This will clear the following address details:<br>
                    • House / Office No.<br>
                    • Society / Building Name<br>
                    • Area / Locality<br>
                    • Landmark<br>
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
    
    // Show the propertySubTypetab section
    const subTypeSection = el('propertySubTypetab');
    if (subTypeSection) {
        subTypeSection.classList.remove('hidden');
    }
    
    // Hide all property subtype tabs
    document.querySelectorAll('[id^="tab-"]').forEach(tab => {
        if (tab.id.startsWith('tab-') && tab.id !== 'propertySubTypetab') {
            tab.classList.add('hidden');
        }
    });
    
    // Show the selected tab
    const selectedTab = el(tabId);
    if (selectedTab) {
        selectedTab.classList.remove('hidden');
    }

    // Toggle Furnish and Size rows based on selected tab (residential/commercial/other)
    const furnishRow = el('furnishRow');
    const sizeRow = el('sizeRow');

    // Determine property type using both human-readable name and tab id for safety
    const typeKey = (propertyTypeName || '').toLowerCase();
    const tabKey = (tabId || '').toLowerCase();
    const isResidential = typeKey.includes('residential') || tabKey.includes('residential') || tabKey.includes('res');
    const isCommercial = typeKey.includes('commercial') || tabKey.includes('commercial') || tabKey.includes('com');

    if (isResidential) {
        if (furnishRow) furnishRow.classList.remove('hidden');
        if (sizeRow) sizeRow.classList.remove('hidden'); // Size hidden for all types
    } else if (isCommercial) {
        if (furnishRow) furnishRow.classList.remove('hidden');
        if (sizeRow) sizeRow.classList.add('hidden');
    } else {
        if (furnishRow) furnishRow.classList.add('hidden');
        if (sizeRow) sizeRow.classList.add('hidden');
    }
    
    // Update active state on property type pills
    document.querySelectorAll('[data-tab-connect]').forEach(pill => {
        pill.classList.remove('active');
    });
    domElement.classList.add('active');
    
    // Update state
    state.activePropertyTab = tabId;
    
    // Update hidden field for main property type
    const mainType = el('mainPropertyType');
    if (mainType) {
        mainType.value = propertyTypeName;
    }
    
    // Clear property type error when selected
    hidePillContainerError('propertyTypeContainer', 'err-propertyType');
};

// Handle top-pill clicks (Owner Type, Other options)
window.topPillClick = function(dom) {
    const group = dom.dataset.group;
    document.querySelectorAll(`[data-group="${group}"]`).forEach(n => n.classList.remove('active'));
    dom.classList.add('active');
    
    if (group === 'ownerType') {
        el('choice_ownerType').value = dom.dataset.value;
        state.ownerType = dom.dataset.value;
        // Clear error when owner type is selected
        hidePillContainerError('ownerTypeContainer', 'err-ownerType');
    }
    if (group === 'othLooking') {
        // Clear error when other looking option is selected
        hidePillContainerError('othLookingContainer', 'err-othLooking');
        hideFieldError('othDesc', 'err-othDesc');
    }
};

// Handle card/pill selection for property subtypes
window.selectCard = function(dom) {
    const group = dom.dataset.group;
    document.querySelectorAll(`[data-group="${group}"]`).forEach(n => n.classList.remove('active'));
    dom.classList.add('active');
    const v = dom.dataset.value;
    const subtypeName = dom.dataset.subtypeName || '';
    
    // Show/hide Other Option Details based on property sub type
    const otherDetailsRow = document.getElementById('otherDetailsRow');
    if (subtypeName.toLowerCase().includes('other')) {
        otherDetailsRow.style.display = 'block';
        document.getElementById('othDesc').required = true;
    } else {
        otherDetailsRow.style.display = 'none';
        document.getElementById('othDesc').required = false;
        document.getElementById('othDesc').value = ''; // Clear the field
        hideFieldError('othDesc', 'err-othDesc'); // Clear any errors
    }
    
    // Clear errors when residential/commercial sub type is selected
    if (group === 'resType') {
        hidePillContainerError('resTypeContainer', 'err-resType');
    }
    if (group === 'comType') {
        hidePillContainerError('comTypeContainer', 'err-comType');
    }
};

// Handle chip selection for furnish type and BHK
window.selectChip = function(dom) {
    const group = dom.dataset.group;
    document.querySelectorAll(`[data-group="${group}"]`).forEach(n => n.classList.remove('active'));
    dom.classList.add('active');
    const v = dom.dataset.value;
    
    // Clear errors when chips are selected
    if (group === 'resFurnish') {
        hidePillContainerError('resFurnishContainer', 'err-resFurnish');
    }
    if (group === 'resSize') {
        hidePillContainerError('resSizeContainer', 'err-resSize');
    }
    if (group === 'comFurnish') {
        hidePillContainerError('comFurnishContainer', 'err-comFurnish');
    }
};

// ========================
// PRICE CALCULATION
// ========================
function calculateDynamicPrice(areaValue) {
    const area = Number(areaValue);
    if (!area || area <= 0) return 0;
    
    // Price calculation logic (same as setup page)
    const baseArea = 1500;
    const basePrice = 599;
    const extraArea = 500;
    const extraAreaPrice = 200;
    
    let price = basePrice;
    if (area > baseArea) {
        const extra = area - baseArea;
        const blocks = Math.ceil(extra / extraArea);
        price += blocks * extraAreaPrice;
    }
    return price;
}

function updatePriceDisplay() {
    const areaInput = el('area');
    const priceInput = el('price');
    
    if (areaInput && priceInput) {
        const areaValue = areaInput.value.trim();
        const calculatedPrice = calculateDynamicPrice(areaValue);
        
        if (calculatedPrice > 0) {
            priceInput.value = calculatedPrice;
        }
    }
}

// ========================
// VALIDATION FUNCTIONS
// ========================
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
    }
    
    if (errorEl) {
        errorEl.style.display = 'none';
        errorEl.classList.remove('show');
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

function markFieldValid(fieldId) {
    const field = el(fieldId);
    if (field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    }
}

function validateForm() {
    let isValid = true;
    const errors = [];
    
    // Validate Owner Type
    const ownerType = el('choice_ownerType')?.value;
    if (!ownerType) {
        showPillContainerError('ownerTypeContainer', 'err-ownerType');
        errors.push('Owner Type is required');
        isValid = false;
    } else {
        hidePillContainerError('ownerTypeContainer', 'err-ownerType');
    }
    
    // Validate Property Type
    if (!state.activePropertyTab) {
        showPillContainerError('propertyTypeContainer', 'err-propertyType');
        errors.push('Property Type is required');
        isValid = false;
    } else {
        hidePillContainerError('propertyTypeContainer', 'err-propertyType');
    }
    
    // Validate based on active property tab
    if (state.activePropertyTab === 'res') {
        // Residential validations
        const resType = document.querySelector('#resTypeContainer .top-pill.active');
        if (!resType) {
            showPillContainerError('resTypeContainer', 'err-resType');
            errors.push('Property Sub Type is required');
            isValid = false;
        } else {
            hidePillContainerError('resTypeContainer', 'err-resType');
        }
        
        const resFurnish = document.querySelector('#resFurnishContainer .chip.active');
        if (!resFurnish) {
            showPillContainerError('resFurnishContainer', 'err-resFurnish');
            errors.push('Furnish Type is required');
            isValid = false;
        } else {
            hidePillContainerError('resFurnishContainer', 'err-resFurnish');
        }
        
        const resSize = document.querySelector('#resSizeContainer .chip.active');
        if (!resSize) {
            showPillContainerError('resSizeContainer', 'err-resSize');
            errors.push('Size (BHK / RK) is required');
            isValid = false;
        } else {
            hidePillContainerError('resSizeContainer', 'err-resSize');
        }
    } else if (state.activePropertyTab === 'com') {
        // Commercial validations
        const comType = document.querySelector('#comTypeContainer .top-pill.active');
        if (!comType) {
            showPillContainerError('comTypeContainer', 'err-comType');
            errors.push('Property Sub Type is required');
            isValid = false;
        } else {
            hidePillContainerError('comTypeContainer', 'err-comType');
        }
        
        const comFurnish = document.querySelector('#comFurnishContainer .chip.active');
        if (!comFurnish) {
            showPillContainerError('comFurnishContainer', 'err-comFurnish');
            errors.push('Furnish Type is required');
            isValid = false;
        } else {
            hidePillContainerError('comFurnishContainer', 'err-comFurnish');
        }
    } else if (state.activePropertyTab === 'oth') {
        // Other validations
        const othLooking = document.querySelector('#othLookingContainer .top-pill.active');
        const othDesc = el('othDesc')?.value?.trim();
        
        if (!othLooking && !othDesc) {
            showPillContainerError('othLookingContainer', 'err-othLooking');
            showFieldError('othDesc', 'err-othDesc');
            errors.push('Select Option or Other Option Details is required');
            isValid = false;
        } else {
            hidePillContainerError('othLookingContainer', 'err-othLooking');
            hideFieldError('othDesc', 'err-othDesc');
        }
    }
    
    // Validate Other Option Details if the row is visible
    const otherDetailsRow = document.getElementById('otherDetailsRow');
    if (otherDetailsRow && otherDetailsRow.style.display !== 'none') {
        const othDesc = el('othDesc')?.value?.trim();
        if (!othDesc) {
            showFieldError('othDesc', 'err-othDesc');
            errors.push('Other Option Details is required');
            isValid = false;
        } else {
            hideFieldError('othDesc', 'err-othDesc');
        }
    }
    
    // Validate Area
    const areaField = el('area');
    const area = areaField?.value?.trim();
    if (!area || Number(area) <= 0) {
        if (areaField) {
            areaField.classList.add('is-invalid');
            areaField.classList.remove('is-valid');
        }
        errors.push('Super Built-up Area is required');
        isValid = false;
    } else {
        markFieldValid('area');
    }
    
    // Validate Address Fields
    const houseNo = el('house_no')?.value?.trim();
    if (!houseNo) {
        const field = el('house_no');
        if (field) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        }
        errors.push('House / Office No is required');
        isValid = false;
    } else {
        markFieldValid('house_no');
    }
    
    const building = el('building')?.value?.trim();
    if (!building) {
        const field = el('building');
        if (field) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        }
        errors.push('Society / Building Name is required');
        isValid = false;
    } else {
        markFieldValid('building');
    }
    
    const pinCode = el('pin_code')?.value?.trim();
    if (!pinCode) {
        const field = el('pin_code');
        if (field) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        }
        errors.push('PIN Code is required');
        isValid = false;
    } else if (!/^[0-9]{6}$/.test(pinCode)) {
        const field = el('pin_code');
        if (field) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        }
        errors.push('PIN Code must be 6 digits');
        isValid = false;
    } else {
        markFieldValid('pin_code');
    }
    
    const fullAddress = el('full_address')?.value?.trim();
    if (!fullAddress) {
        const field = el('full_address');
        if (field) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        }
        errors.push('Full Address is required');
        isValid = false;
    } else {
        markFieldValid('full_address');
    }
    
    // Validate Billing Details if checkbox is checked
    const billingCheckbox = el('differentBillingName');
    if (billingCheckbox && billingCheckbox.checked) {
        const firmNameField = el('firmName');
        const gstNoField = el('gstNo');
        const firmName = firmNameField?.value?.trim();
        const gstNo = gstNoField?.value?.trim();
        
        if (!firmName) {
            showFieldError('firmName', 'err-firmName');
            if (firmNameField) {
                firmNameField.classList.add('is-invalid');
                firmNameField.classList.remove('is-valid');
            }
            errors.push('Company Name is required');
            isValid = false;
        } else {
            hideFieldError('firmName', 'err-firmName');
            if (firmNameField) {
                firmNameField.classList.remove('is-invalid');
                firmNameField.classList.add('is-valid');
            }
        }
        
        if (!gstNo) {
            showFieldError('gstNo', 'err-gstNo');
            if (gstNoField) {
                gstNoField.classList.add('is-invalid');
                gstNoField.classList.remove('is-valid');
            }
            errors.push('GST No is required');
            isValid = false;
        } else {
            hideFieldError('gstNo', 'err-gstNo');
            if (gstNoField) {
                gstNoField.classList.remove('is-invalid');
                gstNoField.classList.add('is-valid');
            }
        }
    } else {
        // Checkbox is not checked - clear any errors
        hideFieldError('firmName', 'err-firmName');
        hideFieldError('gstNo', 'err-gstNo');
    }
    
    if (!isValid && errors.length > 0) {
        // Scroll to first error
        const firstError = document.querySelector('.has-error, .is-invalid');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    return isValid;
}

// ========================
// DOCUMENT READY
// ========================
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ========================
    // AREA INPUT - AUTO CALCULATE PRICE & VALIDATION
    // ========================
    const areaInput = el('area');
    if (areaInput) {
        areaInput.addEventListener('input', function() {
            updatePriceDisplay();
            // Clear error when user starts typing
            if (this.value.trim() && Number(this.value.trim()) > 0) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }
    
    // ========================
    // BILLING FIELDS VALIDATION
    // ========================
    const firmNameInput = el('firmName');
    if (firmNameInput) {
        firmNameInput.addEventListener('input', function() {
            if (this.value.trim()) {
                hideFieldError('firmName', 'err-firmName');
            }
        });
    }
    
    const gstNoInput = el('gstNo');
    if (gstNoInput) {
        gstNoInput.addEventListener('input', function() {
            if (this.value.trim()) {
                hideFieldError('gstNo', 'err-gstNo');
            }
        });
    }
    
    // ========================
    // OTHER OPTION DETAILS VALIDATION
    // ========================
    const othDescInput = el('othDesc');
    if (othDescInput) {
        othDescInput.addEventListener('input', function() {
            if (this.value.trim()) {
                hideFieldError('othDesc', 'err-othDesc');
            }
        });
    }
    
    // ========================
    // ADDRESS FIELDS VALIDATION
    // ========================
    const houseNoInput = el('house_no');
    if (houseNoInput) {
        houseNoInput.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }
    
    const buildingInput = el('building');
    if (buildingInput) {
        buildingInput.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }
    
    const pinCodeInput = el('pin_code');
    if (pinCodeInput) {
        pinCodeInput.addEventListener('input', function() {
            const value = this.value.trim();
            if (value && /^[0-9]{6}$/.test(value)) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else if (value && value.length === 6) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
    }
    
    const fullAddressInput = el('full_address');
    if (fullAddressInput) {
        fullAddressInput.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }

    // ========================
    // BILLING DETAILS CHECKBOX
    // ========================
    const differentBillingCheckbox = el('differentBillingName');
    const billingDetailsRow = el('billingDetailsRow');
    
    if (differentBillingCheckbox) {
        differentBillingCheckbox.addEventListener('change', function() {
            if (this.checked) {
                billingDetailsRow.classList.remove('hidden');
                billingDetailsRow.style.display = '';
            } else {
                billingDetailsRow.classList.add('hidden');
                billingDetailsRow.style.display = 'none';
                // DON'T clear fields - just hide them to preserve existing data
                // Fields will not be validated or updated if hidden
            }
        });
    }

    // ========================
    // FORM SUBMISSION HANDLER
    // ========================
    const form = document.querySelector('form.needs-validation');
    
    if (form) {
        // Remove any existing Bootstrap validation
        form.classList.remove('was-validated');
        
        form.addEventListener('submit', function(e) {
            // Stop the form from submitting immediately
            e.preventDefault();
            e.stopPropagation();
            
            // Validate form before submission
            const isValid = validateForm();
            
            if (!isValid) {
                form.classList.add('was-validated');
                return false;
            }
            
            // Collect selected property subtype based on active tab
            let propertySubTypeId = null;
            let propertyTypeId = null;
            
            // Get active property type pill
            const activePropertyPill = document.querySelector('[data-tab-connect].active');
            if (activePropertyPill) {
                propertyTypeId = activePropertyPill.dataset.typeId;
            }
            
            // Get active property subtype from the visible tab
            if (state.activePropertyTab) {
                const activeTab = el(state.activePropertyTab);
                if (activeTab) {
                    const activeSubTypePill = activeTab.querySelector('.top-pill.active');
                    if (activeSubTypePill) {
                        propertySubTypeId = activeSubTypePill.dataset.value;
                    }
                }
            }
            
            // Add property_type_id to form
            if (propertyTypeId) {
                let existingInput = form.querySelector('input[name="property_type_id"]');
                if (!existingInput) {
                    existingInput = document.createElement('input');
                    existingInput.type = 'hidden';
                    existingInput.name = 'property_type_id';
                    form.appendChild(existingInput);
                }
                existingInput.value = propertyTypeId;
            }
            
            // Add property_sub_type_id to form
            if (propertySubTypeId) {
                let existingInput = form.querySelector('input[name="property_sub_type_id"]');
                if (!existingInput) {
                    existingInput = document.createElement('input');
                    existingInput.type = 'hidden';
                    existingInput.name = 'property_sub_type_id';
                    form.appendChild(existingInput);
                }
                existingInput.value = propertySubTypeId;
            }
            
            // Add furniture_type based on active tab
            const activeFurnishPill = state.activePropertyTab === 'res' 
                ? document.querySelector('#resFurnishContainer .chip.active')
                : (state.activePropertyTab === 'com' 
                    ? document.querySelector('#comFurnishContainer .chip.active')
                    : null);
                
            if (activeFurnishPill) {
                let furnInput = form.querySelector('input[name="furniture_type"]');
                if (!furnInput) {
                    furnInput = document.createElement('input');
                    furnInput.type = 'hidden';
                    furnInput.name = 'furniture_type';
                    form.appendChild(furnInput);
                }
                furnInput.value = activeFurnishPill.dataset.value;
            }
            
            // Add bhk_id for residential
            if (state.activePropertyTab === 'res') {
                const activeBhkPill = document.querySelector('#resSizeContainer .chip.active');
                if (activeBhkPill) {
                    let bhkInput = form.querySelector('input[name="bhk_id"]');
                    if (!bhkInput) {
                        bhkInput = document.createElement('input');
                        bhkInput.type = 'hidden';
                        bhkInput.name = 'bhk_id';
                        form.appendChild(bhkInput);
                    }
                    bhkInput.value = activeBhkPill.dataset.value;
                }
            }
            
            // Area is now a single common field (already in the form as input[name="area"])
            // No need to add it dynamically
            
            // Billing fields are already in the HTML form with proper names and values
            // They will be submitted naturally without manipulation
            
            // Submit the form (use native submit to bypass event listener)
            HTMLFormElement.prototype.submit.call(this);
        });
    }

    // ========================
    // STATE/CITY RELATIONSHIP
    // ========================
    const stateSelect = el('state_id');
    const citySelect = el('city_id');

    if (stateSelect && citySelect) {
        const allCities = Array.from(citySelect.options).map(option => ({
            value: option.value,
            text: option.text,
            stateId: option.getAttribute('data-state-id')
        }));

        stateSelect.addEventListener('change', function () {
            const selectedStateId = this.value;
            citySelect.innerHTML = '<option value="">Select city</option>';

            if (!selectedStateId) {
                allCities.forEach(city => {
                    if (city.value) {
                        const option = document.createElement('option');
                        option.value = city.value;
                        option.text = city.text;
                        option.setAttribute('data-state-id', city.stateId);
                        citySelect.appendChild(option);
                    }
                });
            } else {
                const filteredCities = allCities.filter(
                    city => city.stateId === selectedStateId && city.value
                );

                filteredCities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.value;
                    option.text = city.text;
                    option.setAttribute('data-state-id', city.stateId);
                    citySelect.appendChild(option);
                });

                if (filteredCities.length === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.text = 'No cities available for this state';
                    option.disabled = true;
                    citySelect.appendChild(option);
                }
            }

            citySelect.value = '';
        });
    }

    // Initialize - hide all tabs initially
    hideAllPropertyTabs();
});
