/**
 * Bookings Form - Create Page
 * Handles form utilities for booking creation
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ========================
    // FORM UTILITIES
    // ========================

    // Dynamic Property Subtype Filtering
    const propertyTypeSelect = document.getElementById('property_type_id');
    const propertySubTypeSelect = document.getElementById('property_sub_type_id');

    if (propertyTypeSelect && propertySubTypeSelect) {
        const allSubTypes = Array.from(propertySubTypeSelect.options).map(option => ({
            value: option.value,
            text: option.text,
            typeId: option.getAttribute('data-property-type-id')
        }));

        propertyTypeSelect.addEventListener('change', function () {
            const selectedTypeId = this.value;
            propertySubTypeSelect.innerHTML = '<option value="">Select subtype</option>';

            if (!selectedTypeId) {
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

                if (filteredSubTypes.length === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.text = 'No subtypes available for this type';
                    option.disabled = true;
                    propertySubTypeSelect.appendChild(option);
                }
            }

            propertySubTypeSelect.value = '';
        });
    }

    // State/City Relationship
    const stateSelect = document.getElementById('state_id');
    const citySelect = document.getElementById('city_id');

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
});
