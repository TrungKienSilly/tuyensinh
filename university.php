<?php
require_once 'config/database.php';

$university_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$university_id) {
    header('Location: index.php');
    exit;
}

$pdo = getDBConnection();

// Lấy thông tin trường đại học
$university_query = "SELECT * FROM universities WHERE id = :id";
$university_stmt = $pdo->prepare($university_query);
$university_stmt->execute([':id' => $university_id]);
$university = $university_stmt->fetch();

if (!$university) {
    header('Location: index.php');
    exit;
}

// Lấy danh sách ngành đào tạo
$majors_query = "
    SELECT 
        m.*,
        COUNT(a.id) as score_count,
        AVG(a.min_score) as avg_score,
        MIN(a.min_score) as min_score,
        MAX(a.min_score) as max_score
    FROM majors m
    LEFT JOIN admission_scores a ON m.id = a.major_id AND a.year = YEAR(CURDATE())
    WHERE m.university_id = :university_id
    GROUP BY m.id
    ORDER BY m.name
";
$majors_stmt = $pdo->prepare($majors_query);
$majors_stmt->execute([':university_id' => $university_id]);
$majors = $majors_stmt->fetchAll();

// Bảo vệ xem điểm chi tiết -> yêu cầu đăng nhập học sinh
$selected_major_id = isset($_GET['major_id']) ? (int)$_GET['major_id'] : 0;
if ($selected_major_id && (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true)) {
    $redirectUrl = 'student/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']);
    header('Location: ' . $redirectUrl);
    exit;
}

// Lấy điểm chuẩn chi tiết cho ngành được chọn
$scores = [];
if ($selected_major_id) {
    $scores_query = "
        SELECT * FROM admission_scores 
        WHERE major_id = :major_id 
        ORDER BY year DESC, block
    ";
    $scores_stmt = $pdo->prepare($scores_query);
    $scores_stmt->execute([':major_id' => $selected_major_id]);
    $scores = $scores_stmt->fetchAll();
}

$page_title = escape($university['name']) . ' - Thông tin tuyển sinh';
include 'includes/header.php';
?>
        <!-- University Info -->
        <div class="university-card" style="margin-bottom: 2rem;">
            <div class="university-header">
                <div class="university-name"><?php echo escape($university['name']); ?></div>
                <div class="university-code"><?php echo escape($university['code']); ?></div>
            </div>
            <div class="university-body">
                <div class="university-info">
                    <div class="info-item">
                        <span class="info-label">📍 Địa chỉ:</span>
                        <span class="info-value"><?php echo escape($university['address']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">🏛️ Loại trường:</span>
                        <span class="info-value"><?php echo escape($university['university_type']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">📅 Thành lập:</span>
                        <span class="info-value"><?php echo escape($university['established_year']); ?></span>
                    </div>
                    <?php if ($university['website']): ?>
                    <div class="info-item">
                        <span class="info-label">🌐 Website:</span>
                        <span class="info-value">
                            <a href="<?php echo escape($university['website']); ?>" target="_blank" style="color: #667eea;">
                                <?php echo escape($university['website']); ?>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if ($university['phone']): ?>
                    <div class="info-item">
                        <span class="info-label">📞 Điện thoại:</span>
                        <span class="info-value"><?php echo escape($university['phone']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($university['email']): ?>
                    <div class="info-item">
                        <span class="info-label">✉️ Email:</span>
                        <span class="info-value"><?php echo escape($university['email']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($university['description']): ?>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                    <h3 style="margin-bottom: 0.5rem; color: #333;">Giới thiệu</h3>
                    <p style="line-height: 1.6; color: #666;">&nbsp;<?php echo nl2br(escape($university['description'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Majors Section -->
        <div class="major-list">
            <div style="padding: 1.5rem; background: #f8f9fa; border-bottom: 1px solid #eee;">
                <h2 style="margin: 0; color: #333;">📚 Danh sách ngành đào tạo (<?php echo count($majors); ?> ngành)</h2>
            </div>
            
            <?php if (empty($majors)): ?>
                <div class="alert alert-info" style="margin: 1rem;">
                    <strong>Chưa có thông tin ngành đào tạo!</strong>
                </div>
            <?php else: ?>
                <?php foreach ($majors as $major): ?>
                    <div class="major-item">
                        <div class="major-header">
                            <div class="major-name"><?php echo escape($major['name']); ?></div>
                            <div class="major-code"><?php echo escape($major['code']); ?></div>
                        </div>
                        <div class="major-info">
                            <div>
                                <strong>Trình độ:</strong> <?php echo escape($major['training_level']); ?>
                            </div>
                            <div>
                                <strong>Thời gian:</strong> <?php echo escape($major['duration_years']); ?> năm
                            </div>
                            <?php if ($major['avg_score']): ?>
                            <div>
                                <strong>Điểm TB:</strong> 
                                <span class="score-medium"><?php echo formatScore($major['avg_score']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div>
                                <strong>Điểm chuẩn:</strong>
                                <?php if ($major['min_score'] && $major['max_score']): ?>
                                    <?php echo formatScore($major['min_score']); ?> - <?php echo formatScore($major['max_score']); ?>
                                <?php else: ?>
                                    Chưa có dữ liệu
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="margin-top: 1rem;">
                            <?php $detailPath = 'university.php?id='.$university_id.'&major_id='.$major['id']; ?>
                            <?php if (!empty($_SESSION['student_logged_in'])): ?>
                                <a href="<?php echo $detailPath; ?>" class="btn btn-secondary" style="font-size: 0.9rem;">Xem điểm chuẩn chi tiết</a>
                            <?php else: ?>
                                <a href="student/login.php?redirect=<?php echo urlencode($detailPath); ?>" class="btn btn-secondary" style="font-size: 0.9rem;">Xem điểm chuẩn chi tiết</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Scores Detail -->
        <?php if ($selected_major_id && !empty($scores)): ?>
            <div class="major-list" style="margin-top: 2rem;">
                <div style="padding: 1.5rem; background: #f8f9fa; border-bottom: 1px solid #eee;">
                    <h2 style="margin: 0; color: #333;">📊 Điểm chuẩn chi tiết</h2>
                </div>
                
                <table class="score-table">
                    <thead>
                        <tr>
                            <th>Năm</th>
                            <th>Khối</th>
                            <th>Điểm chuẩn</th>
                            <th>Chỉ tiêu</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scores as $score): ?>
                            <tr>
                                <td><?php echo escape($score['year']); ?></td>
                                <td>
                                    <span class="major-code"><?php echo escape($score['block']); ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $score_value = (float)$score['min_score'];
                                    if ($score_value >= 25) {
                                        echo '<span class="score-high">' . formatScore($score['min_score']) . '</span>';
                                    } elseif ($score_value >= 20) {
                                        echo '<span class="score-medium">' . formatScore($score['min_score']) . '</span>';
                                    } else {
                                        echo '<span class="score-low">' . formatScore($score['min_score']) . '</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo formatNumber($score['quota']); ?></td>
                                <td><?php echo escape($score['note']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($selected_major_id): ?>
            <div class="alert alert-info" style="margin-top: 2rem;">
                <strong>Chưa có dữ liệu điểm chuẩn cho ngành này!</strong>
            </div>
        <?php endif; ?>

<?php include 'includes/footer.php'; ?>

