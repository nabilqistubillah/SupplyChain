<?php
// modules/wisatawan/dashboard.php
// BahariChain: Wisatawan Dashboard

// Security check: only wisatawan can access
require_role(['wisatawan']);

$pageTitle = "Dashboard Wisatawan";

// Fetch statistics
try {
    // Total destinasi
    $destCount = db_query("SELECT COUNT(*) as total FROM destinasi")->fetch()['total'];
    
    // Total paket wisata
    $paketCount = db_query("SELECT COUNT(*) as total FROM paket_wisata")->fetch()['total'];
    
    // User's reservations
    $pesananCount = db_query("SELECT COUNT(*) as total FROM pesanan WHERE user_id = ?", [$_SESSION['user_id']])->fetch()['total'];
    
    // Pending payments
    $pendingPayments = db_query("SELECT COUNT(*) as total FROM pembayaran WHERE status = 'unpaid'")->fetch()['total'];
    
    // User's reviews
    $reviewCount = db_query("SELECT COUNT(*) as total FROM review WHERE user_id = ?", [$_SESSION['user_id']])->fetch()['total'];
    
} catch (PDOException $e) {
    log_error("Wisatawan dashboard database error: " . $e->getMessage());
    $destCount = 0;
    $paketCount = 0;
    $pesananCount = 0;
    $pendingPayments = 0;
    $reviewCount = 0;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Hero Banner -->
<div style="background: linear-gradient(135deg, #0284c7, #38bdf8); color: white; border-radius: var(--radius); padding: 40px 30px; margin-bottom: 40px; box-shadow: var(--shadow-lg);">
    <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
        <i class="fa-solid fa-umbrella-beach"></i> Selamat Datang, <?= esc($_SESSION['full_name'] ?? $_SESSION['username']) ?>!
    </h1>
    <p style="font-size: 14px; opacity: 0.9;">Jelajahi destinasi bahari menakjubkan dan pesan paket wisata impian Anda di BahariChain</p>
</div>

<!-- Quick Stats -->
<div class="grid grid-4" style="margin-bottom: 40px;">
    <div class="card" style="padding: 20px; text-align: center; border-top: 3px solid #0284c7;">
        <p style="color: var(--text-secondary); font-size: 12px; margin: 0 0 4px 0;">Destinasi Tersedia</p>
        <h2 style="font-size: 28px; font-weight: 700; color: #0284c7; margin: 0;"><?= $destCount ?></h2>
    </div>
    <div class="card" style="padding: 20px; text-align: center; border-top: 3px solid #10b981;">
        <p style="color: var(--text-secondary); font-size: 12px; margin: 0 0 4px 0;">Paket Wisata</p>
        <h2 style="font-size: 28px; font-weight: 700; color: #10b981; margin: 0;"><?= $paketCount ?></h2>
    </div>
    <div class="card" style="padding: 20px; text-align: center; border-top: 3px solid #f59e0b;">
        <p style="color: var(--text-secondary); font-size: 12px; margin: 0 0 4px 0;">Pesanan Saya</p>
        <h2 style="font-size: 28px; font-weight: 700; color: #f59e0b; margin: 0;"><?= $pesananCount ?></h2>
    </div>
    <div class="card" style="padding: 20px; text-align: center; border-top: 3px solid #8b5cf6;">
        <p style="color: var(--text-secondary); font-size: 12px; margin: 0 0 4px 0;">Ulasan Saya</p>
        <h2 style="font-size: 28px; font-weight: 700; color: #8b5cf6; margin: 0;"><?= $reviewCount ?></h2>
    </div>
</div>

<!-- Main Features Grid -->
<h2 style="font-weight: 700; color: var(--primary); margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
    <i class="fa-solid fa-star"></i> Fitur Utama
</h2>

<div class="grid grid-3" style="gap: 20px;">
    <!-- Feature 1: Jelajahi Destinasi -->
    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=destinations" style="text-decoration: none;">
        <div class="card" style="padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; border: 2px solid transparent;">
            <i class="fa-solid fa-umbrella-beach" style="font-size: 48px; color: #0284c7; margin-bottom: 16px;"></i>
            <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Jelajahi Destinasi</h3>
            <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Temukan pantai dan destinasi bahari terindah di Madura</p>
            <div style="margin-top: 16px; display: inline-block; padding: 6px 12px; background: #ecf0ff; color: #0284c7; border-radius: 6px; font-size: 12px; font-weight: 600;">
                <?= $destCount ?> Destinasi Tersedia
            </div>
        </div>
    </a>

    <!-- Feature 2: Paket Wisata -->
    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=packages" style="text-decoration: none;">
        <div class="card" style="padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; border: 2px solid transparent;">
            <i class="fa-solid fa-box" style="font-size: 48px; color: #10b981; margin-bottom: 16px;"></i>
            <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Paket Wisata</h3>
            <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Pesan paket liburan all-in-one dengan harga terjangkau</p>
            <div style="margin-top: 16px; display: inline-block; padding: 6px 12px; background: #ecfdf5; color: #10b981; border-radius: 6px; font-size: 12px; font-weight: 600;">
                <?= $paketCount ?> Paket Tersedia
            </div>
        </div>
    </a>

    <!-- Feature 3: Pesanan & Tiket -->
    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reservations" style="text-decoration: none;">
        <div class="card" style="padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; border: 2px solid transparent;">
            <i class="fa-solid fa-cart-shopping" style="font-size: 48px; color: #f59e0b; margin-bottom: 16px;"></i>
            <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Pesanan & Tiket Saya</h3>
            <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Pantau status pesanan dan download e-tiket perjalanan</p>
            <div style="margin-top: 16px; display: inline-block; padding: 6px 12px; background: #fffbeb; color: #f59e0b; border-radius: 6px; font-size: 12px; font-weight: 600;">
                <?= $pesananCount ?> Pesanan Aktif
            </div>
        </div>
    </a>

    <!-- Feature 4: Ulasan & Rating -->
    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reviews" style="text-decoration: none;">
        <div class="card" style="padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; border: 2px solid transparent;">
            <i class="fa-solid fa-star" style="font-size: 48px; color: #8b5cf6; margin-bottom: 16px;"></i>
            <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Ulasan & Rating</h3>
            <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Bagikan pengalaman wisata dan beri rating destinasi favorit</p>
            <div style="margin-top: 16px; display: inline-block; padding: 6px 12px; background: #f3f0ff; color: #8b5cf6; border-radius: 6px; font-size: 12px; font-weight: 600;">
                <?= $reviewCount ?> Ulasan Saya
            </div>
        </div>
    </a>

    <!-- Feature 5: Profil Saya -->
    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=profile" style="text-decoration: none;">
        <div class="card" style="padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; border: 2px solid transparent;">
            <i class="fa-solid fa-user" style="font-size: 48px; color: #ec4899; margin-bottom: 16px;"></i>
            <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Profil Saya</h3>
            <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Perbarui data pribadi, alamat, dan informasi kontak</p>
            <div style="margin-top: 16px; display: inline-block; padding: 6px 12px; background: #fce7f3; color: #ec4899; border-radius: 6px; font-size: 12px; font-weight: 600;">
                Kelola Akun
            </div>
        </div>
    </a>

    <!-- Feature 6: Notifikasi -->
    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=notifications" style="text-decoration: none;">
        <div class="card" style="padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; border: 2px solid transparent;">
            <i class="fa-solid fa-bell" style="font-size: 48px; color: #06b6d4; margin-bottom: 16px;"></i>
            <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Notifikasi</h3>
            <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Terima update tentang pesanan, promo, dan informasi penting</p>
            <div style="margin-top: 16px; display: inline-block; padding: 6px 12px; background: #ecf8ff; color: #06b6d4; border-radius: 6px; font-size: 12px; font-weight: 600;">
                Lihat Semua
            </div>
        </div>
    </a>
</div>

<!-- Info Section -->
<div class="card" style="padding: 30px; background: linear-gradient(135deg, rgba(2, 132, 199, 0.05), rgba(56, 189, 248, 0.05)); border-left: 4px solid #0284c7; margin-top: 40px;">
    <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-info-circle"></i> Tips Wisata
    </h3>
    <ul style="color: var(--text-secondary); font-size: 13px; margin: 0; padding-left: 20px; line-height: 1.8;">
        <li>Jelajahi destinasi bahari terbaik dan pilih paket yang sesuai dengan budget Anda</li>
        <li>Daftarkan akomodasi dan transportasi yang Anda butuhkan dalam satu pesanan</li>
        <li>Unggah bukti pembayaran dan tunggu verifikasi dari tim administrator</li>
        <li>Bagikan pengalaman Anda dan bantu wisatawan lain memilih destinasi terbaik</li>
    </ul>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
