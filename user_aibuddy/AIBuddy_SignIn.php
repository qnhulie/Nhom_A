<?php
ob_start();
session_start();
require_once 'config/db.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Chuẩn bị câu lệnh SQL
    $stmt = $conn->prepare("SELECT UserID, UserName, UserPassword FROM users WHERE UserEmail = ?");
    
    if ($stmt) {
        // 2. Gán tham số
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // 3. Lấy kết quả
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // 4. Kiểm tra mật khẩu
        if ($user && password_verify($password, $user['UserPassword'])) {
            // --- ĐĂNG NHẬP THÀNH CÔNG ---
            
            // Lưu session (Lưu ý: key là 'userid' chữ thường)
            $_SESSION['userid'] = $user['UserID'];
            $_SESSION['username'] = $user['UserName'];
            
            // Đóng session để đảm bảo dữ liệu được lưu ngay lập tức
            session_write_close();

            // --- SỬA FLOW TẠI ĐÂY: Chuyển hướng thẳng sang Chatbot ---
            echo "<script>
                    // alert('Đăng nhập thành công!'); // Có thể bỏ alert nếu muốn trải nghiệm mượt hơn
                    window.location.href = 'AIBuddy_Chatbot.php';
                  </script>";
            exit();
            
        } else {
            $error = "Email hoặc Mật khẩu không đúng!";
        }
        
        $stmt->close();
    } else {
        $error = "Lỗi kết nối hệ thống.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Buddy - Sign In</title>
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

        /* Header Styles (giống Homepage) */
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

        /* Sign In Section - Phần chính */
        .signin-section {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            padding: 40px 0;
            margin: 20px 0;
        }

        .signin-container {
            width: 100%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signin-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 40px;
            width: 100%;
            box-shadow: var(--card-shadow);
            border-top: 5px solid var(--accent);
            position: relative;
            overflow: hidden;
        }

        .signin-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--accent), var(--primary-light));
        }

        .signin-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .signin-header h1 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        .signin-header p {
            color: var(--primary-light);
            font-size: 1.1rem;
        }

        .signin-icon {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary);
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-light);
            font-size: 1.2rem;
        }

        .form-input {
            width: 100%;
            padding: 14px 15px 14px 50px;
            border: 2px solid var(--gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: rgba(174, 195, 176, 0.05);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(51, 198, 231, 0.2);
            background-color: var(--white);
        }

        .form-input::placeholder {
            color: var(--primary-light);
            opacity: 0.7;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--primary-light);
            cursor: pointer;
            font-size: 1.2rem;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
        }

        .remember-me input {
            accent-color: var(--accent);
        }

        .forgot-password {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        /* Sign In Button */
        .signin-form-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        .signin-form-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(1, 22, 30, 0.2);
        }

        .signin-form-btn:active {
            transform: translateY(0);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: var(--primary-light);
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background-color: var(--gray);
        }

        .divider span {
            padding: 0 15px;
            font-size: 0.9rem;
        }

        /* Social Login */
        .social-login {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid var(--gray);
            border-radius: 8px;
            background-color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            color: var(--primary);
        }

        .social-btn:hover {
            border-color: var(--accent);
            background-color: rgba(51, 198, 231, 0.05);
        }

        .social-btn.google i {
            color: #DB4437;
        }

        .social-btn.apple i {
            color: #000000;
        }

        /* Sign Up Link */
        .signup-link {
            text-align: center;
            font-size: 1rem;
            color: var(--primary-light);
            margin-top: 20px;
        }

        .signup-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: color 0.3s;
        }

        .signup-link a:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        /* Footer (giống Homepage) */
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

            .signin-card {
                padding: 30px 25px;
            }

            .signin-header h1 {
                font-size: 1.8rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .social-login {
                flex-direction: column;
            }

            .signin-btn {
                margin: 10px 0 0 0;
            }
        }

        @media (max-width: 480px) {
            .signin-card {
                padding: 25px 20px;
            }

            .signin-header h1 {
                font-size: 1.6rem;
            }

            .signin-icon {
                font-size: 2.5rem;
            }

            .form-input {
                padding: 12px 15px 12px 45px;
            }

            nav a {
                margin: 0 5px 5px;
                font-size: 0.85rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .signin-card {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }
    </style>
</head>

<body>
    <!-- Header với navigation  -->
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
            <a href="AIBuddy_SignIn.php">
                <button class="signin-btn">Sign In</button>
            </a>
        </div>
    </header>

    <!-- Sign In Section -->
    <section class="signin-section">
        <div class="container signin-container">
            <div class="signin-card">
                <div class="signin-header">
                    <div class="signin-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h1>Welcome Back</h1>
                    <p>Sign in to continue your mental wellness journey with AI Buddy</p>
                </div>
                <?php if (!empty($error)): ?>
    <div style="
        background:#F44336;
        color:white;
        padding:12px;
        border-radius:6px;
        margin-bottom:20px;
        text-align:center;
    ">
        <?= $error ?>
    </div>
<?php endif; ?>

                <form method="POST" action="AIBuddy_SignIn.php">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input
    type="email"
    id="email"
    name="email"
    class="form-input"
    placeholder="you@example.com"
    required
>

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input
    type="password"
    id="password"
    name="password"
    class="form-input"
    placeholder="Enter your password"
    required
>

                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" id="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="AIBuddy_ChangePassword.php" class="forgot-password">Forgot Password?</a>                    
                    </div>

                    <button type="submit" class="signin-form-btn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>


                    </div>

                    <div class="signup-link">
                        Don't have an account?
                        <a href="AiBuddy_SignUp.php" id="signupLink">Sign up now</a>
                    </div>
                </form>
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

    <script>
        // Toggle hiển thị mật khẩu
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
              
        // Thêm CSS cho animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
        
        // Responsive navigation cho mobile
        function adjustNavigation() {
            const nav = document.querySelector('nav');
            const headerContent = document.querySelector('.header-content');
            
            if (window.innerWidth <= 768) {
                if (nav) {
                    nav.style.display = 'flex';
                    nav.style.flexWrap = 'wrap';
                    nav.style.justifyContent = 'center';
                }
            }
        }
        
        // Chạy khi load và khi resize
        window.addEventListener('load', adjustNavigation);
        window.addEventListener('resize', adjustNavigation);
    </script>
</body>
</html>