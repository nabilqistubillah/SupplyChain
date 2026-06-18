<nav class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-anchor"></i></div>
        <div>
            <div class="brand-text">BahariChain</div>
            <div class="brand-sub">Portal Wisatawan</div>
        </div>
    </div>

    <!-- User info -->
    <div class="sidebar-user">
        <div class="sidebar-avatar">
            <?= strtoupper(substr($_SESSION['username'] ?? 'W', 0, 1)) ?>
        </div>
        <div class="sidebar-user-info">
            <div class="user-name"><?= esc($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Wisatawan') ?></div>
            <div class="user-role"><i class="fa-solid fa-person-swimming" style="font-size:9px;margin-right:3px;"></i>Wisatawan</div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-nav">
        <div class="sidebar-section-label">Menu Utama</div>
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=dashboard">
            <i class="fa-solid fa-house nav-icon"></i> Beranda
        </a>

        <div class="sidebar-section-label">Eksplorasi</div>
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=destinations">
            <i class="fa-solid fa-umbrella-beach nav-icon"></i> Jelajahi Destinasi
        </a>
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=packages">
            <i class="fa-solid fa-box nav-icon"></i> Paket Wisata
        </a>

        <div class="sidebar-section-label">Aktivitas Saya</div>
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reservations">
            <i class="fa-solid fa-cart-shopping nav-icon"></i> Pesanan & Tiket Saya
        </a>
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=reviews">
            <i class="fa-solid fa-star nav-icon"></i> Ulasan & Rating
        </a>

        <div class="sidebar-section-label">Akun</div>
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=profile">
            <i class="fa-solid fa-user nav-icon"></i> Profil Saya
        </a>
        <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=notifications">
            <i class="fa-regular fa-bell nav-icon"></i> Notifikasi
        </a>
    </div>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>index.php?module=auth&action=logout">
            <i class="fa-solid fa-right-from-bracket nav-icon"></i> Keluar
        </a>
    </div>

</nav>
