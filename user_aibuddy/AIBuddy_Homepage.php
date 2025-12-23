<?php



session_start();

require_once 'config/db.php';



$currentUserName = null;



// Kiểm tra đăng nhập và lấy tên mới nhất từ DB

if (isset($_SESSION['userid'])) {

    $userID = $_SESSION['userid'];

    $stmt = $conn->prepare("SELECT UserName FROM users WHERE UserID = ?");

    $stmt->bind_param("i", $userID);

    $stmt->execute();

    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {

        $currentUserName = $res['UserName'];

    }

}

?>



<!DOCTYPE html>

<html lang="en">

<!DOCTYPE html>

<html lang="en">



<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>AI Buddy - Your Mental Health Companion</title>

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



    .signin-btn {

      background-color: var(--accent);

      color: var(--white);

      border: none;

      padding: 8px 20px;

      border-radius: 20px;

      font-weight: 600;

      cursor: pointer;

      transition: background-color 0.3s;

    }



    .signin-btn:hover {

      background-color: #2ab4d1;

    }



    /* Hero Section */

    .hero {

      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);

      color: var(--white);

      padding: 80px 0;

      text-align: center;

      margin: 20px auto;

      border-radius: 10px;

      box-shadow: var(--card-shadow);

    }



    .hero-content {

      max-width: 800px;

      margin: 0 auto;

    }



    .hero h1 {

      font-size: 2.8rem;

      margin-bottom: 20px;

      line-height: 1.2;

    }



    .hero p {

      font-size: 1.2rem;

      margin-bottom: 30px;

      opacity: 0.9;

    }



    .cta-button {

      background-color: var(--accent);

      color: var(--white);

      border: none;

      padding: 12px 35px;

      border-radius: 30px;

      font-size: 1.1rem;

      font-weight: 600;

      cursor: pointer;

      transition: all 0.3s;

      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);

    }



    .cta-button:hover {

      background-color: #2ab4d1;

      transform: translateY(-3px);

      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);

    }



    /* Main Content */

    main {

      display: flex;

      gap: 30px;

      margin: 40px auto;

    }



    /* Left Column */

    .left-column {

      flex: 2;

    }



    .feature-intro {

      background-color: var(--white);

      border-radius: 10px;

      padding: 25px;

      margin-bottom: 25px;

      box-shadow: var(--card-shadow);

    }



    .feature-intro h3 {

      color: var(--primary);

      margin-bottom: 15px;

      font-size: 1.5rem;

    }



    .feature-intro p {

      margin-bottom: 20px;

    }



    .feature-intro ul {

      padding-left: 20px;

    }



    .feature-intro li {

      margin-bottom: 10px;

      position: relative;

      padding-left: 10px;

    }



    .feature-intro li:before {

      content: "•";

      color: var(--accent);

      font-weight: bold;

      position: absolute;

      left: -10px;

    }



    /* Feature Cards */

    .feature-card {

      background-color: var(--white);

      border-radius: 10px;

      padding: 25px;

      margin-bottom: 25px;

      box-shadow: var(--card-shadow);

      display: flex;

      justify-content: space-between;

      align-items: center;

      transition: transform 0.3s, box-shadow 0.3s;

    }



    .feature-card:hover {

      transform: translateY(-5px);

      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);

    }



    .feature-info {

      width: 60%;

    }



    .feature-info h4 {

      color: var(--primary);

      margin-bottom: 10px;

      font-size: 1.3rem;

    }



    .feature-image {

      width: 35%;

      height: 150px;

      border-radius: 8px;

      background-color: var(--light);

      display: flex;

      align-items: center;

      justify-content: center;

      color: var(--primary);

      font-weight: 500;

      overflow: hidden;

    }



    .feature-image-placeholder {

      width: 100%;

      height: 100%;

      display: flex;

      align-items: center;

      justify-content: center;

      background: linear-gradient(135deg, var(--light) 0%, var(--primary-light) 100%);

    }



    /* Right Column */

    .right-column {

      flex: 1;

    }



    .perks-box,

    .subscription-box,

    .faq-box {

      background-color: var(--white);

      border-radius: 10px;

      padding: 25px;

      margin-bottom: 25px;

      box-shadow: var(--card-shadow);

    }



    .perks-box h4,

    .subscription-box h4,

    .faq-box h4 {

      color: var(--primary);

      margin-bottom: 15px;

      font-size: 1.3rem;

    }



    .perks-box ul {

      padding-left: 20px;

    }



    .perks-box li {

      margin-bottom: 10px;

      position: relative;

      padding-left: 10px;

    }



    .perks-box li:before {

      content: "✓";

      color: var(--accent);

      font-weight: bold;

      position: absolute;

      left: -10px;

    }



    .subscription-box p {

      margin-bottom: 15px;

    }



    .subscription-btn {

      background-color: var(--primary);

      color: var(--white);

      border: none;

      padding: 10px 20px;

      border-radius: 5px;

      font-weight: 600;

      cursor: pointer;

      transition: background-color 0.3s;

    }



    .subscription-btn:hover {

      background-color: var(--primary-dark);

    }



    .faq-item {

      margin-bottom: 15px;

      padding-bottom: 15px;

      border-bottom: 1px solid var(--gray);

    }



    .faq-item:last-child {

      border-bottom: none;

    }



    .faq-item p {

      margin-bottom: 5px;

    }



    .faq-question {

      font-weight: bold;

      color: var(--primary);

    }



    .faq-form textarea {

      width: 100%;

      height: 40px;

      border: 1px solid var(--gray);

      border-radius: 5px;

      padding: 10px;

      margin-top: 10px;

      resize: vertical;

      font-family: inherit;

    }



    .submit-btn {

      background-color: var(--accent);

      color: var(--white);

      border: none;

      padding: 8px 20px;

      border-radius: 5px;

      font-weight: 600;

      cursor: pointer;

      margin-top: 10px;

      transition: background-color 0.3s;

    }



    .submit-btn:hover {

      background-color: #2ab4d1;

    }



    /* =========================

   FAQ ENHANCEMENT STYLES

   ========================= */



    .faq-box {

      border-left: 5px solid var(--accent);

    }



    .faq-item {

      padding: 12px 0;

    }



    .faq-question {

      font-size: 1.05rem;

    }



    .faq-item p:last-child {

      font-size: 0.95rem;

      color: var(--text);

    }



    /* Button riêng cho FAQ support */

    .faq-support-btn {

      background-color: var(--accent);

      color: var(--white);

      border: none;

      padding: 12px 28px;

      border-radius: 25px;

      font-size: 1rem;

      font-weight: 600;

      cursor: pointer;

      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);

      transition: all 0.3s ease;

    }



    .faq-support-btn:hover {

      background-color: #2ab4d1;

      transform: translateY(-2px);

      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);

    }



    /* Footer */

    footer {

      background-color: var(--primary-dark);

      color: var(--white);

      padding: 60px 0 20px;

      margin-top: 60px;

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



    .social-links {

      display: flex;

      gap: 15px;

      margin-top: 15px;

    }



    .social-links a {

      color: var(--light);

      font-size: 1.2rem;

      transition: color 0.3s;

    }



    .social-links a:hover {

      color: var(--accent);

    }



    /* Responsive Design */

    @media (max-width: 768px) {

      main {

        flex-direction: column;

      }



      .header-content {

        flex-direction: column;

        text-align: center;

      }



      nav {

        margin: 15px 0;

      }



      nav a {

        margin: 0 10px;

      }



      .hero h1 {

        font-size: 2.2rem;

      }



      .feature-card {

        flex-direction: column;

      }



      .feature-info,

      .feature-image {

        width: 100%;

      }



      .feature-image {

        margin-top: 15px;

        height: 120px;

      }

    }



    /* ============================================

   STYLES FOR NEW PRICING SECTION

   ============================================ */

    .pricing-section {

      padding: 60px 15px;

      text-align: center;

    }



    .section-title {

      font-size: 2.2rem;

      color: var(--primary);

      margin-bottom: 10px;

    }



    .section-subtitle {

      font-size: 1.1rem;

      color: var(--text);

      margin-bottom: 40px;

    }



    .pricing-grid {

      display: grid;

      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));

      gap: 30px;

      justify-content: center;

    }



    .pricing-card {

      background-color: var(--white);

      border-radius: 10px;

      padding: 30px;

      box-shadow: var(--card-shadow);

      text-align: left;

      border: 3px solid var(--white);

      transition: all 0.3s ease;

      position: relative;

    }



    .pricing-card:hover {

      transform: translateY(-8px);

      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);

    }



    .pricing-card.highlighted {

      border-color: var(--accent);

      box-shadow: 0 8px 30px rgba(51, 198, 231, 0.3);

    }



    .popular-badge {

      background-color: var(--accent);

      color: var(--white);

      font-weight: 600;

      font-size: 0.8rem;

      padding: 4px 12px;

      border-radius: 20px;

      position: absolute;

      top: -15px;

      left: 50%;

      transform: translateX(-50%);

    }



    .pricing-card h4 {

      font-size: 1.5rem;

      color: var(--primary);

      margin-bottom: 15px;

      text-align: center;

    }



    .pricing-card .price {

      font-size: 1.2rem;

      color: var(--primary-light);

      margin-bottom: 20px;

      text-align: center;

    }



    .pricing-card .price span {

      font-size: 2.5rem;

      font-weight: 700;

      color: var(--primary-dark);

    }



    .pricing-card .features-list {

      list-style: none;

      margin: 20px 0;

      padding: 0;

    }



    .pricing-card .features-list li {

      margin-bottom: 12px;

      display: flex;

      align-items: center;

      gap: 10px;

      color: var(--text);

    }



    .pricing-card .features-list li i {

      color: var(--accent);

      font-size: 1.2rem;

    }



    .pricing-card .features-list li.disabled {

      color: var(--gray);

      text-decoration: line-through;

    }



    .pricing-card .features-list li.disabled i {

      color: var(--gray);

    }



    .pricing-card .btn-primary,

    .pricing-card .btn-secondary {

      width: 100%;

      padding: 12px;

      font-size: 1rem;

      font-weight: 600;

      border-radius: 6px;

      cursor: pointer;

      transition: all 0.3s;

      border: none;

      margin-top: 10px;

    }



    .pricing-card .btn-primary {

      background-color: var(--primary);

      color: var(--white);

    }



    .pricing-card .btn-primary:hover {

      background-color: var(--primary-dark);

    }



    .pricing-card .highlighted .btn-primary {

      background-color: var(--accent);

      color: var(--white);

    }



    .pricing-card .highlighted .btn-primary:hover {

      background-color: #2ab4d1;

    }



    .pricing-card .btn-secondary {

      background-color: var(--background);

      color: var(--primary-light);

      border: 2px solid var(--light);

      cursor: not-allowed;

    }



    /* ============================================

   STYLES FOR PRICING MODAL

   ============================================ */

    .modal-overlay {

      position: fixed;

      top: 0;

      left: 0;

      width: 100%;

      height: 100%;

      background-color: rgba(1, 22, 30, 0.7);

      /* --primary-dark với opacity */

      z-index: 1000;

      display: flex;

      align-items: center;

      justify-content: center;

      opacity: 0;

      visibility: hidden;

      transition: all 0.3s ease;

    }



    .modal-overlay.active {

      opacity: 1;

      visibility: visible;

    }



    .modal-content {

      background-color: var(--background);

      padding: 20px;

      border-radius: 10px;

      width: 90%;

      max-width: 1000px;

      position: relative;

      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);

      transform: scale(0.9);

      transition: all 0.3s ease;

      overflow-y: auto;

      max-height: 90vh;

    }



    .modal-overlay.active .modal-content {

      transform: scale(1);

    }



    .modal-close {

      position: absolute;

      top: 15px;

      right: 20px;

      background: none;

      border: none;

      font-size: 2.5rem;

      color: var(--primary-light);

      cursor: pointer;

      line-height: 1;

    }



    .modal-close:hover {

      color: var(--primary-dark);

    }



    /* Tinh chỉnh lại pricing-section khi ở trong modal */

    .modal-content .pricing-section {

      padding: 20px 0 0 0;

    }



    .modal-content .section-title {

      font-size: 1.8rem;

    }



    .modal-content .section-subtitle {

      margin-bottom: 25px;

    }



    .modal-content .pricing-grid {

      gap: 20px;

    }



    .modal-content .pricing-card {

      padding: 20px;

    }



    /* ============================================

   STYLES CHO MODAL VÀ PRICING CARDS

   ============================================ */



    /* 3.1. Styles cho Modal (Lớp phủ, khung, nút đóng) */

    .modal-overlay {

      position: fixed;

      top: 0;

      left: 0;

      width: 100%;

      height: 100%;

      background-color: rgba(1, 22, 30, 0.7);

      /* Dùng màu --primary-dark với 70% opacity */

      z-index: 1000;

      display: flex;

      align-items: center;

      justify-content: center;

      opacity: 0;

      visibility: hidden;

      transition: all 0.3s ease;

    }



    .modal-overlay.active {

      opacity: 1;

      visibility: visible;

    }



    .modal-content {

      background-color: var(--background);

      /* Nền của modal giống nền body */

      padding: 20px;

      border-radius: 10px;

      width: 90%;

      max-width: 1000px;

      /* Giới hạn chiều rộng của modal */

      position: relative;

      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);

      transform: scale(0.9);

      transition: all 0.3s ease;

      overflow-y: auto;

      max-height: 90vh;

      /* Cho phép scroll nếu nội dung quá dài */

    }



    .modal-overlay.active .modal-content {

      transform: scale(1);

      /* Hiệu ứng phóng to khi hiện */

    }



    .modal-close {

      position: absolute;

      top: 15px;

      right: 20px;

      background: none;

      border: none;

      font-size: 2.5rem;

      color: var(--primary-light);

      cursor: pointer;

      line-height: 1;

      z-index: 1001;

    }



    .modal-close:hover {

      color: var(--primary-dark);

    }



    /* 3.2. Styles cho Pricing Section (Bên trong Modal) */

    .pricing-section {

      padding: 20px 0 0 0;

      text-align: center;

    }



    .section-title {

      font-size: 2.2rem;

      color: var(--primary);

      margin-bottom: 10px;

    }



    .section-subtitle {

      font-size: 1.1rem;

      color: var(--text);

      margin-bottom: 30px;

    }



    .pricing-grid {

      display: grid;

      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));

      gap: 20px;

      justify-content: center;

    }



    .pricing-card {

      background-color: var(--white);

      border-radius: 10px;

      padding: 25px;

      box-shadow: var(--card-shadow);

      text-align: left;

      border: 3px solid var(--white);

      transition: all 0.3s ease;

      position: relative;

    }



    .pricing-card.highlighted {

      border-color: var(--accent);

      box-shadow: 0 8px 30px rgba(51, 198, 231, 0.3);

    }



    .popular-badge {

      background-color: var(--accent);

      color: var(--white);

      font-weight: 600;

      font-size: 0.8rem;

      padding: 4px 12px;

      border-radius: 20px;

      position: absolute;

      top: -15px;

      left: 50%;

      transform: translateX(-50%);

    }



    .pricing-card h4 {

      font-size: 1.5rem;

      color: var(--primary);

      margin-bottom: 15px;

      text-align: center;

    }



    .pricing-card .price {

      font-size: 1.2rem;

      color: var(--primary-light);

      margin-bottom: 20px;

      text-align: center;

    }



    .pricing-card .price span {

      font-size: 2.5rem;

      font-weight: 700;

      color: var(--primary-dark);

    }



    .pricing-card .features-list {

      list-style: none;

      margin: 20px 0;

      padding: 0;

    }



    .pricing-card .features-list li {

      margin-bottom: 12px;

      display: flex;

      align-items: center;

      gap: 10px;

      color: var(--text);

    }



    .pricing-card .features-list li i {

      color: var(--accent);

      font-size: 1.2rem;

    }



    .pricing-card .features-list li.disabled {

      color: var(--gray);

      text-decoration: line-through;

    }



    .pricing-card .features-list li.disabled i {

      color: var(--gray);

    }



    .pricing-card .btn-primary,

    .pricing-card .btn-secondary {

      width: 100%;

      padding: 12px;

      font-size: 1rem;

      font-weight: 600;

      border-radius: 6px;

      cursor: pointer;

      transition: all 0.3s;

      border: none;

      margin-top: 10px;

    }



    .pricing-card .btn-primary {

      background-color: var(--primary);

      color: var(--white);

    }



    .pricing-card .btn-primary:hover {

      background-color: var(--primary-dark);

    }



    .pricing-card.highlighted .btn-primary {

      background-color: var(--accent);

      color: var(--white);

    }



    .pricing-card.highlighted .btn-primary:hover {

      background-color: #2ab4d1;

    }



    .pricing-card .btn-secondary {

      background-color: var(--background);

      color: var(--primary-light);

      border: 2px solid var(--light);

      cursor: not-allowed;

    }

    /* --- CSS CHO LỜI CHÀO --- */

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

  </style>

</head>



<body>

  <!-- Header -->

  <header>

    <div class="container header-content">

      <div class="logo">

        <span class="logo-icon">🤖</span>

        AI Buddy

      </div>

      <nav>

        <a href="AIBuddy_Homepage.php">Home</a>

        <a href="AIBuddy_Chatbot.php">Chatbot</a>

        <a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a>

        <a href="AIBuddy_Trial.php">Trial</a>

        <a href="AIBuddy_Profile.php">Profile</a>

        <a href="AIBuddy_About.php">About</a>

        <a href="AIBuddy_Contact.php">Contact</a>

      </nav>

     <?php if ($currentUserName): ?>

        <div class="user-greeting-badge">

            How's your day, <strong><?= htmlspecialchars($currentUserName) ?></strong>?

        </div>

      <?php else: ?>

        <button class="signin-btn" onclick="window.location.href='AIBuddy_SignIn.php'">

            Sign In

        </button>

      <?php endif; ?>





    </div>

  </header>



  <!-- Hero Banner -->

  <section class="hero">

    <div class="container hero-content">

      <h1>AI Buddy - Your Mental Health Companion</h1>

      <p>An intelligent AI assistant that helps you track emotions, relax, and improve your mental wellbeing every day

      </p>

      <button class="cta-button">Start Chatting Now</button>

    </div>

  </section>



  <!-- Main content layout -->

  <div class="container">

    <main>

      <!-- Left column -->

      <div class="left-column">

        <!-- Quick Introduction -->

        <div class="feature-intro">

          <h3>Key Features of AI Buddy</h3>

          <p>AI Buddy is a companion chatbot that helps you track emotions, relax, and suggests solutions to improve

            your mental health comprehensively.</p>

          <ul>

            <li><strong>NLP Emotion Chat:</strong> Natural conversation with intelligent AI that understands your

              feelings</li>

            <li><strong>Emotion Journaling:</strong> Track and analyze your daily moods</li>

            <li><strong>Focus/Meditation Recommendations:</strong> Personalized relaxation and focus exercises tailored

              for you</li>

          </ul>

        </div>



        <!-- Feature blocks -->

        <div class="feature-card">

          <div class="feature-info">

            <h4>Emotion Chatbot</h4>

            <p>Experience natural conversation with advanced NLP technology. AI Buddy not only listens but understands

              your emotions, providing helpful advice and immediate support when you need it.</p>

          </div>

          <div class="feature-image">

            <div class="feature-image-placeholder">Friendly Chat UI</div>

          </div>

        </div>



        <div class="feature-card">

          <div class="feature-info">

            <h4>Emotion Tracker</h4>

            <p>Record your daily moods and view your emotion charts by week and month. Analyze emotional trends to help

              you identify factors affecting your mental state.</p>

          </div>

          <div class="feature-image">

            <div class="feature-image-placeholder">Visual Mood Chart</div>

          </div>

        </div>



        <div class="feature-card">

          <div class="feature-info">

            <h4>Focus / Meditation</h4>

            <p>Join short focus and meditation sessions with voice guidance and breathing animations. Reduce stress and

              enhance concentration in just a few minutes each day.</p>

          </div>

          <div class="feature-image">

            <div class="feature-image-placeholder">Relaxing Breathing Animation</div>

          </div>

        </div>

      </div>



      <!-- Right column -->

      <div class="right-column">

        <div class="perks-box">

          <h4>Benefits</h4>

          <p>We're here to help you:</p>

          <ul>

            <li>Receive daily focus and relaxation reminders</li>

            <li>Securely store your emotion journal</li>

            <li>Access personalized virtual coaching</li>

            <li>Track your mental health improvement progress</li>

            <li>Access a rich library of exercises</li>

          </ul>

        </div>



        <div class="subscription-box">
  <h4>Enhanced Experience</h4>
  <p>Experience the premium version with exclusive features and premium content.</p>
  
  <button class="subscription-btn" onclick="window.location.href='AIBuddy_Trial.php'">
    View All Plans
  </button>
</div>



        <div class="faq-box">

          <h4>Frequently Asked Questions</h4>



          <div class="faq-item">

            <p class="faq-question">What is AI Buddy?</p>

            <p>AI Buddy is a mental health support application that uses artificial intelligence to listen, understand,

              and accompany you on your mental wellness journey.</p>

          </div>



          <div class="faq-item">

            <p class="faq-question">Do I need to log in for Focus Sessions?</p>

            <p>You can try some basic features without logging in, but to save your progress and access all features,

              you need to create an account.</p>

          </div>



          

          <div style="text-align:center; margin-top:40px;">

            <p style="margin-bottom:15px; font-size:1.05rem;">

              Can't find the answer you're looking for?

            </p>

            <a href="AIBuddy_Contact.php">

              <button class="faq-support-btn">

                Contact Customer Support

              </button>

            </a>

          </div>



        </div>

      </div>

    </main>

    
      </div>


  <!-- Footer -->

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

            <li><a href="#">Cookie Policy</a></li>

            <li><a href="#">Disclaimer</a></li>

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

        <p>&copy; 2025 AI Buddy. All rights reserved. | Mental Health Companion</p>

      </div>

    </div>

  </footer>



  <!-- Modal HTML (moved out of <script>) -->

  <div class="modal-overlay" id="pricing-modal-overlay">

    <div class="modal-content" id="pricing-modal-content">

      <button class="modal-close" id="modal-close-btn">&times;</button>



      <section class="pricing-section">

        <h2 class="section-title">Find Your Plan</h2>

        <p class="section-subtitle">Start for free and unlock more power as you grow.</p>



        <div class="pricing-grid">

          <div class="pricing-card">

            <h4>🌼 Free Plan</h4>

            <p class="price"><span>$0</span>/month</p>

            <ul class="features-list">

              <li><i class="fas fa-check"></i> 2 short sessions (5 mins)</li>

              <li><i class="fas fa-check"></i> 2 times breathe animation</li>

              <li class="disabled"><i class="fas fa-times"></i> Analyze emotional trends</li>

              <li class="disabled"><i class="fas fa-times"></i> Deeper emotional insights</li>

            </ul>

            <button class="btn-secondary" disabled>Your Current Plan</button>

          </div>



          <div class="pricing-card highlighted">

            <span class="popular-badge">Most Popular</span>

            <h4>🌿 Essential Plan</h4>

            <p class="price"><span>$5.49</span>/month</p>

            <ul class="features-list">

              <li><i class="fas fa-check"></i> 10 session & background music</li>

              <li><i class="fas fa-check"></i> Emotional chat & Mood chat</li>

              <li><i class="fas fa-check"></i> Select multiple persona & topic</li>

            </ul>

            <button class="btn-primary" onclick="location.href='AIBuddy_Checkout.php'">Upgrade Now</button>

          </div>



          <div class="pricing-card">

            <h4>🌸 Premium Plan</h4>

            <p class="price"><span>$8.49</span>/month</p>

            <ul class="features-list">

              <li><i class="fas fa-check"></i> 30 diverse sessions</li>

              <li><i class="fas fa-check"></i> Select multiple persona + topic</li>

              <li><i class="fas fa-check"></i> Automatic focus reminders</li>

              <li><i class="fas fa-check"></i> Analyze emotional trends</li>

              <li><i class="fas fa-check"></i> Deeper emotional insights</li>



            </ul>

            <button class="btn-primary" onclick="location.href='AIBuddy_Checkout.php'">Get Premium</button>

          </div>

        </div>

      </section>



    </div>

  </div>



  <script>

    // --- Script cũ của ông (CTA, FAQ) ---

    document.querySelector('.cta-button').addEventListener('click', function () {

      window.location.href = 'AIBuddy_Chatbot.php';

    });



    // --- Script cũ cho nút "submit-btn" (FAQ) ---

    document.querySelector('.submit-btn').addEventListener('click', function () {

      const question = document.querySelector('.faq-form textarea').value;

      if (question.trim() !== '') {

        alert('Thank you for your question! We will respond as soon as possible.');

        document.querySelector('.faq-form textarea').value = '';

      } else {

        alert('Please enter your question!');

      }

    });



    // --- Script mới cho Modal (POP-UP) ---

    const openBtn = document.getElementById('open-pricing-modal-btn');

    const closeBtn = document.getElementById('modal-close-btn');

    const overlay = document.getElementById('pricing-modal-overlay');



    if (openBtn && closeBtn && overlay) {



      // Mở modal khi nhấn "View All Plans"

      openBtn.addEventListener('click', function () {

        overlay.classList.add('active');

      });



      // Đóng modal khi nhấn nút "X"

      closeBtn.addEventListener('click', function () {

        overlay.classList.remove('active');

      });



      // Đóng modal khi nhấn vào lớp phủ (bên ngoài)

      overlay.addEventListener('click', function (e) {

        if (e.target === overlay) {

          overlay.classList.remove('active');

        }

      });

    }



  </script>

</body>



</html>