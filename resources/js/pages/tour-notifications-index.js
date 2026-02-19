/**
 * Tour Notifications Index - DataTable Implementation
 */

import moment from 'moment';

window.moment = moment;

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const tableElement = document.getElementById('tour-notifications-table');
    if (!tableElement) {
        console.error('Tour notifications table element not found');
        return;
    }

    // Initialize daterangepicker
    $('#filterDateRange').daterangepicker({
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

    $('#filterDateRange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('#filterDateRange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    // Initialize DataTable
    const table = $('#tour-notifications-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.tourNotificationsIndexUrl || '',
            type: 'GET',
            data: function (d) {
                // Add filter parameters
                d.tour_code = $('#filterTourCode').val() || '';
                d.phone_number = $('#filterPhoneNumber').val() || '';
                d.status = $('#filterStatus').val() || '';
                d.booking_id = $('#filterBookingId').val() || '';
                
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
                console.error('DataTable Ajax Error:', error);
            }
        },
        columns: [
            { 
                data: 'id', 
                name: 'id',
                width: '60px'
            },
            { 
                data: 'tour_code', 
                name: 'tour_code',
                orderable: true
            },
            { 
                data: 'phone_number', 
                name: 'phone_number',
                orderable: true
            },
            { 
                data: 'booking_id', 
                name: 'booking_id',
                orderable: true
            },
            { 
                data: 'status', 
                name: 'status',
                orderable: true
            },
            { 
                data: 'created_at', 
                name: 'created_at',
                orderable: true
            },
            { 
                data: 'notified_at', 
                name: 'notified_at',
                orderable: true
            },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false, 
                className: 'text-end'
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        responsive: true,
        language: {
            paginate: {
                previous: "<i class='ri-arrow-left-s-line'></i>",
                next: "<i class='ri-arrow-right-s-line'></i>"
            },
            search: "_INPUT_",
            searchPlaceholder: "Search notifications...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ notifications",
            infoEmpty: "No notifications found",
            infoFiltered: "(filtered from _MAX_ total notifications)",
            loadingRecords: "Loading...",
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: "No notifications available",
            zeroRecords: "No matching notifications found"
        },
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });

    // Panel card refresh button
    const refreshBtn = document.querySelector('[data-panel-action="refresh"]');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            table.ajax.reload(null, false); // false to keep current page
        });
    }

    // Apply filters button
    $('#applyFilters').on('click', function() {
        table.ajax.reload();
    });

    // Clear filters button
    $('#clearFilters').on('click', function() {
        $('#filterTourCode').val('');
        $('#filterPhoneNumber').val('');
        $('#filterStatus').val('');
        $('#filterBookingId').val('');
        $('#filterDateRange').val('');
        table.ajax.reload();
    });

    // View notification modal
    $(document).on('click', '.view-notification', function() {
        const notificationId = $(this).data('id');
        const modal = new bootstrap.Modal(document.getElementById('viewNotificationModal'));
        const detailsContainer = document.getElementById('notificationDetails');
        
        // Show loading
        detailsContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        modal.show();
        
        // Fetch notification details
        const url = window.tourNotificationsShowUrl.replace(':id', notificationId);
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notification) {
                const notif = data.notification;
                const booking = notif.booking || null;
                const metadata = notif.metadata || {};
                
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
                            <p class="mb-0">
                `;
                
                const statusBadges = {
                    'pending': 'bg-warning',
                    'notified': 'bg-success',
                    'failed': 'bg-danger'
                };
                const statusClass = statusBadges[notif.status] || 'bg-secondary';
                html += `<span class="badge ${statusClass} text-uppercase">${notif.status || '-'}</span></p></div>`;
                
                if (booking && booking.id) {
                    html += `
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Booking</label>
                            <p class="mb-0">
                                <a href="${window.appBaseUrl}/${window.adminBasePath}/bookings/${booking.id}" class="text-primary" target="_blank">
                                    Booking #${booking.id}
                                </a>
                            </p>
                        </div>
                    `;
                } else if (notif.booking_id) {
                    html += `
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Booking ID</label>
                            <p class="mb-0">
                                <a href="${window.appBaseUrl}/${window.adminBasePath}/bookings/${notif.booking_id}" class="text-primary" target="_blank">
                                    Booking #${notif.booking_id}
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
                            <p class="mb-0">${notif.formatted_created_at || (notif.created_at || '-')}</p>
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
                
                html += `</div>`;
                detailsContainer.innerHTML = html;
            } else {
                detailsContainer.innerHTML = '<div class="alert alert-danger">Failed to load notification details.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading notification:', error);
            detailsContainer.innerHTML = '<div class="alert alert-danger">Error loading notification details. Please try again.</div>';
        });
    });
});

