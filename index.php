<?php
require_once 'config/database.php';

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$province = isset($_GET['province']) ? trim($_GET['province']) : '';
$university_type = isset($_GET['university_type']) ? trim($_GET['university_type']) : '';

// Xây dựng query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(u.name LIKE :search_name OR u.code LIKE :search_code)";
    $params[':search_name'] = "%$search%";
    $params[':search_code'] = "%$search%";
}

if (!empty($province)) {
    $where_conditions[] = "u.province = :province";
    $params[':province'] = $province;
}

if (!empty($university_type)) {
    $where_conditions[] = "u.university_type = :university_type";
    $params[':university_type'] = $university_type;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Lấy danh sách tỉnh thành
$pdo = getDBConnection();
$provinces_query = "SELECT DISTINCT province FROM universities ORDER BY province";
$provinces = $pdo->query($provinces_query)->fetchAll();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Đếm tổng số trường
$count_query = "SELECT COUNT(*) as total FROM universities u $where_clause";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_universities = $count_stmt->fetch()['total'];
$total_pages = ceil($total_universities / $limit);

// Lấy danh sách trường với phân trang
$query = "
    SELECT 
        u.*,
        COUNT(m.id) as major_count,
        AVG(a.min_score) as avg_score
    FROM universities u
    LEFT JOIN majors m ON u.id = m.university_id
    LEFT JOIN admission_scores a ON m.id = a.major_id AND a.year = YEAR(CURDATE())
    $where_clause
    GROUP BY u.id
    ORDER BY u.name
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$universities = $stmt->fetchAll();

$page_title = 'Hệ thống quản lý trường đại học tuyển sinh';
include 'includes/header.php';
?>
        <!-- Search Section -->
        <section class="search-section">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label for="search">Tìm kiếm trường:</label>
                    <input type="text" id="search" name="search" value="<?php echo escape($search); ?>" placeholder="Nhập tên hoặc mã trường...">
                </div>
                <div class="form-group">
                    <label for="province">Tỉnh/Thành phố:</label>
                    <select id="province" name="province">
                        <option value="">Tất cả tỉnh/thành phố</option>
                        <?php foreach ($provinces as $prov): ?>
                            <option value="<?php echo escape($prov['province']); ?>" 
                                    <?php echo $province === $prov['province'] ? 'selected' : ''; ?>
                            ><?php echo escape($prov['province']); ?></option>
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
                <button type="submit" class="btn">🔍 Tìm kiếm</button>
            </form>
        </section>

        <!-- Results Info -->
        <div style="margin-bottom: 1rem; color: #666;">
            <strong>Tìm thấy <?php echo formatNumber($total_universities); ?> trường đại học</strong>
            <?php if (!empty($search) || !empty($province) || !empty($university_type)): ?>
                <a href="index.php" style="margin-left: 1rem; color: #667eea;">Xóa bộ lọc</a>
            <?php endif; ?>
        </div>

        <!-- University Grid -->
        <?php if (empty($universities)): ?>
            <div class="alert alert-info">
                <strong>Không tìm thấy trường đại học nào!</strong> Vui lòng thử lại với từ khóa khác.
            </div>
        <?php else: ?>
            <div class="university-grid">
                <?php foreach ($universities as $university): ?>
                    <div class="university-card">
                        <div class="university-header">
                            <div class="university-name"><?php echo escape($university['name']); ?></div>
                            <div class="university-code"><?php echo escape($university['code']); ?></div>
                        </div>
                        <div class="university-body">
                            <div class="university-info">
                                <div class="info-item">
                                    <span class="info-label">📍</span>
                                    <span class="info-value"><?php echo escape($university['province']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">🏛️</span>
                                    <span class="info-value"><?php echo escape($university['university_type']); ?></span>
                                </div>
                                <?php if ($university['website']): ?>
                                <div class="info-item">
                                    <span class="info-label">🌐</span>
                                    <span class="info-value">
                                        <a href="<?php echo escape($university['website']); ?>" target="_blank" style="color: #667eea;">
                                            Website
                                        </a>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="university-stats">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo formatNumber($university['major_count']); ?></div>
                                    <div class="stat-label">Ngành đào tạo</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">
                                        <?php echo $university['avg_score'] ? formatScore($university['avg_score']) : 'N/A'; ?>
                                    </div>
                                    <div class="stat-label">Điểm TB</div>
                                </div>
                            </div>
                            <div style="margin-top: 1rem; text-align: center;">
                                <a href="university.php?id=<?php echo $university['id']; ?>" class="btn">
                                    Xem chi tiết
                                </a>
                            </div>
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
