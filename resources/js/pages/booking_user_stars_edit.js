function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function parseStarsData() {
    const container = document.getElementById('userStarsContainer');
    if (!container) {
        return [];
    }

    const raw = container.getAttribute('data-user-stars');
    if (!raw) {
        return [];
    }

    try {
        const parsed = JSON.parse(raw);
        return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
        console.warn('Unable to parse user stars data:', error);
        return [];
    }
}

function getNextStarIndex(container) {
    const rows = Array.from(container.querySelectorAll('.user-star-row'));
    if (!rows.length) {
        return 0;
    }

    const indices = rows.map((row) => Number(row.getAttribute('data-row-index')) || 0);
    return Math.max(...indices) + 1;
}

function buildStarRow(index, star = {}) {
    const label = escapeHtml(star.label ?? '');
    const count = Number.isFinite(Number(star.count)) ? Number(star.count) : '';
    const url = escapeHtml(star.url ?? '');

    return `
        <div class="user-star-row border rounded p-3 mb-3" data-row-index="${index}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Label</label>
                    <input type="text" name="stars[${index}][label]" class="form-control" value="${label}" placeholder="e.g, Item">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Count</label>
                    <input type="number" min="0" max="5" name="stars[${index}][count]" class="form-control" value="${count}" placeholder="e.g, 3.5">
                </div>
                <div class="col-md-4">
                    <label class="form-label">URL</label>
                    <input type="url" name="stars[${index}][url]" class="form-control" value="${url}" placeholder="https://example.com/review">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-danger w-100 remove-user-star" title="Remove star">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

function addStarRow(star = {}) {
    const container = document.getElementById('userStarsContainer');
    if (!container) {
        return;
    }

    const index = getNextStarIndex(container);
    if(index > 2) return alert('Maximum 4 stars allowed');
    container.insertAdjacentHTML('beforeend', buildStarRow(index, star));

    const newRow = container.querySelector('.user-star-row:last-child');
    const removeBtn = newRow?.querySelector('.remove-user-star');
    if (removeBtn) {
        removeBtn.addEventListener('click', () => {
            newRow.remove();
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const addBtn = document.getElementById('addUserStarBtn');
    const container = document.getElementById('userStarsContainer');

    if (!addBtn || !container) {
        return;
    }

    const existingStars = parseStarsData();
    if (existingStars.length) {
        existingStars.forEach((star) => addStarRow(star));
    }

    addBtn.addEventListener('click', () => addStarRow());
});
