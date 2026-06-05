<?php
// helpers/auth_helper.php

if (!function_exists('start_secure_session')) {
    function start_secure_session() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_lifetime' => 86400,
                'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
                'use_only_cookies' => true
            ]);
        }
        
        // Prevent session fixation
        if (!isset($_SESSION['last_regeneration'])) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

if (!function_exists('login_user')) {
    function login_user($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // 'admin', 'vendor', 'buyer', 'wisatawan'
        $_SESSION['logged_in'] = true;
    }
}

if (!function_exists('logout_user')) {
    function logout_user() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}

if (!function_exists('is_authenticated')) {
    function is_authenticated() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
}

if (!function_exists('require_auth')) {
    function require_auth() {
        if (!is_authenticated()) {
            header("Location: " . BASE_URL . "index.php?module=auth&action=login");
            exit;
        }
    }
}

if (!function_exists('require_role')) {
    function require_role(array $roles) {
        require_auth();
        if (!in_array($_SESSION['role'], $roles)) {
            // Load 403 Page
            http_response_code(403);
            if (file_exists(__DIR__ . '/../modules/errors/403.php')) {
                require_once __DIR__ . '/../modules/errors/403.php';
            } else {
                echo "403 Forbidden - Anda tidak memiliki akses ke halaman ini.";
            }
            exit;
        }
    }
}
