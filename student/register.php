<?php
session_start();
require_once '../config/database.php';

$pdo = getDBConnection();

$error = '';
$success = '';

if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');

    if ($username === '' || $password === '' || $password_confirm === '') {
        $error = 'Vui lòng nhập đầy đủ Tên đăng nhập và Mật khẩu!';
    } elseif ($password !== $password_confirm) {
        $error = 'Mật khẩu nhập lại không khớp!';
    } else {
        try {
            // Kiểm tra trùng tên đăng nhập trong bảng students
            $check = $pdo->prepare('SELECT id FROM students WHERE username = ?');
            $check->execute([$username]);
            if ($check->fetch()) {
                $error = 'Tên đăng nhập đã tồn tại!';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins = $pdo->prepare('INSERT INTO students (username, password, email, full_name) VALUES (?, ?, ?, ?)');
                $ins->execute([$username, $hash, $email, $full_name]);
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay.';
            }
        } catch (PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký học sinh</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth{max-width:520px;margin:60px auto;background:#fff;padding:2rem;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
        .auth h1{margin:0 0 .75rem;color:#2c3e50}
        .form-group{margin-bottom:1rem}
        .form-group label{display:block;margin-bottom:.5rem;color:#555;font-weight:500}
        .form-group input{width:100%;padding:.75rem;border:2px solid #e1e5e9;border-radius:5px;font-size:1rem}
        .form-group input:focus{outline:none;border-color:#2c3e50}
        .btn{width:100%;padding:.75rem;background:#28a745;color:#fff;border:none;border-radius:5px;font-size:1rem;font-weight:600;cursor:pointer}
        .btn:hover{background:#218838}
        .error{background:#f8d7da;color:#721c24;padding:.75rem;border-radius:5px;margin-bottom:1rem;border:1px solid #f5c6cb}
        .success{background:#d4edda;color:#155724;padding:.75rem;border-radius:5px;margin-bottom:1rem;border:1px solid #c3e6cb}
        .links{text-align:center;margin-top:1rem}
        .links a{color:#2c3e50;text-decoration:none}
        .links a:hover{text-decoration:underline}
    </style>
</head>
<body>
    <div class="auth">
        <h1>📝 Đăng ký học sinh</h1>
        <p>Tạo tài khoản để xem điểm chuẩn chi tiết và tra cứu nhanh</p>
        <?php if ($error): ?><div class="error"><?php echo escape($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo escape($success); ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Họ và tên</label>
                <input name="full_name" value="<?php echo escape($_POST['full_name'] ?? ''); ?>" placeholder="Nguyễn Văn A">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input name="email" type="email" value="<?php echo escape($_POST['email'] ?? ''); ?>" placeholder="email@domain.com">
            </div>
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input name="username" value="<?php echo escape($_POST['username'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input name="password" type="password" required>
            </div>
            <div class="form-group">
                <label>Nhập lại mật khẩu</label>
                <input name="password_confirm" type="password" required>
            </div>
            <button class="btn" type="submit">Đăng ký</button>
        </form>
        <div class="links">
            <a href="login.php">← Đã có tài khoản? Đăng nhập</a>
        </div>
    </div>
</body>
</html>
