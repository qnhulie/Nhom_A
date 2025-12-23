<?php
session_start();
require_once 'config/db.php';

$currentUserName = null;

// Kiểm tra xem đã đăng nhập chưa
if (isset($_SESSION['userid'])) {
    $userID = $_SESSION['userid'];
    // Lấy tên mới nhất từ Database
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

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AI Buddy · About Us</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
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
      --radius: 10px;
    }

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

    .hero {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      color: var(--white);
      text-align: center;
      padding: 80px 0;
      margin: 20px auto;
      border-radius: var(--radius);
      box-shadow: var(--card-shadow);
    }

    .hero h1 {
      font-size: 2.6rem;
      margin-bottom: 15px;
    }

    .hero p {
      font-size: 1.1rem;
      opacity: 0.95;
      max-width: 700px;
      margin: 0 auto;
    }

    section.card {
      background-color: var(--white);
      border-radius: var(--radius);
      box-shadow: var(--card-shadow);
      padding: 30px;
      margin-bottom: 30px;
    }

    .section-title {
      color: var(--primary);
      margin-bottom: 10px;
      font-size: 1.6rem;
      font-weight: 600;
    }



    .pill {
      display: inline-block;
      background-color: var(--light);
      color: var(--primary-dark);
      border-radius: 20px;
      padding: 6px 12px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .two,
    .three {
      display: grid;
      gap: 20px;
    }

    .two {
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }

    .three {
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }

    .value,
    .member {
      background-color: var(--white);
      border-radius: var(--radius);
      border: 1px solid var(--gray);
      padding: 20px;
      text-align: center;
      transition: 0.3s;
    }

    .value:hover,
    .member:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08);
    }

    .value h4 {
      color: var(--primary);
      margin-bottom: 10px;
      font-size: 1.2rem;
    }

    .avatar {
      width: 80px;
      height: 80px;
      margin: 0 auto 10px;
      border-radius: 50%;
      background-color: var(--light);
      color: var(--primary-dark);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      font-weight: 700;
    }

    .member h4 {
      color: var(--primary);
      margin-bottom: 5px;
    }

    .member small {
      color: var(--text);
    }

    .highlight .avatar {
      background-color: var(--accent);
      color: var(--white);
      box-shadow: 0 0 10px rgba(51, 198, 231, 0.4);
    }

    .cta {
      background: linear-gradient(135deg, var(--light), #dfeee0);
      text-align: center;
      border-radius: var(--radius);
      box-shadow: var(--card-shadow);
      padding: 40px 20px;
    }

    .cta .btn {
      background-color: var(--primary);
      color: var(--white);
      border: none;
      padding: 10px 25px;
      border-radius: 20px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s;
      margin-top: 15px;
    }

    .cta .btn:hover {
      background-color: var(--primary-dark);
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

    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2rem;
      }
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


  <section class="hero">
    <div class="container">
      <h1>About AI Buddy</h1>
      <p>We are a team of students passionate about creating AI solutions that support emotional wellbeing and help
        people find balance in life.</p>
    </div>
  </section>

  <div class="container">
    <section class="card two">
      <div>
        <span class="pill">Mission</span>
        <h3 class="section-title">Technology that heals, not harms</h3>
        <p>AI Buddy blends empathetic AI with psychology to help students and young professionals manage emotions, build
          calm habits, and rediscover balance in daily life.</p>
      </div>
      <div>
        <span class="pill">Vision</span>
        <h3 class="section-title">A kind digital companion for everyone</h3>
        <p>We envision a world where everyone can access a compassionate AI companion that listens, supports, and grows
          alongside them.</p>
      </div>
    </section>

    <section class="card">
      <span class="pill">Our Values</span>
      <h3 class="section-title">What We Believe In</h3>
      <div class="three">
        <div class="value">
          <h4>Empathy First</h4>
          <p>Every feature is designed with compassion - to comfort, not overwhelm. We listen before we advise.</p>
        </div>
        <div class="value">
          <h4>Privacy & Trust</h4>
          <p>Your data is yours. We keep your journal secure, encrypted, and under your full control.</p>
        </div>
        <div class="value">
          <h4>Evidence-Based Design</h4>
          <p>Our product is inspired by CBT, mindfulness, and behavioral science to create a truly supportive
            experience.</p>
        </div>
      </div>
    </section>

    <section class="card">
      <span class="pill">Team</span>
      <h3 class="section-title">The Humans Behind the Buddy</h3>
      <div class="three">
        <div class="member">
          <div class="avatar">P</div>
          <h4>Phương Anh</h4>
          <small>Content & Community</small>
        </div>
        <div class="member">
          <div class="avatar">Đ</div>
          <h4>Tiến Đạt</h4>
          <small>Engineering & Data</small>
        </div>
        <div class="member highlight">
          <div class="avatar">Q</div>
          <h4>Quỳnh Như</h4>
          <small><strong>Project Leader</strong></small>
        </div>
      </div>

      <div class="two" style="margin-top:20px;">
        <div class="member">
          <div class="avatar">N</div>
          <h4>Trọng Nguyên</h4>
          <small>UX & Interface Design</small>
        </div>
        <div class="member">
          <div class="avatar">T</div>
          <h4>Trúc Quỳnh</h4>
          <small>Research & Product Strategy</small>
        </div>
      </div>
    </section>

    <section class="cta">
      <h3>Want to collaborate or learn more?</h3>
      <p>Email us at <a href="mailto:support@aibuddy.com"
          style="color:var(--primary);font-weight:600;">support@aibuddy.com</a></p>
      <a href="AIBuddy_Contact.php"><button class="btn">Contact Us</button></a>
    </section>
  </div>

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

</body>

</html>