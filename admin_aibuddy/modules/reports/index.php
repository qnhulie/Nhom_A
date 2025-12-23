<?php
// ================================================================
// MODULE: REPORTS DASHBOARD
// File: modules/reports/index.php
// ================================================================
session_start();
require_once __DIR__ . '/../../config/db.php';

// Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Get statistics for pending items
$sql_report = "SELECT COUNT(*) as count FROM report WHERE Status = 'Pending'";
$res_report = $conn->query($sql_report);
$count_report = $res_report->fetch_assoc()['count'];

$sql_refund = "SELECT COUNT(*) as count FROM refundrequest WHERE RefundStatus = 'Pending'";
$res_refund = $conn->query($sql_refund);
$count_refund = $res_refund->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Center</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* NEW CSS TO DECORATE CARDS */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); /* Increase min-width for better header look */
            gap: 25px;
        }

        .stat-card {
            display: flex;
            flex-direction: column; /* Stack header and body vertically */
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e0e0e0; /* Add border */
            overflow: hidden; /* Ensure header doesn't overflow rounded corners */
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            border-color: var(--primary, #124559); /* Change border color on hover */
        }

        /* New Header for Card (Dark Blue) */
        .card-header {
            background-color: var(--primary, #124559); /* Use primary color or default dark blue */
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: 600;
        }

        /* Body containing content and icon */
        .card-body {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 36px; /* Increase number size */
            font-weight: bold;
            color: var(--primary, #124559);
        }

        .stat-info p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }

        .stat-icon {
            font-size: 45px;
            color: var(--primary, #124559);
            opacity: 0.2; /* Reduce opacity for background effect */
        }

        /* "Manage Now" Link */
        .card-action-link {
            font-size: 13px;
            color: var(--primary, #124559);
            margin-top: 15px;
            display: block;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>Support & Report Center</h2>
        <div class="user-profile">
            <span>Welcome, <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></span>
        </div>
    </div>

    <div class="main-content">
        
        <div class="dashboard-cards">
            <a href="report.php" class="stat-card">
                <div class="card-header">
                    User Reports
                </div>
                <div class="card-body">
                    <div class="stat-info">
                        <h3><?php echo $count_report; ?></h3>
                        <p>(Pending processing)</p>
                        <span class="card-action-link">Manage Now &rarr;</span>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-flag"></i></div>
                </div>
            </a>

            <a href="refunds.php" class="stat-card">
                <div class="card-header">
                    Refund Requests
                </div>
                <div class="card-body">
                    <div class="stat-info">
                        <h3><?php echo $count_refund; ?></h3>
                        <p>(Pending processing)</p>
                        <span class="card-action-link">Process Now &rarr;</span>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                </div>
            </a>
        </div>

    </div>
</body>
</html>