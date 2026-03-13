import { ICON_LIST } from './constants.js';

let iconInputTarget = null;

export function renderIconGrid(filter = '') {
    const grid = document.getElementById('iconLibGrid');
    if (!grid) return;
    grid.innerHTML = '';
    const icons = ICON_LIST.filter(icon =>
        icon.name.toLowerCase().includes(filter.toLowerCase()) ||
        icon.class.toLowerCase().includes(filter.toLowerCase())
    );
    if (icons.length === 0) {
        grid.innerHTML = '<div class="text-center text-muted">No icons found.</div>';
        return;
    }
    icons.forEach(icon => {
        const col = document.createElement('div');
        col.className = 'col-3 col-sm-2 col-md-2 mb-2';
        col.innerHTML = `
			<button type="button" class="btn btn-light w-100 icon-lib-btn" data-icon-class="${icon.class}" title="${icon.name}">
				<span style="font-size:1.5rem;vertical-align:middle;">
					${icon.class ? `<i class="${icon.class}"></i>` : '&#8709;'}
				</span>
				<div style="font-size:0.8rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${icon.name}</div>
			</button>
		`;
        grid.appendChild(col);
    });
}

export function openIconLibraryModal(inputEl) {
    iconInputTarget = inputEl;
    renderIconGrid('');
    document.getElementById('iconLibSearch').value = '';
    const modal = new bootstrap.Modal(document.getElementById('iconLiberaryModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    // Delegate click on social-type-value input
    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('social-type-value')) {
            openIconLibraryModal(e.target);
        }
    });

    // Search bar
    const searchInput = document.getElementById('iconLibSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            renderIconGrid(this.value);
        });
    }

    // Icon select
    document.getElementById('iconLibGrid').addEventListener('click', function (e) {
        const btn = e.target.closest('.icon-lib-btn');
        if (btn && iconInputTarget) {
            iconInputTarget.value = btn.getAttribute('data-icon-class');
            // Optionally, render the icon next to the input
            bootstrap.Modal.getOrCreateInstance(document.getElementById('iconLiberaryModal')).hide();
        }
    });

    // No Icon button
    const noIconBtn = document.getElementById('iconLibNoIconBtn');
    if (noIconBtn) {
        noIconBtn.addEventListener('click', function () {
            if (iconInputTarget) iconInputTarget.value = '';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('iconLiberaryModal')).hide();
        });
    }

    // Close button
    const closeBtn = document.getElementById('iconLibCloseBtn');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('iconLiberaryModal')).hide();
        });
    }
});