<?php
// modules/orders/process_order.php

require_role(['buyer']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'index.php?module=orders&action=index');
}

// 1. Validasi CSRF
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    $_SESSION['error_message'] = "Token keamanan tidak valid (CSRF). Silakan ulangi checkout.";
    redirect(BASE_URL . 'index.php?module=orders&action=checkout');
}

$buyerId = $_SESSION['user_id'];
$shippingAddress = isset($_POST['shipping_address']) ? trim($_POST['shipping_address']) : '';
$courier = isset($_POST['courier']) ? trim($_POST['courier']) : '';
$paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
$note = isset($_POST['note']) ? trim($_POST['note']) : '';
$totalAmount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0.00;

if (empty($_SESSION['cart'])) {
    $_SESSION['error_message'] = "Keranjang belanja kosong.";
    redirect(BASE_URL . 'index.php?module=orders&action=index');
}

if (empty($shippingAddress) || empty($courier) || empty($paymentMethod)) {
    $_SESSION['error_message'] = "Alamat, metode pengiriman, dan metode pembayaran wajib diisi.";
    redirect(BASE_URL . 'index.php?module=orders&action=checkout');
}

try {
    // Memulai Transaksi Database
    $db = Database::getConnection();
    $db->beginTransaction();
    
    // 2. Insert ke tabel `orders`
    $sqlInsertOrder = "INSERT INTO orders (buyer_id, total_amount, status) VALUES (:buyer_id, :total_amount, 'pending')";
    $stmtOrder = $db->prepare($sqlInsertOrder);
    $stmtOrder->execute([
        'buyer_id' => $buyerId,
        'total_amount' => $totalAmount
    ]);
    
    $orderId = $db->lastInsertId();
    
    // 3. Loop cart item, insert detail, dan kurangi stok produk
    foreach ($_SESSION['cart'] as $productId => $qty) {
        // Ambil info harga & stok terupdate
        $stmtProd = $db->prepare("SELECT price, stock, name FROM products WHERE id = :id LIMIT 1 FOR UPDATE");
        $stmtProd->execute(['id' => $productId]);
        $prod = $stmtProd->fetch();
        
        if (!$prod || $prod['stock'] < $qty) {
            throw new Exception("Stok produk '" . ($prod ? $prod['name'] : 'Unknown') . "' tidak mencukupi untuk jumlah yang dipesan.");
        }
        
        $subtotal = $prod['price'] * $qty;
        
        // Simpan ke `order_details`
        $sqlInsertDetail = "INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) 
                            VALUES (:order_id, :product_id, :quantity, :price, :subtotal)";
        $stmtDetail = $db->prepare($sqlInsertDetail);
        $stmtDetail->execute([
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $qty,
            'price' => $prod['price'],
            'subtotal' => $subtotal
        ]);
        
        // Kurangi stok produk
        $sqlUpdateStock = "UPDATE products SET stock = stock - :qty WHERE id = :id";
        $stmtUpdateStock = $db->prepare($sqlUpdateStock);
        $stmtUpdateStock->execute([
            'qty' => $qty,
            'id' => $productId
        ]);
    }
    
    // 4. Insert ke tabel `payments` (Unpaid)
    $sqlInsertPayment = "INSERT INTO payments (order_id, payment_method, amount, status) 
                         VALUES (:order_id, :payment_method, :amount, 'unpaid')";
    $stmtPayment = $db->prepare($sqlInsertPayment);
    $stmtPayment->execute([
        'order_id' => $orderId,
        'payment_method' => $paymentMethod,
        'amount' => $totalAmount
    ]);
    
    // 5. Insert ke tabel `shipments` (Processing)
    $sqlInsertShipment = "INSERT INTO shipments (order_id, courier, receipt_number, status) 
                          VALUES (:order_id, :courier, '', 'processing')";
    $stmtShipment = $db->prepare($sqlInsertShipment);
    $stmtShipment->execute([
        'order_id' => $orderId,
        'courier' => $courier
    ]);
    
    // Commit Transaksi SQL jika semua query sukses tanpa exception
    $db->commit();
    
    log_info("Buyer User {$buyerId} successfully created Order ID {$orderId} with total amount Rp " . number_format($totalAmount, 0));
    
    // Kosongkan keranjang belanja
    unset($_SESSION['cart']);
    
    $_SESSION['success_message'] = "Pesanan B2B berhasil dibuat! Silakan lakukan pembayaran.";
    redirect(BASE_URL . 'index.php?module=orders&action=history');
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    log_error("Failed to process B2B order: " . $e->getMessage());
    $_SESSION['error_message'] = "Gagal memproses pesanan: " . $e->getMessage();
    redirect(BASE_URL . 'index.php?module=orders&action=checkout');
}
