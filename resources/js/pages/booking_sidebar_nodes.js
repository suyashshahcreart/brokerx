import $ from 'jquery';
import Sortable from 'sortablejs';
import iconLib from './booking_tour_iconLib';

const UNCATEGORIZED_CATEGORY_ID = '__uncategorized__';

function isPlainObject(value) {
    return value !== null && typeof value === 'object' && !Array.isArray(value);
}

function normalizeNodeJson(rawValue) {
    if (isPlainObject(rawValue)) {
        return rawValue;
    }

    if (typeof rawValue !== 'string' || rawValue.trim() === '') {
        return {};
    }

    try {
        const parsed = JSON.parse(rawValue);
        return isPlainObject(parsed) ? parsed : {};
    } catch (error) {
        return {};
    }
}

function getNodeKey(node) {
    return String(node?.id ?? node?.name ?? '');
}

function getCategoryKey(category) {
    return String(category?.id ?? '');
}

function getTitleMap(value) {
    if (isPlainObject(value)) {
        return value;
    }

    const textValue = String(value ?? '').trim();
    return {
        en: textValue,
        hi: textValue,
        gu: textValue,
    };
}

function getEnabledLanguages() {
    const enabledLanguages = Array.isArray(window.enabledLanguages) && window.enabledLanguages.length > 0
        ? window.enabledLanguages
        : ['en'];

    return Array.from(new Set(enabledLanguages.map((language) => String(language))));
}

function getLanguageLabel(language) {
    const labels = {
        en: 'English',
        hi: 'Hindi',
        gu: 'Gujarati',
    };

    return labels[language] || String(language).toUpperCase();
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getNodeDisplayTitle(node) {
    const titleMap = getTitleMap(node.sideMenuTitle);
    const preferredLanguage = window.defaultLanguage || 'en';
    const values = Object.values(titleMap)
        .map((value) => String(value ?? '').trim())
        .filter((value) => value !== '');

    return String(titleMap[preferredLanguage] ?? values[0] ?? node.name ?? 'Untitled Node').trim() || 'Untitled Node';
}

function getCategoryDisplayTitle(category) {
    const titleMap = getTitleMap(category?.name);
    const preferredLanguage = window.defaultLanguage || 'en';
    const values = Object.values(titleMap)
        .map((value) => String(value ?? '').trim())
        .filter((value) => value !== '');

    return String(titleMap[preferredLanguage] ?? values[0] ?? 'Category').trim() || 'Category';
}

function generateSidebarCategoryId() {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return `cat_${crypto.randomUUID()}`;
    }

    return `cat_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`;
}

function isSidebarNodeVisible(node) {
    return !!node && Object.prototype.hasOwnProperty.call(node, 'showInSideMenu')
        && (node.showInSideMenu === true || node.showInSideMenu === 1 || node.showInSideMenu === '1' || node.showInSideMenu === 'true');
}

function sortCategories(categories) {
    return [...categories].sort((left, right) => {
        const leftOrder = Number(left?.order ?? 0);
        const rightOrder = Number(right?.order ?? 0);

        if (leftOrder !== rightOrder) {
            return leftOrder - rightOrder;
        }

        return getCategoryDisplayTitle(left).localeCompare(getCategoryDisplayTitle(right), undefined, { sensitivity: 'base' });
    });
}

function sortNodes(nodes) {
    return [...nodes].sort((left, right) => {
        const leftCategoryOrder = Number(left?.categoryOrder ?? 0);
        const rightCategoryOrder = Number(right?.categoryOrder ?? 0);

        if (leftCategoryOrder !== rightCategoryOrder) {
            return leftCategoryOrder - rightCategoryOrder;
        }

        const leftSideMenuOrder = Number(left?.sideMenuOrder ?? 0);
        const rightSideMenuOrder = Number(right?.sideMenuOrder ?? 0);

        if (leftSideMenuOrder !== rightSideMenuOrder) {
            return leftSideMenuOrder - rightSideMenuOrder;
        }

        return getNodeDisplayTitle(left).localeCompare(getNodeDisplayTitle(right), undefined, { sensitivity: 'base' });
    });
}

function ensureSidebarState() {
    window.sidebarNodesData = Array.isArray(window.sidebarNodesData)
        ? window.sidebarNodesData.filter((node) => isPlainObject(node))
        : [];

    window.sidebarCategoriesData = Array.isArray(window.sidebarCategoriesData)
        ? window.sidebarCategoriesData.filter((category) => isPlainObject(category))
        : [];

    window.sidebarNodesById = new Map(
        window.sidebarNodesData
            .map((node) => [getNodeKey(node), node])
            .filter(([key]) => key !== '')
    );

    window.sidebarCategoriesById = new Map(
        window.sidebarCategoriesData
            .map((category) => [getCategoryKey(category), category])
            .filter(([key]) => key !== '')
    );
}

function getSidebarGroups() {
    ensureSidebarState();

    const categories = sortCategories(window.sidebarCategoriesData);
    const visibleNodes = window.sidebarNodesData.filter((node) => isSidebarNodeVisible(node));
    const groupedNodes = new Map();

    categories.forEach((category) => {
        groupedNodes.set(getCategoryKey(category), []);
    });

    const uncategorizedNodes = [];
    visibleNodes.forEach((node) => {
        const categoryId = String(node.sideMenuCategoryId ?? '').trim();

        if (categoryId !== '' && groupedNodes.has(categoryId)) {
            groupedNodes.get(categoryId).push(node);
            return;
        }

        uncategorizedNodes.push(node);
    });

    groupedNodes.forEach((nodes, categoryId) => {
        groupedNodes.set(categoryId, sortNodes(nodes));
    });

    return {
        categories,
        groupedNodes,
        uncategorizedNodes: sortNodes(uncategorizedNodes),
        visibleCount: visibleNodes.length,
    };
}

function buildNodeRow(node) {
    const nodeId = getNodeKey(node);
    const displayTitle = getNodeDisplayTitle(node);
    const nodeIcon = String(node.sideMenuIcon || 'ri-image-line');

    return `
        <li class="list-group-item d-flex align-items-center justify-content-between sidebar-node-item" data-node-id="${escapeHtml(nodeId)}" data-title="${escapeHtml(displayTitle.toLowerCase())}" data-category-id="${escapeHtml(String(node.sideMenuCategoryId ?? ''))}">
            <div class="d-flex align-items-center gap-2">
                <span class="drag-handle text-muted" style="cursor: grab;"><i class="ri-drag-move-2-line"></i></span>
                <i class="${escapeHtml(nodeIcon)}"></i>
                <span class="sidebar-node-title">${escapeHtml(displayTitle)}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border sidebar-node-order-badge"># ${Number(node.sideMenuOrder ?? 0) + 1}</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-action="edit-sidebar-node">
                    <i class="ri-pencil-line me-1"></i>Edit
                </button>
                <input type="hidden" class="node-json" value="${escapeHtml(JSON.stringify(node))}">
            </div>
        </li>
    `;
}

function buildCategoryCard(category, nodes, options = {}) {
    const categoryId = options.isUncategorized ? UNCATEGORIZED_CATEGORY_ID : getCategoryKey(category);
    const collapseId = `sidebarNodeGroup_${categoryId.replace(/[^A-Za-z0-9_\-]/g, '_')}`;
    const title = options.isUncategorized ? 'Uncategorized' : getCategoryDisplayTitle(category);
    const icon = options.isUncategorized ? 'list' : String(category?.icon || 'folder');
    const draggableClass = options.isUncategorized ? 'sidebar-category-fixed' : '';
    const rows = nodes.length > 0
        ? nodes.map((node) => buildNodeRow(node)).join('')
        : '<li class="list-group-item text-muted sidebar-node-empty">No sidebar nodes in this category.</li>';

    return `
        <div class="border rounded-3 overflow-hidden mb-3 sidebar-category-card ${draggableClass}" data-category-id="${escapeHtml(categoryId)}">
            <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-light border-bottom sidebar-category-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="${options.isUncategorized ? 'text-muted' : 'drag-handle sidebar-category-drag-handle text-muted'}" ${options.isUncategorized ? '' : 'style="cursor: grab;"'}>
                        <i class="${options.isUncategorized ? 'ri-list-unordered' : 'ri-drag-move-2-line'}"></i>
                    </span>
                    <span class="material-icons-outlined" style="font-size: 18px; line-height: 1;">${escapeHtml(icon)}</span>
                    <span class="sidebar-category-title">${escapeHtml(title)}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-white text-dark border sidebar-category-count">${nodes.length}</span>
                    ${options.isUncategorized ? '' : '<button type="button" class="btn btn-sm btn-outline-secondary" data-action="edit-sidebar-category"><i class="ri-pencil-line me-1"></i>Edit</button>'}
                    <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="true" aria-controls="${collapseId}">
                        <i class="ri-subtract-line"></i>
                    </button>
                </div>
            </div>
            <div id="${collapseId}" class="collapse show">
                <ul class="list-group list-group-flush sidebar-node-list" data-category-id="${escapeHtml(options.isUncategorized ? '' : categoryId)}">
                    ${rows}
                </ul>
            </div>
        </div>
    `;
}

function renderSidebarNodes(containerEl, countEl) {
    const { categories, groupedNodes, uncategorizedNodes, visibleCount } = getSidebarGroups();
    const html = [];

    categories.forEach((category) => {
        html.push(buildCategoryCard(category, groupedNodes.get(getCategoryKey(category)) || []));
    });

    if (uncategorizedNodes.length > 0) {
        html.push(buildCategoryCard(null, uncategorizedNodes, { isUncategorized: true }));
    }

    if (html.length === 0) {
        containerEl.innerHTML = '<div class="list-group-item text-muted" id="sidebarNodesEmpty">No sidebar nodes available.</div>';
    } else {
        containerEl.innerHTML = html.join('');
    }

    if (countEl) {
        countEl.textContent = String(visibleCount);
    }
}

function renderSidebarCategoryIconPreview(previewEl, iconName) {
    if (!previewEl || !iconName) {
        previewEl.html('');
        return;
    }

    previewEl.html(`
        <div class="icon-item text-center">
            <span class="material-icons-outlined">${escapeHtml(iconName)}</span>
        </div>
    `);
}

function setupSidebarSortables(containerEl, countEl) {
    const categoriesSortable = new Sortable(containerEl, {
        animation: 150,
        handle: '.sidebar-category-drag-handle',
        draggable: '.sidebar-category-card:not(.sidebar-category-fixed)',
        ghostClass: 'sidebar-category-ghost',
        onEnd: () => syncSidebarNodesPayload(containerEl, countEl),
    });

    const nodeSortables = Array.from(containerEl.querySelectorAll('.sidebar-node-list')).map((nodeList) => new Sortable(nodeList, {
        animation: 150,
        handle: '.drag-handle',
        draggable: '.sidebar-node-item',
        group: {
            name: 'sidebar-nodes',
            pull: true,
            put: true,
        },
        onAdd: () => syncSidebarNodesPayload(containerEl, countEl),
        onEnd: () => syncSidebarNodesPayload(containerEl, countEl),
    }));

    return {
        categoriesSortable,
        nodeSortables,
    };
}

function refreshSidebarLayout(containerEl, countEl, searchEl) {
    if (window.sidebarSortables?.categoriesSortable) {
        window.sidebarSortables.categoriesSortable.destroy();
    }

    if (Array.isArray(window.sidebarSortables?.nodeSortables)) {
        window.sidebarSortables.nodeSortables.forEach((sortable) => sortable.destroy());
    }

    renderSidebarNodes(containerEl, countEl);
    window.sidebarSortables = setupSidebarSortables(containerEl, countEl);
    syncSidebarNodesPayload(containerEl, countEl);

    if (searchEl && searchEl.value.trim() !== '') {
        searchEl.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

function syncSidebarNodesPayload(containerEl, countEl) {
    ensureSidebarState();

    const categoryCards = Array.from(containerEl.querySelectorAll('.sidebar-category-card'));
    const nextCategories = [];
    let globalNodeOrder = 0;

    categoryCards.forEach((card, categoryIndex) => {
        const categoryId = String(card.dataset.categoryId ?? '').trim();
        const isUncategorized = categoryId === '' || categoryId === UNCATEGORIZED_CATEGORY_ID;

        if (!isUncategorized) {
            const category = window.sidebarCategoriesById.get(categoryId);
            if (category) {
                category.order = categoryIndex;
                nextCategories.push(category);
            }
        }

        const rows = Array.from(card.querySelectorAll('.sidebar-node-item'));
        rows.forEach((row, categoryOrder) => {
            const nodeId = String(row.dataset.nodeId ?? '').trim();
            const node = window.sidebarNodesById.get(nodeId);

            if (!node) {
                return;
            }

            node.showInSideMenu = true;
            node.sideMenuCategoryId = isUncategorized ? '' : categoryId;
            node.categoryOrder = categoryOrder;
            node.sideMenuOrder = globalNodeOrder;

            const title = getNodeDisplayTitle(node);
            const titleEl = row.querySelector('.sidebar-node-title');
            if (titleEl) {
                titleEl.textContent = title;
            }

            row.dataset.title = title.toLowerCase();
            row.dataset.categoryId = node.sideMenuCategoryId;

            const badgeEl = row.querySelector('.sidebar-node-order-badge');
            if (badgeEl) {
                badgeEl.textContent = `# ${globalNodeOrder + 1}`;
            }

            const hiddenJsonEl = row.querySelector('.node-json');
            if (hiddenJsonEl) {
                hiddenJsonEl.value = JSON.stringify(node);
            }

            globalNodeOrder += 1;
        });

        const countBadge = card.querySelector('.sidebar-category-count');
        if (countBadge) {
            countBadge.textContent = String(rows.length);
        }
    });

    window.sidebarCategoriesData = nextCategories;

    window.sidebarNodesData = window.sidebarNodesData.map((node) => {
        const nodeId = getNodeKey(node);
        return window.sidebarNodesById.get(nodeId) || node;
    });

    window.sidebarCategoriesById = new Map(
        window.sidebarCategoriesData
            .map((category) => [getCategoryKey(category), category])
            .filter(([key]) => key !== '')
    );

    const payloadInput = document.getElementById('sidebar_node_payload');
    if (payloadInput) {
        payloadInput.value = JSON.stringify({
            nodes: window.sidebarNodesData,
            sidebarCategories: window.sidebarCategoriesData,
        });
    }

    if (countEl) {
        countEl.textContent = String(window.sidebarNodesData.filter((node) => isSidebarNodeVisible(node)).length);
    }
}

function setupSidebarNodeSearch(containerEl, searchEl) {
    if (!searchEl) {
        return;
    }

    searchEl.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        const cards = Array.from(containerEl.querySelectorAll('.sidebar-category-card'));

        cards.forEach((card) => {
            const categoryTitle = card.querySelector('.sidebar-category-title')?.textContent?.trim().toLowerCase() || '';
            const rows = Array.from(card.querySelectorAll('.sidebar-node-item'));
            let visibleRows = 0;

            rows.forEach((row) => {
                const title = row.dataset.title || '';
                const shouldShow = query === '' || title.includes(query) || categoryTitle.includes(query);
                row.style.display = shouldShow ? '' : 'none';

                if (shouldShow) {
                    visibleRows += 1;
                }
            });

            card.style.display = query === '' || categoryTitle.includes(query) || visibleRows > 0 ? '' : 'none';
        });
    });
}

function buildSidebarNodeTitleFields(fieldsEl, node) {
    const titleMap = getTitleMap(node.sideMenuTitle);
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

function updateSidebarNodeRow(row, node) {
    const title = getNodeDisplayTitle(node);
    const titleEl = row.querySelector('.sidebar-node-title');

    if (titleEl) {
        titleEl.textContent = title;
    }

    row.dataset.title = title.toLowerCase();
}

function setupSidebarNodeTitleEditor(containerEl, countEl) {
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
    let activeNode = null;

    containerEl.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="edit-sidebar-node"]');

        if (!button) {
            return;
        }

        activeRow = button.closest('.sidebar-node-item');
        if (!activeRow) {
            return;
        }

        const nodeId = String(activeRow.dataset.nodeId ?? '').trim();
        activeNode = window.sidebarNodesById.get(nodeId) || normalizeNodeJson(activeRow.querySelector('.node-json')?.value || '{}');

        const displayTitle = getNodeDisplayTitle(activeNode);

        if (modalTitleEl) {
            modalTitleEl.textContent = 'Edit Sidebar Node Title';
        }

        if (modalNodeNameEl) {
            modalNodeNameEl.textContent = displayTitle;
        }

        buildSidebarNodeTitleFields(modalFieldsEl, activeNode);
        modal?.show();
    });

    saveButton.addEventListener('click', () => {
        if (!activeRow || !activeNode) {
            return;
        }

        const updatedTitles = {};
        Array.from(modalFieldsEl.querySelectorAll('.sidebar-node-language-row')).forEach((row) => {
            const language = row.dataset.language?.trim();
            const valueInput = row.querySelector('[data-language-value]');
            const value = valueInput?.value.trim() || '';

            if (language) {
                updatedTitles[language] = value;
            }
        });

        activeNode.sideMenuTitle = updatedTitles;

        const hiddenJsonEl = activeRow.querySelector('.node-json');
        if (hiddenJsonEl) {
            hiddenJsonEl.value = JSON.stringify(activeNode);
        }

        updateSidebarNodeRow(activeRow, activeNode);
        syncSidebarNodesPayload(containerEl, countEl);
        modal?.hide();
    });
}

function buildSidebarCategoryNameFields(fieldsEl, category) {
    const nameMap = getTitleMap(category?.name);
    const languages = getEnabledLanguages();
    const content = languages.map((language) => {
        const value = nameMap[language] || '';

        return `
            <div class="mb-3 sidebar-category-language-row" data-language="${escapeHtml(language)}">
                <label class="form-label" for="sidebarCategoryName_${escapeHtml(language)}">${escapeHtml(getLanguageLabel(language))} Name</label>
                <input type="text" class="form-control" id="sidebarCategoryName_${escapeHtml(language)}" data-language-value value="${escapeHtml(value)}" placeholder="Enter ${escapeHtml(getLanguageLabel(language))} name">
            </div>
        `;
    }).join('');

    fieldsEl.innerHTML = `
        <div class="text-muted small mb-3">Set the category name for each available language.</div>
        ${content}
    `;
}

function setupSidebarCategoryEditor(containerEl, countEl, searchEl) {
    const modalEl = document.getElementById('sidebarCategoryModal');
    const modalLabelEl = document.getElementById('sidebarCategoryModalLabel');
    const modalFieldsEl = document.getElementById('sidebarCategoryNameFields');
    const modalIconInput = document.getElementById('sidebarCategoryIconInput');
    const modalIconPreview = $('#sidebarCategoryIconPreview');
    const modalCategoryIdInput = document.getElementById('sidebarCategoryIdInput');
    const addCategoryButton = document.getElementById('addSidebarCategoryButton');
    const selectIconButton = document.getElementById('selectSidebarCategoryIconButton');
    const saveButton = document.getElementById('saveSidebarCategoryButton');

    if (!modalEl || !modalLabelEl || !modalFieldsEl || !modalIconInput || !modalCategoryIdInput || !saveButton) {
        return;
    }

    const modal = typeof bootstrap !== 'undefined'
        ? bootstrap.Modal.getOrCreateInstance(modalEl)
        : null;

    const openCategoryModal = (mode, category) => {
        modalLabelEl.textContent = mode === 'create' ? 'Add Sidebar Category' : 'Edit Sidebar Category';
        modalCategoryIdInput.value = String(category?.id || '');
        modalIconInput.value = String(category?.icon || 'folder');
        renderSidebarCategoryIconPreview(modalIconPreview, modalIconInput.value);
        buildSidebarCategoryNameFields(modalFieldsEl, category || { name: {} });
        modal?.show();
    };

    addCategoryButton?.addEventListener('click', () => {
        openCategoryModal('create', {
            id: '',
            icon: 'folder',
            name: {},
        });
    });

    selectIconButton?.addEventListener('click', () => {
        iconLib.open(modalIconInput, modalIconPreview);
    });

    modalIconInput.addEventListener('click', () => {
        iconLib.open(modalIconInput, modalIconPreview);
    });

    selectIconButton?.addEventListener('click', () => {
        iconLib.open(modalIconInput, modalIconPreview);
    });

    modalIconInput.addEventListener('click', () => {
        iconLib.open(modalIconInput, modalIconPreview);
    });

    containerEl.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="edit-sidebar-category"]');
        if (!button) {
            return;
        }

        const card = button.closest('.sidebar-category-card');
        const categoryId = String(card?.dataset.categoryId ?? '').trim();

        if (!categoryId || categoryId === UNCATEGORIZED_CATEGORY_ID) {
            return;
        }

        const category = window.sidebarCategoriesById.get(categoryId);
        if (!category) {
            return;
        }

        openCategoryModal('edit', category);
    });

    saveButton.addEventListener('click', () => {
        ensureSidebarState();

        const categoryId = modalCategoryIdInput.value.trim();
        const isCreate = categoryId === '';
        const nextId = isCreate ? generateSidebarCategoryId() : categoryId;
        const existingCategory = window.sidebarCategoriesById.get(nextId);

        const updatedName = {};
        Array.from(modalFieldsEl.querySelectorAll('.sidebar-category-language-row')).forEach((row) => {
            const language = row.dataset.language?.trim();
            const valueInput = row.querySelector('[data-language-value]');
            const value = valueInput?.value.trim() || '';

            if (language) {
                updatedName[language] = value;
            }
        });

        const nextCategory = {
            ...(existingCategory || {}),
            id: nextId,
            icon: modalIconInput.value.trim() || 'folder',
            name: updatedName,
        };

        if (isCreate) {
            nextCategory.order = window.sidebarCategoriesData.length;
            window.sidebarCategoriesData.push(nextCategory);
        } else {
            window.sidebarCategoriesData = window.sidebarCategoriesData.map((category) => {
                if (getCategoryKey(category) === nextId) {
                    return {
                        ...category,
                        ...nextCategory,
                    };
                }

                return category;
            });
        }

        window.sidebarCategoriesById = new Map(
            window.sidebarCategoriesData
                .map((category) => [getCategoryKey(category), category])
                .filter(([key]) => key !== '')
        );

        refreshSidebarLayout(containerEl, countEl, searchEl);
        modal?.hide();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const listEl = document.getElementById('sidebarNodes');
    const searchEl = document.getElementById('sidebarNodeSearch');
    const countEl = document.getElementById('sidebarNodeCount');
    const payloadInput = document.getElementById('sidebar_node_payload');

    if (!listEl || !payloadInput) {
        return;
    }

    if (document.getElementById('materialIconModal')) {
        iconLib.init('materialIconModal', 'materialIconSearch', 'materialIconModalClose');
    }

    ensureSidebarState();
    renderSidebarNodes(listEl, countEl);
    window.sidebarSortables = setupSidebarSortables(listEl, countEl);
    setupSidebarNodeSearch(listEl, searchEl);
    setupSidebarNodeTitleEditor(listEl, countEl);
    setupSidebarCategoryEditor(listEl, countEl, searchEl);
    syncSidebarNodesPayload(listEl, countEl);
});
