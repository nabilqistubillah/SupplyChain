<?php
// modules/destinations/delete.php

require_role(['admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect(BASE_URL . 'index.php?module=destinations&action=manage');
}

try {
    // Ambil nama destinasi untuk kebutuhan log & pesan sukses
    $stmt = db_query("SELECT name FROM destinations WHERE id = :id", ['id' => $id]);
    $dest = $stmt->fetch();
    
    if ($dest) {
        $name = $dest['name'];
        // Lakukan penghapusan (akan men-cascade rides dan data terkait berkat constraint FOREIGN KEY ON DELETE CASCADE)
        db_query("DELETE FROM destinations WHERE id = :id", ['id' => $id]);
        
        log_info("Admin deleted destination ID {$id}: '{$name}'");
        $_SESSION['success_message'] = "Destinasi '{$name}' berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Destinasi tidak ditemukan.";
    }
} catch (PDOException $e) {
    log_error("Database error while deleting destination ID {$id}: " . $e->getMessage());
    $_SESSION['error_message'] = "Gagal menghapus data destinasi dari database.";
}

redirect(BASE_URL . 'index.php?module=destinations&action=manage');
