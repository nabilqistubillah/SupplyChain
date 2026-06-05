<?php
// modules/orders/checkout.php
$pageTitle = "Checkout Pemesanan B2B";

require_role(['buyer']);

if (empty($_SESSION['cart'])) {
    $_SESSION['error_message'] = "Keranjang belanja Anda kosong.";
    redirect(BASE_URL . 'index.php?module=orders&action=index');
}

$cartItems = [];
$grandTotal = 0.00;

try {
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
} catch (PDOException $e) {
    log_error("Failed during checkout loading: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi masalah teknis.";
    redirect(BASE_URL . 'index.php?module=orders&action=index');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 30px;">
    <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 6px;">Checkout Pemesanan B2B</h1>
    <p style="color: var(--text-secondary);">Konfirmasikan detail pesanan dan lakukan pemesanan secara resmi.</p>
</div>

<div class="grid grid-3">
    <!-- Form Detail Alamat & Pembayaran -->
    <div class="card" style="grid-column: span 2; padding: 24px;">
        <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 20px;">
            <i class="fa-solid fa-truck-ramp-box"></i> Detail Pengiriman & Pembayaran
        </h3>
        
        <form action="<?= BASE_URL ?>index.php?module=orders&action=process_order" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="total_amount" value="<?= $grandTotal ?>">

            <div class="form-group">
                <label for="shipping_address" class="form-label">Alamat Pengiriman (Hotel / Restoran)</label>
                <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3" placeholder="Masukkan alamat lengkap penerimaan barang..." required style="resize: vertical; font-family: inherit;"></textarea>
            </div>
            
            <div class="grid grid-2" style="gap: 16px;">
                <div class="form-group">
                    <label for="courier" class="form-label">Metode Pengiriman</label>
                    <select id="courier" name="courier" class="form-control" required style="cursor: pointer;">
                        <option value="Kurir Lokal MBT">Kurir Lokal MBT (Direkomendasikan)</option>
                        <option value="Ambil Sendiri / Self Pickup">Ambil Sendiri di Lokasi Vendor</option>
                        <option value="Kurir Vendor Sendiri">Kurir Eksternal Milik Vendor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="payment_method" class="form-label">Metode Pembayaran</label>
                    <select id="payment_method" name="payment_method" class="form-control" required style="cursor: pointer;">
                        <option value="Bank Transfer (BRI / Jatim)">Bank Transfer (BRI / BPD Jatim)</option>
                        <option value="E-Wallet (Dana / OVO)">E-Wallet (Dana / OVO)</option>
                        <option value="Cash / COD">Bayar di Tempat (COD / Cash)</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="note" class="form-label">Catatan Tambahan untuk Vendor</label>
                <textarea id="note" name="note" class="form-control" rows="2" placeholder="Catatan opsional (misal: jam pengiriman, packing tambahan)..." style="resize: vertical; font-family: inherit;"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-weight: 700; margin-top: 10px; font-size: 15px;">
                <i class="fa-solid fa-file-invoice-dollar"></i> Buat Pesanan B2B Sekarang
            </button>
        </form>
    </div>
    
    <!-- Ringkasan Item Pesanan -->
    <div class="card" style="grid-column: span 1; padding: 24px; align-self: start; background-color: #fafafb;">
        <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 20px;">Daftar Pesanan</h3>
        
        <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 20px; max-height: 250px; overflow-y: auto; padding-right: 8px;">
            <?php foreach ($cartItems as $item): ?>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; font-size: 13px; border-bottom: 1px dashed var(--border); padding-bottom: 8px;">
                    <div>
                        <span style="font-weight: 600; color: var(--primary); display: block;"><?= esc($item['name']) ?></span>
                        <span style="color: var(--text-secondary); font-size: 11px;">Vendor: <?= esc($item['business_name']) ?></span>
                        <span style="color: var(--text-secondary); font-size: 11px; display: block;">Jumlah: <?= esc($item['qty']) ?>x</span>
                    </div>
                    <div style="font-weight: 600; text-align: right; color: var(--text-primary);">
                        Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="border-top: 2px solid var(--border); padding-top: 14px; display: flex; justify-content: space-between; font-size: 16px; font-weight: 700; color: var(--primary);">
            <span>Total Bayar</span>
            <span>Rp <?= number_format($grandTotal, 0, ',', '.') ?></span>
        </div>
        
        <a href="<?= BASE_URL ?>index.php?module=orders&action=index" class="btn btn-secondary" style="width: 100%; margin-top: 20px; font-size: 13px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Keranjang
        </a>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
