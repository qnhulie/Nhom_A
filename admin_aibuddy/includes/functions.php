<?php
// ^^^ QUAN TRỌNG: DÒNG TRÊN PHẢI LÀ <?php (KHÔNG ĐƯỢC CÓ KHOẢNG TRẮNG NÀO TRƯỚC NÓ)

// 1. Kiểm tra xem file này đã được load chưa (Debug)
// Nếu bạn thấy dòng chữ này hiện lên trên cùng trang web nghĩa là file đã kết nối đúng.
// Sau khi chạy được thì xóa dòng echo này đi.
// echo "";

function getCount($conn, $sql) {
    if (!isset($conn)) return 0;
    
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['c'] ?? 0;
    }
    return 0;
}

function formatCurrency($amount) {
    if (!$amount) return "0 đ";
    return number_format($amount, 0, ',', '.') . ' đ';
}
?>