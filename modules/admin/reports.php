<?php
// modules/admin/reports.php
// BahariChain: Laporan & Analitik Platform (Admin Portal)

// ==============================================================================
// 1. SECURITY CHECK: Only admin can access this page
// ==============================================================================
require_role(['admin']);

$pageTitle = "Laporan & Analitik Platform";

$db = Database::getConnection();

// ==============================================================================
// 2. FETCH SUMMARY METRICS
// ==============================================================================
try {
    // Total Pendapatan Lunas
    $totalRevenue = db_query("SELECT IFNULL(SUM(total_harga), 0) as total FROM pesanan WHERE status IN ('paid', 'completed')")->fetch()['total'];
    
    // Total Pesanan Terdaftar
    $totalOrders = db_query("SELECT COUNT(*) as total FROM pesanan")->fetch()['total'];
    
    // Total Wisatawan Terdaftar
    $totalTourists = db_query("
        SELECT COUNT(*) as total FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE r.nama_role = 'wisatawan'
    ")->fetch()['total'];
    
    // Rata-rata Rating Ulasan
    $avgRating = db_query("SELECT IFNULL(AVG(rating), 0.0) as total FROM review")->fetch()['total'];
    
} catch (PDOException $e) {
    log_error("Admin reports metrics fetch error: " . $e->getMessage());
    $totalRevenue = 0;
    $totalOrders = 0;
    $totalTourists = 0;
    $avgRating = 0;
}

// ==============================================================================
// 3. FETCH CHART DATA (JSON Format)
// ==============================================================================
// 3.1 Pendapatan Bulanan (Last 6 Months)
$revenueLabels = [];
$revenueData = [];
try {
    $res = db_query("
        SELECT DATE_FORMAT(tanggal_perjalanan, '%b %Y') as bulan, 
               DATE_FORMAT(tanggal_perjalanan, '%Y-%m') as bulan_key,
               SUM(total_harga) as total 
        FROM pesanan 
        WHERE status IN ('paid', 'completed') 
        GROUP BY bulan_key 
        ORDER BY bulan_key ASC 
        LIMIT 6
    ")->fetchAll();
    
    foreach ($res as $row) {
        $revenueLabels[] = $row['bulan'];
        $revenueData[] = (float)$row['total'];
    }
} catch (PDOException $e) {
    log_error("Report error monthly revenue: " . $e->getMessage());
}

// 3.2 Paket Wisata Terpopuler (Top 5)
$packageLabels = [];
$packageCounts = [];
try {
    $res = db_query("
        SELECT pw.nama_paket, COUNT(p.id) as total_bookings
        FROM pesanan p
        JOIN paket_wisata pw ON p.paket_wisata_id = pw.id
        GROUP BY p.paket_wisata_id
        ORDER BY total_bookings DESC
        LIMIT 5
    ")->fetchAll();
    
    foreach ($res as $row) {
        $name = $row['nama_paket'];
        if (strlen($name) > 22) {
            $name = substr($name, 0, 20) . '...';
        }
        $packageLabels[] = $name;
        $packageCounts[] = (int)$row['total_bookings'];
    }
} catch (PDOException $e) {
    log_error("Report error popular packages: " . $e->getMessage());
}

// 3.3 Status Pembayaran Reservasi
$statusLabels = [];
$statusCounts = [];
try {
    $res = db_query("
        SELECT status, COUNT(*) as count 
        FROM pesanan 
        GROUP BY status
    ")->fetchAll();
    
    foreach ($res as $row) {
        $statusLabels[] = ucfirst($row['status']);
        $statusCounts[] = (int)$row['count'];
    }
} catch (PDOException $e) {
    log_error("Report error status distribution: " . $e->getMessage());
}

// 3.4 Distribusi Bintang Rating
$ratingLabels = ['1 Star', '2 Star', '3 Star', '4 Star', '5 Star'];
$ratingCounts = [0, 0, 0, 0, 0];
try {
    $res = db_query("
        SELECT rating, COUNT(*) as count 
        FROM review 
        GROUP BY rating 
        ORDER BY rating ASC
    ")->fetchAll();
    
    foreach ($res as $row) {
        $r = (int)$row['rating'];
        if ($r >= 1 && $r <= 5) {
            $ratingCounts[$r - 1] = (int)$row['count'];
        }
    }
} catch (PDOException $e) {
    log_error("Report error rating distribution: " . $e->getMessage());
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Print-only styling -->
<style>
    @media print {
        body { background: white; color: black; padding: 0; }
        .no-print { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; transform: none !important; }
        main.container { padding-top: 10px !important; }
    }
</style>

<!-- Header Section -->
<div style="margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-chart-line" style="color: #8b5cf6;"></i> Laporan & Analitik Platform
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 4px 0 0 0;">Evaluasi metrik operasional, performa reservasi paket, dan tren laba kotor platform</p>
    </div>
    
    <div style="display: flex; gap: 8px;" class="no-print">
        <button onclick="window.print()" class="btn btn-secondary" style="color: var(--primary-light); border-color: var(--primary-light);">
            <i class="fa-solid fa-print"></i> Cetak Laporan
        </button>
        <a href="<?= BASE_URL ?>index.php?module=admin&action=dashboard" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<!-- ==============================================================================
     SUMMARY METRICS CARDS
     ============================================================================== -->
<div class="grid grid-4" style="margin-bottom: 30px;">
    <!-- Metric 1: Total Pendapatan -->
    <div class="card" style="padding: 20px; border-left: 4px solid #10b981; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <p style="color: var(--text-secondary); font-size: 12px; margin: 0 0 4px 0; font-weight: 500;">Total Pendapatan</p>
            <h3 style="font-size: 20px; font-weight: 700; color: var(--primary); margin: 0;">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></h3>
        </div>
        <i class="fa-solid fa-wallet" style="font-size: 32px; color: #10b981; opacity: 0.25;"></i>
    </div>
    
    <!-- Metric 2: Total Reservasi -->
    <div class="card" style="padding: 20px; border-left: 4px solid #3b82f6; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <p style="color: var(--text-secondary); font-size: 12px; margin: 0 0 4px 0; font-weight: 500;">Total Reservasi</p>
            <h3 style="font-size: 20px; font-weight: 700; color: var(--primary); margin: 0;"><?= number_format($totalOrders) ?> Trip</h3>
        </div>
        <i class="fa-solid fa-calendar-days" style="font-size: 32px; color: #3b82f6; opacity: 0.25;"></i>
    </div>

    <!-- Metric 3: Wisatawan Registrasi -->
    <div class="card" style="padding: 20px; border-left: 4px solid #f59e0b; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <p style="color: var(--text-secondary); font-size: 12px; margin: 0 0 4px 0; font-weight: 500;">Wisatawan Terdaftar</p>
            <h3 style="font-size: 20px; font-weight: 700; color: var(--primary); margin: 0;"><?= number_format($totalTourists) ?> Pengguna</h3>
        </div>
        <i class="fa-solid fa-users" style="font-size: 32px; color: #f59e0b; opacity: 0.25;"></i>
    </div>

    <!-- Metric 4: Rata-rata Rating -->
    <div class="card" style="padding: 20px; border-left: 4px solid #8b5cf6; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <p style="color: var(--text-secondary); font-size: 12px; margin: 0 0 4px 0; font-weight: 500;">Rata-rata Ulasan</p>
            <h3 style="font-size: 20px; font-weight: 700; color: var(--primary); margin: 0;">
                <i class="fa-solid fa-star" style="color: #fbbf24; font-size: 16px;"></i> <?= number_format($avgRating, 1) ?> / 5.0
            </h3>
        </div>
        <i class="fa-solid fa-face-smile" style="font-size: 32px; color: #8b5cf6; opacity: 0.25;"></i>
    </div>
</div>

<!-- ==============================================================================
     CHARTS ROW 1
     ============================================================================== -->
<div class="grid grid-2" style="margin-bottom: 30px; gap: 24px;">
    <!-- Chart 1: Tren Pendapatan Bulanan -->
    <div class="card" style="padding: 24px;">
        <h3 style="color: var(--primary); font-size: 16px; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-chart-area" style="color: #10b981;"></i> Tren Pendapatan Bulanan (Rp)
        </h3>
        <div style="height: 260px; position: relative;">
            <?php if(empty($revenueLabels)): ?>
                <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-secondary);">
                    Belum ada data pendapatan lunas.
                </div>
            <?php else: ?>
                <canvas id="revenueChart"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chart 2: Paket Wisata Terpopuler -->
    <div class="card" style="padding: 24px;">
        <h3 style="color: var(--primary); font-size: 16px; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-award" style="color: #3b82f6;"></i> Top 5 Paket Wisata Terpopuler
        </h3>
        <div style="height: 260px; position: relative;">
            <?php if(empty($packageLabels)): ?>
                <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-secondary);">
                    Belum ada pemesanan paket wisata.
                </div>
            <?php else: ?>
                <canvas id="packageChart"></canvas>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==============================================================================
     CHARTS ROW 2
     ============================================================================== -->
<div class="grid grid-2" style="margin-bottom: 40px; gap: 24px;">
    <!-- Chart 3: Proporsi Status Reservasi -->
    <div class="card" style="padding: 24px;">
        <h3 style="color: var(--primary); font-size: 16px; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-chart-pie" style="color: #f59e0b;"></i> Distribusi Status Reservasi
        </h3>
        <div style="height: 260px; position: relative; display: flex; justify-content: center;">
            <?php if(empty($statusLabels)): ?>
                <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-secondary);">
                    Belum ada data status reservasi.
                </div>
            <?php else: ?>
                <canvas id="statusChart" style="max-width: 320px;"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chart 4: Sebaran Rating Bintang -->
    <div class="card" style="padding: 24px;">
        <h3 style="color: var(--primary); font-size: 16px; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-star-half-stroke" style="color: #8b5cf6;"></i> Sebaran Rating Ulasan Wisatawan
        </h3>
        <div style="height: 260px; position: relative;">
            <?php if(array_sum($ratingCounts) === 0): ?>
                <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: var(--text-secondary);">
                    Belum ada ulasan dari wisatawan.
                </div>
            <?php else: ?>
                <canvas id="ratingChart"></canvas>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==============================================================================
     CHART INITIALIZATION JAVASCRIPT
     ============================================================================== -->
<?php if (!empty($revenueLabels) || !empty($packageLabels) || !empty($statusLabels) || array_sum($ratingCounts) > 0): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Line Chart: Revenue
    <?php if(!empty($revenueLabels)): ?>
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($revenueLabels) ?>,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: <?= json_encode($revenueData) ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.08)',
                borderWidth: 3,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#10b981',
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // 2. Bar Chart: Popular Packages
    <?php if(!empty($packageLabels)): ?>
    const pkgCtx = document.getElementById('packageChart').getContext('2d');
    new Chart(pkgCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($packageLabels) ?>,
            datasets: [{
                label: 'Jumlah Booking',
                data: <?= json_encode($packageCounts) ?>,
                backgroundColor: '#3b82f6',
                borderRadius: 6,
                maxBarThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
    <?php endif; ?>

    // 3. Doughnut Chart: Status distribution
    <?php if(!empty($statusLabels)): ?>
    const statCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($statusLabels) ?>,
            datasets: [{
                data: <?= json_encode($statusCounts) ?>,
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#a855f7', '#ef4444'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, font: { size: 11 } }
                }
            }
        }
    });
    <?php endif; ?>

    // 4. Horizontal Bar Chart: Rating Distribution
    <?php if(array_sum($ratingCounts) > 0): ?>
    const ratCtx = document.getElementById('ratingChart').getContext('2d');
    new Chart(ratCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($ratingLabels) ?>,
            datasets: [{
                label: 'Jumlah Review',
                data: <?= json_encode($ratingCounts) ?>,
                backgroundColor: '#8b5cf6',
                borderRadius: 6,
                maxBarThickness: 25
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>
<?php endif; ?>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
