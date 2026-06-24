<?php
// File: modules/pengelola/destinations_create.php

require_role(['pengelola_wisata']);
$pageTitle = "Tambah Destinasi Baru";

$error = '';
$success = '';

// Proses form jika ada request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $lokasi = trim($_POST['lokasi']);
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);
    $deskripsi = trim($_POST['deskripsi']);
    $foto_path = '';

    // Validasi input dasar
    if (empty($nama) || empty($lokasi) || empty($deskripsi)) {
        $error = "Nama, lokasi, dan deskripsi wajib diisi.";
    } else {
        // Proses Upload Foto (jika ada file yang diunggah)
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../assets/uploads/';
            
            // Buat folder jika belum ada
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['foto']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

            // Validasi format file
            $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFilePath)) {
                    // Simpan path relatif untuk database
                    $foto_path = 'assets/uploads/' . $fileName;
                } else {
                    $error = "Gagal mengunggah foto.";
                }
            } else {
                $error = "Format foto harus JPG, JPEG, PNG, atau WEBP.";
            }
        }

        // Jika tidak ada error, simpan ke database
        if (empty($error)) {
            try {
                $query = "INSERT INTO destinasi (nama, deskripsi, lokasi, latitude, longitude, foto_path, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW())";
                db_query($query, [$nama, $deskripsi, $lokasi, $latitude, $longitude, $foto_path]);
                
                // Redirect kembali ke halaman kelola dengan pesan sukses
                echo "<script>
                        alert('Destinasi berhasil ditambahkan!');
                        window.location.href = '" . BASE_URL . "index.php?module=pengelola&action=destinations';
                      </script>";
                exit;
            } catch (Exception $e) {
                log_error("Gagal menambah destinasi: " . $e->getMessage());
                $error = "Terjadi kesalahan pada database saat menyimpan data.";
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-weight: 700; color: var(--primary);">
            <i class="fa-solid fa-plus-circle"></i> Tambah Destinasi Baru
        </h2>
        <a href="<?= BASE_URL ?>index.php?module=pengelola&action=destinations" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-top-success" style="border-top: 4px solid #047857;">
        <div class="card-body p-4">
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label fw-bold">Nama Destinasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required placeholder="Contoh: Pantai Lon Malang">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="lokasi" class="form-label fw-bold">Lokasi / Kota <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lokasi" name="lokasi" required placeholder="Contoh: Sampang, Madura">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="latitude" class="form-label fw-bold">Latitude</label>
                        <input type="text" class="form-control" id="latitude" name="latitude" placeholder="Contoh: -7.123456">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="longitude" class="form-label fw-bold">Longitude</label>
                        <input type="text" class="form-control" id="longitude" name="longitude" placeholder="Contoh: 113.234567">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label fw-bold">Deskripsi Destinasi <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required placeholder="Jelaskan keindahan dan fasilitas destinasi ini..."></textarea>
                </div>

                <div class="mb-4">
                    <label for="foto" class="form-label fw-bold">Upload Foto Destinasi</label>
                    <input class="form-control" type="file" id="foto" name="foto" accept=".jpg, .jpeg, .png, .webp">
                    <div class="form-text text-muted">Format yang diizinkan: JPG, JPEG, PNG. Maksimal ukuran file disarankan 2MB.</div>
                </div>

                <hr class="my-4">
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-success px-4" style="background-color: #047857; border-color: #047857;">
                        <i class="fa-solid fa-save"></i> Simpan Destinasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>