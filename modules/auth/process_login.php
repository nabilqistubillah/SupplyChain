<?php
// modules/auth/process_login.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'index.php?module=auth&action=login');
}

// 1. Validasi CSRF Token
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
    // 2. Cari user berdasarkan username atau email
    $sql  = "SELECT * FROM users WHERE (username = :username OR email = :email) AND status = 'active' LIMIT 1";
    $stmt = db_query($sql, [
        'username' => $usernameInput,
        'email'    => $usernameInput
    ]);

    $user = $stmt->fetch();

    // 3. Verifikasi user dan password
    if ($user && password_verify($passwordInput, $user['password'])) {
        login_user($user);
        log_info("User '{$user['username']}' logged in. Role: {$user['role']}");

        // 4. Redirect berdasarkan role
        redirect_by_role();
    } else {
        log_warning("Failed login attempt for: '{$usernameInput}'");
        $_SESSION['error_message'] = "Username atau password salah.";
        redirect(BASE_URL . 'index.php?module=auth&action=login');
    }
} catch (PDOException $e) {
    // Fallback: jika kolom status belum ada (DB lama), coba tanpa filter status
    try {
        $sql2  = "SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1";
        $stmt2 = db_query($sql2, ['username' => $usernameInput, 'email' => $usernameInput]);
        $user2 = $stmt2->fetch();
        if ($user2 && password_verify($passwordInput, $user2['password'])) {
            login_user($user2);
            log_info("User '{$user2['username']}' logged in (legacy). Role: {$user2['role']}");
            redirect_by_role();
        } else {
            $_SESSION['error_message'] = "Username atau password salah.";
            redirect(BASE_URL . 'index.php?module=auth&action=login');
        }
    } catch (PDOException $e2) {
        log_error("Database error during login: " . $e2->getMessage());
        $_SESSION['error_message'] = "Terjadi masalah teknis pada server. Silakan coba sesaat lagi.";
        redirect(BASE_URL . 'index.php?module=auth&action=login');
    }
}
