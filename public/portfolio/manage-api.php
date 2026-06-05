<?php
/**
 * Manage API — load/save `data/portfolio.json` (login required).
 *
 * Each item may include:
 * - `sr_no` — global order for the full portfolio (all items).
 * - `property_types_sr_no` — order within `property_type` (used when filtered by type).
 */

require_once __DIR__ . '/auth.php';
requireAuth(true);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$dataDir = __DIR__ . '/data';
$jsonPath = $dataDir . '/portfolio.json';

function loadJson($path) {
    if (!file_exists($path)) {
        return [
            'settings' => ['default_per_page' => 6, 'default_sort' => 'sr_no', 'default_sort_order' => 'desc'],
            'property_types' => ['Residential', 'Commercial', 'Hospitality', 'Industries', 'Religious', 'Spaces', 'Healthcare', 'Heritage'],
            'items' => []
        ];
    }
    $j = json_decode(file_get_contents($path), true);
    return is_array($j) ? $j : ['settings' => [], 'property_types' => [], 'items' => []];
}

function saveJson($path, $data) {
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function validateStructure($data) {
    if (!is_array($data)) return false;
    if (!isset($data['settings']) || !is_array($data['settings'])) return false;
    if (!isset($data['property_types']) || !is_array($data['property_types'])) return false;
    if (!isset($data['items']) || !is_array($data['items'])) return false;
    return true;
}

// GET - return full JSON
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = loadJson($jsonPath);
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$action = $input['action'] ?? '';

if ($action === 'save') {
    $payload = $input['data'] ?? $input;
    if (empty($payload)) {
        echo json_encode(['success' => false, 'message' => 'No data provided']);
        exit;
    }
    if (is_string($payload)) {
        $payload = json_decode($payload, true);
    }
    if (!validateStructure($payload)) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON structure']);
        exit;
    }
    if (saveJson($jsonPath, $payload)) {
        echo json_encode(['success' => true, 'message' => 'Saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to write file']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
