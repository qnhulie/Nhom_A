<?php
session_start();
require_once 'config/db.php';

/* 1. Kiểm tra đăng nhập (Sử dụng đúng key 'userid') */
if (!isset($_SESSION['userid'])) {
    header("Location: AIBuddy_SignIn.php");
    exit();
}
$UserID = $_SESSION['userid'];

/* 2. Nhận PlanID */
$PlanID = $_GET['plan_id'] ?? null;
if (!$PlanID) {
    echo "<script>alert('No plan selected!'); window.location.href='AIBuddy_Trial.php';</script>";
    exit();
}

/* ================= LOAD PLAN ================= */
$stmt = $conn->prepare("SELECT PlanID, PlanName, PlanPrice, BillingCycle FROM plan WHERE PlanID = ?");
$stmt->bind_param("i", $PlanID);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if (!$plan) {
    die("Plan not found");
}

/* ================= LOAD USER ================= */
$stmt = $conn->prepare("SELECT UserID, UserName, UserEmail, PhoneNumber FROM users WHERE UserID = ?");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$paymentSuccess = false;
$orderSummary = null;

/* ================= SUBMIT PAYMENT ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['PaymentMethod'] ?? 'Credit Card';
    $fullName = $_POST['FullName'] ?? $user['UserName'];
    // Các thông tin khác như CardNumber, CVV thường không lưu vào DB vì lý do bảo mật (PCI DSS), chỉ xử lý qua cổng thanh toán.
    // Ở đây ta giả lập lưu vào DB.

    try {
        $conn->begin_transaction();

        /* A. INSERT userorder (Thêm PurchaseTime) */
        $purchaseTime = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("
            INSERT INTO userorder (UserID, PlanID, TotalAmount, OrderStatus, PurchaseTime)
            VALUES (?, ?, ?, 'Completed', ?)
        ");
        $stmt->bind_param("iids", $UserID, $plan['PlanID'], $plan['PlanPrice'], $purchaseTime);
        $stmt->execute();
        $OrderID = $conn->insert_id;

        /* B. INSERT transactions (Code đã sửa lỗi) */
        $sqlTrans = "INSERT INTO transactions (OrderID, PaymentMethod, PaymentStatus, PaymentTime, Amount) VALUES (?, ?, 'Completed', ?, ?)";
        $stmt = $conn->prepare($sqlTrans);
        
        // KIỂM TRA LỖI SQL
        if (!$stmt) {
            throw new Exception("Lỗi SQL (Bảng transactions): " . $conn->error);
        }

        $stmt->bind_param("issd", $OrderID, $paymentMethod, $purchaseTime, $plan['PlanPrice']);
        
        if (!$stmt->execute()) {
            throw new Exception("Lỗi thực thi (Transactions): " . $stmt->error);
        }

        /* C. UPDATE USER STATUS (QUAN TRỌNG: Kích hoạt tài khoản) */
        // Set UsageLeft = 9999 (Unlimited) và IsTrialActive = 1
        $stmt = $conn->prepare("UPDATE users SET UsageLeft = 9999, IsTrialActive = 1 WHERE UserID = ?");
        $stmt->bind_param("i", $UserID);
        $stmt->execute();

        /* D. LOAD DATA FOR SUCCESS MODAL */
        $orderSummary = [
            'OrderID' => $OrderID,
            'PlanName' => $plan['PlanName'],
            'TotalAmount' => $plan['PlanPrice'],
            'PaymentMethod' => $paymentMethod,
            'PaymentTime' => $purchaseTime,
            'Email' => $_POST['UserEmail'] ?? $user['UserEmail']
        ];

        $conn->commit();
        $paymentSuccess = true;

    } catch (Exception $e) {
        $conn->rollback();
        die("Payment failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Buddy - Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Color Variables & Global Styles */
        :root { --primary-dark: #01161e; --primary: #124559; --primary-light: #598392; --accent: #33c6e7; --light: #aec3b0; --background: #eff6e0; --white: #ffffff; --gray: #d9d9d9; --text: #353535; --card-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--background); color: var(--text); line-height: 1.6; }
        .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 0 15px; }

        /* Header */
        header { background-color: var(--white); padding: 15px 0; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); position: sticky; top: 0; z-index: 100; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 24px; font-weight: bold; color: var(--primary); display: flex; align-items: center; }
        .logo-icon { margin-right: 8px; font-size: 28px; }
        nav a { margin: 0 15px; text-decoration: none; color: var(--primary); font-weight: 500; transition: color 0.3s; }
        nav a:hover { color: var(--accent); }
        .signin-btn { background-color: var(--accent); color: var(--white); border: none; padding: 8px 20px; border-radius: 20px; font-weight: 600; cursor: pointer; transition: background-color 0.3s; }
        .signin-btn:hover { background-color: #2ab4d1; }

        /* Hero */
        .page-hero { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: var(--white); padding: 60px 0; text-align: center; margin: 20px auto; border-radius: 10px; box-shadow: var(--card-shadow); }
        .page-hero h1 { font-size: 2.5rem; margin-bottom: 15px; }
        .breadcrumb { display: flex; justify-content: center; list-style: none; }
        .breadcrumb li { margin: 0 10px; position: relative; }
        .breadcrumb li:not(:last-child):after { content: ">"; position: absolute; right: -15px; }
        .breadcrumb a { color: var(--light); text-decoration: none; }

        /* Timer & Order Info */
        .payment-timer { margin: 12px 0; text-align: center; font-size: 1.2rem; color: #e74c3c; font-weight: bold; }
        .order-information { background-color: #aec3b0; border-radius: 10px; padding: 30px; color: var(--primary-dark); margin-bottom: 30px; }
        .order-information h2 { font-size: 1.8rem; margin-bottom: 15px; border-bottom: 2px solid var(--primary); padding-bottom: 10px; }
        .order-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1.1rem; }
        .order-total { display: flex; justify-content: space-between; margin-top: 20px; font-size: 1.5rem; font-weight: bold; color: var(--primary-dark); border-top: 2px solid var(--white); padding-top: 15px; }

        /* Payment Form */
        .payment-section { display: flex; gap: 30px; flex-wrap: wrap; margin-bottom: 60px; }
        .payment-col { flex: 1; background: var(--white); padding: 30px; border-radius: 10px; box-shadow: var(--card-shadow); min-width: 300px; }
        .payment-col h3 { color: var(--primary); margin-bottom: 20px; font-size: 1.4rem; border-left: 5px solid var(--accent); padding-left: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-control { width: 100%; padding: 12px; border: 1px solid var(--gray); border-radius: 5px; font-size: 1rem; }
        .btn { width: 100%; padding: 15px; background: var(--primary); color: white; border: none; border-radius: 5px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 10px; transition: background 0.3s; }
        .btn:hover { background: var(--primary-dark); }

        /* Footer */
        footer { background-color: var(--primary-dark); color: var(--white); padding: 60px 0 20px; margin-top: auto; }
        .copyright { text-align: center; padding-top: 20px; border-top: 1px solid var(--primary); font-size: 0.9rem; color: var(--light); }

        /* Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: white; padding: 40px; border-radius: 15px; text-align: center; max-width: 500px; width: 90%; animation: slideUp 0.4s ease; }
        .modal-content h2 { color: #27ae60; font-size: 2rem; margin-bottom: 20px; }
        .modal-content p { font-size: 1.1rem; margin-bottom: 10px; color: #555; text-align: left; }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
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


    </style>
</head>
<body>

    <header>
        <div class="container header-content">
            <div class="logo"><span class="logo-icon">🤖</span> AI Buddy</div>
            <nav>
              <a href="AIBuddy_Homepage.php">Home</a>

        <a href="AIBuddy_Chatbot.php">Chatbot</a>

        <a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a>

        <a href="AIBuddy_Trial.php">Trial</a>

        <a href="AIBuddy_Profile.php">Profile</a>

        <a href="AIBuddy_About.php">About</a>

        <a href="AIBuddy_Contact.php">Contact</a>

            </nav>
            <?php if (isset($user['UserName'])): ?>
                <div class="user-greeting-badge">How's your day, <strong><?= htmlspecialchars($user['UserName']) ?>?</strong></div>
            <?php endif; ?>
        </div>
    </header>

    <section class="page-hero">
        <div class="container">
            <h1>Secure Checkout</h1>
            <ul class="breadcrumb">
           
                <li style="color: var(--accent);">Payment filling process</li>
            </ul>
        </div>
    </section>

    <div class="container">
        
        <div class="payment-timer">
            <i class="fas fa-clock"></i> Session expires in: <span id="timer-value">05:00</span>
        </div>

        <div class="order-information">
            <h2>Order Summary</h2>
            <div class="order-row">
                <span>Selected Plan:</span>
                <strong><?= htmlspecialchars($plan['PlanName']) ?> (<?= htmlspecialchars($plan['BillingCycle']) ?>)</strong>
            </div>
            <div class="order-row">
                <span>Account Email:</span>
                <strong><?= htmlspecialchars($user['UserEmail']) ?></strong>
            </div>
            <div class="order-total">
                <span>Total to Pay:</span>
                <span><?= number_format($plan['PlanPrice']) ?> VND</span>
            </div>
        </div>

        <form method="post">
            <section class="payment-section">
                <div class="payment-col">
                    <h3>1. Billing Details</h3>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="FullName" class="form-control" value="<?= htmlspecialchars($user['UserName']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="UserEmail" class="form-control" value="<?= htmlspecialchars($user['UserEmail']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="PhoneNumber" class="form-control" value="<?= htmlspecialchars($user['PhoneNumber']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Address (City/Province)</label>
                        <input type="text" name="City" class="form-control" required placeholder="e.g. Ho Chi Minh City">
                    </div>
                </div>

                <div class="payment-col">
                    <h3>2. Payment Method</h3>
                    <div class="form-group">
                        <label>Select Method</label>
                        <select name="PaymentMethod" id="paymentMethod" class="form-control" required>
                            <option value="Credit Card">Credit Card / Debit Card</option>
                            <option value="Bank Transfer">Bank Transfer (QR Code)</option>
                            <option value="Momo">Momo Wallet</option>
                        </select>
                    </div>

                    <div id="card-details">
                        <div class="form-group">
                            <label>Card Number</label>
                            <input type="text" name="CardNumber" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19">
                        </div>
                        <div style="display:flex; gap:15px;">
                            <div class="form-group" style="flex:1">
                                <label>Expiry Date</label>
                                <input type="text" name="Expiry" id="expiryInput" class="form-control" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label>CVV</label>
                                <input type="password" name="CVV" class="form-control" placeholder="123" maxlength="3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Cardholder Name</label>
                            <input type="text" name="CardHolder" class="form-control" placeholder="Name on Card">
                        </div>
                    </div>

                    <div id="bank-details" style="display:none; text-align:center; padding:20px; background:#f0faff; border-radius:8px;">
                        <p>Please transfer <strong><?= number_format($plan['PlanPrice']) ?> VND</strong> to:</p>
                        <p><strong>Vietcombank</strong></p>
                        <p>Account: <strong>9999 8888 7777</strong></p>
                        <p>Name: <strong>AI BUDDY CORP</strong></p>
                        <p>Content: <strong>PAY <?= $UserID ?></strong></p>
                    </div>

                    <button type="submit" class="btn">Confirm & Pay <?= number_format($plan['PlanPrice']) ?> VND</button>
                    <p style="margin-top:10px; font-size:0.85rem; color:#777; text-align:center;">
                        <i class="fas fa-lock"></i> Your payment information is encrypted and secure.
                    </p>
                </div>
            </section>
        </form>
    </div>

    <?php if ($paymentSuccess && $orderSummary): ?>
        <div class="modal-overlay active">
            <div class="modal-content">
                <div style="font-size:60px; color:#27ae60; margin-bottom:10px;"><i class="fas fa-check-circle"></i></div>
                <h2>Payment Successful!</h2>
                <p><strong>Order ID:</strong> #<?= $orderSummary['OrderID'] ?></p>
                <p><strong>Plan:</strong> <?= htmlspecialchars($orderSummary['PlanName']) ?></p>
                <p><strong>Amount:</strong> <?= number_format($orderSummary['TotalAmount']) ?> VND</p>
                <p><strong>Method:</strong> <?= htmlspecialchars($orderSummary['PaymentMethod']) ?></p>
                <p><strong>Time:</strong> <?= $orderSummary['PaymentTime'] ?></p>
                
                <div class="modal-actions" style="margin-top:20px;">
                    <a href="AIBuddy_Profile.php?tab=subscription" class="btn">Go to My Subscription</a>
                    <a href="AIBuddy_Homepage.php" class="btn" style="background:var(--primary-light);">Back to Home</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="modal-overlay" id="timeout-modal">
        <div class="modal-content">
            <h2 style="color:#e74c3c;">Session Expired</h2>
            <p style="text-align:center;">Your payment session has timed out due to inactivity.</p>
            <div class="modal-actions" style="margin-top:20px;">
                <button class="btn" onclick="location.reload()">Try Again</button>
                <a href="AIBuddy_Trial.php" class="btn" style="background:var(--gray); color:#333;">Cancel</a>
            </div>
        </div>
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

    <script>
        // 1. Payment Method Toggle
        const methodSelect = document.getElementById('paymentMethod');
        const cardDetails = document.getElementById('card-details');
        const bankDetails = document.getElementById('bank-details');

        methodSelect.addEventListener('change', function() {
            if(this.value === 'Bank Transfer' || this.value === 'Momo') {
                cardDetails.style.display = 'none';
                bankDetails.style.display = 'block';
            } else {
                cardDetails.style.display = 'block';
                bankDetails.style.display = 'none';
            }
        });

        // 2. Expiry Date Auto-format (MM/YY)
        const expiryInput = document.getElementById("expiryInput");
        expiryInput.addEventListener("input", function(e) {
            let val = e.target.value.replace(/\D/g, "");
            if (val.length > 4) val = val.slice(0, 4);
            if (val.length > 2) val = val.slice(0, 2) + "/" + val.slice(2);
            e.target.value = val;
        });

        // 3. Countdown Timer (5 minutes)
        let timeLeft = 300; // 5 phút
        const timerEl = document.getElementById('timer-value');
        const timeoutModal = document.getElementById('timeout-modal');

        const timer = setInterval(() => {
            if(timeLeft <= 0) {
                clearInterval(timer);
                timeoutModal.classList.add('active');
            } else {
                let m = Math.floor(timeLeft / 60);
                let s = timeLeft % 60;
                timerEl.textContent = `${m < 10 ? '0'+m : m}:${s < 10 ? '0'+s : s}`;
                timeLeft--;
            }
        }, 1000);
    </script>

</body>
</html>