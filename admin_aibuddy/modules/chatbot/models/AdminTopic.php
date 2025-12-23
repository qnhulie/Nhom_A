<?php
class AdminTopic {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAll() {
        $result = $this->conn->query("SELECT * FROM topic ORDER BY TopicID DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM topic WHERE TopicID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function create($data) {
        // Lưu ý: Cột mô tả bây giờ là 'Description'
        $sql = "INSERT INTO topic (TopicName, Description) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        $name = $data['TopicName'] ?? 'New Topic';
        $desc = $data['Description'] ?? '';

        $stmt->bind_param("ss", $name, $desc);
        
        if (!$stmt->execute()) {
            error_log("Error creating topic: " . $stmt->error);
            return false;
        }
        return true;
    }

    public function update($id, $data) {
        $sql = "UPDATE topic SET TopicName = ?, Description = ? WHERE TopicID = ?";
        $stmt = $this->conn->prepare($sql);
        
        $name = $data['TopicName'];
        $desc = $data['Description'];

        $stmt->bind_param("ssi", $name, $desc, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        // Xóa lịch sử chat liên quan đến Topic này trước
        $stmtHistory = $this->conn->prepare("DELETE FROM chathistory WHERE TopicID = ?");
        $stmtHistory->bind_param("i", $id);
        $stmtHistory->execute();
        $stmtHistory->close();
        
        // Sau đó xóa Topic
        $stmt = $this->conn->prepare("DELETE FROM topic WHERE TopicID = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>