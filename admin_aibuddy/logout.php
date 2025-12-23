<?php
// 1. Khởi động session để biết đang xử lý phiên làm việc nào
session_start();

// 2. Xóa sạch các biến đã lưu (User, Role, ID...)
session_unset(); 

// 3. Hủy hoàn toàn session trên server
session_destroy();

// 4. Chuyển hướng người dùng về trang đăng nhập
header("Location: login.php");
exit();
?>