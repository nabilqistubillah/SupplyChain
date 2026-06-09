<nav class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-anchor"></i></div>
        <div>
            <div class="brand-text">BahariChain</div>
            <div class="brand-sub">Administrator Portal</div>
        </div>
    </div>

    <!-- User info -->
    <div class="sidebar-user">
        <div class="sidebar-avatar">
            <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
        </div>
        <div class="sidebar-user-info">
            <div class="user-name"><?= esc($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin') ?></div>
            <div class="user-role"><i class="fa-solid fa-shield-halved" style="font-size:9px;margin-right:3px;"></i>Administrator</div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-nav">
        <div class="sidebar-section-label">Utama</div>
        <a href="<?= BASE_URL ?>index.php?module=admin&action=dashboard">
            <i class="fa-solid fa-gauge-high nav-icon"></i> Dashboard
        </a>

        <div class="sidebar-section-label">Manajemen Pengguna</div>
        <a href="<?= BASE_URL ?>index.php?module=admin&action=users">
            <i class="fa-solid fa-users nav-icon"></i> Manajemen Pengguna
        </a>

        <div class="sidebar-section-label">Konten Wisata</div>
        <a href="<?= BASE_URL ?>index.php?module=admin&action=destinations">
            <i class="fa-solid fa-map-location-dot nav-icon"></i> Manajemen Destinasi
        </a>
        <a href="<?= BASE_URL ?>index.php?module=admin&action=packages">
            <i class="fa-solid fa-suitcase-rolling nav-icon"></i> Manajemen Paket Wisata
        </a>

        <div class="sidebar-section-label">Transaksi</div>
        <a href="<?= BASE_URL ?>index.php?module=admin&action=payments">
            <i class="fa-solid fa-money-check-dollar nav-icon"></i> Verifikasi Pembayaran
        </a>

        <div class="sidebar-section-label">Komunitas</div>
        <a href="<?= BASE_URL ?>index.php?module=admin&action=reviews">
            <i class="fa-solid fa-star nav-icon"></i> Manajemen Ulasan
        </a>

        <div class="sidebar-section-label">Analitik</div>
        <a href="<?= BASE_URL ?>index.php?module=admin&action=reports">
            <i class="fa-solid fa-chart-line nav-icon"></i> Laporan & Analitik
        </a>
    </div>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>index.php?module=auth&action=logout">
            <i class="fa-solid fa-right-from-bracket nav-icon"></i> Keluar
        </a>
    </div>

</nav>
