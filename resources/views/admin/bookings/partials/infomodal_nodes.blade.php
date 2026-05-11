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
<div class="modal fade" id="editInfoModal" tabindex="-2">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header sticky-top bg-white border-bottom">
                <h5 class="modal-title">Edit Info Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editInfoForm" method="POST">
                <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                    <!-- Type Selection -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <label class="form-label fw-semibold d-block mb-3">Info Point Modal Type <span
                                class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="infoType" id="typeNone" value="none"
                                    disabled>
                                <label class="form-check-label" for="typeNone">Tooltip</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="infoType" id="typeImage"
                                    value="image" disabled>
                                <label class="form-check-label" for="typeImage">Image</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="infoType" id="typeYouTube"
                                    value="youtube" disabled>
                                <label class="form-check-label" for="typeYouTube">YouTube</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="infoType" id="typeAudio"
                                    value="audio" disabled>
                                <label class="form-check-label" for="typeAudio">Audio</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="infoType" id="typeButton"
                                    value="button" disabled>
                                <label class="form-check-label" for="typeButton">Button</label>
                            </div>
                        </div>
                    </div>

                    <!-- Button action type section -->
                    <div class="mb-4 p-3 bg-light rounded d-none" id="buttonActionTypeContainer">
                        <!-- JS inject 💉 -->
                    </div>

                    <!-- Image Media Container -->
                    <div id="imageSection" class="mb-4 p-3 border rounded d-none">
                        <label class="form-label fw-semibold mb-3">
                            Image Preview
                        </label>
                        <div id="imagePreview" class="d-flex flex-wrap gap-2 mb-3">
                            <!-- filled by js -->
                        </div>
                        <div class="mb-3">
                            <label for="imageUrls" class="form-label">Images </label>
                            <input type="file" class="form-control" multiple name="images[]" id="imageInput"
                                accept="image/*" placeholder="e.g, Upload images">
                        </div>
                    </div>

                    <!-- video section -->
                    <div id="videoSection" class="mb-4 p-3 border rounded ">
                        <div class="mb-3">
                            <label class="form-label" for="youtubePreview">YouTube Video Preview</label>
                            <div class="ratio ratio-16x9">
                                <iframe id="youtubePreview" src="" title="YouTube video preview"
                                    allowfullscreen></iframe>
                            </div>
                        </div>
                        <div>
                            <label class="form-label" for="youtubeUrl">Video URL</label>
                            <input type="url" name="youtubeUrl" id="youtubeUrlInput" placeholder="e.g, https://www.youtube.com/watch?v=..." class="form-control">
                        </div>
                    </div>

                    <!-- audio section -->
                    <div id="audioSection" class="mb-4 p-3 border rounded d-none">
                        <div class="mb-3">
                            <label class="form-label" for="audioPreview">Audio Preview</label>
                            <audio id="audioPreview" controls class="w-100">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                        <div class="mb-3">
                            <label for="audioUrl" class="form-label">Audio URL</label>
                            <input type="file" accept="audio/*" name="audioFile" class="form-control" id="audioInput"
                                placeholder="e.g, File.pm3">
                        </div>
                    </div>

                    <!-- tool-tip -->
                    <div id="tooltipSection" class="mb-4 p-3 border rounded d-none">
                        <!-- js fille this tool tip section Update -->
                    </div>

                    <!-- Icon Section -->
                    <div id="IconSection" class="mb-4 p-3 border rounded d-none">
                        <h6 class="mb-3 fw-semibold">icon Section</h6>
                        <div class="row g-3 mb-3" id="iconDetailsDiv">
                            <div class="col-md-4">
                                <label class="form-label" for="">icon colors</label>
                                <div class="input-group">
                                    <span class="input-group-text p-0">
                                        <input type="color" class="form-control form-control-color" value="#1A237E"
                                            onchange="this.parentElement.nextElementSibling.value = this.value"
                                            id="buttonColorPreview">
                                    </span>
                                    <input type="text" name="buttonColor" class="form-control" placeholder="#1A237E"
                                        value="#1A237E" id="buttonColorInput"
                                        oninput="this.previousElementSibling.querySelector('input').value = this.value">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="iconInput">
                                    Icon
                                </label>
                                <div class="input-group">
                                    <!-- Preview -->
                                    <span class="input-group-text bg-white" id="iconPreview">
                                        <span class="material-icons-outlined">
                                            home
                                        </span>
                                    </span>
                                    <!-- Input -->
                                    <input type="text" class="form-control" name="icon" id="iconInput" value="home"
                                        placeholder="e.g. home">
                                </div>
                                <small class="text-muted">
                                    Enter material icon name
                                </small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" for="iconSize">Icon Size</label>
                                <select id="iconSizeSelect" name="IconSize" class="form-select">
                                    <!-- js 💉 -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- modal conternt section -->
                    <div id="modalContentSection" class="mb-4 p-3 border rounded d-none">
                        <!-- js filled it -->
                    </div>

                    <!-- button only section -->
                    <div id="buttonOnlySection" class="mb-4 p-3 border rounded d-none">
                        <!-- js fille this button only section Update -->
                    </div>

                    <!-- Media Section -->
                    <div id="mediaSection" class="mb-4 p-3 border rounded d-none">
                        <h6 class="mb-3 fw-semibold">Media & URLs</h6>
                        <div class="mb-3" id="imageSection">
                            <label for="imageUrls" class="form-label">Image URLs</label>
                            <textarea class="form-control" id="imageUrls" rows="3"
                                placeholder="One image URL per line"></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="youtubeUrl" class="form-label">YouTube Video URL</label>
                                <input type="url" class="form-control" id="youtubeUrl"
                                    placeholder="https://www.youtube.com/watch?v=...">
                            </div>
                            <div class="col-md-6">
                                <label for="audioUrl" class="form-label">Audio URL</label>
                                <input type="url" class="form-control" id="audioUrl"
                                    placeholder="https://example.com/audio.mp3">
                            </div>
                        </div>
                    </div>

                    <!-- Button Icon Section -->
                    <div id="buttonIconSection" class="mb-4 p-3 border rounded d-none">
                        <h6 class="mb-3 fw-semibold">Button Section</h6>
                        <div class="mb-3 w-50" id="buttonTypeDiv">
                            <label for="buttonType" class="form-label">Button Type</label>
                            <select class="form-select" id="buttonTypeSelect">
                                <!-- js fill -->
                            </select>
                        </div>
                        <div class="mb-3" id="buttonTitleDiv">
                            <!-- js fill injection 💉 -->
                        </div>
                        <div class="row g-3 mb-3" id="buttonDetailsDiv">
                            <div class="col-md-4">
                                <label class="form-label" for="">Button colors</label>
                                <div class="input-group">
                                    <span class="input-group-text p-0">
                                        <input type="color" class="form-control form-control-color" value="#1A237E"
                                            onchange="this.parentElement.nextElementSibling.value = this.value"
                                            id="buttonColorPreview">
                                    </span>
                                    <input type="text" name="buttonColor" class="form-control" placeholder="#1A237E"
                                        value="#1A237E" id="buttonColorInput"
                                        oninput="this.previousElementSibling.querySelector('input').value = this.value">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="buttonTextColor" class="form-label">Button Text Color</label>
                                <div class="input-group">
                                    <span class="input-group-text p-0">
                                        <input type="color" class="form-control form-control-color" value="#ffffff"
                                            onchange="this.parentElement.nextElementSibling.value = this.value">
                                    </span>
                                    <input type="text" name="buttonTextColor" class="form-control" placeholder="#ffffff"
                                        value="#ffffff"
                                        oninput="this.previousElementSibling.querySelector('input').value = this.value">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="buttonSize" class="form-label">Button Size</label>
                                <select id="buttonSizeSelect" class="form-select">
                                    <!-- js inject 💉 -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Link URL section -->
                    <div class="mb-4 p-3 border rounded d-none" id="buttonLinkContainer">
                        <label for="buttonSize" class="form-label">Link URL *</label>
                        <input type="text" class="form-control" id="LinkUrlInput"
                            placeholder="e.g, http://www.google.com" value="">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="editInfoSaveBtn">Save Changes</button>
                </div>
            </form>
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