<?php
// modules/errors/500.php
$pageTitle = "Kesalahan Server";
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="error-container">
    <div class="error-code" style="color: var(--warning);">500</div>
    <h1 class="error-title">Terjadi Kesalahan Internal</h1>
    <p class="error-desc">Maaf, terjadi kesalahan teknis pada server kami saat memproses permintaan Anda. Tim kami telah mencatat masalah ini.</p>
    <a href="<?= BASE_URL ?>index.php" class="btn btn-primary">
        <i class="fa-solid fa-house"></i> Kembali ke Beranda
    </a>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
