<?php
// modules/auth/login.php
// BahariChain: Dynamic Login Portal with Role-Based Theming

// Redirect if already logged in
if (is_authenticated()) {
    redirect(BASE_URL . 'index.php');
}

// ==============================================================================
// DYNAMIC THEME CONFIGURATION BASED ON TYPE
// ==============================================================================
$type = isset($_GET['type']) ? trim($_GET['type']) : 'default';

// Theme configurations
$themes = [
    'admin' => [
        'title' => 'Portal Administrator BahariChain',
        'subtitle' => 'Sistem manajemen platform pariwisata bahari',
        'icon' => 'fa-shield-halved',
        'icon_color' => '#1e3a8a', // Dark Navy Blue
        'primary_color' => '#1e3a8a',
        'button_bg' => '#1e40af',
        'button_hover' => '#1e3a8a',
        'border_focus' => '#3b82f6',
        'show_register' => false
    ],
    'mitra' => [
        'title' => 'Portal Mitra Pengelola Wisata',
        'subtitle' => 'Kelola destinasi dan paket wisata Anda',
        'icon' => 'fa-handshake',
        'icon_color' => '#047857', // Emerald Green
        'primary_color' => '#047857',
        'button_bg' => '#059669',
        'button_hover' => '#047857',
        'border_focus' => '#10b981',
        'show_register' => true
    ],
    'default' => [
        'title' => 'Jelajahi Wisata Bahari Madura',
        'subtitle' => 'Masuk untuk memesan paket wisata impian Anda',
        'icon' => 'fa-anchor',
        'icon_color' => '#0284c7', // Ocean Blue
        'primary_color' => '#0284c7',
        'button_bg' => '#0ea5e9',
        'button_hover' => '#0284c7',
        'border_focus' => '#38bdf8',
        'show_register' => true
    ]
];

// Get current theme (fallback to default)
$theme = $themes[$type] ?? $themes['default'];

$pageTitle = $theme['title'];

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Dynamic CSS Variables for Current Theme -->
<style>
    :root {
        --theme-primary: <?= $theme['primary_color'] ?>;
        --theme-button-bg: <?= $theme['button_bg'] ?>;
        --theme-button-hover: <?= $theme['button_hover'] ?>;
        --theme-border-focus: <?= $theme['border_focus'] ?>;
    }
    
    .login-container .form-control:focus {
        border-color: var(--theme-border-focus) !important;
        box-shadow: 0 0 0 3px rgba(<?= hexToRgb($theme['border_focus']) ?>, 0.1) !important;
    }
    
    .login-container .btn-primary {
        background-color: var(--theme-button-bg) !important;
        border-color: var(--theme-button-bg) !important;
    }
    
    .login-container .btn-primary:hover {
        background-color: var(--theme-button-hover) !important;
        border-color: var(--theme-button-hover) !important;
        transform: translateY(-1px);
    }
</style>

<div class="login-container" style="max-width: 420px; margin: 40px auto; width: 100%;">
    <div class="card" style="padding: 32px; border-top: 4px solid <?= $theme['primary_color'] ?>;">
        <div style="text-align: center; margin-bottom: 24px;">
            <i class="fa-solid <?= $theme['icon'] ?>" style="font-size: 40px; color: <?= $theme['icon_color'] ?>; margin-bottom: 12px;"></i>
            <h2 style="font-weight: 700; color: <?= $theme['primary_color'] ?>;"><?= esc($theme['title']) ?></h2>
            <p style="color: var(--text-secondary); font-size: 14px;"><?= esc($theme['subtitle']) ?></p>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= esc($_SESSION['error_message']) ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= esc($_SESSION['success_message']) ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>index.php?module=auth&action=process_login" method="POST">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="username" class="form-label">Username atau Email</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username..." required autofocus style="border: 2px solid #e5e7eb;">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password..." required style="border: 2px solid #e5e7eb;">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 600; margin-top: 10px; transition: all 0.2s;">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk
            </button>
        </form>
        
        <?php if ($theme['show_register']): ?>
            <div style="text-align: center; margin-top: 24px; font-size: 14px; color: var(--text-secondary);">
                Belum punya akun? <a href="<?= BASE_URL ?>index.php?module=auth&action=register" style="font-weight: 600; color: <?= $theme['primary_color'] ?>;">Daftar Sekarang</a>
            </div>
        <?php endif; ?>
        
        <?php if ($type !== 'default'): ?>
            <div style="text-align: center; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="<?= BASE_URL ?>index.php?module=auth&action=login" style="font-size: 13px; color: var(--text-secondary);">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Login Wisatawan
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Portal Type Selector (optional) -->
    <?php if ($type === 'default'): ?>
        <div style="text-align: center; margin-top: 20px;">
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 8px;">Atau masuk sebagai:</p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <a href="<?= BASE_URL ?>index.php?module=auth&action=login&type=mitra" style="padding: 8px 16px; background: #ecfdf5; border: 1px solid #10b981; border-radius: 6px; color: #047857; font-size: 13px; font-weight: 500; text-decoration: none;">
                    <i class="fa-solid fa-handshake"></i> Mitra
                </a>
                <a href="<?= BASE_URL ?>index.php?module=auth&action=login&type=admin" style="padding: 8px 16px; background: #eff6ff; border: 1px solid #3b82f6; border-radius: 6px; color: #1e40af; font-size: 13px; font-weight: 500; text-decoration: none;">
                    <i class="fa-solid fa-shield-halved"></i> Admin
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Helper function to convert hex to RGB
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    return hexdec(substr($hex, 0, 2)) . ', ' . hexdec(substr($hex, 2, 2)) . ', ' . hexdec(substr($hex, 4, 2));
}

require_once __DIR__ . '/../../includes/footer.php';
?>
