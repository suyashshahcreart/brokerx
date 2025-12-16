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
import 'bootstrap/js/dist/dropdown';
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
            { data: 'user', name: 'user.name' },
            { data: 'property', name: 'propertyType.name' },
            { data: 'location', name: 'city.name' },
            { data: 'booking_date', name: 'booking_date', width: '120px' },
            { data: 'status', name: 'status', width: '100px' },
            { data: 'payment_status', name: 'payment_status', width: '100px' },
            { data: 'created_by', name: 'creator.name' },
            { data: 'created_at', name: 'created_at', width: '150px' },
            { data: 'assign_action', name: 'assign_action', orderable: false, searchable: false, width: '100px' },
            { data: 'view_action', name: 'view_action', orderable: false, searchable: false, width: '80px' }
        ],
        order: [[8, 'desc']],
        pageLength: 25,
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
            startDate: momentInstance(),
            endDate: momentInstance().add(1, 'month'),
            locale: {
                format: 'DD/MM/YYYY'
            },
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
            document.getElementById('assignTime').value = '';
            document.getElementById('assignPhotographer').value = '';

            const form = document.getElementById('assignBookingForm');
            form.setAttribute('data-booking-id', bookingId);

            const modal = new bootstrap.Modal(document.getElementById('assignBookingModal'));
            modal.show();
        });
    });
}