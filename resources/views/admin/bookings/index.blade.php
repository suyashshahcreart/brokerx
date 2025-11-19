@extends('admin.layouts.vertical', ['title' => 'Bookings', 'subTitle' => 'Property'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Property</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Bookings</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Bookings</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> New Booking
                    </a>
                </div>
            </div>

            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Bookings List</h4>
                        <p class="text-muted mb-0">Manage customer property bookings</p>
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
                        <button type="button" class="btn btn-light border" data-panel-action="close" title="Close">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="bookings-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Type / Subtype</th>
                                    <th>BHK</th>
                                    <th>City / State</th>
                                    <th>Area</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <!-- Schedule Modal -->
                    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="scheduleModalLabel">Schedule Booking</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="scheduleForm">
                                        <div class="mb-3">
                                            <label class="form-label">Current Booking Date</label>
                                            <div id="current-booking-date" class="form-control-plaintext text-primary mb-2"></div>
                                            <label for="schedule-date" class="form-label">Select Date</label>
                                            <input type="date" class="form-control" id="schedule-date" name="schedule_date" required>
                                        </div>
                                        <input type="hidden" id="schedule-booking-id" name="booking_id">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" id="scheduleSubmitBtn">Schedule</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const $ = window.jQuery;
            if (!$) return;

            const table = $('#bookings-table');
            if (!table.length) {
                return;
            }

            const dataTable = table.DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.bookings.index') }}',
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
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search bookings...'
                },
                lengthMenu: [10, 25, 50, 100],
                responsive: true
            });

            // Schedule button click handler
            let holidays = [];
            let minDate = null;
            let maxDate = null;

            function setDateLimits() {
                const today = new Date();
                minDate = today.toISOString().split('T')[0];
                const max = new Date();
                max.setDate(today.getDate() + 60);
                maxDate = max.toISOString().split('T')[0];
                $('#schedule-date').attr('min', minDate);
                $('#schedule-date').attr('max', maxDate);
            }

            function isHoliday(dateStr) {
                return holidays.includes(dateStr);
            }

            // Fetch holidays from API on page load
            function fetchHolidays() {
                $.get('/api/holidays', function(data) {
                    holidays = data.map(h => h.date);
                });
            }
            fetchHolidays();

            table.on('click', '.btn-soft-warning', function (e) {
                e.preventDefault();
                const row = $(this).closest('tr');
                const rowData = dataTable.row(row).data();
                if (rowData && rowData.id) {
                    $('#schedule-booking-id').val(rowData.id);
                    // Set the booking date if available
                    if (rowData.booking_date && rowData.booking_date !== '-') {
                        $('#schedule-date').val(rowData.booking_date);
                        $('#current-booking-date').text(rowData.booking_date);
                    } else {
                        $('#schedule-date').val('');
                        $('#current-booking-date').text('Not set');
                    }
                    setDateLimits();
                    // Disable holidays and past dates on input
                    $('#schedule-date').off('input').on('input', function() {
                        const val = this.value;
                        if (isHoliday(val)) {
                            this.value = '';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Holiday',
                                    text: 'Selected date is a holiday. Please choose another date.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        }
                    });
                    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
                    modal.show();
                }
            });

            // Schedule submit button
            $('#scheduleSubmitBtn').on('click', function () {
                const bookingId = $('#schedule-booking-id').val();
                const date = $('#schedule-date').val();
                if (!date) {
                    $('#schedule-date').addClass('is-invalid');
                    return;
                }
                $('#schedule-date').removeClass('is-invalid');
                $.ajax({
                    url: `/admin/bookings/${bookingId}/reschedule`,
                    method: 'POST',
                    data: {
                        schedule_date: date,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
                        if (response.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Rescheduled!',
                                    text: 'Booking date has been updated.',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            }
                            table.DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to reschedule booking.'
                            });
                        } else {
                            alert('Failed to reschedule booking.');
                        }
                    }
                });
            });
        });
    </script>
@endsection