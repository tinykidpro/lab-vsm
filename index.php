<?php
session_start();

// Kiểm tra trạng thái đăng nhập
if (isset($_SESSION['user_id'])) {
    // Nếu đã đăng nhập, chuyển hướng đến dashboard
    header("Location: dashboard.php");
} else {
    // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
    header("Location: login.php");
}
exit();
?>