<?php
// modules/vendors/profile.php
$pageTitle = "Profil Vendor";

// 1. Otorisasi vendor saja
require_role(['vendor']);

$userId = $_SESSION['user_id'];
$success = "";
$error = "";

try {
    // 2. Jika form disubmit (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validasi CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf_token($token)) {
            $error = "Token keamanan tidak valid (CSRF).";
        } else {
            $businessName = trim($_POST['business_name']);
            $address = trim($_POST['address']);
            $contact = trim($_POST['contact']);
            $destId = isset($_POST['destination_id']) ? (int)$_POST['destination_id'] : 0;
            
            if (empty($businessName)) {
                $error = "Nama bisnis tidak boleh kosong.";
            } else {
                // Update profil vendor
                $sql = "UPDATE vendor_profile SET 
                        business_name = :business_name, 
                        address = :address, 
                        contact = :contact, 
                        destination_id = :dest_id 
                        WHERE user_id = :user_id";
                db_query($sql, [
                    'business_name' => $businessName,
                    'address' => $address,
                    'contact' => $contact,
                    'dest_id' => $destId > 0 ? $destId : null,
                    'user_id' => $userId
                ]);
                log_info("Vendor updated profile for User ID {$userId}. Business: '{$businessName}'");
                $success = "Profil bisnis Anda berhasil diperbarui.";
            }
        }
    }

    // 3. Ambil data profil vendor
    $stmt = db_query("SELECT * FROM vendor_profile WHERE user_id = :user_id LIMIT 1", ['user_id' => $userId]);
    $vendor = $stmt->fetch();
    
    // Fallback jika profile belum terbuat (misal karena registrasi manual)
    if (!$vendor) {
        db_query("INSERT INTO vendor_profile (user_id, business_name, address, contact) VALUES (:user_id, 'UMKM Baru', '', '')", ['user_id' => $userId]);
        $stmt = db_query("SELECT * FROM vendor_profile WHERE user_id = :user_id LIMIT 1", ['user_id' => $userId]);
        $vendor = $stmt->fetch();
    }

    // 4. Ambil daftar destinasi untuk dropdown pencocokan lokasi terdekat
    $stmtDests = db_query("SELECT id, name, location FROM destinations ORDER BY name ASC");
    $destinations = $stmtDests->fetchAll();

} catch (PDOException $e) {
    log_error("Vendor Profile page database error: " . $e->getMessage());
    $error = "Terjadi masalah koneksi basis data saat mengambil profil.";
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="max-width: 650px; margin: 0 auto;">
    <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 6px;">Profil Bisnis Vendor</h1>
            <p style="color: var(--text-secondary);">Kelola data informasi usaha Anda yang akan terhubung ke ekosistem wisata.</p>
        </div>
        <a href="<?= BASE_URL ?>index.php?module=vendors&action=products" class="btn btn-secondary" style="font-size: 13px;">
            <i class="fa-solid fa-box"></i> Kelola Katalog Produk
        </a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= esc($error) ?></div>
    <?php endif; ?>

    <div class="card" style="padding: 32px;">
        <form action="<?= BASE_URL ?>index.php?module=vendors&action=profile" method="POST">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="business_name" class="form-label">Nama Bisnis / Usaha</label>
                <input type="text" id="business_name" name="business_name" class="form-control" value="<?= esc($vendor['business_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="destination_id" class="form-label">Destinasi Terdekat (Lokasi Usaha)</label>
                <select id="destination_id" name="destination_id" class="form-control" required style="cursor: pointer;">
                    <option value="">-- Hubungkan dengan Destinasi --</option>
                    <?php foreach ($destinations as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $vendor['destination_id'] == $d['id'] ? 'selected' : '' ?>>
                            <?= esc($d['name']) ?> (<?= esc($d['location']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--text-secondary); display: block; margin-top: 6px; font-size: 12px; line-height: 1.4;">
                    * Dengan memilih destinasi wisata terdekat, produk/jasa Anda akan otomatis dipromosikan di halaman eksplorasi destinasi tersebut sebagai rekomendasi **Smart Tourism Network**.
                </small>
            </div>
            
            <div class="form-group">
                <label for="contact" class="form-label">Nomor WhatsApp Kontak Usaha</label>
                <input type="text" id="contact" name="contact" class="form-control" placeholder="Contoh: 081234567890" value="<?= esc($vendor['contact']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address" class="form-label">Alamat Lengkap Usaha</label>
                <textarea id="address" name="address" class="form-control" rows="4" placeholder="Tuliskan alamat lengkap vendor..." required style="resize: vertical; font-family: inherit;"><?= esc($vendor['address']) ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600; margin-top: 10px;">
                <i class="fa-solid fa-circle-check"></i> Perbarui Profil Bisnis
            </button>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
