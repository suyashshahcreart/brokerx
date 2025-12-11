/**
 * Booking Assignee Index Page
 * Handles DataTables initialization and interactions
 */
import $ from 'jquery';
import 'datatables.net-bs5';
import moment from 'moment';
import Swal from 'sweetalert2';
import 'bootstrap/js/dist/dropdown';

const baseUrl = window.location.origin + '/brokerx';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
let table = null;

// Initialize DataTable
$(document).ready(function () {
    // Initialize daterangepicker - it should be available from CDN script tag
    if (typeof $.fn.daterangepicker === 'function') {
        initializeDateRangePicker();
    } else {
        console.warn('daterangepicker not available, waiting...');
        setTimeout(() => {
            if (typeof $.fn.daterangepicker === 'function') {
                initializeDateRangePicker();
            }
        }, 500);
    }

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

        fetch('/brokerx/admin/booking-assignees', {
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
    $('#filterDateRange').daterangepicker({
        startDate: moment(),
        endDate: moment().add(1, 'month'),
        locale: {
            format: 'DD/MM/YYYY'
        }
    });

    $('#filterDateRange').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('#filterDateRange').on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
    });
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
            form.action = '/brokerx/admin/booking-assignees';
            form.setAttribute('data-booking-id', bookingId);

            const modal = new bootstrap.Modal(document.getElementById('assignBookingModal'));
            modal.show();
        });
    });
}