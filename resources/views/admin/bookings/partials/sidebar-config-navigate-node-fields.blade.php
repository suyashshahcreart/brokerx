@php
    $navPrefix = $tagPrefix ?? 'sidebar_tag';
    $selectedNodeId = old($navPrefix . '_button_node_id', $tagData['buttonNodeId'] ?? '');
    $buttonNodeView = $tagData['buttonNodeView'] ?? null;
    if (! is_array($buttonNodeView)) {
        $buttonNodeView = null;
    }
    $buttonNodeViewJson = old(
        $navPrefix . '_button_node_view_json',
        json_encode($buttonNodeView)
    );
    $hasTargetView = \App\Support\SidebarConfigHelper::hasValidNavigateNodeView(
        is_string($buttonNodeViewJson) ? json_decode($buttonNodeViewJson, true) : $buttonNodeView
    );
@endphp

<div class="sidebar-tag-navigate-node-fields" data-nav-prefix="{{ $navPrefix }}">
    <input type="hidden"
        name="{{ $navPrefix }}_button_node_view_json"
        id="{{ $navPrefix }}_button_node_view_json"
        class="sidebar-tag-node-view-json"
        value="{{ is_string($buttonNodeViewJson) ? $buttonNodeViewJson : json_encode($buttonNodeView) }}">

    <div class="row">
        <div class="col-md-6">
            <label class="form-label fw-semibold sidebar-tag-node-select-label {{ $selectedNodeId === '' ? 'text-danger' : '' }}"
                for="{{ $navPrefix }}_button_node_id">
                Link to Scene <span class="text-danger">*</span>
            </label>
            <select class="form-select sidebar-tag-node-select" name="{{ $navPrefix }}_button_node_id"
                id="{{ $navPrefix }}_button_node_id" data-placeholder="Select a scene">
                <option value="">Select a scene</option>
                @foreach ($tourNodes as $node)
                    <option value="{{ $node['id'] }}" @selected($selectedNodeId == $node['id'])>
                        {{ $node['name'] }}
                    </option>
                @endforeach
            </select>
            <p class="text-muted small mt-2 mb-0">
                Select a scene from the tour to open when this tag is clicked.
            </p>
            @if (count($tourNodes) === 0)
                <p class="text-warning small mt-2 mb-0">
                    No tour scenes found. Upload a tour ZIP with virtual-tour-nodes first.
                </p>
            @endif
            <p class="text-danger small mt-2 mb-0 sidebar-tag-node-view-hint {{ ($selectedNodeId !== '' && ! $hasTargetView) ? '' : 'd-none' }}">
                Set the target view with Set View before saving.
            </p>
            <p class="text-success small mt-2 mb-0 sidebar-tag-node-view-set {{ $hasTargetView ? '' : 'd-none' }}">
                Target view has been set.
            </p>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold sidebar-tag-node-preview-label {{ ($selectedNodeId !== '' && ! $hasTargetView) ? 'text-danger' : '' }}">
                Target View <span class="text-danger">*</span>
            </label>
            <div class="sidebar-tag-node-preview-wrapper border rounded overflow-hidden bg-light position-relative {{ ($selectedNodeId !== '' && ! $hasTargetView) ? 'border-danger' : '' }}"
                style="height:320px;">
                <div class="sidebar-tag-node-preview-canvas h-100 w-100"></div>
                <button type="button"
                    class="btn btn-sm btn-dark sidebar-tag-node-set-view-btn position-absolute top-0 end-0 m-2"
                    style="z-index:1000;display:none;"
                    disabled>
                    <i class="ri-eye-line me-1"></i> Set View
                </button>
                <div class="sidebar-tag-node-preview-loading position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-light bg-opacity-75"
                    style="z-index:100;display:none;">
                    <span class="text-muted small">Loading panorama…</span>
                </div>
                <div class="sidebar-tag-node-preview-empty position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-muted small px-3 text-center"
                    style="z-index:50;">
                    Select a scene to preview the panorama and set the target view.
                </div>
                <div class="sidebar-tag-node-preview-unavailable position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-warning small px-3 text-center d-none"
                    style="z-index:50;">
                    Panorama preview unavailable for this scene.
                </div>
            </div>
        </div>
    </div>
</div>
