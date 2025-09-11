<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
	header('Location: login.php');
	exit;
}

$pdo = getDBConnection();

// Filters
$selected_university_id = isset($_GET['university_id']) ? (int)$_GET['university_id'] : 0;
$selected_major_id = isset($_GET['major_id']) ? (int)$_GET['major_id'] : 0;
$score_min = isset($_GET['score_min']) && $_GET['score_min'] !== '' ? (float)$_GET['score_min'] : null;
$score_max = isset($_GET['score_max']) && $_GET['score_max'] !== '' ? (float)$_GET['score_max'] : null;

// Load universities for select
$universities = $pdo->query("SELECT id, name FROM universities ORDER BY name")->fetchAll();

// Load majors for select (optionally filtered by university)
if ($selected_university_id) {
	$stmtMajors = $pdo->prepare("SELECT m.id, m.name, m.code, u.name AS university_name FROM majors m JOIN universities u ON m.university_id = u.id WHERE m.university_id = :uid ORDER BY m.name");
	$stmtMajors->execute([':uid' => $selected_university_id]);
	$majors = $stmtMajors->fetchAll();
} else {
	$majors = $pdo->query("SELECT m.id, m.name, m.code, u.name AS university_name FROM majors m JOIN universities u ON m.university_id = u.id ORDER BY u.name, m.name")->fetchAll();
}

$message = '';
$message_type = '';

if ($_POST) {
	$action = $_POST['action'] ?? '';
	$id = (int)($_POST['id'] ?? 0);
	$major_id = (int)($_POST['major_id'] ?? 0);
	$year = (int)($_POST['year'] ?? date('Y'));
	$block = trim($_POST['block'] ?? 'A00');
	$min_score = (float)($_POST['min_score'] ?? 0);
	$quota = (int)($_POST['quota'] ?? 0);
	$note = trim($_POST['note'] ?? '');
	
	if ($action === 'add' || $action === 'edit') {
		if (!$major_id || !$year || empty($block)) {
			$message = 'Vui lòng chọn ngành, năm và khối';
			$message_type = 'error';
		} else {
			try {
				if ($action === 'add') {
					$stmt = $pdo->prepare("INSERT INTO admission_scores (major_id, year, block, min_score, quota, note) VALUES (?, ?, ?, ?, ?, ?)");
					$stmt->execute([$major_id, $year, $block, $min_score, $quota, $note]);
					$message = 'Thêm điểm chuẩn thành công';
				} else {
					$stmt = $pdo->prepare("UPDATE admission_scores SET major_id=?, year=?, block=?, min_score=?, quota=?, note=? WHERE id=?");
					$stmt->execute([$major_id, $year, $block, $min_score, $quota, $note, $id]);
					$message = 'Cập nhật điểm chuẩn thành công';
				}
				$message_type = 'success';
			} catch (PDOException $e) {
				$message = 'Lỗi: ' . $e->getMessage();
				$message_type = 'error';
			}
		}
	} elseif ($action === 'delete') {
		try {
			$stmt = $pdo->prepare("DELETE FROM admission_scores WHERE id = ?");
			$stmt->execute([$id]);
			$message = 'Xóa bản ghi thành công';
			$message_type = 'success';
		} catch (PDOException $e) {
			$message = 'Lỗi: ' . $e->getMessage();
			$message_type = 'error';
		}
	}
}

// Filter
$where = [];
$params = [];
if ($selected_major_id) {
	$where[] = 'a.major_id = :mid';
	$params[':mid'] = $selected_major_id;
} elseif ($selected_university_id) {
	$where[] = 'm.university_id = :uid';
	$params[':uid'] = $selected_university_id;
}
if ($score_min !== null) {
	$where[] = 'a.min_score >= :smin';
	$params[':smin'] = $score_min;
}
if ($score_max !== null) {
	$where[] = 'a.min_score <= :smax';
	$params[':smax'] = $score_max;
}
$whereSql = empty($where) ? '' : ('WHERE ' . implode(' AND ', $where));

// Fetch scores
$sql = "
	SELECT a.*, m.name AS major_name, m.code AS major_code, u.name AS university_name
	FROM admission_scores a
	JOIN majors m ON a.major_id = m.id
	JOIN universities u ON m.university_id = u.id
	$whereSql
	ORDER BY a.year DESC, u.name, m.name, a.block
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$scores = $stmt->fetchAll();

// Edit item
$edit_item = null;
if (isset($_GET['edit'])) {
	$edit_id = (int)$_GET['edit'];
	$s = $pdo->prepare("SELECT * FROM admission_scores WHERE id = ?");
	$s->execute([$edit_id]);
	$edit_item = $s->fetch();
	if ($edit_item) {
		$selected_major_id = $edit_item['major_id'];
	}
}

$show_form = isset($_GET['new']) || !empty($edit_item);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Quản lý điểm chuẩn - Admin</title>
	<link rel="stylesheet" href="../assets/css/style.css">
	<style>
		.admin-header{background:linear-gradient(135deg,#2c3e50 0%,#34495e 100%);color:#fff;padding:1rem 0;margin-bottom:2rem}
		.admin-nav{background:#34495e;padding:.5rem 0;margin-bottom:2rem}
		.admin-nav ul{list-style:none;display:flex;justify-content:center;gap:2rem}
		.admin-nav a{color:#fff;text-decoration:none;padding:.5rem 1rem;border-radius:5px}
		.admin-nav a:hover{background:#2c3e50}
		.form-section{background:#fff;padding:2rem;border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,.1);margin-bottom:2rem}
		.table-header{background:#f8f9fa;padding:1.5rem;border-bottom:1px solid #eee}
		/* Buttons unify */
		.btn{padding:.75rem 1.5rem;border:none;border-radius:5px;cursor:pointer;font-size:1rem;font-weight:500;text-decoration:none;display:inline-block;text-align:center;transition:all .3s ease}
		.btn-primary{background:#2c3e50;color:#fff}
		.btn-primary:hover{background:#34495e}
		.btn-secondary{background:#6c757d;color:#fff}
		.btn-secondary:hover{background:#5a6268}
		.btn-danger{background:#dc3545;color:#fff}
		.btn-danger:hover{background:#c82333}
		.btn-success{background:#28a745;color:#fff}
		.btn-success:hover{background:#218838}
		.back-btn{position:fixed;top:10px;left:10px;z-index:1000}
	</style>
</head>
<body>
	<header class="admin-header">
		<div class="container">
			<h1>📊 Quản lý điểm chuẩn</h1>
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

		<div class="form-section">
			<h2>Bộ lọc</h2>
			<form method="GET" style="margin-bottom:1rem;">
				<div class="form-row">
					<div class="form-group">
						<label>Trường</label>
						<select name="university_id" onchange="this.form.submit()">
							<option value="">-- Chọn trường --</option>
							<?php foreach ($universities as $u): ?>
								<option value="<?php echo $u['id']; ?>" <?php echo (int)$selected_university_id === (int)$u['id'] ? 'selected' : ''; ?>><?php echo escape($u['name']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-group">
						<label>Thang điểm</label>
						<div style="display:flex; gap:.5rem;">
							<input type="number" name="score_min" step="0.1" min="0" max="30" placeholder="Min" value="<?php echo $score_min !== null ? $score_min : ''; ?>">
							<input type="number" name="score_max" step="0.1" min="0" max="30" placeholder="Max" value="<?php echo $score_max !== null ? $score_max : ''; ?>">
							<button class="btn btn-primary" type="submit">Lọc</button>
							<a class="btn btn-secondary" href="scores.php">Xóa</a>
							<a class="btn btn-success" href="scores_fetch.php">⬇ Tải dữ liệu</a>
						</div>
					</div>
				</div>
				<?php if ($selected_major_id): ?>
					<input type="hidden" name="major_id" value="<?php echo $selected_major_id; ?>">
				<?php endif; ?>
			</form>

			<?php if ($show_form): ?>
			<h2><?php echo $edit_item ? 'Chỉnh sửa điểm chuẩn' : 'Thêm điểm chuẩn'; ?></h2>
			<form method="POST">
				<input type="hidden" name="action" value="<?php echo $edit_item ? 'edit' : 'add'; ?>">
				<?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
				<div class="form-row">
					<div class="form-group">
						<label>Ngành</label>
						<select name="major_id" required>
							<option value="">-- Chọn ngành --</option>
							<?php foreach ($majors as $m): ?>
								<option value="<?php echo $m['id']; ?>" <?php echo (int)($edit_item['major_id'] ?? $selected_major_id) === (int)$m['id'] ? 'selected' : ''; ?>><?php echo escape($m['university_name'].' - '.$m['name'].' ('.$m['code'].')'); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-group">
						<label>Năm</label>
						<input type="number" name="year" min="2000" max="<?php echo date('Y')+1; ?>" value="<?php echo (int)($edit_item['year'] ?? date('Y')); ?>" required>
					</div>
					<div class="form-group">
						<label>Khối</label>
						<input type="text" name="block" value="<?php echo escape($edit_item['block'] ?? 'A00'); ?>" required>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group">
						<label>Điểm chuẩn</label>
						<input type="number" name="min_score" min="0" max="30" step="0.1" value="<?php echo (float)($edit_item['min_score'] ?? 0); ?>">
					</div>
					<div class="form-group">
						<label>Chỉ tiêu</label>
						<input type="number" name="quota" min="0" max="10000" value="<?php echo (int)($edit_item['quota'] ?? 0); ?>">
					</div>
					<div class="form-group">
						<label>Ghi chú</label>
						<input type="text" name="note" value="<?php echo escape($edit_item['note'] ?? ''); ?>">
					</div>
				</div>
				<button class="btn btn-primary" type="submit"><?php echo $edit_item ? 'Cập nhật' : 'Thêm mới'; ?></button>
				<?php if ($edit_item): ?><a class="btn btn-secondary" href="scores.php">Hủy</a><?php else: ?><a class="btn btn-secondary" href="scores.php">Đóng</a><?php endif; ?>
			</form>
			<?php endif; ?>
		</div>

		<div class="data-table">
			<div class="table-header"><h2>Danh sách điểm chuẩn (<?php echo count($scores); ?>)</h2></div>
			<div class="table-content">
				<table class="score-table">
					<thead>
						<tr>
							<th>STT</th>
							<th>Trường</th>
							<th>Ngành</th>
							<th>Khối</th>
							<th>Năm</th>
							<th>Điểm</th>
							<th>Chỉ tiêu</th>
							<th>Thao tác</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($scores as $i => $s): ?>
						<tr>
							<td><?php echo $i+1; ?></td>
							<td><?php echo escape($s['university_name']); ?></td>
							<td><?php echo escape($s['major_name'].' ('.$s['major_code'].')'); ?></td>
							<td><span class="major-code"><?php echo escape($s['block']); ?></span></td>
							<td><?php echo (int)$s['year']; ?></td>
							<td><?php echo formatScore($s['min_score']); ?></td>
							<td><?php echo formatNumber($s['quota']); ?></td>
							<td>
								<a class="btn btn-success" href="?edit=<?php echo $s['id']; ?>">Sửa</a>
								<form method="POST" style="display:inline" onsubmit="return confirm('Xóa bản ghi này?')">
									<input type="hidden" name="action" value="delete">
									<input type="hidden" name="id" value="<?php echo $s['id']; ?>">
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
