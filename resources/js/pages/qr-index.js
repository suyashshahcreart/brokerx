import $ from 'jquery';
import 'datatables.net-bs5';

const model = $('#assignBookingModal');

$(function() {
            function updateFilterButtons() {
                // Reset all to outline
                $('#filter-active-qr').removeClass('btn-success').addClass('btn-outline-success');
                $('#filter-inactive-qr').removeClass('btn-danger').addClass('btn-outline-danger');
                $('#filter-all-qr').removeClass('btn-secondary').addClass('btn-outline-secondary');

                // Set selected to solid
                if (qrFilter === 'active') {
                    $('#filter-active-qr').addClass('btn-success').removeClass('btn-outline-success');
                    $('#filter-inactive-qr').addClass('btn-outline-danger').removeClass('btn-danger');
                    $('#filter-all-qr').addClass('btn-outline-secondary').removeClass('btn-secondary');
                } else if (qrFilter === 'inactive') {
                    $('#filter-inactive-qr').addClass('btn-danger').removeClass('btn-outline-danger');
                    $('#filter-active-qr').addClass('btn-outline-success').removeClass('btn-success');
                    $('#filter-all-qr').addClass('btn-outline-secondary').removeClass('btn-secondary');
                } else {
                    $('#filter-all-qr').addClass('btn-secondary').removeClass('btn-outline-secondary');
                    $('#filter-active-qr').addClass('btn-outline-success').removeClass('btn-success');
                    $('#filter-inactive-qr').addClass('btn-outline-danger').removeClass('btn-danger');
                }
            }
        // QR filter state
        let qrFilter = 'all'; // 'all', 'active', 'inactive'

        // Filter button handlers
        $(document).on('click', '#filter-active-qr', function() {
            qrFilter = 'active';
            updateFilterButtons();
            reloadViews();
        });
        $(document).on('click', '#filter-inactive-qr', function() {
            qrFilter = 'inactive';
            updateFilterButtons();
            reloadViews();
        });
        $(document).on('click', '#filter-all-qr', function() {
            qrFilter = 'all';
            updateFilterButtons();
            reloadViews();
            // Set initial filter button state
            updateFilterButtons();
        });

        function reloadViews() {
            // List view
            if (table.length && $.fn.DataTable.isDataTable(table)) {
                table.DataTable().draw();
            }
            // Grid view
            if (typeof loadGridView === 'function') loadGridView();
        }
    const table = $('#qr-table');
    const gridTab = $('#qr-grid-tab');
    const listTab = $('#qr-list-tab');
    const gridView = $('#qr-grid-view');
    const gridContainer = $('#qr-grid-container');
    let gridLoaded = false;

    // Initialize DataTable for List View
    if (table.length) {
        table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: table.data('ajax') || table.data('url') || table.attr('data-ajax') || table.attr('data-url') || window.qrIndexAjaxUrl || '',
                data: function(d) {
                    if (qrFilter === 'active') d.active = 1;
                    if (qrFilter === 'inactive') d.active = 0;
                }
            },
            columns: [
                { data: 'id', name: 'id', className: 'fw-semibold' },
                { data: 'name', name: 'name' },
                { data: 'code', name: 'code' },
                { data: 'booking_id', name: 'booking_id' },
                { data: 'image', name: 'image', orderable: false, searchable: false, render: function(data) { return data ? `<img src="/storage/${data}" width="50"/>` : ''; } },
                { data: 'created_by', name: 'created_by' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[0, 'desc']],
            responsive: true,
            language: {
                search: "Search QR Codes:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries found",
                zeroRecords: "No matching QR codes found"
            }
        });
    }

    // Tab event: load grid data on first show
    gridTab.on('shown.bs.tab', function (e) {
        if (!gridLoaded) {
            loadGridView();
            gridLoaded = true;
        }
    });

    // Optionally reload grid on tab re-entry
    // gridTab.on('click', function(e) { loadGridView(); });

    function loadGridView() {
        if (!gridContainer.length) return;
        gridContainer.html('<div class="col-12 text-center text-muted">Loading...</div>');
        $.ajax({
            url: table.data('ajax') || table.data('url') || table.attr('data-ajax') || table.attr('data-url') || window.qrIndexAjaxUrl || '',
            dataType: 'json',
            data: qrFilter === 'all' ? {} : (qrFilter === 'active' ? { active: 1 } : { active: 0 }),
            success: function(response) {
                let data = response.data || response;
                let html = '';
                if (Array.isArray(data) && data.length === 0) {
                    html = '<div class="col-12 text-center text-muted">No QR codes found.</div>';
                } else {
                    // Filter on client side as fallback (if server doesn't filter)
                    if (qrFilter === 'active') data = data.filter(qr => qr.booking_id);
                    if (qrFilter === 'inactive') data = data.filter(qr => !qr.booking_id);
                    data.forEach(function(qr) {
                        const statusBadge = qr.booking_id ? `<span class="badge bg-success">Active</span>` : `<span class="badge bg-danger">Inactive</span>`;
                        html += `<div class="col-12 col-md-6 col-lg-4">
                            <div class="card shadow-lg">
                                <div class="card-body d-flex flex-column">
                                    <div class="fs-5">
                                        ${statusBadge}
                                    </div>
                                    <div class="m-3 d-flex justify-content-center">
                                        ${qr.image ? `<img src="/storage/${qr.image}" alt="QR Image" class="img-fluid rounded" style="max-height:200px;">` : '<img src="/images/qr_code.png" alt="QR Image" class="img-fluid rounded" style="max-height:200px;">'}
                                    </div>
                                    <div class="d-flex gap-3">
                                        <h5 class="card-title">${qr.name}</h5>
                                        <span class=" fs-4 badge bg-primary">${qr.code}</span>
                                    </div>
                                    <div class="mb-1 text-muted">Booking: ${qr.booking_id ?? '-'}</div>
                                    <div class="mb-1 ">${qr.qr_link ? `<a href="${qr.qr_link}" class="btn btn-soft-info" target="_blank">QR Link</a>` : ''}</div>
                                    <div class="mb-2">
                                        <button class="btn btn-outline-secondary btn-sm assign-booking-btn" data-qr-id="${qr.id}" data-qr-name="${qr.name}" data-qr-code="${qr.code}" data-qr-image="${qr.image}" data-booking-id="${qr.booking_id || ''}">
                                            <i class="ri-link"></i> ${qr.booking_id ? 'View Booking' : 'Assign'}
                                        </button>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        ${qr.actions}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });
                        // Assign Booking Modal logic
                        $(document).on('click', '.assign-booking-btn', function() {
                            const qrId = $(this).data('qr-id');
                            const qrName = $(this).data('qr-name');
                            const qrCode = $(this).data('qr-code');
                            const qrImage = $(this).data('qr-image');
                            const bookingId = $(this).data('booking-id');
                            
                            // Show QR details in modal
                            let qrDetailsHtml = `<div class="d-flex align-items-center gap-3 pb-3 border-bottom">
                                <div>
                                    ${qrImage ? `<img src="/storage/${qrImage}" alt="QR Image" class="img-thumbnail" style="max-width:80px;">` : '<img src="/images/qr_code.png" alt="QR Image" class="img-thumbnail" style="max-width:80px;">'}
                                </div>
                                <div>
                                    <div><strong>Name:</strong> ${qrName}</div>
                                    <div><strong>Code:</strong> <span class="badge bg-primary">${qrCode}</span></div>
                                </div>
                            </div>`;
                            $('#assign-modal-qr-details').html(qrDetailsHtml);
                            
                            // Check if QR is already assigned to a booking
                            if (bookingId) {
                                // QR is assigned - show booking details
                                $('#assignBookingModalLabel').text('QR & Booking Details');
                                $('#assign-modal-content').html('<div class="text-center text-muted">Loading booking details...</div>');
                                
                                const bookingDetailsApiUrl = $('#assignBookingModal').data('booking-details-api');
                                $.ajax({
                                    url: bookingDetailsApiUrl,
                                    data: { booking_id: bookingId },
                                    success: function(data) {
                                        if (data && data.booking) {
                                            const b = data.booking;
                                            let detailsHtml = `
                                                <div class="mt-3">
                                                    <h6 class="mb-3">Booking Details</h6>
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label text-muted small mb-1">Booking ID</label>
                                                                    <div class="fw-semibold">#${b.id}</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label text-muted small mb-1">Customer</label>
                                                                    <div class="fw-semibold">${b.customer || '-'}</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label text-muted small mb-1">Property Type</label>
                                                                    <div>${b.property_type || '-'}</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label text-muted small mb-1">Property Sub-Type</label>
                                                                    <div>${b.property_sub_type || '-'}</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label text-muted small mb-1">BHK</label>
                                                                    <div>${b.bhk || '-'}</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label text-muted small mb-1">Location</label>
                                                                    <div>${b.city || '-'}${b.state ? ', ' + b.state : ''}</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label text-muted small mb-1">Area</label>
                                                                    <div>${b.area ? b.area + ' sq.ft' : '-'}</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label text-muted small mb-1">Price</label>
                                                                    <div class="fw-semibold">${b.price || '-'}</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label text-muted small mb-1">Booking Date</label>
                                                                    <div>${b.booking_date || '-'}</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label text-muted small mb-1">Status</label>
                                                                    <div><span class="badge bg-secondary">${b.status || '-'}</span></div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label text-muted small mb-1">Payment Status</label>
                                                                    <div><span class="badge bg-info">${b.payment_status || '-'}</span></div>
                                                                </div>
                                                                ${b.address ? `<div class="col-12">
                                                                    <label class="form-label text-muted small mb-1">Address</label>
                                                                    <div>${b.address}</div>
                                                                </div>` : ''}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            `;
                                            $('#assign-modal-content').html(detailsHtml);
                                        } else {
                                            $('#assign-modal-content').html('<div class="text-center text-danger">Failed to load booking details.</div>');
                                        }
                                    },
                                    error: function() {
                                        $('#assign-modal-content').html('<div class="text-center text-danger">Failed to load booking details.</div>');
                                    }
                                });
                            } else {
                                // QR is not assigned - show booking list for assignment
                                $('#assignBookingModalLabel').text('Assign QR To Booking');
                                $('#assign-modal-content').html('<div class="text-center text-muted">Loading bookings...</div>');
                                
                                const bookingApiUrl = $('#assignBookingModal').data('booking-list-api');
                                $.ajax({
                                    url: bookingApiUrl,
                                    data: { qr_id: qrId },
                                    success: function(data) {
                                        let bookings = Array.isArray(data) ? data : (data.data || data.bookings || []);
                                        if (!bookings || bookings.length === 0) {
                                            $('#assign-modal-content').html('<div class="text-center text-muted">No bookings available for assignment.</div>');
                                        } else {
                                            let html = '<div class="mt-3"><h6 class="mb-3">Select Booking to Assign</h6><div class="list-group">';
                                            bookings.forEach(function(b) {
                                                const id = b.id || b.booking_id || '';
                                                const title = b.reference || b.name || b.title || ('Booking #' + id);
                                                const meta = b.customer_name || b.customer || b.email || '';
                                                const property = b.property || '';
                                                const date = b.date || b.created_at || '';
                                                html += `<label class="list-group-item d-flex justify-content-between align-items-start">
                                                            <div class="ms-2 me-auto">
                                                              <div class="fw-bold">${title}</div>
                                                              <div class="text-muted small">${meta}${property ? ' · ' + property : ''}${date ? ' · ' + date : ''}</div>
                                                            </div>
                                                            <input type="radio" name="assign_booking_radio" value="${id}">
                                                         </label>`;
                                            });
                                            html += '</div>';
                                            html += '<div class="mt-3 text-end"><button type="button" class="btn btn-primary" id="confirm-assign-btn">Assign Booking</button></div></div>';
                                            $('#assign-modal-content').html(html);

                                            // Confirm assign button
                                            $('#confirm-assign-btn').off('click').on('click', function() {
                                                const selected = $('input[name="assign_booking_radio"]:checked').val();
                                                if (!selected) {
                                                    alert('Please select a booking.');
                                                    return;
                                                }
                                                const assignApi = $('#assignBookingModal').data('assign-api');
                                                if (!assignApi) {
                                                    alert('Assign API not configured.');
                                                    return;
                                                }
                                                $.post(assignApi, {
                                                    qr_id: qrId,
                                                    booking_id: selected,
                                                    _token: $('meta[name="csrf-token"]').attr('content')
                                                })
                                                .done(function(res) {
                                                    if (res && (res.success === true || res.status === 'success')) {
                                                        const modalEl = document.getElementById('assignBookingModal');
                                                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                                                        if (modalInstance) {
                                                            modalInstance.hide();
                                                        }
                                                        // Remove backdrop and restore body
                                                        $('.modal-backdrop').remove();
                                                        $('body').removeClass('modal-open').css('overflow', '');
                                                        if (table.length && $.fn.DataTable.isDataTable(table)) table.DataTable().ajax.reload(null, false);
                                                        if (typeof loadGridView === 'function') loadGridView();
                                                    } else {
                                                        alert(res.message || 'Assignment failed.');
                                                    }
                                                })
                                                .fail(function() {
                                                    alert('Assignment request failed.');
                                                });
                                            });
                                        }
                                    },
                                    error: function() {
                                        $('#assign-modal-content').html('<div class="text-danger text-center">Failed to load bookings.</div>');
                                    }
                                });
                            }
                            
                            // Show modal
                            const modal = new bootstrap.Modal(document.getElementById('assignBookingModal'));
                            modal.show();
                        });
                }
                gridContainer.html(html);
            },
            error: function() {
                gridContainer.html('<div class="col-12 text-center text-danger">Failed to load QR codes.</div>');
            }
        });
    }
});
