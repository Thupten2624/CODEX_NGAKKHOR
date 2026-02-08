<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'Vajrayana Path Tracker',
        // '' si el dominio apunta a /public, '/public' si accedes por /public/index.php
        'base_url' => '',
        'debug' => false,
    ],
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'vajrayana_tracker',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'session_name' => 'vajrayana_session',
    ],
];
