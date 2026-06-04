/**
 * Shared tour-detail language tabs: show enabled languages in languageSlotOrder.
 */

function normalizeCode(code) {
    return String(code || '').toLowerCase();
}

/**
 * All language slot codes in sort order.
 *
 * @returns {string[]}
 */
export function getTourLanguageSlots() {
    const cfg = window.tourLanguageConfig || {};
    const slots = cfg.languageSlotOrder || cfg.slots || [];
    if (Array.isArray(slots) && slots.length > 0) {
        return slots.map(normalizeCode);
    }

    return ['en', 'hi', 'gu'];
}

/**
 * @param {string} code
 * @returns {string}
 */
export function getTourLanguageLabel(code) {
    const cfg = window.tourLanguageConfig || {};
    const display = cfg.languageDisplay || {};
    const lc = normalizeCode(code);

    return display[lc]?.title || lc.toUpperCase();
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/**
 * Build nav + panes HTML for dynamic rows (e.g. sidebar links).
 *
 * @param {object} options
 * @param {string} options.groupId
 * @param {number} options.rowIndex
 * @param {'title'|'content'} options.field
 * @param {Record<string, string>} [options.values]
 * @param {boolean} [options.firstFieldRequired]
 * @returns {{ navHtml: string, panesHtml: string, firstEnabledCode: string }}
 */
export function buildDynamicLanguageTabGroup(options) {
    const {
        groupId,
        rowIndex,
        field,
        values = {},
        firstFieldRequired = false,
    } = options;

    const slots = getTourLanguageSlots();
    const enabled = resolveOrderedEnabledLanguages();
    const enabledSet = new Set(enabled);
    const firstEnabled = enabled[0] || 'en';
    const panePrefix = `${groupId}-lang`;

    let tabsItems = '';
    let panes = '';

    slots.forEach((code) => {
        const isEnabled = enabledSet.has(code);
        const isActive = isEnabled && code === firstEnabled;
        const label = getTourLanguageLabel(code);
        const paneId = `${panePrefix}-${code}-pane`;
        const tabId = `${groupId}-${code}-tab`;
        const rawValue = values[code] ?? '';

        tabsItems += `
            <li class="nav-item ${isEnabled ? '' : 'd-none'}" role="presentation">
                <button type="button" class="nav-link ${isActive ? 'active' : ''} p-1"
                    id="${tabId}"
                    data-language="${code}"
                    data-bs-toggle="tab"
                    data-bs-target="#${paneId}"
                    role="tab"
                    aria-controls="${paneId}"
                    aria-selected="${isActive ? 'true' : 'false'}">${escapeHtml(label)}</button>
            </li>`;

        if (field === 'content') {
            panes += `
            <div class="tab-pane fade ${isActive ? 'show active' : ''} ${isEnabled ? '' : 'd-none'}"
                id="${paneId}" data-language="${code}" role="tabpanel" aria-labelledby="${tabId}">
                <textarea name="sidebar_links[${rowIndex}][content][${code}]" class="editor">${rawValue}</textarea>
            </div>`;
        } else {
            const requiredAttr = firstFieldRequired && isActive ? 'required' : '';
            panes += `
            <div class="tab-pane fade ${isActive ? 'show active' : ''} ${isEnabled ? '' : 'd-none'}"
                id="${paneId}" data-language="${code}" role="tabpanel" aria-labelledby="${tabId}">
                <input type="text" name="sidebar_links[${rowIndex}][title][${code}]"
                    class="form-control mb-2 sidebar-link-title-input" data-language="${code}"
                    placeholder="e.g, Floor Plan" ${requiredAttr} value="${escapeHtml(rawValue)}">
            </div>`;
        }
    });

    return {
        navHtml: `<ul class="nav nav-tabs mb-2 tour-language-tab-nav" id="${groupId}" role="tablist" data-tour-lang-tab-nav="${groupId}">${tabsItems}</ul>`,
        panesHtml: `<div class="tab-content" id="${groupId}Content" data-tour-lang-tab-panes="${groupId}">${panes}</div>`,
        firstEnabledCode: firstEnabled,
    };
}

/**
 * Enabled languages in slot order (Language tab checkboxes, then window config).
 *
 * @returns {string[]}
 */
export function resolveOrderedEnabledLanguages() {
    const cfg = window.tourLanguageConfig || {};
    const slotOrder = (cfg.languageSlotOrder || cfg.slots || []).map(normalizeCode);

    let enabled = [];
    const checkboxInputs = document.querySelectorAll(
        '#languageTabUpdateForm input[name="enable_language[]"]'
    );

    if (checkboxInputs.length > 0) {
        enabled = Array.from(checkboxInputs)
            .filter((input) => input.checked)
            .map((input) => normalizeCode(input.value));
    } else if (Array.isArray(window.enabledLanguages)) {
        enabled = window.enabledLanguages.map(normalizeCode);
    }

    if (enabled.length === 0) {
        enabled = ['en'];
    }

    const enabledSet = new Set(enabled);
    const ordered = [];
    const seen = new Set();

    slotOrder.forEach((code) => {
        if (enabledSet.has(code) && !seen.has(code)) {
            ordered.push(code);
            seen.add(code);
        }
    });

    enabled.forEach((code) => {
        if (!seen.has(code)) {
            ordered.push(code);
            seen.add(code);
        }
    });

    return ordered;
}

function reorderByLanguage(container, selector, languagesToShow, getLangCode = null) {
    if (!container) {
        return;
    }

    const items = Array.from(container.querySelectorAll(selector));
    const byLang = {};
    items.forEach((el) => {
        const lang = getLangCode
            ? getLangCode(el)
            : normalizeCode(el.getAttribute('data-language'));
        if (lang) {
            byLang[lang] = el;
        }
    });

    languagesToShow.forEach((lang) => {
        if (byLang[lang]) {
            container.appendChild(byLang[lang]);
        }
    });

    items.forEach((el) => {
        const lang = getLangCode
            ? getLangCode(el)
            : normalizeCode(el.getAttribute('data-language'));
        if (lang && !languagesToShow.includes(lang)) {
            container.appendChild(el);
        }
    });
}

function syncNavGroup(navEl, languagesToShow) {
    const tabButtons = Array.from(navEl.querySelectorAll('button[data-language]'));

    tabButtons.forEach((button) => {
        const lang = normalizeCode(button.getAttribute('data-language'));
        const shouldShow = languagesToShow.includes(lang);
        const navItem = button.closest('.nav-item') || button;

        navItem.classList.toggle('d-none', !shouldShow);

        if (!shouldShow) {
            button.classList.remove('active');
            button.setAttribute('aria-selected', 'false');
        }
    });

    reorderByLanguage(navEl, ':scope > .nav-item', languagesToShow, (el) => {
        const btn = el.querySelector('button[data-language]');
        return btn ? normalizeCode(btn.getAttribute('data-language')) : null;
    });

    const activeVisible = tabButtons.find((button) => {
        const navItem = button.closest('.nav-item');
        return button.classList.contains('active') && navItem && !navItem.classList.contains('d-none');
    });

    if (!activeVisible && window.bootstrap) {
        const firstVisible = tabButtons.find((button) => {
            const navItem = button.closest('.nav-item');
            return navItem && !navItem.classList.contains('d-none');
        });
        if (firstVisible) {
            window.bootstrap.Tab.getOrCreateInstance(firstVisible).show();
        }
    }
}

function syncPanesGroup(panesEl, languagesToShow) {
    const panes = Array.from(panesEl.querySelectorAll('.tab-pane[data-language]'));

    panes.forEach((pane) => {
        const lang = normalizeCode(pane.getAttribute('data-language'));
        const shouldShow = languagesToShow.includes(lang);
        pane.classList.toggle('d-none', !shouldShow);

        if (!shouldShow) {
            pane.classList.remove('active', 'show');
        }
    });

    reorderByLanguage(panesEl, '.tab-pane[data-language]', languagesToShow);
}

/**
 * Sync every tour language tab group on the page (bookmark, footer, etc.).
 */
export function syncTourLanguageTabs() {
    const languagesToShow = resolveOrderedEnabledLanguages();

    document.querySelectorAll('[data-tour-lang-tab-nav]').forEach((navEl) => {
        const groupId = navEl.getAttribute('data-tour-lang-tab-nav');
        const panesEl = groupId
            ? document.querySelector(`[data-tour-lang-tab-panes="${groupId}"]`)
            : null;

        syncNavGroup(navEl, languagesToShow);
        if (panesEl) {
            syncPanesGroup(panesEl, languagesToShow);
        }
    });

}
