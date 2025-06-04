<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// Pastikan hanya admin yang bisa mengakses
if ($_SESSION['admin_level'] != 'super') {
    header("Location: dashboard.php");
    exit;
}

// Inisialisasi variabel
$success_message = '';
$error_message = '';

// Proses update pengaturan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Update informasi umum
        if (isset($_POST['update_general'])) {
            $site_name = mysqli_real_escape_string($conn, $_POST['site_name']);
            $site_description = mysqli_real_escape_string($conn, $_POST['site_description']);
            
            // Simpan ke database (contoh, bisa disesuaikan dengan tabel settings Anda)
            $query = "UPDATE settings SET 
                      site_name = '$site_name',
                      site_description = '$site_description' 
                      WHERE id = 1";
            mysqli_query($conn, $query);
            
            $success_message = "Pengaturan umum berhasil diperbarui!";
        }
        
        // Update logo
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../assets/uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_name = basename($_FILES['site_logo']['name']);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Validasi file
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $target_file)) {
                    $query = "UPDATE settings SET site_logo = '$file_name' WHERE id = 1";
                    mysqli_query($conn, $query);
                    $success_message .= " Logo berhasil diupload!";
                } else {
                    $error_message = "Gagal mengupload logo.";
                }
            } else {
                $error_message = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
            }
        }
        
    } catch (Exception $e) {
        $error_message = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Ambil data pengaturan saat ini
$settings_query = "SELECT * FROM settings WHERE id = 1";
$settings_result = mysqli_query($conn, $settings_query);
$settings = mysqli_fetch_assoc($settings_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Wisata Lampung</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #ebefff;
            --secondary: #3f37c9;
            --dark: #1f2937;
            --light: #f9fafb;
            --accent: #f72585;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
        }

         :root {
            --primary: #4361ee;
            --primary-light: #ebefff;
            --secondary: #3f37c9;
            --dark: #1f2937;
            --light: #f9fafb;
            --accent: #f72585;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            overflow-x: hidden;
        }

        /* Header Styles */
        .admin-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .admin-header.scrolled {
            padding: 0.7rem 2rem;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.15);
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .admin-brand i {
            font-size: 1.5rem;
            color: var(--accent);
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary);
        }

        .logout-btn {
            color: white;
            background-color: var(--accent);
            border: none;
            border-radius: 6px;
            padding: 8px 15px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .logout-btn:hover {
            background-color: #d11668;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(247, 37, 133, 0.3);
        }

        /* Main Layout */
        .admin-container {
            display: flex;
            min-height: 100vh;
            padding-top: 70px;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            position: fixed;
            height: calc(100vh - 70px);
            transition: all 0.3s ease;
            z-index: 900;
        }

        .sidebar-collapse {
            margin-left: -280px;
        }

        .sidebar-menu {
            list-style: none;
            margin-top: 2rem;
        }

        .sidebar-menu li {
            margin-bottom: 8px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            gap: 12px;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background-color: var(--primary-light);
            color: var(--primary);
            transform: translateX(5px);
        }

        .sidebar-menu a.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .sidebar-menu i {
            width: 24px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            margin-left: 280px;
            transition: all 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Dashboard Content */
        .dashboard-title {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-title h2 {
            font-size: 1.8rem;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }

        .dashboard-title h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 2px;
        }

        .toggle-sidebar {
            background: var(--primary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .toggle-sidebar:hover {
            background: var(--secondary);
            transform: rotate(90deg);
        }

        /* Cards Grid */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .card h3 {
            font-size: 1rem;
            color: var(--gray);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card .number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 10px 0;
            transition: all 0.3s;
        }

        .card .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .card .trend {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .trend.up {
            color: var(--success);
        }

        .trend.down {
            color: var(--danger);
        }

        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            height: 350px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-header h3 {
            font-size: 1.2rem;
            color: var(--dark);
        }

        /* Activities Section */
        .activities-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .recent-activities, .popular-wisata {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h3 {
            font-size: 1.2rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .section-header a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        /* Activity Item */
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--gray-light);
            transition: all 0.3s;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            transform: translateX(5px);
        }

        .activity-icon {
            width: 45px;
            height: 45px;
            background: var(--primary-light);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary);
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-user {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .activity-desc {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .activity-stars {
            color: var(--warning);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .activity-time {
            color: var(--gray);
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Popular Wisata */
        .popular-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-light);
            transition: all 0.3s;
        }

        .popular-item:last-child {
            border-bottom: none;
        }

        .popular-item:hover {
            transform: translateX(5px);
        }

        .popular-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid white;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .popular-content {
            flex: 1;
        }

        .popular-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .popular-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.8rem;
            color: var(--gray);
        }

        .popular-rating {
            display: flex;
            align-items: center;
            gap: 3px;
            color: var(--warning);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fadeIn {
            animation: fadeIn 0.6s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }

        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                margin-left: -280px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .toggle-sidebar {
                display: flex;
            }
            .activities-section {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-cards {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            .admin-header {
                padding: 1rem;
            }
            .admin-brand span {
                display: none;
            }
        }
        
        .settings-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .settings-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .settings-card h3 {
            margin-top: 0;
            color: var(--primary);
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-light);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .logo-preview {
            width: 150px;
            height: 150px;
            object-fit: contain;
            border: 2px dashed var(--gray-light);
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
        }

        .submit-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            background-color: var(--secondary);
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
        }

        .success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .error {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        @media (max-width: 768px) {
            .settings-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="admin-brand">
            <i class="fas fa-map-marked-alt"></i>
            <span>Wisata Lampung</span>
        </div>
        <div class="admin-user">
            <div class="admin-avatar">
                <i class="fas fa-user-shield"></i>
            </div>
            <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </header>

    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="manage_wisata.php"><i class="fas fa-map-marker-alt"></i> Kelola Wisata</a></li>
                <li><a href="kelola_ulasan.php"><i class="fas fa-comments"></i> Kelola Ulasan</a></li>
                <li><a href="kelola_admin.php"><i class="fas fa-users"></i> Kelola Admin</a></li>
                <li><a href="setting.php" class="active"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-title">
                <h2>Pengaturan Sistem</h2>
            </div>

            <?php if ($success_message) : ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message) : ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="settings-container">
                <!-- Form Pengaturan Umum -->
                <div class="settings-card">
                    <h3><i class="fas fa-info-circle"></i> Informasi Umum</h3>
                    <form method="POST" action="setting.php">
                        <div class="form-group">
                            <label for="site_name">Nama Situs</label>
                            <input type="text" id="site_name" name="site_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Wisata Lampung'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_description">Deskripsi Situs</label>
                            <textarea id="site_description" name="site_description" class="form-control" 
                                      rows="4" required><?php echo htmlspecialchars($settings['site_description'] ?? 'Sistem Informasi Wisata Lampung'); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_general" class="submit-btn">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Form Logo -->
                <div class="settings-card">
                    <h3><i class="fas fa-image"></i> Logo Situs</h3>
                    <form method="POST" action="setting.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="site_logo">Upload Logo Baru</label>
                            <input type="file" id="site_logo" name="site_logo" class="form-control" accept="image/*">
                            <small>Format: JPG, PNG, GIF (Maks. 2MB)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Pratinjau Logo</label>
                            <?php if (!empty($settings['site_logo'])) : ?>
                                <img src="../assets/uploads/<?php echo htmlspecialchars($settings['site_logo']); ?>" 
                                     class="logo-preview" id="logoPreview">
                            <?php else : ?>
                                <img src="../assets/images/default-logo.png" class="logo-preview" id="logoPreview">
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-upload"></i> Upload Logo
                        </button>
                    </form>
                </div>

                <!-- Form Pengaturan Lainnya -->
                <div class="settings-card">
                    <h3><i class="fas fa-envelope"></i> Pengaturan Email</h3>
                    <form method="POST" action="setting.php">
                        <div class="form-group">
                            <label for="email_sender">Email Pengirim</label>
                            <input type="email" id="email_sender" name="email_sender" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['email_sender'] ?? 'noreply@wisatalampung.id'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email_name">Nama Pengirim</label>
                            <input type="text" id="email_name" name="email_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['email_name'] ?? 'Wisata Lampung'); ?>" required>
                        </div>
                        
                        <button type="submit" name="update_email" class="submit-btn">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Form Tema -->
                <div class="settings-card">
                    <h3><i class="fas fa-palette"></i> Tema Aplikasi</h3>
                    <form method="POST" action="setting.php">
                        <div class="form-group">
                            <label for="theme_color">Warna Tema</label>
                            <input type="color" id="theme_color" name="theme_color" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#4361ee'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="dark_mode">Mode Gelap</label>
                            <select id="dark_mode" name="dark_mode" class="form-control">
                                <option value="0" <?php echo ($settings['dark_mode'] ?? 0) == 0 ? 'selected' : ''; ?>>Tidak Aktif</option>
                                <option value="1" <?php echo ($settings['dark_mode'] ?? 0) == 1 ? 'selected' : ''; ?>>Aktif</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="update_theme" class="submit-btn">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview logo sebelum upload
        document.getElementById('site_logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Toggle sidebar untuk mobile
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'toggle-sidebar';
            toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
            document.querySelector('.dashboard-title').appendChild(toggleBtn);
            
            toggleBtn.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
            });
        });
    </script>
</body>
</html>