<!-- Tour contact settings -->
<div class="card panel-card border-info border-top mb-3">
    <div class="card-header bg-primary-subtle border-primary">
        <div class="d-flex align-items-center gap-2">
            <h4 class="card-title mb-0"><i class="ri-contacts-line"></i> Tour contact</h4>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12">
                <div class="mb-3">
                    <label class="form-label" for="tour_contact_google_location">Google Location <span class="text-muted">(optional)</span></label>
                    <input type="text" name="tour_contact_google_location" id="tour_contact_google_location" class="form-control"
                        placeholder="e.g., https://maps.google.com/?q=123+Main+St"
                        value="{{ old('tour_contact_google_location', $tour->tour_contact_google_location ?? '') }}">
                    <small class="text-muted">Google Maps location URL or address</small>
                    @error('tour_contact_google_location')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-lg-12">
                <div class="mb-3">
                    <label class="form-label" for="tour_contact_website">Website <span class="text-muted">(optional)</span></label>
                    <input type="text" name="tour_contact_website" id="tour_contact_website" class="form-control"
                        placeholder="https://example.com"
                        value="{{ old('tour_contact_website', $tour->tour_contact_website ?? '') }}">
                    <small class="text-muted">Website URL (http:// or https:// will be added automatically if missing)</small>
                    @error('tour_contact_website')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-lg-12">
                <div class="mb-3">
                    <label class="form-label" for="tour_contact_email">Email <span class="text-muted">(optional)</span></label>
                    <input type="email" name="tour_contact_email" id="tour_contact_email" class="form-control"
                        placeholder="user@example.com"
                        value="{{ old('tour_contact_email', $tour->tour_contact_email ?? '') }}">
                    <small class="text-muted">Contact email address</small>
                    @error('tour_contact_email')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-lg-12">
                <div class="mb-3">
                    <label class="form-label" for="tour_contact_phone">Phone Number <span class="text-muted">(optional)</span></label>
                    <input type="text" name="tour_contact_phone" id="tour_contact_phone" class="form-control"
                        placeholder="+1 (555) 123-4567"
                        value="{{ old('tour_contact_phone', $tour->tour_contact_phone ?? '') }}">
                    <small class="text-muted">Contact phone number</small>
                    @error('tour_contact_phone')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-lg-12">
                <div class="mb-3">
                    <label class="form-label" for="tour_contact_whatsapp">WhatsApp Number <span class="text-muted">(optional)</span></label>
                    <input type="text" name="tour_contact_whatsapp" id="tour_contact_whatsapp" class="form-control"
                        placeholder="+1 (555) 123-4567"
                        value="{{ old('tour_contact_whatsapp', $tour->tour_contact_whatsapp ?? '') }}">
                    <small class="text-muted">WhatsApp contact number (with country code, e.g., +1234567890)</small>
                    @error('tour_contact_whatsapp')<div class="text-danger">{{ $message }}</div>@enderror
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
                <button class="nav-link active" id="attachment-1-tab" data-bs-toggle="tab" data-bs-target="#attachment-1-pane"
                    type="button" role="tab" aria-controls="attachment-1-pane" aria-selected="true">Attachment 1</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="attachment-2-tab" data-bs-toggle="tab" data-bs-target="#attachment-2-pane"
                    type="button" role="tab" aria-controls="attachment-2-pane" aria-selected="false">Attachment 2</button>
            </li>
        </ul>

        <div class="tab-content" id="tourContactAttachmentTabsContent">
            <div class="tab-pane fade show active" id="attachment-1-pane" role="tabpanel" aria-labelledby="attachment-1-tab" tabindex="0">
                <h6 class="mb-3">Attachment 1 (Image, Video, or Document)</h6>

                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_1_type" id="attachment_1_type_image"
                                value="image" {{ old('attachment_1_type', $tour->attachment_1_type ?? 'image') == 'image' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_1_type_image">Image</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_1_type" id="attachment_1_type_video"
                                value="video" {{ old('attachment_1_type', $tour->attachment_1_type ?? '') == 'video' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_1_type_video">Video</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_1_type" id="attachment_1_type_document"
                                value="document" {{ old('attachment_1_type', $tour->attachment_1_type ?? '') == 'document' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_1_type_document">Document</label>
                        </div>
                    </div>
                    @error('attachment_1_type')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_1_file">Attachment file</label>
                    <input type="file" name="attachment_1_file" id="attachment_1_file" class="form-control">
                    @error('attachment_1_file')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_1_tooltip">Tooltip <span class="text-danger">*</span></label>
                    <input type="text" name="attachment_1_tooltip" id="attachment_1_tooltip" class="form-control"
                        value="{{ old('attachment_1_tooltip', $tour->attachment_1_tooltip ?? '') }}">
                    @error('attachment_1_tooltip')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_1_icon">Button icon</label>
                    <select name="attachment_1_icon" id="attachment_1_icon" class="form-select">
                        <option value="" {{ old('attachment_1_icon', $tour->attachment_1_icon ?? '') == '' ? 'selected' : '' }}>Default (by type)</option>
                        <option value="image" {{ old('attachment_1_icon', $tour->attachment_1_icon ?? '') == 'image' ? 'selected' : '' }}>Image</option>
                        <option value="video" {{ old('attachment_1_icon', $tour->attachment_1_icon ?? '') == 'video' ? 'selected' : '' }}>Video</option>
                        <option value="document" {{ old('attachment_1_icon', $tour->attachment_1_icon ?? '') == 'document' ? 'selected' : '' }}>Document</option>
                    </select>
                    <small class="text-muted">Optional: icon shown on the attachment button in the tour. If not set, defaults by type (image/video/document).</small>
                    @error('attachment_1_icon')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">When user clicks the button</label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_1_action" id="attachment_1_action_modal"
                                value="modal" {{ old('attachment_1_action', $tour->attachment_1_action ?? 'modal') == 'modal' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_1_action_modal">View in modal</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_1_action" id="attachment_1_action_download"
                                value="download" {{ old('attachment_1_action', $tour->attachment_1_action ?? '') == 'download' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_1_action_download">Download</label>
                        </div>
                    </div>
                    @error('attachment_1_action')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="tab-pane fade" id="attachment-2-pane" role="tabpanel" aria-labelledby="attachment-2-tab" tabindex="0">
                <h6 class="mb-3">Attachment 2 (Image, Video, or Document)</h6>

                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_2_type" id="attachment_2_type_image"
                                value="image" {{ old('attachment_2_type', $tour->attachment_2_type ?? 'image') == 'image' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_2_type_image">Image</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_2_type" id="attachment_2_type_video"
                                value="video" {{ old('attachment_2_type', $tour->attachment_2_type ?? '') == 'video' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_2_type_video">Video</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_2_type" id="attachment_2_type_document"
                                value="document" {{ old('attachment_2_type', $tour->attachment_2_type ?? '') == 'document' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_2_type_document">Document</label>
                        </div>
                    </div>
                    @error('attachment_2_type')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_2_file">Attachment file</label>
                    <input type="file" name="attachment_2_file" id="attachment_2_file" class="form-control">
                    @error('attachment_2_file')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_2_tooltip">Tooltip <span class="text-danger">*</span></label>
                    <input type="text" name="attachment_2_tooltip" id="attachment_2_tooltip" class="form-control"
                        value="{{ old('attachment_2_tooltip', $tour->attachment_2_tooltip ?? '') }}">
                    @error('attachment_2_tooltip')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="attachment_2_icon">Button icon</label>
                    <select name="attachment_2_icon" id="attachment_2_icon" class="form-select">
                        <option value="" {{ old('attachment_2_icon', $tour->attachment_2_icon ?? '') == '' ? 'selected' : '' }}>Default (by type)</option>
                        <option value="image" {{ old('attachment_2_icon', $tour->attachment_2_icon ?? '') == 'image' ? 'selected' : '' }}>Image</option>
                        <option value="video" {{ old('attachment_2_icon', $tour->attachment_2_icon ?? '') == 'video' ? 'selected' : '' }}>Video</option>
                        <option value="document" {{ old('attachment_2_icon', $tour->attachment_2_icon ?? '') == 'document' ? 'selected' : '' }}>Document</option>
                    </select>
                    <small class="text-muted">Optional: icon shown on the attachment button in the tour. If not set, defaults by type (image/video/document).</small>
                    @error('attachment_2_icon')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">When user clicks the button</label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_2_action" id="attachment_2_action_modal"
                                value="modal" {{ old('attachment_2_action', $tour->attachment_2_action ?? 'modal') == 'modal' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_2_action_modal">View in modal</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="attachment_2_action" id="attachment_2_action_download"
                                value="download" {{ old('attachment_2_action', $tour->attachment_2_action ?? '') == 'download' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attachment_2_action_download">Download</label>
                        </div>
                    </div>
                    @error('attachment_2_action')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
