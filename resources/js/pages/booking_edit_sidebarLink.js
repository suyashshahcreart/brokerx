import $ from 'jquery';
window.$ = window.jQuery = $;
import '../../css/pages/materialIconLiberaryStyles.css';
/* My Liberary Imports */
import { IconLibrary } from '../materialIconLiberary';
import { materialIconList } from '../data/materialIconsList';
/* Quill Imports */
import Quill from 'quill';
import "quill/dist/quill.core.css";
import "quill/dist/quill.snow.css";

const iconLib = new IconLibrary({ materialIconList });
const quillEditors = {}; // map rowIndex -> Quill instance



$(document).ready(function () {
    // setup the icon library modal and search input
    iconLib.init('materialIconModal', 'materialIconSearch');
    // Initialize sidebar links functionality
    initSidebarLinks();
    // Render existing sidebar links
    renderSidebarLinks();
});

function initSidebarLinks() {
    const addBtn = document.getElementById('addSideLinkBtn');
    if (addBtn) {
        addBtn.addEventListener('click', addSidebarLinkRow);
    }
}

function initQuillForRow(rowIndex, initialContent = '') {
    const containerId = `contentQuillEditor_${rowIndex}`;
    const hiddenInput = document.getElementById(`contentHidden_${rowIndex}`);

    if (!hiddenInput || quillEditors[rowIndex]) {
        return;
    }

    const quill = new Quill(`#${containerId}`, {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'header': [1, 2, 3, false] }],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'clean']
            ]
        }
    });

    if (hiddenInput.value) {
        quill.root.innerHTML = initialContent;
    }

    quill.on('text-change', function () {
        hiddenInput.value = quill.root.innerHTML;
    });

    quillEditors[rowIndex] = quill;
}

function addSidebarLinkRow(linkData = {}) {
    const container = document.getElementById('sidebarLinksRow');
    if (!container) return;

    const rowCount = container.querySelectorAll('.sidebar-link-row').length;
    const rowIndex = rowCount;

    // Extract data from linkData
    const icon = linkData.icon || '';
    const titleEn = linkData.title?.en || '';
    const type = linkData.type || '';
    const order = linkData.order || (rowIndex + 1);
    const link = linkData.link || '';
    const contentEn = linkData.content?.en || '';
    const rowHTML = `
        <div class="sidebar-link-row row mb-3 align-items-end border p-3 rounded" data-row-index="${rowIndex}">
            <div class="col-md-2">
                <label class="form-label">Icon <span class="text-muted">(optional)</span></label>
                <div class="input-group">
                    <input type="text" name="sidebar_links[${rowIndex}][icon]" 
                        class="form-control icon-input" placeholder="Click to select" 
                        data-row-index="${rowIndex}" readonly value="${icon}">
                    <div class="icon-preview" id="iconPreview_${rowIndex}">
                        ${icon ?? `
                            <div class="icon-item text-center">
                                <span class="material-icons-outlined">${icon}</span>
                            </div>
                            ` }
                    </div>
                </div>
            </div>
            <div class="col-md-2" id="titleContainer_${rowIndex}">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="sidebar_links[${rowIndex}][title][en]" 
                    class="form-control" placeholder="e.g, Floor Plan" required value="${titleEn}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select id="typeSelect_${rowIndex}" name="sidebar_links[${rowIndex}][type]" class="form-select" required>
                    <option value="">Select Type</option>
                    <option value="link" ${type === 'link' ? 'selected' : ''}>Link</option>
                    <option value="infoModal" ${type === 'infoModal' ? 'selected' : ''}>infoModal</option>
                    <option value="content" ${type === 'content' ? 'selected' : ''}>Content</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Order <span class="text-danger">*</span></label>
                <input type="number" name="sidebar_links[${rowIndex}][order]" 
                    class="form-control" placeholder="1" value="${order}" min="1" required>
            </div>
            <div class="col-md-3" id="linkInputContainer_${rowIndex}" style="display: ${type === 'link' ? 'block' : 'none'};">
                <label class="form-label">Link <span class="text-danger">*</span></label>
                <input type="url" name="sidebar_links[${rowIndex}][link]" 
                    class="form-control" placeholder="e.g, https://example.com" value="${link}" ${type === 'link' ? 'required' : ''}>
            </div>
            <div class="col-md-12 mt-2" id="contentInputContainer_${rowIndex}" style="display: ${type === 'content' || type === 'infoModal' ? 'block' : 'none'};">
                <label class="form-label">Content <span class="text-danger">*</span></label>
                <div id="contentQuillEditor_${rowIndex}" class="quill-editor" style="min-height: 150px; border: 1px solid #ced4da; border-radius: .25rem; background: #fff;"></div>
                <div class="d-none">
                    <input type="hidden" name="sidebar_links[${rowIndex}][content][en]" id="contentHidden_${rowIndex}" class="content-hidden-input" value="${contentEn}">
                </div>
            </div>
            <div class="col-md-12 d-flex justify-content-end align-items-end pb-2 mt-2">
                <button type="button" class="btn btn-danger btn-sm remove-sidebar-link" title="Remove">
                    <i class="ri-delete-bin-line"></i> Remove
                </button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', rowHTML);

    const typeSelect = document.getElementById(`typeSelect_${rowIndex}`);
    const linkContainer = document.getElementById(`linkInputContainer_${rowIndex}`);
    const contentContainer = document.getElementById(`contentInputContainer_${rowIndex}`);
    const linkInput = linkContainer.querySelector('input');
    const contentHidden = document.getElementById(`contentHidden_${rowIndex}`);
    const titleContainer = document.getElementById(`titleContainer_${rowIndex}`);
    const titleInput = titleContainer.querySelector('input');

    typeSelect.addEventListener('change', function () {
        const selectedType = this.value;

        if (selectedType === 'link') {
            linkContainer.style.display = 'block';
            contentContainer.style.display = 'none';
            titleContainer.style.display = 'block';
            linkInput.required = true;
            contentHidden.required = false;
            titleInput.required = true;
        } else if (selectedType === 'content') {
            linkContainer.style.display = 'none';
            contentContainer.style.display = 'block';
            titleContainer.style.display = 'block';
            linkInput.required = false;
            contentHidden.required = true;
            titleInput.required = true;
            initQuillForRow(rowIndex);
        } else if (selectedType === 'infoModal') {
            linkContainer.style.display = 'none';
            contentContainer.style.display = 'block';
            titleContainer.style.display = 'block';
            linkInput.required = false;
            contentHidden.required = true;
            titleInput.required = true;
            initQuillForRow(rowIndex);
        } else {
            linkContainer.style.display = 'none';
            contentContainer.style.display = 'none';
            titleContainer.style.display = 'block';
            linkInput.required = false;
            contentHidden.required = false;
            titleInput.required = true;
        }
    });

    // Initialize Quill if content type is selected
    if (type === 'content' || type === 'infoModal') {
        initQuillForRow(rowIndex, contentEn);
    }

    // Attach event listeners to the new row
    const newRow = container.querySelector('.sidebar-link-row:last-child');

    // Remove button
    const removeBtn = newRow.querySelector('.remove-sidebar-link');
    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            const rowEl = this.closest('.sidebar-link-row');
            const index = Number(rowEl.dataset.rowIndex);
            if (!Number.isNaN(index) && quillEditors[index]) {
                delete quillEditors[index];
            }
            rowEl.remove();
        });
    }

    // Icon modal button
    const openIconBtn = newRow.querySelector('.open-icon-modal');
    if (openIconBtn) {
        openIconBtn.addEventListener('click', function () {
        });
    }

    // Icon input (readonly but clickable)
    const iconInput = newRow.querySelector('.icon-input');
    if (iconInput) {
        iconInput.addEventListener('click', function () {
            iconLib.open(this, $(`#iconPreview_${rowIndex}`));
        });
    }

}

function renderSidebarLinks() {
    let existingLinks = [];

    // Parse the JSON string if it's a string
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

    // Sort by order
    existingLinks.sort((a, b) => (parseInt(a.order ?? 0) - parseInt(b.order ?? 0)) || 0);

    // Create rows for each existing link
    existingLinks.forEach((linkData) => {
        addSidebarLinkRow(linkData);
    });
}