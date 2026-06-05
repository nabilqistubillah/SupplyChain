<?php
// modules/destinations/index.php
$pageTitle = "Jelajahi Destinasi";

try {
    // Ambil seluruh destinasi dari database
    $sql = "SELECT d.*, 
            (SELECT IFNULL(AVG(r.rating), 5.0) FROM reviews r 
             JOIN vendor_profile vp ON r.vendor_id = vp.id 
             WHERE vp.destination_id = d.id) as avg_rating 
            FROM destinations d ORDER BY d.id ASC";
    $stmt = db_query($sql);
    $destinations = $stmt->fetchAll();
} catch (PDOException $e) {
    log_error("Failed to load destinations: " . $e->getMessage());
    $destinations = [];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 30px;">
    <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 8px;">Jelajahi Destinasi Wisata Madura</h1>
    <p style="color: var(--text-secondary);">Temukan keindahan alam pantai legendaris di Madura dan dukung UMKM lokal di sekitarnya.</p>
</div>

<?php if (empty($destinations)): ?>
    <div class="card" style="text-align: center; padding: 40px;">
        <i class="fa-solid fa-map-location" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;"></i>
        <h3>Belum Ada Destinasi</h3>
        <p style="color: var(--text-secondary); margin-top: 8px;">Destinasi wisata saat ini belum tersedia di database.</p>
    </div>
<?php else: ?>
    <div class="grid grid-3">
        <?php foreach ($destinations as $d): ?>
            <div class="card" style="display: flex; flex-direction: column; overflow: hidden; padding: 0;">
                <!-- Image Placeholders or Dynamic Image Paths -->
                <div style="height: 200px; background-color: #cbd5e1; position: relative;">
                    <?php 
                    // Seeding fallback images if file does not exist locally
                    $imgPath = BASE_URL . $d['image_path'];
                    ?>
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary), var(--primary-light)); display: flex; align-items: center; justify-content: center; color: white;">
                        <i class="fa-solid fa-umbrella-beach" style="font-size: 64px; opacity: 0.3;"></i>
                    </div>
                    <div style="position: absolute; top: 12px; left: 12px; background: rgba(15, 76, 129, 0.9); color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                        <i class="fa-solid fa-location-dot" style="margin-right: 4px;"></i><?= esc($d['location']) ?>
                    </div>
                    <?php if ($d['avg_rating']): ?>
                        <div style="position: absolute; top: 12px; right: 12px; background: rgba(245, 158, 11, 0.9); color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                            <i class="fa-solid fa-star" style="margin-right: 4px; color: #ffe066;"></i><?= number_format($d['avg_rating'], 1) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                    <h3 style="font-weight: 700; color: var(--primary); margin-bottom: 8px;"><?= esc($d['name']) ?></h3>
                    <p style="color: var(--text-secondary); font-size: 14px; flex: 1; margin-bottom: 20px;">
                        <?= esc(substr($d['description'], 0, 120)) ?>...
                    </p>
                    <a href="<?= BASE_URL ?>index.php?module=destinations&action=detail&id=<?= $d['id'] ?>" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-circle-info"></i> Eksplorasi Wisata
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
