<?php
// modules/destinations/manage.php
$pageTitle = "Kelola Wisata";

// 1. Otorisasi khusus Admin
require_role(['admin']);

$editDest = null;
$editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;

try {
    // 2. Jika sedang mode Edit, ambil data destinasi yang akan diedit
    if ($editId > 0) {
        $stmtEdit = db_query("SELECT * FROM destinations WHERE id = :id", ['id' => $editId]);
        $editDest = $stmtEdit->fetch();
    }

    // 3. Ambil daftar seluruh destinasi
    $stmtList = db_query("SELECT * FROM destinations ORDER BY id DESC");
    $destinations = $stmtList->fetchAll();
} catch (PDOException $e) {
    log_error("Admin Destinations Manage page error: " . $e->getMessage());
    $_SESSION['error_message'] = "Gagal mengambil data dari database.";
    $destinations = [];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 6px;">Manajemen Destinasi Wisata</h1>
        <p style="color: var(--text-secondary);">Kelola data objek wisata pantai dan fasilitas wahana di wilayah Madura.</p>
    </div>
    <a href="<?= BASE_URL ?>index.php?module=destinations" class="btn btn-secondary">
        <i class="fa-solid fa-eye"></i> Lihat Halaman Depan
    </a>
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
    <!-- Form Tambah / Edit Destinasi -->
    <div class="card" style="grid-column: span 1; padding: 20px; align-self: start;">
        <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 20px;">
            <?= $editDest ? '<i class="fa-solid fa-pen-to-square"></i> Edit Destinasi' : '<i class="fa-solid fa-plus"></i> Tambah Destinasi Baru' ?>
        </h3>
        
        <form action="<?= BASE_URL ?>index.php?module=destinations&action=save" method="POST">
            <?= csrf_field() ?>
            
            <?php if ($editDest): ?>
                <input type="hidden" name="id" value="<?= $editDest['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name" class="form-label">Nama Destinasi</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Nama Pantai/Objek Wisata..." value="<?= $editDest ? esc($editDest['name']) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="location" class="form-label">Lokasi / Kabupaten</label>
                <select id="location" name="location" class="form-control" required>
                    <option value="Pamekasan, Madura" <?= $editDest && $editDest['location'] === 'Pamekasan, Madura' ? 'selected' : '' ?>>Pamekasan</option>
                    <option value="Sampang, Madura" <?= $editDest && $editDest['location'] === 'Sampang, Madura' ? 'selected' : '' ?>>Sampang</option>
                    <option value="Sumenep, Madura" <?= $editDest && $editDest['location'] === 'Sumenep, Madura' ? 'selected' : '' ?>>Sumenep</option>
                    <option value="Bangkalan, Madura" <?= $editDest && $editDest['location'] === 'Bangkalan, Madura' ? 'selected' : '' ?>>Bangkalan</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Deskripsi Lengkap</label>
                <textarea id="description" name="description" class="form-control" rows="5" placeholder="Tuliskan deskripsi lengkap mengenai destinasi..." required style="resize: vertical; font-family: inherit;"><?= $editDest ? esc($editDest['description']) : '' ?></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fa-solid fa-save"></i> Simpan
                </button>
                <?php if ($editDest): ?>
                    <a href="<?= BASE_URL ?>index.php?module=destinations&action=manage" class="btn btn-secondary" style="flex: 1;">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Destinasi -->
    <div class="card" style="grid-column: span 2; padding: 20px; overflow-x: auto;">
        <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 20px;">Daftar Destinasi Wisata</h3>
        
        <?php if (empty($destinations)): ?>
            <p style="color: var(--text-secondary); text-align: center; padding: 30px;">Belum ada data destinasi wisata.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); color: var(--text-secondary); font-weight: 600;">
                        <th style="padding: 12px 8px;">ID</th>
                        <th style="padding: 12px 8px;">Nama Wisata</th>
                        <th style="padding: 12px 8px;">Lokasi</th>
                        <th style="padding: 12px 8px; width: 40%;">Deskripsi</th>
                        <th style="padding: 12px 8px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($destinations as $dest): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px 8px; font-weight: 600;"><?= $dest['id'] ?></td>
                            <td style="padding: 12px 8px; font-weight: 600; color: var(--primary);"><?= esc($dest['name']) ?></td>
                            <td style="padding: 12px 8px;"><span style="background: var(--background); padding: 4px 8px; border-radius: 4px; font-size: 11px;"><?= esc($dest['location']) ?></span></td>
                            <td style="padding: 12px 8px; color: var(--text-secondary);"><?= esc(substr($dest['description'], 0, 100)) ?>...</td>
                            <td style="padding: 12px 8px; text-align: center;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <a href="<?= BASE_URL ?>index.php?module=destinations&action=manage&edit_id=<?= $dest['id'] ?>" class="btn btn-secondary" style="padding: 6px 10px; font-size: 12px; border-radius: 6px; color: var(--primary);">
                                        <i class="fa-solid fa-edit"></i> Edit
                                    </a>
                                    <a href="<?= BASE_URL ?>index.php?module=destinations&action=delete&id=<?= $dest['id'] ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 12px; border-radius: 6px;" onclick="return confirm('Apakah Anda yakin ingin menghapus destinasi ini?')">
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
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
