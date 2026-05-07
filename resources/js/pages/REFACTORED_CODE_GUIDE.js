// ============================================================================
// REFACTORED BOOKING INFOMODAL NODES - CODE STRUCTURE GUIDE
// ============================================================================

/**
 * FILE: booking_infomodal_nodes.js
 * PURPOSE: Manages editing of info modals/points within tour nodes
 * 
 * KEY FEATURES:
 * - Dynamically shows only form sections with existing data
 * - Multi-language support (English, Gujarati, Hindi)
 * - Real-time icon and button preview
 * - Character count tracking
 * - Flexible modal type detection
 */

// ============================================================================
// SECTION 1: UTILITY FUNCTIONS
// ============================================================================

/**
 * String/Value Helpers
 * - isNonEmptyString(value)         : Check non-empty string
 * - pickLocalizedString(value)       : Get first available language
 * - getLocalizedStringForLanguage()  : Get specific language value
 * - hasLocalizedValue(value)        : Check if localized value exists
 * - buildLocalizedFieldValue()      : Build language object, preserving format
 * - escapeHtml(value)               : Prevent XSS
 */

/**
 * DOM Helpers
 * - getCheckedValue(name, fallback)       : Get checked radio value
 * - toggleVisibility(ids, visible)        : Toggle multiple elements
 * - setSectionVisibility(id, visible)     : Toggle one element
 * - getInputValue(id)                     : Read text input
 * - setInputValue(id, value)              : Set text input
 * - getEditorValue(id)                    : Read TinyMCE or textarea
 * - setEditorValue(id, value)             : Set TinyMCE or textarea
 * - parseMultiValueLines(value)           : Split textarea into array
 */

/**
 * Data Detection Helpers
 * - getEditableTitleField(modal)          : Which field stores main title
 * - tryFindNodesArray(root)               : Extract nodes from various JSON shapes
 * - findTheType(modal)                    : Detect modal type from content
 */

// ============================================================================
// SECTION 2: VISIBILITY ANALYSIS
// ============================================================================

/**
 * analyzeModalDataForVisibility(infoModal)
 * 
 * Scans the modal object and determines which form sections have data:
 * - title     : If title/infoModalTitle/linkTitle is populated
 * - description : If description/infoModalDescription is populated
 * - link      : If link/actionUrl/linkTitle is populated
 * - modal     : If modal content, iframe, or width is populated
 * - footer    : If footer button or text is populated
 * - media     : If image, youtube, or audio URL is populated
 * - icon      : If icon, color, or size is set
 * - button    : If button-only or button-related fields are set
 * - position  : If yaw/pitch coordinates exist
 *
 * RETURNS: Array of section keys to show
 * 
 * USAGE:
 *   const sections = analyzeModalDataForVisibility(infoModal);
 *   updateVisibilityFromAnalysis(sections);
 *
 * This allows the form to intelligently show only relevant sections,
 * reducing clutter and focusing the UI on what matters.
 */

/**
 * updateVisibilityFromAnalysis(visibleSections)
 * 
 * - Hides ALL form sections
 * - Shows only sections in the provided array
 * 
 * Uses EDIT_MODAL_VISIBILITY map to find which DOM IDs to toggle
 */

// ============================================================================
// SECTION 3: STATE MANAGEMENT
// ============================================================================

/**
 * EditModalState = {
 *   currentInfoModal: null,      // Deep copy of modal being edited
 *   currentNode: null,           // Parent node
 *   currentModalIndex: null,     // Position in node.infoModals
 *   currentTitleField: null,     // 'title' | 'linkTitle' | 'infoModalTitle'
 *   reset()                      // Clear all when done
 * }
 */

// ============================================================================
// SECTION 4: RENDERING & DISPLAY
// ============================================================================

/**
 * normalizeNodes(finalJson)
 * 
 * Converts raw tour JSON to normalized structure:
 * [{
 *   id: string,
 *   name: string,
 *   showInSideMenu: boolean,
 *   sideMenuTitle: string (localized),
 *   sideMenuOrder: number | null,
 *   infoModals: array
 * }]
 * 
 * Only includes nodes with sidebar visibility AND info modals
 */

/**
 * render(nodes, query='')
 * 
 * Renders accordion list of all nodes and their modals
 * 
 * FEATURES:
 * - Search filtering across node names and modal titles
 * - Shows count of nodes and modals
 * - Edit button for each modal
 * - Auto-detects modal type and displays it
 *
 * FLOW:
 * 1. Filter nodes by search query
 * 2. Build accordion HTML with modal cards
 * 3. Attach edit click handlers to each modal
 */

// ============================================================================
// SECTION 5: OPENING & POPULATING MODAL
// ============================================================================

/**
 * openEditModal(infoModal, node, modalIndex)
 * 
 * Opens the edit form modal and populates all fields from existing modal data
 * 
 * PROCESS:
 * 1. Store state (EditModalState)
 * 2. Detect modal type from content
 * 3. Populate all form fields (EN, GU, media, icons, etc)
 * 4. Analyze which sections have data
 * 5. Show only relevant form sections
 * 6. Display modal
 * 
 * This creates a clean, focused editing experience where only relevant
 * sections are shown to the user.
 */

// ============================================================================
// SECTION 6: SAVING & UPDATING
// ============================================================================

/**
 * getFormState()
 * 
 * Collects all form field values into a single object:
 * {
 *   type: 'none' | 'image' | 'youtube' | 'audio' | 'button',
 *   tooltipPosition: 'up' | 'down' | 'left' | 'right',
 *   tooltipTitle: { en: '', gu: '' },
 *   tooltipDescription: { en: '', gu: '' },
 *   ... (all other field values)
 * }
 * 
 * This is what gets passed to updateNodeWithEditedModal()
 */

/**
 * validateFormData()
 * 
 * Checks required fields before saving:
 * - For 'none' type: tooltip title must be filled (EN or GU)
 * - Shows alert if validation fails
 */

/**
 * updateNodeWithEditedModal(node, modalIndex, formState)
 * 
 * Maps form values back to modal object:
 * 
 * FIELD MAPPING:
 * - Title fields: uses buildLocalizedFieldValue() to preserve format
 * - Description: localized (en, gu)
 * - Links: non-localized URLs
 * - Media: image arrays, URL strings
 * - Icon: name, color, size
 * - Button: type, appearance, actions
 * - Behavior: showOnLoad, delay
 * 
 * Then syncs back to:
 * - node.infoModals[index]
 * - window.tourFinalJson.nodes (if exists)
 * 
 * RETURNS: true if saved successfully, false if node not found
 */

// ============================================================================
// SECTION 7: UI UPDATES (PREVIEWS, COUNTS)
// ============================================================================

/**
 * updateIconPreview()
 * Updates the icon display in real-time as user changes:
 * - Icon name
 * - Icon color
 * - Icon size
 */

/**
 * updateButtonPreview()
 * Shows live preview of button as styled:
 * - Text from EN or GU field
 * - Icon if icon-button type
 * - Colors from button color settings
 */

/**
 * updateCharacterCount(fieldId)
 * Updates character counter display for text fields
 * Called on input for: tooltipTitle, tooltipDescription
 */

// ============================================================================
// SECTION 8: EVENT SETUP
// ============================================================================

/**
 * setupEditModalEvents()
 * 
 * Attaches all event listeners to the edit modal:
 * 
 * CHARACTER COUNTS:
 * - tooltipTitleEN, tooltipTitleGU (max 120)
 * - tooltipDescriptionEN, tooltipDescriptionGU (max 300)
 * - Updates on input
 * 
 * PREVIEW UPDATES:
 * - Icon changes → updateIconPreview()
 * - Button text/type changes → updateButtonPreview()
 * 
 * SAVE BUTTON:
 * - Validates form
 * - Gets form state
 * - Updates node
 * - Closes modal
 * - Refreshes list
 * - Resets state
 */

/**
 * init()
 * 
 * Called on DOMContentLoaded
 * 
 * SETUP:
 * 1. Initialize icon library
 * 2. Initialize rich text editors (TinyMCE)
 * 3. Load nodes from window.tourFinalJson
 * 4. Render initial list
 * 5. Attach search listener
 * 6. Attach refresh listener
 * 7. Setup modal event handlers
 */

// ============================================================================
// SCROLLING FIX
// ============================================================================

/**
 * Modal scrolling issue FIXED by:
 * 
 * BEFORE:
 * <div class="modal-dialog modal-xl modal-dialog-scrollable">
 * 
 * AFTER:
 * <div class="modal-dialog modal-xl">
 *   <div class="modal-content">
 *     <div class="modal-header sticky-top bg-white border-bottom">
 *       ... header stays fixed at top ...
 *     </div>
 *     <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
 *       ... content scrolls ...
 *     </div>
 * 
 * KEY CHANGES:
 * 1. Removed modal-dialog-scrollable class
 * 2. Added inline styles to modal-body: max-height + overflow-y
 * 3. Made header sticky so it stays visible while scrolling
 * 4. This allows content to scroll while header remains fixed
 */

// ============================================================================
// MAIN DATA FLOW
// ============================================================================

/**
 * 1. LOAD
 *    window.tourFinalJson
 *         ↓
 *    normalizeNodes()
 *         ↓
 *    render() - show accordion list
 *
 * 2. EDIT
 *    User clicks "Edit" button
 *         ↓
 *    openEditModal(modal, node, index)
 *         ↓
 *    analyzeModalDataForVisibility()
 *         ↓
 *    Show only sections with data
 *         ↓
 *    Modal appears with prefilled fields
 *
 * 3. SAVE
 *    User clicks "Save Changes"
 *         ↓
 *    validateFormData()
 *         ↓
 *    getFormState()
 *         ↓
 *    updateNodeWithEditedModal()
 *         ↓
 *    Update window.tourFinalJson
 *         ↓
 *    Close modal & refresh list
 */

// ============================================================================
// CUSTOMIZATION POINTS
// ============================================================================

/**
 * To add new fields to the form:
 * 
 * 1. Add HTML input in infomodal_nodes.blade.php
 * 2. Give it an ID: <input id="myFieldId">
 * 3. Add to EDIT_MODAL_VISIBILITY if it should hide/show
 * 4. Populate in openEditModal():
 *    document.getElementById('myFieldId').value = infoModal.myField || '';
 * 5. Read in getFormState():
 *    myField: getInputValue('myFieldId'),
 * 6. Save in updateNodeWithEditedModal():
 *    modal.myField = formState.myField || '';
 */

/**
 * To change visibility logic:
 * 
 * Edit analyzeModalDataForVisibility() to check for your fields:
 *    if (infoModal?.myField) {
 *      visibleSections.push('mySection');
 *    }
 */

/**
 * To add new modal types:
 * 
 * In findTheType():
 *    if (infoModal?.myTypeIndicator) return 'My Type';
 * 
 * In openEditModal():
 *    if (isNonEmptyString(infoModal.myField)) {
 *      detectedType = 'myType';
 *    }
 */


`

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
                    </div>`