<?php
// modules/auth/login.php
$pageTitle = "Masuk Ke Akun";

// Redirect if already logged in
if (is_authenticated()) {
    redirect(BASE_URL . 'index.php');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="max-width: 420px; margin: 40px auto; width: 100%;">
    <div class="card" style="padding: 32px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <i class="fa-solid fa-anchor" style="font-size: 40px; color: var(--primary); margin-bottom: 12px;"></i>
            <h2 style="font-weight: 700; color: var(--primary);">Selamat Datang</h2>
            <p style="color: var(--text-secondary); font-size: 14px;">Silakan masuk ke platform MBT Anda</p>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= esc($_SESSION['error_message']) ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= esc($_SESSION['success_message']) ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>index.php?module=auth&action=process_login" method="POST">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="username" class="form-label">Username atau Email</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username..." required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password..." required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600; margin-top: 10px;">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 24px; font-size: 14px; color: var(--text-secondary);">
            Belum punya akun? <a href="<?= BASE_URL ?>index.php?module=auth&action=register" style="font-weight: 600; color: var(--primary);">Daftar Sekarang</a>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
