<?php
require_once 'config/database.php';

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$province = isset($_GET['province']) ? trim($_GET['province']) : '';
$university_type = isset($_GET['university_type']) ? trim($_GET['university_type']) : '';
$major_name = isset($_GET['major_name']) ? trim($_GET['major_name']) : '';
$min_score = isset($_GET['min_score']) ? (float)$_GET['min_score'] : 0;
$max_score = isset($_GET['max_score']) ? (float)$_GET['max_score'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$pdo = getDBConnection();

// Lấy danh sách tỉnh thành
$provinces_query = "SELECT DISTINCT province FROM universities ORDER BY province";
$provinces = $pdo->query($provinces_query)->fetchAll();

// Lấy danh sách năm có dữ liệu
$years_query = "SELECT DISTINCT year FROM admission_scores ORDER BY year DESC";
$years = $pdo->query($years_query)->fetchAll();

// Xây dựng query tìm kiếm
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(u.name LIKE :search OR u.code LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($province)) {
    $where_conditions[] = "u.province = :province";
    $params[':province'] = $province;
}

if (!empty($university_type)) {
    $where_conditions[] = "u.university_type = :university_type";
    $params[':university_type'] = $university_type;
}

if (!empty($major_name)) {
    $where_conditions[] = "m.name LIKE :major_name";
    $params[':major_name'] = "%$major_name%";
}

if ($min_score > 0) {
    $where_conditions[] = "a.min_score >= :min_score";
    $params[':min_score'] = $min_score;
}

if ($max_score > 0) {
    $where_conditions[] = "a.min_score <= :max_score";
    $params[':max_score'] = $max_score;
}

$where_conditions[] = "a.year = :year";
$params[':year'] = $year;

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Đếm tổng số kết quả
$count_query = "
    SELECT COUNT(DISTINCT CONCAT(u.id, '-', m.id)) as total
    FROM universities u
    INNER JOIN majors m ON u.id = m.university_id
    INNER JOIN admission_scores a ON m.id = a.major_id
    $where_clause
";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_results = $count_stmt->fetch()['total'];
$total_pages = ceil($total_results / $limit);

// Lấy kết quả tìm kiếm
$query = "
    SELECT 
        u.id as university_id,
        u.name as university_name,
        u.code as university_code,
        u.province,
        u.university_type,
        m.id as major_id,
        m.name as major_name,
        m.code as major_code,
        a.year,
        a.block,
        a.min_score,
        a.quota
    FROM universities u
    INNER JOIN majors m ON u.id = m.university_id
    INNER JOIN admission_scores a ON m.id = a.major_id
    $where_clause
    ORDER BY a.min_score DESC, u.name, m.name
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll();

$page_title = 'Tìm kiếm nâng cao - Hệ thống quản lý trường đại học';
include 'includes/header.php';
?>
        <!-- Search Form -->
        <section class="search-section">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label for="search">Tên trường:</label>
                    <input type="text" id="search" name="search" value="<?php echo escape($search); ?>" placeholder="Nhập tên hoặc mã trường...">
                </div>
                <div class="form-group">
                    <label for="province">Tỉnh/Thành phố:</label>
                    <select id="province" name="province">
                        <option value="">Tất cả tỉnh/thành phố</option>
                        <?php foreach ($provinces as $prov): ?>
                            <option value="<?php echo escape($prov['province']); ?>" 
                                    <?php echo $province === $prov['province'] ? 'selected' : ''; ?>>
                                <?php echo escape($prov['province']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="university_type">Loại trường:</label>
                    <select id="university_type" name="university_type">
                        <option value="">Tất cả loại trường</option>
                        <option value="Công lập" <?php echo $university_type === 'Công lập' ? 'selected' : ''; ?>>Công lập</option>
                        <option value="Dân lập" <?php echo $university_type === 'Dân lập' ? 'selected' : ''; ?>>Dân lập</option>
                        <option value="Tư thục" <?php echo $university_type === 'Tư thục' ? 'selected' : ''; ?>>Tư thục</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="major_name">Tên ngành:</label>
                    <input type="text" id="major_name" name="major_name" value="<?php echo escape($major_name); ?>" placeholder="Nhập tên ngành học...">
                </div>
                <div class="form-group">
                    <label for="year">Năm tuyển sinh:</label>
                    <select id="year" name="year">
                        <?php foreach ($years as $y): ?>
                            <option value="<?php echo $y['year']; ?>" 
                                    <?php echo $year == $y['year'] ? 'selected' : ''; ?>>
                                <?php echo $y['year']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="min_score">Điểm tối thiểu:</label>
                    <input type="number" id="min_score" name="min_score" value="<?php echo $min_score; ?>" min="0" max="30" step="0.1" placeholder="0">
                </div>
                <div class="form-group">
                    <label for="max_score">Điểm tối đa:</label>
                    <input type="number" id="max_score" name="max_score" value="<?php echo $max_score; ?>" min="0" max="30" step="0.1" placeholder="30">
                </div>
                <button type="submit" class="btn">🔍 Tìm kiếm</button>
                <a href="search.php" class="btn btn-secondary">Xóa bộ lọc</a>
            </form>
        </section>

        <!-- Results Info -->
        <div style="margin-bottom: 1rem; color: #666;">
            <strong>Tìm thấy <?php echo formatNumber($total_results); ?> kết quả</strong>
            <?php if ($total_results > 0): ?>
                <span style="margin-left: 1rem;">
                    (Năm <?php echo $year; ?><?php echo $min_score > 0 || $max_score > 0 ? ', điểm từ ' . $min_score . ' đến ' . $max_score : ''; ?>)
                </span>
            <?php endif; ?>
        </div>

        <!-- Results -->
        <?php if (empty($results)): ?>
            <div class="alert alert-info">
                <strong>Không tìm thấy kết quả nào!</strong> Vui lòng thử lại với tiêu chí khác.
            </div>
        <?php else: ?>
            <div class="major-list">
                <?php foreach ($results as $result): ?>
                    <div class="major-item">
                        <div class="major-header">
                            <div class="major-name"><?php echo escape($result['major_name']); ?></div>
                            <div class="major-code"><?php echo escape($result['major_code']); ?></div>
                        </div>
                        <div class="major-info">
                            <div>
                                <strong>Trường:</strong> 
                                <a href="university.php?id=<?php echo $result['university_id']; ?>" style="color: #667eea;">
                                    <?php echo escape($result['university_name']); ?>
                                </a>
                            </div>
                            <div>
                                <strong>Mã trường:</strong> <?php echo escape($result['university_code']); ?>
                            </div>
                            <div>
                                <strong>Tỉnh/TP:</strong> <?php echo escape($result['province']); ?>
                            </div>
                            <div>
                                <strong>Loại trường:</strong> <?php echo escape($result['university_type']); ?>
                            </div>
                            <div>
                                <strong>Khối:</strong> 
                                <span class="major-code"><?php echo escape($result['block']); ?></span>
                            </div>
                            <div>
                                <strong>Điểm chuẩn:</strong>
                                <?php 
                                $score = (float)$result['min_score'];
                                if ($score >= 25) {
                                    echo '<span class="score-high">' . formatScore($result['min_score']) . '</span>';
                                } elseif ($score >= 20) {
                                    echo '<span class="score-medium">' . formatScore($result['min_score']) . '</span>';
                                } else {
                                    echo '<span class="score-low">' . formatScore($result['min_score']) . '</span>';
                                }
                                ?>
                            </div>
                            <div>
                                <strong>Chỉ tiêu:</strong> <?php echo formatNumber($result['quota']); ?>
                            </div>
                            <div>
                                <strong>Năm:</strong> <?php echo escape($result['year']); ?>
                            </div>
                        </div>
                        <div style="margin-top: 1rem;">
                            <a href="university.php?id=<?php echo $result['university_id']; ?>&major_id=<?php echo $result['major_id']; ?>" 
                               class="btn" style="font-size: 0.9rem;">
                                Xem chi tiết trường
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">« Trước</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Sau »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

<?php include 'includes/footer.php'; ?>

