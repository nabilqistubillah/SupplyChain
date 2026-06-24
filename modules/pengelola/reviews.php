<?php
// File: modules/pengelola/reviews.php
// BahariChain: Halaman Ulasan Pengunjung

require_role(['pengelola_wisata']);
$pageTitle = "Ulasan Pengunjung";

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-star" style="color: #8b5cf6;"></i> Ulasan Pengunjung
        </h2>
    </div>

    <div class="alert mb-4" style="background-color: #f3f0ff; border-left: 4px solid #8b5cf6; color: #5b21b6;">
        <i class="fa-solid fa-circle-info me-2"></i> 
        Pantau kepuasan wisatawan. Ulasan yang baik dapat meningkatkan daya tarik destinasi wisata Anda.
    </div>

    <div class="card shadow-sm" style="border-top: 4px solid #8b5cf6; border-radius: 8px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 14px;">
                    <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="p-3">No</th>
                            <th class="p-3">Paket Wisata</th>
                            <th class="p-3">Nama Wisatawan</th>
                            <th class="p-3 text-center">Rating</th>
                            <th class="p-3">Komentar</th>
                            <th class="p-3">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Query yang sudah disesuaikan dengan struktur asli:
                            // JOIN ke tabel paket_wisata dan users
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
                                    // Mengamankan output
                                    $nama_paket = esc($row['nama_paket'] ?? 'Paket Tidak Diketahui');
                                    $nama_wisatawan = esc($row['nama_wisatawan'] ?? 'Anonim'); 
                                    $komentar = esc($row['komentar'] ?? '-');
                                    $tanggal = isset($row['created_at']) ? date('d M Y, H:i', strtotime($row['created_at'])) : '-';
                                    
                                    // Bintang rating
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
                                        <td class="p-3 align-middle"><?= $no++ ?></td>
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
                                echo "<tr>
                                        <td colspan='6' class='text-center p-5 text-muted'>
                                            <i class='fa-regular fa-comment-dots mb-3 d-block' style='font-size: 40px; opacity: 0.5;'></i>
                                            Belum ada ulasan dari pengunjung.
                                        </td>
                                      </tr>";
                            }
                        } catch (Exception $e) {
                            log_error("Gagal memuat ulasan: " . $e->getMessage());
                            echo "<tr><td colspan='6' class='text-center p-4 text-danger'>Terjadi kesalahan query: " . esc($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>