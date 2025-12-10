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
$(document).ready(function() {
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
            data: function(d) {
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
            error: function(xhr, error, code) {
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
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end', width: '80px' }
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
        drawCallback: function() {
            initializeDropdowns();
            bindDeleteHandlers();
        }
    });

    // Bind filter buttons
    $('#applyFilters').on('click', function() {
        table.draw();
    });

    $('#clearFilters').on('click', function() {
        $('#filterState').val('');
        $('#filterCity').val('');
        $('#filterStatus').val('');
        $('#filterDateRange').val('');
        table.draw();
    });

    // Filter state - cascade cities
    $('#filterState').on('change', function() {
        const stateId = $(this).val();
        const citySelect = $('#filterCity');
        
        if (stateId) {
            citySelect.find('option').each(function() {
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
    const today = moment();
    const lastMonth = moment().subtract(1, 'month');

    $('#filterDateRange').daterangepicker({
        startDate: '',
        endDate: '',
        locale: {
            format: 'DD/MM/YYYY'
        },
        autoUpdateInput: false
    });

    $('#filterDateRange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('#filterDateRange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
}

/**
 * Initialize Bootstrap dropdowns
 */
function initializeDropdowns() {
    const dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
}

/**
 * Bind delete button handlers
 */
function bindDeleteHandlers() {
    document.querySelectorAll('form[data-delete-form]').forEach(form => {
        const newForm = form.cloneNode(true);
        if (form.parentNode) {
            form.parentNode.replaceChild(newForm, form);
        }
        
        newForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const bookingId = this.getAttribute('data-booking-id');
            
            Swal.fire({
                title: 'Delete Booking?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
}

/**
 * Delete booking
 */
window.deleteBooking = function(bookingId) {
    const form = document.querySelector(`form[data-booking-id="${bookingId}"]`);
    if (form) {
        form.dispatchEvent(new Event('submit'));
    }
};
