<?php
// modules/auth/process_login.php
// BahariChain: Login dengan RBAC (Role-Based Access Control)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'index.php?module=auth&action=login');
}

// 1. CSRF TOKEN VALIDATION
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    $_SESSION['error_message'] = "Token keamanan tidak valid (CSRF). Silakan coba lagi.";
    redirect(BASE_URL . 'index.php?module=auth&action=login');
}

$usernameInput = isset($_POST['username']) ? trim($_POST['username']) : '';
$passwordInput = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($usernameInput) || empty($passwordInput)) {
    $_SESSION['error_message'] = "Username/Email dan Password wajib diisi.";
    redirect(BASE_URL . 'index.php?module=auth&action=login');
}

try {
    // 2. QUERY WITH ROLE JOIN - Get user with role information from roles table
    // This implements proper RBAC by fetching nama_role from roles table
    $sql = "SELECT u.*, r.nama_role 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE (u.username = :username OR u.email = :email) 
            LIMIT 1";
    
    $stmt = db_query($sql, [
        'username' => $usernameInput,
        'email'    => $usernameInput
    ]);

    $user = $stmt->fetch();

    // 3. VERIFY USER & PASSWORD
    if ($user && password_verify($passwordInput, $user['password'])) {
        // Login successful - store user data in session
        login_user($user);
        
        log_info("User '{$user['username']}' (ID: {$user['id']}) logged in. Role: {$user['nama_role']}");

        // 4. REDIRECT BERDASARKAN ROLE
        redirect_by_role();
    } else {
        log_warning("Failed login attempt for: '{$usernameInput}'");
        $_SESSION['error_message'] = "Username atau password salah.";
        redirect(BASE_URL . 'index.php?module=auth&action=login');
    }
} catch (PDOException $e) {
    log_error("Database error during login: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi masalah teknis pada server. Silakan coba sesaat lagi.";
    redirect(BASE_URL . 'index.php?module=auth&action=login');
}
