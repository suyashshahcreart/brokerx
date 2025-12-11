@extends('admin.layouts.vertical', ['title' => 'Edit Photographer Visit', 'subTitle' => 'Management'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-0">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('root') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.photographer-visits.index') }}">Photographer Visits</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit #{{ $photographerVisit->id }}</li>
                    </ol>
                </nav>
                <h3 class="mb-0">Edit Photographer Visit #{{ $photographerVisit->id }}</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <x-admin.back-button :fallback="route('admin.photographer-visits.index')" :classes="['btn', 'btn-soft-secondary']" :merge="false" icon="ri-arrow-go-back-line" />
            </div>
        </div>

        <div class="card panel-card border-primary border-top" data-panel-card>
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-1">Visit Details</h4>
                    <p class="text-muted mb-0">Update photographer visit information</p>
                </div>
                <div class="panel-actions d-flex gap-2">
                    <button type="button" class="btn btn-light border" data-panel-action="collapse" title="Collapse">
                        <i class="ri-arrow-up-s-line"></i>
                    </button>
                    <button type="button" class="btn btn-light border" data-panel-action="fullscreen" title="Fullscreen">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.photographer-visits.update', $photographerVisit) }}" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="booking_id">Booking <span class="text-danger">*</span></label>
                                <select name="booking_id" id="booking_id" class="form-select @error('booking_id') is-invalid @enderror" required>
                                    <option value="">Select booking</option>
                                    @foreach($bookings as $booking)
                                        <option value="{{ $booking->id }}" @selected(old('booking_id', $photographerVisit->booking_id)==$booking->id)>
                                            #{{ $booking->id }} - {{ $booking->society_name ?? $booking->address_area }} ({{ $booking->city?->name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('booking_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Please select a booking.</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="photographer_id">Photographer <span class="text-danger">*</span></label>
                                <select name="photographer_id" id="photographer_id" class="form-select @error('photographer_id') is-invalid @enderror" required>
                                    <option value="">Select photographer</option>
                                    @foreach($photographers as $photographer)
                                        <option value="{{ $photographer->id }}" @selected(old('photographer_id', $photographerVisit->photographer_id)==$photographer->id)>
                                            {{ $photographer->firstname }} {{ $photographer->lastname }} ({{ $photographer->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('photographer_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Please select a photographer.</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="visit_date">Visit Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="visit_date" id="visit_date" 
                                    class="form-control @error('visit_date') is-invalid @enderror" 
                                    value="{{ old('visit_date', $photographerVisit->visit_date?->format('Y-m-d\TH:i')) }}" required>
                                @error('visit_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Please select visit date and time.</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="pending" @selected(old('status', $photographerVisit->status)=='pending')>Pending</option>
                                    <option value="checked_in" @selected(old('status', $photographerVisit->status)=='checked_in')>Checked In</option>
                                    <option value="checked_out" @selected(old('status', $photographerVisit->status)=='checked_out')>Checked Out</option>
                                    <option value="completed" @selected(old('status', $photographerVisit->status)=='completed')>Completed</option>
                                    <option value="cancelled" @selected(old('status', $photographerVisit->status)=='cancelled')>Cancelled</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Please select status.</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label" for="notes">Notes</label>
                                <textarea name="notes" id="notes" rows="4" 
                                    class="form-control @error('notes') is-invalid @enderror" 
                                    placeholder="Enter any notes or special instructions...">{{ old('notes', $photographerVisit->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.photographer-visits.show', $photographerVisit) }}" class="btn btn-soft-secondary">
                            <i class="ri-close-line me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Update Visit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for better dropdown experience
        if (typeof $.fn.select2 !== 'undefined') {
            $('#booking_id, #photographer_id').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }

        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    });
</script>
@endpush
