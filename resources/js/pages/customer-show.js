$(document).ready(function () {
    const table = $('#customer-bookings-table')
    table.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.href || '',
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
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user', name: 'user.firstname', orderable: false, searchable: false },
            { data: 'type_subtype', name: 'propertyType.name', orderable: false, searchable: false },
            { data: 'bhk', name: 'bhk.name', orderable: false, searchable: false },
            { data: 'city_state', name: 'city.name', orderable: false, searchable: false },
            { data: 'area', name: 'area' },
            { data: 'price', name: 'price' },
            { data: 'booking_date', name: 'booking_date' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'payment_status', name: 'payment_status', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ],
        language: {
            search: '_INPUT_',
            searchPlaceholder: 'Search bookings...',
            emptyTable: "No bookings found",
            processing: "Processing...",
            paginate: {
                next: '<i class="ri-arrow-right-s-line"></i>',
                previous: '<i class="ri-arrow-left-s-line"></i>'
            }
        },
        lengthMenu: [10, 25, 50, 100],
        responsive: true
    });
});