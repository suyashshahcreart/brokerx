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
                <i class="ri-list-indefinite me-2"></i>
                <span>Basic Information</span>
            </a>
            <a class="nav-link {{ ($firstActiveTab === 'vl-pills-tour-contact-info') ? 'active show' : '' }}"
                id="vl-pills-tour-contact-info-tab" data-bs-toggle="pill" href="#vl-pills-tour-contact-info" role="tab"
                aria-controls="vl-pills-tour-contact-info"
                aria-selected="{{ ($firstActiveTab === 'vl-pills-tour-contact-info') ? 'true' : 'false' }}">
                <i class="ri-contacts-line me-2"></i>
                <span>Tour Contact Information</span>
            </a>
            <a class="nav-link {{ ($firstActiveTab === 'vl-pills-attachments') ? 'active show' : '' }}"
                id="vl-pills-attachments-tab" data-bs-toggle="pill" href="#vl-pills-attachments" role="tab"
                aria-controls="vl-pills-attachments"
                aria-selected="{{ ($firstActiveTab === 'vl-pills-attachments') ? 'true' : 'false' }}">
                <i class="ri-attachment-line me-2"></i>
                <span>Attachments</span>
            </a>
            <a class="nav-link {{ ($firstActiveTab === 'vl-pills-language') ? 'active show' : '' }}"
                id="vl-pills-language-tab" data-bs-toggle="pill" href="#vl-pills-language" role="tab"
                aria-controls="vl-pills-language"
                aria-selected="{{ ($firstActiveTab === 'vl-pills-language') ? 'true' : 'false' }}">
                <i class="ri-translate-2 me-2"></i>
                <span>Language Section</span>
            </a>
            <a class="nav-link {{ ($firstActiveTab === 'vl-pills-loader-config') ? 'active show' : '' }}"
                id="vl-pills-loader-config-tab" data-bs-toggle="pill" href="#vl-pills-loader-config" role="tab"
                aria-controls="vl-pills-loader-config"
                aria-selected="{{ ($firstActiveTab === 'vl-pills-loader-config') ? 'true' : 'false' }}">
                <i class="ri-timer-flash-line me-2"></i>
                <span>Loader Configuration Section</span>
            </a>
            <a class="nav-link {{ ($firstActiveTab === 'vl-pills-sidebar-section') ? 'active show' : '' }}"
                id="vl-pills-sidebar-section-tab" data-bs-toggle="pill" href="#vl-pills-sidebar-section" role="tab"
                aria-controls="vl-pills-sidebar-section"
                aria-selected="{{ ($firstActiveTab === 'vl-pills-sidebar-section') ? 'true' : 'false' }}">
                <i class="ri-layout-left-line me-2"></i>
                <span>Sidebar Section</span>
            </a>
            <a class="nav-link {{ ($firstActiveTab === 'vl-pills-bottom-mark-top') ? 'active show' : '' }}"
                id="vl-pills-bottom-mark-top-tab" data-bs-toggle="pill" href="#vl-pills-bottom-mark-top" role="tab"
                aria-controls="vl-pills-bottom-mark-top"
                aria-selected="{{ ($firstActiveTab === 'vl-pills-bottom-mark-top') ? 'true' : 'false' }}">
                <i class="ri-layout-row-line me-2"></i>
                <span>Bottom Mark: Top Section</span>
            </a>
            <a class="nav-link {{ ($firstActiveTab === 'vl-pills-bottom-mark-property') ? 'active show' : '' }}"
                id="vl-pills-bottom-mark-property-tab" data-bs-toggle="pill" href="#vl-pills-bottom-mark-property"
                role="tab" aria-controls="vl-pills-bottom-mark-property"
                aria-selected="{{ ($firstActiveTab === 'vl-pills-bottom-mark-property') ? 'true' : 'false' }}">
                <i class="ri-cash-line me-2"></i>
                <span>Bottom Mark: Mark Property</span>
            </a>
        </div>
    </div>
    <div class="col-md-9">
        <!-- all tabs in this Div -->
        <div class="tab-content pt-0" id="vl-pills-tabContent">

            <!-- Basic Information sections -->
            <div class="tab-pane fade {{ ($firstActiveTab === 'vl-pills-home') ? 'active show' : '' }}"
                id="vl-pills-home" role="tabpanel" aria-labelledby="vl-pills-home-tab">
                <div class="card border-1 shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <form id="basicInfoTabUpdate" method="POST"
                            action="{{ route('admin.tours.UpdateBasicInfoTourTab', $tour) }}"
                            enctype="multipart/form-data" class="needs-validation" novalidate>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="tour_name">Tour Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name" id="tour_name" class="form-control"
                                            value="{{ old('name', $tour->name) }}" required>
                                        <div class="invalid-feedback">Please enter tour name.</div>
                                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="tour_title">Tour Title <span
                                                class="text-danger">*</span></label>
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
                                                <option value="{{ $ftpConfig->category_name }}" @selected(old('location', $tour->location ?? '') == $ftpConfig->category_name)>
                                                    {{ $ftpConfig->display_name }} ({{ $ftpConfig->main_url }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('location')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="tour_status">Status <span
                                                class="text-danger">*</span></label>
                                        <select name="status" id="tour_status" class="form-select" required>
                                            <option value="draft" @selected(old('status', $tour->status) == 'draft')>Draft
                                            </option>
                                            <option value="published" @selected(old('status', $tour->status) == 'published')>
                                                Published
                                            </option>
                                            <option value="archived" @selected(old('status', $tour->status) == 'archived')>
                                                Archived
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
                                        <label class="form-label" for="tour_thumbnail">Tour Thumbnail <span
                                                class="text-muted">(Image file, max 5MB)</span></label>

                                        <input type="file" name="tour_thumbnail" id="tour_thumbnail"
                                            class="form-control" accept="image/*">

                                        <div class="">
                                            @if($tour->tour_thumbnail)
                                                <div>
                                                    <small class="text-muted d-block mb-2">Current thumbnail:</small>
                                                    <img src="{{ Storage::disk('s3')->url($tour->tour_thumbnail) }}"
                                                        alt="Tour Thumbnail"
                                                        style="max-width: 200px; max-height: 200px; border:1px solid #ddd; padding:5px; border-radius: 4px;">
                                                </div>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block mt-2">Recommended size: 400x300px. Uploaded to
                                            S3 in settings/tour_thumbnails/</small>
                                        @error('tour_thumbnail')<div class="text-danger mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="is_active">Tour Active</label>
                                        <div class="form-check form-switch form-switch-lg" dir="ltr">
                                            <input type="checkbox" class="form-check-input" id="is_active"
                                                name="is_active" value="1"
                                                {{ old('is_active', $tour->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3" id="testing-credentials-required-field">
                                    <div class="mb-3">
                                        <label class="form-label" for="testing_is_credentials">Credentials
                                            Required</label>
                                        <div class="form-check form-switch form-switch-lg" dir="ltr">
                                            <input type="checkbox" class="form-check-input" id="testing_is_credentials"
                                                name="is_credentials" value="1"
                                                {{ old('is_credentials', $tour->is_credentials) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="testing_is_credentials">Required</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3" id="mobile-validation-field">
                                    <div class="mb-3">
                                        <label class="form-label" for="is_mobile_validation">Mobile Validation</label>
                                        <div class="form-check form-switch form-switch-lg" dir="ltr">
                                            <input type="checkbox" class="form-check-input" id="is_mobile_validation"
                                                name="is_mobile_validation" value="1"
                                                {{ old('is_mobile_validation', $tour->is_mobile_validation) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_mobile_validation">Required</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3" id="testing-is-hosted-field">
                                    <div class="mb-3">
                                        <label class="form-label" for="testing_is_hosted">Is Hosted</label>
                                        <div class="form-check form-switch form-switch-lg" dir="ltr">
                                            <input type="checkbox" class="form-check-input" id="testing_is_hosted"
                                                name="is_hosted" value="1"
                                                {{ old('is_hosted', $tour->is_hosted) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="testing_is_hosted">Hosted</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row {{ old('is_hosted', $tour->is_hosted) ? '' : 'd-none' }}"
                                id="testing-hosted-link-container">
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label class="form-label" for="testing_hosted_link">Hosted Link</label>
                                        <input type="url" name="hosted_link" id="testing_hosted_link"
                                            class="form-control" placeholder="e.g, https://example.com"
                                            value="{{ old('hosted_link', $tour->hosted_link) }}">
                                        <div class="invalid-feedback">Hosted Link is required when Is Hosted is enabled.
                                        </div>
                                        @error('hosted_link')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Credentials Section -->
                            <div id="testing-credentials-section"
                                class="mt-3 {{ old('is_credentials', $tour->is_credentials) ? '' : 'd-none' }}">
                                <h6 class="mb-3">Credentials Management</h6>
                                <div id="testing-credentials-container">
                                    @php
                                        $credentials = old('credentials', $tour->credentials->toArray() ?? []);
                                    @endphp

                                    @foreach($credentials as $index => $credential)
                                        <div class="credential-row row mb-2 align-items-end">
                                            <input type="hidden" name="credentials[{{ $index }}][id]"
                                                value="{{ $credential['id'] ?? '' }}">
                                            <div class="col-md-3">
                                                <label class="form-label">Username</label>
                                                <input type="text" name="credentials[{{ $index }}][user_name]"
                                                    class="form-control" value="{{ $credential['user_name'] }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Password</label>
                                                <input type="text" name="credentials[{{ $index }}][password]"
                                                    class="form-control" value="{{ $credential['password'] }}" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Status</label>
                                                <select name="credentials[{{ $index }}][is_active]" class="form-select">
                                                    <option value="1"
                                                        {{ ($credential['is_active'] ?? true) ? 'selected' : '' }}>Active
                                                    </option>
                                                    <option value="0"
                                                        {{ !($credential['is_active'] ?? true) ? 'selected' : '' }}>Inactive
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger remove-credential"><i
                                                        class="ri-delete-bin-line"></i></button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-success mt-2"
                                    id="testing-add-credential-btn">
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
                                <textarea name="content" id="tour_content" class="form-control"
                                    rows="5">{{ $tour->content }}</textarea>
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
                                        <input type="number" name="duration_days" id="tour_duration_days"
                                            class="form-control" value="{{ $tour->duration_days }}" min="1">
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
                                        <input type="number" name="max_participants" id="tour_max_participants"
                                            class="form-control" value="{{ $tour->max_participants }}" min="1">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="tour_featured_image">Featured Image URL</label>
                                        <input type="text" name="featured_image" id="tour_featured_image"
                                            class="form-control" value="{{ $tour->featured_image }}">
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
            </div>

            <!-- language Update Tab -->
            <div class="tab-pane fade {{ ($firstActiveTab === 'vl-pills-language') ? 'active show' : '' }}"
                id="vl-pills-language" role="tabpanel" aria-labelledby="vl-pills-language-tab">
                <!-- Language settings -->
                <div class="card border-1 shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Language</h4>
                    </div>
                    <div class="card-body">
                        <form id="languageTabUpdateForm" method="POST"
                            action="{{ route('admin.tours.updateTourLanguageTab', $tour) }}" class="needs-validation"
                            novalidate>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                            <div class="mb-3">
                                <label class="form-label">Enabled languages</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="enable_language[]"
                                            id="lang_english" value="en"
                                            {{ (is_array($tour->enable_language) && in_array('en', $tour->enable_language)) || (is_null($tour->enable_language) && in_array('en', old('enable_language', []))) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lang_english">English</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="enable_language[]"
                                            id="lang_hindi" value="hi"
                                            {{ (is_array($tour->enable_language) && in_array('hi', $tour->enable_language)) || in_array('hi', old('enable_language', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lang_hindi">Hindi</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="enable_language[]"
                                            id="lang_gujarati" value="gu"
                                            {{ (is_array($tour->enable_language) && in_array('gu', $tour->enable_language)) || in_array('gu', old('enable_language', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lang_gujarati">Gujarati</label>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">At least one language must be selected.</small>
                                @error('enable_language')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="default_language">Default language</label>
                                        <select name="default_language" id="default_language" class="form-select">
                                            <option value="">Select default language</option>
                                            <option value="en"
                                                {{ old('default_language', $tour->default_language) == 'en' ? 'selected' : '' }}>
                                                English
                                            </option>
                                            <option value="hi"
                                                {{ old('default_language', $tour->default_language) == 'hi' ? 'selected' : '' }}>
                                                Hindi
                                            </option>
                                            <option value="gu"
                                                {{ old('default_language', $tour->default_language) == 'gu' ? 'selected' : '' }}>
                                                Gujarati</option>
                                        </select>
                                        @error('default_language')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i> Update Language Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade {{ ($firstActiveTab === 'vl-pills-tour-contact-info') ? 'active show' : '' }}"
                id="vl-pills-tour-contact-info" role="tabpanel" aria-labelledby="vl-pills-tour-contact-info-tab">
                <div class="card border-1 shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Tour Contact Information</h4>
                    </div>
                    <div class="card-body">
                        <form id="tourContactInfoTabUpdateForm" method="POST"
                            action="{{ route('admin.tours.updateTourContactInfoTab', $tour) }}" class="needs-validation"
                            novalidate>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                            {{-- User Info Accordion --}}
                            <div class="accordion mb-3" id="userInfoAccordion">
                                <div class="accordion-item border-0">
                                    <div id="userInfoCollapse" class="accordion-collapse collapse show"
                                        data-bs-parent="#userInfoAccordion">
                                        <div class="accordion-body px-2 pt-3 pb-1">
                                            <div class="row g-3">
                                                {{-- User Name --}}
                                                <div class="col-lg-6">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="form-label mb-0" for="tour_contact_user_name">
                                                            User Name <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="text-muted small">Show</span>
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="show_contact_user_name"
                                                                    id="show_contact_user_name" value="1"
                                                                    {{ old('show_contact_user_name', $tour->show_contact_user_name ?? true) ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="text" name="contact_user_name"
                                                        id="tour_contact_user_name" class="form-control"
                                                        placeholder="e.g, User Name"
                                                        value="{{ old('contact_user_name', $tour->contact_user_name ?? '') }}">
                                                    <small class="text-muted">Display name for the user</small>
                                                    @error('contact_user_name')<div class="text-danger small">
                                                        {{ $message }}
                                                    </div>@enderror
                                                </div>

                                                {{-- Google Location --}}
                                                <div class="col-lg-6">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="form-label mb-0"
                                                            for="tour_contact_google_location">
                                                            Google Location <span class="text-muted">(optional)</span>
                                                        </label>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="text-muted small">Show</span>
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="show_contact_google_location"
                                                                    id="show_contact_google_location" value="1"
                                                                    {{ old('show_contact_google_location', $tour->show_contact_google_location ?? true) ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="text" name="contact_google_location"
                                                        id="tour_contact_google_location" class="form-control"
                                                        placeholder="e.g, https://www.google.com/maps"
                                                        value="{{ old('contact_google_location', $tour->contact_google_location ?? '') }}">
                                                    <small class="text-muted">Google Maps location URL or
                                                        address</small>
                                                    @error('contact_google_location')<div class="text-danger small">
                                                        {{ $message }}
                                                    </div>@enderror
                                                </div>

                                                {{-- Email --}}
                                                <div class="col-lg-6">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="form-label mb-0" for="tour_contact_email">
                                                            Email <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="text-muted small">Show</span>
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="show_contact_email" id="show_contact_email"
                                                                    value="1"
                                                                    {{ old('show_contact_email', $tour->show_contact_email ?? true) ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="email" name="contact_email" id="tour_contact_email"
                                                        class="form-control" placeholder="e.g, Email@gmail.com"
                                                        value="{{ old('contact_email', $tour->contact_email ?? '') }}">
                                                    <small class="text-muted">Contact email address</small>
                                                    @error('contact_email')<div class="text-danger small">{{ $message }}
                                                    </div>@enderror
                                                </div>

                                                {{-- Website --}}
                                                <div class="col-lg-6">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="form-label mb-0" for="tour_contact_website">
                                                            Website <span class="text-muted">(optional)</span>
                                                        </label>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="text-muted small">Show</span>
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="show_contact_website"
                                                                    id="show_contact_website" value="1"
                                                                    {{ old('show_contact_website', $tour->show_contact_website ?? false) ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="text" name="contact_website" id="tour_contact_website"
                                                        class="form-control"
                                                        placeholder="e.g, https://www.google.com/maps"
                                                        value="{{ old('contact_website', $tour->contact_website ?? '') }}">
                                                    <small class="text-muted">Website URL (http:// or https:// will be
                                                        added automatically if missing)</small>
                                                    @error('contact_website')<div class="text-danger small">
                                                        {{ $message }}
                                                    </div>@enderror
                                                </div>

                                                {{-- Phone Number --}}
                                                <div class="col-lg-6">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="form-label mb-0" for="tour_contact_phone_no">
                                                            Phone Number <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="text-muted small">Show</span>
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="show_contact_phone_no"
                                                                    id="show_contact_phone_no" value="1"
                                                                    {{ old('show_contact_phone_no', $tour->show_contact_phone_no ?? true) ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="text" name="contact_phone_no"
                                                        id="tour_contact_phone_no" class="form-control"
                                                        placeholder="e.g, +91 9876543210"
                                                        value="{{ old('contact_phone_no', $tour->contact_phone_no ?? '') }}">
                                                    <small class="text-muted">Contact phone number</small>
                                                    @error('contact_phone_no')<div class="text-danger small">
                                                        {{ $message }}
                                                    </div>@enderror
                                                </div>

                                                {{-- WhatsApp Number --}}
                                                <div class="col-lg-6">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="form-label mb-0" for="tour_contact_whatsapp_no">
                                                            WhatsApp Number <span class="text-muted">(optional)</span>
                                                        </label>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="text-muted small">Show</span>
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="show_contact_whatsapp_no"
                                                                    id="show_contact_whatsapp_no" value="1"
                                                                    {{ old('show_contact_whatsapp_no', $tour->show_contact_whatsapp_no ?? true) ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="text" name="contact_whatsapp_no"
                                                        id="tour_contact_whatsapp_no" class="form-control"
                                                        placeholder="e.g, +91 9876543210"
                                                        value="{{ old('contact_whatsapp_no', $tour->contact_whatsapp_no ?? '') }}">
                                                    <small class="text-muted">WhatsApp contact number (with country
                                                        code, e.g., +1234567890)</small>
                                                    @error('contact_whatsapp_no')<div class="text-danger small">
                                                        {{ $message }}
                                                    </div>@enderror
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i> Update Tour Contact Information
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Loader Configuration Tab -->
            <div class="tab-pane fade {{ ($firstActiveTab === 'vl-pills-loader-config') ? 'active show' : '' }}"
                id="vl-pills-loader-config" role="tabpanel" aria-labelledby="vl-pills-loader-config-tab">
                <!-- Loader configuration -->
                <div class="card border-1 shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Loader Configuration</h4>
                    </div>
                    <div class="card-body">
                        <form id="loaderConfigTabUpdateForm" method="POST"
                            action="{{ route('admin.tours.updateTourLoaderConfigTab', $tour) }}"
                            class="needs-validation" novalidate>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="overlay_bg_color">Overlay background
                                            color</label>
                                        <div class="input-group">
                                            <span class="input-group-text p-1">
                                                <input type="color" id="overlay_bg_color_picker"
                                                    class="form-control form-control-color"
                                                    value="{{ old('overlay_bg_color', $tour->overlay_bg_color ?? '#000040') }}"
                                                    onchange="document.getElementById('overlay_bg_color').value = this.value">
                                            </span>
                                            <input type="text" name="overlay_bg_color" id="overlay_bg_color"
                                                class="form-control" placeholder="#000040"
                                                oninput="this.previousElementSibling.querySelector('input').value = this.value"
                                                value="{{ old('overlay_bg_color', $tour->overlay_bg_color ?? '#000040') }}">
                                        </div>
                                        @error('overlay_bg_color')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="loader_text">Loader text</label>
                                        <input type="text" name="loader_text" id="loader_text" class="form-control"
                                            placeholder="Loading tour..."
                                            value="{{ old('loader_text', $tour->loader_text ?? '') }}">
                                        @error('loader_text')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="">Loader colors (gradient)</label>
                                @php
                                    $loaderColors = is_array($tour->loader_color) ? $tour->loader_color : [];
                                    if (empty($loaderColors)) {
                                        $loaderColors = ['#b47e37', '#d4a574', '#efd477'];
                                    }
                                @endphp
                                <div class="row g-2 mb-2 " id="loaderColorContainer">
                                    @foreach($loaderColors as $index => $color)
                                        <div class="col-md-2 loader-color-row">
                                            <div class="input-group">
                                                <span class="input-group-text p-1">
                                                    <input type="color"
                                                        class="form-control form-control-color loader-color-picker"
                                                        value="{{ $color }}"
                                                        onchange="this.parentElement.nextElementSibling.value = this.value">
                                                </span>
                                                <input type="text" name="loader_color[]"
                                                    class="form-control loader-color-input" placeholder="#000000"
                                                    value="{{ $color }}"
                                                    oninput="this.previousElementSibling.querySelector('input').value = this.value">
                                                <!-- <button type="button" class="btn btn-soft-danger remove-loader-color">
                                                                                                <i class="ri-delete-bin-line"></i>
                                                                                            </button> -->
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <!-- <button type="button" class="btn btn-soft-primary btn-sm mt-2" id="addLoaderColor">
                                <i class="ri-add-line"></i> Add Color
                            </button> -->
                                @error('loader_color')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="">Spinner colors (gradient)</label>

                                @php
                                    $spinnerColors = is_array($tour->spinner_color) ? $tour->spinner_color : [];
                                    if (empty($spinnerColors)) {
                                        $spinnerColors = ['#b47e37', '#d4a574', '#efd477'];
                                    }
                                @endphp
                                <div class="row g-2 mb-2" id="spinnerColorContainer">
                                    @foreach($spinnerColors as $index => $color)
                                        <div class="col-md-2 spinner-color-row">
                                            <div class="input-group">
                                                <span class="input-group-text p-1">
                                                    <input type="color"
                                                        class="form-control form-control-color spinner-color-picker"
                                                        value="{{ $color }}"
                                                        onchange="this.parentElement.nextElementSibling.value = this.value">
                                                </span>
                                                <input type="text" name="spinner_color[]"
                                                    class="form-control spinner-color-input" placeholder="#000000"
                                                    value="{{ $color }}"
                                                    oninput="this.previousElementSibling.querySelector('input').value = this.value">
                                                <!-- <button type="button" class="btn btn-soft-danger remove-spinner-color">
                                                                                                <i class="ri-delete-bin-line"></i>
                                                                                            </button> -->
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <!-- <button type="button" class="btn btn-soft-primary btn-sm mt-2" id="addSpinnerColor">
                                <i class="ri-add-line"></i> Add Color
                            </button> -->
                                @error('spinner_color')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end mt-4">
                                        <button class="btn btn-primary" type="submit" id="tourSettingsSubmitBtn">
                                            <i class="ri-save-line me-1"></i> Update Loader Configuration
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
                        <!-- nav links for the sidebar tabls -->
                        <ul class="nav nav-tabs mb-3" id="tourAttachmentsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="sidebar-tab-1-tab" data-bs-toggle="tab"
                                    data-bs-target="#sidebar-tab-1-pane" type="button" role="tab"
                                    aria-controls="tour-attachment-1-pane" aria-selected="true">Sidebar Details</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="sidebar-2-tab" data-bs-toggle="tab"
                                    data-bs-target="#sidebar-tab-2-pane" type="button" role="tab"
                                    aria-controls="tour-attachment-2-pane" aria-selected="false">Sidebar Links</button>
                            </li>
                        </ul>

                        <!-- tab Content div -->
                        <div class="tab-content" id="sidebarTabContent">
                            <div class="tab-pane fade show active" id="sidebar-tab-1-pane" role="tabpanel"
                                aria-labelledby="sidebar-tab-1-tab" tabindex="0">
                                <form id="sidebarTabUpdateForm" method="POST"
                                    action="{{ route('admin.tours.updateTourSidebarTab', $tour) }}"
                                    enctype="multipart/form-data" class="needs-validation" novalidate>
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
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
                                                    <input type="file" name="sidebar_logo" id="sidebar_logo" @if (!$qr_code) disabled @endif class="form-control"
                                                        accept="image/*" onchange="previewImage(event, 'sidebar_logo')">
                                                </div>
                                                @error('sidebar_logo')<div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <h5 class="mb-3">Sidebar Tag <span class="text-muted">(optional)</span></h5>
                                            <p class="text-muted mb-3">Vertical tag on the right side of the sidebar.
                                                Leave
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
                                                    <input type="text" name="sidebar_tag_bg_color"
                                                        id="sidebar_tag_bg_color" class="form-control"
                                                        placeholder="e.g. #ff000d"
                                                        oninput="document.getElementById('sidebar_tag_bg_color_picker').value = this.value"
                                                        value="{{ old('sidebar_tag_bg_color', $tour->sidebar_tag_bg_color ?? '#ff000d') }}">
                                                </div>
                                                @error('sidebar_tag_bg_color')<div class="text-danger">{{ $message }}
                                                    </div>
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
                                                @error('made_by_text')<div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="made_by_link" class="form-label">Made by link <span
                                                        class="text-danger">*</span></label>
                                                <input type="url" name="sidebar_footer_link" id="made_by_link"
                                                    class="form-control"
                                                    placeholder="e.g,   https://proppik.com/contact"
                                                    value="{{ old('sidebar_footer_link', $tour->sidebar_footer_link ?? '') }}">
                                                @error('made_by_link')<div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line me-1"></i> Update Sidebar Section
                                        </button>
                                    </div>
                                </form>
                            </div><!-- first tab end -->
                            <div class="tab-pane fade show" id="sidebar-tab-2-pane" role="tabpanel"
                                aria-labelledby="sidebar-tab-2-tab" tabindex="0">
                                <form action="{{ route('admin.tours.updateSidebarLinks', $tour) }}" method="POST" id="sidebarLinksForm" class="needs-validation" novalidate>
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                    <div class="row">
                                        <!-- sidebar link fields -->
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="sidebar_links" class="form-label fs-5">Sidebar Links</label>
                                                <div class="container" id="sidebarLinksRow">
                                                    <!-- Filled by the js -->
                                                </div>
                                                <div>
                                                    <button type="button" id="addSideLinkBtn" class="btn btn-secondary">
                                                        Add Sidebar Link
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ri-save-line me-1"></i> Update Sidebar Section
                                            </button>
                                        </div>
                                    </div><!-- Row end -->
                                </form><!-- Form end -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- icon modal of the material icon -->
            <!-- Modal -->
            <div class="modal fade w-100" id="materialIconModal">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5>Select Icon</h5>
                            <button class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="text" id="materialIconSearch" class="form-control mb-3"
                                placeholder="Search...">
                            <div id="iconContainer" class="icon-grid"></div>
                        </div>
                    </div>
                </div>
            </div> <!-- modal end -->

            <div class="tab-pane fade {{ ($firstActiveTab === 'vl-pills-attachments') ? 'active show' : '' }}"
                id="vl-pills-attachments" role="tabpanel" aria-labelledby="vl-pills-attachments-tab">
                <div class="card border-1 shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Attachments</h4>
                    </div>
                    <div class="card-body">
                        <form id="attachmentsTabUpdateForm" method="POST"
                            action="{{ route('admin.tours.updateTourAttachmentsTab', $tour) }}"
                            enctype="multipart/form-data" class="needs-validation" novalidate>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                            <div class="mb-3 d-flex gap-2 align-items-center">
                                <label class="form-label" for="document_auth_required">Downloard Auth Require</label>
                                <div class="form-check form-switch form-switch-lg">
                                    <input type="hidden" name="document_auth_required" value="0">
                                    <input type="checkbox" class="form-check-input" id="document_auth_required"
                                        name="document_auth_required" value="1"
                                        {{ old('document_auth_required', $tour->document_auth_required) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="document_auth_required">Active</label>
                                </div>
                            </div>

                            <ul class="nav nav-tabs mb-3" id="tourAttachmentsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tour-attachment-1-tab" data-bs-toggle="tab"
                                        data-bs-target="#tour-attachment-1-pane" type="button" role="tab"
                                        aria-controls="tour-attachment-1-pane" aria-selected="true">Attachment
                                        1</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tour-attachment-2-tab" data-bs-toggle="tab"
                                        data-bs-target="#tour-attachment-2-pane" type="button" role="tab"
                                        aria-controls="tour-attachment-2-pane" aria-selected="false">Attachment
                                        2</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="tourAttachmentsTabsContent">
                                @php
                                    $attachment1 = isset($tour->attachment_file[0]) ? $tour->attachment_file[0] : null;
                                @endphp
                                <div class="tab-pane fade show active" id="tour-attachment-1-pane" role="tabpanel"
                                    aria-labelledby="tour-attachment-1-tab" tabindex="0">
                                    <h6 class="mb-3">Attachment 1 (Image, Video, or Document)</h6>

                                    <div class="mb-3">
                                        <label class="form-label">Type <span
                                                class="text-muted">(optional)</span></label>
                                        <div class="d-flex flex-wrap gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="attachment_file[0][type]" id="tour_attachment_0_type_image"
                                                    value="image"
                                                    {{ old('attachment_file.0.type', $attachment1['documentType'] ?? 'image') == 'image' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="tour_attachment_0_type_image">Image</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="attachment_file[0][type]" id="tour_attachment_0_type_video"
                                                    value="video"
                                                    {{ old('attachment_file.0.type', $attachment1['documentType'] ?? '') == 'video' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="tour_attachment_0_type_video">Video</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="attachment_file[0][type]" id="tour_attachment_0_type_document"
                                                    value="document"
                                                    {{ old('attachment_file.0.type', $attachment1['documentType'] ?? '') == 'document' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="tour_attachment_0_type_document">Document</label>
                                            </div>
                                        </div>
                                        @error('attachment_file.0.type')<div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="tour_attachment_0_tooltip">Tooltip <span
                                                class="text-muted">(optional)</span></label>
                                        <input type="text" name="attachment_file[0][tooltip]"
                                            id="tour_attachment_0_tooltip" class="form-control"
                                            placeholder="e.g., Tour Brochure"
                                            value="{{ old('attachment_file.0.tooltip', $attachment1['documentTooltip'] ?? '') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="tour_attachment_0_link">Link URL <span
                                                class="text-muted">(optional)</span></label>
                                        <input type="url" name="attachment_file[0][link]" id="tour_attachment_0_link"
                                            class="form-control"
                                            placeholder="e.g, http://www.example.com/assets/image.jpeg"
                                            value="{{ old('attachment_file.0.link', $attachment1['documentUrl'] ?? '') }}">
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label mb-0" for="show_document_url">Show Attachment 1
                                                URL</label>
                                            <div class="form-check form-switch mb-0">
                                                <input type="hidden" name="show_document_url" value="0">
                                                <input class="form-check-input" type="checkbox" name="show_document_url"
                                                    id="show_document_url" value="1"
                                                    {{ old('show_document_url', $tour->show_document_url ?? true) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                        <small class="text-muted">Controls visibility of attachment 1 URL in tour
                                            data.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="tour_attachment_0_file">Or Upload File <span
                                                class="text-muted">(optional)</span></label>
                                        <input type="file" name="attachment_file[0][file]" id="tour_attachment_0_file"
                                            class="form-control" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Action <span
                                                class="text-muted">(optional)</span></label>
                                        <div class="d-flex flex-wrap gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="attachment_file[0][action]"
                                                    id="tour_attachment_0_action_modal" value="modal"
                                                    {{ old('attachment_file.0.action', $attachment1['documentAction'] ?? 'modal') == 'modal' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="tour_attachment_0_action_modal">View in modal</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="attachment_file[0][action]"
                                                    id="tour_attachment_0_action_download" value="download"
                                                    {{ old('attachment_file.0.action', $attachment1['documentAction'] ?? '') == 'download' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="tour_attachment_0_action_download">Download</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $attachment2 = isset($tour->attachment_file[1]) ? $tour->attachment_file[1] : null;
                                @endphp
                                <div class="tab-pane fade" id="tour-attachment-2-pane" role="tabpanel"
                                    aria-labelledby="tour-attachment-2-tab" tabindex="0">
                                    <h6 class="mb-3">Attachment 2 (Image, Video, or Document)</h6>

                                    <div class="mb-3">
                                        <label class="form-label">Type <span
                                                class="text-muted">(optional)</span></label>
                                        <div class="d-flex flex-wrap gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="attachment_file[1][type]" id="tour_attachment_1_type_image"
                                                    value="image"
                                                    {{ old('attachment_file.1.type', $attachment2['documentType'] ?? 'image') == 'image' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="tour_attachment_1_type_image">Image</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="attachment_file[1][type]" id="tour_attachment_1_type_video"
                                                    value="video"
                                                    {{ old('attachment_file.1.type', $attachment2['documentType'] ?? '') == 'video' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="tour_attachment_1_type_video">Video</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="attachment_file[1][type]" id="tour_attachment_1_type_document"
                                                    value="document"
                                                    {{ old('attachment_file.1.type', $attachment2['documentType'] ?? '') == 'document' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="tour_attachment_1_type_document">Document</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="tour_attachment_1_tooltip">Tooltip <span
                                                class="text-muted">(optional)</span></label>
                                        <input type="text" name="attachment_file[1][tooltip]"
                                            id="tour_attachment_1_tooltip" class="form-control"
                                            placeholder="e.g., Property Documents"
                                            value="{{ old('attachment_file.1.tooltip', $attachment2['documentTooltip'] ?? '') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="tour_attachment_1_link">Link URL <span
                                                class="text-muted">(optional)</span></label>
                                        <input type="url" name="attachment_file[1][link]" id="tour_attachment_1_link"
                                            class="form-control"
                                            placeholder="e.g, http://www.example.com/assets/image.jpeg"
                                            value="{{ old('attachment_file.1.link', $attachment2['documentUrl'] ?? '') }}">
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label mb-0" for="show_document_url2">Show Attachment 2
                                                URL</label>
                                            <div class="form-check form-switch mb-0">
                                                <input type="hidden" name="show_document_url2" value="0">
                                                <input class="form-check-input" type="checkbox"
                                                    name="show_document_url2" id="show_document_url2" value="1"
                                                    {{ old('show_document_url2', $tour->show_document_url2 ?? true) ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                        <small class="text-muted">Controls visibility of attachment 2 URL in tour
                                            data.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="tour_attachment_1_file">Or Upload File <span
                                                class="text-muted">(optional)</span></label>
                                        <input type="file" name="attachment_file[1][file]" id="tour_attachment_1_file"
                                            class="form-control" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Action <span
                                                class="text-muted">(optional)</span></label>
                                        <div class="d-flex flex-wrap gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="attachment_file[1][action]"
                                                    id="tour_attachment_1_action_modal" value="modal"
                                                    {{ old('attachment_file.1.action', $attachment2['documentAction'] ?? 'modal') == 'modal' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="tour_attachment_1_action_modal">View in modal</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="attachment_file[1][action]"
                                                    id="tour_attachment_1_action_download" value="download"
                                                    {{ old('attachment_file.1.action', $attachment2['documentAction'] ?? '') == 'download' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="tour_attachment_1_action_download">Download</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i> Update Attachments
                                </button>
                            </div>
                        </form>
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
                        <form id="bottomTopTabUpdateForm" method="POST"
                            action="{{ route('admin.tours.updateTourBottomTopTab', $tour) }}"
                            enctype="multipart/form-data" class="needs-validation" novalidate>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="mt-2">
                                            @if($tour->footer_logo)
                                                <img id="footer_logo_preview"
                                                    src="{{ Storage::disk('s3')->url($tour->footer_logo) }}"
                                                    data-original-src="{{ Storage::disk('s3')->url($tour->footer_logo) }}"
                                                    alt="Footer Logo"
                                                    style="max-width: 150px; max-height: 80px; border:1px solid #ddd; background:#fff; padding:2px;">
                                            @else
                                                <img id="footer_logo_preview" src="" data-original-src="" alt="Footer Logo"
                                                    style="max-width: 150px; max-height: 80px; border:1px solid #ddd; background:#fff; padding:2px; display:none;">
                                            @endif
                                        </div>
                                        <div>
                                            <label class="form-label" for="footer_logo">Top Image</label>
                                            <input type="file" name="footer_logo" id="footer_logo" @if (!$qr_code)
                                            disabled @endif class="form-control" accept="image/*">
                                        </div>
                                        @error('footer_logo')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Language tabs -->
                            <ul class="nav nav-tabs mb-3" id="testingFooterLanguageTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="testing-footer-lang-english-tab"
                                        data-language="en" data-bs-toggle="tab"
                                        data-bs-target="#testing-footer-lang-english-pane" type="button" role="tab"
                                        aria-controls="testing-footer-lang-english-pane" aria-selected="true">
                                        <span class="badge bg-success me-2">✓</span>English
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="testing-footer-lang-gujarati-tab" data-language="gu"
                                        data-bs-toggle="tab" data-bs-target="#testing-footer-lang-gujarati-pane"
                                        type="button" role="tab" aria-controls="testing-footer-lang-gujarati-pane"
                                        aria-selected="false">
                                        <span class="badge bg-success me-2">✓</span>Gujarati
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="testing-footer-lang-hindi-tab" data-language="hi"
                                        data-bs-toggle="tab" data-bs-target="#testing-footer-lang-hindi-pane"
                                        type="button" role="tab" aria-controls="testing-footer-lang-hindi-pane"
                                        aria-selected="false">
                                        <span class="badge bg-success me-2">✓</span>Hindi
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="testingFooterLanguageTabsContent">
                                <div class="tab-pane fade show active" id="testing-footer-lang-english-pane"
                                    data-language="en" role="tabpanel" aria-labelledby="testing-footer-lang-english-tab"
                                    tabindex="0">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="footer_title_en" class="form-label">Top Title (English)
                                                    <span class="text-danger">*</span></label>
                                                <input type="text" name="footer_title[en]" id="footer_title_en"
                                                    class="form-control" placeholder="e.g, Ramesh Mehta"
                                                    value="{{ old('footer_title.en', data_get($tour, 'footer_title.en', '')) }}">
                                                @error('footer_title.en')<div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="footer_subtitle_en" class="form-label">Top Sub Title
                                                    (English) <span class="text-danger">*</span></label>
                                                <input type="text" name="footer_subtitle[en]" id="footer_subtitle_en"
                                                    class="form-control" placeholder="e.g, JK Real Estate"
                                                    value="{{ old('footer_subtitle.en', data_get($tour, 'footer_subtitle.en', '')) }}">
                                                @error('footer_subtitle.en')<div class="text-danger">{{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="footer_decription_en" class="form-label">Top Description
                                                    (English) <span class="text-muted">(optional)</span></label>
                                                <textarea name="footer_decription[en]" id="footer_decription_en"
                                                    class="form-control"
                                                    placeholder="e.g, For Reant / For Sell / For Lease"
                                                    rows="2">{{ old('footer_decription.en', data_get($tour, 'footer_decription.en', '')) }}</textarea>
                                                @error('footer_decription.en')<div class="text-danger">{{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="testing-footer-lang-gujarati-pane" data-language="gu"
                                    role="tabpanel" aria-labelledby="testing-footer-lang-gujarati-tab" tabindex="0">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="footer_title_gu" class="form-label">Top Title (Gujarati)
                                                    <span class="text-danger">*</span></label>
                                                <input type="text" name="footer_title[gu]" id="footer_title_gu"
                                                    class="form-control" placeholder="e.g, રમેશ મહતા"
                                                    value="{{ old('footer_title.gu', data_get($tour, 'footer_title.gu', '')) }}">
                                                @error('footer_title.gu')<div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="footer_subtitle_gu" class="form-label">Top Sub Title
                                                    (Gujarati) <span class="text-danger">*</span></label>
                                                <input type="text" name="footer_subtitle[gu]" id="footer_subtitle_gu"
                                                    class="form-control" placeholder="e.g, જે કે રીયલ એસ્ટેટ"
                                                    value="{{ old('footer_subtitle.gu', data_get($tour, 'footer_subtitle.gu', '')) }}">
                                                @error('footer_subtitle.gu')<div class="text-danger">{{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="footer_decription_gu" class="form-label">Top Description
                                                    (Gujarati) <span class="text-muted">(optional)</span></label>
                                                <textarea name="footer_decription[gu]" id="footer_decription_gu"
                                                    class="form-control"
                                                    placeholder="e.g, ભાડે માટે / વેચવા માટે / ભાડે માટે"
                                                    rows="2">{{ old('footer_decription.gu', data_get($tour, 'footer_decription.gu', '')) }}</textarea>
                                                @error('footer_decription.gu')<div class="text-danger">{{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="testing-footer-lang-hindi-pane" data-language="hi"
                                    role="tabpanel" aria-labelledby="testing-footer-lang-hindi-tab" tabindex="0">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="footer_title_hi" class="form-label">Top Title (Hindi) <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="footer_title[hi]" id="footer_title_hi"
                                                    class="form-control" placeholder="e.g, रमेश मेहता"
                                                    value="{{ old('footer_title.hi', data_get($tour, 'footer_title.hi', '')) }}">
                                                @error('footer_title.hi')<div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="footer_subtitle_hi" class="form-label">Top Sub Title (Hindi)
                                                    <span class="text-danger">*</span></label>
                                                <input type="text" name="footer_subtitle[hi]" id="footer_subtitle_hi"
                                                    class="form-control" placeholder="e.g, जे के रीयल एस्टेट"
                                                    value="{{ old('footer_subtitle.hi', data_get($tour, 'footer_subtitle.hi', '')) }}">
                                                @error('footer_subtitle.hi')<div class="text-danger">{{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="footer_decription_hi" class="form-label">Top Description
                                                    (Hindi) <span class="text-muted">(optional)</span></label>
                                                <textarea name="footer_decription[hi]" id="footer_decription_hi"
                                                    class="form-control"
                                                    placeholder="e.g, किराए के लिए / बिक्री के लिए / पट्टे के लिए"
                                                    rows="2">{{ old('footer_decription.hi', data_get($tour, 'footer_decription.hi', '')) }}</textarea>
                                                @error('footer_decription.hi')<div class="text-danger">{{ $message }}
                                                </div>@enderror
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
                                            value="{{ old('footer_email', $tour->footer_email) }}"
                                            placeholder="e.g, Contact@example.com">
                                        @error('footer_email')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="footer_mobile" class="form-label">Contact Number</label>
                                        <input type="text" name="footer_mobile" id="footer_mobile" class="form-control"
                                            value="{{ old('footer_mobile', $tour->footer_mobile) }}"
                                            placeholder="eg.+91 9898 363026">
                                        @error('footer_mobile')<div class="text-danger">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i> Update Bottom Top Section
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!--  Bottom Mark: Property Details  -->
            <div class="tab-pane fade {{ ($firstActiveTab === 'vl-pills-bottom-mark-property') ? 'active show' : '' }}"
                id="vl-pills-bottom-mark-property" role="tabpanel" aria-labelledby="vl-pills-bottom-mark-property-tab">
                <!-- Bottommark Multilingual Fields Section -->
                <div class="card border-1 shadow-sm ">
                    <div class="card-header">
                        <h4 class="card-title">Bottom Mark: Property Details</h4>
                    </div>
                    <div class="card-body">
                        <form id="bottomPropertyTabUpdateForm" method="POST"
                            action="{{ route('admin.tours.updateTourBottomPropertyTab', $tour) }}"
                            class="needs-validation" novalidate>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                            <p class="text-muted mb-3">Add property details in multiple languages</p>
                            <!-- Language tabs -->
                            <ul class="nav nav-tabs mb-3" id="testingBottommarkLanguageTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="testing-bottommark-lang-english-tab"
                                        data-language="en" data-bs-toggle="tab"
                                        data-bs-target="#testing-bottommark-lang-english-pane" type="button" role="tab"
                                        aria-controls="testing-bottommark-lang-english-pane" aria-selected="true">
                                        English
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="testing-bottommark-lang-gujarati-tab"
                                        data-language="gu" data-bs-toggle="tab"
                                        data-bs-target="#testing-bottommark-lang-gujarati-pane" type="button" role="tab"
                                        aria-controls="testing-bottommark-lang-gujarati-pane" aria-selected="false">
                                        Gujarati
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="testing-bottommark-lang-hindi-tab" data-language="hi"
                                        data-bs-toggle="tab" data-bs-target="#testing-bottommark-lang-hindi-pane"
                                        type="button" role="tab" aria-controls="testing-bottommark-lang-hindi-pane"
                                        aria-selected="false">
                                        Hindi
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="testingBottommarkLanguageTabsContent">
                                <!-- English Tab -->
                                <div class="tab-pane fade show active" id="testing-bottommark-lang-english-pane"
                                    data-language="en" role="tabpanel"
                                    aria-labelledby="testing-bottommark-lang-english-tab" tabindex="0">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="bottommark_property_name_en" class="form-label">Property
                                                    Name</label>
                                                <input type="text" name="bottommark_property_name_en"
                                                    id="bottommark_property_name_en" class="form-control"
                                                    placeholder="e.g., 3 BHK Apartment"
                                                    value="{{ old('bottommark_property_name_en', $tour->bottommark_property_name['en'] ?? '') }}">
                                                @error('bottommark_property_name_en')<div class="text-danger">
                                                    {{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="bottommark_room_type_en" class="form-label">Room
                                                    Type</label>
                                                <input type="text" name="bottommark_room_type_en"
                                                    id="bottommark_room_type_en" class="form-control"
                                                    placeholder="e.g., Residential"
                                                    value="{{ old('bottommark_room_type_en', $tour->bottommark_room_type['en'] ?? '') }}">
                                                @error('bottommark_room_type_en')<div class="text-danger">{{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="bottommark_dimensions_en"
                                                    class="form-label">Dimensions</label>
                                                <input type="text" name="bottommark_dimensions_en"
                                                    id="bottommark_dimensions_en" class="form-control"
                                                    placeholder="e.g., 1200 sq ft"
                                                    value="{{ old('bottommark_dimensions_en', $tour->bottommark_dimensions['en'] ?? '') }}">
                                                @error('bottommark_dimensions_en')<div class="text-danger">
                                                    {{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gujarati Tab -->
                                <div class="tab-pane fade" id="testing-bottommark-lang-gujarati-pane" data-language="gu"
                                    role="tabpanel" aria-labelledby="testing-bottommark-lang-gujarati-tab" tabindex="0">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="bottommark_property_name_gu" class="form-label">Property
                                                    Name</label>
                                                <input type="text" name="bottommark_property_name_gu"
                                                    id="bottommark_property_name_gu" class="form-control"
                                                    placeholder="e.g., 3 BHK એપાર્ટમેન્ટ"
                                                    value="{{ old('bottommark_property_name_gu', $tour->bottommark_property_name['gu'] ?? '') }}">
                                                @error('bottommark_property_name_gu')<div class="text-danger">
                                                    {{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="bottommark_room_type_gu" class="form-label">Room
                                                    Type</label>
                                                <input type="text" name="bottommark_room_type_gu"
                                                    id="bottommark_room_type_gu" class="form-control"
                                                    placeholder="e.g., રહેણાંક"
                                                    value="{{ old('bottommark_room_type_gu', $tour->bottommark_room_type['gu'] ?? '') }}">
                                                @error('bottommark_room_type_gu')<div class="text-danger">{{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="bottommark_dimensions_gu"
                                                    class="form-label">Dimensions</label>
                                                <input type="text" name="bottommark_dimensions_gu"
                                                    id="bottommark_dimensions_gu" class="form-control"
                                                    placeholder="e.g., 1200 ચોક્સ ફૂટ"
                                                    value="{{ old('bottommark_dimensions_gu', $tour->bottommark_dimensions['gu'] ?? '') }}">
                                                @error('bottommark_dimensions_gu')<div class="text-danger">
                                                    {{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hindi Tab -->
                                <div class="tab-pane fade" id="testing-bottommark-lang-hindi-pane" data-language="hi"
                                    role="tabpanel" aria-labelledby="testing-bottommark-lang-hindi-tab" tabindex="0">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="bottommark_property_name_hi" class="form-label">Property
                                                    Name</label>
                                                <input type="text" name="bottommark_property_name_hi"
                                                    id="bottommark_property_name_hi" class="form-control"
                                                    placeholder="e.g., 3 BHK अपार्टमेंट"
                                                    value="{{ old('bottommark_property_name_hi', $tour->bottommark_property_name['hi'] ?? '') }}">
                                                @error('bottommark_property_name_hi')<div class="text-danger">
                                                    {{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="bottommark_room_type_hi" class="form-label">Room
                                                    Type</label>
                                                <input type="text" name="bottommark_room_type_hi"
                                                    id="bottommark_room_type_hi" class="form-control"
                                                    placeholder="e.g., आवासीय"
                                                    value="{{ old('bottommark_room_type_hi', $tour->bottommark_room_type['hi'] ?? '') }}">
                                                @error('bottommark_room_type_hi')<div class="text-danger">{{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="bottommark_dimensions_hi"
                                                    class="form-label">Dimensions</label>
                                                <input type="text" name="bottommark_dimensions_hi"
                                                    id="bottommark_dimensions_hi" class="form-control"
                                                    placeholder="e.g., 1200 वर्ग फुट"
                                                    value="{{ old('bottommark_dimensions_hi', $tour->bottommark_dimensions['hi'] ?? '') }}">
                                                @error('bottommark_dimensions_hi')<div class="text-danger">
                                                    {{ $message }}
                                                </div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i> Update Property Details
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@vite(['resources/js/pages/booking-tour-detail-update-tab.js', 'resources/js/pages/booking_edit_sidebarLink.js'])

<script>
    window.sidebarLinksData = {!! json_encode(old('sidebar_links', $tour->sidebar_links )) !!};
</script>