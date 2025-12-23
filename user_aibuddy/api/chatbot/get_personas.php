<?php
// user_aibuddy/api/chatbot/get_personas.php

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
session_start();

// Điều chỉnh đường dẫn đến file config/db.php
require_once '../../config/db.php'; 

$response = ['status' => 400, 'data' => [], 'user_plan' => 1];

try {
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['userid'])) {
        $userId = 0; 
    } else {
        $userId = $_SESSION['userid'];
    }

    // --- 1. LOGIC XÁC ĐỊNH GÓI DỰA TRÊN ĐƠN HÀNG MỚI NHẤT ---
    // Mặc định là Free (PlanID = 1)
    $currentPlanId = 1;

    if ($userId > 0) {
        // QUERY: Lấy đơn hàng mới nhất (OrderID lớn nhất), không quan tâm trạng thái lúc query
        $sqlOrder = "SELECT PlanID, OrderStatus 
                     FROM userorder 
                     WHERE UserID = ? 
                     ORDER BY OrderID DESC 
                     LIMIT 1";
                    
        $stmt = $conn->prepare($sqlOrder);
        
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            
            if ($res) {
                // LOGIC QUAN TRỌNG: Kiểm tra trạng thái đơn hàng mới nhất
                if ($res['OrderStatus'] === 'Completed') {
                    // Nếu thành công -> Kích hoạt gói đó
                    $currentPlanId = (int)$res['PlanID'];
                } else {
                    // Nếu là 'Cancelled', 'Pending', 'Failed'... -> Quay về Free
                    $currentPlanId = 1;
                }
            }
            // Nếu không tìm thấy đơn hàng nào ($res = null), mặc định vẫn là Free ($currentPlanId = 1)
            $stmt->close();
        }
    }

    // --- 2. XÁC ĐỊNH QUYỀN VIP ---
    // PlanID >= 2 (Essential hoặc Premium) là VIP
    $isVipUser = ($currentPlanId >= 2);

    // --- 3. LẤY DANH SÁCH PERSONA & XỬ LÝ KHÓA ---
    $sql = "SELECT PersonaID, PersonaName, Description, Icon, IsPremium FROM persona";
    $result = $conn->query($sql);

    $personas = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Xử lý icon mặc định
            if (empty($row['Icon'])) $row['Icon'] = '🤖';

            // --- LOGIC KHÓA ---
            $isLocked = false;
            
            // Nếu Persona là Premium (IsPremium = 1) 
            // VÀ User KHÔNG PHẢI VIP -> KHÓA
            if ($row['IsPremium'] == 1 && !$isVipUser) {
                $isLocked = true;
            }

            $row['is_locked'] = $isLocked; 
            $personas[] = $row;
        }
    }

    // Trả về kết quả
    echo json_encode([
        'status' => 200, 
        'data' => $personas,
        'user_plan' => $currentPlanId, // PlanID thực tế đang áp dụng
        'is_vip' => $isVipUser
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}
?>