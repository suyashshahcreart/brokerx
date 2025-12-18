@extends('admin.layouts.vertical', ['title' => 'Bookings Assigner Calender', 'subTitle' => 'Manage Booking Assigner Schedules'])
@section('css')
    <style>
        #Booking-list {
            padding: 10px;
            width: 100%;
            height: auto;
            overflow: auto;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 4px;
        }

        .booking-box {
            cursor: pointer;
            font-size: 14px;
            margin: 10px 0;
            padding: 8px 10px;
            color: #ffffff;
            border-radius: 4px;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <!-- top navigation and title -->
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Booking</a></li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">{{ $title }}</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    @if (auth()->check() && auth()->user()->hasRole('admin'))
                        <a href="{{ route('admin.booking-assignees.index') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Booking Assignees
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Filter by Photographer: -->
                        <div class="col-12">
                            @if(auth()->check() && auth()->user()->hasRole('admin'))
                                <div class="mt-2 mb-3">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label for="filterPhotographer" class="form-label">Photographer</label>
                                            <select id="filterPhotographer" class="form-select form-select-sm">
                                                <option value="">All Photographers</option>
                                                @foreach($photographers as $photographer)
                                                    <option value="{{ $photographer->id }}">{{ $photographer->firstname }}
                                                        {{ $photographer->lastname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="filterStatus" class="form-label">Status</label>
                                            <select id="filterStatus" class="form-select form-select-sm">
                                                <option value="">All Statuses</option>
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <div class="w-100">
                                                <button id="btnClearFilters" type="button"
                                                    class="btn btn-sm btn-outline-secondary w-50">Clear filters</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Assigne Booking list only for admin -->
                        @if (auth()->check() && auth()->user()->hasRole('admin'))
                            <div class="col-xl-3">
                                <div class="text-start">
                                    <h3 class="mb-0">Booking Assignment List</h3>
                                    <p>Assigne Booking to a Photographer Directly from List</p>
                                </div>
                                <div id="Booking-list">
                                    <p class="text-muted">Select and Booking to assigne Photographer</p>
                                    <div class="external-event bg-soft-primary text-primary" data-class="bg-primary">
                                        <i class="ri-circle-fill me-2 vertical-middle"></i>Team Building Retreat Meeting
                                    </div>
                                    <div class="external-event bg-soft-info text-info" data-class="bg-info">
                                        <i class="ri-circle-fill me-2 vertical-middle"></i>Product Launch Strategy Meeting
                                    </div>
                                    <div class="external-event bg-soft-success text-success" data-class="bg-success">
                                        <i class="ri-circle-fill me-2 vertical-middle"></i>Monthly Sales Review
                                    </div>
                                    <div class="external-event bg-soft-danger text-danger" data-class="bg-danger">
                                        <i class="ri-circle-fill me-2 vertical-middle"></i>Team Lunch Celebration
                                    </div>
                                    <div class="external-event bg-soft-warning text-warning" data-class="bg-warning">
                                        <i class="ri-circle-fill me-2 vertical-middle"></i>Marketing Campaign Kickoff
                                    </div>
                                </div>
                            </div> <!-- end col-->
                            <div class="col-xl-9">
                                <!-- calendar for Admin -->
                                <div class="mt-4 mt-lg-0">
                                    <div id="calendar" data-booking-api="{{ route('api.bookings.by-date-range') }}"
                                        data-check-in-route="{{ url('admin/booking-assignees') }}/:id/check-in"
                                        data-check-out-route="{{ url('admin/booking-assignees') }}/:id/check-out"
                                        data-booking-show-route="{{ url('admin/bookings') }}/:id"></div>
                                </div>
                            </div> <!-- end col -->
                        @endif
                        <!-- calender for Photographer -->
                        <div class="col-xl-12">
                            <div class="mt-4 mt-lg-0">
                                <div id="calendar" data-booking-api="{{ route('api.bookings.by-date-range') }}"
                                    data-check-in-route="{{ url('admin/booking-assignees') }}/:id/check-in"
                                    data-check-out-route="{{ url('admin/booking-assignees') }}/:id/check-out"
                                    data-booking-show-route="{{ url('admin/bookings') }}/:id"></div>
                            </div>
                        </div> <!-- end col -->
                    </div> <!-- end row -->
                </div> <!-- end card body-->
            </div> <!-- end card -->

            <!-- Check-in and Check-Out MODAL -->
            <div class="modal fade" id="event-modal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form class="needs-validation" name="event-form" id="forms-event" novalidate>
                            <div class="modal-header p-3 border-bottom-0">
                                <h5 class="modal-title" id="modal-title">Booking Assigne - Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-3 pb-3 pt-0">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="alert alert-info mb-0">
                                            <strong>Selected Booking Details</strong>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <table class="table table-sm mb-0">
                                            <tbody>
                                                <tr>
                                                    <th style="width:35%">Booking ID</th>
                                                    <td id="modal-booking-id">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Customer</th>
                                                    <td id="modal-booking-customer">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Property</th>
                                                    <td id="modal-booking-property">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Address</th>
                                                    <td id="modal-booking-address">—</td>
                                                </tr>
                                                <tr>
                                                    <th>City / State</th>
                                                    <td id="modal-booking-city-state">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Pincode</th>
                                                    <td id="modal-booking-pincode">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Scheduled Date</th>
                                                    <td id="modal-schedule-date">—</td>
                                                </tr>
                                                <tr>
                                                    <th>Scheduled Time</th>
                                                    <td id="modal-schedule-time">—</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-12 mt-2" id="modal-action-buttons">
                                        <div class="d-flex flex-wrap gap-2" id="modal-buttons-container">
                                            <a id="modal-check-in-link" href="#" class="btn btn-primary"
                                                style="display: none;">
                                                <i class="ri-box-arrow-in-right-line me-1"></i> Go to Check-In
                                            </a>
                                            <a id="modal-check-out-link" href="#" class="btn btn-warning"
                                                style="display: none;">
                                                <i class="ri-box-arrow-out-left-line me-1"></i> Go to Check-Out
                                            </a>
                                            <a id="modal-view-booking-link" href="#" class="btn btn-info"
                                                style="display: none;">
                                                <i class="ri-eye-line me-1"></i> View Booking Details
                                            </a>
                                            <a id="modal-completed-link" href="#" class="btn btn-success"
                                                style="display: none;" disabled>
                                                <i class="ri-checkbox-circle-line me-1"></i> Completed
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div> <!-- end modal-content-->
                </div> <!-- end modal dialog-->
            </div> <!-- end modal-->
        </div> <!-- end col -->
    </div> <!-- end row -->

@endsection

@section('scripts')
    @vite(['resources/js/pages/photographer-index.js',])
@endsection