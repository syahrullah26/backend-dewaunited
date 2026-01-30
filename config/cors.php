<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'user'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3001',
        'https://dewaunitedstore.sgp.dom.my.id',
        'https://admin-dewaunited.vercel.app',
        'https://dewaunitedstore.vercel.app',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
