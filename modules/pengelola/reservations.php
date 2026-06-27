<?php
// File: modules/pengelola/reservations.php
// BahariChain: Halaman Tiket Masuk & Pesanan

require_role(['pengelola_wisata']);
$pageTitle = "Tiket Masuk & Pesanan";

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4 mb-5">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-ticket" style="color: #f59e0b;"></i> Tiket Masuk & Pesanan
        </h2>
    </div>

    <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; color: #b45309; padding: 16px; border-radius: 4px; margin-bottom: 24px;">
        <i class="fa-solid fa-circle-info" style="margin-right: 8px;"></i> 
        Pantau pesanan wisatawan di sini. Pastikan untuk memperbarui status pesanan jika wisatawan sudah tiba di lokasi.
    </div>

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
                                
                                // Gaya CSS Inline untuk Badge Status
                                $badge_style = 'background: #f1f5f9; color: #64748b;'; // Default abu-abu
                                if ($status == 'paid' || $status == 'lunas') $badge_style = 'background: #ecfdf5; color: #047857;'; // Hijau
                                if ($status == 'pending' || $status == 'unpaid') $badge_style = 'background: #fffbeb; color: #b45309;'; // Kuning
                                if ($status == 'completed' || $status == 'selesai') $badge_style = 'background: #f0f9ff; color: #0369a1;'; // Biru
                                if ($status == 'cancelled' || $status == 'batal') $badge_style = 'background: #fef2f2; color: #ef4444;'; // Merah
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
                                        <a href="<?= BASE_URL ?>index.php?module=pengelola&action=reservations_detail&id=<?= $id_pesanan ?>" 
                                           style="color: #3b82f6; text-decoration: none; border: 1px solid #3b82f6; padding: 4px 12px; border-radius: 4px; font-weight: 500; font-size: 12px;" 
                                           title="Lihat Detail">
                                            Detail
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>