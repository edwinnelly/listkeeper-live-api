<?php

// return [
// 'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
//     'allowed_methods' => ['*'],

//     'allowed_origins' => ['http://localhost:3000'], // Next.js URL

//     'allowed_origins_patterns' => [],

//     'allowed_headers' => ['*'],

//     'exposed_headers' => [],

//     'max_age' => 0,

//     'supports_credentials' => true, // ✅ crucial for cookies

// ];

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // ✅ Allow both localhost and 127.0.0.1
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ✅ REQUIRED for Sanctum cookies
    'supports_credentials' => true,

];
