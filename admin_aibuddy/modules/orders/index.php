<?php
// modules/orders/index.php
session_start();
require_once __DIR__ . '/../../config/db.php';

// 1. Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// 2. Handle Search
$search = "";
$where_clause = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_safe = $conn->real_escape_string($search);
    
    // Search by Order ID, or User Name, or Status
    $where_clause = "WHERE UserOrder.OrderID = '$search_safe' 
                      OR Users.UserName LIKE '%$search_safe%' 
                      OR UserOrder.OrderStatus LIKE '%$search_safe%'";
}

// 3. SQL Query (Join 3 tables: UserOrder + Users + Plan)
// Note: If your table is named 'Orders' instead of 'UserOrder', please update the table name below
$sql = "SELECT UserOrder.*, Users.UserName, Plan.PlanName 
        FROM UserOrder
        INNER JOIN Users ON UserOrder.UserID = Users.UserID
        INNER JOIN Plan ON UserOrder.PlanID = Plan.PlanID
        $where_clause
        ORDER BY UserOrder.PurchaseTime DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS for Order Status Badge */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .bg-completed { background-color: #d4edda; color: #155724; } /* Green */
        .bg-pending { background-color: #fff3cd; color: #856404; }   /* Yellow */
        .bg-cancelled { background-color: #f8d7da; color: #721c24; } /* Red */
    </style>
</head>
<body>

    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="top-navbar">
        <h2>Order Management</h2>
        <div class="user-profile">Admin</div>
    </div>

    <div class="main-content">
        
        <div class="card-box" style="margin-bottom: 20px; padding: 20px;">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="search" class="form-control" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" 
                       placeholder="Enter Order ID, Customer Name or Status..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                
                <button type="submit" class="btn" style="background: var(--primary); color: white; border: none; padding: 0 20px; border-radius: 5px; cursor: pointer;">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
                
                <?php if($search): ?>
                    <a href="index.php" class="btn" style="background: #999; color: white; padding: 10px; text-decoration: none; border-radius: 5px; display:flex; align-items:center;">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container card-box" style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--primary); color: white;">
                        <th style="padding: 15px;">Order ID</th>
                        <th style="padding: 15px;">Customer</th>
                        <th style="padding: 15px;">Service Plan</th>
                        <th style="padding: 15px;">Total Amount</th>
                        <th style="padding: 15px;">Purchase Date</th>
                        <th style="padding: 15px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; font-weight: bold;">
                                    #<?php echo $row['OrderID']; ?>
                                </td>
                                
                                <td style="padding: 15px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fa-solid fa-user-circle" style="color: var(--primary-light);"></i>
                                        <?php echo $row['UserName']; ?>
                                    </div>
                                    <small style="color: #999;">ID: <?php echo $row['UserID']; ?></small>
                                </td>
                                
                                <td style="padding: 15px; color: var(--primary);">
                                    <?php echo $row['PlanName']; ?>
                                </td>
                                
                                <td style="padding: 15px; font-weight: bold;">
                                    <?php echo number_format($row['TotalAmount'], 0, ',', '.'); ?> đ
                                </td>
                                
                                <td style="padding: 15px;">
                                    <?php echo date('d/m/Y H:i', strtotime($row['PurchaseTime'])); ?>
                                </td>
                                
                                <td style="padding: 15px;">
                                    <?php 
                                        $statusClass = 'bg-pending';
                                        if($row['OrderStatus'] == 'Completed') $statusClass = 'bg-completed';
                                        if($row['OrderStatus'] == 'Cancelled') $statusClass = 'bg-cancelled';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo $row['OrderStatus']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 30px; text-align: center; color: #999;">
                                No orders found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>