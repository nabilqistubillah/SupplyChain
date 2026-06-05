<?php
// modules/reports/visitors.php
$pageTitle = "Statistik Pengunjung Wisata";

try {
    // 1. Ambil tren kunjungan harian gabungan (7 hari terakhir)
    $sqlDaily = "SELECT visit_date, 
                        SUM(local_tourists) as total_local, 
                        SUM(regional_tourists) as total_regional, 
                        SUM(foreign_tourists) as total_foreign,
                        SUM(local_tourists + regional_tourists + foreign_tourists) as total_visitors
                 FROM visitor_statistics 
                 GROUP BY visit_date 
                 ORDER BY visit_date ASC 
                 LIMIT 7";
    $stmtDaily = db_query($sqlDaily);
    $dailyStats = $stmtDaily->fetchAll();
    
    // Konversi data PHP ke format JSON untuk Javascript Chart.js
    $labelsDaily = [];
    $dataLocal = [];
    $dataRegional = [];
    $dataForeign = [];
    $dataTotal = [];
    
    foreach ($dailyStats as $ds) {
        $labelsDaily[] = date('d M', strtotime($ds['visit_date']));
        $dataLocal[] = (int)$ds['total_local'];
        $dataRegional[] = (int)$ds['total_regional'];
        $dataForeign[] = (int)$ds['total_foreign'];
        $dataTotal[] = (int)$ds['total_visitors'];
    }
    
    // 2. Ambil total segmentasi wisatawan secara keseluruhan
    $sqlSegment = "SELECT SUM(local_tourists) as local, 
                          SUM(regional_tourists) as regional, 
                          SUM(foreign_tourists) as foreign_tourists 
                   FROM visitor_statistics";
    $segmentStats = db_query($sqlSegment)->fetch();
    
    $totalSegmentLocal = (int)$segmentStats['local'];
    $totalSegmentRegional = (int)$segmentStats['regional'];
    $totalSegmentForeign = (int)$segmentStats['foreign_tourists'];
    
    // 3. Ambil perbandingan kunjungan antar objek wisata destinasi
    $sqlDestCompare = "SELECT d.name, 
                              SUM(vs.local_tourists + vs.regional_tourists + vs.foreign_tourists) as total_visitors
                       FROM visitor_statistics vs
                       JOIN destinations d ON vs.destination_id = d.id
                       GROUP BY d.id
                       ORDER BY total_visitors DESC";
    $destCompareStats = db_query($sqlDestCompare)->fetchAll();
    
    $labelsDest = [];
    $dataDest = [];
    foreach ($destCompareStats as $dc) {
        $labelsDest[] = $dc['name'];
        $dataDest[] = (int)$dc['total_visitors'];
    }
    
} catch (PDOException $e) {
    log_error("Failed to load visitor reports: " . $e->getMessage());
    die("Terjadi kesalahan teknis saat mengambil data statistik laporan.");
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Header -->
<div style="margin-bottom: 30px;">
    <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 6px;">Statistik & Analisis Pengunjung</h1>
    <p style="color: var(--text-secondary);">Visualisasi data kunjungan wisatawan harian dan segmentasi daerah asal di objek wisata Madura.</p>
</div>

<!-- Grid Charts -->
<div class="grid grid-3" style="margin-bottom: 30px;">
    
    <!-- 1. Line Chart Kunjungan Harian -->
    <div class="card" style="grid-column: span 2; padding: 24px;">
        <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 16px;">
            <i class="fa-solid fa-chart-area"></i> Tren Kunjungan Harian (7 Hari Terakhir)
        </h3>
        <div style="position: relative; height: 320px; width: 100%;">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>
    
    <!-- 2. Doughnut Chart Segmentasi Wisatawan -->
    <div class="card" style="grid-column: span 1; padding: 24px;">
        <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 16px;">
            <i class="fa-solid fa-chart-pie"></i> Segmentasi Wisatawan
        </h3>
        <div style="position: relative; height: 220px; width: 100%; display: flex; align-items: center; justify-content: center;">
            <canvas id="segmentChart"></canvas>
        </div>
        <div style="margin-top: 20px; display: flex; justify-content: space-around; font-size: 12px; font-weight: 600;">
            <div style="text-align: center;">
                <span style="display: block; width: 12px; height: 12px; background: #0f4c81; margin: 0 auto 4px auto; border-radius: 50%;"></span>
                Lokal: <?= number_format($totalSegmentLocal) ?>
            </div>
            <div style="text-align: center;">
                <span style="display: block; width: 12px; height: 12px; background: #3b82f6; margin: 0 auto 4px auto; border-radius: 50%;"></span>
                Luar Daerah: <?= number_format($totalSegmentRegional) ?>
            </div>
            <div style="text-align: center;">
                <span style="display: block; width: 12px; height: 12px; background: #10b981; margin: 0 auto 4px auto; border-radius: 50%;"></span>
                Mancanegara: <?= number_format($totalSegmentForeign) ?>
            </div>
        </div>
    </div>
</div>

<!-- Grid Perbandingan Pantai -->
<div class="grid grid-3" style="margin-bottom: 40px;">
    <!-- 3. Bar Chart Kunjungan Per Destinasi -->
    <div class="card" style="grid-column: span 2; padding: 24px;">
        <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 16px;">
            <i class="fa-solid fa-chart-column"></i> Perbandingan Kunjungan antar Destinasi Pantai
        </h3>
        <div style="position: relative; height: 260px; width: 100%;">
            <canvas id="destCompareChart"></canvas>
        </div>
    </div>
    
    <!-- Info ringkasan teks -->
    <div class="card" style="grid-column: span 1; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <h3 style="font-weight: 600; color: var(--primary); margin-bottom: 16px;">
                <i class="fa-solid fa-circle-info"></i> Rangkuman Data
            </h3>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 16px;">
                Data statistik pariwisata Madura diolah secara terpusat untuk memetakan demografi wisatawan dan segmentasi daerah asal.
            </p>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                Hal ini membantu para vendor UMKM dan pengelola akomodasi sekitar dalam memprediksi tingkat keterisian kuota (*occupancy rate*) produk dan layanan mereka secara real-time.
            </p>
        </div>
        <div style="border-top: 1px solid var(--border); padding-top: 16px; margin-top: 16px; font-size: 13px; color: var(--text-secondary);">
            <i class="fa-solid fa-clock-rotate-left"></i> Terakhir diperbarui: <?= date('d M Y, H:i') ?> WIB.
        </div>
    </div>
</div>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Chart Kunjungan Harian (Line)
    const ctxDaily = document.getElementById('dailyChart').getContext('2d');
    new Chart(ctxDaily, {
        type: 'line',
        data: {
            labels: <?= json_encode($labelsDaily) ?>,
            datasets: [
                {
                    label: 'Wisatawan Lokal',
                    data: <?= json_encode($dataLocal) ?>,
                    borderColor: '#0f4c81',
                    backgroundColor: 'rgba(15, 76, 129, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Luar Daerah',
                    data: <?= json_encode($dataRegional) ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    tension: 0.3
                },
                {
                    label: 'Total Kunjungan',
                    data: <?= json_encode($dataTotal) ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    borderWidth: 3,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // 2. Chart Segmentasi Wisatawan (Doughnut)
    const ctxSegment = document.getElementById('segmentChart').getContext('2d');
    new Chart(ctxSegment, {
        type: 'doughnut',
        data: {
            labels: ['Lokal', 'Luar Daerah', 'Mancanegara'],
            datasets: [{
                data: [
                    <?= $totalSegmentLocal ?>,
                    <?= $totalSegmentRegional ?>,
                    <?= $totalSegmentForeign ?>
                ],
                backgroundColor: ['#0f4c81', '#3b82f6', '#10b981'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // 3. Chart Perbandingan Destinasi (Bar)
    const ctxCompare = document.getElementById('destCompareChart').getContext('2d');
    new Chart(ctxCompare, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labelsDest) ?>,
            datasets: [{
                label: 'Total Pengunjung',
                data: <?= json_encode($dataDest) ?>,
                backgroundColor: 'rgba(15, 76, 129, 0.8)',
                borderColor: '#0f4c81',
                borderWidth: 1,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
