<?php

$config = require __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name($config['session_name']);
    session_start();
}

function portfolioConfig(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/config.php';
    }
    return $cfg;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['portfolio_admin']);
}

function requireAuth(bool $jsonResponse = false): void
{
    if (isLoggedIn()) {
        return;
    }

    if ($jsonResponse) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
        exit;
    }

    $redirect = 'login.php';
    if (!empty($_SERVER['REQUEST_URI'])) {
        $redirect .= '?redirect=' . urlencode($_SERVER['REQUEST_URI']);
    }
    header('Location: ' . $redirect);
    exit;
}

function attemptLogin(string $username, string $password): bool
{
    $cfg = portfolioConfig();
    $ok = hash_equals($cfg['admin_username'], $username)
        && password_verify($password, $cfg['admin_password_hash']);

    if ($ok) {
        session_regenerate_id(true);
        $_SESSION['portfolio_admin'] = $username;
    }

    return $ok;
}

function hasValidApiKey(): bool
{
    $expected = portfolioConfig()['api_key'] ?? '';
    if ($expected === '') {
        return false;
    }
    $provided = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
    return is_string($provided) && hash_equals($expected, $provided);
}

function requireApiAccess(): void
{
    if (isLoggedIn() || hasValidApiKey()) {
        return;
    }

    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Send header X-Api-Key or query api_key.',
    ]);
    exit;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
