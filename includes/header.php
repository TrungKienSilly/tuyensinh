<?php
// Khởi tạo session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Xác định đường dẫn base dựa trên vị trí hiện tại
$current_dir = dirname($_SERVER['PHP_SELF']);
$base_path = '';

// Nếu đang ở trong thư mục con, cần điều chỉnh đường dẫn
if (strpos($current_dir, '/student') !== false) {
    $base_path = '../';
} elseif (strpos($current_dir, '/admin') !== false) {
    $base_path = '../';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? escape($page_title) : 'Hệ thống quản lý trường đại học tuyển sinh'; ?></title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <?php if (isset($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1>🎓 Hệ thống quản lý trường đại học</h1>
            <p>Tra cứu thông tin tuyển sinh các trường đại học trên toàn quốc</p>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="container">
            <ul>
                <li><a href="<?php echo $base_path; ?>index.php">Trang chủ</a></li>
                <li><a href="<?php echo $base_path; ?>search.php">Tìm kiếm nâng cao</a></li>
                <li><a href="<?php echo $base_path; ?>admin/">Quản trị</a></li>
                <?php if (!empty($_SESSION['student_logged_in'])): ?>
                    <li><a href="<?php echo $base_path; ?>student/logout.php">Đăng xuất</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $base_path; ?>student/login.php">Đăng nhập</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
