<?php
// modules/wisatawan/profile.php
// BahariChain: Pengelolaan Profil Wisatawan

require_role(['wisatawan']);

// ==============================================================================
// POST HANDLING: Proses Update Profil
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    // Validasi CSRF Token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Token keamanan tidak valid. Silakan coba lagi.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=profile');
    }

    $email = trim($_POST['email'] ?? '');
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $telepon = trim($_POST['no_telepon'] ?? '');
    $passBaru = $_POST['password_baru'] ?? '';
    $passKonf = $_POST['konfirmasi_password'] ?? '';

    // Validasi input wajib
    if (empty($email) || empty($nama)) {
        $_SESSION['error_message'] = "Nama lengkap dan email wajib diisi.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=profile');
    }

    try {
        // Validasi keunikan email (abaikan email milik user saat ini)
        $checkEmail = db_query("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $_SESSION['user_id']])->fetch();
        if ($checkEmail) {
            $_SESSION['error_message'] = "Alamat email sudah digunakan oleh akun lain.";
            redirect(BASE_URL . 'index.php?module=wisatawan&action=profile');
        }

        // Jika user berniat mengganti password
        if (!empty($passBaru)) {
            if ($passBaru !== $passKonf) {
                $_SESSION['error_message'] = "Konfirmasi password baru tidak cocok.";
                redirect(BASE_URL . 'index.php?module=wisatawan&action=profile');
            }
            if (strlen($passBaru) < 6) {
                $_SESSION['error_message'] = "Password baru minimal terdiri atas 6 karakter.";
                redirect(BASE_URL . 'index.php?module=wisatawan&action=profile');
            }

            // Hash password menggunakan bcrypt
            $hashedPass = password_hash($passBaru, PASSWORD_BCRYPT);
            
            db_query("
                UPDATE users 
                SET email = ?, nama_lengkap = ?, no_telepon = ?, password = ?
                WHERE id = ?
            ", [$email, $nama, $telepon, $hashedPass, $_SESSION['user_id']]);
        } else {
            // Update tanpa mengganti password
            db_query("
                UPDATE users 
                SET email = ?, nama_lengkap = ?, no_telepon = ?
                WHERE id = ?
            ", [$email, $nama, $telepon, $_SESSION['user_id']]);
        }

        // Update data session aktif
        $_SESSION['full_name'] = $nama;
        $_SESSION['email'] = $email;

        $_SESSION['success_message'] = "Informasi profil Anda berhasil diperbarui.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=profile');

    } catch (PDOException $e) {
        log_error("Wisatawan profile update database error: " . $e->getMessage());
        $_SESSION['error_message'] = "Terjadi kesalahan internal saat memperbarui profil.";
        redirect(BASE_URL . 'index.php?module=wisatawan&action=profile');
    }
}

// ==============================================================================
// GET DISPLAY: Tampilan Pengelolaan Profil
// ==============================================================================
try {
    $user = db_query("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']])->fetch();
    if (!$user) {
        redirect(BASE_URL . 'index.php?module=auth&action=logout');
    }
} catch (PDOException $e) {
    log_error("Wisatawan load profile database error: " . $e->getMessage());
    die("Gagal memuat data profil.");
}

$pageTitle = "Profil Saya";
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Navigation Breadcrumbs -->
<div style="margin-bottom: 20px;">
    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=dashboard" style="color: var(--text-secondary); font-size: 14px;">
        <i class="fa-solid fa-house"></i> Dashboard
    </a>
    <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
    <span style="color: var(--primary); font-size: 14px; font-weight: 600;">Profil Saya</span>
</div>

<!-- Header Section -->
<div style="margin-bottom: 30px;">
    <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 8px;">Kelola Akun & Profil</h1>
    <p style="color: var(--text-secondary);">Perbarui informasi kontak pribadi dan amankan kata sandi akun Anda.</p>
</div>

<!-- Alerts Notification -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> <?= esc($_SESSION['success_message']) ?>
        <?php unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i> <?= esc($_SESSION['error_message']) ?>
        <?php unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<div class="grid grid-3" style="align-items: start; gap: 30px;">
    <!-- Left Column: User Summary Card -->
    <div class="card" style="text-align: center; padding: 30px 24px;">
        <div style="width: 90px; height: 90px; background: linear-gradient(135deg, var(--primary), var(--accent)); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 700; margin: 0 auto 20px auto; box-shadow: var(--shadow-md);">
            <?= strtoupper(substr($user['username'], 0, 1)) ?>
        </div>
        <h2 style="font-size: 18px; font-weight: 700; color: var(--primary); margin: 0 0 4px 0;"><?= esc($user['nama_lengkap'] ?: $user['username']) ?></h2>
        <span style="font-size: 11px; background-color: var(--background); color: var(--text-secondary); padding: 4px 10px; border-radius: 20px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
            <i class="fa-solid fa-umbrella-beach"></i> <?= esc($_SESSION['role']) ?>
        </span>
        
        <div style="border-top: 1px solid var(--border); margin-top: 24px; padding-top: 20px; text-align: left; font-size: 13px; display:flex; flex-direction:column; gap:10px;">
            <div style="display:flex; justify-content:space-between;">
                <span style="color: var(--text-secondary);">Username</span>
                <span style="font-weight: 600; color: var(--text-primary);"><?= esc($user['username']) ?></span>
            </div>
            <div style="display:flex; justify-content:space-between;">
                <span style="color: var(--text-secondary);">Tanggal Bergabung</span>
                <span style="font-weight: 600; color: var(--text-primary);"><?= date('d M Y', strtotime($user['created_at'])) ?></span>
            </div>
        </div>
    </div>

    <!-- Right Column: Edit Profile Form (Span 2) -->
    <div class="card" style="grid-column: span 2; padding: 30px;">
        <h3 style="color: var(--primary); font-weight: 700; font-size: 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid var(--border); padding-bottom: 8px;">
            <i class="fa-solid fa-user-gear" style="color: var(--accent);"></i> Informasi Detail Profil
        </h3>

        <form action="<?= BASE_URL ?>index.php?module=wisatawan&action=profile" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div class="grid grid-2" style="gap: 20px; margin-bottom: 20px;">
                <!-- Full name -->
                <div class="form-group" style="margin: 0;">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" placeholder="Ketik nama lengkap Anda..." value="<?= esc($user['nama_lengkap']) ?>" required>
                </div>

                <!-- Email -->
                <div class="form-group" style="margin: 0;">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="nama@domain.com" value="<?= esc($user['email']) ?>" required>
                </div>

                <!-- Phone number -->
                <div class="form-group" style="margin: 0;">
                    <label for="no_telepon" class="form-label">Nomor Telepon (WhatsApp)</label>
                    <input type="text" id="no_telepon" name="no_telepon" class="form-control" placeholder="Contoh: 08123456789" value="<?= esc($user['no_telepon']) ?>">
                </div>

                <!-- Username (Readonly) -->
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Username Akun</label>
                    <input type="text" class="form-control" value="<?= esc($user['username']) ?>" style="background-color: var(--background); color: var(--text-secondary);" disabled readonly>
                </div>
            </div>

            <!-- Password Change Section -->
            <h3 style="color: var(--primary); font-weight: 700; font-size: 16px; margin-top: 36px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 2px solid var(--border); padding-bottom: 8px;">
                <i class="fa-solid fa-key" style="color: var(--accent);"></i> Ganti Kata Sandi (Opsional)
            </h3>
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 20px;">*Kosongkan kolom sandi di bawah jika Anda tidak ingin mengubah kata sandi saat ini.</p>

            <div class="grid grid-2" style="gap: 20px; margin-bottom: 30px;">
                <!-- New Password -->
                <div class="form-group" style="margin: 0;">
                    <label for="password_baru" class="form-label">Kata Sandi Baru</label>
                    <input type="password" id="password_baru" name="password_baru" class="form-control" placeholder="Kata sandi baru (min 6 karakter)...">
                </div>

                <!-- Confirm Password -->
                <div class="form-group" style="margin: 0;">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="form-control" placeholder="Ketik ulang kata sandi baru...">
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Profil
            </button>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
