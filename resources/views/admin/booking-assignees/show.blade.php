@extends('admin.layouts.vertical', ['title' => 'View Booking Assignment', 'subTitle' => 'Property'])

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.booking-assignees.index') }}">Booking Assignees</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Assignment Details</li>
                        </ol>
                    </nav>
                    <h3 class="mb-0">Assignment Details</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <x-admin.back-button :classes="['btn', 'btn-soft-secondary']" :merge="false"
                        icon="ri-arrow-go-back-line" />
                    <a href="{{ route('admin.booking-assignees.edit', $bookingAssignee->id) }}" class="btn btn-primary">
                        <i class="ri-edit-line me-1"></i> Edit
                    </a>
                </div>
            </div>

            <div class="card panel-card border-primary border-top">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <span class="badge bg-primary me-2">Assignment #{{ $bookingAssignee->id }}</span>
                        Assignment Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Booking Information</label>
                                @if($bookingAssignee->booking)
                                    <div class="d-flex align-items-center gap-2">
                                        <div>
                                            <h6 class="mb-0">
                                                <a href="{{ route('admin.bookings.show', $bookingAssignee->booking->id) }}"
                                                    class="text-decoration-none">
                                                    #{{ $bookingAssignee->booking->id }}
                                                </a>
                                            </h6>
                                            <small class="text-muted">{{ $bookingAssignee->booking->property_name ?? 'Property' }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-danger">Booking Deleted</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Assigned User</label>
                                @if($bookingAssignee->user)
                                    <div class="d-flex align-items-center gap-2">
                                        @if($bookingAssignee->user->profile_photo_path)
                                            <img src="{{ Storage::url($bookingAssignee->user->profile_photo_path) }}"
                                                alt="{{ $bookingAssignee->user->name }}"
                                                class="avatar-md rounded-circle">
                                        @else
                                            <div class="avatar-md rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                                style="width: 48px; height: 48px;">
                                                <span class="text-white fw-bold">{{ substr($bookingAssignee->user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $bookingAssignee->user->name }}</h6>
                                            <small class="text-muted">{{ $bookingAssignee->user->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-danger">User Deleted</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Assignment Date</label>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ri-calendar-line text-primary"></i>
                                    @if($bookingAssignee->date)
                                        <strong>{{ $bookingAssignee->date->format('d M Y') }}</strong>
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Assignment Time</label>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ri-time-line text-primary"></i>
                                    @if($bookingAssignee->time)
                                        <strong>{{ $bookingAssignee->time->format('H:i') }}</strong>
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Created</label>
                                <div>
                                    <small class="d-block">{{ $bookingAssignee->created_at->format('d M Y H:i:s') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Created By</label>
                                <div>
                                    @if($bookingAssignee->createdBy)
                                        <small class="d-block">{{ $bookingAssignee->createdBy->name }}</small>
                                    @else
                                        <small class="d-block text-muted">-</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Last Updated</label>
                                <div>
                                    <small class="d-block">{{ $bookingAssignee->updated_at->format('d M Y H:i:s') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Updated By</label>
                                <div>
                                    @if($bookingAssignee->updatedBy)
                                        <small class="d-block">{{ $bookingAssignee->updatedBy->name }}</small>
                                    @else
                                        <small class="d-block text-muted">-</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($bookingAssignee->deleted_at)
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Deleted</label>
                                    <div>
                                        <small class="d-block text-danger">{{ $bookingAssignee->deleted_at->format('d M Y H:i:s') }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($bookingAssignee->deletedBy)
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Deleted By</label>
                                    <div>
                                        <small class="d-block">{{ $bookingAssignee->deletedBy->name }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-footer d-flex gap-2 justify-content-end">
                    <a href="{{ route('admin.booking-assignees.index') }}" class="btn btn-secondary">
                        <i class="ri-arrow-go-back-line me-1"></i> Back
                    </a>
                    <a href="{{ route('admin.booking-assignees.edit', $bookingAssignee->id) }}" class="btn btn-primary">
                        <i class="ri-edit-line me-1"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('admin.booking-assignees.destroy', $bookingAssignee->id) }}"
                        style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="ri-delete-bin-line me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
