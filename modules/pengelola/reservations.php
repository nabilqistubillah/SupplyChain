<?php
// File: modules/pengelola/reservations.php
// BahariChain: Halaman Tiket Masuk & Pesanan

require_role(['pengelola_wisata']);
$pageTitle = "Tiket Masuk & Pesanan";

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-ticket" style="color: #f59e0b;"></i> Tiket Masuk & Pesanan
        </h2>
    </div>

    <div class="alert mb-4" style="background-color: #fffbeb; border-left: 4px solid #f59e0b; color: #b45309;">
        <i class="fa-solid fa-circle-info me-2"></i> 
        Pantau pesanan wisatawan di sini. Pastikan untuk memperbarui status pesanan jika wisatawan sudah tiba di lokasi.
    </div>

    <div class="card shadow-sm" style="border-top: 4px solid #f59e0b; border-radius: 8px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 14px;">
                    <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="p-3">ID Pesanan</th>
                            <th class="p-3">Nama Wisatawan</th>
                            <th class="p-3">Paket Wisata</th>
                            <th class="p-3 text-center">Peserta</th>
                            <th class="p-3">Tgl Perjalanan</th>
                            <th class="p-3">Total Harga</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Query disesuaikan dengan struktur: JOIN pesanan, paket_wisata, dan users
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
                                    
                                    // Menggunakan kolom tanggal_perjalanan sesuai database
                                    $tgl_perjalanan = isset($row['tanggal_perjalanan']) ? date('d M Y', strtotime($row['tanggal_perjalanan'])) : '-';
                                    
                                    $total_harga = isset($row['total_harga']) ? 'Rp ' . number_format($row['total_harga'], 0, ',', '.') : 'Rp 0';
                                    $status = strtolower($row['status'] ?? 'pending');
                                    
                                    $badge_class = 'bg-secondary';
                                    if ($status == 'paid' || $status == 'lunas') $badge_class = 'bg-success';
                                    if ($status == 'pending' || $status == 'unpaid') $badge_class = 'bg-warning text-dark';
                                    if ($status == 'completed' || $status == 'selesai') $badge_class = 'bg-info text-dark';
                                    if ($status == 'cancelled' || $status == 'batal') $badge_class = 'bg-danger';
                                    ?>
                                    <tr>
                                        <td class="p-3 align-middle fw-bold text-secondary">#ORD-<?= $id_pesanan ?></td>
                                        <td class="p-3 align-middle">
                                            <div class="fw-bold"><?= $nama_wisatawan ?></div>
                                        </td>
                                        <td class="p-3 align-middle text-primary"><?= $nama_paket ?></td>
                                        <td class="p-3 align-middle text-center">
                                            <span class="badge bg-light text-dark border"><i class="fa-solid fa-users me-1"></i> <?= $jumlah_peserta ?></span>
                                        </td>
                                        <td class="p-3 align-middle"><?= $tgl_perjalanan ?></td>
                                        <td class="p-3 align-middle fw-bold text-success"><?= $total_harga ?></td>
                                        <td class="p-3 align-middle text-center">
                                            <span class="badge <?= $badge_class ?> px-2 py-1" style="border-radius: 4px; text-transform: uppercase; font-size: 11px;">
                                                <?= $status ?>
                                            </span>
                                        </td>
                                        <td class="p-3 align-middle text-center">
                                            <a href="<?= BASE_URL ?>index.php?module=pengelola&action=reservations_detail&id=<?= $id_pesanan ?>" 
                                            class="btn btn-sm btn-outline-primary" title="Lihat Detail Pesanan">
                                                <i class="fa-solid fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='8' class='text-center p-5 text-muted'>
                                            <i class='fa-solid fa-ticket mb-3 d-block' style='font-size: 40px; opacity: 0.3;'></i>
                                            Belum ada pesanan masuk.
                                        </td>
                                    </tr>";
                            }
                        } catch (Exception $e) {
                            log_error("Gagal memuat data pesanan: " . $e->getMessage());
                            echo "<tr><td colspan='8' class='text-center p-4 text-danger'>Gagal memuat data pesanan. Error: " . esc($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>