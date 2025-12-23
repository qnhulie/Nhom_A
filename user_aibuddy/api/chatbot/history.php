<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../modules/chatbot/controllers/ChatController.php';

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['status' => 400, 'message' => 'Missing user_id']);
    exit;
}

try {
    $controller = new ChatController();
    $data = $controller->getUserHistory($userId);
    echo json_encode(['status' => 200, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}
?>