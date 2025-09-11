<?php
session_start();
require_once '../config/database.php';

// Nếu đã đăng nhập -> về trang học sinh hoặc trang redirect
if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : (isset($_POST['redirect']) ? $_POST['redirect'] : 'index.php');
    header('Location: ' . ($redirect ?: 'index.php'));
    exit;
}

$error = '';
$redirectAfter = isset($_GET['redirect']) ? $_GET['redirect'] : ($_POST['redirect'] ?? 'index.php');

if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        $pdo = getDBConnection();
        // Đăng nhập học sinh từ bảng students
        $stmt = $pdo->prepare("SELECT * FROM students WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['student_logged_in'] = true;
            $_SESSION['student_username'] = $user['username'];
            $goto = $_POST['redirect'] ?? 'index.php';
            header('Location: ' . ($goto ?: 'index.php'));
            exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập học sinh</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-container{max-width:420px;margin:80px auto;background:#fff;padding:2rem;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.1)}
        .login-header{text-align:center;margin-bottom:1rem}
        .login-header h1{color:#2c3e50;margin:0 0 .5rem}
        .form-group{margin-bottom:1rem}
        .form-group label{display:block;margin-bottom:.5rem;color:#555;font-weight:500}
        .form-group input{width:100%;padding:.75rem;border:2px solid #e1e5e9;border-radius:5px;font-size:1rem}
        .form-group input:focus{outline:none;border-color:#2c3e50}
        .btn{width:100%;padding:.75rem;background:#2c3e50;color:#fff;border:none;border-radius:5px;font-size:1rem;font-weight:500;cursor:pointer}
        .btn:hover{background:#34495e}
        .error{background:#f8d7da;color:#721c24;padding:.75rem;border-radius:5px;margin-bottom:1rem;border:1px solid #f5c6cb}
        .links{text-align:center;margin-top:1rem}
        .links a{color:#2c3e50;text-decoration:none;margin:0 .5rem}
        .links a:hover{text-decoration:underline}
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🎓 Đăng nhập học sinh</h1>
            <p>Truy cập để xem thông tin trường và điểm chuẩn chi tiết</p>
        </div>
        <?php if ($error): ?>
            <div class="error"><?php echo escape($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="redirect" value="<?php echo escape($redirectAfter); ?>">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input id="username" name="username" value="<?php echo escape($_POST['username'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input id="password" name="password" type="password" required>
            </div>
            <button class="btn" type="submit">Đăng nhập</button>
        </form>
        <div class="links">
            <a href="<?php echo escape($redirectAfter ?: '../index.php'); ?>">← Quay lại</a>
            <a href="register.php">Đăng ký tài khoản</a>
        </div>
    </div>
</body>
</html>