import Sortable from 'sortablejs';

function normalizeNodeJson(rawJson) {
    try {
        const parsed = JSON.parse(rawJson || '{}');
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (error) {
        return {};
    }
}

function appendNodeFields(fieldsEl, baseName, value) {
    if (value === null || value === undefined) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = baseName;
        input.value = '';
        fieldsEl.appendChild(input);
        return;
    }

    if (Array.isArray(value)) {
        value.forEach((item, index) => {
            appendNodeFields(fieldsEl, `${baseName}[${index}]`, item);
        });
        return;
    }

    if (typeof value === 'object') {
        Object.entries(value).forEach(([key, nestedValue]) => {
            appendNodeFields(fieldsEl, `${baseName}[${key}]`, nestedValue);
        });
        return;
    }

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = baseName;
    input.value = String(value);
    fieldsEl.appendChild(input);
}

function syncSidebarNodesPayload(listEl, fieldsEl, countEl) {
    const rows = Array.from(listEl.querySelectorAll('.sidebar-node-item'));

    const payload = rows.map((row, index) => {
        const rawJson = row.querySelector('.node-json')?.value || '{}';
        const node = normalizeNodeJson(rawJson);
        node.sideMenuOrder = index;
        return node;
    });

    fieldsEl.innerHTML = '';
    payload.forEach((node, index) => {
        appendNodeFields(fieldsEl, `sidebar_node[${index}]`, node);
    });

    if (countEl) {
        countEl.textContent = String(payload.length);
    }
}

function setupSidebarNodeSearch(listEl, searchEl) {
    if (!searchEl) {
        return;
    }

    searchEl.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        const rows = Array.from(listEl.querySelectorAll('.sidebar-node-item'));

        rows.forEach((row) => {
            const title = row.dataset.title || '';
            row.style.display = title.includes(query) ? '' : 'none';
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const listEl = document.getElementById('sidebarNodes');
    const fieldsEl = document.getElementById('sidebar_node_fields');
    const searchEl = document.getElementById('sidebarNodeSearch');
    const countEl = document.getElementById('sidebarNodeCount');

    if (!listEl || !fieldsEl) {
        return;
    }

    new Sortable(listEl, {
        animation: 150,
        handle: '.drag-handle',
        draggable: '.sidebar-node-item',
        onEnd: () => syncSidebarNodesPayload(listEl, fieldsEl, countEl),
    });

    setupSidebarNodeSearch(listEl, searchEl);
    syncSidebarNodesPayload(listEl, fieldsEl, countEl);
});