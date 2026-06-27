<?php
// includes/navbar.php
?>
<header style="background-color: var(--primary); color: #ffffff; box-shadow: var(--shadow-md);">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; height: 70px;">
        <!-- Logo -->
        <a href="<?= BASE_URL ?>index.php" style="color: #ffffff; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-anchor" style="color: var(--accent);"></i>
            <span>BahariChain<span style="color: var(--accent); font-weight: 300;">Madura</span></span>
        </a>

        <!-- Main Nav -->
        <nav style="display: flex; align-items: center; gap: 24px;">
            <a href="<?= BASE_URL ?>index.php" style="color: #ffffff; font-weight: 500;">
                <i class="fa-solid fa-house" style="margin-right: 5px;"></i>Beranda
            </a>
            <a href="<?= BASE_URL ?>index.php?module=destinations" style="color: #ffffff; font-weight: 500;">
                <i class="fa-solid fa-map-location-dot" style="margin-right: 5px;"></i>Destinasi
            </a>
        </nav>

        <!-- User Actions -->
        <div style="display: flex; align-items: center; gap: 16px;">
            <?php if (is_authenticated()): ?>
                <div style="font-size: 14px; text-align: right; display: flex; flex-direction: column; justify-content: center;">
                    <span style="font-weight: 600; color: #ffffff;">Hi, <?= esc($_SESSION['username']) ?></span>
                    <span style="font-size: 11px; background: rgba(255, 255, 255, 0.2); padding: 2px 6px; border-radius: 4px; display: inline-block; align-self: flex-end; text-transform: uppercase;">
                        <?= esc($_SESSION['role']) ?>
                    </span>
                </div>
                
                <!-- Quick Menu by Role -->
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>index.php?module=destinations&action=manage" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; color: var(--primary);">
                        <i class="fa-solid fa-gears"></i> Kelola Wisata
                    </a>
                <?php elseif ($_SESSION['role'] === 'vendor'): ?>
                    <a href="<?= BASE_URL ?>index.php?module=vendors&action=products" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; color: var(--primary);">
                        <i class="fa-solid fa-box"></i> Katalog UMKM
                    </a>
                <?php elseif ($_SESSION['role'] === 'buyer'): ?>
                    <a href="<?= BASE_URL ?>index.php?module=orders&action=index" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; color: var(--primary); display: flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-cart-shopping"></i> Keranjang
                    </a>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>index.php?module=auth&action=logout" style="color: #fda4af; font-size: 14px; font-weight: 500;">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>index.php?module=auth&action=login" style="color: #ffffff; font-weight: 500; font-size: 14px;">Masuk</a>
                <a href="<?= BASE_URL ?>index.php?module=auth&action=register" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px; color: var(--primary); font-weight: 600;">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</header>
