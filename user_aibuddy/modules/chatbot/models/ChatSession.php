<?php
// modules/chatbot/models/ChatSession.php

class ChatSession {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Lấy lịch sử chat của user
    public function getAllByUser($userId) {
        // Lưu ý: Tên bảng trong SQL của bạn là 'chatsessions' (viết thường)
        // Chúng ta join với bảng 'persona' và 'topic' (đã chuẩn hóa ở bước trước)
        $sql = "SELECT s.SessionID, s.Title, s.CreatedAt, p.PersonaName, t.TopicName 
                FROM chatsessions s
                LEFT JOIN persona p ON s.PersonaID = p.PersonaID
                LEFT JOIN topic t ON s.TopicID = t.TopicID
                WHERE s.UserID = ?
                ORDER BY s.CreatedAt DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tạo phiên chat mới
    public function create($userId, $personaId, $topicId, $title) {
        $sql = "INSERT INTO chatsessions (UserID, PersonaID, TopicID, Title) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $personaId, $topicId, $title]);
        return $this->pdo->lastInsertId();
    }

    // Kiểm tra quyền sở hữu session
    public function getOne($sessionId, $userId) {
        $sql = "SELECT UserID, PersonaID, TopicID FROM chatsessions WHERE SessionID = ? AND UserID = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Đổi tên đoạn chat
    public function rename($sessionId, $userId, $newTitle) {
        $sql = "UPDATE chatsessions SET Title = ? WHERE SessionID = ? AND UserID = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$newTitle, $sessionId, $userId]);
        return $stmt->rowCount() > 0;
    }

    // Xóa đoạn chat
    public function delete($sessionId, $userId) {
        // Vì bạn đã set ON DELETE CASCADE trong SQL, chỉ cần xóa session là message tự mất
        $sql = "DELETE FROM chatsessions WHERE SessionID = ? AND UserID = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId, $userId]);
        return $stmt->rowCount() > 0;
    }
}
?>