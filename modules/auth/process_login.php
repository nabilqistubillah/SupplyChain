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
    $sql = "SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1";
    $stmt = db_query($sql, [
        'username' => $usernameInput,
        'email' => $usernameInput
    ]);
    
    $user = $stmt->fetch();
    
    // 3. Verifikasi user dan password
    if ($user && password_verify($passwordInput, $user['password'])) {
        // Login berhasil
        login_user($user);
        log_info("User '{$user['username']}' logged in successfully. Role: {$user['role']}");
        
        // Arahkan ke dashboard utama
        redirect(BASE_URL . 'index.php');
    } else {
        // Gagal login
        log_warning("Failed login attempt for username: '{$usernameInput}'");
        $_SESSION['error_message'] = "Username atau password salah.";
        redirect(BASE_URL . 'index.php?module=auth&action=login');
    }
} catch (PDOException $e) {
    log_error("Database error during login process: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi masalah teknis pada server. Silakan coba sesaat lagi.";
    redirect(BASE_URL . 'index.php?module=auth&action=login');
}
