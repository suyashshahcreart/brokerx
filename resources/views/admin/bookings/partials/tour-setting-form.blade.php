<!-- Tour Settings Form -->
<form method="POST" id="tourSettingsForm" action="{{ route('admin.tours.updateTourSettings', $tour) }}"
    enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    @method('PUT')

    <!-- Language settings -->
    <div class="card panel-card border-info border-top mb-3">
        <div class="card-header bg-primary-subtle border-primary">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0"><i class="ri-translate-2"></i> Language</h4>
                <p class="mb-0">
                    Choose which languages to enable in the tour.
                    The default language is loaded first in the exported HTML.
                </p>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Enabled languages</label>
                <div class="d-flex flex-wrap gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enable_language[]" id="lang_english"
                            value="en"
                            {{ (is_array($tour->enable_language) && in_array('en', $tour->enable_language)) || (is_null($tour->enable_language) && in_array('en', old('enable_language', []))) ? 'checked' : '' }}>
                        <label class="form-check-label" for="lang_english">English</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enable_language[]" id="lang_hindi"
                            value="hi"
                            {{ (is_array($tour->enable_language) && in_array('hi', $tour->enable_language)) || in_array('hi', old('enable_language', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="lang_hindi">Hindi</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enable_language[]" id="lang_gujarati"
                            value="gu"
                            {{ (is_array($tour->enable_language) && in_array('gu', $tour->enable_language)) || in_array('gu', old('enable_language', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="lang_gujarati">Gujarati</label>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">At least one language must be selected.</small>
                @error('enable_language')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="default_language">Default language</label>
                        <select name="default_language" id="default_language" class="form-select">
                            <option value="">Select default language</option>
                            <option value="en"
                                {{ old('default_language', $tour->default_language) == 'en' ? 'selected' : '' }}>English
                            </option>
                            <option value="hi"
                                {{ old('default_language', $tour->default_language) == 'hi' ? 'selected' : '' }}>Hindi
                            </option>
                            <option value="gu"
                                {{ old('default_language', $tour->default_language) == 'gu' ? 'selected' : '' }}>
                                Gujarati</option>
                            <option value="es"
                                {{ old('default_language', $tour->default_language) == 'es' ? 'selected' : '' }}>Spanish
                            </option>
                        </select>
                        @error('default_language')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loader configuration -->
    <div class="card panel-card border-info border-top mb-3">
        <div class="card-header bg-primary-subtle border-primary">
            <div class="d-flex align-items-center gap-2">
                <h4 class="card-title mb-0"><i class="ri-timer-flash-line"></i> Loader Configuration</h4>
                <p class="mb-0">Customize the loading overlay shown while the tour loads.</p>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="overlay_bg_color">Overlay background color</label>
                        <div class="input-group">
                            <span class="input-group-text p-1">
                                <input type="color" id="overlay_bg_color_picker" class="form-control form-control-color"
                                    value="{{ old('overlay_bg_color', $tour->overlay_bg_color ?? '#000040') }}"
                                    onchange="document.getElementById('overlay_bg_color').value = this.value">
                            </span>
                            <input type="text" name="overlay_bg_color" id="overlay_bg_color" class="form-control"
                                placeholder="#000040"
                                oninput="this.previousElementSibling.querySelector('input').value = this.value"
                                value="{{ old('overlay_bg_color', $tour->overlay_bg_color ?? '#000040') }}">
                        </div>
                        @error('overlay_bg_color')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label" for="loader_text">Loader text</label>
                        <input type="text" name="loader_text" id="loader_text" class="form-control"
                            placeholder="Loading tour..." value="{{ old('loader_text', $tour->loader_text ?? '') }}">
                        @error('loader_text')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="">Loader colors (gradient)</label>
                @php
                    $loaderColors = is_array($tour->loader_color) ? $tour->loader_color : [];
                    if (empty($loaderColors)) {
                        $loaderColors = ['#b47e37', '#d4a574', '#efd477'];
                    }
                @endphp
                <div class="row g-2 mb-2 " id="loaderColorContainer">
                    @foreach($loaderColors as $index => $color)
                        <div class="col-md-2 loader-color-row">
                            <div class="input-group">
                                <span class="input-group-text p-1">
                                    <input type="color" class="form-control form-control-color loader-color-picker"
                                        value="{{ $color }}"
                                        onchange="this.parentElement.nextElementSibling.value = this.value">
                                </span>
                                <input type="text" name="loader_color[]" class="form-control loader-color-input"
                                    placeholder="#000000" value="{{ $color }}"
                                    oninput="this.previousElementSibling.querySelector('input').value = this.value">
                                <!-- <button type="button" class="btn btn-soft-danger remove-loader-color">
                                    <i class="ri-delete-bin-line"></i>
                                </button> -->
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- <button type="button" class="btn btn-soft-primary btn-sm mt-2" id="addLoaderColor">
                    <i class="ri-add-line"></i> Add Color
                </button> -->
                @error('loader_color')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="">Spinner colors (gradient)</label>

                @php
                    $spinnerColors = is_array($tour->spinner_color) ? $tour->spinner_color : [];
                    if (empty($spinnerColors)) {
                        $spinnerColors = ['#b47e37', '#d4a574', '#efd477'];
                    }
                @endphp
                <div class="row g-2 mb-2" id="spinnerColorContainer">
                    @foreach($spinnerColors as $index => $color)
                        <div class="col-md-2 spinner-color-row">
                            <div class="input-group">
                                <span class="input-group-text p-1">
                                    <input type="color" class="form-control form-control-color spinner-color-picker"
                                        value="{{ $color }}"
                                        onchange="this.parentElement.nextElementSibling.value = this.value">
                                </span>
                                <input type="text" name="spinner_color[]" class="form-control spinner-color-input"
                                    placeholder="#000000" value="{{ $color }}"
                                    oninput="this.previousElementSibling.querySelector('input').value = this.value">
                                <!-- <button type="button" class="btn btn-soft-danger remove-spinner-color">
                                    <i class="ri-delete-bin-line"></i>
                                </button> -->
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- <button type="button" class="btn btn-soft-primary btn-sm mt-2" id="addSpinnerColor">
                    <i class="ri-add-line"></i> Add Color
                </button> -->
                @error('spinner_color')<div class="text-danger">{{ $message }}</div>@enderror
            </div>

            <!-- Submit Button -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button class="btn btn-primary" type="submit" id="tourSettingsSubmitBtn">
                            <i class="ri-save-line me-1"></i> Update Tour Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>