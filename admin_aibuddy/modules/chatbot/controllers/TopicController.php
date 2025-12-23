<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../models/AdminTopic.php';

class TopicController {
    private $model;

    public function __construct() {
        global $conn;
        $this->model = new AdminTopic($conn);
    }

    public function index() {
        return $this->model->getAll();
    }

    public function edit($id) {
        return $this->model->getById($id);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['TopicID'] ?? null;
            if ($id) {
                $this->model->update($id, $_POST);
            } else {
                $this->model->create($_POST);
            }
            header('Location: index.php');
            exit;
        }
    }

    public function delete($id) {
        if ($id) $this->model->delete($id);
        header('Location: index.php');
        exit;
    }
}
?>