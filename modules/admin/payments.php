<?php
// modules/admin/payments.php
// BahariChain: Verifikasi Pembayaran Admin

// ==============================================================================
// 1. SECURITY CHECK: Hanya admin yang boleh masuk
// ==============================================================================
require_role(['admin']);

$pageTitle = "Verifikasi Pembayaran";

// ==============================================================================
// 2. PROCESS ACTION: Approve atau Reject Pembayaran
// ==============================================================================
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_payment'])) {
    $id_pembayaran = intval($_POST['id_pembayaran']);
    $id_pesanan = intval($_POST['id_pesanan']);
    $action = $_POST['action_payment']; // 'approve' atau 'reject'

    try {
        if ($action === 'approve') {
            // Update status pembayaran jadi 'paid'
            db_query("UPDATE pembayaran SET status = 'paid' WHERE id = :id", ['id' => $id_pembayaran]);
            // Update status pesanan jadi 'paid' agar pengelola & wisatawan tahu transaksinya sukses
            db_query("UPDATE pesanan SET status = 'paid' WHERE id = :id", ['id' => $id_pesanan]);
            
            $message = "Pembayaran untuk Pesanan #$id_pesanan berhasil diverifikasi (Disetujui).";
            $messageType = "success";
        } elseif ($action === 'reject') {
            // Update status pembayaran jadi 'rejected'
            db_query("UPDATE pembayaran SET status = 'rejected' WHERE id = :id", ['id' => $id_pembayaran]);
            // Balikkan status pesanan menjadi 'unpaid' atau 'cancelled' tergantung alur webmu
            db_query("UPDATE pesanan SET status = 'unpaid' WHERE id = :id", ['id' => $id_pesanan]);
            
            $message = "Pembayaran untuk Pesanan #$id_pesanan telah ditolak.";
            $messageType = "danger";
        }
    } catch (PDOException $e) {
        log_error("Admin payment verification error: " . $e->getMessage());
        $message = "Gagal memproses verifikasi: " . $e->getMessage();
        $messageType = "danger";
    }
}

// ==============================================================================
// 3. FETCH PENDING PAYMENTS
// ==============================================================================
// Mengambil data pembayaran yang 'unpaid' beserta informasi pesanan dan nama wisatawan
$pendingPaymentsList = [];
try {
    $query = "SELECT pb.*, ps.total_harga, ps.tanggal_pesan, u.username 
              FROM pembayaran pb
              JOIN pesanan ps ON pb.id_pesanan = ps.id
              JOIN users u ON ps.user_id = u.id
              WHERE pb.status = 'unpaid'
              ORDER BY pb.id ASC";
              
    $pendingPaymentsList = db_query($query)->fetchAll();
} catch (PDOException $e) {
    log_error("Fetch pending payments error: " . $e->getMessage());
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between;">
    <div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--primary); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-check-circle" style="color: #10b981;"></i> Verifikasi Pembayaran
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 4px 0 0 0;">Periksa bukti transfer wisatawan untuk mengaktifkan reservasi</p>
    </div>
    <a href="<?= BASE_URL ?>index.php?module=admin&action=dashboard" class="btn btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #e2e8f0; color: #334155; border-radius: var(--radius); font-weight: 500; font-size: 14px;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
    </a>
</div>

<?php if ($message): ?>
    <div style="padding: 15px 20px; background: <?= $messageType === 'success' ? '#d1fae5' : '#fee2e2' ?>; color: <?= $messageType === 'success' ? '#065f46' : '#991b1b' ?>; border-radius: var(--radius); margin-bottom: 25px; font-size: 14px; font-weight: 500;">
        <i class="fa-solid <?= $messageType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i> <?= esc($message) ?>
    </div>
<?php endif; ?>

<div class="card" style="padding: 24px; overflow-x: auto;">
    <?php if (empty($pendingPaymentsList)): ?>
        <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
            <i class="fa-solid fa-clipboard-check" style="font-size: 48px; opacity: 0.3; margin-bottom: 16px; display: block;"></i>
            <p style="margin: 0; font-size: 16px; font-weight: 500;">Semua pembayaran telah diverifikasi!</p>
            <p style="margin: 4px 0 0 0; font-size: 13px;">Tidak ada antrean pembayaran saat ini.</p>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--primary); font-weight: 600;">
                    <th style="padding: 12px 8px;">ID / Wisatawan</th>
                    <th style="padding: 12px 8px;">Tanggal Pesan</th>
                    <th style="padding: 12px 8px;">Total Tagihan</th>
                    <th style="padding: 12px 8px;">Metode</th>
                    <th style="padding: 12px 8px; text-align: center;">Bukti Transfer</th>
                    <th style="padding: 12px 8px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingPaymentsList as $pay): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 16px 8px;">
                            <strong style="color: var(--primary);">#<?= $pay['id_pesanan'] ?></strong><br>
                            <span style="font-size: 12px; color: var(--text-secondary);"><?= esc($pay['username']) ?></span>
                        </td>
                        <td style="padding: 16px 8px; color: var(--text-secondary);">
                            <?= date('d M Y', strtotime($pay['tanggal_pesan'])) ?>
                        </td>
                        <td style="padding: 16px 8px; font-weight: 600; color: var(--primary);">
                            Rp <?= number_format($pay['total_harga'], 0, ',', '.') ?>
                        </td>
                        <td style="padding: 16px 8px;">
                            <span style="background: #eff6ff; color: #1e40af; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                <?= esc($pay['metode_pembayaran'] ?? 'Transfer') ?>
                            </span>
                        </td>
                        <td style="padding: 16px 8px; text-align: center;">
                            <?php if (!empty($pay['bukti_transfer'])): ?>
                                <a href="<?= BASE_URL ?>assets/uploads/bukti/<?= $pay['bukti_transfer'] ?>" target="_blank" title="Klik untuk memperbesar">
                                    <img src="<?= BASE_URL ?>assets/uploads/bukti/<?= $pay['bukti_transfer'] ?>" alt="Bukti Transfer" style="max-width: 80px; max-height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); cursor: pointer;">
                                </a>
                            <?php else: ?>
                                <span style="font-size: 12px; color: #ef4444; font-style: italic;">Tidak ada bukti</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 16px 8px; text-align: center;">
                            <div style="display: inline-flex; gap: 8px;">
                                <form method="POST" onsubmit="return confirm('Setujui pembayaran ini?');">
                                    <input type="hidden" name="id_pembayaran" value="<?= $pay['id'] ?>">
                                    <input type="hidden" name="id_pesanan" value="<?= $pay['id_pesanan'] ?>">
                                    <button type="submit" name="action_payment" value="approve" style="background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-check"></i> Setuju
                                    </button>
                                </form>
                                
                                <form method="POST" onsubmit="return confirm('Tolak pembayaran ini?');">
                                    <input type="hidden" name="id_pembayaran" value="<?= $pay['id'] ?>">
                                    <input type="hidden" name="id_pesanan" value="<?= $pay['id_pesanan'] ?>">
                                    <button type="submit" name="action_payment" value="reject" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-xmark"></i> Tolak
                                    </button>
                                </form>
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