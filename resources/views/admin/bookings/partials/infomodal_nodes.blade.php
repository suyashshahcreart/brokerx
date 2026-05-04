<div class="card border-1 shadow-sm">
    <div class="card-header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="card-title mb-0">Info Modal Nodes</h4>
                <small class="text-muted">Nodes with side menu enabled and at least one info modal</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input type="search" id="infomodalNodesSearch" class="form-control form-control-sm"
                    placeholder="Search node / modal title..." style="min-width: 240px;">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="infomodalNodesRefreshBtn">
                    <i class="ri-refresh-line"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div id="infomodalNodesMeta" class="text-muted small mb-2"></div>
        <div id="infomodalNodesList"></div>
        <div id="infomodalNodesEmpty" class="text-muted d-none">
            No nodes found with info modals and <code>showInSideMenu = true</code>.
        </div>
    </div>
</div>
