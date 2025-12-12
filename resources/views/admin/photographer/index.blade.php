@extends('admin.layouts.vertical')

@section('css')
@vite(['node_modules/fullcalendar/main.min.css'])
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="mb-0 fw-semibold">{{ $title }}</h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Photographer</a></li>
                <li class="breadcrumb-item active">{{ $title }}</li>
            </ol>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-3">
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary" id="btn-new-event">
                                <i class="ri-add-line fs-18 me-2"></i> Add New Schedule
                            </button>
                        </div>
                        <div id="external-events">
                            <br>
                            <p class="text-muted">Drag and drop your event or click in the calendar</p>
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
                        <div class="mt-4 mt-lg-0">
                            <div id="calendar" data-booking-api="{{ route('api.bookings.by-date-range') }}"></div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->
            </div> <!-- end card body-->
        </div> <!-- end card -->

        <!-- Add New Event MODAL -->
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
                                <div class="col-12 d-flex justify-content-between align-items-center mt-2">
                                    <a id="modal-check-in-link" href="#" class="btn btn-primary">
                                        <i class="ri-box-arrow-in-right-line me-1"></i> Go to Check-In
                                    </a>
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

@section('script-bottom')
@vite(['resources/js/pages/photographer-index.js'])
@endsection