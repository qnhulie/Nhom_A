<?php
// modules/admin/controllers/DashboardController.php
require_once __DIR__ . '/../../../config/db.php';

class DashboardController {
    private $pdo;

    public function __construct() {
        global $conn;
        $this->pdo = $conn;
    }

    public function getStats() {
        return [
            'users' => $this->countTable('Users'),
            'sessions' => $this->countTable('ChatSessions'),
            'personas' => $this->countTable('persona'),
            'topics' => $this->countTable('topic')
        ];
    }

    private function countTable($tableName) {
        $result = $this->pdo->query("SELECT COUNT(*) as count FROM $tableName");
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}
?>