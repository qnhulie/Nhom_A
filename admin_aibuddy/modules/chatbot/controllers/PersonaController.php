<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../models/AdminPersona.php';

class PersonaController {
    private $model;

    public function __construct() {
        global $conn;
        $this->model = new AdminPersona($conn);
    }

    // Lấy danh sách cho trang Index
    public function index() {
        return $this->model->getAll();
    }

    // Lấy dữ liệu cho trang Edit
    public function edit($id) {
        return $this->model->getById($id);
    }

    // Xử lý Lưu (Thêm mới hoặc Cập nhật)
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['PersonaID'] ?? null;
            
            if ($id) {
                $this->model->update($id, $_POST);
            } else {
                $this->model->create($_POST);
            }
            
            // Redirect về trang danh sách sau khi lưu
            header('Location: index.php');
            exit;
        }
    }

    // Xử lý Xóa
    public function delete($id) {
        if ($id) {
            $this->model->delete($id);
        }
        header('Location: index.php');
        exit;
    }
}
?>