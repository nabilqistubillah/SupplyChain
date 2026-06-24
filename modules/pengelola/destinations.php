<?php
// File: modules/pengelola/destinations.php
// BahariChain: Halaman Kelola Destinasi

// ==============================================================================
// SECURITY CHECK & INITIALIZATION
// ==============================================================================
require_role(['pengelola_wisata']);
$pageTitle = "Kelola Destinasi";

// Ambil ID pengelola dari session untuk memfilter data (agar tidak tertukar dengan pengelola lain)
$pengelola_id = $_SESSION['user_id'] ?? 0;

// Load Header
require_once __DIR__ . '/../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h2 style="font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-umbrella-beach"></i> Kelola Destinasi
    </h2>
    
    <a href="<?= BASE_URL ?>index.php?module=pengelola&action=destinations_create" 
       style="background-color: #047857; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.3s ease; box-shadow: var(--shadow-sm);">
        <i class="fa-solid fa-plus"></i> Tambah Destinasi
    </a>
</div>

<div class="card" style="padding: 24px; border-top: 4px solid #047857; box-shadow: var(--shadow-sm);">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-secondary);">
                    <th style="padding: 12px 8px;">No</th>
                    <th style="padding: 12px 8px;">Foto</th>
                    <th style="padding: 12px 8px;">Nama Destinasi</th>
                    <th style="padding: 12px 8px;">Lokasi</th>
                    <th style="padding: 12px 8px;">Deskripsi Singkat</th>
                    <th style="padding: 12px 8px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    // Query disesuaikan: Mengambil semua data dari tabel destinasi
                    // Catatan: Jika nanti 1 pengelola hanya boleh melihat destinasinya sendiri, 
                    // kamu perlu menambahkan kolom 'pengelola_id' di tabel ini ya.
                    $stmt = db_query("SELECT * FROM destinasi ORDER BY id DESC");
                    $destinasi_list = $stmt->fetchAll();
                    
                    $no = 1;
                    if (count($destinasi_list) > 0) {
                        foreach ($destinasi_list as $row) {
                            ?>
                            <tr style="border-bottom: 1px solid #e5e7eb; transition: background-color 0.2s;">
                                <td style="padding: 16px 8px;"><?= $no++ ?></td>
                                
                                <td style="padding: 16px 8px;">
                                    <?php if(!empty($row['foto_path'])): ?>
                                        <img src="<?= BASE_URL . esc($row['foto_path']) ?>" alt="<?= esc($row['nama']) ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 40px; background: #e2e8f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #64748b;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                
                                <td style="padding: 16px 8px; font-weight: 600; color: var(--primary);">
                                    <?= esc($row['nama']) ?>
                                </td>
                                
                                <td style="padding: 16px 8px;">
                                    <i class="fa-solid fa-location-dot" style="color: #ef4444; margin-right: 4px;"></i> 
                                    <?= esc($row['lokasi']) ?>
                                </td>
                                
                                <td style="padding: 16px 8px;">
                                    <?= esc(strlen($row['deskripsi']) > 50 ? substr($row['deskripsi'], 0, 50) . '...' : $row['deskripsi']) ?>
                                </td>
                                
                                <td style="padding: 16px 8px; text-align: center;">
                                    <a href="<?= BASE_URL ?>index.php?module=pengelola&action=destinations_edit&id=<?= $row['id'] ?>" 
                                       style="color: #f59e0b; margin-right: 12px; text-decoration: none;" title="Edit Destinasi">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>index.php?module=pengelola&action=destinations_delete&id=<?= $row['id'] ?>" 
                                       style="color: #ef4444; text-decoration: none;" title="Hapus Destinasi"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus <?= esc($row['nama']) ?>?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr>
                                <td colspan='6' style='padding: 32px; text-align: center; color: var(--text-secondary);'>
                                    <i class='fa-solid fa-folder-open' style='font-size: 32px; color: #cbd5e1; margin-bottom: 12px; display: block;'></i>
                                    Belum ada data destinasi.
                                </td>
                              </tr>";
                    }
                } catch (Exception $e) {
                    log_error("Gagal memuat daftar destinasi: " . $e->getMessage());
                    echo "<tr><td colspan='6' style='padding: 24px; text-align: center; color: #ef4444;'>Gagal memuat data. Periksa koneksi database.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Load Footer
require_once __DIR__ . '/../../includes/footer.php';
?>