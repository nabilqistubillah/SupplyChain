<?php
// modules/errors/403.php
$pageTitle = "Akses Ditolak";
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="error-container">
    <div class="error-code" style="color: var(--danger);">403</div>
    <h1 class="error-title">Akses Ditolak</h1>
    <p class="error-desc">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Halaman ini memerlukan hak akses khusus.</p>
    <a href="<?= BASE_URL ?>index.php" class="btn btn-primary">
        <i class="fa-solid fa-house"></i> Kembali ke Beranda
    </a>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
