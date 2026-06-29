<?php
// modules/admin/users.php
// BahariChain: Manajemen Pengguna Administrator Portal

// ==============================================================================
// 1. SECURITY CHECK: Only admin can access this page
// ==============================================================================
require_role(['admin']);

$pageTitle = "Manajemen Pengguna";
$message = '';
$messageType = '';

$db = Database::getConnection();

// ==============================================================================
// 2. PROCESS ACTIONS: Add, Edit, Delete User
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aksi 1: Tambah Pengguna Baru
    if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        // CSRF Token Validation
        if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
            $message = "Token keamanan tidak valid. Silakan coba lagi.";
            $messageType = "danger";
        } else {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
            $no_telepon = trim($_POST['no_telepon'] ?? '');
            $role_id = intval($_POST['role_id'] ?? 0);

            if (empty($username) || empty($email) || empty($password) || empty($role_id)) {
                $message = "Username, Email, Password, dan Peran wajib diisi.";
                $messageType = "danger";
            } elseif (strlen($password) < 6) {
                $message = "Password minimal harus 6 karakter.";
                $messageType = "danger";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Format email tidak valid.";
                $messageType = "danger";
            } else {
                try {
                    // Check duplicate
                    $check = db_query("SELECT COUNT(*) as total FROM users WHERE username = :username OR email = :email", [
                        'username' => $username,
                        'email' => $email
                    ])->fetch();

                    if ($check['total'] > 0) {
                        $message = "Username atau Email sudah terdaftar.";
                        $messageType = "danger";
                    } else {
                        $passHash = password_hash($password, PASSWORD_BCRYPT);
                        db_query("INSERT INTO users (role_id, username, password, email, nama_lengkap, no_telepon, created_at) 
                                  VALUES (:role_id, :username, :password, :email, :nama_lengkap, :no_telepon, NOW())", [
                            'role_id' => $role_id,
                            'username' => $username,
                            'password' => $passHash,
                            'email' => $email,
                            'nama_lengkap' => $nama_lengkap ?: null,
                            'no_telepon' => $no_telepon ?: null
                        ]);
                        log_info("Admin created new user '{$username}' with role_id={$role_id}.");
                        $message = "Pengguna baru berhasil ditambahkan.";
                        $messageType = "success";
                    }
                } catch (PDOException $e) {
                    log_error("Admin add user database error: " . $e->getMessage());
                    $message = "Gagal menambahkan pengguna: " . $e->getMessage();
                    $messageType = "danger";
                }
            }
        }
    }

    // Aksi 2: Edit Pengguna
    if (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
        if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
            $message = "Token keamanan tidak valid. Silakan coba lagi.";
            $messageType = "danger";
        } else {
            $id = intval($_POST['id'] ?? 0);
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
            $no_telepon = trim($_POST['no_telepon'] ?? '');
            $role_id = intval($_POST['role_id'] ?? 0);

            if (empty($email) || empty($role_id)) {
                $message = "Email dan Peran wajib diisi.";
                $messageType = "danger";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Format email tidak valid.";
                $messageType = "danger";
            } else {
                try {
                    // Check duplicate email
                    $check = db_query("SELECT COUNT(*) as total FROM users WHERE email = :email AND id != :id", [
                        'email' => $email,
                        'id' => $id
                    ])->fetch();

                    if ($check['total'] > 0) {
                        $message = "Email sudah digunakan oleh pengguna lain.";
                        $messageType = "danger";
                    } else {
                        if (!empty($password)) {
                            if (strlen($password) < 6) {
                                $message = "Password minimal harus 6 karakter.";
                                $messageType = "danger";
                            } else {
                                $passHash = password_hash($password, PASSWORD_BCRYPT);
                                db_query("UPDATE users SET role_id = :role_id, password = :password, email = :email, nama_lengkap = :nama_lengkap, no_telepon = :no_telepon WHERE id = :id", [
                                    'role_id' => $role_id,
                                    'password' => $passHash,
                                    'email' => $email,
                                    'nama_lengkap' => $nama_lengkap ?: null,
                                    'no_telepon' => $no_telepon ?: null,
                                    'id' => $id
                                ]);
                            }
                        } else {
                            db_query("UPDATE users SET role_id = :role_id, email = :email, nama_lengkap = :nama_lengkap, no_telepon = :no_telepon WHERE id = :id", [
                                'role_id' => $role_id,
                                'email' => $email,
                                'nama_lengkap' => $nama_lengkap ?: null,
                                'no_telepon' => $no_telepon ?: null,
                                'id' => $id
                            ]);
                        }
                        
                        if ($messageType !== 'danger') {
                            log_info("Admin updated user ID={$id}.");
                            $message = "Data pengguna berhasil diperbarui.";
                            $messageType = "success";
                        }
                    }
                } catch (PDOException $e) {
                    log_error("Admin edit user database error: " . $e->getMessage());
                    $message = "Gagal memperbarui pengguna: " . $e->getMessage();
                    $messageType = "danger";
                }
            }
        }
    }
}

// Aksi 3: Hapus Pengguna (via GET parameter dengan konfirmasi)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Cegah admin menghapus dirinya sendiri
    if ($delete_id === intval($_SESSION['user_id'])) {
        $message = "Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.";
        $messageType = "danger";
    } else {
        try {
            db_query("DELETE FROM users WHERE id = ?", [$delete_id]);
            log_info("Admin deleted user ID={$delete_id}.");
            $message = "Pengguna berhasil dihapus dari sistem.";
            $messageType = "success";
        } catch (PDOException $e) {
            log_error("Admin delete user database error: " . $e->getMessage());
            $message = "Gagal menghapus pengguna. Data kemungkinan terkait dengan transaksi lain.";
            $messageType = "danger";
        }
    }
}

// ==============================================================================
// 3. FETCH DATA & FILTERS
// ==============================================================================
$search = trim($_GET['search'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');

$params = [];
$whereClause = "WHERE 1=1";

if (!empty($search)) {
    $whereClause .= " AND (u.username LIKE :search OR u.email LIKE :search OR u.nama_lengkap LIKE :search)";
    $params['search'] = "%{$search}%";
}

if (!empty($roleFilter)) {
    $whereClause .= " AND r.nama_role = :role";
    $params['role'] = $roleFilter;
}

try {
    // Fetch users
    $usersQuery = "SELECT u.id, u.username, u.email, u.nama_lengkap, u.no_telepon, u.created_at, r.nama_role, r.id as role_id 
                   FROM users u
                   JOIN roles r ON u.role_id = r.id
                   {$whereClause}
                   ORDER BY u.id DESC";
    $usersList = db_query($usersQuery, $params)->fetchAll();

    // Fetch all roles for forms
    $rolesList = db_query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
} catch (PDOException $e) {
    log_error("Fetch users list error: " . $e->getMessage());
    $usersList = [];
    $rolesList = [];
}

// Fetch single user detail if editing
$editUser = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    foreach ($usersList as $user) {
        if ($user['id'] === $editId) {
            $editUser = $user;
            break;
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Halaman Layout -->
<div style="margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-users" style="color: var(--primary-light);"></i> Manajemen Pengguna
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 4px 0 0 0;">Kelola hak akses portal, akun pengelola wisata, dan wisatawan BahariChain</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= BASE_URL ?>index.php?module=admin&action=users&add=1" class="btn btn-primary" style="font-weight: 600;">
            <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
        </a>
        <a href="<?= BASE_URL ?>index.php?module=admin&action=dashboard" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<?php if ($message): ?>
    <div style="padding: 15px 20px; background: <?= $messageType === 'success' ? '#d1fae5' : '#fee2e2' ?>; color: <?= $messageType === 'success' ? '#065f46' : '#991b1b' ?>; border-radius: var(--radius); margin-bottom: 25px; font-size: 14px; font-weight: 500;">
        <i class="fa-solid <?= $messageType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i> <?= esc($message) ?>
    </div>
<?php endif; ?>

<!-- ==============================================================================
     FORM SECTION (ADD OR EDIT)
     ============================================================================== -->
<?php if (isset($_GET['add']) || $editUser): ?>
    <div class="card" style="padding: 30px; margin-bottom: 30px; border-top: 4px solid var(--primary-light); box-shadow: var(--shadow-md);">
        <h2 style="color: var(--primary); font-size: 20px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid <?= $editUser ? 'fa-user-pen' : 'fa-user-plus' ?>"></i> 
            <?= $editUser ? 'Edit Detail Pengguna' : 'Tambah Pengguna Baru' ?>
        </h2>
        
        <form method="POST" action="<?= BASE_URL ?>index.php?module=admin&action=users">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="<?= $editUser ? 'edit_user' : 'add_user' ?>">
            <?php if ($editUser): ?>
                <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
            <?php endif; ?>

            <div class="grid grid-2" style="gap: 20px; margin-bottom: 20px;">
                <!-- Kolom Kiri -->
                <div>
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px;"
                               value="<?= esc($editUser['username'] ?? '') ?>" <?= $editUser ? 'disabled style="background:#f1f5f9; cursor:not-allowed;"' : 'required' ?>>
                        <?php if ($editUser): ?>
                            <small style="color: var(--text-secondary); font-size: 11px;">Username tidak dapat diubah.</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px;"
                               value="<?= esc($editUser['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px;"
                               <?= $editUser ? 'placeholder="Kosongkan jika tidak ingin mengubah password"' : 'required placeholder="Minimal 6 karakter"' ?>>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div>
                    <div class="form-group">
                        <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" 
                               style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px;"
                               value="<?= esc($editUser['nama_lengkap'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="no_telepon">Nomor Telepon</label>
                        <input type="text" id="no_telepon" name="no_telepon" class="form-control" 
                               style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px;"
                               value="<?= esc($editUser['no_telepon'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="role_id">Peran / Role</label>
                        <select id="role_id" name="role_id" class="form-control" 
                                style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; background: white;" required>
                            <option value="">-- Pilih Peran --</option>
                            <?php foreach ($rolesList as $role): ?>
                                <option value="<?= $role['id'] ?>" <?= (isset($editUser) && $editUser['role_id'] == $role['id']) ? 'selected' : '' ?>>
                                    <?= esc(ucwords(str_replace('_', ' ', $role['nama_role']))) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <a href="<?= BASE_URL ?>index.php?module=admin&action=users" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" style="background-color: var(--primary); font-weight: 600;">
                    <i class="fa-solid fa-save"></i> <?= $editUser ? 'Simpan Perubahan' : 'Simpan Pengguna' ?>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- ==============================================================================
     FILTER & SEARCH SECTION
     ============================================================================== -->
<div class="card" style="padding: 20px; margin-bottom: 25px; box-shadow: var(--shadow-sm);">
    <form method="GET" action="<?= BASE_URL ?>index.php" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <input type="hidden" name="module" value="admin">
        <input type="hidden" name="action" value="users">
        
        <div style="flex: 1; min-width: 250px;">
            <label class="form-label" for="search" style="font-size:13px;">Cari Pengguna</label>
            <div style="position: relative;">
                <input type="text" id="search" name="search" class="form-control" 
                       placeholder="Cari berdasarkan nama, email, username..." 
                       value="<?= esc($search) ?>"
                       style="width: 100%; padding: 10px 12px 10px 36px; border: 1px solid var(--border); border-radius: 8px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
            </div>
        </div>
        
        <div style="width: 200px;">
            <label class="form-label" for="role" style="font-size:13px;">Filter Peran</label>
            <select id="role" name="role" class="form-control" 
                    style="width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; background: white;">
                <option value="">Semua Peran</option>
                <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="pengelola_wisata" <?= $roleFilter === 'pengelola_wisata' ? 'selected' : '' ?>>Pengelola Wisata</option>
                <option value="wisatawan" <?= $roleFilter === 'wisatawan' ? 'selected' : '' ?>>Wisatawan</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary" style="background-color: var(--primary); padding: 10px 18px;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            <?php if (!empty($search) || !empty($roleFilter)): ?>
                <a href="<?= BASE_URL ?>index.php?module=admin&action=users" class="btn btn-secondary" style="padding: 10px 18px;">
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- ==============================================================================
     USERS LIST TABLE
     ============================================================================== -->
<div class="card" style="padding: 24px; overflow-x: auto; box-shadow: var(--shadow-sm);">
    <?php if (empty($usersList)): ?>
        <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
            <i class="fa-solid fa-users-slash" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px; display: block;"></i>
            <p style="margin: 0; font-size: 16px; font-weight: 500;">Tidak ada pengguna ditemukan!</p>
            <p style="margin: 4px 0 0 0; font-size: 13px;">Silakan sesuaikan kriteria pencarian atau tambahkan baru.</p>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--primary); font-weight: 600;">
                    <th style="padding: 12px 8px; width: 60px;">No</th>
                    <th style="padding: 12px 8px;">Username</th>
                    <th style="padding: 12px 8px;">Kontak & Detail</th>
                    <th style="padding: 12px 8px;">Peran</th>
                    <th style="padding: 12px 8px;">Terdaftar Pada</th>
                    <th style="padding: 12px 8px; text-align: center; width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($usersList as $user): 
                    $roleBadgeBg = '#f1f5f9';
                    $roleBadgeColor = '#475569';
                    if ($user['nama_role'] === 'admin') {
                        $roleBadgeBg = '#eff6ff';
                        $roleBadgeColor = '#1e40af';
                    } elseif ($user['nama_role'] === 'pengelola_wisata') {
                        $roleBadgeBg = '#ecfdf5';
                        $roleBadgeColor = '#065f46';
                    } elseif ($user['nama_role'] === 'wisatawan') {
                        $roleBadgeBg = '#fff7ed';
                        $roleBadgeColor = '#c2410c';
                    }
                ?>
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;">
                        <td style="padding: 16px 8px; color: var(--text-secondary);"><?= $no++ ?></td>
                        <td style="padding: 16px 8px;">
                            <strong style="color: var(--primary);"><?= esc($user['username']) ?></strong>
                        </td>
                        <td style="padding: 16px 8px;">
                            <div style="font-weight: 600; color: var(--text-primary);"><?= esc($user['nama_lengkap'] ?? '-') ?></div>
                            <div style="font-size: 12px; color: var(--text-secondary); display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                                <i class="fa-regular fa-envelope"></i> <?= esc($user['email']) ?>
                            </div>
                            <div style="font-size: 12px; color: var(--text-secondary); display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                                <i class="fa-solid fa-phone" style="font-size: 10px;"></i> <?= esc($user['no_telepon'] ?? '-') ?>
                            </div>
                        </td>
                        <td style="padding: 16px 8px;">
                            <span style="background: <?= $roleBadgeBg ?>; color: <?= $roleBadgeColor ?>; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px;">
                                <?= esc(str_replace('_', ' ', $user['nama_role'])) ?>
                            </span>
                        </td>
                        <td style="padding: 16px 8px; color: var(--text-secondary);">
                            <?= date('d M Y, H:i', strtotime($user['created_at'])) ?>
                        </td>
                        <td style="padding: 16px 8px; text-align: center;">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="<?= BASE_URL ?>index.php?module=admin&action=users&edit_id=<?= $user['id'] ?>" 
                                   class="btn btn-secondary" 
                                   style="padding: 6px 10px; font-size: 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; color: var(--warning); border-color: #fde047;" 
                                   title="Edit Pengguna">
                                    <i class="fa-solid fa-user-gear"></i> Edit
                                </a>
                                
                                <a href="<?= BASE_URL ?>index.php?module=admin&action=users&delete_id=<?= $user['id'] ?>" 
                                   class="btn btn-secondary" 
                                   style="padding: 6px 10px; font-size: 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; color: var(--danger); border-color: #fca5a5;" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna <?= esc($user['username']) ?>? Semua data terkait (pesanan, armada, ulasan) juga akan ikut terhapus!');" 
                                   title="Hapus Pengguna">
                                    <i class="fa-solid fa-trash-can"></i> Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
