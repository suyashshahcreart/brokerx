$(document).ready(function () {
    // Initialize DataTable
    let table = $('#bookings-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.href,
            data: function (d) {
                d.status = $('#filter-status').val();
                d.payment_status = $('#filter-payment-status').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
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
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
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
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
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
            url: '/admin/tour-manager/schedule-tour',
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
