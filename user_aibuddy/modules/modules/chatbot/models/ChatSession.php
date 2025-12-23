<?php
// modules/chatbot/models/ChatSession.php

class ChatSession {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Lấy lịch sử chat của user (để hiển thị bên Sidebar trái)
    public function getAllByUser($userId) {
        $sql = "SELECT s.SessionID, s.Title, s.CreatedAt, p.PersonaName, t.TopicName 
                FROM ChatSessions s
                LEFT JOIN Personas p ON s.PersonaID = p.PersonaID
                LEFT JOIN Topics t ON s.TopicID = t.TopicID
                WHERE s.UserID = ?
                ORDER BY s.CreatedAt DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // Tạo phiên chat mới
    public function create($userId, $personaId, $topicId, $title) {
        $sql = "INSERT INTO ChatSessions (UserID, PersonaID, TopicID, Title) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $personaId, $topicId, $title]);
        return $this->pdo->lastInsertId();
    }

    // Kiểm tra session có tồn tại và thuộc về user không (để bảo mật)
    public function getOne($sessionId, $userId) {
        $sql = "SELECT UserID, PersonaID, TopicID FROM ChatSessions WHERE SessionID = ? AND UserID = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId, $userId]);
        return $stmt->fetch();
    }

    // Đổi tên đoạn chat
    public function rename($sessionId, $userId, $newTitle) {
        $sql = "UPDATE ChatSessions SET Title = ? WHERE SessionID = ? AND UserID = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$newTitle, $sessionId, $userId]);
        return $stmt->rowCount() > 0;
    }

    // Xóa đoạn chat
    public function delete($sessionId, $userId) {
        // Lưu ý: Database nên thiết lập Foreign Key CASCADE để tự xóa messages con
        $sql = "DELETE FROM ChatSessions WHERE SessionID = ? AND UserID = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId, $userId]);
        return $stmt->rowCount() > 0;
    }
}
?>