<?php
// modules/orders/add_to_cart.php

require_role(['buyer']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'index.php?module=destinations');
}

// CSRF validation
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    $_SESSION['error_message'] = "Token keamanan kedaluwarsa. Silakan coba lagi.";
    redirect(BASE_URL . 'index.php?module=destinations');
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$destId = isset($_POST['destination_id']) ? (int)$_POST['destination_id'] : 0;

if ($productId <= 0) {
    redirect(BASE_URL . 'index.php?module=destinations');
}

try {
    // Cek apakah produk valid dan stoknya cukup
    $stmt = db_query("SELECT name, stock FROM products WHERE id = :id LIMIT 1", ['id' => $productId]);
    $product = $stmt->fetch();
    
    if ($product) {
        if ($product['stock'] > 0) {
            // Inisialisasi keranjang jika belum ada
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            // Tambahkan kuantitas ke keranjang
            if (isset($_SESSION['cart'][$productId])) {
                if ($_SESSION['cart'][$productId] < $product['stock']) {
                    $_SESSION['cart'][$productId]++;
                } else {
                    $_SESSION['cart_message'] = "Stok produk '" . esc($product['name']) . "' tidak mencukupi untuk ditambah lagi.";
                    redirect(BASE_URL . "index.php?module=destinations&action=detail&id=" . $destId);
                }
            } else {
                $_SESSION['cart'][$productId] = 1;
            }
            
            $_SESSION['cart_message'] = "Produk '" . esc($product['name']) . "' berhasil ditambahkan ke keranjang B2B Anda!";
        } else {
            $_SESSION['cart_message'] = "Maaf, stok produk '" . esc($product['name']) . "' sedang habis.";
        }
    } else {
        $_SESSION['cart_message'] = "Produk tidak ditemukan.";
    }
} catch (PDOException $e) {
    log_error("Error adding product to cart: " . $e->getMessage());
    $_SESSION['cart_message'] = "Gagal menambahkan produk ke keranjang.";
}

// Kembalikan ke halaman detail destinasi asal
if ($destId > 0) {
    redirect(BASE_URL . "index.php?module=destinations&action=detail&id=" . $destId);
} else {
    redirect(BASE_URL . "index.php?module=destinations");
}
