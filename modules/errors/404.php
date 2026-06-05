<?php
// modules/errors/404.php
$pageTitle = "Halaman Tidak Ditemukan";
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="error-container">
    <div class="error-code">404</div>
    <h1 class="error-title">Halaman Tidak Ditemukan</h1>
    <p class="error-desc">Maaf, halaman yang Anda cari tidak ada, telah dihapus, atau sedang dalam perbaikan.</p>
    <a href="<?= BASE_URL ?>index.php" class="btn btn-primary">
        <i class="fa-solid fa-house"></i> Kembali ke Beranda
    </a>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
