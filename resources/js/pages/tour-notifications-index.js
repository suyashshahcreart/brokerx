/**
 * Tour Notifications Index - DataTable
 */
import $ from 'jquery';
if (typeof window.$ === 'undefined') {
    window.$ = window.jQuery = $;
}

import 'datatables.net-bs5';
import { initMobileFiltersToggle } from '../utils/mobile-filters-toggle.js';

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const tableElement = document.getElementById('tour-notifications-table');
    if (!tableElement) {
        return;
    }

    const $filterTourCode = $('#filterTourCode');
    const $filterPhoneNumber = $('#filterPhoneNumber');
    const $filterStatus = $('#filterStatus');
    const $filterBookingId = $('#filterBookingId');
    const $filterDateRange = $('#filterDateRange');

    let dataTable = null;
    let textFilterTimer = null;

    function reloadTable() {
        if (dataTable) {
            dataTable.ajax.reload(null, false);
        }
    }

    function debouncedReloadTable() {
        clearTimeout(textFilterTimer);
        textFilterTimer = setTimeout(reloadTable, 500);
    }

    function initDateRangePicker() {
        if (typeof window.moment === 'undefined' || typeof $.fn.daterangepicker === 'undefined') {
            setTimeout(initDateRangePicker, 100);
            return;
        }

        if ($filterDateRange.data('daterangepicker')) {
            return;
        }

        $filterDateRange.daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD',
            },
            opens: 'left',
            ranges: {
                Today: [window.moment(), window.moment()],
                Yesterday: [window.moment().subtract(1, 'days'), window.moment().subtract(1, 'days')],
                'Last 7 Days': [window.moment().subtract(6, 'days'), window.moment()],
                'Last 30 Days': [window.moment().subtract(29, 'days'), window.moment()],
                'This Month': [window.moment().startOf('month'), window.moment().endOf('month')],
                'Last Month': [window.moment().subtract(1, 'month').startOf('month'), window.moment().subtract(1, 'month').endOf('month')],
            },
            alwaysShowCalendars: true,
            showCustomRangeLabel: true,
        });

        $filterDateRange.on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            reloadTable();
        });

        $filterDateRange.on('cancel.daterangepicker', function () {
            $(this).val('');
            reloadTable();
        });
    }

    setTimeout(initDateRangePicker, 300);

    initMobileFiltersToggle();

    dataTable = $('#tour-notifications-table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        searchDelay: 500,
        ajax: {
            url: window.tourNotificationsIndexUrl || '',
            type: 'GET',
            data: function (d) {
                d.tour_code = $filterTourCode.val() || '';
                d.phone_number = $filterPhoneNumber.val() || '';
                d.status = $filterStatus.val() || '';
                d.booking_id = $filterBookingId.val() || '';

                const dateRange = $filterDateRange.val();
                if (dateRange) {
                    const dates = dateRange.split(' - ');
                    if (dates.length === 2) {
                        d.date_from = dates[0];
                        d.date_to = dates[1];
                    }
                }
            },
            error: function (xhr, error) {
                console.error('DataTable Ajax Error:', error, xhr?.responseText);
            },
        },
        columns: [
            { data: 'id', name: 'tour_notifications.id', width: '60px', searchable: false },
            { data: 'tour_code', name: 'tour_notifications.tour_code' },
            { data: 'phone_number', name: 'tour_notifications.phone_number' },
            { data: 'booking_id', name: 'tour_notifications.booking_id', searchable: false },
            { data: 'status', name: 'tour_notifications.status', searchable: false },
            { data: 'created_at', name: 'tour_notifications.created_at', searchable: false },
            { data: 'notified_at', name: 'tour_notifications.notified_at', searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ],
        order: [[0, 'desc']],
        language: {
            paginate: {
                previous: "<i class='ri-arrow-left-s-line'></i>",
                next: "<i class='ri-arrow-right-s-line'></i>",
            },
            search: '_INPUT_',
            searchPlaceholder: 'Search notifications...',
            emptyTable: 'No notifications available',
            zeroRecords: 'No matching notifications found',
            processing: '<i class="ri-loader-4-line spin"></i> Loading...',
        },
    });

    $filterStatus.on('change', reloadTable);
    $filterTourCode.on('input', debouncedReloadTable);
    $filterPhoneNumber.on('input', debouncedReloadTable);
    $filterBookingId.on('input', debouncedReloadTable);

    $('#clearFilters').on('click', function () {
        $filterTourCode.val('');
        $filterPhoneNumber.val('');
        $filterStatus.val('');
        $filterBookingId.val('');
        $filterDateRange.val('');
        reloadTable();
    });

    document.querySelector('[data-panel-action="refresh"]')?.addEventListener('click', reloadTable);

    $(document).on('click', '.view-notification', function () {
        const notificationId = $(this).data('id');
        const modal = new bootstrap.Modal(document.getElementById('viewNotificationModal'));
        const detailsContainer = document.getElementById('notificationDetails');

        detailsContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        modal.show();

        const url = window.tourNotificationsShowUrl.replace(':id', notificationId);
        fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success || !data.notification) {
                    detailsContainer.innerHTML = '<div class="alert alert-danger">Failed to load notification details.</div>';
                    return;
                }

                const notif = data.notification;
                const booking = notif.booking || null;
                const metadata = notif.metadata || {};

                const statusBadges = {
                    pending: 'bg-warning',
                    notified: 'bg-success',
                    failed: 'bg-danger',
                };
                const statusClass = statusBadges[notif.status] || 'bg-secondary';

                let html = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ID</label>
                            <p class="mb-0">#${notif.id}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tour Code</label>
                            <p class="mb-0"><span class="badge bg-primary">${notif.tour_code || '-'}</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <p class="mb-0">${notif.phone_number || '-'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <p class="mb-0"><span class="badge ${statusClass} text-uppercase">${notif.status || '-'}</span></p>
                        </div>
                `;

                const bookingId = booking?.id || notif.booking_id;
                if (bookingId) {
                    html += `
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Booking</label>
                            <p class="mb-0">
                                <a href="${window.appBaseUrl}/${window.adminBasePath}/bookings/${bookingId}" class="text-primary" target="_blank">
                                    Booking #${bookingId}
                                </a>
                            </p>
                        </div>
                    `;
                }

                html += `
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">IP Address</label>
                            <p class="mb-0">${notif.ip_address || '-'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">User Agent</label>
                            <p class="mb-0 small text-muted">${notif.user_agent || '-'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Created At</label>
                            <p class="mb-0">${notif.formatted_created_at || notif.created_at || '-'}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Notified At</label>
                            <p class="mb-0">${notif.formatted_notified_at || (notif.notified_at ? notif.notified_at : '<span class="text-muted">Not notified yet</span>')}</p>
                        </div>
                `;

                if (Object.keys(metadata).length > 0) {
                    html += `
                        <div class="col-12">
                            <label class="form-label fw-semibold">Metadata</label>
                            <pre class="bg-light p-3 rounded small" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(metadata, null, 2)}</pre>
                        </div>
                    `;
                }

                html += '</div>';
                detailsContainer.innerHTML = html;
            })
            .catch((error) => {
                console.error('Error loading notification:', error);
                detailsContainer.innerHTML = '<div class="alert alert-danger">Error loading notification details. Please try again.</div>';
            });
    });
});
