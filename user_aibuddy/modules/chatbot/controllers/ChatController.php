<?php
// modules/chatbot/controllers/ChatController.php

require_once __DIR__ . '/../../../config/db_pdo.php';
require_once __DIR__ . '/../models/GeminiService.php';
require_once __DIR__ . '/../models/ChatSession.php';
require_once __DIR__ . '/../models/ChatMessage.php';

class ChatController {
    private $pdo;
    private $aiService;
    private $sessionModel;
    private $messageModel;

    public function __construct() {
        global $pdo; 
        $this->pdo = $pdo;

        // Load config
        $config = require __DIR__ . '/../../../config/services.php';
        $this->aiService = new GeminiService($config['gemini']);
        
        $this->sessionModel = new ChatSession($pdo);
        $this->messageModel = new ChatMessage($pdo);
    }

    /**
     * Xử lý chính: Gửi tin nhắn
     */
    public function sendMessage($userId, $personaId, $topicId, $message, $sessionId = null, $imageBase64 = null) {
        $savedImagePath = null;
        if ($imageBase64) {
            $savedImagePath = $this->saveImageToDisk($imageBase64);
        }

        if (!$sessionId) {
            // SỬA: Dùng bảng 'topic'
            $topicName = "New Conversation";
            if ($topicId) {
                $stmt = $this->pdo->prepare("SELECT TopicName FROM topic WHERE TopicID = ?");
                $stmt->execute([$topicId]);
                $t = $stmt->fetch();
                if ($t) $topicName = $t['TopicName'];
            }
            $sessionId = $this->sessionModel->create($userId, $personaId, $topicId, $topicName);
        }

        // Lưu tin User
        $this->messageModel->save($sessionId, 'User', $message, $savedImagePath);

        // Lấy Prompt hệ thống
        $systemPrompt = $this->getPersonaSystemPrompt($personaId);
        
        // Lấy lịch sử chat (Memory)
        $historyContext = $this->messageModel->getRecentContext($userId, 20);

        // Lấy thông tin Topic hiện tại (SỬA: Dùng bảng 'topic')
        $topicContext = "";
        if ($topicId) {
            $stmt = $this->pdo->prepare("SELECT TopicName FROM topic WHERE TopicID = ?");
            $stmt->execute([$topicId]);
            $t = $stmt->fetch();
            if ($t) {
                $topicContext = "Current Topic: " . $t['TopicName'] . "\n";
            }
        }

        // Ghép Prompt
        $finalPrompt = "System Instruction: $systemPrompt\n" .
                        $topicContext . 
                        "IMPORTANT: Always respond in natural, fluent English.\n" .
                        "--- Memory Stream ---\n$historyContext\n" . 
                        "---------------------\n" .
                        "User Input: $message\n" .
                        "Response:";

        // Gọi AI
        try {
            $promptToSend = (empty($message) && $imageBase64) ? "Describe this image or answer user's intent about it." : $finalPrompt;
            $aiResponse = $this->aiService->generateContent($promptToSend, $imageBase64);
        } catch (Exception $e) {
            return ['status' => 500, 'message' => $e->getMessage()];
        }

        // Lưu phản hồi AI
        $this->messageModel->save($sessionId, 'AI', $aiResponse);

        return [
            'status' => 200,
            'data' => [
                'session_id' => $sessionId,
                'response' => $aiResponse
            ]
        ];
    }

    /**
     * KHỞI TẠO CHAT THEO TOPIC
     */
    public function initChatWithTopic($userId, $personaId, $topicId) {
        // SỬA: Dùng bảng 'topic' để lấy tên và mô tả
        $stmt = $this->pdo->prepare("SELECT TopicName, Description FROM topic WHERE TopicID = ?");
        $stmt->execute([$topicId]);
        $topicData = $stmt->fetch();
        
        $topicName = $topicData ? $topicData['TopicName'] : "General Chat";
        $topicDesc = ($topicData && !empty($topicData['Description'])) ? $topicData['Description'] : "";

        // Tạo session mới
        $sessionTitle = "Topic: " . $topicName;
        $sessionId = $this->sessionModel->create($userId, $personaId, $topicId, $sessionTitle);

        // Lấy System Prompt
        $systemPrompt = $this->getPersonaSystemPrompt($personaId);

        // Tạo Prompt hướng dẫn AI mở lời về chủ đề này
        $prompt = "System Instruction: $systemPrompt\n" .
                  "Context: The user has selected a conversation topic: '$topicName'.\n" .
                  "Topic Description: $topicDesc\n" .
                  "Task: Proactively start the conversation with a short, engaging greeting or an open-ended question specifically related to '$topicName'. " .
                  "Do not mention the 'Topic ID'. Be natural and helpful.";

        // Gọi AI
        try {
            $aiGreeting = $this->aiService->generateContent($prompt);
        } catch (Exception $e) {
            $aiGreeting = "Hello! I'm ready to chat about $topicName.";
        }

        // Lưu tin nhắn AI
        $this->messageModel->save($sessionId, 'AI', $aiGreeting);

        return [
            'status' => 200,
            'data' => [
                'session_id' => $sessionId,
                'response' => $aiGreeting
            ]
        ];
    }

    // --- CÁC HÀM PHỤ TRỢ ---

    private function getPersonaSystemPrompt($personaId) {
        // SỬA: Dùng bảng 'persona'
        $stmt = $this->pdo->prepare("SELECT SystemPrompt FROM persona WHERE PersonaID = ?");
        $stmt->execute([$personaId]);
        $row = $stmt->fetch();
        return $row ? $row['SystemPrompt'] : "You are a helpful AI assistant.";
    }

    private function saveImageToDisk($base64String) {
        if (!$base64String) return null;
        $parts = explode(',', $base64String);
        $data = base64_decode(end($parts));
        if (!$data) return null;
        $fileName = 'img_' . time() . '_' . rand(1000,9999) . '.jpg';
        $uploadDir = __DIR__ . '/../../../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        file_put_contents($uploadDir . $fileName, $data);
        return 'public/uploads/' . $fileName;
    }

    // API Getters
    public function getPersonas() {
        // SỬA: Dùng bảng 'persona'
        $stmt = $this->pdo->query("SELECT PersonaID, PersonaName, Description, Icon, IsPremium FROM persona");
        return $stmt->fetchAll();
    }

    public function getTopics() {
        // SỬA: Dùng bảng 'topic'
        $stmt = $this->pdo->query("SELECT TopicID, TopicName, Description FROM topic");
        return $stmt->fetchAll();
    }

    public function getUserHistory($userId) {
        return $this->sessionModel->getAllByUser($userId);
    }

    public function getSessionMessages($sessionId, $userId) {
        $session = $this->sessionModel->getOne($sessionId, $userId);
        if (!$session) return ['status' => 403, 'message' => 'Unauthorized'];
        $messages = $this->messageModel->getBySessionId($sessionId);
        return ['status' => 200, 'data' => ['session_info' => $session, 'messages' => $messages]];
    }

    public function deleteSession($sessionId, $userId) {
        return ['status' => $this->sessionModel->delete($sessionId, $userId) ? 200 : 404];
    }

    public function renameSession($sessionId, $userId, $newTitle) {
        return ['status' => $this->sessionModel->rename($sessionId, $userId, $newTitle) ? 200 : 400];
    }
}
?>