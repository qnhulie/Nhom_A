<?php
session_start();
require_once 'config/db.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // LẤY DATA TỪ FORM
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $secret_answer = trim($_POST['secret_answer'])? $_POST['secret_answer'] : NULL; // <--- THÊM DÒNG NÀY
    // Xử lý giá trị null nếu không nhập
    $birth    = !empty($_POST['birthdate']) ? $_POST['birthdate'] : NULL;
    $phone    = !empty($_POST['phone']) ? $_POST['phone'] : NULL;
    $gender   = !empty($_POST['gender']) ? $_POST['gender'] : NULL;

    // VALIDATE CƠ BẢN
    // VALIDATE CƠ BẢN
    if (!$username || !$email || !$password || !$secret_answer) { // <--- THÊM BIẾN NÀY VÀO CHECK
        $error = "Vui lòng nhập đầy đủ thông tin bắt buộc";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ";
    } else if (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự";
    } else {
        // 1. CHECK EMAIL TỒN TẠI
        $checkStmt = $conn->prepare("SELECT UserID FROM users WHERE UserEmail = ?");
        
        // MySQLi: Phải dùng bind_param ("s" là string)
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute(); // Không truyền tham số vào đây
        $checkStmt->store_result(); // Lưu kết quả để đếm số dòng

        if ($checkStmt->num_rows > 0) {
            $error = "Email này đã được đăng ký. Vui lòng chọn email khác.";
        } else {
            $checkStmt->close(); // Đóng statement kiểm tra

            // 2. HASH PASSWORD
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // 3. INSERT NGƯỜI DÙNG MỚI
            // Lưu ý: Đảm bảo bạn đã chạy lệnh ALTER TABLE để thêm cột 'secret_answer' trong database nhé
            $insertStmt = $conn->prepare("
                INSERT INTO users 
                (UserName, UserEmail, UserPassword, BirthDate, PhoneNumber, Gender, secret_answer) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            if ($insertStmt) {
                // THAY ĐỔI: Thêm 1 chữ 's' (tổng 7 chữ) và thêm biến $secret_answer vào cuối
                $insertStmt->bind_param("sssssss", $username, $email, $hashed, $birth, $phone, $gender, $secret_answer);

                if ($insertStmt->execute()) {
                    $success = "Tạo tài khoản thành công! Đang chuyển hướng...";
                } else {
                    $error = "Lỗi khi tạo tài khoản: " . $insertStmt->error;
                }
                $insertStmt->close();
            } else {
                $error = "Lỗi hệ thống: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Buddy - Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Color Variables từ Homepage - Giữ nguyên từ SignIn */
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
            --error: #F44336;
            --success: #4CAF50;
        }

        /* Global Styles - Giữ nguyên */
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

        /* Header Styles (giống SignIn) */
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

        .signup-btn {
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

        .signup-btn:hover {
            background-color: #2ab4d1;
        }

        /* Sign Up Section - Phần chính */
        .signup-section {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            padding: 40px 0;
            margin: 20px 0;
        }

        .signup-container {
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .signup-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 40px;
            width: 100%;
            box-shadow: var(--card-shadow);
            border-top: 5px solid var(--accent);
            position: relative;
            overflow: hidden;
        }

        .signup-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--accent), var(--primary-light));
        }

        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .signup-header h1 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        .signup-header p {
            color: var(--primary-light);
            font-size: 1.1rem;
        }

        .signup-icon {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary);
        }

        .form-label span.required {
            color: var(--error);
            margin-left: 3px;
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

        /* Không có icon */
        .form-input.no-icon {
            padding: 14px 15px;
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
            z-index: 2;
        }

        /* Password Strength Indicator */
        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .strength-bar {
            flex: 1;
            height: 4px;
            background-color: var(--gray);
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }

        /* Alert Messages */
        .alert-message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
            animation: slideIn 0.3s ease-out;
        }

        .alert-error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        /* Terms and Conditions */
        .terms-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 25px 0;
            font-size: 0.95rem;
        }

        .terms-group input {
            margin-top: 3px;
            accent-color: var(--accent);
        }

        .terms-group a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .terms-group a:hover {
            text-decoration: underline;
        }

        /* Sign Up Button */
        .signup-form-btn {
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

        .signup-form-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(1, 22, 30, 0.2);
        }

        .signup-form-btn:active {
            transform: translateY(0);
        }

        .signup-form-btn:disabled {
            background: var(--gray);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Sign In Link */
        .signin-link {
            text-align: center;
            font-size: 1rem;
            color: var(--primary-light);
            margin-top: 20px;
        }

        .signin-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: color 0.3s;
        }

        .signin-link a:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        /* Footer (giống SignIn) */
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

            .signup-card {
                padding: 30px 25px;
            }

            .signup-header h1 {
                font-size: 1.8rem;
            }

            .social-signup {
                flex-direction: column;
            }

            .signup-btn {
                margin: 10px 0 0 0;
            }
        }

        @media (max-width: 480px) {
            .signup-card {
                padding: 25px 20px;
            }

            .signup-header h1 {
                font-size: 1.6rem;
            }

            .signup-icon {
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

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .signup-card {
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

        /* Form validation styles */
        .form-input.invalid {
            border-color: var(--error);
            box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.1);
        }

        .form-input.valid {
            border-color: var(--success);
        }

        .form-hint {
            font-size: 0.85rem;
            color: var(--primary-light);
            margin-top: 5px;
            display: block;
        }

        .field-error {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
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
                <a href="AIBuddy_Terms of Service.php">Terms</a>
                <a href="AIBuddy_PrivacyPolicy.php">Privacy</a>
            </nav>
            <a href="AIBuddy_SignIn.php">
                <button class="signup-btn">Sign In</button>
            </a>
        </div>
    </header>

    <!-- Sign Up Section -->
    <section class="signup-section">
        <div class="container signup-container">
            <div class="signup-card">
                <div class="signup-header">
                    <div class="signup-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h1>Create Account</h1>
                    <p>Join AI Buddy today and start your mental wellness journey</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-message alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert-message alert-success">
                        <?php echo htmlspecialchars($success); ?>
                        <br><small>You will be redirected to login page in <span id="countdown">5</span> seconds...</small>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="signupForm">
                    <div class="form-group">
                        <label class="form-label" for="username">
                            Full Name <span class="required">*</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input"
                                placeholder="Enter your full name"
                                required
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                            >
                        </div>
                        <span class="form-hint">As you'd like to be addressed</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">
                            Email Address <span class="required">*</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                placeholder="you@example.com"
                                required
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            >
                        </div>
                        <span class="form-hint">We'll never share your email</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">
                            Password <span class="required">*</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Create a strong password"
                                required
                            >
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <span>Strength:</span>
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span id="strengthText">None</span>
                        </div>
                        <span class="form-hint">At least 6 characters with letters and numbers</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">
                            Confirm Password <span class="required">*</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input
                                type="password"
                                id="confirmPassword"
                                name="confirmPassword"
                                class="form-input"
                                placeholder="Re-enter your password"
                                required
                            >
                        </div>
                        

                        <span id="passwordMatch" class="field-error"></span>
                  
                        <div class="form-group">
                        <label class="form-label" for="secret_answer">
                            Security Question (Favorite Pet/School?) <span class="required">*</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-shield-cat"></i> <input
                                type="text"
                                id="secret_answer"
                                name="secret_answer"
                                class="form-input"
                                placeholder="E.g. Kiki, Nguyen Du Primary School..."
                                required
                                value="<?php echo isset($_POST['secret_answer']) ? htmlspecialchars($_POST['secret_answer']) : ''; ?>"
                            >
                        </div>
                        <span class="form-hint">Important: You will use this to reset your password.</span>
                    </div>

                        <span id="passwordMatch" class="field-error"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="birthdate">Birth Date</label>
                        <input
                            type="date"
                            id="birthdate"
                            name="birthdate"
                            class="form-input no-icon"
                            value="<?php echo isset($_POST['birthdate']) ? htmlspecialchars($_POST['birthdate']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <div class="input-with-icon">
                            <i class="fas fa-phone"></i>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                class="form-input"
                                placeholder="Enter your phone number"
                                value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-input no-icon">
                            <option value="">-- Select Gender --</option>
                            <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    

                    <div class="terms-group">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            I agree to the <a href="AIBuddy_Terms of Service.php" target="_blank">Terms of Service</a> 
                            and <a href="AIBuddy_PrivacyPolicy.php" target="_blank">Privacy Policy</a>
                            <span class="required">*</span>
                        </label>
                    </div>

                    <button type="submit" class="signup-form-btn" id="submitBtn">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>


                    <div class="signin-link">
                        Already have an account?
                        <a href="AIBuddy_SignIn.php">Sign in now</a>
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
        const confirmPasswordInput = document.getElementById('confirmPassword');
        
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }

        // Password Strength Indicator
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        
        function checkPasswordStrength(password) {
            let strength = 0;
            
            // Length check
            if (password.length >= 6) strength += 25;
            if (password.length >= 8) strength += 10;
            if (password.length >= 12) strength += 10;
            
            // Character variety checks
            if (/[a-z]/.test(password)) strength += 15;
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
            
            // Update UI
            strengthFill.style.width = Math.min(strength, 100) + '%';
            
            // Set color and text based on strength
            if (strength < 30) {
                strengthFill.style.backgroundColor = '#F44336';
                strengthText.textContent = 'Weak';
                strengthText.style.color = '#F44336';
            } else if (strength < 60) {
                strengthFill.style.backgroundColor = '#FF9800';
                strengthText.textContent = 'Fair';
                strengthText.style.color = '#FF9800';
            } else if (strength < 80) {
                strengthFill.style.backgroundColor = '#2196F3';
                strengthText.textContent = 'Good';
                strengthText.style.color = '#2196F3';
            } else {
                strengthFill.style.backgroundColor = '#4CAF50';
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#4CAF50';
            }
        }
        
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            validatePasswordMatch();
        });

        // Password Match Validation
        function validatePasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const matchElement = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchElement.textContent = '';
                confirmPasswordInput.classList.remove('invalid', 'valid');
                return false;
            }
            
            if (password === confirmPassword) {
                matchElement.textContent = '✓ Passwords match';
                matchElement.style.color = '#4CAF50';
                confirmPasswordInput.classList.remove('invalid');
                confirmPasswordInput.classList.add('valid');
                return true;
            } else {
                matchElement.textContent = '✗ Passwords do not match';
                matchElement.style.color = '#F44336';
                confirmPasswordInput.classList.remove('valid');
                confirmPasswordInput.classList.add('invalid');
                return false;
            }
        }
        
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);

        // Form Validation
        const signupForm = document.getElementById('signupForm');
        const submitBtn = document.getElementById('submitBtn');
        
        function validateForm() {
            let isValid = true;
            
            // Required fields check
            const requiredFields = signupForm.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('invalid');
                    isValid = false;
                } else {
                    field.classList.remove('invalid');
                }
            });
            
            // Email format check
            const emailField = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailField.value && !emailRegex.test(emailField.value)) {
                emailField.classList.add('invalid');
                isValid = false;
            }
            
            // Password match check
            if (!validatePasswordMatch()) {
                isValid = false;
            }
            
            // Password length check
            if (passwordInput.value.length < 6) {
                passwordInput.classList.add('invalid');
                isValid = false;
            }
            
            // Terms agreement check
            const termsCheckbox = document.getElementById('terms');
            if (!termsCheckbox.checked) {
                termsCheckbox.classList.add('invalid');
                isValid = false;
            } else {
                termsCheckbox.classList.remove('invalid');
            }
            
            // Update button state
            submitBtn.disabled = !isValid;
            
            return isValid;
        }
        
        // Validate on input
        signupForm.querySelectorAll('input, select').forEach(field => {
            field.addEventListener('input', validateForm);
            field.addEventListener('change', validateForm);
        });
        
        // Initial validation
        validateForm();

        // Form submission
        signupForm.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                showAlert('Please fill in all required fields correctly', 'error');
                return;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            submitBtn.disabled = true;
            
            // In a real app, you might want to do additional validation here
            // before allowing the form to submit
        });

        // Countdown for redirect after success
        <?php if ($success): ?>
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        const countdownInterval = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.href = 'AIBuddy_SignIn.php';
            }
        }, 1000);
        <?php endif; ?>

        // Show alert function
        function showAlert(message, type) {
            const alertEl = document.createElement('div');
            alertEl.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 1000;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                animation: slideInAlert 0.3s ease-out;
            `;
            
            if (type === 'success') {
                alertEl.style.backgroundColor = '#4CAF50';
            } else if (type === 'error') {
                alertEl.style.backgroundColor = '#F44336';
            } else {
                alertEl.style.backgroundColor = 'var(--primary)';
            }
            
            alertEl.textContent = message;
            document.body.appendChild(alertEl);
            
            // Add animation CSS
            if (!document.querySelector('#alertAnimation')) {
                const style = document.createElement('style');
                style.id = 'alertAnimation';
                style.textContent = `
                    @keyframes slideInAlert {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                    @keyframes slideOutAlert {
                        from { transform: translateX(0); opacity: 1; }
                        to { transform: translateX(100%); opacity: 0; }
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                alertEl.style.animation = 'slideOutAlert 0.3s ease-out';
                setTimeout(() => {
                    if (alertEl.parentNode) {
                        alertEl.parentNode.removeChild(alertEl);
                    }
                }, 300);
        }, 3000);
    }

    // Responsive navigation
    function adjustNavigation() {
            const nav = document.querySelector('nav');
            if (window.innerWidth <= 768) {
                if (nav) {
                    nav.style.display = 'flex';
                    nav.style.flexWrap = 'wrap';
                    nav.style.justifyContent = 'center';
                }
            }
        }
        
        window.addEventListener('load', adjustNavigation);
        window.addEventListener('resize', adjustNavigation);
    </script>
</body>
</html>