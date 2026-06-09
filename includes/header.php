<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MBT Madura Blue Tourism — Platform digital rantai pasok B2B wisata Madura yang menghubungkan vendor UMKM lokal dengan industri perhotelan dan pariwisata.">
    <title><?= isset($pageTitle) ? esc($pageTitle) . ' | ' : '' ?><?= APP_NAME ?></title>

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- CSS Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Chart.js (untuk halaman statistik & laporan) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
</head>
<body>

<?php require_once __DIR__ . '/navbar.php'; ?>

<main class="container" style="flex:1; padding-top:32px; padding-bottom:60px;">
