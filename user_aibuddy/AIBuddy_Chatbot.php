<?php
session_start();
require_once 'config/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['userid'])) {
    header("Location: AIBuddy_SignIn.php");
    exit();
}

// 2. Lấy thông tin User
$UserID = $_SESSION['userid'];
$stmt = $conn->prepare("SELECT UserName FROM users WHERE UserID = ?");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Buddy - Chatbot</title>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/chatbot.css">
</head>
<body>

  <div id="overlay" class="sidebar-overlay"></div>

  <header>
      <div class="header-content">
          <div class="logo-area">
              <button id="menu-toggle" class="mobile-toggle-btn left">
                  <i class="fa-solid fa-bars"></i>
              </button>
              
              <a href="AIBuddy_Homepage.php" class="logo">
                  <span class="logo-icon">🤖</span> AI Buddy
              </a>
          </div>
          
            <nav>
                <a href="AIBuddy_Homepage.php">Home</a>
                <a href="AIBuddy_Chatbot.php" class="active">Chatbot</a>
                <a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a>
                <a href="AIBuddy_Trial.php">Trial</a>
                <a href="AIBuddy_Profile.php">Profile</a>
                <a href="AIBuddy_About.php">About</a>
                <a href="AIBuddy_Contact.php">Contact</a>
            </nav>
          
          <div class="user-area">
              <?php if (isset($_SESSION['userid']) && isset($user)): ?>
                  <div class="user-greeting-badge">
                      How's your day, <strong><?= htmlspecialchars($user['UserName']) ?></strong>?
                  </div>
              <?php endif; ?>

              <button id="tools-toggle" class="mobile-toggle-btn right">
                  <i class="fa-solid fa-sliders"></i>
              </button>
          </div>
      </div>
  </header>

  <?php 
     $currentUserID = $_SESSION['userid'];
     echo "<script>const CURRENT_USER_ID = $currentUserID;</script>";
     include 'modules/chatbot/views/index.php'; 
  ?>

  <script src="assets/js/chatbot.js"></script>
</body>
</html>