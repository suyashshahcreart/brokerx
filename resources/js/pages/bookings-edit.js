/**
 * Bookings Edit - Tabbed AJAX Forms with Dynamic Property Selection
 * Same logic as create page
 */

import Swal from 'sweetalert2';

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
    const tabRes = el('tab-res');
    const tabCom = el('tab-com');
    const tabOth = el('tab-oth');
    
    if (tabRes) tabRes.classList.add('hidden');
    if (tabCom) tabCom.classList.add('hidden');
    if (tabOth) tabOth.classList.add('hidden');
    
    // Remove active state from all property type pills
    ['pillResidential', 'pillCommercial', 'pillOther'].forEach(id => {
        const pill = el(id);
        if (pill) pill.classList.remove('active');
    });
    // Clear main property type hidden field
    const mainType = el('mainPropertyType');
    if (mainType) mainType.value = '';
    state.activePropertyTab = null;
}

function switchMainTab(key) {
    if (!key) {
        hideAllPropertyTabs();
        return;
    }
    state.activePropertyTab = key;
    
    // Hide all tabs first
    const tabRes = el('tab-res');
    const tabCom = el('tab-com');
    const tabOth = el('tab-oth');
    
    if (tabRes) tabRes.classList.add('hidden');
    if (tabCom) tabCom.classList.add('hidden');
    if (tabOth) tabOth.classList.add('hidden');
    
    // Show the selected tab
    if (key === 'res' && tabRes) {
        tabRes.classList.remove('hidden');
    }
    if (key === 'com' && tabCom) {
        tabCom.classList.remove('hidden');
    }
    if (key === 'oth' && tabOth) {
        tabOth.classList.remove('hidden');
    }
    
    // Update active state on property type pills
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
    const mainType = el('mainPropertyType');
    if (mainType) mainType.value = typeMap[key] || 'Residential';
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
    const h = el('house_no')?.value?.trim();
    const b = el('building')?.value?.trim();
    const s = el('society_name')?.value?.trim();
    const a = el('address_area')?.value?.trim();
    const l = el('landmark')?.value?.trim();
    const p = el('pin_code')?.value?.trim();
    const f = el('full_address')?.value?.trim();
    return !!(h || b || s || a || l || p || f);
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
    ['house_no', 'building', 'society_name', 'address_area', 'landmark', 'pin_code', 'full_address', 'city_id', 'state_id'].forEach(id => {
        const input = el(id);
        if (input) {
            input.value = '';
            input.classList.remove('is-valid', 'is-invalid');
        }
    });
}

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
// GLOBAL FUNCTIONS (Called from HTML onclick)
// ========================

// Handle property type tab changes (Residential/Commercial/Other)
window.handlePropertyTabChange = async function(key) {
    // Skip confirmation if we're restoring old values from validation error
    const skipConfirmation = window.isRestoringOldValues;
    
    // Check if property type was already set, user is trying to change it, AND there's actual data filled
    if (!skipConfirmation && state.activePropertyTab && state.activePropertyTab !== key && (hasPropertyDataFilled() || hasAddressDataFilled())) {
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
    switchMainTab(key);
    
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
// DOCUMENT READY
// ========================
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    
    // Get CSRF token
    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenElement) {
        Swal.fire({
            icon: 'error',
            title: 'Configuration Error',
            text: 'CSRF token not found. Please refresh the page.',
        });
        return;
    }
    const csrfToken = csrfTokenElement.content;
    
    // Get booking data from window
    const bookingData = window.bookingData || {};
    const bookingId = bookingData.id || bookingData.bookingId;
    const tourData = bookingData.tour || null;

    // ========================
    // INITIALIZE ACTIVE TAB FROM BOOKING DATA
    // ========================
    // Check which property type is currently selected and set state
    if (el('pillResidential')?.classList.contains('active')) {
        state.activePropertyTab = 'res';
    } else if (el('pillCommercial')?.classList.contains('active')) {
        state.activePropertyTab = 'com';
    } else if (el('pillOther')?.classList.contains('active')) {
        state.activePropertyTab = 'oth';
    }

    // ========================
    // INITIALIZE FURNISH TYPE SELECTION ON PAGE LOAD
    // ========================
    // Get furniture type from booking old values (set by blade template)
    const furnitureType = window.bookingOldValues?.furniture_type;
    
    if (furnitureType) {
        // Normalize furniture type: handle both "Semi Furnished" (space) and "Semi-Furnished" (hyphen)
        // Also handle "Fully Furnished" -> "Furnished"
        let normalizedFurnitureType = furnitureType;
        if (furnitureType === 'Semi Furnished') {
            normalizedFurnitureType = 'Semi-Furnished';
        } else if (furnitureType === 'Fully Furnished') {
            normalizedFurnitureType = 'Furnished';
        }
        
        // Wait a bit for tabs to be properly initialized
        setTimeout(() => {
            // Determine which container to use based on active property tab
            let furnishContainer = null;
            if (state.activePropertyTab === 'res') {
                furnishContainer = el('resFurnishContainer');
            } else if (state.activePropertyTab === 'com') {
                furnishContainer = el('comFurnishContainer');
            }
            
            if (furnishContainer) {
                // Find the chip with matching data-value (use normalized value)
                const matchingChip = furnishContainer.querySelector(`[data-value="${normalizedFurnitureType}"]`);
                if (matchingChip) {
                    // Remove active from all chips in this container
                    furnishContainer.querySelectorAll('.chip').forEach(chip => {
                        chip.classList.remove('active');
                    });
                    // Add active to the matching chip
                    matchingChip.classList.add('active');
                }
            }
        }, 100);
    }

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
            
            if (state.activePropertyTab === 'res') {
                const activePill = document.querySelector('#resTypeContainer .top-pill.active');
                if (activePill) {
                    propertySubTypeId = activePill.dataset.value;
                }
                // Get property type ID for Residential
                const resPill = el('pillResidential');
                if (resPill) propertyTypeId = resPill.dataset.typeId;
            } else if (state.activePropertyTab === 'com') {
                const activePill = document.querySelector('#comTypeContainer .top-pill.active');
                if (activePill) {
                    propertySubTypeId = activePill.dataset.value;
                }
                // Get property type ID for Commercial
                const comPill = el('pillCommercial');
                if (comPill) propertyTypeId = comPill.dataset.typeId;
            } else if (state.activePropertyTab === 'oth') {
                const activePill = document.querySelector('#othLookingContainer .top-pill.active');
                if (activePill) {
                    propertySubTypeId = activePill.dataset.value;
                }
                // Get property type ID for Other
                const othPill = el('pillOther');
                if (othPill) propertyTypeId = othPill.dataset.typeId;
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
            
            // Ensure user_id is captured and submitted (in case Choices.js interferes)
            const userIdSelect = form.querySelector('select[name="user_id"]');
            if (userIdSelect) {
                const userIdValue = userIdSelect.value;
                if (userIdValue) {
                    let userIdInput = form.querySelector('input[name="user_id"]');
                    if (!userIdInput) {
                        userIdInput = document.createElement('input');
                        userIdInput.type = 'hidden';
                        userIdInput.name = 'user_id';
                        form.appendChild(userIdInput);
                    }
                    userIdInput.value = userIdValue;
                }
            }
            
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

    // ========================
    // TOUR CREATE FORM HANDLING
    // ========================
    const tourCreateForm = document.getElementById('tourCreateForm');
    const createTourBtn = document.getElementById('createTourBtn');
    const cancelCreateTourBtn = document.getElementById('cancelCreateTourBtn');
    const noTourMessage = document.getElementById('noTourMessage');

    if (createTourBtn && tourCreateForm) {
        createTourBtn.addEventListener('click', function () {
            noTourMessage?.classList.add('d-none');
            tourCreateForm.classList.remove('d-none');
        });
    }

    if (cancelCreateTourBtn && tourCreateForm) {
        cancelCreateTourBtn.addEventListener('click', function () {
            tourCreateForm.classList.add('d-none');
            noTourMessage?.classList.remove('d-none');
            tourCreateForm.reset();
            tourCreateForm.classList.remove('was-validated');
        });
    }

    // Unlink tour button
    const unlinkBtn = document.getElementById('unlinkTourBtn');
    if (unlinkBtn) {
        unlinkBtn.addEventListener('click', async function () {
            const tourId = document.getElementById('tour_id')?.value;
            if (!tourId) return;

            const result = await Swal.fire({
                title: 'Unlink Tour?',
                text: 'This will remove the link between this booking and the tour. The tour will still exist.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, unlink it',
                cancelButtonText: 'Cancel',
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/admin/tours/${tourId}/unlink-ajax`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    const responseData = await response.json();

                    if (response.ok && responseData.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Unlinked!',
                            text: responseData.message || 'Tour has been unlinked',
                            timer: 2000,
                            showConfirmButton: false,
                        });
                        // Reload page to show updated state
                        window.location.reload();
                    } else {
                        throw new Error(responseData.message || 'Failed to unlink tour');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'An error occurred while unlinking the tour',
                    });
                }
            }
        });
    }
});
