@extends('admin.layouts.vertical', ['title' => 'QR Activity', 'subTitle' => 'QR Analytics'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
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
                    <!-- Filters Section -->
                    <div class="row mb-4 g-3" id="filtersSection">
                        <div class="col-md-3">
                            <label for="filterTourCode" class="form-label">Tour Code</label>
                            <input type="text" id="filterTourCode" class="form-control form-control-sm"
                                placeholder="Search tour code..." />
                        </div>
                        <div class="col-md-3">
                            <label for="filterBookingId" class="form-label">Booking ID</label>
                            <input type="number" id="filterBookingId" class="form-control form-control-sm"
                                placeholder="Enter booking ID..." />
                        </div>
                        <div class="col-md-3">
                            <label for="filterCountry" class="form-label">Country</label>
                            <input type="text" id="filterCountry" class="form-control form-control-sm"
                                placeholder="Search country..." list="countryList" />
                            <datalist id="countryList">
                                @foreach($countries as $country)
                                    <option value="{{ $country }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label for="filterCity" class="form-label">City</label>
                            <input type="text" id="filterCity" class="form-control form-control-sm"
                                placeholder="Search city..." list="cityList" />
                            <datalist id="cityList">
                                @foreach($cities as $city)
                                    <option value="{{ $city }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label for="filterDeviceType" class="form-label">Device Type</label>
                            <select id="filterDeviceType" class="form-select form-select-sm">
                                <option value="">All Devices</option>
                                <option value="mobile">Mobile</option>
                                <option value="tablet">Tablet</option>
                                <option value="desktop">Desktop</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterLocationSource" class="form-label">Location Source</label>
                            <select id="filterLocationSource" class="form-select form-select-sm">
                                <option value="">All Sources</option>
                                <option value="GPS">GPS</option>
                                <option value="IP">IP</option>
                                <option value="UNAVAILABLE">Unavailable</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterTrackingStatus" class="form-label">Tracking Status</label>
                            <select id="filterTrackingStatus" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="success">Success</option>
                                <option value="error">Error</option>
                                <option value="invalid_tour_code">Invalid Tour Code</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterPageType" class="form-label">Page Type</label>
                            <select id="filterPageType" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <option value="welcome">Welcome</option>
                                <option value="tour_code">Tour Code</option>
                                <option value="analytics">Analytics</option>
                            </select>
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
    <!-- Load Google Maps API -->
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
        // Pass data to JavaScript
        window.qrAnalyticsIndexUrl = '{{ route('admin.qr-analytics.index') }}';
        window.qrAnalyticsShowUrl = '{{ route('admin.qr-analytics.show', ':id') }}';
        
        // Wait for Google Maps API to load before initializing maps
        window.initQRMap = function() {
            // This will be called after Google Maps API is loaded
            if (typeof google !== 'undefined' && google.maps) {
                window.googleMapsLoaded = true;
            }
        };
        
        // Check if Google Maps is already loaded
        if (typeof google !== 'undefined' && google.maps) {
            window.googleMapsLoaded = true;
        }
    </script>
@endsection

