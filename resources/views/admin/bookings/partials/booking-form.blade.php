{{-- Booking Details Form (moved from edit.blade.php) --}}
{{-- Display Validation Errors --}}
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h5 class="alert-heading"><i class="ri-error-warning-line me-2"></i>Validation Errors</h5>
    <hr>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Display Success Message --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="needs-validation" novalidate>
    @csrf
    @method('PUT')
    
    @include('admin.bookings.partials.ajax-form-fields')

    <!-- Submit Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-soft-secondary"><i class="ri-close-line me-1"></i> Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="ri-save-line me-1"></i> Update Booking</button>
            </div>
        </div>
    </div>
</form>
