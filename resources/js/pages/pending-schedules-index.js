/**
 * Pending Schedules Index Page
 */
import $ from 'jquery';
if (typeof window.$ === 'undefined') {
    window.$ = window.jQuery = $;
}

import 'datatables.net-bs5';
import Swal from 'sweetalert2';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
let table = null;

$(document).ready(function () {
    table = $('#pending-schedules-table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        searchDelay: 500,
        ajax: {
            url: window.pendingSchedulesIndexUrl || '',
            error: function (xhr, error, code) {
                console.error('DataTable error:', xhr, error, code);
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'customer', name: 'customer' },
            { data: 'type_subtype', name: 'type_subtype' },
            { data: 'bhk', name: 'bhk.name' },
            { data: 'city_state', name: 'city_state' },
            { data: 'area', name: 'area' },
            { data: 'price', name: 'price' },
            { data: 'booking_date', name: 'booking_date' },
            { data: 'status', name: 'status' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[0, 'desc']],
        language: {
            emptyTable: 'No pending schedule requests'
        }
    });

    $('[data-panel-action="refresh"]').on('click', function () {
        table.ajax.reload(null, false);
    });
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getRowData(bookingId) {
    const dataTable = table || $('#pending-schedules-table').DataTable();
    return dataTable.rows().data().toArray().find(row => row.id === bookingId);
}

window.acceptSchedule = async function (bookingId) {
    const rowData = getRowData(bookingId);
    const requestedDate = rowData?.booking_date || 'Not specified';
    const customerNotes = (rowData?.booking_notes || '').trim();
    const userName = rowData?.user || rowData?.customer || 'N/A';

    const htmlContent = `
        <div class="text-start mb-3">
            <div class="border-bottom pb-2 mb-2">
                <p class="mb-2"><strong class="text-muted">Customer:</strong> ${userName}</p>
                ${requestedDate !== '-' && requestedDate !== 'Not specified' ? `
                    <p class="mb-0"><strong class="text-muted">Requested Date:</strong> <span class="text-primary">${requestedDate}</span></p>
                ` : ''}
            </div>
            ${customerNotes ? `
                <div class="mb-3">
                    <label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Customer Notes:</strong></label>
                    <div class="alert alert-info py-3 mb-0" style="background-color: #d1ecf1; border-left: 4px solid #0dcaf0;">
                        <div class="d-flex align-items-start">
                            <i class="ri-message-3-line me-2 mt-1" style="color: #0dcaf0; font-size: 1.1rem;"></i>
                            <div style="color: #055160; line-height: 1.6; white-space: pre-wrap;">${escapeHtml(customerNotes)}</div>
                        </div>
                    </div>
                </div>
            ` : ''}
            <div>
                <label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Admin Notes (Optional):</strong></label>
            </div>
        </div>
    `;

    const result = await Swal.fire({
        title: 'Accept Schedule?',
        html: htmlContent,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Accept',
        cancelButtonText: 'Cancel',
        input: 'textarea',
        inputPlaceholder: 'Add admin notes (optional)...',
        inputAttributes: {
            maxlength: 500
        }
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        const response = await fetch(`${window.appBaseUrl || ''}/${window.adminBasePath}/pending-schedules/${bookingId}/accept`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json'
            },
            body: JSON.stringify({ notes: result.value || null })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Accepted!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            });
            table?.ajax.reload(null, false);
        } else {
            throw new Error(data.message || 'Failed to accept schedule');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to accept schedule'
        });
    }
};

window.declineSchedule = function (bookingId) {
    document.getElementById('declineBookingId').value = bookingId;
    document.getElementById('declineReason').value = '';

    const rowData = getRowData(bookingId);
    const requestedDate = rowData?.booking_date || 'Not specified';
    const customerNotes = (rowData?.booking_notes || '').trim();
    const userName = rowData?.user || rowData?.customer || 'N/A';

    let detailsHtml = '<div class="border-bottom pb-2 mb-3">';
    detailsHtml += '<p class="mb-2"><strong class="text-muted">Customer:</strong> ' + userName + '</p>';
    if (requestedDate && requestedDate !== '-' && requestedDate !== 'Not specified') {
        detailsHtml += '<p class="mb-0"><strong class="text-muted">Requested Date:</strong> <span class="text-primary">' + requestedDate + '</span></p>';
    }
    detailsHtml += '</div>';

    if (customerNotes) {
        detailsHtml += '<div class="mb-3">';
        detailsHtml += '<label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Customer Notes:</strong></label>';
        detailsHtml += '<div class="alert alert-info py-3 mb-0" style="background-color: #d1ecf1; border-left: 4px solid #0dcaf0;">';
        detailsHtml += '<div class="d-flex align-items-start">';
        detailsHtml += '<i class="ri-message-3-line me-2 mt-1" style="color: #0dcaf0; font-size: 1.1rem;"></i>';
        detailsHtml += '<div style="color: #055160; line-height: 1.6; white-space: pre-wrap;">' + escapeHtml(customerNotes) + '</div>';
        detailsHtml += '</div></div></div>';
    }

    document.getElementById('declineBookingDetails').innerHTML = detailsHtml;

    const modal = new bootstrap.Modal(document.getElementById('declineModal'));
    modal.show();
};

window.submitDecline = async function () {
    const bookingId = document.getElementById('declineBookingId').value;
    const reason = document.getElementById('declineReason').value.trim();

    if (!reason) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Please provide a reason for declining'
        });
        return;
    }

    try {
        const response = await fetch(`${window.appBaseUrl || ''}/${window.adminBasePath}/pending-schedules/${bookingId}/decline`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json'
            },
            body: JSON.stringify({ reason })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            bootstrap.Modal.getInstance(document.getElementById('declineModal'))?.hide();

            await Swal.fire({
                icon: 'success',
                title: 'Declined!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            });
            table?.ajax.reload(null, false);
        } else {
            throw new Error(data.message || 'Failed to decline schedule');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to decline schedule'
        });
    }
};
