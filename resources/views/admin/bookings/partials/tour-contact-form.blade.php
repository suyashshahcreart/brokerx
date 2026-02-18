<!-- Tour Contact Information Form -->
<form method="POST" id="contactInfoForm" action="{{ route('admin.tours.updateContactInfo', $tour) }}"
    enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    @method('PUT')

    <div class="card panel-card border-info border-top mb-3">
        <div class="card-header bg-primary-subtle border-primary">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0"><i class="ri-contacts-line"></i> Tour Contact Information</h4>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="contact_google_location">Google Location <span
                                class="text-muted">(optional)</span></label>
                        <input type="text" name="contact_google_location" id="contact_google_location"
                            class="form-control" placeholder="e.g., https://maps.google.com/?q=123+Main+St"
                            value="{{ old('contact_google_location', $tour->contact_google_location ?? '') }}">
                        @error('contact_google_location')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="contact_website">Website <span
                                class="text-muted">(optional)</span></label>
                        <input type="text" name="contact_website" id="contact_website" class="form-control"
                            placeholder="e.g, https://example.com"
                            value="{{ old('contact_website', $tour->contact_website ?? '') }}">
                        @error('contact_website')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="contact_email">Email <span
                                class="text-muted">(optional)</span></label>
                        <input type="email" name="contact_email" id="contact_email" class="form-control"
                            placeholder="e.g, contact@example.com"
                            value="{{ old('contact_email', $tour->contact_email ?? '') }}">
                        @error('contact_email')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="contact_phone_no">Phone Number <span
                                class="text-muted">(optional)</span></label>
                        <input type="text" name="contact_phone_no" id="contact_phone_no" class="form-control"
                            placeholder="e.g, +91 9876543210"
                            value="{{ old('contact_phone_no', $tour->contact_phone_no ?? '') }}">
                        @error('contact_phone_no')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="contact_whatsapp_no">WhatsApp Number <span
                                class="text-muted">(optional)</span></label>
                        <input type="text" name="contact_whatsapp_no" id="contact_whatsapp_no" class="form-control"
                            placeholder="e.g, +91 9876543210"
                            value="{{ old('contact_whatsapp_no', $tour->contact_whatsapp_no ?? '') }}">
                        @error('contact_whatsapp_no')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attachments -->
    <div class="card panel-card border-info border-top mb-3">
        <div class="card-header bg-primary-subtle border-primary">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0"><i class="ri-attachment-line"></i> Attachments</h4>
            </div>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="tourContactAttachmentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="attachment-1-tab" data-bs-toggle="tab"
                        data-bs-target="#attachment-1-pane" type="button" role="tab" aria-controls="attachment-1-pane"
                        aria-selected="true">Attachment 1</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="attachment-2-tab" data-bs-toggle="tab"
                        data-bs-target="#attachment-2-pane" type="button" role="tab" aria-controls="attachment-2-pane"
                        aria-selected="false">Attachment
                        2</button>
                </li>
            </ul>

            <div class="tab-content" id="tourContactAttachmentTabsContent">
                @php
                    $attachment1 = isset($tour->attachment_file[0]) ? $tour->attachment_file[0] : null;
                @endphp
                <div class="tab-pane fade show active" id="attachment-1-pane" role="tabpanel"
                    aria-labelledby="attachment-1-tab" tabindex="0">
                    <h6 class="mb-3">Attachment 1 (Image, Video, or Document)</h6>

                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-muted">(optional)</span></label>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attachment_file[0][type]"
                                    id="attachment_0_type_image" value="image"
                                    {{ old('attachment_file.0.type', $attachment1['documentType'] ?? 'image') == 'image' ? 'checked' : '' }}>
                                <label class="form-check-label" for="attachment_0_type_image">Image</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attachment_file[0][type]"
                                    id="attachment_0_type_video" value="video"
                                    {{ old('attachment_file.0.type', $attachment1['documentType'] ?? '') == 'video' ? 'checked' : '' }}>
                                <label class="form-check-label" for="attachment_0_type_video">Video</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attachment_file[0][type]"
                                    id="attachment_0_type_document" value="document"
                                    {{ old('attachment_file.0.type', $attachment1['documentType'] ?? '') == 'document' ? 'checked' : '' }}>
                                <label class="form-check-label" for="attachment_0_type_document">Document</label>
                            </div>
                        </div>
                        @error('attachment_file.0.type')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="attachment_0_tooltip">Tooltip <span class="text-muted">(optional)</span></label>
                        <input type="text" name="attachment_file[0][tooltip]" id="attachment_0_tooltip" class="form-control"
                            placeholder="e.g., Tour Brochure"
                            value="{{ old('attachment_file.0.tooltip', $attachment1['documentTooltip'] ?? '') }}">
                        @error('attachment_file.0.tooltip')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="attachment_0_file">Link URL <span class="text-muted">(optional)</span></label>
                        <input type="url" name="attachment_file[0][link]" id="attachment_0_link" class="form-control"
                        placeholder="e.g, http://www.example.com/assets/image.jpeg"
                        value="{{ old('attachment_file.1.link', $attachment2['documentLink'] ?? '') }}"
                            accept="url">
                        @error('attachment_file.0.link')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                   
                    <div class="mb-3">
                        <label class="form-label" for="attachment_0_file">Or Upload File <span class="text-muted">(optional)</span></label>
                        <input type="file" name="attachment_file[0][file]" id="attachment_0_file" class="form-control"
                            accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">
                        <small class="text-muted">Supported: Images, Videos, PDF, Word, Excel documents (Max 10MB)</small>
                        @error('attachment_file.0.file')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Action <span class="text-muted">(optional)</span></label>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attachment_file[0][action]"
                                    id="attachment_0_action_modal" value="modal"
                                    {{ old('attachment_file.0.action', $attachment1['documentAction'] ?? 'modal') == 'modal' ? 'checked' : '' }}>
                                <label class="form-check-label" for="attachment_0_action_modal">View in modal</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attachment_file[0][action]"
                                    id="attachment_0_action_download" value="download"
                                    {{ old('attachment_file.0.action', $attachment1['documentAction'] ?? '') == 'download' ? 'checked' : '' }}>
                                <label class="form-check-label" for="attachment_0_action_download">Download</label>
                            </div>
                        </div>
                        @error('attachment_file.0.action')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    @if($attachment1 && isset($attachment1['documentFileName']))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <strong>Current File:</strong> {{ $attachment1['documentFileName'] }}
                        @if(isset($attachment1['documentUrl']))
                            <a href="{{ asset($attachment1['documentUrl']) }}" target="_blank" class="ms-2">
                                <i class="ri-external-link-line"></i> View
                            </a>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                </div>
                <!-- attached file 2 -->
                @php
                    $attachment2 = isset($tour->attachment_file[1]) ? $tour->attachment_file[1] : null;
                @endphp
                <div class="tab-pane fade" id="attachment-2-pane" role="tabpanel" aria-labelledby="attachment-2-tab"
                    tabindex="0">
                    <h6 class="mb-3">Attachment 2 (Image, Video, or Document)</h6>

                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-muted">(optional)</span></label>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attachment_file[1][type]"
                                    id="attachment_1_type_image" value="image"
                                    {{ old('attachment_file.1.type', $attachment2['documentType'] ?? 'image') == 'image' ? 'checked' : '' }}>
                                <label class="form-check-label" for="attachment_1_type_image">Image</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attachment_file[1][type]"
                                    id="attachment_1_type_video" value="video"
                                    {{ old('attachment_file.1.type', $attachment2['documentType'] ?? '') == 'video' ? 'checked' : '' }}>
                                <label class="form-check-label" for="attachment_1_type_video">Video</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attachment_file[1][type]"
                                    id="attachment_1_type_document" value="document"
                                    {{ old('attachment_file.1.type', $attachment2['documentType'] ?? '') == 'document' ? 'checked' : '' }}>
                                <label class="form-check-label" for="attachment_1_type_document">Document</label>
                            </div>
                        </div>
                        @error('attachment_file.1.type')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="attachment_1_tooltip">Tooltip <span class="text-muted">(optional)</span></label>
                        <input type="text" name="attachment_file[1][tooltip]" id="attachment_1_tooltip" class="form-control"
                            placeholder="e.g., Property Documents"
                            value="{{ old('attachment_file.1.tooltip', $attachment2['documentTooltip'] ?? '') }}">
                        @error('attachment_file.1.tooltip')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="attachment_0_file">Link URL <span class="text-muted">(optional)</span></label>
                        <input type="url" name="attachment_file[1][link]" id="attachment_1_link" class="form-control"
                        value="{{ old('attachment_file.1.link', $attachment2['documentLink'] ?? '') }}"
                        placeholder="e.g, http://www.example.com/assets/image.jpeg"
                            accept="url">
                        @error('attachment_file.0.link')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="attachment_1_file">Or Upload File <span class="text-muted">(optional)</span></label>
                        <input type="file" name="attachment_file[1][file]" id="attachment_1_file" class="form-control"
                            accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">
                        <small class="text-muted">Supported: Images, Videos, PDF, Word, Excel documents (Max 10MB)</small>
                        @error('attachment_file.1.file')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Action <span class="text-muted">(optional)</span></label>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attachment_file[1][action]"
                                    id="attachment_1_action_modal" value="modal"
                                    {{ old('attachment_file.1.action', $attachment2['documentAction'] ?? 'modal') == 'modal' ? 'checked' : '' }}>
                                <label class="form-check-label" for="attachment_1_action_modal">View in modal</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attachment_file[1][action]"
                                    id="attachment_1_action_download" value="download"
                                    {{ old('attachment_file.1.action', $attachment2['documentAction'] ?? '') == 'download' ? 'checked' : '' }}>
                                <label class="form-check-label" for="attachment_1_action_download">Download</label>
                            </div>
                        </div>
                        @error('attachment_file.1.action')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    @if($attachment2 && isset($attachment2['documentFileName']))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <strong>Current File:</strong> {{ $attachment2['documentFileName'] }}
                        @if(isset($attachment2['documentUrl']))
                            <a href="{{ asset($attachment2['documentUrl']) }}" target="_blank" class="ms-2">
                                <i class="ri-external-link-line"></i> View
                            </a>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                </div>

            </div>

            <!-- Submit Button at bottom of Attachments -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-primary" type="submit" id="contactInfoSubmitBtn">
                            <i class="ri-save-line me-1"></i> Update Contact Information
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>