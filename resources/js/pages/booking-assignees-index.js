/**
 * Booking Assignee Index Page
 * Handles DataTables initialization and interactions
 */
import $ from 'jquery';
// Only set if not already set (app.js might have set it)
if (typeof window.$ === 'undefined') {
    window.$ = window.jQuery = $;
}

import 'datatables.net-bs5';
import Swal from 'sweetalert2';
import moment from 'moment';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
let table = null;

// Initialize DataTable
$(document).ready(function () {
    // Wait for moment and daterangepicker from app.js (loaded by layout)
    // app.js loads moment and daterangepicker, so we should use those
    let waitAttempts = 0;
    const maxWaitAttempts = 100; // Wait up to 10 seconds

    const initDateRangePicker = () => {
        waitAttempts++;

        // Check if moment is available and has localeData function from app.js
        if (typeof window.moment === 'undefined' ||
            typeof window.moment.localeData !== 'function' ||
            typeof $.fn.daterangepicker === 'undefined') {
            // Wait for app.js to load them
            if (waitAttempts < maxWaitAttempts) {
                setTimeout(initDateRangePicker, 100);
            } else {
                console.error('Timeout waiting for moment and daterangepicker from app.js');
            }
            return;
        }

        // Ensure moment locale is set
        if (typeof window.moment.localeData === 'function') {
            window.moment.locale('en');
        }

        initializeDateRangePicker();
    };

    // Initialize daterangepicker (will use the one from app.js)
    initDateRangePicker();

    table = $('#bookingAssigneesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.pathname,
            type: 'GET',
            data: function (d) {
                // Add filter parameters
                d.state_id = $('#filterState').val() || '';
                d.city_id = $('#filterCity').val() || '';
                d.status = $('#filterStatus').val() || '';

                // Handle date range
                const dateRange = $('#filterDateRange').val();
                if (dateRange) {
                    const dates = dateRange.split(' - ');
                    if (dates.length === 2) {
                        d.date_from = dates[0];
                        d.date_to = dates[1];
                    }
                }
            },
            error: function (xhr, error, code) {
                console.error('DataTable error:', xhr, error, code);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load data'
                });
            }
        },
        columns: [
            { data: 'id', name: 'id', width: '60px' },
            { data: 'user', name: 'users.firstname' },
            { data: 'property', name: 'property_types.name' },
            { data: 'location', name: 'cities.name' },
            { data: 'booking_date', name: 'booking_date', width: '120px' },
            { data: 'status', name: 'status', width: '100px' },
            { data: 'payment_status', name: 'payment_status', width: '100px' },
            { data: 'created_by', name: 'users.firstname', searchable: false },
            { data: 'created_at', name: 'created_at', width: '150px' },
            { data: 'assign_action', name: 'assign_action', orderable: false, searchable: false, width: '100px' },
            { data: 'view_action', name: 'view_action', orderable: false, searchable: false, width: '80px' }
        ],
        order: [[8, 'desc']],
        pageLength: 10,
        language: {
            emptyTable: "No bookings found",
            processing: "Processing...",
            paginate: {
                next: '<i class="ri-arrow-right-s-line"></i>',
                previous: '<i class="ri-arrow-left-s-line"></i>'
            }
        },
        drawCallback: function () {
            initializeDropdowns();
            bindAssignButtons();
            bindReassignButtons();
            bindCancelButtons();
            // Re-initialize tooltips for dynamically rendered action buttons
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });

    // Bind filter buttons
    $('#applyFilters').on('click', function () {
        table.draw();
    });

    $('#clearFilters').on('click', function () {
        $('#filterState').val('');
        $('#filterCity').val('');
        $('#filterStatus').val('');
        $('#filterDateRange').val('');
        table.draw();
    });

    // Handle assignment form submission
    document.getElementById('assignBookingForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const bookingId = this.getAttribute('data-booking-id');
        const userId = document.getElementById('assignPhotographer').value;
        const time = document.getElementById('assignTime').value;

        if (!userId || !time) {
            Swal.fire({
                icon: 'warning',
                title: 'Required Fields',
                text: 'Please select a photographer and set a time'
            });
            return;
        }

        // Client-side double check: ensure selected time is within allowed range and aligned to slots
        const assignTimeInput = document.getElementById('assignTime');
        const modalEl = document.getElementById('assignBookingModal');
        const availableFrom = modalEl?.dataset?.photographerFrom || '08:00';
        const availableTo = modalEl?.dataset?.photographerTo || '21:00';
        const workingDuration = parseInt(modalEl?.dataset?.photographerDuration || '60', 10);

        function toMinutesLocal(t) {
            const parts = (t || '').split(':'); if (parts.length < 2) return null; return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
        }

        const timeMins = toMinutesLocal(time);
        const fromMins = toMinutesLocal(availableFrom);
        const toMins = toMinutesLocal(availableTo);
        // Note: using a select with 15-min slots; max allowed is availableTo

        if (timeMins === null) {
            Swal.fire({ icon: 'warning', title: 'Invalid Time', text: 'Please choose a valid time.' });
            return;
        }

        if (toMins < fromMins) {
            Swal.fire({ icon: 'warning', title: 'No Available Slot', text: 'No available slots configured. Please update settings.' });
            return;
        }

        if (timeMins < fromMins || timeMins > toMins) {
            Swal.fire({ icon: 'warning', title: 'Time Not Allowed', text: `Selected time is outside allowed photographer availability (${availableFrom} — ${availableTo}).` });
            return;
        }

        // Disable submit button to prevent double submission
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Assigning...';

        const formData = new FormData();
        formData.append('booking_id', bookingId);
        formData.append('user_id', userId);
        formData.append('time', time);
        formData.append('_token', csrfToken);

        const storeUrl = document.getElementById('assignBookingForm')?.getAttribute('action') || window.location.pathname;

        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                // Close modal
                const modalElement = document.getElementById('assignBookingModal');
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Booking assigned successfully',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Reload table data
                    table.draw(false);
                });
            })
            .catch(error => {
                console.error('Error:', error);

                let errorMessage = 'Failed to assign booking';
                if (error.message) {
                    errorMessage = error.message;
                } else if (error.errors) {
                    errorMessage = Object.values(error.errors).flat().join(', ');
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Assign';
            });
    });

    // Filter state - cascade cities
    $('#filterState').on('change', function () {
        const stateId = $(this).val();
        const citySelect = $('#filterCity');

        if (stateId) {
            citySelect.find('option').each(function () {
                const $option = $(this);
                if ($option.val() === '' || $option.data('state') == stateId) {
                    $option.show();
                } else {
                    $option.hide();
                }
            });
            citySelect.val('');
        } else {
            citySelect.find('option').show();
            citySelect.val('');
        }
    });
});

/**
 * Initialize Date Range Picker
 */
function initializeDateRangePicker() {
    const input = $('#filterDateRange');
    if (!input.length || input.length === 0) {
        // Element doesn't exist yet, try again later
        setTimeout(initializeDateRangePicker, 200);
        return;
    }

    // Ensure moment is available and has localeData
    if (typeof window.moment === 'undefined' || typeof window.moment.localeData !== 'function') {
        console.error('Moment.js is not available or localeData is not a function');
        // Try to fix it
        if (typeof window.moment !== 'undefined') {
            window.moment.locale('en');
        }
        if (typeof window.moment === 'undefined' || typeof window.moment.localeData !== 'function') {
            return;
        }
    }

    // Ensure daterangepicker is available
    if (typeof $.fn.daterangepicker === 'undefined') {
        console.error('Daterangepicker is not available');
        return;
    }

    // Check if already initialized
    if (input.data('daterangepicker')) {
        return;
    }

    try {
        // Use window.moment from app.js
        const momentInstance = window.moment;

        // Verify moment has localeData before proceeding
        if (typeof momentInstance === 'undefined' || typeof momentInstance.localeData !== 'function') {
            console.error('Moment is not available or localeData is not a function');
            return;
        }

        // Ensure locale is set
        momentInstance.locale('en');

        input.daterangepicker({
            startDate: moment(),
            endDate: moment().add(1, 'month'),
            locale: {
                format: 'DD/MM/YYYY'
            },
            ranges: {
                'Today': [window.moment(), window.moment()],
                'Yesterday': [window.moment().subtract(1, 'days'), window.moment().subtract(1, 'days')],
                'Last 7 Days': [window.moment().subtract(6, 'days'), window.moment()],
                'Last 30 Days': [window.moment().subtract(29, 'days'), window.moment()],
                'This Month': [window.moment().startOf('month'), window.moment().endOf('month')],
                'Last Month': [window.moment().subtract(1, 'month').startOf('month'), window.moment().subtract(1, 'month').endOf('month')],
                'This Year': [window.moment().startOf('year'), window.moment().endOf('year')],
                'Last Year': [window.moment().subtract(1, 'year').startOf('year'), window.moment().subtract(1, 'year').endOf('year')]
            },
            alwaysShowCalendars: true,
            showCustomRangeLabel: true,
            autoUpdateInput: false
        });

        input.on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            // Reload table if it exists
            if (table) {
                table.draw();
            }
        });

        input.on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('');
            // Reload table if it exists
            if (table) {
                table.draw();
            }
        });
    } catch (error) {
        console.error('Error initializing daterangepicker:', error);
    }
}

/**
 * Initialize Bootstrap dropdowns
 */
function initializeDropdowns() {
    const dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
}

/**
 * Bind assign booking buttons
 */
function bindAssignButtons() {
    document.querySelectorAll('.assign-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const bookingId = this.getAttribute('data-booking-id');
            const address = this.getAttribute('data-booking-address');
            const city = this.getAttribute('data-booking-city');
            const state = this.getAttribute('data-booking-state');
            const pincode = this.getAttribute('data-booking-pincode');
            const customer = this.getAttribute('data-booking-customer');
            const date = this.getAttribute('data-booking-date');

            // Populate booking details
            document.getElementById('modalCustomer').textContent = customer || '-';
            document.getElementById('modalAddress').textContent = address || '-';
            document.getElementById('modalCity').textContent = city || '-';
            document.getElementById('modalState').textContent = state || '-';
            document.getElementById('modalPincode').textContent = pincode || '-';
            document.getElementById('modalDate').value = date || '';

            // Reset form fields
            const assignTimeEl = document.getElementById('assignTime');
            assignTimeEl.value = '';
            const assignPhotographerEl = document.getElementById('assignPhotographer');
            assignPhotographerEl.value = '';

            // Read photographer availability from modal data attributes
            const modalEl = document.getElementById('assignBookingModal');
            const availableFrom = modalEl?.dataset?.photographerFrom || '08:00';
            const availableTo = modalEl?.dataset?.photographerTo || '21:00';
            const workingDuration = parseInt(modalEl?.dataset?.photographerDuration || '60', 10);

            function toMinutes(t) {
                const parts = (t || '').split(':');
                if (parts.length < 2) return null;
                return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
            }

            function formatHM(m) {
                const h = Math.floor(m / 60).toString().padStart(2, '0');
                const mm = (m % 60).toString().padStart(2, '0');
                return `${h}:${mm}`;
            }

            // Display in 12-hour format with AM/PM for user-friendly labels
            function formatDisplay(m) {
                const hours = Math.floor(m / 60);
                const minutes = m % 60;
                const period = hours >= 12 ? 'PM' : 'AM';
                let h12 = hours % 12;
                if (h12 === 0) h12 = 12;
                return `${h12}:${minutes.toString().padStart(2, '0')} ${period}`;
            }

            const fromM = toMinutes(availableFrom);
            const toM = toMinutes(availableTo);
            const slotStep = 15; // minutes between options

            const helper = document.getElementById('assignTimeHelper');
            const slotModeAvailable = document.getElementById('slotModeAvailable');
            const slotModeAny = document.getElementById('slotModeAny');

            const setHelper = (msg) => { if (helper) helper.textContent = msg; };
            const resetSelect = () => { assignTimeEl.disabled = true; assignTimeEl.innerHTML = '<option value="">Select a time</option>'; assignTimeEl.value = ''; };

            setHelper('Select a photographer first to see available slots from the API, or choose "Pick any" to ignore conflicts.');
            resetSelect();
            if (slotModeAvailable) slotModeAvailable.checked = true;
            if (slotModeAny) slotModeAny.checked = false;

            const getSlotMode = () => (slotModeAny?.checked ? 'any' : 'available');

            function loadSlots() {
                loadSlotsForElement(
                    assignPhotographerEl,
                    assignTimeEl,
                    helper,
                    document.getElementById('modalDate'),
                    modalEl,
                    slotModeAny,
                    availableFrom,
                    availableTo,
                    workingDuration
                );
            }

            assignPhotographerEl.onchange = loadSlots;
            if (slotModeAvailable) slotModeAvailable.onchange = loadSlots;
            if (slotModeAny) slotModeAny.onchange = loadSlots;

            const form = document.getElementById('assignBookingForm');
            form.setAttribute('data-booking-id', bookingId);

            const modal = new bootstrap.Modal(document.getElementById('assignBookingModal'));
            modal.show();
        });
    });
}

/**
 * Bind reassign booking buttons
 */
function bindReassignButtons() {
    document.querySelectorAll('.reassign-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const assigneeId = this.getAttribute('data-assignee-id');
            const bookingId = this.getAttribute('data-booking-id');
            const currentPhotographerId = this.getAttribute('data-current-photographer-id');
            const currentTime = this.getAttribute('data-current-time');
            const address = this.getAttribute('data-booking-address');
            const city = this.getAttribute('data-booking-city');
            const state = this.getAttribute('data-booking-state');
            const pincode = this.getAttribute('data-booking-pincode');
            const customer = this.getAttribute('data-booking-customer');
            const date = this.getAttribute('data-booking-date');

            // Populate reassignment modal
            document.getElementById('reassignAssigneeId').value = assigneeId;
            document.getElementById('reassignModalCustomer').textContent = customer || '-';
            document.getElementById('reassignModalAddress').textContent = address || '-';
            document.getElementById('reassignModalCity').textContent = city || '-';
            document.getElementById('reassignModalState').textContent = state || '-';
            document.getElementById('reassignModalPincode').textContent = pincode || '-';
            document.getElementById('reassignModalDate').value = date || '';

            // Show current assignment info
            const currentPhotographerEl = document.querySelector(`#assignPhotographer option[value="${currentPhotographerId}"]`);
            const currentPhotographerName = currentPhotographerEl ? currentPhotographerEl.textContent : 'Unknown';
            document.getElementById('currentPhotographerName').textContent = currentPhotographerName;
            document.getElementById('currentAssignedTime').textContent = currentTime || '-';

            // Reset form fields
            const reassignPhotographerEl = document.getElementById('reassignPhotographer');
            const reassignTimeEl = document.getElementById('reassignTime');
            reassignPhotographerEl.value = '';
            reassignTimeEl.value = '';
            reassignTimeEl.disabled = true;

            // Setup slot loading for reassignment
            const modalEl = document.getElementById('reassignBookingModal');
            const availableFrom = modalEl?.dataset?.photographerFrom || '08:00';
            const availableTo = modalEl?.dataset?.photographerTo || '21:00';
            const workingDuration = parseInt(modalEl?.dataset?.photographerDuration || '60', 10);

            const helper = document.getElementById('reassignTimeHelper');
            const slotModeAvailable = document.getElementById('reassignSlotModeAvailable');
            const slotModeAny = document.getElementById('reassignSlotModeAny');

            const setHelper = (msg) => { if (helper) helper.textContent = msg; };
            const resetSelect = () => {
                reassignTimeEl.disabled = true;
                reassignTimeEl.innerHTML = '<option value="">Select a time</option>';
                reassignTimeEl.value = '';
            };

            setHelper('Select a photographer first.');
            resetSelect();
            if (slotModeAvailable) slotModeAvailable.checked = true;
            if (slotModeAny) slotModeAny.checked = false;

            const loadReassignSlots = () => {
                loadSlotsForElement(
                    reassignPhotographerEl,
                    reassignTimeEl,
                    helper,
                    document.getElementById('reassignModalDate'),
                    modalEl,
                    slotModeAny,
                    availableFrom,
                    availableTo,
                    workingDuration
                );
            };

            reassignPhotographerEl.onchange = loadReassignSlots;
            if (slotModeAvailable) slotModeAvailable.onchange = loadReassignSlots;
            if (slotModeAny) slotModeAny.onchange = loadReassignSlots;

            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    });

    // Handle reassignment form submission
    const reassignForm = document.getElementById('reassignBookingForm');
    if (reassignForm && !reassignForm.hasAttribute('data-listener-attached')) {
        reassignForm.setAttribute('data-listener-attached', 'true');
        reassignForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const assigneeId = document.getElementById('reassignAssigneeId').value;
            const userId = document.getElementById('reassignPhotographer').value;
            const time = document.getElementById('reassignTime').value;

            if (!userId || !time) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required Fields',
                    text: 'Please select a photographer and time'
                });
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Reassigning...';

            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('time', time);
            formData.append('_token', csrfToken);

            fetch(`${window.location.pathname}/${assigneeId}/reassign`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    const modalElement = document.getElementById('reassignBookingModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) {
                        modalInstance.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message || 'Booking reassigned successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        table.draw(false);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    let errorMessage = 'Failed to reassign booking';
                    if (error.message) {
                        errorMessage = error.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Reassign';
                });
        });
    }
}

/**
 * Bind cancel assignment buttons
 */
function bindCancelButtons() {
    document.querySelectorAll('.cancel-assignment-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const assigneeId = this.getAttribute('data-assignee-id');
            const bookingId = this.getAttribute('data-booking-id');
            const photographerName = this.getAttribute('data-photographer-name');

            Swal.fire({
                title: 'Cancel Assignment?',
                html: `Are you sure you want to cancel the assignment for <strong>${photographerName}</strong>?<br><small class="text-muted">This will revert the booking status to "Schedul Accepted".</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Cancel Assignment',
                cancelButtonText: 'No, Keep It'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Cancelling...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`${window.location.pathname}/${assigneeId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => Promise.reject(err));
                            }
                            return response.json();
                        })
                        .then(data => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cancelled!',
                                text: data.message || 'Assignment cancelled successfully',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                table.draw(false);
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error.message || 'Failed to cancel assignment'
                            });
                        });
                }
            });
        });
    });
}

/**
 * Helper function to load slots for any element (reusable for assign and reassign)
 */
function loadSlotsForElement(photographerSel, timeSel, helper, dateEl, modalEl, slotModeAnyEl, availableFrom, availableTo, workingDuration) {
    const toMinutes = (t) => {
        const parts = (t || '').split(':');
        if (parts.length < 2) return null;
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    };

    const formatHM = (m) => {
        const h = Math.floor(m / 60).toString().padStart(2, '0');
        const mm = (m % 60).toString().padStart(2, '0');
        return `${h}:${mm}`;
    };

    const formatDisplay = (m) => {
        const hours = Math.floor(m / 60);
        const minutes = m % 60;
        const period = hours >= 12 ? 'PM' : 'AM';
        let h12 = hours % 12;
        if (h12 === 0) h12 = 12;
        return `${h12}:${minutes.toString().padStart(2, '0')} ${period}`;
    };

    const fromM = toMinutes(availableFrom);
    const toM = toMinutes(availableTo);
    const slotStep = 15;

    const setHelper = (msg) => { if (helper) helper.textContent = msg; };
    const resetSelect = () => {
        timeSel.disabled = true;
        timeSel.innerHTML = '<option value="">Select a time</option>';
        timeSel.value = '';
    };

    const getSlotMode = () => (slotModeAnyEl?.checked ? 'any' : 'available');

    // Helper to check if time is in the past
    const isPastTime = (timeInMinutes, selectedDate) => {
        if (!selectedDate) return false;

        const now = new Date();
        const [year, month, day] = selectedDate.split('-').map(Number);
        const selectedDateObj = new Date(year, month - 1, day);
        const todayDateObj = new Date(now.getFullYear(), now.getMonth(), now.getDate());

        if (selectedDateObj.getTime() !== todayDateObj.getTime()) {
            return false;
        }

        const currentMinutes = now.getHours() * 60 + now.getMinutes();
        return timeInMinutes <= currentMinutes;
    };

    const buildAllSlots = () => {
        timeSel.innerHTML = '<option value="">Select a time</option>';
        if (toM < fromM) return;
        const dateVal = dateEl.value;
        for (let t = fromM; t <= toM; t += slotStep) {
            if (dateVal && isPastTime(t, dateVal)) continue;

            const opt = document.createElement('option');
            opt.value = formatHM(t);
            opt.textContent = formatDisplay(t);
            timeSel.appendChild(opt);
        }
        if (timeSel.options.length > 1) {
            timeSel.disabled = false;
            timeSel.value = timeSel.options[1].value;
        } else {
            timeSel.disabled = true;
        }
    };

    if (!photographerSel.value) {
        resetSelect();
        setHelper('Select a photographer first to see available slots from the API, or choose "Pick any" to ignore conflicts.');
        return;
    }

    if (toM < fromM) {
        resetSelect();
        setHelper('No available slots for photographers. Please update settings.');
        return;
    }

    const dateVal = dateEl.value;
    if (!dateVal) {
        resetSelect();
        setHelper('Please select a booking date first.');
        return;
    }

    if (getSlotMode() === 'any') {
        buildAllSlots();
        setHelper(`Pick any slot between ${formatDisplay(fromM)} — ${formatDisplay(toM)} (every ${slotStep} min)`);
        return;
    }

    setHelper('Loading photographer slots...');
    timeSel.disabled = true;
    timeSel.innerHTML = '<option value="">Loading...</option>';

    fetch(`${window.appBaseUrl || ''}/api/booking-assignees/slots?date=${encodeURIComponent(dateVal)}&user_id=${encodeURIComponent(photographerSel.value)}`, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) {
                if (response.status === 403) {
                    return response.json().then(err => {
                        throw new Error(err?.message || 'Access denied');
                    });
                }
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(json => {
            if (!json || json.success === false) {
                setHelper(json?.message || 'Failed to load slots');
                resetSelect();
                return;
            }

            const occupiedIntervals = [];

            (json.data || []).forEach(s => {
                if (!s.time) return;
                const parts = s.time.split(':');
                if (parts.length < 2) return;
                const start = parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
                const bufferTime = workingDuration;
                const end = start + workingDuration + bufferTime;
                occupiedIntervals.push({ start, end });
            });

            timeSel.innerHTML = '<option value="">Select a time</option>';
            for (let t = fromM; t <= toM; t += slotStep) {
                const candidateStart = t;
                const candidateEnd = t + workingDuration;

                if (dateVal && isPastTime(candidateStart, dateVal)) continue;

                let overlaps = false;
                for (const occ of occupiedIntervals) {
                    if (candidateStart < occ.end && candidateEnd > occ.start) {
                        overlaps = true;
                        break;
                    }
                }

                if (overlaps) continue;

                const opt = document.createElement('option');
                opt.value = formatHM(t);
                opt.textContent = formatDisplay(t);
                timeSel.appendChild(opt);
            }

            if (timeSel.options.length <= 1) {
                resetSelect();
                setHelper('No available slots on this date for selected photographer.');
            } else {
                timeSel.disabled = false;
                if (!timeSel.value && timeSel.options.length > 1) {
                    timeSel.value = timeSel.options[1].value;
                }
                timeSel.focus();
                setHelper(`Available slots: ${formatDisplay(fromM)} — ${formatDisplay(toM)} (every ${slotStep} min)`);
            }
        })
        .catch(err => {
            console.error('Error loading slots:', err);
            setHelper(err?.message || 'Failed to load slots.');
            resetSelect();
        });
}