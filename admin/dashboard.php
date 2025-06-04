<?php
require_once __DIR__ . '\config\auth.php';
require_once __DIR__ . '\config\database.php';

// Query untuk data dashboard
$total_wisata = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM wisata"))['total'];
$total_ulasan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM ulasan"))['total'];
$total_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM admin"))['total'];
$avg_rating = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg FROM ulasan"))['avg'];
$recent_activities = mysqli_query($conn, 
    "SELECT u.*, w.nama as wisata_nama 
     FROM ulasan u 
     JOIN wisata w ON u.wisata_id = w.id 
     ORDER BY u.created_at DESC 
     LIMIT 5");
$popular_wisata = mysqli_query($conn,
    "SELECT w.*, COUNT(u.id) as total_ulasan, AVG(u.rating) as avg_rating
     FROM wisata w
     LEFT JOIN ulasan u ON w.id = u.wisata_id
     GROUP BY w.id
     ORDER BY total_ulasan DESC, avg_rating DESC
     LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Wisata Lampung</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a href="/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </header>

    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="kelola_wisata.php"><i class="fas fa-map-marker-alt"></i> Kelola Wisata</a></li>
                <li><a href="kelola_ulasan.php"><i class="fas fa-comments"></i> Kelola Ulasan</a></li>
                <li><a href="kelola_admin.php"><i class="fas fa-users"></i> Kelola Admin</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Pengaturan</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <div class="dashboard-title">
                <h2>Dashboard</h2>
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Statistik Cards -->
            <div class="dashboard-cards">
                <div class="card animate-fadeIn delay-1">
                    <h3><i class="fas fa-map-marker-alt"></i> Total Wisata</h3>
                    <div class="number"><?php echo number_format($total_wisata); ?></div>
                    <div class="card-footer">
                        <span>Semua Kategori</span>
                        <div class="trend up">
                            <i class="fas fa-arrow-up"></i>
                            <span>5.2%</span>
                        </div>
                    </div>
                </div>

                <div class="card animate-fadeIn delay-2">
                    <h3><i class="fas fa-star"></i> Total Ulasan</h3>
                    <div class="number"><?php echo number_format($total_ulasan); ?></div>
                    <div class="card-footer">
                        <span>Rating Rata-rata: <?php echo number_format($avg_rating, 1); ?>/5</span>
                        <div class="trend up">
                            <i class="fas fa-arrow-up"></i>
                            <span>12.7%</span>
                        </div>
                    </div>
                </div>

                <div class="card animate-fadeIn delay-3">
                    <h3><i class="fas fa-users"></i> Total Admin</h3>
                    <div class="number"><?php echo number_format($total_admin); ?></div>
                    <div class="card-footer">
                        <span>Aktif: <?php echo $total_admin; ?></span>
                        <div class="trend">
                            <i class="fas fa-equals"></i>
                            <span>0%</span>
                        </div>
                    </div>
                </div>

                <div class="card animate-fadeIn delay-4">
                    <h3><i class="fas fa-chart-line"></i> Aktivitas</h3>
                    <div class="number"><?php echo number_format($total_ulasan + $total_wisata); ?></div>
                    <div class="card-footer">
                        <span>Total Interaksi</span>
                        <div class="trend up">
                            <i class="fas fa-arrow-up"></i>
                            <span>8.3%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="chart-container animate-fadeIn">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-bar"></i> Statistik Kunjungan</h3>
                    <select id="chartPeriod" style="padding: 5px 10px; border-radius: 6px; border: 1px solid var(--gray-light);">
                        <option value="week">Minggu Ini</option>
                        <option value="month">Bulan Ini</option>
                        <option value="year">Tahun Ini</option>
                    </select>
                </div>
                <canvas id="visitorsChart"></canvas>
            </div>

            <!-- Activities Section -->
            <div class="activities-section">
                <!-- Recent Activities -->
                <div class="recent-activities animate-fadeIn">
                    <div class="section-header">
                        <h3><i class="fas fa-history"></i> Aktivitas Terbaru</h3>
                        <a href="#">Lihat Semua</a>
                    </div>
                    
                    <?php if (mysqli_num_rows($recent_activities) > 0) { ?>
                        <?php while ($activity = mysqli_fetch_assoc($recent_activities)) { ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-comment-dots"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-user"><?php echo htmlspecialchars($activity['nama_user']); ?></div>
                                    <div class="activity-desc">Memberikan ulasan di <?php echo htmlspecialchars($activity['wisata_nama']); ?></div>
                                    <div class="activity-stars"><?php echo str_repeat('★', $activity['rating']) . str_repeat('☆', 5 - $activity['rating']); ?></div>
                                    <div class="activity-time">
                                        <i class="far fa-clock"></i>
                                        <?php echo date('d M Y H:i', strtotime($activity['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p style="text-align: center; color: var(--gray); padding: 20px 0;">Belum ada aktivitas terbaru</p>
                    <?php } ?>
                </div>

                <!-- Popular Wisata -->
                <div class="popular-wisata animate-fadeIn">
                    <div class="section-header">
                        <h3><i class="fas fa-fire"></i> Wisata Populer</h3>
                        <a href="kelola_wisata.php">Lihat Semua</a>
                    </div>
                    
                    <?php if (mysqli_num_rows($popular_wisata) > 0) { ?>
                        <?php while ($wisata = mysqli_fetch_assoc($popular_wisata)) { ?>
                            <div class="popular-item">
                                <img src="../assets/uploads/<?php echo htmlspecialchars($wisata['gambar']); ?>" class="popular-img" alt="<?php echo htmlspecialchars($wisata['nama']); ?>">
                                <div class="popular-content">
                                    <div class="popular-title"><?php echo htmlspecialchars($wisata['nama']); ?></div>
                                    <div class="popular-meta">
                                        <span><?php echo $wisata['kategori']; ?></span>
                                        <div class="popular-rating">
                                            <i class="fas fa-star"></i>
                                            <span><?php echo number_format($wisata['avg_rating'] ?? 0, 1); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p style="text-align: center; color: var(--gray); padding: 20px 0;">Belum ada data wisata</p>
                    <?php } ?>
                </div>
            </div>
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

        // Chart.js Implementation
        const ctx = document.getElementById('visitorsChart').getContext('2d');
        const visitorsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Pengunjung',
                    data: [120, 190, 170, 220, 250, 310, 280],
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderColor: '#4361ee',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4361ee',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        titleFont: { family: 'Poppins', size: 14 },
                        bodyFont: { family: 'Poppins', size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: { family: 'Poppins' }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { family: 'Poppins' }
                        }
                    }
                }
            }
        });

        // Change chart data based on period
        document.getElementById('chartPeriod').addEventListener('change', function() {
            const period = this.value;
            let newLabels, newData;
            
            if (period === 'week') {
                newLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                newData = [120, 190, 170, 220, 250, 310, 280];
            } else if (period === 'month') {
                newLabels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'];
                newData = [850, 920, 780, 950];
            } else {
                newLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                newData = [3200, 2800, 3500, 3800, 4200, 4500, 5100, 4900, 4700, 5200, 4800, 5500];
            }
            
            visitorsChart.data.labels = newLabels;
            visitorsChart.data.datasets[0].data = newData;
            visitorsChart.update();
        });

        // Animation on scroll
        const animateOnScroll = function() {
            const elements = document.querySelectorAll('.animate-fadeIn');
            elements.forEach(el => {
                const elTop = el.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (elTop < windowHeight - 100) {
                    el.style.opacity = 1;
                    el.style.transform = 'translateY(0)';
                }
            });
        };

        // Run once on load
        animateOnScroll();
        
        // Run on scroll
        window.addEventListener('scroll', animateOnScroll);
    </script>
</body>
</html>