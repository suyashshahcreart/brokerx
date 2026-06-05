@extends('admin.layouts.vertical', ['title' => 'QR Activity', 'subTitle' => 'QR Analytics'])

@section('css')
    <style>
        .booking-filters-panel {
            position: relative;
            background: var(--bs-light-bg-subtle, #f8f9fa);
            border: 1px solid var(--bs-border-color);
            border-radius: 0.375rem;
            overflow: visible;
            padding: 0.35rem 0.75rem 0.625rem;
        }

        .booking-filters-grid {
            display: grid;
            gap: 0.5rem 0.65rem;
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }

        .booking-filter-clear {
            position: absolute;
            top: 2px;
            right: 0.5rem;
            z-index: 2;
            line-height: 1;
        }

        .booking-filter-clear .btn {
            padding: 0.1rem 0.35rem;
            font-size: 0.6875rem;
            height: 22px;
            min-width: auto;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
        }

        .booking-filter-clear .btn i {
            font-size: 0.8rem;
            margin: 0 !important;
        }

        @media (max-width: 1199.98px) {
            .booking-filters-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .booking-filters-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .booking-filters-grid {
                grid-template-columns: 1fr;
            }
        }

        .booking-filter-item {
            min-width: 0;
        }

        #filtersSection .form-label {
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: var(--bs-secondary-color);
        }

        #filtersSection .form-control-sm,
        #filtersSection .form-select-sm {
            min-height: 32px;
            font-size: 0.8125rem;
        }

        #filterDateRange {
            background-color: var(--bs-body-bg, #fff);
            height: 32px;
            min-height: 32px;
        }

        .booking-filters-panel > .booking-filters-grid + .booking-filters-grid {
            margin-top: 0.5rem;
        }

        @include('admin.partials.mobile-filters-toggle-styles')
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">QR Activity</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">QR Activity Analytics</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                </div>
            </div>

            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">QR Analytics List</h4>
                        <p class="text-muted mb-0">Track and analyze QR code scans and visits</p>
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
                    <div class="booking-filters-panel mb-3" id="filtersSection">
                        @include('admin.partials.mobile-filters-toggle-button')

                        <div class="booking-filters-body" id="mobileFiltersBody">
                        <div class="booking-filter-clear">
                            <button type="button" class="btn btn-sm btn-soft-secondary" id="clearFilters" title="Clear All">
                                <i class="ri-filter-off-line"></i><span>Clear All</span>
                            </button>
                        </div>

                        <div class="booking-filters-grid">
                            <div class="booking-filter-item">
                                <label for="filterTourCode" class="form-label">Tour Code</label>
                                <input type="text" id="filterTourCode" class="form-control form-control-sm"
                                    placeholder="Search tour code..." autocomplete="off" />
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterBookingId" class="form-label">Booking ID</label>
                                <input type="number" id="filterBookingId" class="form-control form-control-sm"
                                    placeholder="Booking ID..." min="1" autocomplete="off" />
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterCountry" class="form-label">Country</label>
                                <input type="text" id="filterCountry" class="form-control form-control-sm"
                                    placeholder="Search country..." autocomplete="off" />
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterCity" class="form-label">City</label>
                                <input type="text" id="filterCity" class="form-control form-control-sm"
                                    placeholder="Search city..." autocomplete="off" />
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterDeviceType" class="form-label">Device Type</label>
                                <select id="filterDeviceType" class="form-select form-select-sm">
                                    <option value="">All Devices</option>
                                    <option value="mobile">Mobile</option>
                                    <option value="tablet">Tablet</option>
                                    <option value="desktop">Desktop</option>
                                </select>
                            </div>
                        </div>

                        <div class="booking-filters-grid">
                            <div class="booking-filter-item">
                                <label for="filterLocationSource" class="form-label">Location Source</label>
                                <select id="filterLocationSource" class="form-select form-select-sm">
                                    <option value="">All Sources</option>
                                    <option value="GPS">GPS</option>
                                    <option value="IP">IP</option>
                                    <option value="IP-IPAPI">IP-IPAPI</option>
                                    <option value="UNAVAILABLE">Unavailable</option>
                                </select>
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterTrackingStatus" class="form-label">Tracking Status</label>
                                <select id="filterTrackingStatus" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="success">Success</option>
                                    <option value="error">Error</option>
                                    <option value="invalid_tour_code">Invalid Tour Code</option>
                                </select>
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterPageType" class="form-label">Page Type</label>
                                <select id="filterPageType" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="welcome">Welcome</option>
                                    <option value="tour_code">Tour Code</option>
                                    <option value="analytics">Analytics</option>
                                </select>
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterDateRange" class="form-label">Scan Date Range</label>
                                <input type="text" id="filterDateRange" class="form-control form-control-sm"
                                    placeholder="Select date range" readonly />
                            </div>
                        </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="qr-analytics-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tour Code</th>
                                    <th>Booking</th>
                                    <th>Location</th>
                                    <th>Device Info</th>
                                    <th>Location Source</th>
                                    <th>Status</th>
                                    <th>Scan Date</th>
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

    <!-- Fullscreen View Analytics Modal -->
    <div class="modal fade" id="viewAnalyticsModal" tabindex="-1" aria-labelledby="viewAnalyticsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAnalyticsModalLabel">QR Analytics Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="analyticsDetails" style="max-height: calc(100vh - 120px); overflow-y: auto;">
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
    @php
        $googleMapsApiKey = config('services.google.maps_api_key', '');
        $googleMapsUrl = 'https://maps.googleapis.com/maps/api/js';
        if (!empty($googleMapsApiKey)) {
            $googleMapsUrl .= '?key=' . $googleMapsApiKey . '&libraries=places,geometry';
        } else {
            $googleMapsUrl .= '?libraries=places,geometry';
        }
    @endphp
    <script src="{{ $googleMapsUrl }}" async defer></script>

    @vite(['resources/js/pages/qr-analytics-index.js'])
    <script>
        window.qrAnalyticsIndexUrl = @json(route('admin.qr-analytics.index'));
        window.qrAnalyticsShowUrl = @json(route('admin.qr-analytics.show', ':id'));

        window.initQRMap = function () {
            if (typeof google !== 'undefined' && google.maps) {
                window.googleMapsLoaded = true;
            }
        };

        if (typeof google !== 'undefined' && google.maps) {
            window.googleMapsLoaded = true;
        }
    </script>
@endsection
