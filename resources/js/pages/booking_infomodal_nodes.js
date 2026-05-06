import $ from 'jquery';
window.$ = window.jQuery = $;
import '../../css/pages/materialIconLiberaryStyles.css';
import iconLib from './booking_tour_iconLib';
import reinitalizeEditors from '../tinyEditor';

/* global window, document */

// ============================================================================
// CONFIGURATION & CONSTANTS
// ============================================================================

/** Fields that track character count in the UI */

const EDIT_MODAL_TEXT_FIELD_IDS = [
  'tooltipTitleEN', 'tooltipTitleGU', 'tooltipDescriptionEN', 'tooltipDescriptionGU'
];

const EDIT_MODAL_MEDIA_FIELD_IDS = [];

/**
 * EDIT_MODAL_VISIBILITY
 * Maps section categories to DOM element IDs that should be shown/hidden
 * Based on what data the modal contains
 * 
 * Usage: When user edits a modal, we analyze which fields have data,
 * then show only those sections (title, description, links, etc)
 */
const EDIT_MODAL_VISIBILITY = {
  tooltip: ['tooltipTitleENSection', 'tooltipTitleGUSection', 'tooltipDescriptionENSection', 'tooltipDescriptionGUSection', ''],
  title: ['titleSection', 'infoModalTitleSection'],
  description: ['descriptionSection', 'modalDescriptionSection'],
  link: ['linkSection', 'linkUrlSection', 'infoModalLinkSection'],
  modal: ['modalContentSection', 'infoModalIframeSection', 'infoModalSizeSection'],
  footer: ['footerSection', 'infoModalFooterButtonSection', 'infoModalFooterTextSection'],
  media: ['imageSection', 'youtubeSection', 'audioSection'],
  icon: ['iconSection', 'infoPointIconSection'],
  button: ['buttonTypeSection', 'buttonStylingSection', 'buttonActionSection', 'buttonPreviewSection'],
  position: ['positionSection'],
};

function isNonEmptyString(value) {
  return typeof value === 'string' && value.trim() !== '';
}

function safeArray(value) {
  return Array.isArray(value) ? value : [];
}

function pickLocalizedString(value) {
  if (isNonEmptyString(value)) return value.trim();
  if (!value || typeof value !== 'object') return '';

  // common language keys
  const preferred = ['en', 'gu', 'hi'];
  for (const k of preferred) {
    if (isNonEmptyString(value[k])) return value[k].trim();
  }

  // fallback: first string value in the object
  for (const k of Object.keys(value)) {
    if (isNonEmptyString(value[k])) return value[k].trim();
  }

  return '';
}

function getLocalizedStringForLanguage(value, language) {
  if (isNonEmptyString(value)) return value.trim();
  if (!value || typeof value !== 'object') return '';

  const priorityMap = {
    en: ['en', 'gu', 'hi'],
    gu: ['gu', 'hi', 'en'],
    hi: ['hi', 'en', 'gu'],
  };

  const priority = priorityMap[language] || priorityMap.en;
  for (const key of priority) {
    if (isNonEmptyString(value[key])) return value[key].trim();
  }

  return pickLocalizedString(value);
}

function hasLocalizedValue(value) {
  return pickLocalizedString(value) !== '';
}

function getEditableTitleField(modal) {
  const preferredFields = modal?.buttonActionType || modal?.isButtonOnly || isNonEmptyString(modal?.buttonType)
    ? ['linkTitle', 'title', 'infoModalTitle']
    : ['title', 'linkTitle', 'infoModalTitle'];

  for (const field of preferredFields) {
    if (hasLocalizedValue(modal?.[field])) {
      return field;
    }
  }
  return preferredFields[0];
}

function buildLocalizedFieldValue(values, existingValue) {
  const payload = {};

  ['en', 'gu', 'hi'].forEach((language) => {
    const value = values?.[language];
    if (isNonEmptyString(value)) {
      payload[language] = value.trim();
    }
  });

  if (Object.keys(payload).length === 0) {
    return '';
  }

  if (typeof existingValue === 'string') {
    return payload.en || pickLocalizedString(payload);
  }

  return payload;
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function getCheckedValue(groupName, fallback = '') {
  return document.querySelector(`input[name="${groupName}"]:checked`)?.value || fallback;
}

function toggleVisibility(ids, visible) {
  ids.forEach((id) => setSectionVisibility(id, visible));
}

function tryFindNodesArray(root) {
  // Most common shapes we’ve seen/expect:
  // - finalJson.nodes
  // - finalJson.tour.nodes
  // - finalJson.data.nodes
  if (root && Array.isArray(root.nodes)) return root.nodes;

  // Fallback: shallow scan one level deep for a `nodes` array.
  if (root && typeof root === 'object') {
    for (const key of Object.keys(root)) {
      const value = root[key];
      if (value && Array.isArray(value.nodes)) return value.nodes;
    }
  }
  return [];
}

function normalizeNodes(finalJson) {
  const nodes = tryFindNodesArray(finalJson);
  return nodes
    .filter((n) => n && typeof n === 'object')
    .map((node) => {
      const infoModals = safeArray(node?.infoPoints).length > 0 ? node.infoPoints : [];
      return {
        id: node.id ?? '',
        name: node.name ?? '',
        showInSideMenu: Boolean(node.showInSideMenu),
        sideMenuTitle: pickLocalizedString(node.sideMenuTitle) || '',
        sideMenuOrder:
          typeof node.sideMenuOrder === 'number' ? node.sideMenuOrder : null,
        infoModals: safeArray(infoModals),
      };
    })
    .filter((n) => n.showInSideMenu && n.infoModals.length > 0);
}

function findTheType(node) {
  if (isNonEmptyString(node?.type)) {
    if (node.type === 'button') return 'Redirect Button';
    if (node.type === 'image') return 'Images Modal';
    if (node.type === 'youtube') return 'YouTube Modal';
    if (node.type === 'audio') return 'Audio Modal';
    return 'Info Modal';
  }
  if (node?.buttonActionType) return 'Redirect Button';
  if (isNonEmptyString(node?.youtubeUrl) || isNonEmptyString(node?.videoUrl)) return 'YouTube Modal';
  if (isNonEmptyString(node?.audioUrl) || isNonEmptyString(node?.audio)) return 'Audio Modal';
  if (Array.isArray(node?.image) ? node.image.length > 0 : isNonEmptyString(node?.image)) return 'Images Modal';
  if (hasLocalizedValue(node?.infoModalDescription) || hasLocalizedValue(node?.description)) return 'Info Modal';
  return 'unknown type';
}

function render(nodes, query = '') {
  const listEl = document.getElementById('infomodalNodesList');
  const metaEl = document.getElementById('infomodalNodesMeta');
  const emptyEl = document.getElementById('infomodalNodesEmpty');
  if (!listEl || !metaEl || !emptyEl) return;

  const q = String(query ?? '').trim().toLowerCase();
  const filtered = !q
    ? nodes
    : nodes.filter((n) => {
      const hay = [
        n.id,
        n.name,
        n.sideMenuTitle,
        ...n.infoModals.map((m) =>
          pickLocalizedString(m?.title) || pickLocalizedString(m?.infoModalTitle) || '',
        ),
      ]
        .join(' ')
        .toLowerCase();
      return hay.includes(q);
    });

  const modalCount = filtered.reduce((acc, n) => acc + n.infoModals.length, 0);
  metaEl.textContent = `${filtered.length} node(s), ${modalCount} info modal(s)`;

  if (filtered.length === 0) {
    listEl.innerHTML = '';
    emptyEl.classList.remove('d-none');
    return;
  }

  emptyEl.classList.add('d-none');

  const accordionId = 'infomodalNodesAccordion';
  const html = [
    `<div class="accordion" id="${accordionId}">`,
    ...filtered.map((node, idx) => {
      const itemId = `${accordionId}-item-${idx}`;
      const headingId = `${accordionId}-heading-${idx}`;
      const collapseId = `${accordionId}-collapse-${idx}`;

      const title = escapeHtml(pickLocalizedString(node.sideMenuTitle) || node.name || node.id);
      const nodeId = escapeHtml(node.id);
      const badge = `<span class="badge bg-primary-subtle text-primary ms-2">${node.infoModals.length}</span>`;

      const modalsHtml = node.infoModals
        .map((m, mi) => {
          const mt = escapeHtml(
            pickLocalizedString(m?.title) ||
            pickLocalizedString(m?.infoModalTitle) ||
            pickLocalizedString(m?.linkTitle) || "Button select"
          );
          const desc = escapeHtml(
            pickLocalizedString(m?.description) ||
            pickLocalizedString(m?.infoModalDescription) ||
            '',
          );
          const type = findTheType(m);
          const iframe = escapeHtml(m?.iframeUrl || m?.infoModalIframeUrl || '');
          const link = escapeHtml(m?.link || m?.infoModalLink || '');
          const width = escapeHtml(m?.width || m?.infoModalWidth || '');
          const footerBtnTitle = escapeHtml(m?.footerButtonTitle || m?.infoModalFooterButtonTitle || '');
          const footerBtnLink = escapeHtml(m?.footerButtonLink || m?.infoModalFooterButtonLink || '');
          const footerText = escapeHtml(
            pickLocalizedString(m?.footerText) ||
            pickLocalizedString(m?.infoModalFooterText) ||
            '',
          );
          const sourceInfoPointId = escapeHtml(m?._sourceInfoPoint?.id || '');

          return `
            <div class="border rounded p-2 mb-2 bg-light">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="fw-semibold">${mt}</div>
                <div class="d-flex gap-2 align-items-center">
                  ${sourceInfoPointId ? `<span class="badge text-bg-secondary">infoPoint: ${sourceInfoPointId}</span>` : ''}
                  <button type="button" class="btn btn-sm btn-primary editInfoBtn" data-node-id="${escapeHtml(node.id)}" data-modal-index="${mi}">
                    <i class="ri-edit-line"></i> Edit
                  </button>
                </div>
              </div>
              <div class="mt-2 small">
                ${desc ? `<div><span class="text-muted">Description:</span> ${desc}</div>` : ''}
                ${iframe ? `<div class="mt-1"><span class="text-muted">Iframe:</span> <code>${iframe}</code></div>` : ''}
                ${link ? `<div class="mt-1"><span class="text-muted">Link:</span> <code>${link}</code></div>` : ''}
                ${width ? `<div class="mt-1"><span class="text-muted">Width:</span> <code>${width}</code></div>` : ''}
                ${footerText ? `<div class="mt-1"><span class="text-muted">Footer text:</span> ${footerText}</div>` : ''}
                ${footerBtnTitle || footerBtnLink ? `<div class="mt-1"><span class="text-muted">Footer button:</span> ${footerBtnTitle ? `<span class="me-1">${footerBtnTitle}</span>` : ''}${footerBtnLink ? `<code>${footerBtnLink}</code>` : ''}</div>` : ''}
              </div>
            </div>
          `;
        })
        .join('');

      return `
        <div class="accordion-item" id="${itemId}">
          <h2 class="accordion-header" id="${headingId}">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
              <span class="me-2">${title}</span>
              <span class="text-muted small">(${nodeId})</span>
              ${badge}
            </button>
          </h2>
          <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="${headingId}">
            <div class="accordion-body">
              ${modalsHtml}
            </div>
          </div>
        </div>
      `;
    }),
    `</div>`,
  ].join('');

  listEl.innerHTML = html;

  // Fallback toggle handler (some templates override accordion behavior).
  listEl.querySelectorAll('[data-bs-toggle="collapse"]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      const target = btn.getAttribute('data-bs-target');
      if (!target) return;
      const el = document.querySelector(target);
      const Collapse = window?.bootstrap?.Collapse;
      if (!el || !Collapse) return; // let bootstrap default behavior handle

      // Ensure it can always toggle closed.
      e.preventDefault();
      Collapse.getOrCreateInstance(el, { toggle: false }).toggle();

      // Edit button handlers
      listEl.querySelectorAll('.editInfoBtn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const nodeId = btn.getAttribute('data-node-id');
          const modalIndex = parseInt(btn.getAttribute('data-modal-index'), 10);
          const node = nodes.find((n) => n.id === nodeId);

          if (node && node.infoModals && node.infoModals[modalIndex]) {
            openEditModal(node.infoModals[modalIndex], node, modalIndex);
          }
        });
      });
    });
  });
}

/**
 * analyzeModalDataForVisibility(infoModal)
 * 
 * SMART VISIBILITY ANALYZER
 * Scans the modal object and determines which form sections to display
 * 
 * LOGIC:
 * For each category (title, description, link, media, etc), checks if
 * the modal has any non-empty data for that category. If yes, adds the
 * category to the visible sections list.
 * 
 * EXAMPLE:
 * If modal has: { title: 'My Title', description: 'My Desc', image: [...] }
 * This returns: ['title', 'description', 'media']
 * Then only those sections are shown in the form
 * 
 * BENEFITS:
 * - Cleaner UI: users only see relevant sections
 * - Reduces cognitive load
 * - Focuses attention on what matters
 * 
 * EXTENDING:
 * To add a new category, do this:
 * 1. Add section in EDIT_MODAL_VISIBILITY map
 * 2. Add check below:
 *    if (condition for your section) {
 *      visibleSections.push('myCategory');
 *    }
 * 3. In blade: add section with id matching EDIT_MODAL_VISIBILITY
 */
function analyzeModalDataForVisibility(infoModal) {
  const visibleSections = [];

  // Title/Image
  if (hasLocalizedValue(infoModal?.title) || hasLocalizedValue(infoModal?.infoModalTitle) || hasLocalizedValue(infoModal?.linkTitle)) {
    visibleSections.push('title');
  }

  // Description
  if (hasLocalizedValue(infoModal?.description) || hasLocalizedValue(infoModal?.infoModalDescription)) {
    visibleSections.push('description');
  }

  // Links
  if (isNonEmptyString(infoModal?.link) || isNonEmptyString(infoModal?.actionUrl) ||
    hasLocalizedValue(infoModal?.linkTitle) || hasLocalizedValue(infoModal?.infoModalLink)) {
    visibleSections.push('link');
  }

  // Modal Content
  if (hasLocalizedValue(infoModal?.infoModalTitle) || hasLocalizedValue(infoModal?.infoModalDescription) ||
    isNonEmptyString(infoModal?.infoModalIframeUrl) || isNonEmptyString(infoModal?.infoModalWidth)) {
    visibleSections.push('modal');
  }

  // Footer
  if (hasLocalizedValue(infoModal?.infoModalFooterButtonTitle) || isNonEmptyString(infoModal?.infoModalFooterButtonLink) ||
    hasLocalizedValue(infoModal?.infoModalFooterText)) {
    visibleSections.push('footer');
  }

  // Media (images, youtube, audio)
  if (infoModal?.image || isNonEmptyString(infoModal?.youtubeUrl) || isNonEmptyString(infoModal?.videoUrl) ||
    isNonEmptyString(infoModal?.audioUrl) || isNonEmptyString(infoModal?.audio)) {
    visibleSections.push('media');
  }

  // Icon
  if (isNonEmptyString(infoModal?.icon) || isNonEmptyString(infoModal?.iconColor) ||
    isNonEmptyString(infoModal?.iconClass) || isNonEmptyString(infoModal?.iconSize)) {
    visibleSections.push('icon');
  }

  // Button
  if (infoModal?.isButtonOnly || isNonEmptyString(infoModal?.buttonType) ||
    isNonEmptyString(infoModal?.buttonActionType) || isNonEmptyString(infoModal?.buttonAction) ||
    hasLocalizedValue(infoModal?.buttonText) || isNonEmptyString(infoModal?.buttonColor)) {
    visibleSections.push('button');
  }

  // Position
  if (infoModal?.position?.yaw !== undefined || infoModal?.position?.pitch !== undefined) {
    visibleSections.push('position');
  }

  return visibleSections;
}

/**
 * Update form visibility based on which sections have data
 */
function updateVisibilityFromAnalysis(visibleSections) {
  // Hide all sections first
  Object.values(EDIT_MODAL_VISIBILITY).flat().forEach((sectionId) => {
    setSectionVisibility(sectionId, false);
  });

  // Show only relevant sections
  visibleSections.forEach((category) => {
    const sections = EDIT_MODAL_VISIBILITY[category];
    if (sections) {
      sections.forEach((sectionId) => {
        setSectionVisibility(sectionId, true);
      });
    }
  });
}

/**
 * EditModalState
 * Global state tracker for the currently editing modal
 * 
 * PROPERTIES:
 * - currentInfoModal: Deep copy of modal being edited (for rollback if needed)
 * - currentNode: Reference to parent node containing this modal
 * - currentModalIndex: Index position in node.infoModals array
 * - currentTitleField: Which field stores the main title ('title' or 'linkTitle' or 'infoModalTitle')
 * 
 * USAGE:
 *   openEditModal() → set state
 *   User edits form
 *   Save button → use state to know what to update
 *   After save → reset() clears the state
 */
let EditModalState = {
  currentInfoModal: null,
  currentNode: null,
  currentModalIndex: null,
  currentTitleField: null,

  reset() {
    this.currentInfoModal = null;
    this.currentNode = null;
    this.currentModalIndex = null;
    this.currentTitleField = null;
  }
};

/**
 * Update node with edited modal data
 * @param {Object} node - The node to update
 * @param {Number} modalIndex - Index of modal in node's infoModals
 * @param {Object} newData - New modal data
 */
function updateNodeWithEditedModal(node, modalIndex, newData) {
  if (!node || !node.infoModals || !node.infoModals[modalIndex]) {
    console.error('Invalid node or modal index');
    return false;
  }
  const modal = node.infoModals[modalIndex];
  const titleField = EditModalState.currentTitleField || getEditableTitleField(modal);

  // Tooltip (visible) fields
  const titleValue = buildLocalizedFieldValue(newData.tooltipTitle, modal.title || modal[titleField] || modal.infoModalTitle);
  modal.title = titleValue;
  modal[titleField] = titleValue;
  modal.infoModalTitle = titleValue;

  const descValue = buildLocalizedFieldValue(newData.tooltipDescription, modal.description || modal.infoModalDescription);
  modal.description = descValue;
  modal.infoModalDescription = descValue;
  modal.tooltipPosition = newData.tooltipPosition || 'down';

  // Button / Link localized values
  modal.buttonText = buildLocalizedFieldValue(newData.buttonText, modal.buttonText);
  modal.linkTitle = buildLocalizedFieldValue(newData.linkTitle, modal.linkTitle);
  modal.infoModalLink = buildLocalizedFieldValue(newData.infoModalLink, modal.infoModalLink || modal.link || modal.actionUrl);
  // actionUrl is non-localized
  modal.actionUrl = newData.actionUrl || modal.actionUrl || modal.link || modal.infoModalLink;

  // Modal content (localized)
  const modalTitleVal = buildLocalizedFieldValue(newData.modalTitle, modal.infoModalTitle || modal.modalTitle);
  modal.infoModalTitle = modalTitleVal;
  modal.modalTitle = modalTitleVal;

  const modalDescVal = buildLocalizedFieldValue(newData.modalDescription, modal.infoModalDescription || modal.modalDescription);
  modal.infoModalDescription = modalDescVal;
  modal.modalDescription = modalDescVal;

  // Footer localized values
  modal.infoModalFooterButtonTitle = buildLocalizedFieldValue(newData.infoModalFooterButtonTitle, modal.infoModalFooterButtonTitle);
  modal.infoModalFooterText = buildLocalizedFieldValue(newData.infoModalFooterText, modal.infoModalFooterText);
  modal.infoModalFooterButtonLink = newData.infoModalFooterButtonLink || modal.infoModalFooterButtonLink || modal.infoModalFooterLink || '';

  // Iframe / size / media
  modal.infoModalIframeUrl = newData.infoModalIframeUrl || modal.infoModalIframeUrl || modal.iframeUrl || '';
  modal.infoModalSize = newData.infoModalSize || modal.infoModalSize || modal.infoModalWidth || '';
  modal.image = Array.isArray(newData.imageUrls) ? newData.imageUrls : (modal.image || newData.imageUrls || modal.image);
  modal.youtubeUrl = newData.youtubeUrl || modal.youtubeUrl || modal.videoUrl || '';
  modal.audioUrl = newData.audioUrl || modal.audioUrl || modal.audio || '';

  // Persist back to global tour JSON if present
  const updatedNodes = window.tourFinalJson?.nodes || window.tourFinalJson?.tour?.nodes || [];
  const nodeIndex = updatedNodes.findIndex((n) => n.id === node.id);
  if (nodeIndex >= 0) {
    updatedNodes[nodeIndex] = node;
    return true;
  }

  return false;
}

// tooltip section Update function in modal
function renderToottipSection({
  container,
  fields,
  tooltipPosition = 'down',
  name = 'tooltip'
}) {
  if (!container) throw new Error('Container is required');

  const safeId = (str) => str.replace(/[^a-z0-9]/gi, '_');

  const capitalize = (str) =>
    str.charAt(0).toUpperCase() + str.slice(1);

  // collect all languages from all fields
  const langs = Array.from(
    new Set(
      Object.values(fields)
        .flatMap(field => Object.keys(field || {}))
    )
  );

  // nav tabs
  const tabs = langs.map((lang, i) => {
    const id = safeId(lang);

    return `
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link ${i === 0 ? 'active' : ''}"
                    id="${name}-${id}-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#${name}-${id}"
                    type="button"
                    role="tab"
                    aria-controls="${name}-${id}"
                    aria-selected="${i === 0}">
                    ${lang}
                </button>
            </li>
        `;
  }).join('');

  // tab content
  const panes = langs.map((lang, i) => {
    const id = safeId(lang);

    return `
            <div
                class="tab-pane fade ${i === 0 ? 'show active' : ''}"
                id="${name}-${id}"
                role="tabpanel"
                aria-labelledby="${name}-${id}-tab">

                ${Object.entries(fields).map(([fieldName, fieldData]) => `
                    <div class="mb-3">
                        <label class="form-label">
                            ${capitalize(fieldName)} (${lang})
                        </label>

                        ${fieldName.toLowerCase().includes('description')
        ? `
                                    <textarea
                                        class="form-control"
                                        name="${name}[${fieldName}][${lang}]"
                                        rows="3"
                                    >${fieldData?.[lang] || ''}</textarea>
                                `
        : `
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="${name}[${fieldName}][${lang}]"
                                        value="${fieldData?.[lang] || ''}"
                                    >
                                `
      }
                    </div>
                `).join('')}
            </div>
        `;
  }).join('');

  // tooltip position section
  const positionOptions = ['up', 'down', 'left', 'right'];

  const positionHtml = `
        <div id="tooltipPositionSection" class="mb-4 p-3 border rounded mt-4">
            <h6 class="mb-3 fw-semibold">Tooltip Position</h6>
            <div>
                ${positionOptions.map(pos => `
                    <div class="form-check form-check-inline">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="${name}[titleTooltipPosition]"
                            id="${name}-pos-${pos}"
                            value="${pos}"
                            ${tooltipPosition === pos ? 'checked' : ''}
                        >
                        <label
                            class="form-check-label"
                            for="${name}-pos-${pos}">
                            ${capitalize(pos)}
                        </label>
                    </div>
                `).join('')}
            </div>
        </div>
    `;

  container.innerHTML = `
        <h5 class="mb-3 fw-semibold">Tooltip Section</h5>
        <ul class="nav nav-tabs" role="tablist">
            ${tabs}
        </ul>
        <div class="tab-content mt-3">
            ${panes}
        </div>
        ${positionHtml}
    `;
}

// helper
function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * openEditModal(infoModal, node, modalIndex)
 * 
 * OPENS THE EDIT FORM AND POPULATES IT WITH EXISTING DATA
 * 
 * WHAT IT DOES:
 * 1. Stores the modal in EditModalState for later saving
 * 2. Detects what type of modal it is (button, image, youtube, audio, etc)
 * 3. Fills in all form fields from the modal's existing data
 * 4. Analyzes which sections have data
 * 5. Shows only the relevant sections (hides empty ones)
 * 6. Updates preview elements (icon preview, etc)
 * 7. Displays the modal to the user
 * 
 * RESULT:
 * User sees a clean form with only relevant fields, pre-filled with their data
 * 
 * FLOW:
 *   User clicks "Edit"
 *        ↓
 *   openEditModal() called
 *        ↓
 *   Form is populated from existing data
 *        ↓
 *   Only non-empty sections are shown
 *        ↓
 *   Modal appears ready to edit
 */
function openEditModal(infoModal, node, modalIndex) {
  const modalEl = document.getElementById('editInfoModal');
  if (!modalEl) return;

  // STORE STATE - so we know what to update when user saves
  EditModalState.currentInfoModal = JSON.parse(JSON.stringify(infoModal));
  EditModalState.currentNode = node;
  EditModalState.currentModalIndex = modalIndex;
  EditModalState.currentTitleField = getEditableTitleField(infoModal);

  console.log('Editing modal:', EditModalState);

  // DETECT MODAL TYPE
  // If no explicit type, try to detect from content
  let modalType = infoModal.type || 'none';
  if (!infoModal.type) {
    if (infoModal.buttonActionType || infoModal.isButtonOnly || isNonEmptyString(infoModal.buttonType)) {
      modalType = 'button';
    } else if (isNonEmptyString(infoModal.youtubeUrl) || isNonEmptyString(infoModal.videoUrl)) {
      modalType = 'youtube';
    } else if (isNonEmptyString(infoModal.audioUrl) || isNonEmptyString(infoModal.audio)) {
      modalType = 'audio';
    } else if (Array.isArray(infoModal.image) ? infoModal.image.length > 0 : isNonEmptyString(infoModal.image)) {
      modalType = 'image';
    }
  }

  let tooltipTitleLang = Object.keys(infoModal.title || {});
  renderToottipSection({
    container: document.getElementById('tooltipSection'),
    fields: {
      title: infoModal.title,
      description: infoModal.description
    },
    tooltipPosition: infoModal.titleTooltipPosition
  });

  // SET TYPE & POSITION IN FORM
  const typeInput = document.querySelector(`input[name="infoType"][value="${modalType}"]`);
  if (typeInput) typeInput.checked = true;

  const position = infoModal.tooltipPosition || 'down';
  const posInput = document.querySelector(`input[name="tooltipPosition"][value="${position}"]`);
  if (posInput) posInput.checked = true;

  // POPULATE ENGLISH FIELDS
  const tooltipSourceTitle = infoModal.title || infoModal[EditModalState.currentTitleField] || infoModal.infoModalTitle;
  const tooltipTitleEN = getLocalizedStringForLanguage(tooltipSourceTitle, 'en') || '';
  const tooltipTitleGU = getLocalizedStringForLanguage(tooltipSourceTitle, 'gu') || '';

  const tooltipSourceDescription = infoModal.description || infoModal.infoModalDescription;
  const descriptionEN = getLocalizedStringForLanguage(tooltipSourceDescription, 'en') || '';
  const descriptionGU = getLocalizedStringForLanguage(tooltipSourceDescription, 'gu') || '';

  // document.getElementById('tooltipTitleEN').value = tooltipTitleEN;
  // document.getElementById('tooltipTitleGU').value = tooltipTitleGU;
  // document.getElementById('tooltipDescriptionEN').value = descriptionEN;
  // document.getElementById('tooltipDescriptionGU').value = descriptionGU;

  // POPULATE MEDIA & LINK FIELDS
  document.getElementById('imageUrls').value = Array.isArray(infoModal.image) ? infoModal.image.join('\n') : (infoModal.image || '');
  document.getElementById('youtubeUrl').value = infoModal.youtubeUrl || infoModal.videoUrl || '';
  document.getElementById('audioUrl').value = infoModal.audio || infoModal.audioUrl || '';
  document.getElementById('actionUrl').value = infoModal.link || infoModal.actionUrl || '';

  // POPULATE BUTTON TEXT LOCALIZED
  if (document.getElementById('buttonTextEN')) document.getElementById('buttonTextEN').value = getLocalizedStringForLanguage(infoModal.buttonText, 'en') || '';
  if (document.getElementById('buttonTextGU')) document.getElementById('buttonTextGU').value = getLocalizedStringForLanguage(infoModal.buttonText, 'gu') || '';

  // POPULATE TITLES & MODAL CONTENT
  document.getElementById('linkTitleEN').value = getLocalizedStringForLanguage(infoModal.linkTitle, 'en') || '';
  document.getElementById('linkTitleGU').value = getLocalizedStringForLanguage(infoModal.linkTitle, 'gu') || '';
  document.getElementById('infoModalLinkEN').value = getLocalizedStringForLanguage(infoModal.infoModalLink, 'en') || '';
  document.getElementById('infoModalLinkGU').value = getLocalizedStringForLanguage(infoModal.infoModalLink, 'gu') || '';

  document.getElementById('modalTitleEN').value = getLocalizedStringForLanguage(infoModal.infoModalTitle || infoModal.modalTitle, 'en') || '';
  document.getElementById('modalTitleGU').value = getLocalizedStringForLanguage(infoModal.infoModalTitle || infoModal.modalTitle, 'gu') || '';
  setEditorValue('modalDescriptionEN', getLocalizedStringForLanguage(infoModal.infoModalDescription || infoModal.modalDescription, 'en') || '');
  setEditorValue('modalDescriptionGU', getLocalizedStringForLanguage(infoModal.infoModalDescription || infoModal.modalDescription, 'gu') || '');

  document.getElementById('infoModalIframeUrl').value = infoModal.infoModalIframeUrl || infoModal.iframeUrl || '';

  // MODAL SIZE MAPPING
  const sizeEl = document.getElementById('infoModalSize');
  if (sizeEl) {
    const val = infoModal.infoModalWidth || infoModal.infoModalSize || infoModal.infoModalWidth || 'modal-md';
    Array.from(sizeEl.options).forEach((opt) => opt.selected = (opt.value === val));
  }

  document.getElementById('infoModalFooterButtonTitleEN').value = getLocalizedStringForLanguage(infoModal.infoModalFooterButtonTitle, 'en') || '';
  document.getElementById('infoModalFooterButtonTitleGU').value = getLocalizedStringForLanguage(infoModal.infoModalFooterButtonTitle, 'gu') || '';
  document.getElementById('infoModalFooterButtonLink').value = infoModal.infoModalFooterButtonLink || infoModal.infoModalFooterButtonLink || infoModal.infoModalFooterLink || infoModal.infoModalFooterButtonLinkUrl || '';
  document.getElementById('infoModalFooterTextEN').value = getLocalizedStringForLanguage(infoModal.infoModalFooterText, 'en') || '';
  document.getElementById('infoModalFooterTextGU').value = getLocalizedStringForLanguage(infoModal.infoModalFooterText, 'gu') || '';

  // POPULATE ICON, BUTTON & BEHAVIOUR SETTINGS
  document.getElementById('infoPointIcon').value = infoModal.icon || infoModal.iconClass || '';
  document.getElementById('infoPointIconColor').value = infoModal.iconColor || '#3a3abb';
  document.getElementById('infoPointIconSize').value = infoModal.iconSize || 'medium';

  document.getElementById('isButtonOnly').checked = Boolean(infoModal.isButtonOnly);
  document.getElementById('buttonType').value = infoModal.buttonType || '';
  document.getElementById('buttonActionType').value = infoModal.buttonActionType || infoModal.buttonAction || '';
  if (document.getElementById('buttonColor')) document.getElementById('buttonColor').value = infoModal.buttonColor || '#3a3abb';
  if (document.getElementById('buttonTextColor')) document.getElementById('buttonTextColor').value = infoModal.buttonTextColor || '#ffffff';
  if (document.getElementById('buttonSize')) document.getElementById('buttonSize').value = infoModal.buttonSize || '';
  if (document.getElementById('buttonNodeId')) document.getElementById('buttonNodeId').value = infoModal.buttonNodeId || infoModal.buttonNodeId || '';

  document.getElementById('showOnLoad').checked = Boolean(infoModal.showOnLoad);
  if (document.getElementById('showOnLoadDelayMs')) document.getElementById('showOnLoadDelayMs').value = infoModal.showOnLoadDelayMs || infoModal.showOnLoadDelay || 0;

  // POPULATE POSITION (AUTO-SET FROM VIEWER)
  if (document.getElementById('positionYaw')) document.getElementById('positionYaw').value = infoModal.position?.yaw ?? '';
  if (document.getElementById('positionPitch')) document.getElementById('positionPitch').value = infoModal.position?.pitch ?? '';

  // SMART VISIBILITY - SHOW ONLY SECTIONS WITH DATA
  const visibleSections = analyzeModalDataForVisibility(infoModal);
  updateVisibilityFromAnalysis(visibleSections);
  updateCharacterCountsAll();

  const modal = window.bootstrap?.Modal.getOrCreateInstance(modalEl);
  modal.show();
}

/**
 * Normalize textarea / editor content.
 */
function getInputValue(fieldId) {
  return document.getElementById(fieldId)?.value || '';
}

function setInputValue(fieldId, value) {
  const field = document.getElementById(fieldId);
  if (field) {
    field.value = value || '';
  }
}

function setEditorValue(fieldId, value) {
  const editor = window.tinymce?.get(fieldId);
  if (editor) {
    editor.setContent(value || '');
    return;
  }
  setInputValue(fieldId, value);
}

function getEditorValue(fieldId) {
  const editor = window.tinymce?.get(fieldId);
  if (editor) {
    return editor.getContent({ format: 'html' });
  }
  return getInputValue(fieldId);
}

function setSectionVisibility(fieldId, visible) {
  const section = document.getElementById(fieldId);
  if (section) {
    section.classList.toggle('d-none', !visible);
  }
}

function parseMultiValueLines(value) {
  return String(value || '')
    .split(/\r?\n/)
    .map((line) => line.trim())
    .filter(Boolean);
}

function updateIconPreview() {
  const iconName = getInputValue('infoPointIcon') || 'radio_button_unchecked';
  const iconPreview = document.getElementById('infoPointIconPreview');
  if (!iconPreview) return;
  iconPreview.textContent = iconName;
  iconPreview.style.color = getInputValue('infoPointIconColor') || '#3a3abb';
  iconPreview.style.fontSize = getIconSizeInPixels(getInputValue('infoPointIconSize') || 'medium');
}

/**
 * Update modal fields visibility based on selected type and button type.
 */
function updateModalFieldsVisibility() {
  const selectedType = getCheckedValue('infoType', 'none');
  // Only show tooltip fields when the selected type is 'none'
  const showTooltip = selectedType === 'none';
  toggleVisibility(EDIT_MODAL_VISIBILITY.tooltip, showTooltip);
}

function updateCharacterCount(fieldId) {
  const field = document.getElementById(fieldId);
  const countEl = document.getElementById(`${fieldId}Count`);
  if (field && countEl) {
    countEl.textContent = field.value.length;
  }
}

function updateCharacterCountsAll() {
  EDIT_MODAL_TEXT_FIELD_IDS.forEach(updateCharacterCount);
}

function setupColorInputs(baseName) {
  const colorInput = document.getElementById(baseName);
  if (!colorInput) return;

  colorInput.addEventListener('change', () => {
    if (baseName === 'infoPointIconColor') {
      updateIconPreview();
    }
    updateButtonPreview();
  });
}

function getIconSizeInPixels(size) {
  const sizes = { small: '20px', medium: '32px', large: '40px' };
  return sizes[size] || '32px';
}

function updateButtonPreview() {
  const selectedType = getCheckedValue('infoType', 'none');
  const preview = document.getElementById('buttonPreview');
  if (!preview || selectedType !== 'button') return;

  const selectedButtonAppearance = getCheckedValue('buttonType', 'textButton');
  const iconName = getInputValue('infoPointIcon');
  const iconColor = getInputValue('infoPointIconColor') || '#3a3abb';
  const buttonText = getInputValue('buttonTextEN') || getInputValue('buttonTextGU') || 'Button';

  preview.className = 'btn btn-primary d-inline-flex align-items-center gap-2';

  if (selectedButtonAppearance === 'iconButton' && iconName) {
    preview.innerHTML = `<span class="material-icons-outlined" style="color:${iconColor};">${iconName}</span><span>${buttonText}</span>`;
  } else {
    preview.innerHTML = buttonText;
  }
}

function validateFormData() {
  const selectedType = getCheckedValue('infoType', 'none');
  const hasTooltipTitle = isNonEmptyString(getInputValue('tooltipTitleEN')) || isNonEmptyString(getInputValue('tooltipTitleGU'));

  // Require tooltip title only when editing the simplified 'none' type
  if (selectedType === 'none' && !hasTooltipTitle) {
    alert('Please enter at least one tooltip title');
    return false;
  }

  return true;
}

function getFormState() {
  const selectedType = getCheckedValue('infoType', 'none');

  return {
    type: selectedType,
    tooltipPosition: document.querySelector('input[name="tooltipPosition"]:checked')?.value || 'down',
    tooltipTitle: {
      en: getInputValue('tooltipTitleEN'),
      gu: getInputValue('tooltipTitleGU'),
    },
    tooltipDescription: {
      en: getInputValue('tooltipDescriptionEN'),
      gu: getInputValue('tooltipDescriptionGU'),
    },
    // Button / Link localized
    buttonText: {
      en: getInputValue('buttonTextEN'),
      gu: getInputValue('buttonTextGU'),
    },
    linkTitle: {
      en: getInputValue('linkTitleEN'),
      gu: getInputValue('linkTitleGU'),
    },
    infoModalLink: {
      en: getInputValue('infoModalLinkEN'),
      gu: getInputValue('infoModalLinkGU'),
    },
    actionUrl: getInputValue('actionUrl'),
    // Modal localized
    modalTitle: {
      en: getInputValue('modalTitleEN'),
      gu: getInputValue('modalTitleGU'),
    },
    modalDescription: {
      en: getEditorValue('modalDescriptionEN'),
      gu: getEditorValue('modalDescriptionGU'),
    },
    infoModalFooterButtonTitle: {
      en: getInputValue('infoModalFooterButtonTitleEN'),
      gu: getInputValue('infoModalFooterButtonTitleGU'),
    },
    infoModalFooterText: {
      en: getInputValue('infoModalFooterTextEN'),
      gu: getInputValue('infoModalFooterTextGU'),
    },
    infoModalFooterButtonLink: getInputValue('infoModalFooterButtonLink'),
    infoModalIframeUrl: getInputValue('infoModalIframeUrl'),
    infoModalSize: getInputValue('infoModalSize') || (document.getElementById('infoModalSize')?.value || ''),
    imageUrls: parseMultiValueLines(getInputValue('imageUrls')),
    youtubeUrl: getInputValue('youtubeUrl'),
    audioUrl: getInputValue('audioUrl'),
  };
}

function setupEditModalEvents() {
  const modal = document.getElementById('editInfoModal');
  if (!modal) return;
  // Text inputs (tooltip fields) — only wire text inputs

  EDIT_MODAL_TEXT_FIELD_IDS.forEach((fieldId) => {
    const field = document.getElementById(fieldId);
    if (field) {
      field.addEventListener('input', () => {
        updateCharacterCount(fieldId);
      });
    }
  });

  document.getElementById('editInfoSaveBtn')?.addEventListener('click', () => {
    if (!validateFormData()) return;

    const state = getFormState();
    const success = updateNodeWithEditedModal(
      EditModalState.currentNode,
      EditModalState.currentModalIndex,
      state
    );

    if (success) {
      const modalEl = document.getElementById('editInfoModal');
      window.bootstrap?.Modal.getInstance(modalEl)?.hide();
      document.getElementById('infomodalNodesRefreshBtn')?.click();
      EditModalState.reset();
    }
  });
}

function init() {
  const searchEl = document.getElementById('infomodalNodesSearch');
  const refreshBtn = document.getElementById('infomodalNodesRefreshBtn');
  if (!searchEl || !refreshBtn) return;

  iconLib.init('materialIconModal', 'materialIconSearch', 'materialIconModalClose');
  reinitalizeEditors();

  const nodes = normalizeNodes(window.tourFinalJson || {});
  render(nodes, searchEl.value);

  const onChange = () => render(nodes, searchEl.value);
  searchEl.addEventListener('input', onChange);
  refreshBtn.addEventListener('click', () => {
    searchEl.value = '';
    render(nodes, '');
  });

  // Initialize edit modal
  setupEditModalEvents();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

