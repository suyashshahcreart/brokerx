import $ from 'jquery';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import moment from 'moment';
import 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'daterangepicker';
import 'daterangepicker/daterangepicker.css';

// Match booking index globals
window.$ = $;
window.jQuery = $;
window.flatpickr = flatpickr;
window.moment = moment;
moment.locale('en');

document.addEventListener('DOMContentLoaded', function () {
    const $table = $('#bookings-report-table');
    if (!$table.length) return;

    const $state = $('#filterState');
    const $city = $('#filterCity');
    const $status = $('#filterStatus');
    const $dateRange = $('#filterDateRange');

    // Daterangepicker (same pattern as booking-index)
    let dateRangePicker = null;
    const initDateRangePicker = () => {
        if (typeof window.moment === 'undefined' || typeof $.fn.daterangepicker === 'undefined') {
            setTimeout(initDateRangePicker, 100);
            return;
        }
        initializeDateRangePicker();
    };

    function initializeDateRangePicker() {
        if (!$dateRange.length) return;

        if ($dateRange.data('daterangepicker')) return;

        try {
            dateRangePicker = $dateRange.daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                },
                opens: 'left'
            });

            $dateRange.on('apply.daterangepicker', function (_ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $dateRange.on('cancel.daterangepicker', function () {
                $(this).val('');
            });
        } catch (error) {
            console.error('Error initializing daterangepicker:', error);
        }
    }

    setTimeout(initDateRangePicker, 300);

    const dataTable = $table.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.bookingReportUrl || '/ppadmlog/reports/bookings',
            type: 'GET',
            data: function (d) {
                d.state_id = $state.val() || '';
                d.city_id = $city.val() || '';
                d.status = $status.val() || '';

                const dateRange = $dateRange.val();
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
        lengthMenu: [10, 25, 50, 100],
        responsive: true,
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
        drawCallback: function () {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });

    // DataTables error logging (shows server response if available)
    $.fn.dataTable.ext.errMode = 'none';
    $table.on('error.dt', function (_e, settings, techNote, message) {
        console.error('DataTables error:', message);
        if (settings && settings.jqXHR && settings.jqXHR.responseText) {
            console.error('Response:', settings.jqXHR.responseText.substring(0, 1000));
        }
    });

    // Filters
    $('#applyFilters').on('click', function () {
        dataTable.draw();
    });

    $('#clearFilters').on('click', function () {
        $state.val('');
        $city.val('');
        $status.val('');
        $dateRange.val('');
        dataTable.draw();
    });

    // Cascade city options by state
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

    // Export bookings to Excel with current filters
    $('#exportBookings').on('click', function () {
        const params = new URLSearchParams();
        
        const stateId = $state.val();
        const cityId = $city.val();
        const status = $status.val();
        const dateRange = $dateRange.val();

        if (stateId) params.append('state_id', stateId);
        if (cityId) params.append('city_id', cityId);
        if (status) params.append('status', status);
        
        if (dateRange) {
            const dates = dateRange.split(' - ');
            if (dates.length === 2) {
                params.append('date_from', dates[0]);
                params.append('date_to', dates[1]);
            }
        }

        const exportUrl = window.exportBookingsUrl || '/ppadmlog/reports/bookings/export';
        window.location.href = exportUrl + '?' + params.toString();
    });
});
