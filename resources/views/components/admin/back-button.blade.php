@props([
    'label' => 'Back',
    'fallback' => route('admin.index'),
    'classes' => [],
    'icon' => 'ri-arrow-left-line',
    'merge' => true,
])

@php
    $previousUrl = url()->previous();
    $currentUrl = url()->current();
    $backUrl = ($previousUrl && $previousUrl !== $currentUrl) ? $previousUrl : $fallback;

    $customClasses = $classes;
    if (is_string($customClasses)) {
        $customClasses = preg_split('/\s+/', trim($customClasses)) ?: [];
    } elseif (! is_array($customClasses)) {
        $customClasses = [];
    }

    $defaultClasses = $merge ? ['btn', 'btn-light', 'border', 'd-inline-flex', 'align-items-center', 'gap-1'] : [];
    $buttonClasses = array_filter(array_merge($defaultClasses, $customClasses));
@endphp

<a href="{{ $backUrl }}" {{ $attributes->class($buttonClasses) }} title="Go Back" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Go Back">
    <i class="{{ $icon }}"></i>
    @if($label !== false)
        <span>{{ $label }}</span>
    @endif
</a>

