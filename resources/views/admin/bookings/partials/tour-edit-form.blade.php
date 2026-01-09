{{-- Tour Edit Form --}}
<form method="POST" action="{{ route('admin.tours.updateTourDetails', $tour) }}" enctype="multipart/form-data"
    class="needs-validation" novalidate>
    @csrf
    @method('PUT')
    <input type="hidden" name="booking_id" value="{{ $booking->id }}">

    <!-- Basic Information -->
    <div class="card border-primary mb-3">
        <div class="card-header bg-primary-subtle border-primary">
            <h5 class="card-title mb-0"><i class="ri-list-indefinite"></i> Basic Information</h5>
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
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label" for="tour_revision">Revision</label>
                        <input type="text" name="revision" id="tour_revision" class="form-control"
                            value="{{ old('revision', $tour->revision) }}" placeholder="v1.0">
                        @error('revision')<div class="text-danger">{{ $message }}</div>@enderror
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
                <div class="col-lg-3">
                    <div class="mb-3">
                         <label class="form-label" for="is_credentials">Credentials Required</label>
                         <div class="form-check form-switch form-switch-lg" dir="ltr">
                             <input type="checkbox" class="form-check-input" id="is_credentials" name="is_credentials" value="1" {{ old('is_credentials', $tour->is_credentials) ? 'checked' : '' }}>
                             <label class="form-check-label" for="is_credentials">Required</label>
                         </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="mb-3">
                         <label class="form-label" for="is_mobile_validation">Mobile Validation</label>
                         <div class="form-check form-switch form-switch-lg" dir="ltr">
                             <input type="checkbox" class="form-check-input" id="is_mobile_validation" name="is_mobile_validation" value="1" {{ old('is_mobile_validation', $tour->is_mobile_validation) ? 'checked' : '' }}>
                             <label class="form-check-label" for="is_mobile_validation">Required</label>
                         </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="mb-3">
                         <label class="form-label" for="is_hosted">Is Hosted</label>
                         <div class="form-check form-switch form-switch-lg" dir="ltr">
                             <input type="checkbox" class="form-check-input" id="is_hosted" name="is_hosted" value="1" {{ old('is_hosted', $tour->is_hosted) ? 'checked' : '' }}>
                             <label class="form-check-label" for="is_hosted">Hosted</label>
                         </div>
                    </div>
                </div>
            </div>

            <div class="row {{ old('is_hosted', $tour->is_hosted) ? '' : 'd-none' }}" id="hosted-link-container">
                <div class="col-lg-12">
                    <div class="mb-3">
                        <label class="form-label" for="hosted_link">Hosted Link</label>
                        <input type="url" name="hosted_link" id="hosted_link" class="form-control" placeholder="https://example.com" value="{{ old('hosted_link', $tour->hosted_link) }}">
                        @error('hosted_link')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Credentials Section -->
            <div id="credentials-section" class="mt-3 {{ old('is_credentials', $tour->is_credentials) ? '' : 'd-none' }}">
                <h6 class="mb-3">Credentials Management</h6>
                <div id="credentials-container">
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
                <button type="button" class="btn btn-sm btn-success mt-2" id="add-credential-btn">
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

            <!-- Tour Thumbnail Upload -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="mb-3">
                        <label class="form-label" for="tour_thumbnail">Tour Thumbnail <span class="text-muted">(Image file, max 5MB)</span></label>
                        <div class="mt-2 mb-3">
                            @if($tour->tour_thumbnail)
                                <div>
                                    <small class="text-muted d-block mb-2">Current thumbnail:</small>
                                    <img src="{{ Storage::disk('s3')->url($tour->tour_thumbnail) }}" alt="Tour Thumbnail"
                                        style="max-width: 200px; max-height: 200px; border:1px solid #ddd; padding:5px; border-radius: 4px;">
                                </div>
                            @endif
                        </div>
                        <input type="file" name="tour_thumbnail" id="tour_thumbnail" class="form-control"
                            accept="image/*">
                        <small class="text-muted d-block mt-2">Recommended size: 400x300px. Uploaded to S3 in settings/tour_thumbnails/</small>
                        @error('tour_thumbnail')<div class="text-danger mt-2">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>



        </div>
    </div>

    <!-- sidebar section -->
    <div class="card panel-card border-info border-top mb-3">
        <div class="card-header bg-info-subtle border-info">
            <div>
                <h4 class="card-title mb-0"> <i class="ri-layout-left-line"></i> Sidebar section</h4>
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
                            <input type="file" name="sidebar_logo" id="custom_logo_sidebar" @if (!$qr_code) disabled @endif class="form-control"
                                accept="image/*" onchange="previewImage(event, 'sidebar_logo')">
                        </div>
                        @error('sidebar_logo')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-12 d-flex gap-3 align-items-center mb-3">
                    <div class="col-md-4">
                        <label for="sidebar_footer_link_show" class="form-label">Footer button Show</label>
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
                                <label for="sidebar_footer_text" class="form-label">Footer button Text</label>
                                <input type="text" name="sidebar_footer_text" id="sidebar_footer_text"
                                    class="form-control" placeholder="e.g, Designed By Prop Pik"
                                    value="{{ old('sidebar_footer_text', $tour->sidebar_footer_text) }}">
                                @error('sidebar_footer_text')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="sidebar_footer_link" class="form-label">Footer button Link</label>
                                <input type="text" name="sidebar_footer_link" id="sidebar_footer_link"
                                    class="form-control" placeholder="e.g, https://www.proppik.com/contact.html"
                                    value="{{ old('sidebar_footer_link', $tour->sidebar_footer_link) }}">
                                @error('sidebar_footer_link')<div class="text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- footer section -->
    <div class="card panel-card border-info border-top mt-3">
        <div class="card-header bg-warning-subtle border-warning">
            <h4 class="card-title mb-1"> <i class="ri-layout-row-line"></i> Bottom Mark: Top section</h4>
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
                <!-- <div class="col-md-6">
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
                </div> -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="footer_name" class="form-label">Top Title</label>
                        <input type="text" name="footer_name" id="footer_name" class="form-control" placeholder="e.g, Ramesh Mehta"
                            value="{{ old('footer_name', $tour->footer_name) }}">
                        @error('footer_name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="footer_subtitle" class="form-label">Top Subtitle</label>
                        <input type="text" name="footer_subtitle" id="footer_subtitle" class="form-control" placeholder="e.g, JK Real Estate"
                            value="{{ old('footer_subtitle', $tour->footer_subtitle) }}">
                        @error('footer_subtitle')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
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
                        <label for="footer_mobile" class="form-label">Contact Mobile</label>
                        <input type="text" name="footer_mobile" id="footer_mobile" class="form-control"
                            value="{{ old('footer_mobile', $tour->footer_mobile) }}" placeholder="eg.+91 9898 363026">
                        @error('footer_mobile')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="footer_decription" class="form-label">Top Description</label>
                        <textarea name="footer_decription" id="footer_decription" class="form-control"
                        placeholder="e.g, For Reant / For Sell / For Lease"
                            rows="2">{{ old('footer_decription', $tour->footer_decription) }}</textarea>
                        @error('footer_decription')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Brand section -->
    <div class="card panel-card border-info border-top mt-3">
        <div class="card-header bg-secondary-subtle border-secondary">
            <h4 class="card-title mb-1"> <i class="ri-cash-line"></i> Bottom Mark: Bottom section</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <div class="mt-2">
                            @if($tour->footer_brand_logo)
                                <img id="footer_brand_logo_preview"
                                    src="{{ Storage::disk('s3')->url($tour->footer_brand_logo) }}"
                                    data-original-src="{{ Storage::disk('s3')->url($tour->footer_brand_logo) }}"
                                    alt="Footer Brand Logo"
                                    style="max-width: 150px; max-height: 80px; border:1px solid #ddd; background:#fff; padding:2px;">
                            @else
                                <img id="footer_brand_logo_preview" src="" data-original-src="" alt="Footer Brand Logo"
                                    style="max-width: 150px; max-height: 80px; border:1px solid #ddd; background:#fff; padding:2px; display:none;">
                            @endif
                        </div>
                        <div>
                            <label class="form-label" for="footer_brand_logo">Brand Logo (Bottom) </label>
                            <input type="file" name="footer_brand_logo" id="footer_brand_logo" @if (!$qr_code) disabled @endif 
                            class="form-control" accept="image/*">
                        </div>
                        @error('footer_brand_logo')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="footer_brand_text" class="form-label">Contact Text</label>
                    <input type="text" name="footer_brand_text" id="footer_brand_text" class="form-control"
                        value="{{ old('footer_brand_text', $tour->footer_brand_text) }}"
                        placeholder="Designed By Prop pik"
                        >
                    @error('footer_brand_text')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="footer_brand_mobile" class="form-label">Contact Number</label>
                    <input type="text" name="footer_brand_mobile" id="footer_brand_mobile" class="form-control"
                        value="{{ old('footer_brand_mobile', $tour->footer_brand_mobile) }}"
                        placeholder="eg.+91 9898 363026"
                        >
                    @error('footer_brand_mobile')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Button of actions -->
    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">
            <i class="ri-save-line me-1"></i> Update Tour
        </button>
        <button class="btn btn-soft-danger" type="button" id="unlinkTourBtn">
            <i class="ri-link-unlink me-1"></i> Unlink Tour
        </button>
    </div>

</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle credentials section
        const isCredentials = document.getElementById('is_credentials');
        const credentialsSection = document.getElementById('credentials-section');
        
        isCredentials.addEventListener('change', function() {
            if(this.checked) {
                credentialsSection.classList.remove('d-none');
            } else {
                credentialsSection.classList.add('d-none');
            }
        });

        // Toggle hosted link section
        const isHosted = document.getElementById('is_hosted');
        const hostedLinkContainer = document.getElementById('hosted-link-container');

        isHosted.addEventListener('change', function() {
            if(this.checked) {
                hostedLinkContainer.classList.remove('d-none');
            } else {
                hostedLinkContainer.classList.add('d-none');
            }
        });

        // Add credential row
        const container = document.getElementById('credentials-container');
        const addBtn = document.getElementById('add-credential-btn');
        let credentialIndex = {{ count(old('credentials', $tour->credentials ?? [])) }};

        addBtn.addEventListener('click', function() {
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
            container.appendChild(row);
            credentialIndex++;
        });

        // Remove credential row
        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-credential')) {
                e.target.closest('.credential-row').remove();
            }
        });
    });
</script>