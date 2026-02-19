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

                // Set selected to solid - use window.qrFilter to get current value
                const currentFilter = window.qrFilter || 'all';
                if (currentFilter === 'active') {
                    $('#filter-active-qr').addClass('btn-success').removeClass('btn-outline-success');
                    $('#filter-inactive-qr').addClass('btn-outline-danger').removeClass('btn-danger');
                    $('#filter-all-qr').addClass('btn-outline-secondary').removeClass('btn-secondary');
                } else if (currentFilter === 'inactive') {
                    $('#filter-inactive-qr').addClass('btn-danger').removeClass('btn-outline-danger');
                    $('#filter-active-qr').addClass('btn-outline-success').removeClass('btn-success');
                    $('#filter-all-qr').addClass('btn-outline-secondary').removeClass('btn-secondary');
                } else {
                    $('#filter-all-qr').addClass('btn-secondary').removeClass('btn-outline-secondary');
                    $('#filter-active-qr').addClass('btn-outline-success').removeClass('btn-success');
                    $('#filter-inactive-qr').addClass('btn-outline-danger').removeClass('btn-danger');
                }
            }
        // QR filter state - make it globally accessible for DataTable
        window.qrFilter = 'all'; // 'all', 'active', 'inactive'
        let qrFilter = window.qrFilter; // Local reference for consistency

        // Filter button handlers
        $(document).on('click', '#filter-active-qr', function() {
            window.qrFilter = 'active';
            qrFilter = 'active';
            gridPage = 1; // reset pagination
            updateFilterButtons();
            reloadViews();
        });
        $(document).on('click', '#filter-inactive-qr', function() {
            window.qrFilter = 'inactive';
            qrFilter = 'inactive';
            gridPage = 1;
            updateFilterButtons();
            reloadViews();
        });
        $(document).on('click', '#filter-all-qr', function() {
            window.qrFilter = 'all';
            qrFilter = 'all';
            gridPage = 1;
            updateFilterButtons();
            reloadViews();
        });
        
        // Initialize filter button state on page load
        updateFilterButtons();
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
    
    // Function to reload both views when filter changes
    function reloadViews() {
        // Ensure filter is set before reloading
        const currentFilter = window.qrFilter || 'all';
        console.log('reloadViews called with filter:', currentFilter);
        
        // List view - reload DataTable with new filter
        if (table.length && $.fn.DataTable.isDataTable(table)) {
            const dt = table.DataTable();
            // Reset to first page when filter changes
            dt.page('first');
            // Use ajax.reload() with callback to ensure filter is included
            dt.ajax.reload(function(json) {
                console.log('DataTable reloaded with filter:', currentFilter);
            }, false); // false = don't reset pagination (we already did above)
        }
        // Grid view - reload with new filter
        if (typeof loadGridView === 'function') {
            loadGridView();
        }
    }

    // Initialize DataTable for List View
    // Use setTimeout to ensure DOM is fully ready
    setTimeout(function() {
        if (!table.length) {
            console.warn('QR table not found');
            return;
        }
        
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable(table)) {
            try {
                table.DataTable().destroy();
                table.empty();
            } catch(e) {
                console.warn('Error destroying existing DataTable:', e);
            }
        }
        
        // Verify table structure
        const headerCount = table.find('thead tr th').length;
        if (headerCount === 0) {
            console.error('No table headers found!');
            return;
        }
        
        if (headerCount !== 8) {
            console.error('Column count mismatch! Expected 8, found:', headerCount);
            return;
        }
        
        try {
            const dt = table.DataTable({
            processing: true,
            serverSide: true,
            ajax: function(data, callback, settings) {
                // Custom ajax function to ensure filter parameter is included
                const currentFilter = window.qrFilter || 'all';
                const baseUrl = table.data('ajax') || table.data('url') || table.attr('data-ajax') || table.attr('data-url') || window.qrIndexAjaxUrl || '';
                
                // Build URL with filter parameter
                const separator = baseUrl.indexOf('?') !== -1 ? '&' : '?';
                const urlWithFilter = baseUrl + separator + 'filter=' + currentFilter;
                
                //alert("urlWithFilter: " + urlWithFilter);
                // Add filter to data object - jQuery will serialize it into URL for GET requests
                data.filter = currentFilter;
                
                console.log('DataTables custom ajax - Filter:', currentFilter);
                console.log('DataTables custom ajax - URL:', urlWithFilter);
                console.log('DataTables custom ajax - Data object keys:', Object.keys(data));
                
                // Use jQuery ajax to make the request
                // For GET requests, jQuery will serialize data object and append to URL
                $.ajax({
                    url: urlWithFilter,
                    type: 'GET',
                    data: data, // This will be serialized and appended to URL as ?param=value&filter=active
                    dataType: 'json',
                    success: function(json) {
                        console.log('DataTables ajax success - Filter was:', currentFilter);
                        callback(json);
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables AJAX Error:', error, thrown);
                        callback({
                            draw: data.draw || 0,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                    }
                });
            },
            columns: [
                    { 
                        data: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '40px'
                    },
                    { 
                        data: 'id',
                        className: 'fw-semibold'
                    },
                    { 
                        data: 'name'
                    },
                    { 
                        data: 'code'
                    },
                    { 
                        data: 'booking_id'
                    },
                    { 
                        data: 'image',
                        orderable: false,
                        searchable: false
                    },
                    { 
                        data: 'created_by'
                    },
                    { 
                        data: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                order: [[1, 'desc']],
            responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                search: "Search QR Codes:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries found",
                    zeroRecords: "No matching QR codes found",
                    processing: "Processing..."
                },
                drawCallback: function(settings) {
                    //alert("drawCallback called");
                    // Update select all checkbox state after draw
                    if (typeof updateDeleteButton === 'function') {
                        updateDeleteButton();
                    }
                }
            });
        } catch (error) {
            console.error('DataTables initialization error:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack,
                tableExists: table.length > 0,
                headerCount: headerCount,
                tbodyExists: table.find('tbody').length > 0
            });
        }
    }, 50);

    // Multiple Delete Functionality
    let selectedQrIds = new Set();
    const selectAllCheckbox = $('#selectAll');
    const deleteSelectedBtn = $('#deleteSelectedBtn');
    const selectedCountSpan = $('#selectedCount');
    const deleteSelectedGridBtn = $('#deleteSelectedGridBtn');
    const selectedGridCountSpan = $('#selectedGridCount');

    // Function to update delete button state
    function updateDeleteButton() {
        const count = selectedQrIds.size;
        selectedCountSpan.text(count);
        if (selectedGridCountSpan.length) {
            selectedGridCountSpan.text(count);
        }
        const buttonHtml = '<i class="ri-delete-bin-line me-1"></i> Delete Selected (<span id="selectedCount">' + count + '</span>)';
        const gridButtonHtml = '<i class="ri-delete-bin-line me-1"></i> Delete Selected (<span id="selectedGridCount">' + count + '</span>)';
        if (count > 0) {
            deleteSelectedBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-danger').html(buttonHtml);
            if (deleteSelectedGridBtn.length) {
                deleteSelectedGridBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-danger').html(gridButtonHtml);
            }
        } else {
            deleteSelectedBtn.prop('disabled', true).removeClass('btn-danger').addClass('btn-secondary').html(buttonHtml);
            if (deleteSelectedGridBtn.length) {
                deleteSelectedGridBtn.prop('disabled', true).removeClass('btn-danger').addClass('btn-secondary').html(gridButtonHtml);
            }
        }
        // Update select all checkbox state
        if (table.length && $.fn.DataTable.isDataTable(table)) {
            const totalRows = table.DataTable().rows({ filter: 'applied' }).count();
            selectAllCheckbox.prop('checked', count > 0 && count === totalRows);
            selectAllCheckbox.prop('indeterminate', count > 0 && count < totalRows);
        }
    }

    // Select All checkbox handler
    selectAllCheckbox.on('change', function() {
        const isChecked = $(this).prop('checked');
        if (table.length && $.fn.DataTable.isDataTable(table)) {
            table.DataTable().$('.qr-checkbox').each(function() {
                const checkbox = $(this);
                const qrId = checkbox.data('qr-id');
                const qrIdStr = qrId.toString(); // Ensure consistent string format
                if (isChecked) {
                    selectedQrIds.add(qrIdStr);
                    checkbox.prop('checked', true);
                } else {
                    selectedQrIds.delete(qrIdStr);
                    checkbox.prop('checked', false);
                }
            });
            updateDeleteButton();
        }
    });

    // Individual checkbox handler (using event delegation)
    $(document).on('change', '.qr-checkbox', function() {
        const checkbox = $(this);
        const qrId = checkbox.data('qr-id');
        const qrIdStr = qrId.toString(); // Ensure consistent string format
        if (checkbox.prop('checked')) {
            selectedQrIds.add(qrIdStr);
        } else {
            selectedQrIds.delete(qrIdStr);
            selectAllCheckbox.prop('checked', false);
        }
        updateDeleteButton();
    });

    // Grid checkbox handler - ONLY source of truth for border-primary class
    // Only checkbox clicks can toggle selection - card clicks are ignored
    $(document).on('change', '.qr-checkbox-grid', function(e) {
        const checkbox = $(this);
        const qrId = checkbox.data('qr-id');
        const qrIdStr = qrId.toString(); // Ensure consistent string format
        const card = checkbox.closest('.qr-grid-card');
        const isChecked = checkbox.prop('checked');
        
        // Update selectedQrIds based on checkbox state
        if (isChecked) {
            selectedQrIds.add(qrIdStr);
            // Add border-primary border-2, remove border-0
            card.addClass('border-primary border-2').removeClass('border-0');
        } else {
            selectedQrIds.delete(qrIdStr);
            // Remove border-primary border-2, add border-0
            card.removeClass('border-primary border-2').addClass('border-0');
            selectAllCheckbox.prop('checked', false);
        }
        updateDeleteButton();
    });

    // Delete Selected button handler (for both list and grid)
    function handleBulkDelete() {
        if (selectedQrIds.size === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one QR code to delete.',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Delete Selected QR Codes?',
            text: `Are you sure you want to delete ${selectedQrIds.size} QR code(s)? This action cannot be undone.`,
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable buttons and show loading
                deleteSelectedBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');
                if (deleteSelectedGridBtn.length) {
                    deleteSelectedGridBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');
                }

                // Get CSRF token and route URL
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                let bulkDeleteUrl = $('#qr-list-view').data('bulk-delete-url') || $('#qr-grid-view').data('bulk-delete-url');
                if (!bulkDeleteUrl) {
                    // Fallback: construct URL from current path
                    const basePath = window.location.pathname.split('/admin')[0] || '';
                    bulkDeleteUrl = basePath + `/${window.adminBasePath}/qr/bulk-delete`;
                }

                // Make AJAX request
                fetch(bulkDeleteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ids: Array.from(selectedQrIds)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Determine icon and title based on result
                    let icon = 'success';
                    let title = 'Deleted!';
                    let htmlMessage = '';
                    
                    if (data.deleted > 0 && data.skipped > 0) {
                        // Some deleted, some skipped
                        icon = 'warning';
                        title = 'Partially Deleted';
                        htmlMessage = `<div style="text-align: left;">
                            <p style="color: #28a745; font-weight: 600; margin-bottom: 10px;">
                                ✓ Successfully deleted <strong>${data.deleted}</strong> QR code(s).
                            </p>
                            <p style="color: #ffc107; font-weight: 600; margin-bottom: 10px;">
                                ⚠️ <strong>${data.skipped}</strong> QR code(s) could not be deleted because they are assigned to bookings.
                            </p>`;
                        if (data.skipped_codes && data.skipped_codes.length > 0 && data.skipped_codes.length <= 5) {
                            const codesList = data.skipped_codes.map(qr => qr.code).join(', ');
                            htmlMessage += `<p style="color: #6c757d; font-size: 0.9em; margin-top: 8px;">
                                <strong>Assigned QR Codes:</strong> ${codesList}
                            </p>`;
                        } else if (data.skipped_codes && data.skipped_codes.length > 5) {
                            htmlMessage += `<p style="color: #6c757d; font-size: 0.9em; margin-top: 8px;">
                                (${data.skipped_codes.length} QR codes are assigned to bookings)
                            </p>`;
                        }
                        htmlMessage += `<p style="color: #6c757d; font-size: 0.85em; margin-top: 10px; font-style: italic;">
                            Please unassign them from bookings first if you want to delete them.
                        </p></div>`;
                    } else if (data.deleted == 0 && data.skipped > 0) {
                        // All skipped (assigned to bookings)
                        icon = 'error';
                        title = 'Cannot Delete';
                        htmlMessage = `<div style="text-align: left;">
                            <p style="color: #dc3545; font-weight: 600; margin-bottom: 10px;">
                                ❌ Cannot delete selected QR code(s).
                            </p>
                            <p style="color: #6c757d; margin-bottom: 10px;">
                                All <strong>${data.skipped}</strong> selected QR code(s) are assigned to bookings and cannot be deleted.
                            </p>`;
                        if (data.skipped_codes && data.skipped_codes.length > 0 && data.skipped_codes.length <= 5) {
                            const codesList = data.skipped_codes.map(qr => qr.code).join(', ');
                            htmlMessage += `<p style="color: #6c757d; font-size: 0.9em; margin-top: 8px;">
                                <strong>Assigned QR Codes:</strong> ${codesList}
                            </p>`;
                        }
                        htmlMessage += `<p style="color: #6c757d; font-size: 0.85em; margin-top: 10px; font-style: italic;">
                            Please unassign them from bookings first if you want to delete them.
                        </p></div>`;
                    } else if (data.deleted == 0) {
                        // Nothing deleted
                        icon = 'error';
                        title = 'Delete Failed';
                        htmlMessage = data.message || 'Failed to delete QR codes.';
                    } else {
                        // All deleted successfully
                        htmlMessage = data.message || `Successfully deleted ${data.deleted} QR code(s).`;
                    }
                    
                    Swal.fire({
                        icon: icon,
                        title: title,
                        html: htmlMessage || data.message || 'Failed to delete QR codes.',
                        confirmButtonText: 'OK',
                        timer: data.deleted > 0 && data.skipped == 0 ? 3000 : null,
                        timerProgressBar: data.deleted > 0 && data.skipped == 0
                    }).then(() => {
                        // Clear selection
                        selectedQrIds.clear();
                        selectAllCheckbox.prop('checked', false);
                        updateDeleteButton();
                        
                        // Reload DataTable if any were deleted
                        if (data.deleted > 0) {
                            if (table.length && $.fn.DataTable.isDataTable(table)) {
                                table.DataTable().ajax.reload(null, false);
                            }
                            // Reload grid view
                            if (typeof loadGridView === 'function') {
                                loadGridView();
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while deleting QR codes.',
                        confirmButtonText: 'OK'
                    });
                    updateDeleteButton();
                });
            }
        });
    }

    // Attach delete handler to both buttons
    deleteSelectedBtn.on('click', handleBulkDelete);
    deleteSelectedGridBtn.on('click', handleBulkDelete);

    // Clear selection when DataTable is redrawn
    if (table.length && $.fn.DataTable.isDataTable(table)) {
        table.DataTable().on('draw', function() {
            // Uncheck select all when table redraws
            selectAllCheckbox.prop('checked', false);
            selectAllCheckbox.prop('indeterminate', false);
            // Clear selection for rows that are no longer visible
            const visibleIds = new Set();
            table.DataTable().$('.qr-checkbox').each(function() {
                visibleIds.add($(this).data('qr-id'));
            });
            // Remove IDs that are no longer visible
            selectedQrIds.forEach(id => {
                if (!visibleIds.has(id)) {
                    selectedQrIds.delete(id);
                }
            });
            updateDeleteButton();
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
        gridContainer.html('<div class="col-12 text-center text-muted py-5"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</div>');
        
        // Get selected QR IDs as array
        const selectedIds = Array.from(selectedQrIds).map(id => parseInt(id, 10));
        
        $.ajax({
            url: window.location.pathname, // Use current route
            dataType: 'json',
            data: {
                view: 'grid',
                page: gridPage,
                per_page: gridPageSize,
                filter: qrFilter,
                selected_ids: selectedIds
            },
            success: function(response) {
                // Update pagination info
                if (response.pagination) {
                    gridTotalRecords = response.pagination.total_records || 0;
                    gridTotalPages = response.pagination.total_pages || 1;
                    gridPage = response.pagination.current_page || 1;
                }
                
                // Render HTML from Blade
                gridContainer.html(response.html || '<div class="col-12 text-center text-muted py-5">No QR codes found.</div>');
                
                // Update pagination
                renderGridPagination();
                
                // Initialize tooltips for grid view
                if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                    const tooltipTriggerList = gridContainer[0].querySelectorAll('[data-bs-toggle="tooltip"]');
                    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
                }
                
                // Sync checkbox and border states based on selectedQrIds
                gridContainer.find('.qr-checkbox-grid').each(function() {
                    const checkbox = $(this);
                    const qrId = checkbox.data('qr-id');
                    const qrIdStr = qrId.toString();
                    const card = checkbox.closest('.qr-grid-card');
                    const isSelected = selectedQrIds.has(qrIdStr);
                    
                    // Always sync checkbox state with selectedQrIds first
                    checkbox.prop('checked', isSelected);
                    
                    // Then sync border class based on checkbox checked state (checkbox is source of truth)
                    const isChecked = checkbox.prop('checked');
                    if (isChecked) {
                        card.addClass('border-primary border-2').removeClass('border-0');
                    } else {
                        card.removeClass('border-primary border-2').addClass('border-0');
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading grid view:', error);
                gridContainer.html('<div class="col-12 text-center text-danger py-5"><i class="ri-error-warning-line me-2"></i>Error loading QR codes. Please try again.</div>');
            }
        });
    }

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
                            
                            // Show booking list for assignment (allow reassignment if QR already has booking)
                            $('#assignBookingModalLabel').text('Assign QR To Booking' + (bookingId ? ' (Change Booking)' : ''));
                            
                            // If QR is already assigned, show current booking info at top
                            if (bookingId) {
                                $('#assign-modal-content').html(`<div class="alert alert-info mb-3">
                                    <i class="ri-information-line me-2"></i>
                                    <strong>Current Booking:</strong> Booking ID #${bookingId}. You can select a different booking to reassign this QR code.
                                </div>
                                <div class="text-center text-muted">Loading available bookings...</div>`);
                                
                                const bookingDetailsApiUrl = $('#assignBookingModal').data('booking-details-api');
                                // Load current booking details for reference (optional)
                                $.ajax({
                                    url: bookingDetailsApiUrl,
                                    data: { booking_id: bookingId },
                                    success: function(response) {
                                        if (response.booking) {
                                            const b = response.booking;
                                            let currentBookingHtml = `<div class="alert alert-info mb-3">
                                                <div class="d-flex align-items-start">
                                                    <i class="ri-information-line me-2 mt-1"></i>
                                                    <div class="flex-grow-1">
                                                        <strong>Current Booking:</strong>
                                                        <ul class="mb-0 mt-2" style="list-style: none; padding-left: 0;">
                                                            <li><strong>ID:</strong> #${b.id}</li>
                                                            <li><strong>Customer:</strong> ${b.customer || 'N/A'}</li>
                                                            <li><strong>Property:</strong> ${b.property_type || 'N/A'} ${b.bhk ? '- ' + b.bhk : ''}</li>
                                                            <li><strong>Address:</strong> ${b.address || 'N/A'}</li>
                                                        </ul>
                                                        <p class="mb-0 mt-2 text-muted small">You can select a different booking below to reassign this QR code.</p>
                                                    </div>
                                                </div>
                                            </div>`;
                                            $('#assign-modal-content').html(currentBookingHtml + '<div class="text-center text-muted">Loading available bookings...</div>');
                                        }
                                        // Continue to load booking list
                                        loadBookingList();
                                    },
                                    error: function() {
                                        // Continue with booking list loading even if current booking details fail
                                        loadBookingList();
                                    }
                                });
                            } else {
                                $('#assign-modal-content').html('<div class="text-center text-muted">Loading available bookings...</div>');
                                loadBookingList();
                            }
                            
                            // Function to load booking list
                            function loadBookingList() {
                                // Get current content to preserve current booking info
                                const currentContent = $('#assign-modal-content').html();
                                
                                // Append booking list HTML
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
                                                <i class="ri-checkbox-circle-line me-1"></i>${bookingId ? 'Change Booking' : 'Assign Booking'}
                                            </button>
                                        </div>
                                    </div>
                                `;
                                
                                // If there's current booking info, prepend it; otherwise just set the html
                                if (currentContent.includes('Current Booking')) {
                                    $('#assign-modal-content').html(currentContent.replace('<div class="text-center text-muted">Loading available bookings...</div>', html));
                                } else {
                                    $('#assign-modal-content').html(html);
                                }

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
    }); // End of assign-booking-btn click handler

    // Multiple Generate Modal functionality
    // Only initialize if modal exists on the page
    const multipleGenerateModal = document.getElementById('multipleGenerateModal');
    if (multipleGenerateModal) {
        const quantityInput = document.getElementById('quantity');
        const quickQuantityBtns = document.querySelectorAll('.quick-quantity-btn');
        const generateMultipleBtn = document.getElementById('generateMultipleBtn');
        const generateStatus = document.getElementById('generateStatus');

        // Function to generate QR codes
        function generateQRCodes(quantity) {
            if (!quantity || quantity < 1 || quantity > 1000) {
                if (generateStatus) {
                    generateStatus.className = 'alert alert-danger';
                    generateStatus.textContent = 'Please enter a valid quantity between 1 and 1000';
                    generateStatus.classList.remove('d-none');
                }
                return;
            }

            // Disable all buttons and show loading
            if (generateMultipleBtn) {
                generateMultipleBtn.disabled = true;
                generateMultipleBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating...';
            }
            quickQuantityBtns.forEach(b => {
                if (b) b.disabled = true;
            });
            if (generateStatus) {
                generateStatus.classList.add('d-none');
            }

            // Get CSRF token and route URL
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const bulkGenerateUrl = $('#multipleGenerateModal').data('bulk-generate-url') || window.location.origin + `/${window.adminBasePath}/qr/bulk-generate`;

            // Make AJAX request
            fetch(bulkGenerateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (generateStatus) {
                        generateStatus.className = 'alert alert-success';
                        generateStatus.textContent = `Successfully generated ${data.count} QR code(s)!`;
                        generateStatus.classList.remove('d-none');
                    }
                    
                    // Reset form after 2 seconds and close modal
                    setTimeout(() => {
                        if (quantityInput) quantityInput.value = '';
                        quickQuantityBtns.forEach(b => {
                            if (b) {
                                b.classList.remove('active');
                                b.disabled = false;
                            }
                        });
                        if (generateStatus) generateStatus.classList.add('d-none');
                        
                        // Close modal using Bootstrap 5
                        if (multipleGenerateModal) {
                            const modalElement = bootstrap?.Modal?.getInstance(multipleGenerateModal);
                            if (modalElement) {
                                modalElement.hide();
                            } else if (window.bootstrap) {
                                const modal = window.bootstrap.Modal.getInstance(multipleGenerateModal);
                                if (modal) {
                                    modal.hide();
                                }
                            } else {
                                // Fallback: use jQuery if Bootstrap JS not available
                                $('#multipleGenerateModal').modal('hide');
                            }
                        }
                        
                        // Reload both views after 1 more second
                        setTimeout(() => {
                            // Reload DataTable
                            if (table.length && $.fn.DataTable.isDataTable(table)) {
                                table.DataTable().ajax.reload(null, false);
                            }
                            // Reload grid view
                            if (typeof loadGridView === 'function') {
                                loadGridView();
                            }
                        }, 1000);
                    }, 2000);
                } else {
                    if (generateStatus) {
                        generateStatus.className = 'alert alert-danger';
                        generateStatus.textContent = data.message || 'Failed to generate QR codes';
                        generateStatus.classList.remove('d-none');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (generateStatus) {
                    generateStatus.className = 'alert alert-danger';
                    generateStatus.textContent = 'An error occurred while generating QR codes';
                    generateStatus.classList.remove('d-none');
                }
            })
            .finally(() => {
                if (generateMultipleBtn) {
                    generateMultipleBtn.disabled = false;
                    generateMultipleBtn.innerHTML = '<i class="ri-play-line me-1"></i> Generate';
                }
                quickQuantityBtns.forEach(b => {
                    if (b) b.disabled = false;
                });
            });
        }

        // Quick quantity buttons - directly generate on click
        if (quickQuantityBtns && quickQuantityBtns.length > 0) {
            quickQuantityBtns.forEach(btn => {
                if (btn) {
                    btn.addEventListener('click', function() {
                        const quantity = parseInt(this.getAttribute('data-quantity'));
                        if (quantityInput) quantityInput.value = quantity;
                        // Remove active class from all buttons
                        quickQuantityBtns.forEach(b => {
                            if (b) b.classList.remove('active');
                        });
                        // Add active class to clicked button
                        this.classList.add('active');
                        // Directly generate QR codes
                        generateQRCodes(quantity);
                    });
                }
            });
        }

        // Generate button click handler
        if (generateMultipleBtn) {
            generateMultipleBtn.addEventListener('click', function() {
                const quantity = quantityInput ? parseInt(quantityInput.value) : 0;
                generateQRCodes(quantity);
            });
        }

        // Reset modal when closed
        multipleGenerateModal.addEventListener('hidden.bs.modal', function() {
            if (quantityInput) quantityInput.value = '';
            quickQuantityBtns.forEach(b => {
                if (b) {
                    b.classList.remove('active');
                    b.disabled = false;
                }
            });
            if (generateStatus) generateStatus.classList.add('d-none');
        });
    }
});
