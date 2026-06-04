@php
    $infoPrefix = $tagPrefix ?? 'sidebar_tag';
    $infoModalWidth = old($infoPrefix . '_info_modal_width', $tagData['infoModalWidth'] ?? '');
    $useCustomModal = filter_var(old($infoPrefix . '_info_modal_use_custom', ! empty($tagData['infoModalUseCustomModal']) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $iframeEnabled = filter_var(old($infoPrefix . '_info_modal_iframe_enabled', ! empty($tagData['infoModalIframeUrl']) ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $iframeUrl = old($infoPrefix . '_info_modal_iframe_url', $tagData['infoModalIframeUrl'] ?? '');

    $modalTitleValues = \App\Support\LanguageConfigHelper::decodePerLanguageStored($tagData['modalTitle'] ?? null);
    $modalDescValues = \App\Support\LanguageConfigHelper::decodePerLanguageStored($tagData['modalDescription'] ?? null);
    $footerBtnTitleValues = \App\Support\LanguageConfigHelper::decodePerLanguageStored($tagData['infoModalFooterButtonTitle'] ?? null);
    $footerTextValues = \App\Support\LanguageConfigHelper::decodePerLanguageStored($tagData['infoModalFooterText'] ?? null);
    $footerButtonLink = old($infoPrefix . '_info_modal_footer_button_link', $tagData['infoModalFooterButtonLink'] ?? '');

    $infoLangGroupId = $infoPrefix . 'InfoModalLangTabs';
    $infoLangPanePrefix = str_replace('_', '-', $infoPrefix) . '-info-modal-lang';
    $infoFirstLang = $tagFirstLang ?? ($tourOrderedEnabledLanguages[0] ?? 'en');
@endphp

<div class="sidebar-info-modal-fields action-type-open-info-modal" data-info-prefix="{{ $infoPrefix }}">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="{{ $infoPrefix }}_info_modal_width">Modal Size</label>
        <select name="{{ $infoPrefix }}_info_modal_width" id="{{ $infoPrefix }}_info_modal_width" class="form-select">
            <option value="" @selected($infoModalWidth === '')>Medium (Default)</option>
            <option value="modal-sm" @selected($infoModalWidth === 'modal-sm')>Small</option>
            <option value="modal-lg" @selected($infoModalWidth === 'modal-lg')>Large</option>
            <option value="modal-xl" @selected($infoModalWidth === 'modal-xl')>Extra Large</option>
        </select>
    </div>

    <div class="form-check mb-3">
        <input type="hidden" name="{{ $infoPrefix }}_info_modal_use_custom" value="0">
        <input type="checkbox" class="form-check-input sidebar-info-modal-use-custom"
            id="{{ $infoPrefix }}_info_modal_use_custom" name="{{ $infoPrefix }}_info_modal_use_custom" value="1"
            @checked($useCustomModal)>
        <label class="form-check-label" for="{{ $infoPrefix }}_info_modal_use_custom">
            Use custom modal (content only)
        </label>
        <p class="text-muted small mb-0 mt-1">
            When enabled, the HTML viewer shows only the content with a floating close button (no modal header/footer).
        </p>
    </div>

    <ul class="nav nav-tabs mb-0 sidebar-info-modal-section-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link active" data-section="title" role="tab">Modal Title</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link" data-section="content" role="tab">Modal Content</button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link" data-section="footer" role="tab">
                Modal Footer <span class="badge bg-secondary-subtle text-secondary ms-1 sidebar-info-modal-footer-optional">optional</span>
            </button>
        </li>
    </ul>

    <div class="border border-top-0 rounded-bottom p-3 bg-light-subtle">
        {{-- Title section (language tabs inside) --}}
        <div class="sidebar-info-modal-section-pane" data-section="title">
            <x-admin.tour-language-tab-nav
                :group-id="$infoLangGroupId . '-title'"
                :languages="$tourLanguageSlots"
                :enabled-languages="$tourOrderedEnabledLanguages"
                :language-display="$tourLanguageDisplay"
                :active-language="$infoFirstLang"
                :pane-id-prefix="$infoLangPanePrefix . '-title'" />

            <div class="tab-content py-2" data-tour-lang-tab-panes="{{ $infoLangGroupId }}-title">
                @foreach ($tourLanguageSlots as $lang)
                    @php
                        $infoLangEnabled = in_array($lang, $tourOrderedEnabledLanguages, true);
                        $infoLangLabel = \App\Support\LanguageConfigHelper::languageLabel($lang, $tourLanguageDisplay);
                    @endphp
                    <div class="tab-pane m-0 fade {{ $lang === $infoFirstLang ? 'show active' : '' }} {{ $infoLangEnabled ? '' : 'd-none' }}"
                        id="{{ $infoLangPanePrefix }}-title-{{ $lang }}-pane"
                        data-language="{{ $lang }}" role="tabpanel">
                        <div class="sidebar-info-modal-title-standard {{ $useCustomModal ? 'd-none' : '' }}">
                            <label class="form-label fw-semibold" for="{{ $infoPrefix }}_modal_title_{{ $lang }}">
                                Modal Title ({{ $infoLangLabel }}) <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control sidebar-info-modal-title-input"
                                name="{{ $infoPrefix }}_modal_title[{{ $lang }}]"
                                id="{{ $infoPrefix }}_modal_title_{{ $lang }}"
                                maxlength="60"
                                value="{{ old($infoPrefix . '_modal_title.' . $lang, data_get($modalTitleValues, $lang, '')) }}"
                                placeholder="Enter modal title">
                            <small class="text-muted sidebar-info-modal-char-count" data-max="60">0/60 characters</small>
                        </div>
                        <p class="text-muted small mb-0 sidebar-info-modal-title-custom {{ $useCustomModal ? '' : 'd-none' }}">
                            Custom content-only modal: the standard header title is hidden. Use the
                            <strong>Modal Content</strong> tab for HTML.
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Content section --}}
        <div class="sidebar-info-modal-section-pane d-none" data-section="content">
            <div class="sidebar-info-modal-content-standard mb-3 {{ $useCustomModal ? 'd-none' : '' }}">
                <div class="form-check">
                    <input type="hidden" name="{{ $infoPrefix }}_info_modal_iframe_enabled" value="0">
                    <input type="checkbox" class="form-check-input sidebar-info-modal-iframe-toggle"
                        id="{{ $infoPrefix }}_info_modal_iframe_enabled"
                        name="{{ $infoPrefix }}_info_modal_iframe_enabled" value="1"
                        @checked($iframeEnabled)>
                    <label class="form-check-label" for="{{ $infoPrefix }}_info_modal_iframe_enabled">
                        Use iframe instead of text editor
                    </label>
                </div>
            </div>

            <div class="sidebar-info-modal-iframe-wrap mb-3 {{ ($useCustomModal || ! $iframeEnabled) ? 'd-none' : '' }}">
                <label class="form-label fw-semibold" for="{{ $infoPrefix }}_info_modal_iframe_url">
                    Iframe URL <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" name="{{ $infoPrefix }}_info_modal_iframe_url"
                    id="{{ $infoPrefix }}_info_modal_iframe_url"
                    value="{{ $iframeUrl }}"
                    placeholder="example.com/embed/…">
            </div>

            <x-admin.tour-language-tab-nav
                :group-id="$infoLangGroupId . '-content'"
                :languages="$tourLanguageSlots"
                :enabled-languages="$tourOrderedEnabledLanguages"
                :language-display="$tourLanguageDisplay"
                :active-language="$infoFirstLang"
                :pane-id-prefix="$infoLangPanePrefix . '-content'" />

            <div class="tab-content py-2 sidebar-info-modal-editor-wrap {{ ($iframeEnabled && ! $useCustomModal) ? 'd-none' : '' }}"
                data-tour-lang-tab-panes="{{ $infoLangGroupId }}-content">
                @foreach ($tourLanguageSlots as $lang)
                    @php
                        $infoLangEnabled = in_array($lang, $tourOrderedEnabledLanguages, true);
                        $infoLangLabel = \App\Support\LanguageConfigHelper::languageLabel($lang, $tourLanguageDisplay);
                    @endphp
                    <div class="tab-pane m-0 fade {{ $lang === $infoFirstLang ? 'show active' : '' }} {{ $infoLangEnabled ? '' : 'd-none' }}"
                        id="{{ $infoLangPanePrefix }}-content-{{ $lang }}-pane"
                        data-language="{{ $lang }}" role="tabpanel">
                        <label class="form-label fw-semibold" for="{{ $infoPrefix }}_modal_description_{{ $lang }}">
                            Modal Description ({{ $infoLangLabel }}) <span class="text-danger">*</span>
                        </label>
                        <textarea name="{{ $infoPrefix }}_modal_description[{{ $lang }}]"
                            id="{{ $infoPrefix }}_modal_description_{{ $lang }}"
                            class="form-control editor sidebar-info-modal-editor" rows="6"
                            placeholder="Enter modal description">{{ old($infoPrefix . '_modal_description.' . $lang, data_get($modalDescValues, $lang, '')) }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Footer section --}}
        <div class="sidebar-info-modal-section-pane d-none" data-section="footer">
            <div class="sidebar-info-modal-footer-fields {{ $useCustomModal ? 'd-none' : '' }}">
                <x-admin.tour-language-tab-nav
                    :group-id="$infoLangGroupId . '-footer'"
                    :languages="$tourLanguageSlots"
                    :enabled-languages="$tourOrderedEnabledLanguages"
                    :language-display="$tourLanguageDisplay"
                    :active-language="$infoFirstLang"
                    :pane-id-prefix="$infoLangPanePrefix . '-footer'" />

                <div class="tab-content py-2" data-tour-lang-tab-panes="{{ $infoLangGroupId }}-footer">
                    @foreach ($tourLanguageSlots as $lang)
                        @php
                            $infoLangEnabled = in_array($lang, $tourOrderedEnabledLanguages, true);
                            $infoLangLabel = \App\Support\LanguageConfigHelper::languageLabel($lang, $tourLanguageDisplay);
                        @endphp
                        <div class="tab-pane m-0 fade {{ $lang === $infoFirstLang ? 'show active' : '' }} {{ $infoLangEnabled ? '' : 'd-none' }}"
                            id="{{ $infoLangPanePrefix }}-footer-{{ $lang }}-pane"
                            data-language="{{ $lang }}" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="{{ $infoPrefix }}_info_modal_footer_text_{{ $lang }}">
                                    Modal Footer Text (optional) ({{ $infoLangLabel }})
                                </label>
                                <textarea name="{{ $infoPrefix }}_info_modal_footer_text[{{ $lang }}]"
                                    id="{{ $infoPrefix }}_info_modal_footer_text_{{ $lang }}"
                                    class="form-control editor sidebar-info-modal-editor" rows="4"
                                    placeholder="Enter footer text">{{ old($infoPrefix . '_info_modal_footer_text.' . $lang, data_get($footerTextValues, $lang, '')) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="{{ $infoPrefix }}_info_modal_footer_button_title_{{ $lang }}">
                                    Modal Footer Button Title (optional) ({{ $infoLangLabel }})
                                </label>
                                <input type="text" class="form-control"
                                    name="{{ $infoPrefix }}_info_modal_footer_button_title[{{ $lang }}]"
                                    id="{{ $infoPrefix }}_info_modal_footer_button_title_{{ $lang }}"
                                    maxlength="60"
                                    value="{{ old($infoPrefix . '_info_modal_footer_button_title.' . $lang, data_get($footerBtnTitleValues, $lang, '')) }}"
                                    placeholder="Enter footer button title">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    <label class="form-label" for="{{ $infoPrefix }}_info_modal_footer_button_link">
                        Modal Footer Button Link (optional)
                    </label>
                    <input type="text" class="form-control"
                        name="{{ $infoPrefix }}_info_modal_footer_button_link"
                        id="{{ $infoPrefix }}_info_modal_footer_button_link"
                        value="{{ $footerButtonLink }}"
                        placeholder="example.com/your-page">
                    <p class="text-muted small mb-0 mt-1">Link URL for the footer button.</p>
                </div>
            </div>
        </div>
    </div>
</div>
