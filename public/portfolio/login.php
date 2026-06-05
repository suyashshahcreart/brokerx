<?php
require_once __DIR__ . '/auth.php';

if (isLoggedIn()) {
    header('Location: manage.php');
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? 'manage.php';
if (!is_string($redirect) || $redirect === '' || strpos($redirect, '..') !== false || preg_match('#^https?://#i', $redirect)) {
    $redirect = 'manage.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'manage.php';

    if (attemptLogin($username, $password)) {
        header('Location: ' . $redirect);
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Portfolio Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-1 text-center">Portfolio Manager</h1>
                        <p class="text-muted text-center small mb-4">Sign in to manage portfolio.json</p>
                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="post" autocomplete="off">
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                            <div class="mb-3">
                                <label class="form-label" for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Log in</button>
                        </form>
                    </div>
                </div>
                <p class="text-center text-muted small mt-3 mb-0">
                    Portfolio data is served via <a href="index.php">api.php</a> (API key required).
                </p>
            </div>
        </div>
    </div>
</body>
</html>
