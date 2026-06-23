<?php
// modules/wisatawan/reservations.php
// BahariChain: Riwayat Pesanan & E-Tiket Wisatawan

require_role(['wisatawan']);

// ==============================================================================
// LOGIC: BATALKAN PESANAN (PENDING STATUS ONLY)
// ==============================================================================
if (isset($_GET['cancel_id'])) {
    $cancelId = (int)$_GET['cancel_id'];
    try {
        // Ambil pesanan dan pastikan milik user ini & berstatus pending
        $pesanan = db_query("SELECT * FROM pesanan WHERE id = ? AND user_id = ?", [$cancelId, $_SESSION['user_id']])->fetch();
        
        if ($pesanan && $pesanan['status'] === 'pending') {
            db_query("UPDATE pesanan SET status = 'cancelled' WHERE id = ?", [$cancelId]);
            
            // Tambahkan notifikasi pembatalan
            db_query("
                INSERT INTO notifikasi (user_id, judul, pesan, tipe, is_read, created_at)
                VALUES (?, 'Pesanan Dibatalkan', 'Pemesanan Anda untuk paket wisata telah berhasil dibatalkan.', 'info', 0, NOW())
            ", [$_SESSION['user_id']]);

            $_SESSION['success_message'] = "Pesanan #" . str_pad($cancelId, 5, '0', STR_PAD_LEFT) . " berhasil dibatalkan.";
        } else {
            $_SESSION['error_message'] = "Pesanan tidak dapat dibatalkan atau tidak ditemukan.";
        }
    } catch (PDOException $e) {
        log_error("Wisatawan cancellation database error: " . $e->getMessage());
        $_SESSION['error_message'] = "Terjadi kesalahan sistem saat memproses pembatalan.";
    }
    redirect(BASE_URL . 'index.php?module=wisatawan&action=reservations');
}

// ==============================================================================
// LOGIC: TAMPILAN E-TIKET (TICKET VIEW)
// ==============================================================================
if (isset($_GET['ticket_id'])) {
    $ticketId = (int)$_GET['ticket_id'];
    try {
        // Ambil data lengkap pesanan, paket, dan transportasi
        $stmt = db_query("
            SELECT p.*, u.nama_lengkap, u.email, u.no_telepon,
                   pw.nama_paket, pw.durasi_hari, pw.harga as harga_paket, pw.deskripsi as itinerary,
                   t.nama_armada, t.jenis_kendaraan
            FROM pesanan p
            JOIN users u ON p.user_id = u.id
            JOIN paket_wisata pw ON p.paket_wisata_id = pw.id
            LEFT JOIN transportasi t ON p.transportasi_id = t.id
            WHERE p.id = ? AND p.user_id = ?
        ", [$ticketId, $_SESSION['user_id']]);
        $ticket = $stmt->fetch();

        if (!$ticket || !in_array($ticket['status'], ['paid', 'completed'])) {
            $_SESSION['error_message'] = "E-Tiket tidak tersedia atau pembayaran belum lunas.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=reservations');
        }

    } catch (PDOException $e) {
        log_error("Wisatawan load ticket error: " . $e->getMessage());
        redirect(BASE_URL . 'index.php?module=wisatawan&action=reservations');
    }

    $pageTitle = "E-Tiket #" . str_pad($ticket['id'], 5, '0', STR_PAD_LEFT);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title><?= $pageTitle ?> | BahariChain</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <style>
            body { font-family: 'Outfit', 'Helvetica Neue', Arial, sans-serif; background-color: #f1f5f9; color: #1e293b; padding: 40px 20px; margin: 0; }
            .ticket-container { max-width: 700px; margin: 0 auto; background: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden; }
            .ticket-header { background: linear-gradient(135deg, #0f4c81, #1d72b8); color: white; padding: 30px; text-align: center; position: relative; }
            .ticket-header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px; }
            .ticket-header p { margin: 5px 0 0 0; font-size: 13px; opacity: 0.85; }
            .ticket-body { padding: 35px; }
            .ticket-section { margin-bottom: 25px; }
            .ticket-section-title { font-weight: 700; font-size: 14px; text-transform: uppercase; color: #0f4c81; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
            .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
            .info-item label { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px; }
            .info-item span { font-size: 14px; font-weight: 600; color: #1e293b; }
            .barcode-placeholder { text-align: center; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; margin-top: 30px; background-color: #f8fafc; }
            .no-print-actions { max-width: 700px; margin: 20px auto 0 auto; display: flex; justify-content: space-between; gap: 12px; }
            .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 10px; cursor: pointer; text-decoration: none; border: none; gap: 8px; transition: all 0.2s ease; }
            .btn-back { background-color: #cbd5e1; color: #334155; }
            .btn-back:hover { background-color: #94a3b8; }
            .btn-print { background-color: #0f4c81; color: white; }
            .btn-print:hover { background-color: #1d72b8; }
            
            @media print {
                body { background-color: white; padding: 0; }
                .ticket-container { box-shadow: none; border: none; }
                .no-print-actions { display: none; }
                .ticket-header { background: #0f4c81 !important; color: white !important; -webkit-print-color-adjust: exact; }
            }
        </style>
    </head>
    <body>

        <div class="ticket-container">
            <!-- Header -->
            <div class="ticket-header">
                <h1 style="display:flex; justify-content:center; align-items:center; gap:8px;">
                    <i class="fa-solid fa-anchor"></i> BahariChain E-Tiket
                </h1>
                <p>Simpan atau Cetak Tiket Ini Sebagai Bukti Keberangkatan Resmi Anda</p>
                <div style="position: absolute; top: 15px; right: 20px; background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                    STATUS: LUNAS
                </div>
            </div>

            <!-- Body -->
            <div class="ticket-body">
                <!-- Section 1: Detail Pesanan -->
                <div class="ticket-section">
                    <div class="ticket-section-title">
                        <i class="fa-solid fa-receipt"></i> Data Reservasi Perjalanan
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>No. Invoice</label>
                            <span>#INV-<?= str_pad($ticket['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Nama Paket</label>
                            <span><?= esc($ticket['nama_paket']) ?> (<?= $ticket['durasi_hari'] ?> Hari)</span>
                        </div>
                        <div class="info-item">
                            <label>Tanggal Perjalanan</label>
                            <span><?= date('d F Y', strtotime($ticket['tanggal_perjalanan'])) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Jumlah Peserta</label>
                            <span><?= $ticket['jumlah_peserta'] ?> Orang</span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Data Pemesan -->
                <div class="ticket-section">
                    <div class="ticket-section-title">
                        <i class="fa-solid fa-user"></i> Informasi Wisatawan
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Nama Lengkap</label>
                            <span><?= esc($ticket['nama_lengkap'] ?? $_SESSION['full_name']) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Email Utama</label>
                            <span><?= esc($ticket['email'] ?? $_SESSION['email']) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Nomor Telepon</label>
                            <span><?= esc($ticket['no_telepon'] ?: '-') ?></span>
                        </div>
                        <div class="info-item">
                            <label>Rental Transportasi</label>
                            <span><?= $ticket['nama_armada'] ? esc($ticket['nama_armada']) : 'Tidak sewa' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Barcode / Check-in -->
                <div class="barcode-placeholder">
                    <i class="fa-solid fa-qrcode" style="font-size: 64px; color: #475569; margin-bottom: 8px;"></i>
                    <div style="font-size: 14px; font-weight: 700; color: #334155; margin-bottom: 4px;">KODE VERIFIKASI: <?= md5($ticket['id'] . 'bahari') ?></div>
                    <p style="font-size: 11px; color: #64748b; margin: 0; max-width: 480px; margin: 0 auto; line-height: 1.5;">
                        Tunjukkan QR-Code / Bukti e-tiket ini kepada petugas Pos Keberangkatan pariwisata bahari atau perwakilan armada pengelola untuk verifikasi check-in.
                    </p>
                </div>
            </div>
        </div>

        <!-- Buttons outside printed page -->
        <div class="no-print-actions">
            <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reservations" class="btn btn-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <i class="fa-solid fa-print"></i> Cetak Tiket
            </button>
        </div>

    </body>
    </html>
    <?php
    exit;
}

// ==============================================================================
// REGULAR LIST VIEW
// ==============================================================================
try {
    $stmtReservations = db_query("
        SELECT p.*, pw.nama_paket, pw.durasi_hari, pw.harga as harga_paket,
               t.nama_armada, t.jenis_kendaraan, t.harga_sewa
        FROM pesanan p
        JOIN paket_wisata pw ON p.paket_wisata_id = pw.id
        LEFT JOIN transportasi t ON p.transportasi_id = t.id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
    ", [$_SESSION['user_id']]);
    $reservations = $stmtReservations->fetchAll();
} catch (PDOException $e) {
    log_error("Wisatawan load reservations list error: " . $e->getMessage());
    $reservations = [];
}

$pageTitle = "Riwayat Pesanan Saya";
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Navigation Breadcrumbs -->
<div style="margin-bottom: 20px;">
    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=dashboard" style="color: var(--text-secondary); font-size: 14px;">
        <i class="fa-solid fa-house"></i> Dashboard
    </a>
    <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
    <span style="color: var(--primary); font-size: 14px; font-weight: 600;">Riwayat Pesanan</span>
</div>

<!-- Header Section -->
<div style="margin-bottom: 30px;">
    <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 8px;">Riwayat Pesanan & Tiket Saya</h1>
    <p style="color: var(--text-secondary);">Pantau status transaksi reservasi paket wisata dan download e-tiket keberangkatan Anda.</p>
</div>

<!-- Alerts Notification -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> <?= esc($_SESSION['success_message']) ?>
        <?php unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i> <?= esc($_SESSION['error_message']) ?>
        <?php unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<!-- Reservations Table Card -->
<div class="card" style="padding: 0; overflow: hidden; margin-bottom: 40px;">
    <?php if (empty($reservations)): ?>
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fa-solid fa-cart-flatbed-suitcase" style="font-size: 54px; color: var(--text-secondary); opacity: 0.4; margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-primary); font-weight: 600;">Belum Ada Pemesanan</h3>
            <p style="color: var(--text-secondary); margin-top: 8px; margin-bottom: 24px;">Anda belum melakukan pemesanan paket wisata bahari apa pun.</p>
            <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=packages" class="btn btn-primary">
                <i class="fa-solid fa-suitcase"></i> Jelajahi Paket Wisata
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="background-color: var(--background); border-bottom: 1px solid var(--border); color: var(--primary);">
                        <th style="padding: 16px 20px; font-weight: 600;">No. Invoice</th>
                        <th style="padding: 16px 20px; font-weight: 600;">Paket Wisata</th>
                        <th style="padding: 16px 20px; font-weight: 600;">Tgl Perjalanan</th>
                        <th style="padding: 16px 20px; font-weight: 600; text-align: center;">Peserta</th>
                        <th style="padding: 16px 20px; font-weight: 600; text-align: right;">Total Biaya</th>
                        <th style="padding: 16px 20px; font-weight: 600; text-align: center;">Status</th>
                        <th style="padding: 16px 20px; font-weight: 600; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $res): 
                        // Status Badge Colors & Label mapping
                        $statusText = '';
                        $badgeBg = '';
                        $badgeColor = '';
                        
                        switch ($res['status']) {
                            case 'pending':
                                $statusText = 'Belum Bayar';
                                $badgeBg = '#fef3c7';
                                $badgeColor = '#d97706';
                                break;
                            case 'unpaid':
                                $statusText = 'Verifikasi';
                                $badgeBg = '#dbeafe';
                                $badgeColor = '#2563eb';
                                break;
                            case 'paid':
                                $statusText = 'Lunas';
                                $badgeBg = '#d1fae5';
                                $badgeColor = '#059669';
                                break;
                            case 'completed':
                                $statusText = 'Selesai';
                                $badgeBg = '#f3f4f6';
                                $badgeColor = '#4b5563';
                                break;
                            case 'cancelled':
                                $statusText = 'Dibatalkan';
                                $badgeBg = '#fee2e2';
                                $badgeColor = '#dc2626';
                                break;
                        }
                    ?>
                        <tr style="border-bottom: 1px solid var(--border); transition: var(--transition);">
                            <!-- Invoice ID -->
                            <td style="padding: 16px 20px; font-weight: 700; color: var(--primary);">
                                #INV-<?= str_pad($res['id'], 5, '0', STR_PAD_LEFT) ?>
                            </td>

                            <!-- Tour Package -->
                            <td style="padding: 16px 20px;">
                                <div style="font-weight: 600; color: var(--text-primary);"><?= esc($res['nama_paket']) ?></div>
                                <div style="font-size: 12px; color: var(--text-secondary); display:flex; align-items:center; gap: 4px; margin-top: 2px;">
                                    <i class="fa-solid fa-clock"></i> <?= $res['durasi_hari'] ?> Hari
                                    <?php if ($res['nama_armada']): ?>
                                        <span style="color: var(--accent); margin: 0 4px;">|</span>
                                        <i class="fa-solid fa-car-side"></i> Rental: <?= esc($res['nama_armada']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Date Travel -->
                            <td style="padding: 16px 20px; color: var(--text-secondary);">
                                <?= date('d M Y', strtotime($res['tanggal_perjalanan'])) ?>
                            </td>

                            <!-- Pax count -->
                            <td style="padding: 16px 20px; text-align: center; font-weight: 500;">
                                <?= $res['jumlah_peserta'] ?> pax
                            </td>

                            <!-- Total payment -->
                            <td style="padding: 16px 20px; text-align: right; font-weight: 700; color: var(--primary);">
                                Rp <?= number_format($res['total_harga'], 0, ',', '.') ?>
                            </td>

                            <!-- Status Badge -->
                            <td style="padding: 16px 20px; text-align: center;">
                                <span style="background-color: <?= $badgeBg ?>; color: <?= $badgeColor ?>; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; text-transform: uppercase; letter-spacing: 0.25px;">
                                    <?= $statusText ?>
                                </span>
                            </td>

                            <!-- Action Buttons -->
                            <td style="padding: 16px 20px; text-align: center;">
                                <div style="display: inline-flex; gap: 8px; justify-content: center; align-items: center;">
                                    <?php if ($res['status'] === 'pending'): ?>
                                        <!-- Bayar -->
                                        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=payment&pesanan_id=<?= $res['id'] ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px; background-color: var(--success);">
                                            <i class="fa-solid fa-credit-card"></i> Bayar
                                        </a>
                                        <!-- Batal -->
                                        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reservations&cancel_id=<?= $res['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; border-color: var(--danger); color: var(--danger);">
                                            Batal
                                        </a>
                                    <?php elseif (in_array($res['status'], ['paid', 'completed'])): ?>
                                        <!-- E-Tiket -->
                                        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reservations&ticket_id=<?= $res['id'] ?>" target="_blank" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; color: var(--primary); border-color: var(--primary);">
                                            <i class="fa-solid fa-ticket"></i> E-Tiket
                                        </a>
                                        <!-- Beri Ulasan -->
                                        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reviews&paket_wisata_id=<?= $res['paket_wisata_id'] ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px; background-color: var(--accent);">
                                            <i class="fa-solid fa-star"></i> Ulas
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-secondary); font-size: 12px; font-style: italic;">Tidak ada aksi</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
