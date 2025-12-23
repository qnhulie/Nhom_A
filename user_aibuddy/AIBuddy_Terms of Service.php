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

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Buddy - Terms of Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Color Variables từ Homepage */
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header với navigation mới */
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
            text-decoration: none;
        }

        .logo-icon {
            margin-right: 8px;
            font-size: 28px;
        }

        nav {
            display: flex;
            align-items: center;
        }

        nav a {
            margin: 0 10px;
            text-decoration: none;
            color: var(--primary);
            font-weight: 500;
            transition: color 0.3s;
            white-space: nowrap;
            font-size: 0.95rem;
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
            margin-left: 10px;
        }

        .signin-btn:hover {
            background-color: #2ab4d1;
        }

        /* Terms Content */
        .terms-section {
            padding: 40px 0;
            margin: 20px 0;
            flex: 1;
        }

        .terms-container {
            background-color: var(--white);
            border-radius: 10px;
            padding: 40px;
            box-shadow: var(--card-shadow);
            border-top: 5px solid var(--accent);
        }

        .terms-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light);
        }

        .terms-header h1 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 2.5rem;
        }

        .terms-header p {
            color: var(--primary-light);
            font-size: 1.1rem;
        }

        .last-updated {
            font-size: 0.9rem;
            color: var(--gray);
            margin-top: 10px;
        }

        /* Terms Content */
        .terms-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .terms-section-block {
            margin-bottom: 35px;
        }

        .terms-section-block h2 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.5rem;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--light);
        }

        .terms-section-block h3 {
            color: var(--primary);
            margin: 20px 0 10px;
            font-size: 1.2rem;
        }

        .terms-section-block p {
            margin-bottom: 15px;
            text-align: justify;
        }

        .terms-section-block ul {
            margin-left: 25px;
            margin-bottom: 15px;
        }

        .terms-section-block li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 10px;
        }

        .terms-section-block li:before {
            content: "•";
            color: var(--accent);
            font-weight: bold;
            position: absolute;
            left: -10px;
        }

        .important-note {
            background-color: rgba(174, 195, 176, 0.1);
            border-left: 4px solid var(--accent);
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
        }

        .important-note h4 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        /* Footer với Legal Section */
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

        /* Back Button */
        .back-button {
            text-align: center;
            margin-top: 40px;
        }

        .back-btn {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .back-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(1, 22, 30, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            nav a {
                margin: 0 8px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            nav {
                margin: 15px 0;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }

            nav a {
                margin: 0 8px 5px;
                font-size: 0.9rem;
            }

            .terms-container {
                padding: 25px;
            }

            .terms-header h1 {
                font-size: 2rem;
            }

            .terms-section-block h2 {
                font-size: 1.3rem;
            }

            .signin-btn {
                margin: 10px 0 0 0;
            }
        }

        @media (max-width: 480px) {
            .terms-container {
                padding: 20px;
            }

            .terms-header h1 {
                font-size: 1.8rem;
            }

            .terms-section-block {
                margin-bottom: 25px;
            }

            nav a {
                margin: 0 5px 5px;
                font-size: 0.85rem;
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
    <!-- Header với navigation mới -->
    <header>
        <div class="container header-content">
            <a href="AIBuddy_Homepage.php" class="logo">
                <span class="logo-icon">🤖</span>
                AI Buddy
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
            <?php if ($currentUserName): ?>
    <div class="user-greeting-badge">
        How's your day, <strong><?= htmlspecialchars($currentUserName) ?></strong>?
    </div>
<?php else: ?>
    <a href="AIBuddy_SignIn.php">
        <button class="signin-btn">Sign In</button>
    </a>
<?php endif; ?>
        </div>
    </header>

    <!-- Terms of Service Content -->
    <section class="terms-section">
        <div class="container">
            <div class="terms-container">
                <div class="terms-header">
                    <h1>Terms of Service</h1>
                    <p>Please read these terms carefully before using AI Buddy services</p>
                    <div class="last-updated">Last Updated: March 2025</div>
                </div>

                <div class="terms-content">
                    <div class="important-note">
                        <h4><i class="fas fa-exclamation-circle"></i> Important Notice</h4>
                        <p>AI Buddy is a mental wellness companion application designed to provide support and tools for emotional well-being. It is NOT a substitute for professional medical advice, diagnosis, or treatment. If you are experiencing a mental health emergency, please contact emergency services immediately.</p>
                    </div>

                    <div class="terms-section-block">
                        <h2>1. Acceptance of Terms</h2>
                        <p>By accessing or using AI Buddy ("the Service"), you agree to be bound by these Terms of Service. If you disagree with any part of the terms, you may not access the Service.</p>
                    </div>

                    <div class="terms-section-block">
                        <h2>2. Description of Service</h2>
                        <p>AI Buddy provides:</p>
                        <ul>
                            <li>AI-powered conversational support for emotional well-being</li>
                            <li>Emotion tracking and mood journaling tools</li>
                            <li>Meditation and focus exercises</li>
                            <li>Personalized mental wellness insights</li>
                        </ul>
                    </div>

                    <div class="terms-section-block">
                        <h2>3. User Responsibilities</h2>
                        <h3>3.1 Account Security</h3>
                        <p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>
                        
                        <h3>3.2 Appropriate Use</h3>
                        <p>You agree not to use the Service to:</p>
                        <ul>
                            <li>Harm yourself or others</li>
                            <li>Harass, threaten, or intimidate others</li>
                            <li>Share false or misleading information</li>
                            <li>Violate any applicable laws or regulations</li>
                        </ul>
                    </div>

                    <div class="terms-section-block">
                        <h2>4. Privacy and Data</h2>
                    <p>
                    Your privacy is important to us.
                    Please review our
                    <a href="AIBuddy_PrivacyPolicy.php" style="color: var(--accent);">
                        Privacy Policy
                    </a>
                     to understand how we collect, use, and protect your personal information.
                    </p>                        
                    <p>By using AI Buddy, you consent to:</p>
                        <ul>
                            <li>The collection of emotion tracking data for personalized insights</li>
                            <li>Anonymous, aggregated data usage for service improvement</li>
                            <li>Secure storage of your personal information</li>
                        </ul>
                    </div>

                    <div class="terms-section-block">
                        <h2>5. Limitations of Service</h2>
                        <p>AI Buddy is designed as a supportive tool, not a replacement for:</p>
                        <ul>
                            <li>Licensed mental health professionals</li>
                            <li>Medical diagnosis or treatment</li>
                            <li>Emergency mental health services</li>
                            <li>Crisis intervention</li>
                        </ul>
                    </div>

                    <div class="terms-section-block">
                        <h2>6. Subscription and Payments</h2>
                        <p>Certain features of AI Buddy require a paid subscription. By subscribing, you agree to:</p>
                        <ul>
                            <li>Pay all applicable fees</li>
                            <li>Automatic renewal unless canceled</li>
                            <li>Comply with our refund policy</li>
                        </ul>
                    </div>

                    <div class="terms-section-block">
                        <h2>7. Termination</h2>
                        <p>We reserve the right to terminate or suspend your account immediately, without prior notice, for conduct that we believe violates these Terms or is harmful to other users.</p>
                    </div>

                    <div class="terms-section-block">
                        <h2>8. Changes to Terms</h2>
                        <p>We may update these Terms of Service from time to time. We will notify you of any changes by posting the new Terms on this page and updating the "Last Updated" date.</p>
                    </div>

                    <div class="terms-section-block">
                        <h2>9. Contact Information</h2>
                        <p>If you have any questions about these Terms, please contact us at:</p>
                        <p><strong>Email:</strong> legal@aibuddy.com<br>
                        <strong>Address:</strong> 123 Wellness Street, Mindful District, CA 90210</p>
                    </div>

                    <div class="back-button">
                        <button class="back-btn" onclick="window.history.back()">
                            <i class="fas fa-arrow-left"></i> Back to Previous Page
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

      <!-- Footer với Legal Section -->
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