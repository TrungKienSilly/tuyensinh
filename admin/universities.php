<?php
session_start();
require_once '../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$pdo = getDBConnection();

// Bộ lọc tìm kiếm theo tên/mã trường
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Xử lý thêm/sửa/xóa trường
$message = '';
$message_type = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    
    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $established_year = (int)($_POST['established_year'] ?? 0);
        $university_type = $_POST['university_type'] ?? 'Công lập';
        
        if (empty($name) || empty($code) || empty($province)) {
            $message = 'Vui lòng nhập đầy đủ thông tin bắt buộc!';
            $message_type = 'error';
        } else {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare(" 
                        INSERT INTO universities (name, code, province, address, website, phone, email, description, established_year, university_type) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $code, $province, $address, $website, $phone, $email, $description, $established_year, $university_type]);
                    $message = 'Thêm trường đại học thành công!';
                } else {
                    $stmt = $pdo->prepare(" 
                        UPDATE universities 
                        SET name = ?, code = ?, province = ?, address = ?, website = ?, phone = ?, email = ?, description = ?, established_year = ?, university_type = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $code, $province, $address, $website, $phone, $email, $description, $established_year, $university_type, $id]);
                    $message = 'Cập nhật trường đại học thành công!';
                }
            } catch (PDOException $e) {
                $message = 'Lỗi: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM universities WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Xóa trường đại học thành công!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Lấy danh sách trường (kèm lọc)
$whereSql = '';
$params = [];
if ($q !== '') {
    $whereSql = "WHERE (u.name LIKE :q_name OR u.code LIKE :q_code)";
    $params[':q_name'] = "%$q%";
    $params[':q_code'] = "%$q%";
}
$sqlList = "
    SELECT u.*, COUNT(m.id) as major_count
    FROM universities u
    LEFT JOIN majors m ON u.id = m.university_id
    $whereSql
    GROUP BY u.id
    ORDER BY u.name
";
$stmtList = $pdo->prepare($sqlList);
$stmtList->execute($params);
$universities = $stmtList->fetchAll();

// Lấy thông tin trường để chỉnh sửa
$edit_university = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM universities WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_university = $stmt->fetch();
}

$show_form = isset($_GET['new']) || !empty($edit_university);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý trường đại học - Admin Panel</title>
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
        .admin-nav a:hover { background: #2c3e50; }
        .form-section { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #e1e5e9; border-radius: 5px; font-size: 1rem; transition: border-color 0.3s ease; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #2c3e50; }
        .form-group textarea { height: 100px; resize: vertical; }
        .btn-group { display: flex; gap: 1rem; }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; font-weight: 500; text-decoration: none; display: inline-block; text-align: center; transition: all 0.3s ease; }
        .btn-primary { background: #2c3e50; color: white; }
        .btn-primary:hover { background: #34495e; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .data-table { background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .table-header { background: #f8f9fa; padding: 1.5rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .table-content { overflow-x: auto; }
        .score-table { width: 100%; border-collapse: collapse; }
        .score-table th, .score-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        .score-table th { background: #f8f9fa; font-weight: 600; color: #555; }
        .score-table tr:hover { background: #f8f9fa; }
        .action-buttons { display: flex; gap: 0.5rem; }
        .action-buttons .btn { padding: 0.5rem 1rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container">
            <h1>🏛️ Quản lý trường đại học</h1>
            <p>Thêm, sửa, xóa thông tin trường đại học</p>
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
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>" style="margin-bottom: 2rem;">
                <?php echo escape($message); ?>
            </div>
        <?php endif; ?>

        <!-- Filter + Add button -->
        <div class="form-section" style="padding:1rem 1.5rem; margin-bottom:1rem;">
            <form method="GET" style="display:flex; gap:1rem; align-items:end;">
                <div class="form-group" style="flex:1;">
                    <label>Tìm theo tên trường hoặc mã trường</label>
                    <input type="text" name="q" value="<?php echo escape($q); ?>" placeholder="VD: BKA hoặc Bách khoa">
                </div>
                <button class="btn btn-primary" type="submit">Lọc</button>
                <a class="btn btn-secondary" href="universities.php">Xóa</a>
                <a class="btn btn-success" href="universities.php?new=1<?php echo $q !== '' ? '&q='.urlencode($q) : ''; ?>">+ Thêm trường đại học mới</a>
            </form>
        </div>

        <?php if ($show_form): ?>
        <!-- Add/Edit Form -->
        <div class="form-section">
            <h2><?php echo $edit_university ? 'Chỉnh sửa trường đại học' : 'Thêm trường đại học mới'; ?></h2>
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_university ? 'edit' : 'add'; ?>">
                <?php if ($edit_university): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_university['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Tên trường *</label>
                        <input type="text" id="name" name="name" value="<?php echo escape($edit_university['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="code">Mã trường *</label>
                        <input type="text" id="code" name="code" value="<?php echo escape($edit_university['code'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="province">Tỉnh/Thành phố *</label>
                        <input type="text" id="province" name="province" value="<?php echo escape($edit_university['province'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="university_type">Loại trường</label>
                        <select id="university_type" name="university_type">
                            <option value="Công lập" <?php echo ($edit_university['university_type'] ?? '') === 'Công lập' ? 'selected' : ''; ?>>Công lập</option>
                            <option value="Dân lập" <?php echo ($edit_university['university_type'] ?? '') === 'Dân lập' ? 'selected' : ''; ?>>Dân lập</option>
                            <option value="Tư thục" <?php echo ($edit_university['university_type'] ?? '') === 'Tư thục' ? 'selected' : ''; ?>>Tư thục</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Địa chỉ</label>
                    <textarea id="address" name="address"><?php echo escape($edit_university['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" value="<?php echo escape($edit_university['website'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Điện thoại</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo escape($edit_university['phone'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo escape($edit_university['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="established_year">Năm thành lập</label>
                        <input type="number" id="established_year" name="established_year" value="<?php echo $edit_university['established_year'] ?? ''; ?>" min="1800" max="<?php echo date('Y'); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea id="description" name="description" rows="4"><?php echo escape($edit_university['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_university ? 'Cập nhật' : 'Thêm mới'; ?>
                    </button>
                    <?php if ($edit_university): ?>
                        <a href="universities.php" class="btn btn-secondary">Hủy</a>
                    <?php else: ?>
                        <a href="universities.php" class="btn btn-secondary">Đóng</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Universities List -->
        <div class="data-table">
            <div class="table-header">
                <h2>Danh sách trường đại học (<?php echo count($universities); ?> trường)</h2>
            </div>
            <div class="table-content">
                <table class="score-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên trường</th>
                            <th>Mã</th>
                            <th>Tỉnh/TP</th>
                            <th>Loại</th>
                            <th>Số ngành</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($universities as $index => $university): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo escape($university['name']); ?></td>
                                <td><span class="major-code"><?php echo escape($university['code']); ?></span></td>
                                <td><?php echo escape($university['province']); ?></td>
                                <td><?php echo escape($university['university_type']); ?></td>
                                <td><?php echo formatNumber($university['major_count']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?php echo $university['id']; ?>" class="btn btn-success">Sửa</a>
                                        <a href="majors.php?university_id=<?php echo $university['id']; ?>" class="btn btn-primary">Ngành</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa trường này?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $university['id']; ?>">
                                            <button type="submit" class="btn btn-danger">Xóa</button>
                                        </form>
                                    </div>
                                </td>
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

