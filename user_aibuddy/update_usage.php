<?php
session_start();
require_once 'config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập']);
    exit;
}

$userID = $_SESSION['userid'];

// 1. Lấy số lượt hiện tại
$stmt = $conn->prepare("SELECT UsageLeft FROM users WHERE UserID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if ($res && $res['UsageLeft'] > 0) {
    $newUsage = $res['UsageLeft'] - 1;
    
    // LOGIC MỚI: 
    // Nếu còn lượt (>0) -> Status = 1 (Đang dùng)
    // Nếu hết lượt (0) -> Status = 2 (Đã hết hạn - Trial Expired)
    $newStatus = ($newUsage > 0) ? 1 : 2;

    $update = $conn->prepare("UPDATE users SET UsageLeft = ?, IsTrialActive = ? WHERE UserID = ?");
    $update->bind_param("iii", $newUsage, $newStatus, $userID);
    
    if ($update->execute()) {
        echo json_encode(['success' => true, 'newUsage' => $newUsage, 'status' => $newStatus]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi DB']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Hết lượt']);
}
?>