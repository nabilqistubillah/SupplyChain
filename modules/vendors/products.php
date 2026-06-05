<?php
// modules/vendors/products.php
$pageTitle = "Katalog Produk Vendor";

// 1. Otorisasi vendor saja
require_role(['vendor']);

$userId = $_SESSION['user_id'];
$products = [];

try {
    // 2. Ambil ID vendor berdasarkan user_id login
    $stmtVendor = db_query("SELECT id, business_name FROM vendor_profile WHERE user_id = :user_id LIMIT 1", ['user_id' => $userId]);
    $vendor = $stmtVendor->fetch();
    
    if ($vendor) {
        $vendorId = $vendor['id'];
        
        // 3. Ambil seluruh produk milik vendor ini
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.vendor_id = :vendor_id 
                ORDER BY p.id DESC";
        $stmtList = db_query($sql, ['vendor_id' => $vendorId]);
        $products = $stmtList->fetchAll();
    } else {
        // Redirect ke pengisian profil jika profil belum dibuat
        $_SESSION['error_message'] = "Silakan lengkapi profil bisnis Anda terlebih dahulu.";
        redirect(BASE_URL . 'index.php?module=vendors&action=profile');
    }
} catch (PDOException $e) {
    log_error("Vendor products catalog page error: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi masalah saat memuat katalog produk.";
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 6px;">Katalog Produk / Layanan</h1>
        <p style="color: var(--text-secondary);">Kelola daftar produk UMKM, penginapan, kuliner, atau transportasi yang Anda pasarkan.</p>
        <div style="margin-top: 6px;">
            <span style="font-size: 13px; color: var(--text-secondary);">Nama Vendor: </span>
            <span style="font-weight: 600; color: var(--primary); background: #e0f2fe; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                <?= esc($vendor['business_name']) ?>
            </span>
        </div>
    </div>
    
    <div style="display: flex; gap: 10px;">
        <a href="<?= BASE_URL ?>index.php?module=vendors&action=profile" class="btn btn-secondary">
            <i class="fa-solid fa-store"></i> Edit Profil Toko
        </a>
        <a href="<?= BASE_URL ?>index.php?module=vendors&action=manage_product" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah Produk Baru
        </a>
    </div>
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
    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 40px;">
            <i class="fa-solid fa-box-open" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;"></i>
            <h3>Belum Ada Produk</h3>
            <p style="color: var(--text-secondary); margin-top: 8px; margin-bottom: 20px;">Anda belum mengunggah produk atau layanan apa pun di katalog Anda.</p>
            <a href="<?= BASE_URL ?>index.php?module=vendors&action=manage_product" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Tambah Produk Pertama
            </a>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-secondary); font-weight: 600;">
                    <th style="padding: 12px 8px;">Katalog</th>
                    <th style="padding: 12px 8px;">Kategori</th>
                    <th style="padding: 12px 8px;">Harga Satuan</th>
                    <th style="padding: 12px 8px;">Stok/Kuota</th>
                    <th style="padding: 12px 8px; width: 35%;">Deskripsi</th>
                    <th style="padding: 12px 8px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px 8px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 6px; background: linear-gradient(135deg, #e2e8f0, #cbd5e1); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                    <?php if ($p['category_name'] === 'Transportasi'): ?>
                                        <i class="fa-solid fa-car"></i>
                                    <?php elseif ($p['category_name'] === 'Akomodasi'): ?>
                                        <i class="fa-solid fa-bed"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-store"></i>
                                    <?php endif; ?>
                                </div>
                                <span style="font-weight: 600; color: var(--primary);"><?= esc($p['name']) ?></span>
                            </div>
                        </td>
                        <td style="padding: 12px 8px;">
                            <span style="background: var(--background); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                <?= esc($p['category_name']) ?>
                            </span>
                        </td>
                        <td style="padding: 12px 8px; font-weight: 600; color: var(--primary-light);">
                            Rp <?= number_format($p['price'], 0, ',', '.') ?>
                        </td>
                        <td style="padding: 12px 8px; font-weight: 500; color: <?= $p['stock'] > 0 ? 'var(--text-primary)' : 'var(--danger)' ?>;">
                            <?= $p['stock'] > 0 ? esc($p['stock']) . ' Unit' : 'Habis' ?>
                        </td>
                        <td style="padding: 12px 8px; color: var(--text-secondary);"><?= esc(substr($p['description'], 0, 90)) ?>...</td>
                        <td style="padding: 12px 8px; text-align: center;">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="<?= BASE_URL ?>index.php?module=vendors&action=manage_product&edit_id=<?= $p['id'] ?>" class="btn btn-secondary" style="padding: 6px 10px; font-size: 12px; border-radius: 6px; color: var(--primary);">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </a>
                                <a href="<?= BASE_URL ?>index.php?module=vendors&action=delete_product&id=<?= $p['id'] ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 12px; border-radius: 6px;" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </a>
                            </div>
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
