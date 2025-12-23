<?php
session_start();
require_once 'config/db.php';

$currentUserName = null;

// Kiểm tra đăng nhập và lấy tên người dùng
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
    <title>AI Buddy - Privacy Policy</title>
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

        /* Privacy Content */
        .privacy-section {
            padding: 40px 0;
            margin: 20px 0;
            flex: 1;
        }

        .privacy-container {
            background-color: var(--white);
            border-radius: 10px;
            padding: 40px;
            box-shadow: var(--card-shadow);
            border-top: 5px solid var(--accent);
        }

        .privacy-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light);
        }

        .privacy-header h1 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 2.5rem;
        }

        .privacy-header p {
            color: var(--primary-light);
            font-size: 1.1rem;
        }

        .last-updated {
            font-size: 0.9rem;
            color: var(--gray);
            margin-top: 10px;
        }

        /* Privacy Content */
        .privacy-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .privacy-section-block {
            margin-bottom: 35px;
        }

        .privacy-section-block h2 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.5rem;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--light);
        }

        .privacy-section-block h3 {
            color: var(--primary);
            margin: 20px 0 10px;
            font-size: 1.2rem;
        }

        .privacy-section-block p {
            margin-bottom: 15px;
            text-align: justify;
        }

        .privacy-section-block ul {
            margin-left: 25px;
            margin-bottom: 15px;
        }

        .privacy-section-block li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 10px;
        }

        .privacy-section-block li:before {
            content: "•";
            color: var(--accent);
            font-weight: bold;
            position: absolute;
            left: -10px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .data-table th, .data-table td {
            border: 1px solid var(--light);
            padding: 12px;
            text-align: left;
        }

        .data-table th {
            background-color: rgba(174, 195, 176, 0.1);
            color: var(--primary);
            font-weight: 600;
        }

        .data-table tr:nth-child(even) {
            background-color: rgba(174, 195, 176, 0.05);
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

            .privacy-container {
                padding: 25px;
            }

            .privacy-header h1 {
                font-size: 2rem;
            }

            .privacy-section-block h2 {
                font-size: 1.3rem;
            }

            .data-table {
                display: block;
                overflow-x: auto;
            }

            .signin-btn {
                margin: 10px 0 0 0;
            }
        }

        @media (max-width: 480px) {
            .privacy-container {
                padding: 20px;
            }

            .privacy-header h1 {
                font-size: 1.8rem;
            }

            .privacy-section-block {
                margin-bottom: 25px;
            }

            nav a {
                margin: 0 5px 5px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header với navigation mới -->
    <header>
        <div class="container header-content">
            <a href="Prototype_Homepage.html" class="logo">
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
            <a href="AIBuddy_SignIn.php">
                <button class="signin-btn">Sign In</button>
            </a>
        </div>
    </header>

    <!-- Privacy Policy Content -->
    <section class="privacy-section">
        <div class="container">
            <div class="privacy-container">
                <div class="privacy-header">
                    <h1>Privacy Policy</h1>
                    <p>How we collect, use, and protect your personal information</p>
                    <div class="last-updated">Last Updated: March 2025</div>
                </div>

                <div class="privacy-content">
                    <div class="important-note">
                        <h4><i class="fas fa-shield-alt"></i> Our Commitment to Your Privacy</h4>
                        <p>At AI Buddy, we take your privacy seriously, especially given the sensitive nature of mental health data. This policy explains what information we collect, how we use it, and the measures we take to protect it.</p>
                    </div>

                    <div class="privacy-section-block">
                        <h2>1. Information We Collect</h2>
                        <p>We collect information to provide better services to our users:</p>
                        
                        <h3>1.1 Information You Provide</h3>
                        <ul>
                            <li><strong>Account Information:</strong> Name, email, password, date of birth, gender</li>
                            <li><strong>Profile Information:</strong> Preferences, wellness goals, subscription details</li>
                            <li><strong>Health Data:</strong> Emotion tracking entries, mood logs, meditation history</li>
                            <li><strong>Communication:</strong> Chat interactions with AI Buddy, feedback, support requests</li>
                        </ul>
                        
                        <h3>1.2 Automatically Collected Information</h3>
                        <ul>
                            <li><strong>Usage Data:</strong> Feature usage, session duration, interaction patterns</li>
                            <li><strong>Device Information:</strong> Device type, operating system, browser type</li>
                            <li><strong>Location Data:</strong> General location (country/region) for service optimization</li>
                        </ul>
                    </div>

                    <div class="privacy-section-block">
                        <h2>2. How We Use Your Information</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Purpose</th>
                                    <th>Information Used</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Provide and personalize services</td>
                                    <td>Profile data, usage patterns, emotion logs</td>
                                </tr>
                                <tr>
                                    <td>Improve AI responses and recommendations</td>
                                    <td>Chat interactions, feedback, usage analytics</td>
                                </tr>
                                <tr>
                                    <td>Send important notifications</td>
                                    <td>Email address, notification preferences</td>
                                </tr>
                                <tr>
                                    <td>Research and development</td>
                                    <td>Anonymized, aggregated data only</td>
                                </tr>
                                <tr>
                                    <td>Comply with legal obligations</td>
                                    <td>Account information, usage records</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="privacy-section-block">
                        <h2>3. Data Security</h2>
                        <p>We implement robust security measures to protect your data:</p>
                        <ul>
                            <li><strong>Encryption:</strong> All data is encrypted in transit and at rest</li>
                            <li><strong>Access Controls:</strong> Strict access controls limit who can view your data</li>
                            <li><strong>Regular Audits:</strong> Security audits and vulnerability assessments</li>
                            <li><strong>Data Minimization:</strong> We only collect data necessary for our services</li>
                        </ul>
                    </div>

                    <div class="privacy-section-block">
                        <h2>4. Sensitive Health Data</h2>
                        <p>We handle mental health data with special care:</p>
                        <ul>
                            <li>Emotion tracking data is used only to personalize your experience</li>
                            <li>Chat conversations with AI Buddy are processed anonymously for training</li>
                            <li>You can export or delete your health data at any time</li>
                            <li>We never sell your personal or health data to third parties</li>
                        </ul>
                    </div>

                    <div class="privacy-section-block">
                        <h2>5. Data Sharing and Disclosure</h2>
                        <p>We do not sell your personal information. We may share data only in these circumstances:</p>
                        <ul>
                            <li><strong>With Your Consent:</strong> When you explicitly permit sharing</li>
                            <li><strong>Service Providers:</strong> Trusted partners who assist our operations (under strict confidentiality)</li>
                            <li><strong>Legal Requirements:</strong> When required by law or to protect rights</li>
                            <li><strong>Business Transfers:</strong> In connection with mergers or acquisitions</li>
                        </ul>
                    </div>

                    <div class="privacy-section-block">
                        <h2>6. Your Rights and Choices</h2>
                        <p>You have control over your data:</p>
                        <ul>
                            <li><strong>Access:</strong> View the personal data we hold about you</li>
                            <li><strong>Correction:</strong> Update or correct inaccurate information</li>
                            <li><strong>Deletion:</strong> Request deletion of your account and data</li>
                            <li><strong>Export:</strong> Export your data in a portable format</li>
                            <li><strong>Opt-Out:</strong> Control marketing communications and analytics</li>
                        </ul>
                    </div>

                    <div class="privacy-section-block">
                        <h2>7. Data Retention</h2>
                        <p>We retain your information only as long as necessary:</p>
                        <ul>
                            <li>Active accounts: Data retained while account is active</li>
                            <li>Inactive accounts: Deleted after 24 months of inactivity</li>
                            <li>Legal requirements: Some data retained to comply with laws</li>
                            <li>You can request immediate deletion at any time</li>
                        </ul>
                    </div>

                    <div class="privacy-section-block">
                        <h2>8. Children's Privacy</h2>
                        <p>AI Buddy is not intended for children under 13. We do not knowingly collect data from children under 13. If we learn we have collected such data, we will delete it promptly.</p>
                    </div>

                    <div class="privacy-section-block">
                        <h2>9. International Data Transfers</h2>
                        <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place for such transfers.</p>
                    </div>

                    <div class="privacy-section-block">
                        <h2>10. Changes to This Policy</h2>
                        <p>We may update this Privacy Policy periodically. We will notify you of significant changes via email or in-app notification.</p>
                    </div>

                    <div class="privacy-section-block">
                        <h2>11. Contact Us</h2>
                        <p>If you have questions about this Privacy Policy or your data:</p>
                        <p><strong>Data Protection Officer:</strong> privacy@aibuddy.com<br>
                        <strong>Mailing Address:</strong> 123 Wellness Street, Mindful District, CA 90210<br>
                        <strong>Response Time:</strong> We aim to respond within 30 days</p>
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
                        <li><a href="AIBuddy_Terms of Service.html">Terms of Service</a></li>
                        <li><a href="AIBuddy_PrivacyPolicy.html">Privacy Policy</a></li>
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