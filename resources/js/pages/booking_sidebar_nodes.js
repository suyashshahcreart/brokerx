import Sortable from 'sortablejs';

function normalizeNodeJson(rawJson) {
    try {
        const parsed = JSON.parse(rawJson || '{}');
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (error) {
        return {};
    }
}

function getNodeTitleMap(node) {
    const rawTitleMap = node.sideMenuTitle;
    return rawTitleMap && typeof rawTitleMap === 'object' ? rawTitleMap : { en:rawTitleMap, hi:rawTitleMap, gu:rawTitleMap };
}

function getNodeDisplayTitle(node) {
    const titleMap = getNodeTitleMap(node);
    const preferredLanguage = window.defaultLanguage || 'en';
    const values = Object.values(titleMap).filter((value) => String(value ?? '').trim() !== '');

    return titleMap[preferredLanguage]
        || values[0]
        || node.name
        || 'Untitled Node';
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function syncSidebarNodesPayload(containerEl, fieldsEl, countEl) {
    const rows = Array.from(containerEl.querySelectorAll('.sidebar-node-list .sidebar-node-item'));

    const payload = rows.map((row, index) => {
        const rawJson = row.querySelector('.node-json')?.value || '{}';
        const node = normalizeNodeJson(rawJson);
        const hasSideMenuOrder = Object.prototype.hasOwnProperty.call(node, 'sideMenuOrder')
            && node.sideMenuOrder !== null
            && node.sideMenuOrder !== '';

        if (!hasSideMenuOrder) {
            return null;
        }

        node.sideMenuOrder = index;

        const sidebarNodeList = row.closest('.sidebar-node-list');
        const categoryId = sidebarNodeList?.dataset.categoryId?.trim() || '';

        if (categoryId !== '') {
            node.sideMenuCategoryId = categoryId;
        } else {
            delete node.sideMenuCategoryId;
        }

        const categoryRows = sidebarNodeList
            ? Array.from(sidebarNodeList.querySelectorAll('.sidebar-node-item'))
            : [];
        const categoryIndex = categoryRows.indexOf(row);

        if (categoryIndex >= 0) {
            node.categoryOrder = categoryIndex;
        }

        const orderBadge = row.querySelector('.sidebar-node-order-badge');
        if (orderBadge) {
            orderBadge.textContent = `# ${index + 1}`;
        }

        return node;
    }).filter(Boolean);

    fieldsEl.innerHTML = '';
    const payloadInput = document.getElementById('sidebar_node_payload');
    if (payloadInput) {
        payloadInput.value = JSON.stringify(payload);
    }

    if (countEl) {
        countEl.textContent = String(payload.length);
    }
}

function updateSidebarNodeRow(row) {
    const rawJson = row.querySelector('.node-json')?.value || '{}';
    const node = normalizeNodeJson(rawJson);
    const titleEl = row.querySelector('.sidebar-node-title');
    const displayTitle = getNodeDisplayTitle(node);

    if (titleEl) {
        titleEl.textContent = displayTitle;
    }

    row.dataset.title = displayTitle.toLowerCase();
}

function getEnabledLanguages() {
    const enabledLanguages = Array.isArray(window.enabledLanguages) && window.enabledLanguages.length > 0
        ? window.enabledLanguages
        : [];

    const titleLanguages = Array.from(Object.keys(window.sidebarNodeTitles || {}));

    return Array.from(new Set([...enabledLanguages, ...titleLanguages]));
}

function getLanguageLabel(language) {
    const labels = {
        en: 'English',
        hi: 'Hindi',
        gu: 'Gujarati',
    };

    return labels[language] || language.toUpperCase();
}

function buildSidebarNodeTitleFields(fieldsEl, node) {
    const titleMap = getNodeTitleMap(node);
    const languages = getEnabledLanguages();
    const content = languages.map((language) => {
        const value = titleMap[language] || '';

        return `
            <div class="mb-3 sidebar-node-language-row" data-language="${escapeHtml(language)}">
                <label class="form-label" for="sidebarNodeTitle_${escapeHtml(language)}">${escapeHtml(getLanguageLabel(language))} Title</label>
                <input type="text" class="form-control" id="sidebarNodeTitle_${escapeHtml(language)}" data-language-value value="${escapeHtml(value)}" placeholder="Enter ${escapeHtml(getLanguageLabel(language))} title">
            </div>
        `;
    }).join('');

    fieldsEl.innerHTML = `
        <div class="text-muted small mb-3">Update the title for each available language.</div>
        ${content}
    `;
}

function setupSidebarNodeSearch(containerEl, searchEl) {
    if (!searchEl) {
        return;
    }

    searchEl.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        const rows = Array.from(containerEl.querySelectorAll('.sidebar-node-item'));

        rows.forEach((row) => {
            const title = row.dataset.title || '';
            row.style.display = title.includes(query) ? '' : 'none';
        });

        const groups = Array.from(containerEl.querySelectorAll('.sidebar-node-group'));
        groups.forEach((group) => {
            const visibleRows = Array.from(group.querySelectorAll('.sidebar-node-item')).filter((row) => row.style.display !== 'none');
            group.style.display = visibleRows.length > 0 ? '' : 'none';
        });
    });
}

function setupSidebarNodeTitleEditor(containerEl, fieldsEl, countEl) {
    const modalEl = document.getElementById('sidebarNodeTitleModal');
    const modalTitleEl = document.getElementById('sidebarNodeTitleModalLabel');
    const modalNodeNameEl = document.getElementById('sidebarNodeTitleModalNodeName');
    const modalFieldsEl = document.getElementById('sidebarNodeTitleFields');
    const saveButton = document.getElementById('saveSidebarNodeTitleButton');

    if (!modalEl || !modalFieldsEl || !saveButton) {
        return;
    }

    const modal = typeof bootstrap !== 'undefined'
        ? bootstrap.Modal.getOrCreateInstance(modalEl)
        : null;

    let activeRow = null;

    containerEl.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="edit-sidebar-node"]');

        if (!button) {
            return;
        }

        activeRow = button.closest('.sidebar-node-item');
        if (!activeRow) {
            return;
        }

        const rawJson = activeRow.querySelector('.node-json')?.value || '{}';
        const node = normalizeNodeJson(rawJson);
        const displayTitle = getNodeDisplayTitle(node);

        if (modalTitleEl) {
            modalTitleEl.textContent = 'Edit Sidebar Node Title';
        }

        if (modalNodeNameEl) {
            modalNodeNameEl.textContent = displayTitle;
        }

        buildSidebarNodeTitleFields(modalFieldsEl, node);
        modal?.show();
    });

    saveButton.addEventListener('click', () => {
        if (!activeRow) {
            return;
        }

        const rawJsonEl = activeRow.querySelector('.node-json');
        const node = normalizeNodeJson(rawJsonEl?.value || '{}');
        const updatedTitles = {};

        Array.from(modalFieldsEl.querySelectorAll('.sidebar-node-language-row')).forEach((row) => {
            const language = row.dataset.language?.trim();
            const valueInput = row.querySelector('[data-language-value]');
            const value = valueInput?.value.trim() || '';

            if (language) {
                updatedTitles[language] = value;
            }
        });

        node.sideMenuTitle = updatedTitles;

        if (rawJsonEl) {
            rawJsonEl.value = JSON.stringify(node);
        }

        updateSidebarNodeRow(activeRow);
        syncSidebarNodesPayload(containerEl, fieldsEl, countEl);
        modal?.hide();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const listEl = document.getElementById('sidebarNodes');
    const fieldsEl = document.getElementById('sidebar_node_fields');
    const searchEl = document.getElementById('sidebarNodeSearch');
    const countEl = document.getElementById('sidebarNodeCount');
    const nodeLists = listEl ? Array.from(listEl.querySelectorAll('.sidebar-node-list')) : [];

    if (!listEl || !fieldsEl || nodeLists.length === 0) {
        return;
    }

    nodeLists.forEach((nodeList) => {
        new Sortable(nodeList, {
            animation: 150,
            handle: '.drag-handle',
            draggable: '.sidebar-node-item',
            group: 'sidebar-nodes',
            onEnd: () => syncSidebarNodesPayload(listEl, fieldsEl, countEl),
        });
    });

    setupSidebarNodeSearch(listEl, searchEl);
    setupSidebarNodeTitleEditor(listEl, fieldsEl, countEl);
    syncSidebarNodesPayload(listEl, fieldsEl, countEl);
});
