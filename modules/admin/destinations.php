<?php
// modules/admin/destinations.php
// BahariChain: Kelola Destinasi Wisata Bahari (Admin Portal)

// ==============================================================================
// 1. SECURITY CHECK: Only admin can access this page
// ==============================================================================
require_role(['admin']);

$pageTitle = "Kelola Destinasi Wisata";
$message = '';
$messageType = '';

$db = Database::getConnection();

// ==============================================================================
// 2. PROCESS ACTIONS: Add, Edit, Delete Destination
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $message = "Token keamanan tidak valid. Silakan coba lagi.";
        $messageType = "danger";
    } else {
        // Aksi 1: Tambah Destinasi Baru
        if (isset($_POST['action']) && $_POST['action'] === 'add_destination') {
            $nama = trim($_POST['nama'] ?? '');
            $lokasi = trim($_POST['lokasi'] ?? '');
            $latitude = trim($_POST['latitude'] ?? '');
            $longitude = trim($_POST['longitude'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $foto_path = '';

            if (empty($nama) || empty($lokasi) || empty($deskripsi)) {
                $message = "Nama, lokasi, dan deskripsi destinasi wajib diisi.";
                $messageType = "danger";
            } else {
                // File Upload Handling
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../assets/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileName = time() . '_' . basename($_FILES['foto']['name']);
                    $targetFilePath = $uploadDir . $fileName;
                    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

                    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
                    if (in_array($fileType, $allowedTypes)) {
                        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFilePath)) {
                            $foto_path = 'assets/uploads/' . $fileName;
                        } else {
                            $message = "Gagal mengunggah berkas foto.";
                            $messageType = "danger";
                        }
                    } else {
                        $message = "Format foto tidak valid. Hanya JPG, JPEG, PNG, dan WEBP yang diperbolehkan.";
                        $messageType = "danger";
                    }
                }

                if ($messageType !== 'danger') {
                    try {
                        db_query("INSERT INTO destinasi (nama, deskripsi, lokasi, latitude, longitude, foto_path, created_at) 
                                  VALUES (:nama, :deskripsi, :lokasi, :latitude, :longitude, :foto_path, NOW())", [
                            'nama' => $nama,
                            'deskripsi' => $deskripsi,
                            'lokasi' => $lokasi,
                            'latitude' => $latitude ?: null,
                            'longitude' => $longitude ?: null,
                            'foto_path' => $foto_path ?: null
                        ]);
                        log_info("Admin created new destination '{$nama}' in {$lokasi}.");
                        $message = "Destinasi wisata baru berhasil ditambahkan.";
                        $messageType = "success";
                    } catch (PDOException $e) {
                        log_error("Admin add destination error: " . $e->getMessage());
                        $message = "Gagal menyimpan destinasi ke database.";
                        $messageType = "danger";
                    }
                }
            }
        }

        // Aksi 2: Edit Destinasi
        if (isset($_POST['action']) && $_POST['action'] === 'edit_destination') {
            $id = intval($_POST['id'] ?? 0);
            $nama = trim($_POST['nama'] ?? '');
            $lokasi = trim($_POST['lokasi'] ?? '');
            $latitude = trim($_POST['latitude'] ?? '');
            $longitude = trim($_POST['longitude'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');

            if (empty($nama) || empty($lokasi) || empty($deskripsi)) {
                $message = "Nama, lokasi, dan deskripsi destinasi wajib diisi.";
                $messageType = "danger";
            } else {
                try {
                    // Ambil detail destinasi lama untuk menghapus foto lama jika diganti
                    $oldDest = db_query("SELECT foto_path FROM destinasi WHERE id = ?", [$id])->fetch();
                    $foto_path = $oldDest['foto_path'] ?? '';

                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/../../assets/uploads/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $fileName = time() . '_' . basename($_FILES['foto']['name']);
                        $targetFilePath = $uploadDir . $fileName;
                        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

                        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
                        if (in_array($fileType, $allowedTypes)) {
                            if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFilePath)) {
                                // Hapus foto lama jika ada
                                if (!empty($foto_path) && file_exists(__DIR__ . '/../../' . $foto_path)) {
                                    unlink(__DIR__ . '/../../' . $foto_path);
                                }
                                $foto_path = 'assets/uploads/' . $fileName;
                            } else {
                                $message = "Gagal mengunggah berkas foto baru.";
                                $messageType = "danger";
                            }
                        } else {
                            $message = "Format foto tidak valid. Hanya JPG, JPEG, PNG, dan WEBP yang diperbolehkan.";
                            $messageType = "danger";
                        }
                    }

                    if ($messageType !== 'danger') {
                        db_query("UPDATE destinasi SET nama = :nama, deskripsi = :deskripsi, lokasi = :lokasi, 
                                  latitude = :latitude, longitude = :longitude, foto_path = :foto_path WHERE id = :id", [
                            'nama' => $nama,
                            'deskripsi' => $deskripsi,
                            'lokasi' => $lokasi,
                            'latitude' => $latitude ?: null,
                            'longitude' => $longitude ?: null,
                            'foto_path' => $foto_path ?: null,
                            'id' => $id
                        ]);
                        log_info("Admin updated destination ID={$id}.");
                        $message = "Data destinasi berhasil diperbarui.";
                        $messageType = "success";
                    }
                } catch (PDOException $e) {
                    log_error("Admin edit destination error: " . $e->getMessage());
                    $message = "Gagal memperbarui destinasi: " . $e->getMessage();
                    $messageType = "danger";
                }
            }
        }
    }
}

// Aksi 3: Hapus Destinasi
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        // Ambil data untuk hapus filenya
        $dest = db_query("SELECT foto_path FROM destinasi WHERE id = ?", [$delete_id])->fetch();
        if ($dest) {
            if (!empty($dest['foto_path']) && file_exists(__DIR__ . '/../../' . $dest['foto_path'])) {
                unlink(__DIR__ . '/../../' . $dest['foto_path']);
            }
            db_query("DELETE FROM destinasi WHERE id = ?", [$delete_id]);
            log_info("Admin deleted destination ID={$delete_id}.");
            $message = "Destinasi berhasil dihapus dari platform.";
            $messageType = "success";
        }
    } catch (PDOException $e) {
        log_error("Admin delete destination error: " . $e->getMessage());
        $message = "Gagal menghapus destinasi. Destinasi mungkin telah terikat dengan data paket wisata atau armada.";
        $messageType = "danger";
    }
}

// ==============================================================================
// 3. FETCH LIST OF DESTINATIONS
// ==============================================================================
$search = trim($_GET['search'] ?? '');
$params = [];
$whereClause = "WHERE 1=1";

if (!empty($search)) {
    $whereClause .= " AND (nama LIKE :search OR lokasi LIKE :search OR deskripsi LIKE :search)";
    $params['search'] = "%{$search}%";
}

try {
    $destQuery = "SELECT * FROM destinasi {$whereClause} ORDER BY id DESC";
    $destinations = db_query($destQuery, $params)->fetchAll();
} catch (PDOException $e) {
    log_error("Fetch destinations list error: " . $e->getMessage());
    $destinations = [];
}

// Fetch single destination for editing
$editDest = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    foreach ($destinations as $d) {
        if ($d['id'] === $editId) {
            $editDest = $d;
            break;
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Layout Head -->
<div style="margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-umbrella-beach" style="color: #3b82f6;"></i> Kelola Destinasi Wisata
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 4px 0 0 0;">Pantau, tambah, atau perbarui daftar destinasi wisata bahari Madura secara terpadu</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= BASE_URL ?>index.php?module=admin&action=destinations&add=1" class="btn btn-primary" style="background-color: #3b82f6; color: white; font-weight: 600;">
            <i class="fa-solid fa-plus-circle"></i> Tambah Destinasi
        </a>
        <a href="<?= BASE_URL ?>index.php?module=admin&action=dashboard" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<?php if ($message): ?>
    <div style="padding: 15px 20px; background: <?= $messageType === 'success' ? '#d1fae5' : '#fee2e2' ?>; color: <?= $messageType === 'success' ? '#065f46' : '#991b1b' ?>; border-radius: var(--radius); margin-bottom: 25px; font-size: 14px; font-weight: 500;">
        <i class="fa-solid <?= $messageType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i> <?= esc($message) ?>
    </div>
<?php endif; ?>

<!-- ==============================================================================
     FORM SECTION: ADD OR EDIT
     ============================================================================== -->
<?php if (isset($_GET['add']) || $editDest): ?>
    <div class="card" style="padding: 30px; margin-bottom: 30px; border-top: 4px solid #3b82f6; box-shadow: var(--shadow-md);">
        <h2 style="color: var(--primary); font-size: 20px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid <?= $editDest ? 'fa-pen-to-square' : 'fa-plus-circle' ?>"></i> 
            <?= $editDest ? 'Edit Detail Destinasi' : 'Tambah Destinasi Wisata Baru' ?>
        </h2>
        
        <form method="POST" action="<?= BASE_URL ?>index.php?module=admin&action=destinations" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="<?= $editDest ? 'edit_destination' : 'add_destination' ?>">
            <?php if ($editDest): ?>
                <input type="hidden" name="id" value="<?= $editDest['id'] ?>">
            <?php endif; ?>

            <div class="grid grid-2" style="gap: 20px; margin-bottom: 20px;">
                <!-- Kolom Kiri -->
                <div>
                    <div class="form-group">
                        <label class="form-label" for="nama">Nama Destinasi <span style="color:red;">*</span></label>
                        <input type="text" id="nama" name="nama" class="form-control" 
                               placeholder="Contoh: Pantai Lon Malang"
                               value="<?= esc($editDest['nama'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="lokasi">Lokasi Administratif <span style="color:red;">*</span></label>
                        <input type="text" id="lokasi" name="lokasi" class="form-control" 
                               placeholder="Contoh: Sampang, Madura"
                               value="<?= esc($editDest['lokasi'] ?? '') ?>" required>
                    </div>

                    <div class="grid grid-2" style="gap: 10px;">
                        <div class="form-group">
                            <label class="form-label" for="latitude">Garis Lintang (Latitude)</label>
                            <input type="number" step="0.00000001" id="latitude" name="latitude" class="form-control" 
                                   placeholder="Contoh: -6.904500"
                                   value="<?= esc($editDest['latitude'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="longitude">Garis Bujur (Longitude)</label>
                            <input type="number" step="0.00000001" id="longitude" name="longitude" class="form-control" 
                                   placeholder="Contoh: 113.250500"
                                   value="<?= esc($editDest['longitude'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div>
                    <div class="form-group">
                        <label class="form-label" for="deskripsi">Deskripsi & Daya Tarik <span style="color:red;">*</span></label>
                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="5" 
                                  placeholder="Tuliskan ulasan menarik tentang destinasi ini..."
                                  style="resize: none;" required><?= esc($editDest['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="foto">Foto Destinasi</label>
                        <input type="file" id="foto" name="foto" class="form-control" accept="image/*" style="padding: 8px 12px;">
                        <small style="color: var(--text-secondary); font-size: 11px;">Format: JPG, PNG, WEBP. Maksimal 2MB.</small>
                        
                        <?php if ($editDest && !empty($editDest['foto_path'])): ?>
                            <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
                                <img src="<?= BASE_URL . esc($editDest['foto_path']) ?>" alt="Thumbnail" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                                <span style="font-size: 12px; color: var(--text-secondary);">Foto saat ini aktif</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <a href="<?= BASE_URL ?>index.php?module=admin&action=destinations" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" style="background-color: #3b82f6; border-color: #3b82f6; font-weight: 600;">
                    <i class="fa-solid fa-save"></i> <?= $editDest ? 'Simpan Perubahan' : 'Simpan Destinasi' ?>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- ==============================================================================
     FILTER & SEARCH SECTION
     ============================================================================== -->
<div class="card" style="padding: 20px; margin-bottom: 25px; box-shadow: var(--shadow-sm);">
    <form method="GET" action="<?= BASE_URL ?>index.php" style="display: flex; gap: 15px; align-items: flex-end;">
        <input type="hidden" name="module" value="admin">
        <input type="hidden" name="action" value="destinations">
        
        <div style="flex: 1;">
            <label class="form-label" for="search" style="font-size:13px;">Cari Destinasi</label>
            <div style="position: relative;">
                <input type="text" id="search" name="search" class="form-control" 
                       placeholder="Cari berdasarkan nama, lokasi, atau kata kunci..." 
                       value="<?= esc($search) ?>"
                       style="width: 100%; padding: 10px 12px 10px 36px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
            </div>
        </div>
        
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary" style="background-color: #3b82f6; border-color: #3b82f6; padding: 10px 18px;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            <?php if (!empty($search)): ?>
                <a href="<?= BASE_URL ?>index.php?module=admin&action=destinations" class="btn btn-secondary" style="padding: 10px 18px;">
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- ==============================================================================
     DESTINATIONS LIST TABLE
     ============================================================================== -->
<div class="card" style="padding: 24px; overflow-x: auto; box-shadow: var(--shadow-sm);">
    <?php if (empty($destinations)): ?>
        <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
            <i class="fa-solid fa-map-location-dot" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px; display: block;"></i>
            <p style="margin: 0; font-size: 16px; font-weight: 500;">Tidak ada destinasi ditemukan!</p>
            <p style="margin: 4px 0 0 0; font-size: 13px;">Tabel kosong atau ubah kata pencarian Anda.</p>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--primary); font-weight: 600;">
                    <th style="padding: 12px 8px; width: 60px;">No</th>
                    <th style="padding: 12px 8px; width: 100px;">Foto</th>
                    <th style="padding: 12px 8px;">Nama Destinasi</th>
                    <th style="padding: 12px 8px;">Lokasi</th>
                    <th style="padding: 12px 8px;">Deskripsi Singkat</th>
                    <th style="padding: 12px 8px; text-align: center; width: 160px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($destinations as $d): 
                ?>
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;">
                        <td style="padding: 16px 8px; color: var(--text-secondary);"><?= $no++ ?></td>
                        <td style="padding: 16px 8px;">
                            <?php if (!empty($d['foto_path'])): ?>
                                <img src="<?= BASE_URL . esc($d['foto_path']) ?>" alt="<?= esc($d['nama']) ?>" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                            <?php else: ?>
                                <div style="width: 80px; height: 50px; background: #e2e8f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: var(--text-secondary);">
                                    <i class="fa-regular fa-image" style="font-size: 16px; opacity: 0.5;"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 16px 8px;">
                            <strong style="color: var(--primary); font-size: 15px;"><?= esc($d['nama']) ?></strong>
                            <?php if (!empty($d['latitude']) && !empty($d['longitude'])): ?>
                                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                                    <i class="fa-solid fa-map-pin" style="color: #64748b;"></i> <?= esc($d['latitude']) ?>, <?= esc($d['longitude']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 16px 8px; font-weight: 500; color: var(--text-primary);">
                            <i class="fa-solid fa-location-dot" style="color: #ef4444; margin-right: 4px;"></i> <?= esc($d['lokasi']) ?>
                        </td>
                        <td style="padding: 16px 8px; color: var(--text-secondary); max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= esc($d['deskripsi']) ?>
                        </td>
                        <td style="padding: 16px 8px; text-align: center;">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="<?= BASE_URL ?>index.php?module=admin&action=destinations&edit_id=<?= $d['id'] ?>" 
                                   class="btn btn-secondary" 
                                   style="padding: 6px 12px; font-size: 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; color: var(--warning); border-color: #fde047;" 
                                   title="Ubah Destinasi">
                                    <i class="fa-solid fa-pen"></i> Ubah
                                </a>
                                
                                <a href="<?= BASE_URL ?>index.php?module=admin&action=destinations&delete_id=<?= $d['id'] ?>" 
                                   class="btn btn-secondary" 
                                   style="padding: 6px 12px; font-size: 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; color: var(--danger); border-color: #fca5a5;" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus destinasi <?= esc($d['nama']) ?>? Tindakan ini tidak dapat dibatalkan.');" 
                                   title="Hapus Destinasi">
                                    <i class="fa-solid fa-trash-can"></i> Hapus
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
