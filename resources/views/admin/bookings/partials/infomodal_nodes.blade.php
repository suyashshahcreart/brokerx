<div class="card border-1 shadow-sm">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="card-title mb-0">Info Modal Nodes</h4>
                <small class="text-muted">Nodes with side menu enabled and at least one info modal</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input type="search" id="infomodalNodesSearch" class="form-control form-control-sm"
                    placeholder="Search node / modal title..." style="min-width: 240px;">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="infomodalNodesRefreshBtn">
                    <i class="ri-refresh-line"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="infomodalNodesMeta" class="text-muted small mb-2"></div>
        <div id="infomodalNodesList"></div>
        <div id="infomodalNodesEmpty" class="text-muted d-none">
            No nodes found with info modals and <code>showInSideMenu = true</code>.
        </div>
    </div>
</div>

<!-- Edit Info Modal -->
<div class="modal fade" id="editInfoModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit info for <span id="modalNodeIndex">0</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Type <span class="text-danger">*</span></label>
                    <div class="btn-group w-100 flex-wrap" role="group" style="gap: 6px;">
                        <input type="radio" class="btn-check" name="infoType" id="typeNone" value="none" checked>
                        <label class="btn btn-outline-secondary" for="typeNone">None</label>
                        <input type="radio" class="btn-check" name="infoType" id="typeImage" value="image">
                        <label class="btn btn-outline-secondary" for="typeImage">Image</label>
                        <input type="radio" class="btn-check" name="infoType" id="typeYouTube" value="youtube">
                        <label class="btn btn-outline-secondary" for="typeYouTube">YouTube</label>
                        <input type="radio" class="btn-check" name="infoType" id="typeAudio" value="audio">
                        <label class="btn btn-outline-secondary" for="typeAudio">Audio</label>
                        <input type="radio" class="btn-check" name="infoType" id="typeButton" value="button">
                        <label class="btn btn-outline-secondary" for="typeButton">Button Only</label>
                    </div>
                </div>

                <div class="mb-3 d-none" id="buttonTypeSection">
                    <label class="form-label fw-semibold">Button Appearance <span class="text-danger">*</span></label>
                    <div class="btn-group w-100 flex-wrap" role="group" style="gap: 6px;">
                        <input type="radio" class="btn-check" name="buttonType" id="buttonTypeText" value="textButton" checked>
                        <label class="btn btn-outline-secondary" for="buttonTypeText">Text Button</label>
                        <input type="radio" class="btn-check" name="buttonType" id="buttonTypeIcon" value="iconButton">
                        <label class="btn btn-outline-secondary" for="buttonTypeIcon">Icon Button</label>
                    </div>
                </div>

                <div class="mb-3 d-none" id="buttonActionSection">
                    <label class="form-label fw-semibold">Button Action <span class="text-danger">*</span></label>
                    <div class="btn-group w-100 flex-wrap" role="group" style="gap: 6px;">
                        <input type="radio" class="btn-check" name="buttonAction" id="actionRedirect" value="redirectToLink" checked>
                        <label class="btn btn-outline-secondary" for="actionRedirect">Redirect to Link</label>
                        <input type="radio" class="btn-check" name="buttonAction" id="actionOpenModal" value="openInfoModal">
                        <label class="btn btn-outline-secondary" for="actionOpenModal">Open Modal</label>
                        <input type="radio" class="btn-check" name="buttonAction" id="actionNavigateNode" value="navigateToNode">
                        <label class="btn btn-outline-secondary" for="actionNavigateNode">Navigate Node</label>
                        <input type="radio" class="btn-check" name="buttonAction" id="actionOpenImage" value="openImage">
                        <label class="btn btn-outline-secondary" for="actionOpenImage">Open Image</label>
                        <input type="radio" class="btn-check" name="buttonAction" id="actionOpenVideo" value="openVideo">
                        <label class="btn btn-outline-secondary" for="actionOpenVideo">Open Video</label>
                        <input type="radio" class="btn-check" name="buttonAction" id="actionOpenDocument" value="openDocument">
                        <label class="btn btn-outline-secondary" for="actionOpenDocument">Open Document</label>
                    </div>
                </div>

                <div class="mb-3" id="tooltipPositionSection">
                    <label class="form-label fw-semibold">Tooltip Position</label>
                    <div class="btn-group w-100 flex-wrap" role="group" style="gap: 6px;">
                        <input type="radio" class="btn-check" name="tooltipPosition" id="posUp" value="up" checked>
                        <label class="btn btn-outline-secondary" for="posUp">Up</label>
                        <input type="radio" class="btn-check" name="tooltipPosition" id="posDown" value="down">
                        <label class="btn btn-outline-secondary" for="posDown">Down</label>
                        <input type="radio" class="btn-check" name="tooltipPosition" id="posLeft" value="left">
                        <label class="btn btn-outline-secondary" for="posLeft">Left</label>
                        <input type="radio" class="btn-check" name="tooltipPosition" id="posRight" value="right">
                        <label class="btn btn-outline-secondary" for="posRight">Right</label>
                    </div>
                </div>

                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="langEN" data-bs-toggle="tab" data-bs-target="#langENContent" type="button" role="tab">EN</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="langGU" data-bs-toggle="tab" data-bs-target="#langGUContent" type="button" role="tab">GU</button>
                    </li>
                </ul>

                <div class="tab-content mb-4">
                    <div class="tab-pane fade show active" id="langENContent" role="tabpanel" aria-labelledby="langEN">
                        <div class="mb-3" id="tooltipTitleENSection">
                            <label for="tooltipTitleEN" class="form-label">Tooltip Title (EN)</label>
                            <input type="text" class="form-control" id="tooltipTitleEN" maxlength="120" placeholder="Enter tooltip title">
                        </div>
                        <div class="mb-3" id="tooltipDescriptionENSection">
                            <label for="tooltipDescriptionEN" class="form-label">Tooltip Description (EN)</label>
                            <textarea class="form-control" id="tooltipDescriptionEN" rows="3" maxlength="300" placeholder="Enter tooltip description"></textarea>
                        </div>
                        <div class="mb-3 d-none" id="buttonTextENSection">
                            <label for="buttonTextEN" class="form-label">Button Text (EN)</label>
                            <input type="text" class="form-control" id="buttonTextEN" maxlength="120" placeholder="Enter button text">
                        </div>
                        <div class="mb-3 d-none" id="modalTitleENSection">
                            <label for="modalTitleEN" class="form-label">Modal Title (EN)</label>
                            <input type="text" class="form-control" id="modalTitleEN" maxlength="120" placeholder="Enter modal title">
                        </div>
                        <div class="mb-3 d-none" id="modalDescriptionENSection">
                            <label for="modalDescriptionEN" class="form-label">Modal Description (EN)</label>
                            <textarea class="form-control editor" id="modalDescriptionEN" rows="8" placeholder="Enter modal description"></textarea>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="langGUContent" role="tabpanel" aria-labelledby="langGU">
                        <div class="mb-3" id="tooltipTitleGUSection">
                            <label for="tooltipTitleGU" class="form-label">Tooltip Title (GU)</label>
                            <input type="text" class="form-control" id="tooltipTitleGU" maxlength="120" placeholder="Enter tooltip title">
                        </div>
                        <div class="mb-3" id="tooltipDescriptionGUSection">
                            <label for="tooltipDescriptionGU" class="form-label">Tooltip Description (GU)</label>
                            <textarea class="form-control" id="tooltipDescriptionGU" rows="3" maxlength="300" placeholder="Enter tooltip description"></textarea>
                        </div>
                        <div class="mb-3 d-none" id="buttonTextGUSection">
                            <label for="buttonTextGU" class="form-label">Button Text (GU)</label>
                            <input type="text" class="form-control" id="buttonTextGU" maxlength="120" placeholder="Enter button text">
                        </div>
                        <div class="mb-3 d-none" id="modalTitleGUSection">
                            <label for="modalTitleGU" class="form-label">Modal Title (GU)</label>
                            <input type="text" class="form-control" id="modalTitleGU" maxlength="120" placeholder="Enter modal title">
                        </div>
                        <div class="mb-3 d-none" id="modalDescriptionGUSection">
                            <label for="modalDescriptionGU" class="form-label">Modal Description (GU)</label>
                            <textarea class="form-control editor" id="modalDescriptionGU" rows="8" placeholder="Enter modal description"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-4 d-none" id="iconSection">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Icon</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="infoPointIcon" placeholder="Pick an icon" readonly>
                                <button type="button" class="btn btn-outline-secondary" id="infoPointIconPickerBtn">Browse Icons</button>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center gap-3">
                            <div>
                                <label class="form-label fw-semibold d-block">Icon Preview</label>
                                <span class="material-icons-outlined" id="infoPointIconPreview" style="font-size: 32px; line-height: 1;">radio_button_unchecked</span>
                            </div>
                            <div class="flex-fill">
                                <label for="infoPointIconColor" class="form-label fw-semibold">Icon Color</label>
                                <input type="color" class="form-control form-control-color w-100" id="infoPointIconColor" value="#3a3abb">
                            </div>
                            <div class="flex-fill">
                                <label for="infoPointIconSize" class="form-label fw-semibold">Icon Size</label>
                                <select class="form-select" id="infoPointIconSize">
                                    <option value="small">Small</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="large">Large</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3 d-none" id="imageSection">
                    <label for="imageUrls" class="form-label fw-semibold">Images</label>
                    <textarea class="form-control" id="imageUrls" rows="4" placeholder="One image URL per line"></textarea>
                </div>

                <div class="mb-3 d-none" id="youtubeSection">
                    <label for="youtubeUrl" class="form-label fw-semibold">YouTube Video Link</label>
                    <input type="url" class="form-control" id="youtubeUrl" placeholder="https://www.youtube.com/watch?v=...">
                </div>

                <div class="mb-3 d-none" id="audioSection">
                    <label for="audioUrl" class="form-label fw-semibold">Audio Link</label>
                    <input type="url" class="form-control" id="audioUrl" placeholder="https://example.com/audio.mp3">
                </div>

                <div class="mb-3 d-none" id="actionUrlSection">
                    <label for="actionUrl" class="form-label fw-semibold">URL / Link</label>
                    <input type="url" class="form-control" id="actionUrl" placeholder="https://example.com">
                </div>

                <div class="mb-4 d-none" id="actionModalSection">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="infoModalSize" class="form-label fw-semibold">Modal Size</label>
                            <select class="form-select" id="infoModalSize">
                                <option value="medium" selected>Medium</option>
                                <option value="large">Large</option>
                                <option value="small">Small</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="infoModalFooterLinkUrl" class="form-label fw-semibold">Footer Button Link</label>
                            <input type="url" class="form-control" id="infoModalFooterLinkUrl" placeholder="https://example.com">
                        </div>
                    </div>
                </div>

                <div class="mb-3 d-none" id="buttonPreviewSection">
                    <label class="form-label fw-semibold">Button Preview</label>
                    <div class="p-3 bg-light rounded text-center">
                        <button type="button" class="btn" id="buttonPreview" style="background-color: #3a3abb; color: #ffffff;">Preview Button</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editInfoSaveBtn">Save Info</button>
            </div>
        </div>
    </div>
</div>

<!-- Material Icon Picker Modal -->
<div class="modal fade w-100" id="materialIconModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Icon</h5>
                <button class="btn-close" id="materialIconModalClose" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="materialIconSearch" class="form-control mb-3" placeholder="Search icon...">
                <div id="iconContainer" class="icon-grid"></div>
            </div>
        </div>
    </div>
</div>