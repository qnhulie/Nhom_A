<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../modules/chatbot/controllers/ChatController.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['status' => 400, 'message' => 'Invalid JSON']);
    exit;
}

try {
    $controller = new ChatController();
    // input: user_id, persona_id, topic_id, message, session_id (opt), image (opt)
    $response = $controller->sendMessage(
        $input['user_id'], 
        $input['persona_id'], 
        $input['topic_id'], 
        $input['message'] ?? '', 
        $input['session_id'] ?? null,
        $input['image'] ?? null
    );
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}
?>