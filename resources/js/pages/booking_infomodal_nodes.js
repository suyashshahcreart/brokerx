import $ from 'jquery';
window.$ = window.jQuery = $;
import '../../css/pages/materialIconLiberaryStyles.css';
import iconLib from './booking_tour_iconLib';
import reinitalizeEditors from '../tinyEditor';

/* global window, document */

const EDIT_MODAL_TEXT_FIELD_IDS = [
  'tooltipTitleEN', 'tooltipTitleGU', 'tooltipDescriptionEN', 'tooltipDescriptionGU',
  'buttonTextEN', 'buttonTextGU',
  'modalTitleEN', 'modalTitleGU'
];

const EDIT_MODAL_MEDIA_FIELD_IDS = [
  'infoPointIcon', 'infoPointIconColor', 'infoPointIconSize',
  'imageUrls', 'youtubeUrl', 'audioUrl',
  'actionUrl', 'infoModalSize', 'infoModalFooterLinkUrl'
];

const EDIT_MODAL_VISIBILITY = {
  buttonType: ['buttonTypeSection'],
  buttonAction: ['buttonActionSection'],
  buttonPreview: ['buttonPreviewSection'],
  tooltip: ['tooltipTitleENSection', 'tooltipTitleGUSection', 'tooltipDescriptionENSection', 'tooltipDescriptionGUSection', 'tooltipPositionSection'],
  image: ['imageSection'],
  youtube: ['youtubeSection'],
  audio: ['audioSection'],
  textButton: ['buttonTextENSection', 'buttonTextGUSection'],
  iconButton: ['iconSection'],
  actionUrl: ['actionUrlSection'],
  actionModal: ['actionModalSection', 'modalTitleENSection', 'modalTitleGUSection', 'modalDescriptionENSection', 'modalDescriptionGUSection'],
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

function render(nodes, query='') {
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
 * Edit Modal State Management
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

  modal.infoModalTitle = buildLocalizedFieldValue(newData.tooltipTitle, modal.infoModalTitle || modal[titleField]);
  modal.infoModalDescription = buildLocalizedFieldValue(newData.tooltipDescription, modal.infoModalDescription);
  modal.tooltipPosition = newData.tooltipPosition || 'up';
  modal.icon = newData.icon || '';
  modal.iconColor = newData.iconColor || '#3a3abb';
  modal.iconSize = newData.iconSize || 'medium';
  modal.imageUrls = Array.isArray(newData.imageUrls) ? newData.imageUrls : [];
  modal.image = modal.imageUrls;
  modal.youtubeUrl = newData.youtubeUrl || '';
  modal.audioUrl = newData.audioUrl || '';

  // Button appearance (text or icon) and action (redirect, modal, navigate, etc.)
  modal.buttonAppearance = newData.buttonAppearance || 'textButton';
  modal.buttonAction = newData.buttonAction || 'redirectToLink';

  // Button text (for both text and icon buttons)
  modal.buttonText = buildLocalizedFieldValue(newData.buttonText, modal.buttonText);

  // URL for redirect, image, video, document actions
  modal.actionUrl = newData.actionUrl || '';

  // Modal-specific fields (for openInfoModal action)
  modal.modalTitle = buildLocalizedFieldValue(newData.modalTitle, modal.modalTitle || modal.infoModalTitle);
  modal.modalDescription = buildLocalizedFieldValue(newData.modalDescription, modal.modalDescription);
  modal.infoModalSize = newData.infoModalSize || 'medium';
  modal.infoModalFooterLinkUrl = newData.infoModalFooterLinkUrl || '';

  modal.isButtonOnly = newData.type === 'button';
  modal.type = newData.type;

  // Set main title field
  if (newData.type === 'button') {
    modal[titleField] = buildLocalizedFieldValue(newData.buttonText, modal[titleField]);
  }

  // Ensure title field fallback
  if (titleField === 'infoModalTitle' && !hasLocalizedValue(modal.title)) {
    modal.title = modal[titleField];
  }

  // Update main json
  const updatedNodes = window.tourFinalJson?.nodes || window.tourFinalJson?.tour?.nodes || [];
  const nodeIndex = updatedNodes.findIndex((n) => n.id === node.id);
  if (nodeIndex >= 0) {
    updatedNodes[nodeIndex] = node;
    return true;
  }

  return false;
}

/**
 * Open edit modal for a specific info modal
 */
function openEditModal(infoModal, node, modalIndex) {
  const modalEl = document.getElementById('editInfoModal');
  if (!modalEl) return;

  // Initialize state
  EditModalState.currentInfoModal = JSON.parse(JSON.stringify(infoModal));
  EditModalState.currentNode = node;
  EditModalState.currentModalIndex = modalIndex;
  EditModalState.currentTitleField = getEditableTitleField(infoModal);

  // Detect modal type
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

  // Set type radio button
  const typeInput = document.querySelector(`input[name="infoType"][value="${modalType}"]`);
  if (typeInput) typeInput.click();

  // Set tooltip position
  const position = infoModal.tooltipPosition || 'up';
  const posInput = document.querySelector(`input[name="tooltipPosition"][value="${position}"]`);
  if (posInput) posInput.click();

  // Get localized values with fallbacks
  const tooltipTitleEN = getLocalizedStringForLanguage(infoModal[EditModalState.currentTitleField], 'en') || '';
  const tooltipTitleGU = getLocalizedStringForLanguage(infoModal[EditModalState.currentTitleField], 'gu') || '';

  const description = pickLocalizedString(infoModal.infoModalDescription);
  const descriptionEN = getLocalizedStringForLanguage(infoModal.infoModalDescription, 'en') || '';
  const descriptionGU = getLocalizedStringForLanguage(infoModal.infoModalDescription, 'gu') || '';

  // Populate tooltip fields
  document.getElementById('tooltipTitleEN').value = tooltipTitleEN;
  document.getElementById('tooltipTitleGU').value = tooltipTitleGU;
  document.getElementById('tooltipDescriptionEN').value = descriptionEN;
  document.getElementById('tooltipDescriptionGU').value = descriptionGU;
  document.getElementById('infoPointIcon').value = infoModal.icon || infoModal.iconClass || '';
  document.getElementById('infoPointIconColor').value = infoModal.iconColor || '#3a3abb';
  document.getElementById('infoPointIconSize').value = infoModal.iconSize || 'medium';
  updateIconPreview();

  document.getElementById('imageUrls').value = Array.isArray(infoModal.imageUrls)
    ? infoModal.imageUrls.join('\n')
    : (Array.isArray(infoModal.image) ? infoModal.image.join('\n') : (infoModal.image || ''));
  document.getElementById('youtubeUrl').value = infoModal.youtubeUrl || infoModal.videoUrl || '';
  document.getElementById('audioUrl').value = infoModal.audioUrl || '';

  // If button type, populate button-specific fields
  if (modalType === 'button') {
    // Determine button appearance: text or icon
    const hasIcon = isNonEmptyString(infoModal.icon) || isNonEmptyString(infoModal.iconClass);
    const buttonAppearance = hasIcon ? 'iconButton' : 'textButton';

    // Set button appearance
    const appearanceInput = document.querySelector(`input[name="buttonType"][value="${buttonAppearance}"]`);
    if (appearanceInput) appearanceInput.click();

    // Populate text button fields
    const buttonTextEN = getLocalizedStringForLanguage(infoModal.linkButtonText || infoModal.buttonText, 'en') || '';
    const buttonTextGU = getLocalizedStringForLanguage(infoModal.linkButtonText || infoModal.buttonText, 'gu') || '';
    document.getElementById('buttonTextEN').value = buttonTextEN;
    document.getElementById('buttonTextGU').value = buttonTextGU;

    // Populate icon button fields
    document.getElementById('infoPointIcon').value = infoModal.icon || infoModal.iconClass || '';
    document.getElementById('infoPointIconColor').value = infoModal.iconColor || '#3a3abb';
    document.getElementById('infoPointIconSize').value = infoModal.iconSize || 'medium';
    updateIconPreview();

    // Set button action
    const buttonAction = infoModal.buttonActionType || infoModal.buttonAction || 'redirectToLink';
    const actionInput = document.querySelector(`input[name="buttonAction"][value="${buttonAction}"]`);
    if (actionInput) actionInput.click();

    // Populate action-specific fields
    const isUrlAction = ['redirectToLink', 'openImage', 'openVideo', 'openDocument'].includes(buttonAction);
    const isModalAction = buttonAction === 'openInfoModal';

    if (isUrlAction) {
      const urlValue = infoModal.linkButtonUrl || infoModal.link || infoModal.actionUrl || '';
      document.getElementById('actionUrl').value = urlValue;
    }

    if (isModalAction) {
      const modalTitleEN = getLocalizedStringForLanguage(infoModal.modalTitle || infoModal.infoModalTitle, 'en') || '';
      const modalTitleGU = getLocalizedStringForLanguage(infoModal.modalTitle || infoModal.infoModalTitle, 'gu') || '';
      const modalDescriptionEN = getLocalizedStringForLanguage(infoModal.modalDescription || infoModal.infoModalDescription, 'en') || '';
      const modalDescriptionGU = getLocalizedStringForLanguage(infoModal.modalDescription || infoModal.infoModalDescription, 'gu') || '';

      document.getElementById('modalTitleEN').value = modalTitleEN;
      document.getElementById('modalTitleGU').value = modalTitleGU;
      setEditorValue('modalDescriptionEN', modalDescriptionEN);
      setEditorValue('modalDescriptionGU', modalDescriptionGU);

      document.getElementById('infoModalSize').value = infoModal.infoModalSize || 'medium';
      document.getElementById('infoModalFooterLinkUrl').value = infoModal.infoModalFooterLinkUrl || infoModal.modalFooterButtonLinkUrl || '';
    }
  }

  // Update UI and show modal
  updateModalFieldsVisibility();
  updateCharacterCountsAll();
  updateButtonPreview();

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
  const selectedButtonType = getCheckedValue('buttonType', 'textButton');
  const selectedAction = getCheckedValue('buttonAction', 'redirectToLink');

  const isButtonType = selectedType === 'button';

  // Type-based visibility
  toggleVisibility(EDIT_MODAL_VISIBILITY.buttonType, isButtonType);
  toggleVisibility(EDIT_MODAL_VISIBILITY.buttonAction, isButtonType);
  toggleVisibility(EDIT_MODAL_VISIBILITY.buttonPreview, isButtonType);
  toggleVisibility(EDIT_MODAL_VISIBILITY.tooltip, !isButtonType);
  toggleVisibility(EDIT_MODAL_VISIBILITY.image, selectedType === 'image');
  toggleVisibility(EDIT_MODAL_VISIBILITY.youtube, selectedType === 'youtube');
  toggleVisibility(EDIT_MODAL_VISIBILITY.audio, selectedType === 'audio');

  // Button appearance visibility (text vs icon)
  if (isButtonType) {
    toggleVisibility(EDIT_MODAL_VISIBILITY.textButton, selectedButtonType === 'textButton');
    toggleVisibility(EDIT_MODAL_VISIBILITY.iconButton, selectedButtonType === 'iconButton');

    // Button action visibility
    const isUrlAction = ['redirectToLink', 'openImage', 'openVideo', 'openDocument'].includes(selectedAction);
    const isModalAction = selectedAction === 'openInfoModal';

    toggleVisibility(EDIT_MODAL_VISIBILITY.actionUrl, isUrlAction);
    toggleVisibility(EDIT_MODAL_VISIBILITY.actionModal, isModalAction);
  }

  updateButtonPreview();
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
  const selectedAction = getCheckedValue('buttonAction', 'redirectToLink');

  const hasTooltipTitle = isNonEmptyString(getInputValue('tooltipTitleEN')) || isNonEmptyString(getInputValue('tooltipTitleGU'));
  const hasButtonText = isNonEmptyString(getInputValue('buttonTextEN')) || isNonEmptyString(getInputValue('buttonTextGU'));
  const hasModalTitle = isNonEmptyString(getInputValue('modalTitleEN')) || isNonEmptyString(getInputValue('modalTitleGU'));

  if (selectedType !== 'button' && !hasTooltipTitle) {
    alert('Please enter at least one tooltip title');
    return false;
  }

  if (selectedType === 'image' && parseMultiValueLines(getInputValue('imageUrls')).length === 0) {
    alert('Please add at least one image URL');
    return false;
  }

  if (selectedType === 'youtube' && !isNonEmptyString(getInputValue('youtubeUrl'))) {
    alert('Please enter a YouTube video link');
    return false;
  }

  if (selectedType === 'audio' && !isNonEmptyString(getInputValue('audioUrl'))) {
    alert('Please enter an audio link');
    return false;
  }

  if (selectedType === 'button') {
    if (!hasButtonText) {
      alert('Please enter button text');
      return false;
    }

    // Validate action-specific fields
    const isUrlAction = ['redirectToLink', 'openImage', 'openVideo', 'openDocument'].includes(selectedAction);
    const isModalAction = selectedAction === 'openInfoModal';

    if (isUrlAction && !isNonEmptyString(getInputValue('actionUrl'))) {
      alert('Please enter a URL for the button action');
      return false;
    }

    if (isModalAction && !hasModalTitle) {
      alert('Please enter a modal title');
      return false;
    }
  }

  return true;
}

function getFormState() {
  const selectedType = getCheckedValue('infoType', 'none');
  const selectedButtonType = getCheckedValue('buttonType', 'textButton');
  const selectedAction = getCheckedValue('buttonAction', 'redirectToLink');

  return {
    type: selectedType,
    tooltipPosition: document.querySelector('input[name="tooltipPosition"]:checked')?.value || 'up',
    tooltipTitle: {
      en: getInputValue('tooltipTitleEN'),
      gu: getInputValue('tooltipTitleGU'),
    },
    tooltipDescription: {
      en: getInputValue('tooltipDescriptionEN'),
      gu: getInputValue('tooltipDescriptionGU'),
    },
    icon: getInputValue('infoPointIcon'),
    iconColor: getInputValue('infoPointIconColor') || '#3a3abb',
    iconSize: getInputValue('infoPointIconSize') || 'medium',
    imageUrls: parseMultiValueLines(getInputValue('imageUrls')),
    youtubeUrl: getInputValue('youtubeUrl'),
    audioUrl: getInputValue('audioUrl'),
    buttonAppearance: selectedButtonType,
    buttonAction: selectedAction,
    buttonText: {
      en: getInputValue('buttonTextEN'),
      gu: getInputValue('buttonTextGU'),
    },
    actionUrl: getInputValue('actionUrl'),
    modalTitle: {
      en: getInputValue('modalTitleEN'),
      gu: getInputValue('modalTitleGU'),
    },
    modalDescription: {
      en: getEditorValue('modalDescriptionEN'),
      gu: getEditorValue('modalDescriptionGU'),
    },
    infoModalSize: getInputValue('infoModalSize') || 'medium',
    infoModalFooterLinkUrl: getInputValue('infoModalFooterLinkUrl'),
  };
}

function setupEditModalEvents() {
  const modal = document.getElementById('editInfoModal');
  if (!modal) return;

  ['infoType', 'buttonType', 'buttonAction'].forEach((groupName) => {
    document.querySelectorAll(`input[name="${groupName}"]`).forEach((input) => {
      input.addEventListener('change', updateModalFieldsVisibility);
    });
  });

  EDIT_MODAL_TEXT_FIELD_IDS.forEach((fieldId) => {
    const field = document.getElementById(fieldId);
    if (field) {
      field.addEventListener('input', () => {
        updateCharacterCount(fieldId);
        updateButtonPreview();
      });
    }
  });

  EDIT_MODAL_MEDIA_FIELD_IDS.forEach((fieldId) => {
    const field = document.getElementById(fieldId);
    if (field) {
      field.addEventListener('input', () => {
        if (fieldId === 'infoPointIcon') updateIconPreview();
        updateButtonPreview();
      });
      field.addEventListener('change', () => {
        if (fieldId === 'infoPointIcon') updateIconPreview();
        updateButtonPreview();
      });
    }
  });

  document.getElementById('infoPointIconPickerBtn')?.addEventListener('click', () => {
    iconLib.open(document.getElementById('infoPointIcon'), $('#infoPointIconPreview'));
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

