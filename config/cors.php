<?php

return [

    // 'paths' => ['api/*', 'sanctum/csrf-cookie','login'],
'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'dashboard'],
    
    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3000'], // Next.js URL

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // ✅ crucial for cookies

];
