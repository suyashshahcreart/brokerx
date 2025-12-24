{{-- Tour Edit Form --}}
<form method="POST" action="{{ route('admin.tours.update', $tour) }}" class="needs-validation" novalidate>
    @csrf
    @method('PUT')
    <input type="hidden" name="booking_id" value="{{ $booking->id }}">

    <!-- Basic Information -->
    <div class="card border-primary border-top mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Basic Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_name">Tour Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="tour_name" class="form-control" value="{{ old('name', $tour->name) }}" required>
                        <div class="invalid-feedback">Please enter tour name.</div>
                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_title">Tour Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="tour_title" class="form-control" value="{{ old('title', $tour->title) }}" required>
                        <div class="invalid-feedback">Please enter tour title.</div>
                        @error('title')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_slug">Slug</label>
                        <input type="text" name="slug" id="tour_slug" class="form-control" value="{{ old('slug', $tour->slug) }}" placeholder="Auto-generated">
                        @error('slug')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_location">Location</label>
                        <select name="location" id="tour_location" class="form-select">
                            <option value="">Select Location</option>
                            <option value="industry" @selected(old('location', $tour->location) == 'industry')>industry (industry.proppik.com)</option>
                            <option value="htl" @selected(old('location', $tour->location) == 'htl')>htl (htl.proppik.com)</option>
                            <option value="re" @selected(old('location', $tour->location) == 're')>re (re.proppik.com)</option>
                            <option value="rs" @selected(old('location', $tour->location) == 'rs')>rs (rs.proppik.com)</option>
                            <option value="tours" @selected(old('location', $tour->location) == 'tours')>tours (tour.proppik.in)</option>
                            <option value="creart_qr" @selected(old('location', $tour->location) == 'creart_qr')>creart_qr (creart.in/qr/)</option>
                        </select>
                        @error('location')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_status">Status <span class="text-danger">*</span></label>
                        <select name="status" id="tour_status" class="form-select" required>
                            <option value="draft" @selected(old('status', $tour->status) == 'draft')>Draft</option>
                            <option value="published" @selected(old('status', $tour->status) == 'published')>Published</option>
                            <option value="archived" @selected(old('status', $tour->status) == 'archived')>Archived</option>
                        </select>
                        @error('status')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_revision">Revision</label>
                        <input type="text" name="revision" id="tour_revision" class="form-control" value="{{ old('revision', $tour->revision) }}" placeholder="v1.0">
                        @error('revision')<div class="text-danger">{{ $message }}</div>@enderror
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

            <div class="d-none row">
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

            <div class="mb-0">
                <label class="form-label" for="tour_final_json">Final JSON Data</label>
                <textarea name="final_json" id="tour_final_json" class="form-control font-monospace" rows="5" placeholder='{"key": "value"}'>{!! is_array($tour->final_json) ? json_encode($tour->final_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $tour->final_json !!}</textarea>
                <small class="text-muted">Enter valid JSON data for tour configuration</small>
            </div>
        </div>
    </div>

    <!-- SEO Meta Tags -->
    <div class="card border-success border-top mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">SEO Meta Tags</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_meta_title">Meta Title</label>
                        <input type="text" name="meta_title" id="tour_meta_title" class="form-control" value="{{ $tour->meta_title }}">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_meta_keywords">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="tour_meta_keywords" class="form-control" value="{{ $tour->meta_keywords }}">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="tour_meta_description">Meta Description</label>
                <textarea name="meta_description" id="tour_meta_description" class="form-control" rows="2">{{ $tour->meta_description }}</textarea>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_canonical_url">Canonical URL</label>
                        <input type="url" name="canonical_url" id="tour_canonical_url" class="form-control" value="{{ $tour->canonical_url }}">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-0">
                        <label class="form-label" for="tour_meta_robots">Meta Robots</label>
                        <input type="text" name="meta_robots" id="tour_meta_robots" class="form-control" value="{{ $tour->meta_robots }}" placeholder="index, follow">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Open Graph / Social Media -->
    <div class="card border-info border-top mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Open Graph / Social Media</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_og_title">OG Title</label>
                        <input type="text" name="og_title" id="tour_og_title" class="form-control" value="{{ $tour->og_title }}">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_og_image">OG Image URL</label>
                        <input type="text" name="og_image" id="tour_og_image" class="form-control" value="{{ $tour->og_image }}">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="tour_og_description">OG Description</label>
                <textarea name="og_description" id="tour_og_description" class="form-control" rows="2">{{ $tour->og_description }}</textarea>
            </div>

            <h6 class="mt-3 mb-2">Twitter Card</h6>
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_twitter_title">Twitter Title</label>
                        <input type="text" name="twitter_title" id="tour_twitter_title" class="form-control" value="{{ $tour->twitter_title }}">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_twitter_image">Twitter Image URL</label>
                        <input type="text" name="twitter_image" id="tour_twitter_image" class="form-control" value="{{ $tour->twitter_image }}">
                    </div>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label" for="tour_twitter_description">Twitter Description</label>
                <textarea name="twitter_description" id="tour_twitter_description" class="form-control" rows="2">{{ $tour->twitter_description }}</textarea>
            </div>
        </div>
    </div>

    <!-- Structured Data -->
    <div class="card border-warning border-top mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Structured Data (JSON-LD)</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_structured_data_type">Structured Data Type</label>
                        <select name="structured_data_type" id="tour_structured_data_type" class="form-select">
                            <option value="">Select type</option>
                            <option value="Article" @selected($tour->structured_data_type == 'Article')>Article</option>
                            <option value="Place" @selected($tour->structured_data_type == 'Place')>Place</option>
                            <option value="Event" @selected($tour->structured_data_type == 'Event')>Event</option>
                            <option value="Product" @selected($tour->structured_data_type == 'Product')>Product</option>
                            <option value="TouristAttraction" @selected($tour->structured_data_type == 'TouristAttraction')>TouristAttraction</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label" for="tour_structured_data">Structured Data JSON</label>
                <textarea name="structured_data" id="tour_structured_data" class="form-control font-monospace" rows="5" placeholder='{"@context": "https://schema.org", "@type": "TouristAttraction"}'>{!! is_array($tour->structured_data) ? json_encode($tour->structured_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $tour->structured_data !!}</textarea>
                <small class="text-muted">Enter valid JSON-LD structured data</small>
            </div>
        </div>
    </div>

    <!-- Custom Code -->
    <div class="card border-danger border-top mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Custom Code Injection</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="tour_header_code">Header Code (before &lt;/head&gt;)</label>
                <textarea name="header_code" id="tour_header_code" class="form-control font-monospace" rows="4">{{ $tour->header_code }}</textarea>
                <small class="text-muted">Custom HTML, CSS, or scripts to inject in the header</small>
            </div>

            <div class="mb-0">
                <label class="form-label" for="tour_footer_code">Footer Code (before &lt;/body&gt;)</label>
                <textarea name="footer_code" id="tour_footer_code" class="form-control font-monospace" rows="4">{{ $tour->footer_code }}</textarea>
                <small class="text-muted">Custom HTML, CSS, or scripts to inject in the footer</small>
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
