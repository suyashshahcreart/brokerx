@php
    $sidebarConfigForm = \App\Support\SidebarConfigHelper::resolveForForm($tour);
    $sidebarConfigLogoUrl = \App\Support\SidebarConfigHelper::logoPreviewUrl(
        $tour,
        $qr_code ?? null,
        $sidebarConfigForm['logo'] ?? null
    );
    $footerButtonText = \App\Support\LanguageConfigHelper::decodePerLanguageStored(
        data_get($sidebarConfigForm, 'footerButton.text')
    );
    $footerButtonLink = (string) data_get($sidebarConfigForm, 'footerButton.link', '');
    $sidebarConfigFirstLang = $tourOrderedEnabledLanguages[0] ?? 'en';
    $sidebarTag1Data = \App\Support\SidebarConfigHelper::resolveTagSlotForForm($sidebarConfigForm, 'sidebarTag');
    $sidebarTag2Data = \App\Support\SidebarConfigHelper::resolveTagSlotForForm($sidebarConfigForm, 'sidebarTag2');
    $sidebarTourNodes = \App\Support\SidebarConfigHelper::tourNodesForSelect($tour);
    $sidebarTourNodesPreview = \App\Support\SidebarConfigHelper::tourNodesForNavigatePreview($tour, $qr_code ?? null);
@endphp

<div class="tab-pane fade show active" id="sidebar-tab-3-pane" role="tabpanel" aria-labelledby="sidebar-tab-3-tab" tabindex="0">
    <form id="sidebarConfigTabUpdateForm" method="POST"
        action="{{ route('admin.tours.updateTourSidebarConfigTab', $tour) }}"
        enctype="multipart/form-data" class="needs-validation" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

        {{-- Header Logo --}}
        <div class="mb-4">
            <label class="form-label fw-semibold">Header Logo <span class="text-muted">(optional)</span></label>
            <div class="d-flex align-items-start gap-3 flex-wrap">
                <div class="border rounded p-2 bg-light" style="min-width:200px;min-height:80px;">
                    @if ($sidebarConfigLogoUrl)
                        <img id="sidebar_config_logo_preview" src="{{ $sidebarConfigLogoUrl }}" alt="Header Logo"
                            style="max-width:280px;max-height:120px;object-fit:contain;">
                    @else
                        <img id="sidebar_config_logo_preview" src="" alt="Header Logo"
                            style="max-width:280px;max-height:120px;object-fit:contain;display:none;">
                    @endif
                </div>
                <div class="d-flex flex-column gap-2">
                    <label class="btn btn-outline-primary btn-sm mb-0">
                        <i class="ri-image-add-line me-1"></i> Change logo
                        <input type="file" name="sidebar_config_logo" id="sidebar_config_logo" class="d-none"
                            accept="image/*" @if (!$qr_code) disabled @endif>
                    </label>
                    <div class="form-check">
                        <input type="hidden" name="remove_sidebar_config_logo" value="0">
                        <input type="checkbox" class="form-check-input" id="remove_sidebar_config_logo"
                            name="remove_sidebar_config_logo" value="1">
                        <label class="form-check-label text-danger" for="remove_sidebar_config_logo">
                            <i class="ri-delete-bin-line me-1"></i> Remove
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        {{-- Made by (footer button) --}}
        <h5 class="mb-3">Made by</h5>
        <x-admin.tour-language-tab-nav
            group-id="sidebarConfigFooterLanguageTabs"
            :languages="$tourLanguageSlots"
            :enabled-languages="$tourOrderedEnabledLanguages"
            :language-display="$tourLanguageDisplay"
            :active-language="$sidebarConfigFirstLang"
            pane-id-prefix="sidebar-config-footer-lang" />

        <div class="tab-content py-2 mb-3" data-tour-lang-tab-panes="sidebarConfigFooterLanguageTabs">
            @foreach ($tourLanguageSlots as $lang)
                @php
                    $footerLangEnabled = in_array($lang, $tourOrderedEnabledLanguages, true);
                    $footerLangLabel = \App\Support\LanguageConfigHelper::languageLabel($lang, $tourLanguageDisplay);
                @endphp
                <div class="tab-pane m-0 fade {{ $lang === $sidebarConfigFirstLang ? 'show active' : '' }} {{ $footerLangEnabled ? '' : 'd-none' }}"
                    id="sidebar-config-footer-lang-{{ $lang }}-pane"
                    data-language="{{ $lang }}" role="tabpanel">
                    <label class="form-label" for="footer_button_text_{{ $lang }}">
                        Made by text (optional) ({{ $footerLangLabel }})
                    </label>
                    <input type="text" class="form-control"
                        name="footer_button_text[{{ $lang }}]"
                        id="footer_button_text_{{ $lang }}"
                        value="{{ old('footer_button_text.' . $lang, data_get($footerButtonText, $lang, '')) }}"
                        placeholder="e.g. PROP PIK">
                </div>
            @endforeach
        </div>

        <div class="row mb-4">
            <div class="col-md-8">
                <label class="form-label" for="footer_button_link">Made by link <span class="text-danger">*</span></label>
                <input type="url" name="footer_button_link" id="footer_button_link" class="form-control"
                    placeholder="https://proppik.com/contact"
                    value="{{ old('footer_button_link', $footerButtonLink) }}">
            </div>
        </div>

        <hr class="my-4">

        {{-- Sidebar tags --}}
        <ul class="nav nav-tabs mb-3" id="sidebarConfigTagTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="sidebar-config-tag1-tab" data-bs-toggle="tab"
                    data-bs-target="#sidebar-config-tag1-pane" type="button" role="tab">
                    Sidebar tag 1
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sidebar-config-tag2-tab" data-bs-toggle="tab"
                    data-bs-target="#sidebar-config-tag2-pane" type="button" role="tab">
                    Sidebar tag 2
                </button>
            </li>
        </ul>

        <div class="tab-content" id="sidebarConfigTagTabContent">
            <div class="tab-pane fade show active" id="sidebar-config-tag1-pane" role="tabpanel">
                @include('admin.bookings.partials.sidebar-config-tag-slot', [
                    'tagPrefix' => 'sidebar_tag',
                    'tagLabel' => 'Sidebar tag 1',
                    'tagData' => $sidebarTag1Data,
                    'tagFirstLang' => $sidebarConfigFirstLang,
                    'tourNodes' => $sidebarTourNodes,
                ])
            </div>
            <div class="tab-pane fade" id="sidebar-config-tag2-pane" role="tabpanel">
                @include('admin.bookings.partials.sidebar-config-tag-slot', [
                    'tagPrefix' => 'sidebar_tag2',
                    'tagLabel' => 'Sidebar tag 2',
                    'tagData' => $sidebarTag2Data,
                    'tagFirstLang' => $sidebarConfigFirstLang,
                    'tourNodes' => $sidebarTourNodes,
                ])
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="ri-save-line me-1"></i> Update Sidebar Configuration
            </button>
        </div>
    </form>
</div>

<script>
    window.sidebarConfigTourNodes = {!! json_encode($sidebarTourNodesPreview ?? []) !!};
</script>
