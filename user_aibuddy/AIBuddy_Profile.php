<?php
session_start();
require_once 'config/db.php';

// 1. Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['userid'])) {
    header("Location: AIBuddy_SignIn.php");
    exit();
}

$UserID = $_SESSION['userid'];
$tab = $_GET['tab'] ?? 'account'; // mặc định Account Details

// Lấy thông tin User
$stmt = $conn->prepare("
    SELECT UserID, UserName, UserEmail, PhoneNumber, IsTrialActive
    FROM users
    WHERE UserID = ?
");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo "Không tìm thấy thông tin người dùng.";
    exit(); 
}

/* ================================
   LOGIC MỚI: XÁC ĐỊNH TRẠNG THÁI GÓI CƯỚC
================================ */

// 1. Lấy thông tin gói trả phí (nếu có) từ bảng userorder
// Chỉ lấy đơn hàng Active hoặc Completed mới nhất
$stmt = $conn->prepare("
    SELECT 
        o.OrderID,
        o.PlanID,
        o.OrderStatus,
        p.PlanName,
        p.PlanDescription,
        p.PlanPrice
    FROM userorder o
    JOIN plan p ON o.PlanID = p.PlanID
    WHERE o.UserID = ?
    ORDER BY o.OrderID DESC
    LIMIT 1
");
$stmt->bind_param("i", $UserID);
$stmt->execute();
$currentPlan = $stmt->get_result()->fetch_assoc();

// 2. Xác định Membership Status hiển thị ra màn hình
$membershipStatus = 'No Active Plan'; // Mặc định

// Ưu tiên 1: Nếu có gói trả phí đang Active/Completed
if ($currentPlan && in_array($currentPlan['OrderStatus'], ['Active', 'Completed'])) {
    $membershipStatus = $currentPlan['PlanName']; 
} 
// Ưu tiên 2: Nếu không có gói trả phí, kiểm tra trạng thái dùng thử (Free Trial)
elseif (isset($user['IsTrialActive'])) {
    if ($user['IsTrialActive'] == 1) {
        $membershipStatus = "Free Trial (Active)";
    } elseif ($user['IsTrialActive'] == 2) {
        $membershipStatus = "Trial Expired";
    }
}

// Biến $membership dùng cho tab Account cũng lấy từ currentPlan
$membership = $currentPlan;

/* ================================
   BADGE LOGIC (DEMO VERSION)
================================ */
$currentBadge = null;
if ($currentPlan && in_array($currentPlan['PlanName'], ['Essential', 'Premium']) && $currentPlan['OrderStatus'] !== 'Cancelled') {
    $currentBadge = [
        'BadgeID' => 1,
        'BadgeName' => 'Calm Master',
        'BadgeSymbol' => '🏅',
        'BadgeColor' => 'badge1'
    ];
}

/* ================================
   ACTION: REQUEST REFUND
================================ */
if (isset($_POST['action']) && $_POST['action'] === 'request_refund') {

    $refundReason = $_POST['refund_reason'] ?? null; // Lý do (Dropdown)
    $refundDetails = $_POST['refund_details'] ?? null; // Chi tiết (Textarea)

    if (!$refundReason || !$refundDetails) {
        die("Missing refund information");
    }

    // Lấy OrderID mới nhất (Phải là đơn hàng Active/Completed để hoàn tiền)
    $stmt = $conn->prepare("
        SELECT OrderID, TotalAmount
        FROM userorder
        WHERE UserID = ? AND OrderStatus IN ('Active', 'Completed')
        ORDER BY OrderID DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        die("No eligible transaction found for refund.");
    }

    $transactionID = $order['OrderID'];
    $refundAmount = $order['TotalAmount']; // Mặc định yêu cầu hoàn toàn bộ số tiền

    // Insert refund request
    // Lưu ý: Cột RefundType trong bảng refundrequest sẽ lưu lý do (refundReason)
    $stmt = $conn->prepare("
        INSERT INTO refundrequest
        (TransactionID, RefundType, RefundAmount, RefundDetails, RefundStatus, RequestDate)
        VALUES (?, ?, ?, ?, 'Pending', NOW())
    ");
    $stmt->bind_param(
        "isds",
        $transactionID,
        $refundReason,
        $refundAmount,
        $refundDetails
    );
    
    if ($stmt->execute()) {
        header("Location: AIBuddy_Profile.php?tab=subscription&msg=refund_sent");
        exit;
    } else {
        die("Error sending refund request: " . $conn->error);
    }
}

/* ================================
   ACTION: UPDATE PROFILE
================================ */
if (isset($_POST['update_single'])) {
    $field = $_POST['field']; 
    $value = $_POST['value'];
    $allowedFields = ['UserName', 'UserEmail', 'PhoneNumber'];

    if (in_array($field, $allowedFields)) {
        $sql = "UPDATE users SET $field = ? WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $value, $UserID);
        $stmt->execute();
        header("Location: AIBuddy_Profile.php?tab=account");
        exit;
    }
}

/* ================================
   ACTION: CANCEL SUBSCRIPTION
================================ */
if (isset($_POST['action']) && $_POST['action'] === 'cancel_subscription') {

    $cancelType = $_POST['cancel_type'] ?? null;
    $cancelReason = $_POST['cancel_reason'] ?? null;

    if (!$cancelType) {
        die("Missing cancellation type");
    }

    // Lấy OrderID mới nhất đang Active/Completed
    $stmt = $conn->prepare("
        SELECT OrderID
        FROM userorder
        WHERE UserID = ? AND OrderStatus IN ('Active', 'Completed')
        ORDER BY OrderID DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        die("No active subscription found to cancel.");
    }

    $membershipID = $order['OrderID'];

    // Insert vào subscriptioncancel (Lịch sử hủy)
    $stmt = $conn->prepare("
        INSERT INTO subscriptioncancel
        (MembershipID, CancellationType, CancellationReason, CancellationStatus)
        VALUES (?, ?, ?, 'Pending')
    ");
    $stmt->bind_param(
        "iss",
        $membershipID,
        $cancelType,
        $cancelReason
    );
    $stmt->execute();

    // CẬP NHẬT TRẠNG THÁI ORDER THÀNH 'Cancelled'
    $stmt = $conn->prepare("
        UPDATE userorder
        SET OrderStatus = 'Cancelled'
        WHERE OrderID = ?
    ");
    $stmt->bind_param("i", $membershipID);
    $stmt->execute();
    
    // Cập nhật lại User Status (nếu cần, ví dụ set IsTrialActive = 0)
    // $conn->query("UPDATE users SET IsTrialActive = 0 WHERE UserID = $UserID");

    header("Location: AIBuddy_Profile.php?tab=subscription");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Buddy - Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* GIỮ NGUYÊN CSS CŨ */
        :root { --primary-dark: #01161e; --primary: #124559; --primary-light: #598392; --accent: #33c6e7; --light: #aec3b0; --background: #eff6e0; --white: #ffffff; --gray: #d9d9d9; --text: #353535; --card-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--background); color: var(--text); line-height: 1.6; }
        .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        /* ... (Các CSS khác giữ nguyên) ... */
        
        /* CSS BỔ SUNG CHO PHẦN LỊCH SỬ REFUND */
        .refund-history-box {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .refund-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid var(--gray);
        }
        .refund-item.pending { border-left-color: #f1c40f; }
        .refund-item.approved { border-left-color: #2ecc71; }
        .refund-item.rejected { border-left-color: #e74c3c; }
        
        .refund-status { font-weight: bold; font-size: 0.9rem; text-transform: uppercase; }
        .status-pending { color: #f39c12; }
        .status-approved { color: #27ae60; }
        .status-rejected { color: #c0392b; }
        
        .admin-response {
            margin-top: 8px;
            font-size: 0.9rem;
            color: var(--primary);
            background: rgba(18, 69, 89, 0.1);
            padding: 8px;
            border-radius: 5px;
        }

        /* CSS Header & Footer & Sidebar giữ nguyên */
        /* ... */
        /* Header Styles */
        header { background-color: var(--white); padding: 15px 0; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); position: sticky; top: 0; z-index: 100; }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 24px; font-weight: bold; color: var(--primary); display: flex; align-items: center; }
        .logo-icon { margin-right: 8px; font-size: 28px; }
        nav a { margin: 0 15px; text-decoration: none; color: var(--primary); font-weight: 500; transition: color 0.3s; }
        nav a:hover { color: var(--accent); }
        .signin-btn { background-color: var(--accent); color: var(--white); border: none; padding: 8px 20px; border-radius: 20px; font-weight: 600; cursor: pointer; transition: background-color 0.3s; }
        .user-account { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 20px; background-color: var(--background); color: var(--primary); font-weight: 600; text-decoration: none; transition: all 0.25s ease; }
        .user-account:hover { background-color: var(--accent); color: var(--white); }
        .page-hero { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: var(--white); padding: 60px 0; text-align: center; margin: 20px auto; border-radius: 10px; box-shadow: var(--card-shadow); }
        .page-hero h1 { font-size: 2.5rem; margin-bottom: 15px; }
        .breadcrumb { display: flex; justify-content: center; list-style: none; }
        .breadcrumb li { margin: 0 10px; position: relative; }
        .breadcrumb li:not(:last-child):after { content: ">"; position: absolute; right: -15px; }
        .breadcrumb a { color: var(--light); text-decoration: none; }
        .breadcrumb .current { color: var(--accent); }
        .sidebar { background-color: #124559; border-radius: 20px; padding: 25px 20px; color: #fff; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; }
        .sidebar-menu li { display: flex; align-items: center; gap: 15px; padding: 12px 18px; border-radius: 10px; cursor: pointer; transition: background-color 0.3s ease; font-weight: 500; font-size: 18px; }
        .sidebar-menu li:hover { background-color: #124559; }
        .dashboard-section { padding: 80px 0; }
        .dashboard-container { display: grid; grid-template-columns: 1fr 1.5fr 1fr; gap: 30px; }
        .dashboard-box { background-color: var(--white); border-radius: 10px; padding: 30px; box-shadow: var(--card-shadow); min-height: 350px; }
        .dashboard-box h2 { font-size: 1.3rem; margin-bottom: 15px; color: var(--primary); font-weight: 700; }
        .btn-primary { background-color: #eff6e0; color: #124559; padding: 6px 15px; border: none; border-radius: 5px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-primary:hover { background-color: #598392; }
        .account-form .form-group { margin-bottom: 22px; }
        .account-form label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--primary); font-size: 16px; }
        .account-form .form-control { width: 100%; padding: 14px 16px; border-radius: 8px; border: 1px solid #ddd; font-size: 16px; background: #fff; }
        .field-row { display: flex; gap: 12px; align-items: center; }
        .edit-btn { background-color: var(--primary); color: #fff; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .edit-btn:hover { background-color: var(--primary-light); }
        .edit-link { background-color: var(--primary); color: #fff; border: none; display: inline-flex; align-items: center; justify-content: center; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; line-height: normal; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(1, 22, 30, 0.55); display: none; align-items: center; justify-content: center; z-index: 999; }
        .modal-box { background: var(--white); width: 100%; max-width: 520px; border-radius: 20px; padding: 32px 36px; box-shadow: var(--card-shadow); animation: modalFade 0.25s ease; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 26px; }
        .close-modal { font-size: 28px; cursor: pointer; color: #aaa; }
        .modal-box .form-group { margin-bottom: 20px; }
        .modal-box label { display: block; margin-bottom: 6px; font-weight: 600; color: var(--primary); }
        .modal-box input, .modal-box select, .modal-box textarea { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid #dcdcdc; font-size: 15px; background: #fff; }
        .modal-box button { width: 100%; margin-top: 14px; padding: 14px; border-radius: 10px; font-size: 16px; font-weight: 700; }
        @keyframes modalFade { from { opacity: 0; transform: translateY(20px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
        footer { background-color: var(--primary-dark); color: var(--white); padding: 60px 0 20px; margin-top: 60px; }
        .footer-content { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 40px; }
        .footer-column h3 { font-size: 1.2rem; margin-bottom: 20px; color: var(--accent); }
        .footer-column ul { list-style: none; }
        .footer-column ul li { margin-bottom: 10px; }
        .footer-column ul li a { color: var(--light); text-decoration: none; transition: color 0.3s; }
        .social-links { display: flex; gap: 15px; margin-top: 15px; }
        .social-links a { color: var(--light); font-size: 1.2rem; transition: color 0.3s; }
        .user-greeting-badge { background-color: var(--background); color: var(--primary); padding: 8px 20px; border-radius: 20px; font-size: 15px; font-weight: 500; border: 1px solid var(--primary); display: inline-block; }
        .user-greeting-badge strong { color: var(--accent); font-weight: 700; }
        .history-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .history-table th { text-align: left; padding: 12px; background-color: var(--primary); color: var(--white); font-weight: 600; border-radius: 4px 4px 0 0; }
        .history-table td { padding: 12px; border-bottom: 1px solid #eee; color: var(--text); }
        .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 600; }
        .status-badge.completed, .status-badge.active { background-color: #d4edda; color: #155724; }
        .status-badge.pending { background-color: #fff3cd; color: #856404; }
        .status-badge.cancelled { background-color: #f8d7da; color: #721c24; }
        .badge1 { width: 300px; text-align: center; background-color: #aec3b0; color: white; border-radius: 20px; padding: 15px 10px; margin-bottom: 25px; }
        .badge2 { width: 300px; text-align: center; background-color: #598392; border-radius: 20px; color: white; padding: 15px 10px; margin-bottom: 25px; }
        .badge3 { width: 300px; text-align: center; background-color: #124559; border-radius: 20px; color: white; padding: 15px 10px; margin-bottom: 25px; }
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
            <?php if (isset($_SESSION['userid']) && isset($user)): ?>
                <div class="user-greeting-badge">How's your day, <strong><?= htmlspecialchars($user['UserName']) ?></strong>?</div>
            <?php else: ?>
                <a href="AIBuddy_SignIn.php"><button class="signin-btn">Sign In</button></a>
            <?php endif; ?>
        </div>
    </header>

    <section class="page-hero">
        <div class="container">
            <h1>Your Profile</h1>
            <ul class="breadcrumb">
               
                <li class="current">Manage everythings here !</li>
            </ul>
        </div>
    </section>

    <main>
        <section class="dashboard-section">
            <div class="dashboard-container">

                <div class="sidebar">
                    <ul class="sidebar-menu">
                        <li onclick="location.href='AIBuddy_Profile.php?tab=account'">
                            <i class="fas fa-user-circle"></i> Account Details
                        </li>
                        <li onclick="location.href='AIBuddy_Profile.php?tab=subscription'">
                            <i class="fas fa-credit-card"></i> Manage Subscription
                        </li>
                        <li onclick="location.href='AIBuddy_Profile.php?tab=membership'">
                            <i class="fas fa-history"></i> Membership History
                        </li>
                        <li id="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Log Out
                        </li>
                    </ul>
                </div>

                <div class="dashboard-box">
                    
                    <?php if ($tab === 'account'): ?>
                        <h2>Account Details</h2>
                        <div class="account-form">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <div class="field-row">
                                    <input type="text" value="<?= htmlspecialchars($user['UserName']) ?>" class="form-control" disabled>
                                    <button type="button" class="edit-btn" onclick="openEditModal('UserName','Full Name','<?= htmlspecialchars($user['UserName']) ?>')">Edit</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email *</label>
                                <div class="field-row">
                                    <input type="email" value="<?= htmlspecialchars($user['UserEmail']) ?>" class="form-control" disabled>
                                    <button type="button" class="edit-btn" onclick="openEditModal('UserEmail','Email','<?= htmlspecialchars($user['UserEmail']) ?>')">Edit</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Phone Number *</label>
                                <div class="field-row">
                                    <input type="text" value="<?= htmlspecialchars($user['PhoneNumber']) ?>" class="form-control" disabled>
                                    <button type="button" class="edit-btn" onclick="openEditModal('PhoneNumber','Phone Number','<?= htmlspecialchars($user['PhoneNumber']) ?>')">Edit</button>
                                </div>
                            </div>
                            <hr>
                            <br>
                            <p><strong>Membership Status:</strong> <span class="status active"><?= $membershipStatus ?></span></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($tab === 'subscription'): ?>
                        <h2>Manage Subscription</h2>

                        <?php 
                        // Kiểm tra nếu không có gói hoặc gói đã bị hủy (Cancelled)
                        if (!$currentPlan || $currentPlan['OrderStatus'] === 'Cancelled'): 
                        ?>
                            <div style="text-align: center; padding: 30px;">
                                <i class="fas fa-ban" style="font-size: 40px; color: #ccc; margin-bottom: 15px;"></i>
                                <p style="font-size: 1.1rem; font-weight: 500; color: #555;">No Active Subscription</p>
                                <p style="color: #777;">You currently don't have any active plan. Subscribe to unlock premium features.</p>
                                <a href="AIBuddy_Trial.php" class="btn-primary" style="display:inline-block; margin-top:15px; padding: 10px 20px; text-decoration:none;">Browse Plans</a>
                            </div>
                        <?php else: ?>
                            <div class="account-form">
                                <div class="form-group">
                                    <label>Plan Name</label>
                                    <input class="form-control" value="<?= htmlspecialchars($currentPlan['PlanName']) ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label>Plan Description</label>
                                    <textarea class="form-control" rows="3" disabled><?= htmlspecialchars($currentPlan['PlanDescription']) ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Price</label>
                                    <input class="form-control" value="<?= number_format($currentPlan['PlanPrice']) ?> VND" disabled>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <input class="form-control" value="<?= $currentPlan['OrderStatus'] ?>" disabled style="color: green; font-weight: bold;">
                                </div>

                                <div style="display:flex; gap:15px; margin-top:25px;">
                                    <button class="edit-btn" style="background-color: #e74c3c;" onclick="openCancelModal()">Cancel Subscription</button>
                                    <button class="btn-primary" onclick="openRefundModal()">Request Refund</button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php
                        $refundSql = "SELECT * FROM refundrequest r JOIN userorder o ON r.TransactionID = o.OrderID WHERE o.UserID = ? ORDER BY r.RequestDate DESC";
                        $stmtRefund = $conn->prepare($refundSql);
                        $stmtRefund->bind_param("i", $UserID);
                        $stmtRefund->execute();
                        $refunds = $stmtRefund->get_result();
                        ?>
                        
                        <?php if ($refunds->num_rows > 0): ?>
                            <div class="refund-history-box">
                                <h3>Refund Requests History</h3>
                                <?php while($ref = $refunds->fetch_assoc()): ?>
                                    <div class="refund-item <?= strtolower($ref['RefundStatus']) ?>">
                                        <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                                            <span class="refund-status status-<?= strtolower($ref['RefundStatus']) ?>">
                                                <?= $ref['RefundStatus'] ?>
                                            </span>
                                            <small><?= $ref['RequestDate'] ?></small>
                                        </div>
                                        <p><strong>Reason:</strong> <?= htmlspecialchars($ref['RefundType']) ?></p>
                                        <p><strong>Details:</strong> <?= htmlspecialchars($ref['RefundDetails']) ?></p>
                                        
                                        <?php if(!empty($ref['AdminResponse'])): ?>
                                            <div class="admin-response">
                                                <strong>Admin Response:</strong> <?= htmlspecialchars($ref['AdminResponse']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>

                   <?php if ($tab === 'membership'): ?>
                        <h2>Membership History</h2>
                        <?php
                        // SỬA LỖI: Đổi OrderDate thành PurchaseTime cho đúng với database
                        $sqlHistory = "
                            SELECT o.OrderID, o.PurchaseTime, o.TotalAmount, o.OrderStatus, p.PlanName 
                            FROM userorder o
                            JOIN plan p ON o.PlanID = p.PlanID
                            WHERE o.UserID = ?
                            ORDER BY o.PurchaseTime DESC
                        ";
                        $stmtHist = $conn->prepare($sqlHistory);
                        
                        // Kiểm tra nếu prepare thất bại để debug
                        if (!$stmtHist) {
                            die("Lỗi SQL: " . $conn->error);
                        }

                        $stmtHist->bind_param("i", $UserID);
                        $stmtHist->execute();
                        $historyResult = $stmtHist->get_result();
                        ?>
                        <div style="overflow-x: auto;">
                            <table class="history-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Plan</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($historyResult->num_rows > 0): ?>
                                        <?php while ($row = $historyResult->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?= $row['OrderID'] ?></td>
                                                <td><strong><?= htmlspecialchars($row['PlanName']) ?></strong></td>
                                                <td><?= date('M d, Y H:i', strtotime($row['PurchaseTime'])) ?></td>
                                                <td><?= number_format($row['TotalAmount']) ?> VND</td>
                                                <td>
                                                    <span class="status-badge <?= strtolower($row['OrderStatus']) ?>">
                                                        <?= $row['OrderStatus'] ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" style="text-align: center; padding: 20px;">No history found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="dashboard-box">
                    <h2>Badges & Achievements</h2>
                    <?php if ($currentBadge): ?>
                        <div class="<?= $currentBadge['BadgeColor'] ?>">
                            <p><?= $currentBadge['BadgeSymbol'] ?>     <?= $currentBadge['BadgeName'] ?></p>
                            <small>Your current badge</small>
                        </div>
                    <?php else: ?>
                        <p>No badge earned yet.</p>
                    <?php endif; ?>
                    <br>
                    <div class="badge-grid">
                        <div class="badge1"><p>&#127941; Calm Master</p></div>
                        <div class="badge2"><p>&#129496; Focus Hero</p></div>
                        <div class="badge3"><p>&#128172; Consistency Streak</p></div>
                    </div>
                    <button class="btn-primary" id="openBadgeModal">View Details</button>
                </div>

            </div>
        </section>
    </main>

    <div class="modal-overlay" id="cancelModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Cancel Subscription</h2>
                <span class="close-modal" onclick="closeCancelModal()">&times;</span>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="cancel_subscription">
                <div class="form-group">
                    <label>Cancellation Type</label>
                    <select name="cancel_type" class="form-control" required>
                        <option value="Immediate">Cancel Immediately</option>
                        <option value="End of Period">Cancel at End of Period</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Reason (optional)</label>
                    <select name="cancel_reason" class="form-control">
                        <option value="">-- Select reason --</option>
                        <option>I don’t use the service much</option>
                        <option>Service is not suitable</option>
                        <option>Pricing issue</option>
                        <option>Other</option>
                    </select>
                </div>
                <button type="submit" class="edit-btn" style="background-color: #e74c3c;">Confirm Cancellation</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="refundModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Request Refund</h2>
                <span class="close-modal" onclick="closeRefundModal()">&times;</span>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="request_refund">
                
                <div class="form-group">
                    <label>Refund Reason</label>
                    <select name="refund_reason" class="form-control" required>
                        <option value="">-- Select Reason --</option>
                        <option value="Accidental Purchase">Accidental Purchase</option>
                        <option value="Service Not Working">Service Not Working</option>
                        <option value="Billing Error">Billing Error</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Refund Details *</label>
                    <textarea name="refund_details" class="form-control" rows="4" placeholder="Please explain why you want a refund..." required></textarea>
                </div>

                <button type="submit" class="btn-primary">Submit Request</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="editModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2 id="modalTitle">Edit</h2>
                <span class="close-modal" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="post">
                <input type="hidden" name="field" id="editField">
                <div class="form-group">
                    <label id="editLabel"></label>
                    <input type="text" name="value" id="editValue" class="form-control">
                </div>
                <button type="submit" name="update_single" class="btn-primary">Save</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="badgeModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>🏆 Badge Requirements</h2>
                <span class="close-modal" id="closeModal">&times;</span>
            </div>
            <div class="modal-content">
                <div class="badge-requirement">
                    <h3>🏅 Calm Master</h3>
                    <ul style="list-style:none; padding-left:15px;">
                        <li>✔ Essential plan or higher</li>
                        <li>✔ Active subscription</li>
                    </ul>
                </div>
                <div class="badge-requirement">
                    <h3>🧘 Focus Hero</h3>
                    <ul style="list-style:none; padding-left:15px;">
                        <li>✔ Premium plan required</li>
                        <li>✔ Complete 15 focus sessions</li>
                    </ul>
                </div>
                <div class="badge-requirement">
                    <h3>🔥 Consistency Streak</h3>
                    <ul style="list-style:none; padding-left:15px;">
                        <li>✔ Premium plan required</li>
                        <li>✔ Active 7 consecutive days</li>
                    </ul>
                </div>
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
        function openCancelModal() { document.getElementById("cancelModal").style.display = "flex"; }
        function closeCancelModal() { document.getElementById("cancelModal").style.display = "none"; }
        function openRefundModal() { document.getElementById("refundModal").style.display = "flex"; }
        function closeRefundModal() { document.getElementById("refundModal").style.display = "none"; }
        
        function openEditModal(field, label, value) {
            document.getElementById("editModal").style.display = "flex";
            document.getElementById("editField").value = field;
            document.getElementById("editLabel").innerText = label;
            document.getElementById("editValue").value = value;
        }
        function closeEditModal() { document.getElementById("editModal").style.display = "none"; }

        // Badge Modal
        const badgeBtn = document.getElementById("openBadgeModal");
        const badgeModal = document.getElementById("badgeModal");
        const closeModal = document.getElementById("closeModal");
        if(badgeBtn) badgeBtn.addEventListener("click", () => badgeModal.style.display = "flex");
        if(closeModal) closeModal.addEventListener("click", () => badgeModal.style.display = "none");
        if(badgeModal) badgeModal.addEventListener("click", (e) => { if (e.target === badgeModal) badgeModal.style.display = "none"; });

        // Logout
        document.getElementById("logout-btn").addEventListener("click", () => { window.location.href = "AIBuddy_Logout.php"; });
    </script>
</body>
</html>