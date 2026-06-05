<?php
// modules/destinations/detail.php

$destId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($destId <= 0) {
    redirect(BASE_URL . 'index.php?module=destinations');
}

try {
    // 1. Ambil data destinasi
    $stmt = db_query("SELECT * FROM destinations WHERE id = :id", ['id' => $destId]);
    $destination = $stmt->fetch();
    
    if (!$destination) {
        http_response_code(404);
        require_once __DIR__ . '/../errors/404.php';
        exit;
    }
    
    $pageTitle = $destination['name'];
    
    // 2. Ambil data wahana wisata
    $stmtRides = db_query("SELECT * FROM destination_rides WHERE destination_id = :dest_id", ['dest_id' => $destId]);
    $rides = $stmtRides->fetchAll();
    
    // 3. Ambil rangkuman statistik lokal
    $stmtStats = db_query("SELECT IFNULL(SUM(local_tourists), 0) as total_local, 
                                  IFNULL(SUM(regional_tourists), 0) as total_regional, 
                                  IFNULL(SUM(foreign_tourists), 0) as total_foreign,
                                  COUNT(DISTINCT visit_date) as total_days 
                           FROM visitor_statistics 
                           WHERE destination_id = :dest_id", ['dest_id' => $destId]);
    $stats = $stmtStats->fetch();
    
    // 4. Ambil vendor dan produk pendukung di sekitar destinasi (Smart Tourism Network)
    $sqlProducts = "SELECT p.*, c.name as category_name, vp.business_name, vp.contact, vp.rating as vendor_rating 
                    FROM products p
                    JOIN vendor_profile vp ON p.vendor_id = vp.id
                    JOIN categories c ON p.category_id = c.id
                    WHERE vp.destination_id = :dest_id";
    $stmtProducts = db_query($sqlProducts, ['dest_id' => $destId]);
    $products = $stmtProducts->fetchAll();
    
    // Kelompokkan produk berdasarkan kategorinya
    $transportProducts = [];
    $accommodationProducts = [];
    $culinaryProducts = [];
    
    foreach ($products as $p) {
        if ($p['category_name'] === 'Transportasi') {
            $transportProducts[] = $p;
        } elseif ($p['category_name'] === 'Akomodasi') {
            $accommodationProducts[] = $p;
        } else {
            $culinaryProducts[] = $p;
        }
    }
    
} catch (PDOException $e) {
    log_error("Error loading destination detail: " . $e->getMessage());
    die("Terjadi kesalahan teknis saat mengambil data destinasi.");
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Header Banner -->
<div style="background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border-radius: var(--radius); padding: 40px; margin-bottom: 30px; display: flex; flex-direction: column; md-flex-direction: row; justify-content: space-between; align-items: flex-start; gap: 20px;">
    <div>
        <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; text-transform: uppercase;">
            <i class="fa-solid fa-location-dot" style="margin-right: 5px;"></i><?= esc($destination['location']) ?>
        </span>
        <h1 style="font-size: 36px; font-weight: 700; margin-top: 10px; margin-bottom: 12px;"><?= esc($destination['name']) ?></h1>
        <p style="max-width: 700px; font-size: 15px; opacity: 0.9; line-height: 1.7;"><?= esc($destination['description']) ?></p>
    </div>
</div>

<div class="grid grid-3" style="margin-bottom: 40px;">
    <!-- 1. Statistik Pengunjung -->
    <div class="card" style="padding: 20px;">
        <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-chart-simple"></i> Statistik Pengunjung
        </h3>
        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <span style="color: var(--text-secondary);">Wisatawan Lokal</span>
                <span style="font-weight: 600;"><?= number_format($stats['total_local']) ?> orang</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <span style="color: var(--text-secondary);">Wisatawan Luar Daerah</span>
                <span style="font-weight: 600;"><?= number_format($stats['total_regional']) ?> orang</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                <span style="color: var(--text-secondary);">Wisatawan Mancanegara</span>
                <span style="font-weight: 600;"><?= number_format($stats['total_foreign']) ?> orang</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-top: 4px; font-weight: 700; color: var(--primary);">
                <span>Total Pengunjung</span>
                <span><?= number_format($stats['total_local'] + $stats['total_regional'] + $stats['total_foreign']) ?> orang</span>
            </div>
        </div>
    </div>

    <!-- 2. Wahana Wisata -->
    <div class="card" style="grid-column: span 2; padding: 20px;">
        <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-umbrella-beach"></i> Wahana & Aktivitas
        </h3>
        
        <?php if (empty($rides)): ?>
            <p style="color: var(--text-secondary); font-size: 14px; text-align: center; padding: 20px;">Belum ada wahana terdaftar untuk destinasi ini.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php foreach ($rides as $r): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; background-color: var(--background); padding: 12px 16px; border-radius: 8px; border-left: 4px solid var(--accent);">
                        <div>
                            <h4 style="font-weight: 600; color: var(--primary); font-size: 14px;"><?= esc($r['ride_name']) ?></h4>
                            <p style="font-size: 12px; color: var(--text-secondary);"><?= esc($r['description']) ?></p>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-weight: 600; font-size: 14px; color: var(--primary-light);">Rp <?= number_format($r['price'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Smart Tourism Network (Recommended Services) -->
<h2 style="font-weight: 700; color: var(--primary); margin-bottom: 24px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid var(--border); padding-bottom: 8px;">
    <i class="fa-solid fa-circle-nodes" style="color: var(--accent);"></i>
    Smart Tourism Network <span style="font-weight: 300; font-size: 16px; color: var(--text-secondary);">- Layanan UMKM Sekitar Destinasi</span>
</h2>

<!-- Cart feedback -->
<?php if (isset($_SESSION['cart_message'])): ?>
    <div class="alert alert-success">
        <?= esc($_SESSION['cart_message']) ?>
        <?php unset($_SESSION['cart_message']); ?>
    </div>
<?php endif; ?>

<!-- 1. Transportasi Terdekat Section -->
<div style="margin-bottom: 40px;">
    <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-car" style="color: var(--accent);"></i> Transportasi Terdekat
    </h3>
    <?php if (empty($transportProducts)): ?>
        <p style="color: var(--text-secondary); font-size: 14px; background: white; padding: 20px; border-radius: var(--radius); border: 1px dashed var(--border);">
            Tidak ada layanan transportasi terdekat di sekitar destinasi ini.
        </p>
    <?php else: ?>
        <div class="grid grid-3">
            <?php foreach ($transportProducts as $p): ?>
                <?php include __DIR__ . '/_product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- 2. Akomodasi Sekitar Section -->
<div style="margin-bottom: 40px;">
    <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-hotel" style="color: var(--accent);"></i> Akomodasi Sekitar
    </h3>
    <?php if (empty($accommodationProducts)): ?>
        <p style="color: var(--text-secondary); font-size: 14px; background: white; padding: 20px; border-radius: var(--radius); border: 1px dashed var(--border);">
            Tidak ada layanan akomodasi/penginapan di sekitar destinasi ini.
        </p>
    <?php else: ?>
        <div class="grid grid-3">
            <?php foreach ($accommodationProducts as $p): ?>
                <?php include __DIR__ . '/_product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- 3. Kuliner & Oleh-oleh Sekitar Section -->
<div style="margin-bottom: 40px;">
    <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-store" style="color: var(--accent);"></i> Kuliner & Souvenir Lokal
    </h3>
    <?php if (empty($culinaryProducts)): ?>
        <p style="color: var(--text-secondary); font-size: 14px; background: white; padding: 20px; border-radius: var(--radius); border: 1px dashed var(--border);">
            Tidak ada kuliner maupun pusat oleh-oleh di sekitar destinasi ini.
        </p>
    <?php else: ?>
        <div class="grid grid-3">
            <?php foreach ($culinaryProducts as $p): ?>
                <?php include __DIR__ . '/_product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
