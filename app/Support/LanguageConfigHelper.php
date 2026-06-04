<?php

namespace App\Support;

/**
 * Locale / language slot helpers (aligned with proppik src/utils/locale.js).
 */
class LanguageConfigHelper
{
    public const DEFAULT_LANGUAGE_DISPLAY = [
        'en' => ['title' => 'English', 'short' => 'EN'],
        'hi' => ['title' => 'Hindi', 'short' => 'HI'],
        'gu' => ['title' => 'Gujarati', 'short' => 'GU'],
    ];

    /**
     * @param  array<string, array{title?: string, short?: string}>|null  $raw
     * @return array<string, array{title: string, short: string}>
     */
    public static function normalizeLanguageDisplay(?array $raw): array
    {
        $out = [];
        foreach (array_keys(self::DEFAULT_LANGUAGE_DISPLAY) as $code) {
            $def = self::DEFAULT_LANGUAGE_DISPLAY[$code];
            $entry = is_array($raw[$code] ?? null) ? $raw[$code] : [];
            $out[$code] = [
                'title' => trim((string) ($entry['title'] ?? $def['title'])) ?: $def['title'],
                'short' => trim((string) ($entry['short'] ?? $def['short'])) ?: $def['short'],
            ];
        }

        if (is_array($raw)) {
            foreach ($raw as $key => $entry) {
                if (! is_string($key) || ! is_array($entry)) {
                    continue;
                }
                $lc = strtolower($key);
                if (! preg_match('/^[a-z]{2}$/', $lc)) {
                    continue;
                }
                if (isset($out[$lc])) {
                    continue;
                }
                $out[$lc] = [
                    'title' => trim((string) ($entry['title'] ?? '')) ?: strtoupper($lc),
                    'short' => trim((string) ($entry['short'] ?? '')) ?: strtoupper($lc),
                ];
            }
        }

        return $out;
    }

    /**
     * @param  array<int, string>|null  $enabledLanguages
     * @param  array<string, array{title?: string, short?: string}>|null  $languageDisplay
     * @param  array<int, string>|null  $languageSlotOrder
     * @return array<int, string>
     */
    public static function collectLanguageSlotCodes(?array $enabledLanguages, ?array $languageDisplay, ?array $languageSlotOrder): array
    {
        $norm = self::normalizeLanguageDisplay($languageDisplay);
        $codes = array_keys(self::DEFAULT_LANGUAGE_DISPLAY);
        foreach (array_keys($norm) as $k) {
            if (! in_array($k, $codes, true)) {
                $codes[] = $k;
            }
        }
        foreach ($enabledLanguages ?? [] as $c) {
            $lc = strtolower((string) $c);
            if (preg_match('/^[a-z]{2}$/', $lc) && ! in_array($lc, $codes, true)) {
                $codes[] = $lc;
            }
        }

        if (is_array($languageSlotOrder) && $languageSlotOrder !== []) {
            $set = array_flip($codes);
            $out = [];
            $seen = [];
            foreach ($languageSlotOrder as $c) {
                $lc = strtolower((string) $c);
                if (isset($set[$lc]) && ! isset($seen[$lc]) && preg_match('/^[a-z]{2}$/', $lc)) {
                    $out[] = $lc;
                    $seen[$lc] = true;
                }
            }
            $rest = array_values(array_filter($codes, static fn ($c) => ! isset($seen[$c])));
            sort($rest);

            return array_merge($out, $rest);
        }

        sort($codes);

        return $codes;
    }

    /**
     * @param  array<int, string>|null  $enabledLanguages
     * @param  array<int, string>|null  $languageSlotOrder
     * @return array<int, string>
     */
    public static function languageLabel(string $code, ?array $languageDisplay = null): string
    {
        $lc = strtolower($code);
        $display = self::normalizeLanguageDisplay($languageDisplay);

        return $display[$lc]['title'] ?? strtoupper($lc);
    }

    /**
     * Laravel validation rules for a per-language string map (e.g. bookmark_title.en).
     *
     * @param  array<int, string>  $languageCodes
     * @return array<string, mixed>
     */
    public static function perLanguageStringRules(
        string $prefix,
        array $languageCodes,
        int $maxLength = 255,
        bool $nullable = true
    ): array {
        $rules = [$prefix => ['nullable', 'array']];
        $fieldRules = $nullable
            ? ['nullable', 'string', 'max:'.$maxLength]
            : ['required', 'string', 'max:'.$maxLength];

        foreach ($languageCodes as $code) {
            $lc = strtolower((string) $code);
            if (preg_match('/^[a-z]{2}$/', $lc)) {
                $rules["{$prefix}.{$lc}"] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Normalize a DB/JSON per-language column (array, JSON string, or legacy plain string).
     *
     * @return array<string, string>
     */
    public static function decodePerLanguageStored(mixed $stored, string $fallbackLang = 'en'): array
    {
        if (is_array($stored)) {
            return $stored;
        }

        if (is_string($stored) && trim($stored) !== '') {
            $decoded = json_decode($stored, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            return [$fallbackLang => $stored];
        }

        return [];
    }

    /**
     * Merge incoming locale map into existing JSON branding field (scalar → wrapped as en).
     *
     * @param  array<string, mixed>  $existingJsonValue
     * @param  array<string, mixed>  $incoming
     * @param  array<int, string>  $languageCodes
     * @return array<string, string>
     */
    public static function mergeLocaleMapForJson(
        array $existingJsonValue,
        array $incoming,
        array $languageCodes
    ): array {
        if (! is_array($existingJsonValue)) {
            $existingJsonValue = $existingJsonValue !== null && $existingJsonValue !== ''
                ? ['en' => (string) $existingJsonValue]
                : [];
        }

        return self::mergePerLanguageStringMap($existingJsonValue, $incoming, $languageCodes);
    }

    public static function mergePerLanguageStringMap(array $existing, array $incoming, array $languageCodes): array
    {
        foreach ($languageCodes as $code) {
            $lc = strtolower((string) $code);
            if (! preg_match('/^[a-z]{2}$/', $lc)) {
                continue;
            }
            if (array_key_exists($lc, $incoming)) {
                $existing[$lc] = is_string($incoming[$lc]) ? $incoming[$lc] : (string) ($incoming[$lc] ?? '');
            }
        }

        return array_filter(
            $existing,
            static fn ($value) => is_string($value) && trim($value) !== ''
        );
    }

    public static function getOrderedEnabledLanguages(?array $enabledLanguages, ?array $languageSlotOrder): array
    {
        if (! is_array($enabledLanguages) || $enabledLanguages === []) {
            return [];
        }

        $normalizedEnabled = array_values(array_map(
            static fn ($c) => strtolower((string) $c),
            $enabledLanguages
        ));

        if (! is_array($languageSlotOrder) || $languageSlotOrder === []) {
            return $normalizedEnabled;
        }

        $enabledSet = array_flip($normalizedEnabled);
        $out = [];
        $seen = [];
        foreach ($languageSlotOrder as $c) {
            $lc = strtolower((string) $c);
            if (isset($enabledSet[$lc]) && ! isset($seen[$lc])) {
                $out[] = $lc;
                $seen[$lc] = true;
            }
        }
        foreach ($normalizedEnabled as $lc) {
            if (! isset($seen[$lc])) {
                $out[] = $lc;
            }
        }

        return $out;
    }

    /**
     * Resolve language UI state from tour model columns / JSON.
     *
     * @return array{
     *   localeConfig: array,
     *   languageDisplay: array<string, array{title: string, short: string}>,
     *   languageSlotOrder: array<int, string>,
     *   enabledLanguages: array<int, string>,
     *   defaultLanguage: string,
     *   showLanguageInContactPanel: bool
     * }
     */
    public static function resolveFromTour(\App\Models\Tour $tour): array
    {
        $localeConfig = is_array($tour->locale_config) ? $tour->locale_config : [];
        if ($localeConfig === []) {
            $finalJson = is_array($tour->final_json) ? $tour->final_json : [];
            $localeConfig = is_array($finalJson['tour']['localeConfig'] ?? null)
                ? $finalJson['tour']['localeConfig']
                : [];
        }

        $languageDisplay = is_array($tour->language_display) && $tour->language_display !== []
            ? $tour->language_display
            : ($localeConfig['languageDisplay'] ?? []);
        $languageDisplay = self::normalizeLanguageDisplay($languageDisplay);

        $languageSlotOrder = is_array($tour->language_slot_order) && $tour->language_slot_order !== []
            ? array_values(array_map(static fn ($c) => strtolower((string) $c), $tour->language_slot_order))
            : (is_array($localeConfig['languageSlotOrder'] ?? null)
                ? array_values(array_map(static fn ($c) => strtolower((string) $c), $localeConfig['languageSlotOrder']))
                : []);

        $enabledLanguages = is_array($tour->enable_language) && $tour->enable_language !== []
            ? array_values(array_map(static fn ($c) => strtolower((string) $c), $tour->enable_language))
            : array_values(array_map(
                static fn ($c) => strtolower((string) $c),
                $localeConfig['enabledLanguages'] ?? ['en']
            ));

        $defaultLanguage = strtolower((string) (
            $tour->default_language
            ?? $localeConfig['defaultLanguage']
            ?? ($enabledLanguages[0] ?? 'en')
        ));

        $languageSlotOrder = self::collectLanguageSlotCodes($enabledLanguages, $languageDisplay, $languageSlotOrder);

        $finalJson = is_array($tour->final_json) ? $tour->final_json : [];
        $showInPanel = (bool) data_get(
            $finalJson,
            'branding.userInfo.showLanguageInContactPanel',
            data_get($localeConfig, 'showLanguageInContactPanel', false)
        );

        return [
            'localeConfig' => $localeConfig,
            'languageDisplay' => $languageDisplay,
            'languageSlotOrder' => $languageSlotOrder,
            'enabledLanguages' => $enabledLanguages,
            'defaultLanguage' => $defaultLanguage,
            'showLanguageInContactPanel' => $showInPanel,
        ];
    }

    /**
     * Build full localeConfig for persistence (preserves viewerUiByLanguage and other keys).
     *
     * @param  array<string, mixed>  $existingLocaleConfig
     * @param  array<int, string>  $enabledLanguages
     * @param  array<string, array{title: string, short: string}>  $languageDisplay
     * @param  array<int, string>  $languageSlotOrder
     */
    public static function buildLocaleConfig(
        array $existingLocaleConfig,
        array $enabledLanguages,
        string $defaultLanguage,
        array $languageDisplay,
        array $languageSlotOrder,
        bool $showLanguageInContactPanel
    ): array {
        $localeConfig = $existingLocaleConfig;
        $localeConfig['enabledLanguages'] = array_values($enabledLanguages);
        $localeConfig['defaultLanguage'] = $defaultLanguage;
        $localeConfig['languageDisplay'] = $languageDisplay;
        $localeConfig['languageSlotOrder'] = array_values($languageSlotOrder);
        $localeConfig['showLanguageInContactPanel'] = $showLanguageInContactPanel;

        return $localeConfig;
    }
}
