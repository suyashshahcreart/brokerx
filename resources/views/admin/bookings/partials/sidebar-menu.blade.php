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

<div class="row g-3">
    <div class="col-12">
        <div class="card border-1 shadow-sm h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-0">Side Menu Items (<span id="sidebarNodeCount">{{ $sidebarNodeCount }}</span>)</h5>
                    <small class="text-muted d-block">{{ $sidebarNodeCount }} items in one ordered menu</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addSidebarLinkButton">
                        <i class="ri-link me-1"></i>Add Link
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addSidebarCategoryButton">
                        <i class="ri-add-line me-1"></i>Add Group
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="border rounded-3 overflow-hidden">
                    <div class="px-3 py-2 border-bottom bg-white">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="search" class="form-control" id="sidebarNodeSearch" placeholder="Search items...">
                        </div>
                    </div>

                    <div class="px-3 py-2 border-bottom small text-muted d-flex align-items-center gap-3 flex-wrap">
                        <span><i class="ri-drag-move-2-line me-1"></i> Drag categories and items</span>
                        <span><i class="ri-search-line me-1"></i> Search updates the visible tree only</span>
                    </div>

                    <div id="sidebarNodes" class="p-3">
                        <div class="list-group-item text-muted" id="sidebarNodesEmpty">Loading sidebar nodes...</div>
                    </div>
                </div>
                
                <div class="modal fade" id="sidebarLinkModal" tabindex="-1" aria-labelledby="sidebarLinkModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="sidebarLinkModalLabel">Edit sidebar link</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="sidebarLinkIndexInput">
                                <div id="sidebarLinkTitleFields" class="border rounded-3 p-3 bg-white mb-3"></div>
                                <div class="mb-3">
                                    <label for="sidebarLinkIconInput" class="form-label">Icon</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control icon-input" id="sidebarLinkIconInput" placeholder="Click to select" readonly>
                                        <button type="button" class="btn btn-outline-secondary" id="selectSidebarLinkIconButton">
                                            <i class="ri-search-line me-1"></i>Select
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" id="removeSidebarLinkIconButton">
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
                                            <input type="number" id="sidebarLinkOrderInput" class="form-control" min="1">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label for="sidebarLinkUrlInput" class="form-label">Link URL</label>
                                        <input type="url" id="sidebarLinkUrlInput" class="form-control" placeholder="https://example.com">
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
                                        <input type="url" id="sidebarLinkImageInput" class="form-control" placeholder="https://example.com/image.jpg">
                                        <button type="button" class="btn btn-outline-secondary" id="sidebarLinkImagePreviewButton">Preview</button>
                                    </div>
                                    <div class="mt-2" id="sidebarLinkImagePreview"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveSidebarLinkButton">Save Link</button>
                            </div>
                        </div>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">Drag categories or move items between them. Saving persists the full node list into final_json.nodes and sidebarCategories.</small>

                <!-- Form for saving node changes -->
                <form action="{{ route('admin.tours.updateSidebarNodes', $tour) }}" method="POST"
                    id="sidebarNodesForm" class="needs-validation mt-3" novalidate>
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
                                    <h5 class="modal-title" id="sidebarNodeTitleModalLabel">Edit title in sidebar</h5>
                                    <small class="text-muted d-block" id="sidebarNodeTitleModalNodeName"></small>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch" id="sidebarNodeVisibleToggle">
                                            <label class="form-check-label fw-semibold" for="sidebarNodeVisibleToggle">Show in side menu</label>
                                        </div>

                                        <div id="sidebarNodeTitleFields" class="border rounded-3 p-3 bg-white"></div>

                                        <!-- Menu icon removed per user request -->

                                        <div class="mt-3">
                                            <label for="sidebarNodeCategoryInput" class="form-label">Category</label>
                                            <select class="form-select" id="sidebarNodeCategoryInput"></select>
                                            <small class="text-muted d-block mt-1" id="sidebarNodeCategoryPath"></small>
                                            <small class="text-muted d-block">Choose a sidebar category for this node.</small>
                                        </div>

                                        <div class="mt-3">
                                            <label for="sidebarNodeOrderInput" class="form-label">Side Menu Order</label>
                                            <input type="number" class="form-control" id="sidebarNodeOrderInput" min="1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveSidebarNodeTitleButton">Save Title</button>
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
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="sidebarCategoryIdInput">
                                <div class="mb-3">
                                    <label for="sidebarCategoryIconInput" class="form-label">Icon</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control icon-input" id="sidebarCategoryIconInput"
                                            placeholder="Click to select" readonly>
                                        <button type="button" class="btn btn-outline-secondary" id="selectSidebarCategoryIconButton">
                                            <i class="ri-search-line me-1"></i>Select
                                        </button>
                                    </div>
                                    <div class="icon-preview mt-2" id="sidebarCategoryIconPreview"></div>
                                </div>
                                <div id="sidebarCategoryNameFields"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveSidebarCategoryButton">Save category</button>
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