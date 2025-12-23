<?php
// user_aibuddy/config/db_pdo.php

$host = 'localhost';
$dbname = 'aibuddy_database';
$username = 'root';
$password = ''; // Mặc định XAMPP là rỗng

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Thiết lập chế độ lỗi để dễ debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Nếu kết nối lỗi, trả về JSON để Frontend biết thay vì chết trang
    header('Content-Type: application/json');
    die(json_encode([
        'status' => 500, 
        'message' => 'Database Connection Error: ' . $e->getMessage()
    ]));
}
?>