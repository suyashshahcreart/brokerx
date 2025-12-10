@extends('admin.layouts.vertical', ['title' => 'Booking Assignees', 'subTitle' => 'Property'])

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
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Booking Assignees</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Booking Assignees</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :fallback="route('admin.bookings.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                </div>
            </div>

            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">
                            <i class="ri-book-line me-2"></i>Bookings Assign
                        </h4>
                        <p class="text-muted mb-0">Assigne Booking to Photographer</p>
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
                        <table id="bookingAssigneesTable" class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Property</th>
                                    <th>Location</th>
                                    <th>Booking Date</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
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
    // Wait for all external libraries to load, then initialize DataTable
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure jQuery and DataTables are available
        if (typeof jQuery !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
            console.log('jQuery and DataTables are loaded, initializing...');
        } else {
            console.warn('jQuery or DataTables not fully loaded yet');
        }
    });
</script>

@vite(['resources/js/pages/booking-assignees-index.js'])
@endsection


