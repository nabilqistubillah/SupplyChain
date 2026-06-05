<?php
// helpers/security_helper.php

if (!function_exists('esc')) {
    function esc($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            return '';
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token($token) {
        if (session_status() === PHP_SESSION_NONE) {
            return false;
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        $token = generate_csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . esc($token) . '">';
    }
}
