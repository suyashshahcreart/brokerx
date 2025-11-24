import $ from 'jquery';
import 'datatables.net-bs5';
import Swal from 'sweetalert2';

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
            gridPage = 1; // reset pagination
            updateFilterButtons();
            reloadViews();
        });
        $(document).on('click', '#filter-inactive-qr', function() {
            qrFilter = 'inactive';
            gridPage = 1;
            updateFilterButtons();
            reloadViews();
        });
        $(document).on('click', '#filter-all-qr', function() {
            qrFilter = 'all';
            gridPage = 1;
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
    // Pagination state for grid view
    let gridPage = 1; // current page
    let gridPageSize = 9; // cards per page
    let gridTotalRecords = 0;
    let gridTotalPages = 1;
    let gridDraw = 1; // DataTables style draw counter

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

    // Load grid view by default on page load
    if (gridView.hasClass('active')) {
        loadGridView();
        gridLoaded = true;
    }

    // Optionally reload grid on tab re-entry
    // gridTab.on('click', function(e) { loadGridView(); });

    // Pagination renderer
    function renderGridPagination() {
        let paginationEl = $('#qr-grid-pagination');
        if (!paginationEl.length) {
            gridContainer.after('<div id="qr-grid-pagination" class="mt-3"></div>');
            paginationEl = $('#qr-grid-pagination');
        }
        if (gridTotalPages <= 1) { paginationEl.html(''); return; }
        const maxPagesToShow = 7;
        let startPage = Math.max(1, gridPage - Math.floor(maxPagesToShow/2));
        let endPage = startPage + maxPagesToShow - 1;
        if (endPage > gridTotalPages) { endPage = gridTotalPages; startPage = Math.max(1, endPage - maxPagesToShow + 1); }
        let html = '<nav aria-label="Grid pagination"><ul class="pagination justify-content-center pagination-sm">';
        html += `<li class="page-item${gridPage===1?' disabled':''}"><a class="page-link" href="#" data-page="${gridPage-1}">Prev</a></li>`;
        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if (startPage > 2) html += '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
        }
        for (let p=startPage; p<=endPage; p++) {
            html += `<li class="page-item${p===gridPage?' active':''}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
        }
        if (endPage < gridTotalPages) {
            if (endPage < gridTotalPages - 1) html += '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${gridTotalPages}">${gridTotalPages}</a></li>`;
        }
        html += `<li class="page-item${gridPage===gridTotalPages?' disabled':''}"><a class="page-link" href="#" data-page="${gridPage+1}">Next</a></li>`;
        html += '</ul>';
        html += `<div class="text-center small text-muted mt-1">Page ${gridPage} of ${gridTotalPages} • Total ${gridTotalRecords} QR codes</div>`;
        paginationEl.html(html);
    }

    // Pagination click handler
    $(document).on('click', '#qr-grid-pagination .page-link', function(e){
        e.preventDefault();
        const target = parseInt($(this).data('page'),10);
        if (!isNaN(target) && target>=1 && target<=gridTotalPages && target!==gridPage) {
            gridPage = target;
            loadGridView();
            $('html,body').animate({scrollTop: gridContainer.offset().top - 80},300);
        }
    });

    function loadGridView() {
        if (!gridContainer.length) return;
        gridContainer.html('<div class="col-12 text-center text-muted">Loading...</div>');
        $.ajax({
            url: table.data('ajax') || table.data('url') || table.attr('data-ajax') || table.attr('data-url') || window.qrIndexAjaxUrl || '',
            dataType: 'json',
            data: (function(){
                let params = { start: (gridPage-1)*gridPageSize, length: gridPageSize, draw: gridDraw++ };
                if (qrFilter === 'active') params.active = 1;
                if (qrFilter === 'inactive') params.active = 0;
                return params;
            })(),
            success: function(response) {
                let data = response.data || response;
                gridTotalRecords = response.recordsFiltered || response.recordsTotal || (Array.isArray(data)?data.length:0);
                gridTotalPages = Math.max(1, Math.ceil(gridTotalRecords / gridPageSize));
                if (gridPage > gridTotalPages) { gridPage = 1; }
                let html = '';
                if (Array.isArray(data) && data.length === 0) {
                    html = '<div class="col-12 text-center text-muted">No QR codes found.</div>';
                } else {
                    if (qrFilter === 'active') data = data.filter(qr => qr.booking_id);
                    if (qrFilter === 'inactive') data = data.filter(qr => !qr.booking_id);
                    data.forEach(function(qr) {
                        const statusBadge = qr.booking_id ? `<span class="badge bg-success">Active</span>` : `<span class="badge bg-danger">Inactive</span>`;
                        
                        // Display generated QR code or fallback image
                        let qrImageHtml = '';
                        if (qr.qr_code_svg) {
                            qrImageHtml = `<div class="qr-code-container" style="width: 300px; height: 300px;">${qr.qr_code_svg}</div>`;
                        } else if (qr.image) {
                            qrImageHtml = `<img src="/storage/${qr.image}" alt="QR Image" class="img-fluid rounded" style="max-height:300px; max-width:300px;">`;
                        } else {
                            qrImageHtml = `<div class="text-muted"><i class="ri-qr-code-line" style="font-size: 150px;"></i><div>No QR Code</div></div>`;
                        }
                        
                        html += `<div class="col-12 col-md-6 col-lg-4">
                            <div class="card shadow-lg">
                                <div class="card-body d-flex flex-column">
                                    <div class="fs-5">
                                        ${statusBadge}
                                    </div>
                                    <div class="m-3 d-flex justify-content-center align-items-center" style="min-height: 300px;">
                                        ${qrImageHtml}
                                    </div>
                                    <div class="d-flex gap-3">
                                        <h5 class="card-title">${qr.name}</h5>
                                        <span class=" fs-4 badge bg-primary">${qr.code}</span>
                                    </div>
                                    <div class="mb-1 text-muted">Booking: ${qr.booking_id ?? '-'}</div>
                                    <div class="mb-2 d-flex gap-2 flex-wrap">
                                        ${qr.qr_link ? `<a href="${qr.qr_link}" class="btn btn-soft-info btn-sm" target="_blank"><i class="ri-external-link-line me-1"></i>QR Link</a>` : ''}
                                        <a href="/admin/qr/${qr.id}/download" class="btn btn-soft-success btn-sm" title="Download QR with Details" download>
                                            <i class="ri-download-2-line me-1"></i>Download
                                        </a>
                                    </div>
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
                                // QR is not assigned - show booking list for assignment with DataTable
                                $('#assignBookingModalLabel').text('Assign QR To Booking');
                                let html = `
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0"><i class="ri-file-list-3-line me-2"></i>Available Bookings</h6>
                                            <span class="badge bg-primary" id="total-bookings-count">Loading...</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered align-middle mb-0" id="assign-bookings-table" style="width:100%">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th style="width: 20px;">ID</th>
                                                        <th style="width: 150px;">Customer</th>
                                                        <th style="width: 180px;">Property Details</th>
                                                        <th style="width: 150px;">Location</th>
                                                        <th style="width: 120px;">Booking Date</th>
                                                        <th style="width: 90px;">Status</th>
                                                        <th style="width: 70px;" class="text-center">Select</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3 d-flex justify-content-between align-items-center bg-light p-3 rounded">
                                            <div id="selected-booking-info" class="text-muted small"><i class="ri-information-line me-1"></i>Please select a booking to assign</div>
                                            <button type="button" class="btn btn-primary" id="confirm-assign-btn" disabled>
                                                <i class="ri-checkbox-circle-line me-1"></i>Assign Booking
                                            </button>
                                        </div>
                                    </div>
                                `;
                                $('#assign-modal-content').html(html);

                                // Initialize DataTable for bookings
                                const bookingApiUrl = $('#assignBookingModal').data('booking-list-api');
                                let selectedBookingId = null;
                                
                                const assignBookingsTable = $('#assign-bookings-table').DataTable({
                                    processing: true,
                                    serverSide: false,
                                    ajax: {
                                        url: bookingApiUrl,
                                        dataSrc: function(json) {
                                            const data = json.data || json.bookings || json || [];
                                            $('#total-bookings-count').text(data.length + ' Bookings');
                                            return data;
                                        },
                                        error: function() {
                                            $('#assign-modal-content').html('<div class="alert alert-danger text-center"><i class="ri-error-warning-line me-2"></i>Failed to load bookings.</div>');
                                        }
                                    },
                                    columns: [
                                        { 
                                            data: 'id', 
                                            name: 'id',
                                            render: function(data) {
                                                return `<span class="badge bg-success">#${data}</span>`;
                                            }
                                        },
                                        { 
                                            data: 'customer', 
                                            name: 'customer',
                                            render: function(data, type, row) {
                                                const customerName = data || 'N/A';
                                                const mobile = row.customer_mobile ? `<div class="text-muted small"><i class="ri-phone-line"></i> ${row.customer_mobile}</div>` : '';
                                                return `<div class="fw-semibold">${customerName}</div>${mobile}`;
                                            }
                                        },
                                        { 
                                            data: 'property_type', 
                                            name: 'property_type',
                                            render: function(data, type, row) {
                                                const propertyType = data || '-';
                                                const subType = row.property_sub_type ? `<div class="text-muted small">${row.property_sub_type}</div>` : '';
                                                const bhk = row.bhk ? `<span class="badge bg-info badge-sm">${row.bhk}</span>` : '';
                                                const area = row.area ? `<span class="text-muted small ms-1">${parseFloat(row.area).toLocaleString()} sq.ft</span>` : '';
                                                return `<div>${propertyType} ${bhk}</div>${subType}${area}`;
                                            }
                                        },
                                        { 
                                            data: 'city', 
                                            name: 'city',
                                            render: function(data, type, row) {
                                                const city = data || '-';
                                                const state = row.state ? `<div class="text-muted small">${row.state}</div>` : '';
                                                const pincode = row.pin_code ? `<div class="text-muted small"><i class="ri-map-pin-line"></i> ${row.pin_code}</div>` : '';
                                                return `<div class="fw-semibold">${city}</div>${state}${pincode}`;
                                            }
                                        },
                                        { 
                                            data: 'booking_date', 
                                            name: 'booking_date',
                                            render: function(data) {
                                                return data ? `<div class="text-center"><i class="ri-calendar-line me-1"></i>${data}</div>` : '<span class="text-muted">-</span>';
                                            }
                                        },
                                        { 
                                            data: 'status', 
                                            name: 'status',
                                            render: function(data) {
                                                const statusColors = {
                                                    'pending': 'warning',
                                                    'confirmed': 'success',
                                                    'cancelled': 'danger',
                                                    'completed': 'info'
                                                };
                                                const statusIcons = {
                                                    'pending': 'ri-time-line',
                                                    'confirmed': 'ri-checkbox-circle-line',
                                                    'cancelled': 'ri-close-circle-line',
                                                    'completed': 'ri-check-double-line'
                                                };
                                                const color = statusColors[data] || 'secondary';
                                                const icon = statusIcons[data] || 'ri-information-line';
                                                return `<span class="badge bg-${color} text-uppercase"><i class="${icon} me-1"></i>${data || '-'}</span>`;
                                            }
                                        },
                                        { 
                                            data: 'id',
                                            orderable: false,
                                            searchable: false,
                                            className: 'text-center',
                                            render: function(data, type, row) {
                                                return `<input type="radio" name="assign_booking_radio" value="${data}" class="form-check-input" style="width: 20px; height: 20px; cursor: pointer;">`;
                                            }
                                        }
                                    ],
                                    pageLength: 10,
                                    lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                                    order: [[0, 'desc']],
                                    language: {
                                        search: "_INPUT_",
                                        searchPlaceholder: "Search by customer, property, location, pin code...",
                                        lengthMenu: "Show _MENU_ entries",
                                        info: "Showing _START_ to _END_ of _TOTAL_ bookings",
                                        infoEmpty: "No bookings available",
                                        zeroRecords: "<div class='text-center text-muted py-3'><i class='ri-search-line fs-1'></i><div class='mt-2'>No matching bookings found</div></div>",
                                        emptyTable: "<div class='text-center text-muted py-3'><i class='ri-inbox-line fs-1'></i><div class='mt-2'>No bookings available for assignment</div></div>",
                                        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                                    },
                                    dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>><"row"<"col-sm-12"tr>><"row mt-3"<"col-sm-5"i><"col-sm-7"p>>',
                                    initComplete: function() {
                                        // Style the search input
                                        $('#dt-search-1')
                                            .removeClass('form-control-sm')
                                            .addClass('form-control')
                                            .css({
                                                'width': '80%',
                                                'border': '2px solid #e0e0e0',
                                                'border-radius': '2px',
                                                'box-shadow': '0 2px 4px rgba(0,0,0,0.05)'
                                            })
                                            .attr('placeholder', 'Search by customer, property, location, pin code...');
                                        
                                        // Add search icon
                                        $('.dataTables_filter').prepend('<i class="ri-search-line position-absolute" style="left: 30px; top: 50%; transform: translateY(-50%); font-size: 20px; color: #999; z-index: 1;"></i>');
                                        $('.dataTables_filter input').css('padding-left', '45px');
                                        $('.dataTables_filter').css('position', 'relative');
                                        
                                        // Style the length menu
                                        $('.dataTables_length select')
                                            .removeClass('form-select-sm')
                                            .addClass('form-select');
                                    },
                                    drawCallback: function() {
                                        // Add hover effect styling
                                        $('#assign-bookings-table tbody tr').hover(
                                            function() { $(this).addClass('table-active'); },
                                            function() { $(this).removeClass('table-active'); }
                                        );
                                    }
                                });

                                // Handle radio button selection
                                $('#assign-bookings-table tbody').off('change', 'input[name="assign_booking_radio"]').on('change', 'input[name="assign_booking_radio"]', function() {
                                    selectedBookingId = $(this).val();
                                    const row = assignBookingsTable.row($(this).closest('tr')).data();
                                    const customerInfo = row.customer || 'N/A';
                                    const propertyInfo = `${row.property_type || ''} ${row.property_sub_type ? '/ ' + row.property_sub_type : ''}`;
                                    const locationInfo = row.city ? `${row.city}${row.state ? ', ' + row.state : ''}` : '';
                                    $('#selected-booking-info').html(`
                                        <div>
                                            <i class="ri-checkbox-circle-fill text-success me-1"></i>
                                            <strong>Booking #${row.id}</strong> selected
                                            <span class="text-muted">| ${customerInfo} | ${propertyInfo}${locationInfo ? ' | ' + locationInfo : ''}</span>
                                        </div>
                                    `);
                                    $('#confirm-assign-btn').prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
                                });

                                // Confirm assign button
                                $('#confirm-assign-btn').off('click').on('click', function() {
                                    if (!selectedBookingId) {
                                        alert('Please select a booking.');
                                        return;
                                    }
                                    const assignApi = $('#assignBookingModal').data('assign-api');
                                    if (!assignApi) {
                                        alert('Assign API not configured.');
                                        return;
                                    }
                                    
                                    // Disable button and show loading
                                    $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Assigning...');
                                    
                                    $.post(assignApi, {
                                        qr_id: qrId,
                                        booking_id: selectedBookingId,
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    })
                                    .done(function(res) {
                                        if (res && (res.success === true || res.status === 'success')) {
                                            const modalEl = document.getElementById('assignBookingModal');
                                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                                            if (modalInstance) {
                                                modalInstance.hide();
                                            }
                                            // Destroy DataTable before closing
                                            if ($.fn.DataTable.isDataTable('#assign-bookings-table')) {
                                                $('#assign-bookings-table').DataTable().destroy();
                                            }
                                            // Remove backdrop and restore body
                                            $('.modal-backdrop').remove();
                                            $('body').removeClass('modal-open').css('overflow', '');
                                            
                                            // Show success alert with SweetAlert
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Success!',
                                                text: res.message || 'QR code has been successfully assigned to the booking.',
                                                confirmButtonText: 'OK',
                                                timer: 3000,
                                                timerProgressBar: true
                                            }).then(() => {
                                                // Reload tables after alert
                                                if (table.length && $.fn.DataTable.isDataTable(table)) table.DataTable().ajax.reload(null, false);
                                                if (typeof loadGridView === 'function') loadGridView();
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Assignment Failed',
                                                text: res.message || 'Failed to assign QR code to the booking.',
                                                confirmButtonText: 'OK'
                                            });
                                            $('#confirm-assign-btn').prop('disabled', false).html('<i class="ri-checkbox-circle-line me-1"></i>Assign Booking');
                                        }
                                    })
                                    .fail(function(xhr) {
                                        const errorMsg = xhr.responseJSON?.message || 'Assignment request failed.';
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: errorMsg,
                                            confirmButtonText: 'OK'
                                        });
                                        $('#confirm-assign-btn').prop('disabled', false).html('<i class="ri-checkbox-circle-line me-1"></i>Assign Booking');
                                    });
                                });

                                // Clean up DataTable and modal backdrop when modal is closed
                                $('#assignBookingModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
                                    // Destroy DataTable if exists
                                    if ($.fn.DataTable.isDataTable('#assign-bookings-table')) {
                                        $('#assign-bookings-table').DataTable().destroy();
                                    }
                                    // Force remove any lingering backdrops and restore body
                                    setTimeout(function() {
                                        $('.modal-backdrop').remove();
                                        $('body').removeClass('modal-open').css({
                                            'overflow': '',
                                            'padding-right': ''
                                        });
                                    }, 100);
                                });
                            }
                            
                            // Show modal
                            const modal = new bootstrap.Modal(document.getElementById('assignBookingModal'));
                            modal.show();
                        });
                }
                gridContainer.html(html);
                renderGridPagination();
            },
            error: function() {
                gridContainer.html('<div class="col-12 text-center text-danger">Failed to load QR codes.</div>');
                gridTotalRecords = 0; gridTotalPages = 1; renderGridPagination();
            }
        });
    }
});
