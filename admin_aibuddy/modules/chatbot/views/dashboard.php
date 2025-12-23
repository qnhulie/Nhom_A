<?php
require_once __DIR__ . '/../controllers/DashboardController.php';

// 1. Khởi tạo Controller và lấy số liệu
$controller = new DashboardController();
$stats = $controller->getStats();

// Include common header and sidebar
include __DIR__ . '/../../../includes/header.php';
include __DIR__ . '/../../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-navbar">
        <h2>Chatbot Dashboard</h2>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card card-stat p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Total Users</h6>
                        <h3><?php echo $stats['users']; ?></h3>
                    </div>
                    <div class="fs-1 text-primary"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card card-stat p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Chat Sessions</h6>
                        <h3><?php echo $stats['sessions']; ?></h3>
                    </div>
                    <div class="fs-1 text-success"><i class="fas fa-comments"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card card-stat p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">AI Personas</h6>
                        <h3><?php echo $stats['personas']; ?></h3>
                    </div>
                    <div class="fs-1 text-warning"><i class="fas fa-robot"></i></div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card card-stat p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Active Topics</h6>
                        <h3><?php echo $stats['topics']; ?></h3>
                    </div>
                    <div class="fs-1 text-info"><i class="fas fa-lightbulb"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-box">
        <h3>Quick Actions</h3>
        <p>Welcome to the AI Buddy Management System. Use the sidebar to manage content.</p>
        <a href="personas/index.php" class="btn btn-outline-primary me-2">Add New Persona</a>
        <a href="topics/index.php" class="btn btn-outline-secondary">Create Topic</a>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>