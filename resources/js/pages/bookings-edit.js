/**
 * Bookings Edit - Tabbed AJAX Forms with SweetAlert
 * Handles booking and tour forms with AJAX submissions
 */

import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Get booking data from window
    const bookingData = window.bookingData || {};
    const bookingId = bookingData.bookingId;
    const tourData = bookingData.tour || null;

    // ========================
    // BOOKING FORM HANDLING
    // ========================
    const bookingForm = document.getElementById('bookingEditForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (!bookingForm.checkValidity()) {
                bookingForm.classList.add('was-validated');
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields correctly.',
                });
                return;
            }

            // Collect form data
            const formData = new FormData(bookingForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(`/admin/bookings/${bookingId}/update-ajax`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message || 'Booking updated successfully',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    bookingForm.classList.remove('was-validated');
                } else {
                    throw new Error(result.message || 'Failed to update booking');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'An error occurred while updating the booking',
                });
            }
        });
    }

    // ========================
    // TOUR EDIT FORM HANDLING
    // ========================
    const tourEditForm = document.getElementById('tourEditForm');
    if (tourEditForm) {
        tourEditForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (!tourEditForm.checkValidity()) {
                tourEditForm.classList.add('was-validated');
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields correctly.',
                });
                return;
            }

            const tourId = document.getElementById('tour_id')?.value;
            if (!tourId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Tour ID not found',
                });
                return;
            }

            const formData = new FormData(tourEditForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(`/admin/tours/${tourId}/update-ajax`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message || 'Tour updated successfully',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    tourEditForm.classList.remove('was-validated');
                } else {
                    throw new Error(result.message || 'Failed to update tour');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'An error occurred while updating the tour',
                });
            }
        });

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
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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

    if (tourCreateForm) {
        tourCreateForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (!tourCreateForm.checkValidity()) {
                tourCreateForm.classList.add('was-validated');
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields correctly.',
                });
                return;
            }

            const formData = new FormData(tourCreateForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('/admin/tours/create-ajax', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message || 'Tour created successfully',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    // Reload page to show the new tour in edit mode
                    window.location.reload();
                } else {
                    throw new Error(result.message || 'Failed to create tour');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'An error occurred while creating the tour',
                });
            }
        });
    }

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
