<?php
// modules/vendors/save_product.php

require_role(['vendor']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'index.php?module=vendors&action=products');
}

// Validasi CSRF
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    $_SESSION['error_message'] = "Token keamanan tidak valid (CSRF).";
    redirect(BASE_URL . 'index.php?module=vendors&action=products');
}

$userId = $_SESSION['user_id'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0.00;
$stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if (empty($name) || $categoryId <= 0 || $price < 0 || $stock < 0 || empty($description)) {
    $_SESSION['error_message'] = "Seluruh kolom input form wajib diisi secara valid.";
    redirect(BASE_URL . 'index.php?module=vendors&action=products');
}

try {
    // 1. Ambil ID vendor
    $stmtVendor = db_query("SELECT id FROM vendor_profile WHERE user_id = :user_id LIMIT 1", ['user_id' => $userId]);
    $vendor = $stmtVendor->fetch();
    
    if (!$vendor) {
        $_SESSION['error_message'] = "Profil vendor tidak ditemukan.";
        redirect(BASE_URL . 'index.php?module=vendors&action=profile');
    }
    
    $vendorId = $vendor['id'];
    
    if ($id > 0) {
        // 2. Mode Update: Pastikan produk milik vendor ini
        $stmtCheck = db_query("SELECT id FROM products WHERE id = :id AND vendor_id = :vendor_id LIMIT 1", [
            'id' => $id,
            'vendor_id' => $vendorId
        ]);
        if (!$stmtCheck->fetch()) {
            $_SESSION['error_message'] = "Akses ditolak atau produk tidak ditemukan.";
            redirect(BASE_URL . 'index.php?module=vendors&action=products');
        }
        
        $sql = "UPDATE products SET 
                category_id = :category_id, 
                name = :name, 
                price = :price, 
                stock = :stock, 
                description = :description 
                WHERE id = :id";
        db_query($sql, [
            'category_id' => $categoryId,
            'name' => $name,
            'price' => $price,
            'stock' => $stock,
            'description' => $description,
            'id' => $id
        ]);
        
        log_info("Vendor User {$userId} updated product ID {$id}: '{$name}'");
        $_SESSION['success_message'] = "Produk '{$name}' berhasil diperbarui.";
    } else {
        // 3. Mode Insert
        $sql = "INSERT INTO products (vendor_id, category_id, name, price, stock, description) 
                VALUES (:vendor_id, :category_id, :name, :price, :stock, :description)";
        db_query($sql, [
            'vendor_id' => $vendorId,
            'category_id' => $categoryId,
            'name' => $name,
            'price' => $price,
            'stock' => $stock,
            'description' => $description
        ]);
        
        log_info("Vendor User {$userId} added a new product: '{$name}'");
        $_SESSION['success_message'] = "Produk '{$name}' baru berhasil ditambahkan.";
    }
    
} catch (PDOException $e) {
    log_error("Database error saving vendor product: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi masalah teknis saat menyimpan produk.";
}

redirect(BASE_URL . 'index.php?module=vendors&action=products');
