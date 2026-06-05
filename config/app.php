<?php
// config/app.php

// Deteksi base URL secara dinamis
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptName = str_replace('\\', '/', $scriptName);
$baseUrl = $protocol . '://' . $host . ($scriptName === '/' ? '' : $scriptName) . '/';

define('BASE_URL', $baseUrl);
define('APP_NAME', 'Madura Blue Tourism (MBT)');
define('APP_ENV', 'development'); // 'development' atau 'production'

// Setup Error Reporting
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
    
    // Set custom PHP error log location
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// Timezone
date_default_timezone_set('Asia/Jakarta');
