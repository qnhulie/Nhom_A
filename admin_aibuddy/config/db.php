<?php
// FILE: config/db.php
// Lưu ý: Không được có khoảng trắng nào trước chữ <?php ở dòng 1

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
?>