@extends('admin.layouts.vertical', ['title' => 'Edit Booking Assignment', 'subTitle' => 'Property'])

@section('css')
    @vite(['node_modules/choices.js/public/assets/styles/choices.min.css'])
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.booking-assignees.index') }}">Booking Assignees</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Assignment</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Edit Booking Assignment</h3>
                </div>
                <div>
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Validation Error!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card panel-card border-primary border-top">
                <div class="card-header">
                    <h5 class="card-title mb-0">Assignment Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.booking-assignees.update', $bookingAssignee->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="booking_id" class="form-label">Select Booking <span class="text-danger">*</span></label>
                            <select class="form-select @error('booking_id') is-invalid @enderror" id="booking_id"
                                name="booking_id" required>
                                <option value="">-- Choose a Booking --</option>
                                @foreach($bookings as $booking)
                                    <option value="{{ $booking->id }}" @selected($bookingAssignee->booking_id == $booking->id)>
                                        #{{ $booking->id }} - {{ $booking->property_name ?? 'Property' }} ({{ $booking->created_at->format('d M Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('booking_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="user_id" class="form-label">Select User <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id"
                                name="user_id" required>
                                <option value="">-- Choose a User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected($bookingAssignee->user_id == $user->id)>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Assignment Date (Optional)</label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror" id="date"
                                        name="date" value="{{ $bookingAssignee->date ? $bookingAssignee->date->format('Y-m-d') : '' }}">
                                    @error('date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="time" class="form-label">Assignment Time (Optional)</label>
                                    <input type="time" class="form-control @error('time') is-invalid @enderror" id="time"
                                        name="time" value="{{ $bookingAssignee->time ? $bookingAssignee->time->format('H:i') : '' }}">
                                    @error('time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="mb-2"><i class="ri-information-line"></i> Additional Info</h6>
                            <p class="mb-1"><strong>Created:</strong> {{ $bookingAssignee->created_at->format('d M Y H:i') }}</p>
                            <p class="mb-1"><strong>Last Updated:</strong> {{ $bookingAssignee->updated_at->format('d M Y H:i') }}</p>
                            @if($bookingAssignee->createdBy)
                                <p class="mb-0"><strong>Created By:</strong> {{ $bookingAssignee->createdBy->name }}</p>
                            @endif
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('admin.booking-assignees.index') }}" class="btn btn-secondary">
                                <i class="ri-close-line me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-check-line me-1"></i> Update Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @vite(['node_modules/choices.js/public/assets/scripts/choices.min.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bookingSelect = new Choices('#booking_id', {
                searchEnabled: true,
                itemSelectText: 'Press to select',
                shouldSort: false,
                placeholder: true,
            });

            const userSelect = new Choices('#user_id', {
                searchEnabled: true,
                itemSelectText: 'Press to select',
                shouldSort: false,
                placeholder: true,
            });
        });
    </script>
@endsection
