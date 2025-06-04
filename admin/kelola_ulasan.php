<?php
require_once __DIR__ . '\config\auth.php';
require_once __DIR__ . '\config\database.php';

// Query untuk mendapatkan data ulasan
$ulasan_query = "SELECT u.*, w.nama as wisata_nama 
                 FROM ulasan u 
                 JOIN wisata w ON u.wisata_id = w.id 
                 ORDER BY u.created_at DESC";
$ulasan_result = mysqli_query($conn, $ulasan_query);

// Proses hapus ulasan
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $query = "DELETE FROM ulasan WHERE id = $id";
    mysqli_query($conn, $query);
    header("Location: kelola_ulasan.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Ulasan - Wisata Lampung</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Gunakan style yang sama dari dashboard.php */
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

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            margin-left: 280px;
            transition: all 0.3s ease;
        }

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
        }

        .dashboard-title h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .data-table th, .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        .data-table th {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background-color: #f8f9fa;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }

        .delete-btn {
            background-color: var(--danger);
            color: white;
        }

        .delete-btn:hover {
            background-color: #dc2626;
        }

        /* Rating Stars */
        .rating-stars {
            color: var(--warning);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
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
                <i class="fas fa-user"></i>
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
                <li><a href="kelola_wisata.php"><i class="fas fa-map-marker-alt"></i> Kelola Wisata</a></li>
                <li><a href="kelola_ulasan.php" class="active"><i class="fas fa-comments"></i> Kelola Ulasan</a></li>
                <li><a href="kelola_admin.php"><i class="fas fa-users"></i> Kelola Admin</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-title">
                <h2>Kelola Ulasan</h2>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Wisata</th>
                            <th>Nama User</th>
                            <th>Rating</th>
                            <th>Komentar</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($ulasan_result) > 0) : ?>
                            <?php while ($ulasan = mysqli_fetch_assoc($ulasan_result)) : ?>
                                <tr>
                                    <td><?php echo $ulasan['id']; ?></td>
                                    <td><?php echo htmlspecialchars($ulasan['wisata_nama']); ?></td>
                                    <td><?php echo htmlspecialchars($ulasan['nama_user']); ?></td>
                                    <td class="rating-stars">
                                        <?php echo str_repeat('★', $ulasan['rating']) . str_repeat('☆', 5 - $ulasan['rating']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($ulasan['komentar']); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($ulasan['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $ulasan['id']; ?>)">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" style="text-align:center;padding:2rem;color:var(--gray);">
                                    Belum ada data ulasan
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Fungsi konfirmasi hapus
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus ulasan ini?')) {
                window.location.href = 'kelola_ulasan.php?hapus=' + id;
            }
        }

        // Toggle sidebar untuk mobile (jika diperlukan)
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