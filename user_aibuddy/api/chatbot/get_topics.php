<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../modules/chatbot/controllers/ChatController.php';

try {
    $controller = new ChatController();
    $data = $controller->getTopics(); // Hàm này đã được sửa ở Bước 1
    echo json_encode(['status' => 200, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}
?>