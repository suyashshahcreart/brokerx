@extends('admin.layouts.vertical', ['title' => 'QR Codes', 'subTitle' => 'System'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">System</a></li>
                            <li class="breadcrumb-item active" aria-current="page">QR Codes</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">QR Code Management</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    <a href="{{ route('admin.qr.create') }}" class="btn btn-primary" title="Add QR Code"
                        data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add QR Code">
                        <i class="ri-qr-code-line me-1"></i> New QR Code
                    </a>
                </div>
            </div>

            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">QR Code List</h4>
                        <p class="text-muted mb-0">Review QR codes, links, and actions</p>
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
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <ul class="nav nav-pills" id="qrTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="qr-list-tab" data-bs-toggle="tab" href="#qr-list-view" role="tab" aria-controls="qr-list-view" aria-selected="true">
                                    <i class="ri-list-check-2 me-1"></i> List View
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="qr-grid-tab" data-bs-toggle="tab" href="#qr-grid-view" role="tab" aria-controls="qr-grid-view" aria-selected="false">
                                    <i class="ri-layout-grid-line me-1"></i> Grid View
                                </a>
                            </li>
                        </ul>
                        <div class="ms-auto d-flex gap-2">
                            <button class="btn btn-outline-success" id="filter-active-qr">Active</button>
                            <button class="btn btn-outline-danger" id="filter-inactive-qr">Inactive</button>
                            <button class="btn btn-secondary" id="filter-all-qr">All</button>
                        </div>
                    </div>
                    <div class="tab-content pt-2">
                        <div class="tab-pane fade show active" id="qr-list-view" role="tabpanel" aria-labelledby="qr-list-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="qr-table" data-ajax="{{ route('admin.qr.index') }}">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Booking</th>
                                            <th>Image</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="qr-grid-view" role="tabpanel" aria-labelledby="qr-grid-tab">
                            <div class="row g-5" id="qr-grid-container">
                                <!-- Grid cards will be loaded here by JS -->
                            </div>
                        </div>
                        <!-- Assign Booking Modal -->
                        <div class="modal fade" id="assignBookingModal" data-booking-list-api="{{ route('bookings.api-list') }}" data-assign-api="{{ route('qr.assign-booking') }}" data-booking-details-api="{{ route('bookings.details') }}" tabindex="-1" aria-labelledby="assignBookingModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="assignBookingModalLabel">QR & Booking Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="assign-modal-qr-details" class="mb-3"></div>
                                        <div id="assign-modal-content">
                                            <div class="text-center text-muted">Loading...</div>
                                        </div>
                                    </div>
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/pages/qr-index.js'])
@endsection