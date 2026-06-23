<?php
// modules/wisatawan/payment.php
// BahariChain: Unggah Bukti Pembayaran Wisatawan

require_role(['wisatawan']);

// ==============================================================================
// POST HANDLING: Proses Unggah Bukti Transfer
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    // Validasi CSRF Token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Token keamanan tidak valid. Silakan coba lagi.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=reservations');
    }

    $pesananId = (int)$_POST['pesanan_id'];
    $metode = trim($_POST['metode_pembayaran'] ?? '');

    // Validasi data masukan
    if ($pesananId <= 0 || empty($metode)) {
        $_SESSION['error_message'] = "Metode pembayaran wajib dipilih.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=payment&pesanan_id=' . $pesananId);
    }

    try {
        // Ambil data pesanan untuk memastikan kepemilikan dan status pending
        $pesanan = db_query("SELECT * FROM pesanan WHERE id = ? AND user_id = ?", [$pesananId, $_SESSION['user_id']])->fetch();
        
        if (!$pesanan || $pesanan['status'] !== 'pending') {
            $_SESSION['error_message'] = "Pesanan tidak ditemukan atau tidak dapat dibayar.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=reservations');
        }

        // Validasi unggah file
        if (!isset($_FILES['bukti_transfer']) || $_FILES['bukti_transfer']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_message'] = "Struk bukti transfer wajib diunggah.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=payment&pesanan_id=' . $pesananId);
        }

        $file = $_FILES['bukti_transfer'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

        // Periksa ukuran file
        if ($file['size'] > $maxSize) {
            $_SESSION['error_message'] = "Ukuran file terlalu besar. Maksimal adalah 2MB.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=payment&pesanan_id=' . $pesananId);
        }

        // Periksa tipe file (MIME type)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $_SESSION['error_message'] = "Tipe file tidak didukung. Harap unggah gambar JPG/JPEG/PNG.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=payment&pesanan_id=' . $pesananId);
        }

        // Siapkan direktori penyimpanan
        $uploadDir = __DIR__ . '/../../assets/uploads/bukti/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Tentukan nama file baru (unifikasi format)
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'bukti_' . $pesananId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $newFilename;

        // Pindahkan file ke target penyimpanan
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $_SESSION['error_message'] = "Gagal menyimpan berkas ke server lokal.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=payment&pesanan_id=' . $pesananId);
        }

        $dbRelativePath = 'assets/uploads/bukti/' . $newFilename;
        $jumlahBayar = (float)$pesanan['total_harga'];

        // Simpan / update record di tabel pembayaran
        db_query("
            INSERT INTO pembayaran (pesanan_id, metode_pembayaran, jumlah, status, bukti_transfer, tanggal_bayar)
            VALUES (?, ?, ?, 'unpaid', ?, NULL)
            ON DUPLICATE KEY UPDATE 
                metode_pembayaran = VALUES(metode_pembayaran),
                jumlah = VALUES(jumlah),
                status = 'unpaid',
                bukti_transfer = VALUES(bukti_transfer),
                tanggal_bayar = NULL
        ", [$pesananId, $metode, $jumlahBayar, $dbRelativePath]);

        // Update status di tabel pesanan menjadi 'unpaid' (menunggu verifikasi)
        db_query("UPDATE pesanan SET status = 'unpaid' WHERE id = ?", [$pesananId]);

        // Tambahkan notifikasi
        db_query("
            INSERT INTO notifikasi (user_id, judul, pesan, tipe, is_read, created_at)
            VALUES (?, 'Bukti Pembayaran Diunggah', ?, 'transaksi', 0, NOW())
        ", [
            $_SESSION['user_id'],
            "Bukti transfer untuk transaksi #INV-" . str_pad($pesananId, 5, '0', STR_PAD_LEFT) . " telah berhasil diunggah. Menunggu verifikasi admin."
        ]);

        $_SESSION['success_message'] = "Bukti pembayaran berhasil diunggah. Menunggu proses verifikasi administrasi.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=reservations');

    } catch (PDOException $e) {
        log_error("Wisatawan upload payment proof error: " . $e->getMessage());
        $_SESSION['error_message'] = "Terjadi kesalahan database saat mengunggah bukti pembayaran.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=payment&pesanan_id=' . $pesananId);
    }
}

// ==============================================================================
// GET DISPLAY: Tampilan Formulir Unggah Pembayaran
// ==============================================================================
$pesananId = isset($_GET['pesanan_id']) ? (int)$_GET['pesanan_id'] : 0;

if ($pesananId <= 0) {
    redirect(BASE_URL . 'index.php?module=wisatawan&action=reservations');
}

try {
    // Ambil data detail pesanan beserta paket terkait
    $stmt = db_query("
        SELECT p.*, pw.nama_paket, pw.durasi_hari, pw.harga as harga_paket,
               t.nama_armada, t.harga_sewa
        FROM pesanan p
        JOIN paket_wisata pw ON p.paket_wisata_id = pw.id
        LEFT JOIN transportasi t ON p.transportasi_id = t.id
        WHERE p.id = ? AND p.user_id = ?
    ", [$pesananId, $_SESSION['user_id']]);
    $pesanan = $stmt->fetch();

    if (!$pesanan || $pesanan['status'] !== 'pending') {
        $_SESSION['error_message'] = "Pesanan tidak ditemukan atau tidak dalam status menunggu pembayaran.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=reservations');
    }

} catch (PDOException $e) {
    log_error("Wisatawan load payment page database error: " . $e->getMessage());
    redirect(BASE_URL . 'index.php?module=wisatawan&action=reservations');
}

$pageTitle = "Pembayaran Reservasi #" . str_pad($pesanan['id'], 5, '0', STR_PAD_LEFT);
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Navigation Breadcrumbs -->
<div style="margin-bottom: 20px;">
    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=dashboard" style="color: var(--text-secondary); font-size: 14px;">
        <i class="fa-solid fa-house"></i> Dashboard
    </a>
    <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reservations" style="color: var(--text-secondary); font-size: 14px;">
        Riwayat Pesanan
    </a>
    <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
    <span style="color: var(--primary); font-size: 14px; font-weight: 600;">Pembayaran</span>
</div>

<!-- Alert error notification -->
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i> <?= esc($_SESSION['error_message']) ?>
        <?php unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<div class="grid grid-3" style="align-items: start; gap: 30px;">
    <!-- Left: Payment details & instructions (Span 2) -->
    <div style="grid-column: span 2; display: flex; flex-direction: column; gap: 30px;">
        
        <!-- Billing Details Card -->
        <div class="card" style="padding: 24px;">
            <h3 style="color: var(--primary); font-weight: 700; font-size: 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-invoice-dollar" style="color: var(--accent);"></i> Rincian Tagihan
            </h3>
            <div style="display: flex; flex-direction: column; gap: 14px; font-size: 14px;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">Nomor Invoice</span>
                    <span style="font-weight: 700; color: var(--primary);">#INV-<?= str_pad($pesanan['id'], 5, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">Paket Wisata</span>
                    <span style="font-weight: 600;"><?= esc($pesanan['nama_paket']) ?> (<?= $pesanan['durasi_hari'] ?> Hari)</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">Tanggal Perjalanan</span>
                    <span style="font-weight: 600;"><?= date('d F Y', strtotime($pesanan['tanggal_perjalanan'])) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">Jumlah Peserta</span>
                    <span style="font-weight: 600;"><?= $pesanan['jumlah_peserta'] ?> Orang</span>
                </div>
                <?php if ($pesanan['nama_armada']): ?>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                        <span style="color: var(--text-secondary);">Sewa Transportasi</span>
                        <span style="font-weight: 600;"><?= esc($pesanan['nama_armada']) ?> (+ Rp <?= number_format($pesanan['harga_sewa'], 0, ',', '.') ?>)</span>
                    </div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; padding-top: 6px; font-size: 16px; font-weight: 700; color: var(--primary);">
                    <span>Total Yang Harus Dibayar</span>
                    <span style="color: var(--success);">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <!-- Bank Account options -->
        <div class="card" style="padding: 24px;">
            <h3 style="color: var(--primary); font-weight: 700; font-size: 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-building-columns" style="color: var(--accent);"></i> Rekening Tujuan Transfer Bank
            </h3>
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 20px;">Silakan lakukan transfer tepat sejumlah nominal tagihan ke salah satu rekening bank di bawah ini:</p>
            
            <div class="grid grid-2" style="gap: 16px;">
                <!-- Bank BCA -->
                <div style="background-color: var(--background); border-radius: var(--radius); padding: 16px; border: 1px solid var(--border); display: flex; align-items: center; gap: 16px;">
                    <div style="width: 50px; height: 50px; background: white; border-radius: 8px; border: 1px solid var(--border); display:flex; align-items:center; justify-content:center; font-weight: 700; color: #0066ae; font-size: 18px;">
                        BCA
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--text-secondary); font-weight: 500;">NOMOR REKENING</div>
                        <div style="font-size: 15px; font-weight: 700; color: var(--primary); letter-spacing: 0.5px; margin: 2px 0;">749 021 8392</div>
                        <div style="font-size: 11px; color: var(--text-secondary);">a/n PT BahariChain Indonesia</div>
                    </div>
                </div>

                <!-- Bank Mandiri -->
                <div style="background-color: var(--background); border-radius: var(--radius); padding: 16px; border: 1px solid var(--border); display: flex; align-items: center; gap: 16px;">
                    <div style="width: 50px; height: 50px; background: white; border-radius: 8px; border: 1px solid var(--border); display:flex; align-items:center; justify-content:center; font-weight: 700; color: #1c355e; font-size: 13px; text-align: center;">
                        MANDIRI
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--text-secondary); font-weight: 500;">NOMOR REKENING</div>
                        <div style="font-size: 15px; font-weight: 700; color: var(--primary); letter-spacing: 0.5px; margin: 2px 0;">142 0019 283921</div>
                        <div style="font-size: 11px; color: var(--text-secondary);">a/n PT BahariChain Indonesia</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: File Upload Form -->
    <div class="card" style="padding: 24px;">
        <h3 style="color: var(--primary); font-weight: 700; font-size: 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid var(--border); padding-bottom: 8px;">
            <i class="fa-solid fa-cloud-arrow-up" style="color: var(--accent);"></i> Konfirmasi Bukti Bayar
        </h3>
        
        <form action="<?= BASE_URL ?>index.php?module=wisatawan&action=payment" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">
            <input type="hidden" name="pesanan_id" value="<?= $pesanan['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <!-- Bank method select -->
            <div class="form-group">
                <label for="metode_pembayaran" class="form-label">Metode Transfer Ke</label>
                <select id="metode_pembayaran" name="metode_pembayaran" class="form-control" required>
                    <option value="">-- Pilih Rekening Bank --</option>
                    <option value="Transfer BCA">Transfer Bank BCA (749 021 8392)</option>
                    <option value="Transfer Mandiri">Transfer Bank Mandiri (142 0019 283921)</option>
                </select>
            </div>

            <!-- Disabled total display for safety -->
            <div class="form-group">
                <label class="form-label">Nominal Pembayaran</label>
                <input type="text" class="form-control" value="Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?>" style="background-color: var(--background); font-weight: 700; color: var(--primary);" readonly disabled>
            </div>

            <!-- File Upload input -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label for="bukti_transfer" class="form-label">Unggah Foto Struk / Bukti Transfer</label>
                <input type="file" id="bukti_transfer" name="bukti_transfer" class="form-control" accept="image/png, image/jpeg, image/jpg" required>
                <span style="font-size: 11px; color: var(--text-secondary); margin-top: 4px; display: block;">
                    Format berkas diperbolehkan: <strong>PNG, JPG, JPEG</strong>. Maksimal ukuran <strong>2MB</strong>.
                </span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px 20px; font-weight: 600; background-color: var(--success);">
                <i class="fa-solid fa-circle-check"></i> Kirim Bukti Pembayaran
            </button>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
