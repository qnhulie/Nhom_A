<?php
session_start();
require_once 'config/db.php';

// --- INITIALIZE VARIABLES ---
$currentUserName = null;
$userStatus = 0; // 0: New, 1: Active, 2: Expired
$usageLeft = 0;
$hasOrderHistory = false;
$activePlanID = 0; // Biến lưu ID gói đang dùng

// --- GET USER INFO & CHECK ORDER HISTORY ---
if (isset($_SESSION['userid'])) {
    $userID = $_SESSION['userid'];
    
    // 1. Lấy thông tin User
    $stmt = $conn->prepare("SELECT UserName, UsageLeft, IsTrialActive FROM users WHERE UserID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    if ($userData) {
        $currentUserName = $userData['UserName'];
        $usageLeft = $userData['UsageLeft'];
        $userStatus = $userData['IsTrialActive'];
    }

    // 2. Lấy đơn hàng mới nhất để biết User đang dùng gói nào (Active Plan)
    // Chỉ lấy đơn hàng đã hoàn thành (Completed) hoặc Active
    $histStmt = $conn->prepare("SELECT PlanID FROM userorder WHERE UserID = ? AND OrderStatus IN ('Completed', 'Active') ORDER BY OrderID DESC LIMIT 1");
    $histStmt->bind_param("i", $userID);
    $histStmt->execute();
    $histRes = $histStmt->get_result()->fetch_assoc();
    
    if ($histRes) {
        $hasOrderHistory = true;
        $activePlanID = $histRes['PlanID']; // Lưu ID gói đang dùng
    }
}
// ================================================================
// [MỚI] 3. LẤY VIDEO TỪ DATABASE (GÓI FREE - PlanID 1)
// ================================================================
$videoID = 'bXyPSlZPDiY'; // ID mặc định phòng hờ

// Hàm tách ID từ link Youtube
function getYoutubeID($url) {
    $pattern = '%^# Match any youtube URL
        (?:https?://)?  # Optional scheme. Either http or https
        (?:www\.)?      # Optional www subdomain
        (?:             # Group host alternatives
          youtu\.be/    # Either youtu.be,
        | youtube\.com  # or youtube.com
          (?:           # Group path alternatives
            /embed/     # Either /embed/
          | /v/         # or /v/
          | /watch\?v=  # or /watch\?v=
          )             # End path alternatives.
        )               # End host alternatives.
        ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
        $%x';
    $result = preg_match($pattern, $url, $matches);
    return ($result) ? $matches[1] : $url; 
}

// Lấy link video từ gói FREE (PlanID = 1)
$videoQuery = $conn->query("SELECT PlanVideoURL FROM plan WHERE PlanID = 1");
if ($videoQuery && $row = $videoQuery->fetch_assoc()) {
    if (!empty($row['PlanVideoURL'])) {
        $videoID = getYoutubeID($row['PlanVideoURL']);
    }
}
// ================================================================

// --- HANDLE FREE PLAN ACTIVATION ONLY ---
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate_free'])) {
    if (!isset($_SESSION['userid'])) {
        header("Location: AIBuddy_SignIn.php");
        exit;
    }

    // Chỉ xử lý gói Free (ID = 1) tại đây
    if ($userStatus == 2) {
        $msg = "<div class='alert error'>You have used up your trial. Please upgrade to a paid plan!</div>";
    } else {
        // Tạo đơn hàng 0 đồng
        $orderStmt = $conn->prepare("INSERT INTO userorder (UserID, PlanID, TotalAmount, OrderStatus, PurchaseTime) VALUES (?, 1, 0, 'Completed', NOW())");
        $orderStmt->bind_param("i", $userID);

        if ($orderStmt->execute()) {
            // Update User
            $updateUser = $conn->prepare("UPDATE users SET UsageLeft = 2, IsTrialActive = 1 WHERE UserID = ?");
            $updateUser->bind_param("i", $userID);
            $updateUser->execute();

            $msg = "<div class='alert success'>Free Trial Activated! Enjoy.</div>";
            header("Refresh:2"); 
        } else {
            $msg = "<div class='alert error'>System Error: " . $conn->error . "</div>";
        }
    }
}

// --- FETCH PLANS ---
$plansQuery = "SELECT p.PlanID, p.PlanName, p.PlanDescription, p.PlanPrice, p.BillingCycle, GROUP_CONCAT(pf.FeatureDescription SEPARATOR '||') as Features FROM plan p LEFT JOIN planfeature pf ON p.PlanID = pf.PlanID GROUP BY p.PlanID ORDER BY p.PlanPrice ASC";
$plansResult = $conn->query($plansQuery);
$plans = [];
while ($row = $plansResult->fetch_assoc()) $plans[] = $row;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Buddy - Plans</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* GIỮ NGUYÊN CSS CŨ */
        :root { --primary-dark: #01161e; --primary: #124559; --primary-light: #598392; --accent: #33c6e7; --light: #aec3b0; --background: #eff6e0; --white: #ffffff; --gray: #d9d9d9; --text: #353535; --card-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { background-color: var(--background); color: var(--text); line-height: 1.6; }
        .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        header { background-color: var(--white); padding: 15px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 24px; font-weight: bold; color: var(--primary); display: flex; align-items: center; text-decoration: none; }
        .logo-icon { margin-right: 8px; font-size: 28px; }
        nav a { margin: 0 15px; text-decoration: none; color: var(--primary); font-weight: 500; transition: color 0.3s; }
        nav a:hover { color: var(--accent); }
        .signin-btn { background-color: var(--accent); color: var(--white); border: none; padding: 8px 20px; border-radius: 20px; font-weight: 600; cursor: pointer; }
        .user-greeting-badge { background-color: var(--background); color: var(--primary); padding: 8px 20px; border-radius: 20px; font-size: 15px; font-weight: 500; border: 1px solid var(--primary); display: inline-block; }
        .user-greeting-badge strong { color: var(--accent); font-weight: 700; }
        
        .trial-layout { display: flex; margin: 40px auto; gap: 30px; min-height: 600px; align-items: flex-start; }
        .sidebar-wrapper { flex: 1; display: flex; flex-direction: column; gap: 25px; }
        .plan-sidebar { background: var(--white); border-radius: 10px; padding: 20px; box-shadow: var(--card-shadow); }
        .plan-sidebar h3 { color: var(--primary); margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--background); }
        .plan-item { padding: 15px; margin-bottom: 15px; border: 1px solid var(--gray); border-radius: 8px; cursor: pointer; transition: all 0.3s; display: flex; justify-content: space-between; align-items: center; }
        .plan-item:hover { border-color: var(--accent); background: #f0fbfc; }
        .plan-item.active { background-color: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 5px 15px rgba(18, 69, 89, 0.3); }
        .plan-item.active .plan-price { color: var(--accent); font-weight: bold; }
        .plan-item .plan-name { font-weight: 600; font-size: 1.1rem; }
        .plan-item .plan-price { font-size: 0.9rem; color: var(--primary-light); }
        
        .plan-content { flex: 2.5; background: var(--white); border-radius: 10px; padding: 40px; box-shadow: var(--card-shadow); position: relative; }
        .plan-details { display: none; animation: fadeIn 0.4s ease; }
        .plan-details.active { display: block; }
        .plan-header { margin-bottom: 30px; border-bottom: 1px solid var(--gray); padding-bottom: 20px; }
        .plan-header h2 { font-size: 2rem; color: var(--primary); margin-bottom: 10px; }
        .price-tag { font-size: 2.5rem; font-weight: bold; color: var(--primary-dark); margin: 15px 0; }
        .price-tag span { font-size: 1rem; color: var(--primary-light); font-weight: normal; }
        .features-list { margin-bottom: 40px; }
        .features-list li { list-style: none; margin-bottom: 12px; display: flex; align-items: center; }
        .features-list li i { color: var(--accent); margin-right: 12px; font-size: 1.2rem; }
        
        .payment-form-container { background: var(--background); padding: 25px; border-radius: 10px; border: 1px solid var(--gray); }
        .payment-header { display: flex; align-items: center; margin-bottom: 20px; }
        .payment-header i { font-size: 24px; color: var(--primary); margin-right: 10px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .submit-btn { width: 100%; background: var(--primary); color: white; padding: 15px; border: none; border-radius: 5px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .submit-btn:hover { background: var(--primary-dark); }
        
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* Banner & Video styles from previous requests */
        .trial-hero { background-color: #0e4d64; color: #ffffff; padding: 80px 0; text-align: center; width: 100%; }
        .trial-hero h1 { font-size: 2.8rem; margin-bottom: 20px; font-weight: 700; }
        .trial-hero p { font-size: 1.2rem; max-width: 800px; margin: 0 auto; line-height: 1.6; opacity: 0.9; }
        .usage-counter { position: absolute; top: 20px; right: 20px; background: var(--accent); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: bold; z-index: 10; }
        .demo-video-zone { margin-bottom: 40px; padding: 20px; background: #eef5f6; border-radius: 10px; border: 2px dashed var(--accent); text-align: center; }
        .video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; margin-top: 15px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        .video-error-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 10; text-align: center; padding: 20px; border-radius: 10px; }
        .video-error-overlay button { margin-top: 15px; padding: 8px 16px; background: var(--accent); border: none; color: white; cursor: pointer; border-radius: 5px; font-weight: bold; }
        
        /* Footer */
        footer { background-color: var(--primary-dark); color: var(--white); padding: 60px 0 20px; margin-top: 60px; }
        .footer-content { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 40px; }
        .footer-column ul { list-style: none; padding: 0; }
        .copyright { text-align: center; padding-top: 20px; border-top: 1px solid var(--primary); color: var(--light); font-size: 0.9rem; }
        .social-links { display: flex; gap: 15px; margin-top: 15px; }
        @media(max-width: 768px) { .header-content { flex-direction: column; text-align: center; } .trial-layout { flex-direction: column; } .sidebar-wrapper, .plan-content { width: 100%; } }
        
        /* MODAL CONFIRM CSS */
        .confirm-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: none; align-items: center; justify-content: center; z-index: 9999; }
        .confirm-box { background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .confirm-box h3 { color: var(--primary); margin-bottom: 15px; }
        .confirm-actions { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
        .confirm-btn { background: var(--accent); color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .cancel-btn { background: var(--gray); color: var(--text); padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        
        /* Disabled Button Style */
        .btn-disabled { background-color: #ccc !important; cursor: not-allowed; color: #666; }

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


    </style>
</head>
<body>

    <header>
        <div class="container header-content">
            <a href="AIBuddy_Homepage.php" class="logo"><span class="logo-icon">🤖</span> AI Buddy</a>
            <nav>
                <a href="AIBuddy_Homepage.php">Home</a>
                <a href="AIBuddy_Chatbot.php">Chatbot</a>
                <a href="AIBuddy_EmotionTracker.php">Emotion Tracker</a>
                <a href="AIBuddy_Trial.php" style="color: var(--accent);">Trial</a>
                <a href="AIBuddy_Profile.php">Profile</a>
                <a href="AIBuddy_About.php">About</a>
                <a href="AIBuddy_Contact.php">Contact</a>
            </nav>
            <?php if ($currentUserName): ?>
                <div class="user-greeting-badge">How's your day, <strong><?= htmlspecialchars($currentUserName) ?></strong>?</div>
            <?php else: ?>
                <button class="signin-btn" onclick="window.location.href='AIBuddy_SignIn.php'">Sign In</button>
            <?php endif; ?>
        </div>
    </header>

    <section class="trial-hero">
      <div class="container">
        <h1>Start Your AI Buddy Journey</h1>
        <p>Choose a plan that fits your needs. Start with a free trial or unlock premium features immediately.</p>
      </div>
    </section>

    <div class="container">
        <?php echo $msg; ?>

        <div class="trial-layout">
            <div class="sidebar-wrapper">
                <div class="plan-sidebar">
                    <h3>Select Your Plan</h3>
                    <?php foreach ($plans as $index => $plan): ?>
                        <div class="plan-item <?php echo $index === 0 ? 'active' : ''; ?>" onclick="showPlan(<?php echo $plan['PlanID']; ?>, this)">
                            <div class="plan-name"><?php echo $plan['PlanName']; ?></div>
                            <div class="plan-price"><?php echo $plan['PlanPrice'] == 0 ? 'Free' : number_format($plan['PlanPrice']) . 'đ'; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="plan-content">
                <?php if ($userStatus == 1): ?>
                    <div class="usage-counter"><i class="fas fa-bolt"></i> Uses Left: <span id="uses-left-display"><?php echo $usageLeft; ?></span></div>
                <?php elseif ($userStatus == 2): ?>
                    <div class="usage-counter" style="background:#dc3545;"><i class="fas fa-times-circle"></i> Expired</div>
                <?php endif; ?>

                <div class="demo-video-zone">
                    <h3><i class="fas fa-play-circle"></i> Try AI Meditation Feature</h3>
                    <p>Watch the full video to experience. <strong>(Consumes 1 use)</strong></p>
                    <div class="video-container">
                        <div id="youtube-player"></div>
                    </div>
                </div>

                <?php foreach ($plans as $index => $plan): 
                    $features = explode('||', $plan['Features']);
                    $isFree = ($plan['PlanPrice'] == 0);
                    
                    // KIỂM TRA NẾU GÓI NÀY ĐANG LÀ GÓI HIỆN TẠI (ACTIVE)
                    $isCurrentPlan = ($activePlanID == $plan['PlanID']);
                ?>
                    <div id="plan-<?php echo $plan['PlanID']; ?>" class="plan-details <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="plan-header">
                            <h2><?php echo $plan['PlanName']; ?></h2>
                            <p><?php echo $plan['PlanDescription']; ?></p>
                            <div class="price-tag">
                                <?php echo $plan['PlanPrice'] == 0 ? '0 VND' : number_format($plan['PlanPrice']) . ' VND'; ?>
                                <span>/ <?php echo $plan['BillingCycle']; ?></span>
                            </div>
                        </div>

                        <ul class="features-list">
                            <?php foreach ($features as $feature): ?>
                                <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($feature); ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="action-container" style="text-align: center; margin-top: 30px;">
                            <?php if ($isCurrentPlan): ?>
                                <button class="submit-btn" style="background-color: #ccc; cursor: not-allowed;" disabled>
                                    <i class="fas fa-check-circle"></i> Currently Active
                                </button>
                            
                            <?php elseif ($isFree): ?>
                                <form method="POST">
                                    <input type="hidden" name="activate_free" value="1">
                                    <button type="submit" class="submit-btn">Activate Free Trial</button>
                                </form>

                            <?php else: ?>
                                <a href="AIBuddy_Checkout.php?plan_id=<?php echo $plan['PlanID']; ?>" class="submit-btn" style="text-decoration: none; display: inline-block;">
                                    Subscribe Now <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="confirm-overlay" id="confirmModal">
        <div class="confirm-box">
            <h3>Confirm Subscription</h3>
            <p>Are you sure you want to subscribe to <strong id="modalPlanName"></strong>?</p>
            <p style="font-size:0.9rem; color:#666;">Your plan will be updated immediately.</p>
            
            <div class="confirm-actions">
                <button class="cancel-btn" onclick="closeConfirmModal()">Cancel</button>
                <button class="confirm-btn" id="confirmSubscribeBtn">Yes, Confirm</button>
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
        function showPlan(planID, element) {
            document.querySelectorAll('.plan-details').forEach(div => div.classList.remove('active'));
            document.getElementById('plan-' + planID).classList.add('active');
            document.querySelectorAll('.plan-item').forEach(item => item.classList.remove('active'));
            element.classList.add('active');
        }

        // --- MODAL LOGIC ---
        let targetFormID = null;

        function openConfirmModal(planName, planID) {
            document.getElementById('modalPlanName').innerText = planName;
            targetFormID = 'form-' + planID;
            document.getElementById('confirmModal').style.display = 'flex';
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            targetFormID = null;
        }

        document.getElementById('confirmSubscribeBtn').addEventListener('click', function() {
            if (targetFormID) {
                const form = document.getElementById(targetFormID);
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'process_payment';
                input.value = '1';
                form.appendChild(input);
                form.submit();
            }
        });

        // --- YOUTUBE API & USAGE LOGIC ---
        var usageLeft = <?php echo $usageLeft; ?>;
        var userStatus = <?php echo $userStatus; ?>; 
        var player;
        var isProcessing = false;

        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        function onYouTubeIframeAPIReady() {
            if (document.getElementById('youtube-player')) {
                player = new YT.Player('youtube-player', {
                    height: '360',
                    width: '640',
                    videoId: '<?php echo $videoID; ?>', // [ĐÃ SỬA] Lấy ID động từ PHP
                    events: { 'onStateChange': onPlayerStateChange }
                });
            }
        }

        function onPlayerStateChange(event) {
            if (event.data == YT.PlayerState.PLAYING) {
                if (usageLeft <= 0 || userStatus == 0 || userStatus == 2) {
                    player.stopVideo();
                    showVideoError();
                }
            }
            if (event.data == YT.PlayerState.ENDED) {
                checkAndConsumeUsage();
            }
        }

        function showVideoError() {
            const oldOverlay = document.querySelector('.video-error-overlay');
            if (oldOverlay) oldOverlay.remove();

            const videoContainer = document.querySelector('.video-container');
            const errorOverlay = document.createElement('div');
            errorOverlay.className = 'video-error-overlay';
            
            let msg = "<h3>Access Denied</h3><p>Please subscribe to a plan below.</p>";
            if(userStatus == 2) msg = "<h3>Trial Expired</h3><p>Your trial has ended. Please upgrade.</p>";
            else if (usageLeft <= 0) msg = "<h3>Out of Views</h3><p>You have used all your views.</p>";

            msg += '<button onclick="document.querySelector(\'.plan-content\').scrollIntoView({ behavior: \'smooth\' });">View Plans</button>';
            errorOverlay.innerHTML = msg;
            videoContainer.appendChild(errorOverlay);
        }

        function checkAndConsumeUsage() {
            if (isProcessing || usageLeft <= 0 || userStatus != 1) return;
            isProcessing = true;
            fetch('update_usage.php', { method: 'POST' })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    usageLeft = data.newUsage;
                    document.getElementById('uses-left-display').innerText = usageLeft;
                    alert("🎉 Session Completed! Uses left: " + usageLeft);
                    if (usageLeft === 0) location.reload();
                }
            })
            .catch(e => console.error(e))
            .finally(() => setTimeout(() => { isProcessing = false; }, 2000));
        }
    </script>
</body>
</html>