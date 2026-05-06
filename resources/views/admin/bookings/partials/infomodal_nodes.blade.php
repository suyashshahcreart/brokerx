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

                    <div id="tooltipSection" class="mb-4 p-3 border rounded">
                        <h5 class="mb-3 fw-semibold">Tooltip Section</h5>
                        <div class="mb-3" id="tooltipTitleSection">
                            <!-- js inset in to this -->
                        </div>
                        <div id="tooltipPositionSection" class="mb-4 p-3 border rounded">
                            <h6 class="mb-3 fw-semibold">Tooltip Position</h6>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tooltipPosition" id="posUp"
                                        value="up">
                                    <label class="form-check-label" for="posUp">Up</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tooltipPosition" id="posDown"
                                        value="down" checked>
                                    <label class="form-check-label" for="posDown">Down</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tooltipPosition" id="posLeft"
                                        value="left">
                                    <label class="form-check-label" for="posLeft">Left</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tooltipPosition" id="posRight"
                                        value="right">
                                    <label class="form-check-label" for="posRight">Right</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3" id="tooltipDescriptionENSection">
                            <label for="tooltipDescriptionEN" class="form-label">Tooltip Description</label>
                            <textarea class="form-control" id="tooltipDescriptionEN" rows="3" maxlength="300"
                                placeholder="Enter tooltip description"></textarea>
                            <small class="text-muted" id="tooltipDescriptionENCount">0</small>/300
                        </div>
                    </div>


                    <div class="tab-content mb-4">
                        <!-- English Tab -->
                        <div class="tab-pane fade show active" id="langENContent" role="tabpanel"
                            aria-labelledby="langEN">
                            <!-- Title Section -->
                            <div id="titleSection" class="mb-4 p-3 border rounded d-none">
                                <h6 class="mb-3 fw-semibold">Title</h6>
                                <div class="mb-3">
                                    <label for="linkTitleEN" class="form-label">Link Title</label>
                                    <input type="text" class="form-control" id="linkTitleEN"
                                        placeholder="Enter link title">
                                </div>
                                <div class="mb-3">
                                    <label for="modalTitleEN" class="form-label">Modal Title</label>
                                    <input type="text" class="form-control" id="modalTitleEN" maxlength="120"
                                        placeholder="Enter modal title">
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div id="descriptionSection" class="mb-4 p-3 border rounded d-none">
                                <h6 class="mb-3 fw-semibold">Description</h6>
                                <div class="mb-3">
                                    <label for="modalDescriptionEN" class="form-label">Modal Description (Rich
                                        Text)</label>
                                    <textarea class="form-control editor" id="modalDescriptionEN" rows="8"
                                        placeholder="Enter modal description"></textarea>
                                </div>
                            </div>

                            <!-- Link Section -->
                            <div id="linkSection" class="mb-4 p-3 border rounded d-none">
                                <h6 class="mb-3 fw-semibold">Links & Actions</h6>
                                <div class="mb-3">
                                    <label for="infoModalLinkEN" class="form-label">Info Modal Link Text</label>
                                    <input type="text" class="form-control" id="infoModalLinkEN"
                                        placeholder="Link text for info modal">
                                </div>
                                <div class="mb-3" id="linkUrlSection">
                                    <label for="actionUrl" class="form-label">Action URL</label>
                                    <input type="url" class="form-control" id="actionUrl"
                                        placeholder="https://example.com">
                                </div>
                            </div>

                            <!-- Button Section -->
                            <div id="buttonTextENSection" class="mb-4 p-3 border rounded d-none">
                                <h6 class="mb-3 fw-semibold">Button Text</h6>
                                <div class="mb-3">
                                    <label for="buttonTextEN" class="form-label">Button Display Text</label>
                                    <input type="text" class="form-control" id="buttonTextEN" maxlength="120"
                                        placeholder="Enter button text">
                                </div>
                            </div>

                            <!-- Footer Section -->
                            <div id="footerSection" class="mb-4 p-3 border rounded d-none">
                                <h6 class="mb-3 fw-semibold">Modal Footer</h6>
                                <div class="mb-3" id="infoModalFooterButtonSection">
                                    <label for="infoModalFooterButtonTitleEN" class="form-label">Footer Button
                                        Title</label>
                                    <input type="text" class="form-control" id="infoModalFooterButtonTitleEN"
                                        placeholder="Button text in footer">
                                </div>
                                <div class="mb-3" id="infoModalFooterButtonLinkSection">
                                    <label for="infoModalFooterButtonLink" class="form-label">Footer Button Link</label>
                                    <input type="url" class="form-control" id="infoModalFooterButtonLink"
                                        placeholder="https://example.com">
                                </div>
                                <div class="mb-3" id="infoModalFooterTextSection">
                                    <label for="infoModalFooterTextEN" class="form-label">Footer Text</label>
                                    <input type="text" class="form-control" id="infoModalFooterTextEN"
                                        placeholder="Footer text message">
                                </div>
                            </div>
                        </div>

                        <!-- Gujarati Tab -->
                        <div class="tab-pane fade" id="langGUContent" role="tabpanel" aria-labelledby="langGU">
                            <!-- Title Section -->
                            <div id="titleSectionGU" class="mb-4 p-3 border rounded d-none">
                                <h6 class="mb-3 fw-semibold">Title (Gujarati)</h6>
                                <div class="mb-3" id="tooltipTitleGUSection">
                                    <label for="tooltipTitleGU" class="form-label">Tooltip Title</label>
                                    <input type="text" class="form-control" id="tooltipTitleGU" maxlength="120"
                                        placeholder="ટૂલટીપ શીર્ષક દાખલ કરો">
                                    <small class="text-muted" id="tooltipTitleGUCount">0</small>/120
                                </div>
                                <div class="mb-3">
                                    <label for="linkTitleGU" class="form-label">Link Title</label>
                                    <input type="text" class="form-control" id="linkTitleGU" placeholder="લિંક શીર્ષક">
                                </div>
                                <div class="mb-3">
                                    <label for="modalTitleGU" class="form-label">Modal Title</label>
                                    <input type="text" class="form-control" id="modalTitleGU" maxlength="120"
                                        placeholder="મોડલ શીર્ષક">
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div id="descriptionSectionGU" class="mb-4 p-3 border rounded d-none">
                                <h6 class="mb-3 fw-semibold">Description (Gujarati)</h6>
                                <div class="mb-3" id="tooltipDescriptionGUSection">
                                    <label for="tooltipDescriptionGU" class="form-label">Tooltip Description</label>
                                    <textarea class="form-control" id="tooltipDescriptionGU" rows="3" maxlength="300"
                                        placeholder="ટૂલટીપ વર્ણન"></textarea>
                                    <small class="text-muted" id="tooltipDescriptionGUCount">0</small>/300
                                </div>
                                <div class="mb-3">
                                    <label for="modalDescriptionGU" class="form-label">Modal Description</label>
                                    <textarea class="form-control editor" id="modalDescriptionGU" rows="8"
                                        placeholder="મોડલ વર્ણન"></textarea>
                                </div>
                            </div>

                            <!-- Link Section -->
                            <div id="linkSectionGU" class="mb-4 p-3 border rounded d-none">
                                <h6 class="mb-3 fw-semibold">Links & Actions (Gujarati)</h6>
                                <div class="mb-3">
                                    <label for="infoModalLinkGU" class="form-label">Info Modal Link Text</label>
                                    <input type="text" class="form-control" id="infoModalLinkGU"
                                        placeholder="લિંક ટેક્સ્ટ">
                                </div>
                            </div>

                            <!-- Button Section -->
                            <div id="buttonTextGUSectionGU" class="mb-4 p-3 border rounded d-none">
                                <h6 class="mb-3 fw-semibold">Button Text (Gujarati)</h6>
                                <div class="mb-3">
                                    <label for="buttonTextGU" class="form-label">Button Display Text</label>
                                    <input type="text" class="form-control" id="buttonTextGU" maxlength="120"
                                        placeholder="બટન ટેક્સ્ટ">
                                </div>
                            </div>

                            <!-- Footer Section -->
                            <div id="footerSectionGU" class="mb-4 p-3 border rounded d-none">
                                <h6 class="mb-3 fw-semibold">Modal Footer (Gujarati)</h6>
                                <div class="mb-3">
                                    <label for="infoModalFooterButtonTitleGU" class="form-label">Footer Button
                                        Title</label>
                                    <input type="text" class="form-control" id="infoModalFooterButtonTitleGU"
                                        placeholder="ફૂટર બટન ટેક્સ્ટ">
                                </div>
                                <div class="mb-3">
                                    <label for="infoModalFooterTextGU" class="form-label">Footer Text</label>
                                    <input type="text" class="form-control" id="infoModalFooterTextGU"
                                        placeholder="ફૂટર ટેક્સ્ટ">
                                </div>
                            </div>
                        </div>
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

                    <!-- Modal Content Section -->
                    <div id="modalContentSection" class="mb-4 p-3 border rounded d-none">
                        <h6 class="mb-3 fw-semibold">Modal Settings</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="infoModalIframeUrl" class="form-label">Iframe URL</label>
                                <input type="url" class="form-control" id="infoModalIframeUrl" placeholder="Embed URL">
                            </div>
                            <div class="col-md-6">
                                <label for="infoModalSize" class="form-label">Modal Size</label>
                                <select class="form-select" id="infoModalSize">
                                    <option value="modal-sm">Small (modal-sm)</option>
                                    <option value="modal-md" selected>Medium (modal-md)</option>
                                    <option value="modal-lg">Large (modal-lg)</option>
                                    <option value="modal-full">Full Width (modal-full)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Icon Section -->
                    <div id="iconSection" class="mb-4 p-3 border rounded d-none">
                        <h6 class="mb-3 fw-semibold">Icon Settings</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="infoPointIcon" class="form-label">Icon Name</label>
                                <input type="text" class="form-control" id="infoPointIcon"
                                    placeholder="Material icon name (e.g., info)">
                            </div>
                            <div class="col-md-4">
                                <label for="infoPointIconColor" class="form-label">Icon Color</label>
                                <input type="color" class="form-control form-control-color" id="infoPointIconColor"
                                    value="#3a3abb">
                            </div>
                            <div class="col-md-4">
                                <label for="infoPointIconSize" class="form-label">Icon Size</label>
                                <select class="form-select" id="infoPointIconSize">
                                    <option value="small">Small</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="large">Large</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Preview:</small>
                            <div id="icoPreview" style="font-size: 32px; margin-top: 8px;">
                                <span id="infoPointIconPreview" class="material-icons-outlined"
                                    style="color: #3a3abb;">info</span>
                            </div>
                        </div>
                    </div>

                    <!-- Button Styling Section -->
                    <div id="buttonStylingSection" class="mb-4 p-3 border rounded d-none">
                        <h6 class="mb-3 fw-semibold">Button Styling</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Is Button Only</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="isButtonOnly">
                                    <label class="form-check-label" for="isButtonOnly">This is a button-only
                                        modal</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="buttonType" class="form-label">Button Type</label>
                                <select class="form-select" id="buttonType">
                                    <option value="textButton">Text Button</option>
                                    <option value="iconButton">Icon Button</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="buttonColor" class="form-label">Button Color</label>
                                <input type="color" class="form-control form-control-color" id="buttonColor"
                                    value="#3a3abb">
                            </div>
                            <div class="col-md-4">
                                <label for="buttonTextColor" class="form-label">Button Text Color</label>
                                <input type="color" class="form-control form-control-color" id="buttonTextColor"
                                    value="#ffffff">
                            </div>
                            <div class="col-md-4">
                                <label for="buttonSize" class="form-label">Button Size</label>
                                <input type="text" class="form-control" id="buttonSize"
                                    placeholder="small / medium / large">
                            </div>
                        </div>
                    </div>

                    <!-- Button Action Section -->
                    <div id="buttonActionSection" class="mb-4 p-3 border rounded d-none">
                        <h6 class="mb-3 fw-semibold">Button Actions</h6>
                        <div class="mb-3">
                            <label for="buttonActionType" class="form-label">Action Type</label>
                            <select class="form-select" id="buttonActionType">
                                <option value="">Select action...</option>
                                <option value="redirectToLink">Redirect to Link</option>
                                <option value="openModal">Open Modal</option>
                                <option value="navigate">Navigate to Node</option>
                                <option value="openImage">Open Image</option>
                                <option value="openVideo">Open Video</option>
                                <option value="openDocument">Open Document</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="buttonNodeId" class="form-label">Target Node ID</label>
                            <input type="text" class="form-control" id="buttonNodeId"
                                placeholder="Node ID for navigation">
                        </div>
                    </div>

                    <!-- Button Preview Section -->
                    <div id="buttonPreviewSection" class="mb-4 p-3 bg-light rounded d-none">
                        <h6 class="mb-3 fw-semibold">Button Preview</h6>
                        <button type="button" id="buttonPreview" class="btn btn-primary" disabled>Preview
                            Button</button>
                    </div>

                    <!-- Behavior Section -->
                    <div id="behaviorSection" class="mb-4 p-3 border rounded d-none">
                        <h6 class="mb-3 fw-semibold">Behavior</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Show on Load</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showOnLoad">
                                    <label class="form-check-label" for="showOnLoad">Auto-show this modal on page
                                        load</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="showOnLoadDelayMs" class="form-label">Delay (milliseconds)</label>
                                <input type="number" class="form-control" id="showOnLoadDelayMs" placeholder="0"
                                    min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Position Section -->
                    <div id="positionSection" class="mb-4 p-3 border rounded d-none">
                        <h6 class="mb-3 fw-semibold">Position (Auto-populated)</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="positionYaw" class="form-label">Yaw (rotation)</label>
                                <input type="text" readonly class="form-control-plaintext" id="positionYaw"
                                    placeholder="Auto-set from viewer">
                            </div>
                            <div class="col-md-6">
                                <label for="positionPitch" class="form-label">Pitch (tilt)</label>
                                <input type="text" readonly class="form-control-plaintext" id="positionPitch"
                                    placeholder="Auto-set from viewer">
                            </div>
                        </div>
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