@extends('admin.layouts.vertical', ['title' => 'Tour Notifications', 'subTitle' => 'Notify Tour'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tour Notifications</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Tour Notifications</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                </div>
            </div>

            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Tour Notifications List</h4>
                        <p class="text-muted mb-0">Manage tour notification requests</p>
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
                    <!-- Filters Section -->
                    <div class="row mb-4 g-3" id="filtersSection">
                        <div class="col-md-3">
                            <label for="filterTourCode" class="form-label">Tour Code</label>
                            <input type="text" id="filterTourCode" class="form-control form-control-sm"
                                placeholder="Search tour code..." />
                        </div>
                        <div class="col-md-3">
                            <label for="filterPhoneNumber" class="form-label">Phone Number</label>
                            <input type="text" id="filterPhoneNumber" class="form-control form-control-sm"
                                placeholder="Search phone number..." />
                        </div>
                        <div class="col-md-3">
                            <label for="filterStatus" class="form-label">Status</label>
                            <select id="filterStatus" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="notified">Notified</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterBookingId" class="form-label">Booking ID</label>
                            <input type="number" id="filterBookingId" class="form-control form-control-sm"
                                placeholder="Enter booking ID..." />
                        </div>
                        <div class="col-md-3">
                            <label for="filterDateFrom" class="form-label">Date From</label>
                            <input type="date" id="filterDateFrom" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-3">
                            <label for="filterDateTo" class="form-label">Date To</label>
                            <input type="date" id="filterDateTo" class="form-control form-control-sm" />
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-sm btn-primary" id="applyFilters">
                                <i class="ri-search-line me-2"></i>Apply Filters
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" id="clearFilters">
                                <i class="ri-close-line me-2"></i>Clear Filters
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tour-notifications-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tour Code</th>
                                    <th>Phone Number</th>
                                    <th>Booking</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Notified At</th>
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

    <!-- View Notification Modal -->
    <div class="modal fade" id="viewNotificationModal" tabindex="-1" aria-labelledby="viewNotificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewNotificationModalLabel">Tour Notification Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="notificationDetails">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/pages/tour-notifications-index.js'])
    <script>
        // Pass data to JavaScript
        window.tourNotificationsIndexUrl = '{{ route('admin.tour-notifications.index') }}';
        window.tourNotificationsShowUrl = '{{ route('admin.tour-notifications.show', ':id') }}';
    </script>
@endsection

