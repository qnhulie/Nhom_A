<?php
class AdminPersona {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Lấy danh sách tất cả Persona
    public function getAll() {
        $result = $this->conn->query("SELECT * FROM persona ORDER BY PersonaID DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Lấy thông tin chi tiết một Persona theo ID
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM persona WHERE PersonaID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Thêm mới Persona
    public function create($data) {
        $sql = "INSERT INTO persona (PersonaName, Description, SystemPrompt, Icon, IsPremium) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        // Xử lý dữ liệu đầu vào để tránh lỗi null
        $name = $data['PersonaName'] ?? 'New Persona';
        $desc = $data['Description'] ?? '';
        // SystemPrompt rất quan trọng, nếu để trống AI sẽ không biết đóng vai gì
        $prompt = $data['SystemPrompt'] ?? 'You are a helpful AI assistant.'; 
        $icon = $data['Icon'] ?? '🤖';
        $isPremium = isset($data['IsPremium']) ? 1 : 0;

        $stmt->bind_param("ssssi", $name, $desc, $prompt, $icon, $isPremium);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        } else {
            // Ghi log lỗi nếu cần thiết
            error_log("Error creating persona: " . $stmt->error);
            return false;
        }
    }

    // Cập nhật Persona
    public function update($id, $data) {
        $sql = "UPDATE persona SET PersonaName = ?, Description = ?, SystemPrompt = ?, Icon = ?, IsPremium = ? WHERE PersonaID = ?";
        $stmt = $this->conn->prepare($sql);
        
        $name = $data['PersonaName'];
        $desc = $data['Description'];
        $prompt = $data['SystemPrompt'];
        $icon = $data['Icon'];
        $isPremium = isset($data['IsPremium']) ? 1 : 0;

        $stmt->bind_param("ssssii", $name, $desc, $prompt, $icon, $isPremium, $id);
        
        return $stmt->execute();
    }

    // Xóa Persona
    public function delete($id) {
        // Bước 1: Xóa lịch sử chat liên quan đến Persona này để tránh lỗi khóa ngoại
        $stmtHistory = $this->conn->prepare("DELETE FROM chathistory WHERE PersonaID = ?");
        $stmtHistory->bind_param("i", $id);
        $stmtHistory->execute();
        $stmtHistory->close();
        
        // Bước 2: Xóa Persona
        $stmt = $this->conn->prepare("DELETE FROM persona WHERE PersonaID = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>