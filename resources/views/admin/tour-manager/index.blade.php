@extends('admin.layouts.vertical', ['title' => 'Tour Manager'])

@section('css')
    <style>
        #filtersSection .select2-container { width: 100% !important; }
        #filtersSection .select2-container--bootstrap-5 .select2-selection--single {
            min-height: 32px; height: 32px; display: flex; align-items: center;
            font-size: 0.8125rem; border-color: var(--bs-border-color);
        }
        #filtersSection .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 1.25; padding-left: 0.65rem; padding-right: 2.75rem; color: var(--bs-body-color);
        }
        #filtersSection .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
            position: absolute; right: 1.85rem; font-size: 1.05rem; line-height: 1;
            color: var(--bs-secondary-color); cursor: pointer; margin-right: 0; padding: 0 0.1rem;
        }
        #filtersSection .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: var(--bs-secondary-color) !important;
        }
        #filtersSection .select2-container--bootstrap-5.select2-container--disabled .select2-selection {
            background-color: var(--bs-secondary-bg, #e9ecef); cursor: not-allowed;
        }
        #filtersSection .select2-container--bootstrap-5 .select2-selection--multiple {
            min-height: 32px; padding: 0.1rem 0.35rem; font-size: 0.8125rem; border-color: var(--bs-border-color);
        }
        #filtersSection .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
            padding: 0; display: flex; flex-wrap: wrap; gap: 0.15rem;
        }
        #filtersSection .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            font-size: 0.7rem; padding: 0.05rem 0.35rem; margin: 0; line-height: 1.35;
        }
        .booking-filters-panel {
            position: relative; background: var(--bs-light-bg-subtle, #f8f9fa);
            border: 1px solid var(--bs-border-color); border-radius: 0.375rem;
            overflow: visible; padding: 0.35rem 0.75rem 0.625rem;
        }
        .booking-filters-grid {
            display: grid; gap: 0.5rem 0.65rem; grid-template-columns: repeat(5, minmax(0, 1fr));
        }
        .booking-filter-clear {
            position: absolute; top: 0; right: 0.5rem; z-index: 2; line-height: 1;
        }
        .booking-filter-clear .btn {
            padding: 0.1rem 0.35rem; font-size: 0.6875rem; height: 22px; min-width: auto;
            line-height: 1; display: inline-flex; align-items: center; gap: 0.2rem;
        }
        @media (max-width: 1199.98px) { .booking-filters-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 767.98px) { .booking-filters-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 575.98px) { .booking-filters-grid { grid-template-columns: 1fr; } }
        .booking-filter-item { min-width: 0; }
        #filtersSection .form-label {
            font-size: 0.75rem; font-weight: 500; margin-bottom: 0.25rem;
            color: var(--bs-secondary-color); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        #filterDateRange { background-color: var(--bs-body-bg, #fff); height: 32px; min-height: 32px; }
        .booking-filter-field .input-group-sm > .form-control,
        .booking-filter-field .input-group-sm > .input-group-text {
            height: 32px; min-height: 32px;
        }
        .booking-filter-field .input-group-text {
            background: var(--bs-primary); color: #fff; border-color: var(--bs-primary);
        }
        .booking-filters-panel > .booking-filters-grid + .booking-filters-grid { margin-top: 0.5rem; }
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
                            <li class="breadcrumb-item active" aria-current="page">Tour Manager</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Tour Management</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
                    @can('booking_create')
                        <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> New Booking
                        </a>
                    @endcan
                </div>
            </div>

            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="card-title mb-1">Tours List</h4>
                        <p class="text-muted mb-0">Manage property tours</p>
                    </div>
                    <div class="panel-actions d-flex gap-2">
                        <button type="button" class="btn btn-light border" data-panel-action="refresh" title="Refresh">
                            <i class="ri-refresh-line"></i>
                        </button>
                        <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                            <i class="ri-arrow-up-s-line"></i>
                        </button>
                        <button type="button" class="btn btn-light border" data-panel-action="fullscreen" title="Fullscreen">
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
                                <i class="ri-filter-off-line"></i><span>Clear</span>
                            </button>
                        </div>

                        <div class="booking-filters-grid">
                            <div class="booking-filter-item">
                                <label for="filterCustomer" class="form-label">Customer</label>
                                <select id="filterCustomer" class="form-select form-select-sm booking-filter-select" data-theme="bootstrap-5" data-placeholder="All Customers">
                                    <option value="">All Customers</option>
                                </select>
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterStatus" class="form-label">Booking Status</label>
                                <select id="filterStatus" class="form-select form-select-sm booking-filter-select" data-theme="bootstrap-5" data-placeholder="All Status" multiple>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="completed">Completed</option>
                                    <option value="schedul_pending">Schedul Pending</option>
                                    <option value="schedul_accepted">Schedul Accepted</option>
                                    <option value="reschedul_accepted">Reschedul Accepted</option>
                                    <option value="schedul_completed">Schedul Completed</option>
                                    <option value="tour_pending">Tour Pending</option>
                                    <option value="tour_live">Tour Live</option>
                                </select>
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterCountry" class="form-label">Country</label>
                                <select id="filterCountry" class="form-select form-select-sm booking-filter-select" data-theme="bootstrap-5" data-placeholder="All Countries">
                                    <option value="">All Countries</option>
                                    @foreach ($countries ?? [] as $country)
                                        <option value="{{ $country->id }}" @selected(($defaultCountryId ?? null) == $country->id)>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterState" class="form-label">State</label>
                                <select id="filterState" class="form-select form-select-sm booking-filter-select" data-theme="bootstrap-5" data-placeholder="All States">
                                    <option value="">All States</option>
                                    @foreach ($states ?? [] as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterCity" class="form-label">City</label>
                                <select id="filterCity" class="form-select form-select-sm booking-filter-select" data-theme="bootstrap-5" data-placeholder="All Cities" disabled>
                                    <option value="">All Cities</option>
                                </select>
                            </div>
                        </div>

                        <div class="booking-filters-grid">
                            <div class="booking-filter-item">
                                <label for="filterPropertyType" class="form-label">Property Type</label>
                                <select id="filterPropertyType" class="form-select form-select-sm booking-filter-select" data-theme="bootstrap-5" data-placeholder="All Property Types">
                                    <option value="">All Property Types</option>
                                    @foreach ($propertyTypes ?? [] as $propertyType)
                                        <option value="{{ $propertyType->id }}">{{ $propertyType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="booking-filter-item">
                                <label for="filterPropertySubType" class="form-label">Property Sub Type</label>
                                <select id="filterPropertySubType" class="form-select form-select-sm booking-filter-select" data-theme="bootstrap-5" data-placeholder="All Property Sub Types" disabled>
                                    <option value="">All Property Sub Types</option>
                                </select>
                            </div>
                            <div class="booking-filter-item d-none" id="filterFurnishWrap">
                                <label for="filterFurnish" class="form-label">Furnish Type</label>
                                <select id="filterFurnish" class="form-select form-select-sm booking-filter-select" data-theme="bootstrap-5" data-placeholder="All Furnish Types" disabled>
                                    <option value="">All Furnish Types</option>
                                    <option value="Furnished">Fully Furnished</option>
                                    <option value="Semi-Furnished">Semi Furnished</option>
                                    <option value="Unfurnished">Unfurnished</option>
                                </select>
                            </div>
                            <div class="booking-filter-item d-none" id="filterBhkWrap">
                                <label for="filterBhk" class="form-label">Size (BHK / RK)</label>
                                <select id="filterBhk" class="form-select form-select-sm booking-filter-select" data-theme="bootstrap-5" data-placeholder="All Sizes" disabled>
                                    <option value="">All Sizes</option>
                                </select>
                            </div>
                            <div class="booking-filter-item booking-filter-field">
                                <label for="filterDateRange" class="form-label">Date Range</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                    <input type="text" id="filterDateRange" class="form-control form-control-sm" placeholder="Select date range" />
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tour-manager-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Booking</th>
                                    <th>Customer</th>
                                    <th>Location</th>
                                    <th>City / State</th>
                                    <th>QR Code</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th>Price</th>
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

    <div class="modal fade" id="scheduleTourModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="schedule-tour-form">
                    @csrf
                    <input type="hidden" id="booking-id" name="booking_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Schedule Tour</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tour-date" class="form-label">Tour Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="tour-date" name="tour_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="tour-notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="tour-notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule Tour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        window.appBaseUrl = '{{ url("/") }}';
        window.adminBasePath = 'ppadmlog';
        window.tourManagerIndexUrl = '{{ route("admin.tour-manager.index") }}';
        window.citiesOptionsUrl = '{{ route("admin.api.cities.options") }}';
        window.statesOptionsUrl = '{{ route("admin.api.states.options") }}';
        window.customersOptionsUrl = '{{ route("admin.api.customers.options") }}';
        window.propertySubTypesOptionsUrl = '{{ route("admin.api.property-sub-types.options") }}';
        window.bhkOptionsUrl = '{{ route("admin.api.bhk.options") }}';
        window.defaultCountryId = @json($defaultCountryId ?? null);
        window.propertyTypeMeta = @json($propertyTypeMeta ?? []);
    </script>
@endsection

@section('script-bottom')
    @vite(['resources/js/pages/tour-manager.js'])
@endsection
