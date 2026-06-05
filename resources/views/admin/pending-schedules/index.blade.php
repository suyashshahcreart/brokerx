@extends('admin.layouts.vertical', ['title' => 'Pending Schedules', 'subTitle' => 'Bookings'])

@section('css')
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
    @vite(['resources/js/pages/pending-schedules-index.js'])
    <script>
        window.pendingSchedulesIndexUrl = @json(route('admin.pending-schedules.index'));
    </script>
@endsection