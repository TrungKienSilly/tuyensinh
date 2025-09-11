<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
	header('Location: login.php');
	exit;
}

$pdo = getDBConnection();

// Chọn trường (nếu có truyền university_id thì lock vào trường đó)
$selected_university_id = isset($_GET['university_id']) ? (int)$_GET['university_id'] : 0;

// Bộ lọc theo tên/mã ngành
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Lấy danh sách trường phục vụ select
$universities = $pdo->query("SELECT id, name, code FROM universities ORDER BY name")->fetchAll();

$message = '';
$message_type = '';

if ($_POST) {
	$action = $_POST['action'] ?? '';
	$id = (int)($_POST['id'] ?? 0);
	
	$university_id = (int)($_POST['university_id'] ?? 0);
	$code = trim($_POST['code'] ?? '');
	$name = trim($_POST['name'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$training_level = $_POST['training_level'] ?? 'Đại học';
	$duration_years = (int)($_POST['duration_years'] ?? 4);
	
	if ($action === 'add' || $action === 'edit') {
		if (!$university_id || empty($code) || empty($name)) {
			$message = 'Vui lòng nhập đủ Trường, Mã ngành, Tên ngành';
			$message_type = 'error';
		} else {
			try {
				if ($action === 'add') {
					$stmt = $pdo->prepare("INSERT INTO majors (university_id, code, name, description, training_level, duration_years) VALUES (?, ?, ?, ?, ?, ?)");
					$stmt->execute([$university_id, $code, $name, $description, $training_level, $duration_years]);
					$message = 'Thêm ngành thành công';
				} else {
					$stmt = $pdo->prepare("UPDATE majors SET university_id=?, code=?, name=?, description=?, training_level=?, duration_years=? WHERE id=?");
					$stmt->execute([$university_id, $code, $name, $description, $training_level, $duration_years, $id]);
					$message = 'Cập nhật ngành thành công';
				}
				$message_type = 'success';
			} catch (PDOException $e) {
				$message = 'Lỗi: ' . $e->getMessage();
				$message_type = 'error';
			}
		}
	} elseif ($action === 'delete') {
		try {
			$stmt = $pdo->prepare("DELETE FROM majors WHERE id = ?");
			$stmt->execute([$id]);
			$message = 'Xóa ngành thành công';
			$message_type = 'success';
		} catch (PDOException $e) {
			$message = 'Lỗi: ' . $e->getMessage();
			$message_type = 'error';
		}
	}
}

// Lọc theo trường và q
$where = [];
$params = [];
if ($selected_university_id) {
	$where[] = 'm.university_id = :uid';
	$params[':uid'] = $selected_university_id;
}
if ($q !== '') {
	$where[] = '(m.name LIKE :q_name OR m.code LIKE :q_code)';
	$params[':q_name'] = "%$q%";
	$params[':q_code'] = "%$q%";
}
$whereSql = empty($where) ? '' : ('WHERE ' . implode(' AND ', $where));

// Lấy danh sách ngành
$sql = "
	SELECT m.*, u.name as university_name, u.code as university_code,
		(SELECT COUNT(*) FROM admission_scores a WHERE a.major_id = m.id) as score_count
	FROM majors m
	JOIN universities u ON m.university_id = u.id
	$whereSql
	ORDER BY u.name, m.name
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$majors = $stmt->fetchAll();

// Nếu edit
$edit_major = null;
if (isset($_GET['edit'])) {
	$edit_id = (int)$_GET['edit'];
	$s = $pdo->prepare("SELECT * FROM majors WHERE id = ?");
	$s->execute([$edit_id]);
	$edit_major = $s->fetch();
	if ($edit_major) {
		$selected_university_id = $edit_major['university_id'];
	}
}

$show_form = isset($_GET['new']) || !empty($edit_major);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Quản lý ngành - Admin</title>
	<link rel="stylesheet" href="../assets/css/style.css">
	<style>
		.admin-header{background:linear-gradient(135deg,#2c3e50 0%,#34495e 100%);color:#fff;padding:1rem 0;margin-bottom:2rem}
		.admin-nav{background:#34495e;padding:.5rem 0;margin-bottom:2rem}
		.admin-nav ul{list-style:none;display:flex;justify-content:center;gap:2rem}
		.admin-nav a{color:#fff;text-decoration:none;padding:.5rem 1rem;border-radius:5px}
		.admin-nav a:hover{background:#2c3e50}
		.form-section{background:#fff;padding:2rem;border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,.1);margin-bottom:2rem}
		.table-header{background:#f8f9fa;padding:1.5rem;border-bottom:1px solid #eee}
		/* Button variants (đồng bộ với trang universities) */
		.btn{padding:0.75rem 1.5rem;border:none;border-radius:5px;cursor:pointer;font-size:1rem;font-weight:500;text-decoration:none;display:inline-block;text-align:center;transition:all .3s ease}
		.btn-primary{background:#2c3e50;color:#fff}
		.btn-primary:hover{background:#34495e}
		.btn-secondary{background:#6c757d;color:#fff}
		.btn-secondary:hover{background:#5a6268}
		.btn-danger{background:#dc3545;color:#fff}
		.btn-danger:hover{background:#c82333}
		.btn-success{background:#28a745;color:#fff}
		.btn-success:hover{background:#218838}
	</style>
</head>
<body>
	<header class="admin-header">
		<div class="container">
			<h1>📚 Quản lý ngành</h1>
		</div>
	</header>
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
			<div class="alert alert-<?php echo $message_type; ?>" style="margin-bottom:1rem;\"><?php echo escape($message); ?></div>
		<?php endif; ?>

		<!-- Filter -->
		<div class="form-section" style="padding:1rem 1.5rem; margin-bottom:1rem;">
			<form method="GET" class="search-form" style="display:grid; grid-template-columns:1fr 1fr auto auto; gap:1rem;">
				<div class="form-group">
					<label>Trường</label>
					<select name="university_id" onchange="this.form.submit()">
						<option value="">-- Tất cả --</option>
						<?php foreach ($universities as $u): ?>
							<option value="<?php echo $u['id']; ?>" <?php echo (int)$selected_university_id === (int)$u['id'] ? 'selected' : ''; ?>><?php echo escape($u['name']) . ' (' . escape($u['code']) . ')'; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group">
					<label>Tìm theo tên/mã ngành</label>
					<input type="text" name="q" value="<?php echo escape($q); ?>" placeholder="VD: IT01 hoặc CNTT">
				</div>
				<div class="form-group" style="display:flex; gap:.5rem; align-items:end;">
					<button class="btn btn-primary" type="submit">Lọc</button>
					<a class="btn btn-secondary" href="majors.php">Xóa</a>
				</div>
				<div class="form-group" style="display:flex; align-items:end;">
					<a class="btn btn-success" href="majors.php?new=1<?php echo $selected_university_id ? '&university_id='.$selected_university_id : ''; ?><?php echo $q !== '' ? '&q='.urlencode($q) : ''; ?>">+ Thêm ngành mới</a>
				</div>
			</form>
		</div>

		<?php if ($show_form): ?>
		<div class="form-section">
			<h2><?php echo $edit_major ? 'Chỉnh sửa ngành' : 'Thêm ngành mới'; ?></h2>
			<form method="POST">
				<input type="hidden" name="action" value="<?php echo $edit_major ? 'edit' : 'add'; ?>">
				<?php if ($edit_major): ?><input type="hidden" name="id" value="<?php echo $edit_major['id']; ?>"><?php endif; ?>
				<div class="form-row">
					<div class="form-group">
						<label>Trường</label>
						<select name="university_id" required>
							<option value="">-- Chọn trường --</option>
							<?php foreach ($universities as $u): ?>
								<option value="<?php echo $u['id']; ?>" <?php echo (int)($edit_major['university_id'] ?? $selected_university_id) === (int)$u['id'] ? 'selected' : ''; ?>><?php echo escape($u['name']) . ' (' . escape($u['code']) . ')'; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-group">
						<label>Mã ngành</label>
						<input type="text" name="code" value="<?php echo escape($edit_major['code'] ?? ''); ?>" required>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label>Tên ngành</label>
						<input type="text" name="name" value="<?php echo escape($edit_major['name'] ?? ''); ?>" required>
					</div>
					<div class="form-group">
						<label>Trình độ</label>
						<select name="training_level">
							<?php $levels=['Đại học','Cao đẳng','Thạc sĩ','Tiến sĩ']; foreach($levels as $lv): ?>
								<option value="<?php echo $lv; ?>" <?php echo (($edit_major['training_level'] ?? 'Đại học')===$lv)?'selected':''; ?>><?php echo $lv; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label>Thời gian đào tạo (năm)</label>
						<input type="number" name="duration_years" min="1" max="10" value="<?php echo (int)($edit_major['duration_years'] ?? 4); ?>">
					</div>
					<div class="form-group">
						<label>Mô tả</label>
						<input type="text" name="description" value="<?php echo escape($edit_major['description'] ?? ''); ?>">
					</div>
				</div>
				<button class="btn btn-primary" type="submit"><?php echo $edit_major ? 'Cập nhật' : 'Thêm mới'; ?></button>
				<?php if ($edit_major): ?><a class="btn btn-secondary" href="majors.php">Hủy</a><?php else: ?><a class="btn btn-secondary" href="majors.php">Đóng</a><?php endif; ?>
			</form>
		</div>
		<?php endif; ?>

		<div class="data-table">
			<div class="table-header"><h2>Danh sách ngành (<?php echo count($majors); ?>)</h2></div>
			<div class="table-content">
				<table class="score-table">
					<thead>
						<tr>
							<th>STT</th>
							<th>Trường</th>
							<th>Mã ngành</th>
							<th>Tên ngành</th>
							<th>Trình độ</th>
							<th>Thời gian</th>
							<th>Điểm chuẩn</th>
							<th>Thao tác</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($majors as $i => $m): ?>
						<tr>
							<td><?php echo $i+1; ?></td>
							<td><?php echo escape($m['university_name']); ?></td>
							<td><span class="major-code"><?php echo escape($m['code']); ?></span></td>
							<td><?php echo escape($m['name']); ?></td>
							<td><?php echo escape($m['training_level']); ?></td>
							<td><?php echo (int)$m['duration_years']; ?> năm</td>
							<td><?php echo (int)$m['score_count']; ?></td>
							<td>
								<a class="btn btn-success" href="?edit=<?php echo $m['id']; ?>">Sửa</a>
								<a class="btn btn-primary" href="scores.php?major_id=<?php echo $m['id']; ?>">Điểm chuẩn</a>
								<form method="POST" style="display:inline" onsubmit="return confirm('Xóa ngành này?')">
									<input type="hidden" name="action" value="delete">
									<input type="hidden" name="id" value="<?php echo $m['id']; ?>">
									<button class="btn btn-danger" type="submit">Xóa</button>
								</form>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<footer class="footer"><div class="container"><p>&copy; 2024 Hệ thống quản lý trường đại học.</p></div></footer>
</body>
</html>
