<?php
// modules/chatbot/index.php
session_start();

// Include database connection
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin_aibuddy/config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include header and sidebar
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="top-navbar">
        <h2>Chatbot Management</h2>
    </div>

    <div class="dashboard-grid">
        <a href="views/personas/index.php" class="card-menu">
            <div class="card-header-bg" style="background: #27ae60;">
                <span>AI Personas</span>
                <i class="fa-solid fa-masks-theater"></i>
            </div>
            <div class="card-body-content">
                <span class="big-number">Manage</span>
                <span class="sub-text">Configure AI Characters</span>
                <br>
                <span class="link-text">Go to Personas &rarr;</span>
            </div>
        </a>

        <a href="views/topics/index.php" class="card-menu">
            <div class="card-header-bg" style="background: #e67e22;">
                <span>Topics</span>
                <i class="fa-solid fa-list-alt"></i>
            </div>
            <div class="card-body-content">
                <span class="big-number">Manage</span>
                <span class="sub-text">Configure Chat Topics</span>
                <br>
                <span class="link-text">Go to Topics &rarr;</span>
            </div>
        </a>

        <a href="views/dashboard.php" class="card-menu">
            <div class="card-header-bg" style="background: var(--primary-color);">
                <span>Dashboard</span>
                <i class="fa-solid fa-tachometer-alt"></i>
            </div>
            <div class="card-body-content">
                <span class="big-number">Stats</span>
                <span class="sub-text">View Chatbot Statistics</span>
                <br>
                <span class="link-text">View Dashboard &rarr;</span>
            </div>
        </a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>