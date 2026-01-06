@extends('admin.layouts.vertical', ['title' => 'QR Codes', 'subTitle' => 'System'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">System</a></li>
                            <li class="breadcrumb-item active" aria-current="page">QR Codes</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">QR Code Management</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#multipleGenerateModal">
                        <i class="ri-add-circle-line me-1"></i> Multiple Generate
                    </button>
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
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <ul class="nav nav-pills" id="qrTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="qr-grid-tab" data-bs-toggle="tab" href="#qr-grid-view" role="tab" aria-controls="qr-grid-view" aria-selected="true">
                                    <i class="ri-layout-grid-line me-1"></i> Grid View
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="qr-list-tab" data-bs-toggle="tab" href="#qr-list-view" role="tab" aria-controls="qr-list-view" aria-selected="false">
                                    <i class="ri-list-check-2 me-1"></i> List View
                                </a>
                            </li>
                        </ul>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-success" id="filter-active-qr">Active</button>
                            <button class="btn btn-outline-danger" id="filter-inactive-qr">Inactive</button>
                            <button class="btn btn-secondary" id="filter-all-qr">All</button>
                        </div>
                    </div>
                    <div class="tab-content pt-2">
                        <div class="tab-pane fade" id="qr-list-view" role="tabpanel" aria-labelledby="qr-list-tab" data-bulk-delete-url="{{ route('admin.qr.bulk-delete') }}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <button type="button" class="btn btn-danger" id="deleteSelectedBtn" disabled>
                                        <i class="ri-delete-bin-line me-1"></i> Delete Selected (<span id="selectedCount">0</span>)
                                    </button>
                                </div>
                                <div class="text-muted small">
                                    <i class="ri-information-line me-1"></i> Select QR codes to delete
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="qr-table" data-ajax="{{ route('admin.qr.index') }}">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;">
                                                <input type="checkbox" id="selectAll" class="form-check-input" title="Select All">
                                            </th>
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
                        <div class="tab-pane fade show active" id="qr-grid-view" role="tabpanel" aria-labelledby="qr-grid-tab" data-bulk-delete-url="{{ route('admin.qr.bulk-delete') }}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <button type="button" class="btn btn-danger" id="deleteSelectedGridBtn" disabled>
                                        <i class="ri-delete-bin-line me-1"></i> Delete Selected (<span id="selectedGridCount">0</span>)
                                    </button>
                                </div>
                                <div class="text-muted small">
                                    <i class="ri-information-line me-1"></i> Select QR codes to delete
                                </div>
                            </div>
                            <div class="row g-2" id="qr-grid-container">
                                <!-- Grid cards will be loaded here by JS -->
                            </div>
                        </div>
                        <!-- Assign Booking Modal -->
                        <div class="modal fade" id="assignBookingModal" data-booking-list-api="{{ route('bookings.api-list') }}" data-assign-api="{{ route('qr.assign-booking') }}" data-booking-details-api="{{ route('bookings.details') }}" tabindex="-1" aria-labelledby="assignBookingModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
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

    <!-- Multiple Generate Modal -->
    <div class="modal fade" id="multipleGenerateModal" data-bulk-generate-url="{{ route('admin.qr.bulk-generate') }}" tabindex="-1" aria-labelledby="multipleGenerateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="multipleGenerateModalLabel">Generate Multiple QR Codes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="multipleGenerateForm">
                        <div class="mb-3">
                            <label for="quantity" class="form-label">How many QR codes to generate? <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="1000" required placeholder="Enter quantity">
                            <div class="form-text">Enter a number between 1 and 1000</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Quick Select:</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-outline-primary quick-quantity-btn" data-quantity="25">25</button>
                                <button type="button" class="btn btn-outline-primary quick-quantity-btn" data-quantity="50">50</button>
                                <button type="button" class="btn btn-outline-primary quick-quantity-btn" data-quantity="75">75</button>
                                <button type="button" class="btn btn-outline-primary quick-quantity-btn" data-quantity="100">100</button>
                            </div>
                        </div>
                        
                        <div id="generateStatus" class="alert d-none" role="alert"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="generateMultipleBtn">
                        <i class="ri-play-line me-1"></i> Generate
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <style>
        .qr-grid-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
        }
        
        .qr-grid-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
        }
        
        .qr-grid-card.border-primary {
            box-shadow: 0 0 0 2px rgba(85, 110, 230, 0.25) !important;
        }
        
        .qr-grid-card .card-body {
            padding: 0.75rem !important;
        }
        
        .qr-image-wrapper {
            height: 120px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            border-radius: 6px;
            padding: 8px;
            transition: transform 0.3s ease;
            overflow: hidden;
        }
        
        .qr-grid-card:hover .qr-image-wrapper {
            transform: scale(1.03);
        }
        
        .qr-grid-card .form-check-input {
            width: 20px;
            height: 20px;
            margin-top: 0.125rem;
            cursor: pointer;
            background-color: white;
            border: 2px solid #dee2e6;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .qr-grid-card .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .qr-grid-card .form-check-input:focus {
            border-color: #6366f1;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }
        
        .qr-grid-card .form-check-input:hover {
            border-color: #6366f1;
        }
        
        /* Card styling - NOT clickable, only checkbox controls selection */
        .qr-grid-card {
            transition: all 0.2s ease;
        }
        
        .qr-grid-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .qr-grid-card.border-primary {
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }
        
        /* Interactive elements have pointer cursor */
        .qr-grid-card button,
        .qr-grid-card a,
        .qr-grid-card form,
        .qr-grid-card .form-check,
        .qr-grid-card .form-check-input,
        .qr-grid-card .form-check-label {
            cursor: pointer;
            pointer-events: auto;
        }
        
        .qr-grid-card .badge {
            font-weight: 500;
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            letter-spacing: 0.3px;
        }
        
        .qr-grid-card .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            white-space: nowrap;
        }
        
        .qr-grid-card .text-truncate {
            max-width: 100%;
        }
        
        .qr-grid-card .fw-semibold {
            font-size: 0.85rem;
            line-height: 1.3;
        }
        
        .qr-grid-card .small {
            font-size: 0.7rem;
        }
        
        /* Prevent card click when clicking on interactive elements */
        .qr-grid-card .form-check,
        .qr-grid-card .btn,
        .qr-grid-card a {
            cursor: pointer;
            pointer-events: auto;
        }
        
        @media (max-width: 576px) {
            .qr-grid-card {
                margin-bottom: 0.75rem;
            }
            
            .qr-image-wrapper {
                height: 100px !important;
            }
            
            .qr-grid-card .card-body {
                padding: 0.5rem !important;
            }
        }
        
        @media (min-width: 576px) and (max-width: 768px) {
            .qr-image-wrapper {
                height: 110px !important;
            }
        }
        
        @media (min-width: 768px) {
            .qr-image-wrapper {
                height: 120px;
            }
        }
        
        @media (min-width: 992px) {
            .qr-image-wrapper {
                height: 130px;
            }
        }
    </style>
@endsection

@section('script')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/pages/qr-index.js'])
@endsection