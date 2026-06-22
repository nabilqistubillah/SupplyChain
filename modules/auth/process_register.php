<?php
// modules/auth/process_register.php
// BahariChain: Registrasi User dengan RBAC (Role-Based Access Control)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'index.php?module=auth&action=register');
}

// 1. CSRF TOKEN VALIDATION
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    $_SESSION['error_message'] = "Token keamanan tidak valid (CSRF). Silakan coba lagi.";
    redirect(BASE_URL . 'index.php?module=auth&action=register');
}

// 2. COLLECT & TRIM INPUT
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : 'wisatawan';

// 3. VALIDATE INPUT FIELDS
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

// CRITICAL: BahariChain only allows 'wisatawan' and 'pengelola_wisata' roles
// Removed: buyer, vendor (e-commerce roles)
$allowedRoles = ['wisatawan', 'pengelola_wisata'];
if (!in_array($role, $allowedRoles)) {
    $_SESSION['error_message'] = "Role pengguna tidak valid. Hanya 'wisatawan' atau 'pengelola_wisata' yang diperbolehkan.";
    redirect(BASE_URL . 'index.php?module=auth&action=register');
}

try {
    $db = Database::getConnection();
    
    // 4. CHECK DUPLICATE USERNAME & EMAIL
    $sqlCheck = "SELECT COUNT(*) as total FROM users WHERE username = :username OR email = :email";
    $stmtCheck = db_query($sqlCheck, ['username' => $username, 'email' => $email]);
    $result = $stmtCheck->fetch();
    
    if ($result['total'] > 0) {
        $_SESSION['error_message'] = "Username atau Email sudah terdaftar dalam sistem.";
        redirect(BASE_URL . 'index.php?module=auth&action=register');
    }
    
    // 5. GET ROLE_ID FROM ROLES TABLE (RBAC Implementation)
    $sqlGetRoleId = "SELECT id FROM roles WHERE nama_role = :nama_role LIMIT 1";
    $stmtGetRole = db_query($sqlGetRoleId, ['nama_role' => $role]);
    $roleData = $stmtGetRole->fetch();
    
    if (!$roleData) {
        log_error("Role '{$role}' not found in roles table during registration.");
        $_SESSION['error_message'] = "Role tidak ditemukan dalam sistem. Hubungi administrator.";
        redirect(BASE_URL . 'index.php?module=auth&action=register');
    }
    
    $roleId = $roleData['id'];
    
    // 6. HASH PASSWORD (BCRYPT)
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    // 7. BEGIN TRANSACTION
    $db->beginTransaction();
    
    // 8. INSERT NEW USER WITH role_id FOREIGN KEY (No e-commerce vendor_profile)
    $sqlInsertUser = "INSERT INTO users (role_id, username, password, email, created_at) 
                      VALUES (:role_id, :username, :password, :email, NOW())";
    $stmtUser = $db->prepare($sqlInsertUser);
    $stmtUser->execute([
        'role_id' => $roleId,
        'username' => $username,
        'password' => $passwordHash,
        'email' => $email
    ]);
    
    $userId = $db->lastInsertId();
    
    // REMOVED: vendor_profile creation (E-commerce feature)
    // BahariChain does not have vendor profiles - only roles
    
    // 9. COMMIT TRANSACTION
    $db->commit();
    
    log_info("User '{$username}' successfully registered as role '{$role}' with role_id={$roleId}.");
    
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
