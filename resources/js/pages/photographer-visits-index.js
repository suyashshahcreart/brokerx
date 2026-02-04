/**
 * Photographer Visits Index Page
 * DataTables initialization and filter handling
 */
import $ from "jquery";
import moment from "moment";
import 'datatables.net-bs5';

// Set default locale (moment includes 'en' by default)
moment.locale('en');

window.$ = $;
window.jQuery = $;
window.moment = moment;

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const table = $('#visits-table');
    if (!table.length) return;

    // Initialize daterangepicker
    let dateRangePicker = null;

    // Wait for moment and daterangepicker to be available
    const initDateRangePicker = () => {
        if (typeof window.moment === 'undefined' || typeof $.fn.daterangepicker === 'undefined') {
            setTimeout(initDateRangePicker, 100);
            return;
        }
        initializeDateRangePicker();
    };

    function initializeDateRangePicker() {
        const input = $('#filter-date-range');
        if (!input.length || input.length === 0) {
            // Element doesn't exist yet, try again later
            setTimeout(initializeDateRangePicker, 200);
            return;
        }

        // Ensure moment is available
        if (typeof window.moment === 'undefined') {
            console.error('Moment.js is not available');
            return;
        }

        // Ensure daterangepicker is available
        if (typeof $.fn.daterangepicker === 'undefined') {
            console.error('Daterangepicker is not available');
            return;
        }

        // Check if already initialized
        if (input.data('daterangepicker')) {
            return; // Already initialized
        }

        try {
            // Ensure the element is in the DOM
            if (!input.is(':visible') && !document.body.contains(input[0])) {
                setTimeout(initializeDateRangePicker, 200);
                return;
            }

            // Initialize daterangepicker with proper configuration
            dateRangePicker = input.daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                },
                opens: 'left',
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
                showCustomRangeLabel: true
            });

            input.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                // Trigger table reload
                dataTable.draw();
            });

            input.on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
                // Trigger table reload
                dataTable.draw();
            });
        } catch (error) {
            console.error('Error initializing daterangepicker:', error);
            // Don't retry on error to avoid infinite loop
        }
    }

    // Start initialization after a short delay to ensure DOM is ready
    setTimeout(() => {
        initDateRangePicker();
    }, 300);

    // Initialize DataTable
    const dataTable = table.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.photographerVisitsConfig.indexRoute,
            type: 'GET',
            data: function (d) {
                d.status = $('#filter-status').val();
                d.photographer_id = $('#filter-photographer').val();

                // Handle date range
                const dateRange = $('#filter-date-range').val();
                if (dateRange) {
                    const dates = dateRange.split(' - ');
                    if (dates.length === 2) {
                        d.date_from = dates[0];
                        d.date_to = dates[1];
                    }
                }
            },
            dataSrc: function (json) {
                return json.data;
            },
            error: function (xhr, error, thrown) {
                console.error('DataTables Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    thrown: thrown,
                    response: xhr.responseText,
                    responseJSON: xhr.responseJSON
                });

                let errorMsg = 'Error loading data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg += ' ' + xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg += ' ' + xhr.responseJSON.error;
                }

                alert(errorMsg + ' Please check console for details.');
            }
        },
        columns: [
            {
                data: 'id',
                name: 'id',
                width: '80px',
                render: function (data, type, row) {
                    return data || '-';
                }
            },
            {
                data: 'photographer_name',
                name: 'photographer.firstname',
                render: function (data, type, row) {
                    return data || '-';
                }
            },
            {
                data: 'booking_info',
                name: 'booking_id',
                render: function (data, type, row) {
                    return data || '-';
                }
            },
            {
                data: 'visit_date',
                name: 'visit_date',
                render: function (data, type, row) {
                    return data || '-';
                }
            },
            {
                data: 'status',
                name: 'status',
                width: '120px',
                render: function (data, type, row) {
                    return data || '-';
                }
            },
            {
                data: 'check_actions',
                name: 'check_actions',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return data || '';
                }
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-end',
                width: '100px',
                render: function (data, type, row) {
                    return data || '';
                }
            }
        ],
        order: [[0, 'desc']],
        language: {
            search: '_INPUT_',
            searchPlaceholder: 'Search visits...',
            processing: '<i class="ri-loader-4-line spin"></i> Loading...',
            emptyTable: 'No photographer visits found',
            zeroRecords: 'No matching visits found',
            paginate: {
                next: '<i class="ri-arrow-right-s-line"></i>',
                previous: '<i class="ri-arrow-left-s-line"></i>'
            }
        },
        lengthMenu: [10, 25, 50, 100],
        responsive: true,
        lengthMenu: [10, 25, 50, 100],
        responsive: true,
        drawCallback: function (settings) {
            // Reinitialize tooltips if they exist
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        }
    });

    // Filter change events
    $('#filter-status, #filter-photographer').on('change', function () {
        dataTable.draw();
    });

    // Panel card refresh
    $('[data-panel-action="refresh"]').on('click', function () {
        dataTable.ajax.reload();
    });

});
