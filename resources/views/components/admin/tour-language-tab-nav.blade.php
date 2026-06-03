@props([
    'groupId',
    'languages' => [],
    'enabledLanguages' => [],
    'languageDisplay' => [],
    'activeLanguage' => null,
    'paneIdPrefix' => null,
])

@php
    use App\Support\LanguageConfigHelper;

    $enabledSet = array_flip(array_map(static fn ($c) => strtolower((string) $c), $enabledLanguages));
    $activeLang = $activeLanguage ?? ($enabledLanguages[0] ?? ($languages[0] ?? 'en'));
@endphp

<ul {{ $attributes->merge(['class' => 'nav nav-tabs mb-3 tour-language-tab-nav']) }}
    id="{{ $groupId }}"
    role="tablist"
    data-tour-lang-tab-nav="{{ $groupId }}">
    @foreach ($languages as $lang)
        @php
            $lang = strtolower((string) $lang);
            $isEnabled = isset($enabledSet[$lang]);
            $isActive = $isEnabled && $lang === $activeLang;
            $label = LanguageConfigHelper::languageLabel($lang, $languageDisplay);
            $paneId = $paneIdPrefix ? "{$paneIdPrefix}-{$lang}-pane" : null;
        @endphp
        <li class="nav-item {{ $isEnabled ? '' : 'd-none' }}" role="presentation">
            <button type="button"
                class="nav-link {{ $isActive ? 'active' : '' }}"
                id="{{ $groupId }}-{{ $lang }}-tab"
                data-language="{{ $lang }}"
                data-bs-toggle="tab"
                @if ($paneId)
                    data-bs-target="#{{ $paneId }}"
                    aria-controls="{{ $paneId }}"
                @endif
                role="tab"
                aria-selected="{{ $isActive ? 'true' : 'false' }}">
                {{ $label }}
            </button>
        </li>
    @endforeach
</ul>
