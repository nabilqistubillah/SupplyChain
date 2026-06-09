<nav class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-anchor"></i></div>
        <div>
            <div class="brand-text">BahariChain</div>
            <div class="brand-sub">Pengelola Wisata</div>
        </div>
    </div>

    <!-- User info -->
    <div class="sidebar-user">
        <div class="sidebar-avatar">
            <?= strtoupper(substr($_SESSION['username'] ?? 'P', 0, 1)) ?>
        </div>
        <div class="sidebar-user-info">
            <div class="user-name"><?= esc($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Pengelola') ?></div>
            <div class="user-role"><i class="fa-solid fa-briefcase" style="font-size:9px;margin-right:3px;"></i>Pengelola Wisata</div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-nav">
        <div class="sidebar-section-label">Utama</div>
        <a href="<?= BASE_URL ?>index.php?module=pengelola&action=dashboard">
            <i class="fa-solid fa-gauge nav-icon"></i> Dashboard
        </a>

        <div class="sidebar-section-label">Konten Saya</div>
        <a href="<?= BASE_URL ?>index.php?module=pengelola&action=destinations">
            <i class="fa-solid fa-map-location-dot nav-icon"></i> Destinasi Saya
        </a>
        <a href="<?= BASE_URL ?>index.php?module=pengelola&action=packages">
            <i class="fa-solid fa-suitcase-rolling nav-icon"></i> Paket Wisata Saya
        </a>

        <div class="sidebar-section-label">Operasional</div>
        <a href="<?= BASE_URL ?>index.php?module=pengelola&action=transportation">
            <i class="fa-solid fa-ship nav-icon"></i> Manajemen Transportasi
        </a>
        <a href="<?= BASE_URL ?>index.php?module=pengelola&action=reservations">
            <i class="fa-solid fa-calendar-check nav-icon"></i> Manajemen Reservasi
        </a>

        <div class="sidebar-section-label">Analitik</div>
        <a href="<?= BASE_URL ?>index.php?module=pengelola&action=reports">
            <i class="fa-solid fa-chart-bar nav-icon"></i> Laporan
        </a>
    </div>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>index.php?module=auth&action=logout">
            <i class="fa-solid fa-right-from-bracket nav-icon"></i> Keluar
        </a>
    </div>

</nav>
