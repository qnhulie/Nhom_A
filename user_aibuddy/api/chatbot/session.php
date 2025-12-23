<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../modules/chatbot/controllers/ChatController.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$controller = new ChatController();

if ($method === 'DELETE') {
    // Xóa
    echo json_encode($controller->deleteSession($input['session_id'], $input['user_id']));
} elseif ($method === 'PUT') {
    // Đổi tên
    echo json_encode($controller->renameSession($input['session_id'], $input['user_id'], $input['title']));
}
?>