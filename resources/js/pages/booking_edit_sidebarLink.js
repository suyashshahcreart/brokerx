import $ from 'jquery';
window.$ = window.jQuery = $;
import '../../css/pages/materialIconLiberaryStyles.css';
import iconLib from './booking_tour_iconLib';
import reinitalizeEditors from '../tinyEditor';
import {
    buildDynamicLanguageTabGroup,
    resolveOrderedEnabledLanguages,
    syncTourLanguageTabs,
} from '../utils/tour-language-tabs';

const quillEditors = {};

$(document).ready(function () {
    iconLib.init('materialIconModal', 'materialIconSearch', 'materialIconModalClose');
    initSidebarLinks();
    renderSidebarLinks();
});

function initSidebarLinks() {
    const addBtn = document.getElementById('addSideLinkBtn');
    if (addBtn) {
        addBtn.addEventListener('click', () => addSidebarLinkRow());
    }
}

function getFirstTitleInput(rowIndex) {
    const firstCode = resolveOrderedEnabledLanguages()[0] || 'en';
    const container = document.getElementById(`titleContainer_${rowIndex}`);
    if (!container) {
        return null;
    }

    return container.querySelector(
        `input.sidebar-link-title-input[data-language="${firstCode}"]`
    ) || container.querySelector('input.sidebar-link-title-input');
}

function setTitleRequired(rowIndex, required) {
    const container = document.getElementById(`titleContainer_${rowIndex}`);
    if (!container) {
        return;
    }

    container.querySelectorAll('.sidebar-link-title-input').forEach((input) => {
        input.required = false;
    });

    if (required) {
        const firstInput = getFirstTitleInput(rowIndex);
        if (firstInput) {
            firstInput.required = true;
        }
    }
}

function addSidebarLinkRow(linkData = {}) {
    const container = document.getElementById('sidebarLinksRow');
    if (!container) {
        return;
    }

    const existingRows = Array.from(container.querySelectorAll('.sidebar-link-row'));
    const rowIndex = existingRows.length
        ? Math.max(...existingRows.map((row) => Number(row.dataset.rowIndex) || 0)) + 1
        : 0;

    const icon = linkData.icon || '';
    const type = linkData.type || '';
    const order = linkData.order || rowIndex + 1;
    const link = linkData.link || '';
    const titleValues = linkData.title && typeof linkData.title === 'object' ? linkData.title : {};
    const contentValues = linkData.content && typeof linkData.content === 'object' ? linkData.content : {};

    const titleGroupId = `sidebarLinkTitle-${rowIndex}`;
    const contentGroupId = `sidebarLinkContent-${rowIndex}`;

    const titleTabs = buildDynamicLanguageTabGroup({
        groupId: titleGroupId,
        rowIndex,
        field: 'title',
        values: titleValues,
        firstFieldRequired: true,
    });

    const contentTabs = buildDynamicLanguageTabGroup({
        groupId: contentGroupId,
        rowIndex,
        field: 'content',
        values: contentValues,
    });

    const rowHTML = `
        <div class="sidebar-link-row row mb-3 align-items-end border p-3 rounded" data-row-index="${rowIndex}">
            <div class="col-md-2">
                <label class="form-label">Icon <span class="text-muted">(optional)</span></label>
                <div class="input-group">
                    <input type="text" name="sidebar_links[${rowIndex}][icon]"
                        class="form-control icon-input" placeholder="Click to select"
                        data-row-index="${rowIndex}" readonly value="${icon.replace(/"/g, '&quot;')}">
                    <div class="icon-preview" id="sidebarIconPreview_${rowIndex}">
                        ${icon ? `
                            <div class="icon-item text-center">
                                <span class="material-icons-outlined">${icon}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>

            <div class="col-md-4" id="titleContainer_${rowIndex}">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                ${titleTabs.navHtml}
                ${titleTabs.panesHtml}
            </div>

            <div class="col-md-2">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select id="typeSelect_${rowIndex}" name="sidebar_links[${rowIndex}][type]" class="form-select" required>
                    <option value="">Select Type</option>
                    <option value="infoModal" ${type === 'infoModal' ? 'selected' : ''}>Info Modal</option>
                    <option value="link" ${type === 'link' ? 'selected' : ''}>Link</option>
                    <option value="image" ${type === 'image' ? 'selected' : ''}>Image</option>
                    <option value="video" ${type === 'video' ? 'selected' : ''}>Video</option>
                    <option value="document" ${type === 'document' ? 'selected' : ''}>Document</option>
                </select>
            </div>

            <div class="col-md-1">
                <label class="form-label">Order <span class="text-danger">*</span></label>
                <input type="number" name="sidebar_links[${rowIndex}][order]"
                    class="form-control" placeholder="1" value="${order}" min="1" required>
            </div>

            <div class="col-md-3" id="linkUrlContainer_${rowIndex}" style="display: ${type === 'link' ? 'block' : 'none'};">
                <label class="form-label">Link <span class="text-danger">*</span></label>
                <input type="url" name="sidebar_links[${rowIndex}][link]"
                    class="form-control sidebar-link-url-input" placeholder="e.g, https://example.com"
                    value="${(link || '').replace(/"/g, '&quot;')}" ${type === 'link' ? 'required' : ''}>
            </div>

            <div class="col-md-3" id="linkMediaContainer_${rowIndex}" style="display: ${['image', 'video', 'document'].includes(type) ? 'block' : 'none'};">
                <p class="m-0">This feature is currently unavailable.<span class="text-danger">We are working on it!</span></p>
            </div>

            <div class="col-md-12 mt-2" id="contentInputContainer_${rowIndex}" style="display: ${type === 'content' || type === 'infoModal' ? 'block' : 'none'};">
                <label class="form-label">Content</label>
                ${contentTabs.navHtml}
                ${contentTabs.panesHtml}
            </div>

            <div class="col-md-12 d-flex justify-content-end align-items-end pb-2 mt-2">
                <button type="button" class="btn btn-danger btn-sm remove-sidebar-link" title="Remove">
                    <i class="ri-delete-bin-line"></i> Remove
                </button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', rowHTML);
    reinitalizeEditors();

    const typeSelect = document.getElementById(`typeSelect_${rowIndex}`);
    const linkUrlContainer = document.getElementById(`linkUrlContainer_${rowIndex}`);
    const linkMediaContainer = document.getElementById(`linkMediaContainer_${rowIndex}`);
    const contentContainer = document.getElementById(`contentInputContainer_${rowIndex}`);
    const linkInput = linkUrlContainer?.querySelector('.sidebar-link-url-input');

    const applyTypeVisibility = () => {
        const selectedType = typeSelect?.value || '';

        if (selectedType === 'link') {
            if (linkUrlContainer) {
                linkUrlContainer.style.display = 'block';
            }
            if (linkMediaContainer) {
                linkMediaContainer.style.display = 'none';
            }
            if (contentContainer) {
                contentContainer.style.display = 'none';
            }
            if (linkInput) {
                linkInput.required = true;
            }
            setTitleRequired(rowIndex, true);
        } else if (selectedType === 'content' || selectedType === 'infoModal') {
            if (linkUrlContainer) {
                linkUrlContainer.style.display = 'none';
            }
            if (linkMediaContainer) {
                linkMediaContainer.style.display = 'none';
            }
            if (contentContainer) {
                contentContainer.style.display = 'block';
            }
            if (linkInput) {
                linkInput.required = false;
            }
            setTitleRequired(rowIndex, true);
        } else if (['image', 'video', 'document'].includes(selectedType)) {
            if (linkUrlContainer) {
                linkUrlContainer.style.display = 'none';
            }
            if (linkMediaContainer) {
                linkMediaContainer.style.display = 'block';
            }
            if (contentContainer) {
                contentContainer.style.display = 'none';
            }
            if (linkInput) {
                linkInput.required = false;
            }
            setTitleRequired(rowIndex, true);
        } else {
            if (linkUrlContainer) {
                linkUrlContainer.style.display = 'none';
            }
            if (linkMediaContainer) {
                linkMediaContainer.style.display = 'none';
            }
            if (contentContainer) {
                contentContainer.style.display = 'none';
            }
            if (linkInput) {
                linkInput.required = false;
            }
            setTitleRequired(rowIndex, false);
        }
    };

    typeSelect?.addEventListener('change', applyTypeVisibility);
    applyTypeVisibility();

    if (typeof syncTourLanguageTabs === 'function') {
        syncTourLanguageTabs();
    }

    const newRow = container.querySelector('.sidebar-link-row:last-child');

    const removeBtn = newRow?.querySelector('.remove-sidebar-link');
    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            const rowEl = this.closest('.sidebar-link-row');
            const index = Number(rowEl?.dataset.rowIndex);
            if (!Number.isNaN(index) && quillEditors[index]) {
                delete quillEditors[index];
            }
            rowEl?.remove();
        });
    }

    const iconInput = newRow?.querySelector('.icon-input');
    if (iconInput) {
        iconInput.addEventListener('click', function () {
            iconLib.open(this, $(`#sidebarIconPreview_${rowIndex}`));
        });
    }
}

function renderSidebarLinks() {
    let existingLinks = [];

    if (typeof window.sidebarLinksData === 'string') {
        try {
            existingLinks = JSON.parse(window.sidebarLinksData);
        } catch (e) {
            console.error('Error parsing sidebarLinksData:', e);
            existingLinks = [];
        }
    } else if (Array.isArray(window.sidebarLinksData)) {
        existingLinks = window.sidebarLinksData;
    }

    existingLinks.sort(
        (a, b) => (parseInt(a.order ?? 0, 10) - parseInt(b.order ?? 0, 10)) || 0
    );

    existingLinks.forEach((linkData) => {
        addSidebarLinkRow(linkData);
    });

    if (typeof syncTourLanguageTabs === 'function') {
        syncTourLanguageTabs();
    }
}
