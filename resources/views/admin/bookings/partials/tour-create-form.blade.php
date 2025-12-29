{{-- Tour Create Form --}}
<div class="text-center py-5" id="noTourMessage">
    <i class="ri-map-pin-line display-4 text-muted"></i>
    <h5 class="mt-3">No Tour Linked</h5>
    <p class="text-muted">This booking doesn't have a tour associated with it yet.</p>
    <button class="btn btn-primary" id="createTourBtn">
        <i class="ri-add-line me-1"></i> Create New Tour
    </button>
</div>

<!-- Tour Creation Form -->
<form method="POST" action="{{ route('admin.tours.store') }}" class="needs-validation d-none" id="tourCreateForm" novalidate>
    @csrf
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
                        <label class="form-label" for="new_tour_name">Tour Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="new_tour_name" class="form-control" value="{{ old('name') }}" required>
                        <div class="invalid-feedback">Please enter tour name.</div>
                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_title">Tour Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="new_tour_title" class="form-control" value="{{ old('title') }}" required>
                        <div class="invalid-feedback">Please enter tour title.</div>
                        @error('title')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_slug">Slug</label>
                        <input type="text" name="slug" id="new_tour_slug" class="form-control" value="{{ old('slug') }}" placeholder="Auto-generated">
                        @error('slug')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_location">Location</label>
                        <select name="location" id="new_tour_location" class="form-select">
                            <option value="">Select Location</option>
                            @php
                                $ftpConfigs = \App\Models\FtpConfiguration::active()->ordered()->get();
                            @endphp
                            @foreach($ftpConfigs as $ftpConfig)
                                <option value="{{ $ftpConfig->category_name }}" 
                                    @selected(old('location') == $ftpConfig->category_name)>
                                    {{ $ftpConfig->display_name }} ({{ $ftpConfig->main_url }})
                                </option>
                            @endforeach
                        </select>
                        @error('location')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_status">Status <span class="text-danger">*</span></label>
                        <select name="status" id="new_tour_status" class="form-select" required>
                            <option value="draft" @selected(old('status', 'draft') == 'draft')>Draft</option>
                            <option value="published" @selected(old('status') == 'published')>Published</option>
                            <option value="archived" @selected(old('status') == 'archived')>Archived</option>
                        </select>
                        @error('status')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_revision">Revision</label>
                        <input type="text" name="revision" id="new_tour_revision" class="form-control" value="{{ old('revision') }}" placeholder="v1.0">
                        @error('revision')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="new_tour_description">Short Description</label>
                <textarea name="description" id="new_tour_description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label" for="new_tour_content">Full Content</label>
                <textarea name="content" id="new_tour_content" class="form-control" rows="5"></textarea>
            </div>

            <div class="d-none row">
                <div class="col-lg-3">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_price">Price (₹)</label>
                        <input type="number" name="price" id="new_tour_price" class="form-control" step="0.01" min="0">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_duration_days">Duration (Days)</label>
                        <input type="number" name="duration_days" id="new_tour_duration_days" class="form-control" min="1">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_start_date">Start Date</label>
                        <input type="date" name="start_date" id="new_tour_start_date" class="form-control">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_end_date">End Date</label>
                        <input type="date" name="end_date" id="new_tour_end_date" class="form-control">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_max_participants">Max Participants</label>
                        <input type="number" name="max_participants" id="new_tour_max_participants" class="form-control" min="1">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_featured_image">Featured Image URL</label>
                        <input type="text" name="featured_image" id="new_tour_featured_image" class="form-control">
                    </div>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label" for="new_tour_final_json">Final JSON Data</label>
                <textarea name="final_json" id="new_tour_final_json" class="form-control font-monospace" rows="5" placeholder='{"key": "value"}'></textarea>
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
                        <label class="form-label" for="new_tour_meta_title">Meta Title</label>
                        <input type="text" name="meta_title" id="new_tour_meta_title" class="form-control">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_meta_keywords">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="new_tour_meta_keywords" class="form-control">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="new_tour_meta_description">Meta Description</label>
                <textarea name="meta_description" id="new_tour_meta_description" class="form-control" rows="2"></textarea>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_canonical_url">Canonical URL</label>
                        <input type="url" name="canonical_url" id="new_tour_canonical_url" class="form-control">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-0">
                        <label class="form-label" for="new_tour_meta_robots">Meta Robots</label>
                        <input type="text" name="meta_robots" id="new_tour_meta_robots" class="form-control" placeholder="index, follow">
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
                        <label class="form-label" for="new_tour_og_title">OG Title</label>
                        <input type="text" name="og_title" id="new_tour_og_title" class="form-control">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_og_image">OG Image URL</label>
                        <input type="text" name="og_image" id="new_tour_og_image" class="form-control">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="new_tour_og_description">OG Description</label>
                <textarea name="og_description" id="new_tour_og_description" class="form-control" rows="2"></textarea>
            </div>

            <h6 class="mt-3 mb-2">Twitter Card</h6>
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_twitter_title">Twitter Title</label>
                        <input type="text" name="twitter_title" id="new_tour_twitter_title" class="form-control">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="new_tour_twitter_image">Twitter Image URL</label>
                        <input type="text" name="twitter_image" id="new_tour_twitter_image" class="form-control">
                    </div>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label" for="new_tour_twitter_description">Twitter Description</label>
                <textarea name="twitter_description" id="new_tour_twitter_description" class="form-control" rows="2"></textarea>
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
                        <label class="form-label" for="new_tour_structured_data_type">Structured Data Type</label>
                        <select name="structured_data_type" id="new_tour_structured_data_type" class="form-select">
                            <option value="">Select type</option>
                            <option value="Article">Article</option>
                            <option value="Place">Place</option>
                            <option value="Event">Event</option>
                            <option value="Product">Product</option>
                            <option value="TouristAttraction">TouristAttraction</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label" for="new_tour_structured_data">Structured Data JSON</label>
                <textarea name="structured_data" id="new_tour_structured_data" class="form-control font-monospace" rows="5" placeholder='{"@context": "https://schema.org", "@type": "TouristAttraction"}'></textarea>
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
                <label class="form-label" for="new_tour_header_code">Header Code (before &lt;/head&gt;)</label>
                <textarea name="header_code" id="new_tour_header_code" class="form-control font-monospace" rows="4"></textarea>
                <small class="text-muted">Custom HTML, CSS, or scripts to inject in the header</small>
            </div>

            <div class="mb-0">
                <label class="form-label" for="new_tour_footer_code">Footer Code (before &lt;/body&gt;)</label>
                <textarea name="footer_code" id="new_tour_footer_code" class="form-control font-monospace" rows="4"></textarea>
                <small class="text-muted">Custom HTML, CSS, or scripts to inject in the footer</small>
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
