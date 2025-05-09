<?php
$host = 'localhost';
$dbname = 'demo4';
$username = 'demo4'; // Thay bằng username MySQL của bạn
$password = 'thuong'; // Thay bằng password MySQL của bạn

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET CHARACTER SET utf8");
} catch (PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}
?>