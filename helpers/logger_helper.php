<?php
// helpers/logger_helper.php

if (!function_exists('write_log')) {
    function write_log($level, $message) {
        $logDir = __DIR__ . '/../logs';
        $logFile = $logDir . '/app.log';
        $time = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Guest';
        
        $formattedMessage = sprintf("[%s] [%s] [User ID: %s] [IP: %s] %s\n", $time, strtoupper($level), $userId, $ip, $message);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        error_log($formattedMessage, 3, $logFile);
    }
}

if (!function_exists('log_info')) {
    function log_info($msg) { write_log('info', $msg); }
}

if (!function_exists('log_warning')) {
    function log_warning($msg) { write_log('warning', $msg); }
}

if (!function_exists('log_error')) {
    function log_error($msg) { write_log('error', $msg); }
}

if (!function_exists('log_critical')) {
    function log_critical($msg) { write_log('critical', $msg); }
}
