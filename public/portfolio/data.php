<?php
/**
 * Portfolio data endpoint - reads from JSON file
 * Supports: filters, list (filter/sort/paginate), export from database
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$dataDir = __DIR__ . '/data';
$jsonPath = $dataDir . '/portfolio.json';

function loadPortfolioJson($path) {
    if (!file_exists($path)) {
        return [
            'settings' => ['default_per_page' => 6, 'default_sort' => 'sr_no', 'default_sort_order' => 'desc'],
            'property_types' => ['Residential', 'Commercial', 'Hospitality', 'Industries', 'Religious', 'Spaces', 'Healthcare', 'Heritage'],
            'items' => []
        ];
    }
    $json = json_decode(file_get_contents($path), true);
    return is_array($json) ? $json : ['settings' => [], 'property_types' => [], 'items' => []];
}

function savePortfolioJson($path, $data) {
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// --- Export from Database ---
if ($action === 'export') {
    $envPath = dirname(__DIR__, 2) . '/.env';
    $env = [];
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $val) = explode('=', $line, 2);
                $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
            }
        }
    }

    $dbHost = $env['DB_HOST'] ?? '127.0.0.1';
    $dbPort = $env['DB_PORT'] ?? '3306';
    $dbName = $env['DB_DATABASE'] ?? 'prop_pik';
    $dbUser = $env['DB_USERNAME'] ?? 'root';
    $dbPass = $env['DB_PASSWORD'] ?? '';
    $awsUrl = $env['AWS_URL'] ?? '';

    try {
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    $listSql = "
        SELECT b.id, b.user_id, pt.name as property_type_name, pst.name as property_sub_type_name,
               t.title, t.tour_thumbnail, t.hosted_link, t.is_hosted, t.slug, t.location
        FROM bookings b
        LEFT JOIN property_types pt ON pt.id = b.property_type_id
        LEFT JOIN property_sub_types pst ON pst.id = b.property_sub_type_id
        LEFT JOIN (
            SELECT t1.* FROM tours t1
            INNER JOIN (SELECT booking_id, MAX(id) as max_id FROM tours WHERE deleted_at IS NULL GROUP BY booking_id) t2
            ON t1.booking_id = t2.booking_id AND t1.id = t2.max_id
            WHERE t1.deleted_at IS NULL
        ) t ON t.booking_id = b.id
        WHERE b.deleted_at IS NULL
        ORDER BY b.id DESC
    ";
    $stmt = $pdo->query($listSql);
    $rows = $stmt->fetchAll();

    $ftpStmt = $pdo->query("SELECT category_name, main_url, remote_path_pattern, url_pattern FROM ftp_configurations WHERE deleted_at IS NULL");
    $ftpConfigs = [];
    while ($row = $ftpStmt->fetch()) {
        $ftpConfigs[$row['category_name']] = $row;
    }

    function getThumbnailUrl($thumb, $awsUrl) {
        if (empty($thumb)) return null;
        if (preg_match('#^https?://#i', $thumb)) return $thumb;
        if (!empty($awsUrl)) return rtrim($awsUrl, '/') . '/' . ltrim($thumb, '/');
        return $thumb;
    }

    function getLiveUrl($r, $ftpConfigs) {
        if (!empty($r['is_hosted']) && !empty($r['hosted_link'])) return $r['hosted_link'];
        if (empty($r['location']) || empty($r['slug']) || empty($r['user_id'])) return '#';
        $cfg = $ftpConfigs[$r['location']] ?? null;
        if (!$cfg) return '#';
        $rp = str_replace(['{customer_id}', '{slug}'], [$r['user_id'], $r['slug']], $cfg['remote_path_pattern'] ?? '{customer_id}/{slug}/index.php');
        $url = str_replace(['{main_url}', '{remote_path}'], [$cfg['main_url'], $rp], $cfg['url_pattern'] ?? 'https://{main_url}/{remote_path}');
        return preg_replace('#/index\.php$#', '', rtrim($url, '/'));
    }

    $items = [];
    $propertyTypes = [];
    $srNo = 1;
    foreach ($rows as $r) {
        $liveUrl = getLiveUrl($r, $ftpConfigs);
        $type = $r['property_type_name'] ?? $r['property_sub_type_name'] ?? 'Other';
        if ($type && !in_array($type, $propertyTypes)) {
            $propertyTypes[] = $type;
        }
        $items[] = [
            'id' => 'db-' . $r['id'],
            'sr_no' => $srNo++,
            'title' => $r['title'] ?? 'Untitled',
            'property_type' => $r['property_type_name'] ?? $r['property_sub_type_name'] ?? 'Other',
            'property_sub_type' => $r['property_sub_type_name'] ?? '',
            'thumbnail' => getThumbnailUrl($r['tour_thumbnail'] ?? null, $awsUrl),
            'tour_live_link' => $liveUrl,
            'date' => date('Y-m-d'),
        ];
    }

    $existing = loadPortfolioJson($jsonPath);
    $allTypes = array_merge($existing['property_types'] ?? [], $propertyTypes);
    $allTypes = array_values(array_unique($allTypes));
    sort($allTypes);

    $data = [
        'settings' => $existing['settings'] ?? ['default_per_page' => 6, 'default_sort' => 'sr_no', 'default_sort_order' => 'desc'],
        'property_types' => $allTypes,
        'items' => $items,
    ];

    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    if (savePortfolioJson($jsonPath, $data)) {
        echo json_encode(['success' => true, 'message' => 'Exported ' . count($items) . ' items to portfolio.json']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to write portfolio.json']);
    }
    exit;
}

// --- Filters ---
if ($action === 'filters') {
    $data = loadPortfolioJson($jsonPath);
    $types = $data['property_types'] ?? [];
    $propertyTypes = [];
    $otherSubTypes = [];
    $knownMain = ['Residential', 'Commercial'];
    foreach ($types as $i => $name) {
        $arr = is_array($name) ? $name : ['id' => $i + 1, 'name' => $name, 'type' => in_array($name, $knownMain) ? 'property_type' : 'property_sub_type'];
        if (is_string($name)) {
            $arr = ['id' => $i + 1, 'name' => $name, 'type' => in_array($name, $knownMain) ? 'property_type' : 'property_sub_type'];
        }
        if (in_array($arr['name'], $knownMain)) {
            $propertyTypes[] = $arr;
        } else {
            $arr['property_type_id'] = 3;
            $otherSubTypes[] = $arr;
        }
    }
    echo json_encode([
        'success' => true,
        'data' => [
            'property_types' => $propertyTypes,
            'other_sub_types' => $otherSubTypes,
        ],
    ]);
    exit;
}

// --- List ---
$data = loadPortfolioJson($jsonPath);
$items = $data['items'] ?? [];
$settings = $data['settings'] ?? [];

$propertyTypeFilter = $_GET['property_type_filter'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : null;
$perPage = isset($_GET['per_page']) ? min(max((int)($_GET['per_page'] ?? $settings['default_per_page'] ?? 6), 1), 100) : ($settings['default_per_page'] ?? 6);
$sort = $_GET['sort'] ?? $settings['default_sort'] ?? 'sr_no';
$sortOrder = strtolower($_GET['sort_order'] ?? $settings['default_sort_order'] ?? 'asc');

$filterNames = [];
if ($propertyTypeFilter) {
    $filterNames = array_map('trim', explode(',', $propertyTypeFilter));
    $filterNames = array_filter($filterNames);
}

if (!empty($filterNames)) {
    $items = array_filter($items, function ($item) use ($filterNames) {
        $pt = $item['property_type'] ?? '';
        $pst = $item['property_sub_type'] ?? '';
        return in_array($pt, $filterNames) || in_array($pst, $filterNames);
    });
    $items = array_values($items);
}

$sortBy = in_array($sort, ['sr_no', 'date']) ? $sort : 'sr_no';
$asc = ($sortOrder === 'asc');
usort($items, function ($a, $b) use ($sortBy, $asc) {
    $va = $a[$sortBy] ?? ($sortBy === 'date' ? '' : 0);
    $vb = $b[$sortBy] ?? ($sortBy === 'date' ? '' : 0);
    $cmp = $sortBy === 'date' ? strcmp($va, $vb) : (($va <=> $vb));
    return $asc ? $cmp : -$cmp;
});

$total = count($items);
$offset = $limit = null;
if ($page !== null && $page > 0) {
    $offset = ($page - 1) * $perPage;
    $limit = $perPage;
}

$out = $items;
if ($limit !== null) {
    $out = array_slice($items, $offset, $limit);
}

$outData = [];
foreach ($out as $item) {
    $outData[] = [
        'booking_id' => $item['id'] ?? 0,
        'property_type' => $item['property_type'] ?? null,
        'property_sub_type' => $item['property_sub_type'] ?? null,
        'booking_live_link' => $item['tour_live_link'] ?? '#',
        'sr_no' => $item['sr_no'] ?? 0,
        'date' => $item['date'] ?? null,
        'tour' => [
            'title' => $item['title'] ?? 'Untitled',
            'name' => $item['title'] ?? 'Untitled',
            'tour_thumbnail' => $item['thumbnail'] ?? null,
            'tour_live_link' => $item['tour_live_link'] ?? '#',
            'hosted_link' => $item['tour_live_link'] ?? null,
        ],
    ];
}

$response = ['success' => true, 'data' => $outData];

if ($page !== null) {
    $totalPages = max(1, $perPage > 0 ? (int)ceil($total / $perPage) : 1);
    $response['meta'] = [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $page,
        'total_pages' => $totalPages,
    ];
    $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . dirname($_SERVER['SCRIPT_NAME']) . '/data.php';
    $q = ['page' => 1, 'per_page' => $perPage];
    if ($propertyTypeFilter) $q['property_type_filter'] = $propertyTypeFilter;
    if ($sort !== 'sr_no') $q['sort'] = $sort;
    if ($sortOrder !== 'asc') $q['sort_order'] = $sortOrder;
    $response['links'] = [
        'first' => $base . '?' . http_build_query(array_merge($q, ['page' => 1])),
        'last' => $base . '?' . http_build_query(array_merge($q, ['page' => $totalPages])),
        'prev' => $page > 1 ? $base . '?' . http_build_query(array_merge($q, ['page' => $page - 1])) : null,
        'next' => $page < $totalPages ? $base . '?' . http_build_query(array_merge($q, ['page' => $page + 1])) : null,
    ];
} else {
    $response['meta'] = ['total' => count($outData)];
}

echo json_encode($response);
