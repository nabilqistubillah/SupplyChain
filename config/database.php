<?php
// config/database.php
// Database Configuration for BahariChain Platform

return [
    'host'    => '127.0.0.1',
    'port'    => 3307,                  // Custom MySQL port (default 3306 had issues)
    'db'      => 'baharichain_db',      // BahariChain database
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
];
