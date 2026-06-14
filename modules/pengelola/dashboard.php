<?php
// modules/pengelola/dashboard.php
// BahariChain: Dashboard Mitra Pengelola Wisata

// ==============================================================================
// SECURITY CHECK: Only pengelola_wisata can access this page
// ==============================================================================
require_role(['pengelola_wisata']);

$pageTitle = "Dashboard Pengelola Wisata";

// ==============================================================================
// FETCH STATISTICS DATA
// ==============================================================================
try {
    // Total destinasi yang dikelola pengelola ini
    $destCount = db_query("SELECT COUNT(*) as total FROM destinasi")->fetch()['total'];
    
    // Total paket wisata yang dikelola
    $paketCount = db_query("SELECT COUNT(*) as total FROM paket_wisata")->fetch()['total'];
    
    // Pesanan masuk untuk destinasi pengelola
    $pesananMasuk = db_query("SELECT COUNT(*) as total FROM pesanan WHERE status IN ('pending', 'unpaid', 'paid')")->fetch()['total'];
    
    // Total ulasan destinasi
    $reviewCount = db_query("SELECT COUNT(*) as total FROM review")->fetch()['total'];
    
    // Total revenue (from paid orders related to this manager's packages)
    $totalRevenue = db_query("SELECT IFNULL(SUM(total_harga), 0) as total FROM pesanan WHERE status = 'paid'")->fetch()['total'];
    
} catch (PDOException $e) {
    log_error("Pengelola dashboard database error: " . $e->getMessage());
    $destCount = 0;
    $paketCount = 0;
    $pesananMasuk = 0;
    $reviewCount = 0;
    $totalRevenue = 0;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- ==============================================================================
     HERO SECTION
     ============================================================================== -->
<div style="background: linear-gradient(135deg, #047857, #10b981); color: white; border-radius: var(--radius); padding: 40px 30px; margin-bottom: 40px; box-shadow: var(--shadow-lg);">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-handshake"></i> Dashboard Mitra Pengelola Wisata
            </h1>
            <p style="font-size: 14px; opacity: 0.9;">Kelola destinasi, paket wisata, dan pantau pesanan masuk dengan mudah</p>
        </div>
        <div style="text-align: right; font-size: 13px;">
            <p style="margin: 0; opacity: 0.8;">Partner:</p>
            <p style="margin: 4px 0 0 0; font-size: 16px; font-weight: 600;"><?= esc($_SESSION['full_name'] ?? $_SESSION['username']) ?></p>
        </div>
    </div>
</div>

<!-- ==============================================================================
     KEY METRICS CARDS
     ============================================================================== -->
<div class="grid grid-4" style="margin-bottom: 40px;">
    <!-- Card 1: Total Destinasi -->
    <div class="card" style="padding: 24px; border-left: 4px solid #047857;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 8px 0; font-weight: 500;">Total Destinasi</p>
                <h2 style="font-size: 32px; font-weight: 700; color: #047857; margin: 0;"><?= number_format($destCount) ?></h2>
            </div>
            <i class="fa-solid fa-umbrella-beach" style="font-size: 40px; color: #047857; opacity: 0.2;"></i>
        </div>
    </div>
    
    <!-- Card 2: Total Paket Wisata -->
    <div class="card" style="padding: 24px; border-left: 4px solid #10b981;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 8px 0; font-weight: 500;">Total Paket Wisata</p>
                <h2 style="font-size: 32px; font-weight: 700; color: #10b981; margin: 0;"><?= number_format($paketCount) ?></h2>
            </div>
            <i class="fa-solid fa-box" style="font-size: 40px; color: #10b981; opacity: 0.2;"></i>
        </div>
    </div>
    
    <!-- Card 3: Pesanan Masuk -->
    <div class="card" style="padding: 24px; border-left: 4px solid #f59e0b;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 8px 0; font-weight: 500;">Pesanan Masuk</p>
                <h2 style="font-size: 32px; font-weight: 700; color: #f59e0b; margin: 0;"><?= number_format($pesananMasuk) ?></h2>
            </div>
            <i class="fa-solid fa-inbox" style="font-size: 40px; color: #f59e0b; opacity: 0.2;"></i>
        </div>
    </div>
    
    <!-- Card 4: Rata-rata Rating -->
    <div class="card" style="padding: 24px; border-left: 4px solid #8b5cf6;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 8px 0; font-weight: 500;">Total Ulasan</p>
                <h2 style="font-size: 32px; font-weight: 700; color: #8b5cf6; margin: 0;"><?= number_format($reviewCount) ?></h2>
            </div>
            <i class="fa-solid fa-star" style="font-size: 40px; color: #8b5cf6; opacity: 0.2;"></i>
        </div>
    </div>
</div>

<!-- ==============================================================================
     MANAGEMENT MENU GRID
     ============================================================================== -->
<h2 style="font-weight: 700; color: var(--primary); margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
    <i class="fa-solid fa-sliders"></i> Menu Pengelolaan
</h2>

<div class="grid grid-2" style="gap: 20px; margin-bottom: 40px;">
    <!-- Menu 1: Kelola Destinasi -->
    <a href="<?= BASE_URL ?>index.php?module=destinasi&action=manage" style="text-decoration: none;">
        <div class="card" style="padding: 30px; cursor: pointer; transition: all 0.3s ease; border-top: 4px solid #047857;">
            <div style="display: flex; align-items: flex-start; gap: 20px;">
                <i class="fa-solid fa-umbrella-beach" style="font-size: 48px; color: #047857; margin-top: 4px;"></i>
                <div style="flex: 1;">
                    <h3 style="color: var(--primary); font-weight: 700; margin: 0 0 8px 0;">Kelola Destinasi</h3>
                    <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 12px 0;">Tambah, edit, atau hapus objek wisata bahari yang Anda kelola</p>
                    <span style="display: inline-block; background: #ecfdf5; color: #047857; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                        <?= $destCount ?> Destinasi Aktif
                    </span>
                </div>
            </div>
        </div>
    </a>
    
    <!-- Menu 2: Tiket Masuk & Pesanan -->
    <a href="<?= BASE_URL ?>index.php?module=pesanan&action=incoming" style="text-decoration: none;">
        <div class="card" style="padding: 30px; cursor: pointer; transition: all 0.3s ease; border-top: 4px solid #f59e0b;">
            <div style="display: flex; align-items: flex-start; gap: 20px;">
                <i class="fa-solid fa-ticket" style="font-size: 48px; color: #f59e0b; margin-top: 4px;"></i>
                <div style="flex: 1;">
                    <h3 style="color: var(--primary); font-weight: 700; margin: 0 0 8px 0;">Tiket Masuk & Pesanan</h3>
                    <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 12px 0;">Pantau pesanan wisatawan dan kelola status perjalanan</p>
                    <span style="display: inline-block; background: #fffbeb; color: #f59e0b; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                        <?= $pesananMasuk ?> Pesanan Aktif
                    </span>
                </div>
            </div>
        </div>
    </a>
    
    <!-- Menu 3: Ulasan Pengunjung -->
    <a href="<?= BASE_URL ?>index.php?module=review&action=manage" style="text-decoration: none;">
        <div class="card" style="padding: 30px; cursor: pointer; transition: all 0.3s ease; border-top: 4px solid #8b5cf6;">
            <div style="display: flex; align-items: flex-start; gap: 20px;">
                <i class="fa-solid fa-star" style="font-size: 48px; color: #8b5cf6; margin-top: 4px;"></i>
                <div style="flex: 1;">
                    <h3 style="color: var(--primary); font-weight: 700; margin: 0 0 8px 0;">Ulasan Pengunjung</h3>
                    <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 12px 0;">Lihat rating dan ulasan dari wisatawan yang telah berkunjung</p>
                    <span style="display: inline-block; background: #f3f0ff; color: #8b5cf6; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                        <?= $reviewCount ?> Ulasan Total
                    </span>
                </div>
            </div>
        </div>
    </a>
    
    <!-- Menu 4: Laporan Pendapatan -->
    <a href="<?= BASE_URL ?>index.php?module=laporan" style="text-decoration: none;">
        <div class="card" style="padding: 30px; cursor: pointer; transition: all 0.3s ease; border-top: 4px solid #10b981;">
            <div style="display: flex; align-items: flex-start; gap: 20px;">
                <i class="fa-solid fa-chart-line" style="font-size: 48px; color: #10b981; margin-top: 4px;"></i>
                <div style="flex: 1;">
                    <h3 style="color: var(--primary); font-weight: 700; margin: 0 0 8px 0;">Laporan Pendapatan</h3>
                    <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 12px 0;">Analisis penjualan, revenue, dan performa bisnis Anda</p>
                    <span style="display: inline-block; background: #ecfdf5; color: #10b981; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                        Rp <?= number_format($totalRevenue, 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>
    </a>
</div>

<!-- ==============================================================================
     INFO SECTION
     ============================================================================== -->
<div class="card" style="padding: 30px; background: linear-gradient(135deg, rgba(4, 120, 87, 0.05), rgba(16, 185, 129, 0.05)); border-left: 4px solid #047857;">
    <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-lightbulb"></i> Tips untuk Mitra
    </h3>
    <ul style="color: var(--text-secondary); font-size: 13px; margin: 0; padding-left: 20px; line-height: 1.8;">
        <li>Pastikan informasi destinasi selalu terbaru dan akurat untuk menarik lebih banyak wisatawan</li>
        <li>Buat paket wisata yang beragam dengan harga kompetitif sesuai dengan season</li>
        <li>Respons cepat terhadap pesanan masuk untuk meningkatkan kepuasan pelanggan</li>
        <li>Manfaatkan ulasan positif dalam marketing dan tangani ulasan negatif dengan profesional</li>
    </ul>
</div>

<!-- ==============================================================================
     SYSTEM INFO
     ============================================================================== -->
<div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--border); text-align: center; color: var(--text-secondary); font-size: 12px;">
    <p style="margin: 0;">
        <i class="fa-solid fa-info-circle"></i> 
        BahariChain v3.0 | Partner: <strong><?= esc($_SESSION['username']) ?></strong> | Last Updated: <?= date('d M Y H:i:s') ?>
    </p>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
