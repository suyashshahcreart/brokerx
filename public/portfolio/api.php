<?php
/**
 * Secured read-only Portfolio API (API key or admin session).
 * GET  /api.php?api_key=YOUR_KEY
 * Header: X-Api-Key: YOUR_KEY
 *
 * GET  /api.php?action=filters
 * GET  /api.php?page=1&per_page=6&sort=sr_no&sort_order=asc&property_type=Residential&search=...
 */

require_once __DIR__ . '/auth.php';
requireApiAccess();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Accept, X-Api-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$jsonPath = __DIR__ . '/data/portfolio.json';

if (!file_exists($jsonPath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Portfolio data not found']);
    exit;
}

$data = json_decode(file_get_contents($jsonPath), true);
if (!is_array($data)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Invalid portfolio data']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'filters') {
    echo json_encode([
        'success' => true,
        'data' => [
            'property_types' => array_map(function ($t) {
                return ['name' => $t];
            }, $data['property_types'] ?? []),
            'other_sub_types' => [],
        ],
    ]);
    exit;
}

$items = $data['items'] ?? [];
$settings = $data['settings'] ?? [];

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $q = mb_strtolower($search);
    $items = array_values(array_filter($items, function ($item) use ($q) {
        $hay = mb_strtolower(
            ($item['title'] ?? '') . ' '
            . ($item['property_type'] ?? '') . ' '
            . ($item['property_sub_type'] ?? '')
        );
        return mb_strpos($hay, $q) !== false;
    }));
}

$typeFilter = trim($_GET['property_type'] ?? $_GET['property_type_filter'] ?? '');
if ($typeFilter !== '') {
    $types = array_map('trim', explode(',', $typeFilter));
    $items = array_values(array_filter($items, function ($item) use ($types) {
        return in_array($item['property_type'] ?? '', $types, true);
    }));
}

$sort = $_GET['sort'] ?? ($settings['default_sort'] ?? 'sr_no');
$sortOrder = strtolower($_GET['sort_order'] ?? ($settings['default_sort_order'] ?? 'asc'));
$allowedSort = ['sr_no', 'property_types_sr_no', 'date', 'title'];
if (!in_array($sort, $allowedSort, true)) {
    $sort = 'sr_no';
}
$desc = ($sortOrder === 'desc');

usort($items, function ($a, $b) use ($sort, $desc) {
    $va = $a[$sort] ?? '';
    $vb = $b[$sort] ?? '';
    if ($sort === 'sr_no' || $sort === 'property_types_sr_no') {
        $va = (int) $va;
        $vb = (int) $vb;
    }
    if ($va === $vb) {
        return 0;
    }
    $cmp = ($va < $vb) ? -1 : 1;
    return $desc ? -$cmp : $cmp;
});

$total = count($items);
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : null;
$perPage = isset($_GET['per_page']) ? max(1, min(100, (int) $_GET['per_page'])) : (int) ($settings['default_per_page'] ?? 6);

if ($page !== null) {
    $offset = ($page - 1) * $perPage;
    $paged = array_slice($items, $offset, $perPage);
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $paged,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / max(1, $perPage)),
            ],
            'settings' => $settings,
            'property_types' => $data['property_types'] ?? [],
        ],
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'data' => [
        'settings' => $settings,
        'property_types' => $data['property_types'] ?? [],
        'items' => $items,
    ],
]);
