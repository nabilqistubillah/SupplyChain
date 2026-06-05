<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? esc($pageTitle) . ' | ' : '' ?><?= APP_NAME ?></title>
    
    <!-- CSS stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css">
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php require_once __DIR__ . '/navbar.php'; ?>

<main class="container" style="flex: 1; padding-top: 30px; padding-bottom: 50px;">
