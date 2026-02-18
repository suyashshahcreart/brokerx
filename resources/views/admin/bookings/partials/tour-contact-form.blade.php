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

            <!-- Submit Button -->
            <div class="row">
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
                <button class="nav-link" id="attachment-2-tab" data-bs-toggle="tab" data-bs-target="#attachment-2-pane"
                    type="button" role="tab" aria-controls="attachment-2-pane" aria-selected="false">Attachment
                    2</button>
            </li>
        </ul>

        <div class="tab-content" id="tourContactAttachmentTabsContent">
            <div class="tab-pane fade show active" id="attachment-1-pane" role="tabpanel"
                aria-labelledby="attachment-1-tab" tabindex="0">
                <h6 class="mb-3">Attachment 1 (Image, Video, or Document)</h6>

                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_1_type"
                                id="attachment_1_type_image" value="image"
                                {{ old('attachment_1_type', $tour->attachment_1_type ?? 'image') == 'image' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_1_type_image">Image</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_1_type"
                                id="attachment_1_type_video" value="video"
                                {{ old('attachment_1_type', $tour->attachment_1_type ?? '') == 'video' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_1_type_video">Video</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_1_type"
                                id="attachment_1_type_document" value="document"
                                {{ old('attachment_1_type', $tour->attachment_1_type ?? '') == 'document' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_1_type_document">Document</label>
                        </div>
                    </div>
                    @error('attachment_1_type')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_1_file">Link URL</label>
                    <input type="text" name="attachment_1_url" id="attachment_1_file"
                    placeholder="e.g, https://example.com/assets/image.png"
                    class="form-control">
                    @error('attachment_1_file')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_1_file">Or Attachment file</label>
                    <input type="file" name="attachment_1_file" id="attachment_1_file" class="form-control">
                    @error('attachment_1_file')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_1_tooltip">Tooltip <span
                            class="text-danger">*</span></label>
                    <input type="text" name="attachment_1_tooltip" id="attachment_1_tooltip" class="form-control"
                        value="{{ old('attachment_1_tooltip', $tour->attachment_1_tooltip ?? '') }}">
                    @error('attachment_1_tooltip')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_1_icon">Button icon</label>
                    <select name="attachment_1_icon" id="attachment_1_icon" class="form-select">
                        <option value=""
                            {{ old('attachment_1_icon', $tour->attachment_1_icon ?? '') == '' ? 'selected' : '' }}>
                            Default (by type)</option>
                        <option value="image"
                            {{ old('attachment_1_icon', $tour->attachment_1_icon ?? '') == 'image' ? 'selected' : '' }}>
                            Image</option>
                        <option value="video"
                            {{ old('attachment_1_icon', $tour->attachment_1_icon ?? '') == 'video' ? 'selected' : '' }}>
                            Video</option>
                        <option value="document"
                            {{ old('attachment_1_icon', $tour->attachment_1_icon ?? '') == 'document' ? 'selected' : '' }}>
                            Document</option>
                    </select>
                    <small class="text-muted">Optional: icon shown on the attachment button in the tour. If not set,
                        defaults by type (image/video/document).</small>
                    @error('attachment_1_icon')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">When user clicks the button</label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_1_action"
                                id="attachment_1_action_modal" value="modal"
                                {{ old('attachment_1_action', $tour->attachment_1_action ?? 'modal') == 'modal' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_1_action_modal">View in modal</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_1_action"
                                id="attachment_1_action_download" value="download"
                                {{ old('attachment_1_action', $tour->attachment_1_action ?? '') == 'download' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_1_action_download">Download</label>
                        </div>
                    </div>
                    @error('attachment_1_action')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
            <!-- attached file 2 -->
            <div class="tab-pane fade" id="attachment-2-pane" role="tabpanel" aria-labelledby="attachment-2-tab"
                tabindex="0">
                <h6 class="mb-3">Attachment 2 (Image, Video, or Document)</h6>

                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_2_type"
                                id="attachment_2_type_image" value="image"
                                {{ old('attachment_2_type', $tour->attachment_2_type ?? 'image') == 'image' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_2_type_image">Image</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_2_type"
                                id="attachment_2_type_video" value="video"
                                {{ old('attachment_2_type', $tour->attachment_2_type ?? '') == 'video' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_2_type_video">Video</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_2_type"
                                id="attachment_2_type_document" value="document"
                                {{ old('attachment_2_type', $tour->attachment_2_type ?? '') == 'document' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_2_type_document">Document</label>
                        </div>
                    </div>
                    @error('attachment_2_type')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_2_file">Attachment file</label>
                    <input type="text" name="attachment_2_url" 
                    placeholder="e.g, https://example.com/assets/image.png"
                    id="attachment_2_file" class="form-control">
                    @error('attachment_2_file')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label" for="attachment_2_file">Or Attachment file</label>
                    <input type="file" name="attachment_2_file" id="attachment_2_file" class="form-control">
                    @error('attachment_2_file')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_2_tooltip">Tooltip <span
                            class="text-danger">*</span></label>
                    <input type="text" name="attachment_2_tooltip" id="attachment_2_tooltip" class="form-control"
                        value="{{ old('attachment_2_tooltip', $tour->attachment_2_tooltip ?? '') }}">
                    @error('attachment_2_tooltip')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_2_icon">Button icon</label>
                    <select name="attachment_2_icon" id="attachment_2_icon" class="form-select">
                        <option value=""
                            {{ old('attachment_2_icon', $tour->attachment_2_icon ?? '') == '' ? 'selected' : '' }}>
                            Default (by type)</option>
                        <option value="image"
                            {{ old('attachment_2_icon', $tour->attachment_2_icon ?? '') == 'image' ? 'selected' : '' }}>
                            Image</option>
                        <option value="video"
                            {{ old('attachment_2_icon', $tour->attachment_2_icon ?? '') == 'video' ? 'selected' : '' }}>
                            Video</option>
                        <option value="document"
                            {{ old('attachment_2_icon', $tour->attachment_2_icon ?? '') == 'document' ? 'selected' : '' }}>
                            Document</option>
                    </select>
                    <small class="text-muted">Optional: icon shown on the attachment button in the tour. If not set,
                        defaults by type (image/video/document).</small>
                    @error('attachment_2_icon')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">When user clicks the button</label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_2_action"
                                id="attachment_2_action_modal" value="modal"
                                {{ old('attachment_2_action', $tour->attachment_2_action ?? 'modal') == 'modal' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_2_action_modal">View in modal</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_2_action"
                                id="attachment_2_action_download" value="download"
                                {{ old('attachment_2_action', $tour->attachment_2_action ?? '') == 'download' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_2_action_download">Download</label>
                        </div>
                    </div>
                    @error('attachment_2_action')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>