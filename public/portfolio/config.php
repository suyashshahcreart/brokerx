<?php
/**
 * Portfolio API — admin credentials.
 * Default login: admin / portfolio2026
 * Change admin_password_hash after first login (generate with password_hash()).
 */
return [
    'admin_username' => 'admin',
    'admin_password_hash' => '$2y$10$IbUrlEUR.ZyC6o2G.7RPTOQVQY/DypOoKeYkc74N30DMwzxafYjZm',
    'session_name' => 'portfolio_api_session',
    // Required for api.php (header X-Api-Key or ?api_key=). Change in production.
    'api_key' => 'ppk_7f3a9c2e1b8d4f6a0e5c9b2d7h4k1m6n',
];
