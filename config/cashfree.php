<?php

return [
    'env' => env('CASHFREE_ENV', 'sandbox'),
    'app_id' => env('CASHFREE_APP_ID', ''),
    'secret_key' => env('CASHFREE_SECRET_KEY', ''),
    'api_version' => env('CASHFREE_API_VERSION', '2023-08-01'),
    'timeout' => (int) env('CASHFREE_TIMEOUT', 300),
    'base_url' => env('CASHFREE_BASE_URL','https://sandbox.cashfree.com/pg'),
    'return_url' => env('CASHFREE_RETURN_URL','http://localhost/brokerx/frontend/setup/payment/callback'),
     
];
