<?php
// File: modules/pengelola/reservations.php
// BahariChain: Halaman Tiket Masuk & Pesanan (Mitra Pengelola)

// ==============================================================================
// 1. SECURITY CHECK: Only pengelola_wisata can access
// ==============================================================================
require_role(['pengelola_wisata']);
$message = '';
$messageType = '';

// ==============================================================================
// 2. PROCESS POST: UPDATE STATUS RESERVASI
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $message = "Token keamanan tidak valid. Silakan coba lagi.";
        $messageType = "danger";
    } else {
        $id_pesanan = intval($_POST['id_pesanan']);
        $status = trim($_POST['status'] ?? '');

        $allowedStatus = ['pending', 'unpaid', 'paid', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatus)) {
            $message = "Status tidak valid.";
            $messageType = "danger";
        } else {
            try {
                db_query("UPDATE pesanan SET status = ? WHERE id = ?", [$status, $id_pesanan]);
                
                // Kirim notifikasi ke wisatawan terkait
                $statusText = '';
                switch ($status) {
                    case 'paid': $statusText = 'Lunas'; break;
                    case 'completed': $statusText = 'Selesai'; break;
                    case 'cancelled': $statusText = 'Dibatalkan'; break;
                    case 'pending': $statusText = 'Menunggu Konfirmasi'; break;
                    case 'unpaid': $statusText = 'Belum Dibayar'; break;
                }

                db_query("
                    INSERT INTO notifikasi (user_id, judul, pesan, tipe, is_read, created_at)
                    SELECT user_id, 'Status Pesanan Diperbarui', CONCAT('Pesanan Anda #ORD-', ?, ' statusnya telah diubah menjadi ', ?), 'info', 0, NOW()
                    FROM pesanan WHERE id = ?
                ", [$id_pesanan, $statusText, $id_pesanan]);

                log_info("Pengelola updated reservation #ORD-{$id_pesanan} status to '{$status}'.");
                
                $_SESSION['success_message'] = "Status pesanan #ORD-{$id_pesanan} berhasil diperbarui menjadi " . strtoupper($status) . ".";
                redirect(BASE_URL . "index.php?module=pengelola&action=reservations&id=" . $id_pesanan);
            } catch (PDOException $e) {
                log_error("Pengelola update status error: " . $e->getMessage());
                $message = "Gagal memperbarui status pesanan: " . $e->getMessage();
                $messageType = "danger";
            }
        }
    }
}

// ==============================================================================
// 3. LOAD SUCCESS MESSAGE FROM SESSION IF ANY
// ==============================================================================
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $messageType = 'success';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $messageType = 'danger';
    unset($_SESSION['error_message']);
}

// ==============================================================================
// 4. DETAIL VIEW OR LIST VIEW
// ==============================================================================
$id_detail = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_detail > 0) {
    // --------------------------------------------------------------------------
    // A. DETAILED RESERVATION VIEW
    // --------------------------------------------------------------------------
    try {
        $query = "SELECT p.*, 
                         pkt.nama_paket, pkt.harga as harga_paket, pkt.durasi_hari, pkt.deskripsi as itinerary, pkt.foto_path as foto_paket,
                         u.username AS username_wisatawan, u.nama_lengkap, u.email, u.no_telepon,
                         t.nama_armada, t.jenis_kendaraan, t.harga_sewa,
                         pb.metode_pembayaran, pb.bukti_transfer, pb.tanggal_bayar, pb.status as status_pembayaran, pb.jumlah as jumlah_pembayaran
                  FROM pesanan p 
                  LEFT JOIN paket_wisata pkt ON p.paket_wisata_id = pkt.id 
                  LEFT JOIN users u ON p.user_id = u.id 
                  LEFT JOIN transportasi t ON p.transportasi_id = t.id
                  LEFT JOIN pembayaran pb ON p.id = pb.pesanan_id
                  WHERE p.id = ?";
        
        $pesanan = db_query($query, [$id_detail])->fetch();
        
        if (!$pesanan) {
            $_SESSION['error_message'] = "Pesanan tidak ditemukan.";
            redirect(BASE_URL . "index.php?module=pengelola&action=reservations");
        }
    } catch (PDOException $e) {
        log_error("Pengelola load reservation detail error: " . $e->getMessage());
        $_SESSION['error_message'] = "Terjadi kesalahan database.";
        redirect(BASE_URL . "index.php?module=pengelola&action=reservations");
    }

    $pageTitle = "Detail Pesanan #ORD-" . $pesanan['id'];
    require_once __DIR__ . '/../../includes/header.php';
    
    // Badge styles
    $status = strtolower($pesanan['status'] ?? 'pending');
    $badge_style = 'background: #f1f5f9; color: #64748b;';
    if ($status == 'paid' || $status == 'lunas') $badge_style = 'background: #ecfdf5; color: #047857;';
    if ($status == 'pending' || $status == 'unpaid') $badge_style = 'background: #fffbeb; color: #b45309;';
    if ($status == 'completed' || $status == 'selesai') $badge_style = 'background: #f0f9ff; color: #0369a1;';
    if ($status == 'cancelled' || $status == 'batal') $badge_style = 'background: #fef2f2; color: #ef4444;';
    ?>

    <!-- Navigation Header -->
    <div style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-file-invoice" style="color: #f59e0b;"></i> Rincian Pesanan #ORD-<?= $pesanan['id'] ?>
            </h1>
            <p style="color: var(--text-secondary); font-size: 14px; margin: 4px 0 0 0;">Detail transaksi reservasi paket wisata dan armada transportasi</p>
        </div>
        <a href="<?= BASE_URL ?>index.php?module=pengelola&action=reservations" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <?php if ($message): ?>
        <div style="padding: 15px 20px; background: <?= $messageType === 'success' ? '#d1fae5' : '#fee2e2' ?>; color: <?= $messageType === 'success' ? '#065f46' : '#991b1b' ?>; border-radius: var(--radius); margin-bottom: 25px; font-size: 14px; font-weight: 500;">
            <i class="fa-solid <?= $messageType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i> <?= esc($message) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-3" style="align-items: start; gap: 24px; margin-bottom: 40px;">
        
        <!-- Left: Order and Package Details (Takes 2 Columns) -->
        <div style="grid-column: span 2; display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Card 1: Utama Reservasi -->
            <div class="card" style="padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="color: var(--primary); font-size: 16px; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                    <i class="fa-solid fa-circle-info" style="color:#0ea5e9;"></i> Detail Reservasi
                </h3>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; font-size: 14px;">
                    <div>
                        <p style="color: var(--text-secondary); margin: 0 0 4px 0;">Tanggal Pemesanan:</p>
                        <strong style="color: var(--primary);"><?= date('d F Y, H:i', strtotime($pesanan['created_at'])) ?></strong>
                    </div>
                    <div>
                        <p style="color: var(--text-secondary); margin: 0 0 4px 0;">Tanggal Keberangkatan:</p>
                        <strong style="color: var(--primary);"><?= date('d F Y', strtotime($pesanan['tanggal_perjalanan'])) ?></strong>
                    </div>
                    <div>
                        <p style="color: var(--text-secondary); margin: 0 0 4px 0;">Jumlah Peserta:</p>
                        <strong><?= esc($pesanan['jumlah_peserta']) ?> Pax</strong>
                    </div>
                    <div>
                        <p style="color: var(--text-secondary); margin: 0 0 4px 0;">Status Reservasi:</p>
                        <span style="<?= $badge_style ?> padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase; display: inline-block;">
                            <?= $status ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Paket Wisata & Itinerary -->
            <div class="card" style="padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="color: var(--primary); font-size: 16px; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                    <i class="fa-solid fa-suitcase-rolling" style="color:#3b82f6;"></i> Paket Wisata Yang Dipesan
                </h3>
                
                <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 16px;">
                    <?php if (!empty($pesanan['foto_paket'])): ?>
                        <img src="<?= BASE_URL . esc($pesanan['foto_paket']) ?>" alt="Foto Paket" style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                    <?php else: ?>
                        <div style="width: 120px; height: 80px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-secondary);">
                            <i class="fa-regular fa-image" style="font-size: 24px;"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h4 style="color: var(--primary); margin: 0 0 8px 0; font-weight: 700; font-size: 16px;"><?= esc($pesanan['nama_paket']) ?></h4>
                        <p style="margin: 0; font-size: 14px; color: var(--text-secondary);"><i class="fa-regular fa-clock"></i> Durasi: <strong><?= esc($pesanan['durasi_hari']) ?> Hari</strong></p>
                        <p style="margin: 4px 0 0 0; font-size: 14px; color: var(--text-secondary);"><i class="fa-solid fa-tag"></i> Tarif Dasar: <strong>Rp <?= number_format($pesanan['harga_paket'], 0, ',', '.') ?> / pax</strong></p>
                    </div>
                </div>

                <div style="background-color: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid var(--border); font-size: 13px;">
                    <p style="font-weight: 600; color: var(--primary); margin: 0 0 8px 0;"><i class="fa-solid fa-route"></i> Itinerary Perjalanan:</p>
                    <p style="margin: 0; color: var(--text-primary); line-height: 1.6; white-space: pre-line;"><?= esc($pesanan['itinerary']) ?></p>
                </div>
            </div>

            <!-- Card 3: Sewa Transportasi -->
            <div class="card" style="padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="color: var(--primary); font-size: 16px; font-weight: 700; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                    <i class="fa-solid fa-car" style="color:#10b981;"></i> Sewa Jasa Transportasi (Tambahan)
                </h3>
                
                <?php if ($pesanan['transportasi_id']): ?>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; font-size: 14px;">
                        <div>
                            <p style="color: var(--text-secondary); margin: 0 0 4px 0;">Armada:</p>
                            <strong><?= esc($pesanan['nama_armada']) ?></strong>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); margin: 0 0 4px 0;">Jenis Kendaraan:</p>
                            <span style="background: #eff6ff; color: #1e40af; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                                <?= esc(str_replace('_', ' ', $pesanan['jenis_kendaraan'])) ?>
                            </span>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); margin: 0 0 4px 0;">Tarif Sewa (Flat):</p>
                            <strong style="color: #047857;">Rp <?= number_format($pesanan['harga_sewa'], 0, ',', '.') ?></strong>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-secondary); font-style: italic; font-size: 14px; margin: 0;">Tidak memesan transportasi tambahan.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Actions, Customer, and Payments (Takes 1 Column) -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Card 4: Update Status & Total Tagihan -->
            <div class="card" style="padding: 24px; box-shadow: var(--shadow-sm); border-top: 4px solid #f59e0b;">
                <p style="color: var(--text-secondary); font-size: 12px; margin: 0 0 4px 0; font-weight: 600; text-transform: uppercase;">Total Pembayaran</p>
                <h2 style="font-size: 26px; font-weight: 700; color: #047857; margin: 0 0 20px 0;">
                    Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?>
                </h2>

                <h3 style="color: var(--primary); font-size: 15px; font-weight: 700; margin-bottom: 12px;">
                    <i class="fa-solid fa-gear"></i> Update Status Pesanan
                </h3>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id_pesanan" value="<?= $pesanan['id'] ?>">

                    <div class="form-group" style="margin-bottom: 16px;">
                        <select name="status" class="form-control" style="background: white; border: 1px solid var(--border); padding: 10px; border-radius: 8px; font-size: 14px;" required>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>PENDING (Menunggu)</option>
                            <option value="unpaid" <?= $status === 'unpaid' ? 'selected' : '' ?>>UNPAID (Belum Bayar)</option>
                            <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>PAID (Lunas)</option>
                            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>COMPLETED (Selesai)</option>
                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>CANCELLED (Batal)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; background: #f59e0b; border-color:#f59e0b; color:white; font-weight: 600;">
                        <i class="fa-solid fa-circle-check"></i> Simpan Status
                    </button>
                </form>
            </div>

            <!-- Card 5: Info Wisatawan -->
            <div class="card" style="padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="color: var(--primary); font-size: 15px; font-weight: 700; margin-bottom: 15px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                    <i class="fa-solid fa-user" style="color:#a855f7;"></i> Kontak Wisatawan
                </h3>
                
                <div style="font-size: 13px; display: flex; flex-direction: column; gap: 10px;">
                    <div>
                        <span style="color: var(--text-secondary); display: block; font-size: 11px;">NAMA LENGKAP:</span>
                        <strong style="color: var(--primary); font-size: 14px;"><?= esc($pesanan['nama_lengkap'] ?? $pesanan['username_wisatawan']) ?></strong>
                    </div>
                    <div>
                        <span style="color: var(--text-secondary); display: block; font-size: 11px;">EMAIL:</span>
                        <strong><?= esc($pesanan['email']) ?></strong>
                    </div>
                    <div>
                        <span style="color: var(--text-secondary); display: block; font-size: 11px;">NO TELEPON:</span>
                        <strong><?= esc($pesanan['no_telepon'] ?? '-') ?></strong>
                    </div>
                </div>
            </div>

            <!-- Card 6: Bukti Pembayaran -->
            <div class="card" style="padding: 24px; box-shadow: var(--shadow-sm);">
                <h3 style="color: var(--primary); font-size: 15px; font-weight: 700; margin-bottom: 15px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                    <i class="fa-solid fa-receipt" style="color:#ef4444;"></i> Bukti Transfer
                </h3>

                <?php if (!empty($pesanan['bukti_transfer'])): ?>
                    <div style="font-size: 13px; display: flex; flex-direction: column; gap: 12px;">
                        <div>
                            <span style="color: var(--text-secondary); display: block; font-size: 11px;">METODE:</span>
                            <strong><?= esc($pesanan['metode_pembayaran']) ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--text-secondary); display: block; font-size: 11px;">NOMINAL:</span>
                            <strong style="color:#047857;">Rp <?= number_format($pesanan['jumlah_pembayaran'], 0, ',', '.') ?></strong>
                        </div>
                        
                        <div style="text-align: center; margin-top: 10px;">
                            <a href="<?= BASE_URL . esc($pesanan['bukti_transfer']) ?>" target="_blank" title="Klik untuk perbesar">
                                <img src="<?= BASE_URL . esc($pesanan['bukti_transfer']) ?>" alt="Bukti Transfer" style="max-width: 100%; max-height: 160px; object-fit: contain; border-radius: 8px; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-secondary); font-style: italic; font-size: 13px; margin: 0;">Wisatawan belum mengunggah bukti transfer.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <?php
    require_once __DIR__ . '/../../includes/footer.php';
} else {
    // --------------------------------------------------------------------------
    // B. LIST VIEW (ALL RESERVATIONS)
    // --------------------------------------------------------------------------
    require_once __DIR__ . '/../../includes/header.php';
    ?>

    <div class="container-fluid py-4 mb-5">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-ticket" style="color: #f59e0b;"></i> Tiket Masuk & Pesanan
            </h2>
            <a href="<?= BASE_URL ?>index.php?module=pengelola&action=dashboard" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-arrow-left"></i> Dashboard
            </a>
        </div>

        <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; color: #b45309; padding: 16px; border-radius: 4px; margin-bottom: 24px;">
            <i class="fa-solid fa-circle-info" style="margin-right: 8px;"></i> 
            Pantau pesanan wisatawan di sini. Pastikan untuk memperbarui status pesanan jika wisatawan sudah tiba di lokasi.
        </div>

        <?php if ($message): ?>
            <div style="padding: 15px 20px; background: <?= $messageType === 'success' ? '#d1fae5' : '#fee2e2' ?>; color: <?= $messageType === 'success' ? '#065f46' : '#991b1b' ?>; border-radius: var(--radius); margin-bottom: 25px; font-size: 14px; font-weight: 500;">
                <i class="fa-solid <?= $messageType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i> <?= esc($message) ?>
            </div>
        <?php endif; ?>

        <div class="card" style="padding: 24px; border-top: 4px solid #f59e0b; box-shadow: var(--shadow-sm); border-radius: 8px;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e2e8f0; color: var(--text-secondary);">
                            <th style="padding: 12px 8px;">ID Pesanan</th>
                            <th style="padding: 12px 8px;">Nama Wisatawan</th>
                            <th style="padding: 12px 8px;">Paket Wisata</th>
                            <th style="padding: 12px 8px; text-align: center;">Jumlah Peserta</th>
                            <th style="padding: 12px 8px;">Tgl Perjalanan</th>
                            <th style="padding: 12px 8px;">Total Harga</th>
                            <th style="padding: 12px 8px; text-align: center;">Status</th>
                            <th style="padding: 12px 8px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $query = "SELECT p.*, 
                                             pkt.nama_paket, 
                                             u.username AS nama_wisatawan 
                                      FROM pesanan p 
                                      LEFT JOIN paket_wisata pkt ON p.paket_wisata_id = pkt.id 
                                      LEFT JOIN users u ON p.user_id = u.id 
                                      ORDER BY p.created_at DESC";
                            
                            $stmt = db_query($query);
                            $pesanan_list = $stmt->fetchAll();
                            
                            if (count($pesanan_list) > 0) {
                                foreach ($pesanan_list as $row) {
                                    $id_pesanan = esc($row['id'] ?? '-');
                                    $nama_wisatawan = esc($row['nama_wisatawan'] ?? 'Anonim');
                                    $nama_paket = esc($row['nama_paket'] ?? 'Paket Tidak Diketahui');
                                    $jumlah_peserta = esc($row['jumlah_peserta'] ?? 1);
                                    
                                    $tgl_perjalanan = isset($row['tanggal_perjalanan']) ? date('d M Y', strtotime($row['tanggal_perjalanan'])) : '-';
                                    $total_harga = isset($row['total_harga']) ? 'Rp ' . number_format($row['total_harga'], 0, ',', '.') : 'Rp 0';
                                    $status = strtolower($row['status'] ?? 'pending');
                                    
                                    $badge_style = 'background: #f1f5f9; color: #64748b;';
                                    if ($status == 'paid' || $status == 'lunas') $badge_style = 'background: #ecfdf5; color: #047857;';
                                    if ($status == 'pending' || $status == 'unpaid') $badge_style = 'background: #fffbeb; color: #b45309;';
                                    if ($status == 'completed' || $status == 'selesai') $badge_style = 'background: #f0f9ff; color: #0369a1;';
                                    if ($status == 'cancelled' || $status == 'batal') $badge_style = 'background: #fef2f2; color: #ef4444;';
                                    ?>
                                    <tr style="border-bottom: 1px solid #e5e7eb; transition: background-color 0.2s;">
                                        <td style="padding: 16px 8px; font-weight: 600; color: #64748b;">#ORD-<?= $id_pesanan ?></td>
                                        <td style="padding: 16px 8px; font-weight: 600; color: var(--primary);"><?= $nama_wisatawan ?></td>
                                        <td style="padding: 16px 8px;"><?= $nama_paket ?></td>
                                        <td style="padding: 16px 8px; text-align: center;">
                                            <span style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 4px; font-weight: 600;">
                                                <i class="fa-solid fa-users" style="color: #64748b; margin-right: 4px;"></i> <?= $jumlah_peserta ?>
                                            </span>
                                        </td>
                                        <td style="padding: 16px 8px;"><?= $tgl_perjalanan ?></td>
                                        <td style="padding: 16px 8px; font-weight: 600; color: #047857;"><?= $total_harga ?></td>
                                        <td style="padding: 16px 8px; text-align: center;">
                                            <span style="<?= $badge_style ?> padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                                                <?= $status ?>
                                            </span>
                                        </td>
                                        <td style="padding: 16px 8px; text-align: center;">
                                            <a href="<?= BASE_URL ?>index.php?module=pengelola&action=reservations&id=<?= $id_pesanan ?>" 
                                               style="color: #3b82f6; text-decoration: none; border: 1px solid #3b82f6; padding: 6px 14px; border-radius: 6px; font-weight: 600; font-size: 12px; transition: var(--transition);" 
                                               onmouseover="this.style.background='rgba(59, 130, 246, 0.05)'"
                                               onmouseout="this.style.background='transparent'"
                                               title="Lihat Detail">
                                                <i class="fa-solid fa-circle-info"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='8' style='padding: 40px 16px; text-align: center; color: var(--text-secondary);'>
                                            <i class='fa-solid fa-ticket' style='font-size: 48px; color: #cbd5e1; margin-bottom: 16px; display: block;'></i>
                                            <p style='margin: 0; font-weight: 500;'>Belum ada pesanan masuk.</p>
                                        </td>
                                      </tr>";
                            }
                        } catch (Exception $e) {
                            log_error("Gagal memuat data pesanan: " . $e->getMessage());
                            echo "<tr><td colspan='8' style='padding: 24px; text-align: center; color: #ef4444;'>Gagal memuat data. Error: " . esc($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../../includes/footer.php';
}
?>