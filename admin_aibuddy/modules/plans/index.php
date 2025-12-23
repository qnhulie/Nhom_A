<?php
// modules/plans/index.php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// HANDLE PLAN DELETION (Code xóa giữ nguyên như cũ - Nên dùng cách kiểm tra ràng buộc khóa ngoại)
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    $checkOrder = $conn->query("SELECT COUNT(*) as count FROM userorder WHERE PlanID = $id");
    $rowOrder = $checkOrder->fetch_assoc();

    $checkFeature = $conn->query("SELECT COUNT(*) as count FROM planfeature WHERE PlanID = $id");
    $rowFeature = $checkFeature->fetch_assoc();

    if ($rowOrder['count'] > 0) {
        echo "<script>alert('Cannot delete this plan because it has active subscriptions!'); window.location.href='index.php';</script>";
    } else {
        if ($rowFeature['count'] > 0) {
            $conn->query("DELETE FROM planfeature WHERE PlanID = $id");
        }
        $conn->query("DELETE FROM Plan WHERE PlanID = $id");
        echo "<script>alert('Service plan deleted successfully!'); window.location.href='index.php';</script>";
    }
    exit();
}

// Get plan list
$sql = "SELECT * FROM Plan ORDER BY PlanPrice ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Plan Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>Service Plan Management</h2>
        <div class="user-profile">Admin: <?php echo $_SESSION['user_name']; ?></div>
    </div>

    <div class="main-content">
        
        <div style="margin-bottom: 20px; text-align: right;">
            <a href="add.php" class="btn" style="background: var(--primary); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                <i class="fa-solid fa-plus"></i> Add New Plan
            </a>
        </div>

        <div class="table-container card-box" style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--primary); color: white;">
                        <th style="padding: 15px;">ID</th>
                        <th style="padding: 15px;">Plan Name</th>
                        <th style="padding: 15px;">Price (VND)</th>
                        <th style="padding: 15px;">Cycle</th>
                        <th style="padding: 15px;">Video Intro</th> <th style="padding: 15px; width: 25%;">Description</th>
                        <th style="padding: 15px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px;">#<?php echo $row['PlanID']; ?></td>
                                <td style="padding: 15px; font-weight: bold; color: var(--primary);">
                                    <?php echo $row['PlanName']; ?>
                                </td>
                                <td style="padding: 15px; font-weight: bold; color: #e67e22;">
                                    <?php echo number_format($row['PlanPrice'], 0, ',', '.'); ?> đ
                                </td>
                                <td style="padding: 15px;">
                                    <span style="background: #eef2f3; padding: 5px 10px; border-radius: 15px; font-size: 12px;">
                                        <?php echo $row['BillingCycle']; ?>
                                    </span>
                                </td>
                                <td style="padding: 15px;">
                                    <?php if (!empty($row['PlanVideoURL'])): ?>
                                        <a href="<?php echo htmlspecialchars($row['PlanVideoURL']); ?>" target="_blank" style="color: #e74c3c; text-decoration: none;">
                                            <i class="fa-brands fa-youtube"></i> Watch
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #ccc;">No Video</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px; font-size: 14px; color: #666;">
                                    <?php echo substr($row['PlanDescription'], 0, 50) . '...'; ?>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <a href="edit.php?id=<?php echo $row['PlanID']; ?>" style="color: var(--accent); margin-right: 10px; font-size: 18px;" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="index.php?delete_id=<?php echo $row['PlanID']; ?>" onclick="return confirm('Are you sure you want to delete this plan?');" style="color: #e74c3c; font-size: 18px;" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="padding: 30px; text-align: center;">No service plans found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>