<?php
// modules/vendors/manage_product.php
$pageTitle = "Form Produk/Layanan";

require_role(['vendor']);

$userId = $_SESSION['user_id'];
$editProduct = null;
$editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;

try {
    // 1. Ambil profile vendor
    $stmtVendor = db_query("SELECT id FROM vendor_profile WHERE user_id = :user_id LIMIT 1", ['user_id' => $userId]);
    $vendor = $stmtVendor->fetch();
    
    if (!$vendor) {
        $_SESSION['error_message'] = "Silakan lengkapi profil bisnis Anda terlebih dahulu.";
        redirect(BASE_URL . 'index.php?module=vendors&action=profile');
    }
    
    $vendorId = $vendor['id'];
    
    // 2. Jika mode edit, ambil data produk lama dan pastikan produk tersebut milik vendor ini (security check)
    if ($editId > 0) {
        $stmtEdit = db_query("SELECT * FROM products WHERE id = :id AND vendor_id = :vendor_id LIMIT 1", [
            'id' => $editId,
            'vendor_id' => $vendorId
        ]);
        $editProduct = $stmtEdit->fetch();
        if (!$editProduct) {
            $_SESSION['error_message'] = "Produk tidak ditemukan atau Anda tidak memiliki hak akses.";
            redirect(BASE_URL . 'index.php?module=vendors&action=products');
        }
    }
    
    // 3. Ambil daftar kategori produk untuk select dropdown
    $stmtCats = db_query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmtCats->fetchAll();
    
} catch (PDOException $e) {
    log_error("Vendor manage product page error: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi kesalahan sistem saat memuat form.";
    redirect(BASE_URL . 'index.php?module=vendors&action=products');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 6px;">
                <?= $editProduct ? 'Edit Produk' : 'Tambah Produk Baru' ?>
            </h1>
            <p style="color: var(--text-secondary);">Silakan lengkapi informasi katalog produk/jasa Anda.</p>
        </div>
        <a href="<?= BASE_URL ?>index.php?module=vendors&action=products" class="btn btn-secondary" style="font-size: 13px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog
        </a>
    </div>

    <div class="card" style="padding: 32px;">
        <form action="<?= BASE_URL ?>index.php?module=vendors&action=save_product" method="POST">
            <?= csrf_field() ?>
            
            <?php if ($editProduct): ?>
                <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name" class="form-label">Nama Produk / Jasa</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Sewa Elf Pariwisata, Rengginang..." value="<?= $editProduct ? esc($editProduct['name']) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category_id" class="form-label">Kategori</label>
                <select id="category_id" name="category_id" class="form-control" required style="cursor: pointer;">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $editProduct && $editProduct['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= esc($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="grid grid-2" style="gap: 16px;">
                <div class="form-group">
                    <label for="price" class="form-label">Harga Satuan (Rp)</label>
                    <input type="number" id="price" name="price" class="form-control" placeholder="Contoh: 15000" min="0" value="<?= $editProduct ? esc($editProduct['price']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="stock" class="form-label">Stok / Batas Pemesanan</label>
                    <input type="number" id="stock" name="stock" class="form-control" placeholder="Contoh: 50" min="0" value="<?= $editProduct ? esc($editProduct['stock']) : '0' ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Deskripsi Lengkap</label>
                <textarea id="description" name="description" class="form-control" rows="5" placeholder="Tuliskan spesifikasi produk, kelebihan, atau durasi layanan..." required style="resize: vertical; font-family: inherit;"><?= $editProduct ? esc($editProduct['description']) : '' ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600; margin-top: 10px;">
                <i class="fa-solid fa-cloud-arrow-up"></i> <?= $editProduct ? 'Simpan Perubahan' : 'Unggah Produk' ?>
            </button>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
