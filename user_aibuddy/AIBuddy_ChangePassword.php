<?php
session_start();
// 1. SỬA LỖI ĐƯỜNG DẪN FILE CẤU HÌNH
require_once 'config/db.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $secret_answer = trim($_POST['secret_answer']); // <--- Dùng secret_answer thay cho old_password
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // VALIDATE
    if (empty($email) || empty($secret_answer) || empty($new_password) || empty($confirm_password)) {
        $error = "Vui lòng nhập đầy đủ thông tin";
    } else if ($new_password !== $confirm_password) {
        $error = "Mật khẩu mới không khớp";
    } else if (strlen($new_password) < 6) {
        $error = "Mật khẩu mới phải có ít nhất 6 ký tự";
    } else {
        // 2. KẾT NỐI DB VÀ KIỂM TRA
        // Lấy secret_answer từ database dựa trên email
        $stmt = $conn->prepare("SELECT UserID, secret_answer FROM users WHERE UserEmail = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Kiểm tra xem user có tồn tại và câu trả lời bí mật có đúng không
        // (Dùng strtolower để không phân biệt hoa thường)
        if ($user && strtolower($secret_answer) === strtolower($user['secret_answer'])) {
            
            // CẬP NHẬT MẬT KHẨU MỚI
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            
            $updateStmt = $conn->prepare("UPDATE users SET UserPassword = ? WHERE UserID = ?");
            $updateStmt->bind_param("si", $hashed, $user['UserID']); 
            
            if ($updateStmt->execute()) {
                $success = "Password changed successfully! Please login again";
            } else {
                $error = "Something went wrong. Try again later!";
            }
            $updateStmt->close();
            
        } else {
            $error = "Incorrect email or security answer ";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Buddy - Change Password</title>
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
            --error: #F44336;
            --success: #4CAF50;
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

        /* Change Password Section */
        .change-section {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            padding: 40px 0;
            margin: 20px 0;
        }

        .change-container {
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .change-card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 40px;
            width: 100%;
            box-shadow: var(--card-shadow);
            border-top: 5px solid var(--accent);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out;
        }

        .change-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--accent), var(--primary-light));
        }

        .change-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .change-header h1 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        .change-header p {
            color: var(--primary-light);
            font-size: 1.1rem;
        }

        .change-icon {
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

        /* Alert Messages */
        .alert-message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 25px;
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

        /* Change Button */
        .change-form-btn {
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

        .change-form-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(1, 22, 30, 0.2);
        }

        .change-form-btn:active {
            transform: translateY(0);
        }

        /* Back to Sign In Link */
        .back-link {
            text-align: center;
            font-size: 1rem;
            color: var(--primary-light);
            margin-top: 20px;
        }

        .back-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: var(--primary);
            text-decoration: underline;
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

        /* Field Error */
        .field-error {
            color: var(--error);
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
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
        @media (max-width: 1024px) {
            nav a { margin: 0 8px; font-size: 0.9rem; }
        }

        @media (max-width: 768px) {
            .header-content { flex-direction: column; text-align: center; }
            nav { margin: 15px 0; display: flex; flex-wrap: wrap; justify-content: center; }
            nav a { margin: 0 8px 5px; font-size: 0.9rem; }
            .change-card { padding: 30px 25px; }
            .change-header h1 { font-size: 1.8rem; }
            .signin-btn { margin: 10px 0 0 0; }
        }

        @media (max-width: 480px) {
            .change-card { padding: 25px 20px; }
            .change-header h1 { font-size: 1.6rem; }
            .change-icon { font-size: 2.5rem; }
            .form-input { padding: 12px 15px 12px 45px; }
            nav a { margin: 0 5px 5px; font-size: 0.85rem; }
        }

        /* Animation */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .form-input.invalid {
            border-color: var(--error);
            box-shadow: 0 0 0 3px rgba(244, 67, 54, 0.1);
        }

        .form-input.valid {
            border-color: var(--success);
        }
    </style>
</head>

<body>
    <header>
        <div class="container header-content">
            <a href="AIBuddy_Homepage.php" class="logo">
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
            <?php if (isset($_SESSION['username'])): ?>
                <a href="AIBuddy_Profile.php" class="user-account">
                    <i class="fa-regular fa-user"></i>
                    <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                </a>
            <?php else: ?>
                <a href="AIBuddy_SignIn.php">
                    <button class="signin-btn">Sign In</button>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <section class="change-section">
        <div class="container change-container">
            <div class="change-card">
                <div class="change-header">
                    <div class="change-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h1>Change Password</h1>
                    <p>Enter your email and old password to set a new password</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-message alert-error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert-message alert-success">
                        <?php echo htmlspecialchars($success); ?>
                        
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="changeForm">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-input" placeholder="you@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="secret_answer">Security Question Answer</label>
                        <div class="input-with-icon">
                            <i class="fas fa-shield-cat"></i>
                            <input type="text" id="secret_answer" name="secret_answer" class="form-input" placeholder="E.g. Name of your first pet..." required>
                        </div>
                        <span style="font-size: 0.85rem; color: var(--primary-light);">
                            (Câu trả lời bạn đã đặt lúc đăng ký tài khoản)
                        </span>
                    

                    <div class="form-group">
                        <label class="form-label" for="new_password">New Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="new_password" name="new_password" class="form-input" placeholder="Enter your new password" required>
                            <button type="button" class="password-toggle" id="toggleNewPassword">
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
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Confirm your new password" required>
                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span id="passwordMatch" class="field-error"></span>
                    </div>

                    <button type="submit" class="change-form-btn" id="submitBtn">
                        <i class="fas fa-sync-alt"></i> Change Password
                    </button>
                </form>

                <div class="back-link">
                    Remember your password?
                    <a href="AIBuddy_SignIn.php">Back to Sign In</a>
                </div>
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
        <script>
    // --- PHẦN 1: LOGIC TỰ ĐỘNG CHUYỂN HƯỚNG (QUAN TRỌNG NHẤT) ---
    <?php if ($success): ?>
        // Chỉ chạy đoạn này khi PHP báo đổi mật khẩu thành công
        let seconds = 5; // Số giây đếm ngược
        const countdownElement = document.getElementById('countdown');
        
        const countdownInterval = setInterval(() => {
            seconds--;
            // Cập nhật số giây lên màn hình nếu thẻ đó tồn tại
            if (countdownElement) {
                countdownElement.textContent = seconds;
            }

            // Khi đếm về 0 thì chuyển trang
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                window.location.href = 'AIBuddy_SignIn.php'; // <--- DÒNG LỆNH CHUYỂN HƯỚNG
            }
        }, 1000); // Chạy mỗi 1 giây (1000ms)
    <?php endif; ?>

    // --- PHẦN 2: CÁC BIẾN GIAO DIỆN ---
    const toggleNewPassword = document.getElementById('toggleNewPassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    
    const secretAnswerInput = document.getElementById('secret_answer'); 
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const changeForm = document.getElementById('changeForm');
    const submitBtn = document.getElementById('submitBtn');

    // --- PHẦN 3: HIỆN/ẨN MẬT KHẨU ---
    function setupPasswordToggle(toggleBtn, inputField) {
        if (toggleBtn && inputField) {
            toggleBtn.addEventListener('click', function() {
                const type = inputField.getAttribute('type') === 'password' ? 'text' : 'password';
                inputField.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
    }
    setupPasswordToggle(toggleNewPassword, newPasswordInput);
    setupPasswordToggle(toggleConfirmPassword, confirmPasswordInput);

    // --- PHẦN 4: KIỂM TRA ĐỘ MẠNH MẬT KHẨU ---
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 6) strength += 25;
        if (password.length >= 8) strength += 10;
        if (password.length >= 12) strength += 10;
        if (/[a-z]/.test(password)) strength += 15;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/[0-9]/.test(password)) strength += 15;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
        
        if(strengthFill) strengthFill.style.width = Math.min(strength, 100) + '%';
        
        if (strengthText) {
            if (strength < 30) {
                if(strengthFill) strengthFill.style.backgroundColor = '#F44336';
                strengthText.textContent = 'Weak';
                strengthText.style.color = '#F44336';
            } else if (strength < 60) {
                if(strengthFill) strengthFill.style.backgroundColor = '#FF9800';
                strengthText.textContent = 'Fair';
                strengthText.style.color = '#FF9800';
            } else if (strength < 80) {
                if(strengthFill) strengthFill.style.backgroundColor = '#2196F3';
                strengthText.textContent = 'Good';
                strengthText.style.color = '#2196F3';
            } else {
                if(strengthFill) strengthFill.style.backgroundColor = '#4CAF50';
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#4CAF50';
            }
        }
    }
    
    if(newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            validateForm();
        });
    }

    // --- PHẦN 5: KIỂM TRA KHỚP MẬT KHẨU ---
    function validatePasswordMatch() {
        if(!newPasswordInput || !confirmPasswordInput) return false;

        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const matchElement = document.getElementById('passwordMatch');
        
        if (confirmPassword === '') {
            if(matchElement) matchElement.textContent = '';
            confirmPasswordInput.classList.remove('invalid', 'valid');
            return false;
        }
        
        if (password === confirmPassword) {
            if(matchElement) {
                matchElement.textContent = '✓ Passwords match';
                matchElement.style.color = '#4CAF50';
            }
            confirmPasswordInput.classList.remove('invalid');
            confirmPasswordInput.classList.add('valid');
            return true;
        } else {
            if(matchElement) {
                matchElement.textContent = '✗ Passwords do not match';
                matchElement.style.color = '#F44336';
            }
            confirmPasswordInput.classList.remove('valid');
            confirmPasswordInput.classList.add('invalid');
            return false;
        }
    }
    
    if(confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function(){
            validatePasswordMatch();
            validateForm();
        });
    }

    // --- PHẦN 6: VALIDATE FORM TỔNG HỢP ---
    function validateForm() {
        let isValid = true;
        
        if(!changeForm) return false;

        // Check required fields
        const requiredFields = changeForm.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('invalid');
                isValid = false;
            } else {
                // Logic loại trừ confirm password ra khỏi check đơn giản
                if(field !== confirmPasswordInput || (field === confirmPasswordInput && validatePasswordMatch())) {
                     field.classList.remove('invalid');
                }
            }
        });
        
        if (!validatePasswordMatch()) isValid = false;
        if (newPasswordInput && newPasswordInput.value.length < 6) {
            newPasswordInput.classList.add('invalid');
            isValid = false;
        }
        
        if(submitBtn) submitBtn.disabled = !isValid;
        return isValid;
    }

    // Lắng nghe sự kiện input
    if(changeForm) {
        changeForm.querySelectorAll('input').forEach(field => {
            field.addEventListener('input', validateForm);
        });
        
        // Initial check
        validateForm();

        // Submit Handler
        changeForm.addEventListener('submit', function(e) {
            // Nếu form chưa valid thì chặn luôn
            if (!validateForm()) {
                e.preventDefault();
                return;
            }
            // Nếu valid thì đổi nút thành Loading
            if(submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
            }
        });
    }

    // --- PHẦN 7: RESPONSIVE NAV ---
    function adjustNavigation() {
        const nav = document.querySelector('nav');
        if (window.innerWidth <= 768 && nav) {
            nav.style.display = 'flex';
            nav.style.flexWrap = 'wrap';
            nav.style.justifyContent = 'center';
        }
    }
    window.addEventListener('load', adjustNavigation);
    window.addEventListener('resize', adjustNavigation);
</script>
</body>
</html>