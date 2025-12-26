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
                        <input type="text" name="name" id="tour_name" class="form-control"
                            value="{{ old('name', $tour->name) }}" required>
                        <div class="invalid-feedback">Please enter tour name.</div>
                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_title">Tour Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="tour_title" class="form-control"
                            value="{{ old('title', $tour->title) }}" required>
                        <div class="invalid-feedback">Please enter tour title.</div>
                        @error('title')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_slug">Slug</label>
                        <input type="text" name="slug" id="tour_slug" class="form-control"
                            value="{{ old('slug', $tour->slug) }}" placeholder="Auto-generated">
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
                            <option value="industry" @selected(old('location', $tour->location) == 'industry')>industry
                                (industry.proppik.com)</option>
                            <option value="htl" @selected(old('location', $tour->location) == 'htl')>htl (htl.proppik.com)
                            </option>
                            <option value="re" @selected(old('location', $tour->location) == 're')>re (re.proppik.com)
                            </option>
                            <option value="rs" @selected(old('location', $tour->location) == 'rs')>rs (rs.proppik.com)
                            </option>
                            <option value="tours" @selected(old('location', $tour->location) == 'tours')>tours
                                (tour.proppik.in)</option>
                            <option value="creart_qr" @selected(old('location', $tour->location) == 'creart_qr')>creart_qr
                                (creart.in/qr/)</option>
                        </select>
                        @error('location')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_status">Status <span class="text-danger">*</span></label>
                        <select name="status" id="tour_status" class="form-select" required>
                            <option value="draft" @selected(old('status', $tour->status) == 'draft')>Draft</option>
                            <option value="published" @selected(old('status', $tour->status) == 'published')>Published
                            </option>
                            <option value="archived" @selected(old('status', $tour->status) == 'archived')>Archived
                            </option>
                        </select>
                        @error('status')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_revision">Revision</label>
                        <input type="text" name="revision" id="tour_revision" class="form-control"
                            value="{{ old('revision', $tour->revision) }}" placeholder="v1.0">
                        @error('revision')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="d-none mb-3">
                <label class="form-label" for="tour_description">Short Description</label>
                <textarea name="description" id="tour_description" class="form-control"
                    rows="3">{{ $tour->description }}</textarea>
            </div>

            <div class="d-none mb-3">
                <label class="form-label" for="tour_content">Full Content</label>
                <textarea name="content" id="tour_content" class="form-control" rows="5">{{ $tour->content }}</textarea>
            </div>

            <div class="d-none row">
                <div class="col-lg-3">
                    <div class="mb-3">
                        <label class="form-label" for="tour_price">Price (₹)</label>
                        <input type="number" name="price" id="tour_price" class="form-control"
                            value="{{ $tour->price }}" step="0.01" min="0">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="mb-3">
                        <label class="form-label" for="tour_duration_days">Duration (Days)</label>
                        <input type="number" name="duration_days" id="tour_duration_days" class="form-control"
                            value="{{ $tour->duration_days }}" min="1">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="mb-3">
                        <label class="form-label" for="tour_start_date">Start Date</label>
                        <input type="date" name="start_date" id="tour_start_date" class="form-control"
                            value="{{ optional($tour->start_date)->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="mb-3">
                        <label class="form-label" for="tour_end_date">End Date</label>
                        <input type="date" name="end_date" id="tour_end_date" class="form-control"
                            value="{{ optional($tour->end_date)->format('Y-m-d') }}">
                    </div>
                </div>
            </div>

            <div class="d-none row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_max_participants">Max Participants</label>
                        <input type="number" name="max_participants" id="tour_max_participants" class="form-control"
                            value="{{ $tour->max_participants }}" min="1">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="tour_featured_image">Featured Image URL</label>
                        <input type="text" name="featured_image" id="tour_featured_image" class="form-control"
                            value="{{ $tour->featured_image }}">
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- sidebar section -->
    <div class="card panel-card border-info border-top mb-3">
        <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h4>Sidebar section</h4>
                <p class="text-muted mb-0">Add company branding and contact details for this tour</p>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <div class="mt-2 items-start">
                            @if($tour->sidebar_logo)
                                <img id="sidebar_logo_preview" src="{{ Storage::disk('s3')->url($tour->sidebar_logo) }}"
                                    alt="Sidebar Logo"
                                    style="max-width: 300px; max-height: auto; border:1px solid #ddd; background:#fff; padding:2px;">
                            @else
                                <img id="sidebar_logo_preview" src="" alt="Sidebar Logo"
                                    style="max-width: 300px; max-height: auto; border:1px solid #ddd; background:#fff; padding:2px; display:none;">
                            @endif
                        </div>
                        <div class="mt-3">
                            <label class="form-label" for="sidebar_logo">Sidebar Logo</label>
                            <input type="file" name="sidebar_logo" id="custom_logo_sidebar" class="form-control"
                                accept="image/*" onchange="previewImage(event, 'sidebar_logo')">
                        </div>
                        @error('sidebar_logo')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-12 d-flex gap-3 align-items-center mb-3">
                    <div class="col-md-4">
                        <label for="sidebar_footer_link_show" class="form-label">Sidebar Footer Link Show</label>
                        <select name="sidebar_footer_link_show" id="sidebar_footer_link_show" class="form-select">
                            <option value="1"
                                {{ old('sidebar_footer_link_show', $tour->sidebar_footer_link_show) == 1 ? 'selected' : '' }}>
                                Show</option>
                            <option value="0"
                                {{ old('sidebar_footer_link_show', $tour->sidebar_footer_link_show) == 0 ? 'selected' : '' }}>
                                Hide</option>
                        </select>
                        @error('sidebar_footer_link_show')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8" id="sidebar-footer-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="sidebar_footer_text" class="form-label">Sidebar Footer Text</label>
                                <input type="text" name="sidebar_footer_text" id="sidebar_footer_text"
                                    class="form-control"
                                    value="{{ old('sidebar_footer_text', $tour->sidebar_footer_text) }}">
                                @error('sidebar_footer_text')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="sidebar_footer_link" class="form-label">Sidebar Footer Link</label>
                                <input type="text" name="sidebar_footer_link" id="sidebar_footer_link"
                                    class="form-control"
                                    value="{{ old('sidebar_footer_link', $tour->sidebar_footer_link) }}">
                                @error('sidebar_footer_link')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- footer section -->
        <div class="card panel-card border-info border-top mt-3">
            <div class="card-header">
                <h4 class="card-title mb-1">footer Section</h4>
                <p class="text-muted mb-0">add and edit details of Tour Footer</p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" for="footer_logo">Footer Logo</label>
                            <input type="file" name="footer_logo" id="footer_logo" class="form-control" accept="image/*"
                                onchange="previewImage(event, 'footer_logo_preview')">
                            <div class="mt-2">
                                @if($tour->footer_logo)
                                    <img id="footer_logo_preview" src="{{ Storage::disk('s3')->url($tour->footer_logo) }}"
                                        alt="Footer Logo"
                                        style="max-width: 150px; max-height: 80px; border:1px solid #ddd; background:#fff; padding:2px;">
                                @else
                                    <img id="footer_logo_preview" src="" alt="Footer Logo"
                                        style="max-width: 150px; max-height: 80px; border:1px solid #ddd; background:#fff; padding:2px; display:none;">
                                @endif
                            </div>
                            @error('footer_logo')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="footer_info_type" class="form-label">Footer Info Type</label>
                            <select name="footer_info_type" id="footer_info_type" class="form-select">
                                <option value="company"
                                    {{ old('footer_info_type', $tour->footer_info_type) == 'company' ? 'selected' : '' }}>
                                    Company</option>
                                <option value="agent"
                                    {{ old('footer_info_type', $tour->footer_info_type) == 'agent' ? 'selected' : '' }}>
                                    Agent</option>
                            </select>
                            @error('footer_info_type')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="footer_brand_logo" class="form-label">Footer Brand Logo</label>
                            <input type="text" name="footer_brand_logo" id="footer_brand_logo" class="form-control"
                                value="{{ old('footer_brand_logo', $tour->footer_brand_logo) }}">
                            @error('footer_brand_logo')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="footer_brand_text" class="form-label">Footer Brand Text</label>
                            <input type="text" name="footer_brand_text" id="footer_brand_text" class="form-control"
                                value="{{ old('footer_brand_text', $tour->footer_brand_text) }}">
                            @error('footer_brand_text')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="footer_brand_mobile" class="form-label">Footer Brand Mobile</label>
                            <input type="text" name="footer_brand_mobile" id="footer_brand_mobile" class="form-control"
                                value="{{ old('footer_brand_mobile', $tour->footer_brand_mobile) }}">
                            @error('footer_brand_mobile')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="footer_name" class="form-label">Footer Name</label>
                            <input type="text" name="footer_name" id="footer_name" class="form-control"
                                value="{{ old('footer_name', $tour->footer_name) }}">
                            @error('footer_name')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="footer_email" class="form-label">Footer Email</label>
                            <input type="email" name="footer_email" id="footer_email" class="form-control"
                                value="{{ old('footer_email', $tour->footer_email) }}">
                            @error('footer_email')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="footer_mobile" class="form-label">Footer Mobile</label>
                            <input type="text" name="footer_mobile" id="footer_mobile" class="form-control"
                                value="{{ old('footer_mobile', $tour->footer_mobile) }}">
                            @error('footer_mobile')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="footer_decription" class="form-label">Footer Description</label>
                            <textarea name="footer_decription" id="footer_decription" class="form-control"
                                rows="2">{{ old('footer_decription', $tour->footer_decription) }}</textarea>
                            @error('footer_decription')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Brand section -->
        <div class="card panel-card border-info border-top mt-3">
            <div class="card-header">
                <h4 class="card-title mb-1">Footer Brand</h4>
                <p class="text-muted mb-0">Add and edit details of Footer Brand</p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="footer_brand_logo">Footer Brand Logo</label>
                            <input type="file" name="footer_brand_logo" id="footer_brand_logo" class="form-control"
                                accept="image/*" onchange="previewImage(event, 'footer_brand_logo_preview')">
                            <div class="mt-2">
                                @if($tour->footer_brand_logo)
                                    <img id="footer_brand_logo_preview"
                                        src="{{ Storage::disk('s3')->url($tour->footer_brand_logo) }}"
                                        alt="Footer Brand Logo"
                                        style="max-width: 150px; max-height: 80px; border:1px solid #ddd; background:#fff; padding:2px;">
                                @else
                                    <img id="footer_brand_logo_preview" src="" alt="Footer Brand Logo"
                                        style="max-width: 150px; max-height: 80px; border:1px solid #ddd; background:#fff; padding:2px; display:none;">
                                @endif
                            </div>
                            @error('footer_brand_logo')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="footer_brand_text" class="form-label">Footer Brand Text</label>
                        <input type="text" name="footer_brand_text" id="footer_brand_text" class="form-control"
                            value="{{ old('footer_brand_text', $tour->footer_brand_text) }}">
                        @error('footer_brand_text')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="footer_brand_mobile" class="form-label">Footer Brand Mobile</label>
                        <input type="text" name="footer_brand_mobile" id="footer_brand_mobile" class="form-control"
                            value="{{ old('footer_brand_mobile', $tour->footer_brand_mobile) }}">
                        @error('footer_brand_mobile')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
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