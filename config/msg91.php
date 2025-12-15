<?php

return [
    'auth_key' => env('MSG91_AUTH_KEY', ''),
    'sender'   => env('MSG91_SENDER_ID', 'PROPPK'),

    // All Flow Template IDs
    'templates' => [
        'login_otp' => '692962755f7a7c3df13a38b3',
        'registration_otp' => '69295c05f1955a3c3175f2c2',
        'order_confirmation' => '69295ee79cb8142aae77f2a2',
        'payment_failed' => '69295eabe5d99077c61b7ac1',
        'photographer_visit' => '69295e78ac05de097d748e03',
        'appointment_cancelled' => '69295df5c4ff77276a41cfa2',
        'appointment_scheduled' => '69295d82a0f6627e122a0252',
        'work_completed_review' => '69295d3918ff385b39418e42',
        'final_link_delivered' => '69295cd5502ac933f4068bd2',
        'expert_running_late' => '69295c757219e807f10118f2',
        'demo_101' => '69295c757219e807f1011830',
        'demo_103' => '69295c757219e807f1011850',
        'demo_104' => '69295c757219e807f1011860',
        'demo_105' => '69295c757219e807f1011870',
    
    ],
];

