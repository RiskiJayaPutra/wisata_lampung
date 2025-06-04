<?php
// require_once '../config/auth.php';
require_once __DIR__ . '\config\auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Wisata</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-header">
        <div class="admin-brand">
            <i class="fas fa-map-marked-alt"></i>
            <span>Panel Admin</span>
        </div>
        <div class="admin-user">
            <i class="fas fa-user-circle"></i>
            <?= $_SESSION['admin_name'] ?>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
    <!-- ... (kode sebelumnya) ... -->
        <form id="wisata-form" enctype="multipart/form-data">
            <input type="text" name="nama" placeholder="Nama Wisata" required>
            <textarea name="deskripsi" placeholder="Deskripsi"></textarea>
            <input type="text" name="alamat" placeholder="Alamat">
            <input type="number" step="0.00000001" name="latitude" placeholder="Latitude" required>
            <input type="number" step="0.00000001" name="longitude" placeholder="Longitude" required>
            <select name="kategori" required>
                <option value="Alam">Alam</option>
                <option value="Sejarah">Sejarah</option>
                <option value="Kuliner">Kuliner</option>
            </select>
            <input type="file" name="gambar" accept="image/*">
            <button type="submit">Simpan</button>
        </form>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>