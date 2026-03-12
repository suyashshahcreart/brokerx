<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Manager | PROP PIK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Portfolio Manager</span>
            <a href="index.php" class="btn btn-light btn-sm">View Portfolio</a>
        </div>
    </nav>

    <div class="container py-4">
        <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

        <!-- Export & Settings -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Data & Settings</h5>
                <button type="button" class="btn btn-success" id="exportBtn">
                    Export from Database
                </button>
            </div>
            <div class="card-body">
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Portfolio Items</h5>
                <button type="button" class="btn btn-primary" id="addItemBtn">Add Item</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 60px;">SR No</th>
                                <th>Title</th>
                                <th>Property Type</th>
                                <th style="width: 80px;">Thumbnail</th>
                                <th>Date</th>
                                <th style="width: 180px;">Actions</th>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
(function() {
    let data = { settings: {}, property_types: [], items: [] };

    function toast(msg, type = 'success') {
        const c = document.getElementById('toastContainer');
        const el = document.createElement('div');
        el.className = `alert alert-${type} alert-dismissible fade show`;
        el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        c.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    async function load() {
        const r = await fetch('manage-api.php');
        const j = await r.json();
        if (j.success && j.data) data = j.data;
        if (!data.settings) data.settings = { default_per_page: 6, default_sort: 'sr_no', default_sort_order: 'desc' };
        if (!data.property_types) data.property_types = [];
        if (!data.items) data.items = [];
        render();
    }

    async function save() {
        const r = await fetch('manage-api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'save', data: data })
        });
        const j = await r.json();
        if (j.success) toast('Saved successfully');
        else toast(j.message || 'Save failed', 'danger');
    }

    async function doExport() {
        const btn = document.getElementById('exportBtn');
        btn.disabled = true;
        btn.textContent = 'Exporting...';
        try {
            const r = await fetch('manage-api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'export' })
            });
            const j = await r.json();
            if (j.success) {
                toast(j.message);
                await load();
            } else toast(j.message || 'Export failed', 'danger');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Export from Database';
        }
    }

    function render() {
        const s = data.settings;
        document.getElementById('defaultPerPage').value = s.default_per_page ?? 6;
        document.getElementById('defaultSort').value = s.default_sort ?? 'sr_no';
        document.getElementById('defaultSortOrder').value = s.default_sort_order ?? 'desc';

        const ptList = document.getElementById('propertyTypesList');
        ptList.innerHTML = data.property_types.map((t, i) => {
            const name = typeof t === 'string' ? t : t.name || t;
            return `<span class="badge bg-secondary d-inline-flex align-items-center gap-1">${escapeHtml(name)} <button type="button" class="btn-close btn-close-white btn-close-sm" data-i="${i}" aria-label="Remove"></button></span>`;
        }).join('');
        ptList.querySelectorAll('.btn-close').forEach(b => {
            b.onclick = () => {
                data.property_types.splice(parseInt(b.dataset.i), 1);
                save().then(render);
            };
        });

        const tbody = document.getElementById('itemsTableBody');
        tbody.innerHTML = data.items.map((item, i) => {
            const thumb = item.thumbnail || 'assets/images/mockup.png';
            return `<tr>
                <td>${item.sr_no ?? (i + 1)}</td>
                <td>${escapeHtml(item.title || '')}</td>
                <td>${escapeHtml(item.property_type || '')}</td>
                <td><img src="${escapeHtml(thumb)}" alt="" class="img-thumbnail" style="width:60px;height:40px;object-fit:cover" onerror="this.src='assets/images/mockup.png'"></td>
                <td>${escapeHtml(item.date || '')}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-i="${i}">Edit</button>
                    <button class="btn btn-sm btn-outline-secondary me-1" data-action="up" data-i="${i}">↑</button>
                    <button class="btn btn-sm btn-outline-secondary me-1" data-action="down" data-i="${i}">↓</button>
                    <button class="btn btn-sm btn-outline-danger" data-action="del" data-i="${i}">Delete</button>
                </td>
            </tr>`;
        }).join('');

        tbody.querySelectorAll('[data-action]').forEach(b => {
            b.onclick = () => {
                const i = parseInt(b.dataset.i);
                if (b.dataset.action === 'edit') openEditModal(i);
                else if (b.dataset.action === 'del') { data.items.splice(i, 1); renumberSrNo(); save().then(render); }
                else if (b.dataset.action === 'up' && i > 0) { [data.items[i-1], data.items[i]] = [data.items[i], data.items[i-1]]; renumberSrNo(); save().then(render); }
                else if (b.dataset.action === 'down' && i < data.items.length - 1) { [data.items[i], data.items[i+1]] = [data.items[i+1], data.items[i]]; renumberSrNo(); save().then(render); }
            };
        });
    }

    function renumberSrNo() {
        data.items.forEach((item, i) => item.sr_no = i + 1);
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
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

    document.getElementById('exportBtn').onclick = doExport;

    document.getElementById('saveSettingsBtn').onclick = () => {
        data.settings = {
            default_per_page: parseInt(document.getElementById('defaultPerPage').value) || 6,
            default_sort: document.getElementById('defaultSort').value,
            default_sort_order: document.getElementById('defaultSortOrder').value
        };
        save().then(render);
    };

    document.getElementById('addPropertyTypeBtn').onclick = () => {
        const v = document.getElementById('newPropertyType').value.trim();
        if (v && !data.property_types.includes(v)) {
            data.property_types.push(v);
            data.property_types.sort();
            document.getElementById('newPropertyType').value = '';
            save().then(render);
        }
    };

    document.getElementById('addItemBtn').onclick = () => openEditModal(-1);

    document.getElementById('saveItemBtn').onclick = () => {
        const idx = parseInt(document.getElementById('editItemIndex').value);
        const item = {
            id: idx >= 0 && data.items[idx]?.id ? data.items[idx].id : 'item-' + Date.now(),
            sr_no: idx >= 0 ? data.items[idx].sr_no : (data.items.length + 1),
            title: document.getElementById('itemTitle').value.trim(),
            property_type: document.getElementById('itemPropertyType').value,
            property_sub_type: document.getElementById('itemPropertySubType').value.trim(),
            thumbnail: document.getElementById('itemThumbnail').value.trim(),
            tour_live_link: document.getElementById('itemTourLink').value.trim() || '#',
            date: document.getElementById('itemDate').value || new Date().toISOString().slice(0, 10)
        };
        if (!item.title) { toast('Title is required', 'danger'); return; }
        if (idx >= 0) data.items[idx] = item;
        else { data.items.push(item); renumberSrNo(); }
        bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
        save().then(render);
    };

    load();
})();
    </script>
</body>
</html>
