<?php
// modules/orders/index.php
$pageTitle = "Keranjang Belanja B2B";

require_role(['buyer']);

$cartItems = [];
$grandTotal = 0.00;

try {
    if (!empty($_SESSION['cart'])) {
        $productIds = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        
        $sql = "SELECT p.*, c.name as category_name, vp.business_name 
                FROM products p
                JOIN categories c ON p.category_id = c.id
                JOIN vendor_profile vp ON p.vendor_id = vp.id
                WHERE p.id IN ($placeholders)";
                
        $stmt = db_query($sql, $productIds);
        $products = $stmt->fetchAll();
        
        foreach ($products as $p) {
            $qty = $_SESSION['cart'][$p['id']];
            $subtotal = $p['price'] * $qty;
            $grandTotal += $subtotal;
            
            $cartItems[] = array_merge($p, [
                'qty' => $qty,
                'subtotal' => $subtotal
            ]);
        }
    }
} catch (PDOException $e) {
    log_error("Failed to load cart items: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi masalah saat memuat keranjang belanja.";
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 30px;">
    <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 6px;">Keranjang Belanja B2B</h1>
    <p style="color: var(--text-secondary);">Daftar produk pengadaan dan layanan lokal pariwisata Madura yang Anda pilih.</p>
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

<div class="grid grid-3">
    <!-- List Keranjang -->
    <div class="card" style="grid-column: span 2; padding: 24px; overflow-x: auto; align-self: start;">
        <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 20px;">Daftar Item</h3>
        
        <?php if (empty($cartItems)): ?>
            <div style="text-align: center; padding: 40px;">
                <i class="fa-solid fa-cart-shopping" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;"></i>
                <h3>Keranjang Anda Kosong</h3>
                <p style="color: var(--text-secondary); margin-top: 8px; margin-bottom: 20px;">Anda belum menambahkan layanan/produk apa pun.</p>
                <a href="<?= BASE_URL ?>index.php?module=destinations" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari Layanan di Destinasi
                </a>
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); color: var(--text-secondary); font-weight: 600;">
                        <th style="padding: 12px 8px;">Produk / Layanan</th>
                        <th style="padding: 12px 8px;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: right;">Harga</th>
                        <th style="padding: 12px 8px; text-align: center;">Jumlah</th>
                        <th style="padding: 12px 8px; text-align: right;">Subtotal</th>
                        <th style="padding: 12px 8px; text-align: center;">Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px 8px; font-weight: 600; color: var(--primary);"><?= esc($item['name']) ?></td>
                            <td style="padding: 12px 8px; font-size: 13px; color: var(--text-secondary);"><?= esc($item['business_name']) ?></td>
                            <td style="padding: 12px 8px; text-align: right;">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                            <td style="padding: 12px 8px; text-align: center; font-weight: 600;"><?= esc($item['qty']) ?></td>
                            <td style="padding: 12px 8px; text-align: right; font-weight: 600; color: var(--primary-light);">
                                Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                            </td>
                            <td style="padding: 12px 8px; text-align: center;">
                                <a href="<?= BASE_URL ?>index.php?module=orders&action=remove_from_cart&id=<?= $item['id'] ?>" style="color: var(--danger);" onclick="return confirm('Hapus item dari keranjang?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Ringkasan Checkout -->
    <div class="card" style="grid-column: span 1; padding: 24px; align-self: start;">
        <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 20px;">Ringkasan Belanja</h3>
        
        <div style="font-size: 14px; margin-bottom: 24px; display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <span style="color: var(--text-secondary);">Total Item</span>
                <span style="font-weight: 600;"><?= empty($cartItems) ? 0 : array_sum(array_column($cartItems, 'qty')) ?> Unit</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-top: 8px; font-size: 16px; font-weight: 700; color: var(--primary);">
                <span>Grand Total</span>
                <span>Rp <?= number_format($grandTotal, 0, ',', '.') ?></span>
            </div>
        </div>
        
        <?php if (!empty($cartItems)): ?>
            <a href="<?= BASE_URL ?>index.php?module=orders&action=checkout" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600;">
                <i class="fa-solid fa-cash-register"></i> Lanjut ke Checkout
            </a>
        <?php else: ?>
            <button class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600; opacity: 0.5; cursor: not-allowed;" disabled>
                <i class="fa-solid fa-cash-register"></i> Lanjut ke Checkout
            </button>
        <?php endif; ?>
        
        <a href="<?= BASE_URL ?>index.php?module=destinations" class="btn btn-secondary" style="width: 100%; margin-top: 10px; font-size: 13px;">
            <i class="fa-solid fa-arrow-left"></i> Belanja Lagi
        </a>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
