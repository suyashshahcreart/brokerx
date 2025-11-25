@extends('admin.layouts.vertical', ['title' => 'Edit Booking', 'subTitle' => 'Property'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit #{{ $booking->id }}</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Edit Booking #{{ $booking->id }}</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.bookings.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="bookingEditTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="booking-tab" data-bs-toggle="tab" data-bs-target="#booking-pane" type="button" role="tab" aria-controls="booking-pane" aria-selected="true">
                            <i class="ri-file-list-3-line me-1"></i> Booking Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tour-tab" data-bs-toggle="tab" data-bs-target="#tour-pane" type="button" role="tab" aria-controls="tour-pane" aria-selected="false">
                            <i class="ri-map-pin-line me-1"></i> Tour Details
                            @if($tour ?? null)
                                <span class="badge bg-success ms-1">Linked</span>
                            @else
                                <span class="badge bg-warning ms-1">Not Linked</span>
                            @endif
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="bookingEditTabsContent">
                    <!-- Booking Tab -->
                    <div class="tab-pane fade show active" id="booking-pane" role="tabpanel" aria-labelledby="booking-tab" tabindex="0">
                        <form id="bookingEditForm" class="needs-validation" novalidate>
                            @csrf
                            <input type="hidden" id="booking_id" value="{{ $booking->id }}">
                            
                            @include('admin.bookings.partials.ajax-form-fields')

                            <div class="d-flex gap-2 mt-4">
                                <button class="btn btn-primary" type="submit">
                                    <i class="ri-save-line me-1"></i> Update Booking
                                </button>
                                <a href="{{ route('admin.bookings.index') }}" class="btn btn-soft-secondary">
                                    <i class="ri-close-line me-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Tour Tab -->
                    <div class="tab-pane fade" id="tour-pane" role="tabpanel" aria-labelledby="tour-tab" tabindex="0">
                        @if($tour ?? null)
                            @include('admin.bookings.partials.tour-edit-form')
                        @else
                            @include('admin.bookings.partials.tour-create-form')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/pages/bookings-edit.js'])
    <script>
        // Pass data to JavaScript
        window.bookingData = {
            id: {{ $booking->id }},
            @if($tour ?? null)
            tourId: {{ $tour->id }},
            hasTour: true
            @else
            tourId: null,
            hasTour: false
            @endif
        };
    </script>
@endpush
