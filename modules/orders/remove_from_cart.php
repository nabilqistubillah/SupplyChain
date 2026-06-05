<?php
// modules/orders/remove_from_cart.php

require_role(['buyer']);

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
    unset($_SESSION['cart'][$productId]);
    $_SESSION['success_message'] = "Produk berhasil dihapus dari keranjang belanja.";
}

redirect(BASE_URL . 'index.php?module=orders&action=index');
