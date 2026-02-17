<!-- Language settings -->
<div class="card panel-card border-info border-top mb-3">
    <div class="card-header bg-primary-subtle border-primary">
        <div class="d-flex align-items-center gap-2">
            <h4 class="card-title mb-0"><i class="ri-translate-2"></i> Language</h4>
            <span class="badge bg-success">Enabled</span>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-2">
            <h5 class="mb-2">Languages</h5>
            <p class="text-muted mb-3">
                Choose which languages to use in the tour (Sidebar, Footer Marker, Group, info points, etc.).
                The default language is loaded first in the exported HTML.
            </p>
        </div>

        <div class="mb-3">
            <label class="form-label">Enabled languages</label>
            <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="enabled_languages[]" id="lang_english" value="English"
                        {{ in_array('English', old('enabled_languages', $tour->enabled_languages ?? ['English'])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="lang_english">English</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="enabled_languages[]" id="lang_hindi" value="Hindi"
                        {{ in_array('Hindi', old('enabled_languages', $tour->enabled_languages ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="lang_hindi">Hindi</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="enabled_languages[]" id="lang_gujarati" value="Gujarati"
                        {{ in_array('Gujarati', old('enabled_languages', $tour->enabled_languages ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="lang_gujarati">Gujarati</label>
                </div>
            </div>
            <small class="text-muted d-block mt-2">At least one language must be selected.</small>
            @error('enabled_languages')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="default_language">Default language (loaded by default in HTML)</label>
                    <select name="default_language" id="default_language" class="form-select">
                        <option value="English" {{ old('default_language', $tour->default_language ?? 'English') == 'English' ? 'selected' : '' }}>English</option>
                        <option value="Hindi" {{ old('default_language', $tour->default_language ?? '') == 'Hindi' ? 'selected' : '' }}>Hindi</option>
                        <option value="Gujarati" {{ old('default_language', $tour->default_language ?? '') == 'Gujarati' ? 'selected' : '' }}>Gujarati</option>
                    </select>
                    <small class="text-muted d-block mt-2">Used when the tour loads and when no language is selected in the URL.</small>
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
            <h4 class="card-title mb-0"><i class="ri-timer-flash-line"></i> Loader configuration</h4>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">
            These settings apply to the loading overlay shown while the tour loads (exported HTML and preview).
        </p>

        <div class="row">
            <div class="col-lg-6">
                <div class="mb-3">
                    <label class="form-label" for="loader_overlay_color">Overlay background color</label>
                    <div class="input-group">
                        <span class="input-group-text p-1">
                            <input type="color" id="loader_overlay_color_picker" class="form-control form-control-color"
                                value="{{ old('loader_overlay_color', $tour->loader_overlay_color ?? '#000040') }}"
                                onchange="document.getElementById('loader_overlay_color').value = this.value">
                        </span>
                        <input type="text" name="loader_overlay_color" id="loader_overlay_color" class="form-control"
                            placeholder="e.g. rgb(0, 0, 64) or #000040"
                            value="{{ old('loader_overlay_color', $tour->loader_overlay_color ?? 'rgb(0, 0, 64)') }}">
                    </div>
                    <small class="text-muted">e.g. rgb(0, 0, 64) or hex #000040</small>
                    @error('loader_overlay_color')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-lg-6">
                <div class="mb-3">
                    <label class="form-label" for="loader_loading_text">Loading text</label>
                    <input type="text" name="loader_loading_text" id="loader_loading_text" class="form-control"
                        placeholder="It's PROP PIK, It's Real..."
                        value="{{ old('loader_loading_text', $tour->loader_loading_text ?? '') }}">
                    <small class="text-muted">Text shown below the spinner while the tour loads.</small>
                    @error('loader_loading_text')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="mb-3">
            <h6 class="mb-1">Spinner gradient colors</h6>
            <p class="text-muted mb-2">Three colors for the loading spinner SVG (start, middle, end).</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="loader_spinner_color_1">Color 1</label>
                    <div class="input-group">
                        <span class="input-group-text p-1">
                            <input type="color" id="loader_spinner_color_1_picker" class="form-control form-control-color"
                                value="{{ old('loader_spinner_color_1', $tour->loader_spinner_color_1 ?? '#b47e37') }}"
                                onchange="document.getElementById('loader_spinner_color_1').value = this.value">
                        </span>
                        <input type="text" name="loader_spinner_color_1" id="loader_spinner_color_1" class="form-control"
                            value="{{ old('loader_spinner_color_1', $tour->loader_spinner_color_1 ?? '#b47e37') }}">
                    </div>
                    @error('loader_spinner_color_1')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="loader_spinner_color_2">Color 2</label>
                    <div class="input-group">
                        <span class="input-group-text p-1">
                            <input type="color" id="loader_spinner_color_2_picker" class="form-control form-control-color"
                                value="{{ old('loader_spinner_color_2', $tour->loader_spinner_color_2 ?? '#d4a574') }}"
                                onchange="document.getElementById('loader_spinner_color_2').value = this.value">
                        </span>
                        <input type="text" name="loader_spinner_color_2" id="loader_spinner_color_2" class="form-control"
                            value="{{ old('loader_spinner_color_2', $tour->loader_spinner_color_2 ?? '#d4a574') }}">
                    </div>
                    @error('loader_spinner_color_2')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="loader_spinner_color_3">Color 3</label>
                    <div class="input-group">
                        <span class="input-group-text p-1">
                            <input type="color" id="loader_spinner_color_3_picker" class="form-control form-control-color"
                                value="{{ old('loader_spinner_color_3', $tour->loader_spinner_color_3 ?? '#efd477') }}"
                                onchange="document.getElementById('loader_spinner_color_3').value = this.value">
                        </span>
                        <input type="text" name="loader_spinner_color_3" id="loader_spinner_color_3" class="form-control"
                            value="{{ old('loader_spinner_color_3', $tour->loader_spinner_color_3 ?? '#efd477') }}">
                    </div>
                    @error('loader_spinner_color_3')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div>
            <h6 class="mb-1">Text gradient colors</h6>
            <p class="text-muted mb-2">Three colors for the loading text gradient (start, middle, end).</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="loader_text_color_1">Color 1</label>
                    <div class="input-group">
                        <span class="input-group-text p-1">
                            <input type="color" id="loader_text_color_1_picker" class="form-control form-control-color"
                                value="{{ old('loader_text_color_1', $tour->loader_text_color_1 ?? '#b47e37') }}"
                                onchange="document.getElementById('loader_text_color_1').value = this.value">
                        </span>
                        <input type="text" name="loader_text_color_1" id="loader_text_color_1" class="form-control"
                            value="{{ old('loader_text_color_1', $tour->loader_text_color_1 ?? '#b47e37') }}">
                    </div>
                    @error('loader_text_color_1')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="loader_text_color_2">Color 2</label>
                    <div class="input-group">
                        <span class="input-group-text p-1">
                            <input type="color" id="loader_text_color_2_picker" class="form-control form-control-color"
                                value="{{ old('loader_text_color_2', $tour->loader_text_color_2 ?? '#d4a574') }}"
                                onchange="document.getElementById('loader_text_color_2').value = this.value">
                        </span>
                        <input type="text" name="loader_text_color_2" id="loader_text_color_2" class="form-control"
                            value="{{ old('loader_text_color_2', $tour->loader_text_color_2 ?? '#d4a574') }}">
                    </div>
                    @error('loader_text_color_2')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="loader_text_color_3">Color 3</label>
                    <div class="input-group">
                        <span class="input-group-text p-1">
                            <input type="color" id="loader_text_color_3_picker" class="form-control form-control-color"
                                value="{{ old('loader_text_color_3', $tour->loader_text_color_3 ?? '#efd477') }}"
                                onchange="document.getElementById('loader_text_color_3').value = this.value">
                        </span>
                        <input type="text" name="loader_text_color_3" id="loader_text_color_3" class="form-control"
                            value="{{ old('loader_text_color_3', $tour->loader_text_color_3 ?? '#efd477') }}">
                    </div>
                    @error('loader_text_color_3')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function bindColorSync(textId, pickerId) {
            const textInput = document.getElementById(textId);
            const pickerInput = document.getElementById(pickerId);

            if (!textInput || !pickerInput) {
                return;
            }

            const applyToPicker = function (value) {
                if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(value)) {
                    pickerInput.value = value;
                }
            };

            textInput.addEventListener('input', function (event) {
                applyToPicker(event.target.value.trim());
            });

            pickerInput.addEventListener('input', function (event) {
                textInput.value = event.target.value;
            });

            applyToPicker(textInput.value.trim());
        }

        bindColorSync('loader_overlay_color', 'loader_overlay_color_picker');
        bindColorSync('loader_spinner_color_1', 'loader_spinner_color_1_picker');
        bindColorSync('loader_spinner_color_2', 'loader_spinner_color_2_picker');
        bindColorSync('loader_spinner_color_3', 'loader_spinner_color_3_picker');
        bindColorSync('loader_text_color_1', 'loader_text_color_1_picker');
        bindColorSync('loader_text_color_2', 'loader_text_color_2_picker');
        bindColorSync('loader_text_color_3', 'loader_text_color_3_picker');
    });
</script>