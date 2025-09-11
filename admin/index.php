<?php
session_start();
require_once '../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$pdo = getDBConnection();

// Lấy thống kê tổng quan
$stats = [];

// Tổng số trường
$stats['total_universities'] = $pdo->query("SELECT COUNT(*) as count FROM universities")->fetch()['count'];

// Tổng số ngành
$stats['total_majors'] = $pdo->query("SELECT COUNT(*) as count FROM majors")->fetch()['count'];

// Tổng số điểm chuẩn
$stats['total_scores'] = $pdo->query("SELECT COUNT(*) as count FROM admission_scores")->fetch()['count'];

// Số trường theo loại
$university_types = $pdo->query("
    SELECT university_type, COUNT(*) as count 
    FROM universities 
    GROUP BY university_type
")->fetchAll();

// Số ngành theo trường (top 10)
$top_universities = $pdo->query("
    SELECT u.name, COUNT(m.id) as major_count
    FROM universities u
    LEFT JOIN majors m ON u.id = m.university_id
    GROUP BY u.id, u.name
    ORDER BY major_count DESC
    LIMIT 10
")->fetchAll();

// Điểm chuẩn cao nhất năm hiện tại
$current_year = date('Y');
$highest_scores = $pdo->query("
    SELECT u.name as university_name, m.name as major_name, a.min_score, a.block
    FROM admission_scores a
    JOIN majors m ON a.major_id = m.id
    JOIN universities u ON m.university_id = u.id
    WHERE a.year = $current_year
    ORDER BY a.min_score DESC
    LIMIT 10
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Hệ thống quản lý trường đại học</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .admin-nav {
            background: #34495e;
            padding: 0.5rem 0;
            margin-bottom: 2rem;
        }
        .admin-nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 2rem;
        }
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .admin-nav a:hover {
            background: #2c3e50;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
            font-size: 1.1rem;
        }
        .data-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .table-header {
            background: #f8f9fa;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        .table-content {
            padding: 1.5rem;
        }
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container">
            <h1>🔧 Admin Panel</h1>
            <p>Quản lý hệ thống trường đại học tuyển sinh</p>
            <div style="text-align: right; margin-top: 1rem;">
                <a href="logout.php" class="logout-btn">Đăng xuất</a>
            </div>
        </div>
    </header>

    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="container">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="universities.php">Quản lý trường</a></li>
                <li><a href="majors.php">Quản lý ngành</a></li>
                <li><a href="scores.php">Quản lý điểm chuẩn</a></li>
                <li><a href="../index.php">Xem website</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo formatNumber($stats['total_universities']); ?></div>
                <div class="stat-label">Trường đại học</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo formatNumber($stats['total_majors']); ?></div>
                <div class="stat-label">Ngành đào tạo</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo formatNumber($stats['total_scores']); ?></div>
                <div class="stat-label">Điểm chuẩn</div>
            </div>
        </div>

        <!-- University Types Chart -->
        <div class="data-table">
            <div class="table-header">
                <h2>📊 Phân bố trường theo loại</h2>
            </div>
            <div class="table-content">
                <table class="score-table">
                    <thead>
                        <tr>
                            <th>Loại trường</th>
                            <th>Số lượng</th>
                            <th>Tỷ lệ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($university_types as $type): ?>
                            <tr>
                                <td><?php echo escape($type['university_type']); ?></td>
                                <td><?php echo formatNumber($type['count']); ?></td>
                                <td><?php echo round(($type['count'] / $stats['total_universities']) * 100, 1); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Universities by Majors -->
        <div class="data-table">
            <div class="table-header">
                <h2>🏆 Top 10 trường có nhiều ngành nhất</h2>
            </div>
            <div class="table-content">
                <table class="score-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên trường</th>
                            <th>Số ngành</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_universities as $index => $university): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo escape($university['name']); ?></td>
                                <td><?php echo formatNumber($university['major_count']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Highest Scores -->
        <div class="data-table">
            <div class="table-header">
                <h2>🎯 Top 10 điểm chuẩn cao nhất năm <?php echo $current_year; ?></h2>
            </div>
            <div class="table-content">
                <table class="score-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Trường</th>
                            <th>Ngành</th>
                            <th>Khối</th>
                            <th>Điểm chuẩn</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($highest_scores as $index => $score): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo escape($score['university_name']); ?></td>
                                <td><?php echo escape($score['major_name']); ?></td>
                                <td><span class="major-code"><?php echo escape($score['block']); ?></span></td>
                                <td><span class="score-high"><?php echo formatScore($score['min_score']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Hệ thống quản lý trường đại học. Tất cả quyền được bảo lưu.</p>
        </div>
    </footer>
</body>
</html>
