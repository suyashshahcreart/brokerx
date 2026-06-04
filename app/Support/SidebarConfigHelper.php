<?php

namespace App\Support;

use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SidebarConfigHelper
{
    public const TAG_DEFAULT_BG = '#000040';

    public const TAG_DEFAULT_FG = '#ffffff';

    public const TAG_SIZES = ['small', 'medium', 'large'];

    public const ACTION_TYPES = [
        'redirectToLink',
        'openInfoModal',
        'navigateToNode',
        'openImage',
        'openVideo',
        'openDocument',
        'openMenu',
    ];

    /**
     * @return list<string>
     */
    public static function legacyColumnNames(): array
    {
        return [
            'sidebar_tag_text',
            'sidebar_tag_color',
            'sidebar_tag_bg_color',
            'sidebar_footer_link',
            'sidebar_footer_text',
            'sidebar_footer_link_show',
            'sidebar_logo',
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function mergeLegacyRowIntoConfig(array $config, array $row): array
    {
        if (! isset($config['footerButton']) && (! empty($row['sidebar_footer_text']) || ! empty($row['sidebar_footer_link']))) {
            $footerButton = [
                'text' => LanguageConfigHelper::decodePerLanguageStored($row['sidebar_footer_text'] ?? null),
                'link' => (string) ($row['sidebar_footer_link'] ?? ''),
            ];
            if (array_key_exists('sidebar_footer_link_show', $row)) {
                $footerButton['show'] = (bool) $row['sidebar_footer_link_show'];
            }
            $config['footerButton'] = $footerButton;
        }

        if (! isset($config['sidebarTag']) && (! empty($row['sidebar_tag_text']) || ! empty($row['sidebar_tag_bg_color']) || ! empty($row['sidebar_tag_color']))) {
            $config['sidebarTag'] = [
                'text' => LanguageConfigHelper::decodePerLanguageStored($row['sidebar_tag_text'] ?? null),
                'backgroundColor' => $row['sidebar_tag_bg_color'] ?? self::TAG_DEFAULT_BG,
                'textColor' => $row['sidebar_tag_color'] ?? self::TAG_DEFAULT_FG,
                'showTag' => true,
            ];
        }

        if (empty($config['logo']) && ! empty($row['sidebar_logo'])) {
            $config['logo'] = (string) $row['sidebar_logo'];
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>|null
     */
    public static function defaultFromTourSettings(array $settings): ?array
    {
        $footerText = trim((string) ($settings['tour_footer_button_text'] ?? ''));
        $footerLink = trim((string) ($settings['tour_footer_button_link'] ?? ''));

        if ($footerText === '' && $footerLink === '') {
            return null;
        }

        return [
            'footerButton' => [
                'text' => ['en' => $footerText !== '' ? $footerText : 'Designe By PROP PIK'],
                'link' => $footerLink,
                'show' => (bool) ($settings['tour_footer_link_show'] ?? 1),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function resolveForForm(Tour $tour): array
    {
        return is_array($tour->sidebar_config) ? $tour->sidebar_config : [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function resolveTagSlotForForm(array $config, string $slotKey): array
    {
        $raw = $config[$slotKey] ?? [];
        if (! is_array($raw)) {
            $raw = [];
        }

        $text = LanguageConfigHelper::decodePerLanguageStored($raw['text'] ?? null);

        return [
            'showTag' => array_key_exists('showTag', $raw)
                ? ($raw['showTag'] !== false)
                : self::tagSlotHasTextContent($text),
            'text' => $text,
            'backgroundColor' => (string) ($raw['backgroundColor'] ?? self::TAG_DEFAULT_BG),
            'textColor' => (string) ($raw['textColor'] ?? $raw['color'] ?? self::TAG_DEFAULT_FG),
            'tagSize' => self::normalizeTagSize($raw['tagSize'] ?? 'small'),
            'clickable' => ! empty($raw['clickable']),
            'action' => self::normalizeAction($raw['action'] ?? 'redirectToLink'),
            'openLinkUrl' => (string) ($raw['openLinkUrl'] ?? ''),
            'buttonNodeId' => (string) ($raw['buttonNodeId'] ?? ''),
            'modalTitle' => LanguageConfigHelper::decodePerLanguageStored($raw['modalTitle'] ?? null),
            'modalDescription' => LanguageConfigHelper::decodePerLanguageStored($raw['modalDescription'] ?? null),
            'infoModalFooterButtonTitle' => LanguageConfigHelper::decodePerLanguageStored($raw['infoModalFooterButtonTitle'] ?? null),
            'infoModalFooterButtonLink' => (string) ($raw['infoModalFooterButtonLink'] ?? ''),
            'infoModalFooterText' => LanguageConfigHelper::decodePerLanguageStored($raw['infoModalFooterText'] ?? null),
            'videoUrl' => (string) ($raw['videoUrl'] ?? ''),
            'documentUrl' => (string) ($raw['documentUrl'] ?? ''),
            'video' => is_array($raw['video'] ?? null) ? $raw['video'] : null,
            'document' => is_array($raw['document'] ?? null) ? $raw['document'] : null,
            'images' => is_array($raw['images'] ?? null) ? $raw['images'] : [],
            'buttonNodeView' => is_array($raw['buttonNodeView'] ?? null) ? $raw['buttonNodeView'] : null,
            'buttonMenuItems' => is_array($raw['buttonMenuItems'] ?? null) ? $raw['buttonMenuItems'] : [],
            'buttonMenuShowLift' => ! empty($raw['buttonMenuShowLift']),
            'infoModalWidth' => (string) ($raw['infoModalWidth'] ?? ''),
            'infoModalUseCustomModal' => ! empty($raw['infoModalUseCustomModal']),
            'infoModalIframeUrl' => (string) ($raw['infoModalIframeUrl'] ?? ''),
        ];
    }

    /**
     * Tour panorama nodes for Navigate to Node / Open menu scene pickers.
     *
     * @return array<int, array{id: string, name: string}>
     */
    public static function tourNodesForSelect(Tour $tour): array
    {
        $json = is_array($tour->virtual_tour_nodes_json) ? $tour->virtual_tour_nodes_json : [];
        $nodes = $json['nodes'] ?? data_get($json, 'tour.nodes', []);
        if (! is_array($nodes)) {
            return [];
        }

        $out = [];
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }
            $id = $node['id'] ?? null;
            if ($id === null || $id === '') {
                continue;
            }
            $name = self::resolveNodeDisplayName($node, $id);

            $out[] = [
                'id' => (string) $id,
                'name' => $name,
            ];
        }

        return $out;
    }

    /**
     * Tour nodes with resolved panorama URLs for Navigate to Node preview.
     *
     * @return array<int, array{id: string, name: string, panoramaUrl: string|null}>
     */
    public static function tourNodesForNavigatePreview(Tour $tour, ?string $qrCode): array
    {
        $json = is_array($tour->virtual_tour_nodes_json) ? $tour->virtual_tour_nodes_json : [];
        $nodes = $json['nodes'] ?? data_get($json, 'tour.nodes', []);
        if (! is_array($nodes)) {
            return [];
        }

        $assetBase = self::resolveTourAssetBaseUrl($json, $qrCode);
        $out = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }
            $id = $node['id'] ?? null;
            if ($id === null || $id === '') {
                continue;
            }

            $displayName = self::resolveNodeDisplayName($node, $id);

            $out[] = [
                'id' => (string) $id,
                'name' => $displayName,
                'panoramaUrl' => self::resolveNodePanoramaPreviewUrl(
                    $node['panorama'] ?? null,
                    $assetBase,
                    (string) $id,
                    $displayName
                ),
            ];
        }

        return $out;
    }

    public static function hasValidNavigateNodeView(mixed $view): bool
    {
        return is_array($view)
            && isset($view['yaw'])
            && is_numeric($view['yaw'])
            && ! is_nan((float) $view['yaw']);
    }

    private static function resolveNodeDisplayName(array $node, mixed $id): string
    {
        $name = $node['name'] ?? $node['sideMenuTitle'] ?? null;
        if (is_array($name)) {
            $name = $name['en'] ?? reset($name) ?: null;
        }
        if (! is_string($name) || trim($name) === '') {
            $name = 'Node ' . $id;
        }

        return trim($name);
    }

    private static function resolveTourAssetBaseUrl(array $json, ?string $qrCode): ?string
    {
        $s3Link = trim((string) ($json['s3_link'] ?? ''));
        if ($s3Link !== '') {
            return rtrim($s3Link, '/');
        }

        if ($qrCode) {
            return rtrim(Storage::disk('s3')->url('tours/' . $qrCode . '/'), '/');
        }

        return null;
    }

    private static function resolveNodePanoramaPreviewUrl(
        mixed $panorama,
        ?string $assetBase,
        ?string $nodeId = null,
        ?string $nodeName = null
    ): ?string {
        if ($assetBase === null || trim($assetBase) === '') {
            return null;
        }

        $baseFileName = self::extractNodeBaseFileName($panorama, $nodeId, $nodeName);
        if ($baseFileName === null || $baseFileName === '') {
            return null;
        }

        return rtrim($assetBase, '/') . '/images/' . $baseFileName . '_base.webp';
    }

    private static function extractNodeBaseFileName(
        mixed $panorama,
        ?string $nodeId = null,
        ?string $nodeName = null
    ): ?string {
        if (is_string($panorama) && trim($panorama) !== '') {
            $fromPath = self::baseFileNameFromPanoramaPath(trim($panorama));
            if ($fromPath !== null && $fromPath !== '') {
                return $fromPath;
            }
        }

        if (is_array($panorama)) {
            if (! empty($panorama['tileBase']) && is_string($panorama['tileBase'])) {
                return trim($panorama['tileBase']) ?: null;
            }

            $base = $panorama['baseUrl'] ?? null;
            if (is_string($base) && trim($base) !== '') {
                $fromPath = self::baseFileNameFromPanoramaPath(trim($base));
                if ($fromPath !== null && $fromPath !== '') {
                    return $fromPath;
                }
            }

            if (is_array($base)) {
                foreach (['front', 'right', 'left', 'back', 'top', 'bottom'] as $face) {
                    if (! empty($base[$face]) && is_string($base[$face])) {
                        $fromFace = self::baseFileNameFromPanoramaPath(trim($base[$face]));
                        if ($fromFace !== null && $fromFace !== '') {
                            return preg_replace('/_(front|right|left|back|top|bottom)$/i', '', $fromFace) ?: $fromFace;
                        }
                    }
                }
            }
        }

        if (is_string($nodeName) && trim($nodeName) !== '') {
            $fromName = self::baseFileNameFromPanoramaPath(trim($nodeName));
            if ($fromName !== null && $fromName !== '') {
                return $fromName;
            }
        }

        if ($nodeId !== null && trim((string) $nodeId) !== '') {
            return trim((string) $nodeId);
        }

        return null;
    }

    private static function baseFileNameFromPanoramaPath(string $path): ?string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = (string) (parse_url($path, PHP_URL_PATH) ?: $path);
        }

        $path = (string) (preg_split('/[?#]/', $path, 2)[0] ?? $path);
        $basename = basename($path);

        if ($basename === '' || $basename === '.' || $basename === '..') {
            return null;
        }

        if (preg_match('/^(.+?)_base\.webp$/i', $basename, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(.+?)_(front|right|left|back|top|bottom)_base\.webp$/i', $basename, $matches)) {
            return $matches[1];
        }

        $stripped = preg_replace('/\.(jpg|jpeg|png|webp)$/i', '', $basename);

        return is_string($stripped) && $stripped !== '' ? $stripped : null;
    }

    /**
     * @param  array<int, string>  $languageCodes
     * @return array<string, mixed>
     */
    public static function validationRules(array $languageCodes): array
    {
        $rules = [
            'sidebar_config_logo' => ['nullable', 'file', 'image', 'max:5120'],
            'remove_sidebar_config_logo' => ['nullable', 'boolean'],
            'footer_button_link' => ['nullable', 'string', 'max:500'],
        ];

        $rules = array_merge(
            $rules,
            LanguageConfigHelper::perLanguageStringRules('footer_button_text', $languageCodes, 255)
        );

        foreach (self::tagFormPrefixes() as $prefix) {
            $rules[$prefix . '_show'] = ['nullable', 'boolean'];
            $rules[$prefix . '_bg_color'] = ['nullable', 'string', 'max:50'];
            $rules[$prefix . '_text_color'] = ['nullable', 'string', 'max:50'];
            $rules[$prefix . '_size'] = ['nullable', 'string', 'in:small,medium,large'];
            $rules[$prefix . '_clickable'] = ['nullable', 'boolean'];
            $rules[$prefix . '_action'] = ['nullable', 'string', 'max:50'];
            $rules[$prefix . '_open_link_url'] = ['nullable', 'string', 'max:500'];
            $rules[$prefix . '_button_node_id'] = ['nullable', 'string', 'max:255'];
            $rules[$prefix . '_button_node_view_json'] = ['nullable', 'string'];
            $rules[$prefix . '_menu_items_json'] = ['nullable', 'string'];
            $rules[$prefix . '_menu_show_lift'] = ['nullable', 'boolean'];
            $rules[$prefix . '_video_url'] = ['nullable', 'string', 'max:500'];
            $rules[$prefix . '_document_url'] = ['nullable', 'string', 'max:500'];
            $rules[$prefix . '_existing_video_json'] = ['nullable', 'string'];
            $rules[$prefix . '_existing_document_json'] = ['nullable', 'string'];
            $rules[$prefix . '_video_file'] = ['nullable', 'file', 'max:102400', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm'];
            $rules[$prefix . '_document_file'] = ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt'];
            $rules[$prefix . '_info_modal_footer_button_link'] = ['nullable', 'string', 'max:500'];
            $rules[$prefix . '_info_modal_width'] = ['nullable', 'string', 'in:,modal-sm,modal-lg,modal-xl'];
            $rules[$prefix . '_info_modal_use_custom'] = ['nullable', 'boolean'];
            $rules[$prefix . '_info_modal_iframe_enabled'] = ['nullable', 'boolean'];
            $rules[$prefix . '_info_modal_iframe_url'] = ['nullable', 'string', 'max:500'];
            $rules[$prefix . '_existing_images_json'] = ['nullable', 'string'];
            $rules[$prefix . '_image_files'] = ['nullable', 'array'];
            $rules[$prefix . '_image_files.*'] = ['nullable', 'file', 'image', 'max:10240'];

            $rules = array_merge(
                $rules,
                LanguageConfigHelper::perLanguageStringRules($prefix . '_text', $languageCodes, 120),
                LanguageConfigHelper::perLanguageStringRules($prefix . '_modal_title', $languageCodes, 60, true),
                LanguageConfigHelper::perLanguageStringRules($prefix . '_modal_description', $languageCodes, 50000, true),
                LanguageConfigHelper::perLanguageStringRules($prefix . '_info_modal_footer_button_title', $languageCodes, 500, true),
                LanguageConfigHelper::perLanguageStringRules($prefix . '_info_modal_footer_text', $languageCodes, 50000, true)
            );
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $existingConfig
     * @param  array<int, string>  $languageCodes
     * @return array<string, mixed>
     */
    public static function buildFromValidated(
        array $validated,
        Request $request,
        array $existingConfig,
        array $languageCodes,
        ?string $qrCode
    ): array {
        $config = is_array($existingConfig) ? $existingConfig : [];

        $existingFooterText = LanguageConfigHelper::decodePerLanguageStored(
            data_get($config, 'footerButton.text')
        );
        $footerText = LanguageConfigHelper::mergePerLanguageStringMap(
            $existingFooterText,
            is_array($validated['footer_button_text'] ?? null) ? $validated['footer_button_text'] : [],
            $languageCodes
        );

        $footerLink = array_key_exists('footer_button_link', $validated)
            ? trim((string) $validated['footer_button_link'])
            : trim((string) data_get($config, 'footerButton.link', ''));

        if ($footerText !== [] && $footerLink !== '') {
            $config['footerButton'] = [
                'text' => $footerText,
                'link' => $footerLink,
            ];
        } else {
            unset($config['footerButton']);
        }

        if ($request->boolean('remove_sidebar_config_logo')) {
            unset($config['logo'], $config['logoFileName']);
        }

        $logoFile = $request->file('sidebar_config_logo');
        if ($logoFile && $qrCode) {
            $filename = 'logo_sidebar_' . time() . '_' . Str::random(8) . '.' . $logoFile->getClientOriginalExtension();
            $path = 'tours/' . $qrCode . '/assets/' . $filename;
            Storage::disk('s3')->put($path, file_get_contents($logoFile->getRealPath()), [
                'ContentType' => $logoFile->getMimeType(),
            ]);
            $config['logo'] = 'assets/' . $filename;
            $config['logoFileName'] = $logoFile->getClientOriginalName();
        }

        foreach (self::tagSlotMap() as $prefix => $slotKey) {
            $existingSlot = is_array($config[$slotKey] ?? null) ? $config[$slotKey] : [];
            $built = self::buildTagSlotFromValidated($validated, $request, $prefix, $slotKey, $existingSlot, $languageCodes, $qrCode);
            if ($built !== null) {
                $config[$slotKey] = $built;
            } else {
                unset($config[$slotKey]);
            }
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $existingSlot
     * @return array<string, mixed>|null
     */
    public static function buildTagSlotFromValidated(
        array $validated,
        Request $request,
        string $prefix,
        string $slotKey,
        array $existingSlot,
        array $languageCodes,
        ?string $qrCode
    ): ?array {
        $showTag = (bool) ($validated[$prefix . '_show'] ?? false);
        if (! $showTag) {
            return [
                'showTag' => false,
                'text' => '',
                'backgroundColor' => self::TAG_DEFAULT_BG,
                'textColor' => self::TAG_DEFAULT_FG,
                'tagSize' => 'small',
                'clickable' => false,
            ];
        }

        $existingText = LanguageConfigHelper::decodePerLanguageStored($existingSlot['text'] ?? null);
        $textMap = LanguageConfigHelper::mergePerLanguageStringMap(
            $existingText,
            is_array($validated[$prefix . '_text'] ?? null) ? $validated[$prefix . '_text'] : [],
            $languageCodes
        );

        $textField = count($languageCodes) === 1
            ? trim((string) ($textMap[$languageCodes[0]] ?? ''))
            : $textMap;

        if (is_array($textField) && $textField === []) {
            $textField = '';
        }

        $slot = [
            'showTag' => true,
            'text' => $textField,
            'backgroundColor' => trim((string) ($validated[$prefix . '_bg_color'] ?? self::TAG_DEFAULT_BG)) ?: self::TAG_DEFAULT_BG,
            'textColor' => trim((string) ($validated[$prefix . '_text_color'] ?? self::TAG_DEFAULT_FG)) ?: self::TAG_DEFAULT_FG,
            'tagSize' => self::normalizeTagSize($validated[$prefix . '_size'] ?? 'small'),
            'clickable' => (bool) ($validated[$prefix . '_clickable'] ?? false),
        ];

        if (! $slot['clickable']) {
            return $slot;
        }

        $action = self::normalizeAction($validated[$prefix . '_action'] ?? 'redirectToLink');
        $slot['clickable'] = true;
        $slot['action'] = $action;

        if ($action === 'redirectToLink') {
            $slot['openLinkUrl'] = trim((string) ($validated[$prefix . '_open_link_url'] ?? ''));
        } elseif ($action === 'navigateToNode') {
            $slot['buttonNodeId'] = trim((string) ($validated[$prefix . '_button_node_id'] ?? ''));
            $viewJson = $request->input($prefix . '_button_node_view_json');
            if (is_string($viewJson) && trim($viewJson) !== '' && trim($viewJson) !== 'null') {
                $view = json_decode($viewJson, true);
                if (self::hasValidNavigateNodeView($view)) {
                    $slot['buttonNodeView'] = [
                        'yaw' => (float) $view['yaw'],
                        'pitch' => 0,
                        'zoom' => isset($view['zoom']) && is_numeric($view['zoom']) ? (float) $view['zoom'] : 0,
                        ...(isset($view['fov']) && is_numeric($view['fov']) ? ['fov' => (float) $view['fov']] : []),
                        ...(isset($view['depth']) && is_numeric($view['depth']) ? ['depth' => (float) $view['depth']] : []),
                    ];
                }
            }
        } elseif ($action === 'openMenu') {
            $slot['buttonMenuItems'] = self::parseMenuItemsJson($request->input($prefix . '_menu_items_json'));
            if ($request->boolean($prefix . '_menu_show_lift') && count($slot['buttonMenuItems']) >= 2) {
                $slot['buttonMenuShowLift'] = true;
            }
        } elseif ($action === 'openInfoModal') {
            $modalWidth = trim((string) ($validated[$prefix . '_info_modal_width'] ?? ''));
            if ($modalWidth !== '') {
                $slot['infoModalWidth'] = $modalWidth;
            }

            $useCustomModal = $request->boolean($prefix . '_info_modal_use_custom');
            if ($useCustomModal) {
                $slot['infoModalUseCustomModal'] = true;
            }

            $iframeEnabled = $request->boolean($prefix . '_info_modal_iframe_enabled');
            if (! $useCustomModal && $iframeEnabled) {
                $iframeUrl = trim((string) ($validated[$prefix . '_info_modal_iframe_url'] ?? ''));
                if ($iframeUrl !== '') {
                    $slot['infoModalIframeUrl'] = $iframeUrl;
                }
            }

            $slot['modalTitle'] = self::mergeLocalizedFieldAsObject(
                $existingSlot,
                'modalTitle',
                $validated,
                $prefix . '_modal_title',
                $languageCodes
            );
            $slot['modalDescription'] = self::mergeLocalizedFieldAsObject(
                $existingSlot,
                'modalDescription',
                $validated,
                $prefix . '_modal_description',
                $languageCodes
            );

            if (! $useCustomModal) {
                $slot['infoModalFooterButtonTitle'] = self::mergeLocalizedFieldAsObject(
                    $existingSlot,
                    'infoModalFooterButtonTitle',
                    $validated,
                    $prefix . '_info_modal_footer_button_title',
                    $languageCodes
                );
                $footerLink = trim((string) ($validated[$prefix . '_info_modal_footer_button_link'] ?? ''));
                if ($footerLink !== '') {
                    $slot['infoModalFooterButtonLink'] = $footerLink;
                }
                $slot['infoModalFooterText'] = self::mergeLocalizedFieldAsObject(
                    $existingSlot,
                    'infoModalFooterText',
                    $validated,
                    $prefix . '_info_modal_footer_text',
                    $languageCodes
                );
            }
        } elseif ($action === 'openVideo') {
            self::applyMediaActionToSlot(
                $slot,
                $request,
                $prefix,
                $slotKey,
                'video',
                $qrCode
            );
        } elseif ($action === 'openDocument') {
            self::applyMediaActionToSlot(
                $slot,
                $request,
                $prefix,
                $slotKey,
                'document',
                $qrCode
            );
        } elseif ($action === 'openImage') {
            $existingImages = [];
            $jsonRaw = $request->input($prefix . '_existing_images_json');
            if (is_string($jsonRaw) && trim($jsonRaw) !== '') {
                $decoded = json_decode($jsonRaw, true);
                if (is_array($decoded)) {
                    $existingImages = $decoded;
                }
            }

            $images = array_values(array_filter($existingImages, static function ($img) {
                return is_array($img) && ! empty($img['url']);
            }));

            if ($request->hasFile($prefix . '_image_files') && $qrCode) {
                foreach ($request->file($prefix . '_image_files') as $file) {
                    if (! $file || ! $file->isValid()) {
                        continue;
                    }
                    $fileName = $slotKey . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                    $filePath = 'tours/' . $qrCode . '/info/' . $fileName;
                    Storage::disk('s3')->put($filePath, file_get_contents($file->getRealPath()), [
                        'ContentType' => $file->getMimeType(),
                    ]);
                    $images[] = [
                        'url' => 'info/' . $fileName,
                        'fileName' => $file->getClientOriginalName(),
                    ];
                }
            }

            $slot['images'] = $images;
        }

        return $slot;
    }

    /**
     * @return array{url: string, fileName: string, preview: string}|null
     */
    public static function normalizeMediaFileForForm(mixed $media, ?string $qrCode): ?array
    {
        if (! is_array($media) || empty($media['url'])) {
            return null;
        }

        $url = (string) $media['url'];
        $preview = $url;
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://') && ! str_starts_with($url, 'blob:') && $qrCode) {
            $preview = Storage::disk('s3')->url('tours/' . $qrCode . '/' . ltrim($url, '/'));
        }

        return [
            'url' => $url,
            'fileName' => (string) ($media['fileName'] ?? basename($url)),
            'preview' => $preview,
        ];
    }

    /**
     * @param  array<string, mixed>  $slot
     */
    private static function applyMediaActionToSlot(
        array &$slot,
        Request $request,
        string $prefix,
        string $slotKey,
        string $mediaType,
        ?string $qrCode
    ): void {
        $urlKey = $mediaType === 'video' ? 'videoUrl' : 'documentUrl';
        $objectKey = $mediaType;
        $urlInput = $prefix . '_' . $mediaType . '_url';
        $fileInput = $prefix . '_' . $mediaType . '_file';
        $existingInput = $prefix . '_existing_' . $mediaType . '_json';

        $mediaUrl = trim((string) $request->input($urlInput, ''));
        $mediaObject = self::parseExistingMediaJson($request->input($existingInput));

        if ($request->hasFile($fileInput) && $qrCode) {
            $file = $request->file($fileInput);
            if ($file && $file->isValid()) {
                $mediaObject = self::uploadInfoFolderMedia($file, $slotKey, $mediaType, $qrCode);
                $mediaUrl = '';
            }
        }

        if ($mediaUrl !== '') {
            $slot[$urlKey] = $mediaUrl;
        }

        if (is_array($mediaObject) && ! empty($mediaObject['url'])) {
            $slot[$objectKey] = [
                'url' => (string) $mediaObject['url'],
                'fileName' => (string) ($mediaObject['fileName'] ?? basename((string) $mediaObject['url'])),
            ];
        }
    }

    /**
     * @return array{url: string, fileName: string}|null
     */
    private static function parseExistingMediaJson(mixed $jsonRaw): ?array
    {
        if (! is_string($jsonRaw) || trim($jsonRaw) === '' || trim($jsonRaw) === 'null') {
            return null;
        }

        $decoded = json_decode($jsonRaw, true);
        if (! is_array($decoded) || empty($decoded['url'])) {
            return null;
        }

        $url = (string) $decoded['url'];
        if (str_starts_with($url, 'blob:')) {
            return null;
        }

        return [
            'url' => $url,
            'fileName' => (string) ($decoded['fileName'] ?? basename($url)),
        ];
    }

    /**
     * @return array{url: string, fileName: string}
     */
    private static function uploadInfoFolderMedia(
        \Illuminate\Http\UploadedFile $file,
        string $slotKey,
        string $mediaType,
        string $qrCode
    ): array {
        $storedName = $slotKey . '_' . $mediaType . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $filePath = 'tours/' . $qrCode . '/info/' . $storedName;
        Storage::disk('s3')->put($filePath, file_get_contents($file->getRealPath()), [
            'ContentType' => $file->getMimeType() ?: 'application/octet-stream',
        ]);

        return [
            'url' => 'info/' . $storedName,
            'fileName' => $file->getClientOriginalName(),
        ];
    }

    public static function logoPreviewUrl(Tour $tour, ?string $qrCode, mixed $logo): ?string
    {
        if (! is_string($logo) || trim($logo) === '') {
            return null;
        }

        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            return $logo;
        }

        if ($qrCode) {
            return Storage::disk('s3')->url('tours/' . $qrCode . '/' . ltrim($logo, '/'));
        }

        return Storage::disk('s3')->url(ltrim($logo, '/'));
    }

    /**
     * @param  array<string, mixed>  $images
     * @return array<int, array{url: string, fileName: string, preview: string}>
     */
    public static function normalizeImagesForForm(array $images, ?string $qrCode): array
    {
        $out = [];
        foreach ($images as $img) {
            if (! is_array($img) || empty($img['url'])) {
                continue;
            }
            $url = (string) $img['url'];
            $preview = $url;
            if (! str_starts_with($url, 'http') && $qrCode) {
                $preview = Storage::disk('s3')->url('tours/' . $qrCode . '/' . ltrim($url, '/'));
            }
            $out[] = [
                'url' => $url,
                'fileName' => (string) ($img['fileName'] ?? basename($url)),
                'preview' => $preview,
            ];
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    public static function tagSlotMap(): array
    {
        return [
            'sidebar_tag' => 'sidebarTag',
            'sidebar_tag2' => 'sidebarTag2',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function tagFormPrefixes(): array
    {
        return array_keys(self::tagSlotMap());
    }

    public static function normalizeTagSize(mixed $value): string
    {
        $size = strtolower(trim((string) $value));

        return in_array($size, self::TAG_SIZES, true) ? $size : 'small';
    }

    public static function normalizeAction(mixed $value): string
    {
        $action = trim((string) $value);

        return in_array($action, self::ACTION_TYPES, true) ? $action : 'redirectToLink';
    }

    /**
     * @param  array<string, string>  $textMap
     */
    private static function tagSlotHasTextContent(array $textMap): bool
    {
        foreach ($textMap as $value) {
            if (is_string($value) && trim($value) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function parseMenuItemsJson(mixed $jsonRaw): array
    {
        if (! is_string($jsonRaw) || trim($jsonRaw) === '') {
            return [];
        }

        $decoded = json_decode($jsonRaw, true);
        if (! is_array($decoded)) {
            return [];
        }

        $items = [];
        foreach ($decoded as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $title = trim((string) ($row['title'] ?? ''));
            $nodeId = trim((string) ($row['nodeId'] ?? ''));
            if ($title === '' || $nodeId === '') {
                continue;
            }
            $item = [
                'id' => (string) ($row['id'] ?? ('bm-' . time() . '-' . $index)),
                'title' => $title,
                'nodeId' => $nodeId,
                'order' => $index,
            ];
            if (isset($row['targetView']) && is_array($row['targetView']) && $row['targetView'] !== []) {
                $item['targetView'] = $row['targetView'];
            }
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $existingSlot
     * @param  array<string, mixed>  $validated
     * @return array<string, string>
     */
    private static function mergeLocalizedFieldAsObject(
        array $existingSlot,
        string $fieldKey,
        array $validated,
        string $inputKey,
        array $languageCodes
    ): array {
        $existing = LanguageConfigHelper::decodePerLanguageStored($existingSlot[$fieldKey] ?? null);

        return LanguageConfigHelper::mergePerLanguageStringMap(
            $existing,
            is_array($validated[$inputKey] ?? null) ? $validated[$inputKey] : [],
            $languageCodes
        );
    }

    /**
     * @param  array<string, mixed>  $existingSlot
     * @param  array<string, mixed>  $validated
     * @return array<string, string>|string
     */
    private static function mergeLocalizedField(
        array $existingSlot,
        string $fieldKey,
        array $validated,
        string $inputKey,
        array $languageCodes
    ): array|string {
        $existing = LanguageConfigHelper::decodePerLanguageStored($existingSlot[$fieldKey] ?? null);
        $merged = LanguageConfigHelper::mergePerLanguageStringMap(
            $existing,
            is_array($validated[$inputKey] ?? null) ? $validated[$inputKey] : [],
            $languageCodes
        );

        return count($languageCodes) === 1
            ? trim((string) ($merged[$languageCodes[0]] ?? ''))
            : $merged;
    }
}
