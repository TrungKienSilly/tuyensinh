<?php
require_once '../config/database.php';

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$page_title = 'Khu vực học sinh';
$additional_css = '
    <style>
        .hero{max-width:900px;margin:60px auto;background:#fff;padding:2rem;border-radius:10px;box-shadow:0 5px 20px rgba(0,0,0,.1)}
        .actions{margin-top:1.5rem;display:flex;gap:1rem}
        .btn{padding:.75rem 1.25rem;border:none;border-radius:6px;cursor:pointer;text-decoration:none;display:inline-block}
        .btn-primary{background:#2c3e50;color:#fff}
        .btn-secondary{background:#6c757d;color:#fff}
    </style>
';
include '../includes/header.php';
?>
    <div class="hero">
        <h1>Chào, <?php echo escape($_SESSION['student_username'] ?? 'Học sinh'); ?>!</h1>
        <p>Bạn đã đăng nhập thành công. Bạn có thể tra cứu trường/ngành và xem điểm chuẩn mới nhất.</p>
        <div class="actions">
            <a class="btn btn-primary" href="../search.php">Tìm kiếm trường/ngành</a>
            <a class="btn btn-secondary" href="logout.php">Đăng xuất</a>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>