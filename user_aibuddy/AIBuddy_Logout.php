<?php
session_start(); // Khởi động session để có thể hủy nó

// Xóa tất cả các biến session
$_SESSION = array();

// Hủy session
session_destroy();

// Chuyển hướng về trang đăng nhập
header("Location: AIBuddy_SignIn.php");
exit();
?>