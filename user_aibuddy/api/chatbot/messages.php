<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../modules/chatbot/controllers/ChatController.php';

$userId = $_GET['user_id'] ?? null;
$sessionId = $_GET['session_id'] ?? null;

if (!$userId || !$sessionId) {
    echo json_encode(['status' => 400, 'message' => 'Missing params']);
    exit;
}

try {
    $controller = new ChatController();
    $response = $controller->getSessionMessages($sessionId, $userId);
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}
?>