<?php
// modules/chatbot/models/ChatMessage.php

class ChatMessage {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Lưu tin nhắn
    public function save($sessionId, $sender, $content, $imagePath = null) {
        $sql = "INSERT INTO chatmessages (SessionID, Sender, Content, ImagePath) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId, $sender, $content, $imagePath]);
        return $this->pdo->lastInsertId();
    }

    // Lấy tin nhắn của session
    public function getBySessionId($sessionId) {
        $sql = "SELECT Sender, Content, CreatedAt, AudioUrl, ImagePath 
                FROM chatmessages 
                WHERE SessionID = ? 
                ORDER BY CreatedAt ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy ngữ cảnh (Memory) cho AI
    public function getRecentContext($userId, $limit = 20) {
        // Join giữa chatmessages và chatsessions để lấy tin của đúng User đó
        $sql = "SELECT m.Sender, m.Content, s.SessionID 
                FROM chatmessages m
                JOIN chatsessions s ON m.SessionID = s.SessionID
                WHERE s.UserID = ? 
                ORDER BY m.CreatedAt DESC 
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $rows = array_reverse($rows); // Đảo ngược để xếp theo thứ tự thời gian

        $contextString = "";
        $currentSession = null;

        foreach ($rows as $msg) {
            if ($currentSession !== $msg['SessionID']) {
                $contextString .= "\n[--- Conversation Segment ---]\n";
                $currentSession = $msg['SessionID'];
            }
            // Chuẩn hóa Sender để AI dễ hiểu
            $role = ($msg['Sender'] === 'User') ? 'User' : 'AI';
            // Xóa ký tự xuống dòng thừa để prompt gọn hơn
            $cleanContent = str_replace(["\r", "\n"], " ", $msg['Content']);
            $contextString .= "$role: $cleanContent\n";
        }

        return $contextString;
    }
}
?>