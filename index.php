<?php
// index.php - Front Controller BahariChain

// 1. Load Configurations & Helpers
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/db_helper.php';
require_once __DIR__ . '/helpers/auth_helper.php';
require_once __DIR__ . '/helpers/security_helper.php';
require_once __DIR__ . '/helpers/logger_helper.php';
require_once __DIR__ . '/helpers/response_helper.php';

// 2. Start Secure Session
start_secure_session();

// 3. Define Clean Routing Parameters
$module = isset($_GET['module']) ? trim($_GET['module']) : 'dashboard';
$action = isset($_GET['action']) ? trim($_GET['action']) : 'index';

// Sanitasi parameter input route untuk mencegah directory traversal
$module = preg_replace('/[^a-zA-Z0-9_-]/', '', $module);
$action = preg_replace('/[^a-zA-Z0-9_-]/', '', $action);

// 4. White-listed Routes
$routes = [
    // ── Public / Auth ──────────────────────────────────────────────
    'auth'        => ['login', 'register', 'logout', 'process_login', 'process_register'],

    // ── Legacy public pages (kept for compatibility) ────────────────
    'dashboard'   => ['index'],

    // ── Administrator ───────────────────────────────────────────────
    'admin'       => [
        'dashboard',
        'users',
        'destinations',
        'packages',
        'payments',
        'reviews',
        'reports',
    ],

    // ── Pengelola Wisata ─────────────────────────────────────────────
    'pengelola'   => [
        'dashboard',
        'destinations',
        "destinations_create",
        'packages',
        'transportation',
        'reservations',
        'reports',
        'reviews',
    ],

    // ── Wisatawan ────────────────────────────────────────────────────
    'wisatawan'   => [
        'dashboard',
        'destinations',
        'packages',
        'reservations',
        'payment',
        'notifications',
        'reviews',
        'profile',
    ],

    // ── Error pages ──────────────────────────────────────────────────
    'errors'      => ['403', '404', '500'],
];

// 5. Route Execution
if (array_key_exists($module, $routes) && in_array($action, $routes[$module])) {
    $moduleFile = __DIR__ . "/modules/{$module}/{$action}.php";

    if (file_exists($moduleFile)) {
        try {
            require_once $moduleFile;
        } catch (Throwable $e) {
            log_critical("Unhandled Exception in {$module}/{$action}: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            http_response_code(500);
            require_once __DIR__ . '/modules/errors/500.php';
        }
    } else {
        log_error("Module file not found: {$moduleFile}");
        http_response_code(404);
        require_once __DIR__ . '/modules/errors/404.php';
    }
} else {
    // Fallback: jika modul tidak dikenali, arahkan ke dashboard sesuai role
    if (is_authenticated()) {
        redirect_by_role();
    } else {
        redirect(BASE_URL . 'index.php?module=auth&action=login');
    }
}
