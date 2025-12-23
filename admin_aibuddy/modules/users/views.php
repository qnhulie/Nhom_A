<?php
// ================================================================
// MODULE: USER MANAGER - VIEW DETAILS
// File: modules/users/views.php
// ================================================================
session_start();
require_once __DIR__ . '/../../config/db.php';

// Check admin privileges
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Check ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("User ID not found!");
}

$id = intval($_GET['id']);

// Get user info
$sql = "SELECT * FROM Users WHERE UserID = $id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    die("User does not exist!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Details: <?php echo htmlspecialchars($user['UserName']); ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .detail-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .detail-table td { padding: 12px 15px; border-bottom: 1px solid #eee; vertical-align: top; }
        .detail-table tr:last-child td { border-bottom: none; }
        .label-col { font-weight: 600; color: var(--primary); width: 150px; background: #f9f9f9; }
        .value-col { color: #333; }
        .card-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>User Profile</h2>
        <div class="user-profile">Admin: <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></div>
    </div>

    <div class="main-content">
        <a href="index.php" style="display: inline-block; margin-bottom: 20px; text-decoration: none; color: var(--primary); font-weight: 600;">
            <i class="fa-solid fa-arrow-left"></i> Back to list
        </a>

        <div class="card-box" style="max-width: 700px; margin: 0 auto;">
            
            <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px;">
                <div class="icon-box" style="width: 80px; height: 80px; font-size: 40px; margin: 0 auto 15px auto; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-user"></i>
                </div>
                <h2 style="color: var(--primary); margin: 0;"><?php echo htmlspecialchars($user['UserName']); ?></h2>
                <p style="color: #777; margin-top: 5px;">ID: #<?php echo $user['UserID']; ?></p>
            </div>

            <table class="detail-table">
                <tr>
                    <td class="label-col">Email:</td>
                    <td class="value-col"><?php echo htmlspecialchars($user['UserEmail']); ?></td>
                </tr>
                <tr>
                    <td class="label-col">Phone Number:</td>
                    <td class="value-col">
                        <?php echo !empty($user['PhoneNumber']) ? htmlspecialchars($user['PhoneNumber']) : '<span style="color:#aaa;">Not updated</span>'; ?>
                    </td>
                </tr>
                
               <tr>
                    <td class="label-col">Birth Date:</td>
                    <td class="value-col">
                        <?php 
                        // Check rigorously: not empty, not null, and not default date '0000-00-00'
                        if (!empty($user['BirthDate']) && $user['BirthDate'] != '0000-00-00') {
                            // Convert to d/m/Y format
                            $dateObj = date_create($user['BirthDate']);
                            if ($dateObj) {
                                echo date_format($dateObj, 'd/m/Y');
                            } else {
                                // If data exists but format is wrong -> show original for debug
                                echo htmlspecialchars($user['BirthDate']); 
                            }
                        } else {
                            echo '<span style="color:#aaa; font-style:italic;">Not updated</span>';
                        }
                        ?>
                    </td>
                </tr>

                <tr>
                    <td class="label-col">Gender:</td>
                    <td class="value-col">
                        <?php 
                        // Check rigorously: trim() to remove extra spaces if any
                        $gender = isset($user['Gender']) ? trim($user['Gender']) : '';
                        
                        if ($gender !== '' && $gender !== null) {
                            echo htmlspecialchars($gender);
                        } else {
                            echo '<span style="color:#aaa; font-style:italic;">Not updated</span>';
                        }
                        ?>
                    </td>
                </tr>

                <tr>
                    <td class="label-col">Current Plan:</td>
                    <td class="value-col">
                        <?php 
                            // Logic to display trial/paid status
                            if ($user['UsageLeft'] >= 9000) echo "<span style='color:green; font-weight:bold;'>Premium / Unlimited</span>";
                            elseif ($user['IsTrialActive'] == 1) echo "<span style='color:blue;'>Free Trial (Active)</span>";
                            elseif ($user['IsTrialActive'] == 2) echo "<span style='color:red;'>Trial Expired</span>";
                            else echo "Free User";
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Usage Left:</td>
                    <td class="value-col"><?php echo $user['UsageLeft']; ?> uses</td>
                </tr>
            </table>
            
            <div style="margin-top: 40px; text-align: center;">
                 <a href="index.php?delete_id=<?php echo $user['UserID']; ?>" 
                    onclick="return confirm('WARNING: Are you sure you want to permanently delete this account? This action cannot be undone.');"
                    style="background: #e74c3c; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; box-shadow: 0 4px 6px rgba(231, 76, 60, 0.3);">
                    <i class="fa-solid fa-trash"></i> Delete Account
                 </a>
            </div>
        </div>
    </div>

</body>
</html>