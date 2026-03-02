<div class="row mb-5">
    <div class="col-sm-3 col-md-3 col-lg-3 mb-2 mb-sm-0">
        <div class="nav flex-column nav-pills settings-nav-pills p-1" id="vl-pills-tab" role="tablist"
            aria-orientation="vertical" style="position: sticky; top: 20px;">
            @php
                $firstActiveTab = 'vl-pills-home';
            @endphp
            <a class="nav-link {{ ($firstActiveTab === 'vl-pills-home') ? 'active show' : '' }}" id="vl-pills-home-tab"
                data-bs-toggle="pill" href="#vl-pills-home" role="tab" aria-controls="vl-pills-home"
                aria-selected="{{ ($firstActiveTab === 'vl-pills-home') ? 'true' : 'false' }}">
                <i class="ri-calendar-event-line me-2"></i>
                <span>Basic Information</span>
            </a>
            <a class="nav-link {{ ($firstActiveTab === 'vl-pills-sidebar-section') ? 'active show' : '' }}"
                id="vl-pills-sidebar-section-tab" data-bs-toggle="pill" href="#vl-pills-sidebar-section" role="tab"
                aria-controls="vl-pills-sidebar-section"
                aria-selected="{{ ($firstActiveTab === 'vl-pills-sidebar-section') ? 'true' : 'false' }}">
                <i class="ri-bus-2-line me-2"></i>
                <span>Sidebar Section</span>
            </a>
            <a class="nav-link {{ ($firstActiveTab === 'vl-pills-bottom-mark-top') ? 'active show' : '' }}"
                id="vl-pills-bottom-mark-top-tab" data-bs-toggle="pill" href="#vl-pills-bottom-mark-top" role="tab"
                aria-controls="vl-pills-bottom-mark-top"
                aria-selected="{{ ($firstActiveTab === 'vl-pills-bottom-mark-top') ? 'true' : 'false' }}">
                <i class="ri-home-4-line me-2"></i>
                <span>Bottom Mark: Top Section</span>
            </a>
            <a class="nav-link {{ ($firstActiveTab === 'vl-pills-bottom-mark-property') ? 'active show' : '' }}"
                id="vl-pills-bottom-mark-property-tab" data-bs-toggle="pill" href="#vl-pills-bottom-mark-property" role="tab"
                aria-controls="vl-pills-bottom-mark-property"
                aria-selected="{{ ($firstActiveTab === 'vl-pills-bottom-mark-property') ? 'true' : 'false' }}">
                <i class="ri-home-4-line me-2"></i>
                <span>Bottom Mark: Mark Property</span>
            </a>
        </div>
    </div>
    <div class="col-md-9">
        <form method="POST" id="tourDetailForm" action="{{ route('admin.tours.updateTourDetails', $tour) }}"
            enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">

            <div class="tab-content pt-0" id="vl-pills-tabContent">
            <!-- Basic Information sections -->
            <div class="tab-pane fade {{ ($firstActiveTab === 'vl-pills-home') ? 'active show' : '' }}"
                id="vl-pills-home" role="tabpanel" aria-labelledby="vl-pills-home-tab">
                 <div class="card">
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
                                    @php
                                        $ftpConfigs = \App\Models\FtpConfiguration::active()->ordered()->get();
                                    @endphp
                                    @foreach($ftpConfigs as $ftpConfig)
                                        <option value="{{ $ftpConfig->category_name }}" 
                                            @selected(old('location', $tour->location ?? '') == $ftpConfig->category_name)>
                                            {{ $ftpConfig->display_name }} ({{ $ftpConfig->main_url }})
                                        </option>
                                    @endforeach
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
                        <div class="col-lg-4 d-none">
                            <div class="mb-3 ">
                                <label class="form-label" for="tour_revision">Revision</label>
                                <input type="text" name="revision" id="tour_revision" class="form-control"
                                    value="{{ old('revision', $tour->revision) }}" placeholder="v1.0">
                                @error('revision')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label" for="tour_thumbnail">Tour Thumbnail <span class="text-muted">(Image file, max 5MB)</span></label>
                                
                                <input type="file" name="tour_thumbnail" id="tour_thumbnail" class="form-control"
                                    accept="image/*">

                                    <div class="">
                                        @if($tour   ->tour_thumbnail)
                                            <div>
                                                <small class="text-muted d-block mb-2">Current thumbnail:</small>
                                                <img src="{{ Storage::disk('s3')->url($tour->tour_thumbnail) }}" alt="Tour Thumbnail"
                                                    style="max-width: 200px; max-height: 200px; border:1px solid #ddd; padding:5px; border-radius: 4px;">
                                            </div>
                                        @endif
                                    </div>
                                <small class="text-muted d-block mt-2">Recommended size: 400x300px. Uploaded to S3 in settings/tour_thumbnails/</small>
                                @error('tour_thumbnail')<div class="text-danger mt-2">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="mb-3">
                                <label class="form-label" for="is_active">Tour Active</label>
                                <div class="form-check form-switch form-switch-lg" dir="ltr">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $tour->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3" id="testing-credentials-required-field">
                            <div class="mb-3">
                                <label class="form-label" for="testing_is_credentials">Credentials Required</label>
                                <div class="form-check form-switch form-switch-lg" dir="ltr">
                                    <input type="checkbox" class="form-check-input" id="testing_is_credentials" name="is_credentials" value="1" {{ old('is_credentials', $tour->is_credentials) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="testing_is_credentials">Required</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3" id="mobile-validation-field">
                            <div class="mb-3">
                                <label class="form-label" for="is_mobile_validation">Mobile Validation</label>
                                <div class="form-check form-switch form-switch-lg" dir="ltr">
                                    <input type="checkbox" class="form-check-input" id="is_mobile_validation" name="is_mobile_validation" value="1" {{ old('is_mobile_validation', $tour->is_mobile_validation) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_mobile_validation">Required</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3" id="testing-is-hosted-field">
                            <div class="mb-3">
                                <label class="form-label" for="testing_is_hosted">Is Hosted</label>
                                <div class="form-check form-switch form-switch-lg" dir="ltr">
                                    <input type="checkbox" class="form-check-input" id="testing_is_hosted" name="is_hosted" value="1" {{ old('is_hosted', $tour->is_hosted) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="testing_is_hosted">Hosted</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row {{ old('is_hosted', $tour->is_hosted) ? '' : 'd-none' }}" id="testing-hosted-link-container">
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label class="form-label" for="testing_hosted_link">Hosted Link</label>
                                <input type="url" name="hosted_link" id="testing_hosted_link" class="form-control"
                                    placeholder="e.g, https://example.com"
                                    value="{{ old('hosted_link', $tour->hosted_link) }}">
                                <div class="invalid-feedback">Hosted Link is required when Is Hosted is enabled.</div>
                                @error('hosted_link')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Credentials Section -->
                    <div id="testing-credentials-section" class="mt-3 {{ old('is_credentials', $tour->is_credentials) ? '' : 'd-none' }}">
                        <h6 class="mb-3">Credentials Management</h6>
                        <div id="testing-credentials-container">
                            @php
                                $credentials = old('credentials', $tour->credentials->toArray() ?? []);
                            @endphp
                            
                            @foreach($credentials as $index => $credential)
                                <div class="credential-row row mb-2 align-items-end">
                                    <input type="hidden" name="credentials[{{ $index }}][id]" value="{{ $credential['id'] ?? '' }}">
                                    <div class="col-md-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="credentials[{{ $index }}][user_name]" class="form-control" value="{{ $credential['user_name'] }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Password</label>
                                        <input type="text" name="credentials[{{ $index }}][password]" class="form-control" value="{{ $credential['password'] }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Status</label>
                                        <select name="credentials[{{ $index }}][is_active]" class="form-select">
                                            <option value="1" {{ ($credential['is_active'] ?? true) ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !($credential['is_active'] ?? true) ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-credential"><i class="ri-delete-bin-line"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-success mt-2" id="testing-add-credential-btn">
                            <i class="ri-add-line"></i> Add Credential
                        </button>
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
                    
            </div>

            <!-- Sidebar sections -->
            <div class="tab-pane fade {{ ($firstActiveTab === 'vl-pills-sidebar-section') ? 'active show' : '' }}"
                id="vl-pills-sidebar-section" role="tabpanel" aria-labelledby="vl-pills-sidebar-section-tab">
                    <div class="card border-1 shadow-sm">
                        <!-- sidebar section -->
                        <div class="card-header">
                            <div>
                                <h4 class="card-title mb-0">Sidebar section
                                </h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="mt-2 items-start">
                                            @if($tour->sidebar_logo)
                                                <img id="sidebar_logo_preview"
                                                    src="{{ Storage::disk('s3')->url($tour->sidebar_logo) }}"
                                                    alt="Sidebar Logo"
                                                    style="max-width: 300px; max-height: auto; border:1px solid #ddd; background:#fff; padding:2px;">
                                            @else
                                                <img id="sidebar_logo_preview" src="" alt="Sidebar Logo"
                                                    style="max-width: 300px; max-height: auto; border:1px solid #ddd; background:#fff; padding:2px; display:none;">
                                            @endif
                                        </div>
                                        <div class="mt-3">
                                            <label class="form-label" for="sidebar_logo">Sidebar Logo</label>
                                            <input type="file" name="sidebar_logo" id="sidebar_logo" @if (!$qr_code) disabled @endif class="form-control" accept="image/*"
                                                onchange="previewImage(event, 'sidebar_logo')">
                                        </div>
                                        @error('sidebar_logo')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <h5 class="mb-3">Sidebar Tag <span class="text-muted">(optional)</span></h5>
                                    <p class="text-muted mb-3">Vertical tag on the right side of the sidebar. Leave
                                        empty to hide.</p>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="sidebar_tag_text" class="form-label">Tag Title</label>
                                        <input type="text" name="sidebar_tag_text" id="sidebar_tag_text"
                                            class="form-control" placeholder="e.g, sold out"
                                            value="{{ old('sidebar_tag_text', $tour->sidebar_tag_text ?? '') }}">
                                        @error('sidebar_tag_text')<div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="sidebar_tag_bg_color">Tag Background
                                            Color</label>
                                        <div class="input-group">
                                            <span class="input-group-text p-1">
                                                <input type="color" id="sidebar_tag_bg_color_picker"
                                                    class="form-control form-control-color"
                                                    value="{{ old('sidebar_tag_bg_color', $tour->sidebar_tag_bg_color ?? '#ff000d') }}"
                                                    onchange="document.getElementById('sidebar_tag_bg_color').value = this.value">
                                            </span>
                                            <input type="text" name="sidebar_tag_bg_color" id="sidebar_tag_bg_color"
                                                class="form-control" placeholder="e.g. #ff000d"
                                                oninput="document.getElementById('sidebar_tag_bg_color_picker').value = this.value"
                                                value="{{ old('sidebar_tag_bg_color', $tour->sidebar_tag_bg_color ?? '#ff000d') }}">
                                        </div>
                                        @error('sidebar_tag_bg_color')<div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="sidebar_tag_color">Tag Text Color</label>
                                        <div class="input-group">
                                            <span class="input-group-text p-1">
                                                <input type="color" id="sidebar_tag_color_picker"
                                                    class="form-control form-control-color"
                                                    value="{{ old('sidebar_tag_color', $tour->sidebar_tag_color ?? '#ffffff') }}"
                                                    onchange="document.getElementById('sidebar_tag_color').value = this.value">
                                            </span>
                                            <input type="text" name="sidebar_tag_color" id="sidebar_tag_color"
                                                class="form-control" placeholder="e.g. #ffffff"
                                                oninput="document.getElementById('sidebar_tag_color_picker').value = this.value"
                                                value="{{ old('sidebar_tag_color', $tour->sidebar_tag_color ?? '#ffffff') }}">
                                        </div>
                                        @error('sidebar_tag_color')<div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="made_by_text" class="form-label">Made by text <span
                                                class="text-muted">(optional)</span></label>
                                        <input type="text" name="sidebar_footer_text" id="made_by_text"
                                            class="form-control" placeholder="e.g, Prop Pik"
                                            value="{{ old('sidebar_footer_text', $tour->sidebar_footer_text ?? '') }}">
                                        @error('made_by_text')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="made_by_link" class="form-label">Made by link <span
                                                class="text-danger">*</span></label>
                                        <input type="url" name="sidebar_footer_link" id="made_by_link"
                                            class="form-control" placeholder="e.g,   https://proppik.com/contact"
                                            value="{{ old('sidebar_footer_link', $tour->sidebar_footer_link ?? '') }}">
                                        @error('made_by_link')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

            <!--  Bottom Mark: Top section  -->
            <div class="tab-pane fade {{ ($firstActiveTab === 'vl-pills-bottom-mark-top') ? 'active show' : '' }}"
                id="vl-pills-bottom-mark-top" role="tabpanel" aria-labelledby="vl-pills-bottom-mark-top-tab">
                <div class="card border-1 shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title"> Bottom Mark: Top section</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="mt-2">
                                        @if($tour->footer_logo)
                                            <img id="footer_logo_preview" src="{{ Storage::disk('s3')->url($tour->footer_logo) }}"
                                                data-original-src="{{ Storage::disk('s3')->url($tour->footer_logo) }}" alt="Footer Logo"
                                                style="max-width: 150px; max-height: 80px; border:1px solid #ddd; background:#fff; padding:2px;">
                                        @else
                                            <img id="footer_logo_preview" src="" data-original-src="" alt="Footer Logo"
                                                style="max-width: 150px; max-height: 80px; border:1px solid #ddd; background:#fff; padding:2px; display:none;">
                                        @endif
                                    </div>
                                    <div>
                                        <label class="form-label" for="footer_logo">Top Image</label>
                                        <input type="file" name="footer_logo" id="footer_logo" @if (!$qr_code) disabled @endif
                                            class="form-control" accept="image/*">
                                    </div>
                                    @error('footer_logo')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Language tabs -->
                        <ul class="nav nav-tabs mb-3" id="testingFooterLanguageTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="testing-footer-lang-english-tab" data-bs-toggle="tab" data-bs-target="#testing-footer-lang-english-pane"
                                    type="button" role="tab" aria-controls="testing-footer-lang-english-pane" aria-selected="true">
                                    <span class="badge bg-success me-2">✓</span>English
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="testing-footer-lang-gujarati-tab" data-bs-toggle="tab" data-bs-target="#testing-footer-lang-gujarati-pane"
                                    type="button" role="tab" aria-controls="testing-footer-lang-gujarati-pane" aria-selected="false">
                                    <span class="badge bg-success me-2">✓</span>Gujarati
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="testing-footer-lang-hindi-tab" data-bs-toggle="tab" data-bs-target="#testing-footer-lang-hindi-pane"
                                    type="button" role="tab" aria-controls="testing-footer-lang-hindi-pane" aria-selected="false">
                                    <span class="badge bg-success me-2">✓</span>Hindi
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="testingFooterLanguageTabsContent">
                            <div class="tab-pane fade show active" id="testing-footer-lang-english-pane" role="tabpanel" aria-labelledby="testing-footer-lang-english-tab" tabindex="0">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="footer_title_en" class="form-label">Top Title (English) <span class="text-danger">*</span></label>
                                            <input type="text" name="footer_title[en]" id="footer_title_en" class="form-control" placeholder="e.g, Ramesh Mehta"
                                                value="{{ old('footer_title.en', data_get($tour, 'footer_title.en', '')) }}">
                                            @error('footer_title.en')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="footer_subtitle_en" class="form-label">Top Sub Title (English) <span class="text-danger">*</span></label>
                                            <input type="text" name="footer_subtitle[en]" id="footer_subtitle_en" class="form-control" placeholder="e.g, JK Real Estate"
                                                value="{{ old('footer_subtitle.en', data_get($tour, 'footer_subtitle.en', '')) }}">
                                            @error('footer_subtitle.en')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="footer_decription_en" class="form-label">Top Description (English) <span class="text-muted">(optional)</span></label>
                                            <textarea name="footer_decription[en]" id="footer_decription_en" class="form-control"
                                            placeholder="e.g, For Reant / For Sell / For Lease"
                                                rows="2">{{ old('footer_decription.en', data_get($tour, 'footer_decription.en', '')) }}</textarea>
                                            @error('footer_decription.en')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="testing-footer-lang-gujarati-pane" role="tabpanel" aria-labelledby="testing-footer-lang-gujarati-tab" tabindex="0">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="footer_title_gu" class="form-label">Top Title (Gujarati) <span class="text-danger">*</span></label>
                                            <input type="text" name="footer_title[gu]" id="footer_title_gu" class="form-control" placeholder="e.g, રમેશ મહતા"
                                                value="{{ old('footer_title.gu', data_get($tour, 'footer_title.gu', '')) }}">
                                            @error('footer_title.gu')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="footer_subtitle_gu" class="form-label">Top Sub Title (Gujarati) <span class="text-danger">*</span></label>
                                            <input type="text" name="footer_subtitle[gu]" id="footer_subtitle_gu" class="form-control" placeholder="e.g, જે કે રીયલ એસ્ટેટ"
                                                value="{{ old('footer_subtitle.gu', data_get($tour, 'footer_subtitle.gu', '')) }}">
                                            @error('footer_subtitle.gu')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="footer_decription_gu" class="form-label">Top Description (Gujarati) <span class="text-muted">(optional)</span></label>
                                            <textarea name="footer_decription[gu]" id="footer_decription_gu" class="form-control"
                                            placeholder="e.g, ભાડે માટે / વેચવા માટે / ભાડે માટે"
                                                rows="2">{{ old('footer_decription.gu', data_get($tour, 'footer_decription.gu', '')) }}</textarea>
                                            @error('footer_decription.gu')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="testing-footer-lang-hindi-pane" role="tabpanel" aria-labelledby="testing-footer-lang-hindi-tab" tabindex="0">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="footer_title_hi" class="form-label">Top Title (Hindi) <span class="text-danger">*</span></label>
                                            <input type="text" name="footer_title[hi]" id="footer_title_hi" class="form-control" placeholder="e.g, रमेश मेहता"
                                                value="{{ old('footer_title.hi', data_get($tour, 'footer_title.hi', '')) }}">
                                            @error('footer_title.hi')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="footer_subtitle_hi" class="form-label">Top Sub Title (Hindi) <span class="text-danger">*</span></label>
                                            <input type="text" name="footer_subtitle[hi]" id="footer_subtitle_hi" class="form-control" placeholder="e.g, जे के रीयल एस्टेट"
                                                value="{{ old('footer_subtitle.hi', data_get($tour, 'footer_subtitle.hi', '')) }}">
                                            @error('footer_subtitle.hi')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="footer_decription_hi" class="form-label">Top Description (Hindi) <span class="text-muted">(optional)</span></label>
                                            <textarea name="footer_decription[hi]" id="footer_decription_hi" class="form-control"
                                            placeholder="e.g, किराए के लिए / बिक्री के लिए / पट्टे के लिए"
                                                rows="2">{{ old('footer_decription.hi', data_get($tour, 'footer_decription.hi', '')) }}</textarea>
                                            @error('footer_decription.hi')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="footer_email" class="form-label">Contact Email</label>
                                    <input type="email" name="footer_email" id="footer_email" class="form-control"
                                        value="{{ old('footer_email', $tour->footer_email) }}" placeholder="e.g, Contact@example.com">
                                    @error('footer_email')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="footer_mobile" class="form-label">Contact Number</label>
                                    <input type="text" name="footer_mobile" id="footer_mobile" class="form-control"
                                        value="{{ old('footer_mobile', $tour->footer_mobile) }}" placeholder="eg.+91 9898 363026">
                                    @error('footer_mobile')<div class="text-danger">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--  Bottom Mark: Property Details  -->
            <div class="tab-pane fade {{ ($firstActiveTab === 'vl-pills-bottom-mark-property') ? 'active show' : '' }}"
                id="vl-pills-bottom-mark-property" role="tabpanel" aria-labelledby="vl-pills-bottom-mark-property-tab">
                  <!-- Bottommark Multilingual Fields Section -->
                <div class="card panel-card border-top ">
                    <div class="card-header">
                        <h4 class="card-title">Bottom Mark: Property Details</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Add property details in multiple languages</p>
                        <!-- Language tabs -->
                        <ul class="nav nav-tabs mb-3" id="testingBottommarkLanguageTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="testing-bottommark-lang-english-tab" data-bs-toggle="tab" data-bs-target="#testing-bottommark-lang-english-pane"
                                    type="button" role="tab" aria-controls="testing-bottommark-lang-english-pane" aria-selected="true">
                                    English
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="testing-bottommark-lang-gujarati-tab" data-bs-toggle="tab" data-bs-target="#testing-bottommark-lang-gujarati-pane"
                                    type="button" role="tab" aria-controls="testing-bottommark-lang-gujarati-pane" aria-selected="false">
                                    Gujarati
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="testing-bottommark-lang-hindi-tab" data-bs-toggle="tab" data-bs-target="#testing-bottommark-lang-hindi-pane"
                                    type="button" role="tab" aria-controls="testing-bottommark-lang-hindi-pane" aria-selected="false">
                                    Hindi
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="testingBottommarkLanguageTabsContent">
                            <!-- English Tab -->
                            <div class="tab-pane fade show active" id="testing-bottommark-lang-english-pane" role="tabpanel" aria-labelledby="testing-bottommark-lang-english-tab" tabindex="0">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bottommark_property_name_en" class="form-label">Property Name</label>
                                            <input type="text" name="bottommark_property_name_en" id="bottommark_property_name_en" class="form-control"
                                                placeholder="e.g., 3 BHK Apartment"
                                                value="{{ old('bottommark_property_name_en', $tour->bottommark_property_name['en'] ?? '') }}">
                                            @error('bottommark_property_name_en')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bottommark_room_type_en" class="form-label">Room Type</label>
                                            <input type="text" name="bottommark_room_type_en" id="bottommark_room_type_en" class="form-control"
                                                placeholder="e.g., Residential"
                                                value="{{ old('bottommark_room_type_en', $tour->bottommark_room_type['en'] ?? '') }}">
                                            @error('bottommark_room_type_en')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bottommark_dimensions_en" class="form-label">Dimensions</label>
                                            <input type="text" name="bottommark_dimensions_en" id="bottommark_dimensions_en" class="form-control"
                                                placeholder="e.g., 1200 sq ft"
                                                value="{{ old('bottommark_dimensions_en', $tour->bottommark_dimensions['en'] ?? '') }}">
                                            @error('bottommark_dimensions_en')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gujarati Tab -->
                            <div class="tab-pane fade" id="testing-bottommark-lang-gujarati-pane" role="tabpanel" aria-labelledby="testing-bottommark-lang-gujarati-tab" tabindex="0">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bottommark_property_name_gu" class="form-label">Property Name</label>
                                            <input type="text" name="bottommark_property_name_gu" id="bottommark_property_name_gu" class="form-control"
                                                placeholder="e.g., 3 BHK એપાર્ટમેન્ટ"
                                                value="{{ old('bottommark_property_name_gu', $tour->bottommark_property_name['gu'] ?? '') }}">
                                            @error('bottommark_property_name_gu')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bottommark_room_type_gu" class="form-label">Room Type</label>
                                            <input type="text" name="bottommark_room_type_gu" id="bottommark_room_type_gu" class="form-control"
                                                placeholder="e.g., રહેણાંક"
                                                value="{{ old('bottommark_room_type_gu', $tour->bottommark_room_type['gu'] ?? '') }}">
                                            @error('bottommark_room_type_gu')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bottommark_dimensions_gu" class="form-label">Dimensions</label>
                                            <input type="text" name="bottommark_dimensions_gu" id="bottommark_dimensions_gu" class="form-control"
                                                placeholder="e.g., 1200 ચોક્સ ફૂટ"
                                                value="{{ old('bottommark_dimensions_gu', $tour->bottommark_dimensions['gu'] ?? '') }}">
                                            @error('bottommark_dimensions_gu')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hindi Tab -->
                            <div class="tab-pane fade" id="testing-bottommark-lang-hindi-pane" role="tabpanel" aria-labelledby="testing-bottommark-lang-hindi-tab" tabindex="0">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bottommark_property_name_hi" class="form-label">Property Name</label>
                                            <input type="text" name="bottommark_property_name_hi" id="bottommark_property_name_hi" class="form-control"
                                                placeholder="e.g., 3 BHK अपार्टमेंट"
                                                value="{{ old('bottommark_property_name_hi', $tour->bottommark_property_name['hi'] ?? '') }}">
                                            @error('bottommark_property_name_hi')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bottommark_room_type_hi" class="form-label">Room Type</label>
                                            <input type="text" name="bottommark_room_type_hi" id="bottommark_room_type_hi" class="form-control"
                                                placeholder="e.g., आवासीय"
                                                value="{{ old('bottommark_room_type_hi', $tour->bottommark_room_type['hi'] ?? '') }}">
                                            @error('bottommark_room_type_hi')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bottommark_dimensions_hi" class="form-label">Dimensions</label>
                                            <input type="text" name="bottommark_dimensions_hi" id="bottommark_dimensions_hi" class="form-control"
                                                placeholder="e.g., 1200 वर्ग फुट"
                                                value="{{ old('bottommark_dimensions_hi', $tour->bottommark_dimensions['hi'] ?? '') }}">
                                            @error('bottommark_dimensions_hi')<div class="text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>                                    

            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-save-line me-1"></i> Update Tour Details
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const testingForm = document.querySelector('#test-pane form#tourDetailForm');
        if (!testingForm) {
            return;
        }

        const isActive = testingForm.querySelector('#is_active');
        const isCredentials = testingForm.querySelector('#testing_is_credentials');
        const isHosted = testingForm.querySelector('#testing_is_hosted');

        const credentialsRequiredField = testingForm.querySelector('#testing-credentials-required-field');
        const mobileValidationField = testingForm.querySelector('#mobile-validation-field');
        const isHostedField = testingForm.querySelector('#testing-is-hosted-field');
        const hostedLinkContainer = testingForm.querySelector('#testing-hosted-link-container');
        const hostedLinkInput = testingForm.querySelector('#testing_hosted_link');
        const credentialsSection = testingForm.querySelector('#testing-credentials-section');
        const credentialsContainer = testingForm.querySelector('#testing-credentials-container');
        const addCredentialBtn = testingForm.querySelector('#testing-add-credential-btn');

        if (!isActive || !isCredentials || !isHosted || !credentialsRequiredField || !mobileValidationField || !isHostedField || !hostedLinkContainer || !credentialsSection) {
            return;
        }

        function toggleTestingFields() {
            if (isActive.checked) {
                credentialsRequiredField.style.display = '';
                mobileValidationField.style.display = '';
                isHostedField.style.display = '';

                if (isHosted.checked) {
                    hostedLinkContainer.classList.remove('d-none');
                    if (hostedLinkInput) {
                        hostedLinkInput.required = true;
                    }
                } else {
                    hostedLinkContainer.classList.add('d-none');
                    if (hostedLinkInput) {
                        hostedLinkInput.required = false;
                        hostedLinkInput.classList.remove('is-valid', 'is-invalid');
                    }
                }

                if (isCredentials.checked) {
                    credentialsSection.classList.remove('d-none');
                } else {
                    credentialsSection.classList.add('d-none');
                }
            } else {
                credentialsRequiredField.style.display = 'none';
                mobileValidationField.style.display = 'none';
                isHostedField.style.display = 'none';
                hostedLinkContainer.classList.add('d-none');
                credentialsSection.classList.add('d-none');
            }
        }

        isActive.addEventListener('change', toggleTestingFields);
        isCredentials.addEventListener('change', toggleTestingFields);
        isHosted.addEventListener('change', toggleTestingFields);
        toggleTestingFields();

        if (addCredentialBtn && credentialsContainer) {
            let credentialIndex = credentialsContainer.querySelectorAll('.credential-row').length;

            addCredentialBtn.addEventListener('click', function () {
                const row = document.createElement('div');
                row.className = 'credential-row row mb-2 align-items-end';
                row.innerHTML = `
                    <div class="col-md-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="credentials[${credentialIndex}][user_name]" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Password</label>
                        <input type="text" name="credentials[${credentialIndex}][password]" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="credentials[${credentialIndex}][is_active]" class="form-select">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-credential"><i class="ri-delete-bin-line"></i></button>
                    </div>
                `;
                credentialsContainer.appendChild(row);
                credentialIndex++;
            });

            credentialsContainer.addEventListener('click', function (event) {
                if (event.target.closest('.remove-credential')) {
                    event.target.closest('.credential-row')?.remove();
                }
            });
        }
    });
</script>