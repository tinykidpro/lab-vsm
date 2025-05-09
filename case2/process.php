function updateScore($user_id) {
    global $pdo;

    $score = 0;
    if ($_SESSION['case2']['hacker_blocked']) $score += 25; // Chặn IP hacker: 25 điểm
    if ($_SESSION['case2']['password_changed']) $score += 25; // Đổi mật khẩu mạnh: 25 điểm
    if ($_SESSION['case2']['ssh_nat_removed']) $score += 25; // Xóa rule NAT SSH: 25 điểm
    if ($_SESSION['case2']['brute_force_selected']) $score += 25; // Chọn đúng lý do Brute Force: 25 điểm

    // Chỉ cập nhật nếu điểm mới cao hơn điểm hiện tại
    $stmt = $pdo->prepare("SELECT score FROM user_case_scores WHERE user_id = ? AND case_id = 2");
    $stmt->execute([$user_id]);
    $current_score = $stmt->fetchColumn() ?: 0;

    if ($score > $current_score) {
        $stmt = $pdo->prepare("INSERT INTO user_case_scores (user_id, case_id, score) VALUES (?, 2, ?) 
                               ON DUPLICATE KEY UPDATE score = ?");
        $stmt->execute([$user_id, $score, $score]);

        // Cập nhật tổng điểm trong bảng users
        $stmt = $pdo->prepare("SELECT SUM(score) as total_score FROM user_case_scores WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_score = $stmt->fetchColumn() ?: 0;

        $stmt = $pdo->prepare("UPDATE users SET score = ? WHERE id = ?");
        $stmt->execute([$total_score, $user_id]);

        $_SESSION['score'] = $total_score;
    }
}