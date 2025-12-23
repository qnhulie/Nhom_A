<?php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "aibuddy_database"; 

// Tạo kết nối
$conn = new mysqli($host, $user, $pass, $dbname);

// Kiểm tra nếu kết nối bị lỗi thì dừng và báo ngay
if ($conn->connect_error) {
    die("LỖI KẾT NỐI DATABASE: " . $conn->connect_error);
}

// Thiết lập font chữ tiếng Việt
$conn->set_charset("utf8mb4");
define('BASE_URL', '/admin_aibuddy/');
$conn->set_charset("utf8mb4");
// Kiểm tra: Nếu chưa có BASE_URL thì mới định nghĩa
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/user_aibuddy/'); // Thay đường dẫn đúng của bạn vào đây
}
?>