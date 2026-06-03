@php
    $mediaType = $mediaType ?? 'video';
    $isVideo = $mediaType === 'video';
    $mediaPrefix = $tagPrefix ?? 'sidebar_tag';
    $mediaFile = $mediaFile ?? null;
    $mediaUrl = $mediaUrl ?? '';

    $existingJson = old(
        $mediaPrefix . '_existing_' . $mediaType . '_json',
        $mediaFile ? json_encode(['url' => $mediaFile['url'], 'fileName' => $mediaFile['fileName']]) : ''
    );
    $hasExistingFile = is_string($existingJson) && trim($existingJson) !== '' && trim($existingJson) !== 'null';
    $urlValue = old($mediaPrefix . '_' . $mediaType . '_url', $hasExistingFile ? '' : $mediaUrl);

    $urlLabel = $isVideo ? 'Video URL' : 'Document URL';
    $urlPlaceholder = $isVideo ? 'youtube.com/... or example.com/video.mp4' : 'example.com/brochure.pdf';
    $urlHelp = $isVideo
        ? 'YouTube URLs open in an iframe; direct video URLs or choose from folder play in the video player (view and exported HTML).'
        : 'Direct document URLs open in a modal (PDF/DOCX/etc.) in the exported tour.';
    $chooseLabel = $isVideo ? 'Choose Video' : 'Choose Document';
    $chooseHelp = $isVideo
        ? 'Choose a video file from the source folder or upload to the info folder.'
        : 'Choose a document file from the source folder or upload to the info folder.';
    $validationMessage = $isVideo
        ? 'Set either Video URL or upload a video file'
        : 'Set either Document URL or upload a document file';
    $chooseIcon = $isVideo ? 'ri-video-line' : 'ri-file-text-line';
    $fileAccept = $isVideo ? 'video/*' : '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt';
    $fileInputName = $mediaPrefix . '_' . $mediaType . '_file';
@endphp

<div class="sidebar-tag-media-action-fields" data-media-type="{{ $mediaType }}" data-media-prefix="{{ $mediaPrefix }}">
    <input type="hidden"
        name="{{ $mediaPrefix }}_existing_{{ $mediaType }}_json"
        id="{{ $mediaPrefix }}_existing_{{ $mediaType }}_json"
        class="sidebar-tag-media-existing-json"
        value="{{ $existingJson }}">

    <label class="form-label fw-semibold sidebar-tag-media-url-label" for="{{ $mediaPrefix }}_{{ $mediaType }}_url">
        {{ $urlLabel }} <span class="text-muted fw-normal small">(optional if uploading)</span>
    </label>
    <div class="d-flex gap-2 align-items-center flex-wrap mb-1">
        <input type="text" class="form-control sidebar-tag-media-url-input flex-grow-1"
            name="{{ $mediaPrefix }}_{{ $mediaType }}_url"
            id="{{ $mediaPrefix }}_{{ $mediaType }}_url"
            value="{{ $urlValue }}"
            placeholder="{{ $urlPlaceholder }}"
            @disabled($hasExistingFile)>
        <button type="button"
            class="btn btn-outline-danger btn-sm sidebar-tag-media-url-remove {{ $urlValue !== '' ? '' : 'd-none' }}"
            title="Remove URL">
            <i class="ri-close-line"></i> Remove
        </button>
    </div>
    <p class="text-muted small mb-3">{{ $urlHelp }}</p>

    <div class="text-center text-muted small my-2">OR</div>

    <label class="form-label fw-semibold sidebar-tag-media-choose-label">
        {{ $chooseLabel }} <span class="text-muted fw-normal small">(optional if URL is set)</span>
    </label>
    <label class="btn btn-outline-primary w-100 mb-1 sidebar-tag-media-choose-btn">
        <i class="{{ $chooseIcon }} me-1"></i> {{ $chooseLabel }}
        <input type="file" class="d-none sidebar-tag-media-file-input"
            name="{{ $fileInputName }}"
            id="{{ $fileInputName }}"
            accept="{{ $fileAccept }}">
    </label>
    <p class="text-muted small mb-3">{{ $chooseHelp }}</p>

    <div class="sidebar-tag-media-preview border rounded p-2 {{ ($hasExistingFile || $mediaFile) ? '' : 'd-none' }}"
        id="{{ $mediaPrefix }}_{{ $mediaType }}_preview">
        @if ($mediaFile)
            <div class="d-flex align-items-center gap-3 sidebar-tag-media-preview-item"
                data-url="{{ $mediaFile['url'] }}"
                data-file-name="{{ $mediaFile['fileName'] }}"
                data-is-blob="0">
                <div class="sidebar-tag-media-preview-thumb border rounded bg-light d-flex align-items-center justify-content-center"
                    style="width:120px;height:80px;overflow:hidden;">
                    @if ($isVideo)
                        <video src="{{ $mediaFile['preview'] }}" class="w-100 h-100" style="object-fit:cover;" muted playsinline preload="metadata"></video>
                    @else
                        <i class="{{ $chooseIcon }} fs-2 text-secondary"></i>
                    @endif
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="text-truncate fw-semibold sidebar-tag-media-preview-name" title="{{ $mediaFile['fileName'] }}">
                        {{ $mediaFile['fileName'] }}
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger sidebar-tag-media-file-remove" title="Remove uploaded file">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        @endif
    </div>

    <p class="text-danger small mb-0 sidebar-tag-media-validation {{ ($urlValue !== '' || $hasExistingFile || $mediaFile) ? 'd-none' : '' }}">
        {{ $validationMessage }}
    </p>
</div>
