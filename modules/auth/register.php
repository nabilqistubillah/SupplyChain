<?php
// modules/auth/register.php
$pageTitle = "Daftar Akun Baru";

if (is_authenticated()) {
    redirect(BASE_URL . 'index.php');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="max-width: 460px; margin: 40px auto; width: 100%;">
    <div class="card" style="padding: 32px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <i class="fa-solid fa-user-plus" style="font-size: 40px; color: var(--primary); margin-bottom: 12px;"></i>
            <h2 style="font-weight: 700; color: var(--primary);">Registrasi Akun</h2>
            <p style="color: var(--text-secondary); font-size: 14px;">Daftar untuk bergabung ke ekosistem MBT</p>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= esc($_SESSION['error_message']) ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>index.php?module=auth&action=process_register" method="POST">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username baru..." required autofocus>
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan email aktif..." required>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Buat password minimal 6 karakter..." required>
            </div>
            
            <div class="form-group">
                <label for="role" class="form-label">Daftar Sebagai</label>
                <select id="role" name="role" class="form-control" required style="cursor: pointer;">
                    <option value="wisatawan" selected>Wisatawan / Pengunjung</option>
                    <option value="buyer">Pihak Hotel / Restoran (Buyer B2B)</option>
                    <option value="vendor">Vendor Lokal / UMKM (Seller)</option>
                </select>
                <small style="color: var(--text-secondary); display: block; margin-top: 6px; font-size: 11px;">
                    * Akun Vendor memerlukan data profil usaha tambahan setelah registrasi.
                </small>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600; margin-top: 10px;">
                <i class="fa-solid fa-user-check"></i> Daftar Akun
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 24px; font-size: 14px; color: var(--text-secondary);">
            Sudah memiliki akun? <a href="<?= BASE_URL ?>index.php?module=auth&action=login" style="font-weight: 600; color: var(--primary);">Masuk</a>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
