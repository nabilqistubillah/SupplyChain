<?php
// File: modules/pengelola/reviews.php
// BahariChain: Halaman Ulasan Pengunjung

// 1. Include file koneksi database (Sesuaikan path dan nama filenya)
require_once __DIR__ . '/../../config/database.php'; 

// 2. Cek role dan set judul halaman
require_role(['pengelola_wisata']);
$pageTitle = "Ulasan Pengunjung";

// 3. Include header
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4 mb-5">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-star" style="color: #8b5cf6;"></i> Ulasan Pengunjung
        </h2>
        <a href="<?= BASE_URL ?>index.php?module=pengelola&action=dashboard" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <div class="alert mb-4" style="background-color: #f3f0ff; border-left: 4px solid #8b5cf6; color: #5b21b6;">
        <i class="fa-solid fa-circle-info me-2"></i> 
        Pantau kepuasan wisatawan. Ulasan yang baik dapat meningkatkan daya tarik destinasi wisata Anda.
    </div>

    <div class="card" style="padding: 24px; border-top: 4px solid #047857; box-shadow: var(--shadow-sm);">
        <div class="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); color: var(--text-secondary);">
                            <th style="padding: 12px 8px;">No</th>
                            <th style="padding: 12px 8px;">Paket Wisata</th>
                            <th style="padding: 12px 8px;">Nama Wisatawan</th>
                            <th style="padding: 12px 8px;">Rating</th>
                            <th style="padding: 12px 8px;">Komentar</th>
                            <th style="padding: 12px 8px;">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Query disesuaikan dengan kolom di phpMyAdmin: user_id dan paket_wisata_id
                            $query = "SELECT r.*, 
                                             p.nama_paket AS nama_paket, 
                                             u.username AS nama_wisatawan 
                                      FROM review r 
                                      LEFT JOIN paket_wisata p ON r.paket_wisata_id = p.id 
                                      LEFT JOIN users u ON r.user_id = u.id 
                                      ORDER BY r.created_at DESC";
                            
                            $stmt = db_query($query);
                            $reviews = $stmt->fetchAll();
                            
                            $no = 1;
                            if (count($reviews) > 0) {
                                foreach ($reviews as $row) {
                                    $nama_paket = esc($row['nama_paket'] ?? 'Paket Tidak Diketahui');
                                    // Asumsi tabel users memiliki kolom 'username' atau 'nama'
                                    $nama_wisatawan = esc($row['nama_wisatawan'] ?? 'Anonim'); 
                                    $komentar = esc($row['komentar'] ?? '-');
                                    $tanggal = isset($row['created_at']) ? date('d M Y, H:i', strtotime($row['created_at'])) : '-';
                                    
                                    // Render Bintang Rating
                                    $rating = (int)($row['rating'] ?? 0);
                                    $stars_html = '';
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            $stars_html .= '<i class="fa-solid fa-star text-warning"></i>';
                                        } else {
                                            $stars_html .= '<i class="fa-regular fa-star text-muted" style="opacity: 0.3;"></i>';
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td class="p-3 align-middle text-center"><?= $no++ ?></td>
                                        <td class="p-3 align-middle fw-bold text-primary"><?= $nama_paket ?></td>
                                        <td class="p-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 32px; height: 32px;">
                                                    <i class="fa-solid fa-user text-secondary" style="font-size: 12px;"></i>
                                                </div>
                                                <?= $nama_wisatawan ?>
                                            </div>
                                        </td>
                                        <td class="p-3 align-middle text-center" style="min-width: 120px;">
                                            <?= $stars_html ?>
                                        </td>
                                        <td class="p-3 align-middle fst-italic text-secondary">
                                            "<?= $komentar ?>"
                                        </td>
                                        <td class="p-3 align-middle text-muted" style="font-size: 12px;">
                                            <?= $tanggal ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                // Tampilan saat tabel database kosong (0 rows)
                                echo "<tr>
                                        <td colspan='6' class='text-center p-5 text-muted'>
                                            <i class='fa-regular fa-comment-dots mb-3 d-block' style='font-size: 40px; opacity: 0.5;'></i>
                                            Belum ada ulasan dari pengunjung.
                                        </td>
                                      </tr>";
                            }
                        } catch (Exception $e) {
                            if(function_exists('log_error')) {
                                log_error("Gagal memuat ulasan: " . $e->getMessage());
                            }
                            echo "<tr><td colspan='6' class='text-center p-4 text-danger'>Terjadi kesalahan sistem/database: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>