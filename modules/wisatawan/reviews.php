<?php
// modules/wisatawan/reviews.php
// BahariChain: Ulasan & Rating Wisatawan

require_role(['wisatawan']);

// ==============================================================================
// POST HANDLING: Menyimpan Ulasan Baru
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit') {
    // Validasi CSRF Token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Token keamanan tidak valid. Silakan coba lagi.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=reviews');
    }

    $paketId = (int)$_POST['paket_wisata_id'];
    $rating = (int)$_POST['rating'];
    $komentar = trim($_POST['komentar'] ?? '');

    // Validasi input wajib
    if ($paketId <= 0 || $rating < 1 || $rating > 5) {
        $_SESSION['error_message'] = "Rating bintang (1-5) wajib dipilih.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=reviews&paket_wisata_id=' . $paketId);
    }

    try {
        // Pengecekan keamanan kelayakan: Pastikan user memiliki pesanan paid/completed untuk paket ini
        $orderExists = db_query("
            SELECT id FROM pesanan 
            WHERE user_id = ? AND paket_wisata_id = ? AND status IN ('paid', 'completed')
            LIMIT 1
        ", [$_SESSION['user_id'], $paketId])->fetch();

        if (!$orderExists) {
            $_SESSION['error_message'] = "Anda tidak dapat memberikan ulasan untuk paket wisata yang belum pernah Anda pesan/selesaikan.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=reviews');
        }

        // Pengecekan duplikasi: Pastikan belum pernah mengulas paket ini
        $reviewExists = db_query("SELECT id FROM review WHERE user_id = ? AND paket_wisata_id = ?", [$_SESSION['user_id'], $paketId])->fetch();
        if ($reviewExists) {
            $_SESSION['error_message'] = "Anda sudah memberikan ulasan untuk paket wisata ini sebelumnya.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=reviews');
        }

        // Simpan ulasan ke database
        db_query("
            INSERT INTO review (user_id, paket_wisata_id, rating, komentar, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ", [$_SESSION['user_id'], $paketId, $rating, $komentar]);

        $_SESSION['success_message'] = "Terima kasih! Ulasan dan rating Anda telah berhasil disimpan.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=reviews');

    } catch (PDOException $e) {
        log_error("Wisatawan save review error: " . $e->getMessage());
        $_SESSION['error_message'] = "Terjadi kesalahan internal saat menyimpan ulasan.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=reviews');
    }
}

// ==============================================================================
// GET DISPLAY: Formulir Pengisian Ulasan Baru
// ==============================================================================
$paketId = isset($_GET['paket_wisata_id']) ? (int)$_GET['paket_wisata_id'] : 0;

if ($paketId > 0) {
    try {
        // Ambil data paket wisata
        $paket = db_query("SELECT * FROM paket_wisata WHERE id = ?", [$paketId])->fetch();
        
        if (!$paket) {
            redirect(BASE_URL . 'index.php?module=wisatawan&action=reviews');
        }

        // Validasi kelayakan ulasan di database
        $orderExists = db_query("
            SELECT id FROM pesanan 
            WHERE user_id = ? AND paket_wisata_id = ? AND status IN ('paid', 'completed')
            LIMIT 1
        ", [$_SESSION['user_id'], $paketId])->fetch();

        if (!$orderExists) {
            $_SESSION['error_message'] = "Akses ditolak. Anda belum pernah menyelesaikan perjalanan untuk paket ini.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=reviews');
        }

        // Validasi apakah sudah diulas sebelumnya
        $reviewExists = db_query("SELECT id FROM review WHERE user_id = ? AND paket_wisata_id = ?", [$_SESSION['user_id'], $paketId])->fetch();
        if ($reviewExists) {
            $_SESSION['error_message'] = "Anda sudah pernah mengulas paket wisata ini.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=reviews');
        }

    } catch (PDOException $e) {
        log_error("Wisatawan load review form error: " . $e->getMessage());
        redirect(BASE_URL . 'index.php?module=wisatawan&action=reviews');
    }

    $pageTitle = "Beri Ulasan - " . esc($paket['nama_paket']);
    require_once __DIR__ . '/../../includes/header.php';
    ?>

    <!-- Navigation Breadcrumbs -->
    <div style="margin-bottom: 20px;">
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=dashboard" style="color: var(--text-secondary); font-size: 14px;">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reviews" style="color: var(--text-secondary); font-size: 14px;">
            Ulasan & Rating
        </a>
        <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
        <span style="color: var(--primary); font-size: 14px; font-weight: 600;">Beri Ulasan</span>
    </div>

    <!-- Alert Notification -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i> <?= esc($_SESSION['error_message']) ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <!-- Review Form Card -->
    <div class="card" style="max-width: 600px; margin: 0 auto; padding: 30px;">
        <h3 style="color: var(--primary); font-weight: 700; font-size: 18px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-star" style="color: #f59e0b;"></i> Berikan Penilaian & Ulasan
        </h3>
        <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 24px;">Bagikan pengalaman liburan Anda dengan paket <strong><?= esc($paket['nama_paket']) ?></strong>.</p>

        <form action="<?= BASE_URL ?>index.php?module=wisatawan&action=reviews" method="POST">
            <input type="hidden" name="action" value="submit">
            <input type="hidden" name="paket_wisata_id" value="<?= $paket['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <!-- Star rating input -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" style="margin-bottom: 12px; display:block;">Pilih Rating Bintang</label>
                <div class="star-rating" style="font-size: 32px; display: flex; gap: 10px; cursor: pointer; color: #cbd5e1;">
                    <i class="fa-solid fa-star star-btn" data-value="1" title="Sangat Buruk"></i>
                    <i class="fa-solid fa-star star-btn" data-value="2" title="Buruk"></i>
                    <i class="fa-solid fa-star star-btn" data-value="3" title="Biasa Saja"></i>
                    <i class="fa-solid fa-star star-btn" data-value="4" title="Bagus"></i>
                    <i class="fa-solid fa-star star-btn" data-value="5" title="Sangat Memuaskan"></i>
                </div>
                <input type="hidden" name="rating" id="rating-value" value="0" required>
                <span id="rating-text" style="font-size: 13px; color: var(--text-secondary); margin-top: 8px; display: block; font-weight: 600; min-height: 20px;"></span>
            </div>

            <!-- Review comment area -->
            <div class="form-group" style="margin-bottom: 30px;">
                <label for="komentar" class="form-label">Komentar / Ulasan Tertulis</label>
                <textarea id="komentar" name="komentar" class="form-control" placeholder="Tuliskan pengalaman menyenangkan atau masukan untuk paket wisata ini..." rows="5" required></textarea>
            </div>

            <!-- Action buttons -->
            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Ulasan
                </button>
                <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reviews" class="btn btn-secondary" style="padding: 12px 20px;">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <!-- Interactive Star Selector Script -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const stars = document.querySelectorAll(".star-btn");
        const ratingInput = document.getElementById("rating-value");
        const ratingText = document.getElementById("rating-text");

        const ratingLabels = {
            1: "Sangat Buruk (1/5 Bintang)",
            2: "Buruk (2/5 Bintang)",
            3: "Biasa Saja (3/5 Bintang)",
            4: "Bagus (4/5 Bintang)",
            5: "Sangat Memuaskan! (5/5 Bintang)"
        };

        stars.forEach(star => {
            // Hover effect
            star.addEventListener("mouseover", function() {
                const val = this.getAttribute("data-value");
                highlightStars(val);
            });

            // Mouseout effect (revert to clicked value)
            star.addEventListener("mouseout", function() {
                const activeVal = parseInt(ratingInput.value) || 0;
                highlightStars(activeVal);
            });

            // Click effect
            star.addEventListener("click", function() {
                const val = this.getAttribute("data-value");
                ratingInput.value = val;
                ratingText.innerText = ratingLabels[val];
                ratingText.style.color = val >= 4 ? "var(--success)" : (val == 3 ? "var(--warning)" : "var(--danger)");
                highlightStars(val);
            });
        });

        function highlightStars(val) {
            stars.forEach(s => {
                const sVal = s.getAttribute("data-value");
                if (sVal <= val) {
                    s.style.color = "#fbbf24"; // yellow highlight
                } else {
                    s.style.color = "#cbd5e1"; // gray default
                }
            });
        }
    });
    </script>

    <?php
    require_once __DIR__ . '/../../includes/footer.php';

} else {
    // ==============================================================================
    // LIST VIEW: Daftar Ulasan & Paket Menunggu Ulasan
    // ==============================================================================
    try {
        // 1. Paket Wisata yang belum diulas (sudah dipesan oleh user dengan status paid/completed)
        $waitingReviews = db_query("
            SELECT DISTINCT pw.* 
            FROM pesanan p
            JOIN paket_wisata pw ON p.paket_wisata_id = pw.id
            LEFT JOIN review r ON r.paket_wisata_id = pw.id AND r.user_id = p.user_id
            WHERE p.user_id = ? 
              AND p.status IN ('paid', 'completed')
              AND r.id IS NULL
        ", [$_SESSION['user_id']])->fetchAll();

        // 2. Daftar riwayat ulasan yang sudah pernah ditulis oleh user
        $myReviews = db_query("
            SELECT r.*, pw.nama_paket, pw.durasi_hari 
            FROM review r
            JOIN paket_wisata pw ON r.paket_wisata_id = pw.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ", [$_SESSION['user_id']])->fetchAll();

    } catch (PDOException $e) {
        log_error("Wisatawan reviews dashboard load failure: " . $e->getMessage());
        $waitingReviews = [];
        $myReviews = [];
    }

    $pageTitle = "Ulasan & Rating Saya";
    require_once __DIR__ . '/../../includes/header.php';
    ?>

    <!-- Navigation Breadcrumbs -->
    <div style="margin-bottom: 20px;">
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=dashboard" style="color: var(--text-secondary); font-size: 14px;">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
        <span style="color: var(--primary); font-size: 14px; font-weight: 600;">Ulasan & Rating</span>
    </div>

    <!-- Header Section -->
    <div style="margin-bottom: 30px;">
        <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 8px;">Ulasan & Rating Paket Wisata</h1>
        <p style="color: var(--text-secondary);">Berikan umpan balik kepuasan perjalanan dan kelola ulasan yang telah Anda kirimkan.</p>
    </div>

    <!-- Alerts Notification -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> <?= esc($_SESSION['success_message']) ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i> <?= esc($_SESSION['error_message']) ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-3" style="align-items: start; gap: 30px; margin-bottom: 40px;">
        <!-- Left: Waiting for Review list (Span 1) -->
        <div class="card" style="padding: 24px;">
            <h3 style="color: var(--primary); font-weight: 700; font-size: 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid var(--border); padding-bottom: 8px;">
                <i class="fa-solid fa-clock-rotate-left" style="color: var(--accent);"></i> Menunggu Ulasan
            </h3>
            
            <?php if (empty($waitingReviews)): ?>
                <div style="text-align: center; padding: 30px 10px; border: 1px dashed var(--border); border-radius: 8px; background-color: var(--background);">
                    <i class="fa-solid fa-circle-check" style="font-size: 32px; color: var(--success); opacity: 0.8; margin-bottom: 10px;"></i>
                    <p style="color: var(--text-secondary); font-size: 13px; margin: 0; line-height: 1.5;">Hebat! Semua perjalanan Anda telah diulas.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <?php foreach ($waitingReviews as $wr): ?>
                        <div style="background-color: var(--background); padding: 14px; border-radius: 8px; border: 1px solid var(--border); display: flex; flex-direction: column; gap: 10px;">
                            <div style="flex: 1;">
                                <h4 style="font-size: 13px; font-weight: 700; color: var(--primary); margin: 0 0 2px 0;"><?= esc($wr['nama_paket']) ?></h4>
                                <span style="font-size: 11px; color: var(--text-secondary);"><i class="fa-solid fa-clock"></i> <?= $wr['durasi_hari'] ?> Hari</span>
                            </div>
                            <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reviews&paket_wisata_id=<?= $wr['id'] ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 11px; align-self: flex-start; background-color: var(--accent);">
                                <i class="fa-solid fa-star"></i> Beri Ulasan
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: My Reviews History (Span 2) -->
        <div class="card" style="grid-column: span 2; padding: 24px;">
            <h3 style="color: var(--primary); font-weight: 700; font-size: 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid var(--border); padding-bottom: 8px;">
                <i class="fa-solid fa-comments" style="color: var(--accent);"></i> Riwayat Ulasan Saya
            </h3>

            <?php if (empty($myReviews)): ?>
                <div style="text-align: center; padding: 40px 10px;">
                    <i class="fa-solid fa-message" style="font-size: 48px; color: var(--text-secondary); opacity: 0.4; margin-bottom: 16px;"></i>
                    <h4 style="color: var(--text-primary); font-weight: 600;">Belum Ada Ulasan</h4>
                    <p style="color: var(--text-secondary); font-size: 13px; margin-top: 6px;">Ulasan yang Anda tulis akan muncul di riwayat ini.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($myReviews as $r): ?>
                        <div style="background-color: var(--background); padding: 16px; border-radius: var(--radius); border: 1px solid var(--border); transition: var(--transition);">
                            <div style="display: flex; justify-content: space-between; align-items: start; gap: 12px; margin-bottom: 10px;">
                                <div>
                                    <h4 style="font-size: 14px; font-weight: 700; color: var(--primary); margin: 0 0 2px 0;"><?= esc($r['nama_paket']) ?></h4>
                                    <span style="font-size: 11px; color: var(--text-secondary);"><i class="fa-solid fa-clock"></i> <?= $r['durasi_hari'] ?> Hari</span>
                                </div>
                                <span style="font-size: 11px; color: var(--text-secondary);"><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></span>
                            </div>

                            <!-- Star icons -->
                            <div style="color: #fbbf24; font-size: 14px; margin-bottom: 10px; display: flex; gap: 2px;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-<?= $i <= $r['rating'] ? 'solid' : 'regular' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>

                            <!-- Review comment text -->
                            <p style="color: var(--text-secondary); font-size: 13px; line-height: 1.6; margin: 0; background-color: white; padding: 12px; border-radius: 8px; border: 1px solid var(--border);">
                                <?= esc($r['komentar']) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    require_once __DIR__ . '/../../includes/footer.php';
}
?>
