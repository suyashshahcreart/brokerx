@php
    $submittedSidebarPayload = old('sidebar_node_payload');
    $submittedSidebarPayload = is_string($submittedSidebarPayload) ? json_decode($submittedSidebarPayload, true) : [];
    $submittedSidebarPayload = is_array($submittedSidebarPayload) ? $submittedSidebarPayload : [];

    $storedNodesFromFinalJson = data_get($tour->final_json, 'nodes', []);
    $sidebarCategoriesValue = data_get($tour->final_json, 'sidebarCategories', []);
    $sidebarNodeValue = $submittedSidebarPayload['nodes'] ?? old('sidebar_node', !empty($storedNodesFromFinalJson) ? $storedNodesFromFinalJson : ($tour->sidebar_node ?? []));
    $sidebarCategoriesValue = $submittedSidebarPayload['sidebarCategories'] ?? $sidebarCategoriesValue;
    $sidebarLinksValue = old('sidebar_links', $tour->sidebar_links ?? data_get($tour->final_json, 'sidebarLinks', []));

    if (is_string($sidebarNodeValue)) {
        $decodedSidebarNodes = json_decode($sidebarNodeValue, true);
        $sidebarNodeValue = is_array($decodedSidebarNodes) ? $decodedSidebarNodes : [];
    }

    if (is_string($sidebarCategoriesValue)) {
        $decodedSidebarCategories = json_decode($sidebarCategoriesValue, true);
        $sidebarCategoriesValue = is_array($decodedSidebarCategories) ? $decodedSidebarCategories : [];
    }

    if (!is_array($sidebarNodeValue)) {
        $sidebarNodeValue = [];
    }

    if (!is_array($sidebarCategoriesValue)) {
        $sidebarCategoriesValue = [];
    }

    $sidebarNodeCount = collect($sidebarNodeValue)
        ->filter(function ($node) {
            return is_array($node)
                && array_key_exists('showInSideMenu', $node)
                && in_array($node['showInSideMenu'], [true, 1, '1', 'true'], true);
        })
        ->count();

    $sidebarExtraLinkCount = is_array($sidebarLinksValue) ? count($sidebarLinksValue) : 0;

    $sidebarNodesSource = collect($sidebarNodeValue)
        ->filter(function ($node) {
            return is_array($node);
        })
        ->values()
        ->toArray();

    $sidebarCategoriesSource = collect($sidebarCategoriesValue)
        ->filter(function ($category) {
            return is_array($category) && data_get($category, 'id');
        })
        ->sortBy(function ($category) {
            return (int) data_get($category, 'sideMenuOrder', data_get($category, 'order', 0));
        })
        ->values()
        ->toArray();
@endphp

<style>
    .sidebar-menu-board {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
    }

    .sidebar-menu-toolbar {
        background: #fff;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    }

    .sidebar-menu-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.9rem;
        background: #fff;
        overflow: hidden;
    }

    .sidebar-menu-card+.sidebar-menu-card {
        margin-top: 0.75rem;
    }

    .sidebar-menu-category-header {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }

    .sidebar-menu-category-header:hover {
        background: linear-gradient(180deg, #f0f4f8 0%, #e8ecf5 100%);
    }

    .sidebar-menu-category-header.collapsed {
        border-bottom: 0;
    }

    .sidebar-menu-card .collapse {
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar-menu-card .collapse:not(.show) {
        max-height: 0;
        opacity: 0;
    }

    .sidebar-menu-card .collapse.show {
        max-height: 10000px;
        opacity: 1;
    }

    .sidebar-menu-row {
        min-height: 68px;
        gap: 1rem;
    }

    .sidebar-menu-row .sidebar-menu-row-main {
        min-width: 0;
    }

    .sidebar-menu-row .sidebar-menu-row-title {
        font-weight: 600;
        line-height: 1.2;
    }

    .sidebar-menu-row .sidebar-menu-row-subtitle {
        font-size: 0.82rem;
        color: #64748b;
        word-break: break-word;
    }

    .sidebar-menu-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        border: 1px solid rgba(15, 23, 42, 0.1);
        background: #fff;
        font-size: 0.75rem;
        color: #334155;
        white-space: nowrap;
    }

    .sidebar-menu-category-toggle {
        width: 2.2rem;
        height: 2.2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.6rem;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar-menu-category-toggle:hover {
        background: rgba(15, 23, 42, 0.06);
    }

    .sidebar-menu-category-toggle i {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar-menu-category-header .sidebar-menu-category-toggle[aria-expanded="false"] i {
        transform: rotate(-180deg);
    }

    .sidebar-menu-category-header .sidebar-menu-category-toggle[aria-expanded="true"] i {
        transform: rotate(0deg);
    }

    .sidebar-menu-empty-state {
        border: 1px dashed rgba(15, 23, 42, 0.18);
        border-radius: 0.85rem;
        background: rgba(248, 250, 252, 0.9);
    }

    .sidebar-menu-order-input {
        width: 7.8rem;
    }

    .sidebar-menu-action-group .btn {
        white-space: nowrap;
    }

    .sidebar-menu-icon-wrap {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        color: #334155;
        flex: 0 0 auto;
    }

    .sidebar-menu-icon-wrap i,
    .sidebar-menu-icon-wrap .material-icons-outlined {
        font-size: 1rem;
        line-height: 1;
    }
</style>

<div class="row g-3">
    <div class="col-12">
        <div class="sidebar-menu-board h-100">
            <div
                class="card-header sidebar-menu-toolbar d-flex align-items-center justify-content-between flex-wrap gap-3 px-3 py-3">
                <div>
                    <h5 class="card-title mb-0">Side Menu Items (<span
                            id="sidebarNodeCount">{{ $sidebarNodeCount }}</span>)</h5>
                    <small class="text-muted d-block">{{ $sidebarNodeCount }} items in one ordered menu</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addSidebarLinkButton">
                        <i class="ri-link me-1"></i>Add Link
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addSidebarCategoryButton">
                        <i class="ri-add-line me-1"></i>Add Category
                    </button>
                </div>
            </div>
            <div class="card-body p-3 p-lg-4">
                <div class="sidebar-menu-card overflow-hidden">
                    <div class="px-3 py-2 border-bottom bg-white">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="search" class="form-control" id="sidebarNodeSearch"
                                placeholder="Search items...">
                        </div>
                    </div>

                    <div class="px-3 py-2 border-bottom small text-muted d-flex align-items-center gap-3 flex-wrap">
                        <span><i class="ri-drag-move-2-line me-1"></i> Drag categories and items</span>
                        <span><i class="ri-search-line me-1"></i> Search updates the visible tree only</span>
                    </div>

                    <div id="sidebarNodes" class="p-3 sidebar-menu-dropzone">
                        <div class="sidebar-menu-empty-state text-muted px-3 py-3" id="sidebarNodesEmpty">Loading
                            sidebar nodes...</div>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">Drag categories or move items between them. Saving persists the
                    full node list into final_json.nodes and sidebarCategories.</small>

                <!-- Form for saving node changes -->
                <form action="{{ route('admin.tours.updateSidebarNodes', $tour) }}" method="POST" id="sidebarNodesForm"
                    class="needs-validation mt-3" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="sidebar_node_payload" id="sidebar_node_payload">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary">Save Menu Order</button>
                    </div>
                </form>

                @error('sidebar_node_payload')<div class="text-danger">{{ $message }}</div>@enderror
                @error('sidebar_node')<div class="text-danger">{{ $message }}</div>@enderror

                <div class="modal fade" id="sidebarNodeTitleModal" tabindex="-1"
                    aria-labelledby="sidebarNodeTitleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title" id="">Edit sidebar nodes</h5>
                                    <p class="text-md d-block" id="sidebarNodeTitleModalNodeName"></p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="sidebarNodeVisibleToggle">
                                            <label class="form-check-label fw-semibold"
                                                for="sidebarNodeVisibleToggle">Show in side menu</label>
                                        </div>

                                        <div id="sidebarNodeTitleFields" class="border rounded-3 p-3 bg-white"></div>

                                        <div class="mb-3 mt-3">
                                            <label for="sidebarNodeIconInput" class="form-label">Icon</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control icon-input"
                                                    id="sidebarNodeIconInput" placeholder="Click to select" readonly>
                                                <button type="button" class="btn btn-outline-secondary"
                                                    id="selectSidebarNodeIconButton">
                                                    <i class="ri-search-line me-1"></i>Select
                                                </button>
                                                <button type="button" class="btn btn-outline-danger"
                                                    id="removeSidebarNodeIconButton">
                                                    <i class="ri-close-line me-1"></i>Remove
                                                </button>
                                            </div>
                                            <div class="icon-preview mt-2" id="sidebarNodeIconPreview"></div>
                                        </div>

                                        <div class="mt-3">
                                            <label for="sidebarNodeCategoryInput" class="form-label">Category</label>
                                            <select class="form-select" id="sidebarNodeCategoryInput"></select>
                                            <small class="text-muted d-block mt-1" id="sidebarNodeCategoryPath"></small>
                                            <small class="text-muted d-block">Choose a sidebar category for this
                                                node.</small>
                                        </div>

                                        <div class="mt-3">
                                            <label for="sidebarNodeOrderInput" class="form-label">Side Menu
                                                Order</label>
                                            <input type="number" class="form-control" id="sidebarNodeOrderInput"
                                                min="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveSidebarNodeTitleButton">Save
                                    Title</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="sidebarLinkModal" tabindex="-1" aria-labelledby="sidebarLinkModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="sidebarLinkModalLabel">Edit sidebar link</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="sidebarLinkIndexInput">
                                <div id="sidebarLinkTitleFields" class="border rounded-3 p-3 bg-white mb-3"></div>
                                <div class="mb-3">
                                    <label for="sidebarLinkIconInput" class="form-label">Icon</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control icon-input" id="sidebarLinkIconInput"
                                            placeholder="Click to select" readonly>
                                        <button type="button" class="btn btn-outline-secondary"
                                            id="selectSidebarLinkIconButton">
                                            <i class="ri-search-line me-1"></i>Select
                                        </button>
                                        <button type="button" class="btn btn-outline-danger"
                                            id="removeSidebarLinkIconButton">
                                            <i class="ri-close-line me-1"></i>Remove
                                        </button>
                                    </div>
                                    <div class="icon-preview mt-2" id="sidebarLinkIconPreview"></div>
                                </div>
                                <div class="mb-3">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label for="sidebarLinkTypeInput" class="form-label">Type</label>
                                            <select id="sidebarLinkTypeInput" class="form-select">
                                                <option value="link">Link</option>
                                                <option value="content">Content</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="sidebarLinkOrderInput" class="form-label">Order</label>
                                            <input type="number" id="sidebarLinkOrderInput" class="form-control"
                                                min="1">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label for="sidebarLinkUrlInput" class="form-label">Link URL</label>
                                        <input type="url" id="sidebarLinkUrlInput" class="form-control"
                                            placeholder="https://example.com">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="sidebarLinkActionInput" class="form-label">Action</label>
                                    <select id="sidebarLinkActionInput" class="form-select">
                                        <option value="link">Open Link</option>
                                        <option value="modal">Open In Modal</option>
                                        <option value="content">Show Content</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="sidebarLinkContentWrapper" style="display: none;">
                                    <label class="form-label">Content</label>
                                    <textarea id="sidebarLinkContentInput" class="form-control" rows="8"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="sidebarLinkImageInput" class="form-label">Image (URL)</label>
                                    <div class="input-group">
                                        <input type="url" id="sidebarLinkImageInput" class="form-control"
                                            placeholder="https://example.com/image.jpg">
                                        <button type="button" class="btn btn-outline-secondary"
                                            id="sidebarLinkImagePreviewButton">Preview</button>
                                    </div>
                                    <div class="mt-2" id="sidebarLinkImagePreview"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveSidebarLinkButton">Save
                                    Link</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="sidebarCategoryModal" tabindex="-1"
                    aria-labelledby="sidebarCategoryModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="sidebarCategoryModalLabel">Add Sidebar Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="sidebarCategoryIdInput">
                                <div class="mb-3">
                                    <label for="sidebarCategoryIconInput" class="form-label">Icon</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control icon-input" id="sidebarCategoryIconInput"
                                            placeholder="Click to select" readonly>
                                        <button type="button" class="btn btn-outline-secondary"
                                            id="selectSidebarCategoryIconButton">
                                            <i class="ri-search-line me-1"></i>Select
                                        </button>
                                        <button type="button" class="btn btn-outline-danger"
                                            id="removeSidebarCategoryIconButton">
                                            <i class="ri-close-line me-1"></i>Remove
                                        </button>
                                    </div>
                                    <div class="icon-preview mt-2" id="sidebarCategoryIconPreview"></div>
                                </div>
                                <div id="sidebarCategoryNameFields"></div>
                                <div class="mb-3">
                                    <label for="sidebarCategoryParentIdInput" class="form-label">Parent Category</label>
                                    <select class="form-select" id="sidebarCategoryParentIdInput">
                                        <option value="">No parent (top level)</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">Select a parent to create a
                                        subcategory.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveSidebarCategoryButton">Save
                                    category</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    window.sidebarNodesData = {!! json_encode($sidebarNodesSource, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};
    window.sidebarCategoriesData = {!! json_encode($sidebarCategoriesSource, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};
    window.sidebarLinksData = {!! json_encode($sidebarLinksValue ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};
    window.enabledLanguages = {!! json_encode($tour->enable_language ?? ['en'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};
    window.defaultLanguage = {!! json_encode($tour->default_language ?? 'en', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!};
</script>