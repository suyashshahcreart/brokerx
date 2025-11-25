{{-- Tour Edit Form --}}
<form id="tourEditForm" class="needs-validation" novalidate>
    @csrf
    <input type="hidden" id="tour_id" value="{{ $tour->id }}">
    <input type="hidden" id="tour_booking_id" name="booking_id" value="{{ $booking->id }}">

    <div class="row">
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label" for="tour_title">Tour Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="tour_title" class="form-control" value="{{ $tour->title }}" required>
                <div class="invalid-feedback">Please enter tour title.</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label" for="tour_slug">Slug</label>
                <input type="text" name="slug" id="tour_slug" class="form-control" value="{{ $tour->slug }}" placeholder="Auto-generated">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label" for="tour_location">Location</label>
                <input type="text" name="location" id="tour_location" class="form-control" value="{{ $tour->location }}">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label" for="tour_status">Status <span class="text-danger">*</span></label>
                <select name="status" id="tour_status" class="form-select" required>
                    <option value="draft" @selected($tour->status == 'draft')>Draft</option>
                    <option value="published" @selected($tour->status == 'published')>Published</option>
                    <option value="archived" @selected($tour->status == 'archived')>Archived</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="tour_description">Short Description</label>
        <textarea name="description" id="tour_description" class="form-control" rows="3">{{ $tour->description }}</textarea>
    </div>

    <div class="mb-3">
        <label class="form-label" for="tour_content">Full Content</label>
        <textarea name="content" id="tour_content" class="form-control" rows="5">{{ $tour->content }}</textarea>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <div class="mb-3">
                <label class="form-label" for="tour_price">Price (₹)</label>
                <input type="number" name="price" id="tour_price" class="form-control" value="{{ $tour->price }}" step="0.01" min="0">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="mb-3">
                <label class="form-label" for="tour_duration_days">Duration (Days)</label>
                <input type="number" name="duration_days" id="tour_duration_days" class="form-control" value="{{ $tour->duration_days }}" min="1">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="mb-3">
                <label class="form-label" for="tour_start_date">Start Date</label>
                <input type="date" name="start_date" id="tour_start_date" class="form-control" value="{{ optional($tour->start_date)->format('Y-m-d') }}">
            </div>
        </div>
        <div class="col-lg-3">
            <div class="mb-3">
                <label class="form-label" for="tour_end_date">End Date</label>
                <input type="date" name="end_date" id="tour_end_date" class="form-control" value="{{ optional($tour->end_date)->format('Y-m-d') }}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label" for="tour_max_participants">Max Participants</label>
                <input type="number" name="max_participants" id="tour_max_participants" class="form-control" value="{{ $tour->max_participants }}" min="1">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label" for="tour_featured_image">Featured Image URL</label>
                <input type="text" name="featured_image" id="tour_featured_image" class="form-control" value="{{ $tour->featured_image }}">
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">
            <i class="ri-save-line me-1"></i> Update Tour
        </button>
        <button class="btn btn-soft-danger" type="button" id="unlinkTourBtn">
            <i class="ri-link-unlink me-1"></i> Unlink Tour
        </button>
    </div>
</form>
