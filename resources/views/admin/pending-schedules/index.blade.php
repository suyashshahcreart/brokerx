@extends('admin.layouts.vertical', ['title' => 'Pending Schedules', 'subTitle' => 'Bookings'])

@section('css')
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Pending Schedules</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Pending Schedules</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :fallback="route('admin.bookings.index')" :classes="['btn', 'btn-soft-secondary']"
                        :merge="false" icon="ri-arrow-go-back-line" />
                </div>
            </div>

            <div class="card panel-card border-warning border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">
                            <i class="ri-calendar-todo-line me-2"></i>Pending Schedule Requests
                        </h4>
                        <p class="text-muted mb-0">Approve or decline customer schedule requests</p>
                    </div>
                    <div class="panel-actions d-flex gap-2">
                        <button type="button" class="btn btn-light border" data-panel-action="refresh" title="Refresh">
                            <i class="ri-refresh-line"></i>
                        </button>
                        <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                            <i class="ri-arrow-up-s-line"></i>
                        </button>
                        <button type="button" class="btn btn-light border" data-panel-action="fullscreen"
                            title="Fullscreen">
                            <i class="ri-fullscreen-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="pending-schedules-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Type / Subtype</th>
                                    <th>BHK</th>
                                    <th>City / State</th>
                                    <th>Area</th>
                                    <th>Price</th>
                                    <th>Requested Date</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Decline Reason Modal -->
    <div class="modal fade" id="declineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Decline Schedule Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Booking Details -->
                    <div id="declineBookingDetails" class="mb-3"></div>

                    <div class="mb-3">
                        <label for="declineReason" class="form-label">Reason for Decline <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="declineReason" rows="3"
                            placeholder="Enter reason for declining this schedule request..." required></textarea>
                    </div>
                    <input type="hidden" id="declineBookingId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="submitDecline()">
                        <i class="ri-close-line me-1"></i> Decline Schedule
                    </button>
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- jQuery (must be loaded before DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const baseUrl = '{{ url("/") }}';
        const csrfToken = '{{ csrf_token() }}';
        let table = null; // Define table variable globally

        // Initialize DataTable
        $(document).ready(function () {
            table = $('#pending-schedules-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("admin.pending-schedules.index") }}',
                    error: function (xhr, error, code) {
                        console.error('DataTable error:', xhr, error, code);
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'user', name: 'user.firstname' },
                    { data: 'type_subtype', name: 'propertyType.name' },
                    { data: 'bhk', name: 'bhk.name' },
                    { data: 'city_state', name: 'city.name' },
                    { data: 'area', name: 'area' },
                    { data: 'price', name: 'price' },
                    { data: 'booking_date', name: 'booking_date' },
                    { data: 'status', name: 'status' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    emptyTable: "No pending schedule requests"
                },
                drawCallback: function () {
                    // Re-initialize tooltips for dynamically rendered action buttons
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            });
        });

        // Accept schedule
        window.acceptSchedule = async function (bookingId) {
            // Get booking data from DataTable
            const dataTable = table || $('#pending-schedules-table').DataTable();
            const rowData = dataTable.rows().data().toArray().find(row => row.id === bookingId);

            const requestedDate = rowData?.booking_date || 'Not specified';
            const customerNotes = (rowData?.booking_notes || '').trim();
            const userName = rowData?.user || 'N/A';

            // Escape HTML to prevent XSS
            const escapeHtml = (text) => {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };

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

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`${baseUrl}/admin/pending-schedules/${bookingId}/accept`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
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
                        if (table) {
                            table.ajax.reload();
                        } else {
                            window.location.reload();
                        }
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
            }
        };

        // Decline schedule - open modal
        window.declineSchedule = function (bookingId) {
            document.getElementById('declineBookingId').value = bookingId;
            document.getElementById('declineReason').value = '';

            // Get booking data from DataTable
            const dataTable = table || $('#pending-schedules-table').DataTable();
            const rowData = dataTable.rows().data().toArray().find(row => row.id === bookingId);

            const requestedDate = rowData?.booking_date || 'Not specified';
            const customerNotes = (rowData?.booking_notes || '').trim();
            const userName = rowData?.user || 'N/A';

            // Escape HTML to prevent XSS
            const escapeHtml = (text) => {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            };

            // Build booking details HTML
            let detailsHtml = '<div class="border-bottom pb-2 mb-3">';
            detailsHtml += '<p class="mb-2"><strong class="text-muted">Customer:</strong> ' + userName + '</p>';
            if (requestedDate && requestedDate !== '-' && requestedDate !== 'Not specified') {
                detailsHtml += '<p class="mb-0"><strong class="text-muted">Requested Date:</strong> <span class="text-primary">' + requestedDate + '</span></p>';
            }
            detailsHtml += '</div>';

            // Add customer notes if available
            if (customerNotes) {
                detailsHtml += '<div class="mb-3">';
                detailsHtml += '<label class="form-label mb-2" style="font-weight: 600; color: #495057;"><strong>Customer Notes:</strong></label>';
                detailsHtml += '<div class="alert alert-info py-3 mb-0" style="background-color: #d1ecf1; border-left: 4px solid #0dcaf0;">';
                detailsHtml += '<div class="d-flex align-items-start">';
                detailsHtml += '<i class="ri-message-3-line me-2 mt-1" style="color: #0dcaf0; font-size: 1.1rem;"></i>';
                detailsHtml += '<div style="color: #055160; line-height: 1.6; white-space: pre-wrap;">' + escapeHtml(customerNotes) + '</div>';
                detailsHtml += '</div>';
                detailsHtml += '</div>';
                detailsHtml += '</div>';
            }

            document.getElementById('declineBookingDetails').innerHTML = detailsHtml;

            const modal = new bootstrap.Modal(document.getElementById('declineModal'));
            modal.show();
        };

        // Submit decline
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
                const response = await fetch(`${baseUrl}/admin/pending-schedules/${bookingId}/decline`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ reason: reason })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('declineModal'));
                    modal.hide();

                    await Swal.fire({
                        icon: 'success',
                        title: 'Declined!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    if (table) {
                        table.ajax.reload();
                    } else {
                        window.location.reload();
                    }
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
        }
    </script>
@endsection