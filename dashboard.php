<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách case
$stmt = $pdo->query("SELECT * FROM cases");
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy điểm số của người dùng cho từng case
$user_scores = [];
$stmt = $pdo->prepare("SELECT case_id, score FROM user_case_scores WHERE user_id = ?");
$stmt->execute([$user_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $user_scores[$row['case_id']] = $row['score'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Hệ thống SIM LAB</title>
    <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Hệ thống VSM - SIM LAB</h1>
        <div class="user-info">
            <p>Xin chào, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></p>
            <p class="score">Tổng điểm: <span id="scoreDisplay"><?php echo $_SESSION['score']; ?></span></p>
        </div>
        <h2>Danh sách Case</h2>
        <div class="case-list">
            <?php foreach ($cases as $case): ?>
                <div class="case-item">
                    <div>
                        <h3><?php echo htmlspecialchars($case['case_name']); ?></h3>
                        <p><?php echo htmlspecialchars($case['case_description']); ?></p>
                        <p class="case-score">
                            Điểm: 
                            <span class="achieved-score"><?php echo isset($user_scores[$case['id']]) ? $user_scores[$case['id']] : 0; ?></span>
                            / 
                            <span class="max-score"><?php echo $case['max_score']; ?></span>
                        </p>
                    </div>
                    <button onclick="window.location.href='<?php echo $case['case_url']; ?>'">Bắt đầu</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Đăng xuất</button>
    </div>

    <!-- Footer bản quyền -->
    <footer class="footer">
        <p>&copy; 2025 Công ty Vệ Sĩ Mạng. Tất cả quyền được bảo lưu.</p>
        <p>Phát triển bởi <a href="https://vesimang.org" target="_blank">VSM TEAM</a> | Liên hệ: <a href="mailto:support@vesimang.org">support@vesimang.org</a></p>
    </footer>
</body>
</html>