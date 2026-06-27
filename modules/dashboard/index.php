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
    
} catch (PDOException $e) {
    log_error("Dashboard page database error: " . $e->getMessage());
    $destCount = 0; $vendorCount = 0; $productCount = 0; $visitorSum = 0;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="background-image: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.7)), url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=1920&auto=format&fit=crop'); background-size: cover; background-position: center; border-radius: var(--radius); padding: 80px 24px; text-align: center; color: white; margin-bottom: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
    <h1 style="font-size: 38px; font-weight: 700; margin-bottom: 12px; letter-spacing: -0.5px;">Connecting Local Vendors to Global Tourism</h1>
    <p style="font-size: 16px; max-width: 680px; margin: 0 auto 30px auto; opacity: 0.9; line-height: 1.6;">
        Selamat datang di BahariChain - Platform Reservasi Pariwisata Bahari. Jelajahi destinasi pantai eksotis Madura, pesan paket wisata unggulan, dan nikmati pengalaman liburan tak terlupakan dengan dukungan UMKM lokal dan jasa transportasi terpercaya.
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

<div class="grid grid-4" style="margin-bottom: 40px;">
    <div class="card" style="text-align: center; padding: 24px;">
        <i class="fa-solid fa-umbrella-beach" style="font-size: 32px; color: var(--accent); margin-bottom: 12px;"></i>
        <h2 style="font-size: 28px; font-weight: 700; color: var(--primary);"><?= number_format($destCount) ?></h2>
        <p style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">Destinasi Pantai</p>
    </div>
    
    <div class="card" style="text-align: center; padding: 24px;">
        <i class="fa-solid fa-store" style="font-size: 32px; color: var(--accent); margin-bottom: 12px;"></i>
        <h2 style="font-size: 28px; font-weight: 700; color: var(--primary);"><?= number_format($vendorCount) ?></h2>
        <p style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">Vendor Lokal Aktif</p>
    </div>
    
    <div class="card" style="text-align: center; padding: 24px;">
        <i class="fa-solid fa-cubes" style="font-size: 32px; color: var(--accent); margin-bottom: 12px;"></i>
        <h2 style="font-size: 28px; font-weight: 700; color: var(--primary);"><?= number_format($productCount) ?></h2>
        <p style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">Katalog Layanan & Jasa</p>
    </div>
    
    <div class="card" style="text-align: center; padding: 24px;">
        <i class="fa-solid fa-users" style="font-size: 32px; color: var(--accent); margin-bottom: 12px;"></i>
        <h2 style="font-size: 28px; font-weight: 700; color: var(--primary);"><?= number_format($visitorSum) ?></h2>
        <p style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">Kunjungan Wisatawan</p>
    </div>
</div>

<div class="card" style="padding: 40px 30px; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 16px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
    <div>
        <h3 style="color: #166534; font-weight: 700; margin-bottom: 12px;">Apakah Anda Memiliki Usaha/UMKM di Madura?</h3>
        <p style="color: #1e3f20; font-size: 15px; max-width: 780px; margin: 0 auto; line-height: 1.6;">
            Daftarkan bisnis penginapan (homestay), penyewaan kendaraan, warung kuliner, atau toko oleh-oleh Anda untuk terintegrasi dengan jaringan hotel, restoran, dan wisatawan global.
        </p>
    </div>
    <a href="<?= BASE_URL ?>index.php?module=auth&action=register" class="btn btn-primary" style="background-color: #166534; border-color: #166534; color: white; white-space: nowrap; margin-top: 8px; padding: 10px 24px; font-weight: 600;">
        <i class="fa-solid fa-user-plus"></i> Gabung Sebagai Vendor
    </a>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>