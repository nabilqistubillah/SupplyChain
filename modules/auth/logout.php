<?php
// modules/auth/logout.php

if (is_authenticated()) {
    log_info("User '{$_SESSION['username']}' logged out.");
}

logout_user();

// Redirect to dashboard/home
redirect(BASE_URL . 'index.php');
