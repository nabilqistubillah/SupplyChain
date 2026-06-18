<?php
// modules/admin/dashboard.php
// BahariChain: Admin Dashboard

// ==============================================================================
// 1. SECURITY CHECK: Only admin can access this page
// ==============================================================================
require_role(['admin']);

$pageTitle = "Dashboard Administrator";

// ==============================================================================
// 2. FETCH STATISTICS DATA
// ==============================================================================
try {
    // Total destinasi
    $destCount = db_query("SELECT COUNT(*) as total FROM destinasi")->fetch()['total'];
    
    // Total paket wisata
    $paketCount = db_query("SELECT COUNT(*) as total FROM paket_wisata")->fetch()['total'];
    
    // Menunggu verifikasi pembayaran (unpaid payments)
    $pendingPayments = db_query("SELECT COUNT(*) as total FROM pembayaran WHERE status = 'unpaid'")->fetch()['total'];
    
    // Total reservasi
    $pesananCount = db_query("SELECT COUNT(*) as total FROM pesanan")->fetch()['total'];
    
    // Total revenue (paid reservations)
    $totalRevenue = db_query("SELECT IFNULL(SUM(total_harga), 0) as total FROM pesanan WHERE status = 'paid'")->fetch()['total'];
    
} catch (PDOException $e) {
    log_error("Admin dashboard database error: " . $e->getMessage());
    $destCount = 0;
    $paketCount = 0;
    $pendingPayments = 0;
    $pesananCount = 0;
    $totalRevenue = 0;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- ==============================================================================
     ADMIN DASHBOARD HERO SECTION
     ============================================================================== -->
<div style="background: linear-gradient(135deg, var(--primary-dark), var(--primary)); color: white; border-radius: var(--radius); padding: 40px 30px; margin-bottom: 40px; box-shadow: var(--shadow-lg);">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid fa-shield-halved"></i> Dashboard Administrator BahariChain
            </h1>
            <p style="font-size: 14px; opacity: 0.9;">Kelola platform, verifikasi pembayaran, dan pantau aktivitas sistem</p>
        </div>
        <div style="text-align: right; font-size: 13px;">
            <p style="margin: 0; opacity: 0.8;">Waktu:</p>
            <p style="margin: 4px 0 0 0; font-size: 16px; font-weight: 600;"><?= date('d M Y, H:i') ?></p>
        </div>
    </div>
</div>

<!-- ==============================================================================
     KEY METRICS CARDS
     ============================================================================== -->
<div class="grid grid-4" style="margin-bottom: 40px;">
    <!-- Card 1: Total Destinasi -->
    <div class="card" style="padding: 24px; border-left: 4px solid #3b82f6;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 8px 0; font-weight: 500;">Total Destinasi Wisata</p>
                <h2 style="font-size: 32px; font-weight: 700; color: var(--primary); margin: 0;"><?= number_format($destCount) ?></h2>
            </div>
            <i class="fa-solid fa-umbrella-beach" style="font-size: 40px; color: #3b82f6; opacity: 0.2;"></i>
        </div>
    </div>
    
    <!-- Card 2: Total Paket Wisata -->
    <div class="card" style="padding: 24px; border-left: 4px solid #10b981;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 8px 0; font-weight: 500;">Total Paket Wisata</p>
                <h2 style="font-size: 32px; font-weight: 700; color: var(--primary); margin: 0;"><?= number_format($paketCount) ?></h2>
            </div>
            <i class="fa-solid fa-suitcase" style="font-size: 40px; color: #10b981; opacity: 0.2;"></i>
        </div>
    </div>
    
    <!-- Card 3: Menunggu Verifikasi Pembayaran -->
    <div class="card" style="padding: 24px; border-left: 4px solid #f59e0b;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 8px 0; font-weight: 500;">Menunggu Verifikasi</p>
                <h2 style="font-size: 32px; font-weight: 700; color: var(--primary); margin: 0;"><?= number_format($pendingPayments) ?></h2>
                <p style="color: #f59e0b; font-size: 12px; margin: 4px 0 0 0;">Pembayaran</p>
            </div>
            <i class="fa-solid fa-credit-card" style="font-size: 40px; color: #f59e0b; opacity: 0.2;"></i>
        </div>
    </div>
    
    <!-- Card 4: Total Reservasi -->
    <div class="card" style="padding: 24px; border-left: 4px solid #8b5cf6;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 8px 0; font-weight: 500;">Total Reservasi</p>
                <h2 style="font-size: 32px; font-weight: 700; color: var(--primary); margin: 0;"><?= number_format($pesananCount) ?></h2>
            </div>
            <i class="fa-solid fa-calendar-check" style="font-size: 40px; color: #8b5cf6; opacity: 0.2;"></i>
        </div>
    </div>
</div>

<!-- ==============================================================================
     ADMIN ACTION PANEL
     ============================================================================== -->
<div style="margin-bottom: 40px;">
    <h2 style="font-weight: 700; color: var(--primary); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-toolbox"></i> Kelola Platform
    </h2>
    
    <div class="grid grid-4" style="gap: 16px;">
        <!-- Action 1: Verifikasi Pembayaran -->
        <a href="<?= BASE_URL ?>index.php?module=admin&action=payments" style="text-decoration: none;">
            <div class="card" style="padding: 20px; cursor: pointer; transition: all 0.3s ease; text-align: center;">
                <i class="fa-solid fa-check-circle" style="font-size: 36px; color: #10b981; margin-bottom: 12px;"></i>
                <h3 style="color: var(--primary); font-weight: 600; margin-bottom: 4px;">Verifikasi Pembayaran</h3>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Periksa dan setujui bukti transfer</p>
                <?php if ($pendingPayments > 0): ?>
                    <span style="display: inline-block; background: #f59e0b; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; margin-top: 10px;">
                        <?= $pendingPayments ?> Pending
                    </span>
                <?php endif; ?>
            </div>
        </a>
        
        <!-- Action 2: Kelola Destinasi -->
        <a href="<?= BASE_URL ?>index.php?module=destinations&action=manage" style="text-decoration: none;">
            <div class="card" style="padding: 20px; cursor: pointer; transition: all 0.3s ease; text-align: center;">
                <i class="fa-solid fa-map-pin" style="font-size: 36px; color: #3b82f6; margin-bottom: 12px;"></i>
                <h3 style="color: var(--primary); font-weight: 600; margin-bottom: 4px;">Kelola Destinasi</h3>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Tambah/edit destinasi wisata bahari</p>
            </div>
        </a>
        
        <!-- Action 3: Registrasi Mitra Baru -->
        <a href="<?= BASE_URL ?>index.php?module=admin&action=users" style="text-decoration: none;">
            <div class="card" style="padding: 20px; cursor: pointer; transition: all 0.3s ease; text-align: center;">
                <i class="fa-solid fa-user-plus" style="font-size: 36px; color: #10b981; margin-bottom: 12px;"></i>
                <h3 style="color: var(--primary); font-weight: 600; margin-bottom: 4px;">➕ Registrasi Mitra Baru</h3>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Daftarkan akun vendor UMKM</p>
            </div>
        </a>
        
        <!-- Action 4: Laporan & Analitik -->
        <a href="<?= BASE_URL ?>index.php?module=admin&action=reports" style="text-decoration: none;">
            <div class="card" style="padding: 20px; cursor: pointer; transition: all 0.3s ease; text-align: center;">
                <i class="fa-solid fa-chart-line" style="font-size: 36px; color: #8b5cf6; margin-bottom: 12px;"></i>
                <h3 style="color: var(--primary); font-weight: 600; margin-bottom: 4px;">Laporan & Analitik</h3>
                <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Lihat statistik dan revenue platform</p>
            </div>
        </a>
    </div>
</div>

<!-- ==============================================================================
     REVENUE OVERVIEW
     ============================================================================== -->
<div class="card" style="padding: 30px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(59, 130, 246, 0.05)); border-left: 4px solid #10b981;">
    <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-money-bill-wave"></i> Ringkasan Keuangan Platform
    </h3>
    <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 16px 0;">Total revenue dari reservasi yang telah diverifikasi</p>
    
    <div style="display: flex; align-items: baseline; gap: 16px;">
        <div>
            <p style="color: var(--text-secondary); font-size: 13px; margin: 0 0 4px 0;">Total Pendapatan</p>
            <h2 style="font-size: 36px; font-weight: 700; color: #10b981; margin: 0;">
                Rp <?= number_format($totalRevenue, 0, ',', '.') ?>
            </h2>
        </div>
        <div style="flex: 1; text-align: right; color: var(--text-secondary); font-size: 12px;">
            <p style="margin: 0;">Dari <?= number_format($pesananCount) ?> total reservasi</p>
            <p style="margin: 4px 0 0 0;">Update: <?= date('d M Y H:i') ?></p>
        </div>
    </div>
</div>

<!-- ==============================================================================
     SYSTEM INFO
     ============================================================================== -->
<div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--border); text-align: center; color: var(--text-secondary); font-size: 12px;">
    <p style="margin: 0;">
        <i class="fa-solid fa-info-circle"></i> 
        BahariChain v3.0 | Logged as: <strong><?= esc($_SESSION['username']) ?></strong> | Last Updated: <?= date('d M Y H:i:s') ?>
    </p>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
