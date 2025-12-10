@extends('admin.layouts.vertical', ['title' => 'Booking Assignees', 'subTitle' => 'Property'])

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Property</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Booking Assignees</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Booking Assignees</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    <a href="{{ route('admin.booking-assignees.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Assign Booking
                    </a>
                </div>
            </div>

            <div data-alert-container>
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> Please check the form for errors.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>

            <div class="card panel-card border-primary border-top" data-panel-card>
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">All Booking Assignments</h5>
                    <div>
                        <input type="text" id="tableSearch" class="form-control form-control-sm" placeholder="Search assignments..." style="width: 250px;">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="bookingAssigneesTable" class="table table-hover mb-0 table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Booking</th>
                                    <th>Assigned User</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
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

@section('scripts')
    @vite(['resources/js/pages/booking-assignees-index.js'])
@endsection


