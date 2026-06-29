<?php
// modules/wisatawan/notifications.php
// BahariChain: Pusat Notifikasi Wisatawan

require_role(['wisatawan']);

// ==============================================================================
// GET HANDLING: Tandai Semua Dibaca
// ==============================================================================
if (isset($_GET['mark_read']) && $_GET['mark_read'] === 'all') {
    try {
        db_query("UPDATE notifikasi SET is_read = 1 WHERE user_id = ?", [$_SESSION['user_id']]);
        $_SESSION['success_message'] = "Semua notifikasi ditandai sebagai dibaca.";
    } catch (PDOException $e) {
        log_error("Failed to mark all notifications read: " . $e->getMessage());
        $_SESSION['error_message'] = "Terjadi kesalahan saat memperbarui status notifikasi.";
    }
    redirect(BASE_URL . 'index.php?module=wisatawan&action=notifications');
}

// ==============================================================================
// GET HANDLING: Hapus Notifikasi Individual
// ==============================================================================
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        db_query("DELETE FROM notifikasi WHERE id = ? AND user_id = ?", [$deleteId, $_SESSION['user_id']]);
        $_SESSION['success_message'] = "Notifikasi berhasil dihapus.";
    } catch (PDOException $e) {
        log_error("Failed to delete notification: " . $e->getMessage());
        $_SESSION['error_message'] = "Terjadi kesalahan saat menghapus notifikasi.";
    }
    redirect(BASE_URL . 'index.php?module=wisatawan&action=notifications');
}

// ==============================================================================
// GET DISPLAY: Tampilan Pusat Notifikasi
// ==============================================================================
try {
    // Muat semua notifikasi milik user
    $notifications = db_query("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC", [$_SESSION['user_id']])->fetchAll();
    
    // Hitung jumlah notifikasi belum dibaca
    $unreadCount = db_query("SELECT COUNT(*) as total FROM notifikasi WHERE user_id = ? AND is_read = 0", [$_SESSION['user_id']])->fetch()['total'];

} catch (PDOException $e) {
    log_error("Wisatawan load notifications error: " . $e->getMessage());
    $notifications = [];
    $unreadCount = 0;
}

$pageTitle = "Notifikasi Saya";
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Navigation Breadcrumbs -->
<div style="margin-bottom: 20px;">
    <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=dashboard" style="color: var(--text-secondary); font-size: 14px;">
        <i class="fa-solid fa-house"></i> Dashboard
    </a>
    <span style="color: var(--text-secondary); margin: 0 8px; font-size: 12px;">/</span>
    <span style="color: var(--primary); font-size: 14px; font-weight: 600;">Notifikasi</span>
</div>

<!-- Header Section -->
<div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-weight: 700; color: var(--primary); margin-bottom: 8px;">Pemberitahuan & Notifikasi</h1>
        <p style="color: var(--text-secondary);">Pantau informasi pemesanan, verifikasi pembayaran, dan pembaruan sistem secara langsung.</p>
    </div>
    
    <?php if ($unreadCount > 0): ?>
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=notifications&mark_read=all" class="btn btn-secondary" style="padding: 10px 18px; font-size: 13px;">
            <i class="fa-solid fa-check-double"></i> Tandai Semua Dibaca
        </a>
    <?php endif; ?>
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

<!-- Notifications List -->
<div class="card" style="padding: 0; overflow: hidden; margin-bottom: 40px;">
    <?php if (empty($notifications)): ?>
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fa-solid fa-bell-slash" style="font-size: 54px; color: var(--text-secondary); opacity: 0.4; margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-primary); font-weight: 600;">Kotak Masuk Kosong</h3>
            <p style="color: var(--text-secondary); margin-top: 8px;">Saat ini Anda tidak memiliki pemberitahuan atau pesan masuk.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column;">
            <?php foreach ($notifications as $n): 
                // Determine icon based on notification type
                $nIcon = 'fa-info-circle';
                $iconBg = 'rgba(14, 165, 233, 0.1)';
                $iconColor = 'var(--accent)';
                
                if ($n['tipe'] === 'transaksi') {
                    $nIcon = 'fa-receipt';
                    $iconBg = 'rgba(16, 185, 129, 0.1)';
                    $iconColor = 'var(--success)';
                } elseif ($n['tipe'] === 'alert') {
                    $nIcon = 'fa-triangle-exclamation';
                    $iconBg = 'rgba(239, 68, 68, 0.1)';
                    $iconColor = 'var(--danger)';
                }

                // Highlight unread notifications
                $itemBg = $n['is_read'] ? 'transparent' : 'rgba(15, 76, 129, 0.03)';
                $borderLeft = $n['is_read'] ? '4px solid transparent' : '4px solid var(--primary)';
                $titleWeight = $n['is_read'] ? '600' : '700';
            ?>
                <div style="display: flex; align-items: start; gap: 16px; padding: 20px 24px; background-color: <?= $itemBg ?>; border-left: <?= $borderLeft ?>; border-bottom: 1px solid var(--border); transition: var(--transition);">
                    <!-- Icon -->
                    <div style="width: 40px; height: 40px; background-color: <?= $iconBg ?>; color: <?= $iconColor ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                        <i class="fa-solid <?= $nIcon ?>"></i>
                    </div>

                    <!-- Message Body -->
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 4px;">
                            <h4 style="font-size: 14px; font-weight: <?= $titleWeight ?>; color: var(--text-primary); margin: 0;"><?= esc($n['judul']) ?></h4>
                            <span style="font-size: 11px; color: var(--text-secondary); white-space: nowrap;"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></span>
                        </div>
                        <p style="color: var(--text-secondary); font-size: 13px; line-height: 1.6; margin: 0; word-break: break-word;"><?= esc($n['pesan']) ?></p>
                    </div>

                    <!-- Actions -->
                    <div style="flex-shrink: 0; margin-left: 10px;">
                        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=notifications&delete_id=<?= $n['id'] ?>" title="Hapus Notifikasi" style="color: var(--text-secondary); opacity: 0.6; font-size: 13px;" onmouseover="this.style.color='var(--danger)'; this.style.opacity='1'" onmouseout="this.style.color='var(--text-secondary)'; this.style.opacity='0.6'">
                            <i class="fa-solid fa-trash-can"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
