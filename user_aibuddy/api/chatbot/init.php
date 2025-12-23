<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../modules/chatbot/controllers/ChatController.php';

$input = json_decode(file_get_contents('php://input'), true);

try {
    $controller = new ChatController();
    $response = $controller->initChatWithTopic($input['user_id'], $input['persona_id'], $input['topic_id']);
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}
?>