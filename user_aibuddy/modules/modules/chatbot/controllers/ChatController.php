<?php
// modules/chatbot/controllers/ChatController.php

// 1. Load các thành phần phụ thuộc
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/GeminiService.php';
require_once __DIR__ . '/../models/ChatSession.php';
require_once __DIR__ . '/../models/ChatMessage.php';

class ChatController {
    private $pdo;
    private $aiService;
    private $sessionModel;
    private $messageModel;

    public function __construct() {
        global $pdo; // Lấy biến kết nối từ config/database.php
        $this->pdo = $pdo;

        // Load cấu hình và khởi tạo Service
        $config = require __DIR__ . '/../../../config/services.php';
        $this->aiService = new GeminiService($config['gemini']);
        
        // Khởi tạo Models
        $this->sessionModel = new ChatSession($pdo);
        $this->messageModel = new ChatMessage($pdo);
    }

    /**
     * Xử lý chính: Gửi tin nhắn và nhận phản hồi
     */
    public function sendMessage($userId, $personaId, $topicId, $message, $sessionId = null, $imageBase64 = null) {
        // Bước 1: Nếu chưa có Session ID, tạo mới

        $savedImagePath = null;
        if ($imageBase64) {
            $savedImagePath = $this->saveImageToDisk($imageBase64);
        }

        if (!$sessionId) {
            $title = "New Conversation"; // Tạm thời đặt tên mặc định
            $sessionId = $this->sessionModel->create($userId, $personaId, $topicId, $title);
        }

        // Bước 2: Lưu tin nhắn của User vào DB
        // (Nếu có ảnh, logic lưu ảnh vào server nên được xử lý riêng, ở đây ta tạm truyền null hoặc path nếu đã xử lý)
        $this->messageModel->save($sessionId, 'User', $message, $savedImagePath);

        // Bước 3: Xây dựng ngữ cảnh (Context) để AI "nhớ"
        // Lấy Prompt hệ thống của Persona (VD: "Bạn là một người bạn thân...")
        $systemPrompt = $this->getPersonaSystemPrompt($personaId);
        
        // Lấy lịch sử chat gần đây (Memory)
        $historyContext = $this->messageModel->getRecentContext($userId, 20);

        // Ghép thành Prompt hoàn chỉnh gửi cho Gemini
        $finalPrompt = "System Instruction: $systemPrompt\n" .
                        "IMPORTANT: Always respond in natural, fluent English.\n\n" . // Thêm dòng này
                        "--- Memory Stream ---\n$historyContext\n" . 
                        "---------------------\n" .
                        "User Input: $message\n" .
                        "Response:";

        // Bước 4: Gọi AI Service
        try {
            $promptToSend = empty($message) ? "Describe this image or answer user's intent about it." : $finalPrompt;
            
            $aiResponse = $this->aiService->generateContent($promptToSend, $imageBase64);
        } catch (Exception $e) {
            // Nếu AI lỗi, trả về thông báo lỗi nhưng không lưu vào DB
            return [
                'status' => 500,
                'message' => $e->getMessage()
            ];
        }

        // Bước 5: Lưu phản hồi của AI vào DB
        $this->messageModel->save($sessionId, 'AI', $aiResponse);

        // Bước 6: Trả kết quả về cho Frontend
        return [
            'status' => 200,
            'data' => [
                'session_id' => $sessionId,
                'response' => $aiResponse
            ]
        ];
    }

    /**
     * API: Lấy danh sách lịch sử chat bên trái
     */
    public function getUserHistory($userId) {
        return $this->sessionModel->getAllByUser($userId);
    }

    /**
     * API: Lấy chi tiết tin nhắn của 1 đoạn chat (khi click vào lịch sử)
     */
    public function getSessionMessages($sessionId, $userId) {
        // Kiểm tra quyền sở hữu session
        $session = $this->sessionModel->getOne($sessionId, $userId);
        if (!$session) {
            return ['status' => 403, 'message' => 'Unauthorized or Not Found'];
        }

        $messages = $this->messageModel->getBySessionId($sessionId);
        
        return [
            'status' => 200,
            'data' => [
                'session_info' => $session,
                'messages' => $messages
            ]
        ];
    }

    /**
     * API: Xóa đoạn chat
     */
    public function deleteSession($sessionId, $userId) {
        $success = $this->sessionModel->delete($sessionId, $userId);
        return [
            'status' => $success ? 200 : 404,
            'message' => $success ? 'Deleted successfully' : 'Delete failed'
        ];
    }

    /**
     * API: Đổi tên đoạn chat
     */
    public function renameSession($sessionId, $userId, $newTitle) {
        $success = $this->sessionModel->rename($sessionId, $userId, $newTitle);
        return [
            'status' => $success ? 200 : 400,
            'message' => $success ? 'Renamed successfully' : 'Update failed'
        ];
    }

    /**
     * Helper: Lấy System Prompt từ PersonaID
     * (Viết nhanh SQL tại đây vì chưa có PersonaModel)
     */
    private function getPersonaSystemPrompt($personaId) {
        $stmt = $this->pdo->prepare("SELECT SystemPrompt FROM Personas WHERE PersonaID = ?");
        $stmt->execute([$personaId]);
        $row = $stmt->fetch();
        return $row ? $row['SystemPrompt'] : "You are a helpful AI assistant.";
    }
    /**
     * Khởi tạo đoạn chat theo chủ đề (AI nói trước)
     */
    public function initChatWithTopic($userId, $personaId, $topicId) {
        // 1. Tạo session mới
        // Lấy tên Topic để đặt tên Session (Tạm thời hardcode hoặc lấy từ DB nếu có Model Topic)
        $sessionTitle = "Chat about Topic " . $topicId;
        $sessionId = $this->sessionModel->create($userId, $personaId, $topicId, $sessionTitle);

        // 2. Lấy System Prompt
        $systemPrompt = $this->getPersonaSystemPrompt($personaId);

        // 3. Tạo Prompt để AI chủ động chào
        $prompt = "System: $systemPrompt\n" .
                  "Task: The user selected Topic ID $topicId. " .
                  "Proactively start the conversation with a short, engaging greeting or question related to this topic. " .
                  "Do not wait for the user. Greeting in English.";

        // 4. Gọi AI
        try {
            $aiGreeting = $this->aiService->generateContent($prompt);
        } catch (Exception $e) {
            $aiGreeting = "Hello! I'm ready to chat about this topic.";
        }

        // 5. Lưu tin nhắn AI (User chưa nói gì cả)
        $this->messageModel->save($sessionId, 'AI', $aiGreeting);

        return [
            'status' => 200,
            'data' => [
                'session_id' => $sessionId,
                'response' => $aiGreeting
            ]
        ];
    }
    // Thêm hàm này vào trong class ChatController
    private function saveImageToDisk($base64String) {
        if (!$base64String) return null;

        // Tách header base64 (ví dụ: "data:image/png;base64,")
        $parts = explode(',', $base64String);
        $data = base64_decode(end($parts));
        
        if (!$data) return null;

        // Tạo tên file ngẫu nhiên
        $fileName = 'img_' . time() . '_' . rand(1000,9999) . '.jpg';
        
        // Đường dẫn file hệ thống (để lưu)
        $uploadDir = __DIR__ . '/../../../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        file_put_contents($uploadDir . $fileName, $data);
        
        // Trả về đường dẫn web (để lưu vào DB)
        return 'public/uploads/' . $fileName;
    }

    // --- THÊM MỚI: Lấy danh sách Persona ---
    public function getPersonas() {
        // Chỉ lấy các cột cần thiết để hiển thị
        $stmt = $this->pdo->query("SELECT PersonaID, PersonaName, Description, Icon, IsPremium FROM Personas");
        return $stmt->fetchAll();
    }

    // --- THÊM MỚI: Lấy danh sách Topic ---
    public function getTopics() {
        $stmt = $this->pdo->query("SELECT TopicID, TopicName FROM Topics");
        return $stmt->fetchAll();
    }
}
?>