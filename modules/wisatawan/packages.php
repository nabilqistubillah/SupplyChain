<?php
// modules/wisatawan/packages.php
// BahariChain: Halaman Katalog & Reservasi Paket Wisata (Wisatawan Role)

// Pastikan hanya role wisatawan yang dapat mengakses halaman ini
require_role(['wisatawan']);

// ==============================================================================
// POST HANDLING: Pembuatan Reservasi Baru
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    // Validasi CSRF Token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Token keamanan kedaluwarsa atau tidak valid. Silakan coba lagi.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=packages&id=' . (int)$_POST['paket_wisata_id']);
    }

    $paketId = (int)$_POST['paket_wisata_id'];
    $tanggal = trim($_POST['tanggal_perjalanan'] ?? '');
    $peserta = (int)($_POST['jumlah_peserta'] ?? 1);
    $transportId = isset($_POST['transportasi_id']) && $_POST['transportasi_id'] !== '' ? (int)$_POST['transportasi_id'] : null;

    // Validasi input wajib
    if ($paketId <= 0 || empty($tanggal) || $peserta <= 0) {
        $_SESSION['error_message'] = "Semua kolom pemesanan wajib diisi dengan benar.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=packages&id=' . $paketId);
    }

    // Validasi tanggal tidak di masa lalu
    $today = date('Y-m-d');
    if ($tanggal < $today) {
        $_SESSION['error_message'] = "Tanggal perjalanan tidak boleh di masa lalu.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=packages&id=' . $paketId);
    }

    try {
        // Ambil data paket untuk memeriksa keberadaan & tarif dasar
        $paket = db_query("SELECT * FROM paket_wisata WHERE id = ?", [$paketId])->fetch();
        if (!$paket) {
            $_SESSION['error_message'] = "Paket wisata tidak ditemukan di sistem.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=packages');
        }

        // Kalkulasi harga dasar di sisi server
        $hargaPaket = (float)$paket['harga'];
        $totalHarga = $hargaPaket * $peserta;

        // Validasi dan kalkulasi tambahan untuk sewa transportasi jika dipilih
        if ($transportId !== null) {
            // Ambil data armada dan pastikan status ketersediaannya tersedia
            $trans = db_query("SELECT * FROM transportasi WHERE id = ? AND status_ketersediaan = 'tersedia'", [$transportId])->fetch();
            if (!$trans) {
                $_SESSION['error_message'] = "Armada transportasi yang dipilih tidak tersedia saat ini.";
                redirect(BASE_URL . 'index.php?module=wisatawan&action=packages&id=' . $paketId);
            }
            
            // Tambahkan tarif rental armada secara flat
            $totalHarga += (float)$trans['harga_sewa'];
        }

        // Simpan pesanan baru ke database dengan status pending
        db_query("
            INSERT INTO pesanan (user_id, paket_wisata_id, transportasi_id, tanggal_perjalanan, jumlah_peserta, total_harga, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
        ", [$_SESSION['user_id'], $paketId, $transportId, $tanggal, $peserta, $totalHarga]);

        // Kirim notifikasi transaksi baru kepada user
        db_query("
            INSERT INTO notifikasi (user_id, judul, pesan, tipe, is_read, created_at)
            VALUES (?, 'Reservasi Baru Dibuat', ?, 'transaksi', 0, NOW())
        ", [
            $_SESSION['user_id'],
            "Pemesanan paket '" . $paket['nama_paket'] . "' berhasil dibuat. Segera selesaikan pembayaran sebesar Rp " . number_format($totalHarga, 0, ',', '.') . " dan unggah bukti transfer."
        ]);

        $_SESSION['success_message'] = "Reservasi berhasil dibuat! Silakan selesaikan pembayaran dan unggah bukti transfer di bawah ini.";
        
        // Arahkan wisatawan ke halaman riwayat pesanan (Reservations)
        redirect(BASE_URL . 'index.php?module=wisatawan&action=reservations');

    } catch (PDOException $e) {
        log_error("Wisatawan booking transaction failure: " . $e->getMessage());
        $_SESSION['error_message'] = "Terjadi kesalahan internal saat membuat reservasi. Silakan hubungi admin.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=packages&id=' . $paketId);
    }
}

// ==============================================================================
// DISPLAY LOGIC
// ==============================================================================
$paketId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($paketId > 0) {
    // ==============================================================================
    // DETAIL VIEW & BOOKING FORM
    // ==============================================================================
    try {
        // Ambil rincian paket wisata
        $paket = db_query("SELECT * FROM paket_wisata WHERE id = ?", [$paketId])->fetch();

        if (!$paket) {
            redirect(BASE_URL . 'index.php?module=wisatawan&action=packages');
        }

        $pageTitle = esc($paket['nama_paket']) . " - Detail Paket";

        // Ambil daftar destinasi rute perjalanan paket ini
        $destinations = db_query("
            SELECT d.*, dpd.hari_ke, dpd.urutan
            FROM destinasi d
            JOIN detail_paket_destinasi dpd ON d.id = dpd.destinasi_id
            WHERE dpd.paket_wisata_id = ?
            ORDER BY dpd.hari_ke ASC, dpd.urutan ASC
        ", [$paketId])->fetchAll();

        // Ambil daftar transportasi yang beroperasi di pos terdekat destinasi-destinasi tersebut
        $transportation = db_query("
            SELECT t.*, d.nama as destinasi_nama 
            FROM transportasi t
            JOIN destinasi d ON t.destinasi_id = d.id
            WHERE t.status_ketersediaan = 'tersedia'
              AND t.destinasi_id IN (
                  SELECT destinasi_id FROM detail_paket_destinasi WHERE paket_wisata_id = ?
              )
            ORDER BY t.harga_sewa ASC
        ", [$paketId])->fetchAll();

    } catch (PDOException $e) {
        log_error("Wisatawan package detail database error: " . $e->getMessage());
        $paket = null;
        $destinations = [];
        $transportation = [];
    }

    require_once __DIR__ . '/../../includes/header.php';
    ?>

    <!-- Navigation Breadcrumbs -->
    <div style="margin-bottom: 20px;">
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=dashboard" style="color: var(--text-secondary); font-size: 14px;">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=packages" style="color: var(--text-secondary); font-size: 14px;">
            Paket Wisata
        </a>
        <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
        <span style="color: var(--primary); font-size: 14px; font-weight: 600;"><?= esc($paket['nama_paket']) ?></span>
    </div>

    <!-- Error/Alert Notification -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i> <?= esc($_SESSION['error_message']) ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <!-- Main Detail Grid -->
    <div class="grid grid-3" style="align-items: start; gap: 30px;">
        <!-- Left: Package itinerary & destinations (Span 2) -->
        <div style="grid-column: span 2; display: flex; flex-direction: column; gap: 30px;">
            
            <!-- Hero Card -->
            <div class="card" style="padding: 0; overflow: hidden;">
                <div style="height: 240px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); display: flex; align-items: center; justify-content: center; color: white; position: relative;">
                    <i class="fa-solid fa-suitcase" style="font-size: 80px; opacity: 0.15;"></i>
                    <div style="position: absolute; bottom: 20px; left: 24px;">
                        <span style="background: var(--accent); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-clock"></i> Durasi: <?= $paket['durasi_hari'] ?> Hari
                        </span>
                        <h1 style="font-size: 28px; font-weight: 700; margin-top: 8px; margin-bottom: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.15);"><?= esc($paket['nama_paket']) ?></h1>
                    </div>
                </div>
                <div style="padding: 24px;">
                    <h3 style="color: var(--primary); font-weight: 700; font-size: 16px; margin-bottom: 12px;">Deskripsi & Agenda Perjalanan</h3>
                    <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.8; white-space: pre-line;"><?= esc($paket['deskripsi']) ?></p>
                </div>
            </div>

            <!-- Destinations itinerary map -->
            <div>
                <h3 style="font-weight: 700; color: var(--primary); font-size: 18px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-map-location-dot" style="color: var(--accent);"></i> Rute & Destinasi Kunjungan
                </h3>

                <?php if (empty($destinations)): ?>
                    <div class="card" style="padding: 30px; text-align: center;">
                        <p style="color: var(--text-secondary); margin: 0;">Belum ada rute destinasi yang dimasukkan untuk paket ini.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php foreach ($destinations as $d): ?>
                            <div class="card" style="padding: 20px; display: flex; gap: 20px; align-items: center; border-left: 5px solid var(--accent); transition: var(--transition);">
                                <!-- Number block representing day/sequence -->
                                <div style="width: 48px; height: 48px; background-color: rgba(14, 165, 233, 0.08); color: var(--accent); border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">
                                    <span>H-<?= $d['hari_ke'] ?></span>
                                    <span style="font-size: 10px; opacity: 0.8; font-weight: 500;">No.<?= $d['urutan'] ?></span>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <h4 style="font-weight: 700; color: var(--primary); font-size: 15px; margin: 0 0 4px 0;"><?= esc($d['nama']) ?></h4>
                                    <span style="font-size: 11px; background-color: var(--background); color: var(--text-secondary); padding: 2px 8px; border-radius: 4px; display: inline-block; margin-bottom: 6px;">
                                        <i class="fa-solid fa-location-dot"></i> <?= esc($d['lokasi']) ?>
                                    </span>
                                    <p style="color: var(--text-secondary); font-size: 12px; line-height: 1.6; margin: 0; text-overflow: ellipsis; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                        <?= esc($d['deskripsi']) ?>
                                    </p>
                                </div>
                                <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=destinations&id=<?= $d['id'] ?>" class="btn btn-secondary" style="padding: 8px 14px; font-size: 11px;">
                                    Detail Lokasi
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Reservation Form -->
        <div class="card" style="padding: 24px; position: sticky; top: 20px;">
            <h3 style="color: var(--primary); font-weight: 700; font-size: 18px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid var(--border); padding-bottom: 8px;">
                <i class="fa-solid fa-calendar-check" style="color: var(--accent);"></i> Formulir Reservasi
            </h3>

            <div style="background-color: rgba(15, 76, 129, 0.04); border-radius: var(--radius); padding: 16px; margin-bottom: 20px; border: 1px solid rgba(15, 76, 129, 0.08);">
                <span style="font-size: 12px; color: var(--text-secondary); display: block; text-transform: uppercase; font-weight: 500;">Tarif Paket</span>
                <span style="font-size: 24px; font-weight: 700; color: var(--primary);" id="base-package-price" data-raw-price="<?= $paket['harga'] ?>">
                    Rp <?= number_format($paket['harga'], 0, ',', '.') ?> <span style="font-size: 12px; font-weight: 500; color: var(--text-secondary);">/ pax</span>
                </span>
            </div>

            <form action="<?= BASE_URL ?>index.php?module=wisatawan&action=packages" method="POST">
                <!-- CSRF Token & Actions -->
                <input type="hidden" name="action" value="book">
                <input type="hidden" name="paket_wisata_id" value="<?= $paket['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <!-- Date Travel Picker -->
                <div class="form-group">
                    <label for="tanggal_perjalanan" class="form-label">Tanggal Keberangkatan</label>
                    <input type="date" id="tanggal_perjalanan" name="tanggal_perjalanan" class="form-control" min="<?= date('Y-m-d') ?>" required>
                </div>

                <!-- Number of travelers -->
                <div class="form-group">
                    <label for="jumlah_peserta" class="form-label">Jumlah Peserta (Pax)</label>
                    <input type="number" id="jumlah_peserta" name="jumlah_peserta" class="form-control" min="1" value="1" required>
                </div>

                <!-- Transportation Rental (Optional) -->
                <div class="form-group" style="margin-bottom: 24px;">
                    <label for="transportasi_id" class="form-label">Sewa Transportasi (Opsional)</label>
                    <select id="transportasi_id" name="transportasi_id" class="form-control">
                        <option value="">-- Tidak Sewa Armada --</option>
                        <?php foreach ($transportation as $t): 
                            $tLabel = esc($t['nama_armada']) . " (Kapasitas: " . $t['kapasitas'] . " pax)";
                        ?>
                            <option value="<?= $t['id'] ?>" data-price="<?= $t['harga_sewa'] ?>">
                                <?= $tLabel ?> (+ Rp <?= number_format($t['harga_sewa'], 0, ',', '.') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span style="font-size: 11px; color: var(--text-secondary); margin-top: 4px; display: block;">
                        *Armada terafiliasi dengan lokasi wisata yang dikunjungi.
                    </span>
                </div>

                <!-- Live Price Estimate summary -->
                <div style="border-top: 2px dashed var(--border); padding-top: 16px; margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-size: 13px; color: var(--text-secondary);">Subtotal Paket</span>
                        <span style="font-size: 13px; font-weight: 600; color: var(--text-primary);" id="subtotal-pax">Rp <?= number_format($paket['harga'], 0, ',', '.') ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-size: 13px; color: var(--text-secondary);">Sewa Transportasi</span>
                        <span style="font-size: 13px; font-weight: 600; color: var(--text-primary);" id="subtotal-transport">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 14px; font-weight: 700; color: var(--primary);">Total Pembayaran</span>
                        <span style="font-size: 18px; font-weight: 700; color: var(--success);" id="total-price-display">Rp <?= number_format($paket['harga'], 0, ',', '.') ?></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px 20px; font-weight: 600;">
                    <i class="fa-solid fa-cart-shopping"></i> Buat Pesanan Sekarang
                </button>
            </form>
        </div>
    </div>

    <!-- Live Calculation Javascript -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const basePrice = parseFloat(document.getElementById("base-package-price").getAttribute("data-raw-price")) || 0;
        const inputPeserta = document.getElementById("jumlah_peserta");
        const selectTransport = document.getElementById("transportasi_id");
        
        const displaySubtotalPax = document.getElementById("subtotal-pax");
        const displaySubtotalTrans = document.getElementById("subtotal-transport");
        const displayTotal = document.getElementById("total-price-display");

        function recalculate() {
            const count = parseInt(inputPeserta.value) || 0;
            const subtotalPax = basePrice * count;
            
            let subtotalTrans = 0;
            if (selectTransport && selectTransport.value) {
                const selectedOption = selectTransport.options[selectTransport.selectedIndex];
                subtotalTrans = parseFloat(selectedOption.getAttribute("data-price")) || 0;
            }

            const grandTotal = subtotalPax + subtotalTrans;

            // Update DOM text
            displaySubtotalPax.innerText = "Rp " + subtotalPax.toLocaleString("id-ID");
            displaySubtotalTrans.innerText = "Rp " + subtotalTrans.toLocaleString("id-ID");
            displayTotal.innerText = "Rp " + grandTotal.toLocaleString("id-ID");
        }

        inputPeserta.addEventListener("input", recalculate);
        inputPeserta.addEventListener("change", recalculate);
        if (selectTransport) {
            selectTransport.addEventListener("change", recalculate);
        }
    });
    </script>

    <?php
    require_once __DIR__ . '/../../includes/footer.php';

} else {
    // ==============================================================================
    // LIST VIEW
    // ==============================================================================
    try {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Query utama dengan aggregasi review rating
        $sql = "
            SELECT pw.*,
                   (SELECT IFNULL(AVG(r.rating), 0) FROM review r WHERE r.paket_wisata_id = pw.id) as avg_rating,
                   (SELECT COUNT(r.id) FROM review r WHERE r.paket_wisata_id = pw.id) as total_reviews
            FROM paket_wisata pw
            WHERE 1=1
        ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (pw.nama_paket LIKE ? OR pw.deskripsi LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $sql .= " ORDER BY pw.id ASC";
        $packages = db_query($sql, $params)->fetchAll();

    } catch (PDOException $e) {
        log_error("Wisatawan packages catalog database error: " . $e->getMessage());
        $packages = [];
    }

    $pageTitle = "Katalog Paket Wisata";
    require_once __DIR__ . '/../../includes/header.php';
    ?>

    <!-- Navigation Breadcrumbs -->
    <div style="margin-bottom: 20px;">
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=dashboard" style="color: var(--text-secondary); font-size: 14px;">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
        <span style="color: var(--primary); font-size: 14px; font-weight: 600;">Paket Wisata</span>
    </div>

    <!-- Header Section -->
    <div style="margin-bottom: 30px;">
        <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 8px;">Pilih Paket Wisata Impian Anda</h1>
        <p style="color: var(--text-secondary);">Dapatkan penawaran paket wisata all-in-one di Madura dengan tarif ekonomis dan jadwal terstruktur.</p>
    </div>

    <!-- Search input filter -->
    <div class="card" style="padding: 20px; margin-bottom: 30px;">
        <form method="GET" action="<?= BASE_URL ?>index.php" style="display: flex; gap: 16px; align-items: flex-end;">
            <input type="hidden" name="module" value="wisatawan">
            <input type="hidden" name="action" value="packages">

            <div style="flex: 1;">
                <label for="search-pack" class="form-label" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Cari Nama atau Kata Kunci Paket</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="text" id="search-pack" name="search" class="form-control" placeholder="Ketik nama paket wisata, camping, pantai..." value="<?= esc($search) ?>" style="padding-left: 45px;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 12px 24px; flex-shrink: 0;">
                <i class="fa-solid fa-compass"></i> Cari Paket
            </button>
            <?php if ($search !== ''): ?>
                <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=packages" class="btn btn-secondary" style="padding: 12px 20px; flex-shrink: 0;">
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Success Feedback Notification -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> <?= esc($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <!-- Packages Catalog Grid -->
    <?php if (empty($packages)): ?>
        <div class="card" style="text-align: center; padding: 60px 20px;">
            <i class="fa-solid fa-box-open" style="font-size: 54px; color: var(--text-secondary); opacity: 0.4; margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-primary); font-weight: 600;">Paket Wisata Tidak Ditemukan</h3>
            <p style="color: var(--text-secondary); margin-top: 8px;">Silakan cari menggunakan nama atau kata kunci lainnya.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-3">
            <?php foreach ($packages as $p): 
                $avgRat = (float)$p['avg_rating'];
                $totRev = (int)$p['total_reviews'];
            ?>
                <div class="card" style="display: flex; flex-direction: column; overflow: hidden; padding: 0; transition: var(--transition);">
                    <!-- Image representation -->
                    <div style="height: 180px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); display: flex; align-items: center; justify-content: center; color: white; position: relative;">
                        <i class="fa-solid fa-suitcase" style="font-size: 64px; opacity: 0.25;"></i>
                        
                        <!-- Duration Tag -->
                        <div style="position: absolute; top: 12px; left: 12px; background: rgba(15, 76, 129, 0.9); color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                            <i class="fa-solid fa-clock" style="margin-right: 4px;"></i> <?= $p['durasi_hari'] ?> Hari
                        </div>

                        <!-- Rating Badge -->
                        <?php if ($totRev > 0): ?>
                            <div style="position: absolute; top: 12px; right: 12px; background: rgba(245, 158, 11, 0.95); color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                <i class="fa-solid fa-star" style="margin-right: 4px; color: #ffe066;"></i><?= number_format($avgRat, 1) ?> (<?= $totRev ?>)
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Content Details -->
                    <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                        <h3 style="font-weight: 700; color: var(--primary); font-size: 16px; margin: 0 0 8px 0; min-height: 48px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= esc($p['nama_paket']) ?>
                        </h3>
                        <p style="color: var(--text-secondary); font-size: 13px; line-height: 1.6; margin-bottom: 20px; flex: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= esc($p['deskripsi']) ?>
                        </p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 14px;">
                            <div>
                                <span style="font-size: 10px; color: var(--text-secondary); display: block; text-transform: uppercase;">Tarif Mulai</span>
                                <span style="font-weight: 700; color: var(--primary-light); font-size: 15px;">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span>
                            </div>
                            <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=packages&id=<?= $p['id'] ?>" class="btn btn-primary" style="padding: 8px 14px; font-size: 12px;">
                                Detail & Pesan
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    require_once __DIR__ . '/../../includes/footer.php';
}
?>
