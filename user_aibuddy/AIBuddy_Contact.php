<?php
session_start();
require_once 'config/db.php';

$successMsg = null;
$errorMsg = null;

/* 1. BẮT BUỘC PHẢI ĐĂNG NHẬP */
if (!isset($_SESSION['userid'])) {
    header("Location: AIBuddy_SignIn.php");
    exit;
}

$userID = $_SESSION['userid'];

// Lấy tên người dùng để hiển thị trên Header
$stmtUser = $conn->prepare("SELECT UserName FROM users WHERE UserID = ?");
$stmtUser->bind_param("i", $userID);
$stmtUser->execute();
$userData = $stmtUser->get_result()->fetch_assoc();
$currentUserName = $userData ? $userData['UserName'] : 'User';

/* 2. XỬ LÝ GỬI FORM (INSERT VÀO BẢNG REPORT) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $topic   = trim($_POST['topic'] ?? ''); // Map vào ReportType
    $content = trim($_POST['content'] ?? ''); // Map vào ReportContent

    if ($topic === '' || $content === '') {
        $errorMsg = "Please fill in all required fields.";
    } else {
        // SQL Insert theo cấu trúc bảng report trong ảnh
        // ReportStartTime, ReportEndTime, ReportTime đều lấy thời gian hiện tại cho khớp data mẫu
        $stmt = $conn->prepare("
            INSERT INTO report 
            (UserID, AdminID, ReportType, ReportContent, ReportStartTime, ReportEndTime, ReportTime, Status, AdminResponse) 
            VALUES (?, NULL, ?, ?, NOW(), NOW(), NOW(), 'Pending', NULL)
        ");

        if ($stmt) {
            $stmt->bind_param("iss", $userID, $topic, $content);
            
            if ($stmt->execute()) {
                $successMsg = "Your report has been submitted successfully.";
            } else {
                $errorMsg = "Error sending report: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errorMsg = "Database error: " . $conn->error;
        }
    }
}

/* 3. LẤY LỊCH SỬ REPORT & PHẢN HỒI CỦA ADMIN */
$historyQuery = "SELECT ReportType, ReportContent, ReportTime, Status, AdminResponse 
                 FROM report 
                 WHERE UserID = ? 
                 ORDER BY ReportTime DESC";
$stmtHistory = $conn->prepare($historyQuery);
$stmtHistory->bind_param("i", $userID);
$stmtHistory->execute();
$resultHistory = $stmtHistory->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - AI Buddy</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Color Variables */
    :root {
      --primary-dark: #01161e;
      --primary: #124559;
      --primary-light: #598392;
      --accent: #33c6e7;
      --light: #aec3b0;
      --background: #eff6e0;
      --white: #ffffff;
      --gray: #d9d9d9;
      --text: #353535;
      --card-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    /* Global Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: var(--background);
      color: var(--text);
      line-height: 1.6;
    }

    .container {
      width: 90%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }

    /* Header Styles */
    header {
      background-color: var(--white);
      padding: 15px 0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: 24px;
      font-weight: bold;
      color: var(--primary);
      display: flex;
      align-items: center;
    }

    .logo-icon {
      margin-right: 8px;
      font-size: 28px;
    }

    nav a {
      margin: 0 15px;
      text-decoration: none;
      color: var(--primary);
      font-weight: 500;
      transition: color 0.3s;
    }

    nav a:hover {
      color: var(--accent);
    }

    .user-greeting-badge {
        background-color: var(--background);
        color: var(--primary);
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 15px;
        font-weight: 500;
        border: 1px solid var(--primary);
        display: inline-block;
    }

    .user-greeting-badge strong {
        color: var(--accent);
        font-weight: 700;
    }

    /* Page Hero */
    .page-hero {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      color: var(--white);
      padding: 60px 0;
      text-align: center;
      margin: 20px auto;
      border-radius: 10px;
      box-shadow: var(--card-shadow);
    }

    .page-hero h1 {
      font-size: 2.5rem;
      margin-bottom: 15px;
    }

    .breadcrumb {
      display: flex;
      justify-content: center;
      list-style: none;
    }

    .breadcrumb li {
      margin: 0 10px;
      position: relative;
    }

    .breadcrumb li:not(:last-child):after {
      content: ">";
      position: absolute;
      right: -15px;
    }

    .breadcrumb a {
      color: var(--light);
      text-decoration: none;
      transition: color 0.3s;
    }

    .breadcrumb a:hover {
      color: var(--accent);
    }

    .breadcrumb .current {
      color: var(--accent);
    }

    /* Contact Section */
    .contact-section {
      padding: 80px 0;
    }

    .contact-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
    }

    .contact-info {
      background-color: var(--white);
      border-radius: 10px;
      padding: 40px;
      box-shadow: var(--card-shadow);
    }

    .contact-info h2 {
      font-size: 2rem;
      margin-bottom: 20px;
      color: var(--primary);
    }

    .contact-info p {
      margin-bottom: 30px;
      color: var(--text);
    }

    .contact-details {
      margin-bottom: 40px;
    }

    .contact-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 25px;
    }

    .contact-icon {
      width: 50px;
      height: 50px;
      background-color: var(--light);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      color: var(--primary);
      font-size: 20px;
      flex-shrink: 0;
    }

    .contact-text h3 {
      font-size: 1.2rem;
      margin-bottom: 5px;
      color: var(--primary);
    }

    .contact-text p {
      margin-bottom: 0;
      color: var(--text);
    }

    .social-links {
      display: flex;
      gap: 15px;
    }

    .social-links a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background-color: var(--primary);
      border-radius: 50%;
      color: var(--white);
      text-decoration: none;
      transition: background-color 0.3s;
    }

    .social-links a:hover {
      background-color: var(--accent);
    }

    /* Contact Form */
    .contact-form {
      background-color: var(--white);
      border-radius: 10px;
      padding: 40px;
      box-shadow: var(--card-shadow);
    }

    .contact-form h2 {
      font-size: 2rem;
      margin-bottom: 20px;
      color: var(--primary);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--primary);
    }

    .form-control {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid var(--gray);
      border-radius: 5px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--accent);
    }

    textarea.form-control {
      min-height: 150px;
      resize: vertical;
    }

    .btn-primary {
      background-color: var(--accent);
      color: var(--white);
      padding: 12px 30px;
      border: none;
      border-radius: 5px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-primary:hover {
      background-color: #2ab4d1;
      transform: translateY(-2px);
    }

    /* === REPORT HISTORY TABLE === */
    .history-section {
        margin-top: 50px;
        background: var(--white);
        padding: 30px;
        border-radius: 10px;
        box-shadow: var(--card-shadow);
    }
    .history-section h2 {
        color: var(--primary);
        margin-bottom: 20px;
        border-bottom: 2px solid var(--background);
        padding-bottom: 10px;
    }
    .report-table {
        width: 100%;
        border-collapse: collapse;
    }
    .report-table th, .report-table td {
        padding: 12px 15px;
        border-bottom: 1px solid var(--gray);
        text-align: left;
    }
    .report-table th {
        background-color: var(--primary);
        color: var(--white);
    }
    .report-table tr:hover {
        background-color: #f9f9f9;
    }
    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .status-pending { background-color: #fff3cd; color: #856404; }
    .status-processed { background-color: #d1ecf1; color: #0c5460; }
    .status-resolved { background-color: #d4edda; color: #155724; }
    
    .admin-reply {
        font-style: italic;
        color: var(--primary);
        font-weight: 500;
        margin-top: 5px;
        display: block;
        background: #f0f8ff;
        padding: 8px;
        border-radius: 5px;
        border-left: 3px solid var(--accent);
    }

    /* Footer */
    footer {
      background-color: var(--primary-dark);
      color: var(--white);
      padding: 60px 0 20px;
    }

    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 40px;
      margin-bottom: 40px;
    }

    .footer-column h3 {
      font-size: 1.2rem;
      margin-bottom: 20px;
      color: var(--accent);
    }

    .footer-column ul {
      list-style: none;
    }

    .footer-column ul li {
      margin-bottom: 10px;
    }

    .footer-column ul li a {
      color: var(--light);
      text-decoration: none;
      transition: color 0.3s;
    }

    .footer-column ul li a:hover {
      color: var(--accent);
    }

    .copyright {
      text-align: center;
      padding-top: 20px;
      border-top: 1px solid var(--primary);
      color: var(--light);
      font-size: 0.9rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .header-content { flex-direction: column; text-align: center; }
      nav { margin: 15px 0; }
      .contact-container { grid-template-columns: 1fr; }
      .report-table { display: block; overflow-x: auto; }
    }
  </style>
</head>

<body>
  <header>
    <div class="container header-content">
      <a  class="logo">
        <span class="logo-icon">🤖</span> AI Buddy
      </a>
      <nav>
        <a href="AIBuddy_Homepage.php">Home</a>
        <a href="AIBuddy_Chatbot.php">Chatbot</a>
        <a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a>
        <a href="AIBuddy_Trial.php">Trial</a>
        <a href="AIBuddy_Profile.php">Profile</a>
        <a href="AIBuddy_About.php">About</a>
        <a href="AIBuddy_Contact.php">Contact</a>
      </nav>
      <div class="user-greeting-badge">
        How's your day, <strong><?= htmlspecialchars($currentUserName) ?></strong>?
      </div>
    </div>
  </header>

  <section class="page-hero">
    <div class="container">
      <h1>Contact Us</h1>
      <ul class="breadcrumb">
        <li><a href="AIBuddy_Homepage.php">Home</a></li>
        <li class="current">Contact</li>
      </ul>
    </div>
  </section>

  <section class="contact-section">
    <div class="container">
        
      <div class="contact-container">
        <div class="contact-info">
          <h2>Get in Touch</h2>
          <p>We're always here to listen. Don't hesitate to reach out to us.</p>
          <div class="contact-details">
            <div class="contact-item">
              <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
              <div class="contact-text">
                <h3>Address</h3>
                <p>123 Wellness Street, Mindful District, CA 90210</p>
              </div>
            </div>
            <div class="contact-item">
              <div class="contact-icon"><i class="fas fa-envelope"></i></div>
              <div class="contact-text">
                <h3>Email</h3>
                <p>support@aibuddy.com</p>
              </div>
            </div>
          </div>
        </div>

        <div class="contact-form">
          <h2>Send a Report</h2>

          <?php if (!empty($successMsg)): ?>
            <p style="color: green; background:#e6fffa; padding:10px; border-radius:5px; margin-bottom: 15px;">
              <i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMsg) ?>
            </p>
          <?php endif; ?>

          <?php if (!empty($errorMsg)): ?>
            <p style="color: red; background:#fff5f5; padding:10px; border-radius:5px; margin-bottom: 15px;">
              <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errorMsg) ?>
            </p>
          <?php endif; ?>

          <form method="POST">
            <div class="form-group">
              <label>Report Type *</label>
              <select name="topic" class="form-control" required>
                <option value="Lỗi kỹ thuật">Lỗi kỹ thuật</option>
                <option value="Nội dung xấu">Nội dung xấu</option>
                <option value="Thanh toán">Thanh toán</option>
                <option value="Khác">Khác</option>
              </select>
            </div>

            <div class="form-group">
              <label>Content *</label>
              <textarea name="content" class="form-control" placeholder="Describe your issue..." required></textarea>
            </div>

            <button type="submit" class="btn-primary">Submit Report</button>
          </form>
        </div>
      </div>

      <div class="history-section">
          <h2>Your Reports & Responses</h2>
          <?php if ($resultHistory->num_rows > 0): ?>
              <table class="report-table">
                  <thead>
                      <tr>
                          <th>Date</th>
                          <th>Type</th>
                          <th>Content</th>
                          <th>Status</th>
                          <th>Admin Response</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php while ($row = $resultHistory->fetch_assoc()): ?>
                          <?php 
                              // Xác định class màu sắc cho Status
                              $statusClass = 'status-pending';
                              if ($row['Status'] == 'Processed') $statusClass = 'status-processed';
                              if ($row['Status'] == 'Resolved') $statusClass = 'status-resolved';
                          ?>
                          <tr>
                              <td style="white-space:nowrap;"><?= date('Y-m-d H:i', strtotime($row['ReportTime'])) ?></td>
                              <td><strong><?= htmlspecialchars($row['ReportType']) ?></strong></td>
                              <td><?= nl2br(htmlspecialchars($row['ReportContent'])) ?></td>
                              <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($row['Status']) ?></span></td>
                              <td>
                                  <?php if (!empty($row['AdminResponse']) && $row['AdminResponse'] !== 'NULL'): ?>
                                      <span class="admin-reply">
                                          <i class="fas fa-user-shield"></i> <?= nl2br(htmlspecialchars($row['AdminResponse'])) ?>
                                      </span>
                                  <?php else: ?>
                                      <span style="color:#999; font-style:italic;">Waiting for response...</span>
                                  <?php endif; ?>
                              </td>
                          </tr>
                      <?php endwhile; ?>
                  </tbody>
              </table>
          <?php else: ?>
              <p style="text-align:center; color:#666;">You haven't submitted any reports yet.</p>
          <?php endif; ?>
      </div>

    </div>
  </section>

  <footer>
    <div class="container">
      <div class="footer-content">
        <div class="footer-column">
          <h3>AI Buddy</h3>
          <p>Your companion for mental wellness with intelligent AI support and personalized care.</p>
          <div class="social-links">

            <a href="#"><i class="fab fa-facebook-f"></i></a>

            <a href="#"><i class="fab fa-twitter"></i></a>

            <a href="#"><i class="fab fa-instagram"></i></a>

            <a href="#"><i class="fab fa-linkedin-in"></i></a>

          </div>

        </div>
        <div class="footer-column">
          <h3>Quick Links</h3>
          <ul>
            <li><a href="AIBuddy_Homepage.php">Home</a></li>

            <li><a href="AIBuddy_Chatbot.php">Chatbot</a></li>

            <li><a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a></li>

            <li><a href="AIBuddy_Trial.php">Trial</a></li>

            <li><a href="AIBuddy_Contact.php">Contact</a></li>
          </ul>
        </div>
        <div class="footer-column">
          <h3>Legal</h3>
          <ul>
            <li><a href="AIBuddy_Terms of Service.php">Terms of Service</a></li>
            <li><a href="AIBuddy_PrivacyPolicy.php">Privacy Policy</a></li>
    

            <li><a href="AIBuddy_Terms of Service.php">Cookie Policy</a></li>

            <li><a href="AIBuddy_Terms of Service.php">Disclaimer</a></li>
          </ul>
        </div>
        <div class="footer-column">
          <h3>Contact</h3>
          <ul>
          <li><i class="fas fa-map-marker-alt"></i> 123 Wellness Street, Mindful District, CA 90210</li>

            <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>

            <li><i class="fas fa-envelope"></i> support@aibuddy.com</li>

            <li><i class="fas fa-clock"></i> Mon-Fri: 8:00 AM - 8:00 PM</li>
          </ul>
        </div>
      </div>
      <div class="copyright">
        <p>&copy; 2025 AI Buddy. All rights reserved.</p>
      </div>
    </div>
  </footer>

</body>
</html>