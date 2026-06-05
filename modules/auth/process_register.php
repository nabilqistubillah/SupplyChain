<?php
// modules/auth/process_register.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'index.php?module=auth&action=register');
}

// 1. Validasi CSRF
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    $_SESSION['error_message'] = "Token keamanan tidak valid (CSRF). Silakan coba lagi.";
    redirect(BASE_URL . 'index.php?module=auth&action=register');
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : 'wisatawan';

// 2. Validasi Kolom Input
if (empty($username) || empty($email) || empty($password) || empty($role)) {
    $_SESSION['error_message'] = "Semua kolom wajib diisi.";
    redirect(BASE_URL . 'index.php?module=auth&action=register');
}

if (strlen($password) < 6) {
    $_SESSION['error_message'] = "Password minimal harus 6 karakter.";
    redirect(BASE_URL . 'index.php?module=auth&action=register');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "Format alamat email tidak valid.";
    redirect(BASE_URL . 'index.php?module=auth&action=register');
}

// Validasi white-list role untuk mencegah pengubahan data request
$allowedRoles = ['wisatawan', 'buyer', 'vendor'];
if (!in_array($role, $allowedRoles)) {
    $_SESSION['error_message'] = "Role pengguna tidak valid.";
    redirect(BASE_URL . 'index.php?module=auth&action=register');
}

try {
    // 3. Periksa keunikan Username dan Email
    $sqlCheck = "SELECT COUNT(*) as total FROM users WHERE username = :username OR email = :email";
    $stmtCheck = db_query($sqlCheck, ['username' => $username, 'email' => $email]);
    $result = $stmtCheck->fetch();
    
    if ($result['total'] > 0) {
        $_SESSION['error_message'] = "Username atau Email sudah terdaftar dalam sistem.";
        redirect(BASE_URL . 'index.php?module=auth&action=register');
    }
    
    // 4. Buat password hash secara aman
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    // Mulai transaksi database untuk menjamin integritas data (terutama untuk vendor)
    $db = Database::getConnection();
    $db->beginTransaction();
    
    // 5. Masukkan user baru ke tabel `users`
    $sqlInsertUser = "INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)";
    $stmtUser = $db->prepare($sqlInsertUser);
    $stmtUser->execute([
        'username' => $username,
        'password' => $passwordHash,
        'email' => $email,
        'role' => $role
    ]);
    
    $userId = $db->lastInsertId();
    
    // 6. Jika daftar sebagai vendor, buat profil default secara otomatis (One-to-One Users ke Vendor_Profile)
    if ($role === 'vendor') {
        $sqlInsertVendor = "INSERT INTO vendor_profile (user_id, business_name, address, contact) VALUES (:user_id, :business_name, :address, :contact)";
        $stmtVendor = $db->prepare($sqlInsertVendor);
        $stmtVendor->execute([
            'user_id' => $userId,
            'business_name' => 'UMKM ' . ucfirst($username),
            'address' => '',
            'contact' => ''
        ]);
    }
    
    // Commit transaksi
    $db->commit();
    
    log_info("User '{$username}' successfully registered as {$role}.");
    
    $_SESSION['success_message'] = "Registrasi sukses! Silakan masuk dengan akun Anda.";
    redirect(BASE_URL . 'index.php?module=auth&action=login');
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    log_error("Database error during registration: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi masalah teknis saat menyimpan data. Silakan coba lagi.";
    redirect(BASE_URL . 'index.php?module=auth&action=register');
}
