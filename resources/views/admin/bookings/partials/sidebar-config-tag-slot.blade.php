@php
    $tagPrefix = $tagPrefix ?? 'sidebar_tag';
    $tagLabel = $tagLabel ?? 'Sidebar tag 1';
    $tagData = $tagData ?? [];
    $tagFirstLang = $tagFirstLang ?? ($tourOrderedEnabledLanguages[0] ?? 'en');
    $tagTextValues = $tagData['text'] ?? [];
    if (! is_array($tagTextValues)) {
        $tagTextValues = $tagTextValues !== '' ? ['en' => $tagTextValues] : [];
    }
    $tagImages = \App\Support\SidebarConfigHelper::normalizeImagesForForm($tagData['images'] ?? [], $qr_code ?? null);
    $tagVideoFile = \App\Support\SidebarConfigHelper::normalizeMediaFileForForm($tagData['video'] ?? null, $qr_code ?? null);
    $tagDocumentFile = \App\Support\SidebarConfigHelper::normalizeMediaFileForForm($tagData['document'] ?? null, $qr_code ?? null);
    $tagVideoUrl = (string) ($tagData['videoUrl'] ?? '');
    $tagDocumentUrl = (string) ($tagData['documentUrl'] ?? '');
    $tagShowChecked = filter_var(old($tagPrefix . '_show', ($tagData['showTag'] ?? false) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $tagClickableChecked = filter_var(old($tagPrefix . '_clickable', ($tagData['clickable'] ?? false) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $tourNodes = $tourNodes ?? [];
    $menuItems = $tagData['buttonMenuItems'] ?? [];
    if (! is_array($menuItems)) {
        $menuItems = [];
    }
    $menuItemsJson = old($tagPrefix . '_menu_items_json', json_encode(array_values($menuItems)));
    $menuShowLiftChecked = filter_var(old($tagPrefix . '_menu_show_lift', ($tagData['buttonMenuShowLift'] ?? false) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $tagNavId = str_replace('_', '-', $tagPrefix) . '-lang';
    $tagPanePrefix = str_replace('_', '-', $tagPrefix) . '-title-lang';
    $tagGroupId = $tagPrefix . 'TitleLanguageTabs';
@endphp

<div class="sidebar-config-tag-slot border rounded p-3 mb-3" data-tag-prefix="{{ $tagPrefix }}">
    <div class="form-check form-switch form-switch-lg mb-3">
        <input type="hidden" name="{{ $tagPrefix }}_show" value="0">
        <input type="checkbox" class="form-check-input sidebar-tag-show-toggle"
            id="{{ $tagPrefix }}_show" name="{{ $tagPrefix }}_show" value="1"
            @checked($tagShowChecked)>
        <label class="form-check-label fw-semibold" for="{{ $tagPrefix }}_show">Show tag</label>
    </div>
    <p class="text-muted small mb-3">
        When off, the vertical tag is hidden. Falls back to tour default when a cluster does not set this.
    </p>

    <div class="sidebar-tag-fields {{ $tagShowChecked ? '' : 'd-none' }}">
        <x-admin.tour-language-tab-nav
            :group-id="$tagGroupId"
            :languages="$tourLanguageSlots"
            :enabled-languages="$tourOrderedEnabledLanguages"
            :language-display="$tourLanguageDisplay"
            :active-language="$tagFirstLang"
            :pane-id-prefix="$tagPanePrefix" />

        <div class="tab-content py-2 mb-3" data-tour-lang-tab-panes="{{ $tagGroupId }}">
            @foreach ($tourLanguageSlots as $lang)
                @php
                    $tagLangEnabled = in_array($lang, $tourOrderedEnabledLanguages, true);
                    $tagLangLabel = \App\Support\LanguageConfigHelper::languageLabel($lang, $tourLanguageDisplay);
                @endphp
                <div class="tab-pane m-0 fade {{ $lang === $tagFirstLang ? 'show active' : '' }} {{ $tagLangEnabled ? '' : 'd-none' }}"
                    id="{{ $tagPanePrefix }}-{{ $lang }}-pane"
                    data-language="{{ $lang }}"
                    role="tabpanel">
                    <label class="form-label" for="{{ $tagPrefix }}_text_{{ $lang }}">
                        Tag title ({{ $tagLangLabel }}) <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control sidebar-tag-title-input"
                        name="{{ $tagPrefix }}_text[{{ $lang }}]"
                        id="{{ $tagPrefix }}_text_{{ $lang }}"
                        maxlength="120"
                        value="{{ old($tagPrefix . '_text.' . $lang, data_get($tagTextValues, $lang, '')) }}"
                        placeholder="Tag title ({{ $tagLangLabel }})">
                </div>
            @endforeach
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label" for="{{ $tagPrefix }}_bg_color">Tag background</label>
                <div class="input-group">
                    <span class="input-group-text p-1">
                        <input type="color" class="form-control form-control-color sidebar-tag-color-picker"
                            data-target="{{ $tagPrefix }}_bg_color"
                            value="{{ old($tagPrefix . '_bg_color', $tagData['backgroundColor'] ?? '#000040') }}">
                    </span>
                    <input type="text" name="{{ $tagPrefix }}_bg_color" id="{{ $tagPrefix }}_bg_color"
                        class="form-control sidebar-tag-color-text"
                        value="{{ old($tagPrefix . '_bg_color', $tagData['backgroundColor'] ?? '#000040') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="{{ $tagPrefix }}_text_color">Tag text color</label>
                <div class="input-group">
                    <span class="input-group-text p-1">
                        <input type="color" class="form-control form-control-color sidebar-tag-color-picker"
                            data-target="{{ $tagPrefix }}_text_color"
                            value="{{ old($tagPrefix . '_text_color', $tagData['textColor'] ?? '#ffffff') }}">
                    </span>
                    <input type="text" name="{{ $tagPrefix }}_text_color" id="{{ $tagPrefix }}_text_color"
                        class="form-control sidebar-tag-color-text"
                        value="{{ old($tagPrefix . '_text_color', $tagData['textColor'] ?? '#ffffff') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="{{ $tagPrefix }}_size">Tag size</label>
                <select name="{{ $tagPrefix }}_size" id="{{ $tagPrefix }}_size" class="form-select">
                    @foreach (['small' => 'Small (default)', 'medium' => 'Medium', 'large' => 'Large'] as $sizeVal => $sizeLabel)
                        <option value="{{ $sizeVal }}" @selected(old($tagPrefix . '_size', $tagData['tagSize'] ?? 'small') === $sizeVal)>
                            {{ $sizeLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-check form-switch form-switch-lg mb-3">
            <input type="hidden" name="{{ $tagPrefix }}_clickable" value="0">
            <input type="checkbox" class="form-check-input sidebar-tag-clickable-toggle"
                id="{{ $tagPrefix }}_clickable" name="{{ $tagPrefix }}_clickable" value="1"
                @checked($tagClickableChecked)>
            <label class="form-check-label fw-semibold" for="{{ $tagPrefix }}_clickable">Clickable tag</label>
        </div>
        <p class="text-muted small mb-3">
            When on, clicking the tag runs the action below (same behavior as the tour bookmark ribbon in HTML).
        </p>

        @php
            $currentAction = old($tagPrefix . '_action', $tagData['action'] ?? 'redirectToLink');
            $clickableOn = $tagClickableChecked;
        @endphp

        <div class="sidebar-tag-action-wrap {{ $clickableOn ? '' : 'd-none' }}">
            <label class="form-label fw-semibold">Click action <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-3 mb-2">
                @foreach ([
                    'redirectToLink' => 'Open Link',
                    'openInfoModal' => 'Open Info modal',
                    'navigateToNode' => 'Navigate to Node',
                    'openImage' => 'Open image',
                    'openVideo' => 'Open video',
                    'openDocument' => 'Open document',
                ] as $actionVal => $actionLabel)
                    <div class="form-check">
                        <input class="form-check-input sidebar-tag-action-radio" type="radio"
                            name="{{ $tagPrefix }}_action" id="{{ $tagPrefix }}_action_{{ $actionVal }}"
                            value="{{ $actionVal }}" @checked($currentAction === $actionVal)>
                        <label class="form-check-label" for="{{ $tagPrefix }}_action_{{ $actionVal }}">
                            {{ $actionLabel }}
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input sidebar-tag-action-radio" type="radio"
                        name="{{ $tagPrefix }}_action" id="{{ $tagPrefix }}_action_openMenu"
                        value="openMenu" @checked($currentAction === 'openMenu')>
                    <label class="form-check-label" for="{{ $tagPrefix }}_action_openMenu">Open menu</label>
                </div>
            </div>

            {{-- Open Link --}}
            <div class="sidebar-tag-action-section border rounded p-3 mb-3 {{ $currentAction === 'redirectToLink' ? '' : 'd-none' }}"
                data-action="redirectToLink">
                <label class="form-label fw-semibold" for="{{ $tagPrefix }}_open_link_url">Link URL <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="{{ $tagPrefix }}_open_link_url"
                    id="{{ $tagPrefix }}_open_link_url"
                    value="{{ old($tagPrefix . '_open_link_url', $tagData['openLinkUrl'] ?? '') }}"
                    placeholder="example.com, mailto:..., tel:, or #">
                <p class="text-muted small mt-2 mb-0">
                    Use https or http, a host without the scheme (example.com), mailto:, tel:, or # for same-page hash links.
                </p>
            </div>

            {{-- Navigate to Node --}}
            <div class="sidebar-tag-action-section border rounded p-3 mb-3 {{ $currentAction === 'navigateToNode' ? '' : 'd-none' }}"
                data-action="navigateToNode">
                @include('admin.bookings.partials.sidebar-config-navigate-node-fields', [
                    'tagPrefix' => $tagPrefix,
                    'tagData' => $tagData,
                    'tourNodes' => $tourNodes,
                ])
            </div>

            {{-- Open menu --}}
            <div class="sidebar-tag-action-section border rounded p-3 mb-3 {{ $currentAction === 'openMenu' ? '' : 'd-none' }}"
                data-action="openMenu">
                <input type="hidden" name="{{ $tagPrefix }}_menu_items_json"
                    id="{{ $tagPrefix }}_menu_items_json" value="{{ $menuItemsJson }}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="form-label fw-semibold mb-0">Menu items <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-sm btn-outline-primary sidebar-tag-menu-add-btn">
                        <i class="ri-add-line me-1"></i> Add menu item
                    </button>
                </div>
                <div class="sidebar-tag-menu-items-list mb-3" id="{{ $tagPrefix }}_menu_items_list">
                    @php $menuRows = json_decode($menuItemsJson, true) ?: []; @endphp
                    @forelse ($menuRows as $menuIndex => $menuRow)
                        <div class="row g-2 align-items-end border rounded p-2 mb-2 sidebar-tag-menu-item-row"
                            data-index="{{ $menuIndex }}"
                            data-item-id="{{ $menuRow['id'] ?? ('bm-' . $menuIndex) }}">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Title</label>
                                <input type="text" class="form-control form-control-sm sidebar-tag-menu-title"
                                    value="{{ $menuRow['title'] ?? '' }}" placeholder="Menu label">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1">Scene</label>
                                <select class="form-select form-select-sm sidebar-tag-menu-node">
                                    <option value="">Select scene</option>
                                    @foreach ($tourNodes as $node)
                                        <option value="{{ $node['id'] }}" @selected(($menuRow['nodeId'] ?? '') == $node['id'])>
                                            {{ $node['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger sidebar-tag-menu-remove-btn" title="Remove">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small sidebar-tag-menu-empty mb-0">No menu items yet. Click "Add menu item".</p>
                    @endforelse
                </div>
                <div class="form-check form-switch">
                    <input type="hidden" name="{{ $tagPrefix }}_menu_show_lift" value="0">
                    <input type="checkbox" class="form-check-input" id="{{ $tagPrefix }}_menu_show_lift"
                        name="{{ $tagPrefix }}_menu_show_lift" value="1" @checked($menuShowLiftChecked)>
                    <label class="form-check-label" for="{{ $tagPrefix }}_menu_show_lift">Show lift layout (2+ items)</label>
                </div>
            </div>

            {{-- Open Video --}}
            <div class="sidebar-tag-action-section border rounded p-3 mb-3 {{ $currentAction === 'openVideo' ? '' : 'd-none' }}"
                data-action="openVideo">
                @include('admin.bookings.partials.sidebar-config-media-action-fields', [
                    'mediaType' => 'video',
                    'tagPrefix' => $tagPrefix,
                    'mediaFile' => $tagVideoFile,
                    'mediaUrl' => $tagVideoUrl,
                ])
            </div>

            {{-- Open Document --}}
            <div class="sidebar-tag-action-section border rounded p-3 mb-3 {{ $currentAction === 'openDocument' ? '' : 'd-none' }}"
                data-action="openDocument">
                @include('admin.bookings.partials.sidebar-config-media-action-fields', [
                    'mediaType' => 'document',
                    'tagPrefix' => $tagPrefix,
                    'mediaFile' => $tagDocumentFile,
                    'mediaUrl' => $tagDocumentUrl,
                ])
            </div>

            {{-- Open Image --}}
            <div class="sidebar-tag-action-section border rounded p-3 mb-3 {{ $currentAction === 'openImage' ? '' : 'd-none' }}"
                data-action="openImage">
                <input type="hidden" name="{{ $tagPrefix }}_existing_images_json"
                    id="{{ $tagPrefix }}_existing_images_json"
                    value="{{ old($tagPrefix . '_existing_images_json', json_encode($tagImages)) }}">
                <label class="form-label fw-semibold">Images <span class="text-danger">*</span></label>
                <label class="btn btn-outline-secondary w-100 mb-2">
                    <i class="ri-image-add-line me-1"></i> Choose Image
                    <input type="file" class="d-none sidebar-tag-image-input"
                        name="{{ $tagPrefix }}_image_files[]" multiple accept="image/*">
                </label>
                <p class="text-muted small">Add multiple images; on click a slider will be shown (same as image info point).</p>
                <div class="d-flex flex-wrap gap-2 sidebar-tag-images-preview" id="{{ $tagPrefix }}_images_preview">
                    @foreach ($tagImages as $img)
                        <div class="position-relative border rounded p-1 sidebar-tag-image-item" style="width:120px;"
                            data-url="{{ $img['url'] }}" data-file-name="{{ $img['fileName'] }}">
                            <img src="{{ $img['preview'] }}" alt="" class="img-fluid rounded" style="height:80px;object-fit:cover;width:100%;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 sidebar-tag-image-remove"
                                title="Remove">&times;</button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Open Info modal --}}
            <div class="sidebar-tag-action-section border rounded p-3 mb-3 {{ $currentAction === 'openInfoModal' ? '' : 'd-none' }}"
                data-action="openInfoModal">
                @include('admin.bookings.partials.sidebar-config-info-modal-fields', [
                    'tagPrefix' => $tagPrefix,
                    'tagData' => $tagData,
                    'tagFirstLang' => $tagFirstLang,
                ])
            </div>
        </div>
    </div>
</div>
