/**
 * Bookings Form - Create/Edit
 * Handles dynamic property subtype filtering and form validation
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Form validation
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }

    // Dynamic Property Subtype Filtering
    const propertyTypeSelect = document.getElementById('property_type_id');
    const propertySubTypeSelect = document.getElementById('property_sub_type_id');

    if (propertyTypeSelect && propertySubTypeSelect) {
        // Store all subtypes on page load
        const allSubTypes = Array.from(propertySubTypeSelect.options).map(option => ({
            value: option.value,
            text: option.text,
            typeId: option.getAttribute('data-property-type-id')
        }));

        // Filter subtypes based on selected property type
        propertyTypeSelect.addEventListener('change', function () {
            const selectedTypeId = this.value;
            
            // Clear current options except the first placeholder
            propertySubTypeSelect.innerHTML = '<option value="">Select subtype</option>';
            
            if (!selectedTypeId) {
                // If no type selected, show all subtypes
                allSubTypes.forEach(subType => {
                    if (subType.value) {
                        const option = document.createElement('option');
                        option.value = subType.value;
                        option.text = subType.text;
                        option.setAttribute('data-property-type-id', subType.typeId);
                        propertySubTypeSelect.appendChild(option);
                    }
                });
            } else {
                // Filter and show only matching subtypes
                const filteredSubTypes = allSubTypes.filter(
                    subType => subType.typeId === selectedTypeId && subType.value
                );
                
                filteredSubTypes.forEach(subType => {
                    const option = document.createElement('option');
                    option.value = subType.value;
                    option.text = subType.text;
                    option.setAttribute('data-property-type-id', subType.typeId);
                    propertySubTypeSelect.appendChild(option);
                });

                // Show message if no subtypes available
                if (filteredSubTypes.length === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.text = 'No subtypes available for this type';
                    option.disabled = true;
                    propertySubTypeSelect.appendChild(option);
                }
            }

            // Reset subtype selection
            propertySubTypeSelect.value = '';
        });
    }

    // State/City Relationship (Optional - if cities depend on states)
    const stateSelect = document.getElementById('state_id');
    const citySelect = document.getElementById('city_id');

    if (stateSelect && citySelect) {
        // Store all cities on page load
        const allCities = Array.from(citySelect.options).map(option => ({
            value: option.value,
            text: option.text,
            stateId: option.getAttribute('data-state-id')
        }));

        stateSelect.addEventListener('change', function () {
            const selectedStateId = this.value;
            
            // Clear current options except the first placeholder
            citySelect.innerHTML = '<option value="">Select city</option>';
            
            if (!selectedStateId) {
                // If no state selected, show all cities
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
                // Filter and show only matching cities
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

                // Show message if no cities available
                if (filteredCities.length === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.text = 'No cities available for this state';
                    option.disabled = true;
                    citySelect.appendChild(option);
                }
            }

            // Reset city selection
            citySelect.value = '';
        });
    }

    // Price formatting (add commas for readability)
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('blur', function () {
            if (this.value) {
                const value = parseInt(this.value.replace(/,/g, ''));
                if (!isNaN(value)) {
                    this.dataset.rawValue = value;
                }
            }
        });
    }

    // Area validation - prevent negative values
    const areaInput = document.getElementById('area');
    if (areaInput) {
        areaInput.addEventListener('input', function () {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    }

    // Auto-calculate full address from individual fields
    const addressFields = {
        house_no: document.getElementById('house_no'),
        building: document.getElementById('building'),
        society_name: document.getElementById('society_name'),
        address_area: document.getElementById('address_area'),
        landmark: document.getElementById('landmark'),
        pin_code: document.getElementById('pin_code')
    };
    const fullAddressField = document.getElementById('full_address');

    if (fullAddressField && Object.values(addressFields).every(field => field)) {
        const updateFullAddress = () => {
            // Only auto-fill if full address is empty
            if (!fullAddressField.value.trim()) {
                const parts = [];
                
                if (addressFields.house_no.value) parts.push(addressFields.house_no.value);
                if (addressFields.building.value) parts.push(addressFields.building.value);
                if (addressFields.society_name.value) parts.push(addressFields.society_name.value);
                if (addressFields.address_area.value) parts.push(addressFields.address_area.value);
                if (addressFields.landmark.value) parts.push('Near ' + addressFields.landmark.value);
                
                const cityText = citySelect?.options[citySelect.selectedIndex]?.text;
                const stateText = stateSelect?.options[stateSelect.selectedIndex]?.text;
                
                if (cityText && cityText !== 'Select city') parts.push(cityText);
                if (stateText && stateText !== 'Select state') parts.push(stateText);
                if (addressFields.pin_code.value) parts.push(addressFields.pin_code.value);

                fullAddressField.value = parts.join(', ');
            }
        };

        // Add button to trigger auto-fill
        const fullAddressWrapper = fullAddressField.parentElement;
        const autoFillBtn = document.createElement('button');
        autoFillBtn.type = 'button';
        autoFillBtn.className = 'btn btn-sm btn-soft-secondary mt-1';
        autoFillBtn.innerHTML = '<i class="ri-refresh-line me-1"></i> Auto-fill from fields above';
        autoFillBtn.addEventListener('click', function() {
            fullAddressField.value = ''; // Clear first
            updateFullAddress();
        });
        
        if (!fullAddressWrapper.querySelector('.btn-soft-secondary')) {
            fullAddressWrapper.appendChild(autoFillBtn);
        }
    }
});
