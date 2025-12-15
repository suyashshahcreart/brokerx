<?php

$returnUrlRoute = env('CASHFREE_RETURN_URL', 'frontend.cashfree.callback');
// Convert route name to full URL if it's a route name (not a full URL)
$returnUrl = $returnUrlRoute;
if (strpos($returnUrlRoute, 'http') !== 0 && strpos($returnUrlRoute, '/') !== 0) {
    // Use app URL with a default path if route() is not yet available
    $returnUrl = rtrim(env('APP_URL', ''), '/') . '/cashfree/callback';
}

return [
    'env' => env('CASHFREE_ENV', 'sandbox'),
    'app_id' => env('CASHFREE_APP_ID', ''),
    'secret_key' => env('CASHFREE_SECRET_KEY', ''),
    'api_version' => env('CASHFREE_API_VERSION', '2023-08-01'),
    'timeout' => (int) env('CASHFREE_TIMEOUT', 300),
    'base_url' => env('CASHFREE_BASE_URL','https://sandbox.cashfree.com/pg'),
    // Return URL: full URL (converted from route name stored in .env)
    'return_url' => $returnUrl,
    // Return URL route name (stored in .env as CASHFREE_RETURN_URL)
    'return_url_route' => $returnUrlRoute,
];
