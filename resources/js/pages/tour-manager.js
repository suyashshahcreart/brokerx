import moment from 'moment';

window.moment = moment;

$(document).ready(function () {
    // Initialize daterangepicker
    $('#filter-date-range').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            format: 'YYYY-MM-DD'
        },
        opens: 'left',
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'This Year': [moment().startOf('year'), moment().endOf('year')],
            'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
        },
        alwaysShowCalendars: true,
        showCustomRangeLabel: true
    });

    $('#filter-date-range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('#filter-date-range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    // Initialize DataTable
    let table = $('#bookings-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.href,
            data: function (d) {
                d.status = $('#filter-status').val();
                d.payment_status = $('#filter-payment-status').val();
                
                // Handle date range
                const dateRange = $('#filter-date-range').val();
                if (dateRange) {
                    const dates = dateRange.split(' - ');
                    if (dates.length === 2) {
                        d.date_from = dates[0];
                        d.date_to = dates[1];
                    }
                }
            }
        },
        columns: [
            { data: 'booking_info', name: 'id' },
            { data: 'customer', name: 'user.firstname' },
            { data: 'location', name: 'city.name' },
            // { data: 'booking_date', name: 'booking_date' },
            { data: 'status', name: 'status' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'price', name: 'price' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            paginate: {
                previous: "<i class='ri-arrow-left-s-line'></i>",
                next: "<i class='ri-arrow-right-s-line'></i>"
            }
        },
        drawCallback: function () {
            // Re-initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });

    // Apply filters
    $('#apply-filters').on('click', function () {
        table.draw();
    });

    // Reset filters
    $('#reset-filters').on('click', function () {
        $('#filter-status').val('');
        $('#filter-payment-status').val('');
        $('#filter-date-range').val('');
        table.draw();
    });

    // Schedule tour modal
    $(document).on('click', '.schedule-tour-btn', function () {
        let bookingId = $(this).data('id');
        $('#booking-id').val(bookingId);

        // Use Bootstrap 5 modal API
        const modalElement = document.getElementById('scheduleTourModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    });

    // Handle schedule tour form submission
    $('#schedule-tour-form').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: `${window.appBaseUrl}/${window.adminBasePath}/tour-manager/schedule-tour`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    // Use Bootstrap 5 modal API to hide
                    const modalElement = document.getElementById('scheduleTourModal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                    }
                    $('#schedule-tour-form')[0].reset();
                    table.draw();

                    // Show success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Tour scheduled successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(response.message || 'Tour scheduled successfully!');
                    }
                }
            },
            error: function (xhr) {
                let errorMessage = 'An error occurred while scheduling the tour.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                // Show error message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                } else {
                    alert(errorMessage);
                }
            }
        });
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
