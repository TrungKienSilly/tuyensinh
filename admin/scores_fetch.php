<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$pdo = getDBConnection();

// Load universities and majors
$universities = $pdo->query("SELECT id, name FROM universities ORDER BY name")->fetchAll();

$logs = [];
$summary = [
    'created_majors' => 0,
    'inserted' => 0,
    'updated' => 0,
    'skipped' => 0,
    'errors' => 0,
];

function httpFetch($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; AdmissionsBot/1.0)',
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    $html = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) { throw new Exception('HTTP error: ' . $err); }
    return $html;
}

function tryParseTable($html) {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    // Chọn bảng lớn nhất theo số dòng
    $tables = $xpath->query('//table');
    $best = null; $bestRows = 0;
    foreach ($tables as $table) {
        $rows = $xpath->query('.//tr', $table)->length;
        if ($rows > $bestRows) { $best = $table; $bestRows = $rows; }
    }
    if (!$best) return [];

    $rows = [];
    foreach ($xpath->query('.//tr', $best) as $tr) {
        $cells = [];
        foreach ($xpath->query('./th|./td', $tr) as $td) {
            $cells[] = trim(preg_replace('/\s+/', ' ', $td->textContent));
        }
        if (!empty(array_filter($cells))) $rows[] = $cells;
    }
    return $rows;
}

function normalizeColumn($name) {
    $n = mb_strtolower($name, 'UTF-8');
    $n = str_replace(['điểm chuẩn','điểm','điểm trúng tuyển'], 'score', $n);
    $n = str_replace(['ngành','tên ngành'], 'major', $n);
    $n = str_replace(['mã ngành','mã','code'], 'code', $n);
    $n = str_replace(['khối','tổ hợp','tổ hợp môn'], 'block', $n);
    $n = str_replace(['chỉ tiêu','quota','ct'], 'quota', $n);
    return $n;
}

function extractMajorCodeFromText($text) {
    // 1) Pattern: "106 - Khoa học Máy tính" hoặc "106 Khoa học Máy tính"
    if (preg_match('/^(\d{2,6})\s*-?\s*(.+)$/u', trim($text), $m)) {
        return [$m[1], trim($m[2])];
    }
    // 2) Pattern: "Khoa học Máy tính (106)"
    if (preg_match('/^(.*)\((\d{2,6})\)$/u', trim($text), $m)) {
        return [$m[2], trim($m[1])];
    }
    // 3) Pattern: chứa số ở cuối/đầu câu
    if (preg_match('/(\d{2,6})/u', $text, $m)) {
        return [$m[1], trim(preg_replace('/\(?' . preg_quote($m[1], '/') . '\)?/u', '', $text))];
    }
    return ['', trim($text)];
}

function makeMajorCode($code, $name) {
    $c = trim($code);
    if ($c !== '') return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $c));
    [$guess, $cleanName] = extractMajorCodeFromText($name);
    if ($guess !== '') return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $guess));
    $base = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name)));
    if ($base === '') $base = 'NOCODE';
    return substr($base, 0, 20);
}

if ($_POST) {
    $university_id = (int)($_POST['university_id'] ?? 0);
    $year = (int)($_POST['year'] ?? date('Y'));
    $url = trim($_POST['url'] ?? '');

    try {
        if (!$university_id || !$year || empty($url)) {
            throw new Exception('Vui lòng chọn trường, năm và nhập URL nguồn');
        }

        $html = httpFetch($url);
        $table = tryParseTable($html);
        if (count($table) < 2) throw new Exception('Không tìm thấy bảng dữ liệu hợp lệ ở URL đã nhập.');

        // Header mapping
        $headers = array_map('normalizeColumn', $table[0]);
        $idx = [
            'code' => -1,
            'major' => -1,
            'block' => -1,
            'score' => -1,
            'quota' => -1,
        ];
        foreach ($headers as $i => $h) {
            foreach ($idx as $k => $v) {
                if (strpos($h, $k) !== false && $idx[$k] === -1) $idx[$k] = $i;
            }
        }
        if ($idx['major'] === -1 && $idx['code'] === -1) {
            throw new Exception('Không tìm thấy cột Tên ngành / Mã ngành trong bảng.');
        }
        if ($idx['score'] === -1) {
            throw new Exception('Không tìm thấy cột Điểm chuẩn trong bảng.');
        }

        // Chuẩn bị truy vấn
        $findMajor = $pdo->prepare('SELECT id FROM majors WHERE university_id = ? AND (code = ? OR name = ?) LIMIT 1');
        $insertMajor = $pdo->prepare('INSERT INTO majors (university_id, code, name) VALUES (?, ?, ?)');

        $upsertScore = $pdo->prepare('INSERT INTO admission_scores (major_id, year, block, min_score, quota, note) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE min_score = VALUES(min_score), quota = VALUES(quota), note = VALUES(note)');

        // Duyệt dữ liệu
        for ($r = 1; $r < count($table); $r++) {
            $row = $table[$r];
            $rawCode = $idx['code'] !== -1 ? ($row[$idx['code']] ?? '') : '';
            $majorText = $idx['major'] !== -1 ? ($row[$idx['major']] ?? '') : '';
            $block = $idx['block'] !== -1 ? ($row[$idx['block']] ?? '') : 'A00';
            $scoreStr = $row[$idx['score']] ?? '';
            $quotaStr = $idx['quota'] !== -1 ? ($row[$idx['quota']] ?? '0') : '0';

            // Nếu thiếu mã nhưng tên ngành chứa mã, tách ra
            if ($rawCode === '' && $majorText !== '') {
                [$guessed, $cleanMajor] = extractMajorCodeFromText($majorText);
                if ($guessed !== '') { $rawCode = $guessed; $majorText = $cleanMajor; }
            }

            // Parse số
            $score = floatval(str_replace([','], ['.'], preg_replace('/[^0-9,\.]/', '', $scoreStr)));
            $quota = intval(preg_replace('/[^0-9]/', '', $quotaStr));
            if ($score <= 0) { $summary['skipped']++; $logs[] = 'Bỏ qua: điểm không hợp lệ ở dòng '.($r+1); continue; }

            // Sinh mã ngành an toàn nếu thiếu
            $safeCode = makeMajorCode($rawCode, $majorText);

            // Tìm/ tạo ngành
            $findMajor->execute([$university_id, $safeCode, $majorText ?: $safeCode]);
            $major = $findMajor->fetch();
            if (!$major) {
                $insertMajor->execute([$university_id, $safeCode, $majorText ?: $safeCode]);
                $majorId = (int)$pdo->lastInsertId();
                $summary['created_majors']++;
            } else {
                $majorId = (int)$major['id'];
            }

            // Upsert điểm
            $upsertScore->execute([$majorId, $year, $block, $score, $quota, 'Imported from '.$url]);
            if ($upsertScore->rowCount() === 1) $summary['inserted']++; else $summary['updated']++;
        }
        $logs[] = 'Hoàn tất tải dữ liệu.';
    } catch (Exception $e) {
        $summary['errors']++;
        $logs[] = 'Lỗi: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tải dữ liệu điểm chuẩn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-header{background:linear-gradient(135deg,#2c3e50 0%,#34495e 100%);color:#fff;padding:1rem 0;margin-bottom:2rem}
        .container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
        .form-section{background:#fff;padding:2rem;border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,.1);margin-bottom:2rem}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem}
        .form-group{margin-bottom:1rem}
        .btn{padding:.75rem 1.5rem;border:none;border-radius:5px;cursor:pointer;font-size:1rem;font-weight:500;text-decoration:none;display:inline-block;text-align:center;transition:all .3s ease}
        .btn-primary{background:#2c3e50;color:#fff}
        .btn-secondary{background:#6c757d;color:#fff}
        .btn-success{background:#28a745;color:#fff}
        .btn-danger{background:#dc3545;color:#fff}
        .log-box{background:#0f172a;color:#e2e8f0;padding:1rem;border-radius:8px;max-height:300px;overflow:auto;font-family:monospace}
        .stat{display:inline-block;margin-right:1rem;color:#334155}
        .back-btn{position:fixed;top:10px;left:10px;z-index:1000}
    </style>
</head>
<body>
    <a href="scores.php" class="btn btn-secondary back-btn">← Quay lại</a>
    <header class="admin-header">
        <div class="container">
            <h1>⬇ Tải dữ liệu điểm chuẩn</h1>
            <p>Nhập URL trang chính thức của trường có bảng điểm chuẩn. Hệ thống sẽ cố gắng nhận diện và nhập dữ liệu.</p>
        </div>
    </header>

    <div class="container">
        <div class="form-section">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Trường</label>
                        <select name="university_id" required>
                            <option value="">-- Chọn trường --</option>
                            <?php foreach ($universities as $u): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo escape($u['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Năm</label>
                        <input type="number" name="year" value="<?php echo date('Y'); ?>" min="2000" max="<?php echo date('Y')+1; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>URL nguồn (trang chính thức của trường)</label>
                    <input type="url" name="url" placeholder="https://example.edu.vn/diem-chuan-2024" required>
                </div>
                <button class="btn btn-success" type="submit">Bắt đầu tải</button>
                <a href="scores.php" class="btn btn-secondary">Hủy</a>
            </form>
        </div>

        <?php if (!empty($logs)): ?>
            <div class="form-section">
                <h2>Kết quả</h2>
                <div class="stat">Ngành tạo mới: <strong><?php echo $summary['created_majors']; ?></strong></div>
                <div class="stat">Bản ghi thêm: <strong><?php echo $summary['inserted']; ?></strong></div>
                <div class="stat">Bản ghi cập nhật: <strong><?php echo $summary['updated']; ?></strong></div>
                <div class="stat">Bỏ qua: <strong><?php echo $summary['skipped']; ?></strong></div>
                <div class="stat">Lỗi: <strong><?php echo $summary['errors']; ?></strong></div>
                <div class="log-box" style="margin-top:1rem;">
                    <?php foreach ($logs as $line): ?>
                        <div>• <?php echo escape($line); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
