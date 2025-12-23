<?php
// modules/chatbot/models/ChatMessage.php

class ChatMessage {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Lưu tin nhắn (của User hoặc AI)
    public function save($sessionId, $sender, $content, $imagePath = null) {
        $sql = "INSERT INTO ChatMessages (SessionID, Sender, Content, ImagePath) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId, $sender, $content, $imagePath]);
        return $this->pdo->lastInsertId();
    }

    // Lấy toàn bộ tin nhắn của 1 session (để hiển thị lên màn hình chat)
    public function getBySessionId($sessionId) {
        $sql = "SELECT Sender, Content, CreatedAt, AudioUrl, ImagePath 
                FROM ChatMessages 
                WHERE SessionID = ? 
                ORDER BY CreatedAt ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    // Lấy ngữ cảnh hội thoại gần nhất (Memory cho AI)
    // Logic này lấy từ hàm getConversationContext cũ
    public function getRecentContext($userId, $limit = 30) {
        // Lấy X tin nhắn gần nhất CỦA USER (Bất kể session nào - Global Memory)
        $sql = "SELECT m.Sender, m.Content, s.SessionID 
                FROM ChatMessages m
                JOIN ChatSessions s ON m.SessionID = s.SessionID
                WHERE s.UserID = ? 
                ORDER BY m.CreatedAt DESC 
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Đảo ngược để đúng thứ tự thời gian (Cũ nhất -> Mới nhất)
        $rows = array_reverse($rows);

        $contextString = "";
        $currentSession = null;

        foreach ($rows as $msg) {
            // Thêm dấu ngăn cách nếu chuyển sang session khác
            if ($currentSession !== $msg['SessionID']) {
                $contextString .= "\n[--- Conversation Segment ---]\n";
                $currentSession = $msg['SessionID'];
            }

            $role = ($msg['Sender'] === 'User') ? 'User' : 'AI';
            $cleanContent = str_replace(["\r", "\n"], " ", $msg['Content']);
            $contextString .= "$role: $cleanContent\n";
        }

        return $contextString;
    }
}
?>