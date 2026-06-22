<?php
// modules/dashboard/index.php
$pageTitle = "Beranda Utama";

try {
    // Ambil data metrik ringkasan ekosistem wisata
    $destCount = db_query("SELECT COUNT(*) as total FROM destinations")->fetch()['total'];
    $vendorCount = db_query("SELECT COUNT(*) as total FROM vendor_profile")->fetch()['total'];
    $productCount = db_query("SELECT COUNT(*) as total FROM products")->fetch()['total'];
    
    // Total pengunjung dari statistik
    $visitorSum = db_query("SELECT IFNULL(SUM(local_tourists + regional_tourists + foreign_tourists), 0) as total FROM visitor_statistics")->fetch()['total'];
    
    // Ambil 3 destinasi terpopuler / teratas
    $stmtFeatured = db_query("SELECT * FROM destinations ORDER BY id ASC LIMIT 3");
    $featuredDestinations = $stmtFeatured->fetchAll();
    
} catch (PDOException $e) {
    log_error("Dashboard page database error: " . $e->getMessage());
    $destCount = 0; $vendorCount = 0; $productCount = 0; $visitorSum = 0;
    $featuredDestinations = [];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Hero Banner Section -->
<div style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; border-radius: var(--radius); padding: 50px 30px; text-align: center; margin-bottom: 40px; box-shadow: var(--shadow-lg);">
    <h1 style="font-size: 38px; font-weight: 700; margin-bottom: 12px; letter-spacing: -0.5px;">Connecting Local Vendors to Global Tourism</h1>
    <p style="font-size: 16px; max-width: 680px; margin: 0 auto 30px auto; opacity: 0.9; line-height: 1.6;">
        Selamat datang di **BahariChain** - Platform Reservasi Pariwisata Bahari. Jelajahi destinasi pantai eksotis Madura, pesan paket wisata unggulan, dan nikmati pengalaman liburan tak terlupakan dengan dukungan UMKM lokal dan jasa transportasi terpercaya.
    </p>
    <div style="display: inline-flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>index.php?module=destinations" class="btn btn-primary" style="background-color: var(--accent); color: white; font-weight: 600;">
            <i class="fa-solid fa-map-marked-alt"></i> Jelajahi Destinasi Wisata
        </a>
        <a href="<?= BASE_URL ?>index.php?module=reports&action=visitors" class="btn btn-secondary" style="background-color: rgba(255, 255, 255, 0.1); color: white; border-color: rgba(255,255,255,0.25);">
            <i class="fa-solid fa-chart-pie"></i> Lihat Statistik Pengunjung
        </a>
    </div>
</div>

<!-- Ecosystem Metrics Counter Cards -->
<div class="grid grid-4" style="margin-bottom: 40px;">
    <!-- 1. Destinasi -->
    <div class="card" style="text-align: center; padding: 24px;">
        <i class="fa-solid fa-umbrella-beach" style="font-size: 32px; color: var(--accent); margin-bottom: 12px;"></i>
        <h2 style="font-size: 28px; font-weight: 700; color: var(--primary);"><?= number_format($destCount) ?></h2>
        <p style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">Destinasi Pantai</p>
    </div>
    
    <!-- 2. Vendor UMKM -->
    <div class="card" style="text-align: center; padding: 24px;">
        <i class="fa-solid fa-store" style="font-size: 32px; color: var(--accent); margin-bottom: 12px;"></i>
        <h2 style="font-size: 28px; font-weight: 700; color: var(--primary);"><?= number_format($vendorCount) ?></h2>
        <p style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">Vendor Lokal Aktif</p>
    </div>
    
    <!-- 3. Produk Katalog -->
    <div class="card" style="text-align: center; padding: 24px;">
        <i class="fa-solid fa-cubes" style="font-size: 32px; color: var(--accent); margin-bottom: 12px;"></i>
        <h2 style="font-size: 28px; font-weight: 700; color: var(--primary);"><?= number_format($productCount) ?></h2>
        <p style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">Katalog Layanan & Jasa</p>
    </div>
    
    <!-- 4. Total Pengunjung -->
    <div class="card" style="text-align: center; padding: 24px;">
        <i class="fa-solid fa-users" style="font-size: 32px; color: var(--accent); margin-bottom: 12px;"></i>
        <h2 style="font-size: 28px; font-weight: 700; color: var(--primary);"><?= number_format($visitorSum) ?></h2>
        <p style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">Kunjungan Wisatawan</p>
    </div>
</div>

<!-- Featured Destinations Section -->
<div style="margin-bottom: 40px;">
    <h2 style="font-weight: 700; color: var(--primary); margin-bottom: 20px; text-align: left; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-star" style="color: #f59e0b;"></i> Destinasi Wisata Pilihan
    </h2>
    
    <?php if (empty($featuredDestinations)): ?>
        <p style="color: var(--text-secondary);">Belum ada destinasi terdaftar.</p>
    <?php else: ?>
        <div class="grid grid-3">
            <?php foreach ($featuredDestinations as $d): ?>
                <div class="card" style="display: flex; flex-direction: column; overflow: hidden; padding: 0;">
                    <div style="height: 160px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); display: flex; align-items: center; justify-content: center; color: white; position: relative;">
                        <i class="fa-solid fa-water" style="font-size: 48px; opacity: 0.25;"></i>
                        <span style="position: absolute; bottom: 12px; left: 12px; background: rgba(0,0,0,0.4); padding: 2px 8px; border-radius: 4px; font-size: 11px; color: white;">
                            <i class="fa-solid fa-location-dot"></i> <?= esc($d['location']) ?>
                        </span>
                    </div>
                    <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                        <h3 style="font-weight: 700; color: var(--primary); margin-bottom: 8px;"><?= esc($d['name']) ?></h3>
                        <p style="color: var(--text-secondary); font-size: 13px; flex: 1; margin-bottom: 16px;">
                            <?= esc(substr($d['description'], 0, 90)) ?>...
                        </p>
                        <a href="<?= BASE_URL ?>index.php?module=destinations&action=detail&id=<?= $d['id'] ?>" class="btn btn-secondary" style="width: 100%; border-color: var(--primary); color: var(--primary); font-weight: 600;">
                            Detail Eksplorasi
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Eco-System Partnership Info banner -->
<div class="card" style="padding: 30px; display: flex; flex-direction: column; md-flex-direction: row; justify-content: space-between; align-items: center; gap: 20px; background-color: #f0fdf4; border-color: #bbf7d0;">
    <div>
        <h3 style="color: #166534; font-weight: 700; margin-bottom: 6px;">Apakah Anda Memiliki Usaha/UMKM di Madura?</h3>
        <p style="color: #1e3f20; font-size: 14px; max-width: 780px;">
            Daftarkan bisnis penginapan (homestay), penyewaan kendaraan, warung kuliner, atau toko oleh-oleh Anda untuk terintegrasi dengan jaringan hotel, restoran, dan wisatawan global.
        </p>
    </div>
    <a href="<?= BASE_URL ?>index.php?module=auth&action=register" class="btn btn-primary" style="background-color: #166534; white-space: nowrap;">
        <i class="fa-solid fa-user-plus"></i> Gabung Sebagai Vendor
    </a>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
