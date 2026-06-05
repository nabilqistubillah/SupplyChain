<?php
// modules/orders/process_payment.php

require_role(['buyer']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'index.php?module=orders&action=history');
}

// 1. Validasi CSRF
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($token)) {
    $_SESSION['error_message'] = "Token keamanan tidak valid (CSRF).";
    redirect(BASE_URL . 'index.php?module=orders&action=history');
}

$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$buyerId = $_SESSION['user_id'];

if ($orderId <= 0) {
    redirect(BASE_URL . 'index.php?module=orders&action=history');
}

try {
    // Mulai transaksi database
    $db = Database::getConnection();
    $db->beginTransaction();
    
    // 2. Verifikasi kepemilikan order (security check)
    $stmtCheck = $db->prepare("SELECT id, total_amount FROM orders WHERE id = :id AND buyer_id = :buyer_id LIMIT 1 FOR UPDATE");
    $stmtCheck->execute(['id' => $orderId, 'buyer_id' => $buyerId]);
    $order = $stmtCheck->fetch();
    
    if (!$order) {
        throw new Exception("Order tidak ditemukan atau Anda tidak memiliki akses.");
    }
    
    // 3. Update status di tabel `payments`
    $sqlUpdatePayment = "UPDATE payments SET status = 'paid', payment_date = CURRENT_TIMESTAMP WHERE order_id = :order_id";
    $stmtPay = $db->prepare($sqlUpdatePayment);
    $stmtPay->execute(['order_id' => $orderId]);
    
    // 4. Update status di tabel `orders` menjadi 'paid'
    $sqlUpdateOrder = "UPDATE orders SET status = 'paid' WHERE id = :id";
    $stmtOrd = $db->prepare($sqlUpdateOrder);
    $stmtOrd->execute(['id' => $orderId]);
    
    // 5. Update status di tabel `shipments` menjadi 'shipping' dan beri nomor resi tiruan untuk simulasi logistik
    $receiptNumber = 'MBT-RESI-' . str_pad($orderId, 6, '0', STR_PAD_LEFT) . '-' . rand(100, 999);
    $sqlUpdateShipment = "UPDATE shipments SET status = 'shipping', receipt_number = :receipt WHERE order_id = :order_id";
    $stmtShip = $db->prepare($sqlUpdateShipment);
    $stmtShip->execute([
        'receipt' => $receiptNumber,
        'order_id' => $orderId
    ]);
    
    // Commit Transaksi
    $db->commit();
    
    log_info("Buyer User {$buyerId} paid for Order ID {$orderId}. Generated tracking receipt: {$receiptNumber}");
    
    $_SESSION['success_message'] = "Pembayaran untuk order #MBT-{$orderId} berhasil dikonfirmasi! Pesanan kini masuk dalam status pengiriman (Resi: {$receiptNumber}).";
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    log_error("Payment process error: " . $e->getMessage());
    $_SESSION['error_message'] = "Gagal memproses pembayaran: " . $e->getMessage();
}

redirect(BASE_URL . 'index.php?module=orders&action=history');
