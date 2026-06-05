<?php
// modules/destinations/save.php

require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'index.php?module=destinations&action=manage');
}

// Validasi CSRF
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    $_SESSION['error_message'] = "Token keamanan tidak valid (CSRF). Silakan coba lagi.";
    redirect(BASE_URL . 'index.php?module=destinations&action=manage');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$location = isset($_POST['location']) ? trim($_POST['location']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if (empty($name) || empty($location) || empty($description)) {
    $_SESSION['error_message'] = "Semua kolom input wajib diisi.";
    redirect(BASE_URL . 'index.php?module=destinations&action=manage');
}

try {
    if ($id > 0) {
        // Mode Update
        $sql = "UPDATE destinations SET name = :name, location = :location, description = :description WHERE id = :id";
        db_query($sql, [
            'name' => $name,
            'location' => $location,
            'description' => $description,
            'id' => $id
        ]);
        log_info("Admin updated destination ID {$id}: '{$name}'");
        $_SESSION['success_message'] = "Destinasi '{$name}' berhasil diperbarui.";
    } else {
        // Mode Insert
        $sql = "INSERT INTO destinations (name, location, description, image_path) VALUES (:name, :location, :description, :image_path)";
        db_query($sql, [
            'name' => $name,
            'location' => $location,
            'description' => $description,
            'image_path' => 'assets/images/talang_siring.jpg' // Default placeholder image
        ]);
        log_info("Admin added a new destination: '{$name}'");
        $_SESSION['success_message'] = "Destinasi '{$name}' baru berhasil ditambahkan.";
    }
    
    redirect(BASE_URL . 'index.php?module=destinations&action=manage');
} catch (PDOException $e) {
    log_error("Database error while saving destination: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi masalah teknis saat menyimpan data ke database.";
    redirect(BASE_URL . 'index.php?module=destinations&action=manage');
}
