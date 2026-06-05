<?php require_once __DIR__ . '/auth.php'; requireAuth(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .portfolio-drag-handle {
            cursor: grab;
            user-select: none;
            color: var(--bs-secondary);
            font-size: 1rem;
            line-height: 1;
            padding: 0.25rem 0.35rem;
            display: inline-block;
            border-radius: 0.25rem;
        }
        .portfolio-drag-handle:hover { background: rgba(0,0,0,.06); color: var(--bs-dark); }
        .portfolio-drag-handle:active { cursor: grabbing; }
        tr.portfolio-row-drag-over { outline: 2px dashed var(--bs-primary); outline-offset: -2px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Portfolio Manager</span>
            <div class="d-flex gap-2">
                <a href="manage-api.php" class="btn btn-outline-light btn-sm" target="_blank" rel="noopener">View JSON</a>
                <a href="api.php" class="btn btn-outline-light btn-sm" target="_blank" rel="noopener">View API</a>
                <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

        <!-- Export & Settings -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Data & Settings</h5>
                
            </div>
            <div class="card-body">
                <div class="alert alert-secondary small mb-3">
                    <strong>API key</strong> (for frontends — <code>api.php</code> only):<br>
                    <code class="user-select-all"><?= htmlspecialchars(portfolioConfig()['api_key'] ?? '') ?></code><br>
                    <span class="text-muted">Header <code>X-Api-Key</code> or <code>?api_key=</code>. Direct <code>data/portfolio.json</code> is blocked.</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Items per page</label>
                        <input type="number" class="form-control" id="defaultPerPage" min="1" max="100" value="6">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Default sort</label>
                        <select class="form-select" id="defaultSort">
                            <option value="sr_no">SR No</option>
                            <option value="date">Date</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort order</label>
                        <select class="form-select" id="defaultSortOrder">
                            <option value="asc">Ascending</option>
                            <option value="desc">Descending</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" id="saveSettingsBtn">Save Settings</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Property Types -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Property Types</h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="newPropertyType" placeholder="Add type..." style="width: 180px;">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addPropertyTypeBtn">Add</button>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2" id="propertyTypesList"></div>
            </div>
        </div>

        <!-- Items -->
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0">Portfolio Items</h5>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <label class="mb-0 small text-muted">Reorder mode</label>
                    <select class="form-select form-select-sm" id="reorderMode" style="width: auto; min-width: 220px;" title="All: ↑↓ changes main order. A type: ↑↓ changes order within that type only (used when the site filters by that type)."></select>
                    <button type="button" class="btn btn-primary" id="addItemBtn">Add Item</button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2" id="reorderModeHelp"></p>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 40px;" title="Drag rows to reorder (same as ↑↓ for current mode)"></th>
                                <th style="width: 72px;" title="Order for the full portfolio (all items)">Main SR</th>
                                <th style="width: 72px;" title="Order within this property type (used when visitors filter by type)">Type SR</th>
                                <th>Title</th>
                                <th>Property Type</th>
                                <th style="width: 80px;">Thumbnail</th>
                                <th>Date</th>
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit/Add Item Modal -->
    <div class="modal fade" id="itemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalTitle">Add Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editItemIndex">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" id="itemTitle" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Property Type *</label>
                        <select class="form-select" id="itemPropertyType"></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Property Sub Type</label>
                        <input type="text" class="form-control" id="itemPropertySubType" placeholder="Optional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Thumbnail URL</label>
                        <input type="url" class="form-control" id="itemThumbnail" placeholder="https://...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tour Live Link *</label>
                        <input type="url" class="form-control" id="itemTourLink" placeholder="https://...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="itemDate">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveItemBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Remove property type: two-step confirmation -->
    <div class="modal fade" id="deletePropertyTypeModal" tabindex="-1" aria-labelledby="deletePtModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePtModalTitle">Remove property type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="deletePtStep1"></div>
                    <div id="deletePtStep2" class="d-none"></div>
                </div>
                <div class="modal-footer" id="deletePtFooter1">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="deletePtContinueBtn">Continue</button>
                </div>
                <div class="modal-footer d-none" id="deletePtFooter2">
                    <button type="button" class="btn btn-outline-secondary" id="deletePtBackBtn">Back</button>
                    <button type="button" class="btn btn-danger" id="deletePtConfirmBtn">Remove from list</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
(function() {
    const THUMB_PLACEHOLDER = 'https://bk.proppik.cloud/portfolio/assets/images/mockup.png';
    let data = { settings: {}, property_types: [], items: [] };
    let reorderMode = 'all';
    let pendingPtDelete = null;
    let lastPortfolioDragPayload = null;
    let lastPortfolioDragOverTr = null;

    function toast(msg, type = 'success') {
        const c = document.getElementById('toastContainer');
        const el = document.createElement('div');
        el.className = `alert alert-${type} alert-dismissible fade show`;
        el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        c.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    function migratePropertyTypesSrNo() {
        if (!data.items.some(it => it.property_types_sr_no == null)) return;
        const sorted = [...data.items].sort((a, b) => (a.sr_no || 0) - (b.sr_no || 0));
        const counters = {};
        sorted.forEach(it => {
            const k = it.property_type || '';
            counters[k] = (counters[k] || 0) + 1;
            it.property_types_sr_no = counters[k];
        });
    }

    function nextTypeSr(typeKey, excludeItemId) {
        let m = 0;
        data.items.forEach(it => {
            if (excludeItemId != null && it.id === excludeItemId) return;
            if ((it.property_type || '') !== (typeKey || '')) return;
            const v = parseInt(it.property_types_sr_no, 10) || 0;
            if (v > m) m = v;
        });
        return m + 1;
    }

    function renumberMainSrNo() {
        data.items.forEach((item, i) => { item.sr_no = i + 1; });
    }

    function renumberPropertyTypeGroup(typeKey) {
        const k = typeKey || '';
        const group = data.items
            .filter(it => (it.property_type || '') === k)
            .sort((a, b) => (a.property_types_sr_no || 0) - (b.property_types_sr_no || 0));
        group.forEach((it, idx) => { it.property_types_sr_no = idx + 1; });
    }

    function moveWithinType(typeKey, displayIndex, delta) {
        const group = data.items
            .filter(it => (it.property_type || '') === typeKey)
            .sort((a, b) => (a.property_types_sr_no || 0) - (b.property_types_sr_no || 0));
        const j = displayIndex + delta;
        if (j < 0 || j >= group.length) return;
        const row = group.splice(displayIndex, 1)[0];
        group.splice(j, 0, row);
        group.forEach((it, idx) => { it.property_types_sr_no = idx + 1; });
    }

    function moveItemInGlobalItems(fromIndex, toIndex) {
        if (fromIndex === toIndex) return;
        if (fromIndex < 0 || toIndex < 0 || fromIndex >= data.items.length || toIndex >= data.items.length) return;
        const [el] = data.items.splice(fromIndex, 1);
        data.items.splice(toIndex, 0, el);
        renumberMainSrNo();
    }

    function moveTypeRowTo(typeKey, fromDi, toDi) {
        if (fromDi === toDi) return;
        const group = data.items
            .filter(it => (it.property_type || '') === typeKey)
            .sort((a, b) => (a.property_types_sr_no || 0) - (b.property_types_sr_no || 0));
        if (fromDi < 0 || toDi < 0 || fromDi >= group.length || toDi >= group.length) return;
        const [row] = group.splice(fromDi, 1);
        group.splice(toDi, 0, row);
        group.forEach((it, idx) => { it.property_types_sr_no = idx + 1; });
    }

    function buildReorderModeSelect() {
        const sel = document.getElementById('reorderMode');
        const types = [...new Set(data.items.map(it => it.property_type || ''))].sort();
        sel.innerHTML = '<option value="all">All items (main SR)</option>' +
            types.map(t => `<option value="${escapeHtml(t)}">${escapeHtml(t || '(empty type)')}</option>`).join('');
        sel.value = reorderMode;
        if (![...sel.options].some(o => o.value === reorderMode)) reorderMode = 'all';
        sel.value = reorderMode;
    }

    async function load() {
        const r = await fetch('manage-api.php');
        if (r.status === 401) {
            window.location.href = 'login.php?redirect=' + encodeURIComponent('manage.php');
            return;
        }
        const j = await r.json();
        if (j.success && j.data) data = j.data;
        if (!data.settings) data.settings = { default_per_page: 6, default_sort: 'sr_no', default_sort_order: 'desc' };
        if (!data.property_types) data.property_types = [];
        if (!data.items) data.items = [];
        migratePropertyTypesSrNo();
        render();
    }

    async function save() {
        const r = await fetch('manage-api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'save', data: data })
        });
        if (r.status === 401) {
            window.location.href = 'login.php?redirect=' + encodeURIComponent('manage.php');
            return;
        }
        const j = await r.json();
        if (j.success) toast('Saved successfully');
        else toast(j.message || 'Save failed', 'danger');
    }

    function setupItemsTableDragDrop() {
        const tbody = document.getElementById('itemsTableBody');
        if (!tbody || tbody.dataset.dragBound === '1') return;
        tbody.dataset.dragBound = '1';

        function clearDragOverRow() {
            if (lastPortfolioDragOverTr) {
                lastPortfolioDragOverTr.classList.remove('portfolio-row-drag-over');
                lastPortfolioDragOverTr = null;
            }
        }

        tbody.addEventListener('dragstart', (e) => {
            const handle = e.target.closest && e.target.closest('.portfolio-drag-handle');
            if (!handle) return;
            const tr = handle.closest('tr[data-gi]');
            if (!tr) return;
            lastPortfolioDragPayload = {
                gi: parseInt(tr.dataset.gi, 10),
                di: parseInt(tr.dataset.di, 10),
                mode: reorderMode
            };
            e.dataTransfer.setData('text/plain', JSON.stringify(lastPortfolioDragPayload));
            e.dataTransfer.effectAllowed = 'move';
            tr.classList.add('opacity-50');
        });

        tbody.addEventListener('dragend', () => {
            tbody.querySelectorAll('tr.portfolio-item-row').forEach(r => r.classList.remove('opacity-50'));
            clearDragOverRow();
            lastPortfolioDragPayload = null;
        });

        tbody.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const tr = e.target.closest('tr[data-gi]');
            if (tr === lastPortfolioDragOverTr) return;
            clearDragOverRow();
            lastPortfolioDragOverTr = tr;
            if (tr) tr.classList.add('portfolio-row-drag-over');
        });

        tbody.addEventListener('dragleave', (e) => {
            const tr = e.target.closest('tr[data-gi]');
            if (tr && e.relatedTarget && tr.contains(e.relatedTarget)) return;
            if (tr) tr.classList.remove('portfolio-row-drag-over');
            if (lastPortfolioDragOverTr === tr) lastPortfolioDragOverTr = null;
        });

        tbody.addEventListener('drop', (e) => {
            e.preventDefault();
            clearDragOverRow();
            tbody.querySelectorAll('tr.portfolio-item-row').forEach(r => r.classList.remove('opacity-50'));
            const tr = e.target.closest('tr[data-gi]');
            if (!tr) return;
            let p = lastPortfolioDragPayload;
            try {
                const raw = e.dataTransfer.getData('text/plain');
                if (raw) p = JSON.parse(raw);
            } catch (err) { /* keep lastPortfolioDragPayload */ }
            if (!p || p.mode !== reorderMode) return;
            const toGi = parseInt(tr.dataset.gi, 10);
            const toDi = parseInt(tr.dataset.di, 10);
            if (reorderMode === 'all') {
                if (p.gi === toGi) return;
                moveItemInGlobalItems(p.gi, toGi);
            } else {
                if (p.di === toDi) return;
                moveTypeRowTo(reorderMode, p.di, toDi);
            }
            save().then(render);
        });
    }


    function render() {
        const s = data.settings;
        document.getElementById('defaultPerPage').value = s.default_per_page ?? 6;
        document.getElementById('defaultSort').value = s.default_sort ?? 'sr_no';
        document.getElementById('defaultSortOrder').value = s.default_sort_order ?? 'desc';

        buildReorderModeSelect();
        document.getElementById('reorderMode').onchange = () => {
            reorderMode = document.getElementById('reorderMode').value;
            render();
        };
        const help = document.getElementById('reorderModeHelp');
        if (reorderMode === 'all') {
            help.textContent = 'Main SR: order for the full portfolio. Drag the ⋮⋮ handle or use ↑↓ to change main order.';
        } else {
            help.textContent = 'Type SR mode: drag ⋮⋮ or use ↑↓ within “' + (reorderMode || '(empty type)') + '” only. Main SR is unchanged.';
        }

        const ptList = document.getElementById('propertyTypesList');
        ptList.innerHTML = data.property_types.map((t, i) => {
            const name = typeof t === 'string' ? t : t.name || t;
            return `<span class="badge bg-secondary d-inline-flex align-items-center gap-1">${escapeHtml(name)} <button type="button" class="btn-close btn-close-white btn-close-sm" data-i="${i}" aria-label="Remove"></button></span>`;
        }).join('');
        ptList.querySelectorAll('.btn-close').forEach(b => {
            b.onclick = () => openDeletePropertyTypeFlow(parseInt(b.dataset.i, 10));
        });

        const tbody = document.getElementById('itemsTableBody');
        let rows;
        if (reorderMode === 'all') {
            rows = data.items.map((item, i) => ({ item, globalIndex: i, displayIndex: i }));
        } else {
            const tk = reorderMode;
            rows = data.items
                .map((item, globalIndex) => ({ item, globalIndex }))
                .filter(x => (x.item.property_type || '') === tk)
                .sort((a, b) => (a.item.property_types_sr_no || 0) - (b.item.property_types_sr_no || 0))
                .map((row, displayIndex) => ({ ...row, displayIndex }));
        }

        tbody.innerHTML = rows.map((row) => {
            const item = row.item;
            const gi = row.globalIndex;
            const di = row.displayIndex;
            const thumbRaw = item.thumbnail != null ? String(item.thumbnail).trim() : '';
            const thumb = thumbRaw || THUMB_PLACEHOLDER;
            const mainSr = item.sr_no ?? (gi + 1);
            const typeSr = item.property_types_sr_no ?? mainSr;
            return `<tr data-gi="${gi}" data-di="${di}" class="portfolio-item-row">
                <td class="text-center align-middle pe-0">
                    <span class="portfolio-drag-handle" draggable="true" title="Drag to reorder">⋮⋮</span>
                </td>
                <td>${mainSr}</td>
                <td>${typeSr}</td>
                <td>${escapeHtml(item.title || '')}</td>
                <td>${escapeHtml(item.property_type || '')}</td>
                <td><img src="${escapeHtml(thumb)}" alt="" class="img-thumbnail" style="width:60px;height:40px;object-fit:cover" onerror='this.onerror=null;this.src=${JSON.stringify(THUMB_PLACEHOLDER)}'></td>
                <td>${escapeHtml(item.date || '')}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-i="${gi}">Edit</button>
                    <button class="btn btn-sm btn-outline-secondary me-1" data-action="up" data-i="${gi}" data-di="${di}" ${reorderMode === 'all' ? '' : 'data-type="1"'}>↑</button>
                    <button class="btn btn-sm btn-outline-secondary me-1" data-action="down" data-i="${gi}" data-di="${di}" ${reorderMode === 'all' ? '' : 'data-type="1"'}>↓</button>
                    <button class="btn btn-sm btn-outline-danger" data-action="del" data-i="${gi}">Delete</button>
                </td>
            </tr>`;
        }).join('');

        tbody.querySelectorAll('[data-action]').forEach(b => {
            b.onclick = () => {
                const i = parseInt(b.dataset.i, 10);
                const di = parseInt(b.dataset.di, 10);
                if (b.dataset.action === 'edit') openEditModal(i);
                else if (b.dataset.action === 'del') {
                    const removed = data.items[i];
                    data.items.splice(i, 1);
                    renumberMainSrNo();
                    renumberPropertyTypeGroup(removed.property_type || '');
                    save().then(render);
                } else if (b.dataset.action === 'up') {
                    if (reorderMode === 'all') {
                        if (i > 0) {
                            [data.items[i - 1], data.items[i]] = [data.items[i], data.items[i - 1]];
                            renumberMainSrNo();
                            save().then(render);
                        }
                    } else if (b.dataset.type === '1') {
                        moveWithinType(reorderMode, di, -1);
                        save().then(render);
                    }
                } else if (b.dataset.action === 'down') {
                    if (reorderMode === 'all') {
                        if (i < data.items.length - 1) {
                            [data.items[i], data.items[i + 1]] = [data.items[i + 1], data.items[i]];
                            renumberMainSrNo();
                            save().then(render);
                        }
                    } else if (b.dataset.type === '1') {
                        moveWithinType(reorderMode, di, 1);
                        save().then(render);
                    }
                }
            };
        });
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function normalizePropertyTypeEntry(t) {
        if (t == null) return '';
        return typeof t === 'string' ? t.trim() : String(t.name || '').trim();
    }

    function propertyTypesListHasName(name) {
        const n = (name || '').trim().toLowerCase();
        if (!n) return false;
        return data.property_types.some(t => normalizePropertyTypeEntry(t).toLowerCase() === n);
    }

    function addPropertyTypeFromInput() {
        const input = document.getElementById('newPropertyType');
        const v = (input && input.value) ? input.value.trim() : '';
        if (!v) {
            toast('Enter a property type name first.', 'warning');
            input.focus();
            return;
        }
        if (propertyTypesListHasName(v)) {
            toast('That property type is already in the list.', 'warning');
            input.select();
            return;
        }
        data.property_types.push(v);
        data.property_types.sort((a, b) =>
            normalizePropertyTypeEntry(a).localeCompare(normalizePropertyTypeEntry(b), undefined, { sensitivity: 'base' }));
        input.value = '';
        save().then(render);
    }

    function getPropertyTypeNameAt(index) {
        const t = data.property_types[index];
        return typeof t === 'string' ? t : (t && t.name) || '';
    }

    function resetDeletePropertyTypeModalUi() {
        document.getElementById('deletePtStep1').classList.remove('d-none');
        document.getElementById('deletePtStep2').classList.add('d-none');
        document.getElementById('deletePtFooter1').classList.remove('d-none');
        document.getElementById('deletePtFooter2').classList.add('d-none');
    }

    function openDeletePropertyTypeFlow(index) {
        const name = getPropertyTypeNameAt(index);
        pendingPtDelete = { index, name };
        resetDeletePropertyTypeModalUi();

        const affected = data.items.filter(it => (it.property_type || '') === name);
        const step1 = document.getElementById('deletePtStep1');
        let html = '<p class="mb-2">Remove <strong>' + escapeHtml(name) + '</strong> from the <em>Property Types</em> list?</p>';
        html += '<div class="alert alert-info small mb-3 py-2"><strong>No portfolio items are deleted.</strong> This only removes the name from the master list used for labels and the add-item dropdown.</div>';
        if (affected.length > 0) {
            html += '<p class="mb-1 fw-semibold">' + affected.length + ' item(s) still use this property type:</p>';
            html += '<ul class="small mb-2 ps-3" style="max-height: 200px; overflow-y: auto;">';
            affected.slice(0, 25).forEach(it => {
                html += '<li>' + escapeHtml(it.title || '(untitled)') + '</li>';
            });
            if (affected.length > 25) {
                html += '<li class="text-muted">… and ' + (affected.length - 25) + ' more</li>';
            }
            html += '</ul>';
            html += '<p class="small text-muted mb-0">Those rows stay in your data with the same type text until you edit each item. Reorder mode and filters may still see this type until items are updated.</p>';
        } else {
            html += '<p class="small text-muted mb-0">No portfolio items use this type. It will only be removed from the list.</p>';
        }

        step1.innerHTML = html;
        document.getElementById('deletePtModalTitle').textContent = 'Remove property type — step 1 of 2';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deletePropertyTypeModal')).show();
    }

    function openEditModal(index) {
        const item = index >= 0 ? data.items[index] : null;
        document.getElementById('itemModalTitle').textContent = item ? 'Edit Item' : 'Add Item';
        document.getElementById('editItemIndex').value = index;
        document.getElementById('itemTitle').value = item?.title || '';
        const types = data.property_types && data.property_types.length ? data.property_types : ['Other'];
        document.getElementById('itemPropertyType').innerHTML = types.map(t => {
            const n = typeof t === 'string' ? t : t.name || t;
            return `<option value="${escapeHtml(n)}">${escapeHtml(n)}</option>`;
        }).join('');
        if (item) {
            document.getElementById('itemPropertyType').value = item.property_type || '';
            document.getElementById('itemPropertySubType').value = item.property_sub_type || '';
            document.getElementById('itemThumbnail').value = item.thumbnail || '';
            document.getElementById('itemTourLink').value = item.tour_live_link || '';
            document.getElementById('itemDate').value = item.date || '';
        } else {
            document.getElementById('itemPropertySubType').value = '';
            document.getElementById('itemThumbnail').value = '';
            document.getElementById('itemTourLink').value = '';
            document.getElementById('itemDate').value = new Date().toISOString().slice(0, 10);
        }
        new bootstrap.Modal(document.getElementById('itemModal')).show();
    }

    document.getElementById('saveSettingsBtn').onclick = () => {
        data.settings = {
            default_per_page: parseInt(document.getElementById('defaultPerPage').value) || 6,
            default_sort: document.getElementById('defaultSort').value,
            default_sort_order: document.getElementById('defaultSortOrder').value
        };
        save().then(render);
    };

    document.getElementById('addPropertyTypeBtn').addEventListener('click', () => addPropertyTypeFromInput());

    document.getElementById('newPropertyType').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addPropertyTypeFromInput();
        }
    });

    document.getElementById('addItemBtn').onclick = () => openEditModal(-1);

    document.getElementById('deletePtContinueBtn').onclick = () => {
        if (!pendingPtDelete) return;
        const name = pendingPtDelete.name;
        document.getElementById('deletePtStep1').classList.add('d-none');
        document.getElementById('deletePtStep2').classList.remove('d-none');
        document.getElementById('deletePtFooter1').classList.add('d-none');
        document.getElementById('deletePtFooter2').classList.remove('d-none');
        document.getElementById('deletePtModalTitle').textContent = 'Remove property type — step 2 of 2';
        document.getElementById('deletePtStep2').innerHTML =
            '<p class="mb-2">Confirm again: remove <strong>' + escapeHtml(name) + '</strong> from the Property Types list?</p>' +
            '<p class="small text-muted mb-0">You can add the same name again later. Items are not removed.</p>';
    };

    document.getElementById('deletePtBackBtn').onclick = () => {
        resetDeletePropertyTypeModalUi();
        document.getElementById('deletePtModalTitle').textContent = 'Remove property type — step 1 of 2';
    };

    document.getElementById('deletePtConfirmBtn').onclick = () => {
        if (!pendingPtDelete) return;
        const idx = pendingPtDelete.index;
        data.property_types.splice(idx, 1);
        pendingPtDelete = null;
        bootstrap.Modal.getInstance(document.getElementById('deletePropertyTypeModal'))?.hide();
        save().then(render);
    };

    document.getElementById('deletePropertyTypeModal').addEventListener('hidden.bs.modal', () => {
        pendingPtDelete = null;
        resetDeletePropertyTypeModalUi();
        document.getElementById('deletePtStep1').innerHTML = '';
        document.getElementById('deletePtStep2').innerHTML = '';
    });

    document.getElementById('saveItemBtn').onclick = () => {
        const idx = parseInt(document.getElementById('editItemIndex').value, 10);
        const pt = document.getElementById('itemPropertyType').value;
        const newId = idx >= 0 && data.items[idx]?.id ? data.items[idx].id : 'item-' + Date.now();
        const prev = idx >= 0 ? data.items[idx] : null;
        const item = {
            id: newId,
            sr_no: idx >= 0 ? data.items[idx].sr_no : (data.items.length + 1),
            property_types_sr_no: idx >= 0 ? data.items[idx].property_types_sr_no : nextTypeSr(pt, null),
            title: document.getElementById('itemTitle').value.trim(),
            property_type: pt,
            property_sub_type: document.getElementById('itemPropertySubType').value.trim(),
            thumbnail: document.getElementById('itemThumbnail').value.trim(),
            tour_live_link: document.getElementById('itemTourLink').value.trim() || '#',
            date: document.getElementById('itemDate').value || new Date().toISOString().slice(0, 10)
        };
        if (!item.title) { toast('Title is required', 'danger'); return; }
        if (idx >= 0) {
            const oldType = prev.property_type || '';
            const newType = item.property_type || '';
            if (oldType !== newType) {
                item.property_types_sr_no = nextTypeSr(newType, item.id);
                data.items[idx] = item;
                renumberPropertyTypeGroup(oldType);
            } else {
                data.items[idx] = item;
            }
        } else {
            data.items.push(item);
            renumberMainSrNo();
        }
        bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
        save().then(render);
    };

    setupItemsTableDragDrop();
    load();
})();
    </script>
</body>
</html>
