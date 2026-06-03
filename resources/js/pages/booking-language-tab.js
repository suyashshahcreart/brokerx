/**
 * Tour booking edit — Language tab (enabled slots, add language, drag order, save payloads).
 */
(function () {
    const DEFAULT_DISPLAY = {
        en: { title: 'English', short: 'EN' },
        hi: { title: 'Hindi', short: 'HI' },
        gu: { title: 'Gujarati', short: 'GU' },
    };

    const form = document.getElementById('languageTabUpdateForm');
    if (!form || !window.tourLanguageConfig) {
        return;
    }

    const state = {
        languageDisplay: { ...(window.tourLanguageConfig.languageDisplay || {}) },
        languageSlotOrder: [...(window.tourLanguageConfig.languageSlotOrder || window.tourLanguageConfig.slots || ['en'])],
        enabledLanguages: [...(window.tourLanguageConfig.enabledLanguages || ['en'])],
        defaultLanguage: window.tourLanguageConfig.defaultLanguage || 'en',
        showLanguageInContactPanel: !!window.tourLanguageConfig.showLanguageInContactPanel,
    };

    const tbody = document.getElementById('language-names-tbody');
    const enabledContainer = document.getElementById('enabled-languages-checkboxes');
    const defaultSelect = document.getElementById('default_language');
    const showPanelSwitch = document.getElementById('show_language_in_contact_panel');
    const displayPayload = document.getElementById('language_display_payload');
    const orderPayload = document.getElementById('language_slot_order_payload');

    let dragCode = null;

    function normalizeDisplay(raw) {
        const out = {};
        Object.keys(DEFAULT_DISPLAY).forEach((code) => {
            const def = DEFAULT_DISPLAY[code];
            const entry = raw && raw[code] ? raw[code] : {};
            out[code] = {
                title: String(entry.title || def.title).trim() || def.title,
                short: String(entry.short || def.short).trim() || def.short,
            };
        });
        if (raw && typeof raw === 'object') {
            Object.keys(raw).forEach((key) => {
                const lc = String(key).toLowerCase();
                if (!/^[a-z]{2}$/.test(lc) || out[lc]) {
                    return;
                }
                const entry = raw[key] || {};
                out[lc] = {
                    title: String(entry.title || '').trim() || lc.toUpperCase(),
                    short: String(entry.short || '').trim() || lc.toUpperCase(),
                };
            });
        }
        return out;
    }

    state.languageDisplay = normalizeDisplay(state.languageDisplay);

    function slotTitle(code) {
        return state.languageDisplay[code]?.title || DEFAULT_DISPLAY[code]?.title || code.toUpperCase();
    }

    function collectSlots() {
        const codes = new Set(state.languageSlotOrder);
        Object.keys(state.languageDisplay).forEach((c) => codes.add(c));
        state.enabledLanguages.forEach((c) => codes.add(c));
        const ordered = [];
        const seen = new Set();
        state.languageSlotOrder.forEach((c) => {
            const lc = String(c).toLowerCase();
            if (codes.has(lc) && !seen.has(lc) && /^[a-z]{2}$/.test(lc)) {
                ordered.push(lc);
                seen.add(lc);
            }
        });
        Array.from(codes)
            .filter((c) => !seen.has(c))
            .sort()
            .forEach((c) => ordered.push(c));
        state.languageSlotOrder = ordered;
        return ordered;
    }

    function readEnabledFromDom() {
        const checked = Array.from(form.querySelectorAll('.enabled-language-checkbox:checked')).map(
            (el) => el.value.toLowerCase()
        );
        state.enabledLanguages = checked.length ? checked : ['en'];
    }

    function readDisplayFromTable() {
        tbody.querySelectorAll('tr.language-names-row').forEach((row) => {
            const code = row.getAttribute('data-lang-code');
            if (!code) {
                return;
            }
            const titleInput = row.querySelector('.language-title-input');
            const shortInput = row.querySelector('.language-short-input');
            state.languageDisplay[code] = {
                title: titleInput ? titleInput.value.trim() : state.languageDisplay[code]?.title || '',
                short: shortInput ? shortInput.value.trim() : state.languageDisplay[code]?.short || '',
            };
        });
        const order = [];
        tbody.querySelectorAll('tr.language-names-row').forEach((row) => {
            const code = row.getAttribute('data-lang-code');
            if (code) {
                order.push(code);
            }
        });
        if (order.length) {
            state.languageSlotOrder = order;
        }
    }

    function syncHiddenPayloads() {
        readEnabledFromDom();
        readDisplayFromTable();
        if (defaultSelect) {
            state.defaultLanguage = defaultSelect.value || state.defaultLanguage;
        }
        if (showPanelSwitch) {
            state.showLanguageInContactPanel = showPanelSwitch.checked;
        }
        if (displayPayload) {
            displayPayload.value = JSON.stringify(state.languageDisplay);
        }
        if (orderPayload) {
            orderPayload.value = JSON.stringify(state.languageSlotOrder);
        }
        window.enabledLanguages = [...state.enabledLanguages];
    }

    window.prepareLanguageTabFormBeforeSubmit = syncHiddenPayloads;

    function renderEnabledCheckboxes() {
        const slots = collectSlots();
        enabledContainer.innerHTML = '';
        slots.forEach((code) => {
            const wrap = document.createElement('div');
            wrap.className = 'form-check';
            const id = `lang_enabled_${code}`;
            const checked = state.enabledLanguages.includes(code);
            wrap.innerHTML = `
                <input class="form-check-input enabled-language-checkbox" type="checkbox"
                    name="enable_language[]" id="${id}" value="${code}" ${checked ? 'checked' : ''}>
                <label class="form-check-label" for="${id}">${slotTitle(code)}</label>
            `;
            enabledContainer.appendChild(wrap);
        });
        enabledContainer.querySelectorAll('.enabled-language-checkbox').forEach((input) => {
            input.addEventListener('change', onEnabledChange);
        });
        updateShowPanelDisabled();
    }

    function renderDefaultSelect() {
        const slots = collectSlots();
        const current = defaultSelect.value || state.defaultLanguage;
        defaultSelect.innerHTML = '';
        slots.forEach((code) => {
            const opt = document.createElement('option');
            opt.value = code;
            opt.textContent = slotTitle(code);
            if (code === current) {
                opt.selected = true;
            }
            defaultSelect.appendChild(opt);
        });
        if (!slots.includes(current) && slots.length) {
            defaultSelect.value = slots[0];
            state.defaultLanguage = slots[0];
        }
    }

    function renderLanguageTable() {
        const slots = collectSlots();
        const enabledSet = new Set(state.enabledLanguages);
        const rowsHtml = slots
            .filter((code) => enabledSet.has(code))
            .map((code) => {
                const title = state.languageDisplay[code]?.title || '';
                const short = state.languageDisplay[code]?.short || '';
                const label = title || code.toUpperCase();
                return `
                <tr class="language-names-row" draggable="true" data-lang-code="${code}">
                    <td class="align-middle text-muted language-drag-handle" title="Drag to reorder">
                        <i class="ri-draggable fs-18"></i>
                    </td>
                    <td class="text-muted small language-slot-label">${label} (${code})</td>
                    <td>
                        <input type="text" class="form-control form-control-sm language-title-input"
                            data-lang-code="${code}" value="${escapeAttr(title)}" autocomplete="off">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm language-short-input"
                            data-lang-code="${code}" maxlength="12" value="${escapeAttr(short)}" autocomplete="off">
                    </td>
                </tr>`;
            })
            .join('');
        tbody.innerHTML = rowsHtml;
        bindDragRows();
    }

    function escapeAttr(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');
    }

    function bindDragRows() {
        tbody.querySelectorAll('tr.language-names-row').forEach((row) => {
            row.addEventListener('dragstart', (e) => {
                dragCode = row.getAttribute('data-lang-code');
                row.classList.add('table-active');
                try {
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', dragCode);
                } catch (_) {}
            });
            row.addEventListener('dragend', () => {
                dragCode = null;
                row.classList.remove('table-active');
            });
            row.addEventListener('dragover', (e) => {
                e.preventDefault();
            });
            row.addEventListener('drop', (e) => {
                e.preventDefault();
                const targetCode = row.getAttribute('data-lang-code');
                const from = dragCode;
                if (!from || !targetCode || from === targetCode) {
                    return;
                }
                readDisplayFromTable();
                const enabledSet = new Set(state.enabledLanguages);
                const enabledList = state.languageSlotOrder.filter((c) => enabledSet.has(c));
                const disabledTail = state.languageSlotOrder.filter((c) => !enabledSet.has(c));
                const fromIdx = enabledList.indexOf(from);
                const toIdx = enabledList.indexOf(targetCode);
                if (fromIdx < 0 || toIdx < 0) {
                    return;
                }
                const next = [...enabledList];
                next.splice(fromIdx, 1);
                next.splice(toIdx, 0, from);
                state.languageSlotOrder = [...next, ...disabledTail];
                renderLanguageTable();
            });
        });
    }

    function updateShowPanelDisabled() {
        if (!showPanelSwitch) {
            return;
        }
        const count = form.querySelectorAll('.enabled-language-checkbox:checked').length;
        showPanelSwitch.disabled = count <= 1;
        if (count <= 1) {
            showPanelSwitch.checked = false;
        }
    }

    function onEnabledChange() {
        readEnabledFromDom();
        renderLanguageTable();
        renderDefaultSelect();
        updateShowPanelDisabled();
        window.enabledLanguages = [...state.enabledLanguages];
        if (typeof window.syncTourLanguageTabs === 'function') {
            window.syncTourLanguageTabs();
        }
    }

    function showAddError(msg) {
        const el = document.getElementById('add_language_error');
        if (!el) {
            return;
        }
        if (msg) {
            el.textContent = msg;
            el.classList.remove('d-none');
        } else {
            el.textContent = '';
            el.classList.add('d-none');
        }
    }

    document.getElementById('add_language_btn')?.addEventListener('click', () => {
        const titleInput = document.getElementById('add_language_title');
        const shortInput = document.getElementById('add_language_short');
        const title = (titleInput?.value || '').trim();
        const shortRaw = (shortInput?.value || '').trim();
        showAddError('');

        if (!title) {
            showAddError('Enter a title (e.g. Spanish).');
            return;
        }
        if (!shortRaw) {
            showAddError('Enter a short code (e.g. ES).');
            return;
        }
        const lettersOnly = shortRaw.replace(/[^a-zA-Z]/g, '');
        if (lettersOnly.length < 2) {
            showAddError('Short code must contain at least two letters (e.g. ES).');
            return;
        }
        const code = lettersOnly.slice(0, 2).toLowerCase();
        if (state.languageSlotOrder.includes(code)) {
            showAddError('A language with that short code is already listed.');
            return;
        }
        const shortDisplay = shortRaw.length > 12 ? shortRaw.slice(0, 12) : shortRaw;
        state.languageDisplay[code] = { title, short: shortDisplay };
        state.languageSlotOrder.push(code);
        if (!state.enabledLanguages.includes(code)) {
            state.enabledLanguages.push(code);
        }
        if (titleInput) {
            titleInput.value = '';
        }
        if (shortInput) {
            shortInput.value = '';
        }
        renderEnabledCheckboxes();
        renderDefaultSelect();
        renderLanguageTable();
        if (window.tourLanguageConfig) {
            window.tourLanguageConfig.languageSlotOrder = [...state.languageSlotOrder];
            window.tourLanguageConfig.slots = [...state.languageSlotOrder];
            window.tourLanguageConfig.languageDisplay = { ...state.languageDisplay };
        }
        window.enabledLanguages = [...state.enabledLanguages];
        if (typeof window.syncTourLanguageTabs === 'function') {
            window.syncTourLanguageTabs();
        }
    });

    enabledContainer?.querySelectorAll('.enabled-language-checkbox').forEach((input) => {
        input.addEventListener('change', onEnabledChange);
    });

    form.addEventListener('submit', () => {
        syncHiddenPayloads();
    }, true);

    updateShowPanelDisabled();

    /** Re-render table when only enabled langs should show in names table */
    renderLanguageTable();
})();
