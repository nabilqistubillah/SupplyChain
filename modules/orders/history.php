<?php
// modules/orders/history.php
$pageTitle = "Riwayat Pemesanan B2B";

require_role(['buyer']);

$buyerId = $_SESSION['user_id'];
$orders = [];

try {
    // Ambil seluruh data order milik buyer ini beserta status pembayaran dan pengiriman
    $sql = "SELECT o.*, p.payment_method, p.status as payment_status, s.courier, s.receipt_number, s.status as shipment_status 
            FROM orders o
            JOIN payments p ON o.id = p.order_id
            JOIN shipments s ON o.id = s.order_id
            WHERE o.buyer_id = :buyer_id
            ORDER BY o.order_date DESC";
            
    $stmt = db_query($sql, ['buyer_id' => $buyerId]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    log_error("Failed to load order history: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi masalah saat mengambil riwayat transaksi.";
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 30px;">
    <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 6px;">Riwayat Transaksi B2B</h1>
    <p style="color: var(--text-secondary);">Pantau status pembayaran, pengiriman logistik, dan riwayat pesanan pengadaan Anda.</p>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?= esc($_SESSION['success_message']) ?>
        <?php unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <?= esc($_SESSION['error_message']) ?>
        <?php unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<div class="card" style="padding: 24px; overflow-x: auto;">
    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 40px;">
            <i class="fa-solid fa-receipt" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;"></i>
            <h3>Belum Ada Riwayat Transaksi</h3>
            <p style="color: var(--text-secondary); margin-top: 8px; margin-bottom: 20px;">Anda belum pernah melakukan pemesanan produk/layanan B2B.</p>
            <a href="<?= BASE_URL ?>index.php?module=destinations" class="btn btn-primary">
                <i class="fa-solid fa-magnifying-glass"></i> Cari Produk di Destinasi
            </a>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-secondary); font-weight: 600;">
                    <th style="padding: 12px 8px;">ID Order</th>
                    <th style="padding: 12px 8px;">Tanggal Transaksi</th>
                    <th style="padding: 12px 8px; text-align: right;">Total Transaksi</th>
                    <th style="padding: 12px 8px;">Metode Pembayaran</th>
                    <th style="padding: 12px 8px; text-align: center;">Status Bayar</th>
                    <th style="padding: 12px 8px; text-align: center;">Status Pengiriman</th>
                    <th style="padding: 12px 8px; text-align: center;">Aksi Konfirmasi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px 8px; font-weight: 700; color: var(--primary);">#MBT-<?= esc($o['id']) ?></td>
                        <td style="padding: 12px 8px; color: var(--text-secondary);"><?= esc(date('d M Y, H:i', strtotime($o['order_date']))) ?></td>
                        <td style="padding: 12px 8px; font-weight: 600; text-align: right; color: var(--primary-light);">
                            Rp <?= number_format($o['total_amount'], 0, ',', '.') ?>
                        </td>
                        <td style="padding: 12px 8px; font-size: 13px; font-weight: 500;"><?= esc($o['payment_method']) ?></td>
                        <td style="padding: 12px 8px; text-align: center;">
                            <?php if ($o['payment_status'] === 'paid'): ?>
                                <span style="background-color: #ecfdf5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; border: 1px solid #a7f3d0;">
                                    Lunas
                                </span>
                            <?php else: ?>
                                <span style="background-color: #fef2f2; color: #991b1b; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; border: 1px solid #fca5a5;">
                                    Belum Bayar
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px 8px; text-align: center;">
                            <?php if ($o['shipment_status'] === 'delivered'): ?>
                                <span style="background-color: #ecfdf5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                    Diterima
                                </span>
                            <?php elseif ($o['shipment_status'] === 'shipping'): ?>
                                <span style="background-color: #eff6ff; color: #1e40af; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                    Dikirim (Resi: <?= esc($o['receipt_number']) ?>)
                                </span>
                            <?php else: ?>
                                <span style="background-color: #fffbeb; color: #854d0e; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                    Diproses Vendor
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px 8px; text-align: center;">
                            <?php if ($o['payment_status'] === 'unpaid'): ?>
                                <!-- Tombol Simulasi Pembayaran -->
                                <form action="<?= BASE_URL ?>index.php?module=orders&action=process_payment" method="POST" style="margin: 0; display: inline-block;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px; border-radius: 6px;" onclick="return confirm('Simulasi pelunasan pembayaran B2B?')">
                                        <i class="fa-solid fa-credit-card"></i> Bayar Sekarang
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="font-size: 12px; color: var(--text-secondary); font-weight: 500;">
                                    <i class="fa-solid fa-circle-check" style="color: var(--success); margin-right: 4px;"></i> Selesai
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
