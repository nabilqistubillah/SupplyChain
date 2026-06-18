<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? esc($pageTitle) . ' | ' : '' ?><?= APP_NAME ?></title>
    <meta name="description" content="<?= isset($pageDesc) ? esc($pageDesc) : 'BahariChain — Platform Reservasi Wisata Bahari' ?>">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dashboard.css">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dash-shell">

    <!-- ── SIDEBAR (injected by each role's layout) ────────── -->
    <?php require_once $sidebarFile; ?>

    <!-- ── MAIN CONTENT ────────────────────────────────────── -->
    <div class="dash-main">

        <!-- Top bar -->
        <header class="dash-topbar">
            <div class="topbar-left">
                <button id="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <div class="topbar-page-title"><?= isset($pageTitle) ? esc($pageTitle) : 'Dashboard' ?></div>
                    <?php if (isset($breadcrumb)): ?>
                    <div class="topbar-breadcrumb"><?= $breadcrumb ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="topbar-right">
                <?php if ($_SESSION['role'] === ROLE_WISATAWAN): ?>
                <a href="<?= BASE_URL ?>index.php?module=wisatawan&action=notifications"
                   class="topbar-notif-btn" title="Notifikasi">
                    <i class="fa-regular fa-bell"></i>
                    <span class="notif-badge">3</span>
                </a>
                <?php endif; ?>

                <div class="topbar-user" title="<?= esc($_SESSION['full_name'] ?? $_SESSION['username']) ?>">
                    <div class="topbar-avatar">
                        <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)) ?>
                    </div>
                    <span class="topbar-username"><?= esc($_SESSION['username'] ?? '') ?></span>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;color:var(--text-muted);"></i>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <main class="dash-content">
