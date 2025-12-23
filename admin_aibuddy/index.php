<?php
// ================================================================
// MAIN DASHBOARD PAGE (ROOT)
// File: index.php
// ================================================================
session_start();

// 1. DATABASE CONNECTION (This file is in root, so just go into config folder)
require_once __DIR__ . '/config/db.php';

// 2. LOGIN CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 3. GET OVERVIEW STATISTICS

// Count Users
$res_user = $conn->query("SELECT COUNT(*) as count FROM Users");
$count_user = $res_user->fetch_assoc()['count'];

// Count Chatbot Messages
$res_chat = $conn->query("SELECT COUNT(*) as count FROM chathistory");
$count_chat = $res_chat->fetch_assoc()['count'];

// Count Personas (AI Characters)
$res_persona = $conn->query("SELECT COUNT(*) as count FROM persona");
$count_persona = $res_persona->fetch_assoc()['count'];

// Count Pending Reports (Assuming report table exists)
$count_report = 0;
$check_report = $conn->query("SHOW TABLES LIKE 'report'");
if($check_report->num_rows > 0){
    $res_report = $conn->query("SELECT COUNT(*) as count FROM report WHERE Status = 'Pending'");
    $count_report = $res_report->fetch_assoc()['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AI Buddy</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Custom CSS for Main Dashboard */
        :root { --primary-color: #124559; }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .card-menu {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: inherit;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
        }

        .card-menu:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        .card-header-bg {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header-bg i { font-size: 30px; opacity: 0.8; }
        .card-header-bg span { font-size: 18px; font-weight: 600; }

        .card-body-content {
            padding: 20px;
            text-align: center;
        }

        .big-number {
            font-size: 36px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
            display: block;
        }
        
        .sub-text { font-size: 14px; color: #777; }
        
        .link-text {
            margin-top: 15px;
            display: inline-block;
            font-size: 13px;
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>System Overview</h2>
        <div class="user-profile">
            <span>Welcome, <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></span>
        </div>
    </div>

    <div class="main-content">
        
        <div class="card-box" style="margin-bottom: 25px; border-left: 5px solid var(--primary-color);">
            <h3 style="margin: 0; color: #333;">👋 Welcome back!</h3>
            <p style="margin: 5px 0 0; color: #666;">Below are the activity statistics of the AI Buddy system.</p>
        </div>

        <div class="dashboard-grid">
            
            <a href="modules/users/index.php" class="card-menu">
                <div class="card-header-bg" style="background: #2980b9;">
                    <span>Users</span>
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="card-body-content">
                    <span class="big-number"><?php echo number_format($count_user); ?></span>
                    <span class="sub-text">Registered Accounts</span>
                    <br>
                    <span class="link-text">Manage Users &rarr;</span>
                </div>
            </a>

            <a href="modules/chatbot/index.php" class="card-menu">
                <div class="card-header-bg" style="background: var(--primary-color);">
                    <span>Chat History</span>
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div class="card-body-content">
                    <span class="big-number"><?php echo number_format($count_chat); ?></span>
                    <span class="sub-text">Stored Messages</span>
                    <br>
                    <span class="link-text">View Details &rarr;</span>
                </div>
            </a>

            <a href="modules/chatbot/views/personas/index.php" class="card-menu">
                <div class="card-header-bg" style="background: #27ae60;">
                    <span>AI Personas</span>
                    <i class="fa-solid fa-masks-theater"></i>
                </div>
                <div class="card-body-content">
                    <span class="big-number"><?php echo number_format($count_persona); ?></span>
                    <span class="sub-text">Active Characters</span>
                    <br>
                    <span class="link-text">Configure &rarr;</span>
                </div>
            </a>

            <a href="modules/reports/index.php" class="card-menu">
                <div class="card-header-bg" style="background: #e67e22;">
                    <span>Reports & Support</span>
                    <i class="fa-solid fa-flag"></i>
                </div>
                <div class="card-body-content">
                    <span class="big-number"><?php echo number_format($count_report); ?></span>
                    <span class="sub-text">Pending Requests</span>
                    <br>
                    <span class="link-text">Process Now &rarr;</span>
                </div>
            </a>

        </div>

        <div class="card-box" style="margin-top: 30px;">
            <h3 style="color: var(--primary-color); border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0;">
                <i class="fa-regular fa-clock"></i> Recent Chat Activity
            </h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background: #f8f9fa; color: #555;">
                        <th style="padding: 10px; text-align: left;">Time</th>
                        <th style="padding: 10px; text-align: left;">User ID</th>
                        <th style="padding: 10px; text-align: left;">Content</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get the 5 most recent messages for quick view
                    $last_msg = $conn->query("SELECT * FROM chathistory ORDER BY ChatTime DESC LIMIT 5");
                    if($last_msg && $last_msg->num_rows > 0) {
                        while($row = $last_msg->fetch_assoc()){
                            echo "<tr style='border-bottom: 1px solid #eee;'>";
                            echo "<td style='padding: 10px; color: #666; font-size: 13px;'>" . date("H:i d/m", strtotime($row['ChatTime'])) . "</td>";
                            echo "<td style='padding: 10px; font-weight: bold; color: var(--primary-color);'>" . $row['UserID'] . "</td>";
                            echo "<td style='padding: 10px; color: #333;'>" . htmlspecialchars(substr($row['MessageContent'], 0, 50)) . "...</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='padding:15px; text-align:center'>No activity yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
    <div class="main-content">
        
        <?php include __DIR__ . '/../../htdocs/admin_aibuddy/includes/footer.php'; ?>
        
    </div>

</body>
</html>