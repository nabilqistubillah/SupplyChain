<?php
// modules/vendors/delete_product.php

require_role(['vendor']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user_id'];

if ($id <= 0) {
    redirect(BASE_URL . 'index.php?module=vendors&action=products');
}

try {
    // 1. Ambil ID vendor
    $stmtVendor = db_query("SELECT id FROM vendor_profile WHERE user_id = :user_id LIMIT 1", ['user_id' => $userId]);
    $vendor = $stmtVendor->fetch();
    
    if ($vendor) {
        $vendorId = $vendor['id'];
        
        // 2. Cek eksistensi produk dan kepemilikan vendor (security verification)
        $stmtProd = db_query("SELECT name FROM products WHERE id = :id AND vendor_id = :vendor_id LIMIT 1", [
            'id' => $id,
            'vendor_id' => $vendorId
        ]);
        $prod = $stmtProd->fetch();
        
        if ($prod) {
            $name = $prod['name'];
            
            // 3. Hapus produk
            db_query("DELETE FROM products WHERE id = :id", ['id' => $id]);
            
            log_info("Vendor User {$userId} deleted product ID {$id}: '{$name}'");
            $_SESSION['success_message'] = "Produk '{$name}' berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Produk tidak ditemukan atau hak akses ditolak.";
        }
    } else {
        $_SESSION['error_message'] = "Profil vendor belum lengkap.";
    }
} catch (PDOException $e) {
    log_error("Database error deleting product ID {$id}: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi masalah teknis saat menghapus produk.";
}

redirect(BASE_URL . 'index.php?module=vendors&action=products');
