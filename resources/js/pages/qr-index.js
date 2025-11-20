import $ from 'jquery';
import 'datatables.net-bs5';


$(function() {
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
            ajax: table.data('ajax') || table.data('url') || table.attr('data-ajax') || table.attr('data-url') || window.qrIndexAjaxUrl || '',
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
            success: function(response) {
                let data = response.data || response;
                let html = '';
                if (Array.isArray(data) && data.length === 0) {
                    html = '<div class="col-12 text-center text-muted">No QR codes found.</div>';
                } else {
                    data.forEach(function(qr) {
                        const statusBadge = false ? `<span class="badge bg-success">Active</span>` : `<span class="badge bg-danger ">Inactive</span>`;

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
                                    <div></div>
                                    <div class="d-flex justify-content-end gap-2">
                                        ${qr.actions}
                                    </div>

                                </div>
                            </div>
                        </div>`;
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
