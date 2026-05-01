import $ from 'jquery';
import Sortable from 'sortablejs';
import iconLib from './booking_tour_iconLib';

const UNCATEGORIZED_CATEGORY_ID = '__uncategorized__';

let SidebarNodesState = null;
let sidebarCategoriesState = null;
let sidebarLinksState = null;

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

function renderIconElement(iconName) {
    const icon = String(iconName ?? '').trim();
    
    // Check if it's a remixicon (starts with ri-)
    if (icon.startsWith('ri-')) {
        return `<i class="${escapeHtml(icon)}"></i>`;
    }
    
    // Check if it's a material icon (no hyphens at start or contains underscores)
    if (icon && !icon.startsWith('-')) {
        // If it doesn't have 'ri-' prefix, treat as material icon
        return `<span class="material-icons-outlined">${escapeHtml(icon)}</span>`;
    }
    
    // Default fallback to remixicon
    return `<i class="ri-link-line"></i>`;
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
        const leftOrder = Number(left?.sideMenuOrder ?? left?.order ?? 0);
        const rightOrder = Number(right?.sideMenuOrder ?? right?.order ?? 0);

        if (leftOrder !== rightOrder) {
            return leftOrder - rightOrder;
        }

        return getCategoryDisplayTitle(left).localeCompare(getCategoryDisplayTitle(right), undefined, { sensitivity: 'base' });
    });
}

function sortLinks(links) {
    return [...links].sort((left, right) => {
        const leftOrder = Number(left?.order ?? left?.sideMenuOrder ?? 0);
        const rightOrder = Number(right?.order ?? right?.sideMenuOrder ?? 0);

        if (leftOrder !== rightOrder) {
            return leftOrder - rightOrder;
        }

        const leftTitle = String(left?.title?.en || left?.title || left?.link || '').trim();
        const rightTitle = String(right?.title?.en || right?.title || right?.link || '').trim();

        return leftTitle.localeCompare(rightTitle, undefined, { sensitivity: 'base' });
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

function getSidebarMenuItems() {
    ensureSidebarState();

    const links = sortLinks(Array.isArray(window.sidebarLinksData) ? window.sidebarLinksData : []);
    const items = [];

    const visibleNodes = window.sidebarNodesData.filter((node) => isSidebarNodeVisible(node));
    sortNodes(visibleNodes).forEach((node) => {
        items.push({
            type: 'node',
            order: Number(node?.sideMenuOrder ?? 0),
            node,
        });
    });

    links.forEach((link, index) => {
        items.push({
            type: 'link',
            order: Number(link?.order ?? link?.sideMenuOrder ?? index),
            link,
        });
    });

    return items.sort((left, right) => {
        if (left.order !== right.order) {
            return left.order - right.order;
        }

        const typeWeight = { node: 0, link: 1 };
        if (typeWeight[left.type] !== typeWeight[right.type]) {
            return typeWeight[left.type] - typeWeight[right.type];
        }

        const leftTitle = left.type === 'node'
            ? getNodeDisplayTitle(left.node)
            : String(left.link?.title?.en || left.link?.title || left.link?.link || '');
        const rightTitle = right.type === 'node'
            ? getNodeDisplayTitle(right.node)
            : String(right.link?.title?.en || right.link?.title || right.link?.link || '');

        return String(leftTitle).localeCompare(String(rightTitle), undefined, { sensitivity: 'base' });
    });
}

function buildSidebarCategorySelectOptions(selectedCategoryId) {
    ensureSidebarState();
    const categories = sortCategories(window.sidebarCategoriesData);
    const selectedValue = String(selectedCategoryId ?? '').trim();

    let options = `<option value="" ${selectedValue === '' ? 'selected' : ''}>No category</option>`;
    categories.forEach((category) => {
        const categoryId = getCategoryKey(category);
        const title = getCategoryDisplayTitle(category);
        const selected = categoryId === selectedValue ? 'selected' : '';
        options += `<option value="${escapeHtml(categoryId)}" ${selected}>${escapeHtml(title)}</option>`;
    });

    return options;
}

function buildNodeRow(node) {
    const nodeId = getNodeKey(node);
    const displayTitle = getNodeDisplayTitle(node);
    const nodeIcon = String(node.sideMenuIcon || 'ri-image-line');
    const isVisible = isSidebarNodeVisible(node);
    const categoryLabel = getSidebarNodeCategoryLabel(node.sideMenuCategoryId);
    const categoryOptions = buildSidebarCategorySelectOptions(node.sideMenuCategoryId);

    return `
        <li class="list-group-item d-flex align-items-center justify-content-between sidebar-node-item sidebar-menu-item sidebar-menu-row px-3 py-3" data-menu-item-type="node" data-node-id="${escapeHtml(nodeId)}" data-title="${escapeHtml(displayTitle.toLowerCase())}" data-category-id="${escapeHtml(String(node.sideMenuCategoryId ?? ''))}" data-show-in-side-menu="${isVisible ? '1' : '0'}">
            <div class="d-flex align-items-center gap-3 sidebar-menu-row-main">
                <span class="drag-handle text-muted" style="cursor: grab;"><i class="ri-drag-move-2-line"></i></span>
                <span class="sidebar-menu-icon-wrap">${renderIconElement(nodeIcon)}</span>
                <div class="sidebar-menu-row-main">
                    <div class="sidebar-menu-row-title sidebar-node-title">${escapeHtml(displayTitle)}</div>
                    <div class="sidebar-menu-row-subtitle sidebar-node-category-label">${escapeHtml(categoryLabel)}</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end sidebar-menu-action-group">
                <select class="form-select form-select-sm sidebar-node-category-select" style="min-width: 170px" title="Category">
                    ${categoryOptions}
                </select>
                <div class="input-group input-group-sm sidebar-node-order-control sidebar-menu-order-input">
                    <span class="input-group-text">#</span>
                    <input type="text" class="form-control sidebar-node-order-field" value="${Number(node.sideMenuOrder ?? 0) + 1}" readonly>
                    <button type="button" class="btn btn-outline-secondary" data-action="move-node-up" title="Move up" aria-label="Move up">
                        <i class="ri-arrow-up-line"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-action="move-node-down" title="Move down" aria-label="Move down">
                        <i class="ri-arrow-down-line"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-action="edit-sidebar-node">
                    <i class="ri-pencil-line me-1"></i>Edit
                </button>
                <input type="hidden" class="sidebar-node-visible-input" value="${isVisible ? '1' : '0'}">
                <input type="hidden" class="node-json" value="${escapeHtml(JSON.stringify(node))}">
            </div>
        </li>
    `;
}

function buildCategoryCard(category, nodes, options = {}) {
    const categoryId = getCategoryKey(category);
    const collapseId = `sidebarNodeGroup_${categoryId.replace(/[^A-Za-z0-9_\-]/g, '_')}`;
    const title = getCategoryDisplayTitle(category);
    const icon = String(category?.icon || 'folder');
    const rows = nodes.length > 0
        ? nodes.map((node) => buildNodeRow(node)).join('')
        : '<li class="list-group-item text-muted sidebar-node-empty">No sidebar nodes in this category.</li>';
    const isCollapsed = !!category?.collapsed;
    const collapseClass = isCollapsed ? 'collapse' : 'collapse show';
    const toggleIcon = isCollapsed ? 'ri-add-line' : 'ri-subtract-line';

    return `
        <div class="sidebar-menu-card mb-3 sidebar-category-card sidebar-menu-item" data-menu-item-type="category" data-category-id="${escapeHtml(categoryId)}">
            <div class="sidebar-menu-category-header d-flex align-items-center justify-content-between gap-3 px-3 py-3">
                <div class="d-flex align-items-center gap-3 sidebar-menu-row-main">
                    <span class="drag-handle sidebar-category-drag-handle text-muted" style="cursor: grab;">
                        <i class="ri-drag-move-2-line"></i>
                    </span>
                    <span class="sidebar-menu-icon-wrap">${renderIconElement(icon)}</span>
                    <div class="sidebar-menu-row-main">
                        <div class="sidebar-menu-row-title sidebar-category-title">${escapeHtml(title)}</div>
                        <div class="sidebar-menu-row-subtitle d-flex align-items-center gap-2 flex-wrap">
                            <span class="sidebar-menu-chip"><i class="ri-node-tree"></i><span class="sidebar-category-count">${nodes.length}</span> nodes</span>
                            <span class="sidebar-menu-chip">${escapeHtml(options.isUncategorized ? 'Top level' : 'Category')}</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end sidebar-menu-action-group">
                    <div class="input-group input-group-sm sidebar-category-order-control sidebar-menu-order-input">
                        <span class="input-group-text">#</span>
                        <input type="text" class="form-control sidebar-category-order-field" value="${Number(category.sideMenuOrder ?? category.order ?? 0) + 1}" readonly>
                        <button type="button" class="btn btn-outline-secondary" data-action="move-category-up" title="Move up" aria-label="Move up">
                            <i class="ri-arrow-up-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-action="move-category-down" title="Move down" aria-label="Move down">
                            <i class="ri-arrow-down-line"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="edit-sidebar-category"><i class="ri-pencil-line me-1"></i>Edit</button>
                    <button type="button" class="btn btn-sm btn-light border sidebar-category-toggle sidebar-menu-category-toggle" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="${!isCollapsed}" aria-controls="${collapseId}">
                        <i class="${toggleIcon}"></i>
                    </button>
                </div>
            </div>
            <div id="${collapseId}" class="${collapseClass}">
                <ul class="list-group list-group-flush sidebar-node-list bg-white" data-category-id="${escapeHtml(options.isUncategorized ? '' : categoryId)}">
                    ${rows}
                </ul>
            </div>
        </div>
    `;
}

function buildTopLevelNodeList(nodes) {
    const rows = nodes.length > 0
        ? nodes.map((node) => buildNodeRow(node)).join('')
        : '<li class="list-group-item text-muted sidebar-node-empty sidebar-menu-empty-state px-3 py-3">Drop nodes here to keep them without category.</li>';

    return `
        <ul class="list-group list-group-flush sidebar-node-list sidebar-top-level-list mb-3" data-category-id="">
            ${rows}
        </ul>
    `;
}

function buildLinkRow(link, index) {
    const title = (link?.title?.en || link?.title || '').toString();
    const href = String(link?.link ?? link?.href ?? '');
    const action = String(link?.mediaAction ?? link?.action ?? 'link');
    const icon = String(link?.sideMenuIcon ?? link?.icon ?? 'ri-link-line');

    return `
        <li class="list-group-item d-flex align-items-center justify-content-between sidebar-link-item sidebar-menu-item sidebar-menu-row px-3 py-3" data-menu-item-type="link" data-link-index="${index}">
            <div class="d-flex align-items-center gap-3 sidebar-menu-row-main">
                <span class="drag-handle" style="cursor:grab"><i class="ri-drag-move-2-line"></i></span>
                <span class="sidebar-menu-icon-wrap">${renderIconElement(icon)}</span>
                <div class="sidebar-menu-row-main">
                    <div class="sidebar-menu-row-title">${escapeHtml(title || href)}</div>
                    <div class="sidebar-menu-row-subtitle">${escapeHtml(href || action)}</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end sidebar-menu-action-group">
                <input type="hidden" class="link-json" value='${escapeHtml(JSON.stringify(link))}'>
                <div class="input-group input-group-sm sidebar-menu-order-input">
                    <span class="input-group-text">#</span>
                    <input type="text" class="form-control sidebar-link-order-field" value="${Number(index) + 1}" readonly>
                    <button type="button" class="btn btn-outline-secondary" data-action="move-link-up" title="Move up" aria-label="Move up">
                        <i class="ri-arrow-up-line"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-action="move-link-down" title="Move down" aria-label="Move down">
                        <i class="ri-arrow-down-line"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-action="edit-sidebar-link"><i class="ri-pencil-line"></i></button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-sidebar-link"><i class="ri-delete-bin-line"></i></button>
            </div>
        </li>
    `;
}

function renderSidebarNodes(containerEl, countEl) {
    const items = getSidebarMenuItems();
    const visibleCount = window.sidebarNodesData.filter((node) => isSidebarNodeVisible(node)).length;
    const html = items.map((item) => {
        if (item.type === 'node') {
            return buildNodeRow(item.node);
        }

        if (item.type === 'link') {
            return buildLinkRow(item.link, item.order);
        }

        return '';
    });

    if (html.length === 0) {
        containerEl.innerHTML = '<div class="sidebar-menu-empty-state text-muted px-3 py-3" id="sidebarNodesEmpty">No sidebar nodes available.</div>';
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
            ${renderIconElement(iconName.trim())}
        </div>
    `);
}

function setupSidebarSortables(containerEl, countEl) {
    const rootSortable = new Sortable(containerEl, {
        animation: 150,
        handle: '.drag-handle',
        draggable: '.sidebar-node-item, .sidebar-link-item',
        ghostClass: 'sidebar-category-ghost',
        onEnd: () => syncSidebarNodesPayload(containerEl, countEl),
    });

    return {
        rootSortable,
        nodeSortables: [],
    };
}

function syncSidebarNodeEmptyState(listEl) {
    if (!listEl) {
        return;
    }

    const rows = Array.from(listEl.querySelectorAll('.sidebar-node-item'));
    const emptyRows = Array.from(listEl.querySelectorAll('.sidebar-node-empty'));
    const isTopLevelList = String(listEl.dataset.categoryId ?? '').trim() === '';

    if (rows.length === 0) {
        if (emptyRows.length === 0) {
            const message = isTopLevelList
                ? 'Drop nodes here to keep them without category.'
                : 'No sidebar nodes in this category.';
            listEl.insertAdjacentHTML('beforeend', `<li class="list-group-item text-muted sidebar-node-empty sidebar-menu-empty-state px-3 py-3">${escapeHtml(message)}</li>`);
        }
        return;
    }

    emptyRows.forEach((row) => row.remove());
}

function refreshSidebarLayout(containerEl, countEl, searchEl) {
    if (window.sidebarSortables?.rootSortable) {
        window.sidebarSortables.rootSortable.destroy();
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

// category collapse UI removed (single list menu)

function syncSidebarNodesPayload(containerEl, countEl) {
    ensureSidebarState();

    const menuItems = Array.from(containerEl.children).filter((child) => child.classList?.contains('sidebar-menu-item'));
    const nextLinks = [];
    let globalOrder = 0;
    let linkOrder = 0;

    menuItems.forEach((item) => {
        const itemType = String(item.dataset.menuItemType ?? '').trim();

        if (itemType === 'node') {
            const nodeId = String(item.dataset.nodeId ?? '').trim();
            const node = window.sidebarNodesById.get(nodeId);
            if (!node) {
                return;
            }

            const visibleInput = item.querySelector('.sidebar-node-visible-input');
            node.showInSideMenu = visibleInput ? (visibleInput.value === '1' || visibleInput.value === 'true') : true;

            const categorySelect = item.querySelector('.sidebar-node-category-select');
            node.sideMenuCategoryId = categorySelect ? String(categorySelect.value ?? '').trim() : String(node.sideMenuCategoryId ?? '');

            node.sideMenuOrder = globalOrder;
            globalOrder += 1;

            const title = getNodeDisplayTitle(node);
            const titleEl = item.querySelector('.sidebar-node-title');
            if (titleEl) titleEl.textContent = title;

            const categoryLabelEl = item.querySelector('.sidebar-node-category-label');
            if (categoryLabelEl) categoryLabelEl.textContent = getSidebarNodeCategoryLabel(node.sideMenuCategoryId);

            item.dataset.title = title.toLowerCase();
            item.dataset.categoryId = node.sideMenuCategoryId;

            const orderFieldEl = item.querySelector('.sidebar-node-order-field');
            if (orderFieldEl) orderFieldEl.value = String(node.sideMenuOrder + 1);

            const hiddenJsonEl = item.querySelector('.node-json');
            if (hiddenJsonEl) hiddenJsonEl.value = JSON.stringify(node);

            return;
        }

        if (itemType === 'link') {
            const linkJsonEl = item.querySelector('.link-json');
            let link = {};
            if (linkJsonEl) {
                try {
                    link = JSON.parse(linkJsonEl.value || '{}');
                } catch (error) {
                    link = {};
                }
            }

            link.order = linkOrder;
            link.sideMenuOrder = linkOrder;
            nextLinks.push(link);

            const orderFieldEl = item.querySelector('.sidebar-link-order-field');
            if (orderFieldEl) orderFieldEl.value = String(linkOrder + 1);

            linkOrder += 1;
            return;
        }
    });

    window.sidebarLinksData = nextLinks;

    window.sidebarNodesData = window.sidebarNodesData.map((node) => {
        const nodeId = getNodeKey(node);
        return window.sidebarNodesById.get(nodeId) || node;
    });

    const allRows = Array.from(containerEl.querySelectorAll('.sidebar-node-item'));
    allRows.forEach((row, index) => {
        const upButton = row.querySelector('[data-action="move-node-up"]');
        const downButton = row.querySelector('[data-action="move-node-down"]');

        if (upButton) {
            upButton.disabled = index === 0;
        }

        if (downButton) {
            downButton.disabled = index === allRows.length - 1;
        }
    });

    const linkRows = Array.from(containerEl.querySelectorAll('.sidebar-link-item'));
    linkRows.forEach((row, index) => {
        const up = row.querySelector('[data-action="move-link-up"]');
        const down = row.querySelector('[data-action="move-link-down"]');
        if (up) up.disabled = index === 0;
        if (down) down.disabled = index === linkRows.length - 1;
    });

    const payloadInput = document.getElementById('sidebar_node_payload');
    if (payloadInput) {
        payloadInput.value = JSON.stringify({
            nodes: window.sidebarNodesData,
            sidebarCategories: window.sidebarCategoriesData,
            sidebarLinks: Array.isArray(window.sidebarLinksData) ? window.sidebarLinksData : [],
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
        const items = Array.from(containerEl.querySelectorAll('.sidebar-menu-item'));
        items.forEach((row) => {
            const title = String(row.dataset.title ?? '').toLowerCase();
            const shouldShow = query === '' || title.includes(query);
            row.style.display = shouldShow ? '' : 'none';
        });
    });
}

// category collapse listeners removed (single list menu)

function setupSidebarNodeOrderButtons(containerEl, countEl) {
    const moveNodeRowByDirection = (row, direction) => {
        const allRows = Array.from(containerEl.querySelectorAll('.sidebar-node-item'));
        const currentIndex = allRows.indexOf(row);

        if (currentIndex < 0) {
            return false;
        }

        const targetIndex = currentIndex + direction;
        if (targetIndex < 0 || targetIndex >= allRows.length) {
            return false;
        }

        const targetRow = allRows[targetIndex];

        row.remove();

        if (direction < 0) {
            containerEl.insertBefore(row, targetRow);
        } else {
            const nextSibling = targetRow.nextElementSibling;
            if (nextSibling) {
                containerEl.insertBefore(row, nextSibling);
            } else {
                containerEl.appendChild(row);
            }
        }

        return true;
    };

    containerEl.addEventListener('click', (event) => {
        const actionButton = event.target.closest('[data-action="move-node-up"], [data-action="move-node-down"]');

        if (!actionButton) {
            return;
        }

        const row = actionButton.closest('.sidebar-node-item');
        if (!row) {
            return;
        }

        const direction = actionButton.dataset.action === 'move-node-up' ? -1 : 1;
        const moved = moveNodeRowByDirection(row, direction);

        if (moved) {
            syncSidebarNodesPayload(containerEl, countEl);
        }
    });
}

function setupMenuOrderButtons(containerEl, countEl) {
    const moveItemByDirection = (item, direction) => {
        const allItems = Array.from(containerEl.querySelectorAll(':scope > .sidebar-menu-item'));
        const currentIndex = allItems.indexOf(item);

        if (currentIndex < 0) return false;

        const targetIndex = currentIndex + direction;
        if (targetIndex < 0 || targetIndex >= allItems.length) return false;

        const targetItem = allItems[targetIndex];
        item.remove();

        if (direction < 0) {
            containerEl.insertBefore(item, targetItem);
        } else {
            const nextSibling = targetItem.nextElementSibling;
            if (nextSibling) containerEl.insertBefore(item, nextSibling);
            else containerEl.appendChild(item);
        }

        return true;
    };

    containerEl.addEventListener('click', (event) => {
        const actionButton = event.target.closest('[data-action="move-link-up"], [data-action="move-link-down"]');
        if (!actionButton) return;

        const item = actionButton.closest('.sidebar-menu-item');
        if (!item) return;

        const direction = actionButton.dataset.action && actionButton.dataset.action.includes('up') ? -1 : 1;
        const moved = moveItemByDirection(item, direction);

        if (moved) {
            syncSidebarNodesPayload(containerEl, countEl);
            renderSidebarNodes(containerEl, countEl);
        }
    });
}

function buildSidebarNodeCategoryOptions(selectedCategoryId) {
    ensureSidebarState();
    const categories = sortCategories(window.sidebarCategoriesData);
    const selectedValue = String(selectedCategoryId ?? '').trim();

    let options = '<option value="">No category (top level)</option>';
    categories.forEach((category) => {
        const categoryId = getCategoryKey(category);
        const title = getCategoryDisplayTitle(category);
        const selected = categoryId === selectedValue ? 'selected' : '';

        options += `<option value="${escapeHtml(categoryId)}" ${selected}>${escapeHtml(title)}</option>`;
    });

    return options;
}

function getSidebarNodeCategoryLabel(categoryId) {
    ensureSidebarState();
    const selectedValue = String(categoryId ?? '').trim();

    if (selectedValue === '') {
        return 'No category (top level)';
    }

    const category = window.sidebarCategoriesById.get(selectedValue);
    return category ? getCategoryDisplayTitle(category) : 'No category (top level)';
}

function buildSidebarNodeTitleFields(fieldsEl, node) {
    const titleMap = getTitleMap(node.sideMenuTitle);
    const languages = getEnabledLanguages();
    const activeLanguage = languages[0] || 'en';
    const tabs = languages.map((language, index) => {
        const isActive = index === 0;

        return `
            <li class="nav-item" role="presentation">
                <button class="nav-link ${isActive ? 'active' : ''} ${index === 0 ? '' : ''}" id="sidebarNodeTitleTab_${escapeHtml(language)}" data-bs-toggle="tab" data-bs-target="#sidebarNodeTitlePane_${escapeHtml(language)}" type="button" role="tab" aria-controls="sidebarNodeTitlePane_${escapeHtml(language)}" aria-selected="${isActive ? 'true' : 'false'}">${escapeHtml(getLanguageLabel(language))}</button>
            </li>
        `;
    }).join('');

    const panes = languages.map((language, index) => {
        const value = titleMap[language] || '';
        const isActive = index === 0 ? 'show active' : '';
        const required = index === 0 ? 'required' : '';

        return `
            <div class="tab-pane fade ${isActive} p-3 border border-top-0 rounded-bottom" id="sidebarNodeTitlePane_${escapeHtml(language)}" role="tabpanel" aria-labelledby="sidebarNodeTitleTab_${escapeHtml(language)}">
                <label class="form-label" for="sidebarNodeTitle_${escapeHtml(language)}">Side Menu Title (${escapeHtml(getLanguageLabel(language))})<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="sidebarNodeTitle_${escapeHtml(language)}" data-language="${escapeHtml(language)}" data-language-value value="${escapeHtml(value)}" placeholder="Enter ${escapeHtml(getLanguageLabel(language))} title" ${required}>
            </div>
        `;
    }).join('');

    fieldsEl.innerHTML = `
        <ul class="nav nav-tabs" id="sidebarNodeTitleTabs" role="tablist">
            ${tabs}
        </ul>
        <div class="tab-content">
            ${panes}
        </div>
    `;

    return activeLanguage;
}

function updateSidebarNodeRow(row, node) {
    const title = getNodeDisplayTitle(node);
    const titleEl = row.querySelector('.sidebar-node-title');
    const iconEl = row.querySelector('i');
    const visibleInput = row.querySelector('.sidebar-node-visible-input');

    if (titleEl) {
        titleEl.textContent = title;
    }

    if (iconEl && node.sideMenuIcon) {
        iconEl.className = String(node.sideMenuIcon);
    }

    if (visibleInput) {
        visibleInput.value = node.showInSideMenu ? '1' : '0';
    }

    row.dataset.title = title.toLowerCase();
    row.dataset.categoryId = String(node.sideMenuCategoryId ?? '');
    row.dataset.showInSideMenu = node.showInSideMenu ? '1' : '0';
}

function setupSidebarNodeTitleEditor(containerEl, countEl, searchEl) {
    const modalEl = document.getElementById('sidebarNodeTitleModal');
    const modalTitleEl = document.getElementById('sidebarNodeTitleModalLabel');
    const modalNodeNameEl = document.getElementById('sidebarNodeTitleModalNodeName');
    const modalFieldsEl = document.getElementById('sidebarNodeTitleFields');
    const visibleToggleEl = document.getElementById('sidebarNodeVisibleToggle');
    const iconInput = document.getElementById('sidebarNodeIconInput');
    const iconPreviewEl = $('#sidebarNodeIconPreview');
    const selectIconButton = document.getElementById('selectSidebarNodeIconButton');
    const removeIconButton = document.getElementById('removeSidebarNodeIconButton');
    const categorySelectEl = document.getElementById('sidebarNodeCategoryInput');
    const categoryPathEl = document.getElementById('sidebarNodeCategoryPath');
    const orderInputEl = document.getElementById('sidebarNodeOrderInput');
    const saveButton = document.getElementById('saveSidebarNodeTitleButton');
    if (!modalEl || !modalFieldsEl || !saveButton || !visibleToggleEl || !categorySelectEl || !orderInputEl || !iconInput) {
        return;
    }

    const modal = typeof bootstrap !== 'undefined'
        ? bootstrap.Modal.getOrCreateInstance(modalEl)
        : null;

    let activeRow = null;
    let activeNode = null;

    const moveMenuItemToGlobalOrder = (row, orderValue) => {
        const desiredIndex = Number.isFinite(orderValue) ? Math.max(0, Math.floor(orderValue) - 1) : 0;
        const items = Array.from(containerEl.querySelectorAll(':scope > .sidebar-menu-item')).filter((item) => item !== row);
        if (items.length === 0) {
            containerEl.appendChild(row);
            return;
        }

        if (desiredIndex >= items.length) {
            containerEl.appendChild(row);
            return;
        }

        containerEl.insertBefore(row, items[desiredIndex]);
    };

    const refreshCategoryPath = () => {
        if (!categoryPathEl) {
            return;
        }

        categoryPathEl.textContent = getSidebarNodeCategoryLabel(categorySelectEl.value);
    };

    const syncCategoryControlState = () => {
        refreshCategoryPath();
    };

    const syncIconPreview = () => {
        renderSidebarCategoryIconPreview(iconPreviewEl, iconInput.value.trim());
    };

    selectIconButton?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        setTimeout(() => {
            iconLib.open(iconInput, iconPreviewEl);
        }, 100);
    });

    iconInput.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        setTimeout(() => {
            iconLib.open(iconInput, iconPreviewEl);
        }, 100);
    });

    removeIconButton?.addEventListener('click', () => {
        iconInput.value = '';
        syncIconPreview();
    });

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
        const isVisible = isSidebarNodeVisible(activeNode);

        if (modalTitleEl) {
            modalTitleEl.textContent = 'Edit title in sidebar';
        }

        if (modalNodeNameEl) {
            modalNodeNameEl.textContent = displayTitle;
        }

        buildSidebarNodeTitleFields(modalFieldsEl, activeNode);

        visibleToggleEl.checked = isVisible;
        iconInput.value = String(activeNode.sideMenuIcon ?? '');
        categorySelectEl.innerHTML = buildSidebarNodeCategoryOptions(activeNode.sideMenuCategoryId);
        categorySelectEl.value = String(activeNode.sideMenuCategoryId ?? '');
        orderInputEl.value = String(Number(activeNode.sideMenuOrder ?? 0) + 1);

        syncIconPreview();
        syncCategoryControlState();
        modal?.show();
    });

    visibleToggleEl.addEventListener('change', () => {
        if (activeRow) {
            activeRow.dataset.showInSideMenu = visibleToggleEl.checked ? '1' : '0';
        }
    });

    categorySelectEl.addEventListener('change', refreshCategoryPath);

    saveButton.addEventListener('click', () => {
        if (!activeRow || !activeNode) {
            return;
        }

        const updatedTitles = {};
        Array.from(modalFieldsEl.querySelectorAll('.tab-pane [data-language-value]')).forEach((input) => {
            const language = String(input.dataset.language ?? '').trim();
            const value = String(input.value ?? '').trim();

            if (language) {
                updatedTitles[language] = value;
            }
        });

        const selectedCategoryId = String(categorySelectEl.value ?? '').trim();
        const orderValue = Number(orderInputEl.value || 1);

        activeNode.showInSideMenu = visibleToggleEl.checked;
        activeNode.sideMenuTitle = updatedTitles;
        activeNode.sideMenuIcon = String(iconInput.value ?? '').trim();
        activeNode.sideMenuCategoryId = selectedCategoryId;
        activeNode.sideMenuOrder = Number.isNaN(orderValue) ? 0 : Math.max(0, orderValue - 1);

        // In single-list mode, category only changes metadata; order moves within one list.
        const rowCategorySelect = activeRow.querySelector('.sidebar-node-category-select');
        if (rowCategorySelect) {
            rowCategorySelect.value = selectedCategoryId;
        }
        moveMenuItemToGlobalOrder(activeRow, orderValue);

        const hiddenJsonEl = activeRow.querySelector('.node-json');
        if (hiddenJsonEl) {
            hiddenJsonEl.value = JSON.stringify(activeNode);
        }

        const visibleInput = activeRow.querySelector('.sidebar-node-visible-input');
        if (visibleInput) {
            visibleInput.value = activeNode.showInSideMenu ? '1' : '0';
        }

        activeRow.dataset.showInSideMenu = activeNode.showInSideMenu ? '1' : '0';
        activeRow.dataset.categoryId = selectedCategoryId;

        updateSidebarNodeRow(activeRow, activeNode);

        window.sidebarNodesById.set(getNodeKey(activeNode), activeNode);
        syncSidebarNodesPayload(containerEl, countEl);
        refreshSidebarLayout(containerEl, countEl, searchEl);
        modal?.hide();
    });
}

function setupSidebarLinksEditor(containerEl, countEl) {
    const modalEl = document.getElementById('sidebarLinkModal');
    const modal = typeof bootstrap !== 'undefined' ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    const indexInput = document.getElementById('sidebarLinkIndexInput');
    const titleFieldsEl = document.getElementById('sidebarLinkTitleFields');
    const urlInput = document.getElementById('sidebarLinkUrlInput');
    const typeInput = document.getElementById('sidebarLinkTypeInput');
    const orderInput = document.getElementById('sidebarLinkOrderInput');
    const actionInput = document.getElementById('sidebarLinkActionInput');
    const iconInput = document.getElementById('sidebarLinkIconInput');
    const iconPreviewEl = $('#sidebarLinkIconPreview');
    const contentWrapper = document.getElementById('sidebarLinkContentWrapper');
    const contentInput = document.getElementById('sidebarLinkContentInput');
    const imageInput = document.getElementById('sidebarLinkImageInput');
    const imagePreviewBtn = document.getElementById('sidebarLinkImagePreviewButton');
    const imagePreviewEl = document.getElementById('sidebarLinkImagePreview');
    const selectIconButton = document.getElementById('selectSidebarLinkIconButton');
    const removeIconButton = document.getElementById('removeSidebarLinkIconButton');
    const saveButton = document.getElementById('saveSidebarLinkButton');
    const addButton = document.getElementById('addSidebarLinkButton');
       
    if (!modalEl || !titleFieldsEl || !urlInput || !actionInput || !saveButton || !iconInput) return;

    const buildTitleFields = (link) => {
        const titleMap = getTitleMap(link?.title || {});
        const languages = getEnabledLanguages();
        const tabs = languages.map((language, index) => {
            const isActive = index === 0;
            return `
                <li class="nav-item" role="presentation">
                    <button class="nav-link ${isActive ? 'active' : ''}" id="sidebarLinkTitleTab_${escapeHtml(language)}" data-bs-toggle="tab" data-bs-target="#sidebarLinkTitlePane_${escapeHtml(language)}" type="button" role="tab">${escapeHtml(getLanguageLabel(language))}</button>
                </li>
            `;
        }).join('');

        const panes = languages.map((language, index) => {
            const isActive = index === 0 ? 'show active' : '';
            const value = titleMap[language] || '';
            return `
                <div class="tab-pane fade ${isActive} p-3 border border-top-0 rounded-bottom" id="sidebarLinkTitlePane_${escapeHtml(language)}" role="tabpanel">
                    <label class="form-label" for="sidebarLinkTitle_${escapeHtml(language)}">Sidebar Link Title (${escapeHtml(getLanguageLabel(language))})<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="sidebarLinkTitle_${escapeHtml(language)}" data-lang="${escapeHtml(language)}" data-language-value value="${escapeHtml(value)}" placeholder="Enter ${escapeHtml(getLanguageLabel(language))} title">
                </div>
            `;
        }).join('');

        titleFieldsEl.innerHTML = `
            <ul class="nav nav-tabs" role="tablist">
                ${tabs}
            </ul>
            <div class="tab-content">
                ${panes}
            </div>
        `;
    };

    const syncIconPreview = () => {
        renderSidebarCategoryIconPreview(iconPreviewEl, iconInput.value.trim());
    };

    const updateContentVisibility = () => {
        const typeVal = typeInput ? String(typeInput.value || '') : '';
        const actionVal = actionInput ? String(actionInput.value || '') : '';
        const shouldShow = typeVal === 'content' || actionVal === 'content';
        if (contentWrapper) {
            contentWrapper.style.display = shouldShow ? '' : 'none';
        }
    };

    typeInput?.addEventListener('change', updateContentVisibility);
    actionInput?.addEventListener('change', updateContentVisibility);

    const openModalForIndex = (index, presetLink = null) => {
        ensureSidebarState();
        const idx = Number.isFinite(Number(index)) ? Number(index) : -1;
        indexInput.value = String(idx);
        const link = isPlainObject(presetLink)
            ? presetLink
            : ((Array.isArray(window.sidebarLinksData) && window.sidebarLinksData[idx]) ? window.sidebarLinksData[idx] : {});
        buildTitleFields(link);
        urlInput.value = String(link.link ?? link.href ?? '');
        actionInput.value = String(link.mediaAction ?? link.action ?? 'link');
        typeInput && (typeInput.value = String(link.type ?? (link.mediaAction === 'content' ? 'content' : 'link')));
        orderInput && (orderInput.value = link.order ? String(Number(link.order) + 1) : '');
        iconInput.value = String(link.sideMenuIcon ?? link.icon ?? 'ri-link-line');
        // content
        if (contentInput) {
            const html = String(link.content ?? '');
            try {
                if (window.tinymce && tinymce.get('sidebarLinkContentInput')) {
                    tinymce.get('sidebarLinkContentInput').setContent(html);
                } else {
                    contentInput.value = html;
                }
            } catch (err) {
                contentInput.value = html;
            }
        }
        if (imageInput) imageInput.value = String(link.image ?? '');
        syncIconPreview();
        updateContentVisibility();
        modal?.show();

        // initialize TinyMCE for content editor if available and needed
        try {
            if (contentInput && window.tinymce && !tinymce.get('sidebarLinkContentInput')) {
                tinymce.init({
                    selector: '#sidebarLinkContentInput',
                    height: 300,
                    menubar: false,
                    plugins: 'link image lists paste table code',
                    toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code',
                });
            }
        } catch (err) {
            // ignore init errors
        }
    };

    selectIconButton?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        setTimeout(() => {
            iconLib.open(iconInput, iconPreviewEl);
        }, 100);
    });

    iconInput.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        setTimeout(() => {
            iconLib.open(iconInput, iconPreviewEl);
        }, 100);
    });

    removeIconButton?.addEventListener('click', () => {
        iconInput.value = 'ri-link-line';
        syncIconPreview();
    });

    // image preview
    imagePreviewBtn?.addEventListener('click', () => {
        const url = String(imageInput.value || '').trim();
        if (!url) {
            imagePreviewEl.innerHTML = '';
            return;
        }
        imagePreviewEl.innerHTML = `<div class="border p-2"><img src="${escapeHtml(url)}" style="max-width:100%;height:auto"></div>`;
    });

    containerEl.addEventListener('click', (event) => {
        const editBtn = event.target.closest('[data-action="edit-sidebar-link"]');
        const removeBtn = event.target.closest('[data-action="remove-sidebar-link"]');
        const moveUpBtn = event.target.closest('[data-action="move-link-up"]');
        const moveDownBtn = event.target.closest('[data-action="move-link-down"]');

        if (moveUpBtn || moveDownBtn) {
            const row = (moveUpBtn || moveDownBtn).closest('.sidebar-link-item');
            if (!row) return;
            const allRows = Array.from(containerEl.querySelectorAll('.sidebar-link-item'));
            const idx = allRows.indexOf(row);
            const direction = moveUpBtn ? -1 : 1;
            const targetIndex = idx + direction;
            if (targetIndex < 0 || targetIndex >= allRows.length) return;
            const targetRow = allRows[targetIndex];
            row.remove();
            if (direction < 0) {
                containerEl.insertBefore(row, targetRow);
            } else {
                const nextSibling = targetRow.nextElementSibling;
                if (nextSibling) containerEl.insertBefore(row, nextSibling);
                else containerEl.appendChild(row);
            }
            syncSidebarNodesPayload(containerEl, countEl);
            renderSidebarNodes(containerEl, countEl);
            syncSidebarNodesPayload(containerEl, countEl);
            return;
        }

        if (editBtn) {
            const row = editBtn.closest('.sidebar-link-item');
            if (!row) {
                return;
            }

            const idx = Number(row.dataset.linkIndex ?? -1);
            const rowJson = normalizeNodeJson(row.querySelector('.link-json')?.value || '{}');
            openModalForIndex(idx, rowJson);
            return;
        }

        if (removeBtn) {
            const row = removeBtn.closest('.sidebar-link-item');
            if (!row || !Array.isArray(window.sidebarLinksData)) {
                return;
            }

            const rowJson = row.querySelector('.link-json')?.value || '';
            const linkIndex = window.sidebarLinksData.findIndex((link) => {
                try {
                    return JSON.stringify(link) === rowJson;
                } catch (err) {
                    return false;
                }
            });

            if (linkIndex >= 0) {
                window.sidebarLinksData.splice(linkIndex, 1);
                renderSidebarNodes(containerEl, countEl);
                syncSidebarNodesPayload(containerEl, countEl);
            }
            return;
        }
    });

    addButton?.addEventListener('click', () => openModalForIndex(-1));

    saveButton.addEventListener('click', () => {
        const idx = Number(indexInput.value ?? -1);
        const titleInputs = Array.from(titleFieldsEl.querySelectorAll('[data-lang]'));
        const titleMap = {};
        titleInputs.forEach((inp) => { titleMap[inp.dataset.lang] = inp.value || ''; });

        const contentHtml = (function () {
            try {
                if (window.tinymce && tinymce.get('sidebarLinkContentInput')) return tinymce.get('sidebarLinkContentInput').getContent();
            } catch (err) {}
            return contentInput ? String(contentInput.value || '') : '';
        }());

        const nextLink = {
            title: titleMap,
            link: String(urlInput.value || ''),
            mediaAction: String(actionInput.value || 'link'),
            type: typeInput ? String(typeInput.value || '') : undefined,
            order: orderInput && orderInput.value ? Number(orderInput.value) - 1 : undefined,
            sideMenuIcon: String(iconInput.value || 'ri-link-line'),
            content: contentHtml || undefined,
            image: imageInput ? String(imageInput.value || '') : undefined,
        };

        if (!Array.isArray(window.sidebarLinksData)) {
            window.sidebarLinksData = [];
        }

        if (idx >= 0 && idx < window.sidebarLinksData.length) {
            window.sidebarLinksData[idx] = nextLink;
        } else {
            window.sidebarLinksData.push(nextLink);
        }

        renderSidebarNodes(containerEl, countEl);
        syncSidebarNodesPayload(containerEl, countEl);
        modal?.hide();
        // destroy tinymce instance for content editor to avoid duplicates
        try {
            if (window.tinymce && tinymce.get('sidebarLinkContentInput')) {
                tinymce.get('sidebarLinkContentInput').destroy();
            }
        } catch (err) {
            // ignore
        }
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

    selectIconButton?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        setTimeout(() => {
            iconLib.open(modalIconInput, modalIconPreview);
        }, 100);
    });

    modalIconInput.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        setTimeout(() => {
            iconLib.open(modalIconInput, modalIconPreview);
        }, 100);
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

        if (!Object.prototype.hasOwnProperty.call(nextCategory, 'sideMenuOrder')) {
            nextCategory.sideMenuOrder = Number(existingCategory?.sideMenuOrder ?? existingCategory?.order ?? 0);
        }

        if (isCreate) {
            nextCategory.order = window.sidebarCategoriesData.length;
            nextCategory.sideMenuOrder = window.sidebarCategoriesData.length;
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
        
        // Disable Bootstrap modal on icon modal to prevent focus trap recursion
        const iconModalEl = document.getElementById('materialIconModal');
        if (iconModalEl && typeof bootstrap !== 'undefined') {
            try {
                // Remove Bootstrap Modal instance if it exists
                const existingModal = bootstrap.Modal.getInstance(iconModalEl);
                if (existingModal) {
                    existingModal.dispose();
                }
            } catch (err) {
                // ignore
            }
        }
    }

    ensureSidebarState();
    renderSidebarNodes(listEl, countEl);
    window.sidebarSortables = setupSidebarSortables(listEl, countEl);
    setupSidebarNodeSearch(listEl, searchEl);
    setupSidebarNodeOrderButtons(listEl, countEl);
    setupMenuOrderButtons(listEl, countEl);
    setupSidebarNodeTitleEditor(listEl, countEl, searchEl);
    setupSidebarCategoryEditor(listEl, countEl, searchEl);
    setupSidebarLinksEditor(listEl, countEl);

    // category dropdown change (single list menu)
    listEl.addEventListener('change', (event) => {
        const select = event.target.closest('.sidebar-node-category-select');
        if (!select) return;
        syncSidebarNodesPayload(listEl, countEl);
    });
    syncSidebarNodesPayload(listEl, countEl);
});
