<?php
// modules/wisatawan/destinations.php
// BahariChain: Halaman Penjelajahan Destinasi (Wisatawan Role)

// Pastikan hanya role wisatawan yang dapat mengakses halaman ini
require_role(['wisatawan']);

$destId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($destId > 0) {
    // ==============================================================================
    // DETAIL VIEW
    // ==============================================================================
    try {
        // Fetch detail destinasi
        $stmt = db_query("SELECT * FROM destinasi WHERE id = ?", [$destId]);
        $dest = $stmt->fetch();

        if (!$dest) {
            // Jika destinasi tidak ditemukan, arahkan kembali ke daftar destinasi
            redirect(BASE_URL . 'index.php?module=wisatawan&action=destinations');
        }

        $pageTitle = esc($dest['nama']) . " - Detail Destinasi";

        // Ambil ulasan rata-rata secara agregat untuk destinasi ini
        $ratingData = db_query("
            SELECT IFNULL(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as total_reviews
            FROM review r
            JOIN detail_paket_destinasi dpd ON r.paket_wisata_id = dpd.paket_wisata_id
            WHERE dpd.destinasi_id = ?
        ", [$destId])->fetch();
        $avgRating = (float)$ratingData['avg_rating'];
        $totalReviews = (int)$ratingData['total_reviews'];

        // Ambil daftar paket wisata yang mencakup destinasi ini
        $packages = db_query("
            SELECT pw.* 
            FROM paket_wisata pw
            JOIN detail_paket_destinasi dpd ON pw.id = dpd.paket_wisata_id
            WHERE dpd.destinasi_id = ?
            ORDER BY pw.harga ASC
        ", [$destId])->fetchAll();

        // Ambil armada transportasi terdekat yang tersedia
        $transportation = db_query("
            SELECT * FROM transportasi 
            WHERE destinasi_id = ? AND status_ketersediaan = 'tersedia'
            ORDER BY harga_sewa ASC
        ", [$destId])->fetchAll();

    } catch (PDOException $e) {
        log_error("Wisatawan destination detail database error: " . $e->getMessage());
        $dest = null;
        $packages = [];
        $transportation = [];
        $avgRating = 0;
        $totalReviews = 0;
    }

    require_once __DIR__ . '/../../includes/header.php';
    ?>

    <!-- Navigation Breadcrumbs -->
    <div style="margin-bottom: 20px;">
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=dashboard" style="color: var(--text-secondary); font-size: 14px;">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=destinations" style="color: var(--text-secondary); font-size: 14px;">
            Destinasi
        </a>
        <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
        <span style="color: var(--primary); font-size: 14px; font-weight: 600;"><?= esc($dest['nama']) ?></span>
    </div>

    <!-- Detail Hero Section -->
    <div style="background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border-radius: var(--radius); padding: 40px 30px; margin-bottom: 30px; box-shadow: var(--shadow-lg); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -50px; bottom: -50px; opacity: 0.1; font-size: 240px; pointer-events: none;">
            <i class="fa-solid fa-umbrella-beach"></i>
        </div>
        <div style="position: relative; z-index: 2;">
            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 12px;">
                <span style="background: rgba(255, 255, 255, 0.2); padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-location-dot" style="margin-right: 4px;"></i> <?= esc($dest['lokasi']) ?>
                </span>
                <?php if ($totalReviews > 0): ?>
                    <span style="background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.4); color: #fef08a; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        <i class="fa-solid fa-star" style="margin-right: 4px; color: #fbbf24;"></i> <?= number_format($avgRating, 1) ?> (<?= $totalReviews ?> Ulasan)
                    </span>
                <?php endif; ?>
            </div>
            <h1 style="font-size: 36px; font-weight: 700; margin: 0 0 12px 0;"><?= esc($dest['nama']) ?></h1>
            <p style="font-size: 15px; opacity: 0.95; max-width: 800px; line-height: 1.7; margin: 0;"><?= esc($dest['deskripsi']) ?></p>
        </div>
    </div>

    <!-- Main Detail Content -->
    <div class="grid grid-3" style="align-items: start; gap: 30px;">
        <!-- Left: Detail & Map Info -->
        <div class="card" style="grid-column: span 2; padding: 30px;">
            <h3 style="color: var(--primary); font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid var(--border); padding-bottom: 10px;">
                <i class="fa-solid fa-circle-info" style="color: var(--accent);"></i> Informasi Lengkap
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                    <span style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">Nama Destinasi</span>
                    <span style="font-weight: 600; color: var(--text-primary); font-size: 14px;"><?= esc($dest['nama']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                    <span style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">Lokasi</span>
                    <span style="font-weight: 600; color: var(--text-primary); font-size: 14px;"><?= esc($dest['lokasi']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                    <span style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">Tanggal Pendaftaran</span>
                    <span style="font-weight: 600; color: var(--text-primary); font-size: 14px;"><?= date('d F Y', strtotime($dest['created_at'])) ?></span>
                </div>
            </div>

            <!-- GPS Coordinates -->
            <?php if (!empty($dest['latitude']) && !empty($dest['longitude'])): ?>
                <div style="background-color: var(--background); border-radius: var(--radius); padding: 20px; border: 1px solid var(--border);">
                    <h4 style="font-weight: 600; color: var(--primary); font-size: 14px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-map-location-dot" style="color: var(--accent);"></i> Koordinat Geografis
                    </h4>
                    <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 16px;">
                        Destinasi ini terletak pada koordinat: <strong><?= esc($dest['latitude']) ?></strong>, <strong><?= esc($dest['longitude']) ?></strong>
                    </p>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?= $dest['latitude'] ?>,<?= $dest['longitude'] ?>" target="_blank" class="btn btn-primary" style="font-size: 13px; width: 100%;">
                        <i class="fa-solid fa-map-pin"></i> Petunjuk Arah di Google Maps
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Transportation nearby -->
        <div class="card" style="padding: 24px;">
            <h3 style="color: var(--primary); font-weight: 700; font-size: 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-car-side" style="color: var(--accent);"></i> Transportasi Terdekat
            </h3>
            <p style="color: var(--text-secondary); font-size: 12px; margin-bottom: 20px;">Layanan rental armada aktif di sekitar pos wisata:</p>

            <?php if (empty($transportation)): ?>
                <div style="text-align: center; padding: 30px 10px; border: 1px dashed var(--border); border-radius: 8px; background-color: var(--background);">
                    <i class="fa-solid fa-circle-question" style="font-size: 32px; color: var(--text-secondary); opacity: 0.5; margin-bottom: 10px;"></i>
                    <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Tidak ada transportasi aktif.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($transportation as $t): 
                        // Set icon based on vehicle type
                        $tIcon = 'fa-route';
                        if ($t['jenis_kendaraan'] === 'motor') $tIcon = 'fa-motorcycle';
                        elseif ($t['jenis_kendaraan'] === 'mobil') $tIcon = 'fa-car';
                        elseif ($t['jenis_kendaraan'] === 'shuttle_bus') $tIcon = 'fa-bus';
                        elseif ($t['jenis_kendaraan'] === 'perahu_wisata') $tIcon = 'fa-ship';
                    ?>
                        <div style="background-color: var(--background); padding: 14px; border-radius: 8px; border: 1px solid var(--border); display: flex; align-items: center; gap: 14px; transition: var(--transition);">
                            <div style="width: 42px; height: 42px; background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(15, 76, 129, 0.1)); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                <i class="fa-solid <?= $tIcon ?>"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="font-size: 13px; font-weight: 700; color: var(--primary); margin: 0 0 2px 0; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                    <?= esc($t['nama_armada']) ?>
                                </h4>
                                <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-secondary);">
                                    <span>Kapasitas: <?= $t['kapasitas'] ?> pax</span>
                                    <span style="font-weight: 600; color: var(--success);">Rp <?= number_format($t['harga_sewa'], 0, ',', '.') ?>/trip</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Tourism Packages -->
    <div style="margin-top: 40px;">
        <h2 style="font-weight: 700; color: var(--primary); font-size: 22px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-suitcase" style="color: var(--accent);"></i> Paket Wisata yang Tersedia
        </h2>

        <?php if (empty($packages)): ?>
            <div class="card" style="text-align: center; padding: 40px;">
                <i class="fa-solid fa-box-open" style="font-size: 48px; color: var(--text-secondary); opacity: 0.5; margin-bottom: 16px;"></i>
                <h4 style="color: var(--text-primary); font-weight: 600;">Belum Ada Paket Wisata</h4>
                <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px;">Saat ini destinasi ini belum terhubung dengan paket wisata mana pun.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-3">
                <?php foreach ($packages as $p): ?>
                    <div class="card" style="display: flex; flex-direction: column; overflow: hidden; padding: 0;">
                        <!-- Image representation / Fallback -->
                        <div style="height: 180px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); display: flex; align-items: center; justify-content: center; color: white; position: relative;">
                            <i class="fa-solid fa-map" style="font-size: 54px; opacity: 0.25;"></i>
                            <div style="position: absolute; bottom: 12px; left: 12px; background: rgba(15, 76, 129, 0.9); color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                <i class="fa-solid fa-clock" style="margin-right: 4px;"></i> <?= $p['durasi_hari'] ?> Hari
                            </div>
                        </div>
                        <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                            <h3 style="font-weight: 700; color: var(--primary); font-size: 16px; margin: 0 0 8px 0; min-height: 48px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= esc($p['nama_paket']) ?>
                            </h3>
                            <p style="color: var(--text-secondary); font-size: 13px; line-height: 1.6; margin-bottom: 16px; flex: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= esc($p['deskripsi']) ?>
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid var(--border);">
                                <div>
                                    <span style="font-size: 10px; color: var(--text-secondary); display: block; text-transform: uppercase;">Harga Mulai</span>
                                    <span style="font-weight: 700; color: var(--primary-light); font-size: 15px;">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span>
                                </div>
                                <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=packages&id=<?= $p['id'] ?>" class="btn btn-primary" style="padding: 8px 14px; font-size: 12px;">
                                    Detail Paket
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php
    require_once __DIR__ . '/../../includes/footer.php';

} else {
    // ==============================================================================
    // LIST VIEW
    // ==============================================================================
    try {
        // Ambil semua lokasi destinasi yang unik untuk filter dropdown
        $locations = db_query("SELECT DISTINCT lokasi FROM destinasi ORDER BY lokasi ASC")->fetchAll();

        // Parameter filter dan pencarian
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $location = isset($_GET['location']) ? trim($_GET['location']) : '';

        // Query utama dengan pencarian, filter, dan aggregates rating
        $sql = "
            SELECT d.*,
                   (SELECT IFNULL(AVG(r.rating), 0) 
                    FROM review r 
                    JOIN detail_paket_destinasi dpd ON r.paket_wisata_id = dpd.paket_wisata_id 
                    WHERE dpd.destinasi_id = d.id) as avg_rating,
                   (SELECT COUNT(r.id) 
                    FROM review r 
                    JOIN detail_paket_destinasi dpd ON r.paket_wisata_id = dpd.paket_wisata_id 
                    WHERE dpd.destinasi_id = d.id) as total_reviews
            FROM destinasi d
            WHERE 1=1
        ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND d.nama LIKE ?";
            $params[] = '%' . $search . '%';
        }

        if ($location !== '') {
            $sql .= " AND d.lokasi = ?";
            $params[] = $location;
        }

        $sql .= " ORDER BY d.nama ASC";
        $destinations = db_query($sql, $params)->fetchAll();

    } catch (PDOException $e) {
        log_error("Wisatawan destinations list database error: " . $e->getMessage());
        $locations = [];
        $destinations = [];
    }

    $pageTitle = "Eksplorasi Destinasi Bahari";
    require_once __DIR__ . '/../../includes/header.php';
    ?>

    <!-- Navigation Breadcrumbs -->
    <div style="margin-bottom: 20px;">
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=dashboard" style="color: var(--text-secondary); font-size: 14px;">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
        <span style="color: var(--primary); font-size: 14px; font-weight: 600;">Destinasi</span>
    </div>

    <!-- Header Section -->
    <div style="margin-bottom: 30px;">
        <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 8px;">Jelajahi Destinasi Wisata Madura</h1>
        <p style="color: var(--text-secondary);">Temukan objek wisata pantai eksotis, wisata mangrove, dan keindahan bahari legendaris di Madura.</p>
    </div>

    <!-- Search & Filter Panel -->
    <div class="card" style="padding: 20px; margin-bottom: 30px;">
        <form method="GET" action="<?= BASE_URL ?>index.php" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <!-- Pastikan parameter module dan action tetap terbawa dalam form method GET -->
            <input type="hidden" name="module" value="wisatawan">
            <input type="hidden" name="action" value="destinations">

            <!-- Search input -->
            <div style="flex: 2; min-width: 250px;">
                <label for="search-dest" class="form-label" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Cari Nama Destinasi</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="text" id="search-dest" name="search" class="form-control" placeholder="Ketik nama pantai atau mangrove..." value="<?= esc($search) ?>" style="padding-left: 45px;">
                </div>
            </div>

            <!-- Location filter -->
            <div style="flex: 1; min-width: 180px;">
                <label for="filter-location" class="form-label" style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Filter Lokasi</label>
                <select id="filter-location" name="location" class="form-control">
                    <option value="">Semua Lokasi</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= esc($loc['lokasi']) ?>" <?= $location === $loc['lokasi'] ? 'selected' : '' ?>>
                            <?= esc($loc['lokasi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Action buttons -->
            <div style="display: flex; gap: 10px; flex-shrink: 0;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">
                    <i class="fa-solid fa-filter"></i> Terapkan
                </button>
                <?php if ($search !== '' || $location !== ''): ?>
                    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=destinations" class="btn btn-secondary" style="padding: 12px 20px;">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Destinations Grid -->
    <?php if (empty($destinations)): ?>
        <div class="card" style="text-align: center; padding: 60px 20px;">
            <i class="fa-solid fa-map-location" style="font-size: 54px; color: var(--text-secondary); opacity: 0.4; margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-primary); font-weight: 600;">Destinasi Tidak Ditemukan</h3>
            <p style="color: var(--text-secondary); margin-top: 8px;">Coba gunakan kata kunci pencarian lain atau ubah filter lokasi.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-3">
            <?php foreach ($destinations as $d): 
                $avgRat = (float)$d['avg_rating'];
                $totRev = (int)$d['total_reviews'];
            ?>
                <div class="card" style="display: flex; flex-direction: column; overflow: hidden; padding: 0;">
                    <!-- Image Area with fallbacks and tags -->
                    <div style="height: 200px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); display: flex; align-items: center; justify-content: center; color: white; position: relative;">
                        <i class="fa-solid fa-umbrella-beach" style="font-size: 64px; opacity: 0.3;"></i>
                        
                        <!-- Location Tag -->
                        <div style="position: absolute; top: 12px; left: 12px; background: rgba(15, 76, 129, 0.9); color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; box-shadow: var(--shadow-sm);">
                            <i class="fa-solid fa-location-dot" style="margin-right: 4px; color: var(--accent);"></i><?= esc($d['lokasi']) ?>
                        </div>

                        <!-- Rating Tag -->
                        <?php if ($totRev > 0): ?>
                            <div style="position: absolute; top: 12px; right: 12px; background: rgba(245, 158, 11, 0.95); color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; box-shadow: var(--shadow-sm);">
                                <i class="fa-solid fa-star" style="margin-right: 4px; color: #ffe066;"></i><?= number_format($avgRat, 1) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Description Content -->
                    <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                        <h3 style="font-weight: 700; color: var(--primary); font-size: 17px; margin: 0 0 10px 0; min-height: 26px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?= esc($d['nama']) ?>
                        </h3>
                        <p style="color: var(--text-secondary); font-size: 13px; line-height: 1.6; margin-bottom: 20px; flex: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= esc($d['deskripsi']) ?>
                        </p>
                        
                        <div style="display: flex; gap: 8px; border-top: 1px solid var(--border); padding-top: 14px; align-items: center;">
                            <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=destinations&id=<?= $d['id'] ?>" class="btn btn-primary" style="flex: 1; font-size: 13px; padding: 10px;">
                                <i class="fa-solid fa-compass"></i> Lihat Detail
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
