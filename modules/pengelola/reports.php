<?php
// File: modules/pengelola/reports.php
// BahariChain: Halaman Laporan Pendapatan

require_role(['pengelola_wisata']);
$pageTitle = "Laporan Pendapatan";

require_once __DIR__ . '/../../includes/header.php';

// ==============================================================================
// MENGHITUNG RINGKASAN PENDAPATAN (Hanya pesanan yang sudah dibayar/selesai)
// ==============================================================================
try {
    $summaryQuery = "SELECT 
                        COUNT(id) as total_transaksi, 
                        SUM(total_harga) as total_pendapatan 
                     FROM pesanan 
                     WHERE status IN ('paid', 'lunas', 'completed', 'selesai')";
    $summaryStmt = db_query($summaryQuery);
    $summaryData = $summaryStmt->fetch();
    
    $totalPendapatan = $summaryData['total_pendapatan'] ?? 0;
    $totalTransaksi = $summaryData['total_transaksi'] ?? 0;
} catch (Exception $e) {
    $totalPendapatan = 0;
    $totalTransaksi = 0;
    log_error("Gagal menghitung ringkasan pendapatan: " . $e->getMessage());
}
?>

<div class="container-fluid py-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-10" style="margin-bottom: 15px;">
        <h2 style="font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-chart-line" style="color: #10b981;"></i> Laporan Pendapatan
        </h2>
        <button class="btn btn-outline-success" onclick="window.print()" style="border-color: #10b981; color: #10b981;">
            <i class="fa-solid fa-print"></i> Cetak Laporan
        </button>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4" style="margin-bottom: 24px;">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #10b981, #059669); color: white;">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1" style="opacity: 0.9; font-size: 14px; font-weight: 500;">Total Pendapatan Bersih</p>
                        <h2 class="mb-0 fw-bold">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></h2>
                    </div>
                    <i class="fa-solid fa-wallet" style="font-size: 48px; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4" style="margin-bottom: 24px;">
            <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid #10b981;">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size: 14px; font-weight: 500;">Total Transaksi Sukses</p>
                        <h2 class="mb-0 fw-bold" style="color: #10b981;"><?= number_format($totalTransaksi) ?> <span style="font-size: 16px; font-weight: normal; color: var(--text-secondary);">Pesanan</span></h2>
                    </div>
                    <i class="fa-solid fa-check-circle" style="font-size: 48px; color: #10b981; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-