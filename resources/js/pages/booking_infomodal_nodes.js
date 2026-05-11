import $, { map } from 'jquery';
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
const finalJson = window.tourFinalJson || {};

const modalEl = document.getElementById('editInfoModal');
modalEl.addEventListener('hidden.bs.modal', () => {
  const form = modalEl.querySelector('editInfoForm');
  if (form) form.reset();
  EditModalState.reset();
  document.getElementById('audioPreview')?.pause();
});

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
// Button action system
const BUTTON_ACTION_TYPES = [
  {
    label: 'Redirect to Link',
    value: 'redirectToLink'
  },
  {
    label: 'Open Info Modal',
    value: 'openInfoModal'
  },
  {
    label: 'Navigate to Node',
    value: 'navigateToNode'
  },
  {
    label: 'Open Image',
    value: 'openImage'
  },
  {
    label: 'Open Video',
    value: 'openVideo'
  },
  {
    label: 'Open Document',
    value: 'openDocument'
  }
];


// helper
function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

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
    .filter((n) => n.infoModals.length > 0);
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
    uploadedImageFiles = []; // Clear uploaded images
    let LinURLRest = updateLinkContainer('');
    LinURLRest() // reset link url
  }
};

/**
 * Update node with edited modal data
 * @param {Object} node - The node to update
 * @param {Number} modalIndex - Index of modal in node's infoModals
 * @param {Object} newData - New modal data
 */
function updateNodeWithEditedModal(node, modalIndex, newData) {
  if (!node || typeof node !== 'object') return;
  alert('running update function')
  return false;
}

//  Clean utility and object check
function hasValidTranslations(obj) {
  return obj &&
    typeof obj === 'object' &&
    Object.keys(obj).length > 0 &&
    Object.values(obj).some(val => val && val.trim() !== '');
}

// render button actions
function renderButtonActionTypes(containerId, selectedValue = '') {
  const container = document.getElementById(containerId);
  if (!container) {
    console.error(`Container not found: ${containerId}`);
    return;
  }
  const radios = BUTTON_ACTION_TYPES.map((type, index) => {
    const radioId = `${containerId}_${index}`;
    return `
            <div class="form-check form-check-inline">
                <input
                    class="form-check-input"
                    type="radio"
                    name="buttonActionType"
                    id="${radioId}"
                    value="${type.value}"
                    disabled
                    ${selectedValue === type.value ? 'checked' : ''}
                >
                <label
                    class="form-check-label"
                    for="${radioId}"
                >
                    ${type.label}
                </label>
            </div>
        `;
  }).join('');
  container.innerHTML = `
        <label class="form-label fw-semibold d-block mb-3">
            Button Action Type
            <span class="text-danger">*</span>
        </label>

        <div>
            ${radios}
        </div>
    `;
  container.classList.remove('d-none');
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
                            ${capitalize(fieldName)} (${lang})*
                        </label>
                        ${fieldName.toLowerCase().includes('description') ? `
                          <textarea
                              class="form-control"
                              name="${name}[${fieldName}][${lang}]"
                              rows="3"
                              required
                          >${fieldData?.[lang] || ''}</textarea>
                        `
        : `<input
                              type="text"
                              class="form-control" required
                              name="${name}[${fieldName}][${lang}]"
                              value="${fieldData?.[lang] || ''}"
                          >`}
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

// modal rendering section function 
function renderInfoModalEditor({
  container,
  data,
  name = 'infoModal'
}) {
  if (!container) throw new Error('Container is required');

  const safeId = (str) => str.replace(/[^a-z0-9]/gi, '_');
  const capitalize = (str) =>
    str.replace(/([A-Z])/g, ' $1')
      .replace(/^./, s => s.toUpperCase());

  // 🔥 CONFIG (this is the real power)
  const multiLangFields = {
    infoModalTitle: { type: 'text' },
    infoModalDescription: { type: 'textarea' },
    infoModalFooterButtonTitle: { type: 'text' },
  };

  const singleFields = {
    infoModalFooterButtonLink: { type: 'text' },
    infoModalIframeUrl: { type: 'text' },
    infoModalWidth: { type: 'select', options: ['modal-sm', 'modal-md', 'modal-lg', 'modal-xl'] }
  };

  // 🌍 collect all languages
  const langs = Object.keys(data.infoModalTitle || {});

  // 🧠 fallback (important)
  if (langs.length === 0) langs.push('en');

  // 🔹 Tabs
  const tabs = langs.map((lang, i) => {
    const id = safeId(lang);
    return `
            <li class="nav-item">
                <button 
                    type="button"
                    class="nav-link ${i === 0 ? 'active' : ''}"
                    data-bs-toggle="tab"
                    data-bs-target="#modal-${id}">
                    ${lang}
                </button>
            </li>
        `;
  }).join('');

  // 🔹 Tab content (multilang)
  const panes = langs.map((lang, i) => {
    const id = safeId(lang);
    return `
            <div class="tab-pane fade ${i === 0 ? 'show active' : ''}" id="modal-${id}">
                <div class="mb-3">
                    <label class="form-label">
                        Modal Info Button Text (${lang})*
                    </label>
                    <input type="text" 
                        class="form-control"
                        name="infoModalLink[${lang}]"
                        value="${data.infoModalLink?.[lang]}">
                </div>
                ${Object.entries(multiLangFields).map(([field, config]) => {
      const value = data[field]?.[lang] || '';
      return `
                        <div class="mb-3">
                            <label class="form-label">
                                ${capitalize(field)} (${lang})*
                            </label>
                            ${config.type === 'textarea'
          ? `<textarea class="form-control editor" 
                                  name="${name}[${field}][${lang}]"
                                  rows="4">${value}</textarea>`
          : `<input type="text" 
                                  class="form-control"
                                  name="${name}[${field}][${lang}]"
                                  value="${value}">`}
                        </div>
                    `;
    }).join('')}
            </div>
        `;
  }).join('');

  // SELECT MODAL WIDTH OPTIONS
  const selectModalWidthOptions = ['modal-sm', 'modal-md', 'modal-lg', 'modal-xl'].map(opt => `
        <option value="${opt}" ${data.infoModalWidth === opt ? 'selected' : ''}>
            Modal Width ${opt.replace('modal-', '').toUpperCase()}
        </option>
    `).join('');


  // 🔥 FINAL RENDER
  container.innerHTML = `
      <h5 class="mb-3">Modal Configuration</h5>
      <div class="mb-3 w-lg-50">
          <label class="form-label">select Modal Width*</label>
          <select class="form-select" name="infoModalWidth" required>
              ${selectModalWidthOptions}
          </select>
      </div>
      <ul class="nav nav-tabs">
          ${tabs}
      </ul>
      <div class="tab-content mt-3">
          ${panes}
      </div>
      <div class="mb-3">
          <label class="form-label">
              Modal Footer Button Link *
          </label>
          <input type="text" 
              class="form-control"
              name="infoModalFooterButtonLink"
              value="${data?.infoModalFooterButtonLink}">
      </div>
    `;
}

// modal button section settings
function renderButtonSettingsEditor({
  container,
  data,
  name = 'Button Settings'
}) {
  if (!container) throw new Error('Container is required');
  let buttonTitleDiv = document.getElementById('buttonTitleDiv');
  if (buttonTitleDiv && !isNonEmptyString(data.linkTitle)) {
    // 🔹 Tabs
    let navtabs = Object.keys(data.linkTitle).map((lang, i) => {
      return `
            <li class="nav-item">
                <button 
                    type="button"
                    class="nav-link ${i === 0 ? 'active' : ''}"
                    data-bs-toggle="tab"
                    data-bs-target="#ButtonTitle-modal-${lang}">
                    ${lang}
                </button>
            </li>
        `;
    }).join('');
    let titlesTabs = Object.keys(data.linkTitle).map((lang, i) => {
      return `
      <div class="tab-pane fade show ${i === 0 ? 'active' : ''}" id="ButtonTitle-modal-${lang}" role="tabpanel" aria-labelledby="ButtonTitle-modal-${lang}-tab">
        <div class="mb-3">
            <label class="form-label">Button title (${lang})*</label>
            <input type="text" class="form-control" name="buttonLink[${lang}]" value="${data.linkTitle?.[lang] || ''}">
        </div>
      </div>
      `;
    }).join('');
    buttonTitleDiv.innerHTML = `
      <ul class="nav nav-tabs" id="myTab" role="tablist">${navtabs}</ul>
      <div id="ButtonTitleTabContent" class="tab-content mt-3">${titlesTabs}</div>
    `;
  }


  // button select type
  let buttonTypeSelect = document.getElementById('buttonTypeSelect');
  buttonTypeSelect.onchange = function (e) {
    if (e.target.value === 'icon') renderIconSettingEditor({
      container: document.getElementById('iconDetailsDiv'),
    })
  };

  if (buttonTypeSelect) {
    [{ label: 'Text', value: 'text' }, { label: 'Icon', value: 'icon' }].map((option) => {
      let optionEl = document.createElement('option');
      optionEl.value = option.value;
      optionEl.textContent = option.label;
      if (data.buttonType === option.value) {
        optionEl.selected = true;
      }
      buttonTypeSelect.appendChild(optionEl);
      buttonTypeSelect.disabled = false;
    })
  };
  // button size select populete
  let buttonSizeSelect = document.getElementById('buttonSizeSelect');
  if (buttonSizeSelect) {
    [
      { label: 'Small', value: 'small' },
      { label: 'Medium', value: 'medium' },
      { label: 'Large', value: 'large' }
    ].map((option) => {
      let optionEl = document.createElement('option');
      optionEl.value = option.value;
      optionEl.textContent = option.label;
      if (data.buttonSize === option.value) {
        optionEl.selected = true;
      }
      buttonSizeSelect.appendChild(optionEl);
    });
  }

  // button color section
  let buttonColorPreview = document.getElementById('buttonColorPreview');
  let buttonColorInput = document.getElementById('buttonColorInput');
  if (buttonColorPreview && buttonColorInput) {
    buttonColorPreview.value = data.buttonColor || '#000000';
    buttonColorInput.value = data.buttonColor || '#000000';
  }

} // end function

// icon reander function
function renderIconSettingEditor() {
  let container = document.getElementById('IconSection');
  let data = EditModalState.currentInfoModal || {};
  let iconPreview = document.getElementById('iconPreview');
  let name = 'Icon Settings';
  if (!container) throw new Error('Container is required');
  // main icon selection section
  let iconInput = document.getElementById('iconInput');
  iconInput.onclick = function () {
    iconLib.open($('iconInput'), $('iconPreview'));
  }
  // icon title 
  let IconTitleDiv = document.getElementById('IconTitleDiv');
  if (IconTitleDiv && !isNonEmptyString(data?.linkTitle)) {
    console.log('rendering icon title editor with data:', data?.linkTitle);
    let navtabs = Object.keys(data?.linkTitle).map((lang, i) => {
      return `
            <li class="nav-item">
                <button 
                    type="button"
                    class="nav-link ${i === 0 ? 'active' : ''}"
                    data-bs-toggle="tab"
                    data-bs-target="#IconTitleDiv-modal-${lang}">
                    ${lang}
                </button>
            </li>
        `;
    }).join('');

    let titlesTabs = Object.keys(data?.linkTitle).map((lang, i) => {
      return `
      <div class="tab-pane fade show ${i === 0 ? 'active' : ''}" id="IconTitleDiv-modal-${lang}" role="tabpanel" aria-labelledby="IconTitleDiv-modal-${lang}-tab">
        <div class="mb-3">
            <label class="form-label">Icon Tooltip (${lang})*</label>
            <input type="text" class="form-control" name="IconTitleDiv[${lang}]" value="${data.linkTitle?.[lang] || ''}">
        </div>
      </div>
      `;
    }).join('');
    // IconTitleDiv.innerHTML = `
    //   <ul class="nav nav-tabs" id="myTab" role="tablist">${navtabs}</ul>
    //   <div id="ButtonTitleTabContent" class="tab-content mt-1">${titlesTabs}</div>
    // `;
  }

  // icon preview section
  if (iconPreview)
    if (data.icon) iconInput.value = data.icon; iconPreview.innerHTML = `<span class="material-icons-outlined">${data.icon}</span>`;

  // icon selection section
  const sizes = [
    { label: 'Small', value: 'small' },
    { label: 'Medium', value: 'medium' },
    { label: 'Large', value: 'large' }
  ];
  const iconOptions = sizes.map((size) => {
    return `
        <option 
            value="${size.value}" 
            ${data?.iconSize === size.value ? 'selected' : ''}
        >
            ${size.label}
        </option>
    `;
  }).join('');
  document.getElementById('iconSizeSelect').innerHTML = iconOptions;
  container.classList.remove('d-none');
}

// reset form function to helper function to reset form fields when modal is closed or when needed
function hideAndResetButtonSettings() {
  // Main wrappers
  const buttonTitleDiv = document.getElementById('buttonTitleDiv');
  const buttonDetailsDiv = document.getElementById('buttonDetailsDiv');
  // ---------- Hide Sections ----------
  if (buttonTitleDiv) {
    buttonTitleDiv.style.display = 'none';
    buttonTitleDiv.innerHTML = '';
  }
  if (buttonDetailsDiv) {
    buttonDetailsDiv.style.display = 'none';
  }
  // ---------- Reset Inputs ----------
  // Text Inputs
  const textInputs = buttonDetailsDiv.querySelectorAll(
    'input[type="text"]'
  );
  textInputs.forEach(input => {
    input.value = '';
  });
  // Color Inputs
  const colorInputs = buttonDetailsDiv.querySelectorAll(
    'input[type="color"]'
  );
  colorInputs.forEach(input => {
    input.value = '#000000';
  });
  // Selects
  const selects = buttonDetailsDiv.querySelectorAll('select');
  selects.forEach(select => {
    select.selectedIndex = 0;
  });
}

// Updateing the Link section
function updateLinkContainer(url = '') {
  // Get elements
  const container = document.getElementById('buttonLinkContainer');
  const input = document.getElementById('LinkUrlInput');
  // Update input value
  input.value = url;
  // Show container
  container.style.display = 'block';
  container.classList.remove('d-none');
  return function () {
    container.classList.add('d-none');
    input.value = '';
  }
}

// Stores newly uploaded image files
let uploadedImageFiles = [];

// Render image preview with existing and newly uploaded images
function renderImagePreview(images = []) {
  const imageSection = document.getElementById('imageSection');
  const imagePreview = document.getElementById('imagePreview');
  
  if (!imageSection || !imagePreview) {
    console.error('Image preview container not found');
    return;
  }
  
  // Show section if there are any images (existing or new)
  const hasImages = (Array.isArray(images) && images.length > 0) || uploadedImageFiles.length > 0;
  if (hasImages) {
    imageSection.classList.remove('d-none');
  }
  
  // Render existing images from modal
  const existingImagesHtml = (images || []).map((image, index) => {
    const imageUrl = typeof image === 'string' && image.startsWith('http') 
      ? image 
      : `${finalJson.s3_link}${image}`;
    
    return `
      <div class="position-relative" style="width: fit-content;">
        <img
          src="${imageUrl}"
          alt="Existing Image ${index + 1}"
          class="img-thumbnail"
          style="
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
          "
          loading="lazy"
        >
        <span class="badge bg-secondary position-absolute bottom-0 start-50 translate-middle-x" style="font-size: 10px;">Existing</span>
        <button 
          type="button"
          class="btn btn-sm btn-danger position-absolute top-0 end-0"
          style="transform: translate(5px, -5px); padding: 2px 6px; font-size: 12px;"
          onclick="removeExistingImage(event, ${index})"
          title="Remove existing image"
        >
          <i class="ri-close-line"></i>
        </button>
      </div>
    `;
  }).join('');
  
  // Render newly uploaded images with FileReader promises
  const uploadedImagePromises = uploadedImageFiles.map((file, index) => {
    return new Promise((resolve) => {
      // Validate file
      if (!file.type.startsWith('image/')) {
        resolve('');
        return;
      }
      
      const reader = new FileReader();
      reader.onload = (e) => {
        const html = `
          <div class="position-relative" style="width: fit-content;">
            <img
              src="${e.target.result}"
              alt="New Upload ${index + 1}"
              class="img-thumbnail border-success"
              style="
                width: 120px;
                height: 120px;
                object-fit: cover;
                border-radius: 8px;
                border: 2px solid #28a745 !important;
              "
              loading="lazy"
            >
            <span class="badge bg-success position-absolute top-0 start-0" style="font-size: 10px;">New</span>
            <button 
              type="button"
              class="btn btn-sm btn-danger position-absolute top-0 end-0"
              style="transform: translate(5px, -5px); padding: 2px 6px; font-size: 12px;"
              onclick="removeUploadedImage(${index})"
              title="Remove new image"
            >
              <i class="ri-close-line"></i>
            </button>
          </div>
        `;
        resolve(html);
      };
      
      reader.onerror = () => {
        console.error(`Failed to read file: ${file.name}`);
        resolve('');
      };
      
      reader.readAsDataURL(file);
    });
  });
  
  // Combine and render all images
  if (uploadedImagePromises.length > 0) {
    Promise.all(uploadedImagePromises).then((uploadedHtmlArray) => {
      const uploadedHtml = uploadedHtmlArray.filter(Boolean).join('');
      imagePreview.innerHTML = existingImagesHtml + uploadedHtml;
    });
  } else {
    imagePreview.innerHTML = existingImagesHtml;
  }
}

// Remove existing image from modal
function removeExistingImage(event, index) {
  event.preventDefault();
  if (EditModalState.currentInfoModal && Array.isArray(EditModalState.currentInfoModal.image)) {
    EditModalState.currentInfoModal.image.splice(index, 1);
    renderImagePreview(EditModalState.currentInfoModal.image);
  }
}

// Remove newly uploaded image
function removeUploadedImage(index) {
  uploadedImageFiles.splice(index, 1);
  renderImagePreview(EditModalState.currentInfoModal?.image || []);
}

// Expose to global window for inline event handlers
window.removeExistingImage = removeExistingImage;
window.removeUploadedImage = removeUploadedImage;
window.renderImagePreview = renderImagePreview;

// Setup image upload handler for multiple files
function setupImageUploadHandler() {
  const imageInput = document.getElementById('imageInput');
  const imageSection = document.getElementById('imageSection');
  
  if (!imageInput) {
    console.warn('Image input element not found');
    return;
  }
  
  // Remove any existing listeners to prevent duplicates
  const newImageInput = imageInput.cloneNode(true);
  imageInput.parentNode?.replaceChild(newImageInput, imageInput);
  
  newImageInput.addEventListener('change', function(e) {
    const files = Array.from(e.target.files || []);
    
    if (files.length === 0) return;
    
    // Validate files (max size 5MB per file)
    const maxSize = 5 * 1024 * 1024; // 5MB
    const validFiles = files.filter((file) => {
      if (!file.type.startsWith('image/')) {
        console.warn(`Skipped non-image file: ${file.name}`);
        return false;
      }
      if (file.size > maxSize) {
        console.warn(`File too large (${file.name}): ${(file.size / 1024 / 1024).toFixed(2)}MB`);
        return false;
      }
      return true;
    });
    
    if (validFiles.length > 0) {
      // Add valid files to upload list
      uploadedImageFiles.push(...validFiles);
      
      // Show image section
      if (imageSection) {
        imageSection.classList.remove('d-none');
      }
      
      // Re-render previews
      renderImagePreview(EditModalState.currentInfoModal?.image || []);
    }
    
    // Clear input for next selection
    this.value = '';
  });
}

function audioPreview() {
  const audioSection = document.getElementById('audioSection');
  const audioPreview = document.getElementById('audioPreview');
  const audioInput = document.getElementById('audioInput');
  if (!audioSection || !audioPreview){console.error('Audio preview container not found');return;}
  audioPreview.src = `${finalJson.s3_link}${EditModalState.currentInfoModal.audio}`;
  audioInput.onchange = function (e) {
    const file = e.target.files[0];
    if (file) {
      const url = URL.createObjectURL(file);
      audioPreview.src = url;
    }
  }
  // display the audio section 
  audioSection.classList.remove('d-none');
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


  //  tooltip section updating
  if (
    hasValidTranslations(infoModal.title) &&
    hasValidTranslations(infoModal.description)
  ) {
    renderToottipSection({
      container: document.getElementById('tooltipSection'),
      fields: {
        title: infoModal.title,
        description: infoModal.description
      },
      tooltipPosition: infoModal.titleTooltipPosition
    });
    document
      .getElementById('tooltipSection')
      .classList.remove('d-none');
  }
  // modal content section updating
  if (
    hasValidTranslations(infoModal.infoModalTitle) &&
    hasValidTranslations(infoModal.infoModalDescription)
  ) {
    renderInfoModalEditor({
      container: document.getElementById('modalContentSection'),
      data: {
        infoModalTitle: infoModal.infoModalTitle,
        infoModalDescription: infoModal.infoModalDescription,
        infoModalLink: infoModal.infoModalLink,
        infoModalFooterButtonTitle: infoModal.infoModalFooterButtonTitle,
        infoModalFooterText: infoModal.infoModalFooterText,
        infoModalFooterButtonLink: infoModal.infoModalFooterButtonLink,
        infoModalIframeUrl: infoModal.infoModalIframeUrl,
        infoModalWidth: infoModal.infoModalWidth || infoModal.infoModalSize
      }
    });
    document
      .getElementById('modalContentSection')
      .classList.remove('d-none');
    reinitalizeEditors(); // re-apply rich text editors after dynamic render
  }

  // icon settings section updating
  if (isNonEmptyString(infoModal.icon)) renderIconSettingEditor();
  // button settings
  if (isNonEmptyString(infoModal.buttonActionType) || infoModal.isButtonOnly || isNonEmptyString(infoModal.buttonType)) {
    renderButtonSettingsEditor({
      container: document.getElementById('buttonIconSection'),
      data: infoModal
    });
    document
      .getElementById('buttonIconSection')
      .classList.remove('d-none');
  }

  // button settings section updating
  if (isNonEmptyString(infoModal.buttonActionType) || infoModal.isButtonOnly) renderButtonActionTypes('buttonActionTypeContainer', infoModal.buttonActionType);

  // link URL field
  if (isNonEmptyString(infoModal.link)) updateLinkContainer(infoModal.link);

  // images rendering 
  uploadedImageFiles = []; // Reset uploaded images for this modal
  if (infoModal?.image?.length > 0) {
    renderImagePreview(infoModal.image);
  } else {
    renderImagePreview([]);
  }
  setupImageUploadHandler(); // Initialize image upload handler

  // audio file rendering
  if(infoModal?.audio) audioPreview();

  // SMART VISIBILITY - SHOW ONLY SECTIONS WITH DATA
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

