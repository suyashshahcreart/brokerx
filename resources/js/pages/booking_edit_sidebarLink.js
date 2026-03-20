import $ from 'jquery';
window.$ = window.jQuery = $;
import '../../css/pages/materialIconLiberaryStyles.css';
import { IconLibrary } from '../materialIconLiberary';
import { materialIconList } from '../data/materialIconsList';

const iconLib = new IconLibrary({ materialIconList });

$(document).ready(function () {
    // setup the icon library modal and search input
    iconLib.init('materialIconModal', 'materialIconSearch');

    // Initialize sidebar links functionality
    initSidebarLinks();
});

function initSidebarLinks() {
    const addBtn = document.getElementById('addSideLinkBtn');
    if (addBtn) {
        addBtn.addEventListener('click', addSidebarLinkRow);
    }
}

function addSidebarLinkRow() {
    const container = document.getElementById('sidebarLinksRow');
    if (!container) return;

    const rowCount = container.querySelectorAll('.sidebar-link-row').length;
    const rowIndex = rowCount;

    const rowHTML = `
        <div class="sidebar-link-row row mb-3 align-items-end border p-2 rounded">
            <div class="col-md-2">
                <label class="form-label">Icon <span class="text-muted">(optional)</span></label>
                <div class="input-group">
                    <input type="text" name="sidebar_links[${rowIndex}][icon]" 
                        class="form-control icon-input" placeholder="Click to select" 
                        data-row-index="${rowIndex}">
                    <button class="btn btn-outline-secondary open-icon-modal" type="button" data-row-index="${rowIndex}">
                        <i class="ri-image-add-line"></i>
                    </button>
                </div>
                <div class="icon-preview mt-2" id="iconPreview_${rowIndex}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="sidebar_links[${rowIndex}][title]" 
                    class="form-control" placeholder="e.g, Floor Plan" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="sidebar_links[${rowIndex}][type]" class="form-select" required>
                    <option value="">Select Type</option>
                    <option value="link">Link</option>
                    <option value="internal">Internal</option>
                    <option value="external">External</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Order <span class="text-danger">*</span></label>
                <input type="number" name="sidebar_links[${rowIndex}][order]" 
                    class="form-control" placeholder="1" value="${rowIndex + 1}" min="1" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Link <span class="text-danger">*</span></label>
                <input type="url" name="sidebar_links[${rowIndex}][link]" 
                    class="form-control" placeholder="e.g, https://example.com" required>
            </div>
            <div class="col-md-2 d-flex justify-content-end align-items-end pb-2">
                <button type="button" class="btn btn-danger btn-sm remove-sidebar-link" title="Remove">
                    <i class="ri-delete-bin-line"></i> Remove
                </button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', rowHTML);

    // Attach event listeners to the new row
    const newRow = container.querySelector('.sidebar-link-row:last-child');
    
    // Remove button
    const removeBtn = newRow.querySelector('.remove-sidebar-link');
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            this.closest('.sidebar-link-row').remove();
        });
    }

    // Icon modal button
    const openIconBtn = newRow.querySelector('.open-icon-modal');
    if (openIconBtn) {
        openIconBtn.addEventListener('click', function() {
           console.log('Open icon modal for row index:', rowIndex);
        });
    }

    // Icon input (readonly but clickable)
    const iconInput = newRow.querySelector('.icon-input');
    if (iconInput) {
        iconInput.addEventListener('click', function() {
            console.log('Icon input clicked for row index:', rowIndex);
            iconLib.open(this, $(`#iconPreview_${rowIndex}`));
        });
    }
     
}

/* 
<div class="row">
    <div class="col-10">
        <input type="text" id="iconInput"
            placeholder="Select the Icon By click" class="form-control mb-2"
            readonly>
    </div>
    <div class="col-2">
        <div id="iconPreview"></div>
    </div>
</div>

*/