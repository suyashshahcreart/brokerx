{{-- Tour Create Form --}}
<div class="text-center py-5" id="noTourMessage">
    <i class="ri-map-pin-line display-4 text-muted"></i>
    <h5 class="mt-3">No Tour Linked</h5>
    <p class="text-muted">This booking doesn't have a tour associated with it yet.</p>
    <button class="btn btn-primary" id="createTourBtn">
        <i class="ri-add-line me-1"></i> Create New Tour
    </button>
</div>

<!-- Hidden Tour Creation Form -->
<form id="tourCreateForm" class="needs-validation d-none" novalidate>
    @csrf
    <input type="hidden" name="booking_id" value="{{ $booking->id }}">

    <div class="row">
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label" for="new_tour_title">Tour Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="new_tour_title" class="form-control" required>
                <div class="invalid-feedback">Please enter tour title.</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label" for="new_tour_slug">Slug</label>
                <input type="text" name="slug" id="new_tour_slug" class="form-control" placeholder="Auto-generated">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label" for="new_tour_location">Location</label>
                <input type="text" name="location" id="new_tour_location" class="form-control">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label" for="new_tour_status">Status <span class="text-danger">*</span></label>
                <select name="status" id="new_tour_status" class="form-select" required>
                    <option value="draft" selected>Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="new_tour_description">Short Description</label>
        <textarea name="description" id="new_tour_description" class="form-control" rows="3"></textarea>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="mb-3">
                <label class="form-label" for="new_tour_price">Price (₹)</label>
                <input type="number" name="price" id="new_tour_price" class="form-control" step="0.01" min="0">
            </div>
        </div>
        <div class="col-lg-4">
            <div class="mb-3">
                <label class="form-label" for="new_tour_duration_days">Duration (Days)</label>
                <input type="number" name="duration_days" id="new_tour_duration_days" class="form-control" min="1">
            </div>
        </div>
        <div class="col-lg-4">
            <div class="mb-3">
                <label class="form-label" for="new_tour_max_participants">Max Participants</label>
                <input type="number" name="max_participants" id="new_tour_max_participants" class="form-control" min="1">
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">
            <i class="ri-save-line me-1"></i> Create Tour
        </button>
        <button class="btn btn-soft-secondary" type="button" id="cancelCreateTourBtn">
            <i class="ri-close-line me-1"></i> Cancel
        </button>
    </div>
</form>
