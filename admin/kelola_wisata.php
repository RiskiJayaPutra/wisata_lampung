<?php
require_once __DIR__ . '\config\auth.php';
require_once __DIR__ . '\config\database.php';

// Query untuk mendapatkan data wisata
$wisata_query = "SELECT * FROM wisata ORDER BY created_at DESC";
$wisata_result = mysqli_query($conn, $wisata_query);

// Proses tambah wisata
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_wisata'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $kategori = $_POST['kategori'];
    
    // Upload gambar
    $gambar = '';
        if (isset($_FILES['gambar'])) {
        $target_dir = "../assets/uploads/";
        $gambar = basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $gambar;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
    }
    
    $query = "INSERT INTO wisata (nama, deskripsi, alamat, latitude, longitude, kategori, gambar) 
              VALUES ('$nama', '$deskripsi', '$alamat', $latitude, $longitude, '$kategori', '$gambar')";
    mysqli_query($conn, $query);
    header("Location: manage_wisata.php");
    exit;
}

// Proses hapus wisata
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $query = "DELETE FROM wisata WHERE id = $id";
    mysqli_query($conn, $query);
    header("Location: manage_wisata.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Wisata - Wisata Lampung</title>
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

        .table-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid white;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
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

        .edit-btn {
            background-color: var(--warning);
            color: white;
        }

        .edit-btn:hover {
            background-color: #e58e0b;
        }

        .delete-btn {
            background-color: var(--danger);
            color: white;
        }

        .delete-btn:hover {
            background-color: #dc2626;
        }

        .add-btn {
            background-color: var(--success);
            color: white;
            padding: 10px 15px;
            margin-bottom: 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-btn:hover {
            background-color: #0d9c6f;
        }

                /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s ease;
        }

        .wisataForm {
            padding-bottom: 20px;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .modal-header h3 {
            color: var(--dark);
            font-size: 1.5rem;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray);
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
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
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
            width: 100%;
        }

        .submit-btn:hover {
            background-color: var(--secondary);
        }

        #imagePreview {
            margin-top: 10px;
        }

        /* Responsive Table */
        @media (max-width: 768px) {
            .data-table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .toggle-sidebar {
                display: flex;
            }
            
            .sidebar {
                margin-left: -280px;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
        }

        @media (max-width: 576px) {
            .admin-header {
                padding: 1rem;
            }
            
            .admin-brand span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header" id="adminHeader">
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
        <div class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="kelola_wisata.php" class="active"><i class="fas fa-map-marker-alt"></i> Kelola Wisata</a></li>
                <li><a href="kelola_ulasan.php"><i class="fas fa-comments"></i> Kelola Ulasan</a></li>
                <li><a href="kelola_admin.php"><i class="fas fa-users"></i> Kelola Admin</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <div class="dashboard-title">
                <h2>Kelola Wisata</h2>
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <button class="add-btn" id="openModal">
                <i class="fas fa-plus"></i> Tambah Wisata
            </button>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Wisata</th>
                            <th>Kategori</th>
                            <th>Alamat</th>
                            <th>Koordinat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($wisata_result) > 0) : ?>
                            <?php while ($wisata = mysqli_fetch_assoc($wisata_result)) : ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($wisata['gambar'])) : ?>
                                            <img src="../assets/uploads/<?php echo htmlspecialchars($wisata['gambar']); ?>" class="table-img" alt="<?php echo htmlspecialchars($wisata['nama']); ?>">
                                        <?php else : ?>
                                            <div style="width:60px;height:60px;background:#f0f0f0;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-image" style="color:#ccc;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($wisata['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($wisata['kategori']); ?></td>
                                    <td><?php echo htmlspecialchars($wisata['alamat']); ?></td>
                                    <td><?php echo $wisata['latitude'] . ', ' . $wisata['longitude']; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn edit-btn" onclick="editWisata(<?php echo $wisata['id']; ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $wisata['id']; ?>)">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" style="text-align:center;padding:2rem;color:var(--gray);">
                                    Belum ada data wisata
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Wisata -->
    <div class="modal" id="wisataModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Wisata Baru</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <form id="wisataForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="wisataId" name="id">
                
                <div class="form-group">
                    <label for="nama">Nama Wisata</label>
                    <input type="text" id="nama" name="nama" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" class="form-control" required>
                        <option value="Alam">Alam</option>
                        <option value="Sejarah">Sejarah</option>
                        <option value="Kuliner">Kuliner</option>
                        <option value="Budaya">Budaya</option>
                        <option value="Religi">Religi</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <input type="text" id="alamat" name="alamat" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="latitude">Latitude</label>
                    <input type="number" step="0.00000001" id="latitude" name="latitude" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="longitude">Longitude</label>
                    <input type="number" step="0.00000001" id="longitude" name="longitude" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="gambar">Gambar</label>
                    <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*">
                    <small class="text-muted">Ukuran maksimal 2MB (JPG, PNG, JPEG)</small>
                    <div id="imagePreview" style="margin-top:10px;"></div>
                </div>
                
                <button type="submit" name="tambah_wisata" class="submit-btn">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </form>
        </div>
    </div>

    <script>
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('mainContent').classList.toggle('expanded');
        });

        // Header Scroll Effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 10) {
                document.getElementById('adminHeader').classList.add('scrolled');
            } else {
                document.getElementById('adminHeader').classList.remove('scrolled');
            }
        });

        // Modal Functionality
        const modal = document.getElementById('wisataModal');
        const openModalBtn = document.getElementById('openModal');
        const closeModalBtn = document.getElementById('closeModal');
        const modalTitle = document.getElementById('modalTitle');
        const wisataForm = document.getElementById('wisataForm');
        const wisataId = document.getElementById('wisataId');
        const imagePreview = document.getElementById('imagePreview');

        // Open modal for adding new wisata
        openModalBtn.addEventListener('click', function() {
            modalTitle.textContent = 'Tambah Wisata Baru';
            wisataForm.reset();
            wisataId.value = '';
            imagePreview.innerHTML = '';
            modal.style.display = 'flex';
        });

        // Close modal
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Preview image before upload
        document.getElementById('gambar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.innerHTML = `
                        <img src="${e.target.result}" style="max-width:200px;max-height:150px;border-radius:8px;border:2px solid #eee;">
                    `;
                }
                reader.readAsDataURL(file);
            } else {
                imagePreview.innerHTML = '';
            }
        });

        // Function to edit wisata
        function editWisata(id) {
            modalTitle.textContent = 'Edit Wisata';
            wisataId.value = id;
            modal.style.display = 'flex';
            
            // In a real implementation, you would fetch the data here via AJAX
            // and populate the form fields with the existing data
        }

        // Function to confirm deletion
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus wisata ini?')) {
                window.location.href = `manage_wisata.php?hapus=${id}`;
            }
        }
    </script>
</body>
</html>